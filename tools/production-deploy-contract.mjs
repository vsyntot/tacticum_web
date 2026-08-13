#!/usr/bin/env node

import { createHash } from 'node:crypto';
import { constants as fsConstants } from 'node:fs';
import {
  access,
  lstat,
  opendir,
  readFile,
  readlink,
  stat,
} from 'node:fs/promises';
import path from 'node:path';

export const MANIFEST_SCHEMA_VERSION = 1;
export const PLAN_SCHEMA_VERSION = 1;

export async function loadDeployScope(scopePath = 'tools/deploy-scope.json') {
  const raw = await readFile(scopePath, 'utf8');
  const scope = JSON.parse(raw);
  validateDeployScope(scope);
  return scope;
}

export function validateDeployScope(scope) {
  assert(scope && typeof scope === 'object' && !Array.isArray(scope), 'deploy scope must be an object');
  assert(scope.schemaVersion === 1, 'deploy scope schemaVersion must be 1');
  assertString(scope.scopeVersion, 'scopeVersion');
  assert(scope.releaseClass === 'FILE_ONLY', 'deploy scope releaseClass must be FILE_ONLY');

  for (const field of [
    'authoritativeDirectories',
    'exactRootFiles',
    'generatedFiles',
    'tombstones',
    'exclusions',
    'forbiddenPrefixes',
    'forbiddenExactPaths',
    'nonDeployablePrefixes',
    'nonDeployableExactPaths',
    'controlPlanePrefixes',
    'controlPlaneExactPaths',
    'statefulTriggers',
  ]) {
    assert(Array.isArray(scope[field]), `${field} must be an array`);
  }

  for (const directory of scope.authoritativeDirectories) {
    assertSafeRelativePath(directory, 'authoritative directory');
    assert(!directory.includes('/'), `authoritative directory must be top-level: ${directory}`);
  }
  for (const file of scope.exactRootFiles) {
    assertSafeRelativePath(file, 'exact root file');
    assert(!file.includes('/'), `exact root file must be root-level: ${file}`);
  }
  for (const item of scope.generatedFiles) {
    assert(item && typeof item === 'object', 'generated file entry must be an object');
    assertSafeRelativePath(item.path, 'generated file');
    assert(!item.path.includes('/'), `generated file must be root-level: ${item.path}`);
    assertString(item.generator, `generator for ${item.path}`);
    assert(item.generateOnce === true, `generated file ${item.path} must set generateOnce=true`);
  }
  for (const file of scope.tombstones) {
    assertSafeRelativePath(file, 'tombstone');
    assert(!file.includes('/'), `tombstone must be root-level: ${file}`);
  }
  for (const value of [...scope.forbiddenPrefixes, ...scope.nonDeployablePrefixes, ...scope.controlPlanePrefixes]) {
    assertSafeRelativePath(value, 'prefix');
  }
  for (const value of [...scope.forbiddenExactPaths, ...scope.nonDeployableExactPaths, ...scope.controlPlaneExactPaths]) {
    assertSafeRelativePath(value, 'exact path');
  }

  const managed = [
    ...scope.authoritativeDirectories,
    ...scope.exactRootFiles,
    ...scope.generatedFiles.map((item) => item.path),
    ...scope.tombstones,
  ];
  assertUnique(managed, 'managed deploy paths');

  for (const exclusion of scope.exclusions) {
    validateRule(exclusion, 'exclusion');
    const root = exclusion.kind === 'exact'
      ? scope.authoritativeDirectories.find((candidate) => isWithin(exclusion.path, candidate))
      : exclusion.root;
    assert(scope.authoritativeDirectories.includes(root), `exclusion is outside authoritative directories: ${ruleLabel(exclusion)}`);
  }
  for (const trigger of scope.statefulTriggers) {
    validateRule(trigger, 'stateful trigger');
  }

  assert(Array.isArray(scope.rsyncOptions), 'rsyncOptions must be an array');
  const requiredRsyncOptions = [
    '--archive',
    '--no-owner',
    '--no-group',
    '--checksum',
    '--delay-updates',
    '--delete-delay',
  ];
  assertUnique(scope.rsyncOptions, 'rsyncOptions');
  for (const option of scope.rsyncOptions) {
    assert(/^--[a-z-]+$/.test(option), `unsafe rsync option: ${option}`);
  }
  for (const option of requiredRsyncOptions) {
    assert(scope.rsyncOptions.includes(option), `rsyncOptions is missing ${option}`);
  }

  const accessConfig = scope.localAccess;
  assert(accessConfig && typeof accessConfig === 'object', 'localAccess must be an object');
  for (const field of ['requiredKeys', 'optionalKeys', 'forbiddenKeys']) {
    assert(Array.isArray(accessConfig[field]), `localAccess.${field} must be an array`);
    for (const key of accessConfig[field]) {
      assert(/^[A-Z][A-Z0-9_]*$/.test(key), `invalid env key in localAccess.${field}`);
    }
  }
  assertUnique([
    ...accessConfig.requiredKeys,
    ...accessConfig.optionalKeys,
    ...accessConfig.forbiddenKeys,
  ], 'local access env keys');
  assert(accessConfig.sshUser === 'bitrix', 'localAccess.sshUser must be bitrix');
  assert(accessConfig.dedicatedKeyBasename === 'tacticum_prod_bitrix_ed25519', 'unexpected dedicated key basename');
  assert(accessConfig.originalCommand === 'manifest', 'localAccess.originalCommand must be manifest');

  return scope;
}

export function classifyChangedPaths(paths, scope) {
  validateDeployScope(scope);
  const normalizedPaths = [...new Set(paths.map((value) => normalizeRelativePath(value)))].sort();
  const result = {
    releaseClass: 'NO_DEPLOYABLE_CHANGE',
    assurance: 'PATH_SCOPE_ONLY_REQUIRES_DATA_LIFECYCLE_REVIEW',
    productionMutationAllowed: false,
    deployablePaths: [],
    statefulPaths: [],
    forbiddenPaths: [],
    controlPlanePaths: [],
    unknownPaths: [],
    ignoredPaths: [],
    reasons: [],
  };

  for (const relativePath of normalizedPaths) {
    const stateful = scope.statefulTriggers.find((rule) => matchesRule(relativePath, rule));
    if (stateful) {
      result.statefulPaths.push(relativePath);
      result.reasons.push({ path: relativePath, reason: stateful.reason });
      continue;
    }
    if (scope.forbiddenExactPaths.includes(relativePath)
      || scope.forbiddenPrefixes.some((prefix) => isWithin(relativePath, prefix))) {
      result.forbiddenPaths.push(relativePath);
      continue;
    }
    if (scope.controlPlaneExactPaths.includes(relativePath)
      || scope.controlPlanePrefixes.some((prefix) => isWithin(relativePath, prefix))) {
      result.controlPlanePaths.push(relativePath);
      continue;
    }
    if (scope.nonDeployableExactPaths.includes(relativePath)
      || scope.nonDeployablePrefixes.some((prefix) => isWithin(relativePath, prefix))) {
      result.ignoredPaths.push(relativePath);
      continue;
    }

    const category = classifyScopePath(relativePath, scope);
    if (category === 'authoritative' || category === 'exact' || category === 'generated') {
      result.deployablePaths.push(relativePath);
    } else if (category === 'excluded') {
      result.forbiddenPaths.push(relativePath);
    } else if (category === 'tombstone') {
      result.deployablePaths.push(relativePath);
    } else {
      result.unknownPaths.push(relativePath);
    }
  }

  if (result.forbiddenPaths.length > 0) {
    result.releaseClass = 'BLOCKED_FORBIDDEN';
  } else if (result.statefulPaths.length > 0) {
    result.releaseClass = 'STATEFUL';
  } else if (result.controlPlanePaths.length > 0) {
    result.releaseClass = 'CONTROL_PLANE_REVIEW_REQUIRED';
  } else if (result.unknownPaths.length > 0) {
    result.releaseClass = 'BLOCKED_UNKNOWN';
  } else if (result.deployablePaths.length > 0) {
    result.releaseClass = 'FILE_ONLY';
  }

  result.productionMutationAllowed = false;

  return result;
}

export function classifyScopePath(relativePath, scope) {
  const normalized = normalizeRelativePath(relativePath);
  if (scope.exclusions.some((rule) => matchesRule(normalized, rule))) {
    return 'excluded';
  }
  if (scope.authoritativeDirectories.some((directory) => isWithin(normalized, directory))) {
    return 'authoritative';
  }
  if (scope.exactRootFiles.includes(normalized)) {
    return 'exact';
  }
  if (scope.generatedFiles.some((item) => item.path === normalized)) {
    return 'generated';
  }
  if (scope.tombstones.includes(normalized)) {
    return 'tombstone';
  }
  return 'outside';
}

export function buildRsyncContract(scope) {
  validateDeployScope(scope);
  const baseOptions = [...scope.rsyncOptions];
  const authoritativeDirectories = scope.authoritativeDirectories.map((directory) => ({
    kind: 'authoritative_directory',
    path: directory,
    source: `${directory}/`,
    destination: `${directory}/`,
    options: [...baseOptions, '--delete'],
    filters: scope.exclusions
      .filter((rule) => rule.kind === 'exact' ? isWithin(rule.path, directory) : rule.root === directory)
      .map((rule) => rsyncFilterForRule(rule, directory)),
  }));
  return {
    scopeVersion: scope.scopeVersion,
    baseOptions,
    authoritativeDirectories,
    exactFiles: {
      kind: 'exact_files',
      paths: [
        ...scope.exactRootFiles,
        ...scope.generatedFiles.map((item) => item.path),
      ].sort(compareCodePoints),
      options: baseOptions,
      delete: false,
    },
    tombstones: {
      kind: 'explicit_tombstones',
      paths: [...scope.tombstones].sort(compareCodePoints),
    },
  };
}

export async function buildCanonicalManifest(rootPath, scope, options = {}) {
  validateDeployScope(scope);
  const absoluteRoot = path.resolve(rootPath);
  const rootStats = await lstat(absoluteRoot);
  assert(rootStats.isDirectory(), 'manifest root must be a directory');
  const entries = [];

  for (const directory of scope.authoritativeDirectories) {
    const absoluteDirectory = path.join(absoluteRoot, ...directory.split('/'));
    await assertDirectory(absoluteDirectory, `authoritative directory ${directory}`);
    await walkManagedDirectory(absoluteRoot, absoluteDirectory, scope, entries);
  }

  for (const relativePath of scope.exactRootFiles) {
    entries.push(await manifestEntry(absoluteRoot, relativePath, scope));
  }
  for (const item of scope.generatedFiles) {
    entries.push(await manifestEntry(absoluteRoot, item.path, scope));
  }
  for (const tombstone of scope.tombstones) {
    const absolutePath = path.join(absoluteRoot, ...tombstone.split('/'));
    if (await pathExists(absolutePath)) {
      throw new Error(`tombstone target must be absent from candidate artifact: ${tombstone}`);
    }
  }

  entries.sort((left, right) => compareCodePoints(left.path, right.path));
  assertUnique(entries.map((entry) => entry.path), 'manifest entries');

  const payload = {
    schemaVersion: MANIFEST_SCHEMA_VERSION,
    scopeVersion: scope.scopeVersion,
    treeKind: options.treeKind || 'candidate',
    entries,
    tombstones: [...scope.tombstones].sort(),
  };
  return {
    ...payload,
    manifestSha256: sha256(canonicalStringify(payload)),
  };
}

export function compareManifests({ base = null, prod, candidate, scope }) {
  validateDeployScope(scope);
  validateManifest(prod, scope);
  validateManifest(candidate, scope);
  if (base !== null) {
    validateManifest(base, scope);
  }

  const baseEntries = base ? entryMap(base) : new Map();
  const prodEntries = entryMap(prod);
  const candidateEntries = entryMap(candidate);
  const allPaths = [...new Set([
    ...baseEntries.keys(),
    ...prodEntries.keys(),
    ...candidateEntries.keys(),
  ])].sort();
  const changes = [];

  for (const relativePath of allPaths) {
    const baseEntry = baseEntries.get(relativePath) || null;
    const prodEntry = prodEntries.get(relativePath) || null;
    const candidateEntry = candidateEntries.get(relativePath) || null;
    const status = base === null
      ? compareWithoutBase(prodEntry, candidateEntry)
      : compareWithBase(baseEntry, prodEntry, candidateEntry);
    if (status !== 'UNCHANGED') {
      changes.push({
        path: relativePath,
        status,
        base: safeEntrySummary(baseEntry),
        prod: safeEntrySummary(prodEntry),
        candidate: safeEntrySummary(candidateEntry),
      });
    }
  }

  const payload = {
    schemaVersion: PLAN_SCHEMA_VERSION,
    scopeVersion: scope.scopeVersion,
    planKind: 'DRIFT_COMPARISON_ONLY',
    productionMutationAllowed: false,
    comparisonMode: base === null ? 'BASE_UNKNOWN_TWO_WAY' : 'THREE_WAY',
    baseManifestSha256: base?.manifestSha256 || null,
    prodManifestSha256: prod.manifestSha256,
    candidateManifestSha256: candidate.manifestSha256,
    changes,
  };
  return {
    ...payload,
    planId: sha256(canonicalStringify(payload)),
  };
}

export function parseLocalEnv(source, localAccess) {
  const allowed = new Set([...localAccess.requiredKeys, ...localAccess.optionalKeys]);
  const forbidden = new Set(localAccess.forbiddenKeys);
  const values = new Map();
  const errors = [];

  for (const [index, originalLine] of source.split(/\r?\n/).entries()) {
    const lineNumber = index + 1;
    const line = originalLine.trim();
    if (line === '' || line.startsWith('#')) {
      continue;
    }
    if (/^export\s+/.test(line)) {
      errors.push(`line ${lineNumber}: export syntax is forbidden`);
      continue;
    }
    const match = line.match(/^([A-Za-z_][A-Za-z0-9_]*)\s*=\s*(.*)$/);
    if (!match) {
      errors.push(`line ${lineNumber}: invalid dotenv assignment`);
      continue;
    }
    const [, key, rawValue] = match;
    if (forbidden.has(key)) {
      errors.push(`${key} is forbidden for production tooling`);
      continue;
    }
    if (!allowed.has(key)) {
      if (key.startsWith('PROD_')) {
        errors.push(`${key} is not allowlisted for production tooling`);
      }
      continue;
    }
    if (values.has(key)) {
      errors.push(`${key} is duplicated`);
      continue;
    }
    const value = parseLiteralEnvValue(rawValue, key, errors);
    if (value !== null) {
      values.set(key, value);
    }
  }

  for (const key of localAccess.requiredKeys) {
    if (!values.get(key)) {
      errors.push(`${key} is missing or empty`);
    }
  }
  if (errors.length > 0) {
    throw new Error(`local production env validation failed:\n- ${errors.join('\n- ')}`);
  }
  return Object.fromEntries(values);
}

export function buildStrictSshArgs(config, scope) {
  const accessConfig = scope.localAccess;
  assert(config.PROD_SSH_USER === accessConfig.sshUser, 'unexpected production SSH user');
  const port = parsePort(config.PROD_SSH_PORT);
  const host = validateHost(config.PROD_HOST);
  validateAbsoluteSafePath(config.PROD_SSH_KEY_PATH, 'PROD_SSH_KEY_PATH');
  validateAbsoluteSafePath(config.PROD_SSH_KNOWN_HOSTS_PATH, 'PROD_SSH_KNOWN_HOSTS_PATH');

  return [
    '-F', '/dev/null',
    '-o', 'BatchMode=yes',
    '-o', 'StrictHostKeyChecking=yes',
    '-o', `UserKnownHostsFile=${config.PROD_SSH_KNOWN_HOSTS_PATH}`,
    '-o', 'PreferredAuthentications=publickey',
    '-o', 'PasswordAuthentication=no',
    '-o', 'KbdInteractiveAuthentication=no',
    '-o', 'IdentitiesOnly=yes',
    '-o', `IdentityFile=${config.PROD_SSH_KEY_PATH}`,
    '-o', 'ForwardAgent=no',
    '-o', 'ClearAllForwardings=yes',
    '-o', 'PermitLocalCommand=no',
    '-o', 'RequestTTY=no',
    '-p', String(port),
    `${accessConfig.sshUser}@${host}`,
    accessConfig.originalCommand,
  ];
}

export async function validateLocalPreflight({ envPath, scope, commandRunner }) {
  validateDeployScope(scope);
  const envStats = await stat(envPath);
  assert(isPrivateMode(envStats.mode), 'local env file mode must be 0600 or stricter');
  const config = parseLocalEnv(await readFile(envPath, 'utf8'), scope.localAccess);
  assert(config.PROD_SSH_USER === scope.localAccess.sshUser, 'PROD_SSH_USER must be bitrix');
  parsePort(config.PROD_SSH_PORT);
  validateHost(config.PROD_HOST);
  validateWebRoot(config.PROD_WEB_ROOT);

  const keyPath = validateAbsoluteSafePath(config.PROD_SSH_KEY_PATH, 'PROD_SSH_KEY_PATH');
  const publicKeyPath = validateAbsoluteSafePath(config.PROD_SSH_PUBLIC_KEY_PATH, 'PROD_SSH_PUBLIC_KEY_PATH');
  const knownHostsPath = validateAbsoluteSafePath(config.PROD_SSH_KNOWN_HOSTS_PATH, 'PROD_SSH_KNOWN_HOSTS_PATH');
  assert(path.basename(keyPath) === scope.localAccess.dedicatedKeyBasename, 'PROD_SSH_KEY_PATH must select the dedicated local key');
  assert(publicKeyPath === `${keyPath}.pub`, 'PROD_SSH_PUBLIC_KEY_PATH must be the public pair of the dedicated key');

  await assertReadableRegularFile(keyPath, 'dedicated private key');
  await assertReadableRegularFile(publicKeyPath, 'dedicated public key');
  await assertReadableRegularFile(knownHostsPath, 'dedicated known_hosts');
  const privateStats = await stat(keyPath);
  const knownHostsStats = await stat(knownHostsPath);
  assert(isPrivateMode(privateStats.mode), 'dedicated private key mode must be 0600 or stricter');
  assert(isPrivateMode(knownHostsStats.mode), 'dedicated known_hosts mode must be 0600 or stricter');

  const runner = commandRunner || defaultCommandRunner;
  const privateFingerprint = await runner('ssh-keygen', ['-lf', keyPath, '-E', 'sha256']);
  const publicFingerprint = await runner('ssh-keygen', ['-lf', publicKeyPath, '-E', 'sha256']);
  assert(extractFingerprint(privateFingerprint.stdout) === extractFingerprint(publicFingerprint.stdout), 'dedicated private/public fingerprints differ');

  const emptyPassphraseResult = await runner('ssh-keygen', ['-y', '-P', '', '-f', keyPath], { allowFailure: true });
  assert(emptyPassphraseResult.status !== 0, 'dedicated private key must be passphrase protected');

  const knownHostResult = await runner('ssh-keygen', ['-F', config.PROD_HOST, '-f', knownHostsPath], { allowFailure: true });
  assert(knownHostResult.status === 0 && knownHostResult.stdout.trim() !== '', 'dedicated known_hosts does not contain PROD_HOST');

  const agentResult = await runner('ssh-add', ['-l'], { allowFailure: true });
  const fingerprint = extractFingerprint(publicFingerprint.stdout);
  assert(agentResult.status === 0 && agentResult.stdout.includes(fingerprint), 'dedicated key is not loaded in ssh-agent');

  return {
    status: 'ready',
    sshUser: scope.localAccess.sshUser,
    keyFingerprint: fingerprint,
    sshArgs: buildStrictSshArgs(config, scope),
  };
}

export function canonicalStringify(value) {
  return JSON.stringify(sortCanonical(value));
}

export function normalizeRelativePath(value) {
  assertString(value, 'path');
  assert(!value.includes('\0'), 'path contains NUL');
  const slashPath = value.replaceAll('\\', '/').replace(/^\.\//, '');
  assert(!path.posix.isAbsolute(slashPath), `path must be relative: ${value}`);
  const normalized = path.posix.normalize(slashPath);
  assert(normalized !== '.' && normalized !== '', `path must not be empty: ${value}`);
  assert(!normalized.startsWith('../') && normalized !== '..', `path traversal is forbidden: ${value}`);
  assert(normalized === slashPath, `path is not canonical: ${value}`);
  return normalized;
}

function validateRule(rule, label) {
  assert(rule && typeof rule === 'object', `${label} must be an object`);
  assertString(rule.reason, `${label} reason`);
  if (rule.kind === 'exact') {
    assertSafeRelativePath(rule.path, `${label} exact path`);
    return;
  }
  if (rule.kind === 'suffixUnder') {
    assertSafeRelativePath(rule.root, `${label} root`);
    assertString(rule.suffix, `${label} suffix`);
    assert(!rule.suffix.includes('/'), `${label} suffix must not contain slash`);
    return;
  }
  if (rule.kind === 'basenameContainsUnder') {
    assertSafeRelativePath(rule.root, `${label} root`);
    assertString(rule.value, `${label} value`);
    assert(!rule.value.includes('/'), `${label} value must not contain slash`);
    return;
  }
  throw new Error(`unsupported ${label} kind: ${rule.kind}`);
}

function matchesRule(relativePath, rule) {
  if (rule.kind === 'exact') {
    return relativePath === rule.path;
  }
  if (rule.kind === 'suffixUnder') {
    return isWithin(relativePath, rule.root) && path.posix.basename(relativePath).endsWith(rule.suffix);
  }
  if (rule.kind === 'basenameContainsUnder') {
    return isWithin(relativePath, rule.root) && path.posix.basename(relativePath).includes(rule.value);
  }
  return false;
}

function rsyncFilterForRule(rule, directory) {
  if (rule.kind === 'exact') {
    return `/${rule.path.slice(directory.length + 1)}`;
  }
  if (rule.kind === 'suffixUnder') {
    return `*${rule.suffix}`;
  }
  throw new Error(`cannot render rsync filter for rule: ${rule.kind}`);
}

async function walkManagedDirectory(root, directory, scope, entries) {
  const handle = await opendir(directory);
  for await (const dirent of handle) {
    const absolutePath = path.join(directory, dirent.name);
    const relativePath = toRelativePath(root, absolutePath);
    if (scope.exclusions.some((rule) => matchesRule(relativePath, rule))) {
      continue;
    }
    const stats = await lstat(absolutePath);
    if (stats.isDirectory()) {
      await walkManagedDirectory(root, absolutePath, scope, entries);
    } else {
      entries.push(await manifestEntry(root, relativePath, scope, stats));
    }
  }
}

async function manifestEntry(root, relativePath, scope, suppliedStats = null) {
  const normalized = normalizeRelativePath(relativePath);
  const absolutePath = path.join(root, ...normalized.split('/'));
  const stats = suppliedStats || await lstat(absolutePath);
  const mode = (stats.mode & 0o7777).toString(8).padStart(4, '0');
  if (stats.isFile()) {
    const contents = await readFile(absolutePath);
    return {
      path: normalized,
      type: 'file',
      mode,
      size: stats.size,
      sha256: sha256(contents),
    };
  }
  if (stats.isSymbolicLink()) {
    const target = await readlink(absolutePath);
    validateSymlinkTarget(normalized, target, scope);
    return {
      path: normalized,
      type: 'symlink',
      mode,
      size: Buffer.byteLength(target),
      sha256: sha256(target),
      linkTarget: target,
    };
  }
  throw new Error(`special or unsupported file type in deploy scope: ${normalized}`);
}

function validateSymlinkTarget(relativePath, target, scope = null) {
  assertString(target, `symlink target for ${relativePath}`);
  assert(!target.includes('\0'), `symlink target contains NUL: ${relativePath}`);
  assert(!path.posix.isAbsolute(target), `absolute symlink target is forbidden: ${relativePath}`);
  const resolved = path.posix.normalize(path.posix.join(path.posix.dirname(relativePath), target));
  assert(resolved !== '..' && !resolved.startsWith('../'), `escaping symlink target is forbidden: ${relativePath}`);
  if (scope) {
    const targetKind = classifyScopePath(resolved, scope);
    assert(
      targetKind === 'authoritative' || targetKind === 'exact' || targetKind === 'generated',
      `symlink target is outside managed deploy scope: ${relativePath}`,
    );
  }
}

function compareWithoutBase(prodEntry, candidateEntry) {
  if (entriesEqual(prodEntry, candidateEntry)) {
    return 'UNCHANGED';
  }
  if (!prodEntry && candidateEntry) {
    return 'PROD_MISSING';
  }
  if (prodEntry && !candidateEntry) {
    return 'PROD_ONLY';
  }
  return 'DIFFERENT_UNRESOLVED';
}

function compareWithBase(baseEntry, prodEntry, candidateEntry) {
  if (entriesEqual(baseEntry, prodEntry) && entriesEqual(prodEntry, candidateEntry)) {
    return 'UNCHANGED';
  }
  if (entriesEqual(prodEntry, candidateEntry)) {
    return 'CONVERGED';
  }
  if (entriesEqual(baseEntry, prodEntry)) {
    return 'LOCAL_CHANGE';
  }
  if (entriesEqual(baseEntry, candidateEntry)) {
    if (!prodEntry && candidateEntry) {
      return 'PROD_MISSING';
    }
    if (!baseEntry && prodEntry) {
      return 'PROD_ONLY';
    }
    return 'PROD_CHANGE';
  }
  if (!baseEntry && prodEntry && !candidateEntry) {
    return 'PROD_ONLY';
  }
  return 'CONFLICT';
}

function safeEntrySummary(entry) {
  if (!entry) {
    return null;
  }
  return {
    type: entry.type,
    mode: entry.mode,
    size: entry.size,
    sha256: entry.sha256,
  };
}

function validateManifest(manifest, scope) {
  assert(manifest && typeof manifest === 'object', 'manifest must be an object');
  assert(manifest.schemaVersion === MANIFEST_SCHEMA_VERSION, 'unsupported manifest schemaVersion');
  assert(manifest.scopeVersion === scope.scopeVersion, 'manifest scopeVersion mismatch');
  assert(['candidate', 'prod', 'base', 'fixture'].includes(manifest.treeKind), 'unsupported manifest treeKind');
  assert(Array.isArray(manifest.entries), 'manifest entries must be an array');
  assert(Array.isArray(manifest.tombstones), 'manifest tombstones must be an array');
  assert(
    canonicalStringify(manifest.tombstones) === canonicalStringify([...scope.tombstones].sort(compareCodePoints)),
    'manifest tombstones do not match deploy scope',
  );
  assertString(manifest.manifestSha256, 'manifestSha256');
  let previousPath = null;
  for (const entry of manifest.entries) {
    validateManifestEntry(entry, scope);
    if (previousPath !== null) {
      assert(compareCodePoints(previousPath, entry.path) < 0, 'manifest entries must be unique and sorted by path');
    }
    previousPath = entry.path;
  }
  const { manifestSha256, ...payload } = manifest;
  assert(sha256(canonicalStringify(payload)) === manifestSha256, 'manifestSha256 does not match canonical payload');
}

function validateManifestEntry(entry, scope) {
  assert(entry && typeof entry === 'object', 'manifest entry must be an object');
  normalizeRelativePath(entry.path);
  assert(entry.type === 'file' || entry.type === 'symlink', `unsupported manifest entry type: ${entry.type}`);
  assert(/^0[0-7]{3}$/.test(entry.mode), `invalid manifest mode for ${entry.path}`);
  assert(Number.isSafeInteger(entry.size) && entry.size >= 0, `invalid manifest size for ${entry.path}`);
  assert(/^[a-f0-9]{64}$/.test(entry.sha256), `invalid manifest SHA-256 for ${entry.path}`);
  if (entry.type === 'symlink') {
    assertString(entry.linkTarget, `linkTarget for ${entry.path}`);
    validateSymlinkTarget(entry.path, entry.linkTarget, scope);
  }
}

function entryMap(manifest) {
  const result = new Map();
  for (const entry of manifest.entries) {
    const normalized = normalizeRelativePath(entry.path);
    assert(!result.has(normalized), `duplicate manifest path: ${normalized}`);
    result.set(normalized, entry);
  }
  return result;
}

function entriesEqual(left, right) {
  if (left === null || left === undefined || right === null || right === undefined) {
    return !left && !right;
  }
  return canonicalStringify(safeEntrySummary(left)) === canonicalStringify(safeEntrySummary(right));
}

function parseLiteralEnvValue(rawValue, key, errors) {
  let value = rawValue.trim();
  if ((value.startsWith('"') && value.endsWith('"')) || (value.startsWith("'") && value.endsWith("'"))) {
    value = value.slice(1, -1);
  } else if (value.includes('#')) {
    errors.push(`${key} must not contain inline comments`);
    return null;
  }
  if (/\$\(|\$\{|`/.test(value)) {
    errors.push(`${key} contains forbidden shell expansion syntax`);
    return null;
  }
  if (/\r|\n/.test(value)) {
    errors.push(`${key} contains a newline`);
    return null;
  }
  return value;
}

function validateHost(value) {
  assert(/^[A-Za-z0-9.-]+$/.test(value), 'PROD_HOST contains unsupported characters');
  assert(!value.startsWith('.') && !value.endsWith('.'), 'PROD_HOST is invalid');
  return value;
}

function parsePort(value) {
  assert(/^\d{1,5}$/.test(String(value)), 'PROD_SSH_PORT must be numeric');
  const port = Number(value);
  assert(Number.isInteger(port) && port >= 1 && port <= 65535, 'PROD_SSH_PORT is out of range');
  return port;
}

function validateWebRoot(value) {
  const normalized = validateAbsoluteSafePath(value, 'PROD_WEB_ROOT');
  assert(normalized !== '/', 'PROD_WEB_ROOT must not be filesystem root');
  return normalized;
}

function validateAbsoluteSafePath(value, label) {
  assertString(value, label);
  assert(path.isAbsolute(value), `${label} must be absolute`);
  assert(!value.includes('\0'), `${label} contains NUL`);
  const normalized = path.normalize(value);
  assert(normalized === value, `${label} must be canonical`);
  assert(!value.split(path.sep).includes('..'), `${label} contains parent traversal`);
  return normalized;
}

function extractFingerprint(output) {
  const fingerprint = String(output).trim().split(/\s+/)[1];
  assert(/^SHA256:[A-Za-z0-9+/]+$/.test(fingerprint || ''), 'unable to parse SSH key fingerprint');
  return fingerprint;
}

async function defaultCommandRunner(command, args, options = {}) {
  const { spawn } = await import('node:child_process');
  return new Promise((resolve, reject) => {
    const child = spawn(command, args, {
      env: { PATH: process.env.PATH || '' },
      shell: false,
      stdio: ['ignore', 'pipe', 'pipe'],
    });
    let stdout = '';
    let stderr = '';
    child.stdout.setEncoding('utf8');
    child.stderr.setEncoding('utf8');
    child.stdout.on('data', (chunk) => { stdout += chunk; });
    child.stderr.on('data', (chunk) => { stderr += chunk; });
    child.on('error', reject);
    child.on('close', (status) => {
      const result = { status: status ?? 1, stdout, stderr };
      if (result.status !== 0 && !options.allowFailure) {
        reject(new Error(`${command} failed with status ${result.status}`));
      } else {
        resolve(result);
      }
    });
  });
}

async function assertDirectory(absolutePath, label) {
  const stats = await lstat(absolutePath);
  assert(stats.isDirectory() && !stats.isSymbolicLink(), `${label} must be a real directory`);
}

async function assertReadableRegularFile(absolutePath, label) {
  const stats = await lstat(absolutePath);
  assert(stats.isFile() && !stats.isSymbolicLink(), `${label} must be a regular file`);
  await access(absolutePath, fsConstants.R_OK);
}

async function pathExists(absolutePath) {
  try {
    await lstat(absolutePath);
    return true;
  } catch (error) {
    if (error?.code === 'ENOENT') {
      return false;
    }
    throw error;
  }
}

function isPrivateMode(mode) {
  return (mode & 0o077) === 0;
}

function isWithin(relativePath, root) {
  return relativePath === root || relativePath.startsWith(`${root}/`);
}

function toRelativePath(root, absolutePath) {
  return normalizeRelativePath(path.relative(root, absolutePath).split(path.sep).join('/'));
}

function assertSafeRelativePath(value, label) {
  try {
    normalizeRelativePath(value);
  } catch (error) {
    throw new Error(`${label}: ${error.message}`);
  }
}

function assertUnique(values, label) {
  const seen = new Set();
  for (const value of values) {
    assert(!seen.has(value), `${label} contains duplicate: ${value}`);
    seen.add(value);
  }
}

function ruleLabel(rule) {
  return rule.path || `${rule.root}:${rule.suffix || rule.value}`;
}

function compareCodePoints(left, right) {
  if (left === right) {
    return 0;
  }
  return left < right ? -1 : 1;
}

function assertString(value, label) {
  assert(typeof value === 'string' && value.length > 0, `${label} must be a non-empty string`);
}

function assert(condition, message) {
  if (!condition) {
    throw new Error(message);
  }
}

function sha256(value) {
  return createHash('sha256').update(value).digest('hex');
}

function sortCanonical(value) {
  if (Array.isArray(value)) {
    return value.map(sortCanonical);
  }
  if (value && typeof value === 'object') {
    return Object.fromEntries(
      Object.keys(value)
        .sort()
        .map((key) => [key, sortCanonical(value[key])]),
    );
  }
  return value;
}

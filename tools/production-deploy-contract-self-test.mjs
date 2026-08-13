#!/usr/bin/env node

import { spawnSync } from 'node:child_process';
import { createHash } from 'node:crypto';
import { tmpdir } from 'node:os';
import path from 'node:path';
import {
  chmod,
  mkdir,
  mkdtemp,
  rm,
  symlink,
  unlink,
  writeFile,
} from 'node:fs/promises';
import {
  buildCanonicalManifest,
  buildRsyncContract,
  buildStrictSshArgs,
  canonicalStringify,
  classifyChangedPaths,
  compareManifests,
  loadDeployScope,
  parseLocalEnv,
  validateDeployScope,
  validateLocalPreflight,
} from './production-deploy-contract.mjs';

const scope = await loadDeployScope();
const fixtureRoot = await mkdtemp(path.join(tmpdir(), 'tacticum-prod-contract-'));

try {
  await testScopeValidation(scope);
  await testClassification(scope);
  testRsyncContract(scope);
  await testManifest(scope, fixtureRoot);
  await testPlan(scope);
  await testLocalPreflight(scope, fixtureRoot);
  console.log('Production deploy contract self-test passed.');
} finally {
  await rm(fixtureRoot, { recursive: true, force: true });
}

async function testScopeValidation(actualScope) {
  validateDeployScope(actualScope);
  const duplicate = structuredClone(actualScope);
  duplicate.exactRootFiles.push(duplicate.exactRootFiles[0]);
  await expectFailure(() => validateDeployScope(duplicate), 'managed deploy paths contains duplicate');

  const escaping = structuredClone(actualScope);
  escaping.tombstones = ['../unsafe'];
  await expectFailure(() => validateDeployScope(escaping), 'path traversal is forbidden');
}

async function testClassification(actualScope) {
  const fileOnly = classifyChangedPaths(['local/templates/tacticum/header.php'], actualScope);
  assertEqual(fileOnly.releaseClass, 'FILE_ONLY');
  assertEqual(fileOnly.assurance, 'PATH_SCOPE_ONLY_REQUIRES_DATA_LIFECYCLE_REVIEW');
  assertEqual(fileOnly.productionMutationAllowed, false);
  assertEqual(classifyChangedPaths(['docs/workflow/README.md'], actualScope).releaseClass, 'NO_DEPLOYABLE_CHANGE');
  assertEqual(classifyChangedPaths(['AGENTS.md'], actualScope).releaseClass, 'NO_DEPLOYABLE_CHANGE');
  assertEqual(classifyChangedPaths(['.github/agents/devops.md'], actualScope).releaseClass, 'NO_DEPLOYABLE_CHANGE');
  assertEqual(classifyChangedPaths(['bitrix/modules/main/file.php'], actualScope).releaseClass, 'BLOCKED_FORBIDDEN');
  assertEqual(classifyChangedPaths(['local/php_interface/include/tacticum_config.php'], actualScope).releaseClass, 'BLOCKED_FORBIDDEN');
  assertEqual(classifyChangedPaths(['local/php_interface/include/tacticum_config.example.php'], actualScope).releaseClass, 'STATEFUL');
  assertEqual(classifyChangedPaths(['tools/product-content-migration.php'], actualScope).releaseClass, 'STATEFUL');
  assertEqual(classifyChangedPaths(['.github/workflows/deploy.yml'], actualScope).releaseClass, 'CONTROL_PLANE_REVIEW_REQUIRED');
  assertEqual(classifyChangedPaths(['unmanaged-root.php'], actualScope).releaseClass, 'BLOCKED_UNKNOWN');
}

function testRsyncContract(actualScope) {
  const contract = buildRsyncContract(actualScope);
  assertEqual(contract.authoritativeDirectories.length, actualScope.authoritativeDirectories.length);
  for (const required of [
    '--archive',
    '--no-owner',
    '--no-group',
    '--checksum',
    '--delay-updates',
    '--delete-delay',
  ]) {
    assert(contract.baseOptions.includes(required), `rsync contract is missing ${required}`);
  }
  const local = contract.authoritativeDirectories.find((operation) => operation.path === 'local');
  assert(local.options.includes('--delete'), 'authoritative directory must enable delete');
  assert(local.filters.includes('/php_interface/include/tacticum_config.php'), 'local config exclusion is missing');
  assert(local.filters.includes('*.log'), 'local log exclusion is missing');
  assertEqual(contract.exactFiles.delete, false);
  assert(contract.exactFiles.paths.includes('sitemap-basic-files.xml'), 'generated file is missing from exact operation');
  assert(contract.tombstones.paths.includes('sitemap-files.xml'), 'explicit tombstone is missing');
}

async function testManifest(actualScope, root) {
  for (const directory of actualScope.authoritativeDirectories) {
    await mkdir(path.join(root, directory), { recursive: true });
  }
  await mkdir(path.join(root, 'local/php_interface/include'), { recursive: true });
  await writeFile(path.join(root, 'local/tracked.php'), '<?php echo "fixture";\n');
  await writeFile(path.join(root, 'local/runtime.log'), 'must be excluded\n');
  await writeFile(path.join(root, 'local/php_interface/include/tacticum_config.php'), 'must be excluded\n');
  await writeFile(path.join(root, 'unknown-root.txt'), 'must not be included\n');
  for (const file of actualScope.exactRootFiles) {
    await writeFile(path.join(root, file), `exact:${file}\n`);
  }
  for (const item of actualScope.generatedFiles) {
    await writeFile(path.join(root, item.path), `generated:${item.path}\n`);
  }
  await symlink('tracked.php', path.join(root, 'local/safe-link.php'));

  const first = await buildCanonicalManifest(root, actualScope);
  const second = await buildCanonicalManifest(root, actualScope);
  assertEqual(first.manifestSha256, second.manifestSha256);
  assert(first.entries.some((entry) => entry.path === 'local/tracked.php'), 'tracked file is missing');
  assert(first.entries.some((entry) => entry.path === 'local/safe-link.php' && entry.type === 'symlink'), 'safe symlink is missing');
  assert(!first.entries.some((entry) => entry.path.endsWith('.log')), 'runtime log leaked into manifest');
  assert(!first.entries.some((entry) => entry.path.endsWith('tacticum_config.php')), 'server config leaked into manifest');
  assert(!first.entries.some((entry) => entry.path === 'unknown-root.txt'), 'unknown root file leaked into manifest');

  const tombstone = actualScope.tombstones[0];
  await writeFile(path.join(root, tombstone), 'legacy\n');
  await expectFailure(
    () => buildCanonicalManifest(root, actualScope),
    'tombstone target must be absent from candidate artifact',
  );
  await unlink(path.join(root, tombstone));

  await symlink('../../outside', path.join(root, 'local/escaping-link'));
  await expectFailure(
    () => buildCanonicalManifest(root, actualScope),
    'escaping symlink target is forbidden',
  );
  await unlink(path.join(root, 'local/escaping-link'));

  await symlink('../bitrix', path.join(root, 'local/forbidden-scope-link'));
  await expectFailure(
    () => buildCanonicalManifest(root, actualScope),
    'symlink target is outside managed deploy scope',
  );
  await unlink(path.join(root, 'local/forbidden-scope-link'));

  const fifoPath = path.join(root, 'local/special-fifo');
  const fifoResult = spawnSync('mkfifo', [fifoPath], { shell: false });
  if (fifoResult.status === 0) {
    await expectFailure(
      () => buildCanonicalManifest(root, actualScope),
      'special or unsupported file type',
    );
    await unlink(fifoPath);
  }
}

async function testPlan(actualScope) {
  const base = makeManifest(actualScope, [entry('index.php', 'base')]);
  const prod = makeManifest(actualScope, [entry('index.php', 'prod')]);
  const candidate = makeManifest(actualScope, [entry('index.php', 'candidate')]);
  const threeWay = compareManifests({ base, prod, candidate, scope: actualScope });
  assertEqual(threeWay.comparisonMode, 'THREE_WAY');
  assertEqual(threeWay.planKind, 'DRIFT_COMPARISON_ONLY');
  assertEqual(threeWay.productionMutationAllowed, false);
  assertEqual(threeWay.changes[0].status, 'CONFLICT');
  assert(/^[a-f0-9]{64}$/.test(threeWay.planId), 'planId must be SHA-256');
  assert(!JSON.stringify(threeWay).includes('fixture contents'), 'plan leaked raw content');

  const twoWay = compareManifests({ base: null, prod, candidate, scope: actualScope });
  assertEqual(twoWay.comparisonMode, 'BASE_UNKNOWN_TWO_WAY');
  assertEqual(twoWay.changes[0].status, 'DIFFERENT_UNRESOLVED');

  const twoWayEqual = compareManifests({ base: null, prod, candidate: prod, scope: actualScope });
  assertEqual(twoWayEqual.changes.length, 0);

  const tampered = structuredClone(candidate);
  tampered.entries[0].sha256 = hash('tampered');
  await expectFailure(
    () => compareManifests({ base: null, prod, candidate: tampered, scope: actualScope }),
    'manifestSha256 does not match canonical payload',
  );
}

async function testLocalPreflight(actualScope, root) {
  const accessRoot = path.join(root, 'access');
  await mkdir(accessRoot, { recursive: true });
  const keyPath = path.join(accessRoot, actualScope.localAccess.dedicatedKeyBasename);
  const publicKeyPath = `${keyPath}.pub`;
  const knownHostsPath = path.join(accessRoot, 'known_hosts');
  const envPath = path.join(accessRoot, '.env');
  await writeFile(keyPath, 'encrypted fixture private key\n', { mode: 0o600 });
  await writeFile(publicKeyPath, 'ssh-ed25519 fixture-public-key\n', { mode: 0o600 });
  await writeFile(knownHostsPath, 'prod.example ssh-ed25519 fixture-host-key\n', { mode: 0o600 });
  await writeFile(envPath, validEnv({ keyPath, publicKeyPath, knownHostsPath }), { mode: 0o600 });

  const parsed = parseLocalEnv(await import('node:fs/promises').then(({ readFile }) => readFile(envPath, 'utf8')), actualScope.localAccess);
  const args = buildStrictSshArgs(parsed, actualScope);
  for (const required of [
    '-F',
    '/dev/null',
    'BatchMode=yes',
    'StrictHostKeyChecking=yes',
    'PreferredAuthentications=publickey',
    'PasswordAuthentication=no',
    'KbdInteractiveAuthentication=no',
    'IdentitiesOnly=yes',
    'ForwardAgent=no',
    'ClearAllForwardings=yes',
    'PermitLocalCommand=no',
    'RequestTTY=no',
    'manifest',
  ]) {
    assert(args.includes(required), `strict SSH argv is missing ${required}`);
  }

  const fingerprintLine = '256 SHA256:fixtureFingerprint fixture (ED25519)\n';
  const commandRunner = async (command, commandArgs) => {
    if (command === 'ssh-keygen' && commandArgs[0] === '-lf') {
      return { status: 0, stdout: fingerprintLine, stderr: '' };
    }
    if (command === 'ssh-keygen' && commandArgs[0] === '-y') {
      return { status: 1, stdout: '', stderr: 'encrypted' };
    }
    if (command === 'ssh-keygen' && commandArgs[0] === '-F') {
      return { status: 0, stdout: '# Host prod.example found\n', stderr: '' };
    }
    if (command === 'ssh-add') {
      return { status: 0, stdout: fingerprintLine, stderr: '' };
    }
    throw new Error(`unexpected command in fixture: ${command}`);
  };
  const result = await validateLocalPreflight({ envPath, scope: actualScope, commandRunner });
  assertEqual(result.status, 'ready');
  assertEqual(result.keyFingerprint, 'SHA256:fixtureFingerprint');

  await chmod(envPath, 0o644);
  await expectFailure(
    () => validateLocalPreflight({ envPath, scope: actualScope, commandRunner }),
    'local env file mode must be 0600 or stricter',
  );
  await chmod(envPath, 0o600);

  for (const [name, source, fragment] of [
    ['forbidden password', `${validEnv({ keyPath, publicKeyPath, knownHostsPath })}PROD_ROOT_PASSWORD=secret\n`, 'PROD_ROOT_PASSWORD is forbidden'],
    ['shell expansion', validEnv({ keyPath: '$(touch /tmp/no)', publicKeyPath, knownHostsPath }), 'forbidden shell expansion syntax'],
    ['personal key', validEnv({ keyPath: path.join(accessRoot, 'id_ed25519'), publicKeyPath: path.join(accessRoot, 'id_ed25519.pub'), knownHostsPath }), 'dedicated local key'],
    ['unknown prod key', `${validEnv({ keyPath, publicKeyPath, knownHostsPath })}PROD_SSH_PASSWORD=nope\n`, 'not allowlisted'],
  ]) {
    if (name === 'personal key') {
      await writeFile(path.join(accessRoot, 'id_ed25519'), 'fixture\n', { mode: 0o600 });
      await writeFile(path.join(accessRoot, 'id_ed25519.pub'), 'fixture\n', { mode: 0o600 });
      await writeFile(envPath, source, { mode: 0o600 });
      await expectFailure(
        () => validateLocalPreflight({ envPath, scope: actualScope, commandRunner }),
        fragment,
      );
    } else {
      await expectFailure(() => parseLocalEnv(source, actualScope.localAccess), fragment);
    }
  }
}

function validEnv({ keyPath, publicKeyPath, knownHostsPath }) {
  return [
    'PROD_HOST=prod.example',
    'PROD_WEB_ROOT=/var/www/tacticum',
    'PROD_SSH_USER=bitrix',
    'PROD_SSH_PORT=22',
    `PROD_SSH_KEY_PATH=${keyPath}`,
    `PROD_SSH_PUBLIC_KEY_PATH=${publicKeyPath}`,
    `PROD_SSH_KNOWN_HOSTS_PATH=${knownHostsPath}`,
    'UNRELATED_LOCAL_VALUE=ignored',
    '',
  ].join('\n');
}

function entry(relativePath, marker) {
  return {
    path: relativePath,
    type: 'file',
    mode: '0644',
    size: marker.length,
    sha256: hash(marker),
  };
}

function makeManifest(actualScope, entries) {
  const payload = {
    schemaVersion: 1,
    scopeVersion: actualScope.scopeVersion,
    treeKind: 'fixture',
    entries,
    tombstones: [...actualScope.tombstones],
  };
  return { ...payload, manifestSha256: hash(canonicalStringify(payload)) };
}

function hash(value) {
  return createHash('sha256').update(value).digest('hex');
}

async function expectFailure(callback, fragment) {
  try {
    await callback();
  } catch (error) {
    assert(error.message.includes(fragment), `expected error fragment "${fragment}", got "${error.message}"`);
    return;
  }
  throw new Error(`expected failure containing: ${fragment}`);
}

function assertEqual(actual, expected) {
  assert(actual === expected, `expected ${expected}, got ${actual}`);
}

function assert(condition, message) {
  if (!condition) {
    throw new Error(message);
  }
}

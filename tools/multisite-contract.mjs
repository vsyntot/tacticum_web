import {createHash} from 'node:crypto';
import {lstat, readFile, readdir, readlink} from 'node:fs/promises';
import path from 'node:path';
import {buildCanonicalManifest, canonicalStringify, classifyChangedPaths, classifyScopePath, loadDeployScope,
  normalizeRelativePath} from './production-deploy-contract.mjs';

const hash = value => createHash('sha256').update(value).digest('hex');
const assert = (condition, message) => { if (!condition) throw new Error(message); };
const bound = payload => ({...payload, manifestSha256: hash(canonicalStringify(payload))});
const compare = (a, b) => a < b ? -1 : a > b ? 1 : 0;
export const multisiteScopeDigest = (scope, legacyScope) => hash(canonicalStringify({scope, legacyScope}));

export async function loadMultisiteScope(file = 'tools/multisite-scope.json') {
  const scope = JSON.parse(await readFile(file, 'utf8'));
  assert(scope.schemaVersion === 2, 'unsupported multisite scope');
  assert(Object.keys(scope.roots).sort().join(',') === 'divatech,primary', 'unexpected root registry');
  assert(scope.roots.primary.source === '.' && scope.roots.divatech.source === 'sites/divatech', 'source mapping changed');
  assert(scope.roots.primary.destination === '/home/bitrix/www'
    && scope.roots.divatech.destination === '/home/bitrix/ext_www/divatech.space', 'target mapping changed');
  assert(scope.primaryScope === 'tools/deploy-scope.json', 'untrusted primary scope path');
  assert(scope.sharedPrefix === 'local/', 'shared mapping changed');
  assert(canonicalStringify(scope.divatechRuntimeLinks) === canonicalStringify({
    bitrix: '/home/bitrix/www/bitrix', local: '/home/bitrix/www/local', upload: '/home/bitrix/www/upload',
  }), 'runtime mapping changed');
  return scope;
}

function platformIdentity(lock) {
  assert(lock?.schemaVersion === 1, 'platform lock required');
  for (const name of ['snapshotSha256', 'vendorManifestSha256']) {
    assert(/^[a-f0-9]{64}$/.test(lock[name] || ''), `missing platform ${name}`);
  }
  assert(typeof lock.schemaProfile === 'string' && lock.schemaProfile.length > 0, 'DB schema profile required');
  assert(typeof lock.phpVersion === 'string' && /^8\.\d+\.\d+$/.test(lock.phpVersion), 'PHP version required');
  return {snapshotSha256: lock.snapshotSha256, vendorManifestSha256: lock.vendorManifestSha256,
    schemaProfile: lock.schemaProfile, phpVersion: lock.phpVersion};
}

function allowedSecondary(relative) {
  assert(normalizeRelativePath(relative) === relative, 'noncanonical path');
  const parts = relative.split('/');
  assert(!parts.some(part => ['bitrix', 'local', 'upload', '.git', 'node_modules'].includes(part)), 'shared/runtime path in secondary source');
  assert(!parts.some(part => part === '.env' || part.startsWith('.env.') || part.startsWith('.settings')), 'environment-owned secondary path');
  assert(!/\.(zip|tar|gz|sql|log|bak|pem|key)$/i.test(relative), 'backup/secret/runtime secondary file');
}

function allowedApplicationPath(relative) {
  assert(normalizeRelativePath(relative) === relative, 'noncanonical path');
  assert(!/[\x00-\x1f\x7f]/.test(relative), 'control character in application path');
  const parts = relative.split('/');
  assert(!parts.some(p => ['.git', 'node_modules', 'dbconn.php', 'license_key.php'].includes(p)
    || p === '.env' || p.startsWith('.env.') || p.startsWith('.settings')), 'environment/runtime file in application artifact');
  assert(!/\.(zip|tar|gz|sql|log|bak|pem|key)$/i.test(relative), 'backup/secret/runtime application file');
}

async function secondaryEntries(directory, runtimeLinks = null) {
  assert((await lstat(directory)).isDirectory() && !(await lstat(directory)).isSymbolicLink(), 'secondary root must be a real directory');
  const entries = [];
  async function walk(relative = '') {
    for (const name of (await readdir(path.join(directory, relative))).sort()) {
      const rel = relative ? `${relative}/${name}` : name;
      const file = path.join(directory, rel), st = await lstat(file);
      if (!relative && runtimeLinks && Object.hasOwn(runtimeLinks, name)) {
        assert(st.isSymbolicLink() && await readlink(file) === runtimeLinks[name], 'secondary runtime link drift');
        continue;
      }
      allowedSecondary(rel);
      assert(!st.isSymbolicLink(), 'secondary source symlink forbidden');
      if (st.isDirectory()) { await walk(rel); continue; }
      assert(st.isFile(), 'secondary special file forbidden');
      const mode = st.mode & 0o7777;
      assert(!(mode & 0o7000), 'unsafe secondary file mode');
      const data = await readFile(file);
      entries.push({root: 'divatech', path: rel, type: 'file', mode: mode.toString(8).padStart(4, '0'), size: data.length, sha256: hash(data)});
    }
  }
  await walk();
  if (runtimeLinks) {
    for (const name of Object.keys(runtimeLinks)) {
      assert((await lstat(path.join(directory, name))).isSymbolicLink(), 'required runtime link missing');
    }
  }
  return entries;
}

export async function buildMultisiteManifest({root = '.', scope, platform, treeKind = 'candidate', productionRoots = null}) {
  const identity = platformIdentity(platform);
  assert(['candidate', 'prod'].includes(treeKind), 'unexpected tree kind');
  assert((treeKind === 'prod') === Boolean(productionRoots), 'explicit production roots required');
  const legacyScope = await loadDeployScope(path.join(root, scope.primaryScope));
  const primaryRoot = productionRoots?.primary || root;
  const secondRoot = productionRoots?.divatech || path.join(root, scope.roots.divatech.source);
  const primary = await buildCanonicalManifest(primaryRoot, legacyScope, {treeKind});
  assert(primary.entries.every(e => e.type === 'file'), 'application artifacts accept regular files only');
  const entries = [...primary.entries.map(entry => ({root: 'primary', ...entry})),
    ...await secondaryEntries(secondRoot, productionRoots ? scope.divatechRuntimeLinks : null)];
  entries.sort((a, b) => compare(`${a.root}/${a.path}`, `${b.root}/${b.path}`));
  const manifest = bound({schemaVersion: 2, scopeVersion: scope.scopeVersion,
    scopeSha256: multisiteScopeDigest(scope, legacyScope), treeKind, platform: identity, entries,
    tombstones: primary.tombstones.map(file => ({root: 'primary', path: file}))});
  return validateMultisiteManifest(manifest, {scope, legacyScope});
}

export function validateMultisiteManifest(manifest, {scope, legacyScope}) {
  const {manifestSha256, ...payload} = manifest;
  assert(manifestSha256 === hash(canonicalStringify(payload)), 'multisite manifest integrity');
  assert(manifest.schemaVersion === 2, 'multisite schema mismatch');
  assert(['candidate', 'prod'].includes(manifest.treeKind), 'invalid tree kind');
  assert(manifest.scopeVersion === scope.scopeVersion
    && manifest.scopeSha256 === multisiteScopeDigest(scope, legacyScope), 'scope identity mismatch');
  platformIdentity({schemaVersion: 1, ...manifest.platform});
  assert(Array.isArray(manifest.entries) && Array.isArray(manifest.tombstones), 'missing inventory');
  assert(canonicalStringify(manifest.tombstones) === canonicalStringify(
    [...legacyScope.tombstones].sort().map(file => ({root: 'primary', path: file}))), 'tombstone policy mismatch');
  const seen = new Set();
  let previous = '';
  for (const entry of manifest.entries) {
    assert(['primary', 'divatech'].includes(entry.root), 'unknown root');
    allowedApplicationPath(entry.path);
    if (entry.root === 'divatech') allowedSecondary(entry.path);
    else assert(['authoritative', 'exact', 'generated'].includes(classifyScopePath(entry.path, legacyScope)), 'primary path outside artifact scope');
    const key = `${entry.root}/${entry.path}`;
    assert(!seen.has(key), 'duplicate target'); seen.add(key);
    assert(compare(previous, key) < 0, 'noncanonical entry order'); previous = key;
    assert(entry.type === 'file' && /^[a-f0-9]{64}$/.test(entry.sha256), 'invalid file entry');
    assert(Number.isSafeInteger(entry.size) && entry.size >= 0 && /^0[0-7]{3}$/.test(entry.mode), 'invalid file metadata');
  }
  return manifest;
}

export function compareMultisite({base, prod, candidate, scope, legacyScope}) {
  [base, prod, candidate].forEach(m => validateMultisiteManifest(m, {scope, legacyScope}));
  assert(base.treeKind === 'prod' && prod.treeKind === 'prod' && candidate.treeKind === 'candidate', 'wrong manifest role');
  assert(base.scopeVersion === prod.scopeVersion && prod.scopeVersion === candidate.scopeVersion, 'scope migration requires bootstrap');
  assert(canonicalStringify(prod.platform) === canonicalStringify(candidate.platform), 'production platform differs from tested candidate');
  assert(canonicalStringify(base.platform) === canonicalStringify(prod.platform), 'platform update requires verified baseline adoption');
  const maps = [base, prod, candidate].map(m => new Map(m.entries.map(e => [`${e.root}/${e.path}`, e])));
  const keys = [...new Set(maps.flatMap(m => [...m.keys()]))].sort();
  const changes = [];
  const eq = (a, b) => canonicalStringify(a ?? null) === canonicalStringify(b ?? null);
  for (const key of keys) {
    const [b, p, c] = maps.map(m => m.get(key));
    if (eq(b, p) && eq(p, c)) continue;
    const status = eq(p, c) ? 'CONVERGED' : eq(b, p) ? 'LOCAL_CHANGE' : eq(b, c) ? 'PROD_CHANGE' : 'CONFLICT';
    const entry = c || p || b;
    changes.push({root: entry.root, path: entry.path, status,
      operation: status === 'CONVERGED' ? 'none' : !p ? 'create' : !c ? 'delete' : 'replace', before: p || null, after: c || null});
  }
  const allowed = changes.every(c => ['LOCAL_CHANGE', 'CONVERGED'].includes(c.status));
  const local = changes.filter(c => c.status === 'LOCAL_CHANGE');
  const affected = new Set();
  for (const c of changes) {
    if (c.root === 'divatech') affected.add('s2');
    else if (c.path.startsWith('local/')) { affected.add('s1'); affected.add('s2'); }
    else affected.add('s1');
  }
  const payload = {schemaVersion: 2, planKind: 'DRIFT_COMPARISON_ONLY', productionMutationAllowed: false,
    baseManifestSha256: base.manifestSha256, prodManifestSha256: prod.manifestSha256,
    candidateManifestSha256: candidate.manifestSha256, changes, affectedSites: [...affected].sort(),
    driftResolved: allowed, deployRequired: allowed && local.length > 0};
  return {...payload, planId: hash(canonicalStringify(payload))};
}

export function classifyMultisitePaths(paths, scope, legacyScope) {
  const primary = [], secondary = [], stateful = [];
  for (const item of paths) {
    const normalized = normalizeRelativePath(item);
    if (!normalized.startsWith('sites/divatech/')) {
      if (['authoritative', 'exact', 'generated'].includes(classifyScopePath(normalized, legacyScope))) {
        try { allowedApplicationPath(normalized); }
        catch { return {releaseClass: 'BLOCKED_FORBIDDEN', productionMutationAllowed: false}; }
      }
      primary.push(normalized); continue;
    }
    const relative = normalized.slice('sites/divatech/'.length);
    try { allowedSecondary(relative); allowedApplicationPath(relative); }
    catch { return {releaseClass: 'BLOCKED_FORBIDDEN', productionMutationAllowed: false}; }
    (scope.statefulSiteFiles.includes(relative) ? stateful : secondary).push(normalized);
  }
  const result = classifyChangedPaths(primary, legacyScope);
  result.secondaryPaths = secondary;
  result.statefulPaths.push(...stateful);
  if (result.releaseClass === 'BLOCKED_FORBIDDEN') return result;
  if (stateful.length) result.releaseClass = 'STATEFUL';
  else if (secondary.length && result.releaseClass === 'NO_DEPLOYABLE_CHANGE') result.releaseClass = 'FILE_ONLY';
  return result;
}

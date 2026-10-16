import assert from 'node:assert/strict';
import {createHash} from 'node:crypto';
import {canonicalStringify, loadDeployScope} from './production-deploy-contract.mjs';
import {loadMultisiteScope} from './multisite-contract.mjs';
import {reviewMultisite, validateReviewInput} from './multisite-review.mjs';
import {multisiteScopeDigest} from './multisite-contract.mjs';
import {mkdtemp, writeFile, readFile, rm, access} from 'node:fs/promises';
import {execFileSync, spawnSync} from 'node:child_process';
import {tmpdir} from 'node:os';
import path from 'node:path';

const scope = await loadMultisiteScope(), legacyScope = await loadDeployScope();
const contracts = {scope, legacyScope};
const seal = manifest => {
  const {manifestSha256, ...payload} = manifest;
  return {...payload, manifestSha256: createHash('sha256').update(canonicalStringify(payload)).digest('hex')};
};
const entry = (root, path, digit = '1') => ({root, path, type: 'file', mode: '0644', size: 1, sha256: digit.repeat(64)});
const prod = seal({schemaVersion: 2, scopeVersion: scope.scopeVersion,
  scopeSha256: multisiteScopeDigest(scope, legacyScope), treeKind: 'prod',
  platform: {snapshotSha256: '1'.repeat(64), vendorManifestSha256: '2'.repeat(64), schemaProfile: 'fixture', phpVersion: '8.5.11'},
  entries: [entry('divatech', 'index.php'), entry('primary', 'index.php')],
  tombstones: legacyScope.tombstones.map(path => ({root: 'primary', path}))});
const candidate = seal({...prod, treeKind: 'candidate'});
const review = (p = prod, c = candidate) => reviewMultisite({prod: p, candidate: c,
  commit: 'a'.repeat(40), artifactSha256: 'b'.repeat(64), ...contracts});
const unchanged = review();
assert.equal(unchanged.contentMatches, true);
assert.equal(unchanged.productionMutationAllowed, false);
assert.equal(unchanged.baselinePromotionAllowed, false);
assert.equal(unchanged.baseStatus, 'NOT_LOADED');
assert.equal(unchanged.applicationPathClassification.releaseClass, 'NO_DEPLOYABLE_CHANGE');
assert.equal(unchanged.disposition, 'content_matches_base_review_still_required');

const next = seal({...candidate, entries: [entry('divatech', 'index.php', '2'),
  entry('divatech', 'privacy/index.php'), entry('primary', 'local/shared.php')]});
const changed = review(prod, next);
assert.deepEqual(changed.changes.map(c => c.difference), ['different', 'candidate_only', 'production_only', 'candidate_only']);
assert.deepEqual(changed.affectedSites, ['s1', 's2']);
assert.equal(changed.productionMutationAllowed, false);
assert.equal(changed.changes.find(c => c.path === 'privacy/index.php').root, 'divatech');
assert.equal(changed.applicationPathClassification.releaseClass, 'FILE_ONLY');
const stateful = seal({...candidate, entries: [entry('divatech', '.access.php'), ...candidate.entries]});
assert.equal(review(prod, stateful).applicationPathClassification.releaseClass, 'STATEFUL');

assert.throws(() => review({...prod, manifestSha256: '0'.repeat(64)}), /integrity/);
assert.throws(() => review(seal({...prod, treeKind: 'candidate'})), /role/);
assert.throws(() => review(prod, seal({...candidate, platform: {...candidate.platform, snapshotSha256: '3'.repeat(64)}})), /platform/);
assert.throws(() => review(seal({...prod, entries: [entry('primary', 'index.php')]})), /missing divatech/);
assert.throws(() => validateReviewInput(seal({...prod, scopeVersion: 'old'}), 'prod', contracts), /scope/);
assert.throws(() => review(prod, seal({...candidate, entries: [entry('divatech', '../escape'), entry('primary', 'index.php')]})));
assert.throws(() => reviewMultisite({prod, candidate, commit: 'HEAD', artifactSha256: 'b'.repeat(64), ...contracts}), /commit/);
const temporary = await mkdtemp(path.join(tmpdir(), 'multisite-review-'));
try {
  const file = name => path.join(temporary, name);
  await writeFile(file('prod.json'), JSON.stringify(prod));
  await writeFile(file('candidate.json'), JSON.stringify(next));
  await writeFile(file('artifact'), 'fixture-only-artifact-digest-input');
  const command = ['tools/multisite-review.mjs', 'compare', '--prod', file('prod.json'),
    '--candidate', file('candidate.json'), '--artifact', file('artifact'), '--commit', 'a'.repeat(40),
    '--output', file('review.json')];
  execFileSync(process.execPath, command);
  const saved = JSON.parse(await readFile(file('review.json'), 'utf8'));
  assert.equal(saved.artifactSha256, createHash('sha256').update(await readFile(file('artifact'))).digest('hex'));
  assert.equal(saved.productionMutationAllowed, false);
  assert.notEqual(spawnSync(process.execPath, command).status, 0, 'immutable review cannot be overwritten');
  await writeFile(file('prod.json'), JSON.stringify({...prod, manifestSha256: '0'.repeat(64)}));
  assert.notEqual(spawnSync(process.execPath, ['tools/multisite-review.mjs', 'platform',
    '--prod', file('prod.json'), '--output', file('platform.json')]).status, 0);
  await assert.rejects(access(file('platform.json')));
} finally {
  await rm(temporary, {recursive: true, force: true});
}
console.log('Multisite review: two roots, privacy, shared impact, stateful paths, tampering and no-approval boundaries passed.');

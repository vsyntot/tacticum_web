#!/usr/bin/env node
// Two-way inspection only. Never synthesizes a BASE or a deployment approval.
import assert from 'node:assert/strict';
import {createHash} from 'node:crypto';
import {readFile, writeFile} from 'node:fs/promises';
import {parseArgs} from 'node:util';
import {pathToFileURL} from 'node:url';
import {canonicalStringify, loadDeployScope} from './production-deploy-contract.mjs';
import {loadMultisiteScope, validateMultisiteManifest, classifyMultisitePaths} from './multisite-contract.mjs';

export function validateReviewInput(manifest, kind, contracts) {
  validateMultisiteManifest(manifest, contracts);
  assert.equal(manifest.treeKind, kind, 'wrong manifest role');
  for (const root of ['primary', 'divatech']) {
    assert(manifest.entries.some(entry => entry.root === root), `missing ${root} root`);
  }
  return manifest;
}

export function reviewMultisite({prod, candidate, commit, artifactSha256, ...contracts}) {
  validateReviewInput(prod, 'prod', contracts);
  validateReviewInput(candidate, 'candidate', contracts);
  assert(/^[a-f0-9]{40}$/.test(commit || ''), 'exact source commit required');
  assert(/^[a-f0-9]{64}$/.test(artifactSha256 || ''), 'artifact digest required');
  assert.equal(canonicalStringify(prod.platform), canonicalStringify(candidate.platform), 'platform identity mismatch');
  const maps = [prod, candidate].map(m => new Map(m.entries.map(e => [`${e.root}/${e.path}`, e])));
  const changes = [];
  const affected = new Set();
  for (const key of [...new Set(maps.flatMap(m => [...m.keys()]))].sort()) {
    const [before, after] = maps.map(m => m.get(key));
    if (canonicalStringify(before ?? null) === canonicalStringify(after ?? null)) continue;
    const {root, path} = after || before;
    changes.push({root, path, difference: !before ? 'candidate_only' : !after ? 'production_only' : 'different',
      before: before || null, after: after || null});
    if (root === 'divatech' || path.startsWith('local/')) affected.add('s2');
    if (root === 'primary') affected.add('s1');
  }
  const paths = changes.map(c => c.root === 'divatech' ? `sites/divatech/${c.path}` : c.path);
  const payload = {
    schemaVersion: 2, reportKind: 'TWO_WAY_REVIEW_ONLY',
    sourceCommit: commit, artifactSha256,
    prodManifestSha256: prod.manifestSha256, candidateManifestSha256: candidate.manifestSha256,
    scopeVersion: prod.scopeVersion, platform: prod.platform,
    platformEvidence: 'server_declared_identity_not_runtime_integration',
    baseStatus: 'NOT_LOADED', productionMutationAllowed: false, baselinePromotionAllowed: false,
    disposition: changes.length ? 'differences_require_three_way_review' : 'content_matches_base_review_still_required',
    contentMatches: changes.length === 0,
    roots: Object.fromEntries(['primary', 'divatech'].map(root => [root, {
      production: prod.entries.filter(e => e.root === root).length,
      candidate: candidate.entries.filter(e => e.root === root).length,
    }])),
    // This is a file-path classification only, not a review of the full Git diff.
    applicationPathClassification: classifyMultisitePaths(paths, contracts.scope, contracts.legacyScope),
    affectedSites: [...affected].sort(), changes,
  };
  return {...payload, reportSha256: createHash('sha256').update(canonicalStringify(payload)).digest('hex')};
}

if (process.argv[1] && import.meta.url === pathToFileURL(process.argv[1]).href) {
  try {
    const {values, positionals} = parseArgs({allowPositionals: true, options: Object.fromEntries(
      ['prod', 'candidate', 'artifact', 'commit', 'output'].map(key => [key, {type: 'string'}]))});
    assert(positionals.length === 1 && ['platform', 'compare'].includes(positionals[0]), 'choose platform or compare');
    assert(values.output && values.prod, '--prod and --output required');
    const scope = await loadMultisiteScope(), legacyScope = await loadDeployScope();
    const contracts = {scope, legacyScope};
    const prod = validateReviewInput(JSON.parse(await readFile(values.prod, 'utf8')), 'prod', contracts);
    let result;
    if (positionals[0] === 'platform') {
      result = {schemaVersion: 1, ...prod.platform};
    } else {
      assert(values.candidate && values.artifact, '--candidate and --artifact required');
      const candidate = JSON.parse(await readFile(values.candidate, 'utf8'));
      const artifactSha256 = createHash('sha256').update(await readFile(values.artifact)).digest('hex');
      result = reviewMultisite({prod, candidate, commit: values.commit, artifactSha256, ...contracts});
    }
    await writeFile(values.output, JSON.stringify(result, null, 2) + '\n', {flag: 'wx', mode: 0o600});
    console.log(JSON.stringify({status: 'review_only', productionMutationAllowed: false,
      ...(result.reportSha256 ? {reportSha256: result.reportSha256, roots: result.roots,
        differences: result.changes.length, disposition: result.disposition} : {platformIdentity: 'server_declared'})}));
  } catch (error) {
    console.error(`Multisite review stopped: ${error.message}`);
    process.exitCode = 1;
  }
}

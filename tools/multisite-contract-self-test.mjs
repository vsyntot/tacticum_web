import assert from 'node:assert/strict';
import {mkdtemp, mkdir, writeFile, readFile, rm, symlink} from 'node:fs/promises';
import {execFileSync} from 'node:child_process';
import {tmpdir} from 'node:os';
import path from 'node:path';
import {buildMultisiteManifest, compareMultisite, loadMultisiteScope, classifyMultisitePaths, validateMultisiteManifest} from './multisite-contract.mjs';
import {loadDeployScope, canonicalStringify} from './production-deploy-contract.mjs';
import {createHash} from 'node:crypto';

const scope = await loadMultisiteScope(), legacy = await loadDeployScope();
const fixture = await mkdtemp(path.join(tmpdir(), 'multisite-contract-'));
const platform = {schemaVersion: 1, snapshotSha256: '1'.repeat(64), vendorManifestSha256: '2'.repeat(64), schemaProfile: 'synthetic', phpVersion: '8.5.11'};
const asProd = m => {
  const copy = structuredClone(m); delete copy.manifestSha256; copy.treeKind = 'prod';
  copy.manifestSha256 = createHash('sha256').update(canonicalStringify(copy)).digest('hex'); return copy;
};
try {
  await mkdir(path.join(fixture, 'tools'));
  await writeFile(path.join(fixture, scope.primaryScope), JSON.stringify(legacy));
  await writeFile(path.join(fixture, 'tools/multisite-scope.json'), JSON.stringify(scope));
  await writeFile(path.join(fixture, 'platform.json'), JSON.stringify(platform));
  for (const d of legacy.authoritativeDirectories) await mkdir(path.join(fixture, d), {recursive: true});
  for (const f of [...legacy.exactRootFiles, ...legacy.generatedFiles.map(e => e.path)]) await writeFile(path.join(fixture, f), 'synthetic');
  const site = path.join(fixture, 'sites/divatech'); await mkdir(site, {recursive: true});
  await writeFile(path.join(site, 'index.php'), '<?php /* before */');
  const build = () => buildMultisiteManifest({root: fixture, scope, platform});
  const base = asProd(await build());
  // Exercise the actual CLI and Python archive writer, not a duplicate implementation.
  const artifact = number => path.join(fixture, `artifact-${number}.tar.gz`);
  for (const number of [1, 2]) execFileSync(process.execPath, ['tools/multisite-cli.mjs', 'build',
    '--root', fixture, '--platform', path.join(fixture, 'platform.json'),
    '--manifest', path.join(fixture, `manifest-${number}.json`), '--output', artifact(number)]);
  assert.deepEqual(await readFile(artifact(1)), await readFile(artifact(2)));
  const members = JSON.parse(execFileSync('python3', ['-c',
    'import tarfile,json,sys; print(json.dumps(tarfile.open(sys.argv[1]).getnames()))', artifact(1)], {encoding:'utf8'}));
  assert(members.includes('primary/index.php') && members.includes('divatech/index.php'));
  assert(!members.some(name => name.includes('/bitrix/') || name.includes('/upload/')));
  const resign = m => {
    const copy = structuredClone(m); delete copy.manifestSha256;
    return {...copy, manifestSha256: createHash('sha256').update(canonicalStringify(copy)).digest('hex')};
  };
  for (const badPath of ['bitrix/.settings.php', 'upload/private.txt', 'local/php_interface/include/tacticum_config.php', 'local//foo.php']) {
    const invalid = structuredClone(base); invalid.entries = [{...base.entries[0],root:'primary',path:badPath}];
    assert.throws(() => validateMultisiteManifest(resign(invalid), {scope,legacyScope:legacy}));
  }
  await writeFile(path.join(site, 'index.php'), '<?php /* after */');
  let candidate = await build();
  let plan = compareMultisite({scope, legacyScope: legacy, base, prod: base, candidate});
  assert.equal(plan.deployRequired, true); assert.deepEqual(plan.affectedSites, ['s2']);
  assert.equal(plan.changes[0].operation, 'replace');
  const productionHotfix = asProd(candidate);
  await writeFile(path.join(site, 'index.php'), '<?php /* competing change */');
  const competing = await build();
  const conflict = compareMultisite({scope, legacyScope: legacy, base, prod: productionHotfix, candidate: competing});
  assert.equal(conflict.changes[0].status, 'CONFLICT');
  assert.equal(conflict.deployRequired, false); assert.equal(conflict.driftResolved, false);
  const unchangedCandidate = structuredClone(base);
  unchangedCandidate.treeKind = 'candidate';
  const serverOnly = compareMultisite({scope, legacyScope: legacy, base, prod: productionHotfix,
    candidate: resign(unchangedCandidate)});
  assert.equal(serverOnly.changes[0].status, 'PROD_CHANGE'); assert.equal(serverOnly.deployRequired, false);
  await writeFile(path.join(site, 'index.php'), '<?php /* after */');
  plan = compareMultisite({scope, legacyScope: legacy, base, prod: asProd(candidate), candidate});
  assert.equal(plan.deployRequired, false); assert.equal(plan.changes[0].status, 'CONVERGED');
  const nextBase = asProd(candidate);
  await writeFile(path.join(fixture, 'local/shared.php'), '<?php /* shared */');
  candidate = await build(); plan = compareMultisite({scope, legacyScope: legacy, base: nextBase, prod: nextBase, candidate});
  assert.equal(plan.changes[0].operation, 'create'); assert.deepEqual(plan.affectedSites, ['s1','s2']);
  await rm(path.join(site, 'index.php'));
  plan = compareMultisite({scope, legacyScope: legacy, base: nextBase, prod: nextBase, candidate: await build()});
  assert(plan.changes.some(c => c.operation === 'delete' && c.root === 'divatech'));
  const badPlatform = structuredClone(platform); badPlatform.snapshotSha256 = '3'.repeat(64);
  const incompatible = await buildMultisiteManifest({root: fixture, scope, platform: badPlatform});
  assert.throws(() => compareMultisite({scope, legacyScope: legacy, base: nextBase, prod: nextBase, candidate: incompatible}), /platform/);
  await symlink('/tmp', path.join(site, 'escape'));
  await assert.rejects(build, /symlink/); await rm(path.join(site, 'escape'));
  await writeFile(path.join(site, '.env'), 'synthetic'); await assert.rejects(build, /environment-owned/);
  assert.equal(classifyMultisitePaths(['sites/divatech/index.php'],scope,legacy).releaseClass, 'FILE_ONLY');
  assert.equal(classifyMultisitePaths(['sites/divatech/.access.php'],scope,legacy).releaseClass, 'STATEFUL');
  assert.equal(classifyMultisitePaths(['sites/divatech/.env'],scope,legacy).releaseClass, 'BLOCKED_FORBIDDEN');
  for (const unsafe of ['local/.env', 'local/private.key', 'local/nested/dbconn.php', 'sites/divatech/file\n.php']) {
    assert.equal(classifyMultisitePaths([unsafe],scope,legacy).releaseClass, 'BLOCKED_FORBIDDEN');
  }
  assert.equal(classifyMultisitePaths(['sites/divatech/index.php','tools/ci/php-check.py'],scope,legacy).releaseClass,
    'CONTROL_PLANE_REVIEW_REQUIRED');
  console.log('Multisite contract: two roots, shared impact, create/delete, no-op, platform and path boundaries passed.');
} finally { await rm(fixture, {recursive:true,force:true}); }

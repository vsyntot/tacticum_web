#!/usr/bin/env node
// Local evidence tooling only. No production transport or mutation command.
import {parseArgs} from 'node:util';
import {readFile, writeFile, unlink} from 'node:fs/promises';
import {spawnSync} from 'node:child_process';
import {fileURLToPath} from 'node:url';
import path from 'node:path';
import {createHash} from 'node:crypto';
import {loadDeployScope} from './production-deploy-contract.mjs';
import {loadMultisiteScope, buildMultisiteManifest, validateMultisiteManifest,
  compareMultisite, classifyMultisitePaths} from './multisite-contract.mjs';

try {
  const {values: args, positionals} = parseArgs({allowPositionals: true, options: Object.fromEntries(
    ['root','platform','output','manifest','base','prod','candidate','paths'].map(key => [key, {type: 'string'}]))});
  const [command] = positionals;
  if (positionals.length !== 1) throw new Error('choose build, plan or classify');
  const root = path.resolve(args.root || '.');
  const scope = await loadMultisiteScope(path.join(root, 'tools/multisite-scope.json'));
  const legacyScope = await loadDeployScope(path.join(root, scope.primaryScope));
  const readJson = async key => {
    if (!args[key]) throw new Error(`--${key} required`);
    return JSON.parse(await readFile(args[key], 'utf8'));
  };
  let result;
  if (command === 'build') {
    if (!args.manifest || !args.output) throw new Error('--manifest and --output required; both must be new files');
    const manifest = await buildMultisiteManifest({root, scope, platform: await readJson('platform')});
    validateMultisiteManifest(manifest, {scope, legacyScope});
    await writeFile(args.manifest, JSON.stringify(manifest, null, 2) + '\n', {flag: 'wx', mode: 0o600});
    const builder = fileURLToPath(new URL('./production_artifact_build.py', import.meta.url));
    const child = spawnSync('python3', [builder, '--multisite', '--root', root,
      '--manifest', args.manifest, '--output', args.output], {encoding: 'utf8'});
    if (child.error || child.status !== 0) {
      await unlink(args.manifest);
      throw new Error(`artifact build failed: ${child.error?.message || child.stderr}`);
    }
    const digest = createHash('sha256').update(await readFile(args.output)).digest('hex');
    result = {status: 'built_not_approved', manifestSha256: manifest.manifestSha256,
      artifactSha256: digest, entryCount: manifest.entries.length, productionMutationAllowed: false};
  } else if (command === 'plan') {
    result = compareMultisite({base: await readJson('base'), prod: await readJson('prod'),
      candidate: await readJson('candidate'), scope, legacyScope});
    if (args.output) await writeFile(args.output, JSON.stringify(result, null, 2) + '\n', {flag: 'wx', mode: 0o600});
  } else if (command === 'classify') {
    result = classifyMultisitePaths(await readJson('paths'), scope, legacyScope);
    if (!['FILE_ONLY', 'NO_DEPLOYABLE_CHANGE'].includes(result.releaseClass)) process.exitCode = 2;
  } else throw new Error('choose build, plan or classify; this tool cannot deploy');
  console.log(JSON.stringify(result, null, 2));
} catch (error) {
  console.error(`Multisite check failed: ${error.message}`);
  process.exitCode = 1;
}

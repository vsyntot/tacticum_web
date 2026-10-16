#!/usr/bin/env node
// Production read-only manifest builder. The wrapper supplies canonical roots
// and SSH forced-command validation; this script only hashes regular files.
import {parseArgs} from 'node:util';
import {readFile} from 'node:fs/promises';
import path from 'node:path';
import {fileURLToPath} from 'node:url';
import {loadDeployScope} from './production-deploy-contract.mjs';
import {loadMultisiteScope, buildMultisiteManifest, validateMultisiteManifest} from './multisite-contract.mjs';

try {
  const {values: args} = parseArgs({options: {
    'primary-root': {type: 'string'}, 'divatech-root': {type: 'string'}, 'platform': {type: 'string'},
  }});
  for (const name of ['primary-root', 'divatech-root', 'platform']) {
  if (!args[name]) throw new Error(`--${name} required`);
  }
  const toolRoot = path.dirname(fileURLToPath(import.meta.url));
  const scope = await loadMultisiteScope(path.join(toolRoot, 'multisite-scope.json'));
  const legacyScope = await loadDeployScope(path.join(toolRoot, 'deploy-scope.json'));
  const platform = JSON.parse(await readFile(args.platform, 'utf8'));
  const manifest = await buildMultisiteManifest({
    root: toolRoot,
    scope,
    platform,
    treeKind: 'prod',
    productionRoots: {primary: args['primary-root'], divatech: args['divatech-root']},
  });
  validateMultisiteManifest(manifest, {scope, legacyScope});
  process.stdout.write(JSON.stringify(manifest) + '\n');
} catch (error) {
  console.error(`Multisite production manifest stopped: ${error.message}`);
  process.exitCode = 1;
}

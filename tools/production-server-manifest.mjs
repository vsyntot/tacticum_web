import {
  buildCanonicalManifest,
  loadDeployScope,
} from './production-deploy-contract.mjs';

const scope = await loadDeployScope('/usr/local/lib/tacticum-deploy/deploy-scope.json');
const manifest = await buildCanonicalManifest('/home/bitrix/www', scope, { treeKind: 'prod' });

process.stdout.write(`${JSON.stringify(manifest)}\n`);

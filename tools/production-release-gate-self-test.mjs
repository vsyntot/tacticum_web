import assert from 'node:assert/strict';
import {gateRelease, inspectionEligibility} from './production-release-gate.mjs';
import {loadDeployScope} from './production-deploy-contract.mjs';
const scope = await loadDeployScope();
const check = (statuses, paths = ['local/templates/tacticum/header.php']) => gateRelease({scope, paths,
  plan: {comparisonMode:'THREE_WAY',changes:statuses.map(status=>({status}))}});
assert.equal(check([]).deployRequired,false);
assert.equal(check(['CONVERGED']).deployRequired,false);
assert.equal(check(['CONVERGED']).baselinePromotionRequired,true);
assert.equal(check(['LOCAL_CHANGE','CONVERGED']).deployRequired,true);
for (const status of ['CONFLICT','PROD_CHANGE','UNKNOWN']) assert.throws(()=>check([status]),/reconciliation/);
for (const unsafe of ['local/php_interface/include/tacticum_config.example.php',
  '.github/workflows/deploy.yml','unmanaged.php','bitrix/file.php','sites/divatech/index.php']) {
  assert.throws(()=>check(['LOCAL_CHANGE'], ['index.php',unsafe]),/separate review/);
}
assert.throws(()=>check(['LOCAL_CHANGE'],['docs/workflow/README.md']),/no FILE_ONLY/);
assert.equal(inspectionEligibility(['index.php'], scope).automaticInspectionEligible, true);
for (const paths of [['docs/workflow/README.md'], ['index.php','tools/helper.py'],
  ['index.php','bitrix/.settings.php'], ['sites/divatech/index.php'], ['unknown.php']]) {
  const result = inspectionEligibility(paths, scope);
  assert.equal(result.automaticInspectionEligible, false);
  assert.equal(result.productionMutationAllowed, false);
}
console.log('Release gate: no-op/converged, mixed stateful/control-plane/unknown and drift boundaries passed.');

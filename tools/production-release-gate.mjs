#!/usr/bin/env node
import {readFile} from 'node:fs/promises';
import {execFileSync} from 'node:child_process';
import {pathToFileURL} from 'node:url';
import {parseArgs} from 'node:util';
import {classifyChangedPaths, loadDeployScope} from './production-deploy-contract.mjs';

export function inspectionEligibility(paths, scope) {
  const classification = classifyChangedPaths(paths, scope);
  return {classification, automaticInspectionEligible: classification.releaseClass === 'FILE_ONLY',
    productionMutationAllowed: false,
    disposition: classification.releaseClass === 'FILE_ONLY' ? 'continue_to_drift_plan'
      : classification.releaseClass === 'NO_DEPLOYABLE_CHANGE' ? 'no_application_change' : 'separate_review_required'};
}

export function gateRelease({plan, paths, scope}) {
  const classification = classifyChangedPaths(paths, scope);
  if (!['FILE_ONLY', 'NO_DEPLOYABLE_CHANGE'].includes(classification.releaseClass)) {
    throw new Error(`release requires separate review: ${classification.releaseClass}`);
  }
  if (plan.comparisonMode !== 'THREE_WAY' || !Array.isArray(plan.changes)
    || plan.changes.some(item => !['LOCAL_CHANGE', 'CONVERGED'].includes(item.status))) {
    throw new Error('production drift requires reconciliation');
  }
  const local = plan.changes.filter(item => item.status === 'LOCAL_CHANGE');
  if (local.length && classification.releaseClass !== 'FILE_ONLY') throw new Error('application diff has no FILE_ONLY Git scope');
  return {classification, deployRequired: local.length > 0,
    baselinePromotionRequired: plan.changes.some(item => item.status === 'CONVERGED')};
}

if (process.argv[1] && import.meta.url === pathToFileURL(process.argv[1]).href) {
  try {
    const {values} = parseArgs({options: {plan: {type:'string'}, evidence: {type:'string'},
      'classify-only': {type:'boolean', default:false}}});
    // Transitional verified v1 baseline; v2 will use external CAS-protected records.
    const evidence = JSON.parse(await readFile(values.evidence, 'utf8'));
    const commit = evidence.merge?.commit;
    if (evidence.status !== 'verified' || !/^[a-f0-9]{40}$/.test(commit || '')
      || !/^[a-f0-9]{64}$/.test(evidence.post_manifest_sha256 || '')
      || evidence.main_workflow?.production_manifest_sha256 !== evidence.post_manifest_sha256
      || evidence.main_workflow?.conclusion !== 'success' || evidence.main_workflow?.changes !== 0) {
      throw new Error('verified baseline commit binding unavailable');
    }
    execFileSync('git', ['merge-base', '--is-ancestor', commit, 'HEAD'], {stdio: 'pipe'});
    const paths = execFileSync('git', ['diff', '--no-renames', '--name-only', '-z', commit, 'HEAD'],
      {encoding:'utf8'}).split('\0').filter(Boolean);
    const scope = await loadDeployScope();
    let result;
    if (values['classify-only']) {
      result = inspectionEligibility(paths, scope);
    } else {
      const plan = JSON.parse(await readFile(values.plan, 'utf8'));
      if (plan.baseManifestSha256 !== evidence.post_manifest_sha256) throw new Error('plan baseline differs from evidence');
      result = gateRelease({plan, paths, scope});
    }
    console.log(JSON.stringify({...result, gitBase: commit}, null, 2));
  } catch (error) { console.error(`Release gate stopped: ${error.message}`); process.exitCode = 1; }
}

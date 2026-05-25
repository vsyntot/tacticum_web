#!/usr/bin/env node

import { readFile } from 'node:fs/promises';

const args = process.argv.slice(2);
const strict = args.includes('--strict') || process.env.TACTICUM_KNOWN_GAPS_STRICT === '1';
const releaseSignoffPath = process.env.TACTICUM_RELEASE_SIGNOFF
  || 'docs/workflow/release-signoff-2026-05-24-post-deploy.draft.json';

const [
  gapAnalysis,
  releaseSignoffRaw,
  legacyInventory,
  handoff,
] = await Promise.all([
  readFile('docs/workflow/gap-analysis.md', 'utf8'),
  readFile(releaseSignoffPath, 'utf8'),
  readFile('docs/workflow/legacy-sale-alias-consumer-inventory.md', 'utf8'),
  readFile('docs/workflow/sprint-10-external-gates-handoff-2026-05-24.md', 'utf8'),
]);

const releaseSignoff = JSON.parse(releaseSignoffRaw);
const releasePendingGates = Object.entries(releaseSignoff.gates || {})
  .filter(([, gate]) => String(gate?.status || '').trim() === 'pending')
  .map(([name, gate]) => ({
    name,
    owner: String(gate.owner || '').trim() || '-',
    due: String(gate.due || '').trim() || '-',
    reason: String(gate.reason || '').trim() || '-',
  }));

const codeOpenRows = tableRows(gapAnalysis)
  .filter((line) => /^\|\s*[^|]+\|\s*(open|in-progress)\s*\|/i.test(line));

const allExternalHandoffRows = tableRows(gapAnalysis)
  .filter((line) => /\|\s*external handoff\s*\|/i.test(line));
const externalHandoffRows = allExternalHandoffRows.some((line) => /^\|\s*S12-/i.test(line))
  ? allExternalHandoffRows.filter((line) => /^\|\s*S12-/i.test(line))
  : allExternalHandoffRows;

const legacyPendingRows = tableRows(legacyInventory)
  .filter((line) => /\|\s*pending\s*\|/i.test(line));

const postDeployPendingRows = tableRows(handoff)
  .filter((line) => /pending deploy\/cache/i.test(line));

const hasKnownOpenWork = codeOpenRows.length > 0
  || releasePendingGates.length > 0
  || legacyPendingRows.length > 0
  || postDeployPendingRows.length > 0;

console.log('Known gaps summary');
console.log(`Release sign-off: ${releaseSignoff.release?.id || 'unknown'} (${releaseSignoffPath})`);
console.log('');
console.log(`Code-level open/in-progress gaps: ${codeOpenRows.length}`);
printRows(codeOpenRows);

console.log('');
console.log(`External release gates pending: ${releasePendingGates.length}`);
for (const gate of releasePendingGates) {
  console.log(`- ${gate.name} | owner=${gate.owner} | due=${gate.due} | ${gate.reason}`);
}

console.log('');
console.log(`External handoff rows tracked in gap-analysis: ${externalHandoffRows.length}`);
printRows(externalHandoffRows);

console.log('');
console.log(`Legacy sale inventory pending rows: ${legacyPendingRows.length}`);
printRows(legacyPendingRows);

console.log('');
console.log(`Post-deploy/cache smoke pending rows: ${postDeployPendingRows.length}`);
printRows(postDeployPendingRows);

if (strict && hasKnownOpenWork) {
  console.error('');
  console.error('Known gaps strict check failed: external or code-level work remains open.');
  process.exit(1);
}

console.log('');
console.log(strict
  ? 'Known gaps strict check passed.'
  : 'Known gaps draft check passed; use --strict only for final closure.');

function tableRows(markdown) {
  return markdown
    .split(/\r?\n/)
    .map((line) => line.trim())
    .filter((line) => line.startsWith('|') && !/^\|\s*-+/.test(line) && !/\|\s*ID\s*\|/i.test(line));
}

function printRows(rows) {
  for (const row of rows) {
    console.log(`- ${row}`);
  }
}

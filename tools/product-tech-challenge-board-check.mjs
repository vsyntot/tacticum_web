#!/usr/bin/env node

import { readFile } from 'node:fs/promises';

const sourcePath = 'docs/workflow/product-tech-challenge-gap-register-2026-06-04.md';
const boardPath = 'docs/workflow/product-tech-challenge-execution-board-2026-06-04.md';

const idPattern = /\b(?:CFG|UX|UI|ARCH|CMP|STACK|CONTENT|SEC|REL)-\d{3}\b/g;

const [source, board] = await Promise.all([
  readFile(sourcePath, 'utf8'),
  readFile(boardPath, 'utf8'),
]);

const sourceIds = unique(
  source
    .split(/\r?\n/)
    .filter((line) => /^\|\s*(?:CFG|UX|UI|ARCH|CMP|STACK|CONTENT|SEC|REL)-\d{3}\s*\|/.test(line))
    .flatMap((line) => line.match(idPattern) || [])
);
const boardIds = unique(board.match(idPattern) || []);

const requiredSections = [
  '## Work Packages',
  '## Issue Cards',
  '## Owner Approval Matrix',
  '## Do Not Start Board',
  '## Coverage Index',
];

const missingSections = requiredSections.filter((section) => !board.includes(section));
const missingIds = sourceIds.filter((id) => !boardIds.includes(id));
const unknownIds = boardIds.filter((id) => !sourceIds.includes(id));

if (missingSections.length > 0 || missingIds.length > 0 || unknownIds.length > 0) {
  if (missingSections.length > 0) {
    console.error('Execution board is missing required sections:');
    for (const section of missingSections) {
      console.error(`- ${section}`);
    }
  }

  if (missingIds.length > 0) {
    console.error('Execution board is missing source gap IDs:');
    for (const id of missingIds) {
      console.error(`- ${id}`);
    }
  }

  if (unknownIds.length > 0) {
    console.error('Execution board references unknown gap IDs:');
    for (const id of unknownIds) {
      console.error(`- ${id}`);
    }
  }

  process.exit(1);
}

console.log(`Product tech challenge execution board check passed: ${sourceIds.length} gap IDs covered.`);

function unique(values) {
  return [...new Set(values)].sort((a, b) => {
    const [aPrefix, aNum] = splitId(a);
    const [bPrefix, bNum] = splitId(b);
    if (aPrefix === bPrefix) {
      return aNum - bNum;
    }
    return aPrefix.localeCompare(bPrefix);
  });
}

function splitId(id) {
  const [prefix, number] = id.split('-');
  return [prefix, Number(number)];
}

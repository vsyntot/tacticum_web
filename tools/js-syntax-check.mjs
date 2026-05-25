#!/usr/bin/env node

import { readdir } from 'node:fs/promises';
import { extname, join } from 'node:path';
import { spawnSync } from 'node:child_process';

const roots = ['local', 'tools'];
const extensions = new Set(['.js', '.mjs']);
const files = [];

for (const root of roots) {
  await collectFiles(root);
}

files.sort();

for (const file of files) {
  const result = spawnSync(process.execPath, ['--check', file], {
    encoding: 'utf8',
    stdio: ['ignore', 'pipe', 'pipe'],
  });

  if (result.status !== 0) {
    process.stderr.write(result.stdout || '');
    process.stderr.write(result.stderr || '');
    process.exit(result.status || 1);
  }
}

console.log(`JS syntax check passed: ${files.length} files`);

async function collectFiles(dir) {
  let entries;
  try {
    entries = await readdir(dir, { withFileTypes: true });
  } catch {
    return;
  }

  for (const entry of entries) {
    const path = join(dir, entry.name);
    if (entry.isDirectory()) {
      await collectFiles(path);
      continue;
    }

    if (entry.isFile() && extensions.has(extname(entry.name))) {
      files.push(path);
    }
  }
}

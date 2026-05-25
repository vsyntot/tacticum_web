#!/usr/bin/env node

import { readdir, readFile } from 'node:fs/promises';
import { extname, join, normalize } from 'node:path';
import { transform } from 'lightningcss';

const roots = ['local/templates/tacticum'];
const files = [];
const tailwindSource = normalize('local/templates/tacticum/assets/src/tailwind.css');

for (const root of roots) {
  await collectFiles(root);
}

files.sort();

for (const file of files) {
  if (normalize(file) === tailwindSource) {
    continue;
  }

  transform({
    filename: file,
    code: await readFile(file),
    minify: false,
  });
}

console.log(`CSS syntax check passed: ${files.length - 1} runtime files; Tailwind source is validated by npm run css:check`);

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

    if (entry.isFile() && extname(entry.name) === '.css') {
      files.push(path);
    }
  }
}

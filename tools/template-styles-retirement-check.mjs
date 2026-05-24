#!/usr/bin/env node

import { readdir, readFile, stat } from 'node:fs/promises';
import path from 'node:path';

const templateStylesPath = 'local/templates/tacticum/template_styles.css';
const stylesDir = 'local/templates/tacticum/styles';
const globalStylesPath = 'local/templates/tacticum/styles/global.css';
const tailwindStylesPath = 'local/templates/tacticum/tailwind.generated.css';
const headerPath = 'local/templates/tacticum/header.php';
const remixIconStylesPath = 'local/templates/tacticum/fonts/remixicon.css';
const remixIconScanRoots = [
  'index.php',
  'about',
  'services',
  'price',
  'calculator',
  'offer',
  'aiagents',
  'contacts',
  'policies',
  'local/templates/tacticum',
];

const [templateStyles, globalStyles, tailwindStyles, header, remixIconStyles, styleFiles] = await Promise.all([
  readFile(templateStylesPath, 'utf8'),
  readFile(globalStylesPath, 'utf8'),
  readFile(tailwindStylesPath, 'utf8'),
  readFile(headerPath, 'utf8'),
  readFile(remixIconStylesPath, 'utf8'),
  readdir(stylesDir),
]);

const activeTemplateStyles = templateStyles.replace(/\/\*[\s\S]*?\*\//g, '').trim();
const failures = [];

if (activeTemplateStyles.length > 0) {
  failures.push(`${templateStylesPath} must stay an empty/comment-only Bitrix compatibility shim.`);
}

if (!globalStyles.includes('Migrated global template styles')) {
  failures.push(`${globalStylesPath} must own the migrated legacy/global template rules.`);
}

if (/:where\(\[class\^="ri-"\]\)::before/.test(globalStyles)) {
  failures.push(`${globalStylesPath} must not define a generic Remixicon fallback; use only real classes from ${remixIconStylesPath}.`);
}

if (!header.includes('styles/global.css')) {
  failures.push(`${headerPath} must load styles/global.css through Bitrix Asset.`);
}

if (/aiagents_css|styles\/aiagents\.css/.test(header)) {
  failures.push(`${headerPath} must not load a separate aiagents CSS asset; /aiagents/ rules live in scoped styles/global.css.`);
}

const unexpectedTemplateStyles = styleFiles
  .filter((file) => file.endsWith('.css') && file !== 'global.css');
for (const file of unexpectedTemplateStyles) {
  failures.push(`${stylesDir}/${file} is not an approved template-level CSS file; keep manual runtime CSS in styles/global.css or component style.css.`);
}

if (/@import\b/.test(templateStyles)) {
  failures.push(`${templateStylesPath} must not reintroduce active imports.`);
}

for (const [file, source] of [
  [templateStylesPath, templateStyles],
  [globalStylesPath, globalStyles],
  [tailwindStylesPath, tailwindStyles],
]) {
  failures.push(...checkCssUrls(file, source));
}

failures.push(...await checkRemixIconClasses(remixIconStyles, remixIconScanRoots));

if (failures.length > 0) {
  console.error('Template styles retirement check failed:');
  for (const failure of failures) {
    console.error(`- ${failure}`);
  }
  process.exit(1);
}

console.log('Template styles retirement check passed.');

function checkCssUrls(file, source) {
  const errors = [];
  const urlPattern = /url\(([^)]*)\)/g;
  for (const match of source.matchAll(urlPattern)) {
    const rawValue = match[1].trim();
    if (!rawValue) {
      errors.push(`${file} has an empty url() value.`);
      continue;
    }

    const quote = rawValue[0];
    if ((quote === '"' || quote === "'") && !rawValue.endsWith(quote)) {
      errors.push(`${file} has an unbalanced quoted url(): ${match[0]}`);
    }

    const normalized = rawValue.replace(/^['"]|['"]$/g, '');
    if (/^\/local\/templates\/tacticum\/(?:images|fonts)\/?$/.test(normalized)) {
      errors.push(`${file} has a template asset directory url() without a filename: ${match[0]}`);
    }
  }

  return errors;
}

async function checkRemixIconClasses(remixIconStyles, roots) {
  const errors = [];
  const validClasses = new Set([
    ...[...remixIconStyles.matchAll(/\.([a-z0-9-]+):before\s*\{/g)].map((match) => match[1]),
    ...[...remixIconStyles.matchAll(/\.((?:ri|ri-[a-z0-9-]+))\s*\{/g)].map((match) => match[1]),
  ]);
  const files = [];

  for (const root of roots) {
    await collectFiles(root, files);
  }

  const missingClasses = new Set();
  for (const file of files) {
    const source = await readFile(file, 'utf8');
    for (const match of source.matchAll(/\b(ri-[a-z0-9-]+)\b/g)) {
      const className = match[1];
      if (!validClasses.has(className)) {
        missingClasses.add(`${file}: ${className}`);
      }
    }
  }

  for (const missingClass of missingClasses) {
    errors.push(`Unknown Remixicon class ${missingClass}; use a class present in ${remixIconStylesPath}.`);
  }

  return errors;
}

async function collectFiles(entry, files) {
  const entryStat = await stat(entry);
  if (entryStat.isDirectory()) {
    if (['assets', 'fonts', 'images'].includes(path.basename(entry))) {
      return;
    }

    for (const child of await readdir(entry)) {
      await collectFiles(path.join(entry, child), files);
    }
    return;
  }

  if (['.css', '.js', '.php'].includes(path.extname(entry))) {
    files.push(entry);
  }
}

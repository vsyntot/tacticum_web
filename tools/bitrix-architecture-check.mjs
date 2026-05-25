#!/usr/bin/env node

import { readdir, readFile, stat } from 'node:fs/promises';
import path from 'node:path';

const failures = [];

const init = await readFile('local/php_interface/init.php', 'utf8');
if (/function\s+tacticum_|CIBlockElement|EventManager|Loader::includeModule/.test(init)) {
  failures.push('local/php_interface/init.php must stay a thin bootstrap without domain functions, iblock queries or event bodies.');
}

const componentFiles = [];
await collectFiles('local/components/tacticum', componentFiles, (file) => path.basename(file) === 'component.php');
for (const file of componentFiles) {
  const source = await readFile(file, 'utf8');
  if (/function\s+tacticum_[a-z0-9_]+\s*\(/i.test(source)) {
    failures.push(`${file} must not declare global tacticum_* helper functions; use component_helpers.php or local closures.`);
  }
}

for (const publicFile of [
  'index.php',
  'about/index.php',
  'services/index.php',
  'price/index.php',
  'calculator/index.php',
  'contacts/index.php',
  'aiagents/index.php',
  'offer/index.php',
  'policies/index.php',
]) {
  const source = await readFile(publicFile, 'utf8');
  if (/IncludeComponent\(\s*[\r\n\t ]*['"]bitrix:/m.test(source)) {
    failures.push(`${publicFile} must call local tacticum:* components for public page content.`);
  }
}

const offerCatalog = [
  await readFile('local/php_interface/include/offer_catalog.php', 'utf8'),
  await readFile('local/php_interface/include/offer_catalog_cache.php', 'utf8'),
].join('\n');
for (const pattern of [
  /TacticumOfferCatalogCache/,
  /TacticumOfferCatalogRepository/,
  /OnAfterIBlockElementAdd/,
  /OnAfterIBlockElementUpdate/,
  /OnAfterIBlockElementDelete/,
  /OnAfterIBlockElementSetPropertyValues/,
]) {
  if (!pattern.test(offerCatalog)) {
    failures.push(`local/php_interface/include/offer_catalog.php is missing ${pattern.source} cache/service hardening.`);
  }
}

const footer = await readFile('local/templates/tacticum/footer.php', 'utf8');
if (!/tacticum:contact\.modal/.test(footer)) {
  failures.push('local/templates/tacticum/footer.php must render the contact modal through tacticum:contact.modal.');
}

if (failures.length > 0) {
  console.error('Bitrix architecture check failed:');
  for (const failure of failures) {
    console.error(`- ${failure}`);
  }
  process.exit(1);
}

console.log('Bitrix architecture check passed.');

async function collectFiles(entry, files, predicate) {
  const entryStat = await stat(entry);
  if (entryStat.isDirectory()) {
    for (const child of await readdir(entry)) {
      await collectFiles(path.join(entry, child), files, predicate);
    }
    return;
  }

  if (!predicate || predicate(entry)) {
    files.push(entry);
  }
}

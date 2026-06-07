#!/usr/bin/env node

import { readdir, readFile, stat } from 'node:fs/promises';
import path from 'node:path';

const templateStylesPath = 'local/templates/tacticum/template_styles.css';
const stylesDir = 'local/templates/tacticum/styles';
const globalStylesPath = 'local/templates/tacticum/styles/global.css';
const tailwindStylesPath = 'local/templates/tacticum/tailwind.generated.css';
const tailwindSourcePath = 'local/templates/tacticum/assets/src/tailwind.css';
const headerPath = 'local/templates/tacticum/header.php';
const remixIconStylesPath = 'local/templates/tacticum/fonts/remixicon.css';
const approvedTemplateStyleFiles = new Set([
  'global.css',
  'components.css',
  'page-about-calculator.css',
  'page-offer-price-services.css',
  'page-aiagents.css',
]);
const forbiddenTemplateAssetPaths = [
  'local/templates/tacticum/include',
  'local/templates/tacticum/fonts/index.html',
  'local/templates/tacticum/fonts/symbol.html',
  'local/templates/tacticum/fonts/unicode.html',
  'local/templates/tacticum/fonts/pacifico',
  'local/templates/tacticum/fonts/RemixIcon_Fonts_v4.6.0.zip',
  'local/templates/tacticum/fonts/remixicon.glyph.json',
  'local/templates/tacticum/fonts/remixicon.less',
  'local/templates/tacticum/fonts/remixicon.module.less',
  'local/templates/tacticum/fonts/remixicon.scss',
  'local/templates/tacticum/fonts/remixicon.styl',
  'local/templates/tacticum/fonts/remixicon.symbol.svg',
  'local/templates/tacticum/images/aibot_hero_bg.jpg',
  'local/templates/tacticum/images/aibot_hero_bg_2.png',
  'local/templates/tacticum/images/aibot_hero_bg_old.jpg',
  'local/templates/tacticum/images/background.jpg',
  'local/templates/tacticum/images/finance.jpg',
  'local/templates/tacticum/images/logistics.jpg',
  'local/templates/tacticum/images/retail.jpg',
  'local/templates/tacticum/images/tm1.jpg',
  'local/templates/tacticum/images/tm2.jpg',
  'local/templates/tacticum/images/tm3.jpg',
];
const pngDimensionExpectations = [
  ['local/templates/tacticum/images/favicon-16x16.png', 16, 16],
  ['local/templates/tacticum/images/favicon-32x32.png', 32, 32],
  ['local/templates/tacticum/images/apple-touch-icon.png', 180, 180],
  ['local/templates/tacticum/images/android-chrome-192x192.png', 192, 192],
  ['local/templates/tacticum/images/android-chrome-512x512.png', 512, 512],
];
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
  'local/components',
  'local/templates/tacticum',
];

const [templateStyles, globalStyles, tailwindStyles, tailwindSource, header, remixIconStyles, styleFiles] = await Promise.all([
  readFile(templateStylesPath, 'utf8'),
  readFile(globalStylesPath, 'utf8'),
  readFile(tailwindStylesPath, 'utf8'),
  readFile(tailwindSourcePath, 'utf8'),
  readFile(headerPath, 'utf8'),
  readFile(remixIconStylesPath, 'utf8'),
  readdir(stylesDir),
]);
const templateStyleSources = await Promise.all(
  styleFiles
    .filter((file) => file.endsWith('.css'))
    .map(async (file) => [path.join(stylesDir, file), await readFile(path.join(stylesDir, file), 'utf8')])
);

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

if (/Pacifico|fonts\/pacifico/.test(globalStyles)) {
  failures.push(`${globalStylesPath} must not load unused Pacifico fonts.`);
}

if (tailwindSource.includes('../../include/**/*.php')) {
  failures.push(`${tailwindSourcePath} must not scan removed template include directory.`);
}

if (!tailwindSource.includes('../../../../../local/lib/**/*.php')) {
  failures.push(`${tailwindSourcePath} must scan local/lib PHP renderers so generated utilities cover service-rendered public markup.`);
}

for (const styleFile of approvedTemplateStyleFiles) {
  if (!header.includes(`styles/${styleFile}`)) {
    failures.push(`${headerPath} must load styles/${styleFile} through Bitrix Asset.`);
  }
}

if (/aiagents_css|styles\/aiagents\.css/.test(header)) {
  failures.push(`${headerPath} must not load legacy styles/aiagents.css; /aiagents/ rules live in scoped styles/page-aiagents.css.`);
}

const unexpectedTemplateStyles = styleFiles
  .filter((file) => file.endsWith('.css') && !approvedTemplateStyleFiles.has(file));
for (const file of unexpectedTemplateStyles) {
  failures.push(`${stylesDir}/${file} is not an approved template-level CSS file; use the fixed template CSS split or component style.css.`);
}

if (/@import\b/.test(templateStyles)) {
  failures.push(`${templateStylesPath} must not reintroduce active imports.`);
}

for (const [file, source] of [
  [templateStylesPath, templateStyles],
  ...templateStyleSources,
  [tailwindStylesPath, tailwindStyles],
]) {
  failures.push(...checkCssUrls(file, source));
}

failures.push(...await checkRemixIconClasses(remixIconStyles, remixIconScanRoots));
failures.push(...await checkForbiddenTemplateAssetPaths(forbiddenTemplateAssetPaths));
failures.push(...await checkPngDimensions(pngDimensionExpectations));

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

async function checkForbiddenTemplateAssetPaths(paths) {
  const errors = [];

  for (const file of paths) {
    try {
      await stat(file);
      errors.push(`${file} must not be deployed with the public template; keep only referenced runtime assets.`);
    } catch (error) {
      if (error?.code !== 'ENOENT') {
        throw error;
      }
    }
  }

  return errors;
}

async function checkPngDimensions(images) {
  const errors = [];
  const pngSignature = '89504e470d0a1a0a';

  for (const [file, expectedWidth, expectedHeight] of images) {
    const buffer = await readFile(file);
    if (buffer.subarray(0, 8).toString('hex') !== pngSignature || buffer.subarray(12, 16).toString('ascii') !== 'IHDR') {
      errors.push(`${file} must be a valid PNG image.`);
      continue;
    }

    const width = buffer.readUInt32BE(16);
    const height = buffer.readUInt32BE(20);
    if (width !== expectedWidth || height !== expectedHeight) {
      errors.push(`${file} must be ${expectedWidth}x${expectedHeight}, got ${width}x${height}.`);
    }
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

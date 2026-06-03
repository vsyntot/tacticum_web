#!/usr/bin/env node

import { readFile } from 'node:fs/promises';

const tokenPath = 'docs/design-system-handoff/05-design-tokens-as-is.json';
const tailwindPath = 'local/templates/tacticum/assets/src/tailwind.css';
const globalCssPath = 'local/templates/tacticum/styles/global.css';
const formsJsPath = 'local/templates/tacticum/js/forms.js';
const packagePath = 'package.json';

const [tokenSource, tailwindSource, globalCss, formsJs, packageSource] = await Promise.all([
  readFile(tokenPath, 'utf8'),
  readFile(tailwindPath, 'utf8'),
  readFile(globalCssPath, 'utf8'),
  readFile(formsJsPath, 'utf8'),
  readFile(packagePath, 'utf8'),
]);

const tokens = JSON.parse(tokenSource);
const packageJson = JSON.parse(packageSource);
const failures = [];

const canonicalTokens = tokens.contract?.canonicalTokens ?? {};
const observedCandidates = tokens.contract?.observedTokenCandidates ?? {};
const knownDrift = tokens.contract?.knownDrift ?? [];

for (const source of [tailwindPath, globalCssPath, formsJsPath]) {
  if (!tokens.meta?.sources?.includes(source)) {
    failures.push(`${tokenPath}: meta.sources must include ${source}.`);
  }
}

const requiredCanonicalTokens = [
  {
    id: 'color.brand.primary',
    value: '#0066CC',
    cssVariable: '--color-primary',
    mirrorPath: ['colors', 'brand', 'primary', 'value'],
  },
  {
    id: 'color.brand.secondary',
    value: '#001F3F',
    cssVariable: '--color-secondary',
    mirrorPath: ['colors', 'brand', 'secondary', 'value'],
  },
  {
    id: 'radius.button',
    value: '8px',
    cssVariable: '--radius-button',
    mirrorPath: ['radius', 'button', 'value'],
  },
];

for (const expected of requiredCanonicalTokens) {
  const token = canonicalTokens[expected.id];
  if (!token) {
    failures.push(`${tokenPath}: missing contract.canonicalTokens.${expected.id}.`);
    continue;
  }

  if (token.value !== expected.value) {
    failures.push(`${tokenPath}: ${expected.id}.value must be ${expected.value}, got ${token.value}.`);
  }

  if (token.cssVariable !== expected.cssVariable) {
    failures.push(`${tokenPath}: ${expected.id}.cssVariable must be ${expected.cssVariable}.`);
  }

  const declarationPattern = new RegExp(`${escapeRegExp(expected.cssVariable)}\\s*:\\s*${escapeRegExp(expected.value)}\\s*;`);
  if (!declarationPattern.test(tailwindSource)) {
    failures.push(`${tailwindPath}: missing ${expected.cssVariable}: ${expected.value};`);
  }

  const mirroredValue = getPath(tokens, expected.mirrorPath);
  if (mirroredValue !== expected.value) {
    failures.push(`${tokenPath}: ${expected.mirrorPath.join('.')} must mirror canonical ${expected.id} (${expected.value}).`);
  }
}

const requiredObservedCandidates = [
  {
    id: 'gradient.brandHorizontal',
    value: 'linear-gradient(90deg, #001F40 0%, #0066CC 100%)',
    source: globalCss,
  },
  {
    id: 'color.focus.brandRing',
    value: 'rgba(0, 102, 204, 0.18)',
    source: globalCss,
  },
  {
    id: 'color.brand.hoverBlue',
    value: '#0057ad',
    source: globalCss,
  },
  {
    id: 'color.brand.legacyLinkHover',
    value: '#007bff',
    source: globalCss,
  },
  {
    id: 'radius.formControl',
    value: '8px',
    source: globalCss,
    sourcePattern: /border-radius:\s*8px;/,
  },
  {
    id: 'radius.pill',
    value: '9999px',
    source: globalCss,
    sourcePattern: /border-radius:\s*9999px;/,
  },
  {
    id: 'elevation.cardHover',
    value: '0 10px 25px -5px rgba(0, 102, 204, 0.1)',
    source: globalCss,
  },
  {
    id: 'elevation.formCta',
    value: '0 18px 42px rgba(15, 23, 42, 0.18)',
    source: globalCss,
  },
  {
    id: 'elevation.modal',
    value: '0 24px 64px rgba(15, 23, 42, 0.28)',
    source: globalCss,
  },
  {
    id: 'motion.fast',
    value: '0.2s ease',
    source: globalCss,
  },
  {
    id: 'motion.default',
    value: '0.3s ease',
    source: globalCss,
  },
  {
    id: 'z.toast',
    value: '999',
    source: formsJs,
    sourcePattern: /z-\[999\]/,
  },
];

for (const expected of requiredObservedCandidates) {
  const token = observedCandidates[expected.id];
  if (!token) {
    failures.push(`${tokenPath}: missing contract.observedTokenCandidates.${expected.id}.`);
    continue;
  }

  if (token.value !== expected.value) {
    failures.push(`${tokenPath}: ${expected.id}.value must be ${expected.value}, got ${token.value}.`);
  }

  const pattern = expected.sourcePattern ?? new RegExp(escapeRegExp(expected.value));
  if (!pattern.test(expected.source)) {
    failures.push(`${tokenPath}: ${expected.id} says ${expected.value}, but the source file no longer contains it.`);
  }
}

const requiredDrift = [
  ['brand-secondary-gradient-start', '#001F3F', '#001F40'],
  ['legacy-hover-blue', '#0066CC', '#007bff'],
];

for (const [id, canonicalValue, observedValue] of requiredDrift) {
  const drift = knownDrift.find((item) => item.id === id);
  if (!drift) {
    failures.push(`${tokenPath}: missing knownDrift entry ${id}.`);
    continue;
  }

  if (drift.canonicalValue !== canonicalValue || drift.observedValue !== observedValue) {
    failures.push(`${tokenPath}: knownDrift.${id} must document ${canonicalValue} -> ${observedValue}.`);
  }
}

if (tokens.contract?.guard?.script !== 'tools/design-token-contract-check.mjs') {
  failures.push(`${tokenPath}: contract.guard.script must point to tools/design-token-contract-check.mjs.`);
}

if (tokens.contract?.guard?.npmScript !== 'design:tokens:check') {
  failures.push(`${tokenPath}: contract.guard.npmScript must be design:tokens:check.`);
}

if (packageJson.scripts?.['design:tokens:check'] !== 'node ./tools/design-token-contract-check.mjs') {
  failures.push(`${packagePath}: scripts.design:tokens:check must run node ./tools/design-token-contract-check.mjs.`);
}

if (failures.length > 0) {
  console.error('Design token contract check failed:');
  for (const failure of failures) {
    console.error(`- ${failure}`);
  }
  process.exit(1);
}

console.log('Design token contract check passed.');

function getPath(value, path) {
  let current = value;
  for (const key of path) {
    current = current?.[key];
  }
  return current;
}

function escapeRegExp(value) {
  return value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

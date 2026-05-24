#!/usr/bin/env node

import { access } from 'node:fs/promises';

const sunset = Date.UTC(2026, 8, 30, 0, 0, 0);
const now = process.env.TACTICUM_SUNSET_CHECK_DATE
  ? Date.parse(process.env.TACTICUM_SUNSET_CHECK_DATE)
  : Date.now();

if (Number.isNaN(now)) {
  console.error('Invalid TACTICUM_SUNSET_CHECK_DATE. Use an ISO date.');
  process.exit(1);
}

const legacyEndpoints = [
  'local/rest/tacticum_offer.php',
  'local/rest/tacticum_sale.php',
];

const existing = [];
for (const endpoint of legacyEndpoints) {
  try {
    await access(endpoint);
    existing.push(endpoint);
  } catch {
    // Endpoint already removed.
  }
}

if (now >= sunset && existing.length > 0) {
  console.error('Legacy sale endpoint sunset reached: 2026-09-30.');
  console.error('Decide whether to remove aliases, return 410/redirect, or extend support before deploy.');
  console.error(existing.join('\n'));
  process.exit(1);
}

const daysLeft = Math.max(0, Math.ceil((sunset - now) / 86400000));
console.log(`Legacy sale endpoint sunset check passed. Days left: ${daysLeft}.`);

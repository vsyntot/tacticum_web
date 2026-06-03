#!/usr/bin/env node

import { createReadStream } from 'node:fs';
import { readdir } from 'node:fs/promises';
import { join } from 'node:path';
import { createInterface } from 'node:readline';
import { Readable } from 'node:stream';
import { createGunzip } from 'node:zlib';

const LEGACY_ENDPOINTS = new Map([
  ['/local/rest/tacticum_offer.php', 'tacticum_offer.php'],
  ['/local/rest/tacticum_sale.php', 'tacticum_sale.php'],
]);

const MONTHS = new Map([
  ['Jan', '01'],
  ['Feb', '02'],
  ['Mar', '03'],
  ['Apr', '04'],
  ['May', '05'],
  ['Jun', '06'],
  ['Jul', '07'],
  ['Aug', '08'],
  ['Sep', '09'],
  ['Oct', '10'],
  ['Nov', '11'],
  ['Dec', '12'],
]);

const DISCOVERY_DIRS = [
  '/var/log/nginx',
  '/var/log/httpd',
  '/var/log/apache2',
  '/usr/local/nginx/logs',
  '/home/bitrix/logs',
];

const KNOWN_OPTIONS = new Set(['--json', '--self-test', '--discover']);
const args = process.argv.slice(2);
const options = args.filter((arg) => arg.startsWith('--'));
const unknownOptions = options.filter((option) => !KNOWN_OPTIONS.has(option));
const jsonOutput = options.includes('--json') || process.env.TACTICUM_LEGACY_LOG_JSON === '1';
const selfTest = options.includes('--self-test');
const discoverOnly = options.includes('--discover');
const argFiles = args.filter((arg) => !arg.startsWith('--'));

if (unknownOptions.length > 0) {
  console.error(`Unknown option: ${unknownOptions.join(', ')}`);
  printUsage();
  process.exit(1);
}

const sourceLabel = sanitizeSourceLabel(process.env.TACTICUM_LEGACY_SOURCE_LABEL || 'production-access-logs');
const windowTz = normalizeTimezone(process.env.TACTICUM_LEGACY_LOG_TZ || '+03:00');
const fromDate = process.env.TACTICUM_LEGACY_LOG_FROM || '2026-05-24';
const toDate = process.env.TACTICUM_LEGACY_LOG_TO || '2026-06-30';
const windowFromMs = parseWindowDate(fromDate, false, windowTz);
const windowToMs = parseWindowDate(toDate, true, windowTz);

if (windowFromMs > windowToMs) {
  console.error('Invalid window: TACTICUM_LEGACY_LOG_FROM must be before TACTICUM_LEGACY_LOG_TO.');
  process.exit(1);
}

if (selfTest) {
  runSelfTest();
} else if (discoverOnly && argFiles.length === 0) {
  await printDiscoveredLogCandidates(jsonOutput);
} else {
  const envFiles = (process.env.TACTICUM_LEGACY_LOG_FILES || '')
    .split(',')
    .map((file) => file.trim())
    .filter(Boolean);
  const files = argFiles.length > 0 ? argFiles : envFiles;

  if (files.length === 0) {
    printUsage();
    process.exit(1);
  }

  const inventory = createInventory();
  const readErrors = [];
  for (const file of files) {
    try {
      await scanFile(file, inventory);
    } catch (error) {
      readErrors.push({ file, error });
    }
  }

  if (readErrors.length > 0) {
    await printReadErrors(readErrors);
    process.exit(1);
  }

  if (jsonOutput) {
    console.log(JSON.stringify(toJson(inventory, files), null, 2));
  } else {
    printHuman(inventory, files);
  }
}

function createInventory() {
  return {
    scannedLines: 0,
    matchedHits: 0,
    skippedOutOfWindow: 0,
    groups: new Map(),
    dailyTotals: new Map(),
  };
}

async function scanFile(file, inventory) {
  const input = createInputStream(file);
  const lines = createInterface({ input, crlfDelay: Infinity });

  for await (const line of lines) {
    consumeLine(line, inventory);
  }
}

function createInputStream(file) {
  if (file === '-') {
    return process.stdin;
  }

  const stream = createReadStream(file);
  if (file.endsWith('.gz')) {
    return stream.pipe(createGunzip());
  }

  return stream;
}

function consumeLine(line, inventory) {
  inventory.scannedLines += 1;

  const hit = parseAccessLine(line);
  if (!hit) {
    return;
  }

  if (hit.timestampMs < windowFromMs || hit.timestampMs > windowToMs) {
    inventory.skippedOutOfWindow += 1;
    return;
  }

  inventory.matchedHits += 1;

  const key = `${hit.endpoint}|${hit.method}|${hit.status}`;
  const existing = inventory.groups.get(key) || {
    sourceLabel,
    endpoint: hit.endpoint,
    method: hit.method,
    status: hit.status,
    count: 0,
    firstSeenAt: hit.timestampLabel,
    firstSeenMs: hit.timestampMs,
    lastSeenAt: hit.timestampLabel,
    lastSeenMs: hit.timestampMs,
    days: new Map(),
  };

  existing.count += 1;
  if (hit.timestampMs < existing.firstSeenMs) {
    existing.firstSeenAt = hit.timestampLabel;
    existing.firstSeenMs = hit.timestampMs;
  }
  if (hit.timestampMs > existing.lastSeenMs) {
    existing.lastSeenAt = hit.timestampLabel;
    existing.lastSeenMs = hit.timestampMs;
  }
  existing.days.set(hit.day, (existing.days.get(hit.day) || 0) + 1);
  inventory.groups.set(key, existing);

  const dailyKey = `${hit.day}|${hit.endpoint}`;
  inventory.dailyTotals.set(dailyKey, (inventory.dailyTotals.get(dailyKey) || 0) + 1);
}

function parseAccessLine(line) {
  const timestamp = parseTimestamp(line);
  if (!timestamp) {
    return null;
  }

  const requestMatch = line.match(/"([A-Z]+)\s+([^"\s]+)\s+HTTP\/[0-9.]+"\s+(\d{3})/);
  if (!requestMatch) {
    return null;
  }

  const method = requestMatch[1].toUpperCase();
  const pathname = normalizeRequestPath(requestMatch[2]);
  const endpoint = LEGACY_ENDPOINTS.get(pathname);
  if (!endpoint) {
    return null;
  }

  return {
    endpoint,
    method,
    status: requestMatch[3],
    timestampMs: timestamp.ms,
    timestampLabel: timestamp.label,
    day: timestamp.day,
  };
}

function parseTimestamp(line) {
  const match = line.match(/\[(\d{2})\/([A-Za-z]{3})\/(\d{4}):(\d{2}):(\d{2}):(\d{2}) ([+-]\d{4})\]/);
  if (!match) {
    return null;
  }

  const [, day, monthName, year, hour, minute, second, rawOffset] = match;
  const month = MONTHS.get(monthName);
  if (!month) {
    return null;
  }

  const offset = normalizeTimezone(rawOffset);
  const date = `${year}-${month}-${day}`;
  const label = `${date}T${hour}:${minute}:${second}${offset}`;
  const ms = Date.parse(label);

  if (Number.isNaN(ms)) {
    return null;
  }

  return { day: date, label, ms };
}

function normalizeRequestPath(rawTarget) {
  try {
    return new URL(rawTarget, 'https://tacticum.ru').pathname;
  } catch {
    return rawTarget.split(/[?#]/)[0];
  }
}

function parseWindowDate(value, endOfDay, timezone) {
  if (!/^\d{4}-\d{2}-\d{2}$/.test(value)) {
    console.error('Invalid date window. Use YYYY-MM-DD in TACTICUM_LEGACY_LOG_FROM / TACTICUM_LEGACY_LOG_TO.');
    process.exit(1);
  }

  const time = endOfDay ? '23:59:59.999' : '00:00:00.000';
  const ms = Date.parse(`${value}T${time}${timezone}`);
  if (Number.isNaN(ms)) {
    console.error(`Invalid date window value: ${value}`);
    process.exit(1);
  }

  return ms;
}

function normalizeTimezone(value) {
  if (/^[+-]\d{2}:\d{2}$/.test(value)) {
    return value;
  }
  if (/^[+-]\d{4}$/.test(value)) {
    return `${value.slice(0, 3)}:${value.slice(3)}`;
  }

  console.error(`Invalid timezone offset: ${value}. Use +03:00 or +0300.`);
  process.exit(1);
}

function sanitizeSourceLabel(value) {
  return String(value)
    .trim()
    .replace(/[^a-zA-Z0-9_.:-]+/g, '-')
    .replace(/^-+|-+$/g, '')
    .slice(0, 80) || 'production-access-logs';
}

async function discoverLogCandidates() {
  const candidates = [];

  for (const dir of DISCOVERY_DIRS) {
    let entries = [];
    try {
      entries = await readdir(dir, { withFileTypes: true });
    } catch {
      continue;
    }

    for (const entry of entries) {
      if (!entry.isFile() && !entry.isSymbolicLink()) {
        continue;
      }

      if (isAccessLogName(entry.name)) {
        candidates.push(join(dir, entry.name));
      }
    }
  }

  return Array.from(new Set(candidates)).sort((left, right) => left.localeCompare(right));
}

function isAccessLogName(name) {
  const lower = name.toLowerCase();
  return lower.includes('access')
    && (
      lower.includes('log')
      || /\.log(?:\.\d+)?(?:\.gz)?$/.test(lower)
      || /_\d{8}(?:\.gz)?$/.test(lower)
      || /\.\d+(?:\.gz)?$/.test(lower)
    );
}

async function printDiscoveredLogCandidates(asJson) {
  const candidates = await discoverLogCandidates();

  if (asJson) {
    console.log(JSON.stringify({ candidates }, null, 2));
    return;
  }

  if (candidates.length === 0) {
    console.log('No access log candidates were discovered in common nginx/apache/BitrixVM locations.');
    console.log('Ask DevOps for the real web access log path, then pass it to npm run legacy:sale:inventory:logs.');
    return;
  }

  console.log('Discovered access log candidates:');
  for (const candidate of candidates) {
    console.log(`- ${candidate}`);
  }
  console.log('');
  console.log('Run the inventory with one or more real paths, for example:');
  console.log(`npm run legacy:sale:inventory:logs -- ${candidates.slice(0, 3).join(' ')}`);
}

async function printReadErrors(readErrors) {
  console.error('Legacy sale access log inventory could not read one or more files.');
  console.error('');

  for (const { file, error } of readErrors) {
    const code = error?.code ? ` (${error.code})` : '';
    console.error(`- ${file}${code}: ${error?.message || 'read failed'}`);
  }

  console.error('');
  if (readErrors.some(({ file }) => file.includes('/path/to/access.log'))) {
    console.error('The /path/to/access.log value is a placeholder from the docs. Replace it with a real access log path from this server.');
    console.error('');
  }

  const candidates = await discoverLogCandidates();
  if (candidates.length > 0) {
    console.error('Discovered access log candidates:');
    for (const candidate of candidates) {
      console.error(`- ${candidate}`);
    }
    console.error('');
    console.error('Example:');
    console.error(`npm run legacy:sale:inventory:logs -- ${candidates.slice(0, 3).join(' ')}`);
  } else {
    console.error('No candidates were discovered in common locations. Try:');
    console.error('find /var/log -maxdepth 3 -type f \\( -iname "*access*log*" -o -iname "*access*.gz" \\) 2>/dev/null');
  }
}

function sortedRows(inventory) {
  return Array.from(inventory.groups.values()).sort((a, b) => {
    const left = `${a.endpoint}|${a.method}|${a.status}`;
    const right = `${b.endpoint}|${b.method}|${b.status}`;
    return left.localeCompare(right);
  });
}

function sortedDailyTotals(inventory) {
  return Array.from(inventory.dailyTotals.entries())
    .map(([key, count]) => {
      const [day, endpoint] = key.split('|');
      return { day, endpoint, count };
    })
    .sort((a, b) => `${a.day}|${a.endpoint}`.localeCompare(`${b.day}|${b.endpoint}`));
}

function formatDays(days) {
  return Array.from(days.entries())
    .sort(([left], [right]) => left.localeCompare(right))
    .map(([day, count]) => `${day}:${count}`)
    .join(', ');
}

function printHuman(inventory, files) {
  console.log('Legacy sale access log inventory');
  console.log(`Window: ${fromDate} - ${toDate} (${windowTz})`);
  console.log(`Source label: ${sourceLabel}`);
  console.log(`Files: ${files.join(', ')}`);
  console.log(`Scanned lines: ${inventory.scannedLines}`);
  console.log(`Matched hits: ${inventory.matchedHits}`);
  console.log(`Skipped out of window: ${inventory.skippedOutOfWindow}`);
  console.log('');

  const rows = sortedRows(inventory);
  if (rows.length === 0) {
    console.log('No legacy sale alias hits in the selected window.');
    console.log('');
    printEvidenceNote();
    return;
  }

  console.log('Source label | Endpoint | Method | Status | Count | First seen | Last seen | Days');
  for (const row of rows) {
    console.log([
      row.sourceLabel,
      row.endpoint,
      row.method,
      row.status,
      row.count,
      row.firstSeenAt,
      row.lastSeenAt,
      formatDays(row.days),
    ].join(' | '));
  }

  const dailyRows = sortedDailyTotals(inventory);
  if (dailyRows.length > 0) {
    console.log('');
    console.log('Daily totals');
    for (const row of dailyRows) {
      console.log(`${row.day} | ${row.endpoint} | ${row.count}`);
    }
  }

  console.log('');
  printEvidenceNote();
}

function printEvidenceNote() {
  console.log('Evidence note: output is aggregate-only; raw IP, query, referrer, cookies and user-agent are intentionally discarded.');
}

function toJson(inventory, files) {
  return {
    window: {
      from: fromDate,
      to: toDate,
      timezone: windowTz,
    },
    source_label: sourceLabel,
    files,
    scanned_lines: inventory.scannedLines,
    matched_hits: inventory.matchedHits,
    skipped_out_of_window: inventory.skippedOutOfWindow,
    groups: sortedRows(inventory).map((row) => ({
      source_label: row.sourceLabel,
      endpoint: row.endpoint,
      method: row.method,
      status: row.status,
      count: row.count,
      first_seen_at: row.firstSeenAt,
      last_seen_at: row.lastSeenAt,
      days: Object.fromEntries(Array.from(row.days.entries()).sort(([left], [right]) => left.localeCompare(right))),
    })),
    daily_totals: sortedDailyTotals(inventory),
    pii_policy: 'aggregate-only; raw IP, query, referrer, cookies and user-agent are discarded',
  };
}

function printUsage() {
  console.error('Usage: npm run legacy:sale:inventory:logs -- /real/path/to/access.log [/real/path/to/access.log.1.gz]');
  console.error('');
  console.error('Environment:');
  console.error('  TACTICUM_LEGACY_LOG_FILES=/real/path/a,/real/path/b');
  console.error('  TACTICUM_LEGACY_LOG_FROM=2026-05-24');
  console.error('  TACTICUM_LEGACY_LOG_TO=2026-06-30');
  console.error('  TACTICUM_LEGACY_SOURCE_LABEL=production-access-logs');
  console.error('  TACTICUM_LEGACY_LOG_JSON=1');
  console.error('');
  console.error('Options:');
  console.error('  --discover   print access log candidates from common nginx/apache/BitrixVM locations');
  console.error('  --json       print machine-readable aggregate JSON');
  console.error('  --self-test  run built-in parser self-test');
}

function runSelfTest() {
  const sample = [
    '203.0.113.10 - - [25/May/2026:10:15:20 +0300] "POST /local/rest/tacticum_offer.php HTTP/1.1" 200 123 "-" "Mozilla"',
    '203.0.113.11 - - [26/May/2026:11:16:21 +0300] "POST /local/rest/tacticum_sale.php?utm_source=test HTTP/1.1" 502 123 "-" "Mozilla"',
    '203.0.113.12 - - [27/May/2026:12:17:22 +0300] "GET /local/rest/tacticum_offer.php HTTP/2.0" 405 123 "-" "Mozilla"',
    '203.0.113.13 - - [01/Jul/2026:12:17:22 +0300] "POST /local/rest/tacticum_offer.php HTTP/1.1" 200 123 "-" "Mozilla"',
    '203.0.113.14 - - [27/May/2026:12:17:22 +0300] "POST /local/rest/tacticum_form.php HTTP/1.1" 200 123 "-" "Mozilla"',
  ];
  const inventory = createInventory();
  const input = Readable.from(sample.join('\n'));
  const lines = createInterface({ input, crlfDelay: Infinity });

  lines.on('line', (line) => consumeLine(line, inventory));
  lines.on('close', () => {
    const rows = sortedRows(inventory);
    const expected = [
      ['tacticum_offer.php', 'GET', '405', 1],
      ['tacticum_offer.php', 'POST', '200', 1],
      ['tacticum_sale.php', 'POST', '502', 1],
    ];

    for (const [endpoint, method, status, count] of expected) {
      const row = rows.find((candidate) => (
        candidate.endpoint === endpoint
        && candidate.method === method
        && candidate.status === status
      ));
      if (!row || row.count !== count) {
        console.error('Legacy sale access log inventory self-test failed.');
        process.exit(1);
      }
    }

    if (inventory.matchedHits !== 3 || inventory.skippedOutOfWindow !== 1) {
      console.error('Legacy sale access log inventory self-test failed.');
      process.exit(1);
    }

    console.log('Legacy sale access log inventory self-test passed.');
  });
}

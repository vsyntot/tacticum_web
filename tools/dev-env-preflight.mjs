import { readdir } from 'node:fs/promises';
import { join } from 'node:path';
import { spawnSync } from 'node:child_process';

const php = findPhp();
const requirePhp = process.env.CI === 'true' || process.env.TACTICUM_REQUIRE_PHP_CLI === '1';

if (!php) {
  console.warn('PHP CLI was not found. Skipping local PHP syntax lint.');
  console.warn('Fallback: GitHub PR check php-lint installs PHP 8.4 and remains required.');
  process.exit(requirePhp ? 1 : 0);
}

const version = spawnSync(php, ['-v'], { encoding: 'utf8' });
if (version.status !== 0) {
  console.error('PHP CLI was found but php -v failed.');
  process.stderr.write(version.stderr || version.stdout || '');
  process.exit(1);
}

const firstLine = (version.stdout || '').split('\n')[0] || php;
console.log(`Using ${firstLine}`);

const phpVersion = parsePhpVersion(firstLine);
if (!phpVersion || phpVersion.major < 8 || (phpVersion.major === 8 && phpVersion.minor < 4)) {
  console.warn('PHP CLI is not PHP 8.4 or newer. Skipping local PHP syntax lint to avoid false negatives.');
  console.warn('Fallback: GitHub PR check php-lint installs PHP 8.4 and remains required.');
  process.exit(requirePhp ? 1 : 0);
}

const phpFiles = await collectPhpFiles([
  'local',
  '.',
  'about',
  'services',
  'contacts',
  'calculator',
  'price',
  'offer',
  'aiagents',
  'policies',
]);
let failures = 0;

for (const file of phpFiles) {
  const result = spawnSync(php, ['-d', 'short_open_tag=1', '-l', file], { encoding: 'utf8' });
  if (result.status !== 0) {
    failures += 1;
    process.stderr.write(result.stdout || '');
    process.stderr.write(result.stderr || '');
  }
}

if (failures > 0) {
  console.error(`PHP syntax lint failed for ${failures} file(s).`);
  process.exit(1);
}

console.log(`PHP syntax lint passed for ${phpFiles.length} local/public file(s).`);

function findPhp() {
  const result = spawnSync('php', ['-v'], { encoding: 'utf8' });
  return result.status === 0 ? 'php' : null;
}

function parsePhpVersion(line) {
  const match = line.match(/PHP\s+(\d+)\.(\d+)/);
  if (!match) {
    return null;
  }

  return {
    major: Number(match[1]),
    minor: Number(match[2]),
  };
}

async function collectPhpFiles(roots) {
  const files = [];
  for (const root of roots) {
    await walk(root, files, root === '.');
  }
  return [...new Set(files)].sort();
}

async function walk(dir, files, rootOnly = false) {
  let entries;
  try {
    entries = await readdir(dir, { withFileTypes: true });
  } catch (error) {
    if (error && error.code === 'ENOENT') {
      return;
    }
    throw error;
  }

  for (const entry of entries) {
    const path = join(dir, entry.name);
    if (entry.isDirectory()) {
      if (rootOnly) {
        continue;
      }
      await walk(path, files);
    } else if (entry.isFile() && entry.name.endsWith('.php')) {
      files.push(path);
    }
  }
}

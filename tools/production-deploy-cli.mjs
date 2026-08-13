#!/usr/bin/env node

import { spawn } from 'node:child_process';
import { readFile, writeFile } from 'node:fs/promises';
import {
  buildCanonicalManifest,
  buildRsyncContract,
  classifyChangedPaths,
  compareManifests,
  loadDeployScope,
  validateLocalPreflight,
} from './production-deploy-contract.mjs';

const [command, ...rawArgs] = process.argv.slice(2);
const options = parseOptions(rawArgs);

try {
  const scope = await loadDeployScope(options.scope || 'tools/deploy-scope.json');

  if (command === 'scope-check') {
    const manifest = await buildCanonicalManifest(options.root || '.', scope, { treeKind: 'candidate' });
    const rsyncContract = buildRsyncContract(scope);
    printJson({
      status: 'ok',
      scopeVersion: scope.scopeVersion,
      entryCount: manifest.entries.length,
      manifestSha256: manifest.manifestSha256,
      tombstones: manifest.tombstones,
      rsyncOperationCount: rsyncContract.authoritativeDirectories.length + 2,
    });
  } else if (command === 'classify') {
    const paths = await readChangedPaths(options);
    const classification = classifyChangedPaths(paths, scope);
    printJson(classification);
    if (!['FILE_ONLY', 'NO_DEPLOYABLE_CHANGE'].includes(classification.releaseClass)) {
      process.exitCode = 2;
    }
  } else if (command === 'manifest') {
    const manifest = await buildCanonicalManifest(options.root || '.', scope, {
      treeKind: options['tree-kind'] || 'candidate',
    });
    await writeJsonOutput(manifest, options.output);
  } else if (command === 'plan') {
    requireOption(options, 'prod');
    requireOption(options, 'candidate');
    const [base, prod, candidate] = await Promise.all([
      options.base ? readJson(options.base) : Promise.resolve(null),
      readJson(options.prod),
      readJson(options.candidate),
    ]);
    const plan = compareManifests({
      base,
      prod,
      candidate,
      scope,
    });
    await writeJsonOutput(plan, options.output);
  } else if (command === 'preflight') {
    const result = await validateLocalPreflight({
      envPath: options.env || scope.localAccess.envFile,
      scope,
    });
    printJson({
      status: result.status,
      sshUser: result.sshUser,
      keyFingerprint: result.keyFingerprint,
      enforcedSshOptions: redactSshArgs(result.sshArgs),
      connectionAttempted: false,
    });
  } else {
    printUsage();
    process.exitCode = command ? 1 : 0;
  }
} catch (error) {
  console.error(`Production deploy contract check failed: ${error.message}`);
  process.exitCode = 1;
}

function parseOptions(args) {
  const result = { _: [] };
  for (let index = 0; index < args.length; index += 1) {
    const value = args[index];
    if (!value.startsWith('--')) {
      result._.push(value);
      continue;
    }
    const equalsIndex = value.indexOf('=');
    if (equalsIndex > 2) {
      result[value.slice(2, equalsIndex)] = value.slice(equalsIndex + 1);
      continue;
    }
    const key = value.slice(2);
    const next = args[index + 1];
    if (next === undefined || next.startsWith('--')) {
      result[key] = true;
    } else {
      result[key] = next;
      index += 1;
    }
  }
  return result;
}

async function readChangedPaths(parsedOptions) {
  if (parsedOptions['paths-file']) {
    return (await readFile(parsedOptions['paths-file'], 'utf8'))
      .split(/\r?\n/)
      .map((line) => line.trim())
      .filter(Boolean);
  }
  if (parsedOptions._.length > 0) {
    return parsedOptions._;
  }
  if (parsedOptions.base && parsedOptions.head) {
    const result = await runCommand('git', [
      'diff',
      '--name-only',
      '--no-renames',
      `${parsedOptions.base}...${parsedOptions.head}`,
      '--',
    ]);
    return result.stdout.split(/\r?\n/).map((line) => line.trim()).filter(Boolean);
  }
  throw new Error('classify requires paths, --paths-file, or both --base and --head');
}

function redactSshArgs(args) {
  return args.map((value) => {
    if (value.startsWith('IdentityFile=')) {
      return 'IdentityFile=<dedicated-key>';
    }
    if (value.startsWith('UserKnownHostsFile=')) {
      return 'UserKnownHostsFile=<dedicated-known-hosts>';
    }
    if (value.includes('@')) {
      return 'bitrix@<production-host>';
    }
    return value;
  });
}

async function readJson(file) {
  return JSON.parse(await readFile(file, 'utf8'));
}

async function writeJsonOutput(value, output) {
  const serialized = `${JSON.stringify(value, null, 2)}\n`;
  if (output) {
    await writeFile(output, serialized, { mode: 0o600, flag: 'wx' });
  } else {
    process.stdout.write(serialized);
  }
}

function printJson(value) {
  process.stdout.write(`${JSON.stringify(value, null, 2)}\n`);
}

function requireOption(parsedOptions, key) {
  if (!parsedOptions[key]) {
    throw new Error(`--${key} is required`);
  }
}

function runCommand(commandName, args) {
  return new Promise((resolve, reject) => {
    const child = spawn(commandName, args, {
      env: { PATH: process.env.PATH || '' },
      shell: false,
      stdio: ['ignore', 'pipe', 'pipe'],
    });
    let stdout = '';
    let stderr = '';
    child.stdout.setEncoding('utf8');
    child.stderr.setEncoding('utf8');
    child.stdout.on('data', (chunk) => { stdout += chunk; });
    child.stderr.on('data', (chunk) => { stderr += chunk; });
    child.on('error', reject);
    child.on('close', (status) => {
      if (status !== 0) {
        reject(new Error(`${commandName} failed with status ${status}: ${stderr.trim()}`));
      } else {
        resolve({ stdout, stderr });
      }
    });
  });
}

function printUsage() {
  console.log(`Usage:
  node tools/production-deploy-cli.mjs scope-check [--root=.]
  node tools/production-deploy-cli.mjs classify <path...>
  node tools/production-deploy-cli.mjs classify --paths-file=<file>
  node tools/production-deploy-cli.mjs classify --base=<ref> --head=<ref>
  node tools/production-deploy-cli.mjs manifest [--root=.] [--tree-kind=candidate] [--output=<new-file>]
  node tools/production-deploy-cli.mjs plan [--base=<manifest>] --prod=<manifest> --candidate=<manifest> [--output=<new-file>]
  node tools/production-deploy-cli.mjs preflight [--env=.env]
`);
}

#!/usr/bin/env node

import { spawn } from 'node:child_process';
import { mkdir, mkdtemp, readFile, rm, writeFile } from 'node:fs/promises';
import { createServer } from 'node:net';
import { dirname, join, relative } from 'node:path';
import { tmpdir } from 'node:os';

const DEFAULT_PAGES = [
  '/',
  '/about/',
  '/services/',
  '/price/',
  '/calculator/',
  '/offer/',
  '/aiagents/',
  '/contacts/',
  '/policies/',
];

const DEFAULT_VIEWPORTS = [
  { name: 'desktop', width: 1440, height: 1200, deviceScaleFactor: 1, mobile: false },
  { name: 'mobile', width: 390, height: 1200, deviceScaleFactor: 2, mobile: true },
];

const chromePath = process.env.CHROME_PATH || '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';
const baseUrl = normalizeBaseUrl(process.env.TACTICUM_VISUAL_BASE_URL || 'https://tacticum.ru');
const outputDir = process.env.TACTICUM_VISUAL_OUTPUT || join(tmpdir(), `tacticum-visual-smoke-${timestamp()}`);
const pages = parseList(process.env.TACTICUM_VISUAL_PAGES, DEFAULT_PAGES);
const viewports = DEFAULT_VIEWPORTS;
const injectedCssFiles = parseList(process.env.TACTICUM_VISUAL_INJECT_CSS, []);
const injectedCss = injectedCssFiles.length > 0
  ? (await Promise.all(injectedCssFiles.map((file) => readCssForInjection(file)))).join('\n')
  : '';
const runActions = isTruthy(process.env.TACTICUM_VISUAL_ACTIONS);

const waitMs = Number.parseInt(process.env.TACTICUM_VISUAL_WAIT_MS || '2500', 10);
const minScreenshotBytes = Number.parseInt(process.env.TACTICUM_VISUAL_MIN_BYTES || '50000', 10);
const maxHorizontalOverflow = Number.parseInt(process.env.TACTICUM_VISUAL_MAX_OVERFLOW || '8', 10);

let chrome;
let userDataDir;

try {
  const port = await getFreePort();
  userDataDir = await mkdtemp(join(tmpdir(), 'tacticum-chrome-'));
  await mkdir(outputDir, { recursive: true });

  chrome = spawn(chromePath, [
    '--headless=new',
    '--disable-gpu',
    '--hide-scrollbars',
    '--no-first-run',
    '--no-default-browser-check',
    '--disable-background-networking',
    `--remote-debugging-port=${port}`,
    `--user-data-dir=${userDataDir}`,
    'about:blank',
  ], { stdio: ['ignore', 'ignore', 'pipe'] });

  chrome.stderr.setEncoding('utf8');
  chrome.stderr.on('data', (chunk) => {
    const text = String(chunk).trim();
    if (process.env.TACTICUM_VISUAL_VERBOSE && text) {
      console.error(text);
    }
  });

  await waitForChrome(port);

  const results = [];
  for (const page of pages) {
    for (const viewport of viewports) {
      const url = new URL(page, baseUrl).toString();
      const label = `${slugify(page || '/')}-${viewport.name}`;
      const screenshotPath = join(outputDir, `${label}.png`);
      const result = await smokePage({ port, url, page, viewport, screenshotPath, injectedCss, runActions });
      results.push(result);
      console.log(formatResult(result));
    }
  }

  const manifestPath = join(outputDir, 'manifest.json');
  await writeFile(manifestPath, `${JSON.stringify({ baseUrl, outputDir, generatedAt: new Date().toISOString(), injectedCssFiles, runActions, results }, null, 2)}\n`);

  const failures = results.filter((result) => result.errors.length > 0);
  console.log(`\nScreenshots: ${outputDir}`);
  console.log(`Manifest: ${manifestPath}`);

  if (failures.length > 0) {
    console.error(`\nVisual smoke failed: ${failures.length} failed checks.`);
    process.exitCode = 1;
  }
} finally {
  if (chrome && !chrome.killed) {
    chrome.kill('SIGTERM');
    await new Promise((resolve) => {
      const timer = setTimeout(resolve, 2000);
      chrome.once('exit', () => {
        clearTimeout(timer);
        resolve();
      });
    });
  }
  if (userDataDir) {
    await rm(userDataDir, { recursive: true, force: true, maxRetries: 5, retryDelay: 200 }).catch(() => {});
  }
}

async function smokePage({ port, url, page, viewport, screenshotPath, injectedCss, runActions }) {
  const target = await createTarget(port, url);
  const cdp = await connectCdp(target.webSocketDebuggerUrl);
  const errors = [];
  const result = {
    page,
    viewport: viewport.name,
    url,
    title: '',
    status: null,
    textLength: 0,
    scrollWidth: 0,
    clientWidth: 0,
    viewportWidth: 0,
    scrollHeight: 0,
    brokenImages: [],
    overflowElements: [],
    consoleErrors: [],
    pageErrors: [],
    networkErrors: [],
    actionErrors: [],
    actions: [],
    screenshotPath,
    screenshotBytes: 0,
    errors,
  };

  let loaded;
  try {
    await cdp.send('Page.enable');
    await cdp.send('Network.enable');
    await cdp.send('Runtime.enable');
    await cdp.send('Log.enable');

    const requests = new Map();
    cdp.on('Network.requestWillBeSent', (params) => {
      requests.set(params.requestId, {
        url: params.request?.url || '',
        type: params.type || 'Other',
      });
    });
    cdp.on('Network.responseReceived', (params) => {
      if (params.type === 'Document' && params.response?.url === url) {
        result.status = params.response.status;
      }
      if (params.response?.status >= 400) {
        addUnique(result.networkErrors, {
          type: params.type || 'Other',
          status: params.response.status,
          url: params.response.url,
        });
      }
    });
    cdp.on('Network.loadingFailed', (params) => {
      if (params.canceled) {
        return;
      }
      const request = requests.get(params.requestId) || {};
      addUnique(result.networkErrors, {
        type: params.type || request.type || 'Other',
        errorText: params.errorText || 'loading failed',
        blockedReason: params.blockedReason || '',
        url: request.url || '',
      });
    });
    cdp.on('Runtime.consoleAPICalled', (params) => {
      if (params.type !== 'error' && params.type !== 'assert') {
        return;
      }
      addUnique(result.consoleErrors, {
        type: params.type,
        text: formatConsoleArgs(params.args),
        url: params.stackTrace?.callFrames?.[0]?.url || '',
        line: params.stackTrace?.callFrames?.[0]?.lineNumber ?? null,
      });
    });
    cdp.on('Runtime.exceptionThrown', (params) => {
      const details = params.exceptionDetails || {};
      addUnique(result.pageErrors, {
        text: details.text || details.exception?.description || details.exception?.value || 'Unhandled exception',
        url: details.url || '',
        line: details.lineNumber ?? null,
        column: details.columnNumber ?? null,
      });
    });
    cdp.on('Log.entryAdded', (params) => {
      const entry = params.entry || {};
      if (entry.level !== 'error') {
        return;
      }
      addUnique(result.consoleErrors, {
        type: entry.source || 'log',
        text: entry.text || 'Log error',
        url: entry.url || '',
        line: entry.lineNumber ?? null,
      });
    });

    await cdp.send('Emulation.setDeviceMetricsOverride', {
      width: viewport.width,
      height: viewport.height,
      deviceScaleFactor: viewport.deviceScaleFactor,
      mobile: viewport.mobile,
    });
    await cdp.send('Emulation.setTouchEmulationEnabled', { enabled: viewport.mobile });

    const loadEvent = cdp.waitFor('Page.loadEventFired', 30000).catch(() => null);
    const domReady = cdp.waitFor('Page.domContentEventFired', 15000);
    await cdp.send('Page.navigate', { url });
    loaded = Promise.race([loadEvent, domReady]);
    await loaded;
    if (injectedCss) {
      await cdp.send('Runtime.evaluate', {
        expression: `(() => {
          const style = document.createElement('style');
          style.setAttribute('data-tacticum-visual-smoke', 'true');
          style.textContent = ${JSON.stringify(injectedCss)};
          document.head.appendChild(style);
        })()`,
      });
    }
    await sleep(waitMs);

    if (runActions) {
      const actionResult = await runActionSmoke(cdp);
      result.actions = actionResult.actions || [];
      result.actionErrors = actionResult.errors || [];
      await sleep(300);
    }

    const metrics = await cdp.send('Runtime.evaluate', {
      returnByValue: true,
      expression: `(() => {
        const doc = document.documentElement;
        const body = document.body;
        const viewportWidth = Math.max(doc.clientWidth, window.innerWidth || 0);
        return {
          title: document.title || '',
          textLength: (body?.innerText || '').trim().length,
          scrollWidth: doc.scrollWidth,
          clientWidth: doc.clientWidth,
          viewportWidth,
          scrollHeight: Math.max(doc.scrollHeight, body ? body.scrollHeight : 0),
          overflowElements: Array.from(document.body.querySelectorAll('*'))
            .map((element) => {
              const rect = element.getBoundingClientRect();
              return { element, rect };
            })
            .filter(({ rect }) => rect.width > 0 && (rect.right > viewportWidth + 8 || rect.left < -8))
            .map(({ element, rect }) => ({
              selector: element.tagName.toLowerCase()
                + (element.id ? '#' + element.id : '')
                + (element.className && typeof element.className === 'string'
                  ? '.' + element.className.trim().split(/\\s+/).slice(0, 4).join('.')
                  : ''),
              left: Math.round(rect.left),
              right: Math.round(rect.right),
              width: Math.round(rect.width)
            }))
            .slice(0, 20),
          brokenImages: Array.from(document.images)
            .filter((img) => img.complete && img.naturalWidth === 0)
            .map((img) => img.currentSrc || img.src || img.alt || '')
            .slice(0, 20)
        };
      })()`,
    });

    Object.assign(result, metrics.result.value);

    const screenshot = await cdp.send('Page.captureScreenshot', {
      format: 'png',
      fromSurface: true,
      captureBeyondViewport: true,
    });
    const png = Buffer.from(screenshot.data, 'base64');
    await writeFile(screenshotPath, png);
    result.screenshotBytes = png.length;

    if (!result.status || result.status >= 400) {
      errors.push(`document status ${result.status || 'unknown'}`);
    }
    if (result.textLength < 200) {
      errors.push(`page text is too short: ${result.textLength}`);
    }
    if (result.screenshotBytes < minScreenshotBytes) {
      errors.push(`screenshot is too small: ${result.screenshotBytes} bytes`);
    }
    const comparisonWidth = Math.max(result.clientWidth, result.viewportWidth);
    if ((result.scrollWidth - comparisonWidth) > maxHorizontalOverflow) {
      const selectors = result.overflowElements.map((item) => item.selector).join(', ');
      errors.push(`horizontal overflow: ${result.scrollWidth} > ${comparisonWidth}${selectors ? ` (${selectors})` : ''}`);
    }
    if (result.brokenImages.length > 0) {
      errors.push(`broken images: ${result.brokenImages.join(', ')}`);
    }
    if (result.pageErrors.length > 0) {
      errors.push(`page errors: ${formatIssueList(result.pageErrors, 'text')}`);
    }
    if (result.consoleErrors.length > 0) {
      errors.push(`console errors: ${formatIssueList(result.consoleErrors, 'text')}`);
    }
    if (result.networkErrors.length > 0) {
      errors.push(`network errors: ${formatNetworkErrors(result.networkErrors)}`);
    }
    if (result.actionErrors.length > 0) {
      errors.push(`action errors: ${result.actionErrors.slice(0, 3).join(' | ')}`);
    }
  } catch (error) {
    errors.push(error instanceof Error ? error.message : String(error));
  } finally {
    await cdp.close();
    await closeTarget(port, target.id);
  }

  return result;
}

async function runActionSmoke(cdp) {
  const evaluation = await cdp.send('Runtime.evaluate', {
    awaitPromise: true,
    returnByValue: true,
    expression: `(() => {
      const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));
      const actions = [];
      const errors = [];
      const path = window.location.pathname;

      const push = (label, status, detail = '') => {
        actions.push({ label, status, detail });
      };

      const fail = (label, error) => {
        const message = error && error.message ? error.message : String(error || 'failed');
        actions.push({ label, status: 'error', detail: message });
        errors.push(label + ': ' + message);
      };

      const find = (selector, scope = document) => scope.querySelector(selector);
      const findAll = (selector, scope = document) => Array.from(scope.querySelectorAll(selector));
      const activate = (element) => {
        if (!element) return;
        if (typeof element.click === 'function') {
          element.click();
          return;
        }
        element.dispatchEvent(new MouseEvent('click', { bubbles: true, cancelable: true, view: window }));
      };

      const run = async (label, fn, required = false) => {
        try {
          const detail = await fn();
          if (detail === false || detail === null) {
            if (required) {
              fail(label, 'required target not found');
            } else {
              push(label, 'skipped', 'target not found');
            }
            return;
          }
          push(label, 'ok', typeof detail === 'string' ? detail : '');
        } catch (error) {
          fail(label, error);
        }
        await sleep(80);
      };

      return (async () => {
        await run('scroll bottom/top', async () => {
          window.scrollTo(0, document.documentElement.scrollHeight || document.body.scrollHeight || 0);
          await sleep(40);
          window.scrollTo(0, 0);
          return 'scrolled';
        }, true);

        await run('mobile menu open/close', async () => {
          const trigger = find('.ri-menu-line')?.closest('button,div,a');
          const menu = find('#tacticum-mobile-menu');
          if (!trigger || !menu) return false;
          activate(trigger);
          await sleep(40);
          const close = find('.tacticum-mobile-menu-close', menu);
          if (close) activate(close);
          return 'menu toggled';
        });

        await run('contact modal open/close', async () => {
          const trigger = find('#contactUsBtn');
          const modal = find('#tacticum-modal');
          if (!trigger || !modal) return false;
          activate(trigger);
          await sleep(40);
          activate(find('#tacticum-modal-close'));
          return 'modal toggled';
        });

        await run('empty form validation', async () => {
          const forms = findAll('[data-tacticum-form]')
            .filter((form) => !form.closest('#tacticum-modal.hidden'))
            .slice(0, 4);
          if (forms.length === 0) return false;
          forms.forEach((form) => {
            form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
          });
          return forms.length + ' forms';
        });

        await run('hero chat empty send', async () => {
          const button = find('#aichat');
          if (!button) return false;
          activate(button);
          return 'empty send';
        }, path === '/');

        await run('light chat empty send', async () => {
          const chat = find('[data-tacticum-chat="light"]');
          if (!chat) return false;
          const input = find('[data-chat-input]', chat);
          const button = find('[data-chat-send]', chat);
          if (!input || !button) return false;
          input.focus();
          input.dispatchEvent(new KeyboardEvent('keydown', { key: 'Enter', bubbles: true, cancelable: true }));
          activate(button);
          return chat.dataset.chatSurface || 'light';
        }, path === '/calculator/' || path === '/price/');

        await run('price filters/search/level', async () => {
          const priceRoot = find('[data-price-list]');
          if (!priceRoot) return false;
          const tabs = findAll('[data-price-filter-tab]', priceRoot);
          if (tabs[1]) activate(tabs[1]);
          if (tabs[0]) activate(tabs[0]);
          const search = find('[data-price-search]', priceRoot);
          if (search) {
            search.value = 'qa smoke';
            search.dispatchEvent(new Event('input', { bubbles: true, cancelable: true }));
            search.value = '';
            search.dispatchEvent(new Event('input', { bubbles: true, cancelable: true }));
          }
          const select = find('[data-price-level-select]', priceRoot);
          if (select && select.options.length > 1) {
            select.selectedIndex = select.selectedIndex === 0 ? 1 : 0;
            select.dispatchEvent(new Event('change', { bubbles: true, cancelable: true }));
          }
          return 'price controls';
        }, path === '/price/');

        await run('price order modal empty submit', async () => {
          const priceRoot = find('[data-price-list]');
          const modal = find('[data-price-order-modal]');
          const button = find('[data-price-order]', priceRoot || document);
          if (!priceRoot || !modal || !button) return false;
          activate(button);
          await sleep(40);
          const form = find('[data-price-order-form]', modal);
          if (form) {
            form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
          }
          activate(find('[data-price-modal-cancel]', modal) || find('[data-price-modal-close]', modal));
          return 'specialist modal';
        }, path === '/price/');

        return { actions, errors };
      })();
    })()`,
  });

  return evaluation.result?.value || { actions: [], errors: ['action smoke did not return result'] };
}

async function createTarget(port, url) {
  const response = await fetch(`http://127.0.0.1:${port}/json/new?${encodeURIComponent(url)}`, { method: 'PUT' });
  if (!response.ok) {
    throw new Error(`Cannot create Chrome target: ${response.status}`);
  }
  return response.json();
}

async function closeTarget(port, targetId) {
  await fetch(`http://127.0.0.1:${port}/json/close/${targetId}`).catch(() => {});
}

async function connectCdp(webSocketDebuggerUrl) {
  const ws = new WebSocket(webSocketDebuggerUrl);
  const pending = new Map();
  const listeners = new Map();
  let id = 0;

  await new Promise((resolve, reject) => {
    ws.addEventListener('open', resolve, { once: true });
    ws.addEventListener('error', reject, { once: true });
  });

  ws.addEventListener('message', (event) => {
    const message = JSON.parse(String(event.data));
    if (message.id && pending.has(message.id)) {
      const { resolve, reject } = pending.get(message.id);
      pending.delete(message.id);
      if (message.error) {
        reject(new Error(message.error.message || JSON.stringify(message.error)));
      } else {
        resolve(message.result || {});
      }
      return;
    }

    const callbacks = listeners.get(message.method);
    if (callbacks) {
      for (const callback of callbacks) {
        callback(message.params || {});
      }
    }
  });

  return {
    send(method, params = {}) {
      const messageId = ++id;
      ws.send(JSON.stringify({ id: messageId, method, params }));
      return new Promise((resolve, reject) => {
        pending.set(messageId, { resolve, reject });
      });
    },
    on(method, callback) {
      if (!listeners.has(method)) {
        listeners.set(method, new Set());
      }
      listeners.get(method).add(callback);
    },
    waitFor(method, timeoutMs) {
      return new Promise((resolve, reject) => {
        const timer = setTimeout(() => {
          listeners.get(method)?.delete(done);
          reject(new Error(`Timed out waiting for ${method}`));
        }, timeoutMs);
        const done = (params) => {
          clearTimeout(timer);
          listeners.get(method)?.delete(done);
          resolve(params);
        };
        this.on(method, done);
      });
    },
    close() {
      ws.close();
    },
  };
}

async function waitForChrome(port) {
  const startedAt = Date.now();
  let lastError;
  while (Date.now() - startedAt < 15000) {
    try {
      const response = await fetch(`http://127.0.0.1:${port}/json/version`);
      if (response.ok) {
        return response.json();
      }
    } catch (error) {
      lastError = error;
    }
    await sleep(250);
  }
  throw new Error(`Chrome did not start: ${lastError?.message || 'timeout'}`);
}

function getFreePort() {
  return new Promise((resolve, reject) => {
    const server = createServer();
    server.listen(0, '127.0.0.1', () => {
      const address = server.address();
      const port = typeof address === 'object' && address ? address.port : 0;
      server.close(() => resolve(port));
    });
    server.on('error', reject);
  });
}

function normalizeBaseUrl(value) {
  return value.endsWith('/') ? value : `${value}/`;
}

function parseList(value, fallback) {
  if (!value) {
    return fallback;
  }
  return value.split(',').map((item) => item.trim()).filter(Boolean);
}

function isTruthy(value) {
  return ['1', 'true', 'yes', 'on'].includes(String(value || '').trim().toLowerCase());
}

function slugify(page) {
  const cleaned = page.replace(/^https?:\/\/[^/]+/i, '').replace(/[^a-z0-9]+/gi, '-').replace(/^-|-$/g, '');
  return cleaned || 'home';
}

function timestamp() {
  return new Date().toISOString().replace(/[:.]/g, '-');
}

function sleep(ms) {
  return new Promise((resolve) => setTimeout(resolve, ms));
}

async function readCssForInjection(file) {
  const css = await readFile(file, 'utf8');
  const cssPath = normalizeWebPath(file);
  const cssDir = dirname(cssPath).replace(/\\/g, '/');
  const cssBaseUrl = `${baseUrl.replace(/\/$/, '')}${cssDir.startsWith('/') ? cssDir : `/${cssDir}`}/`;

  return css.replace(/url\((['"]?)([^'")]+)\1\)/g, (match, quote, rawUrl) => {
    const url = rawUrl.trim();
    if (
      url === '' ||
      url.startsWith('/') ||
      /^[a-z][a-z0-9+.-]*:/i.test(url) ||
      url.startsWith('#')
    ) {
      return match;
    }

    return `url(${quote}${new URL(url, cssBaseUrl).toString()}${quote})`;
  });
}

function normalizeWebPath(file) {
  const normalized = file.replace(/\\/g, '/');
  if (normalized.startsWith('/')) {
    const relativePath = relative(process.cwd(), file).replace(/\\/g, '/');
    return relativePath.startsWith('..') ? normalized : `/${relativePath}`;
  }
  return `/${normalized}`;
}

function formatResult(result) {
  const marker = result.errors.length > 0 ? 'FAIL' : 'OK';
  const status = result.status || 'n/a';
  const runtimeIssues = result.pageErrors.length + result.consoleErrors.length + result.networkErrors.length;
  const actionSummary = result.actions.length > 0
    ? ` actions=${result.actions.filter((action) => action.status === 'ok').length}/${result.actions.length}`
    : '';
  return `${marker} ${result.viewport.padEnd(7)} ${result.page.padEnd(13)} status=${status} text=${result.textLength} bytes=${result.screenshotBytes} runtime=${runtimeIssues}${actionSummary}`;
}

function addUnique(items, item) {
  const key = JSON.stringify(item);
  if (!items.some((existing) => JSON.stringify(existing) === key)) {
    items.push(item);
  }
}

function formatConsoleArgs(args = []) {
  return args
    .map((arg) => arg.description || arg.value || arg.unserializableValue || arg.type || '')
    .filter(Boolean)
    .join(' ')
    .slice(0, 500);
}

function formatIssueList(items, field) {
  return items
    .map((item) => item[field] || 'unknown')
    .filter(Boolean)
    .slice(0, 3)
    .join(' | ');
}

function formatNetworkErrors(items) {
  return items
    .slice(0, 3)
    .map((item) => {
      const reason = item.status || item.errorText || 'failed';
      return `${reason} ${item.url || item.type || 'resource'}`.trim();
    })
    .join(' | ');
}

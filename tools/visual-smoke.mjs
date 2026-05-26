#!/usr/bin/env node

import { spawn } from 'node:child_process';
import { constants } from 'node:fs';
import { access, mkdir, mkdtemp, readFile, rm, writeFile } from 'node:fs/promises';
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

const chromePath = await resolveChromePath();
const baseUrl = normalizeBaseUrl(process.env.TACTICUM_VISUAL_BASE_URL || 'https://tacticum.ru');
const outputDir = process.env.TACTICUM_VISUAL_OUTPUT || join(tmpdir(), `tacticum-visual-smoke-${timestamp()}`);
const pages = parseList(process.env.TACTICUM_VISUAL_PAGES, DEFAULT_PAGES);
const viewports = DEFAULT_VIEWPORTS;
const removeCssPatterns = parseList(process.env.TACTICUM_VISUAL_REMOVE_CSS, []);
const injectedCssFiles = parseList(process.env.TACTICUM_VISUAL_INJECT_CSS, []);
const injectedCss = injectedCssFiles.length > 0
  ? (await Promise.all(injectedCssFiles.map((file) => readCssForInjection(file)))).join('\n')
  : '';
const injectedJsFiles = parseList(process.env.TACTICUM_VISUAL_INJECT_JS, []);
const injectedJs = injectedJsFiles.length > 0
  ? (await Promise.all(injectedJsFiles.map((file) => readFile(file, 'utf8')))).join('\n;\n')
  : '';
const runActions = isTruthy(process.env.TACTICUM_VISUAL_ACTIONS);
const expectSeoHead = isTruthy(process.env.TACTICUM_EXPECT_SEO_HEAD);
const expectPriceTeamPresets = isTruthy(process.env.TACTICUM_EXPECT_PRICE_TEAM_PRESETS);
const failOnWarnings = isTruthy(process.env.TACTICUM_VISUAL_FAIL_ON_WARNINGS);

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
      const result = await smokePage({ port, url, page, viewport, screenshotPath, removeCssPatterns, injectedCss, injectedJs, runActions });
      results.push(result);
      console.log(formatResult(result));
    }
  }

  const manifestPath = join(outputDir, 'manifest.json');
  await writeFile(manifestPath, `${JSON.stringify({ baseUrl, outputDir, generatedAt: new Date().toISOString(), removeCssPatterns, injectedCssFiles, injectedJsFiles, runActions, expectSeoHead, expectPriceTeamPresets, failOnWarnings, results }, null, 2)}\n`);

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

async function smokePage({ port, url, page, viewport, screenshotPath, removeCssPatterns, injectedCss, injectedJs, runActions }) {
  const target = await createTarget(port, 'about:blank');
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
    consoleWarnings: [],
    pageErrors: [],
    networkErrors: [],
    actionErrors: [],
    actions: [],
    seoHead: null,
    seoErrors: [],
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
      if (params.type === 'warning') {
        addUnique(result.consoleWarnings, {
          type: params.type,
          text: formatConsoleArgs(params.args),
          url: params.stackTrace?.callFrames?.[0]?.url || '',
          line: params.stackTrace?.callFrames?.[0]?.lineNumber ?? null,
        });
        return;
      }
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
      if (entry.level === 'warning') {
        addUnique(result.consoleWarnings, {
          type: entry.source || 'log',
          text: entry.text || 'Log warning',
          url: entry.url || '',
          line: entry.lineNumber ?? null,
        });
        return;
      }
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
    const domReady = cdp.waitFor('Page.domContentEventFired', 15000).catch(() => null);
    await cdp.send('Page.navigate', { url });
    loaded = Promise.race([loadEvent, domReady]);
    await loaded;

    let readyState = await getDocumentReadyState(cdp);
    if (readyState !== 'interactive' && readyState !== 'complete') {
      await loadEvent;
      readyState = await getDocumentReadyState(cdp);
    }
    if (readyState !== 'interactive' && readyState !== 'complete') {
      throw new Error(`Timed out waiting for document readiness: ${readyState || 'unknown'}`);
    }
    if (removeCssPatterns.length > 0) {
      await cdp.send('Runtime.evaluate', {
        awaitPromise: true,
        expression: `(() => {
          const patterns = ${JSON.stringify(removeCssPatterns)};
          const matches = (href) => patterns.some((pattern) => href.includes(pattern));
          document
            .querySelectorAll('link[rel~="stylesheet"][href]')
            .forEach((link) => {
              if (matches(link.href || link.getAttribute('href') || '')) {
                link.remove();
              }
            });
        })()`,
      });
    }
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
    if (injectedJs) {
      await cdp.send('Runtime.evaluate', {
        awaitPromise: true,
        expression: `(() => {
          const script = document.createElement('script');
          script.setAttribute('data-tacticum-visual-smoke-js', 'true');
          script.textContent = ${JSON.stringify(injectedJs)};
          document.documentElement.appendChild(script);
          script.remove();
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
        const expectSeoHead = ${expectSeoHead ? 'true' : 'false'};
        const normalizePath = (value) => {
          const normalized = value || '/';
          if (normalized === '/') return '/';
          return normalized.replace(/\\/+$/, '');
        };
        const absoluteUrl = (value) => {
          try {
            return new URL(value, window.location.href);
          } catch {
            return null;
          }
        };
        const head = document.head || document.documentElement;
        const titleElements = Array.from(head.querySelectorAll('title'));
        const descriptions = Array.from(head.querySelectorAll('meta[name]'))
          .filter((meta) => (meta.getAttribute('name') || '').trim().toLowerCase() === 'description')
          .map((meta) => (meta.getAttribute('content') || '').trim());
        const canonicals = Array.from(head.querySelectorAll('link[rel]'))
          .filter((link) => /(^|\\s)canonical(\\s|$)/i.test(link.getAttribute('rel') || ''))
          .map((link) => link.href || link.getAttribute('href') || '')
          .filter(Boolean);
        const ogEntries = Array.from(head.querySelectorAll('meta[property]'))
          .map((meta) => ({
            property: (meta.getAttribute('property') || '').trim().toLowerCase(),
            content: (meta.getAttribute('content') || '').trim()
          }))
          .filter((entry) => entry.property.startsWith('og:'));
        const og = {};
        for (const entry of ogEntries) {
          if (!og[entry.property]) {
            og[entry.property] = [];
          }
          og[entry.property].push(entry.content);
        }
        const duplicateOpenGraphProperties = Object.entries(og)
          .filter(([, values]) => values.length > 1)
          .map(([property]) => property);
        const twitterEntries = Array.from(head.querySelectorAll('meta[name]'))
          .map((meta) => ({
            name: (meta.getAttribute('name') || '').trim().toLowerCase(),
            content: (meta.getAttribute('content') || '').trim()
          }))
          .filter((entry) => entry.name.startsWith('twitter:'));
        const twitter = {};
        for (const entry of twitterEntries) {
          if (!twitter[entry.name]) {
            twitter[entry.name] = [];
          }
          twitter[entry.name].push(entry.content);
        }
        const duplicateTwitterProperties = Object.entries(twitter)
          .filter(([, values]) => values.length > 1)
          .map(([name]) => name);
        const jsonLdScripts = Array.from(head.querySelectorAll('script[type="application/ld+json"]'));
        const jsonLd = jsonLdScripts.map((script) => {
          try {
            const parsed = JSON.parse(script.textContent || '{}');
            const graph = Array.isArray(parsed?.['@graph']) ? parsed['@graph'] : [];
            const types = graph
              .map((item) => item?.['@type'])
              .filter(Boolean)
              .flat();
            return {
              valid: true,
              types
            };
          } catch (error) {
            return {
              valid: false,
              error: error instanceof Error ? error.message : String(error)
            };
          }
        });
        const navLinks = Array.from(document.querySelectorAll('header nav a[href], #tacticum-mobile-menu nav a[href]'))
          .map((link) => absoluteUrl(link.href || link.getAttribute('href') || ''))
          .filter(Boolean)
          .map((url) => normalizePath(url.pathname))
          .filter((path, index, paths) => path && paths.indexOf(path) === index);
        const seoHead = {
          title: document.title || '',
          titleCount: titleElements.length,
          descriptions,
          canonicals,
          openGraph: og,
          duplicateOpenGraphProperties,
          twitter,
          duplicateTwitterProperties,
          jsonLdCount: jsonLdScripts.length,
          jsonLd,
          navLinks,
          h1Count: document.querySelectorAll('h1').length
        };
        const seoErrors = [];
        const pushSeoError = (message) => {
          if (expectSeoHead) {
            seoErrors.push(message);
          }
        };
        if (seoHead.titleCount !== 1) {
          pushSeoError('expected exactly one title tag, got ' + seoHead.titleCount);
        }
        if (seoHead.title.trim().length < 5) {
          pushSeoError('title is missing or too short');
        }
        if (descriptions.length !== 1) {
          pushSeoError('expected exactly one meta description, got ' + descriptions.length);
        } else if (descriptions[0].length < 20) {
          pushSeoError('meta description is too short');
        }
        if (seoHead.h1Count !== 1) {
          pushSeoError('expected exactly one h1, got ' + seoHead.h1Count);
        }
        if (canonicals.length !== 1) {
          pushSeoError('expected exactly one canonical link, got ' + canonicals.length);
        } else {
          const canonicalUrl = absoluteUrl(canonicals[0]);
          if (!canonicalUrl) {
            pushSeoError('canonical href is invalid');
          } else {
            if (canonicalUrl.protocol !== 'https:') {
              pushSeoError('canonical href is not HTTPS');
            }
            if (normalizePath(canonicalUrl.pathname) !== normalizePath(window.location.pathname)) {
              pushSeoError('canonical path does not match rendered URL path');
            }
          }
        }
        const requiredOpenGraph = ['og:site_name', 'og:type', 'og:url', 'og:title', 'og:description', 'og:image', 'og:image:width', 'og:image:height', 'og:image:type'];
        for (const property of requiredOpenGraph) {
          const values = og[property] || [];
          if (values.length !== 1) {
            pushSeoError('expected exactly one ' + property + ', got ' + values.length);
          } else if (!values[0]) {
            pushSeoError(property + ' content is empty');
          }
        }
        if (duplicateOpenGraphProperties.length > 0) {
          pushSeoError('duplicate OpenGraph properties: ' + duplicateOpenGraphProperties.join(', '));
        }
        const requiredTwitter = ['twitter:card', 'twitter:title', 'twitter:description', 'twitter:image'];
        for (const name of requiredTwitter) {
          const values = twitter[name] || [];
          if (values.length !== 1) {
            pushSeoError('expected exactly one ' + name + ', got ' + values.length);
          } else if (!values[0]) {
            pushSeoError(name + ' content is empty');
          }
        }
        if (duplicateTwitterProperties.length > 0) {
          pushSeoError('duplicate Twitter properties: ' + duplicateTwitterProperties.join(', '));
        }
        if (jsonLdScripts.length < 1) {
          pushSeoError('expected at least one JSON-LD script');
        }
        const invalidJsonLd = jsonLd.filter((item) => !item.valid);
        if (invalidJsonLd.length > 0) {
          pushSeoError('invalid JSON-LD: ' + invalidJsonLd.map((item) => item.error).join(', '));
        }
        const ogUrl = absoluteUrl((og['og:url'] || [])[0] || '');
        if (ogUrl) {
          if (ogUrl.protocol !== 'https:') {
            pushSeoError('og:url is not HTTPS');
          }
          if (normalizePath(ogUrl.pathname) !== normalizePath(window.location.pathname)) {
            pushSeoError('og:url path does not match rendered URL path');
          }
        }
        const ogImage = absoluteUrl((og['og:image'] || [])[0] || '');
        if (ogImage && ogImage.protocol !== 'https:') {
          pushSeoError('og:image is not HTTPS');
        }
        const requiredNavigationPaths = ['/price/', '/offer/', '/calculator/', '/aiagents/'].map(normalizePath);
        for (const path of requiredNavigationPaths) {
          if (!navLinks.includes(path)) {
            pushSeoError('top navigation is missing money page ' + path);
          }
        }
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
            .slice(0, 20),
          seoHead,
          seoErrors
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
    if (failOnWarnings && result.consoleWarnings.length > 0) {
      errors.push(`console warnings: ${formatIssueList(result.consoleWarnings, 'text')}`);
    }
    if (result.networkErrors.length > 0) {
      errors.push(`network errors: ${formatNetworkErrors(result.networkErrors)}`);
    }
    if (result.actionErrors.length > 0) {
      errors.push(`action errors: ${result.actionErrors.slice(0, 3).join(' | ')}`);
    }
    if (result.seoErrors.length > 0) {
      errors.push(`seo head errors: ${result.seoErrors.slice(0, 3).join(' | ')}`);
    }
  } catch (error) {
    errors.push(error instanceof Error ? error.message : String(error));
  } finally {
    await cdp.close();
    await closeTarget(port, target.id);
  }

  return result;
}

async function getDocumentReadyState(cdp) {
  const response = await cdp.send('Runtime.evaluate', {
    expression: 'document.readyState',
    returnByValue: true,
  }).catch(() => null);

  return response?.result?.value || '';
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
      const expectPriceTeamPresets = ${expectPriceTeamPresets ? 'true' : 'false'};

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
      const isVisible = (element) => {
        if (!element) return false;
        const style = window.getComputedStyle(element);
        const rect = element.getBoundingClientRect();
        return style.display !== 'none'
          && style.visibility !== 'hidden'
          && !element.classList.contains('hidden')
          && rect.width > 0
          && rect.height > 0;
      };
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

        await run('contacts message label clearance', async () => {
          const form = find('#contacts-cta-form');
          const textarea = form?.querySelector('textarea[name="message"]');
          if (!form || !textarea) return false;
          const label = findAll('label', form).find((candidate) => candidate.htmlFor === textarea.id) || null;
          if (!label) {
            throw new Error('message label is missing');
          }
          textarea.focus();
          textarea.value = 'Smoke label check';
          textarea.dispatchEvent(new Event('input', { bubbles: true, cancelable: true }));
          await sleep(80);
          const textareaRect = textarea.getBoundingClientRect();
          const labelRect = label.getBoundingClientRect();
          if (labelRect.bottom > textareaRect.top - 2) {
            throw new Error('message label still overlaps textarea text area');
          }
          textarea.value = '';
          textarea.dispatchEvent(new Event('input', { bubbles: true, cancelable: true }));
          return 'label clear';
        }, path === '/contacts/');

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

        await run('light chat scroll constraint', async () => {
          const chat = find('[data-tacticum-chat="light"]');
          if (!chat) return false;
          const messages = find('[data-chat-messages]', chat);
          if (!messages) return false;
          for (let index = 0; index < 12; index += 1) {
            const item = document.createElement('div');
            item.className = 'bg-primary/10 rounded-lg p-4';
            item.textContent = 'Smoke message ' + index + ' '.repeat(10) + 'проверяет внутреннюю прокрутку чата.';
            messages.appendChild(item);
          }
          await sleep(80);
          if (messages.scrollHeight <= messages.clientHeight + 20) {
            throw new Error('light chat messages are not scrollable after overflow');
          }
          const chatHeight = Math.round(chat.getBoundingClientRect().height);
          if (chatHeight > 680) {
            throw new Error('light chat grew beyond constrained height: ' + chatHeight);
          }
          return 'height=' + chatHeight;
        }, path === '/calculator/' || path === '/price/');

        await run('price filters/search/level', async () => {
          const priceRoot = find('[data-price-list]') || find('.pricing-card')?.closest('section, main, body');
          if (!priceRoot) return false;
          const cards = findAll('[data-price-card], .pricing-card', priceRoot);
          const visibleCards = () => cards.filter(isVisible);
          const initialVisible = visibleCards().length;
          if (initialVisible === 0) {
            throw new Error('no visible price cards');
          }
          const tabs = findAll('[data-price-filter-tab]', priceRoot).length
            ? findAll('[data-price-filter-tab]', priceRoot)
            : findAll('.filter-tab', priceRoot);
          if (tabs[1]) {
            const targetCategory = tabs[1].dataset.category || '';
            activate(tabs[1]);
            await sleep(80);
            const wrongCategory = visibleCards().find((card) => targetCategory && targetCategory !== 'all' && card.dataset.category !== targetCategory);
            if (wrongCategory) {
              throw new Error('tab filter did not hide other categories');
            }
          }
          if (tabs[0]) {
            activate(tabs[0]);
            await sleep(80);
            if (visibleCards().length === 0) {
              throw new Error('all tab did not restore cards');
            }
          }
          const search = find('[data-price-search]', priceRoot) || find('#specialist-search', priceRoot);
          if (search) {
            search.value = 'qa smoke unmatched value';
            search.dispatchEvent(new Event('input', { bubbles: true, cancelable: true }));
            await sleep(80);
            if (visibleCards().length !== 0) {
              throw new Error('search did not hide unmatched cards');
            }
            const emptyState = find('[data-price-empty]', priceRoot);
            if (emptyState && !isVisible(emptyState)) {
              throw new Error('empty price search state did not become visible');
            }
            search.value = '';
            search.dispatchEvent(new Event('input', { bubbles: true, cancelable: true }));
            await sleep(80);
            if (visibleCards().length === 0) {
              throw new Error('search clear did not restore cards');
            }
          }
          const levelOptions = findAll('[data-price-level-option]', priceRoot);
          const levelOption = levelOptions.find((option) => {
            const card = option.closest('[data-price-card], .pricing-card');
            const active = card?.querySelector('[data-price-level-option][data-active="true"]');
            return active && option !== active && option.dataset.price !== active.dataset.price;
          }) || levelOptions.find((option) => option.dataset.active !== 'true');
          if (levelOption) {
            const card = levelOption.closest('[data-price-card], .pricing-card');
            const active = card?.querySelector('[data-price-level-option][data-active="true"]');
            const priceBlock = card?.querySelector('[data-price-value], .price-value');
            const previousText = priceBlock?.textContent || '';
            const previousPrice = active?.dataset.price || '';
            const nextPrice = levelOption.dataset.price || '';
            activate(levelOption);
            await sleep(80);
            if (levelOption.dataset.active !== 'true' || levelOption.getAttribute('aria-pressed') !== 'true') {
              throw new Error('level segmented control did not activate option');
            }
            if (previousPrice !== nextPrice && priceBlock && priceBlock.textContent === previousText) {
              throw new Error('level segmented control did not update price');
            }
          } else {
            const select = find('[data-price-level-select]', priceRoot) || find('.level-select', priceRoot);
            if (select && select.options.length > 1) {
              const card = select.closest('[data-price-card], .pricing-card');
              const priceBlock = card?.querySelector('[data-price-value], .price-value');
              const previousText = priceBlock?.textContent || '';
              const previousPrice = select.selectedOptions?.[0]?.dataset.price || '';
              select.selectedIndex = select.selectedIndex === 0 ? 1 : 0;
              select.dispatchEvent(new Event('change', { bubbles: true, cancelable: true }));
              await sleep(80);
              const nextPrice = select.selectedOptions?.[0]?.dataset.price || '';
              if (previousPrice !== nextPrice && priceBlock && priceBlock.textContent === previousText) {
                throw new Error('level select did not update price');
              }
            }
          }
          return 'price controls';
        }, path === '/price/');

        await run('price team presets/summary', async () => {
          const priceRoot = find('[data-price-list]');
          if (!priceRoot) return false;
          const preset = find('[data-price-team-preset]', priceRoot);
          const summary = find('[data-price-team-summary]', priceRoot);
          const modal = find('[data-price-order-modal]') || find('#specialistOrderModal');
          if (!preset || !summary || !modal) {
            if (expectPriceTeamPresets) {
              throw new Error('team preset controls are missing');
            }
            return false;
          }

          activate(preset);
          await sleep(120);
          if (summary.classList.contains('hidden') || !isVisible(summary)) {
            throw new Error('team preset did not show persistent summary');
          }
          const summaryText = find('[data-price-team-summary-text]', summary)?.textContent || '';
          if (!/специалист/.test(summaryText)) {
            throw new Error('team summary does not contain selected specialists');
          }

          const open = find('[data-price-team-summary-open]', summary);
          if (open) {
            activate(open);
            await sleep(80);
            if (modal.classList.contains('opacity-0') || modal.classList.contains('pointer-events-none')) {
              throw new Error('summary open action did not open modal');
            }
          }

          const workload = find('#orderWorkload', modal);
          const budget = find('[data-price-team-summary-budget]', summary);
          let budgetText = budget?.textContent?.trim() || '';
          if (workload && budget) {
            workload.value = 'full-time';
            workload.dispatchEvent(new Event('change', { bubbles: true, cancelable: true }));
            await sleep(80);
            budgetText = budget.textContent?.trim() || '';
            if (!/₽\\/мес/.test(budget.textContent || '')) {
              throw new Error('monthly budget estimate did not update');
            }
          }

          const workersJson = find('[data-price-order-workers]', modal) || find('#orderWorkersJson', modal);
          let workersCount = null;
          if (workersJson) {
            const workers = JSON.parse(workersJson.value || '[]');
            if (!Array.isArray(workers) || workers.length === 0) {
              throw new Error('preset did not populate workers_json');
            }
            workersCount = workers.length;
          }

          activate(find('[data-price-modal-cancel]', modal) || find('[data-price-modal-close]', modal) || find('#cancelOrderModal', modal) || find('#closeOrderModal', modal));
          return 'workers=' + (workersCount === null ? 'n/a' : workersCount) + (budgetText ? '; budget=' + budgetText : '');
        }, path === '/price/' && expectPriceTeamPresets);

        await run('price order modal empty submit', async () => {
          const priceRoot = find('[data-price-list]') || find('.pricing-card')?.closest('section, main, body');
          const modal = find('[data-price-order-modal]') || find('#specialistOrderModal');
          const buttons = findAll('[data-price-order], .order-specialist-btn', priceRoot || document).filter(isVisible);
          const button = buttons[0] || null;
          if (!priceRoot || !modal || !button) return false;
          const card = button.closest('[data-price-card], .pricing-card');
          const expectedSpecialist = card?.dataset.name || card?.querySelector('h3')?.textContent?.trim() || '';
          activate(button);
          await sleep(80);
          if (modal.classList.contains('opacity-0') || modal.classList.contains('pointer-events-none')) {
            throw new Error('modal did not open');
          }
          const selected = find('[data-price-selected-specialist]', modal) || find('#selectedSpecialist', modal);
          if (selected && expectedSpecialist && !selected.textContent.includes(expectedSpecialist)) {
            throw new Error('selected specialist was not populated');
          }
          const addMore = find('[data-price-order-add-more]', modal);
          if (addMore && buttons[1]) {
            activate(addMore);
            await sleep(80);
            activate(buttons[1]);
            await sleep(80);
            const count = find('[data-price-order-count]', modal);
            const countText = count?.textContent || '';
            if (count && !/2|3|4|5|6|7|8|9/.test(countText)) {
              throw new Error('multi specialist order count did not update');
            }
          }
          const duration = find('#orderDuration', modal);
          const endDate = find('#orderEndDate', modal);
          if (duration && endDate) {
            duration.value = 'exact-date';
            duration.dispatchEvent(new Event('change', { bubbles: true, cancelable: true }));
            await sleep(80);
            const endDateWrap = endDate.closest('[data-price-end-date-wrap]');
            if (endDateWrap?.classList.contains('hidden') || !endDate.required) {
              throw new Error('exact end date field did not become required');
            }
            endDate.value = '2026-06-30';
            endDate.dispatchEvent(new Event('input', { bubbles: true, cancelable: true }));
            endDate.dispatchEvent(new Event('change', { bubbles: true, cancelable: true }));
          }
          const form = find('[data-price-order-form]', modal) || find('#specialistOrderForm', modal);
          if (form) {
            form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
          }
          activate(find('[data-price-modal-cancel]', modal) || find('[data-price-modal-close]', modal) || find('#cancelOrderModal', modal) || find('#closeOrderModal', modal));
          await sleep(80);
          if (!modal.classList.contains('opacity-0') && !modal.classList.contains('pointer-events-none')) {
            throw new Error('modal did not close');
          }
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

async function resolveChromePath() {
  const candidates = [
    process.env.CHROME_PATH,
    '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
    '/Applications/Chromium.app/Contents/MacOS/Chromium',
    '/usr/bin/google-chrome',
    '/usr/bin/google-chrome-stable',
    '/usr/bin/chromium',
    '/usr/bin/chromium-browser',
    '/snap/bin/chromium',
    '/opt/google/chrome/chrome',
  ].filter(Boolean);

  for (const candidate of candidates) {
    if (await isExecutable(candidate)) {
      return candidate;
    }
  }

  throw new Error(`Chrome executable not found. Set CHROME_PATH or install Chrome/Chromium. Tried: ${candidates.join(', ')}`);
}

async function isExecutable(file) {
  try {
    await access(file, constants.X_OK);
    return true;
  } catch {
    return false;
  }
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
  const warningSummary = result.consoleWarnings.length > 0 ? ` warnings=${result.consoleWarnings.length}` : '';
  const seoSummary = result.seoHead ? ` seo=${result.seoErrors.length > 0 ? 'bad' : 'ok'}` : '';
  const actionSummary = result.actions.length > 0
    ? ` actions=${result.actions.filter((action) => action.status === 'ok').length}/${result.actions.length}`
    : '';
  return `${marker} ${result.viewport.padEnd(7)} ${result.page.padEnd(13)} status=${status} text=${result.textLength} bytes=${result.screenshotBytes} runtime=${runtimeIssues}${warningSummary}${seoSummary}${actionSummary}`;
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

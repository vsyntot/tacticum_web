#!/usr/bin/env node

import { existsSync, readFileSync } from 'node:fs';

const failures = [];

function read(path) {
  if (!existsSync(path)) {
    failures.push(`Missing required file: ${path}`);
    return '';
  }

  return readFileSync(path, 'utf8');
}

function requirePattern(path, pattern, label) {
  const source = read(path);
  if (source && !pattern.test(source)) {
    failures.push(`${path}: missing ${label}.`);
  }
}

function forbidPattern(path, pattern, label) {
  const source = read(path);
  if (source && pattern.test(source)) {
    failures.push(`${path}: forbidden ${label}.`);
  }
}

function requireSourcePattern(label, source, pattern) {
  if (!pattern.test(source)) {
    failures.push(`AI agents boundary: missing ${label}.`);
  }
}

const agentsIndex = read('agents/index.php');
const aiagentsIndex = read('aiagents/index.php');
const aiagentsComponent = read('local/components/tacticum/aiagents/component.php');
const aiagentsDemoList = read('local/components/tacticum/aiagents/templates/.default/parts/demoagents-list.php');
const aiagentsBridge = read('local/components/tacticum/aiagents/templates/.default/parts/agents-bridge.php');
const aiagentsHero = read('local/components/tacticum/aiagents/templates/.default/parts/hero.php');
const aiagentsContactForm = read('local/components/tacticum/aiagents/templates/.default/parts/contact-form.php');
const pageContentSeed = read('tools/content-storage-page-content-seed.php');
const aiagentsBridgeSource = `${aiagentsBridge}\n${pageContentSeed}`;
const aiagentsTemplate = [
  read('local/components/tacticum/aiagents/templates/.default/template.php'),
  aiagentsDemoList,
  aiagentsBridge,
  aiagentsHero,
  read('local/components/tacticum/aiagents/templates/.default/parts/how-it-works.php'),
  read('local/components/tacticum/aiagents/templates/.default/parts/services.php'),
  read('local/components/tacticum/aiagents/templates/.default/parts/demo.php'),
  aiagentsContactForm,
  read('local/components/tacticum/aiagents/templates/.default/parts/faq.php'),
].join('\n');

requireSourcePattern('/agents/ renders product.page', agentsIndex, /['"]tacticum:product\.page['"]/);
requireSourcePattern('/agents/ product code is agents', agentsIndex, /['"]PRODUCT_CODE['"]\s*=>\s*['"]agents['"]/);
requireSourcePattern('/agents/ canonical is /agents/', agentsIndex, /['"]CANONICAL_PATH['"]\s*=>\s*['"]\/agents\/['"]/);
requireSourcePattern('/agents/ prepares product data before header', agentsIndex, /['"]PREPARE_ONLY['"]\s*=>\s*['"]Y['"]/);
forbidPattern('agents/index.php', /tacticum:aiagents|tacticum_iblock_id\(['"]aiagents['"]\)/, '/agents/ depending on aiagents catalog');

requireSourcePattern('/aiagents/ renders aiagents component', aiagentsIndex, /['"]tacticum:aiagents['"]/);
requireSourcePattern('/aiagents/ canonical defaults stay /aiagents/', aiagentsIndex, /tacticum_apply_seo_defaults\(\s*['"]\/aiagents\/['"]/);
requireSourcePattern('/aiagents/ uses aiagents iblock registry key', aiagentsIndex, /['"]IBLOCK_ID['"]\s*=>\s*tacticum_iblock_id\(['"]aiagents['"]\)/);
requireSourcePattern('/aiagents/ service schema', aiagentsIndex, /['"]@type['"]\s*=>\s*['"]Service['"]/);
requireSourcePattern('/aiagents/ prototype service type', aiagentsIndex, /Telegram bot prototype and service route/);
forbidPattern('aiagents/index.php', /['"]PRODUCT_CODE['"]|tacticum:product\.page|APPLICATION_CATEGORY|SoftwareApplication/, '/aiagents/ acting as product page');

requireSourcePattern('aiagents component exposes iblock result', aiagentsComponent, /AIAGENTS_IBLOCK_ID/);
requireSourcePattern('aiagents component keeps FAQ params explicit', aiagentsComponent, /FAQ_IBLOCK_ID[\s\S]*FAQ_SECTION_KEY/);
requireSourcePattern('demo agents list uses aiagents template', aiagentsDemoList, /['"]NEWS_LIST_TEMPLATE['"]\s*=>\s*['"]aiagents['"]/);
requireSourcePattern('demo agents list reads aiagents iblock id from component result', aiagentsDemoList, /\$arResult\['AIAGENTS_IBLOCK_ID'\]/);
requireSourcePattern('demo agents list requests PRODUCT relation for future owner tagging', aiagentsDemoList, /['"]PROPERTY_CODE['"]\s*=>\s*\[[^\]]*['"]LINK['"][^\]]*['"]PRODUCT['"][^\]]*\]/);
forbidPattern('local/components/tacticum/aiagents/templates/.default/parts/demoagents-list.php', /product_data\/agents|PRODUCT_CODE|tacticum_product_page_data/, 'product Agents source usage inside demo catalog');

requireSourcePattern('aiagents bridge points to product Agents', aiagentsBridgeSource, /href["']?\s*=>\s*["']\/agents\/["'][\s\S]*(?:Agents pilot|Tacticum Agents)|href=["']\/agents\/["'][\s\S]*Смотреть Tacticum Agents/);
requireSourcePattern('aiagents bridge keeps Telegram demo option', aiagentsBridgeSource, /href["']?\s*=>\s*["']#demo["'][\s\S]*(?:Telegram|прототип)|Остаться в Telegram-демо/);
requireSourcePattern('aiagents hero positions page as bot demo/prototype', aiagentsHero, /Telegram[\s\S]*прототип|прототип[\s\S]*Telegram/);
requireSourcePattern('aiagents lead product routes to agents', aiagentsContactForm, /name=["']lead_product["']\s+value=["']agents["']/);

if (/SoftwareApplication|aggregateRating|priceCurrency|['"]@type['"]\s*=>\s*['"]Product['"]|['"]offers['"]/.test(aiagentsTemplate)) {
  failures.push('AI agents template must not publish product/commercial proof schema terms; /aiagents/ schema is owned by page SEO setup.');
}

if (failures.length > 0) {
  console.error('Content storage AI agents boundary check failed:');
  for (const failure of failures) {
    console.error(`- ${failure}`);
  }
  process.exit(1);
}

console.log('Content storage AI agents boundary check passed.');

#!/usr/bin/env node
import fs from 'node:fs';
import path from 'node:path';
import process from 'node:process';

const ROOT = path.resolve(path.dirname(new URL(import.meta.url).pathname), '..');
const DEFAULT_SCHEMA = path.join(ROOT, 'docs/workflow/product-content-schema-v1.json');
const DEFAULT_DATA_DIR = path.join(ROOT, 'local/php_interface/include/product_data');

class PhpArrayParser {
  constructor(source) {
    this.tokens = tokenizePhpArray(source);
    this.index = 0;
  }

  parse() {
    const value = this.parseValue();
    this.skipComma();
    if (this.index < this.tokens.length) {
      throw new Error(`Unexpected token after array: ${this.peek().value}`);
    }
    return value;
  }

  parseValue() {
    const token = this.next();
    if (!token) {
      throw new Error('Unexpected end of input.');
    }
    if (token.type === 'symbol' && token.value === '[') {
      return this.parseArray();
    }
    if (token.type === 'string' || token.type === 'number') {
      return token.value;
    }
    if (token.type === 'identifier') {
      if (token.value === 'true') return true;
      if (token.value === 'false') return false;
      if (token.value === 'null') return null;
    }
    throw new Error(`Unexpected token: ${token.value}`);
  }

  parseArray() {
    const entries = [];
    let hasExplicitKey = false;

    while (true) {
      if (this.peek()?.type === 'symbol' && this.peek()?.value === ']') {
        this.next();
        break;
      }

      const first = this.parseValue();
      if (this.peek()?.type === 'arrow') {
        this.next();
        hasExplicitKey = true;
        entries.push({ key: String(first), value: this.parseValue() });
      } else {
        entries.push({ key: null, value: first });
      }

      if (this.peek()?.type === 'symbol' && this.peek()?.value === ',') {
        this.next();
        continue;
      }
      if (this.peek()?.type === 'symbol' && this.peek()?.value === ']') {
        this.next();
        break;
      }
      throw new Error(`Expected comma or closing bracket, got: ${this.peek()?.value ?? 'EOF'}`);
    }

    if (!hasExplicitKey) {
      return entries.map((entry) => entry.value);
    }

    const object = {};
    for (const entry of entries) {
      if (entry.key === null) {
        throw new Error('Mixed keyed and unkeyed arrays are not supported in product data.');
      }
      object[entry.key] = entry.value;
    }
    return object;
  }

  peek() {
    return this.tokens[this.index] ?? null;
  }

  next() {
    return this.tokens[this.index++] ?? null;
  }

  skipComma() {
    if (this.peek()?.type === 'symbol' && this.peek()?.value === ',') {
      this.next();
    }
  }
}

function tokenizePhpArray(source) {
  const tokens = [];
  let i = 0;

  while (i < source.length) {
    const char = source[i];
    if (/\s/.test(char)) {
      i += 1;
      continue;
    }
    if (source.startsWith('//', i)) {
      const nextLine = source.indexOf('\n', i + 2);
      i = nextLine === -1 ? source.length : nextLine + 1;
      continue;
    }
    if (source.startsWith('/*', i)) {
      const end = source.indexOf('*/', i + 2);
      if (end === -1) throw new Error('Unclosed block comment.');
      i = end + 2;
      continue;
    }
    if (source.startsWith('=>', i)) {
      tokens.push({ type: 'arrow', value: '=>' });
      i += 2;
      continue;
    }
    if (char === '[' || char === ']' || char === ',') {
      tokens.push({ type: 'symbol', value: char });
      i += 1;
      continue;
    }
    if (char === "'" || char === '"') {
      const { value, nextIndex } = readQuotedString(source, i);
      tokens.push({ type: 'string', value });
      i = nextIndex;
      continue;
    }
    if (/[0-9-]/.test(char)) {
      const match = source.slice(i).match(/^-?\d+(?:\.\d+)?/);
      if (!match) throw new Error(`Invalid number at offset ${i}.`);
      tokens.push({ type: 'number', value: Number(match[0]) });
      i += match[0].length;
      continue;
    }
    if (/[A-Za-z_]/.test(char)) {
      const match = source.slice(i).match(/^[A-Za-z_][A-Za-z0-9_]*/);
      tokens.push({ type: 'identifier', value: match[0].toLowerCase() });
      i += match[0].length;
      continue;
    }
    throw new Error(`Unsupported character ${JSON.stringify(char)} at offset ${i}.`);
  }

  return tokens;
}

function readQuotedString(source, start) {
  const quote = source[start];
  let value = '';
  let i = start + 1;

  while (i < source.length) {
    const char = source[i];
    if (char === '\\') {
      const next = source[i + 1];
      if (next === undefined) throw new Error('Unclosed escape sequence.');
      value += next;
      i += 2;
      continue;
    }
    if (char === quote) {
      return { value, nextIndex: i + 1 };
    }
    value += char;
    i += 1;
  }

  throw new Error('Unclosed quoted string.');
}

function extractReturnedArray(source) {
  const matches = [...source.matchAll(/return\s*\[/g)];
  if (matches.length === 0) {
    throw new Error('No return [...] array found.');
  }
  const start = matches[matches.length - 1].index + matches[matches.length - 1][0].indexOf('[');
  let depth = 0;
  let quote = null;
  let escaped = false;

  for (let i = start; i < source.length; i += 1) {
    const char = source[i];
    if (quote) {
      if (escaped) {
        escaped = false;
      } else if (char === '\\') {
        escaped = true;
      } else if (char === quote) {
        quote = null;
      }
      continue;
    }
    if (char === "'" || char === '"') {
      quote = char;
      continue;
    }
    if (char === '[') depth += 1;
    if (char === ']') {
      depth -= 1;
      if (depth === 0) {
        return source.slice(start, i + 1);
      }
    }
  }

  throw new Error('Could not find matching closing bracket for returned array.');
}

function parseProductDataFile(filePath) {
  const source = fs.readFileSync(filePath, 'utf8');
  const arraySource = extractReturnedArray(source);
  return new PhpArrayParser(arraySource).parse();
}

function loadSchema(schemaPath) {
  const schema = JSON.parse(fs.readFileSync(schemaPath, 'utf8'));
  const requiredTopKeys = ['schema_id', 'version', 'product_codes', 'required_blocks', 'blocks'];
  for (const key of requiredTopKeys) {
    if (!(key in schema)) {
      throw new Error(`Schema misses top-level key: ${key}`);
    }
  }
  return schema;
}

function validateProduct(code, data, schema) {
  const errors = [];
  const at = (pathPart) => `${code}.${pathPart}`;

  for (const key of schema.top_level.required_strings) {
    requireString(data, key, at(key), errors);
  }
  for (const key of schema.top_level.required_arrays) {
    requireArray(data, key, at(key), errors, 1);
  }
  for (const key of schema.top_level.required_objects) {
    requireObject(data, key, at(key), errors);
  }
  for (const key of schema.required_blocks) {
    if (!(key in data)) {
      errors.push(`${at(key)} is required by product schema.`);
    }
  }

  validateUrl(data.secondary_cta_href, at('secondary_cta_href'), schema, errors);
  validateHeroCards(data.hero_cards, schema, at('hero_cards'), errors);
  validateFitGuide(data.fit_guide, schema, at('fit_guide'), errors);
  validateSections(data.sections, schema, at('sections'), errors);
  validateArchitecture(data.architecture, schema, at('architecture'), errors);
  validateUseCases(data.use_cases, schema, at('use_cases'), errors);
  validateComparison(data.comparison, schema, at('comparison'), errors);
  validateProcurement(data.procurement, schema, at('procurement'), errors);
  validateRollout(data.rollout, schema, at('rollout'), errors);
  validateProof(data.proof, schema, at('proof'), errors);
  validateFaq(data.faq, schema, at('faq'), errors);
  validateCta(code, data.cta, schema, at('cta'), errors);

  return errors;
}

function validateHeroCards(cards, schema, pathPart, errors) {
  const rules = schema.blocks.hero_cards;
  if (!Array.isArray(cards) || cards.length < rules.min_items) {
    errors.push(`${pathPart} must contain at least ${rules.min_items} cards.`);
    return;
  }
  cards.forEach((card, index) => {
    requireObject({ card }, 'card', `${pathPart}[${index}]`, errors);
    if (!isPlainObject(card)) return;
    for (const key of rules.item_required_strings) {
      requireString(card, key, `${pathPart}[${index}].${key}`, errors);
    }
  });
}

function validateFitGuide(fitGuide, schema, pathPart, errors) {
  if (!isPlainObject(fitGuide)) return;
  const rules = schema.blocks.fit_guide;
  for (const column of rules.required_columns) {
    const value = fitGuide[column];
    if (!isPlainObject(value)) {
      errors.push(`${pathPart}.${column} must be an object.`);
      continue;
    }
    requireArray(value, 'items', `${pathPart}.${column}.items`, errors, rules.column_min_items);
    validateStringArray(value.items, `${pathPart}.${column}.items`, errors);
  }
}

function validateSections(sections, schema, pathPart, errors) {
  const rules = schema.blocks.section;
  if (!Array.isArray(sections) || sections.length < rules.min_items) {
    errors.push(`${pathPart} must contain at least ${rules.min_items} sections.`);
    return;
  }
  sections.forEach((section, index) => {
    if (!isPlainObject(section)) {
      errors.push(`${pathPart}[${index}] must be an object.`);
      return;
    }
    const hasAnyRequired = rules.item_required_strings_any.some((key) => isNonEmptyString(section[key]));
    if (!hasAnyRequired) {
      errors.push(`${pathPart}[${index}] must have at least one of: ${rules.item_required_strings_any.join(', ')}.`);
    }
    if ('columns_class' in section) {
      validateColumnsClass(section.columns_class, rules.allowed_columns_classes, `${pathPart}[${index}].columns_class`, errors);
    }
    if ('cards' in section) {
      requireArray(section, 'cards', `${pathPart}[${index}].cards`, errors, rules.cards_min_items);
      if (Array.isArray(section.cards)) {
        validateCardItems(section.cards, rules.card_required_strings, `${pathPart}[${index}].cards`, errors);
      }
    }
  });
}

function validateColumnsClass(value, allowedValues, pathPart, errors) {
  if (value === undefined || value === null || value === '') {
    return;
  }
  if (!Array.isArray(allowedValues) || !allowedValues.includes(String(value).trim())) {
    errors.push(`${pathPart} must be one of: ${(allowedValues || []).join(', ')}.`);
  }
}

function validateArchitecture(architecture, schema, pathPart, errors) {
  if (!isPlainObject(architecture)) return;
  const rules = schema.blocks.architecture;
  requireStrings(architecture, rules.required_strings, pathPart, errors);
  requireArray(architecture, 'layers', `${pathPart}.layers`, errors, rules.layers_min_items);
  if (Array.isArray(architecture.layers)) {
    architecture.layers.forEach((layer, index) => {
      if (!isPlainObject(layer)) {
        errors.push(`${pathPart}.layers[${index}] must be an object.`);
        return;
      }
      requireStrings(layer, rules.layer_required_strings, `${pathPart}.layers[${index}]`, errors);
      requireArray(layer, 'items', `${pathPart}.layers[${index}].items`, errors, rules.layer_items_min_items);
      validateStringArray(layer.items, `${pathPart}.layers[${index}].items`, errors);
    });
  }
}

function validateUseCases(useCases, schema, pathPart, errors) {
  if (!isPlainObject(useCases)) return;
  const rules = schema.blocks.use_cases;
  requireStrings(useCases, rules.required_strings, pathPart, errors);
  requireArray(useCases, 'items', `${pathPart}.items`, errors, rules.items_min_items);
  if (Array.isArray(useCases.items)) {
    useCases.items.forEach((item, index) => {
      if (!isPlainObject(item)) {
        errors.push(`${pathPart}.items[${index}] must be an object.`);
        return;
      }
      requireStrings(item, rules.item_required_strings, `${pathPart}.items[${index}]`, errors);
      if ('proof_status' in item && !rules.allowed_proof_statuses.includes(item.proof_status)) {
        errors.push(`${pathPart}.items[${index}].proof_status has unsupported value: ${item.proof_status}`);
      }
    });
  }
}

function validateComparison(comparison, schema, pathPart, errors) {
  if (!isPlainObject(comparison)) return;
  const rules = schema.blocks.comparison;
  requireStrings(comparison, rules.required_strings, pathPart, errors);
  requireArray(comparison, 'columns', `${pathPart}.columns`, errors, rules.columns_min_items);
  if (Array.isArray(comparison.columns)) {
    comparison.columns.forEach((column, index) => {
      if (!isPlainObject(column)) {
        errors.push(`${pathPart}.columns[${index}] must be an object.`);
        return;
      }
      requireStrings(column, rules.column_required_strings, `${pathPart}.columns[${index}]`, errors);
      requireArray(column, 'items', `${pathPart}.columns[${index}].items`, errors, rules.column_items_min_items);
      validateStringArray(column.items, `${pathPart}.columns[${index}].items`, errors);
      if ('href' in column) {
        validateUrl(column.href, `${pathPart}.columns[${index}].href`, schema, errors);
      }
    });
  }
}

function validateProcurement(procurement, schema, pathPart, errors) {
  if (!isPlainObject(procurement)) return;
  const rules = schema.blocks.procurement;
  requireStrings(procurement, rules.required_strings, pathPart, errors);
  if ('cta_href' in procurement) {
    validateUrl(procurement.cta_href, `${pathPart}.cta_href`, schema, errors);
  }
  requireArray(procurement, 'items', `${pathPart}.items`, errors, rules.items_min_items);
  validateCardItems(procurement.items, rules.item_required_strings, `${pathPart}.items`, errors);
}

function validateRollout(rollout, schema, pathPart, errors) {
  if (!isPlainObject(rollout)) return;
  const rules = schema.blocks.rollout;
  requireStrings(rollout, rules.required_strings, pathPart, errors);
  requireArray(rollout, 'steps', `${pathPart}.steps`, errors, rules.steps_min_items);
  validateCardItems(rollout.steps, rules.step_required_strings, `${pathPart}.steps`, errors);
}

function validateProof(proof, schema, pathPart, errors) {
  if (!isPlainObject(proof)) return;
  const rules = schema.blocks.proof;
  requireStrings(proof, rules.required_strings, pathPart, errors);
  requireArray(proof, 'items', `${pathPart}.items`, errors, rules.items_min_items);
  validateCardItems(proof.items, rules.item_required_strings, `${pathPart}.items`, errors);
}

function validateFaq(faq, schema, pathPart, errors) {
  if (!isPlainObject(faq)) return;
  const rules = schema.blocks.faq;
  requireStrings(faq, rules.required_strings, pathPart, errors);
  requireArray(faq, 'items', `${pathPart}.items`, errors, rules.items_min_items);
  validateCardItems(faq.items, rules.item_required_strings, `${pathPart}.items`, errors);
}

function validateCta(productCode, cta, schema, pathPart, errors) {
  if (!isPlainObject(cta)) return;
  const rules = schema.blocks.cta;
  requireStrings(cta, rules.required_strings, pathPart, errors);
  requireArray(cta, 'scenario_options', `${pathPart}.scenario_options`, errors, rules.scenario_options_min_items);
  validateCardItems(cta.scenario_options, rules.scenario_option_required_strings, `${pathPart}.scenario_options`, errors);
  if (Array.isArray(cta.scenario_options)) {
    const allowedScenarioValues = new Set(rules.allowed_scenario_values ?? []);
    for (const [index, option] of cta.scenario_options.entries()) {
      if (!isPlainObject(option)) continue;
      const value = option.VALUE;
      if (typeof value !== 'string' || !allowedScenarioValues.has(value)) {
        errors.push(`${pathPart}.scenario_options[${index}].VALUE has unsupported value: ${value}`);
      }
    }
  }
  requireObject(cta, 'lead_context', `${pathPart}.lead_context`, errors);
  if (isPlainObject(cta.lead_context)) {
    requireStrings(cta.lead_context, rules.lead_context_required_strings, `${pathPart}.lead_context`, errors);
    const allowedLeadContextKeys = new Set(rules.lead_context_allowed_keys ?? rules.lead_context_required_strings ?? []);
    for (const [key, value] of Object.entries(cta.lead_context)) {
      if (!allowedLeadContextKeys.has(key)) {
        errors.push(`${pathPart}.lead_context.${key} is not an allowed key.`);
        continue;
      }
      if (typeof value === 'string' && !/^[a-z0-9_.-]+$/.test(value)) {
        errors.push(`${pathPart}.lead_context.${key} must use a slug-like controlled value.`);
      }
    }
    if (cta.lead_context.lead_product !== productCode) {
      errors.push(`${pathPart}.lead_context.lead_product must match product code ${productCode}.`);
    }
  }
}

function validateCardItems(items, requiredStrings, pathPart, errors) {
  if (!Array.isArray(items)) return;
  items.forEach((item, index) => {
    if (!isPlainObject(item)) {
      errors.push(`${pathPart}[${index}] must be an object.`);
      return;
    }
    requireStrings(item, requiredStrings, `${pathPart}[${index}]`, errors);
    if ('icon' in item) {
      validateIconClass(item.icon, `${pathPart}[${index}].icon`, errors);
    }
  });
}

function validateIconClass(value, pathPart, errors) {
  if (value === undefined || value === null || value === '') {
    return;
  }
  if (typeof value !== 'string' || !/^ri-[a-z0-9]+(?:-[a-z0-9]+)*$/.test(value.trim())) {
    errors.push(`${pathPart} must be a single RemixIcon class token.`);
  }
}

function validateStringArray(items, pathPart, errors) {
  if (!Array.isArray(items)) return;
  items.forEach((item, index) => {
    if (!isNonEmptyString(item)) {
      errors.push(`${pathPart}[${index}] must be a non-empty string.`);
    }
  });
}

function validateUrl(value, pathPart, schema, errors) {
  if (!isNonEmptyString(value)) {
    errors.push(`${pathPart} must be a non-empty URL/path string.`);
    return;
  }
  const normalized = value.trim();
  if (!isSafeUrl(normalized, schema)) {
    errors.push(`${pathPart} must start with one of: ${schema.safe_url_prefixes.join(', ')}.`);
  }
}

function isSafeUrl(value, schema = {}) {
  if (value === '' || /[\x00-\x1F\x7F]/.test(value)) {
    return false;
  }
  const blockedPrefixes = Array.isArray(schema.blocked_url_prefixes)
    ? schema.blocked_url_prefixes
    : ['//', '/\\'];
  if (blockedPrefixes.some((prefix) => value.startsWith(prefix))) {
    return false;
  }
  if (value.startsWith('https://') || value.startsWith('#')) {
    return true;
  }
  return value.startsWith('/') && !value.startsWith('//') && !value.startsWith('/\\');
}

function requireStrings(object, keys, pathPart, errors) {
  for (const key of keys) {
    requireString(object, key, `${pathPart}.${key}`, errors);
  }
}

function requireString(object, key, pathPart, errors) {
  if (!isNonEmptyString(object?.[key])) {
    errors.push(`${pathPart} must be a non-empty string.`);
  }
}

function requireArray(object, key, pathPart, errors, minItems = 0) {
  const value = object?.[key];
  if (!Array.isArray(value)) {
    errors.push(`${pathPart} must be an array.`);
    return;
  }
  if (value.length < minItems) {
    errors.push(`${pathPart} must contain at least ${minItems} item(s).`);
  }
}

function requireObject(object, key, pathPart, errors) {
  if (!isPlainObject(object?.[key])) {
    errors.push(`${pathPart} must be an object.`);
  }
}

function isPlainObject(value) {
  return value !== null && typeof value === 'object' && !Array.isArray(value);
}

function isNonEmptyString(value) {
  return typeof value === 'string' && value.trim() !== '';
}

function runSelfTest(schema) {
  const valid = {
    eyebrow: 'Demo',
    title: 'Demo title',
    lead: 'Demo lead',
    primary_cta_text: 'Primary',
    secondary_cta_text: 'Secondary',
    secondary_cta_href: '/services/',
    badges: ['A'],
    hero_cards: [{ title: 'Hero', text: 'Text' }],
    fit_guide: {
      fits: { items: ['fit'] },
      not_fits: { items: ['not'] },
      start: { items: ['start'] }
    },
    sections: [{ title: 'Section', cards: [{ title: 'Card', text: 'Text' }] }],
    architecture: { title: 'Architecture', text: 'Text', layers: [{ title: 'Layer', text: 'Text', items: ['Item'] }] },
    use_cases: {
      title: 'Use cases',
      text: 'Text',
      items: [1, 2, 3].map((number) => ({
        title: `Use case ${number}`,
        trigger: 'Trigger',
        owner: 'Owner',
        pilot_input: 'Input',
        pilot_output: 'Output',
        limitation: 'Limitation'
      }))
    },
    comparison: {
      title: 'Compare',
      text: 'Text',
      columns: [
        { title: 'A', text: 'Text', items: ['One'] },
        { title: 'B', text: 'Text', items: ['Two'], href: '/agents/' }
      ]
    },
    procurement: { title: 'Proc', text: 'Text', note_text: 'Safe note', items: [{ title: 'Item', text: 'Text' }] },
    rollout: { title: 'Rollout', text: 'Text', steps: [1, 2, 3].map((number) => ({ title: `Step ${number}`, text: 'Text' })) },
    proof: { title: 'Proof', text: 'Text', items: [1, 2, 3].map((number) => ({ meta: 'Pilot', title: `Proof ${number}`, text: 'Text' })) },
    faq: { title: 'FAQ', text: 'Text', items: [1, 2, 3].map((number) => ({ question: `Q${number}`, answer: 'A' })) },
    cta: {
      form_id: 'demo-cta',
      field_prefix: 'demo',
      title: 'CTA',
      text: 'Text',
      form_title: 'Form',
      button_text: 'Send',
      scenario_label: 'Scenario',
      scenario_empty_label: 'Choose',
      scenario_options: [{ VALUE: 'pilot', LABEL: 'Pilot' }],
      lead_context: {
        lead_entry: 'demo',
        lead_page_role: 'product-page',
        lead_product: 'demo',
        lead_intent: 'demo',
        lead_cta: 'demo-cta',
        lead_next_step: 'demo'
      }
    }
  };

  const validErrors = validateProduct('demo', valid, schema);
  if (validErrors.length > 0) {
    throw new Error(`Self-test valid fixture failed:\n- ${validErrors.join('\n- ')}`);
  }

  const invalid = structuredClone(valid);
  invalid.title = '';
  invalid.use_cases.items = invalid.use_cases.items.slice(0, 2);
  invalid.cta.lead_context.lead_product = 'wrong';
  invalid.cta.lead_context.lead_intent = 'unsafe value';
  invalid.cta.lead_context.email = 'person@example.test';
  const invalidErrors = validateProduct('demo', invalid, schema);
  const expectedFragments = [
    'demo.title must be a non-empty string',
    'demo.use_cases.items must contain at least 3 item',
    'demo.cta.lead_context.lead_product must match product code demo',
    'demo.cta.lead_context.lead_intent must use a slug-like controlled value',
    'demo.cta.lead_context.email is not an allowed key'
  ];
  for (const fragment of expectedFragments) {
    if (!invalidErrors.some((error) => error.includes(fragment))) {
      throw new Error(`Self-test invalid fixture missed expected error: ${fragment}`);
    }
  }
}

function parseArgs(argv) {
  const options = {
    schema: DEFAULT_SCHEMA,
    dataDir: DEFAULT_DATA_DIR,
    selfTest: false,
    productCodes: null,
    expectFail: false,
    requiredErrorFragments: []
  };
  for (const argument of argv.slice(2)) {
    if (argument === '--self-test') {
      options.selfTest = true;
      continue;
    }
    if (argument === '--expect-fail') {
      options.expectFail = true;
      continue;
    }
    if (argument.startsWith('--schema=')) {
      options.schema = path.resolve(argument.slice('--schema='.length));
      continue;
    }
    if (argument.startsWith('--data-dir=')) {
      options.dataDir = path.resolve(argument.slice('--data-dir='.length));
      continue;
    }
    if (argument.startsWith('--product-code=')) {
      const codes = argument
        .slice('--product-code='.length)
        .split(',')
        .map((code) => code.trim())
        .filter(Boolean);
      options.productCodes = [...(options.productCodes ?? []), ...codes];
      continue;
    }
    if (argument.startsWith('--require-error-fragment=')) {
      options.requiredErrorFragments.push(argument.slice('--require-error-fragment='.length));
      continue;
    }
    throw new Error(`Unknown argument: ${argument}`);
  }
  return options;
}

function main() {
  const options = parseArgs(process.argv);
  const schema = loadSchema(options.schema);
  const productCodes = options.productCodes ?? schema.product_codes;
  if (productCodes.length === 0) {
    throw new Error('At least one product code must be selected.');
  }
  for (const productCode of productCodes) {
    if (!schema.product_codes.includes(productCode)) {
      throw new Error(`Unknown product code for schema ${schema.schema_id}: ${productCode}`);
    }
  }

  if (options.selfTest) {
    runSelfTest(schema);
    console.log('Product content schema self-test passed.');
    return;
  }

  const errors = [];
  for (const productCode of productCodes) {
    const filePath = path.join(options.dataDir, `${productCode}.php`);
    if (!fs.existsSync(filePath)) {
      errors.push(`${productCode}: missing product data file ${filePath}`);
      continue;
    }
    try {
      const data = parseProductDataFile(filePath);
      errors.push(...validateProduct(productCode, data, schema));
    } catch (error) {
      errors.push(`${productCode}: ${error.message}`);
    }
  }

  if (options.expectFail) {
    if (errors.length === 0) {
      console.error('Product content schema negative fixture unexpectedly passed.');
      process.exit(1);
    }

    const missingFragments = options.requiredErrorFragments.filter((fragment) =>
      !errors.some((error) => error.includes(fragment))
    );
    if (missingFragments.length > 0) {
      console.error('Product content schema negative fixture missed expected error fragments:');
      for (const fragment of missingFragments) {
        console.error(`- ${fragment}`);
      }
      console.error('');
      console.error('Actual errors:');
      for (const error of errors) {
        console.error(`- ${error}`);
      }
      process.exit(1);
    }

    console.log(`Product content schema negative fixture failed as expected: ${errors.length} error(s).`);
    return;
  }

  if (errors.length > 0) {
    console.error('Product content schema check failed:');
    for (const error of errors) {
      console.error(`- ${error}`);
    }
    process.exit(1);
  }

  console.log(`Product content schema check passed for ${productCodes.length} products.`);
}

try {
  main();
} catch (error) {
  console.error(error.message);
  process.exit(1);
}

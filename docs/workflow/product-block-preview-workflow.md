# Product Block Preview Workflow

Дата: 01.06.2026

## Purpose

Этот workflow дает дизайнеру, QA и frontend быстрый AS IS preview product blocks без Storybook и без отдельного frontend runtime.

Он использует реальные rendered pages, стабильные `data-product-block` markers и `tools/visual-smoke.mjs`.

## Scope

Покрываем product pages:

- `/platform/`
- `/agents/`
- `/dev/`
- `/forum/`

Required block taxonomy:

- `hero`
- `fit-guide`
- `content-section`
- `architecture`
- `use-cases`
- `comparison`
- `procurement`
- `rollout`
- `proof`
- `faq`
- `lead-cta`

## Commands

Production:

```bash
npm run product:block-previews:prod
```

Current default base URL:

```bash
npm run product:block-previews
```

Staging or local Bitrix URL:

```bash
TACTICUM_VISUAL_BASE_URL=https://staging.tacticum.ru npm run product:block-previews
```

Optional max block screenshot height:

```bash
TACTICUM_PRODUCT_BLOCK_MAX_HEIGHT=3200 npm run product:block-previews
```

## Outputs

`visual-smoke` prints output directory and writes:

- full-page screenshots: `<page>-<viewport>.png`
- manifest: `manifest.json`
- product block screenshots: `product-blocks/*.png`

Manifest fields:

- `captureProductBlocks: true`
- `results[].productBlocks.required`
- `results[].productBlocks.found`
- `results[].productBlocks.missing`
- `results[].productBlockErrors`
- `results[].productBlockScreenshots[]`

## Passed Criteria

Preview run is usable for design handoff when:

- command exits with code `0`;
- `productBlockErrors` is empty;
- each product page has all required blocks in `productBlocks.found`;
- `productBlockScreenshots[]` has PNG entries for desktop and mobile product pages;
- screenshots show rendered AS IS blocks, not blank/unstyled states.

## Limitations

This is not Storybook and not a token pipeline.

It is a lightweight rendered screenshot workflow for AS IS -> TO BE review:

- screenshots depend on deployed/staging/local rendered pages;
- block screenshots are clipped if a block is taller than `TACTICUM_PRODUCT_BLOCK_MAX_HEIGHT`;
- component states beyond visible rendered state still need design specs;
- final tokens, interactive states and component anatomy remain design-system deliverables.

## When To Use

Use before:

- designer TO BE component mapping;
- QA visual review of product page block inventory;
- refactor from PHP partials to Bitrix local components;
- release evidence review when product pages changed.

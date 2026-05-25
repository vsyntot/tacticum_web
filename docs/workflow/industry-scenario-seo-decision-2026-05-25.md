# Industry / Scenario SEO Decision

Дата: 25.05.2026
Статус: accepted for stabilization
Связанный gap: `FSC-009`

## Decision

Для текущего стабилизационного релиза сохраняем safe strategy:

- `/offer/` остаётся индексируемым hub-каталогом и коммерческим входом;
- валидные `/offer/<code>/` остаются индексируемыми detail pages;
- filtered industry/scenario states в namespace `/offer/catalog/...` остаются `noindex,follow`;
- canonical для filtered states остаётся `/offer/`;
- новые индексируемые industry/scenario landing pages не создаются в Sprint 16.

## Why

Filtered states полезны для навигации и персонализации, но сейчас у них нет уникального редакционного контента, отдельного интента, sitemap policy и owner-а обновления. Индексация таких URL сейчас создаст риск thin/duplicate pages и размоет canonical модель `/offer/`.

## Future Scope If Growth SEO Is Required

Отдельный SEO / Full Feature scope нужен, если PM/SEO решат делать отраслевые или сценарные landing pages:

- список кластеров и search intent;
- уникальные H1/title/description/body copy;
- proof/case evidence для каждого кластера;
- canonical/self-canonical rules;
- sitemap inclusion;
- internal linking from `/services/`, `/offer/`, `/calculator/`;
- rendered SEO smoke and post-deploy `seo:check:prod`.

## Acceptance

`FSC-009` закрыт как accepted decision: текущая noindex strategy является целевым состоянием для стабилизации, а indexable clusters остаются будущим growth scope.

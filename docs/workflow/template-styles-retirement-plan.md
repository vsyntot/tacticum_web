# Template Styles Retirement Plan

Дата фиксации: 23.05.2026

## Контекст

`local/templates/tacticum/template_styles.css` остаётся большим legacy bundle активного Bitrix-шаблона. Файл подключается штатным Bitrix template pipeline и содержит смешанные правила: global styles, utility overrides, page-specific sections и compatibility fixes после перехода на static Tailwind.

Удалять файл одним изменением нельзя: он всё ещё участвует в cascade после `tailwind.generated.css`, а часть правил закрывает реальные layout regressions, найденные visual smoke.

## Цель

Прийти к состоянию, где:

- Tailwind utilities собираются только из `assets/src/tailwind.css` в `tailwind.generated.css`;
- кастомные global/component rules живут в явных template/component assets;
- `template_styles.css` либо пустой/минимальный compatibility shim, либо удалён после доказанного отсутствия влияния на rendered pages;
- deploy workflow ловит visual/runtime regressions до закрытия релиза.

## Этапы

### 1. Ownership Mapping

Для каждого крупного блока `template_styles.css` определить owner:

- `global` — typography, body, header/footer, modal, form, chat;
- `component` — конкретный Bitrix component template;
- `page` — публичная страница или approved page asset;
- `tailwind-compat` — временные compatibility rules;
- `dead` — не используется в rendered pages.

Acceptance criteria:

- рядом с каждым блоком есть понятный owner или задача на удаление;
- нет новых anonymous page-specific rules в legacy bundle.

### 2. Component Extraction

Переносить правила только маленькими партиями:

- component-specific CSS — в `style.css` соответствующего Bitrix component template;
- approved page-specific CSS — в `local/templates/tacticum/styles/<page>.css` только через explicit `TACTICUM_PAGE_ASSETS`;
- global rules — в новый явный template asset, если они не могут быть выражены Tailwind source.

Acceptance criteria:

- после каждой партии проходит `npm run visual:smoke` и `npm run browser:smoke`;
- `.github/workflows/pr-check.yml` не допускает возврат dead artifacts.

### 3. Compatibility Shrink

После production visual baseline:

- проверить, какие compatibility rules больше не нужны;
- удалить только правила с доказанным отсутствием rendered usage;
- не менять одновременно selectors и CSS ownership.

Acceptance criteria:

- diff каждой партии читаемый и rollback-friendly;
- production deploy проходит post-deploy smoke.

### 4. Final Retirement

Финальный шаг возможен только когда:

- rendered asset inventory показывает отсутствие нужных правил в `template_styles.css`;
- visual/browser smoke чистый на `/`, `/about/`, `/services/`, `/price/`, `/calculator/`, `/offer/`, `/aiagents/`, `/contacts/`, `/policies/`;
- Bitrix template не требует файл как side effect для asset cache.

Acceptance criteria:

- `template_styles.css` пустой/minimal или удалён;
- docs/current-state обновлены;
- rollback path зафиксирован в release issue.

## Stop Criteria

Остановить миграцию и не продолжать extraction, если:

- появляется horizontal overflow;
- меняется header/menu/footer layout;
- ломаются forms/chat/modal interactions;
- `/price/` filters/order modal/team summary падают в browser smoke;
- возникают новые first-party console/page errors.

## Текущий Статус

План зафиксирован. Само удаление `template_styles.css` не входит в Sprint 07, потому что сейчас важнее стабилизировать deploy gate и не повторить layout regression без production baseline.

# Template Styles Retirement Plan

Дата фиксации: 23.05.2026

## Контекст

`local/templates/tacticum/template_styles.css` был legacy bundle активного Bitrix-шаблона. После gap №8 активные правила перенесены в `local/templates/tacticum/styles/global.css`, который подключается явно через `Bitrix\Main\Page\Asset`; `template_styles.css` оставлен пустым/comment-only shim для совместимости Bitrix template asset pipeline.

Удалять файл физически пока не нужно: Bitrix может ожидать наличие template-level CSS файла как side effect asset cache. Но файл больше не является местом разработки или хранения CSS-правил.

## Цель

Прийти к состоянию, где:

- Tailwind utilities собираются только из `assets/src/tailwind.css` в `tailwind.generated.css`;
- кастомные global/component rules живут в явных template/component assets;
- `template_styles.css` пустой/минимальный compatibility shim, без active rules и `@import`;
- deploy workflow ловит visual/runtime regressions до закрытия релиза.

## Этапы

### 1. Ownership Mapping

Для каждого крупного блока migrated `styles/global.css` определить owner:

- `global` — typography, body, header/footer, modal, form, chat;
- `component` — конкретный Bitrix component template;
- `page` — публичная страница со scoped body/page class внутри `styles/global.css` или компонентный CSS;
- `tailwind-compat` — временные compatibility rules;
- `dead` — не используется в rendered pages.

Acceptance criteria:

- рядом с каждым блоком есть понятный owner или задача на удаление;
- нет новых anonymous page-specific rules в global bundle;
- `template_styles.css` остаётся comment-only.

Sprint 08 progress:

- старый generated Tailwind block удалён из `template_styles.css`;
- `tailwind.generated.css` остаётся единственным источником generated utilities;
- `.github/workflows/pr-check.yml` блокирует возврат `tailwindcss v*` / `@layer theme|base|utilities` в legacy file.

Sprint 09 next step:

- перед следующим extraction PR пройти `styles/global.css` сверху вниз и проставить owner mapping в issue/checklist, не совмещая это с массовым переносом CSS;
- минимальная карта первого прохода: fonts/nav/header/footer/modal/forms/chat/FAQ — `global`; personal/project CTA — `include`; price filters/cards/order modal — `component: news.list/price`; about/services/calculator/offer/contacts hero/page sections — `page/component`; responsive utility parity block — `tailwind-compat`; неподтверждённые selectors — `unknown` до rendered/source evidence;
- для каждого `unknown` блока выбрать одно действие: оставить с owner-комментарием в `styles/global.css`, перенести в component CSS отдельным PR или удалить только после source/rendered evidence и `visual:smoke:css-local`.

Gap №8 completion:

- `styles/global.css` создан из бывшего `template_styles.css`;
- `header.php` подключает `styles/global.css` через `Asset`;
- относительные `url(...)` поправлены под директорию `styles/`;
- `npm run template-styles:check` блокирует активные правила и `@import` в shim.

### 2. Component Extraction

Переносить правила только маленькими партиями:

- component-specific CSS — в `style.css` соответствующего Bitrix component template;
- approved page-specific CSS на уровне шаблона не создаётся по умолчанию; небольшой page block держать в `styles/global.css` со scoped body/page class, component rules — в component `style.css`;
- global rules — в новый явный template asset, если они не могут быть выражены Tailwind source.

Acceptance criteria:

- после каждой партии проходит `npm run visual:smoke` и `npm run browser:smoke`;
- для CSS replacement против production HTML проходит `npm run visual:smoke:css-local`; при интерактивных изменениях — `npm run browser:smoke:css-local`;
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

Финальный shim-state выполнен. Физическое удаление файла возможно только когда:

- rendered asset inventory показывает, что Bitrix не требует `template_styles.css` как side effect;
- visual/browser smoke чистый на `/`, `/about/`, `/services/`, `/price/`, `/calculator/`, `/offer/`, `/aiagents/`, `/contacts/`, `/policies/`;
- Bitrix template не требует файл как side effect для asset cache.

Acceptance criteria:

- `template_styles.css` пустой/minimal или удалён;
- active CSS остаётся в `styles/global.css` или более точечных component assets;
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

План доведён до shim-state в gap №8: `template_styles.css` пустой/comment-only, active CSS перенесён в `styles/global.css`, header подключает его через `Asset`, добавлен `template-styles:check`. CSS consolidation 24.05.2026 дополнительно удалил отдельный `aiagents.css`, generic Remixicon fallback и добавил проверку `ri-*` классов против локального icon font; дальнейшая работа — не retirement самого shim, а постепенная декомпозиция `styles/global.css` по owner/component/page с сохранением единственного template-level manual CSS file.

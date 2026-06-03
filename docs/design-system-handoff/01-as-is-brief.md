# 01. AS IS Brief

Дата: 30.05.2026

## Контекст Проекта

`tacticum.ru` — корпоративный сайт на PHP 8.4 + 1C-Bitrix. Основная задача публичной части — лидогенерация и маршрутизация пользователя к одному из коммерческих входов:

- рассчитать проект;
- внедрить AI-решение;
- собрать команду;
- запустить AI-бота;
- связаться с компанией напрямую.

Ключевые публичные URL:

- `/`
- `/services/`
- `/price/`
- `/calculator/`
- `/offer/`
- `/aiagents/`
- `/contacts/`
- `/about/`
- `/policies/`

## Текущая UI-Архитектура

Сайт использует кастомный Bitrix template `local/templates/tacticum`.

Слои:

```text
Bitrix public page
  -> split prolog / page properties / SEO defaults
  -> header.php
  -> Bitrix menu templates
  -> public page markup and local components
  -> footer.php
  -> global modal and mobile menu
```

Frontend runtime:

```text
tailwind.generated.css
  + fonts/remixicon.min.css
  + styles/global.css
  + js/menu.js
  + js/analytics.js
  + js/metrika.js
  + js/forms.js
  + js/modal.js
  + js/scroll.js
  + js/tg-link-resolver.js
  + optional page JS
```

Assets подключаются в `local/templates/tacticum/header.php` через `Bitrix\Main\Page\Asset`.

## Степень Формализации Дизайн-Системы

AS IS дизайн-система формально не выделена как отдельный продукт. Есть рабочие паттерны, но они распределены между:

- Tailwind tokens в `assets/src/tailwind.css`;
- utility-классами прямо в PHP-шаблонах;
- ручными CSS-правилами в `styles/global.css`;
- локальными Bitrix-компонентами;
- JS-контрактами в `local/templates/tacticum/js/*.js`;
- шаблонами Bitrix-компонентов в `local/templates/tacticum/components/bitrix/`.

То есть текущая система уже компонентная на уровне Bitrix, но не систематизирована на уровне design tokens, Figma variants и единого component spec.

## Базовые Токены AS IS

Файлы:

- implemented source: `local/templates/tacticum/assets/src/tailwind.css`;
- AS IS contract: `docs/design-system-handoff/05-design-tokens-as-is.json`;
- guard: `npm run design:tokens:check`.

Текущие явно заданные токены:

| Token | Value | Использование |
|---|---|---|
| `primary` | `#0066CC` | CTA, links, focus, active states |
| `secondary` | `#001F3F` | Header/footer dark color, text, gradients |
| `button radius` | `8px` | `rounded-button` |

Многие значения сейчас не токенизированы:

- gray scale;
- success/error colors;
- typography scale;
- spacing scale;
- card radius;
- shadows/elevation;
- z-index scale;
- section backgrounds;
- page hero overlays.

На 01.06.2026 эти значения не стали финальной TO BE дизайн-системой, но появились как `observedTokenCandidates` в `05-design-tokens-as-is.json`. Там же явно отмечены drift-значения, например `#001F40` в `.to-primary` против canonical `#001F3F` и legacy hover `#007bff` против brand primary `#0066CC`. Это нужно дизайнеру как входной материал для нормализации, а frontend использует guard, чтобы JSON не расходился с реальным CSS.

## Визуальный Язык AS IS

Общее ощущение:

- B2B / corporate / SaaS;
- белые карточки на светло-сером фоне;
- темно-синий `secondary` для header/footer/hero;
- яркий синий `primary` для CTA и interactive states;
- умеренные shadows;
- карточки часто получают hover lift;
- секции обычно используют `py-16`, `container mx-auto px-4`;
- радиусы варьируются от `rounded-lg` до `rounded-2xl`;
- иконки — Remix Icon через `remixicon.min.css`.

Основной layout-паттерн:

```html
<section class="py-16 ...">
  <div class="container mx-auto px-4">
    ...
  </div>
</section>
```

## Header / Navigation

Header фиксированный:

- белый фон с прозрачностью `bg-white/95`;
- `backdrop-blur-sm`;
- shadow;
- desktop nav от `lg`;
- mobile menu overlay до `lg`;
- global CTA `Связаться с нами`.

Меню:

- верхнее меню: `bitrix:menu` template `topmenu`;
- mobile menu: `bitrix:menu` template `mobilemenu`;
- footer menu: `bitrix:menu` template `bottommenu`;
- дочерние пункты услуг берутся из `services/.left.menu.php`.

TO BE вопрос: оставить ли текущую структуру навигации или поднять коммерческие входы в более явную top-level навигацию.

## Footer

Footer содержит:

- логотип;
- короткое описание;
- Telegram link;
- bottom menu;
- контакты;
- реквизиты;
- privacy policy;
- global contact modal.

Footer одновременно выполняет навигационную, контактную и legal-функцию. Для TO BE важно решить, должен ли footer быть плотным operational block или более легкой маркетинговой зоной.

## Компонентная Модель

Повторяемые блоки вынесены в `local/components/tacticum`.

Ключевые компоненты:

- `lead.cta` — CTA + lead form;
- `contact.modal` — global modal form;
- `chat.surface` — hero/light chat UI;
- `faq.section` — FAQ wrapper;
- `content.list` — wrapper над `bitrix:news.list`;
- `content.detail` — wrapper над `bitrix:news.detail`;
- `offer` / `offer.catalog` — `/offer/` list/detail flow;
- `aiagents` — section-level page component.

Это хороший фундамент для TO BE: дизайн-система может мапиться не на каждую страницу отдельно, а на набор reusable blocks.

## Пример: `/contacts/`

Файл: `contacts/index.php`.

Страница показывает текущий подход:

- page-level PHP задает данные офиса и SEO;
- hero, contact cards, legal info и map section сверстаны utility-классами;
- CTA-форма берется из `tacticum:lead.cta`;
- footer modal и mobile menu приходят из общего шаблона;
- карта — iframe Яндекс.Карт, без page-specific JS.

Основные секции:

1. Hero: title + intro.
2. Contact cards: phone, email, office.
3. Lead CTA: `tacticum:lead.cta`, `FORM_ID=contacts-cta`.
4. Legal details.
5. Map + hours.

TO BE ценность этой страницы: это хороший шаблон для `contact/legal page` pattern.

## JS-Модель AS IS

JS — vanilla, без frontend framework.

Общие свойства:

- каждый модуль запускается на `DOMContentLoaded`;
- каждый модуль ставит guard через `document.documentElement.dataset.*Init`;
- поведение привязано к `data-*`, id и class контрактам;
- формы и analytics работают через event delegation;
- optional JS подключается через page property `tacticum_page_assets`.

Постоянные JS:

- `menu.js`;
- `analytics.js`;
- `metrika.js`;
- `forms.js`;
- `modal.js`;
- `scroll.js`;
- `tg-link-resolver.js`.

Optional JS:

- `chat-agent.js`;
- `faq.js`;
- `charts.js`;
- `yandex-map.js`.

## Важные Ограничения Для TO BE

1. Новый дизайн должен учитывать Bitrix template/component model.
2. Нельзя проектировать только статичные картинки: нужны states и DOM contracts.
3. Формы являются lead-critical surface: обязательны validation, consent, loading, success/error.
4. Chat и price flow имеют сложные interactive states.
5. CSS сейчас смешивает utilities и ручные классы; TO BE должен решить, какой слой станет canonical.
6. Иконки сейчас Remix Icon; замена иконок потребует migration decision.
7. Контент приходит из инфоблоков, поэтому карточки должны выдерживать разную длину текста.

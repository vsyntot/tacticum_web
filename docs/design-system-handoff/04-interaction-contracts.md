# 04. Interaction Contracts

Дата: 30.05.2026

## Назначение

Этот документ описывает интерактивные контракты AS IS. Для дизайнера это важно потому, что текущий JS привязан к конкретной HTML-разметке. В TO BE можно поменять UI, но нужно явно определить, какие контракты сохраняются, а какие требуют разработки.

## Общий JS Подход

Все first-party JS находятся в `local/templates/tacticum/js/`.

Общие правила AS IS:

- vanilla JS, без frontend framework;
- запуск на `DOMContentLoaded`;
- защита от повторной инициализации через `document.documentElement.dataset.*Init`;
- event delegation там, где элементов много или они могут появляться позже;
- отсутствие PII в analytics events;
- optional scripts подключаются через page property `tacticum_page_assets`.

## Asset Loading Contract

Файл: `local/templates/tacticum/header.php`.

Постоянные scripts:

| Script | Назначение |
|---|---|
| `menu.js` | Mobile menu |
| `analytics.js` | Safe analytics wrapper |
| `metrika.js` | Yandex.Metrika loader |
| `forms.js` | All lead forms |
| `modal.js` | Global contact modal |
| `scroll.js` | Smooth anchors and header shadow |
| `tg-link-resolver.js` | Telegram link resolver |

Conditional scripts:

| Page asset flag | Script | Где используется |
|---|---|---|
| `chat` | `chat-agent.js` | `/`, `/calculator/`, `/price/` |
| `faq` | `faq.js` | Pages with FAQ accordion |
| `charts` | `charts.js` | `/price/` |
| `yandex_map` | `yandex-map.js` | Future constructor-map cases; not current `/contacts/` |

TO BE note: если новый дизайн добавляет новый интерактив, желательно вводить explicit page asset flag, а не грузить JS на все страницы.

## Mobile Menu

Script: `menu.js`.

### DOM Contract

| Selector | Role |
|---|---|
| `[data-tacticum-menu-toggle]` | Open/close button in header |
| `#tacticum-mobile-menu` | Mobile overlay |
| `.tacticum-mobile-menu-close` | Close button |
| `.tacticum-contact-btn` | Contact CTA inside mobile menu |
| `.translate-x-full` | Closed state class |

### Behavior

- toggles `translate-x-full`;
- updates `aria-hidden`;
- updates button `aria-expanded`;
- toggles `body.overflow-hidden`;
- uses `inert` if browser supports it;
- closes on menu links;
- closes on contact CTA;
- closes on Escape.

### States To Design

- closed;
- opening/open;
- nested menu links;
- active current page;
- focus state;
- landscape mobile with scroll;
- contact CTA inside menu.

## Contact Modal

Script: `modal.js`.

Component: `local/components/tacticum/contact.modal`.

### DOM Contract

| Selector | Role |
|---|---|
| `#contactUsBtn` | Header CTA that opens modal |
| `.tacticum-contact-btn` | Any contact CTA that opens modal |
| `#tacticum-modal` | Modal root |
| `#tacticum-modal-close` | Close button |
| `#tacticum-modal-form` | Form |
| `[data-error]` | Error hint nodes |

### Behavior

- removes/adds `hidden`;
- locks body scroll;
- focuses first field;
- focus trap inside modal;
- closes on backdrop;
- closes on Escape;
- resets form on close;
- hides error hints on close.

### States To Design

- hidden;
- open desktop;
- open mobile;
- scrolled modal content;
- field errors;
- loading submit;
- success close/toast;
- backend error;
- focus trap visual focus.

## Forms

Script: `forms.js`.

### DOM Contract

| Selector / attribute | Role |
|---|---|
| `[data-tacticum-form]` | Form handled by global submit logic |
| `data-form-id` | Analytics/form taxonomy id |
| `data-endpoint` | Optional POST endpoint override |
| `data-tacticum-consent` | Required consent checkbox |
| `data-tacticum-close-target` | Optional modal/container to close after success |
| `data-tacticum-close-mode` | `hidden` or `overlay` |
| `[data-role="spinner"]` | Optional submit spinner |
| `[data-role="btn-text"]` | Optional submit text node |
| `[data-error="<field-id>"]` | Optional field error hint |
| `[data-tacticum-prefill-value]` | Click trigger that appends value to a target field |

### Required Field Names

Global validation expects:

- `name`;
- `email`;
- `phone`;
- `message`;
- `[data-tacticum-consent]`.

### Payload Behavior

The script adds:

- `page_url`;
- `sessid`, if `BX.bitrix_sessid()` exists;
- `form_id`, from `data-form-id`;
- `group_id`, if scoped/global offer context exists;
- any form fields, including hidden `lead_*` fields.

Default endpoint:

```text
/local/rest/tacticum_form.php
```

Endpoint override is allowed only for same-site relative paths via `data-endpoint`.

### Analytics Events

| Event | Trigger |
|---|---|
| `tacticum_form_validation_error` | Client validation failed |
| `tacticum_form_submit` | Submit started |
| `tacticum_form_success` | Backend success |
| `tacticum_form_error` | Backend or network error |

### States To Design

- default;
- focus;
- filled;
- invalid field;
- consent error;
- submit loading;
- success toast;
- backend error toast;
- network error;
- disabled;
- autofill;
- mobile single column.

## Analytics

Script: `analytics.js`.

### Contract

Global function:

```js
window.tacticumTrackEvent(eventName, params)
```

Behavior:

- normalizes event names and keys;
- trims string values;
- limits string length;
- sends to Yandex.Metrika via `ym(..., "reachGoal", ...)`;
- dispatches browser event `tacticum:analytics`;
- does not send raw form text or contacts.

TO BE note: design specs should not require analytics to receive PII-like UI values.

## Telegram Link Resolver

Script: `tg-link-resolver.js`.

### DOM Contract

| Selector | Role |
|---|---|
| `a[data-tacticum-tg-resolve][href^="https://t.me/"]` | Resolvable Telegram links |

### Behavior

- lazy/on-click only;
- uses `BX.bitrix_sessid()` when available;
- calls `/local/rest/resolve_telegram_link.php`;
- caches result in `sessionStorage`;
- opens resolved link;
- emits safe analytics events.

### States To Design

Usually no dedicated visual state AS IS. TO BE can choose whether Telegram links need loading/error affordance.

## FAQ Accordion

Script: `faq.js`.

### DOM Contract

| Selector | Role |
|---|---|
| `.faq-item` | Accordion item |
| `.faq-question` | Click target |
| `.faq-answer` | Collapsible content |
| `.faq-icon` | Rotated icon |
| `.active` | Open state |

### Behavior

- click toggles current item;
- closes other FAQ items;
- CSS animates `max-height`;
- icon rotates.

### States To Design

- collapsed;
- open;
- hover;
- focus;
- long answer;
- last item/no border;
- mobile tap target.

## Chat

Script: `chat-agent.js`.

Component: `local/components/tacticum/chat.surface`.

### DOM Contract

| Selector / attribute | Role |
|---|---|
| `[data-tacticum-chat="hero"]` | Hero chat surface |
| `[data-tacticum-chat="light"]` | Light chat surface |
| `data-chat-surface` | Analytics surface id |
| `[data-chat-messages]` | Scrollable messages area |
| `[data-chat-input]` | Input |
| `[data-chat-send]` | Send button |
| `[data-chat-quick-reply]` | Quick reply button |
| `data-message` | Quick reply payload |
| `#main_chat` | Legacy hero chat id |
| `#aichat` | Legacy hero send button id |

### Endpoints

| Endpoint | Purpose |
|---|---|
| `/local/rest/tacticum_chat.php` | Send chat message |
| `/local/rest/tacticum_prefill.php` | Prefill lead form from `group_id` |

### States To Design

- initial assistant message;
- user message;
- assistant message;
- typing indicator;
- backend error;
- quick replies;
- scroll overflow;
- final offer actions;
- lead handoff CTA;
- mobile layout;
- disabled/sending state.

## Price / Staff Order Flow

Script: `local/templates/tacticum/components/bitrix/news.list/price/script.js`.

This is the most complex AS IS interaction.

### Main DOM Contracts

| Contract | Role |
|---|---|
| `[data-price-list]` | Root |
| `[data-price-card]` | Specialist/role card |
| `[data-price-filter-tab]` | Category filter |
| `[data-price-search]` | Search |
| `[data-price-level-select]` | Level selector |
| `[data-price-level-option]` | Segmented level button |
| `[data-price-value]` | Rendered price |
| `[data-price-order]` | Add/order button |
| `[data-price-team-preset]` | Team preset |
| `[data-price-team-summary]` | Persistent summary |
| `[data-price-empty]` | Empty state |
| `[data-price-reset]` | Reset filters |

The script also supports legacy classes for mixed rollout:

- `.pricing-card`;
- `.filter-tab`;
- `.level-select`;
- `.price-value`;
- `.order-specialist-btn`.

### States To Design

- category selected;
- search with results;
- empty search;
- role card default;
- role card selected/added;
- level selected;
- team preset selected;
- summary empty;
- summary filled;
- monthly budget visible;
- modal open;
- duration preset selected;
- order submit loading/error/success.

## Charts

Script: `charts.js`.

### DOM Contract

| Selector | Role |
|---|---|
| `#package-comparison-chart` | ECharts target |

TO BE note: if charts remain, define chart color tokens and empty/loading states.

## Yandex Map

Script: `yandex-map.js`.

Current `/contacts/` does not use it. Contacts page renders Yandex map through iframe.

### Optional DOM Contract

| Selector / attribute | Role |
|---|---|
| `[data-yandex-constructor-map]` | Placeholder for constructor script |
| `data-yandex-constructor-src` | Script src |

TO BE note: contact map should have loading/fallback state in design, even if iframe remains.

## Scroll / Anchors

Script: `scroll.js`.

### Behavior

- adds header shadow after scroll threshold;
- smooth-scrolls internal `#anchor` links;
- supports optional `#backToTop`.

### States To Design

- anchor target offset under fixed header;
- active section navigation if needed;
- back-to-top if retained.


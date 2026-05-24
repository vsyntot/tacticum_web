# Frontend Dev Agent — tacticum.ru

Ты — frontend-разработчик проекта **tacticum.ru**.  
Инструмент: **Copilot Agent Mode (PHPStorm) + Claude + MCP Playwright + MCP Filesystem + MCP Git + MCP GitHub**.

### MCP-серверы (Claude Desktop)
| MCP | Зачем |
|---|---|
| `playwright` | Проверка вёрстки в реальном браузере, тест форм end-to-end, отладка JS |
| `server-filesystem` | Чтение и запись шаблонов, CSS, JS файлов |
| `server-git` | Проверка локальных диффов, статуса и истории перед PR |
| `server-github` | Создание веток, работа с PR |

Подробности: `docs/mcp-tools.md`

### Playwright — сценарии использования

```
# Проверить страницу после правки CSS:
→ Открыть https://tacticum.ru/services/
→ Скриншот desktop (1440px) и mobile (375px)
→ Сравнить с макетом из Figma

# Протестировать форму:
→ Открыть https://tacticum.ru/
→ Нажать «Связаться с нами»
→ Заполнить name/email/phone/message
→ Проверить, что форма отправляется и показывает success-state

# Отладить JS-ошибку:
→ Открыть страницу
→ Проверить console на ошибки
→ Воспроизвести клик / действие
```

> Copilot Agent Mode в PHPStorm работает нативно (файлы + терминал).  
> Playwright используется через Claude для визуальной проверки результата.  
> Главный источник контекста: `.github/copilot-instructions.md`

---

## Твоя зона ответственности

- `local/templates/tacticum/` — активный шаблон Bitrix
  - `header.php` — подключение JS/CSS, мета-теги, Яндекс.Метрика (ID: 103471113)
  - `footer.php` — футер, попап «Связаться с нами», мобильное меню
  - `assets/src/tailwind.css` — source entrypoint static Tailwind CSS
  - `tailwind.generated.css` — generated CSS, обновлять только через `npm run css:build`
  - `template_styles.css` — пустой Bitrix compatibility shim
  - `styles/global.css` — единственный manual runtime CSS, подключён через `Asset`
  - `js/` — JS по функционалу: `forms.js`, `modal.js`, `chat-agent.js`, `faq.js`, `menu.js`, `scroll.js`, `tg-link-resolver.js`
  - `components/bitrix/` — шаблоны Bitrix-компонентов
- Страницы сайта: `about/index.php`, `services/index.php`, `contacts/index.php`, и т.д.

---

## Структура шаблона

```
local/templates/tacticum/
├── header.php          # <head>, подключение assets, Яндекс.Метрика
├── footer.php          # <footer>, попап формы, мобильное меню
├── assets/src/tailwind.css # Source static Tailwind CSS
├── tailwind.generated.css  # Generated Tailwind CSS
├── template_styles.css     # Empty Bitrix compatibility shim
├── fonts/              # RemixIcons (remixicon.min.css)
├── images/             # logo.png, logo2.png, favicon-*
├── js/
│   ├── analytics.js        # Safe client-side events без PII
│   ├── forms.js            # Обработка форм с data-tacticum-form
│   ├── modal.js            # Попап «Связаться с нами»
│   ├── chat-agent.js       # Production AI chat surfaces
│   ├── faq.js              # Аккордеон FAQ
│   ├── menu.js             # Навигация
│   ├── scroll.js           # Scroll-эффекты
│   └── tg-link-resolver.js # Telegram-ссылки
└── styles/
    └── global.css          # Global/template CSS и scoped page blocks через Asset
```

---

## Правила подключения assets

### Новый JS-файл для раздела
```php
// На странице до require bitrix/header.php:
$GLOBALS['TACTICUM_PAGE_ASSETS'] = ['new_section'];

// В header.php подключать по explicit flag:
if ($hasPageAsset('new_section')) {
    $obAsset->addJs(SITE_TEMPLATE_PATH."/js/new_section.js");
}
```

### Новый CSS для раздела
```php
// Предпочтительно: добавить классы/токены в assets/src/tailwind.css и выполнить:
// npm run css:build
// npm run css:check

// Если нужен небольшой page-specific CSS, scope через body/page class в styles/global.css.
// Для Bitrix-компонента использовать component style.css.
```

---

## Формы — обязательные атрибуты

Форма автоматически подхватывается `forms.js` при наличии атрибута `data-tacticum-form`:

```html
<form
  data-tacticum-form
  data-form-id="unique-form-id"
  data-tacticum-close-target="#modal-id"   <!-- опционально: закрыть попап после отправки -->
  data-tacticum-close-mode="hidden"
>
  <input name="name" type="text" required />
  <input name="email" type="email" required />
  <input name="phone" type="tel" required />
  <textarea name="message" required></textarea>

  <!-- Спиннер на кнопке submit -->
  <button type="submit">
    <svg class="animate-spin h-5 w-5 hidden" data-role="spinner" ...></svg>
    <span data-role="btn-text">Отправить</span>
  </button>
</form>
```

---

## CSS: стиль проекта

- **Tailwind CSS** через static `tailwind.generated.css`; browser Tailwind runtime удалён и не должен возвращаться.
- Source CSS менять в `local/templates/tacticum/assets/src/tailwind.css`, затем запускать `npm run css:build` и `npm run css:check`.
- Active global/template CSS живёт в `local/templates/tacticum/styles/global.css`; page-specific правила в этом файле должны быть scoped body/page class, `template_styles.css` должен оставаться пустым/comment-only shim, проверять `npm run template-styles:check`.
- После CSS/JS правок запускать `npm run e2e:css-js:local` до deploy и `npm run e2e:css-js:prod` после deploy; точечные `visual:smoke:*` / `browser:smoke:*` использовать для локализации падения.
- Кастомные CSS переменные: `--color-primary`, `--color-secondary`
- Кнопки: `bg-primary text-white px-6 py-2 rounded-button hover:bg-primary/90`
- Контейнер: `container mx-auto px-4`
- Карточки: `rounded-2xl shadow-sm border border-gray-100`
- Иконки: RemixIcons (`ri-*` классы через `remixicon.min.css`)

---

## Чего НЕ делать

- ❌ Не возвращать `bundle.v3.4.16.js` / `js/init.js`
- ❌ Не добавлять новые файлы в `styles/` без explicit asset flag и обновления `docs/workflow/asset-layout-audit.md`
- ❌ Не возвращать активные CSS-правила в `template_styles.css`
- ❌ Не добавлять `<script>` / `<link>` напрямую в HTML — только через `$obAsset`
- ❌ Не писать inline-стили там, где можно использовать Tailwind-классы
- ❌ Не редактировать файлы в `bitrix/`
- ❌ Не трогать Яндекс.Метрику (ID: 103471113) без отдельной задачи

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
  - `styles/` — CSS по разделам: `main.css`, `services.css`, `price.css`, `calculator.css`, `aiagents.css`, `about.css`, `contacts.css`
  - `js/` — JS по функционалу: `forms.js`, `modal.js`, `chat-agent.js`, `faq.js`, `menu.js`, `scroll.js`, `tg-link-resolver.js`
  - `components/bitrix/` — шаблоны Bitrix-компонентов
- Страницы сайта: `about/index.php`, `services/index.php`, `contacts/index.php`, и т.д.

---

## Структура шаблона

```
local/templates/tacticum/
├── header.php          # <head>, подключение assets, Яндекс.Метрика
├── footer.php          # <footer>, попап формы, мобильное меню
├── template_styles.css # Базовые стили (Tailwind-бандл)
├── fonts/              # RemixIcons (remixicon.min.css)
├── images/             # logo.png, logo2.png, favicon-*
├── js/
│   ├── bundle.v3.4.16.js  # Основной бандл (Tailwind + утилиты) — не редактировать
│   ├── analytics.js        # Safe client-side events без PII
│   ├── forms.js            # Обработка форм с data-tacticum-form
│   ├── modal.js            # Попап «Связаться с нами»
│   ├── chat-agent.js       # Production AI chat surfaces
│   ├── faq.js              # Аккордеон FAQ
│   ├── menu.js             # Навигация
│   ├── scroll.js           # Scroll-эффекты
│   └── tg-link-resolver.js # Telegram-ссылки
└── styles/
    ├── main.css
    ├── services.css
    ├── price.css
    ├── calculator.css
    ├── aiagents.css
    ├── about.css
    └── contacts.css
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
// 1. Создать файл: local/templates/tacticum/styles/new_section.css
// 2. На странице до require bitrix/header.php:
$GLOBALS['TACTICUM_PAGE_ASSETS'] = ['new_section_css'];

// 3. Подключить в header.php по explicit flag:
if ($hasPageAsset('new_section_css')) {
    $obAsset->addCss(SITE_TEMPLATE_PATH."/styles/new_section.css");
}
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

- **Tailwind CSS** через `bundle.v3.4.16.js` — использовать utility-классы
- Кастомные CSS переменные: `--color-primary`, `--color-secondary`
- Кнопки: `bg-primary text-white px-6 py-2 rounded-button hover:bg-primary/90`
- Контейнер: `container mx-auto px-4`
- Карточки: `rounded-2xl shadow-sm border border-gray-100`
- Иконки: RemixIcons (`ri-*` классы через `remixicon.min.css`)

---

## Чего НЕ делать

- ❌ Не редактировать `bundle.v3.4.16.js` (основной бандл)
- ❌ Не добавлять `<script>` / `<link>` напрямую в HTML — только через `$obAsset`
- ❌ Не писать inline-стили там, где можно использовать Tailwind-классы
- ❌ Не редактировать файлы в `bitrix/`
- ❌ Не трогать Яндекс.Метрику (ID: 103471113) без отдельной задачи

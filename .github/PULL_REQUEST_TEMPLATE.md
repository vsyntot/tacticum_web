## Описание

<!-- Кратко: что сделано и зачем -->

Closes #<!-- номер issue -->

---

## Тип изменения

- [ ] ✨ `feat` — новая функция
- [ ] 🐛 `fix` — исправление бага
- [ ] ♻️ `refactor` — рефакторинг без изменения поведения
- [ ] 🛡️ `security` — безопасность / PII / CSRF / CORS / rate limit
- [ ] 📦 `infra` — CI/CD, деплой, конфиг
- [ ] 📝 `docs` — документация / ADR

## Workflow lane

- [ ] Full Feature Lane
- [ ] Fast Fix Lane
- [ ] Security / Integration Lane
- [ ] Incident Lane

## Область

- [ ] backend (PHP, REST, init.php)
- [ ] frontend (шаблон, JS, CSS)
- [ ] ai-integration
- [ ] seo
- [ ] infra

---

## Исполнитель

- [ ] 🤖 AI-агент (Copilot / Claude) — код проверен человеком перед merge
- [ ] 👤 Человек

---

## Чеклист безопасности и качества

### Обязательно для backend (REST-эндпоинты)
- [ ] `tacticum_rest_validate_origin()` вызывается первым
- [ ] `tacticum_rest_rate_limit_by_class('RISK_CLASS', 'action')` вызывается вторым
- [ ] `tacticum_rest_check_csrf($data)` вызывается для POST
- [ ] Нет файлового/debug runtime-логирования payload/response в `/local` и публичных скриптах
- [ ] Нет хардкода ID инфоблоков — используется `tacticum_rest_get_iblock_id('key')`
- [ ] Нет хардкода URL AI-сервиса — используется `tacticum_rest_get_ai_setting('...')`
- [ ] Внешние curl-запросы только по HTTPS

### Общее
- [ ] Файлы в `bitrix/` не тронуты
- [ ] Нет дублирования логики из `rest_helpers.php`
- [ ] Нет глобальных функций без префикса `tacticum_`
- [ ] Код соответствует PSR-12 и соглашениям из `.github/copilot-instructions.md`

### Frontend
- [ ] Новый JS подключен через `$obAsset->addJs()` в `header.php`
- [ ] Новый CSS подключен через `$obAsset->addCss()` в `header.php`
- [ ] Форма использует атрибут `data-tacticum-form`
- [ ] `npm run template-styles:check` проходит; active CSS не возвращён в `template_styles.css`
- [ ] При изменении CSS/JS выполнен `npm run e2e:css-js:local` или указан reason, почему не применимо
- [ ] Новые/изменённые global CSS-правила в `styles/global.css` имеют понятный owner или вынесены в component/page asset

### SEO / инфраструктура (если применимо)
- [ ] `sitemap.xml` обновлён при добавлении нового публичного URL
- [ ] `robots.txt` актуален
- [ ] ADR создан / обновлён при значимом архитектурном решении (`docs/adr/`)
- [ ] `docs/workflow/gap-analysis.md` обновлён, если PR закрывает gap
- [ ] Новые config keys добавлены в `tacticum_config.example.php`; выполнен `npm run config:check`
- [ ] Проверен lifecycle legacy endpoints: `npm run sale:sunset:check`
- [ ] Если PR закрывает release issue, заполнен sign-off JSON по `docs/workflow/release-signoff.example.json` и выполнен `npm run release:signoff:check -- <file>`

### Post-deploy
- [ ] Указаны URL/API для smoke-check после deploy
- [ ] Для реальных form/chat/prefill/staff-order success-flow указан staging/manual sign-off owner
- [ ] При изменении аналитики указан owner проверки целей Yandex.Metrika
- [ ] При изменении config указан owner синхронизации production/staging `tacticum_config.php`
- [ ] Для production-инцидента подготовлен краткий PM summary

---

## Как проверить

```bash
# Пример для нового REST-эндпоинта:
curl -X POST https://tacticum.ru/local/rest/tacticum_XXX.php \
  -H 'Content-Type: application/json' \
  -H 'Origin: https://tacticum.ru' \
  -d '{"sessid":"CSRF_TOKEN","field":"value"}'
```

<!-- Или опиши шаги вручную: страница → действие → ожидаемый результат -->

---

## Скриншоты / логи (опционально)

<!-- Вставь сюда -->

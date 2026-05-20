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
- [ ] `tacticum_rest_rate_limit('action')` вызывается вторым
- [ ] `tacticum_rest_check_csrf($data)` вызывается для POST
- [ ] PII в логах маскируется через `tacticum_rest_mask_pii()` / `mask_string()`
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

### SEO / инфраструктура (если применимо)
- [ ] `sitemap.xml` обновлён при добавлении нового публичного URL
- [ ] `robots.txt` актуален
- [ ] ADR создан / обновлён при значимом архитектурном решении (`docs/adr/`)
- [ ] `docs/workflow/gap-analysis.md` обновлён, если PR закрывает gap

### Post-deploy
- [ ] Указаны URL/API для smoke-check после deploy
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

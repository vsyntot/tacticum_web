# Журнал решений

## 2026-08-13 — accepted

- Решение: сохранить auto-deploy на `main`, но запретить production mutation до полного quality gate и deployment-config preflight.
- Источник: запрос пользователя восстановить весь GitHub delivery chain; существующий workflow contract.
- Рассмотрено: временно перевести deploy только на `workflow_dispatch`.
- Обоснование: manual-only режим меняет release expectation сильнее, чем требуется для исправления.
- Влияние: порядок jobs меняется; production trigger остаётся прежним.

## 2026-08-13 — accepted

- Решение: historical release sign-off остаётся архивным evidence, но не generic blocker будущих deploy runs.
- Источник: clean-runner failure на абсолютных `/tmp` manifests.
- Обоснование: release-specific evidence не переносимо между runners и commits.
- Влияние: generic CI проверяет checker self-test; current run сохраняет собственные smoke artifacts.

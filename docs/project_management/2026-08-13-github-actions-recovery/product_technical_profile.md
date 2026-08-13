# Профиль delivery-контура

## Цель

GitHub Actions должен блокировать некорректный код до production mutation, выполнять deployment только при валидной конфигурации и сохранять проверяемые post-deploy evidence.

## Текущая архитектура

- `.github/workflows/pr-check.yml` — PHP, frontend, lifecycle и security checks только для Pull Request в `main`/`develop`.
- `.github/workflows/deploy.yml` — на каждый push в `main`: PHP lint, SSH/rsync/cache clear, затем lifecycle и production browser smoke.
- `.github/workflows/sitemap.yml` — sitemap checks на ограниченный набор path changes.
- Production: SSH + rsync; секреты `SSH_PRIVATE_KEY`, `SSH_HOST`, `SSH_USER`, `DEPLOY_PATH`.
- Rollback и manual gates описаны в `docs/workflow/`.

## Подтверждённое состояние

- Последний `main`: `8ef02a60a0641f2acda821cbcf82e98eb8f05c51`.
- 79 из 79 deploy runs завершились `failure`; sampled runs с первого по последний падают на `Setup SSH key`.
- Последний лог: `The ssh-private-key argument is empty`.
- 33 sitemap runs завершились успешно.
- PR workflow не имеет запусков в доступной истории; изменения поступают прямыми push в `main`.
- Последний commit меняет только `bitrix/` (13 026 файлов), но всё равно запускает production deploy workflow.
- Локальные PHP и repository quality checks проходят.
- Generic deploy guard вызывает historical product-first sign-off с абсолютными `/tmp` paths и падает на clean runner.

## Неподтверждённое состояние

- Branch protection недоступен integration token (`403`).
- Наличие и корректность остальных deployment secrets нельзя подтвердить без доступа к Settings.
- SSH/rsync/cache steps никогда не достигались в доступной истории, поэтому их runtime-исправность не доказана.

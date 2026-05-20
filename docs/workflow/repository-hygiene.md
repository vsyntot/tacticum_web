# Repository Hygiene

Дата проверки: 21.05.2026

## Current Findings

- `.gitignore` уже исключает `.DS_Store`, `*.log`, Bitrix cache/backup/runtime директории, IDE files и локальный `local/php_interface/include/tacticum_config.php`.
- `.DS_Store`, Bitrix cache/backup и IDE files в рабочем дереве сейчас ignored/untracked.
- `log.txt` в рабочем дереве не найден.
- `local/php_interface/include/tacticum_config.php` убран из Git index через `git rm --cached` и остаётся локальным ignored-файлом.
- `bitrix/.config.php` tracked, но не содержит runtime secret в текущем scope; `bitrix/` не редактируем в рамках проекта.

## Completed Cleanup

Локальный config убран из индекса, оставлен на диске и больше не должен попадать в diff:

```bash
git rm --cached local/php_interface/include/tacticum_config.php
```

Source of truth для структуры config: `local/php_interface/include/tacticum_config.example.php`.

## Guardrails

- Не коммитить secrets, IP allowlists, production-only base URLs и локальные overrides.
- Новые config keys сначала добавлять в `tacticum_config.example.php`.
- Перед PR проверять `git ls-files -c -i --exclude-standard`: tracked ignored files должны быть пустыми или объяснёнными.

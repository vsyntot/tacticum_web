# Offer Example Seed Runbook

Дата: 24.05.2026

## Цель

Наполнить инфоблок `offer` индексируемыми примерами расчетов для `/offer/<ELEMENT_CODE>/`, не создавая дубли и не ломая текущий AI calculator -> offer flow.

Скрипт: `local/tools/seed_offer_examples.php`.

## Product Logic

Генератор создает 1117 синтетических, но реалистичных запросов от российского бизнеса:

- секторы: ритейл, e-commerce, производство, логистика, медицина, финансы, страхование, девелопмент, строительство, HoReCa, образование, HR, юрсервисы, телеком, энергетика, ЖКХ, агро, маркетплейсы, медиа, SaaS и другие;
- сценарии: AI-поддержка, прогноз спроса, RPA/OCR, predictive maintenance, computer vision, рекомендации, BI, pricing, антифрод, knowledge assistant, B2B кабинет, оптимизация маршрутов, voice analytics, CDP, marketplace MVP, integration layer, MLOps, legacy audit, security hardening;
- масштаб: от малого бизнеса до enterprise-группы;
- фаза: MVP, пилот, production, масштабирование, платформа;
- бюджет: от `500 000 руб.` до `150 000 000 руб.`, с округлением по размеру проекта.
- дата synthetic публикации и timestamp в `ELEMENT_CODE`: равномерно распределены с 01.09.2022 по 24.05.2026, чтобы 1117 примеров не выглядели созданными в один день.

Контент не содержит персональных данных и реальных названий клиентов. `CLIENT_NAME`, `GROUP_ID`, `RESPONSE_ID` являются техническими synthetic values.

## Safety Rules

- Скрипт получает инфоблок через `tacticum_iblock_id('offer')`, а не через numeric ID.
- По умолчанию работает в `dry-run`; запись возможна только с `--apply`.
- Повторный запуск идемпотентен: существующие элементы ищутся по `CODE` и `GROUP_ID`.
- Без `--update-existing` уже созданные элементы пропускаются.
- `CODE` строится из транслитерированного H1 и стабильной synthetic timestamp-метки, чтобы URL был похож на production-логику `title + timestamp`, но оставался повторяемым.
- Скрипт пишет `DATE_ACTIVE_FROM` synthetic-датой; каталог и dynamic sitemap используют это поле как дату публикации, не завися от системных `DATE_CREATE` / `TIMESTAMP_X`, которые Bitrix может проставить временем фактического импорта.

## Commands

Проверить первые примеры без записи:

```bash
php local/tools/seed_offer_examples.php --limit=5
```

Создать все 1117 активных элементов:

```bash
php local/tools/seed_offer_examples.php --apply --limit=1117 --active=Y
```

Создать элементы неактивными для ручной проверки в админке:

```bash
php local/tools/seed_offer_examples.php --apply --limit=1117 --active=N
```

Обновить уже созданные synthetic элементы после изменения генератора:

```bash
php local/tools/seed_offer_examples.php --apply --limit=1117 --update-existing --active=Y
```

## Post-Run Checks

1. Открыть `/offer/`, убедиться, что hub-каталог показывает карточки расчетов, статистику, фильтры, сортировку и пагинацию.
2. Проверить фильтр и пагинацию: `/offer/?scenario=ai-kopaylot&page=2&clear_cache=Y` должен нормализоваться в `/offer/catalog/scenario/ai-kopaylot/page/2/?clear_cache=Y`, а страница должна отдавать canonical `/offer/` и `meta robots="noindex,follow"`.
3. Открыть 3-5 URL из samples, убедиться, что `/offer/<code>/` отдает `200`, self-canonical и заполненные блоки summary/goals/team/stack/budget/risks.
4. Проверить `/offer/sitemap.php`: новые активные элементы должны попасть в dynamic sitemap автоматически.
5. Выполнить `npm run seo:check:prod` после deploy/content run.
6. Если элементы создавались активными, точечно проверить выдачу в Яндекс.Вебмастер/поисковой консоли после переобхода sitemap.

## Content QA

При ручной выборочной проверке смотреть:

- нет ли реальных брендов, персональных данных или вводящего в заблуждение клиентского кейса;
- бюджет соответствует масштабу и типу задачи;
- страница не выглядит thin/doorway: есть summary, цели, требования, команда, стек, риски, срок и бюджет;
- CTA prefill получает осмысленный текст из detail template.

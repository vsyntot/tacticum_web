# Proof Claims Matrix

Дата: 25.05.2026
Статус: active
Связанный gap: `FSC-008`

## Rule

Публичный runtime-copy не должен использовать числовые proof claims или обещания результата без владельца, источника и даты подтверждения. Если источник находится вне репозитория, claim не считается approved до PM/Marketing sign-off в release issue.

## Allowed Runtime Claims

| Claim type | Allowed wording | Где можно использовать | Evidence / owner | Notes |
|---|---|---|---|---|
| Категория работ | `AI- и IT-решения`, `разработка программного обеспечения`, `автоматизация бизнес-процессов` | Все публичные страницы | Сайт, услуги, ОКВЭД на `/contacts/`; owner PM | Без числовых обещаний |
| Процесс | `discovery`, `MVP`, `интеграции`, `тестирование`, `запуск`, `support` | `/services/`, `/calculator/`, `/offer/`, `/price/` | Delivery/PM owner | Использовать как описание этапов, не как гарантию срока |
| Команда | `подбор ролей под задачу`, `аналитики, инженеры и разработчики`, `управляемая команда` | `/price/`, `/about/`, shared CTA | PM + Delivery owner | Не писать число специалистов без source sign-off |
| Примеры расчётов | `пример`, `предварительная оценка`, `ориентир`, `не финальная смета` | `/offer/`, `/calculator/`, offer detail | Offer content model; owner Analyst/PM | Обязательно сохранять disclaimer о персональной оценке |
| Юридические данные | ООО, ИНН, КПП, ОГРН, ОКВЭД, адрес | `/contacts/`, footer | Public registry/legal owner | Перепроверять при изменении реквизитов |
| Технологии | Названия стеков, интеграций и AI-сценариев | `/services/`, `/aiagents/`, cases/offers | Delivery/Architect owner | Без claims о superiority/performance без evidence |

## Conditional Claims

| Claim | Status | Условие допуска |
|---|---|---|
| Количество проектов, клиентов, отраслей, AI-проектов | not approved for runtime | Нужен source owner, source system, checked_at и место использования |
| Количество специалистов/команд | not approved for runtime | Нужен HR/delivery source и правило обновления |
| Проценты удовлетворённости, SLA, точность AI, экономия времени/денег | not approved for runtime | Нужна методология расчёта и юридически безопасная формулировка |
| Сроки старта вроде `за 7 дней`, `за 60 секунд` | not approved for runtime | Нужен scope, условия применимости и owner |

## Forbidden Wording

- `98% довольных клиентов`;
- `15+ лет опыта`;
- `гарантия результата`;
- `создай за 60 секунд`;
- `начни продавать сегодня`;
- `поддержка 24/7`;
- `более 120 проектов`, `50+ специалистов`, `18 отраслей` без source sign-off.

## Sprint 16 Runtime Decision

В Sprint 16 оставшиеся runtime numeric claims на `/about/` заменены на качественные proof statements. До появления внешнего evidence числовые claims считаются backlog-кандидатами, а не допустимой публичной копией.

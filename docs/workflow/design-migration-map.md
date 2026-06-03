# Design Migration Map

Дата: 02.06.2026

## Назначение

`docs/design-system-handoff/08-as-is-to-be-migration-map.json` - проверяемая карта миграции из AS IS behavior-bearing компонентов в TO BE дизайн-систему.

Она отвечает на вопрос: что дизайнер может менять как visual restyle, а где нужен отдельный frontend/backend/security scope.

## Связанные Контракты

| Файл | Роль |
|---|---|
| `05-design-tokens-as-is.json` | AS IS token baseline |
| `07-component-state-contract.json` | AS IS component/state baseline |
| `08-as-is-to-be-migration-map.json` | AS IS -> TO BE migration decisions |

## Migration Types

| Type | Значение |
|---|---|
| `visual-restyle` | Меняем визуальную анатомию, layout, CSS classes, density and copy, но сохраняем behavior-bearing selectors/fields |
| `contract-preserving-split` | Переносим markup в более правильный component/partial boundary, но сохраняем selectors and behavior |
| `contract-migration` | Меняем selectors, fields, payload, JS behavior or analytics contract |
| `new-interaction` | Добавляем новый interactive state/control |

## Guard

Запуск:

```bash
npm run design:migration:check
```

Guard проверяет:

- все component ids из `07-component-state-contract.json` покрыты mapping;
- migration types and risk levels допустимы;
- high-risk mappings имеют Design, Frontend and QA gates;
- selectors в `preserveSelectors` действительно есть в component/state contract;
- `package.json` содержит script `design:migration:check`.

## Как Использовать С Дизайнером

1. Открыть `07-component-state-contract.json` and `08-as-is-to-be-migration-map.json`.
2. Для каждого AS IS component подтвердить `toBeComponentName`.
3. Для каждого component выбрать migration type:
   - `visual-restyle`, если текущие selectors/fields подходят;
   - `contract-preserving-split`, если нужно улучшить component boundary;
   - `contract-migration`, если дизайн требует поменять DOM/JS/payload;
   - `new-interaction`, если появляется новый behavior.
4. Для high-risk components проверить gates before implementation:
   - forms/modal/chat/price require QA smoke;
   - payload/endpoint/analytics changes require Security / Integration or analytics review.
5. После изменения mapping запустить:

```bash
npm run design:tokens:check
npm run design:components:check
npm run design:migration:check
```

## Текущее Решение

First TO BE implementation should default to `visual-restyle` for:

- navigation;
- contact modal;
- lead CTA;
- chat;
- FAQ.

`/price/` team builder and product page blocks are marked as `contract-preserving-split`, because their current behavior/data contracts should survive, but implementation may need better component boundaries.

New proof/status, architecture diagram and procurement document request components are tracked in `toBeBacklog`; they should not be implemented as incidental visual additions without their gates.

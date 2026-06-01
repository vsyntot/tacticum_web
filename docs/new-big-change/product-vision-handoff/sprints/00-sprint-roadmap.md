# Sprint Roadmap - AS IS To TO BE Product Vision

Дата: 01.06.2026

Статус: planning baseline.

## Planning Assumption

Спринты рассчитаны как двухнедельные итерации, кроме Sprint 00 и Sprint 08, которые могут быть короче. Даты ниже - ориентир для планирования, а не обязательный календарь.

| Sprint | Suggested Window | Main Outcome |
|---|---|---|
| 00 | 01.06.2026 - 05.06.2026 | Решения и evidence baseline |
| 01 | 08.06.2026 - 19.06.2026 | IA, URL, messaging, page specs |
| 02 | 22.06.2026 - 03.07.2026 | Design system, wireframes, prototypes |
| 03 | 06.07.2026 - 17.07.2026 | Technical foundation and implementation plan |
| 04 | 20.07.2026 - 31.07.2026 | Homepage and navigation MVP |
| 05 | 03.08.2026 - 14.08.2026 | Platform + Agents pages |
| 06 | 17.08.2026 - 28.08.2026 | Dev + Forum pages |
| 07 | 31.08.2026 - 11.09.2026 | Proof, forms, SEO, analytics hardening |
| 08 | 14.09.2026 - 18.09.2026 | Release, post-launch, handoff |

## Gap Coverage

| Gap IDs | Covered In |
|---|---|
| `PV-001`, `PV-002`, `PV-006`, `PV-016`, `PV-017`, `PV-018`, `PV-019`, `PV-020` | Sprint 00 |
| `PV-002`, `PV-003`, `PV-004`, `PV-009`, `PV-013`, `PV-020` | Sprint 01 |
| `PV-003`, `PV-004`, `PV-005`, `PV-010`, `PV-011` | Sprint 02 |
| `PV-008`, `PV-012`, `PV-014`, `PV-015` | Sprint 03 |
| `PV-001`, `PV-002`, `PV-003`, `PV-005`, `PV-009`, `PV-011`, `PV-012` | Sprint 04 |
| `PV-004`, `PV-005`, `PV-007`, `PV-012`, `PV-013`, `PV-015`, `PV-020` | Sprint 05 |
| `PV-004`, `PV-007`, `PV-013`, `PV-017`, `PV-018`, `PV-020` | Sprint 06 |
| `PV-006`, `PV-007`, `PV-012`, `PV-013`, `PV-014`, `PV-016`, `PV-018`, `PV-019` | Sprint 07 |
| All release gates | Sprint 08 |

## Critical Path

1. Product decisions and claim evidence.
2. IA and URL strategy.
3. Design system and page prototypes.
4. Technical architecture and component boundaries.
5. Homepage and navigation.
6. Product pages.
7. Product-aware forms, analytics, SEO, proof hardening.
8. Release smoke and post-launch measurement.

## Release Scope Recommendation

First public release should include:

- updated homepage with ecosystem story;
- product-first navigation;
- Platform page;
- Agents page or approved `/aiagents/` migration;
- Dev page only with safe public framing;
- Forum page;
- product-aware CTA context;
- claim-safe copy;
- SEO metadata and sitemap/canonical coverage;
- analytics events without PII.

Deferrable after first release:

- industry landing pages;
- product-specific calculators;
- gated PDF downloads;
- public pricing/licensing pages;
- complex interactive product demos;
- customer logos/testimonials without legal approval.

## Program-Level Risks

| Risk | Impact | Mitigation |
|---|---|---|
| Design starts before claim evidence | Public copy may become unsafe | Sprint 00 exit gate blocks design start |
| URL strategy delayed | SEO and implementation churn | Sprint 01 must decide canonical `/agents/` vs `/aiagents/` |
| Product pages become sales-deck dumps | Poor UX and low conversion | Sprint 02 requires reusable page template and hierarchy |
| Form payload changes break existing flow | Lost leads or REST regressions | Sprint 03 and Sprint 07 require Security / Integration review |
| Dev page contains workforce reduction claims | Reputation/legal risk | Sprint 00 and Sprint 06 enforce public tone guardrail |
| Customer proof lacks approval | Legal/reputation risk | Sprint 07 claim register must be closed before release |
| New pages miss SEO/sitemap rules | Indexing/canonical issues | Sprint 01, 07 and 08 include SEO gates |


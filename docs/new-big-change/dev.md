# Tacticum Dev — agentic-трансформация разработки

> Enterprise-платформа управления жизненным циклом разработки программного обеспечения с применением AI-агентов. Vibe-coding без потери архитектуры. Класс 06.07 «Системы управления жизненным циклом ПО».

---

## Slide 1 — Title

```
┌───────────────────────────────────────────────────────────────┐
│                                                               │
│    TACTICUM DEV                                               │
│                                                               │
│    Agentic-трансформация разработки                           │
│    Vibe-coding для команд от 50 инженеров                     │
│                                                               │
│    Профили под стек · RE Knowledge · Design tokens            │
│    BRD → ADR → PIN → TESTS · Quality gates                    │
│                                                               │
└───────────────────────────────────────────────────────────────┘
```

---

## Slide 2 — Vibe-revolution. Вы её уже видели

**Vibe-coding изменил правила игры:**

- Один инженер делает за неделю то, что 5 делали за месяц.
- Greenfield-проект MVP — за выходные.
- Personal productivity растёт в 3–5 раз там, где scope понятен и стек чистый.

И это правда. Founder'ы, CTO и senior-разработчики это видели — каждый сам.

> Vibe-coding — это новый baseline для одиночного разработчика.
> Вопрос не *работает ли это*, а *что происходит когда мы это масштабируем на 100+ человек в живой кодовой базе*.

---

## Slide 3 — Vibe-потолок: что ломается на enterprise

**Что vibe-coding делает блестяще:**

- ✅ Один разработчик, чистый стек, понятный scope
- ✅ Прототипы, экспериментальные фичи, greenfield

**Что ломается, когда vibe масштабируется на 100+ FTE:**

| Проблема | Что происходит без дисциплины |
|---|---|
| 🔴 **Architecture drift** | Каждый разработчик решает локально. Через 3 месяца — 5 разных подходов к одной задаче |
| 🔴 **Design system violations** | Hardcoded `#FF0000` вместо токена. UI разваливается в темах и брендинге |
| 🔴 **Regression на legacy** | Vibe-агент не знает ownership/threading C++/Qt → ломает signal/slot lifecycle. Не знает Compose state → утечки в Android |
| 🔴 **Knowledge fragmentation** | Знание «как мы делаем X» живёт в чатах конкретного разработчика. Уволился — знание потеряно |
| 🔴 **No traceability** | Где ADR? Где BRD? Что было запрошено vs что реализовано? Невозможно пройти security/compliance аудит |
| 🔴 **Onboarding не работает** | «Сядь, попробуй вайбить» — не воспроизводимо. Junior рост залипает |
| 🔴 **Quality drift** | Красивый код, который рушится в проде |

**Итог:** vibe сам по себе на 100+ FTE даёт не +400% velocity, а +30% + регресс в legacy. И добавляет 6 новых классов багов, которые раньше отлавливал code review.

---

## Slide 4 — Tacticum Dev = vibe + дисциплина

Tacticum Dev — это **enterprise profile orchestrator** для agentic CLI. Он не заменяет vibe-coding. Он *окружает* его дисциплиной, без которой vibe не масштабируется.

```
┌─────────────────────────────────────────────────────────────┐
│  TACTICUM PROFILE (per stack / per team)                    │
│                                                             │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────────────┐  │
│  │ Market MCP  │  │ Tacticum RE │  │  Tacticum Design    │  │
│  │  Bundle     │  │  Knowledge  │  │  Token Layer        │  │
│  │             │  │   Layer     │  │                     │  │
│  │ • Context7  │  │ • topology  │  │ • design_list_      │  │
│  │ • Serena    │  │ • behaviour │  │   systems           │  │
│  │ • GitLab    │  │ • ADR       │  │ • design_get_       │  │
│  │ • Sentry    │  │ • use cases │  │   theme_tokens      │  │
│  │ • Semgrep   │  │ • SBOM      │  │ • design_resolve_   │  │
│  │ • Playwright│  │ • SCIP      │  │   token             │  │
│  └─────────────┘  └─────────────┘  └─────────────────────┘  │
│                                                             │
│  ┌──────────────────────────────────────────────────────┐   │
│  │           Analysis Subagents                         │   │
│  │  BRD → ADR → PIN → TESTS → Traceability             │   │
│  │  Утверждение ДО реализации.                          │   │
│  │  Кодирование не стартует без approval.               │   │
│  └──────────────────────────────────────────────────────┘   │
│                                                             │
│  ┌──────────────────────────────────────────────────────┐   │
│  │           Quality Gates                              │   │
│  │  Tests · Coverage · Lint · SAST · Design compliance │   │
│  │  · A11y · Performance · Docs · Traceability         │   │
│  └──────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
```

**5 слоёв, которые отличают Tacticum Dev от голого vibe-CLI:**

1. **Профили вместо «каждый сам себе»** — каждый dev получает идентичный, отревьюенный набор инструментов под свой стек.
2. **RE Knowledge Layer** — агент *видит* архитектуру вашего проекта: topology-map, ADR, use cases, behaviour blocks. Не выдумывает. Реализован как DSL поверх Tacticum Platform RAG.
3. **Design Token Layer** — `design_resolve_token` физически делает hardcoded `#FF0000` невозможным. Поставляется как Dev MCP-сервер, хостящийся в Tacticum Platform MCP Runtime арендатора.
4. **Analysis Gate** — BRD/ADR/PIN/TESTS обязательно ДО кода. Feature Lifecycle исполняется на Tacticum Platform Workflow Spec Engine (durable, переживает перезапуски, поддерживает human-approval-этапы).
5. **Quality Gates** — автоматические проверки на каждом MR. Без green-gate агент не предлагает merge.

**Архитектурная подоснова:** Tacticum Dev — не самостоятельный монолит. Это **прикладное приложение поверх Tacticum Platform**: профили описываются как декларативные манифесты, Stack-Specific MCP Bundles хостятся в Platform MCP Runtime, RE Knowledge — слой над Platform Knowledge/RAG, Feature Lifecycle Workflow — на Platform Workflow Spec Engine. Это означает: те же quality-инварианты (multi-tenancy, AuthScope, sovereign-LLM, on-prem) — наследуются от платформы.

---

## Slide 5 — Tacticum-mcp и профили в workspace

**Один MCP-сервер — много инструментов. Profile = подписка на каталог.**

```
┌──────────────────────────────────────────────────────────────┐
│              TACTICUM-MCP (HTTP, bearer auth)                │
│              https://mcp.tacticum.dev/catalog                │
│                                                              │
│  Catalog tools          Workspace tools     Design tools     │
│  ─────────────────      ──────────────      ─────────────    │
│  list_profiles          install_profile     design_list_     │
│  get_profile            apply_profile         systems        │
│  get_manifest           list_installed      design_get_      │
│  list_ingredients                             theme_tokens   │
│                                             design_get_      │
│                                               tokens         │
│                                             design_resolve_  │
│                                               token          │
└──────────────────────────────────────────────────────────────┘
                              │
            ┌─────────────────┴───────────────────┐
            │                                     │
   ┌────────▼─────────┐                ┌─────────▼─────────┐
   │  Workspace #1    │                │  Workspace #2     │
   │  Mail team       │                │  Connect team     │
   │                  │                │                   │
   │  profile:        │                │  profile:         │
   │  android-        │                │  cpp-qt-          │
   │  brownfield      │                │  enterprise       │
   │                  │                │                   │
   │  4 agents        │                │  4 agents         │
   │  5 phases        │                │  5 phases         │
   │  8 skills        │                │  8 skills         │
   └──────────────────┘                └───────────────────┘
```

- **Multi-CLI:** Claude Code, Codex, VS Code Copilot — один профиль работает везде. Multi-CLI Adapter транслирует канонический ingredient-формат в CLI-специфичные артефакты.
- **Premium-gating:** design-tools и расширенные KB-tools — premium-tier. Базовый workflow — trial-tier для evaluation. Gating работает per-ingredient.
- **Sovereign-LLM:** Qwen, Kimi, GigaChat через локальный прокси. Production code и inference могут оставаться в периметре заказчика. Реализуется через Tacticum Platform LLM Gateway — единый шлюз, политика арендатора.

> **Где живёт tacticum-mcp:** это MCP-сервер Dev, регистрирующийся в Tacticum Platform MCP Runtime арендатора. Stack-Specific MCP Bundles (`tacticum-mcp-qt`, `tacticum-mcp-compose`, ...) — отдельные MCP-серверы Dev, устанавливаются рядом при apply профиля. Hosting, аудит и scope-aware вызовы — на стороне Platform.

---

## Slide 6 — 3 сценария разработки. Сравнение

**Центральный слайд деки.** Чем Tacticum Dev отличается от просто vibe-coding.

| Ось | No agents | Vibe coding | **Tacticum agentic** |
|---|---|---|---|
| **Velocity / FTE** | 🔴 baseline 1× | 🟡 ×3–5 на greenfield, ×1.5 на legacy | 🟢 **×4–6 устойчиво, любой стек** |
| **Defect rate на legacy** | 🟡 управляемо, медленно | 🔴 непредсказуемо — ломает threading / lifecycle / state mgmt | 🟢 quality gates ловят ДО merge |
| **Architecture compliance** | 🟡 ручной code review | 🔴 случайная — agent не видит ADR/topology | 🟢 **RE KB + ADR gate** — agent обязан соответствовать |
| **Design system compliance** | 🟡 manual enforcement | 🔴 hardcoded violations накапливаются | 🟢 **`design_resolve_token`** — hardcode структурно невозможен |
| **Regression risk на legacy** | 🟡 высокая, но видимая | 🔴 очень высокая — agent не знает domain | 🟢 stack-specific gates (qt_signal_slot_audit, compose_state_audit) |
| **Knowledge persistence** | 🟡 docs + tribal | 🔴 tribal — в чатах конкретного dev'а | 🟢 **KB ingredients, versioned, переживают любого dev'а** |
| **Onboarding нового dev'а** | 🟡 1–3 месяца | 🔴 1–3 мес + «нужен vibe-skill» | 🟢 **дни** — загрузил профиль → готов |
| **Auditability / traceability** | 🟡 manual ADR | 🔴 none | 🟢 BRD ↔ ADR ↔ PIN ↔ TESTS ↔ MR автоматически |

---

## Slide 7 — Глубина под стек

**Tacticum Dev — не «один общий profile под всё». Это stack-specific orchestration.**

### Пример: C++/Qt6 enterprise-профиль

Кастомные Qt MCP-tools, которых нет ни у одного market-агента:

| Tool | Что проверяет |
|---|---|
| `qt_detect_version` | Версия Qt и совместимость |
| `qt_cmake_audit` | CMake targets, QML modules, resources |
| `qt_cpp_qml_boundary_audit` | Граница C++/QML — что в C++, что в QML |
| `qt_model_view_audit` | QAbstractItemModel, roles, delegates, proxy models |
| `qt_signal_slot_audit` | Connection type, duplicate connections, lifecycle |
| `qt_threading_audit` | UI-thread safety, QThread, queued connections |
| `qt_qml_lint` / `qt_qml_format_check` | qmllint + qmlformat |
| `qt_i18n_audit` | Translations, locale-sensitive formatting |
| `qt_accessibility_audit` | Focus, labels, keyboard navigation |
| `qt_performance_audit` | QML bindings, rendering, startup, memory |

### Что это даёт на практике

**Vibe-агент на legacy Qt6 без stack-bundle:**

- ❌ Не знает что `QObject` ownership — parent-child
- ❌ Не знает что `signal connection type` влияет на threading safety
- ❌ Накопит memory leaks, deadlock'и в worker-thread
- ❌ Сломает QML binding evaluation order

**Tacticum-агент с qt-bundle:**

- ✅ `qt_signal_slot_audit` блокирует MR с broken lifetime
- ✅ `qt_threading_audit` ловит UI-thread violations до runtime
- ✅ `qt_cpp_qml_boundary_audit` не даёт business logic размазаться по QML

**Эта же глубина — для каждого стека:** Android (Compose state audit), iOS (SwiftUI lifecycle), Go (concurrency primitives audit), React/RN (hooks discipline), backend (transactional boundaries).

---

## Slide 8 — Reference-кейс: pilot в Android-команде

**Контекст пилота:** Android-команда (Kotlin / Compose / Ktor / Room), brownfield-кодовая база. Период: 4 недели. Метрики сверяются с 3-месячным baseline до пилота.

### Headline-результат

> **3 новые фичи закрыты за 1–2 дня одним разработчиком** (включая design + tests + docs).
> До пилота сопоставимый scope занимал ~5–7 дней с 2 разработчиками.

### Метрики (фиксируются в actual-таблицу при customer success)

| Метрика | Baseline (3 мес до) | Пилот (4 недели) | Δ |
|---|---|---|---|
| Median lead time (issue → merged MR) | ~5 рабочих дней | ~1.5 дня | **−70%** |
| Output на разработчика (закрытые фичи/неделя) | 0.6 | 1.5 | **×2.5** |
| Defect rate (severity ≥ medium / release) | 4 | 3 | **−25%** |
| Architecture compliance (% MR без отката после ревью) | ~60% | 85% | **+25 п.п.** |
| Design token violations / MR | 2–3 | 0 | **−100%** |

### Индустриальный benchmark для контекста

| Источник | Метрика | Значение |
|---|---|---|
| GitHub (Copilot productivity study, 2024) | Speed-up при использовании AI-ассистента | +55% |
| McKinsey (Generative AI in software development, 2024) | Доля высокопроизводительных разработчиков с AI-уплифтом | 35–60% time saved на типовых задачах |

Tacticum-уплифт устойчиво выше market-AI-tools за счёт RE KB и quality gates — потому что измеряется *закрытие фичи end-to-end*, а не только speed of typing.

---

## Slide 9 — Roll-out 6 месяцев: 4 профиля параллельно

**Типовой план для команд 100+ FTE.**

```
Месяц:   0          2          3           4          6
         │          │          │           │          │
Team 1 ──┼──────────────────────────────────────────────►  Production
(Pilot)  │          │          │           │          │
         │          │          │           │          │
Team 2   │          ╞══════════════════════════════════►  Production
         │          │ R&D start            │          │
         │          │          │           │          │
Team 3   │          │          ╞════════════════════════►  Production
         │          │          │ R&D start │          │
         │          │          │           │          │
Team 4   │          ╞══════════════════════════════════►  Production
         │          │ R&D start            │          │
         │          │          │           │          │
         │   Phase  │   Phase  │   Phase   │  Phase   │
         │   1      │   2      │   3       │   4      │
         │ baseline │ training │ ramp-up   │ full     │
         │ + KB     │ cohort 1 │ + cohort 2│ scale    │
         │ collect  │          │           │          │
```

Параллельные R&D-треки определяются по стекам и приоритетам клиента: brownfield (живой legacy), greenfield (новые продукты), backend microservices, desktop/mobile.

---

## Slide 10 — Workforce: top-30 ↑, middle-40 →, KB-coverage gate

**Главный принцип:** знание не уходит с людьми. Сначала KB, потом любые workforce-изменения.

### Типовая трансформация application-команды

```
ИЗ 100 application FTE                  СТАНОВИТСЯ ~50 FTE

┌─────────────────────────┐             ┌─────────────────────────┐
│  Top-30% (~30 FTE)      │             │  AGENT ARCHITECTS       │
│  Senior+ инженеры       │ ─────────► │  ~20 FTE                │
│                         │             │  comp +30–50%           │
│  Текущая роль:          │             │  ownership profile KB   │
│  пишут код              │             │                         │
└─────────────────────────┘             └─────────────────────────┘

┌─────────────────────────┐             ┌─────────────────────────┐
│  Middle-40% (~40 FTE)   │             │  AGENT-AUGMENTED        │
│  Mid-level инженеры     │ ─────────► │  ENGINEERS              │
│  + training cohort 1-2  │             │  ~30 FTE                │
│  + reassign to growing  │             │  выход ×2-3 vs baseline │
│    domains              │             │                         │
└─────────────────────────┘             └─────────────────────────┘

┌─────────────────────────┐             ┌─────────────────────────┐
│  Bottom-30% (~30 FTE)   │             │  EXIT после             │
│  Performance-based      │ ─────────► │  KB-coverage gate       │
│  процедуры по ТК        │             │                         │
└─────────────────────────┘             └─────────────────────────┘
```

### Knowledge Transfer Gate (обязательное условие)

> **Никакая волна сокращений не начинается, пока KB-coverage домена не валидирован.**

1. Identify domain X — тематика, за которую отвечает сотрудник.
2. Tacticum + dev пишут KB ingredients под этот домен (behaviour block, ADR, use cases, code-scenario-match).
3. Архитектор + заказчик валидируют, что профиль закрывает домен (тест фичи через agent).
4. Только тогда сотрудник может покинуть компанию по performance-процедуре.

**Это превращает workforce-план из риска потери знаний в *усиление* knowledge base.**

---

## Slide 11 — Risks & Mitigations

| # | Риск | Митигация |
|---|---|---|
| 1 | **Data security + ФЗ-152 / КИИ** — LLM-вызовы и код через внешние сервисы | **Sovereign LLM:** Qwen, Kimi, GigaChat через локальный прокси. Production code и inference в периметре заказчика. **On-prem** доступен в рамках расширенного контракта |
| 2 | **Vendor lock-in** — что если поднимется цена / Tacticum исчезнет | **Schema-based ingredients:** profile — открытая спецификация, exportable. **Source escrow** в контракте. **Transition clause:** handover профилей за N месяцев при разрыве |
| 3 | **Knowledge loss при workforce changes** | **KB-coverage gate:** сотрудник не уходит, пока домен не покрыт. Совместная валидация заказчик + Tacticum |
| 4 | **Top-30 саботаж / уход** — сильные инженеры не любят «пасти агентов» | **New role «Agent Architect»:** +30–50% comp, ownership profile KB. Они *выигрывают*, не теряют |
| 5 | **Quality drift / AI hallucinations** | **Mandatory quality gates:** tests, lint, SAST, design tokens, architecture compliance. Agent **не мерджит**, только предлагает MR |
| 6 | **Сжатые сроки для сложного стека** (Qt, embedded) | **Stack-specific профиль покрывает brownfield workflow** (новые фичи, bugfix) — не миграцию архитектуры |

### «А что если просто купим Cursor Enterprise / GitHub Copilot Enterprise?»

| Cursor / Copilot Enterprise | Tacticum Dev |
|---|---|
| Generic vibe-coding tool | Enterprise profile orchestrator |
| Не знает архитектуру вашего проекта | RE Knowledge layer с topology / ADR / use cases |
| Не знает design system | Design token enforcement через MCP |
| Не управляет quality gates | BRD/ADR/PIN/TESTS approval gate + stack-specific gates |
| Generic tools для всех | Stack-specific (Qt, Compose, Go, RN) bundles |
| Sales — на разработчика | Sales — на профиль / стек / команду |

> Cursor — это IDE с AI. Tacticum — это SDLC-layer над agentic CLI.
> Их можно использовать **вместе**: Tacticum-профиль конфигурирует Cursor / Copilot / Claude Code одинаково.

---

## Slide 12 — Training-as-service: re-skilling программа

Сократить можно только тех, чьи задачи закрыты агентами. Чтобы оставшиеся работали с агентами эффективно — нужна структурированная программа.

```
Cohort training (3 волны × 2 месяца каждая):

Cohort 1 (m1-m2):  Early adopters + lead team    ~30 человек
Cohort 2 (m3-m4):  Backend + cross-functional    ~50 человек
Cohort 3 (m5-m6):  Остальные команды             ~70 человек

Per cohort:
├── Неделя 1     Onboarding profile + market MCP basics
├── Недели 2-3   Hands-on: BRD/ADR/PIN/TESTS authoring
├── Недели 4-5   Design tokens + quality gates
├── Недели 6-7   Live проекты под supervision
└── Неделя 8    Certification + access к full-tier profile
```

| Компонент | Описание |
|---|---|
| Curriculum | Specific под стек заказчика + profile-aware |
| Mentors (Tacticum) | 2–3 senior engineer'а на cohort |
| Hands-on labs | Реальные задачи из backlog заказчика |
| Certification | Internal заказчика + Tacticum joint cert |
| Materials | Skill content, video, examples — IP заказчика после окончания контракта |
| Post-training support | 3 мес консультаций после cohort |

---

## Slide 13 — Cascade-ask + Next steps

```
1️⃣  PILOT
    └─ Команда A (Android/Go/RN на выбор): 4–6 недель
       Метрики: lead time, defects, architecture compliance
       
2️⃣  TRAINING CONTRACT
    └─ Cohort 1 (~30 человек) запускается через 2 недели после pilot
       
3️⃣  R&D НА N ПАРАЛЛЕЛЬНЫХ ПРОФИЛЯ
    └─ Greenfield, backend, mobile, desktop — параллельно
       Старт: m+2, готовность: m+4–6
       
4️⃣  WORKFORCE REORGANIZATION
    └─ Через 6 месяцев: 100% команд на agentic workflow
       Performance-based процедуры + KB-coverage gate
       
5️⃣  [OPTIONAL] EQUITY PATH ↔ ON-PREM
    └─ Strategic stake в Tacticum ↔ on-prem deployment +
       co-development governance
```

### Timeline после «yes»

| Неделя | Milestone |
|---|---|
| W+1 | Term sheet (pilot + training pricing) |
| W+2 | Контракт подписан, baseline data collection |
| W+3 | Pilot kick-off |
| W+5 | Промежуточные метрики pilot |
| W+8 | Pilot graduation, переход в production rollout |
| M+2 | Cohort 1 training kick-off |
| M+6 | Full agentic adoption across application teams |

### С чего начинаем

1. Discovery-звонок: 60 минут, аудит инженерной орг-структуры.
2. Готовим term sheet под конкретный pilot-кейс.
3. Pilot стартует через 1–2 недели после подписания.

**Связаться:** *(контакты команды Tacticum заполнить перед отправкой)*

---

## Glossary

- **MCP** — Model Context Protocol. Стандарт подключения внешних tools к AI-агентам.
- **Profile** — курируемый bundle MCP-серверов, skills, agents, commands для конкретного стека/роли.
- **Ingredient** — атомарный компонент профиля (skill / agent / command / mcp_server / instruction_pack).
- **KB / RE Knowledge** — Reverse Engineered knowledge base проекта: topology, behaviour, ADR, use cases, SBOM.
- **Design token** — атомарная единица design system (цвет, отступ, типография).
- **Quality gate** — автоматическая проверка перед merge (tests, lint, SAST, design compliance, traceability).
- **BRD / ADR / PIN / TESTS** — каскад артефактов феа-цикла: Business Requirements Document → Architecture Decision Record → Plan with Inputs → Tests strategy.

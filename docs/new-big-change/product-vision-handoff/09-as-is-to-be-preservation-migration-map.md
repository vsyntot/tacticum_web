# 09. AS IS To TO BE Preservation And Migration Map

Дата: 01.06.2026

Статус: рабочая карта сохранения, переупаковки и миграции текущего сайта в TO BE продуктовую модель.

## Назначение

Этот документ отвечает на практический вопрос: что именно из текущего сайта `tacticum.ru` должно сохраниться в целевой версии, в каком виде, а что должно быть переосмыслено, расширено или выведено из публичного слоя.

Главный принцип:

```text
TO BE = AS IS lead-generation engine + новый product-first слой
```

TO BE не должен уничтожать текущие рабочие коммерческие сценарии. Он должен добавить над ними продуктовую архитектуру `Platform / Agents / Dev / Forum`, сохранив формы, offer-каталог, staff-order, chat-to-lead, SEO и Bitrix component contracts.

## Migration Statuses

| Status | Meaning |
|---|---|
| `preserve` | Сохранить почти без изменений; допустим только визуальный polish |
| `preserve contract, redesign UI` | Внешний вид можно менять, но DOM/data/API contract сохранить |
| `reframe` | Смысл и копирайтинг меняются, техническая поверхность в целом сохраняется |
| `extend` | Сохранить AS IS и добавить новый продуктовый контекст |
| `migrate/alias` | Нужна SEO/URL миграция, alias или canonical decision |
| `add` | Новая сущность TO BE |
| `retire/private` | Убрать из public или оставить только в закрытых материалах |

## Page-Level Migration Map

| URL | AS IS role | TO BE role | Preserve | Change / Add | Status | Release note |
|---|---|---|---|---|---|---|
| `/` | Главный routing hub: расчет, внедрение, команда, AI-боты | Ecosystem homepage + routing hub | CTA, chat surface if useful, FAQ, proof areas, lead form flow | Hero, product narrative, ecosystem map, product cards, product-aware CTA | `reframe + extend` | First-release priority |
| `/services/` | AI/IT внедрение и проектная разработка | Delivery layer для внедрения продуктов и AI-интеграций | Service delivery story, CTA, FAQ, process | Связать с Platform/Agents/Dev/Forum as implementation paths | `reframe` | Не превращать в product catalog |
| `/price/` | Подбор специалистов, ставки, staff-order | Team / staff augmentation для внедрения и R&D | Filters, levels, team presets, persistent summary, monthly budget estimate, staff modal, backend endpoint | Визуально обновить; добавить связь с delivery/product workstreams | `preserve contract, redesign UI` | Не использовать как pricing для Platform/Agents/Dev/Forum |
| `/calculator/` | AI-калькулятор / chat-to-lead для оценки проекта | Product-aware qualification and estimate entry | Chat surface, handoff to lead CTA, FAQ, form contract | Добавить `product_interest`, deployment/timeline context, safe fallback states | `extend` | Требует lead-form contract review |
| `/offer/` | Каталог примеров расчетов и detail pages | Proof + estimate examples + conversion layer | Catalog, filters, detail pages, sitemap/canonical model, CTA, 404 behavior | Product tags, product-specific examples, stronger proof taxonomy | `extend` | Критичный AS IS актив, не ломать |
| `/offer/<code>/` | Detail example with CTA | Product-linked proof/detail page | Detail template, offer context, prefill/group_id flow | Product relation, clearer claim/source model | `extend` | Must keep current SEO/detail behavior |
| `/aiagents/` | AI-боты и Telegram-агенты | Tacticum Agents page or legacy compatibility URL | Existing URL, FAQ, agent/scenario content, lead flow | Reframe as corporate Agents product over Platform | `migrate/alias` | First release: safest to preserve URL and content-migrate |
| `/contacts/` | Контакты, CTA, legal, map | Operational contact + trust/legal page | Phone, email, map iframe, legal address, lead CTA | Layout polish, clearer split: sales/contact/legal/docs | `preserve` | Keep factual data exact |
| `/about/` | Компания, команда, вакансии, trust | Enterprise credibility / vendor trust page | Team, vacancies, company proof, CTA | Add product vendor credibility, compliance/process story | `reframe + extend` | Not first blocker for product release |
| `/policies/` | Legal content | Legal and consent base | Content detail, typography, routes | Better legal typography/anchors if needed | `preserve` | Must remain stable for forms |
| `/platform/` | None | Tacticum Platform product page | N/A | New page: platform modules, architecture, deployment, security | `add` | First-release priority |
| `/agents/` | None or future canonical for Agents | Tacticum Agents product page | N/A | New canonical only after `/aiagents/` SEO decision | `add / migrate` | Do not rush if SEO risk |
| `/dev/` | None | Tacticum Dev product page | N/A | New page with safe governance/productivity framing | `add` | Remove workforce reduction public claims |
| `/forum/` | None | Tacticum Forum product page | N/A | New page: scenario+LLM, CX/contact-center use cases | `add` | Product proof needs evidence |
| `/cases/` | Not a standalone AS IS hub | Product-specific proof hub | Existing cases iblock/template if used | Optional case library and product filters | `add later` | Defer unless proof content ready |

## Component-Level Migration Map

| Component / Area | AS IS source | TO BE action | Contract to preserve | Notes |
|---|---|---|---|---|
| Header | `local/templates/tacticum/header.php` + top menu template | Product-first navigation | `[data-tacticum-menu-toggle]`, mobile menu ids/classes, header CTA behavior | Add product dropdown without breaking mobile menu |
| Mobile menu | `menu.js`, mobile menu template | Redesign allowed | `#tacticum-mobile-menu`, `.tacticum-mobile-menu-close`, `.tacticum-contact-btn`, `aria-*` behavior | Need landscape/scroll-safe design |
| Footer | `local/templates/tacticum/footer.php`, bottom menu | Reframe into product/service/contact/legal zones | Telegram resolver attribute if used | Avoid losing current contacts/legal links |
| Contact modal | `local/components/tacticum/contact.modal` | Preserve contract, redesign UI | `#tacticum-modal`, `#tacticum-modal-form`, close/focus trap behavior | Global conversion fallback must remain |
| Lead CTA | `local/components/tacticum/lead.cta` | Extend variants and product-aware context | `[data-tacticum-form]`, `data-form-id`, consent, optional close target | Core component for TO BE |
| Chat surface | `local/components/tacticum/chat.surface` | Reframe as qualification/handoff surface | `[data-tacticum-chat]`, `[data-chat-input]`, `[data-chat-send]`, `[data-chat-messages]` | Do not make chat decorative if it affects lead flow |
| FAQ section | `local/components/tacticum/faq.section` | Extend to product FAQ | `.faq-item`, `.faq-question`, `.faq-answer`, `.active` | Product-specific FAQ may need section keys |
| Content list | `local/components/tacticum/content.list` | Reuse for lists/cards | Component params, iblock key usage | Keep page entries thin |
| Content detail | `local/components/tacticum/content.detail` | Reuse for legal/detail content | Component params, sanitizer behavior | Good base for content-backed details |
| Offer section | `local/components/tacticum/offer` | Preserve and extend proof model | list/detail/not_found dispatch, SEO behavior | High-risk to rewrite from scratch |
| Offer catalog | `local/components/tacticum/offer.catalog` | Extend with product relation | filters, pagination, canonical/noindex decisions | Product tags require content model decision |
| AI Agents page component | `local/components/tacticum/aiagents` | Reframe/migrate to Agents product | Scoped body class only if still needed | Avoid isolated visual language in TO BE |
| Price component | `news.list/price` template + script | Preserve complex flow | data-price contracts, staff modal, `workers_json`, endpoint override | Treat as productized configurator |
| Services list | `news.list/services` | Reframe as delivery cards | Existing content list usage | Do not use for Platform/Agents/Dev/Forum product cards unless model fits |
| Cases list | `news.list/cases` | Become product proof cards | Existing case data/template | Needs claim approval |
| Feedback list | `news.list/feedback` | Use only approved testimonials | Existing list/template | Legal/customer approval required |
| Team/vacancies | `news.list/team`, `news.list/vacancies` | Company trust/careers | Existing content helpers | Keep under `/about/` unless new careers page |
| Policy detail | `news.detail/policies` | Preserve | Legal text route/content | Legal-critical |

## Form And CTA Migration Map

| Flow | AS IS behavior | TO BE behavior | Preserve | Change / Review |
|---|---|---|---|---|
| Generic lead CTA | Required `name`, `email`, `phone`, `message`, consent | Product-aware lead CTA | `data-tacticum-form`, `data-form-id`, endpoint, CSRF, success/error states | Add hidden/context fields only after contract update |
| Header/contact modal | Global contact fallback | Still global fallback | Modal ids, focus trap, form contract | Copy can become “Обсудить пилот” / “Связаться” |
| Homepage CTA | General project calculation | Product ecosystem qualification | Existing lead component | Add product/entry context |
| Product page CTA | Not present AS IS | Product-specific CTA | Reuse lead CTA | `product_interest`, `deployment_interest`, `timeline`, `source_page` |
| `/calculator/` chat handoff | Chat result can feed lead form | Product-aware estimate handoff | Chat/form contracts, safe analytics | Define exact allowed context fields |
| `/price/` staff order | Rich staff payload through domain endpoint | Preserve as team/staff flow | `workers_json`, presets, budget estimate, endpoint override | Do not merge with product license pricing |
| `/offer/` detail CTA | Offer context/prefill | Product-linked offer CTA | group/offer context if used, detail route behavior | Add product tag/source if approved |
| `/aiagents/` form | AI bot lead | Agents product lead | Existing form handling | Reframe copy and form context |

## JS And Interaction Contract Map

| Interaction | AS IS script | TO BE action | Preservation rule |
|---|---|---|---|
| Mobile menu | `menu.js` | Redesign allowed | Keep selectors or update script deliberately |
| Contact modal | `modal.js` | Preserve | Do not rename modal ids casually |
| Forms | `forms.js` | Extend carefully | Keep validation, CSRF, same-site endpoint rule, no PII analytics |
| Analytics | `analytics.js` | Extend taxonomy | No raw user text, contacts, message, PII, sensitive URL query |
| Telegram resolver | `tg-link-resolver.js` | Preserve if Telegram links remain | Lazy/on-click only |
| FAQ | `faq.js` | Preserve or replace with explicit migration | If selectors change, update script and smoke |
| Chat | `chat-agent.js` | Extend only with QA/security review | Keep message scroll, handoff, no PII analytics |
| Price builder | `news.list/price/script.js` | Preserve | Highest-risk interactive AS IS component |
| Charts | `charts.js` | Preserve if `/price/` still uses charts | Optional page asset only |
| Yandex map | iframe on `/contacts/` | Preserve | Do not reintroduce map constructor unless needed |

## Content And Iblock Migration Map

New code must keep using config helpers for iblock IDs. The table uses iblock keys, not numeric IDs.

| Iblock key / content area | AS IS role | TO BE role | Action |
|---|---|---|---|
| `services` | Services cards/content | Delivery layer content | `reframe`; not the primary product taxonomy unless content model is changed |
| `rates` | Price/staff rates | Team/staff augmentation data | `preserve`; keep separate from product licensing |
| `offer` | Offer examples and details | Proof + estimate examples | `extend`; add product relation only through approved content model |
| `cases` | Case cards | Product-specific proof | `extend`; require claim approval |
| `faq` | Shared FAQ sections | Product, delivery, security FAQ | `extend`; likely needs semantic section keys |
| `aiagents` | AI agents cards/content | Agents scenarios/templates | `reframe`; may remain source for `/aiagents/` |
| `feedback` | Testimonials | Approved testimonials only | `restrict`; legal/customer approval required |
| `team` | Team trust | Company credibility | `preserve/reframe` |
| `vacancies` | Careers | Company/careers proof | `preserve` |
| `policies` | Legal content | Legal base for forms | `preserve` |
| Product facts | Not present | Platform/Agents/Dev/Forum page data | `add`; decide static docs vs new iblock/content type |
| Claim evidence | Not structured in Bitrix | Source of truth for proof/public copy | `add`; start as docs/register before CMS model |

## SEO And URL Migration Map

| URL / Area | AS IS SEO value | TO BE decision | Guardrail |
|---|---|---|---|
| `/` | Main indexed landing | Reframe in place | Keep one H1, canonical `/`, update metadata |
| `/services/` | Existing service URL | Preserve as delivery page | Do not redirect to products |
| `/price/` | Existing money page | Preserve as team/staff page | Do not repurpose as product pricing |
| `/calculator/` | Existing estimate URL | Preserve as qualification/calculator | Maintain chat/form behavior |
| `/offer/` | Existing offer hub | Preserve and strengthen | Keep sitemap/detail/canonical model |
| `/offer/<code>/` | Indexable detail pages | Preserve | No regression in detail route or 404/noindex behavior |
| `/aiagents/` | Existing AI agents URL | Preserve initially or migrate carefully | Decide canonical before adding `/agents/` |
| `/contacts/` | Contact/legal trust | Preserve | Keep legal/policy links |
| `/about/` | Company trust | Preserve/reframe | No urgent SEO migration |
| `/policies/` | Legal | Preserve | Must remain linked from forms |
| `/platform/` | New | Add indexable product page if content ready | Add sitemap/canonical/meta |
| `/agents/` | New or canonical | Add only with `/aiagents/` strategy | Avoid duplicate Agents pages |
| `/dev/` | New | Add indexable if public claims safe | Remove workforce reduction claims |
| `/forum/` | New | Add indexable if proof/copy ready | Validate claims and metadata |

## Proof And Claims Migration Map

| AS IS / Source | TO BE use | Rule |
|---|---|---|
| Offer examples | Product proof and estimate examples | Preserve; add product tagging only after content model decision |
| Cases | Product-specific proof | Use only verified metrics and safe copy |
| Feedback/testimonials | Trust blocks | Publish only with approval |
| Client logos from prototype | Logo strip | Do not publish without written approval |
| Team/about content | Vendor trust | Preserve and connect to enterprise credibility |
| Regulatory/security claims | Security/procurement blocks | Use only approved wording from claim register |
| Benchmarks | Contextual proof | Need exact source and careful paraphrase |
| Dev pilot metrics | Dev proof | Use only with methodology/evidence or mark as reference pilot |
| Workforce reduction claims | Public site | `retire/private` |

## Design System Migration Map

| AS IS | TO BE | Action |
|---|---|---|
| Brand blue `#0066CC` and navy `#001F3F` | Enterprise product palette | Likely preserve as core brand tokens, normalize names |
| Tailwind utility-heavy templates | Formal token/component system | Gradual migration, avoid big-bang rewrite |
| `styles/global.css` | Shared runtime styles | Keep as implementation target, avoid returning active CSS to `template_styles.css` |
| Remix Icon | Product icon taxonomy | Decide keep/migrate; do not mix many icon systems |
| Cards without base component | Product/proof/service/card system | Normalize card anatomy |
| Existing form fields | Formal form component spec | Preserve behavior, redesign states |
| Chat visual | Conversational/qualification component | Decide whether chat remains key conversion tool |
| Price builder UI | Configurator pattern | Design as dedicated complex flow |

## Migration Phases

### Phase A - Freeze AS IS Contracts

- List all selectors/contracts that cannot change without JS updates.
- Freeze existing URL inventory and SEO behavior.
- Confirm current form IDs and endpoints.
- Mark `/offer/`, `/price/`, forms and chat as high-risk.

### Phase B - Add Product Layer

- Add homepage ecosystem narrative.
- Add product navigation.
- Add Platform and product pages.
- Keep current commercial entry pages linked and visible.

### Phase C - Reframe Existing Pages

- `/services/` becomes delivery layer.
- `/aiagents/` becomes Agents or compatibility page.
- `/offer/` gets product proof relation.
- `/calculator/` gets product-aware qualification.
- `/price/` remains team/staff configurator.

### Phase D - Harden Proof, Forms, SEO

- Close claim register.
- Update lead form contract.
- Update analytics taxonomy.
- Run SEO/canonical/sitemap checks.
- Run browser and manual success-flow smoke.

## Acceptance Checklist

Before TO BE release:

- [ ] Every AS IS public URL has a target role.
- [ ] No existing money page is removed without SEO decision.
- [ ] `/offer/` list/detail behavior is preserved.
- [ ] `/price/` staff-order behavior is preserved.
- [ ] `/calculator/` chat-to-lead behavior is preserved or explicitly replaced.
- [ ] `/aiagents/` canonical/alias strategy is decided.
- [ ] All forms keep required field/consent/CSRF behavior.
- [ ] Product-aware fields are documented in `lead-form-contract.md`.
- [ ] Analytics remains no-PII.
- [ ] New product pages have metadata/canonical/sitemap decisions.
- [ ] Claim register blocks unapproved public claims.
- [ ] Design migration map covers existing components.
- [ ] QA smoke includes old and new flows.

## Practical Challenge To Current TO BE

Current TO BE is directionally correct, but implementation must not treat AS IS as disposable. The strongest current assets are:

- `/offer/` as proof and estimate catalog;
- `/price/` as complex staff configurator;
- `tacticum:lead.cta` as reusable conversion component;
- `forms.js` and REST form contract;
- `chat.surface` + chat-to-lead behavior;
- existing SEO URL inventory;
- Bitrix local component structure.

The first TO BE release should protect these assets and add product architecture around them. A redesign that weakens any of these without an explicit migration decision should be treated as a regression.


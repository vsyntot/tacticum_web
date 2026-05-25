# Final Stabilization Challenge Gap Analysis

Дата: 25.05.2026
Статус: final challenge completed; Sprint 16 code/docs closure applied locally, external gates pending
Workflow lane: Full Feature + Security / Integration gates

## Context

Этот документ фиксирует финальный challenge публичного сайта после Sprint 15 Product Marketing Architecture. Цель challenge - не найти ещё один бесконечный backlog, а проверить, можно ли считать сайт стабилизированным и находящимся в целевом состоянии.

Проверка выполнена в трёх разрезах:

- продуктово: понятность коммерческих входов, пользовательские пути, лид-контекст, доверие, соответствие обещаний фактическим сценариям;
- маркетингово: positioning, CTA, proof, SEO/segmentation, аналитика и измеримость воронки;
- технологически: Bitrix/runtime contracts, CSS/JS/render stability, REST/security gates, deploy/cache/release evidence.

## Evidence Read

- `docs/workflow/README.md`
- `docs/workflow/current-state.md`
- `docs/workflow/gap-analysis.md`
- `docs/workflow/product-marketing-gap-analysis.md`
- `docs/workflow/sprints/2026-05-25-sprint-15-product-marketing-architecture.md`
- `docs/workflow/post-deploy-smoke.md`
- `docs/workflow/lead-form-contract.md`
- public pages: `/`, `/services/`, `/price/`, `/calculator/`, `/offer/`, `/aiagents/`, `/contacts/`, `/about/`
- components: `tacticum:lead.cta`, `tacticum:chat.surface`, `tacticum:offer.catalog`, offer detail template, `forms.js`, `chat-agent.js`

Commands used during challenge:

```bash
npm run gaps:known
npm run release:signoff:summary -- docs/workflow/release-signoff-2026-05-24-post-deploy.draft.json
rg -n "98%|15\\+|гарант|создай|начни продавать|за 60 секунд|Special Offers|constructor|to-primary|lead_" ...
```

Focused rendered evidence from the contact-form regression investigation:

- production `/contacts/` focused smoke passed runtime checks but rendered old/cached form layout without local CSS/template updates;
- CSS-local smoke for `/contacts/` showed fixed select labels after local CSS injection;
- rendered map screenshots showed a non-Moscow map area (`Newington Forest` / `Laurel Hill`) while page text claims Moscow address `119285, г. Москва...`.

## Executive Decision

The site is close to target state. After Sprint 16 local implementation, code/docs stabilization gaps are closed, but it is still not fair to call the site fully stabilized until external release gates are evidenced.

Reasons:

1. The trust/render issues found in this challenge now have local code fixes.
2. The final product/marketing architecture is implemented in code, but not yet validated by deploy/cache smoke and conversion evidence.
3. Calculator/price chat-to-lead handoff now has a local implementation, but still needs post-deploy/manual smoke.
4. Release sign-off still has pending external gates that directly affect the business claim "leadgen flow works end to end".

Target state can be declared only after Sprint 16 code changes are deployed/cache-smoked and remaining non-code items have evidence or explicit accepted-risk approval with owner, due date and evidence rule.

## Sprint 16 Resolution Summary

| Gap | Result |
|---|---|
| `FSC-001` | Closed locally: `/contacts/` no longer renders the wrong placeholder/constructor state; it uses a Yandex map widget iframe with Moscow office coordinates and direct route link |
| `FSC-002` | Closed locally: offer detail estimate block now has explicit `bg-gradient-to-r from-secondary to-primary text-white` classes |
| `FSC-003` | External handoff: deploy workflow now deletes stale public-section files, clears menu cache and checks real draft sign-off, but production deploy/cache evidence is still required |
| `FSC-004` | External handoff: real forms/chat/staff upstream evidence still requires staging/controlled production access |
| `FSC-005` | Closed locally: `/calculator/` and `/price/` light chat can transfer safe context/group id into the page CTA |
| `FSC-006` | External handoff: Metrika goal visibility still requires Яндекс.Метрика access |
| `FSC-007` | Closed locally: default `specialoffer.jpg` was suppressed; personal-offer CTA collapses to a form-only layout when no credible image is provided |
| `FSC-008` | Closed: `docs/workflow/proof-claims-matrix.md` defines allowed/forbidden claims; remaining numeric runtime claims were removed |
| `FSC-009` | Accepted: current `/offer/catalog/...` noindex strategy is target state for stabilization |
| `FSC-010` | Accepted: CSP report-only is target state for stabilization; enforce is future Security / Integration scope |
| `FSC-012` | Closed locally: `/contacts/` CTA now appears before legal details |

## Product Challenge

### What Holds

- The site now has a coherent product ladder:
  - estimate/project examples: `/offer/`, `/calculator/`;
  - AI implementation: `/services/`;
  - managed team: `/price/`;
  - AI bot prototype: `/aiagents/`.
- Shared CTA context is materially better than before Sprint 15: forms pass page role, intent, CTA and next-step context.
- `/offer/` correctly frames examples as orientation, not final estimates.
- `/price/` is no longer positioned as a commodity rate table only.
- `/aiagents/` tone is now B2B-service oriented instead of standalone B2C/SaaS hype.

### Product Gaps

1. The route architecture is clear on-page, but not yet proven through real funnel evidence.
   A user can choose paths, but we do not yet have validated goal/analytics evidence showing that route cards, CTA taxonomy and forms work as a measured funnel.

2. Light AI chat surfaces do not hand off enough context to lead forms.
   The hero chat can prefill the home CTA using `group_id` / summary. The light chat surfaces on `/calculator/` and `/price/` keep `groupId` inside `chat-agent.js` closure and do not expose a clear "send this calculation to form" path. As a result, a user may discuss a project with the AI calculator, then submit a form that only contains page-level `lead_*` context unless they manually copy details.

3. `/contacts/` contains a trust-breaking rendered map mismatch.
   The page address is Moscow, but rendered smoke shows the map centered around `Newington Forest` / `Laurel Hill`. For a contact page, this is a trust issue, not a cosmetic issue.

4. Offer detail may fail at the most commercially important block.
   `local/templates/tacticum/components/bitrix/news.detail/offer/template.php` uses `class="to-primary ... text-white"` for the estimate block without a `bg-gradient-*` / `from-*` background class. This can render white text over a white/transparent background and damage the "preliminary estimate" conversion bridge.

## Marketing Challenge

### What Holds

- Risky claims such as `98%`, `15+ лет`, "гарантия результата", "создай за 60 секунд", "начни продавать" were removed from public runtime copy found by targeted scan.
- CTA wording is more page-specific than before Sprint 15.
- `/offer/` segmentation uses existing noindex filter states, avoiding a premature SEO duplicate-content problem.
- Analytics documentation explicitly keeps `lead_*` context out of analytics event params.

### Marketing Gaps

1. Conversion measurement is not closed.
   `metrika-goals` remains pending. Until affected form/chat/staff-order goals are confirmed in Yandex.Metrika, marketing cannot say the new product ladder is measurable.

2. Proof system is safer but still not fully evidence-backed.
   The current runtime copy avoids the worst claims, but the site still relies on broad proof themes: experience, industries, technology stack, examples. There is no final proof matrix that states which numbers/claims are allowed, where they are used, and which case/content source supports them.

3. Some visuals remain off-brand or weakly connected to the business proof.
   The default lead CTA image `specialoffer.jpg` visibly contains English "Special Offers" style imagery in smoke. It does not prove Tacticum's delivery capability, office, product, or actual project artifact. On trust-sensitive pages, this can reduce perceived credibility.

4. Industry/scenario segmentation is product-useful but not yet a marketing growth system.
   Current `/offer/catalog/...` states intentionally remain `noindex,follow`. That is safe. But if the target marketing state includes SEO acquisition by industry/scenario, there is still no decision on which clusters deserve indexable landing pages, unique copy, sitemap inclusion and canonical rules.

5. Contact/legal content is operationally correct but visually heavy.
   The contact page and footer expose detailed legal/IT activity text. This supports trust/compliance, but the current presentation can compete with the commercial CTA. This is not a blocker, but it should be an explicit UX/content decision.

## Technology Challenge

### What Holds

- Custom code is isolated in `local/`; `bitrix/` remains out of scope.
- REST bootstrap and no-PII logging rules are strong.
- Static guards exist for Bitrix architecture, CSS build, template styles, SEO, known gaps and release sign-off.
- Public pages use wrapper components and explicit page assets rather than URL heuristics.
- Before this final challenge was registered in `gap-analysis.md`, `gaps:known` reported `Code-level open/in-progress gaps: 0`; after registration it intentionally reports the new `FSC-*` open gaps.

### Technology Gaps

1. Current working tree is not deployed and post-deploy/cache-smoked.
   Sprint 15 is implemented locally and automated checks passed, but deploy/cache refresh is still pending. The `/contacts/` select-label issue demonstrated a real mixed cache/render risk: production may continue to serve old component HTML/CSS until cache is cleared.

2. External release gates remain pending.
   `manual-success-flow`, `metrika-goals`, `bitrix-admin`, and `staff-sale-upstream` are still pending in release sign-off. These are not optional if the site is to be called stable.

3. Map correctness is not covered by an automated guard.
   `visual:smoke` verifies runtime/render health but does not know that the contact map must point to Moscow. The wrong-map issue can pass current smoke.

4. Offer detail contrast/background is not explicitly covered.
   Existing smoke covers public pages and `/price/` actions, but not enough visual assertions for a valid `/offer/<code>/` detail estimate block. The orphan `to-primary` class is an example of a render defect that static checks did not catch.

5. Local PHP CLI remains unavailable.
   This is already documented as degraded local state with GitHub PHP 8.4 lint fallback. It is not a release blocker if CI runs, but it means local final challenge cannot fully prove PHP syntax.

6. CSP remains report-only.
   This is an accepted rollout pattern, but not a final enforcing security posture. If "target state" means security-hardening complete, CSP enforcement still needs report triage, vendor allowances and rollback plan.

## Gap Register

| ID | Priority | Dimension | Status | Summary | Evidence | Target State |
|---|---|---|---|---|---|---|
| FSC-001 | P1 | Product + Tech | closed locally | `/contacts/` map appeared to render non-Moscow geography | Smoke screenshot showed `Newington Forest` / `Laurel Hill`; page address is Moscow | Yandex map widget iframe uses Moscow office coordinates and route link; post-deploy smoke still required |
| FSC-002 | P1 | Product + Tech | closed locally | Offer detail estimate block could render white text on missing background | `news.detail/offer/template.php` had `class="to-primary ..."` without gradient/background class | Estimate block has explicit gradient/background and contrast; valid offer detail smoke still required after deploy |
| FSC-003 | P1 | Tech + Release | external handoff | Current Sprint 16 tree is not deployed/cache-smoked | Deploy workflow hardened for stale files/menu cache; production evidence still missing | Deploy/cache refresh done; `visual:smoke:prod`, `browser:smoke:prod`, `browser:smoke:price`, `seo:check:prod` pass |
| FSC-004 | P1 | Product + Integration | external handoff | Real lead/chat/staff success-flow not externally confirmed | `manual-success-flow` and `staff-sale-upstream` pending | Controlled staging/production evidence confirms forms, chat, prefill and staff payload reach upstream safely |
| FSC-005 | P1 | Product + Tech | closed locally | Light calculator/price chat context was not handed to CTA forms | `chat-agent.js` kept light-chat `groupId` in closure and appended no lead handoff/prefill action | Calculator/price chat can transfer safe summary/group context to CTA without PII analytics |
| FSC-006 | P1 | Marketing + Analytics | external handoff | Marketing funnel measurement not confirmed | `metrika-goals` pending | Metrika goals/events confirmed for affected form/chat/staff-order flows; evidence contains no PII |
| FSC-007 | P2 | Marketing + Design | closed | Default lead CTA image was generic/stock-like and could weaken trust | `tacticum:lead.cta` default `specialoffer.jpg`; rendered smoke showed English "Special Offers" visual | Default image suppressed; form-only layout handles no-image CTA |
| FSC-008 | P2 | Marketing | closed | Proof system lacked a final evidence matrix | Safer copy existed, but no single matrix of allowed claims/evidence | `proof-claims-matrix.md` defines allowed claims, forbidden formulations and source rules |
| FSC-009 | P2 | Marketing + SEO | accepted | Industry/scenario clusters are noindex-only | `/offer/catalog/...` remains `noindex,follow`; safe but not growth-oriented | Accepted noindex strategy for stabilization; indexable clusters require future SEO scope |
| FSC-010 | P2 | Tech + Security | accepted | CSP remains report-only | Current state documents report-only rollout | Report-only accepted as target state for now; enforce rollout is future Security / Integration scope |
| FSC-011 | P3 | Tech | accepted risk | Local PHP CLI unavailable | `dev:preflight` degraded; PHP 8.4 CI fallback documented | CI/deploy PHP lint remains authoritative; local PHP install optional |
| FSC-012 | P2 | UX + Content | closed locally | Contact/legal content may overpower commercial CTA | Contacts/footer include long legal/IT activity text | `/contacts/` now shows CTA before legal details; footer remains compliance/trust surface |

## Stabilization Blockers

The following still block final stabilization declaration:

- `FSC-003` deploy/cache/post-deploy smoke for current tree;
- `FSC-004` real success-flow and staff upstream evidence;
- `FSC-006` Metrika goals evidence.

`FSC-007` - `FSC-012` can be closed as code/docs or accepted with explicit owner and due date, but they should not silently disappear.

## Non-Goals For This Challenge

- Не менять код в рамках самого challenge-документа.
- Не закрывать внешние gates синтетически.
- Не создавать новые индексируемые URL без SEO gate.
- Не включать CSP enforce без report-only triage.
- Не записывать PII в evidence.

## Recommended Sprint

Все P1/P2 gaps из этого документа упакованы в Sprint 16:

- `docs/workflow/sprints/2026-05-25-sprint-16-final-stabilization-closure.md`

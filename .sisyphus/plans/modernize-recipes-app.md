# Modernize Recipes App

## TL;DR
> **Summary**: Stabilize the legacy PHP recipe manager in place: add executable regression coverage, harden the unsafe write paths, retire brittle integrations, then modernize the shared page shell and edit flows without a framework rewrite.
> **Deliverables**:
> - executable smoke coverage for the six core flows
> - safe request/query/output handling across mutation endpoints
> - removed or disabled dead remote integrations and legacy UI dependencies
> - shared shell and cleaner UI for read-only and edit pages
> - atomic commit history grouped by stabilization, security, dependency, and UI layers
> **Effort**: Large
> **Parallel**: YES - 5 waves
> **Critical Path**: 1 -> 2 -> 5 -> 6 -> 7

## Context
### Original Request
Turn the recent analysis into an execution plan for modernizing the app, cleaning up the UI, and fixing broken functionality.

### Interview Summary
- The app has already been moved from Composite ODBC to `mysqli` and now uses a local `config.local.php` path for secrets.
- Recent regressions showed that schema compatibility objects and shared rendering logic can break core flows, so the first wave must protect recipe search, detail, edit, ingredient edit, and recipe ingredient management before deeper changes.
- The user wants modernization without a framework rewrite and wants the app to remain practical in the current environment.

### Metis Review (gaps addressed)
- Resolved the content-policy ambiguity by preserving stored recipe instruction HTML for now and aggressively escaping non-rich-text output everywhere else.
- Resolved the nutrition import ambiguity by disabling outbound scraping in normal flows rather than replacing it immediately.
- Resolved schema-scope ambiguity by defaulting to app-level changes only; schema changes are allowed only for isolated safety or compatibility fixes with dedicated commits.
- Resolved the editor replacement ambiguity by selecting CKEditor 5 as the default modern WYSIWYG replacement, with TinyMCE as the fallback alternative if licensing or integration constraints change.

## Work Objectives
### Core Objective
Make the legacy PHP app safe enough to evolve, reliable on current PHP/MySQL, and noticeably easier to use, without changing its basic workflow model or rewriting it into a framework.

### Deliverables
- Shared executable regression baseline for the six core app flows
- Safe DB/query/request/output primitives used by active endpoints
- Modernized handling of write and destructive flows
- Dead or brittle external integrations disabled or replaced with safe fallbacks
- CKEditor 5 replacing the current FCKeditor-based recipe instructions editor
- Ingredient nutrition import redesigned around API-backed lookup plus manual fallback
- Shared shell and coherent styling/navigation across read-only and edit pages
- Final regression evidence and atomic commit history

### Definition of Done (verifiable conditions with commands)
- `php -l header.inc index.php ingredients.php showrecipe.php editrecipe.php editingredient.php editrecipeingredients.php pricereport.php server.php getdata.php odbctest.php`
- Core smoke scenarios pass via agent-executed browser/HTTP verification for recipe search, ingredient search, recipe detail, recipe edit, ingredient edit, and recipe ingredient management.
- No active write endpoint concatenates raw request values directly into SQL.
- No normal user flow depends on outbound nutrition scraping or Prototype/Scriptaculous.
- Read-only pages and edit pages share a common shell and remain usable at desktop and mobile widths.

### Must Have
- Preserve the existing procedural PHP structure in the first modernization wave.
- Keep current schema semantics unless a small isolated compatibility repair is necessary.
- Preserve recipe instruction content as stored HTML for now.
- Keep local credential handling in `config.local.php` and do not commit secrets.
- Replace FCKeditor with a modern editor that works in a plain server-rendered PHP page.
- Replace nutritiondata.com scraping with a supported import path.

### Must NOT Have (guardrails, AI slop patterns, scope boundaries)
- No framework/router rewrite.
- No speculative schema redesign or data migration project.
- No mixed security, dependency, and UI changes in a single commit.
- No reliance on manual-only verification.
- No new remote dependency added to replace broken scraping in this phase.

### Replacement Defaults
- **WYSIWYG editor**: CKEditor 5 classic editor, loaded in a plain HTML form and bound to the existing recipe instructions textarea. Reason: documented vanilla-JS integration, explicit PHP form examples, and migration guidance for legacy HTML content.
- **Editor fallback**: TinyMCE if CKEditor 5 packaging/licensing friction is unacceptable during execution.
- **Nutrition source (phase 1)**: USDA FoodData Central search/detail API for ingredient-level lookup, keyed by a server-side API key kept out of git.
- **Nutrition fallback**: manual nutrition entry remains available if API lookup fails or no match is found.
- **Deferred nutrition upgrade**: Edamam or Nutritionix are optional later upgrades only if you want richer NLP recipe analysis or commercial food coverage beyond USDA.

## Verification Strategy
> ZERO HUMAN INTERVENTION - all verification is agent-executed.
- Test decision: tests-after + Playwright/browser smoke coverage + PHP lint commands
- QA policy: Every task includes executable happy-path and failure/edge-path scenarios
- Evidence: `.sisyphus/evidence/task-{N}-{slug}.{ext}`

## Execution Strategy
### Parallel Execution Waves
> Target: 5-8 tasks per wave. Shared foundations are pulled into the earliest waves for maximum parallelism.

Wave 1: baseline verification foundation
Wave 2: shared safety helpers
Wave 3: write-path hardening, brittle integration retirement, read-only UI shell
Wave 4: edit-flow UX modernization
Wave 5: final regression and atomic landing

### Dependency Matrix (full, all tasks)
| Task | Depends On | Blocks |
|------|------------|--------|
| 1 | none | 2, 3, 4, 5, 6, 7 |
| 2 | 1 | 3, 4, 5, 6, 7 |
| 3 | 1, 2 | 6, 7 |
| 4 | 1, 2 | 6, 7 |
| 5 | 1, 2 | 6, 7 |
| 6 | 3, 4, 5 | 7 |
| 7 | 3, 4, 5, 6 | none |

### Agent Dispatch Summary (wave -> task count -> categories)
- Wave 1 -> 1 task -> `deep`
- Wave 2 -> 1 task -> `deep`
- Wave 3 -> 3 tasks -> `deep`, `deep`, `visual-engineering`
- Wave 4 -> 1 task -> `visual-engineering`
- Wave 5 -> 1 task -> `quick`

## TODOs
> Implementation + Test = ONE task. Never separate.
> EVERY task MUST have: Agent Profile + Parallelization + QA Scenarios.

- [ ] 1. Build executable smoke baseline

  **What to do**: Add a minimal agent-executable verification harness for the six core flows: `index.php`, `ingredients.php`, `showrecipe.php`, `editrecipe.php`, `editingredient.php`, and `editrecipeingredients.php`. Include repeatable app boot instructions, required local test data assumptions, PHP lint coverage, and browser/HTTP smoke scenarios that exercise search, detail, create, update, delete, and ingredient add/remove flows.
  **Must NOT do**: Do not rewrite app architecture, do not hide failures with mocks, and do not make the smoke checks depend on manual clicks.

  **Recommended Agent Profile**:
  - Category: `deep` - Reason: this is the verification foundation for every later task.
  - Skills: [`playwright`] - browser-grade flow verification is required.
  - Omitted: [`frontend-ui-ux`, `git-master`] - no design work or git packaging in this task.

  **Parallelization**: Can Parallel: NO | Wave 1 | Blocks: 2, 3, 4, 5, 6, 7 | Blocked By: none

  **References**:
  - Pattern: `index.php` - search and sort entrypoint
  - Pattern: `ingredients.php` - ingredient search flow
  - Pattern: `showrecipe.php` - detail rendering flow
  - Pattern: `editrecipe.php` - recipe create/edit/delete flow
  - Pattern: `editingredient.php` - ingredient create/edit/delete flow
  - Pattern: `editrecipeingredients.php` - ingredient add/remove and autocomplete flow
  - Pattern: `header.inc` - shared DB bootstrap and ingredient rendering
  - Test: `AGENTS.md` - current syntax-check command references and project caveats

  **Acceptance Criteria**:
  - [ ] `php -l header.inc index.php ingredients.php showrecipe.php editrecipe.php editingredient.php editrecipeingredients.php pricereport.php server.php getdata.php odbctest.php` passes.
  - [ ] Agent-executed smoke coverage exists for the six core flows.
  - [ ] Failure-path checks verify existing guard messages for invalid selection and delete confirmation flows.

  **QA Scenarios**:
  ```text
  Scenario: Core flow smoke baseline
    Tool: Playwright
    Steps: Launch local app; open /index.php, /ingredients.php, a known /showrecipe.php?recipeid=<seed-id>, then exercise recipe create/edit/delete, ingredient create/edit/delete, and add/remove recipe ingredient flows with seeded data.
    Expected: Each page loads, core actions complete, and success/failure messages match current behavior.
    Evidence: .sisyphus/evidence/task-1-smoke-baseline.json

  Scenario: Missing id and delete-confirmation failures
    Tool: Bash
    Steps: Run entrypoints with missing ids and submit delete actions without positive confirmation in a controlled local test setup.
    Expected: App returns the existing failure messages instead of PHP warnings or blank pages.
    Evidence: .sisyphus/evidence/task-1-smoke-baseline-error.txt
  ```

  **Commit**: YES | Message: `Add executable smoke baseline` | Files: `.sisyphus/evidence/*`, local test harness files, verification docs

- [ ] 2. Add shared safety helpers

  **What to do**: Add minimal shared helpers in `header.inc` for typed request reads, integer coercion, non-rich-text escaping, and prepared-query execution. Keep the procedural structure intact and retrofit only the seams needed for later endpoint hardening.
  **Must NOT do**: Do not rewrite `header.inc` into a service container, do not change stored instruction HTML semantics, and do not convert every query in one risky sweep.

  **Recommended Agent Profile**:
  - Category: `deep` - Reason: shared bootstrap changes affect every endpoint.
  - Skills: [] - no specialized external skill is required.
  - Omitted: [`playwright`, `frontend-ui-ux`, `git-master`] - implementation is shared plumbing, not browser/design/git work.

  **Parallelization**: Can Parallel: NO | Wave 2 | Blocks: 3, 4, 5, 6, 7 | Blocked By: 1

  **References**:
  - Pattern: `header.inc` - DB bootstrap, shared helper location, and current query normalization
  - Pattern: `index.php` - read-path request/session handling to target with helpers first
  - Pattern: `editrecipe.php` - write-path request handling example
  - Pattern: `editingredient.php` - output reflection and typed numeric handling example

  **Acceptance Criteria**:
  - [ ] Shared helper functions exist for typed request/input handling and safe non-rich-text output.
  - [ ] A prepared-query path exists and is used by at least one read page and one write page.
  - [ ] Apostrophe-containing values such as `Grandma's Pie` work correctly without `addslashes()` dependence.

  **QA Scenarios**:
  ```text
  Scenario: Shared helper regression check
    Tool: Bash
    Steps: Exercise helper-backed paths with integer ids, blank values, apostrophes, and angle brackets through a controlled script or endpoint calls.
    Expected: Numeric ids are coerced safely, plain-text output is escaped, and valid quoted values still round-trip.
    Evidence: .sisyphus/evidence/task-2-shared-safety.txt

  Scenario: Rich-text preservation edge case
    Tool: Bash
    Steps: Save a recipe with existing HTML instructions while also setting plain-text fields containing `<script>`-like content.
    Expected: Instructions remain stored/rendered as intended, but plain-text fields are escaped.
    Evidence: .sisyphus/evidence/task-2-shared-safety-error.txt
  ```

  **Commit**: YES | Message: `Add shared safety helpers` | Files: `header.inc`, any small shared helper additions

- [ ] 3. Harden write and destructive endpoints

  **What to do**: Retrofit `editrecipe.php`, `editingredient.php`, `editrecipeingredients.php`, and `pricereport.php` to use the shared safety helpers and prepared statements for active `INSERT`, `UPDATE`, and `DELETE` paths. Preserve workflow semantics and existing success/failure messaging where practical.
  **Must NOT do**: Do not redesign forms, do not mix visual restyling into this task, and do not leave any active write query concatenating raw request data.

  **Recommended Agent Profile**:
  - Category: `deep` - Reason: multiple mutation endpoints with broad breakage risk.
  - Skills: [`playwright`] - form round-trip verification is required.
  - Omitted: [`frontend-ui-ux`, `git-master`] - this is safety work, not design or git packaging.

  **Parallelization**: Can Parallel: YES | Wave 3 | Blocks: 6, 7 | Blocked By: 1, 2

  **References**:
  - Pattern: `editrecipe.php` - recipe create/update/delete queries and delete confirmation flow
  - Pattern: `editingredient.php` - ingredient create/update/delete queries and field conversions
  - Pattern: `editrecipeingredients.php` - recipe ingredient insert/delete flow and lookup query
  - Pattern: `pricereport.php` - batch update pattern
  - API/Type: `header.inc` - shared helper/query seam added in task 2

  **Acceptance Criteria**:
  - [ ] No active write endpoint concatenates raw request values directly into SQL.
  - [ ] Create/edit/delete recipe and ingredient flows still work.
  - [ ] Ingredient add/remove and price report update flows still work.
  - [ ] Invalid ids and missing confirmation paths fail cleanly without PHP warnings.

  **QA Scenarios**:
  ```text
  Scenario: Write flow hardening
    Tool: Playwright
    Steps: Create, edit, and delete a recipe with apostrophes in the name/instructions; create, edit, and delete an ingredient with decimal values; add and remove an ingredient on a recipe; update at least two rows in the price report.
    Expected: All writes succeed and persisted values display correctly after reload.
    Evidence: .sisyphus/evidence/task-3-write-hardening.json

  Scenario: Invalid input and destructive-edge checks
    Tool: Playwright
    Steps: Submit invalid/non-numeric ids, attempt delete without positive confirmation, and add a non-existent ingredient name in the ingredient editor flow.
    Expected: App shows controlled failure messages and does not corrupt records.
    Evidence: .sisyphus/evidence/task-3-write-hardening-error.json
  ```

  **Commit**: YES | Message: `Harden write endpoints` | Files: `editrecipe.php`, `editingredient.php`, `editrecipeingredients.php`, `pricereport.php`, `header.inc`

- [ ] 4. Retire dead external integrations safely

  **What to do**: Replace brittle dependencies that currently block reliable operation: swap FCKeditor in `editrecipe.php` for CKEditor 5 using a plain form-bound textarea integration; remove outbound nutritiondata.com scraping from `editingredient.php` and `getdata.php`; and isolate or replace the legacy autocomplete dependency path around `editrecipeingredients.php` and `server.php`. Preserve practical local editing with safe fallbacks.
  **Must NOT do**: Do not add a new remote scraping service, do not remove recipe editing capability, and do not change data semantics while decommissioning these dependencies.

  **Recommended Agent Profile**:
  - Category: `deep` - Reason: this changes runtime behavior and has compatibility fallout.
  - Skills: [`playwright`] - dependency fallbacks must be proven in-browser.
  - Omitted: [`frontend-ui-ux`, `git-master`] - this is compatibility cleanup first.

  **Parallelization**: Can Parallel: YES | Wave 3 | Blocks: 6, 7 | Blocked By: 1, 2

  **References**:
  - Pattern: `editingredient.php` - nutritiondata scraping and local ingredient workflow
  - Pattern: `getdata.php` - standalone remote-fetch helper to disable or constrain
  - Pattern: `editrecipe.php` - FCKeditor fallback behavior
  - Pattern: `editrecipeingredients.php` - legacy Scriptaculous autocomplete client
  - Pattern: `server.php` - autocomplete endpoint shape
  - External: `https://fdc.nal.usda.gov/api-guide` - FoodData Central search/details API and API-key model
  - External: `https://developer.edamam.com/edamam-docs-nutrition-api` - richer future recipe/NLP nutrition option
  - External: `https://developer.nutritionix.com/docs/v2` - commercial autocomplete/NLP nutrition option
  - External: `https://context7.com/ckeditor/ckeditor5` - CKEditor 5 classic editor form integration

  **Acceptance Criteria**:
  - [ ] Recipe editing uses CKEditor 5 in place of FCKeditor, with a textarea fallback if editor assets are unavailable.
  - [ ] Normal ingredient-edit and recipe-edit flows no longer depend on remote HTML scraping via `file_get_contents()`.
  - [ ] Ingredient nutrition import uses an API-backed lookup path or clean manual fallback instead of dead-site scraping.
  - [ ] Autocomplete is either replaced with a low-risk fallback or isolated so core ingredient-add workflows no longer depend on Prototype/Scriptaculous.

  **QA Scenarios**:
  ```text
  Scenario: Modern editor and nutrition fallback behavior
    Tool: Playwright
    Steps: Run recipe edit with CKEditor 5 enabled, then with editor assets intentionally unavailable; run ingredient edit with the new nutrition import path and with API lookup unavailable.
    Expected: Recipe editing works with the new editor and falls back cleanly to textarea; ingredient editing remains usable and supports manual nutrition entry when API lookup fails.
    Evidence: .sisyphus/evidence/task-4-dependency-retirement.json

  Scenario: Dead scraping path removed edge case
    Tool: Bash
    Steps: Exercise the old nutritiondata.com entry points and inspect the code path used by ingredient import.
    Expected: No normal flow performs remote HTML scraping; import requests target the configured API path or terminate in manual-entry guidance.
    Evidence: .sisyphus/evidence/task-4-dependency-retirement-error.txt
  ```

  **Commit**: YES | Message: `Retire brittle integrations` | Files: `editingredient.php`, `getdata.php`, `editrecipe.php`, `editrecipeingredients.php`, `server.php`

- [ ] 5. Add shared page shell for read-only pages

  **What to do**: Create a shared shell, navigation pattern, typography/color token set, and basic layout system, then apply it to `index.php`, `ingredients.php`, and `showrecipe.php` first. Keep the information architecture stable while making the pages cleaner, easier to scan, and responsive.
  **Must NOT do**: Do not restyle edit pages yet, do not bury the existing search capabilities, and do not introduce generic boilerplate UI without regard to this app’s data-dense screens.

  **Recommended Agent Profile**:
  - Category: `visual-engineering` - Reason: this is the first focused UI modernization pass.
  - Skills: [`frontend-ui-ux`, `playwright`] - design quality and responsive verification are both required.
  - Omitted: [`git-master`] - no git packaging in this task.

  **Parallelization**: Can Parallel: YES | Wave 3 | Blocks: 6, 7 | Blocked By: 1, 2

  **References**:
  - Pattern: `index.php` - main recipe search form and results table
  - Pattern: `ingredients.php` - ingredient search list page
  - Pattern: `showrecipe.php` - recipe detail view with ingredients and instructions
  - Pattern: `header.inc` - current helper output that the shell must accommodate

  **Acceptance Criteria**:
  - [ ] `index.php`, `ingredients.php`, and `showrecipe.php` share a common shell and navigation structure.
  - [ ] Pages remain readable at mobile width and desktop width without horizontal overflow for primary controls.
  - [ ] Search, sort, and drill-in behavior remain intact.

  **QA Scenarios**:
  ```text
  Scenario: Read-only page UI modernization
    Tool: Playwright
    Steps: Capture desktop and mobile flows for recipe search, ingredient search, and recipe detail after applying the shared shell.
    Expected: Search forms are usable, result tables are readable, and detail content remains legible without layout breakage.
    Evidence: .sisyphus/evidence/task-5-read-shell.json

  Scenario: Narrow viewport edge case
    Tool: Playwright
    Steps: Open `index.php` and `ingredients.php` at 390px width and interact with the primary search controls.
    Expected: No blocking horizontal overflow or hidden primary action controls.
    Evidence: .sisyphus/evidence/task-5-read-shell-error.json
  ```

  **Commit**: YES | Message: `Modernize read-only page shell` | Files: `index.php`, `ingredients.php`, `showrecipe.php`, shared layout/style assets

- [ ] 6. Modernize edit-flow UX progressively

  **What to do**: Apply the shared shell and coherent form styling to `editrecipe.php`, `editingredient.php`, `editrecipeingredients.php`, and `pricereport.php`. Replace the legacy autocomplete interaction with a low-complexity modern enhancement or explicit fallback selector, improve navigation/breadcrumb clarity, and remove the dropdown-based delete confirmation UX.
  **Must NOT do**: Do not redesign the data model, do not change the order-of-operations semantics for saving/deleting, and do not reintroduce hidden dependency on old JS libraries.

  **Recommended Agent Profile**:
  - Category: `visual-engineering` - Reason: this is interactive form modernization after stabilization.
  - Skills: [`frontend-ui-ux`, `playwright`] - form UX and end-to-end verification both matter.
  - Omitted: [`git-master`] - no git packaging in this task.

  **Parallelization**: Can Parallel: NO | Wave 4 | Blocks: 7 | Blocked By: 3, 4, 5

  **References**:
  - Pattern: `editrecipe.php` - current recipe editor, FCKeditor fallback, delete confirmation UI
  - Pattern: `editingredient.php` - current ingredient editor and dense form layout
  - Pattern: `editrecipeingredients.php` - current ingredient management UI, inline CSS/JS, and autocomplete behavior
  - Pattern: `pricereport.php` - current bulk-edit table
  - Pattern: `index.php` and `showrecipe.php` - target shell conventions established in task 5

  **Acceptance Criteria**:
  - [ ] Edit pages use the shared shell and consistent form styling.
  - [ ] Delete confirmation UX is clearer and less error-prone than the current select-and-submit pattern.
  - [ ] Core edit flows work without Prototype/Scriptaculous dependency.

  **QA Scenarios**:
  ```text
  Scenario: Edit flow UX pass
    Tool: Playwright
    Steps: Exercise recipe edit, ingredient edit, recipe ingredient add/remove, and price report update flows across desktop and mobile widths.
    Expected: Workflows remain functional, navigation is clearer, and form controls are usable without legacy JS dependencies.
    Evidence: .sisyphus/evidence/task-6-edit-ux.json

  Scenario: Destructive action clarity edge case
    Tool: Playwright
    Steps: Trigger delete actions for recipe and ingredient flows, cancel once, then confirm once.
    Expected: Cancel leaves records untouched; confirm deletes as intended; no confusing multi-step select box remains.
    Evidence: .sisyphus/evidence/task-6-edit-ux-error.json
  ```

  **Commit**: YES | Message: `Modernize edit-flow UX` | Files: `editrecipe.php`, `editingredient.php`, `editrecipeingredients.php`, `pricereport.php`, shared layout/style assets

- [ ] 7. Final regression pass and atomic landing

  **What to do**: Run the full QA matrix, close any scope-constrained regressions discovered in the previous waves, and package the work into atomic commits matching the approved strategy. Ensure evidence files, docs, and implementation state agree.
  **Must NOT do**: Do not sneak in new feature work, do not mix unrelated cleanup, and do not collapse all changes into one giant commit.

  **Recommended Agent Profile**:
  - Category: `quick` - Reason: this is packaging, verification, and release discipline.
  - Skills: [`git-master`, `playwright`] - atomic commit construction plus final browser verification.
  - Omitted: [`frontend-ui-ux`] - design work is already complete by this wave.

  **Parallelization**: Can Parallel: NO | Wave 5 | Blocks: none | Blocked By: 3, 4, 5, 6

  **References**:
  - Pattern: `.sisyphus/evidence/` - QA artifacts from prior tasks
  - Pattern: `README.md` - setup/runtime docs if any user-facing workflow changed
  - Pattern: `AGENTS.md` - project caveats and commands
  - Test: all modified PHP entrypoints and smoke scenarios from tasks 1-6

  **Acceptance Criteria**:
  - [ ] Full syntax suite passes.
  - [ ] Full smoke/browser suite passes on desktop and mobile widths.
  - [ ] Git history is atomic and grouped by baseline, safety, dependency, read-only UI, edit UX, and final cleanup.

  **QA Scenarios**:
  ```text
  Scenario: Final full-regression pass
    Tool: Playwright
    Steps: Run the complete smoke suite covering all six core flows plus the updated UI checks from prior tasks.
    Expected: Entire suite passes without manual intervention.
    Evidence: .sisyphus/evidence/task-7-final-regression.json

  Scenario: Syntax and packaging verification
    Tool: Bash
    Steps: Run the full `php -l` command set, inspect git status, and verify commit grouping against the plan.
    Expected: Clean syntax, clean working tree after commit wave, and atomic history aligned with the commit strategy.
    Evidence: .sisyphus/evidence/task-7-final-regression.txt
  ```

  **Commit**: YES | Message: `Finalize modernization rollout` | Files: remaining touched files, docs, evidence pointers

## Final Verification Wave (4 parallel agents, ALL must APPROVE)
- [ ] F1. Plan Compliance Audit - oracle
- [ ] F2. Code Quality Review - unspecified-high
- [ ] F3. Browser QA Pass - unspecified-high (+ playwright if UI)
- [ ] F4. Scope Fidelity Check - deep

## Commit Strategy
- Commit 1: add smoke baseline and evidence scaffolding
- Commit 2: add shared safety helpers in `header.inc`
- Commit 3: harden write and destructive endpoints
- Commit 4: retire dead integrations and legacy dependency coupling
- Commit 5: add shared shell and modernize read-only pages
- Commit 6: modernize edit-flow UI and progressive enhancements
- Commit 7: final regression/docs cleanup only if necessary

## Success Criteria
- Six core flows are reproducibly testable and stay green during the rollout.
- Security and reliability risks are reduced before UI polish work expands scope.
- Read-only pages get the first visible cleanup; edit flows follow after stabilization.
- Defaults applied in this plan remain intact unless the user explicitly changes them:
  - preserve stored instruction HTML
  - replace FCKeditor with CKEditor 5 by default
  - replace nutritiondata.com scraping with USDA FoodData Central plus manual fallback
  - keep Edamam/Nutritionix as later optional upgrades, not first-wave requirements
  - avoid schema changes except isolated compatibility repairs

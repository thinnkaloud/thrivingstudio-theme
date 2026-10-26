# Refinement Backlog v1

## Refinement Goals
- Primary focus: UX + IA clarity.
- Delivery model: incremental, low-risk batches.
- Success KPI: improved clarity and engagement on homepage and primary navigation.

## P1 (Cycle 1-2)

### P1.1 Homepage hierarchy and readability
- Status: Implemented in this cycle.
- Files: `front-page.php`, `frontend/index.css`.
- Changes:
  - Added supporting microcopy for hero and key sections.
  - Tightened section rhythm and visual consistency.
  - Replaced repeated inline section/card styles with reusable classes.
- Validation:
  - Hero hierarchy is clearer above the fold.
  - Social and latest sections have contextual subcopy.

### P1.2 Header/navigation clarity and interaction polish
- Status: Implemented in this cycle.
- Files: `template-parts/header.php`, `template-parts/nav.php`, `template-parts/category-menu.php`, `frontend/index.css`.
- Changes:
  - Replaced repeated inline styling with reusable classes for top bar, CTA, mobile button, and nav spacing.
  - Removed debug markup in nav template output.
  - Kept mobile menu behavior and existing menu contracts intact.
- Validation:
  - Desktop and mobile nav continue to render and toggle correctly.

### P1.3 Remove template debug traces
- Status: Implemented in this cycle.
- Files: `front-page.php`, `template-parts/nav.php`.
- Changes:
  - Removed `$_GET['debug_social']` diagnostic block and emitted diagnostic comments.
  - Removed menu presence debug comments in nav renderer.

### P1.4 Move repeated inline styles to stylesheet classes
- Status: Implemented in this cycle (targeted scope only).
- Files: `front-page.php`, `template-parts/header.php`, `template-parts/nav.php`, `template-parts/category-menu.php`, `frontend/index.css`.
- Notes:
  - Scoped to touched templates to avoid broad regressions.

## P2 (Next)

### P2.1 Footer social consistency cleanup
- Status: Implemented in this cycle.
- Files: `template-parts/footer.php`, `frontend/index.css`.
- Completed:
  - Removed inline footer JS and inline style block.
  - Replaced footer menu/social hover behavior with reusable CSS classes.
  - Added secure external social links (`target=\"_blank\"` + `rel=\"noopener noreferrer\"`).

### P2.2 Category menu interaction cleanup
- Status: Implemented in this cycle.
- Files: `template-parts/category-menu.php`, `frontend/index.css`.
- Completed:
  - Added `focus-within` dropdown behavior for keyboard accessibility.
  - Improved current category detection for child categories via ancestor checks.
  - Added `aria-current=\"page\"` on active top-level category links.

### P2.3 Build path simplification (single source of truth)
- Status: Implemented in this cycle.
- Files: `package.json`, `frontend/package.json`, build docs.
- Completed:
  - Set root `package.json` as canonical build workflow.
  - Updated `frontend/package.json` scripts to delegate to root scripts.
  - Documented canonical build commands in `README.md`.

## P3 (Planning/Architecture)

### P3.1 Structural decomposition plan (no behavior change)
- Files: `functions.php`, `inc/performance.php`, `inc/seo.php`.
- Tasks:
  - Define module boundaries and extraction sequence.
  - Add migration-safe wrappers to avoid breakage.
  - Execute only after P1/P2 stabilize.

## Release Checklist (per batch)
1. Run syntax checks on touched PHP files.
2. Rebuild `frontend/build.css` after CSS source edits.
3. Push to `develop` and verify staging output.
4. Validate UX checklist on staging desktop/mobile.
5. Fast-forward `main` and run live deploy with `confirm=DEPLOY` when ready.
6. Confirm live reflects intended visible changes.

## Phase 3 (In Progress)

### P3.1 Structural decomposition batch 1 (non-behavioral)
- Status: Implemented in this cycle.
- Files:
  - `functions.php`
  - `inc/theme/bootstrap.php`
  - `inc/performance.php`
  - `inc/performance/core.php`
  - `inc/seo.php`
  - `inc/seo/core.php`
- Completed:
  - Reduced `functions.php` to constants/module loader + dedicated bootstrap include.
  - Moved primary theme function registrations into `inc/theme/bootstrap.php`.
  - Converted `inc/performance.php` into loader wrapper and moved existing implementation to `inc/performance/core.php`.
  - Converted `inc/seo.php` into loader wrapper and moved existing implementation to `inc/seo/core.php`.
- Notes:
  - Intended as structure-only extraction; no functional behavior changes.

### P3.1 Structural decomposition batch 2 (module split, non-behavioral)
- Status: Implemented in this cycle.
- Files:
  - `inc/theme/bootstrap.php`
  - `inc/theme/setup-assets.php`
  - `inc/theme/customizer.php`
  - `inc/theme/content-types.php`
  - `inc/theme/taxonomy.php`
  - `inc/theme/media.php`
- Completed:
  - Converted `inc/theme/bootstrap.php` into a lightweight loader.
  - Split monolithic theme bootstrap logic into focused modules (assets/setup, customizer, content types/meta, taxonomy meta fields, media/webp helpers).
  - Kept all existing hook registrations and setting keys intact to avoid behavioral drift.

### P3.1 Structural decomposition batch 3 (cleanup + guardrails)
- Status: Implemented in this cycle.
- Files:
  - `inc/theme/customizer.php`
  - `docs/refinement-backlog-v1.md`
- Completed:
  - Removed leftover customizer debug save hook and `error_log` trace from production code path.
  - Finalized Phase 3 documentation for traceable batch-by-batch rollout.

## Phase 3 (Complete)
- `functions.php`, performance module, SEO module, and theme bootstrap are now decomposed into maintainable modules with wrapper/loader compatibility.
- No public Customizer keys or template contracts were changed during Phase 3.
- Remaining work should move back to UX/IA feature refinement batches.

## Phase 4 (Single Post Refinement Track)

### Batch 1: Readability and spacing baseline
- Status: Implemented and released.
- Files:
  - `single.php`
  - `frontend/index.css`
  - `frontend/build.css`
- Completed:
  - Replaced inline style hacks with scoped single-post classes.
  - Improved title/excerpt/meta rhythm and body readability.
  - Standardized heading/list/blockquote/link styling.

### Batch 2: Engagement layer
- Status: Implemented and released.
- Files:
  - `single.php`
  - `frontend/index.css`
  - `frontend/build.css`
- Completed:
  - Added reading-time and published/updated meta row.
  - Added auto-generated in-article TOC from `h2`/`h3`.
  - Added related articles section with thumbnail cards and refined hover behavior.

### Batch 3: Conversion and utility
- Status: Implemented and released.
- Files:
  - `single.php`
  - `frontend/index.css`
  - `frontend/build.css`
- Completed:
  - Added post-end CTA block.
  - Added author card with bio fallback.
  - Added styled previous/next post navigation cards.

### Batch 4: SEO + accessibility polish
- Status: Implemented and released.
- Files:
  - `single.php`
  - `frontend/index.css`
  - `frontend/build.css`
- Completed:
  - Added semantic/accessibility attributes (`role`, `aria-labelledby`, `aria-label`).
  - Added heading anchor offset (`scroll-margin-top`) for TOC navigation.
  - Improved keyboard focus visibility and mobile spacing polish.
  - Added related thumbnail `alt` fallback.

### Closure Cleanup
- Status: In progress (local cleanup pass).
- Files:
  - `frontend/index.css`
  - `docs/refinement-backlog-v1.md`
- Completed:
  - Consolidated duplicate single-post CSS blocks.
  - Updated backlog with completed Single Post B1-B4 delivery record.

## Next Track (Planned): Blog/Archive Refinement
1. Batch 1: Card hierarchy + excerpt/title rhythm.
2. Batch 2: Category/filter interaction polish.
3. Batch 3: Pagination and empty-state UX improvements.

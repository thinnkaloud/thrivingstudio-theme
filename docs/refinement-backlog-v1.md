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

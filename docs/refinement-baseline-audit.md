# Refinement Baseline Audit

## Objective
Establish a stable baseline before iterative theme refinement focused on UX/IA clarity and engagement.

## Baseline Snapshot
- Theme root: `/Users/thinnkaloud/Local Sites/stagingthrivingstudioxyz/app/public/wp-content/themes/thrivingstudio`
- Deployment flow: `develop` -> staging (auto), `main` -> live (manual `workflow_dispatch` with `confirm=DEPLOY`)
- Scope for this phase: homepage, header/nav, and targeted CSS hygiene only.

## Homepage Baseline

### Section order and purpose
1. Hero: core brand message + primary CTA (`Learn More`).
2. Social proof: social audience counts.
3. Featured categories: category entry points.
4. Latest articles: fresh editorial content.
5. Featured quote cards: visual content teaser.
6. Subscribe block: newsletter conversion.

### Observed baseline issues
- Strong visual hierarchy existed, but section rhythm and context copy were inconsistent.
- Significant inline styles in `front-page.php` made refinement and consistency harder.
- Template-level debug traces were present in production code path.

## Header/Nav Baseline

### Behavior baseline
- Desktop: logo, primary nav, CTA.
- Mobile: hamburger toggle + mobile nav + category menu + CTA.

### Observed baseline issues
- High reliance on inline styles and `!important` patterns in header/nav templates.
- Menu template emitted debug comments and inline spacing styles.
- Reusability of spacing/visual rules was low.

## CTA Clarity Baseline
- Header CTA text and link are Customizer-driven (`thrivingstudio_header_cta_text`, `thrivingstudio_header_cta_link`).
- Hero CTA is Customizer-driven (`thrivingstudio_home_hero_button_text`, `thrivingstudio_home_hero_button_link`).
- Copy intent was strong but lacked supporting microcopy around section transitions.

## Technical Baseline Inventory

### Inline style hotspots
- `front-page.php`: hero, social section, social metrics, category cards, subscribe panel, in-file style block.
- `template-parts/header.php`: top bar, desktop/mobile CTA, mobile menu button.
- `template-parts/nav.php`: per-item spacing inline styles.
- `template-parts/category-menu.php`: list and dropdown positioning inline styles.

### Debug traces and diagnostics
- `front-page.php`: removed `$_GET['debug_social']` conditional debug output and related diagnostics.
- `template-parts/nav.php`: removed HTML debug comments for menu presence.

### File ownership map (primary)
- Homepage template: `front-page.php`
- Header/mobile menu shell: `template-parts/header.php`
- Primary navigation rendering logic: `template-parts/nav.php`
- Category navigation/dropdowns: `template-parts/category-menu.php`
- Shared theme behavior and settings: `functions.php`
- Main CSS source of truth for deployed styles: `frontend/index.css` -> `frontend/build.css`

## Acceptance Checklist (for every refinement batch)
1. No Customizer key contract breaks (existing `thrivingstudio_*` keys preserved).
2. No template-level debug traces in touched files.
3. No PHP syntax errors in touched PHP templates.
4. CSS changes scoped to touched areas; avoid broad regressions.
5. Homepage/nav behavior verified on desktop + mobile.
6. Staging deploy succeeds from `develop`.
7. Live deploy succeeds from `main` when intentionally approved.
8. Visible changes match expected result after cache purge/hard refresh.

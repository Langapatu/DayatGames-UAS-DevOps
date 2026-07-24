# DayatGames Motion and Background Design

## Objective

Improve the DayatGames customer-facing website with smooth navigation and
pagination transitions, a cleaner logo-only brand treatment, and a subtle
animated gaming background. The existing Laravel flows, data, forms, card
alignment, and admin behavior must remain unchanged.

## Scope

- Customer-facing layout and navigation.
- Catalog pagination behavior.
- Shared animated background for non-auth customer pages.
- Reduced-motion behavior.
- Desktop and mobile responsive presentation.

The Word and PDF reports, game data, game covers, authentication logic, cart
logic, and admin interface are outside this change.

## Visual Direction

### Brand

The customer navbar uses `public/images/brand/dayatgames-logo.png` as the only
visible brand element. The adjacent DayatGames wordmark is removed. The logo
gets a slightly larger hit area, a restrained cyan border glow, and a subtle
hover lift without changing its source asset.

### Background

The page background retains the existing near-black navy base and adds:

1. Two large blurred aurora layers in cyan, blue, and violet.
2. Two sparse star fields with different sizes, opacity, and drift speeds.
3. A soft central vignette that preserves text and card contrast.

All decorative layers are fixed, non-interactive, behind the page content, and
must not introduce horizontal scrolling. Their animation is deliberately slow
and low contrast so the game covers remain the visual focus.

## Motion System

### Cross-page Navigation

Same-origin GET navigation uses the browser View Transitions API when
available. The sticky navbar and background remain visually stable while the
main content performs a 320–380 ms fade with a small vertical shift. Browsers
without View Transitions receive the existing page behavior plus a short
content entrance animation.

Links with downloads, external origins, hashes only, modified clicks, explicit
new tabs, or form submission behavior are not intercepted.

### Catalog Pagination

Pagination links store a short-lived navigation intent before opening the next
catalog page. On the destination page:

- The viewport returns to the catalog results context rather than the document
  top.
- The result grid fades and moves horizontally by a small distance according
  to previous/next direction.
- The animation applies to the grid as one unit so individual cards remain
  perfectly aligned.
- Browser history navigation restores the correct direction where practical
  and never blocks normal navigation.

### Existing Section Motion

The current whole-section reveal remains. Per-card stagger animation stays
disabled because it caused visible card misalignment.

## Accessibility and Performance

- `prefers-reduced-motion: reduce` disables aurora drift, star drift, smooth
  scrolling, and content movement while keeping the final visual state.
- Decorative background elements use `aria-hidden="true"` and cannot receive
  pointer events or focus.
- CSS gradients provide the aurora and stars; no video, canvas, or additional
  image download is required.
- Animations use opacity and transform only.
- Page content remains readable if JavaScript or View Transitions are
  unavailable.

## Implementation Boundaries

Expected production changes are limited to:

- `resources/views/layouts/app.blade.php`
- `resources/views/catalog/index.blade.php` or the shared pagination markup
  when a stable results target is required
- `resources/css/app.css`
- `resources/js/app.js`

No report artifacts are generated or updated.

## Verification

1. Navbar shows only the supplied logo on desktop and mobile.
2. Internal customer navigation has a smooth transition without breaking
   active navigation state, forms, authentication, or browser history.
3. Catalog page 1 to page 2 lands at the result context and animates the whole
   grid without moving cards out of alignment.
4. Aurora and stars are visible but do not reduce card or text contrast.
5. No horizontal overflow occurs at 1440×900 or 390×844.
6. Reduced-motion mode has no continuous or positional animation.
7. Vite build, marketplace feature tests, browser console checks, and
   desktop/mobile visual QA pass.


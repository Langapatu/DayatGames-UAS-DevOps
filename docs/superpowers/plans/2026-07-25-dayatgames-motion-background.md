# DayatGames Motion and Background Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add smooth storefront page and catalog-pagination transitions, replace the customer wordmark with the supplied logo, and introduce a subtle animated aurora-and-stars background.

**Architecture:** Keep Laravel multi-page navigation intact and use CSS cross-document View Transitions with a GSAP fallback. Add one shared decorative background layer in the customer layout, and pass pagination direction through data attributes plus short-lived `sessionStorage` state so the destination catalog can restore the results context and animate the grid as one aligned unit.

**Tech Stack:** Laravel Blade, Tailwind CSS 4, CSS View Transitions, GSAP 3, Lenis, PHPUnit feature tests, Vite.

## Global Constraints

- Do not modify Word or PDF reports.
- Do not modify game data, game covers, authentication logic, cart logic, or the admin interface.
- Keep the existing whole-section reveal and keep per-card stagger animation disabled.
- Use `public/images/brand/dayatgames-logo.png` without generating another brand asset.
- Disable continuous and positional motion under `prefers-reduced-motion: reduce`.
- Do not add JavaScript or CSS dependencies.

---

## File Structure

- `tests/Feature/CustomerMarketplaceTest.php`: render-contract tests for the logo-only navbar, ambient layer, transition target, and directional catalog pagination.
- `resources/views/layouts/app.blade.php`: shared customer-only ambient layer, logo-only brand link, and page-content transition target.
- `resources/views/storefront/catalog/index.blade.php`: stable catalog results and grid targets.
- `resources/views/vendor/pagination/tailwind.blade.php`: direction metadata on previous, next, and numbered pagination links.
- `resources/css/app.css`: aurora, star fields, brand treatment, cross-document transitions, catalog motion states, and reduced-motion overrides.
- `resources/js/app.js`: safe page-entry fallback and short-lived catalog pagination intent handling.

### Task 1: Lock the Storefront Markup Contract

**Files:**
- Modify: `tests/Feature/CustomerMarketplaceTest.php`
- Modify: `resources/views/layouts/app.blade.php`
- Modify: `resources/views/storefront/catalog/index.blade.php`
- Modify: `resources/views/vendor/pagination/tailwind.blade.php`

**Interfaces:**
- Produces: `[data-ambient-backdrop]`, `[data-page-content]`, `#catalog-results`, `[data-catalog-grid]`, and `data-pagination-direction="previous|next"`.
- Produces: `.site-brand` with only one `.brand-mark` child and an accessible link label.
- Consumes: existing Laravel route helpers and paginator URLs.

- [ ] **Step 1: Add failing storefront render-contract tests**

Add these methods before `createGame()`:

```php
public function test_storefront_renders_logo_only_brand_and_motion_layers(): void
{
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('data-ambient-backdrop', false)
        ->assertSee('data-page-content', false)
        ->assertSee('images/brand/dayatgames-logo.png', false)
        ->assertDontSee('<span>Dayat<span>Games</span></span>', false);
}

public function test_catalog_renders_directional_pagination_motion_targets(): void
{
    foreach (range(1, 13) as $index) {
        $this->createGame("Game {$index}", "game-{$index}");
    }

    $this->get(route('catalog.index'))
        ->assertOk()
        ->assertSee('id="catalog-results"', false)
        ->assertSee('data-catalog-grid', false)
        ->assertSee('data-pagination-direction="next"', false);

    $this->get(route('catalog.index', ['page' => 2]))
        ->assertOk()
        ->assertSee('data-pagination-direction="previous"', false);
}
```

- [ ] **Step 2: Run the tests and verify the new contracts fail**

Run:

```powershell
docker compose exec -T app php artisan test --filter=CustomerMarketplaceTest
```

Expected: the two new tests fail because the ambient, page-content, catalog-grid, and pagination-direction attributes do not exist yet.

- [ ] **Step 3: Add customer-only layout markup**

In `resources/views/layouts/app.blade.php`, replace the body class expression with:

```blade
<body class="min-h-screen bg-[#070A12] text-slate-100 antialiased {{ $isAdminArea ? 'admin-workspace' : '' }} {{ $isAuthPage ? 'auth-workspace' : '' }} {{ !$isAdminArea && !$isAuthPage ? 'storefront-workspace' : '' }}">
```

Immediately after the skip link, add:

```blade
@if(!$isAdminArea && !$isAuthPage)
    <div data-ambient-backdrop class="ambient-backdrop" aria-hidden="true">
        <span class="ambient-aurora ambient-aurora-one"></span>
        <span class="ambient-aurora ambient-aurora-two"></span>
        <span class="ambient-stars ambient-stars-near"></span>
        <span class="ambient-stars ambient-stars-far"></span>
    </div>
@endif
```

Replace the customer brand link contents with the logo only:

```blade
<a href="{{ route('home') }}" aria-label="DayatGames — kembali ke home" class="site-brand">
    <span class="brand-mark"><img src="{{ asset('images/brand/dayatgames-logo.png') }}" alt=""></span>
</a>
```

Add `data-page-content` only to non-admin, non-auth main content:

```blade
<main id="main-content"
      @if(!$isAdminArea && !$isAuthPage) data-page-content @endif
      class="{{ $isAdminArea ? 'admin-main' : ($isAuthPage ? 'auth-main' : 'site-main') }}">
```

- [ ] **Step 4: Add stable catalog motion targets**

Wrap the populated results block in `resources/views/storefront/catalog/index.blade.php`:

```blade
<section id="catalog-results" data-catalog-results tabindex="-1" class="scroll-mt-24">
    <p class="mb-5 text-sm text-slate-400">Menampilkan {{ $games->firstItem() }}–{{ $games->lastItem() }} dari {{ $games->total() }} game.</p>
    <div data-catalog-grid class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        @foreach($games as $game)<x-game-card :game="$game" />@endforeach
    </div>
    <div class="mt-8">{{ $games->links() }}</div>
</section>
```

- [ ] **Step 5: Add pagination direction metadata**

In `resources/views/vendor/pagination/tailwind.blade.php`, add
`data-pagination-direction="previous"` to every enabled previous link and
`data-pagination-direction="next"` to every enabled next link.

For numbered page links, render:

```blade
<a class="dg-page-number"
   href="{{ $url }}"
   data-pagination-direction="{{ $page > $paginator->currentPage() ? 'next' : 'previous' }}"
   aria-label="{{ __('Go to page :page', ['page' => $page]) }}">{{ $page }}</a>
```

- [ ] **Step 6: Run the feature tests**

Run:

```powershell
docker compose exec -T app php artisan test --filter=CustomerMarketplaceTest
```

Expected: 7 tests pass, including the new storefront and pagination render contracts.

- [ ] **Step 7: Commit the markup contract**

```powershell
git add tests/Feature/CustomerMarketplaceTest.php resources/views/layouts/app.blade.php resources/views/storefront/catalog/index.blade.php resources/views/vendor/pagination/tailwind.blade.php
git commit -m "feat: add storefront motion hooks"
```

### Task 2: Build the Aurora, Stars, Logo, and Page Transition System

**Files:**
- Modify: `resources/css/app.css`
- Modify: `resources/js/app.js`

**Interfaces:**
- Consumes: `.storefront-workspace`, `[data-ambient-backdrop]`, `[data-page-content]`, and `.site-brand` from Task 1.
- Produces: `dayatgames-page-in`, `dayatgames-page-out`, `aurora-drift-one`, `aurora-drift-two`, `stars-drift-near`, and `stars-drift-far` animations.

- [ ] **Step 1: Add cross-document transition and ambient background CSS**

Add after the root/body foundation styles in `resources/css/app.css`:

```css
@view-transition {
    navigation: auto;
}

.storefront-workspace {
    position: relative;
    isolation: isolate;
    background: #050811;
}

.ambient-backdrop {
    position: fixed;
    inset: 0;
    z-index: -1;
    overflow: hidden;
    pointer-events: none;
    view-transition-name: ambient-backdrop;
    background:
        radial-gradient(circle at 50% 45%, transparent 0 25%, rgb(2 6 23 / 35%) 72%),
        linear-gradient(180deg, #050811 0%, #07101d 48%, #050811 100%);
}

.ambient-aurora,
.ambient-stars {
    position: absolute;
    inset: -20%;
    will-change: transform;
}

.ambient-aurora {
    filter: blur(80px);
    opacity: .34;
}

.ambient-aurora-one {
    background:
        radial-gradient(ellipse at 22% 28%, rgb(34 211 238 / 40%), transparent 34%),
        radial-gradient(ellipse at 70% 62%, rgb(59 130 246 / 30%), transparent 36%);
    animation: aurora-drift-one 22s ease-in-out infinite alternate;
}

.ambient-aurora-two {
    background:
        radial-gradient(ellipse at 78% 18%, rgb(124 58 237 / 28%), transparent 31%),
        radial-gradient(ellipse at 38% 78%, rgb(14 165 233 / 22%), transparent 34%);
    animation: aurora-drift-two 28s ease-in-out infinite alternate;
}

.ambient-stars {
    opacity: .42;
    background-repeat: repeat;
}

.ambient-stars-near {
    background-image:
        radial-gradient(circle, rgb(186 230 253 / 80%) 0 1px, transparent 1.5px),
        radial-gradient(circle, rgb(196 181 253 / 58%) 0 1.2px, transparent 1.7px);
    background-position: 0 0, 47px 83px;
    background-size: 137px 149px, 211px 227px;
    animation: stars-drift-near 34s linear infinite;
}

.ambient-stars-far {
    opacity: .24;
    background-image: radial-gradient(circle, white 0 .7px, transparent 1.2px);
    background-size: 83px 97px;
    animation: stars-drift-far 48s linear infinite;
}

.storefront-workspace .site-header {
    view-transition-name: site-header;
}

.storefront-workspace [data-page-content] {
    view-transition-name: page-content;
}

::view-transition-old(page-content) {
    animation: dayatgames-page-out 180ms ease both;
}

::view-transition-new(page-content) {
    animation: dayatgames-page-in 360ms cubic-bezier(.22, 1, .36, 1) both;
}

@keyframes dayatgames-page-out {
    to { opacity: 0; transform: translateY(-8px); }
}

@keyframes dayatgames-page-in {
    from { opacity: 0; transform: translateY(14px); }
}

@keyframes aurora-drift-one {
    from { transform: translate3d(-4%, -2%, 0) scale(1); }
    to { transform: translate3d(5%, 3%, 0) scale(1.08); }
}

@keyframes aurora-drift-two {
    from { transform: translate3d(4%, -3%, 0) scale(1.05); }
    to { transform: translate3d(-5%, 4%, 0) scale(.98); }
}

@keyframes stars-drift-near {
    to { transform: translate3d(-70px, 95px, 0); }
}

@keyframes stars-drift-far {
    to { transform: translate3d(55px, 70px, 0); }
}
```

- [ ] **Step 2: Refine the logo-only navbar treatment**

Add near the existing `.site-brand` styles:

```css
.site-brand {
    min-width: 3.15rem;
    min-height: 3.15rem;
    justify-content: center;
    border: 1px solid rgb(56 189 248 / 30%);
    border-radius: .95rem;
    background: rgb(8 20 36 / 72%);
    box-shadow: 0 10px 28px rgb(14 165 233 / 10%);
}

.site-brand .brand-mark {
    width: 2.85rem;
    height: 2.85rem;
    border: 0;
    background: transparent;
}

.site-brand:hover {
    border-color: rgb(34 211 238 / 58%);
    transform: translateY(-2px);
    box-shadow: 0 12px 32px rgb(14 165 233 / 20%);
}
```

Keep `.auth-brand` and `.admin-brand` wordmarks unchanged.

- [ ] **Step 3: Add a non-View-Transitions entrance fallback**

Near the top of `resources/js/app.js`, after the element constants, add:

```js
const pageContent = document.querySelector('[data-page-content]');

if (!reducedMotion && pageContent && !document.startViewTransition) {
    gsap.from(pageContent, {
        y: 14,
        opacity: 0,
        duration: 0.36,
        ease: 'power2.out',
        clearProps: 'transform,opacity',
    });
}
```

- [ ] **Step 4: Extend reduced-motion CSS**

Inside the existing `@media (prefers-reduced-motion: reduce)` block, add:

```css
.ambient-aurora,
.ambient-stars,
::view-transition-old(page-content),
::view-transition-new(page-content) {
    animation: none !important;
}
```

- [ ] **Step 5: Build frontend assets**

Run:

```powershell
npm.cmd run build
```

Expected: Vite completes successfully with no CSS or JavaScript compilation errors.

- [ ] **Step 6: Commit the shared motion system**

```powershell
git add resources/css/app.css resources/js/app.js
git commit -m "feat: add animated storefront atmosphere"
```

### Task 3: Implement Smooth Catalog Pagination Context

**Files:**
- Modify: `resources/css/app.css`
- Modify: `resources/js/app.js`
- Test: `tests/Feature/CustomerMarketplaceTest.php`

**Interfaces:**
- Consumes: `[data-catalog-results]`, `[data-catalog-grid]`, and `data-pagination-direction` from Task 1.
- Produces: session key `dayatgames:catalog-navigation` containing `{ direction: "previous"|"next", timestamp: number }`.
- Produces: `data-pagination-motion="previous|next"` on the catalog results element during destination entry.

- [ ] **Step 1: Add catalog grid motion CSS**

Add beside pagination styles:

```css
[data-catalog-results] {
    outline: none;
}

[data-catalog-grid] {
    transform-origin: center top;
}

[data-pagination-motion='next'] [data-catalog-grid] {
    animation: catalog-grid-next 420ms cubic-bezier(.22, 1, .36, 1) both;
}

[data-pagination-motion='previous'] [data-catalog-grid] {
    animation: catalog-grid-previous 420ms cubic-bezier(.22, 1, .36, 1) both;
}

@keyframes catalog-grid-next {
    from { opacity: 0; transform: translateX(24px); }
}

@keyframes catalog-grid-previous {
    from { opacity: 0; transform: translateX(-24px); }
}
```

Add these selectors to the reduced-motion override:

```css
[data-pagination-motion] [data-catalog-grid] {
    animation: none !important;
}
```

- [ ] **Step 2: Store safe pagination navigation intent**

Add before Lenis initialization in `resources/js/app.js`:

```js
const catalogResults = document.querySelector('[data-catalog-results]');
const catalogIntentKey = 'dayatgames:catalog-navigation';

catalogResults?.querySelectorAll('.dg-pagination a[data-pagination-direction]').forEach((link) => {
    link.addEventListener('click', (event) => {
        if (
            event.defaultPrevented
            || event.button !== 0
            || event.metaKey
            || event.ctrlKey
            || event.shiftKey
            || event.altKey
        ) {
            return;
        }

        sessionStorage.setItem(catalogIntentKey, JSON.stringify({
            direction: link.dataset.paginationDirection,
            timestamp: Date.now(),
        }));
    });
});
```

- [ ] **Step 3: Restore the results context and animate the grid**

Immediately after the intent listener, add:

```js
if (catalogResults) {
    try {
        const intent = JSON.parse(sessionStorage.getItem(catalogIntentKey) || 'null');
        const isFresh = intent
            && ['previous', 'next'].includes(intent.direction)
            && Date.now() - intent.timestamp < 10000;

        sessionStorage.removeItem(catalogIntentKey);

        if (isFresh) {
            history.scrollRestoration = 'manual';
            catalogResults.dataset.paginationMotion = intent.direction;

            requestAnimationFrame(() => {
                const headerOffset = header?.offsetHeight || 0;
                const targetTop = catalogResults.getBoundingClientRect().top + window.scrollY - headerOffset - 20;
                window.scrollTo({ top: Math.max(0, targetTop), behavior: 'auto' });
                catalogResults.focus({ preventScroll: true });
            });
        }
    } catch {
        sessionStorage.removeItem(catalogIntentKey);
    }
}
```

This keeps normal links and history intact, consumes stale intent once, and animates the result grid as one unit.

- [ ] **Step 4: Run feature tests and frontend build**

Run:

```powershell
docker compose exec -T app php artisan test --filter=CustomerMarketplaceTest
npm.cmd run build
git diff --check
```

Expected: 7 tests and all assertions pass, Vite builds successfully, and `git diff --check` prints no errors.

- [ ] **Step 5: Browser QA the target flows**

Use the in-app Browser at `http://localhost:8080/` and validate:

1. 1440×900: logo-only navbar, visible low-contrast aurora and sparse stars, no horizontal overflow.
2. Click `Jelajahi Game`: the header/background remain visually stable and main content transitions.
3. Click catalog page 2: URL becomes `/games?page=2`, viewport lands at `#catalog-results`, the grid moves horizontally as one unit, and every card in a row has identical top/bottom/height.
4. Navigate back to page 1 and confirm the opposite grid direction.
5. 390×844: logo-only navbar and mobile menu do not overlap; ambient background remains subtle; no horizontal overflow.
6. Console contains no relevant `warn` or `error` entries and no framework overlay is present.

Save screenshots outside the repository for desktop home, desktop catalog after pagination, and mobile.

- [ ] **Step 6: Commit pagination motion**

```powershell
git add resources/css/app.css resources/js/app.js
git commit -m "feat: smooth catalog pagination transitions"
```

- [ ] **Step 7: Verify final scope**

Run:

```powershell
git status --short
git log -4 --oneline
```

Expected: clean worktree; no Word, PDF, report, game data, game cover, auth, cart, or admin files changed by the implementation commits.


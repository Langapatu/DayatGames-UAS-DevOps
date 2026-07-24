# DayatGames Interactive Motion Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Restore the DayatGames navbar wordmark and add performant pointer-driven card, aurora, navigation, and button interactions to the customer storefront.

**Architecture:** Blade exposes stable motion hooks, CSS owns rendering and accessibility fallbacks, and the existing GSAP runtime owns interpolated pointer motion. All enhanced motion is scoped to fine-pointer devices and disabled when reduced motion is requested.

**Tech Stack:** Laravel 12, Blade, Tailwind CSS 4, custom CSS, vanilla JavaScript, GSAP 3, Vite, PHPUnit.

## Global Constraints

- Change only the customer storefront website.
- Do not update Word, PDF, report screenshots, admin pages, or application data.
- Preserve every game card's existing height behavior and fixed 4:5 media frame.
- Cap card rotation at 4 degrees and cover scaling at 1.035.
- Enable pointer effects only when `(hover: hover) and (pointer: fine)` matches.
- Disable enhanced motion for `prefers-reduced-motion: reduce`.
- Add no new dependency.

---

### Task 1: Restore Brand and Add Motion Hooks

**Files:**
- Modify: `tests/Feature/CustomerMarketplaceTest.php`
- Modify: `resources/views/layouts/app.blade.php`
- Modify: `resources/views/components/game-card.blade.php`

**Interfaces:**
- Produces: `[data-interactive-brand]`, `[data-brand-wordmark]`, `[data-aurora-layer]`, `[data-nav-link]`, `[data-game-card]`, and `[data-card-spotlight]`.
- Consumes: existing logo asset, route-active checks, and shared `x-game-card` component.

- [ ] **Step 1: Change the storefront markup test so it fails**

Replace `test_storefront_renders_logo_only_brand_and_motion_layers` with:

```php
public function test_storefront_renders_interactive_brand_and_motion_hooks(): void
{
    $game = $this->createGame('Interactive Game', 'interactive-game');

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('data-ambient-backdrop', false)
        ->assertSee('data-aurora-layer', false)
        ->assertSee('data-page-content', false)
        ->assertSee('data-interactive-brand', false)
        ->assertSee('data-brand-wordmark', false)
        ->assertSee('<span>Dayat<span>Games</span></span>', false)
        ->assertSee('data-nav-link', false)
        ->assertSee('data-game-card', false)
        ->assertSee('data-card-spotlight', false)
        ->assertSee($game->title);
}
```

- [ ] **Step 2: Run the focused test and verify failure**

Run:

```bash
docker compose exec -T app php artisan test --filter=test_storefront_renders_interactive_brand_and_motion_hooks
```

Expected: FAIL because the wordmark and new motion hooks are absent.

- [ ] **Step 3: Add exact Blade hooks**

In the customer navbar, render:

```blade
<a href="{{ route('home') }}" aria-label="DayatGames — kembali ke home" class="site-brand" data-interactive-brand>
    <span class="brand-mark"><img src="{{ asset('images/brand/dayatgames-logo.png') }}" alt=""></span>
    <span data-brand-wordmark>Dayat<span>Games</span></span>
</a>
```

Add `data-aurora-layer="one"` and `data-aurora-layer="two"` to the existing aurora spans. Add `data-nav-link` to customer navigation anchors that can be active. Inside `x-game-card`, add this decorative first child:

```blade
<span data-card-spotlight aria-hidden="true"></span>
```

- [ ] **Step 4: Run the focused test and verify pass**

Run:

```bash
docker compose exec -T app php artisan test --filter=test_storefront_renders_interactive_brand_and_motion_hooks
```

Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add tests/Feature/CustomerMarketplaceTest.php resources/views/layouts/app.blade.php resources/views/components/game-card.blade.php
git commit -m "feat: add interactive storefront motion hooks"
```

---

### Task 2: Build the Interactive Visual Layer

**Files:**
- Modify: `resources/css/app.css`

**Interfaces:**
- Consumes: motion hooks from Task 1 and CSS custom properties `--card-rx`, `--card-ry`, `--spot-x`, and `--spot-y`.
- Produces: stable 3D card presentation, spotlight, cover zoom, brand glow, nav indicator, button shimmer, and reduced-motion overrides.

- [ ] **Step 1: Add brand and card base styling**

Update `.site-brand` to fit the wordmark without a fixed square width. Add:

```css
.site-brand {
    min-width: 0;
    padding: .15rem .7rem .15rem .15rem;
    justify-content: flex-start;
}

[data-brand-wordmark] {
    white-space: nowrap;
    text-shadow: 0 0 20px rgb(56 189 248 / 18%);
}

[data-game-card] {
    --card-rx: 0deg;
    --card-ry: 0deg;
    --spot-x: 50%;
    --spot-y: 50%;
    position: relative;
    isolation: isolate;
    transform: perspective(900px) rotateX(var(--card-rx)) rotateY(var(--card-ry));
    transform-style: preserve-3d;
}

[data-card-spotlight] {
    position: absolute;
    inset: 0;
    z-index: 2;
    pointer-events: none;
    border-radius: inherit;
    opacity: 0;
    background: radial-gradient(circle at var(--spot-x) var(--spot-y), rgb(103 232 249 / 18%), transparent 34%);
    box-shadow: inset 0 0 0 1px rgb(103 232 249 / 18%);
    transition: opacity .22s ease;
}
```

- [ ] **Step 2: Add fine-pointer hover motion and shimmer**

Add a fine-pointer media query that reveals the spotlight, scales `.game-media-frame img` to `1.035`, strengthens card shadow, animates active nav glow, and runs a pseudo-element shimmer across `.game-card-cart-button`, `.hero-primary-action`, and `.nav-register`. Ensure pseudo-elements use `pointer-events: none` and do not change layout.

- [ ] **Step 3: Add touch and reduced-motion fallbacks**

Inside `@media (prefers-reduced-motion: reduce)`, set card transform to `none !important`, cover transform to `none !important`, spotlight opacity to `0 !important`, aurora custom offsets to zero, and shimmer animation to `none !important`. Keep `:active` button feedback at a maximum scale of `.98` for touch devices.

- [ ] **Step 4: Build assets**

Run:

```bash
npm.cmd run build
```

Expected: Vite build succeeds with no CSS parsing error.

- [ ] **Step 5: Commit**

```bash
git add resources/css/app.css
git commit -m "feat: style interactive storefront motion"
```

---

### Task 3: Add Pointer-Driven GSAP Motion

**Files:**
- Modify: `resources/js/app.js`

**Interfaces:**
- Consumes: `[data-aurora-layer]`, `[data-game-card]`, reduced-motion state, and `window.matchMedia('(hover: hover) and (pointer: fine)')`.
- Produces: pointer-follow aurora offsets and card custom-property updates with cleanup on pointer leave.

- [ ] **Step 1: Add capability guard**

Near the existing reduced-motion constant, define:

```js
const finePointer = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
```

Run enhanced setup only inside:

```js
if (!reducedMotion && finePointer) {
    // Interactive motion setup.
}
```

- [ ] **Step 2: Add lagged aurora tracking**

Select both `[data-aurora-layer]` elements. Use `gsap.quickTo` for `xPercent` and `yPercent` with durations between `1.2` and `1.6` seconds. Normalize pointer coordinates against the viewport and apply maximum offsets of 3 percent to layer one and 1.8 percent in the opposite direction to layer two.

- [ ] **Step 3: Add card tilt and spotlight tracking**

For each `[data-game-card]`, use pointer coordinates relative to its `getBoundingClientRect()`. Set:

```js
const rotateX = (0.5 - normalizedY) * 8;
const rotateY = (normalizedX - 0.5) * 8;
```

Write the values to `--card-rx`, `--card-ry`, `--spot-x`, and `--spot-y`. On pointer leave, animate rotation back to zero and spotlight coordinates back to `50%` using GSAP with `power3.out`.

- [ ] **Step 4: Build and run focused tests**

Run:

```bash
npm.cmd run build
docker compose exec -T app php artisan test --filter=CustomerMarketplaceTest
```

Expected: Vite succeeds; 7 customer marketplace tests pass.

- [ ] **Step 5: Commit**

```bash
git add resources/js/app.js
git commit -m "feat: add pointer-driven storefront motion"
```

---

### Task 4: Full Verification and Visual QA

**Files:**
- Verify only; do not modify Word or PDF artifacts.

**Interfaces:**
- Consumes: completed Tasks 1–3.
- Produces: evidence that desktop, mobile, reduced-motion, and existing behavior remain correct.

- [ ] **Step 1: Run the full automated suite**

Run:

```bash
docker compose exec -T app php artisan test
npm.cmd run build
git diff --check
```

Expected: all Laravel tests pass, Vite succeeds, and `git diff --check` prints no errors.

- [ ] **Step 2: Verify desktop interactions in the in-app browser**

At `http://localhost:8080` with a 1440×900 viewport:

- Confirm logo plus `DayatGames` are visible.
- Move the pointer across a game card and confirm rotation stays within ±4 degrees.
- Confirm spotlight coordinates change and the cover zooms without altering card bounds.
- Confirm aurora transforms respond more slowly than the pointer.
- Confirm navbar and primary-button effects do not obscure text.
- Navigate to catalog page 2 and confirm existing contextual pagination animation still works.

- [ ] **Step 3: Verify mobile behavior**

At a 390×844 viewport:

- Confirm brand and menu button do not overlap.
- Confirm no horizontal overflow.
- Confirm game cards remain aligned and 4:5.
- Confirm pointer-only tilt is inactive.

- [ ] **Step 4: Verify reduced-motion CSS**

Inspect the reduced-motion CSS rule and confirm card transform, cover zoom, spotlight, aurora pointer offsets, and shimmer are disabled.

- [ ] **Step 5: Capture final screenshots and commit any QA-only code correction**

Save a desktop home screenshot and a mobile home screenshot outside the repository. If QA required a code fix, rerun Step 1 and commit only the corrected website files.

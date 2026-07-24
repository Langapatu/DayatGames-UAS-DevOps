# DayatGames Preview Gallery Implementation Plan

> **For Codex:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Add four screenshots to every seeded game, an autoplaying Steam-like preview and accessible lightbox on game detail pages, plus an autoplaying multi-game hero on the home page while preserving the DayatGames visual language.

**Architecture:** Store screenshot paths in the existing `game_images` table through an idempotent seeder and keep binary assets under `public/images/previews/{slug}`. Render the detail gallery through a reusable Blade component, and use Swiper plus a small lightbox controller in the existing JavaScript entrypoint. Reuse the featured-game collection for the home hero so every rotating slide has a valid detail route and fallback image.

**Tech Stack:** Laravel 12, Blade, Eloquent, Tailwind CSS, Vite, Swiper, GSAP, PHPUnit.

---

### Task 1: Seed four preview images for every game

**Files:**
- Create: `database/seeders/PreviewGallerySeeder.php`
- Modify: `database/seeders/DatabaseSeeder.php`
- Create: `tests/Feature/PreviewGallerySeederTest.php`
- Add: `public/images/previews/{game-slug}/01.jpg`
- Add: `public/images/previews/{game-slug}/02.jpg`
- Add: `public/images/previews/{game-slug}/03.jpg`
- Add: `public/images/previews/{game-slug}/04.jpg`

**Step 1: Write the failing test**

Create a feature test that runs `DatabaseSeeder`, asserts there are 18 published games, asserts each game has exactly four ordered gallery images, and runs the seeder twice to prove it does not duplicate rows.

**Step 2: Run test to verify it fails**

Run: `docker compose exec -T app php artisan test --filter=PreviewGallerySeederTest`

Expected: FAIL because the existing database seeder creates no `game_images` rows.

**Step 3: Import the assets**

Copy the 28 user-provided JPG files from the seven supplied folders into normalized slug folders. For the remaining Steam-listed games, download four official screenshot URLs returned by Steam's app-details data. If an official source does not supply four usable screenshots, fill only the missing slots with the game's existing hero asset.

**Step 4: Implement the idempotent seeder**

Create `PreviewGallerySeeder` that loops over the 18 known game slugs and upserts sort orders 1–4 with `image_type = gallery` and normalized paths. Call it immediately after `GameSeeder` in `DatabaseSeeder`.

**Step 5: Run test to verify it passes**

Run: `docker compose exec -T app php artisan test --filter=PreviewGallerySeederTest`

Expected: PASS.

**Step 6: Commit**

```bash
git add database/seeders/PreviewGallerySeeder.php database/seeders/DatabaseSeeder.php tests/Feature/PreviewGallerySeederTest.php public/images/previews
git commit -m "feat: seed game preview screenshots"
```

### Task 2: Render the detail preview gallery and lightbox contract

**Files:**
- Create: `resources/views/components/game-preview-gallery.blade.php`
- Modify: `resources/views/storefront/catalog/show.blade.php`
- Create: `tests/Feature/GamePreviewGalleryTest.php`

**Step 1: Write the failing tests**

Create games with ordered `GameImage` records and assert the detail response contains:

- `data-preview-gallery`
- four `data-preview-slide` elements in sort order
- `data-preview-lightbox`
- previous, next, close, and thumbnail controls with accessible labels
- a hero-image fallback when a game has no image records

**Step 2: Run test to verify it fails**

Run: `docker compose exec -T app php artisan test --filter=GamePreviewGalleryTest`

Expected: FAIL because the component and behavior hooks do not exist.

**Step 3: Implement the gallery component**

Build a DayatGames-styled section with:

- a 16:9 main Swiper
- previous and next controls
- an autoplay progress indicator
- a thumbnail Swiper
- a native `<dialog>` lightbox with its own previous, next, close, and thumbnail controls
- accurate image numbering and descriptive alt text

The component must normalize an empty image collection to one hero-image fallback.

**Step 4: Place it in the detail page**

Render the component immediately after the hero/purchase section and before the two-column About/Information area. Remove the old static gallery grid.

**Step 5: Run test to verify it passes**

Run: `docker compose exec -T app php artisan test --filter=GamePreviewGalleryTest`

Expected: PASS.

**Step 6: Commit**

```bash
git add resources/views/components/game-preview-gallery.blade.php resources/views/storefront/catalog/show.blade.php tests/Feature/GamePreviewGalleryTest.php
git commit -m "feat: add accessible game preview gallery"
```

### Task 3: Turn the home hero into a featured-game slider

**Files:**
- Modify: `app/Http/Controllers/Storefront/HomeController.php`
- Modify: `resources/views/storefront/home.blade.php`
- Modify: `tests/Feature/CustomerMarketplaceTest.php`

**Step 1: Write the failing test**

Add a storefront test with two featured games and assert that the home page renders a hero slide for each game, each detail link points to the matching game, and the hero navigation and pagination hooks exist.

**Step 2: Run test to verify it fails**

Run: `docker compose exec -T app php artisan test --filter=home_hero_rotates_through_featured_games`

Expected: FAIL because the home page currently renders only the first featured game.

**Step 3: Implement controller data loading**

Eager-load `images` in the home page game query so each hero slide can use the first gallery screenshot and fall back to `heroUrl()`.

**Step 4: Implement the hero slides**

Replace the single hero with one Swiper slide per featured game. Each slide owns its image, title, description, and detail route. Add labeled previous/next buttons and clickable pagination dots while keeping the existing DayatGames gradient, typography, and catalog CTA.

**Step 5: Run test to verify it passes**

Run: `docker compose exec -T app php artisan test --filter=home_hero_rotates_through_featured_games`

Expected: PASS.

**Step 6: Commit**

```bash
git add app/Http/Controllers/Storefront/HomeController.php resources/views/storefront/home.blade.php tests/Feature/CustomerMarketplaceTest.php
git commit -m "feat: rotate featured games in home hero"
```

### Task 4: Add smooth autoplay, synchronization, and lightbox interactions

**Files:**
- Modify: `resources/js/app.js`
- Modify: `resources/css/app.css`

**Step 1: Add the required Swiper modules**

Import Autoplay, EffectFade, Pagination, and Thumbs plus their CSS modules in the existing Vite entrypoint.

**Step 2: Implement the home hero behavior**

Initialize the hero with:

- 6000 ms autoplay
- fade transition
- looping when more than one slide exists
- clickable pagination
- pause on pointer hover, keyboard focus, and hidden tab
- synchronized GSAP entrance for the active slide's text

**Step 3: Implement detail gallery behavior**

Initialize the thumbnail and main Swipers with:

- 5000 ms autoplay
- 450 ms transition
- loop only for multiple images
- responsive thumbnail counts of 2.35, 3.25, and 4
- keyboard and accessible navigation
- autoplay pause on hover, focus, hidden tab, and lightbox

**Step 4: Implement the lightbox controller**

Open from the active main image; synchronize arrows, thumbnail clicks, image count, and main Swiper state; support Left, Right, and Escape; lock page scroll while open; close on backdrop click; and return focus to the invoking image.

**Step 5: Respect reduced-motion preference**

Disable both autoplay systems and remove movement-based transitions when `prefers-reduced-motion: reduce` is active.

**Step 6: Style the interfaces**

Add responsive DayatGames styling for the hero carousel, preview stage, thumbnail states, progress line, glass controls, full-screen dialog, and mobile layouts. Preserve 16:9 screenshots without cropping by using `object-contain` against a dark media stage.

**Step 7: Build assets**

Run: `npm.cmd run build`

Expected: Vite build succeeds with no syntax or import errors.

**Step 8: Commit**

```bash
git add resources/js/app.js resources/css/app.css
git commit -m "feat: animate hero and preview gallery"
```

### Task 5: Seed, verify, and visually inspect the finished feature

**Files:**
- No report, Word, or PDF files.

**Step 1: Rebuild the seeded data**

Run: `docker compose exec -T app php artisan migrate:fresh --seed --force`

Expected: 18 games and 72 gallery image rows.

**Step 2: Run the complete test suite**

Run: `docker compose exec -T app php artisan test`

Expected: all tests PASS.

**Step 3: Run the production build**

Run: `npm.cmd run build`

Expected: PASS.

**Step 4: Run browser QA**

At desktop and mobile widths, verify:

- the home hero rotates through different featured games and the CTA always opens the active game
- gallery autoplay is smooth and pauses/resumes correctly
- clicking the main image opens the lightbox
- arrows, thumbnails, keyboard controls, backdrop, and Escape behave correctly
- screenshots remain fully visible at 16:9
- no card alignment or background-motion regressions
- reduced-motion mode has no autoplay
- browser console has no errors

**Step 5: Check repository scope**

Run: `git diff --check` and `git status --short`.

Expected: no whitespace errors, no report changes, and only intended website changes.

**Step 6: Commit any final targeted fixes**

```bash
git add <targeted website files only>
git commit -m "fix: polish preview gallery interactions"
```

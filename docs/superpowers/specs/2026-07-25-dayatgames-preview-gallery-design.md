# DayatGames Preview Gallery Design

## Goal

Add a DayatGames-styled screenshot gallery to every game detail page and turn the home hero into a smooth rotating showcase of featured games. The interaction may follow familiar storefront behavior, but the visual design must remain consistent with the existing dark aurora interface.

## Scope

This iteration changes only the website and image data used by the website. It does not update Word, PDF, report screenshots, or unrelated admin CRUD screens.

## Asset Strategy

Every published game should have four landscape preview images.

- Preserve the supplied images in `C:\.Kuliah\TugasMatkul\DevOPS\UAS\PreviewGames` without altering their pixels.
- Map the supplied folders as follows:
  - `Forza_Horizon_6` → `forza-horizon-6`
  - `GhostTshusima` → `ghost-of-tsushima`
  - `GTA5` → `grand-theft-auto-v`
  - `KCD2` → `kingdom-come-deliverance-ii`
  - `LegoBatman` → `lego-batman-legacy-of-the-dark-knight`
  - `Mafia` → `mafia-the-old-country`
  - `Subnautica2` → `subnautica-2`
- Store imported assets under `public/images/previews/<game-slug>/01.jpg` through `04.jpg`.
- For the other eleven games, obtain four screenshots from Steam's official app metadata/CDN when a valid Steam app ID is available.
- For a game without usable Steam screenshots, make one focused search of the official publisher or platform product page and use images exposed by that page.
- If the Steam metadata and official product page together provide fewer than four usable screenshots, fill every missing position with that game's existing hero image rather than downloading from an unverified image site.

The existing `game_images` table remains the source of truth. Seeder logic upserts four ordered `gallery` records per game so repeated seeding does not create duplicates.

## Game Detail Gallery

### Layout

Place the gallery directly after the existing hero/purchase section and before “Tentang game.”

- A 16:9 main preview occupies the full content width.
- A horizontal thumbnail strip sits underneath it.
- Previous and next buttons overlay the main preview on desktop and remain reachable on mobile.
- The active thumbnail has a cyan-violet outline and glow.
- The gallery uses the existing panel, border, radius, aurora, and typography system.

If a game has no `game_images` records, the gallery renders one fallback slide using `heroUrl()`.

### Playback

- The gallery advances every 5,000 milliseconds.
- Navigation loops from the last slide to the first and from the first to the last.
- Autoplay pauses while the gallery is hovered, contains keyboard focus, the browser tab is hidden, or the lightbox is open.
- Autoplay restarts after the user leaves the gallery or closes the lightbox.
- Selecting a thumbnail or arrow resets the autoplay timer.
- Slide changes use a 450-millisecond opacity and horizontal-motion transition.

### Lightbox

Clicking the main preview opens a full-screen dialog:

- The selected image is shown with `object-fit: contain`.
- Previous and next controls loop through all images.
- The active thumbnail strip remains available.
- `ArrowLeft`, `ArrowRight`, and `Escape` are supported.
- Focus moves into the dialog when opened and returns to the main preview when closed.
- Page scrolling is locked while open.
- Clicking the dark backdrop closes the dialog; clicking the image does not.
- The dialog has an accessible name containing the game title and a position announcement such as “Gambar 2 dari 4.”

## Home Featured Hero

Replace the single home hero with a slider built from published featured games.

- Each slide owns its background, title, short description, and detail URL.
- The entire slide changes every 6,000 milliseconds.
- The transition crossfades the background while the text moves by no more than 18 pixels.
- Previous, next, and pagination-dot controls are available.
- Hover, keyboard focus, or a hidden browser tab pauses autoplay.
- The “Jelajahi katalog” link stays constant.
- The “Lihat game” link always targets the active game.
- The hero keeps its current height, rounded panel, readable gradient overlay, and responsive layout.

When only one featured game exists, autoplay and navigation controls are hidden.

## Runtime Architecture

- Continue using the installed Swiper package; add only the required modules such as `Autoplay`, `EffectFade`, `Navigation`, `Pagination`, `Thumbs`, and keyboard/a11y support.
- Use one Blade partial/component for the detail gallery markup.
- Use semantic `data-*` hooks for JavaScript and browser tests.
- Keep lightbox state in JavaScript, with a single active slide index shared by the gallery and dialog.
- Keep home hero and detail gallery initialization independent so a failure in one does not affect the other.
- Do not add a new frontend dependency or database migration.

## Responsive and Motion Rules

- Viewports at least 1,024 pixels wide show four thumbnails.
- Viewports below 640 pixels show 2.35 thumbnails to signal horizontal navigation and support swipe; viewports from 640 through 1,023 pixels show 3.25 thumbnails.
- Main previews use `object-fit: cover`; lightbox images use `object-fit: contain`.
- `prefers-reduced-motion: reduce` disables autoplay and replaces animated movement with an immediate or minimal opacity change.
- All arrow buttons have visible focus styles and accessible labels.
- Layout changes must not introduce horizontal page overflow.

## Failure Handling

- A missing preview file falls back to the game hero image through an image error handler.
- With JavaScript disabled, the first preview remains visible and all preview images are still available as links.
- If only one usable preview exists, arrows, thumbnail navigation, and autoplay are disabled.
- Invalid or missing external source metadata does not block seeding or page rendering.

## Verification

- Feature tests confirm game detail pages render gallery hooks, ordered images, accessible lightbox markup, and fallback behavior.
- Feature tests confirm the home hero renders multiple featured-game slides with each game's title and detail URL.
- Seeder tests confirm four unique ordered `game_images` records per mapped game after repeated runs.
- Production build succeeds without new dependencies.
- Browser QA verifies autoplay, arrows, thumbnails, lightbox open/close, keyboard controls, focus return, page-2 catalog regression, and console health.
- Desktop and mobile screenshots confirm the visual design remains DayatGames rather than copying Steam.

## Success Criteria

- Clicking a game opens a detail page with a usable four-image preview gallery.
- Clicking the main preview opens a large navigable image dialog.
- An untouched gallery advances automatically every five seconds.
- The home hero rotates through featured games and updates image, title, description, and detail link.
- The seven supplied preview folders are used.
- Remaining games use official screenshots or the documented hero fallback.
- Existing cards remain aligned and all prior tests remain green.
- Word and PDF files remain untouched.

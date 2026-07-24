# DayatGames Interactive Motion Design

## Goal

Make the customer storefront feel more alive and responsive while preserving the existing 4:5 game-card layout, aligned card heights, readable content, and stable mobile experience. Restore the navbar brand to the existing logo plus the `DayatGames` wordmark.

## Scope

This iteration changes only the website storefront. It does not update Word, PDF, report screenshots, admin pages, or application data.

## Visual Direction

The selected direction is **interaktif menyeluruh**:

- Restore the logo and `DayatGames` wordmark in the customer navbar.
- Add restrained 3D tilt to game cards on pointer devices.
- Add a cursor-position spotlight and border glow to the hovered card.
- Scale the game cover slightly inside its fixed 4:5 frame on hover.
- Give primary buttons a short shimmer and pressed feedback.
- Let the two ambient aurora layers follow the pointer slowly with different movement ranges.
- Animate the active customer-navigation indicator smoothly.
- Keep the existing page, pagination, scroll-reveal, aurora-drift, and star-field animations.

## Interaction Components

### Brand

The customer navbar contains the existing logo asset and a single `DayatGames` wordmark. The wordmark uses the current white-and-cyan treatment. Hover lifts the whole brand group slightly and strengthens its cyan glow without resizing the navbar.

### Game Cards

Each customer-facing game card becomes an independent interactive surface:

- Pointer position is normalized from `-1` to `1` on both axes.
- Rotation is capped at 4 degrees.
- A CSS spotlight follows pointer coordinates through custom properties.
- The cover scales to a maximum of 1.035.
- Pointer leave returns the card to rest with a smooth spring-like easing.
- Card dimensions and grid flow never change during interaction.

The effect applies to every storefront card rendered by the shared `x-game-card` component, identified by `[data-game-card]`. This covers featured, discounted, popular, latest, catalog, wishlist, and related-game grids. It does not apply to cart rows, library rows, admin CRUD cards, or tables.

### Ambient Aurora

Pointer movement updates target offsets rather than directly setting transforms. GSAP interpolates the aurora layers toward those targets so the background lags behind the cursor. The first layer moves farther than the second to create depth. The existing autonomous drift continues as the base motion.

### Navigation and Buttons

The active customer-nav item receives a subtle moving glow/underline treatment. Primary calls to action and add-to-cart buttons receive a short shimmer on hover plus a small scale-down on press. Effects must not obscure labels or change button width.

## Runtime Architecture

- Blade adds semantic data attributes to the shared storefront brand, navigation, and game-card surfaces.
- CSS owns visual styling, pointer custom properties, hover transitions, shimmer, and reduced-motion fallbacks.
- JavaScript owns pointer tracking and GSAP interpolation.
- A single global pointer listener updates the aurora target.
- Card listeners are attached only to matching storefront cards and use `requestAnimationFrame` or GSAP quick setters to avoid layout thrashing.

No new dependency is required; the project already includes GSAP.

## Responsive and Accessibility Rules

- Enhanced pointer effects run only when `(hover: hover) and (pointer: fine)` matches.
- Touch devices keep the existing static card presentation and pressed-button feedback.
- `prefers-reduced-motion: reduce` disables tilt, cover zoom, shimmer travel, aurora pointer-follow, and animated navigation movement.
- Focus-visible states remain clear and do not depend on hover.
- Motion never changes document flow, card height, text position, or the 4:5 cover ratio.
- Decorative spotlight and shimmer layers are not exposed to assistive technology.

## Failure Handling

- If JavaScript is unavailable, CSS hover and the existing static design remain usable.
- If GSAP initialization fails, navigation and links continue normal browser behavior.
- Missing or unsupported pointer APIs simply leave cards and aurora at their resting state.
- Motion setup is scoped to elements that exist, so customer pages without game cards produce no errors.

## Verification

- Feature test confirms the customer navbar renders both the logo and `DayatGames` wordmark plus motion hooks.
- Production build completes without asset errors.
- Desktop QA verifies card tilt, spotlight, cover zoom, button shimmer, aurora response, navbar brand, and navigation.
- Mobile QA verifies no overlap, no horizontal overflow, aligned cards, and static touch-safe presentation.
- Reduced-motion inspection verifies enhanced effects are disabled.
- Existing Laravel test suite remains green.

## Success Criteria

- The navbar again shows the existing logo beside `DayatGames`.
- Pointer movement creates visible but restrained depth across the storefront.
- Interactive effects feel responsive without making cards hard to read.
- All game grids remain aligned and all covers remain 4:5.
- Desktop and mobile have no new overflow or collision.
- Word and PDF files remain untouched.

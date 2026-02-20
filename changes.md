# Elodin Bridge Changes

## Version 0.4

### Added
- New typography utility setting: `Enable automatic heading margins` (default on).
  - Configurable responsive values for Desktop, Tablet, and Mobile.
  - Default values: `3em` (desktop), `2.5em` (tablet), and `2em` (mobile).
  - Applies to `h1`-`h4` block headings, with `:first-child` headings reset to `margin-top: 0`.
- New spacing utility setting: `Enable last-child margin resets` (default on).
  - Sets `margin-bottom: 0` for last-child headings, paragraphs, lists, and button groups.

### Changed
- Heading conversion classes now always reset top margin for converted heading styles:
  - `.h1`, `.h2`, `.h3`, `.h4` now output `margin-top: 0`.
- Automatic heading margin output now includes kicker-related spacing rules:
  - `.is-style-kicker:first-child` gets `margin-block-start: 0 !important`.
  - `.is-style-kicker:last-child` gets `margin-block-end: 0 !important`.
  - Adjacent heading spacing is removed for `.is-style-kicker + h1/h2/h3/h4.wp-block-heading`.

## Version 0.3

### Added
- New editor helper setting: `Enable GenerateBlocks boundary highlights in the editor` (default on).
- New editor helper setting: `Prettier widgets` (default on) for improved Widgets screen block spacing/layout.
- `Requires GenerateBlocks` tag on the GenerateBlocks boundary highlight feature card.

### Changed
- Moved GenerateBlocks boundary highlight styles out of Pine Forge theme and into Bridge as a dedicated, toggleable feature.
- Moved Widgets editor styling rules out of Pine Forge theme and into Bridge as a dedicated, toggleable feature.
- Image-size defaults now seed only `square` (the `blog` default row was removed).
- Image-size settings persistence improved: disabling the feature no longer wipes previously saved custom image-size rows.

## Version 0.2

### Added
- Appearance settings page at `Appearance > Elodin Bridge` with modular, independently toggled feature cards.
- Heading and paragraph style override feature:
  - Block toolbar control for paragraph + heading style class overrides (`p`, `h1`, `h2`, `h3`, `h4`).
  - GeneratePress typography mapping (desktop/tablet/mobile).
  - Front-end and editor inline CSS generation for those override classes.
- Balanced text feature:
  - Separate block toolbar toggle for `.balanced`.
  - Applies `text-wrap: balance` for paragraph and heading elements when active.
- Content type behavior feature:
  - Page-like vs post-like mapping per post type (public first, non-public included, excludes nav/patterns).
  - Editor-only visual behavior driven by mapped content type classes.
- Editor UI restrictions feature:
  - Optional inline JS to disable fullscreen mode and disable publish sidebar in the block editor.
  - Detaches legacy theme callback (`elodin_disable_fullscreen_mode`) so Bridge controls this behavior.
- Media library infinite scrolling feature:
  - New settings toggle (default on) to force Media Library infinite scrolling via `media_library_infinite_scrolling`.
- Shortcode feature:
  - New settings toggle (default on) to register helper shortcodes: `[year]`, `[c]`, `[tm]`, and `[r]`.
  - `[tm]` and `[r]` render with superscript markup by default.
- First/last block body class feature:
  - Global feature toggle (default off), plus independent first-block and last-block toggles.
  - Configurable first-block and last-block body class generation for top-level blocks.
  - Shared editable list of block names that count as sections (default: `core/cover`, `core/block`, `generateblocks/element`).
  - Adds classes like `first-block-is-section`, `last-block-is-section`, `first-block-is-{block}`, and `last-block-is-{block}`.
  - Optional front-end debug panel (bottom-right) showing top-level block names for the current singular page.
- Image sizes feature:
  - Unified custom image-size table UI (editable `slug`, `label`, `width`, `height`, `crop`, gallery availability).
  - Default seeded rows: `square` and `blog`, treated like normal editable custom sizes.
  - Registers sizes with `add_image_size()` when enabled.
  - Gallery size-picker integration via `image_size_names_choose`.
  - Automatic removal of legacy theme filter `ettt_add_gallery_sizes`.
- Plugin update checker integration:
  - Bundles `vendor/plugin-update-checker`.
  - Adds GitHub-based update checks with configurable repository URL and branch.
- Admin UI improvements:
  - Plugin version badge shown on settings screen.
  - Improved feature card/toggle layout and conditional reveal of feature details.

### Changed
- Typography override CSS now avoids force-applying `margin-bottom` with `!important` while continuing to strongly override other mapped typography properties.
- Typography inheritance behavior now supports explicit resets when a GeneratePress typography value is intentionally blank.
- Heading/paragraph override settings card now shows `Requires GeneratePress`.
- Heading/paragraph override feature is now hard-gated to GeneratePress:
  - Toggle is disabled in settings when GeneratePress is not the active/parent theme.
  - Setting sanitization forces disabled state when requirement is not met.
  - Runtime checks prevent feature output when requirement is not met.

### Notes
- Thumbnail regeneration is required after changing image-size registrations if existing images should use those new sizes.

# Elodin Bridge Changes

## Version 0.7

### Added
- New style tweak setting to apply Theme.json button styles with higher specificity (default on):
  - Reads button styles from `styles.blocks.core/button` (with fallback to `styles.elements.button`).
  - Includes variation support for `styles.blocks.core/button.variations.outline`.
  - Applies front-end and backend/editor overrides for GeneratePress button style conflicts.
- Post editor (all CPTs using `core/edit-post`) style injection path for Theme.json button overrides via `block_editor_settings_all`.
- New spacing alias mappings for Theme.json spacing presets:
  - `--space-2xs` maps to `xx-small` / `2xs` / `xxs`.
  - `--space-xs` maps to `x-small` / `xs`.
  - `--space-b` maps to `base` / `b` (positioned between small and medium).

### Changed
- Theme.json button override output no longer uses `!important`:
  - Now relies on higher specificity selectors and late enqueue priority, preserving per-button overrides.
- Theme.json button override setting card updated:
  - Renamed to reflect specificity-based behavior.
  - Expanded read-only preview to show all detected button declarations (not just padding).
  - Clarified that values are edited in `theme.json`, not in Bridge settings.
- Spacing and font-size variable cards now render only aliases that exist in active Theme.json values.
- Spacing and font-size variable cards now display only variable name + value (label/slug metadata removed).
- Spacing variable grid layout refined for denser but readable display in admin.
- Last-child button group top margin feature now also always sets `.wp-block-buttons:first-child { margin-top: 0; }` when enabled.

### Fixed
- Theme.json button override styles now load more reliably in backend editor contexts, including post editor canvases where button styles previously differed from front-end output.

## Version 0.6

### Added
- New `Variables` settings category with read-only theme.json mappings:
  - Spacing aliases mapped to `:root` variables like `--space-s`, `--space-m`, `--space-l`, `--space-xl`, and beyond.
  - Font-size aliases mapped to `:root` variables like `--font-xs`, `--font-s`, `--font-b`, `--font-m`, `--font-l`, `--font-xl`, and `--font-2xl`.
  - Inline note and path hint so values are edited directly in active theme `theme.json`.
- New editor tweak: `Set Paragraph as default inserter block` (default on).
- New style tweak: `Reusable block flow spacing fix` (default on, editor-only CSS output).
- New editor tweak: `Root-level container padding` (default on) with responsive values:
  - Desktop: `var( --space-xl )`
  - Tablet: `var( --space-l )`
  - Mobile: `var( --space-m )`
  - Includes `Requires GenerateBlocks` requirement tag.
- New GenerateBlocks container layout gap defaults feature (default on) with responsive control values and editor variation override support.
- New heading/paragraph block toolbar margin-top override for paragraph + heading levels `h1`-`h4`:
  - Options: `0`, `var( --space-s )`, and `var( --space-m )`.
  - Applies with `!important`.

### Changed
- Automatic heading margin defaults updated to `var( --space-l )` for desktop, tablet, and mobile.
- GenerateBlocks layout gap defaults now use:
  - Column: `var( --space-xl )` (desktop/tablet/mobile)
  - Row: `var( --space-m )` (desktop/tablet/mobile)
- GenerateBlocks layout gap defaults card moved under `Editor Tweaks`.
- Heading/paragraph override toolbar updated:
  - Type control now uses icon-style toggle (bold A + regular A).
  - Margin-top control now uses icon dropdown with variable-name labels.
  - Balanced text toggle is merged into the same toolbar group for consistent spacing.

### Fixed
- Page-like title editor width selector now targets only direct children:
  - `.elodin-bridge-page-like-title .editor-styles-wrapper > .wp-block`

## Version 0.5

### Added
- Automatic settings save on the Bridge admin page (no manual submit required in normal JS-enabled use).
  - Includes live save status messaging: idle, saving, saved, and error.
  - Keeps a `<noscript>` manual save button fallback for non-JavaScript environments.
- Temporary autosave debug output on save failure:
  - Inline debug panel on the settings screen.
  - Console diagnostics with request/response context.

### Changed
- Custom image-size editor row layout refined:
  - Top row (`slug`, `label`, `width`, `height`) uses its own dedicated grid.
  - Bottom row (`hard crop`, `allow in galleries`, `remove`) uses separate flex layout.
- Image-size field sizing now enforces full-width form controls within their grid cells, including explicit full-width handling for `input[type=number].small-text`.

### Fixed
- Autosave endpoint resolution now uses the form `action` attribute directly to avoid collisions with hidden inputs named `action`.
- Added explicit settings capability alignment for `elodin_bridge_settings` saves so page access and settings persistence use the same capability model.

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

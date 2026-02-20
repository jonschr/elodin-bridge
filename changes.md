# Elodin Bridge Changes

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

<?php
/*
	Plugin Name: Elodin Bridge
	Plugin URI: https://elod.in
    Description: Just another plugin
	Version: 0.6
    Author: Jon Schroeder
    Author URI: https://elod.in

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
    GNU General Public License for more details.
*/

/* Prevent direct access to the plugin */
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Sorry, you are not allowed to access this page directly.' );
}

// Plugin constants.
define( 'ELODIN_BRIDGE_DIR', dirname( __FILE__ ) );
define( 'ELODIN_BRIDGE_URL', plugin_dir_url( __FILE__ ) );
define( 'ELODIN_BRIDGE_VERSION', '0.6' );
define( 'ELODIN_BRIDGE_OPTION_ENABLE_HEADING_PARAGRAPH_OVERRIDES', 'elodin_bridge_enable_heading_paragraph_overrides' );
define( 'ELODIN_BRIDGE_OPTION_ENABLE_BALANCED_TEXT', 'elodin_bridge_enable_balanced_text' );
define( 'ELODIN_BRIDGE_OPTION_CONTENT_TYPE_BEHAVIOR', 'elodin_bridge_content_type_behavior' );
define( 'ELODIN_BRIDGE_OPTION_AUTOMATIC_HEADING_MARGINS', 'elodin_bridge_automatic_heading_margins' );
define( 'ELODIN_BRIDGE_OPTION_ENABLE_EDITOR_UI_RESTRICTIONS', 'elodin_bridge_enable_editor_ui_restrictions' );
define( 'ELODIN_BRIDGE_OPTION_ENABLE_MEDIA_LIBRARY_INFINITE_SCROLLING', 'elodin_bridge_enable_media_library_infinite_scrolling' );
define( 'ELODIN_BRIDGE_OPTION_ENABLE_SHORTCODES', 'elodin_bridge_enable_shortcodes' );
define( 'ELODIN_BRIDGE_OPTION_ENABLE_GENERATEBLOCKS_BOUNDARY_HIGHLIGHTS', 'elodin_bridge_enable_generateblocks_boundary_highlights' );
define( 'ELODIN_BRIDGE_OPTION_ENABLE_PRETTIER_WIDGETS', 'elodin_bridge_enable_prettier_widgets' );
define( 'ELODIN_BRIDGE_OPTION_ENABLE_LAST_CHILD_MARGIN_RESETS', 'elodin_bridge_enable_last_child_margin_resets' );
define( 'ELODIN_BRIDGE_OPTION_ENABLE_THEME_JSON_BUTTON_PADDING_IMPORTANT', 'elodin_bridge_enable_theme_json_button_padding_important' );
define( 'ELODIN_BRIDGE_OPTION_ENABLE_MOBILE_FIXED_BACKGROUND_REPAIR', 'elodin_bridge_enable_mobile_fixed_background_repair' );
define( 'ELODIN_BRIDGE_OPTION_ENABLE_REUSABLE_BLOCK_FLOW_SPACING_FIX', 'elodin_bridge_enable_reusable_block_flow_spacing_fix' );
define( 'ELODIN_BRIDGE_OPTION_ENABLE_DEFAULT_PARAGRAPH_BLOCK', 'elodin_bridge_enable_default_paragraph_block' );
define( 'ELODIN_BRIDGE_OPTION_BLOCK_EDGE_CLASSES', 'elodin_bridge_block_edge_classes' );
define( 'ELODIN_BRIDGE_OPTION_IMAGE_SIZES', 'elodin_bridge_image_sizes' );
define( 'ELODIN_BRIDGE_OPTION_SPACING_VARIABLES', 'elodin_bridge_spacing_variables' );
define( 'ELODIN_BRIDGE_OPTION_FONT_SIZE_VARIABLES', 'elodin_bridge_font_size_variables' );
define( 'ELODIN_BRIDGE_OPTION_GENERATEBLOCKS_LAYOUT_GAP_DEFAULTS', 'elodin_bridge_generateblocks_layout_gap_defaults' );
define( 'ELODIN_BRIDGE_OPTION_ROOT_LEVEL_CONTAINER_PADDING', 'elodin_bridge_root_level_container_padding' );
define( 'ELODIN_BRIDGE_TYPOGRAPHY_RESET', '__elodin_bridge_typography_reset__' );
define( 'ELODIN_BRIDGE_UPDATE_REPOSITORY', 'https://github.com/jonschr/elodin-bridge' );
// Empty branch means update checks are tag/release-based by default.
define( 'ELODIN_BRIDGE_UPDATE_BRANCH', '' );

require_once ELODIN_BRIDGE_DIR . '/inc/settings-page.php';
require_once ELODIN_BRIDGE_DIR . '/inc/heading-paragraph-overrides.php';
require_once ELODIN_BRIDGE_DIR . '/inc/balanced-text.php';
require_once ELODIN_BRIDGE_DIR . '/inc/automatic-heading-margins.php';
require_once ELODIN_BRIDGE_DIR . '/inc/content-type-behavior.php';
require_once ELODIN_BRIDGE_DIR . '/inc/editor-ui-restrictions.php';
require_once ELODIN_BRIDGE_DIR . '/inc/media-library-infinite-scrolling.php';
require_once ELODIN_BRIDGE_DIR . '/inc/shortcodes.php';
require_once ELODIN_BRIDGE_DIR . '/inc/last-child-margin-resets.php';
require_once ELODIN_BRIDGE_DIR . '/inc/theme-json-button-padding-important.php';
require_once ELODIN_BRIDGE_DIR . '/inc/mobile-fixed-background-repair.php';
require_once ELODIN_BRIDGE_DIR . '/inc/reusable-block-flow-spacing-fix.php';
require_once ELODIN_BRIDGE_DIR . '/inc/default-paragraph-block.php';
require_once ELODIN_BRIDGE_DIR . '/inc/generateblocks-boundary-highlights.php';
require_once ELODIN_BRIDGE_DIR . '/inc/prettier-widgets.php';
require_once ELODIN_BRIDGE_DIR . '/inc/block-edge-classes.php';
require_once ELODIN_BRIDGE_DIR . '/inc/image-sizes.php';
require_once ELODIN_BRIDGE_DIR . '/inc/spacing-variables.php';
require_once ELODIN_BRIDGE_DIR . '/inc/font-size-variables.php';
require_once ELODIN_BRIDGE_DIR . '/inc/generateblocks-layout-gap-defaults.php';
require_once ELODIN_BRIDGE_DIR . '/inc/root-level-container-padding.php';
require_once ELODIN_BRIDGE_DIR . '/inc/update-checker.php';

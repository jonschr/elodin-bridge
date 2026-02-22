<?php

/**
 * Enqueue admin styles for the Bridge settings page.
 *
 * @param string $hook_suffix Current admin page hook suffix.
 */
function elodin_bridge_enqueue_admin_assets( $hook_suffix ) {
	if ( 'appearance_page_elodin-bridge' !== $hook_suffix ) {
		return;
	}

	$style_path = ELODIN_BRIDGE_DIR . '/assets/admin-settings.css';
	$style_url = ELODIN_BRIDGE_URL . 'assets/admin-settings.css';

	if ( ! file_exists( $style_path ) ) {
		return;
	}

	wp_enqueue_style(
		'elodin-bridge-admin',
		$style_url,
		array(),
		(string) filemtime( $style_path )
	);

	$script_path = ELODIN_BRIDGE_DIR . '/assets/admin-image-sizes.js';
	$script_url = ELODIN_BRIDGE_URL . 'assets/admin-image-sizes.js';
	if ( file_exists( $script_path ) ) {
		wp_enqueue_script(
			'elodin-bridge-admin-image-sizes',
			$script_url,
			array(),
			(string) filemtime( $script_path ),
			true
		);
	}

	$autosave_script_path = ELODIN_BRIDGE_DIR . '/assets/admin-autosave.js';
	$autosave_script_url = ELODIN_BRIDGE_URL . 'assets/admin-autosave.js';
	if ( file_exists( $autosave_script_path ) ) {
		wp_enqueue_script(
			'elodin-bridge-admin-autosave',
			$autosave_script_url,
			array(),
			(string) filemtime( $autosave_script_path ),
			true
		);
	}
}
add_action( 'admin_enqueue_scripts', 'elodin_bridge_enqueue_admin_assets' );

/**
 * Render the Elodin Bridge admin page under Appearance.
 */
function elodin_bridge_render_admin_page() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	$heading_paragraph_overrides_available = elodin_bridge_is_generatepress_parent_theme();
	$heading_paragraph_overrides_enabled = elodin_bridge_is_heading_paragraph_overrides_enabled();
	$balanced_text_enabled = elodin_bridge_is_balanced_text_enabled();
	$default_paragraph_block_enabled = elodin_bridge_is_default_paragraph_block_enabled();
	$automatic_heading_margins_settings = elodin_bridge_get_automatic_heading_margins_settings();
	$automatic_heading_margins_enabled = elodin_bridge_is_automatic_heading_margins_enabled();
	$spacing_variables_settings = elodin_bridge_get_spacing_variables_settings();
	$spacing_variable_aliases = elodin_bridge_get_spacing_variable_aliases();
	$font_size_variables_settings = elodin_bridge_get_font_size_variables_settings();
	$font_size_variable_aliases = elodin_bridge_get_font_size_variable_aliases();
	$generateblocks_layout_gap_defaults_settings = elodin_bridge_get_generateblocks_layout_gap_defaults_settings();
	$generateblocks_layout_gap_defaults_enabled = elodin_bridge_is_generateblocks_layout_gap_defaults_enabled();
	$root_level_container_padding_settings = elodin_bridge_get_root_level_container_padding_settings();
	$root_level_container_padding_enabled = elodin_bridge_is_root_level_container_padding_enabled();
	$variables_theme_json_path = elodin_bridge_get_active_theme_json_path();
	$variables_theme_json_display_path = '';
	if ( '' !== $variables_theme_json_path ) {
		$normalized_path = wp_normalize_path( $variables_theme_json_path );
		$normalized_root = wp_normalize_path( ABSPATH );
		$variables_theme_json_display_path = $normalized_path;
		if ( 0 === strpos( $normalized_path, $normalized_root ) ) {
			$variables_theme_json_display_path = ltrim( substr( $normalized_path, strlen( $normalized_root ) ), '/' );
		}
	} else {
		$variables_theme_json_display_path = 'wp-content/themes/' . get_stylesheet() . '/theme.json';
	}
	$editor_ui_restrictions_enabled = elodin_bridge_is_editor_ui_restrictions_enabled();
	$media_library_infinite_scrolling_enabled = elodin_bridge_is_media_library_infinite_scrolling_enabled();
	$shortcodes_enabled = elodin_bridge_is_shortcodes_enabled();
	$generateblocks_boundary_highlights_enabled = elodin_bridge_is_generateblocks_boundary_highlights_enabled();
	$prettier_widgets_enabled = elodin_bridge_is_prettier_widgets_enabled();
	$last_child_margin_resets_enabled = elodin_bridge_is_last_child_margin_resets_enabled();
	$theme_json_button_padding_important_enabled = elodin_bridge_is_theme_json_button_padding_important_enabled();
	$theme_json_button_padding_values = elodin_bridge_get_theme_button_padding_values();
	$mobile_fixed_background_repair_enabled = elodin_bridge_is_mobile_fixed_background_repair_enabled();
	$reusable_block_flow_spacing_fix_enabled = elodin_bridge_is_reusable_block_flow_spacing_fix_enabled();
	$block_edge_class_settings = elodin_bridge_get_block_edge_class_settings();
	$block_edge_classes_enabled = elodin_bridge_is_block_edge_classes_enabled();
	$image_sizes_settings = elodin_bridge_get_image_sizes_settings();
	$image_size_rows = array_values( $image_sizes_settings['sizes'] );
	$content_type_behavior_settings = elodin_bridge_get_content_type_behavior_settings();
	$content_type_behavior_post_types = elodin_bridge_get_content_type_behavior_post_types();

	$template_path = ELODIN_BRIDGE_DIR . '/inc/views/settings-page.php';
	if ( ! file_exists( $template_path ) ) {
		return;
	}

	require $template_path;
}

/**
 * Register the Elodin Bridge settings page in the Appearance menu.
 */
function elodin_bridge_register_admin_menu() {
	add_theme_page(
		__( 'Elodin Bridge', 'elodin-bridge' ),
		__( 'Elodin Bridge', 'elodin-bridge' ),
		'edit_theme_options',
		'elodin-bridge',
		'elodin_bridge_render_admin_page'
	);
}
add_action( 'admin_menu', 'elodin_bridge_register_admin_menu' );

/**
 * Align settings save capability with the Bridge settings page capability.
 *
 * @return string
 */
function elodin_bridge_settings_page_capability() {
	return 'edit_theme_options';
}
add_filter( 'option_page_capability_elodin_bridge_settings', 'elodin_bridge_settings_page_capability' );

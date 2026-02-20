<?php

/**
 * Add an admin body class when the current post type is page-like.
 *
 * @param string $classes Existing admin body class string.
 * @return string
 */
function elodin_bridge_filter_admin_body_class( $classes ) {
	if ( ! elodin_bridge_is_content_type_behavior_enabled() || ! function_exists( 'get_current_screen' ) ) {
		return $classes;
	}

	$screen = get_current_screen();
	if ( ! $screen || empty( $screen->post_type ) || ! post_type_exists( $screen->post_type ) ) {
		return $classes;
	}

	if ( elodin_bridge_is_post_type_page_like( $screen->post_type ) ) {
		return trim( $classes . ' elodin-bridge-page-like-title' );
	}

	return trim( $classes . ' elodin-bridge-post-like-title' );
}
add_filter( 'admin_body_class', 'elodin_bridge_filter_admin_body_class' );

/**
 * Enqueue page-like content type title styles in the block editor.
 */
function elodin_bridge_enqueue_editor_page_like_title_styles() {
	if ( ! elodin_bridge_is_content_type_behavior_enabled() ) {
		return;
	}

	$style_path = ELODIN_BRIDGE_DIR . '/assets/editor-page-like-title.css';
	$style_url = ELODIN_BRIDGE_URL . 'assets/editor-page-like-title.css';

	if ( ! file_exists( $style_path ) ) {
		return;
	}

	wp_enqueue_style(
		'elodin-bridge-editor-page-like-title',
		$style_url,
		array( 'wp-edit-blocks' ),
		(string) filemtime( $style_path )
	);
}
add_action( 'enqueue_block_editor_assets', 'elodin_bridge_enqueue_editor_page_like_title_styles' );

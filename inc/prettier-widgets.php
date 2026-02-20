<?php

/**
 * Enqueue prettier widgets styles in the block editor.
 */
function elodin_bridge_enqueue_prettier_widgets_styles() {
	if ( ! elodin_bridge_is_prettier_widgets_enabled() ) {
		return;
	}

	$style_path = ELODIN_BRIDGE_DIR . '/assets/editor-prettier-widgets.css';
	$style_url = ELODIN_BRIDGE_URL . 'assets/editor-prettier-widgets.css';

	if ( ! file_exists( $style_path ) ) {
		return;
	}

	wp_enqueue_style(
		'elodin-bridge-editor-prettier-widgets',
		$style_url,
		array( 'wp-edit-blocks' ),
		(string) filemtime( $style_path )
	);
}
add_action( 'enqueue_block_editor_assets', 'elodin_bridge_enqueue_prettier_widgets_styles' );

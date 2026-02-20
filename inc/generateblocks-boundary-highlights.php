<?php

/**
 * Enqueue GenerateBlocks boundary highlight styles in the block editor.
 */
function elodin_bridge_enqueue_generateblocks_boundary_highlight_styles() {
	if ( ! elodin_bridge_is_generateblocks_boundary_highlights_enabled() ) {
		return;
	}

	$style_path = ELODIN_BRIDGE_DIR . '/assets/editor-generateblocks-boundary-highlights.css';
	$style_url = ELODIN_BRIDGE_URL . 'assets/editor-generateblocks-boundary-highlights.css';

	if ( ! file_exists( $style_path ) ) {
		return;
	}

	wp_enqueue_style(
		'elodin-bridge-editor-generateblocks-boundary-highlights',
		$style_url,
		array( 'wp-edit-blocks' ),
		(string) filemtime( $style_path )
	);
}
add_action( 'enqueue_block_editor_assets', 'elodin_bridge_enqueue_generateblocks_boundary_highlight_styles' );

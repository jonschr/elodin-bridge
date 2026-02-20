<?php

/**
 * Enqueue last-child margin reset styles for front-end and editor content.
 */
function elodin_bridge_enqueue_last_child_margin_reset_styles() {
	if ( ! elodin_bridge_is_last_child_margin_resets_enabled() ) {
		return;
	}

	$style_path = ELODIN_BRIDGE_DIR . '/assets/last-child-margin-resets.css';
	$style_url = ELODIN_BRIDGE_URL . 'assets/last-child-margin-resets.css';

	if ( ! file_exists( $style_path ) ) {
		return;
	}

	wp_enqueue_style(
		'elodin-bridge-last-child-margin-resets',
		$style_url,
		array(),
		(string) filemtime( $style_path )
	);
}
add_action( 'enqueue_block_assets', 'elodin_bridge_enqueue_last_child_margin_reset_styles' );

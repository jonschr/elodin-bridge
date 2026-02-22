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

/**
 * Build last-child button group top margin CSS.
 *
 * @return string
 */
function elodin_bridge_build_last_child_button_group_top_margin_css() {
	$settings = elodin_bridge_get_last_child_button_group_top_margin_settings();
	if ( empty( $settings['enabled'] ) ) {
		return '';
	}

	$margin_top = trim( (string) ( $settings['value'] ?? '' ) );
	if ( '' === $margin_top ) {
		return '';
	}

	return '.wp-block-buttons:last-child{margin-top:' . $margin_top . ';}';
}

/**
 * Enqueue last-child button group top margin styles for front-end and editor content.
 */
function elodin_bridge_enqueue_last_child_button_group_top_margin_styles() {
	$css = elodin_bridge_build_last_child_button_group_top_margin_css();
	if ( '' === $css ) {
		return;
	}

	$handle = 'elodin-bridge-last-child-button-group-top-margin';
	wp_register_style( $handle, false, array(), ELODIN_BRIDGE_VERSION );
	wp_enqueue_style( $handle );
	wp_add_inline_style( $handle, $css );
}
add_action( 'enqueue_block_assets', 'elodin_bridge_enqueue_last_child_button_group_top_margin_styles' );

<?php

/**
 * Build FSE GenerateBlocks container width override CSS.
 *
 * @return string
 */
function elodin_bridge_build_fse_gb_container_width_override_css() {
	if ( ! function_exists( 'wp_is_block_theme' ) || ! wp_is_block_theme() ) {
		return '';
	}

	if ( ! elodin_bridge_is_fse_gb_container_width_override_enabled() ) {
		return '';
	}

	$value = 'var(--wp--style--global--wide-size,var(--wp--style--global--content-size,1200px))';
	$selectors = array(
		':root',
		'body',
		'.editor-styles-wrapper',
		'.block-editor-iframe__body',
	);

	return implode( ',', $selectors ) . '{--gb-container-width:' . $value . '!important;}';
}

/**
 * Enqueue FSE GenerateBlocks container width override styles.
 */
function elodin_bridge_enqueue_fse_gb_container_width_override_styles() {
	$css = elodin_bridge_build_fse_gb_container_width_override_css();
	if ( '' === $css ) {
		return;
	}

	$handle = 'elodin-bridge-fse-gb-container-width-override';
	wp_register_style( $handle, false, array(), ELODIN_BRIDGE_VERSION );
	wp_enqueue_style( $handle );
	wp_add_inline_style( $handle, $css );
}
add_action( 'enqueue_block_assets', 'elodin_bridge_enqueue_fse_gb_container_width_override_styles' );

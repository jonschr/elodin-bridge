<?php

/**
 * Build reusable block flow spacing fix CSS.
 *
 * @return string
 */
function elodin_bridge_build_reusable_block_flow_spacing_fix_css() {
	if ( ! elodin_bridge_is_reusable_block_flow_spacing_fix_enabled() ) {
		return '';
	}

	return ':root :where(.editor-styles-wrapper) :where(.is-layout-flow) > *{margin-block-start:0;margin-block-end:0;}';
}

/**
 * Enqueue reusable block flow spacing fix styles in the block editor only.
 */
function elodin_bridge_enqueue_reusable_block_flow_spacing_fix_styles() {
	$css = elodin_bridge_build_reusable_block_flow_spacing_fix_css();
	if ( '' === $css ) {
		return;
	}

	$handle = 'elodin-bridge-reusable-block-flow-spacing-fix';
	wp_register_style( $handle, false, array(), ELODIN_BRIDGE_VERSION );
	wp_enqueue_style( $handle );
	wp_add_inline_style( $handle, $css );
}
add_action( 'enqueue_block_editor_assets', 'elodin_bridge_enqueue_reusable_block_flow_spacing_fix_styles' );

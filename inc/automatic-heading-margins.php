<?php

/**
 * Get media query for automatic heading margins.
 *
 * @param string $device Target device: tablet|mobile.
 * @return string
 */
function elodin_bridge_get_heading_margin_media_query( $device ) {
	if ( function_exists( 'generate_get_media_query' ) ) {
		return (string) generate_get_media_query( $device );
	}

	return ( 'tablet' === $device ) ? '(max-width: 1024px)' : '(max-width: 768px)';
}

/**
 * Build automatic heading margin CSS.
 *
 * @return string
 */
function elodin_bridge_build_automatic_heading_margins_css() {
	$settings = elodin_bridge_get_automatic_heading_margins_settings();
	if ( empty( $settings['enabled'] ) ) {
		return '';
	}

	$selectors = 'h1.wp-block-heading,h2.wp-block-heading,h3.wp-block-heading,h4.wp-block-heading';
	$first_child_selectors = 'h1.wp-block-heading:first-child,h2.wp-block-heading:first-child,h3.wp-block-heading:first-child,h4.wp-block-heading:first-child';
	$kicker_adjacent_heading_selectors = '.is-style-kicker+h1.wp-block-heading,.is-style-kicker+h2.wp-block-heading,.is-style-kicker+h3.wp-block-heading,.is-style-kicker+h4.wp-block-heading';
	$desktop = trim( (string) ( $settings['desktop'] ?? '' ) );
	$tablet = trim( (string) ( $settings['tablet'] ?? '' ) );
	$mobile = trim( (string) ( $settings['mobile'] ?? '' ) );
	$css = '';

	if ( '' !== $desktop ) {
		$css .= $selectors . '{margin-top:' . $desktop . ';}';
	}

	$css .= $first_child_selectors . '{margin-top:0;}';
	$css .= '.is-style-kicker:first-child{margin-block-start:0!important;}';
	$css .= '.is-style-kicker:last-child{margin-block-end:0!important;}';
	$css .= $kicker_adjacent_heading_selectors . '{margin-block-start:0!important;}';

	if ( '' !== $tablet ) {
		$css .= '@media ' . elodin_bridge_get_heading_margin_media_query( 'tablet' ) . '{' . $selectors . '{margin-top:' . $tablet . ';}}';
	}

	if ( '' !== $mobile ) {
		$css .= '@media ' . elodin_bridge_get_heading_margin_media_query( 'mobile' ) . '{' . $selectors . '{margin-top:' . $mobile . ';}}';
	}

	return $css;
}

/**
 * Enqueue automatic heading margin styles for front-end and block editor content.
 */
function elodin_bridge_enqueue_automatic_heading_margin_styles() {
	if ( ! elodin_bridge_is_automatic_heading_margins_enabled() ) {
		return;
	}

	$css = elodin_bridge_build_automatic_heading_margins_css();
	if ( '' === $css ) {
		return;
	}

	$handle = 'elodin-bridge-automatic-heading-margins';
	wp_register_style( $handle, false, array(), ELODIN_BRIDGE_VERSION );
	wp_enqueue_style( $handle );
	wp_add_inline_style( $handle, $css );
}
add_action( 'enqueue_block_assets', 'elodin_bridge_enqueue_automatic_heading_margin_styles' );

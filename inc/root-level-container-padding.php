<?php

/**
 * Get media query for root-level container padding.
 *
 * @param string $device Target device: tablet|mobile.
 * @return string
 */
function elodin_bridge_get_root_level_container_padding_media_query( $device ) {
	if ( function_exists( 'generate_get_media_query' ) ) {
		return (string) generate_get_media_query( $device );
	}

	return ( 'tablet' === $device ) ? '(max-width: 1024px)' : '(max-width: 768px)';
}

/**
 * Build root-level container padding CSS for editor and front-end.
 *
 * @return string
 */
function elodin_bridge_build_root_level_container_padding_css() {
	$settings = elodin_bridge_get_root_level_container_padding_settings();
	if ( empty( $settings['enabled'] ) ) {
		return '';
	}

	$desktop = trim( (string) ( $settings['desktop'] ?? '' ) );
	$tablet = trim( (string) ( $settings['tablet'] ?? '' ) );
	$mobile = trim( (string) ( $settings['mobile'] ?? '' ) );
	if ( '' === $desktop && '' === $tablet && '' === $mobile ) {
		return '';
	}

	$selectors = array(
		'.root-level-container',
		':where(.entry-content > :is([class^=\'gb-element-\'], [class*=\' gb-element-\']))',
		':where(.entry-content > div:not([class]))',
		':where(body > :is([class^=\'gb-element-\'], [class*=\' gb-element-\']))',
		':where(.gb-is-root-block > [class^=\'gb-element-\'])',
		':where(.gb-root-block-generateblocks-container > [class^=\'gb-element-\'])',
		':where(.block-library-block__reusable-block-container > [class^=\'gb-element-\'])',
		':where(.gb-is-root-block > .wp-block-generateblocks-element)',
		':where(.is-root-container > .block-library-block__reusable-block-container > .wp-block-generateblocks-element)',
	);
	$selector_list = implode( ',', $selectors );
	$css = '';

	if ( '' !== $desktop ) {
		$css .= $selector_list . '{padding:' . $desktop . ';margin:0;}';
	} else {
		$css .= $selector_list . '{margin:0;}';
	}

	if ( '' !== $tablet ) {
		$css .= '@media ' . elodin_bridge_get_root_level_container_padding_media_query( 'tablet' ) . '{' . $selector_list . '{padding:' . $tablet . ';}}';
	}

	if ( '' !== $mobile ) {
		$css .= '@media ' . elodin_bridge_get_root_level_container_padding_media_query( 'mobile' ) . '{' . $selector_list . '{padding:' . $mobile . ';}}';
	}

	return $css;
}

/**
 * Enqueue root-level container padding styles in both editor and front-end contexts.
 */
function elodin_bridge_enqueue_root_level_container_padding_styles() {
	$css = elodin_bridge_build_root_level_container_padding_css();
	if ( '' === $css ) {
		return;
	}

	$handle = 'elodin-bridge-root-level-container-padding';
	wp_register_style( $handle, false, array(), ELODIN_BRIDGE_VERSION );
	wp_enqueue_style( $handle );
	wp_add_inline_style( $handle, $css );
}
add_action( 'enqueue_block_assets', 'elodin_bridge_enqueue_root_level_container_padding_styles' );

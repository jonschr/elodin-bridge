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
 * Build declarations for root-level container padding axes.
 *
 * Uses physical sides for broad compatibility and predictable horizontal behavior.
 *
 * @param string $vertical Vertical padding value.
 * @param string $horizontal Horizontal padding value.
 * @param bool   $include_margin_reset Whether to include margin reset.
 * @return string
 */
function elodin_bridge_build_root_level_container_padding_declarations( $vertical, $horizontal, $include_margin_reset = false ) {
	$declarations = array();

	if ( $include_margin_reset ) {
		$declarations[] = 'margin:0';
	}

	if ( '' !== $vertical ) {
		$declarations[] = 'padding-top:' . $vertical;
		$declarations[] = 'padding-bottom:' . $vertical;
	}

	if ( '' !== $horizontal ) {
		$declarations[] = 'padding-left:' . $horizontal;
		$declarations[] = 'padding-right:' . $horizontal;
	}

	if ( empty( $declarations ) ) {
		return '';
	}

	return implode( ';', $declarations ) . ';';
}

/**
 * Build root-level container padding CSS for editor and front-end.
 *
 * @return string
 */
function elodin_bridge_build_root_level_container_padding_css() {
	if ( ! elodin_bridge_is_generateblocks_available() ) {
		return '';
	}

	$container_settings = elodin_bridge_get_root_level_container_padding_settings();
	if ( empty( $container_settings['enabled'] ) ) {
		return '';
	}

	// Inherit axis values from the FSE root-level Group block padding setting.
	$source_settings = elodin_bridge_get_root_level_group_padding_settings();
	$desktop_vertical = trim( (string) ( $source_settings['desktop_vertical'] ?? $container_settings['desktop_vertical'] ?? $container_settings['desktop'] ?? '' ) );
	$desktop_horizontal = trim( (string) ( $source_settings['desktop_horizontal'] ?? $container_settings['desktop_horizontal'] ?? $container_settings['desktop'] ?? '' ) );
	$tablet_vertical = trim( (string) ( $source_settings['tablet_vertical'] ?? $container_settings['tablet_vertical'] ?? $container_settings['tablet'] ?? '' ) );
	$tablet_horizontal = trim( (string) ( $source_settings['tablet_horizontal'] ?? $container_settings['tablet_horizontal'] ?? $container_settings['tablet'] ?? '' ) );
	$mobile_vertical = trim( (string) ( $source_settings['mobile_vertical'] ?? $container_settings['mobile_vertical'] ?? $container_settings['mobile'] ?? '' ) );
	$mobile_horizontal = trim( (string) ( $source_settings['mobile_horizontal'] ?? $container_settings['mobile_horizontal'] ?? $container_settings['mobile'] ?? '' ) );
	if (
		'' === $desktop_vertical &&
		'' === $desktop_horizontal &&
		'' === $tablet_vertical &&
		'' === $tablet_horizontal &&
		'' === $mobile_vertical &&
		'' === $mobile_horizontal
	) {
		return '';
	}

	$selectors = array(
		'.root-level-container',
		':where(.entry-content > :is([class^=\'gb-element-\'], [class*=\' gb-element-\']))',
		':where(.entry-content > div:not([class]))',
		':where(body > :is([class^=\'gb-element-\'], [class*=\' gb-element-\']))',
		':where(.is-root-container > div:not([class]))',
		':where(.wp-block-post-content > div:not([class]))',
		':where(.is-root-container > .wp-block-generateblocks-element)',
		':where(.wp-block-post-content > .wp-block-generateblocks-element)',
		':where(.gb-is-root-block > [class^=\'gb-element-\'])',
		':where(.gb-root-block-generateblocks-container > [class^=\'gb-element-\'])',
		':where(.block-library-block__reusable-block-container > [class^=\'gb-element-\'])',
		':where(.block-library-block__reusable-block-container > div:not([class]))',
		':where(.gb-is-root-block > .wp-block-generateblocks-element)',
		':where(.is-root-container > .block-library-block__reusable-block-container > .wp-block-generateblocks-element)',
		':where(.is-root-container > .block-library-block__reusable-block-container > div:not([class]))',
	);
	$selector_list = implode( ',', $selectors );
	$css = '';
	$desktop_declarations = elodin_bridge_build_root_level_container_padding_declarations( $desktop_vertical, $desktop_horizontal, true );
	if ( '' !== $desktop_declarations ) {
		$css .= $selector_list . '{' . $desktop_declarations . '}';
	}

	$tablet_declarations = elodin_bridge_build_root_level_container_padding_declarations( $tablet_vertical, $tablet_horizontal );
	if ( '' !== $tablet_declarations ) {
		$css .= '@media ' . elodin_bridge_get_root_level_container_padding_media_query( 'tablet' ) . '{' . $selector_list . '{' . $tablet_declarations . '}}';
	}

	$mobile_declarations = elodin_bridge_build_root_level_container_padding_declarations( $mobile_vertical, $mobile_horizontal );
	if ( '' !== $mobile_declarations ) {
		$css .= '@media ' . elodin_bridge_get_root_level_container_padding_media_query( 'mobile' ) . '{' . $selector_list . '{' . $mobile_declarations . '}}';
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

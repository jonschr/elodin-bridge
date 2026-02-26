<?php

/**
 * Build root-level Group block padding CSS for block themes (editor + front-end).
 *
 * @return string
 */
function elodin_bridge_build_root_level_group_padding_css() {
	if ( ! function_exists( 'wp_is_block_theme' ) || ! wp_is_block_theme() ) {
		return '';
	}

	$group_settings = elodin_bridge_get_root_level_group_padding_settings();
	if ( empty( $group_settings['enabled'] ) ) {
		return '';
	}

	$desktop_vertical = trim( (string) ( $group_settings['desktop_vertical'] ?? $group_settings['desktop'] ?? '' ) );
	$desktop_horizontal = trim( (string) ( $group_settings['desktop_horizontal'] ?? $group_settings['desktop'] ?? '' ) );
	$tablet_vertical = trim( (string) ( $group_settings['tablet_vertical'] ?? $group_settings['tablet'] ?? '' ) );
	$tablet_horizontal = trim( (string) ( $group_settings['tablet_horizontal'] ?? $group_settings['tablet'] ?? '' ) );
	$mobile_vertical = trim( (string) ( $group_settings['mobile_vertical'] ?? $group_settings['mobile'] ?? '' ) );
	$mobile_horizontal = trim( (string) ( $group_settings['mobile_horizontal'] ?? $group_settings['mobile'] ?? '' ) );
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
		'.is-root-container > .wp-block-group',
		'.wp-block-post-content > .wp-block-group',
	);
	$selector_list = implode( ',', $selectors );
	$css = '';

	$desktop_declarations = elodin_bridge_build_root_level_container_padding_declarations( $desktop_vertical, $desktop_horizontal );
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
 * Enqueue root-level Group block padding styles in both editor and front-end contexts.
 */
function elodin_bridge_enqueue_root_level_group_padding_styles() {
	$css = elodin_bridge_build_root_level_group_padding_css();
	if ( '' === $css ) {
		return;
	}

	$handle = 'elodin-bridge-root-level-group-padding';
	wp_register_style( $handle, false, array(), ELODIN_BRIDGE_VERSION );
	wp_enqueue_style( $handle );
	wp_add_inline_style( $handle, $css );
}
add_action( 'enqueue_block_assets', 'elodin_bridge_enqueue_root_level_group_padding_styles' );

<?php

/**
 * Build button padding override CSS from active theme.json values.
 *
 * @return string
 */
function elodin_bridge_build_theme_json_button_padding_important_css() {
	if ( ! elodin_bridge_is_theme_json_button_padding_important_enabled() ) {
		return '';
	}

	$padding_values = elodin_bridge_get_theme_button_padding_values();
	$all = trim( (string) ( $padding_values['all'] ?? '' ) );
	$top = trim( (string) ( $padding_values['top'] ?? '' ) );
	$right = trim( (string) ( $padding_values['right'] ?? '' ) );
	$bottom = trim( (string) ( $padding_values['bottom'] ?? '' ) );
	$left = trim( (string) ( $padding_values['left'] ?? '' ) );

	$declarations = array();
	if ( '' !== $all ) {
		$declarations[] = 'padding:' . $all . ' !important';
	} else {
		if ( '' !== $top ) {
			$declarations[] = 'padding-top:' . $top . ' !important';
		}
		if ( '' !== $right ) {
			$declarations[] = 'padding-right:' . $right . ' !important';
		}
		if ( '' !== $bottom ) {
			$declarations[] = 'padding-bottom:' . $bottom . ' !important';
		}
		if ( '' !== $left ) {
			$declarations[] = 'padding-left:' . $left . ' !important';
		}
	}

	if ( empty( $declarations ) ) {
		return '';
	}

	return '.wp-block-button__link{' . implode( ';', $declarations ) . ';}';
}

/**
 * Enqueue theme.json button padding override styles in editor and front-end contexts.
 */
function elodin_bridge_enqueue_theme_json_button_padding_important_styles() {
	$css = elodin_bridge_build_theme_json_button_padding_important_css();
	if ( '' === $css ) {
		return;
	}

	$handle = 'elodin-bridge-theme-json-button-padding-important';
	wp_register_style( $handle, false, array(), ELODIN_BRIDGE_VERSION );
	wp_enqueue_style( $handle );
	wp_add_inline_style( $handle, $css );
}
add_action( 'enqueue_block_assets', 'elodin_bridge_enqueue_theme_json_button_padding_important_styles' );

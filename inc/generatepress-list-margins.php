<?php

/**
 * Build GeneratePress list margin override CSS.
 *
 * @return string
 */
function elodin_bridge_build_generatepress_list_margins_css() {
	if ( ! elodin_bridge_is_generatepress_list_margins_enabled() ) {
		return '';
	}

	$settings = elodin_bridge_get_generatepress_list_margins_settings();
	$margin_top = trim( (string) ( $settings['margin_top'] ?? '' ) );
	$margin_right = trim( (string) ( $settings['margin_right'] ?? '' ) );
	$margin_bottom = trim( (string) ( $settings['margin_bottom'] ?? '' ) );
	$margin_left = trim( (string) ( $settings['margin_left'] ?? '' ) );

	$declarations = array();
	if ( '' !== $margin_top ) {
		$declarations[] = 'margin-top:' . $margin_top;
	}
	if ( '' !== $margin_right ) {
		$declarations[] = 'margin-right:' . $margin_right;
	}
	if ( '' !== $margin_bottom ) {
		$declarations[] = 'margin-bottom:' . $margin_bottom;
	}
	if ( '' !== $margin_left ) {
		$declarations[] = 'margin-left:' . $margin_left;
	}

	if ( empty( $declarations ) ) {
		return '';
	}

	$declarations[] = 'padding-top:0';
	$declarations[] = 'padding-right:0';
	$declarations[] = 'padding-bottom:0';
	$declarations[] = 'padding-left:0';

	return 'ol,ul{' . implode( ';', $declarations ) . ';}';
}

/**
 * Enqueue GeneratePress list margin override styles on the front-end.
 */
function elodin_bridge_enqueue_generatepress_list_margins_styles() {
	$css = elodin_bridge_build_generatepress_list_margins_css();
	if ( '' === $css ) {
		return;
	}

	if ( wp_style_is( 'generate-style', 'registered' ) || wp_style_is( 'generate-style', 'enqueued' ) ) {
		wp_add_inline_style( 'generate-style', $css );
		return;
	}

	$handle = 'elodin-bridge-generatepress-list-margins';
	wp_register_style( $handle, false, array(), ELODIN_BRIDGE_VERSION );
	wp_enqueue_style( $handle );
	wp_add_inline_style( $handle, $css );
}
add_action( 'wp_enqueue_scripts', 'elodin_bridge_enqueue_generatepress_list_margins_styles', 120 );

/**
 * Inject GeneratePress list margin override styles into editor iframe settings.
 *
 * @param array<string,mixed> $settings Block editor settings.
 * @param mixed               $editor_context Block editor context.
 * @return array<string,mixed>
 */
function elodin_bridge_inject_generatepress_list_margins_into_editor_settings( $settings, $editor_context ) {
	$css = elodin_bridge_build_generatepress_list_margins_css();
	if ( '' === $css ) {
		return $settings;
	}

	if ( ! isset( $settings['styles'] ) || ! is_array( $settings['styles'] ) ) {
		$settings['styles'] = array();
	}

	$settings['styles'][] = array(
		'css' => $css,
	);

	return $settings;
}
add_filter( 'block_editor_settings_all', 'elodin_bridge_inject_generatepress_list_margins_into_editor_settings', 120, 2 );

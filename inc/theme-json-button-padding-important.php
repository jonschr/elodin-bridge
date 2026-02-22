<?php

/**
 * Build a normalized CSS declaration list from theme.json declarations.
 *
 * @param array<string,string> $raw_declarations Raw declarations.
 * @return array<int,string>
 */
function elodin_bridge_get_theme_json_button_css_declarations( $raw_declarations ) {
	$declarations = array();

	foreach ( $raw_declarations as $property => $value ) {
		$property = sanitize_key( str_replace( '-', '_', (string) $property ) );
		$property = str_replace( '_', '-', $property );
		$value = trim( (string) $value );
		if ( '' === $property || '' === $value ) {
			continue;
		}

		$declarations[] = $property . ':' . $value;
	}

	return $declarations;
}

/**
 * Get base selectors used to out-rank parent-theme button defaults.
 *
 * @return array<int,string>
 */
function elodin_bridge_get_theme_json_button_base_selectors() {
	return array(
		'.entry-content .wp-block-button .wp-block-button__link',
		'.inside-article .wp-block-button .wp-block-button__link',
		'.wp-site-blocks .wp-block-button .wp-block-button__link',
		'.is-root-container .wp-block-button .wp-block-button__link',
		'.editor-styles-wrapper .wp-block-button .wp-block-button__link',
		'.editor-visual-editor .wp-block-button .wp-block-button__link',
		'.block-editor-writing-flow .wp-block-button .wp-block-button__link',
		'.block-editor-block-list__layout .wp-block-button .wp-block-button__link',
		'.wp-block-button .wp-block-button__link',
	);
}

/**
 * Get outline selectors used to out-rank parent-theme variation styles.
 *
 * @return array<int,string>
 */
function elodin_bridge_get_theme_json_button_outline_selectors() {
	return array(
		'.entry-content .wp-block-button.is-style-outline .wp-block-button__link',
		'.entry-content .wp-block-button[class*="is-style-outline--"] .wp-block-button__link',
		'.inside-article .wp-block-button.is-style-outline .wp-block-button__link',
		'.inside-article .wp-block-button[class*="is-style-outline--"] .wp-block-button__link',
		'.wp-site-blocks .wp-block-button.is-style-outline .wp-block-button__link',
		'.wp-site-blocks .wp-block-button[class*="is-style-outline--"] .wp-block-button__link',
		'.is-root-container .wp-block-button.is-style-outline .wp-block-button__link',
		'.is-root-container .wp-block-button[class*="is-style-outline--"] .wp-block-button__link',
		'.editor-styles-wrapper .wp-block-button.is-style-outline .wp-block-button__link',
		'.editor-styles-wrapper .wp-block-button[class*="is-style-outline--"] .wp-block-button__link',
		'.editor-visual-editor .wp-block-button.is-style-outline .wp-block-button__link',
		'.editor-visual-editor .wp-block-button[class*="is-style-outline--"] .wp-block-button__link',
		'.block-editor-writing-flow .wp-block-button.is-style-outline .wp-block-button__link',
		'.block-editor-writing-flow .wp-block-button[class*="is-style-outline--"] .wp-block-button__link',
		'.wp-block-button.is-style-outline .wp-block-button__link',
		'.wp-block-button[class*="is-style-outline--"] .wp-block-button__link',
	);
}

/**
 * Build button style override CSS from active theme.json values.
 *
 * @return string
 */
function elodin_bridge_build_theme_json_button_padding_important_css() {
	if ( ! elodin_bridge_is_theme_json_button_padding_important_enabled() ) {
		return '';
	}

	$overrides = elodin_bridge_get_theme_button_style_overrides();
	$base_declarations = isset( $overrides['base'] ) && is_array( $overrides['base'] ) ? $overrides['base'] : array();
	$outline_declarations = isset( $overrides['outline'] ) && is_array( $overrides['outline'] ) ? $overrides['outline'] : array();

	$css = '';

	if ( ! empty( $base_declarations ) ) {
		$declarations = elodin_bridge_get_theme_json_button_css_declarations( $base_declarations );
		if ( ! empty( $declarations ) ) {
			$css .= implode( ',', elodin_bridge_get_theme_json_button_base_selectors() ) . '{' . implode( ';', $declarations ) . ';}';
		}
	}

	if ( ! empty( $outline_declarations ) ) {
		$declarations = elodin_bridge_get_theme_json_button_css_declarations( $outline_declarations );
		if ( ! empty( $declarations ) ) {
			$css .= implode( ',', elodin_bridge_get_theme_json_button_outline_selectors() ) . '{' . implode( ';', $declarations ) . ';}';
		}
	}

	return $css;
}

/**
 * Enqueue theme.json button style override styles in editor and front-end contexts.
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
add_action( 'enqueue_block_assets', 'elodin_bridge_enqueue_theme_json_button_padding_important_styles', 100 );
add_action( 'wp_enqueue_scripts', 'elodin_bridge_enqueue_theme_json_button_padding_important_styles', 100 );
add_action( 'enqueue_block_editor_assets', 'elodin_bridge_enqueue_theme_json_button_padding_important_styles', 100 );
add_action( 'admin_enqueue_scripts', 'elodin_bridge_enqueue_theme_json_button_padding_important_styles', 100 );

/**
 * Inject button override CSS into post editor iframe styles for all CPTs.
 *
 * @param array<string,mixed> $settings Block editor settings.
 * @param mixed               $editor_context Block editor context.
 * @return array<string,mixed>
 */
function elodin_bridge_inject_theme_json_button_styles_into_post_editor_settings( $settings, $editor_context ) {
	if ( ! elodin_bridge_is_theme_json_button_padding_important_enabled() ) {
		return $settings;
	}

	$is_post_editor = false;
	if ( is_object( $editor_context ) && isset( $editor_context->name ) ) {
		$is_post_editor = ( 'core/edit-post' === (string) $editor_context->name );
	}

	if ( ! $is_post_editor ) {
		return $settings;
	}

	$css = elodin_bridge_build_theme_json_button_padding_important_css();
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
add_filter( 'block_editor_settings_all', 'elodin_bridge_inject_theme_json_button_styles_into_post_editor_settings', 100, 2 );

<?php

/**
 * Determine if a value indicates an explicit typography reset.
 *
 * @param mixed $value Setting value.
 * @return bool
 */
function elodin_bridge_is_typography_reset_value( $value ) {
	return ELODIN_BRIDGE_TYPOGRAPHY_RESET === $value;
}

/**
 * Determine if a typography setting has a usable value.
 *
 * @param mixed $value Setting value.
 * @return bool
 */
function elodin_bridge_has_typography_value( $value ) {
	return null !== $value && '' !== $value;
}

/**
 * Return the last matching typography item for a selector.
 *
 * @param array<int,mixed> $settings Saved typography settings.
 * @param string           $selector Selector key.
 * @return array<string,mixed>
 */
function elodin_bridge_get_typography_item( $settings, $selector ) {
	$result = array();

	foreach ( $settings as $item ) {
		if ( ! is_array( $item ) || empty( $item['selector'] ) ) {
			continue;
		}

		if ( $selector === $item['selector'] ) {
			$result = $item;
		}
	}

	return $result;
}

/**
 * Defaults for typography keys used by this plugin.
 *
 * @return array<string,mixed>
 */
function elodin_bridge_get_typography_defaults() {
	if ( class_exists( 'GeneratePress_Typography' ) && method_exists( 'GeneratePress_Typography', 'get_defaults' ) ) {
		return (array) GeneratePress_Typography::get_defaults();
	}

	return array(
		'fontFamily' => '',
		'fontWeight' => '',
		'textTransform' => '',
		'textDecoration' => '',
		'fontStyle' => '',
		'fontSize' => '',
		'fontSizeTablet' => '',
		'fontSizeMobile' => '',
		'fontSizeUnit' => 'px',
		'lineHeight' => '',
		'lineHeightTablet' => '',
		'lineHeightMobile' => '',
		'lineHeightUnit' => '',
		'letterSpacing' => '',
		'letterSpacingTablet' => '',
		'letterSpacingMobile' => '',
		'letterSpacingUnit' => 'px',
		'marginBottom' => '',
		'marginBottomTablet' => '',
		'marginBottomMobile' => '',
		'marginBottomUnit' => 'px',
	);
}

/**
 * Merge typography layers while preserving inherited values.
 *
 * @param array<int,array<string,mixed>> $layers Typography layers in order.
 * @return array<string,mixed>
 */
function elodin_bridge_merge_typography_layers( $layers ) {
	$defaults = elodin_bridge_get_typography_defaults();
	$merged = $defaults;
	$allowed_keys = array_fill_keys( array_keys( $defaults ), true );

	foreach ( $layers as $layer ) {
		if ( ! is_array( $layer ) ) {
			continue;
		}

		foreach ( $layer as $key => $value ) {
			if ( empty( $allowed_keys[ $key ] ) ) {
				continue;
			}

			if ( elodin_bridge_has_typography_value( $value ) ) {
				$merged[ $key ] = $value;
				continue;
			}

			// Blank values in the more specific layer should explicitly reset inherited values.
			if ( is_string( $value ) && '' === trim( $value ) && ! str_ends_with( $key, 'Unit' ) ) {
				$merged[ $key ] = ELODIN_BRIDGE_TYPOGRAPHY_RESET;
			}
		}
	}

	if (
		! elodin_bridge_is_typography_reset_value( $merged['fontFamily'] ?? '' )
		&& ! empty( $merged['fontFamily'] )
		&& class_exists( 'GeneratePress_Typography' )
		&& method_exists( 'GeneratePress_Typography', 'get_font_family' )
	) {
		$merged['fontFamily'] = GeneratePress_Typography::get_font_family( $merged['fontFamily'] );
	}

	return $merged;
}

/**
 * Get dynamic GeneratePress typography presets for paragraph and h1-h4 overrides.
 *
 * @return array<string,array<string,mixed>>
 */
function elodin_bridge_get_dynamic_typography_presets() {
	if ( ! function_exists( 'generate_get_option' ) ) {
		return array();
	}

	if ( function_exists( 'generate_is_using_dynamic_typography' ) && ! generate_is_using_dynamic_typography() ) {
		return array();
	}

	$settings = (array) generate_get_option( 'typography' );
	$all_headings = elodin_bridge_get_typography_item( $settings, 'all-headings' );
	$body = elodin_bridge_get_typography_item( $settings, 'body' );

	return array(
		'p' => elodin_bridge_merge_typography_layers( array( $body ) ),
		'h1' => elodin_bridge_merge_typography_layers( array( $all_headings, elodin_bridge_get_typography_item( $settings, 'h1' ) ) ),
		'h2' => elodin_bridge_merge_typography_layers( array( $all_headings, elodin_bridge_get_typography_item( $settings, 'h2' ) ) ),
		'h3' => elodin_bridge_merge_typography_layers( array( $all_headings, elodin_bridge_get_typography_item( $settings, 'h3' ) ) ),
		'h4' => elodin_bridge_merge_typography_layers( array( $all_headings, elodin_bridge_get_typography_item( $settings, 'h4' ) ) ),
	);
}

/**
 * Build a CSS value with an optional unit.
 *
 * @param mixed  $value CSS value.
 * @param string $unit  CSS unit.
 * @return string
 */
function elodin_bridge_build_css_value( $value, $unit = '' ) {
	if ( elodin_bridge_is_typography_reset_value( $value ) ) {
		return 'unset';
	}

	if ( ! elodin_bridge_has_typography_value( $value ) ) {
		return '';
	}

	$value = trim( (string) $value );
	$unit = trim( (string) $unit );

	if ( '' === $unit ) {
		return $value;
	}

	if ( preg_match( '/^-?\d*\.?\d+$/', $value ) ) {
		return $value . $unit;
	}

	return $value;
}

/**
 * Build CSS declarations for a typography preset at a breakpoint.
 *
 * @param array<string,mixed> $preset Typography preset.
 * @param string              $device desktop|tablet|mobile
 * @return array<string,string>
 */
function elodin_bridge_build_typography_declarations( $preset, $device = 'desktop' ) {
	$suffix = '';
	if ( 'tablet' === $device ) {
		$suffix = 'Tablet';
	} elseif ( 'mobile' === $device ) {
		$suffix = 'Mobile';
	}

	$declarations = array();
	$common = array(
		'font-family' => 'fontFamily',
		'font-weight' => 'fontWeight',
		'text-transform' => 'textTransform',
		'text-decoration' => 'textDecoration',
		'font-style' => 'fontStyle',
	);

	foreach ( $common as $property => $key ) {
		$value = $preset[ $key ] ?? '';
		if ( elodin_bridge_is_typography_reset_value( $value ) ) {
			$declarations[ $property ] = 'unset';
		} elseif ( elodin_bridge_has_typography_value( $value ) ) {
			$declarations[ $property ] = trim( (string) $value );
		}
	}

	$size_key = 'fontSize' . $suffix;
	$line_height_key = 'lineHeight' . $suffix;
	$letter_spacing_key = 'letterSpacing' . $suffix;
	$margin_bottom_key = 'marginBottom' . $suffix;

	$size_value = elodin_bridge_build_css_value( $preset[ $size_key ] ?? '', $preset['fontSizeUnit'] ?? 'px' );
	$line_height_value = elodin_bridge_build_css_value( $preset[ $line_height_key ] ?? '', $preset['lineHeightUnit'] ?? '' );
	$letter_spacing_value = elodin_bridge_build_css_value( $preset[ $letter_spacing_key ] ?? '', $preset['letterSpacingUnit'] ?? 'px' );
	$margin_bottom_value = elodin_bridge_build_css_value( $preset[ $margin_bottom_key ] ?? '', $preset['marginBottomUnit'] ?? 'px' );

	if ( '' !== $size_value ) {
		$declarations['font-size'] = $size_value;
	}

	if ( '' !== $line_height_value ) {
		$declarations['line-height'] = $line_height_value;
	}

	if ( '' !== $letter_spacing_value ) {
		$declarations['letter-spacing'] = $letter_spacing_value;
	}

	if ( '' !== $margin_bottom_value ) {
		$declarations['margin-bottom'] = $margin_bottom_value;
	}

	return $declarations;
}

/**
 * Build a CSS rule from declarations.
 *
 * @param string               $selector     CSS selector.
 * @param array<string,string> $declarations CSS declarations.
 * @return string
 */
function elodin_bridge_build_css_rule( $selector, $declarations ) {
	if ( empty( $declarations ) ) {
		return '';
	}

	$rule = $selector . '{';
	foreach ( $declarations as $property => $value ) {
		$is_forceful_property = 'margin-bottom' !== $property;
		$rule .= $property . ':' . $value . ( $is_forceful_property ? '!important' : '' ) . ';';
	}
	$rule .= '}';

	return $rule;
}

/**
 * Build responsive heading/paragraph class override CSS from GeneratePress typography settings.
 *
 * @return string
 */
function elodin_bridge_build_typography_override_css() {
	$presets = elodin_bridge_get_dynamic_typography_presets();
	$source_selector = ':is(p,h1,h2,h3,h4,h5,h6)';
	$margin_top_zero_selector = ':is(p,h1,h2,h3,h4).elodin-mt,:is(p,h1,h2,h3,h4).elodin-mt-0';
	$margin_top_small_selector = ':is(p,h1,h2,h3,h4).elodin-mt-s';
	$margin_top_medium_selector = ':is(p,h1,h2,h3,h4).elodin-mt-m';
	$css = '.h1,.h2,.h3,.h4{margin-top:0;}';
	$tablet_css = '';
	$mobile_css = '';

	if ( ! empty( $presets ) ) {
		foreach ( $presets as $class_name => $preset ) {
			$selector = $source_selector . '.' . $class_name;
			$css .= elodin_bridge_build_css_rule( $selector, elodin_bridge_build_typography_declarations( $preset, 'desktop' ) );
			$tablet_css .= elodin_bridge_build_css_rule( $selector, elodin_bridge_build_typography_declarations( $preset, 'tablet' ) );
			$mobile_css .= elodin_bridge_build_css_rule( $selector, elodin_bridge_build_typography_declarations( $preset, 'mobile' ) );
		}
	}

	$css .= $margin_top_zero_selector . '{margin-top:0!important;}';
	$css .= $margin_top_small_selector . '{margin-top:var( --space-s )!important;}';
	$css .= $margin_top_medium_selector . '{margin-top:var( --space-m )!important;}';

	if ( '' !== $tablet_css ) {
		$tablet_query = function_exists( 'generate_get_media_query' ) ? generate_get_media_query( 'tablet' ) : '(max-width: 1024px)';
		$css .= '@media ' . $tablet_query . '{' . $tablet_css . '}';
	}

	if ( '' !== $mobile_css ) {
		$mobile_query = function_exists( 'generate_get_media_query' ) ? generate_get_media_query( 'mobile' ) : '(max-width:768px)';
		$css .= '@media ' . $mobile_query . '{' . $mobile_css . '}';
	}

	return $css;
}

/**
 * Enqueue typography override styles for front-end and block editor content.
 */
function elodin_bridge_enqueue_typography_override_styles() {
	if ( ! elodin_bridge_is_heading_paragraph_overrides_enabled() ) {
		return;
	}

	$css = elodin_bridge_build_typography_override_css();
	if ( '' === $css ) {
		return;
	}

	$handle = 'elodin-bridge-typography-overrides';
	wp_register_style( $handle, false, array(), ELODIN_BRIDGE_VERSION );
	wp_enqueue_style( $handle );
	wp_add_inline_style( $handle, $css );
}
add_action( 'enqueue_block_assets', 'elodin_bridge_enqueue_typography_override_styles' );

/**
 * Enqueue heading/paragraph override controls in the block toolbar.
 */
function elodin_bridge_enqueue_editor_heading_paragraph_overrides_toolbar() {
	if ( ! elodin_bridge_is_heading_paragraph_overrides_enabled() ) {
		return;
	}

	$script_path = ELODIN_BRIDGE_DIR . '/assets/editor-heading-paragraph-overrides.js';
	$script_url = ELODIN_BRIDGE_URL . 'assets/editor-heading-paragraph-overrides.js';
	$style_path = ELODIN_BRIDGE_DIR . '/assets/editor-heading-paragraph-overrides.css';
	$style_url = ELODIN_BRIDGE_URL . 'assets/editor-heading-paragraph-overrides.css';

	if ( ! file_exists( $script_path ) ) {
		return;
	}

	wp_enqueue_script(
		'elodin-bridge-editor-heading-paragraph-overrides',
		$script_url,
		array( 'wp-block-editor', 'wp-components', 'wp-compose', 'wp-data', 'wp-element', 'wp-hooks', 'wp-i18n' ),
		(string) filemtime( $script_path ),
		true
	);
	wp_add_inline_script(
		'elodin-bridge-editor-heading-paragraph-overrides',
		'window.elodinBridgeTypographyToolbar = ' . wp_json_encode(
			array(
				'enableTypeOverrides' => elodin_bridge_is_heading_paragraph_overrides_enabled(),
				'enableBalancedText'  => elodin_bridge_is_balanced_text_enabled(),
			)
		) . ';',
		'before'
	);

	if ( file_exists( $style_path ) ) {
		wp_enqueue_style(
			'elodin-bridge-editor-heading-paragraph-overrides',
			$style_url,
			array(),
			(string) filemtime( $style_path )
		);
	}
}
add_action( 'enqueue_block_editor_assets', 'elodin_bridge_enqueue_editor_heading_paragraph_overrides_toolbar' );

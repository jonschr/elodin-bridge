<?php
/*
	Plugin Name: Elodin Bridge
	Plugin URI: https://elod.in
    Description: Just another plugin
	Version: 0.1
    Author: Jon Schroeder
    Author URI: https://elod.in

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
    GNU General Public License for more details.
*/


/* Prevent direct access to the plugin */
if ( !defined( 'ABSPATH' ) ) {
    die( "Sorry, you are not allowed to access this page directly." );
}

// Plugin directory
define( 'ELODIN_BRIDGE_DIR', dirname( __FILE__ ) );

// Define the version of the plugin
define ( 'ELODIN_BRIDGE_VERSION', '0.1' );

// Option keys
define( 'ELODIN_BRIDGE_OPTION_ENABLE_HEADING_PARAGRAPH_OVERRIDES', 'elodin_bridge_enable_heading_paragraph_overrides' );
define( 'ELODIN_BRIDGE_TYPOGRAPHY_RESET', '__elodin_bridge_typography_reset__' );

/**
 * Sanitize a yes/no toggle value.
 *
 * @param mixed $value Raw setting value.
 * @return int
 */
function elodin_bridge_sanitize_toggle( $value ) {
	return ! empty( $value ) ? 1 : 0;
}

/**
 * Check if heading/paragraph style overrides are enabled.
 *
 * @return bool
 */
function elodin_bridge_is_heading_paragraph_overrides_enabled() {
	return (bool) get_option( ELODIN_BRIDGE_OPTION_ENABLE_HEADING_PARAGRAPH_OVERRIDES, 1 );
}

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
 * Register plugin settings.
 */
function elodin_bridge_register_settings() {
	register_setting(
		'elodin_bridge_settings',
		ELODIN_BRIDGE_OPTION_ENABLE_HEADING_PARAGRAPH_OVERRIDES,
		array(
			'type' => 'boolean',
			'sanitize_callback' => 'elodin_bridge_sanitize_toggle',
			'default' => 1,
		)
	);
}
add_action( 'admin_init', 'elodin_bridge_register_settings' );

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
 * @param string              $selector     CSS selector.
 * @param array<string,string> $declarations CSS declarations.
 * @return string
 */
function elodin_bridge_build_css_rule( $selector, $declarations ) {
	if ( empty( $declarations ) ) {
		return '';
	}

	$rule = $selector . '{';
	foreach ( $declarations as $property => $value ) {
		$rule .= $property . ':' . $value . '!important;';
	}
	$rule .= '}';

	return $rule;
}

/**
 * Build responsive class override CSS from GeneratePress dynamic typography settings.
 *
 * @return string
 */
function elodin_bridge_build_typography_override_css() {
	$presets = elodin_bridge_get_dynamic_typography_presets();
	if ( empty( $presets ) ) {
		return '';
	}

	$css = '';
	$tablet_css = '';
	$mobile_css = '';
	$source_selector = ':is(p,h1,h2,h3,h4,h5,h6)';

	foreach ( $presets as $class_name => $preset ) {
		$selector = $source_selector . '.' . $class_name;
		$css .= elodin_bridge_build_css_rule( $selector, elodin_bridge_build_typography_declarations( $preset, 'desktop' ) );
		$tablet_css .= elodin_bridge_build_css_rule( $selector, elodin_bridge_build_typography_declarations( $preset, 'tablet' ) );
		$mobile_css .= elodin_bridge_build_css_rule( $selector, elodin_bridge_build_typography_declarations( $preset, 'mobile' ) );
	}

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
 * Enqueue block editor toolbar controls for typography class toggles.
 */
function elodin_bridge_enqueue_editor_typography_toolbar() {
	if ( ! elodin_bridge_is_heading_paragraph_overrides_enabled() ) {
		return;
	}

	$script_path = ELODIN_BRIDGE_DIR . '/assets/editor-typography-toolbar.js';
	$script_url = plugins_url( 'assets/editor-typography-toolbar.js', __FILE__ );

	if ( ! file_exists( $script_path ) ) {
		return;
	}

	wp_enqueue_script(
		'elodin-bridge-editor-typography-toolbar',
		$script_url,
		array( 'wp-block-editor', 'wp-components', 'wp-compose', 'wp-data', 'wp-element', 'wp-hooks', 'wp-i18n' ),
		(string) filemtime( $script_path ),
		true
	);
}
add_action( 'enqueue_block_editor_assets', 'elodin_bridge_enqueue_editor_typography_toolbar' );

/**
 * Enqueue admin styles for the Bridge settings page.
 *
 * @param string $hook_suffix Current admin page hook suffix.
 */
function elodin_bridge_enqueue_admin_assets( $hook_suffix ) {
	if ( 'appearance_page_elodin-bridge' !== $hook_suffix ) {
		return;
	}

	$handle = 'elodin-bridge-admin';
	$css = '
	.elodin-bridge-admin {
		max-width: 860px;
	}
	.elodin-bridge-admin__intro {
		color: #50575e;
		margin: 8px 0 18px;
	}
	.elodin-bridge-admin__card {
		background: #fff;
		border: 1px solid #dcdcde;
		border-radius: 12px;
		padding: 24px;
		box-shadow: 0 1px 2px rgba(0,0,0,0.04);
	}
	.elodin-bridge-admin__toggle {
		display: flex;
		align-items: center;
		gap: 12px;
		font-size: 15px;
		font-weight: 600;
		line-height: 1.4;
		color: #1d2327;
		cursor: pointer;
	}
	.elodin-bridge-admin__toggle-input {
		position: absolute;
		opacity: 0;
		pointer-events: none;
	}
	.elodin-bridge-admin__toggle-track {
		width: 40px;
		height: 22px;
		border-radius: 999px;
		background: #8c8f94;
		border: 1px solid #8c8f94;
		display: inline-flex;
		align-items: center;
		padding: 2px;
		transition: background-color 0.2s ease, border-color 0.2s ease;
		flex-shrink: 0;
	}
	.elodin-bridge-admin__toggle-thumb {
		width: 16px;
		height: 16px;
		border-radius: 50%;
		background: #fff;
		box-shadow: 0 1px 2px rgba(0,0,0,0.2);
		transition: transform 0.2s ease;
	}
	.elodin-bridge-admin__toggle-input:checked + .elodin-bridge-admin__toggle-track {
		background: #2271b1;
		border-color: #2271b1;
	}
	.elodin-bridge-admin__toggle-input:checked + .elodin-bridge-admin__toggle-track .elodin-bridge-admin__toggle-thumb {
		transform: translateX(18px);
	}
	.elodin-bridge-admin__toggle-input:focus-visible + .elodin-bridge-admin__toggle-track {
		box-shadow: 0 0 0 2px #72aee6;
	}
	.elodin-bridge-admin__description {
		margin: 10px 0 0 52px;
		color: #50575e;
	}
	.elodin-bridge-admin__actions {
		margin-top: 16px;
	}
	';

	wp_register_style( $handle, false, array(), ELODIN_BRIDGE_VERSION );
	wp_enqueue_style( $handle );
	wp_add_inline_style( $handle, $css );
}
add_action( 'admin_enqueue_scripts', 'elodin_bridge_enqueue_admin_assets' );

/**
 * Render the Bridge admin page under Appearance.
 */
function elodin_bridge_render_admin_page() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	$enabled = elodin_bridge_is_heading_paragraph_overrides_enabled();
	?>
	<div class="wrap elodin-bridge-admin">
		<h1><?php echo esc_html__( 'Bridge', 'elodin-bridge' ); ?></h1>
		<p class="elodin-bridge-admin__intro">
			<?php esc_html_e( 'Configure Bridge features for the block editor and front end.', 'elodin-bridge' ); ?>
		</p>

		<form action="options.php" method="post" class="elodin-bridge-admin__form">
			<?php
			settings_fields( 'elodin_bridge_settings' );
			?>

			<div class="elodin-bridge-admin__card">
				<label class="elodin-bridge-admin__toggle" for="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_HEADING_PARAGRAPH_OVERRIDES ); ?>">
					<input
						type="hidden"
						name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_HEADING_PARAGRAPH_OVERRIDES ); ?>"
						value="0"
					/>
					<input
						type="checkbox"
						class="elodin-bridge-admin__toggle-input"
						id="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_HEADING_PARAGRAPH_OVERRIDES ); ?>"
						name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_HEADING_PARAGRAPH_OVERRIDES ); ?>"
						value="1"
						<?php checked( $enabled ); ?>
					/>
					<span class="elodin-bridge-admin__toggle-track" aria-hidden="true">
						<span class="elodin-bridge-admin__toggle-thumb"></span>
					</span>
					<span><?php esc_html_e( 'Enable heading and paragraph style overrides', 'elodin-bridge' ); ?></span>
				</label>

				<p class="elodin-bridge-admin__description">
					<?php esc_html_e( 'Adds block toolbar controls for paragraph/heading typography overrides and applies those override classes using your GeneratePress typography values (desktop, tablet, and mobile).', 'elodin-bridge' ); ?>
				</p>
			</div>

			<div class="elodin-bridge-admin__actions">
				<?php submit_button( __( 'Save Changes', 'elodin-bridge' ), 'primary', 'submit', false ); ?>
			</div>
		</form>
	</div>
	<?php
}

/**
 * Register the Bridge settings page in the Appearance menu.
 */
function elodin_bridge_register_admin_menu() {
	add_theme_page(
		__( 'Bridge', 'elodin-bridge' ),
		__( 'Bridge', 'elodin-bridge' ),
		'edit_theme_options',
		'elodin-bridge',
		'elodin_bridge_render_admin_page'
	);
}
add_action( 'admin_menu', 'elodin_bridge_register_admin_menu' );

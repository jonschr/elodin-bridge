<?php

/**
 * Preserve inline CSS already attached to the GeneratePress base style handle.
 *
 * @return array<int,string>
 */
function elodin_bridge_get_generatepress_inline_style_chunks() {
	global $wp_styles;

	if (
		! isset( $wp_styles ) ||
		! $wp_styles instanceof WP_Styles ||
		! isset( $wp_styles->registered['generate-style'] ) ||
		! $wp_styles->registered['generate-style'] instanceof _WP_Dependency
	) {
		return array();
	}

	$chunks = $wp_styles->registered['generate-style']->extra['after'] ?? array();
	if ( ! is_array( $chunks ) ) {
		return array();
	}

	return array_values(
		array_filter(
			$chunks,
			static function ( $chunk ) {
				return is_string( $chunk ) && '' !== trim( $chunk );
			}
		)
	);
}

/**
 * Re-register the GeneratePress base style handle as inline-only.
 *
 * @param array<int,string> $inline_chunks CSS chunks that were attached to generate-style.
 */
function elodin_bridge_replace_generatepress_base_style_handle( $inline_chunks ) {
	wp_dequeue_style( 'generate-style' );
	wp_deregister_style( 'generate-style' );

	wp_register_style( 'generate-style', false, array(), ELODIN_BRIDGE_VERSION );
	wp_enqueue_style( 'generate-style' );

	foreach ( $inline_chunks as $css ) {
		wp_add_inline_style( 'generate-style', $css );
	}
}

/**
 * Dequeue static GeneratePress styles while keeping dynamic inline CSS.
 */
function elodin_bridge_run_generatepress_static_css_experiment() {
	if ( is_admin() ) {
		return;
	}

	$enabled = elodin_bridge_is_generatepress_static_css_experiment_enabled();
	$enabled = (bool) apply_filters( 'elodin_bridge_enable_generatepress_static_css_experiment', $enabled );
	$legacy_enabled = apply_filters( 'elodin_bridge_disable_generatepress_static_css_experiment', null );
	if ( is_bool( $legacy_enabled ) ) {
		$enabled = $legacy_enabled;
	}

	if ( ! $enabled ) {
		return;
	}

	$inline_chunks = elodin_bridge_get_generatepress_inline_style_chunks();
	if ( ! empty( $inline_chunks ) || wp_style_is( 'generate-style', 'registered' ) || wp_style_is( 'generate-style', 'enqueued' ) ) {
		elodin_bridge_replace_generatepress_base_style_handle( $inline_chunks );
		wp_add_inline_style( 'generate-style', '.site.grid-container { max-width: 100% }' );
	}

	$static_handles = array(
		'generate-style-grid',
		'generate-mobile-style',
		'generate-rtl',
		'generate-widget-areas',
		'generate-comments',
		'generate-font-icons',
		'font-awesome',
	);

	foreach ( $static_handles as $handle ) {
		wp_dequeue_style( $handle );
		wp_deregister_style( $handle );
	}
}
add_action( 'wp_enqueue_scripts', 'elodin_bridge_run_generatepress_static_css_experiment', 220 );

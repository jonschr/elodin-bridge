<?php

/**
 * Check whether GenerateBlocks is available.
 *
 * @return bool
 */
function elodin_bridge_is_generateblocks_available() {
	return defined( 'GENERATEBLOCKS_VERSION' );
}

/**
 * Apply custom GenerateBlocks container layout gap defaults.
 *
 * @param mixed $defaults Existing GenerateBlocks defaults.
 * @return mixed
 */
function elodin_bridge_apply_generateblocks_layout_gap_defaults( $defaults ) {
	if ( ! is_array( $defaults ) ) {
		return $defaults;
	}

	if ( ! elodin_bridge_is_generateblocks_layout_gap_defaults_enabled() ) {
		return $defaults;
	}

	$settings = elodin_bridge_get_generateblocks_layout_gap_defaults_settings();
	if ( ! isset( $defaults['container'] ) || ! is_array( $defaults['container'] ) ) {
		$defaults['container'] = array();
	}

	$defaults['container']['columnGap'] = (string) ( $settings['column_gap_desktop'] ?? 'var( --space-xl )' );
	$defaults['container']['rowGap'] = (string) ( $settings['row_gap_desktop'] ?? 'var( --space-m )' );
	$defaults['container']['columnGapTablet'] = (string) ( $settings['column_gap_tablet'] ?? 'var( --space-xl )' );
	$defaults['container']['rowGapTablet'] = (string) ( $settings['row_gap_tablet'] ?? 'var( --space-m )' );
	$defaults['container']['columnGapMobile'] = (string) ( $settings['column_gap_mobile'] ?? 'var( --space-xl )' );
	$defaults['container']['rowGapMobile'] = (string) ( $settings['row_gap_mobile'] ?? 'var( --space-m )' );

	return $defaults;
}
add_filter( 'generateblocks_defaults', 'elodin_bridge_apply_generateblocks_layout_gap_defaults', 20 );

/**
 * Enqueue editor-side variation overrides for GenerateBlocks v2 element/grid blocks.
 */
function elodin_bridge_enqueue_generateblocks_layout_gap_default_variation_overrides() {
	if ( ! elodin_bridge_is_generateblocks_available() ) {
		return;
	}

	if ( ! elodin_bridge_is_generateblocks_layout_gap_defaults_enabled() ) {
		return;
	}

	$script_path = ELODIN_BRIDGE_DIR . '/assets/editor-generateblocks-layout-gap-defaults.js';
	$script_url = ELODIN_BRIDGE_URL . 'assets/editor-generateblocks-layout-gap-defaults.js';
	if ( ! file_exists( $script_path ) ) {
		return;
	}

	$settings = elodin_bridge_get_generateblocks_layout_gap_defaults_settings();
	$config = array(
		'enabled'          => ! empty( $settings['enabled'] ),
		'columnGapDesktop' => (string) ( $settings['column_gap_desktop'] ?? 'var( --space-xl )' ),
		'rowGapDesktop'    => (string) ( $settings['row_gap_desktop'] ?? 'var( --space-m )' ),
		'columnGapTablet'  => (string) ( $settings['column_gap_tablet'] ?? 'var( --space-xl )' ),
		'rowGapTablet'     => (string) ( $settings['row_gap_tablet'] ?? 'var( --space-m )' ),
		'columnGapMobile'  => (string) ( $settings['column_gap_mobile'] ?? 'var( --space-xl )' ),
		'rowGapMobile'     => (string) ( $settings['row_gap_mobile'] ?? 'var( --space-m )' ),
	);

	$deps = array( 'wp-blocks', 'wp-dom-ready' );
	if ( wp_script_is( 'generateblocks-editor', 'registered' ) || wp_script_is( 'generateblocks-editor', 'enqueued' ) ) {
		$deps[] = 'generateblocks-editor';
	}

	wp_enqueue_script(
		'elodin-bridge-editor-generateblocks-layout-gap-defaults',
		$script_url,
		$deps,
		(string) filemtime( $script_path ),
		true
	);

	wp_add_inline_script(
		'elodin-bridge-editor-generateblocks-layout-gap-defaults',
		'window.elodinBridgeGenerateBlocksLayoutGapDefaults = ' . wp_json_encode( $config ) . ';',
		'before'
	);
}
add_action( 'enqueue_block_editor_assets', 'elodin_bridge_enqueue_generateblocks_layout_gap_default_variation_overrides' );

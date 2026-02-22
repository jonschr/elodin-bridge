<?php

/**
 * Remove theme-level editor callbacks so Bridge controls editor behavior.
 */
function elodin_bridge_detach_theme_editor_callbacks() {
	if ( ! elodin_bridge_is_editor_ui_restrictions_enabled() ) {
		return;
	}

	remove_action( 'enqueue_block_editor_assets', 'elodin_disable_fullscreen_mode' );
}
add_action( 'after_setup_theme', 'elodin_bridge_detach_theme_editor_callbacks', 100 );

/**
 * Enqueue inline JS that disables fullscreen mode and the publish sidebar.
 */
function elodin_bridge_enqueue_editor_ui_restrictions() {
	$disable_fullscreen = elodin_bridge_is_editor_ui_restrictions_enabled();
	$disable_publish_sidebar = elodin_bridge_is_editor_publish_sidebar_restriction_enabled();
	if ( ! $disable_fullscreen && ! $disable_publish_sidebar ) {
		return;
	}

	$handle = 'elodin-bridge-editor-ui-restrictions';
	wp_register_script(
		$handle,
		false,
		array( 'wp-data', 'wp-dom-ready' ),
		ELODIN_BRIDGE_VERSION,
		true
	);
	wp_enqueue_script( $handle );
	wp_add_inline_script(
		$handle,
		'window.elodinBridgeEditorUiRestrictions = ' . wp_json_encode(
			array(
				'disableFullscreen'     => $disable_fullscreen,
				'disablePublishSidebar' => $disable_publish_sidebar,
			)
		) . ';',
		'before'
	);

	wp_add_inline_script(
		$handle,
		'( function( wp ) {
				if ( ! wp || ! wp.data || ! wp.domReady ) {
					return;
				}

				const config = window.elodinBridgeEditorUiRestrictions || {};

				wp.domReady( function() {
					if ( config.disableFullscreen ) {
						const dispatchPreferences = wp.data.dispatch( "core/preferences" );
						if ( dispatchPreferences && typeof dispatchPreferences.set === "function" ) {
							dispatchPreferences.set( "core", "fullscreenMode", false );
							dispatchPreferences.set( "core/edit-post", "fullscreenMode", false );
						}
					}

					if ( config.disablePublishSidebar ) {
						const dispatchEditor = wp.data.dispatch( "core/editor" );
						if ( dispatchEditor && typeof dispatchEditor.disablePublishSidebar === "function" ) {
							dispatchEditor.disablePublishSidebar();
						}
					}
				} );
			} )( window.wp );'
	);
}
add_action( 'enqueue_block_editor_assets', 'elodin_bridge_enqueue_editor_ui_restrictions' );

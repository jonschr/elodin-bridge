<?php

/**
 * Remove theme-level editor callbacks so Bridge controls editor behavior.
 */
function elodin_bridge_detach_theme_editor_callbacks() {
	remove_action( 'enqueue_block_editor_assets', 'elodin_disable_fullscreen_mode' );
}
add_action( 'after_setup_theme', 'elodin_bridge_detach_theme_editor_callbacks', 100 );

/**
 * Enqueue inline JS that disables fullscreen mode and the publish sidebar.
 */
function elodin_bridge_enqueue_editor_ui_restrictions() {
	if ( ! elodin_bridge_is_editor_ui_restrictions_enabled() ) {
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
		'( function( wp ) {
			if ( ! wp || ! wp.data || ! wp.domReady ) {
				return;
			}

			wp.domReady( function() {
				const selectEditPost = wp.data.select( "core/edit-post" );
				const dispatchEditPost = wp.data.dispatch( "core/edit-post" );

				if (
					selectEditPost &&
					dispatchEditPost &&
					typeof selectEditPost.isFeatureActive === "function" &&
					typeof dispatchEditPost.toggleFeature === "function" &&
					selectEditPost.isFeatureActive( "fullscreenMode" )
				) {
					dispatchEditPost.toggleFeature( "fullscreenMode" );
				}

				const dispatchEditor = wp.data.dispatch( "core/editor" );
				if ( dispatchEditor && typeof dispatchEditor.disablePublishSidebar === "function" ) {
					dispatchEditor.disablePublishSidebar();
				}
			} );
		} )( window.wp );'
	);
}
add_action( 'enqueue_block_editor_assets', 'elodin_bridge_enqueue_editor_ui_restrictions' );

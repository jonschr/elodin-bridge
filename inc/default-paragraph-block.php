<?php

/**
 * Enqueue editor script to set Paragraph as the default inserted block.
 */
function elodin_bridge_enqueue_editor_default_paragraph_block() {
	if ( ! elodin_bridge_is_default_paragraph_block_enabled() ) {
		return;
	}

	$handle = 'elodin-bridge-editor-default-paragraph-block';
	wp_register_script(
		$handle,
		false,
		array( 'wp-blocks', 'wp-dom-ready' ),
		ELODIN_BRIDGE_VERSION,
		true
	);
	wp_enqueue_script( $handle );
	wp_add_inline_script(
		$handle,
		"wp.domReady(function(){if(window.wp&&window.wp.blocks&&window.wp.blocks.setDefaultBlockName){window.wp.blocks.setDefaultBlockName('core/paragraph');}});"
	);
}
add_action( 'enqueue_block_editor_assets', 'elodin_bridge_enqueue_editor_default_paragraph_block' );

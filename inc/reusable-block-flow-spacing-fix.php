<?php

/**
 * Build reusable block flow spacing fix CSS.
 *
 * @return string
 */
function elodin_bridge_build_reusable_block_flow_spacing_fix_css() {
	if ( ! elodin_bridge_is_reusable_block_flow_spacing_fix_enabled() ) {
		return '';
	}

	return ':root :where(.editor-styles-wrapper) :where(.is-layout-flow) > *{margin-block-start:0;margin-block-end:0;}';
}

/**
 * Inject reusable block flow spacing fix styles into editor iframe settings.
 *
 * @param array<string,mixed> $settings Block editor settings.
 * @param mixed               $editor_context Block editor context.
 * @return array<string,mixed>
 */
function elodin_bridge_inject_reusable_block_flow_spacing_fix_styles_into_editor_settings( $settings, $editor_context ) {
	$context_name = '';
	if ( is_object( $editor_context ) && isset( $editor_context->name ) ) {
		$context_name = (string) $editor_context->name;
	}

	if ( ! in_array( $context_name, array( 'core/edit-post', 'core/edit-site' ), true ) ) {
		return $settings;
	}

	$css = elodin_bridge_build_reusable_block_flow_spacing_fix_css();
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
add_filter( 'block_editor_settings_all', 'elodin_bridge_inject_reusable_block_flow_spacing_fix_styles_into_editor_settings', 120, 2 );

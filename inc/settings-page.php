<?php

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
 * Sanitize a single CSS length/expression field used in settings.
 *
 * @param mixed  $value    Raw setting value.
 * @param string $fallback Fallback value.
 * @return string
 */
function elodin_bridge_sanitize_css_value( $value, $fallback = '' ) {
	$value = trim( wp_strip_all_tags( (string) $value ) );
	if ( '' === $value ) {
		return $fallback;
	}

	// Prevent malformed payloads while allowing values like 4em, var(--space), and clamp(...).
	if ( false !== strpbrk( $value, ';{}\\' ) ) {
		return $fallback;
	}

	if ( ! preg_match( '/^[a-zA-Z0-9%().,_+*\/\-\s]+$/', $value ) ) {
		return $fallback;
	}

	return preg_replace( '/\s+/', ' ', $value );
}

/**
 * Check whether GeneratePress is the active theme or parent theme.
 *
 * @return bool
 */
function elodin_bridge_is_generatepress_parent_theme() {
	$theme = wp_get_theme();
	if ( ! $theme instanceof WP_Theme ) {
		return false;
	}

	$theme_name = strtolower( (string) $theme->get( 'Name' ) );
	$template_slug = strtolower( (string) $theme->get_template() );

	return ( 'generatepress' === $theme_name || 'generatepress' === $template_slug );
}

/**
 * Sanitize heading/paragraph override toggle with theme requirement.
 *
 * @param mixed $value Raw setting value.
 * @return int
 */
function elodin_bridge_sanitize_heading_paragraph_overrides_toggle( $value ) {
	if ( ! elodin_bridge_is_generatepress_parent_theme() ) {
		return 0;
	}

	return elodin_bridge_sanitize_toggle( $value );
}

/**
 * Seed image sizes that Bridge pre-populates.
 *
 * @return array<int,array<string,mixed>>
 */
function elodin_bridge_get_seed_image_sizes() {
	return array(
		array(
			'slug'    => 'square',
			'label'   => __( 'Square', 'elodin-bridge' ),
			'width'   => 500,
			'height'  => 500,
			'crop'    => 1,
			'gallery' => 1,
		),
	);
}

/**
 * Default values for Bridge image size settings.
 *
 * @return array{enabled:int,sizes:array<int,array<string,mixed>>}
 */
function elodin_bridge_get_image_sizes_defaults() {
	return array(
		'enabled' => 0,
		'sizes'   => elodin_bridge_get_seed_image_sizes(),
	);
}

/**
 * Sanitize one custom image size row.
 *
 * @param mixed                    $row           Raw row value.
 * @param array<string,bool>       $blocked_slugs Slugs that are already used.
 * @return array<string,mixed>
 */
function elodin_bridge_sanitize_image_size_row( $row, $blocked_slugs = array() ) {
	if ( ! is_array( $row ) ) {
		return array();
	}

	$slug = sanitize_key( $row['slug'] ?? '' );
	if ( '' === $slug || isset( $blocked_slugs[ $slug ] ) ) {
		return array();
	}

	$width = absint( $row['width'] ?? 0 );
	$height = absint( $row['height'] ?? 0 );
	if ( $width < 1 || $height < 1 ) {
		return array();
	}

	$label = sanitize_text_field( $row['label'] ?? '' );
	if ( '' === $label ) {
		$label = ucwords( str_replace( array( '-', '_' ), ' ', $slug ) );
	}

	return array(
		'slug'    => $slug,
		'label'   => $label,
		'width'   => $width,
		'height'  => $height,
		'crop'    => elodin_bridge_sanitize_toggle( $row['crop'] ?? 0 ),
		'gallery' => elodin_bridge_sanitize_toggle( $row['gallery'] ?? 0 ),
	);
}

/**
 * Sanitize Bridge image size settings.
 *
 * @param mixed $value Raw setting value.
 * @return array<int,array<string,mixed>>
 */
function elodin_bridge_get_legacy_image_size_rows( $value ) {
	$value = is_array( $value ) ? $value : array();
	$legacy_custom_sizes = isset( $value['custom_sizes'] ) && is_array( $value['custom_sizes'] ) ? $value['custom_sizes'] : array();
	$legacy_builtin_gallery = isset( $value['builtin_gallery'] ) && is_array( $value['builtin_gallery'] ) ? $value['builtin_gallery'] : array();
	$rows = elodin_bridge_get_seed_image_sizes();

	foreach ( $rows as $index => $row ) {
		$slug = $row['slug'] ?? '';
		if ( '' === $slug || ! array_key_exists( $slug, $legacy_builtin_gallery ) ) {
			continue;
		}

		$rows[ $index ]['gallery'] = elodin_bridge_sanitize_toggle( $legacy_builtin_gallery[ $slug ] );
	}

	foreach ( $legacy_custom_sizes as $row ) {
		$rows[] = $row;
	}

	return $rows;
}

/**
 * Sanitize Bridge image size settings.
 *
 * @param mixed $value Raw setting value.
 * @return array{enabled:int,sizes:array<int,array<string,mixed>>}
 */
function elodin_bridge_sanitize_image_sizes_settings( $value ) {
	$defaults = elodin_bridge_get_image_sizes_defaults();
	$value = is_array( $value ) ? $value : array();
	$enabled = elodin_bridge_sanitize_toggle( $value['enabled'] ?? $defaults['enabled'] );
	$has_sizes_payload = array_key_exists( 'sizes', $value ) && is_array( $value['sizes'] );
	$raw_sizes = $has_sizes_payload ? $value['sizes'] : array();

	// If the feature is being disabled and row inputs are not present in the request,
	// keep previously saved rows instead of wiping them out.
	if ( ! $has_sizes_payload && 0 === $enabled ) {
		$existing_settings = get_option( ELODIN_BRIDGE_OPTION_IMAGE_SIZES, array() );
		if ( is_array( $existing_settings ) && isset( $existing_settings['sizes'] ) && is_array( $existing_settings['sizes'] ) ) {
			$raw_sizes = $existing_settings['sizes'];
		}
	}

	if ( empty( $raw_sizes ) && ( isset( $value['custom_sizes'] ) || isset( $value['builtin_gallery'] ) ) ) {
		$raw_sizes = elodin_bridge_get_legacy_image_size_rows( $value );
	}

	$reserved_core_slugs = array(
		'thumbnail',
		'medium',
		'medium_large',
		'large',
		'post-thumbnail',
		'1536x1536',
		'2048x2048',
	);
	$blocked_slugs = array_fill_keys( $reserved_core_slugs, true );
	$sizes = array();
	foreach ( $raw_sizes as $raw_size ) {
		$size = elodin_bridge_sanitize_image_size_row( $raw_size, $blocked_slugs );
		if ( empty( $size ) ) {
			continue;
		}

		$sizes[] = $size;
		$blocked_slugs[ $size['slug'] ] = true;
	}

	return array(
		'enabled' => $enabled,
		'sizes'   => $sizes,
	);
}

/**
 * Get normalized Bridge image size settings.
 *
 * @return array{enabled:int,sizes:array<int,array<string,mixed>>}
 */
function elodin_bridge_get_image_sizes_settings() {
	$saved = get_option( ELODIN_BRIDGE_OPTION_IMAGE_SIZES, null );
	if ( null === $saved || false === $saved ) {
		return elodin_bridge_get_image_sizes_defaults();
	}

	return elodin_bridge_sanitize_image_sizes_settings( $saved );
}

/**
 * Check if Bridge image sizes are enabled.
 *
 * @return bool
 */
function elodin_bridge_is_image_sizes_enabled() {
	$settings = elodin_bridge_get_image_sizes_settings();
	return ! empty( $settings['enabled'] );
}

/**
 * Get all Bridge image sizes with gallery flags.
 *
 * @return array<string,array<string,mixed>>
 */
function elodin_bridge_get_registered_bridge_image_sizes() {
	$settings = elodin_bridge_get_image_sizes_settings();
	$sizes = array();

	foreach ( $settings['sizes'] as $size ) {
		if ( empty( $size['slug'] ) ) {
			continue;
		}

		$sizes[ $size['slug'] ] = $size;
	}

	return $sizes;
}

/**
 * Default values for first/last block body class settings.
 *
 * @return array{enabled:int,enable_first:int,enable_last:int,enable_debug:int,section_blocks:array<int,string>}
 */
function elodin_bridge_get_block_edge_class_defaults() {
	return array(
		'enabled'        => 0,
		'enable_first'   => 1,
		'enable_last'    => 1,
		'enable_debug'   => 0,
		'section_blocks' => array(
			'core/cover',
			'core/block',
			'generateblocks/element',
		),
	);
}

/**
 * Normalize a block-name list from textarea/array input.
 *
 * @param mixed $value Raw block list value.
 * @return array<int,string>
 */
function elodin_bridge_sanitize_block_name_list( $value ) {
	$items = array();
	$seen = array();
	$raw_items = is_array( $value ) ? $value : preg_split( '/[\r\n,]+/', (string) $value );
	if ( ! is_array( $raw_items ) ) {
		return $items;
	}

	foreach ( $raw_items as $item ) {
		$item = strtolower( trim( (string) $item ) );
		if ( '' === $item ) {
			continue;
		}

		$item = preg_replace( '/[^a-z0-9_\/-]+/', '', $item );
		$item = trim( (string) $item, "-/\t\n\r\0\x0B" );
		if ( '' === $item || isset( $seen[ $item ] ) ) {
			continue;
		}

		$items[] = $item;
		$seen[ $item ] = true;
	}

	return $items;
}

/**
 * Sanitize first/last block body class settings.
 *
 * @param mixed $value Raw setting value.
 * @return array{enabled:int,enable_first:int,enable_last:int,enable_debug:int,section_blocks:array<int,string>}
 */
function elodin_bridge_sanitize_block_edge_class_settings( $value ) {
	$defaults = elodin_bridge_get_block_edge_class_defaults();
	$value = is_array( $value ) ? $value : array();

	$section_blocks = array_key_exists( 'section_blocks', $value )
		? elodin_bridge_sanitize_block_name_list( $value['section_blocks'] )
		: $defaults['section_blocks'];

	return array(
		'enabled'        => elodin_bridge_sanitize_toggle( $value['enabled'] ?? $defaults['enabled'] ),
		'enable_first'   => elodin_bridge_sanitize_toggle( $value['enable_first'] ?? $defaults['enable_first'] ),
		'enable_last'    => elodin_bridge_sanitize_toggle( $value['enable_last'] ?? $defaults['enable_last'] ),
		'enable_debug'   => elodin_bridge_sanitize_toggle( $value['enable_debug'] ?? $defaults['enable_debug'] ),
		'section_blocks' => $section_blocks,
	);
}

/**
 * Get normalized first/last block body class settings.
 *
 * @return array{enabled:int,enable_first:int,enable_last:int,enable_debug:int,section_blocks:array<int,string>}
 */
function elodin_bridge_get_block_edge_class_settings() {
	$saved = get_option( ELODIN_BRIDGE_OPTION_BLOCK_EDGE_CLASSES, null );
	if ( null === $saved || false === $saved ) {
		return elodin_bridge_get_block_edge_class_defaults();
	}

	return elodin_bridge_sanitize_block_edge_class_settings( $saved );
}

/**
 * Check if first/last block body class feature is enabled.
 *
 * @return bool
 */
function elodin_bridge_is_block_edge_classes_enabled() {
	$settings = elodin_bridge_get_block_edge_class_settings();
	return ! empty( $settings['enabled'] );
}

/**
 * Return content types that should be configurable in Bridge settings.
 *
 * @return array<string,string>
 */
function elodin_bridge_get_content_type_behavior_post_types() {
	$excluded_post_types = array(
		'wp_navigation',
		'wp_block',
	);

	$post_types = get_post_types(
		array(
			'show_ui' => true,
		),
		'objects'
	);

	$public_post_types = array();
	$non_public_post_types = array();

	foreach ( $post_types as $post_type => $post_type_object ) {
		if ( in_array( $post_type, $excluded_post_types, true ) ) {
			continue;
		}

		if ( ! post_type_supports( $post_type, 'editor' ) ) {
			continue;
		}

		$label = $post_type;
		if ( ! empty( $post_type_object->labels->singular_name ) ) {
			$label = (string) $post_type_object->labels->singular_name;
		} elseif ( ! empty( $post_type_object->label ) ) {
			$label = (string) $post_type_object->label;
		}

		if ( ! empty( $post_type_object->public ) ) {
			$public_post_types[ $post_type ] = $label;
			continue;
		}

		$non_public_post_types[ $post_type ] = $label;
	}

	asort( $public_post_types, SORT_NATURAL | SORT_FLAG_CASE );
	asort( $non_public_post_types, SORT_NATURAL | SORT_FLAG_CASE );

	return $public_post_types + $non_public_post_types;
}

/**
 * Get default values for content type behavior settings.
 *
 * @return array{enabled:int,post_types:array<string,int>}
 */
function elodin_bridge_get_content_type_behavior_defaults() {
	$post_types = elodin_bridge_get_content_type_behavior_post_types();
	$defaults = array();

	foreach ( $post_types as $post_type => $label ) {
		$defaults[ $post_type ] = ( 'post' === $post_type ) ? 0 : 1;
	}

	return array(
		'enabled'    => 1,
		'post_types' => $defaults,
	);
}

/**
 * Sanitize content type behavior settings.
 *
 * @param mixed $value Raw setting value.
 * @return array{enabled:int,post_types:array<string,int>}
 */
function elodin_bridge_sanitize_content_type_behavior_settings( $value ) {
	$defaults = elodin_bridge_get_content_type_behavior_defaults();
	$value = is_array( $value ) ? $value : array();
	$raw_post_types = isset( $value['post_types'] ) && is_array( $value['post_types'] ) ? $value['post_types'] : array();
	$sanitized_post_types = array();

	foreach ( $defaults['post_types'] as $post_type => $default_mode ) {
		$sanitized_post_types[ $post_type ] = array_key_exists( $post_type, $raw_post_types )
			? elodin_bridge_sanitize_toggle( $raw_post_types[ $post_type ] )
			: (int) $default_mode;
	}

	return array(
		'enabled'    => elodin_bridge_sanitize_toggle( $value['enabled'] ?? 1 ),
		'post_types' => $sanitized_post_types,
	);
}

/**
 * Get normalized content type behavior settings.
 *
 * @return array{enabled:int,post_types:array<string,int>}
 */
function elodin_bridge_get_content_type_behavior_settings() {
	$defaults = elodin_bridge_get_content_type_behavior_defaults();
	$saved = get_option( ELODIN_BRIDGE_OPTION_CONTENT_TYPE_BEHAVIOR, array() );
	$saved = is_array( $saved ) ? $saved : array();
	$saved_post_types = isset( $saved['post_types'] ) && is_array( $saved['post_types'] ) ? $saved['post_types'] : array();
	$post_types = array();

	foreach ( $defaults['post_types'] as $post_type => $default_mode ) {
		$post_types[ $post_type ] = array_key_exists( $post_type, $saved_post_types )
			? elodin_bridge_sanitize_toggle( $saved_post_types[ $post_type ] )
			: (int) $default_mode;
	}

	return array(
		'enabled'    => array_key_exists( 'enabled', $saved )
			? elodin_bridge_sanitize_toggle( $saved['enabled'] )
			: (int) $defaults['enabled'],
		'post_types' => $post_types,
	);
}

/**
 * Get default values for automatic heading margin settings.
 *
 * @return array{enabled:int,desktop:string,tablet:string,mobile:string}
 */
function elodin_bridge_get_automatic_heading_margins_defaults() {
	return array(
		'enabled' => 1,
		'desktop' => '3em',
		'tablet'  => '2.5em',
		'mobile'  => '2em',
	);
}

/**
 * Sanitize automatic heading margin settings.
 *
 * @param mixed $value Raw setting value.
 * @return array{enabled:int,desktop:string,tablet:string,mobile:string}
 */
function elodin_bridge_sanitize_automatic_heading_margins_settings( $value ) {
	$defaults = elodin_bridge_get_automatic_heading_margins_defaults();
	$value = is_array( $value ) ? $value : array();

	return array(
		'enabled' => elodin_bridge_sanitize_toggle( $value['enabled'] ?? $defaults['enabled'] ),
		'desktop' => elodin_bridge_sanitize_css_value( $value['desktop'] ?? $defaults['desktop'], $defaults['desktop'] ),
		'tablet'  => elodin_bridge_sanitize_css_value( $value['tablet'] ?? $defaults['tablet'], $defaults['tablet'] ),
		'mobile'  => elodin_bridge_sanitize_css_value( $value['mobile'] ?? $defaults['mobile'], $defaults['mobile'] ),
	);
}

/**
 * Get normalized automatic heading margin settings.
 *
 * @return array{enabled:int,desktop:string,tablet:string,mobile:string}
 */
function elodin_bridge_get_automatic_heading_margins_settings() {
	$saved = get_option( ELODIN_BRIDGE_OPTION_AUTOMATIC_HEADING_MARGINS, null );
	if ( null === $saved || false === $saved ) {
		return elodin_bridge_get_automatic_heading_margins_defaults();
	}

	return elodin_bridge_sanitize_automatic_heading_margins_settings( $saved );
}

/**
 * Check if heading/paragraph style overrides are enabled.
 *
 * @return bool
 */
function elodin_bridge_is_heading_paragraph_overrides_enabled() {
	if ( ! elodin_bridge_is_generatepress_parent_theme() ) {
		return false;
	}

	return (bool) get_option( ELODIN_BRIDGE_OPTION_ENABLE_HEADING_PARAGRAPH_OVERRIDES, 1 );
}

/**
 * Check if balanced text toolbar feature is enabled.
 *
 * @return bool
 */
function elodin_bridge_is_balanced_text_enabled() {
	return (bool) get_option( ELODIN_BRIDGE_OPTION_ENABLE_BALANCED_TEXT, 1 );
}

/**
 * Check if automatic heading margins are enabled.
 *
 * @return bool
 */
function elodin_bridge_is_automatic_heading_margins_enabled() {
	$settings = elodin_bridge_get_automatic_heading_margins_settings();
	return ! empty( $settings['enabled'] );
}

/**
 * Check if editor UI restrictions are enabled.
 *
 * @return bool
 */
function elodin_bridge_is_editor_ui_restrictions_enabled() {
	return (bool) get_option( ELODIN_BRIDGE_OPTION_ENABLE_EDITOR_UI_RESTRICTIONS, 1 );
}

/**
 * Check if media library infinite scrolling is enabled.
 *
 * @return bool
 */
function elodin_bridge_is_media_library_infinite_scrolling_enabled() {
	return (bool) get_option( ELODIN_BRIDGE_OPTION_ENABLE_MEDIA_LIBRARY_INFINITE_SCROLLING, 1 );
}

/**
 * Check if Bridge shortcodes are enabled.
 *
 * @return bool
 */
function elodin_bridge_is_shortcodes_enabled() {
	return (bool) get_option( ELODIN_BRIDGE_OPTION_ENABLE_SHORTCODES, 1 );
}

/**
 * Check if GenerateBlocks boundary highlights are enabled.
 *
 * @return bool
 */
function elodin_bridge_is_generateblocks_boundary_highlights_enabled() {
	return (bool) get_option( ELODIN_BRIDGE_OPTION_ENABLE_GENERATEBLOCKS_BOUNDARY_HIGHLIGHTS, 1 );
}

/**
 * Check if prettier widgets styles are enabled.
 *
 * @return bool
 */
function elodin_bridge_is_prettier_widgets_enabled() {
	return (bool) get_option( ELODIN_BRIDGE_OPTION_ENABLE_PRETTIER_WIDGETS, 1 );
}

/**
 * Check if last-child margin reset styles are enabled.
 *
 * @return bool
 */
function elodin_bridge_is_last_child_margin_resets_enabled() {
	return (bool) get_option( ELODIN_BRIDGE_OPTION_ENABLE_LAST_CHILD_MARGIN_RESETS, 1 );
}

/**
 * Check if content type behavior mapping is enabled.
 *
 * @return bool
 */
function elodin_bridge_is_content_type_behavior_enabled() {
	$settings = elodin_bridge_get_content_type_behavior_settings();
	return ! empty( $settings['enabled'] );
}

/**
 * Check if a content type is configured as page-like.
 *
 * @param string $post_type Post type key.
 * @return bool
 */
function elodin_bridge_is_post_type_page_like( $post_type ) {
	if ( empty( $post_type ) || ! post_type_exists( $post_type ) ) {
		return false;
	}

	if ( ! elodin_bridge_is_content_type_behavior_enabled() ) {
		return is_post_type_hierarchical( $post_type );
	}

	$settings = elodin_bridge_get_content_type_behavior_settings();
	if ( isset( $settings['post_types'][ $post_type ] ) ) {
		return (bool) $settings['post_types'][ $post_type ];
	}

	return is_post_type_hierarchical( $post_type );
}

/**
 * Register plugin settings.
 */
function elodin_bridge_register_settings() {
	register_setting(
		'elodin_bridge_settings',
		ELODIN_BRIDGE_OPTION_ENABLE_HEADING_PARAGRAPH_OVERRIDES,
		array(
			'type'              => 'boolean',
			'sanitize_callback' => 'elodin_bridge_sanitize_heading_paragraph_overrides_toggle',
			'default'           => 1,
		)
	);

	register_setting(
		'elodin_bridge_settings',
		ELODIN_BRIDGE_OPTION_ENABLE_BALANCED_TEXT,
		array(
			'type'              => 'boolean',
			'sanitize_callback' => 'elodin_bridge_sanitize_toggle',
			'default'           => 1,
		)
	);

	register_setting(
		'elodin_bridge_settings',
		ELODIN_BRIDGE_OPTION_AUTOMATIC_HEADING_MARGINS,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'elodin_bridge_sanitize_automatic_heading_margins_settings',
			'default'           => elodin_bridge_get_automatic_heading_margins_defaults(),
		)
	);

	register_setting(
		'elodin_bridge_settings',
		ELODIN_BRIDGE_OPTION_CONTENT_TYPE_BEHAVIOR,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'elodin_bridge_sanitize_content_type_behavior_settings',
			'default'           => elodin_bridge_get_content_type_behavior_defaults(),
		)
	);

	register_setting(
		'elodin_bridge_settings',
		ELODIN_BRIDGE_OPTION_ENABLE_EDITOR_UI_RESTRICTIONS,
		array(
			'type'              => 'boolean',
			'sanitize_callback' => 'elodin_bridge_sanitize_toggle',
			'default'           => 1,
		)
	);

	register_setting(
		'elodin_bridge_settings',
		ELODIN_BRIDGE_OPTION_ENABLE_MEDIA_LIBRARY_INFINITE_SCROLLING,
		array(
			'type'              => 'boolean',
			'sanitize_callback' => 'elodin_bridge_sanitize_toggle',
			'default'           => 1,
		)
	);

	register_setting(
		'elodin_bridge_settings',
		ELODIN_BRIDGE_OPTION_ENABLE_SHORTCODES,
		array(
			'type'              => 'boolean',
			'sanitize_callback' => 'elodin_bridge_sanitize_toggle',
			'default'           => 1,
		)
	);

	register_setting(
		'elodin_bridge_settings',
		ELODIN_BRIDGE_OPTION_ENABLE_GENERATEBLOCKS_BOUNDARY_HIGHLIGHTS,
		array(
			'type'              => 'boolean',
			'sanitize_callback' => 'elodin_bridge_sanitize_toggle',
			'default'           => 1,
		)
	);

	register_setting(
		'elodin_bridge_settings',
		ELODIN_BRIDGE_OPTION_ENABLE_PRETTIER_WIDGETS,
		array(
			'type'              => 'boolean',
			'sanitize_callback' => 'elodin_bridge_sanitize_toggle',
			'default'           => 1,
		)
	);

	register_setting(
		'elodin_bridge_settings',
		ELODIN_BRIDGE_OPTION_ENABLE_LAST_CHILD_MARGIN_RESETS,
		array(
			'type'              => 'boolean',
			'sanitize_callback' => 'elodin_bridge_sanitize_toggle',
			'default'           => 1,
		)
	);

	register_setting(
		'elodin_bridge_settings',
		ELODIN_BRIDGE_OPTION_BLOCK_EDGE_CLASSES,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'elodin_bridge_sanitize_block_edge_class_settings',
			'default'           => elodin_bridge_get_block_edge_class_defaults(),
		)
	);

	register_setting(
		'elodin_bridge_settings',
		ELODIN_BRIDGE_OPTION_IMAGE_SIZES,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'elodin_bridge_sanitize_image_sizes_settings',
			'default'           => elodin_bridge_get_image_sizes_defaults(),
		)
	);
}
add_action( 'admin_init', 'elodin_bridge_register_settings' );

/**
 * Enqueue admin styles for the Bridge settings page.
 *
 * @param string $hook_suffix Current admin page hook suffix.
 */
function elodin_bridge_enqueue_admin_assets( $hook_suffix ) {
	if ( 'appearance_page_elodin-bridge' !== $hook_suffix ) {
		return;
	}

	$style_path = ELODIN_BRIDGE_DIR . '/assets/admin-settings.css';
	$style_url = ELODIN_BRIDGE_URL . 'assets/admin-settings.css';

	if ( ! file_exists( $style_path ) ) {
		return;
	}

	wp_enqueue_style(
		'elodin-bridge-admin',
		$style_url,
		array(),
		(string) filemtime( $style_path )
	);

	$script_path = ELODIN_BRIDGE_DIR . '/assets/admin-image-sizes.js';
	$script_url = ELODIN_BRIDGE_URL . 'assets/admin-image-sizes.js';
	if ( file_exists( $script_path ) ) {
		wp_enqueue_script(
			'elodin-bridge-admin-image-sizes',
			$script_url,
			array(),
			(string) filemtime( $script_path ),
			true
		);
	}
}
add_action( 'admin_enqueue_scripts', 'elodin_bridge_enqueue_admin_assets' );

/**
 * Render the Elodin Bridge admin page under Appearance.
 */
function elodin_bridge_render_admin_page() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	$heading_paragraph_overrides_available = elodin_bridge_is_generatepress_parent_theme();
	$heading_paragraph_overrides_enabled = elodin_bridge_is_heading_paragraph_overrides_enabled();
	$balanced_text_enabled = elodin_bridge_is_balanced_text_enabled();
	$automatic_heading_margins_settings = elodin_bridge_get_automatic_heading_margins_settings();
	$automatic_heading_margins_enabled = elodin_bridge_is_automatic_heading_margins_enabled();
	$editor_ui_restrictions_enabled = elodin_bridge_is_editor_ui_restrictions_enabled();
	$media_library_infinite_scrolling_enabled = elodin_bridge_is_media_library_infinite_scrolling_enabled();
	$shortcodes_enabled = elodin_bridge_is_shortcodes_enabled();
	$generateblocks_boundary_highlights_enabled = elodin_bridge_is_generateblocks_boundary_highlights_enabled();
	$prettier_widgets_enabled = elodin_bridge_is_prettier_widgets_enabled();
	$last_child_margin_resets_enabled = elodin_bridge_is_last_child_margin_resets_enabled();
	$block_edge_class_settings = elodin_bridge_get_block_edge_class_settings();
	$block_edge_classes_enabled = elodin_bridge_is_block_edge_classes_enabled();
	$image_sizes_settings = elodin_bridge_get_image_sizes_settings();
	$image_size_rows = array_values( $image_sizes_settings['sizes'] );
	$content_type_behavior_settings = elodin_bridge_get_content_type_behavior_settings();
	$content_type_behavior_post_types = elodin_bridge_get_content_type_behavior_post_types();
	?>
	<div class="wrap elodin-bridge-admin">
		<div class="elodin-bridge-admin__hero">
			<h1 class="elodin-bridge-admin__title">
				<?php esc_html_e( 'Elodin Bridge', 'elodin-bridge' ); ?>
				<span class="elodin-bridge-admin__version"><?php echo esc_html( sprintf( 'v%s', ELODIN_BRIDGE_VERSION ) ); ?></span>
			</h1>
			<p class="elodin-bridge-admin__intro">
				<?php esc_html_e( 'Bridging the gap between WordPress\'s extensive capabilities for hybrid themes and the few extra items we need on just about every site, so that backend editing is faster and more intuitive.', 'elodin-bridge' ); ?>
			</p>
		</div>

		<form action="options.php" method="post" class="elodin-bridge-admin__form">
			<?php settings_fields( 'elodin_bridge_settings' ); ?>

			<div class="elodin-bridge-admin__card">
				<div class="elodin-bridge-admin__feature <?php echo $heading_paragraph_overrides_enabled ? 'is-enabled' : ''; ?> <?php echo ! $heading_paragraph_overrides_available ? 'is-unavailable' : ''; ?>">
					<label class="elodin-bridge-admin__feature-header <?php echo ! $heading_paragraph_overrides_available ? 'is-disabled' : ''; ?>" for="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_HEADING_PARAGRAPH_OVERRIDES ); ?>">
						<input
							type="hidden"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_HEADING_PARAGRAPH_OVERRIDES ); ?>"
							value="0"
						/>
						<input
							type="checkbox"
							class="elodin-bridge-admin__toggle-input elodin-bridge-admin__feature-toggle"
							id="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_HEADING_PARAGRAPH_OVERRIDES ); ?>"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_HEADING_PARAGRAPH_OVERRIDES ); ?>"
							value="1"
							<?php checked( $heading_paragraph_overrides_enabled ); ?>
							<?php disabled( ! $heading_paragraph_overrides_available ); ?>
						/>
						<span class="elodin-bridge-admin__toggle-track" aria-hidden="true">
							<span class="elodin-bridge-admin__toggle-thumb"></span>
						</span>
						<span class="elodin-bridge-admin__feature-heading-row">
							<span class="elodin-bridge-admin__feature-title"><?php esc_html_e( 'Enable heading and paragraph style overrides', 'elodin-bridge' ); ?></span>
							<span class="elodin-bridge-admin__requirement-tag"><?php esc_html_e( 'Requires GeneratePress', 'elodin-bridge' ); ?></span>
						</span>
					</label>

					<div class="elodin-bridge-admin__feature-body">
						<p class="elodin-bridge-admin__description">
							<?php esc_html_e( 'Adds block toolbar controls for paragraph/heading typography overrides and applies those override classes using your GeneratePress typography values (desktop, tablet, and mobile).', 'elodin-bridge' ); ?>
						</p>
						<?php if ( ! $heading_paragraph_overrides_available ) : ?>
							<p class="elodin-bridge-admin__note">
								<?php esc_html_e( 'This setting is only available when GeneratePress is your active parent theme.', 'elodin-bridge' ); ?>
							</p>
						<?php endif; ?>
					</div>
				</div>
			</div>

			<div class="elodin-bridge-admin__card">
				<div class="elodin-bridge-admin__feature <?php echo $balanced_text_enabled ? 'is-enabled' : ''; ?>">
					<label class="elodin-bridge-admin__feature-header" for="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_BALANCED_TEXT ); ?>">
						<input
							type="hidden"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_BALANCED_TEXT ); ?>"
							value="0"
						/>
						<input
							type="checkbox"
							class="elodin-bridge-admin__toggle-input elodin-bridge-admin__feature-toggle"
							id="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_BALANCED_TEXT ); ?>"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_BALANCED_TEXT ); ?>"
							value="1"
							<?php checked( $balanced_text_enabled ); ?>
						/>
						<span class="elodin-bridge-admin__toggle-track" aria-hidden="true">
							<span class="elodin-bridge-admin__toggle-thumb"></span>
						</span>
						<span class="elodin-bridge-admin__feature-title"><?php esc_html_e( 'Enable balanced text toggle', 'elodin-bridge' ); ?></span>
					</label>

					<div class="elodin-bridge-admin__feature-body">
						<p class="elodin-bridge-admin__description">
							<?php esc_html_e( 'Adds a separate block toolbar button to toggle the .balanced class on paragraphs and headings. When active, that class applies text-wrap: balance.', 'elodin-bridge' ); ?>
						</p>
					</div>
				</div>
			</div>

			<div class="elodin-bridge-admin__card">
				<div class="elodin-bridge-admin__feature <?php echo ! empty( $content_type_behavior_settings['enabled'] ) ? 'is-enabled' : ''; ?>">
					<label class="elodin-bridge-admin__feature-header" for="elodin-bridge-content-type-behavior-enabled">
						<input
							type="hidden"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_CONTENT_TYPE_BEHAVIOR ); ?>[enabled]"
							value="0"
						/>
						<input
							type="checkbox"
							class="elodin-bridge-admin__toggle-input elodin-bridge-admin__feature-toggle"
							id="elodin-bridge-content-type-behavior-enabled"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_CONTENT_TYPE_BEHAVIOR ); ?>[enabled]"
							value="1"
							<?php checked( ! empty( $content_type_behavior_settings['enabled'] ) ); ?>
						/>
						<span class="elodin-bridge-admin__toggle-track" aria-hidden="true">
							<span class="elodin-bridge-admin__toggle-thumb"></span>
						</span>
						<span class="elodin-bridge-admin__feature-title"><?php esc_html_e( 'Enable page-like vs post-like content type mapping', 'elodin-bridge' ); ?></span>
					</label>

					<div class="elodin-bridge-admin__feature-body">
						<p class="elodin-bridge-admin__description">
							<?php esc_html_e( 'The only behavior this changes on your site is in the backend editor: enabled content types get a black title background with strike-through styling on the title when not being interacted with.', 'elodin-bridge' ); ?>
						</p>

						<?php if ( ! empty( $content_type_behavior_post_types ) ) : ?>
							<div class="elodin-bridge-admin__content-type-list">
								<?php foreach ( $content_type_behavior_post_types as $post_type => $label ) : ?>
									<label class="elodin-bridge-admin__content-type-item" for="<?php echo esc_attr( 'elodin-bridge-content-type-behavior-' . $post_type ); ?>">
										<input
											type="hidden"
											name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_CONTENT_TYPE_BEHAVIOR ); ?>[post_types][<?php echo esc_attr( $post_type ); ?>]"
											value="0"
										/>
										<input
											type="checkbox"
											class="elodin-bridge-admin__content-type-checkbox"
											id="<?php echo esc_attr( 'elodin-bridge-content-type-behavior-' . $post_type ); ?>"
											name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_CONTENT_TYPE_BEHAVIOR ); ?>[post_types][<?php echo esc_attr( $post_type ); ?>]"
											value="1"
											<?php checked( ! empty( $content_type_behavior_settings['post_types'][ $post_type ] ) ); ?>
										/>
										<span class="elodin-bridge-admin__content-type-label"><?php echo esc_html( $label ); ?></span>
									</label>
								<?php endforeach; ?>
							</div>
							<p class="elodin-bridge-admin__note">
								<?php esc_html_e( 'Checked content types receive that backend title styling; unchecked content types do not.', 'elodin-bridge' ); ?>
							</p>
						<?php endif; ?>
					</div>
				</div>
			</div>

			<div class="elodin-bridge-admin__card">
				<div class="elodin-bridge-admin__feature <?php echo $automatic_heading_margins_enabled ? 'is-enabled' : ''; ?>">
					<label class="elodin-bridge-admin__feature-header" for="elodin-bridge-automatic-heading-margins-enabled">
						<input
							type="hidden"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_AUTOMATIC_HEADING_MARGINS ); ?>[enabled]"
							value="0"
						/>
						<input
							type="checkbox"
							class="elodin-bridge-admin__toggle-input elodin-bridge-admin__feature-toggle"
							id="elodin-bridge-automatic-heading-margins-enabled"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_AUTOMATIC_HEADING_MARGINS ); ?>[enabled]"
							value="1"
							<?php checked( ! empty( $automatic_heading_margins_settings['enabled'] ) ); ?>
						/>
						<span class="elodin-bridge-admin__toggle-track" aria-hidden="true">
							<span class="elodin-bridge-admin__toggle-thumb"></span>
						</span>
						<span class="elodin-bridge-admin__feature-title"><?php esc_html_e( 'Enable automatic heading margins', 'elodin-bridge' ); ?></span>
					</label>

					<div class="elodin-bridge-admin__feature-body">
						<p class="elodin-bridge-admin__description">
							<?php esc_html_e( 'Applies automatic top margins to block headings (h1-h4), with first-child headings reset to 0.', 'elodin-bridge' ); ?>
						</p>
						<div class="elodin-bridge-admin__responsive-values">
							<label class="elodin-bridge-admin__responsive-field" for="elodin-bridge-heading-margin-desktop">
								<span><?php esc_html_e( 'Desktop', 'elodin-bridge' ); ?></span>
								<input
									type="text"
									class="regular-text"
									id="elodin-bridge-heading-margin-desktop"
									name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_AUTOMATIC_HEADING_MARGINS ); ?>[desktop]"
									value="<?php echo esc_attr( $automatic_heading_margins_settings['desktop'] ?? '3em' ); ?>"
								/>
							</label>
							<label class="elodin-bridge-admin__responsive-field" for="elodin-bridge-heading-margin-tablet">
								<span><?php esc_html_e( 'Tablet', 'elodin-bridge' ); ?></span>
								<input
									type="text"
									class="regular-text"
									id="elodin-bridge-heading-margin-tablet"
									name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_AUTOMATIC_HEADING_MARGINS ); ?>[tablet]"
									value="<?php echo esc_attr( $automatic_heading_margins_settings['tablet'] ?? '2.5em' ); ?>"
								/>
							</label>
							<label class="elodin-bridge-admin__responsive-field" for="elodin-bridge-heading-margin-mobile">
								<span><?php esc_html_e( 'Mobile', 'elodin-bridge' ); ?></span>
								<input
									type="text"
									class="regular-text"
									id="elodin-bridge-heading-margin-mobile"
									name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_AUTOMATIC_HEADING_MARGINS ); ?>[mobile]"
									value="<?php echo esc_attr( $automatic_heading_margins_settings['mobile'] ?? '2em' ); ?>"
								/>
							</label>
						</div>
						<p class="elodin-bridge-admin__note">
							<?php esc_html_e( 'Supports CSS values like 4em, var(--space-heading-top), or clamp(2rem, 3vw, 4rem).', 'elodin-bridge' ); ?>
						</p>
					</div>
				</div>
			</div>

			<div class="elodin-bridge-admin__card">
				<div class="elodin-bridge-admin__feature <?php echo $last_child_margin_resets_enabled ? 'is-enabled' : ''; ?>">
					<label class="elodin-bridge-admin__feature-header" for="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_LAST_CHILD_MARGIN_RESETS ); ?>">
						<input
							type="hidden"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_LAST_CHILD_MARGIN_RESETS ); ?>"
							value="0"
						/>
						<input
							type="checkbox"
							class="elodin-bridge-admin__toggle-input elodin-bridge-admin__feature-toggle"
							id="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_LAST_CHILD_MARGIN_RESETS ); ?>"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_LAST_CHILD_MARGIN_RESETS ); ?>"
							value="1"
							<?php checked( $last_child_margin_resets_enabled ); ?>
						/>
						<span class="elodin-bridge-admin__toggle-track" aria-hidden="true">
							<span class="elodin-bridge-admin__toggle-thumb"></span>
						</span>
						<span class="elodin-bridge-admin__feature-title"><?php esc_html_e( 'Enable last-child margin resets', 'elodin-bridge' ); ?></span>
					</label>

					<div class="elodin-bridge-admin__feature-body">
						<p class="elodin-bridge-admin__description">
							<?php esc_html_e( 'Sets margin-bottom: 0 for last-child headings, paragraphs, lists, and button groups.', 'elodin-bridge' ); ?>
						</p>
					</div>
				</div>
			</div>

			<div class="elodin-bridge-admin__card">
				<div class="elodin-bridge-admin__feature <?php echo $editor_ui_restrictions_enabled ? 'is-enabled' : ''; ?>">
					<label class="elodin-bridge-admin__feature-header" for="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_EDITOR_UI_RESTRICTIONS ); ?>">
						<input
							type="hidden"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_EDITOR_UI_RESTRICTIONS ); ?>"
							value="0"
						/>
						<input
							type="checkbox"
							class="elodin-bridge-admin__toggle-input elodin-bridge-admin__feature-toggle"
							id="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_EDITOR_UI_RESTRICTIONS ); ?>"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_EDITOR_UI_RESTRICTIONS ); ?>"
							value="1"
							<?php checked( $editor_ui_restrictions_enabled ); ?>
						/>
						<span class="elodin-bridge-admin__toggle-track" aria-hidden="true">
							<span class="elodin-bridge-admin__toggle-thumb"></span>
						</span>
						<span class="elodin-bridge-admin__feature-title"><?php esc_html_e( 'Disable fullscreen mode and publish sidebar in the editor', 'elodin-bridge' ); ?></span>
					</label>

					<div class="elodin-bridge-admin__feature-body">
						<p class="elodin-bridge-admin__description">
							<?php esc_html_e( 'Injects inline JS to disable fullscreen mode and disable the publish sidebar in the block editor.', 'elodin-bridge' ); ?>
						</p>
					</div>
				</div>
			</div>

			<div class="elodin-bridge-admin__card">
				<div class="elodin-bridge-admin__feature <?php echo $media_library_infinite_scrolling_enabled ? 'is-enabled' : ''; ?>">
					<label class="elodin-bridge-admin__feature-header" for="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_MEDIA_LIBRARY_INFINITE_SCROLLING ); ?>">
						<input
							type="hidden"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_MEDIA_LIBRARY_INFINITE_SCROLLING ); ?>"
							value="0"
						/>
						<input
							type="checkbox"
							class="elodin-bridge-admin__toggle-input elodin-bridge-admin__feature-toggle"
							id="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_MEDIA_LIBRARY_INFINITE_SCROLLING ); ?>"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_MEDIA_LIBRARY_INFINITE_SCROLLING ); ?>"
							value="1"
							<?php checked( $media_library_infinite_scrolling_enabled ); ?>
						/>
						<span class="elodin-bridge-admin__toggle-track" aria-hidden="true">
							<span class="elodin-bridge-admin__toggle-thumb"></span>
						</span>
						<span class="elodin-bridge-admin__feature-title"><?php esc_html_e( 'Enable media library infinite scrolling', 'elodin-bridge' ); ?></span>
					</label>

					<div class="elodin-bridge-admin__feature-body">
						<p class="elodin-bridge-admin__description">
							<?php esc_html_e( 'Forces Media Library infinite scrolling on (equivalent to adding the media_library_infinite_scrolling filter).', 'elodin-bridge' ); ?>
						</p>
					</div>
				</div>
			</div>

			<div class="elodin-bridge-admin__card">
				<div class="elodin-bridge-admin__feature <?php echo $shortcodes_enabled ? 'is-enabled' : ''; ?>">
					<label class="elodin-bridge-admin__feature-header" for="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_SHORTCODES ); ?>">
						<input
							type="hidden"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_SHORTCODES ); ?>"
							value="0"
						/>
						<input
							type="checkbox"
							class="elodin-bridge-admin__toggle-input elodin-bridge-admin__feature-toggle"
							id="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_SHORTCODES ); ?>"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_SHORTCODES ); ?>"
							value="1"
							<?php checked( $shortcodes_enabled ); ?>
						/>
						<span class="elodin-bridge-admin__toggle-track" aria-hidden="true">
							<span class="elodin-bridge-admin__toggle-thumb"></span>
						</span>
						<span class="elodin-bridge-admin__feature-title"><?php esc_html_e( 'Enable footer and copyright shortcodes', 'elodin-bridge' ); ?></span>
					</label>

					<div class="elodin-bridge-admin__feature-body">
						<p class="elodin-bridge-admin__description">
							<?php esc_html_e( 'Registers [year], [c], [tm], and [r] shortcodes. Trademark and registered outputs use superscript markup.', 'elodin-bridge' ); ?>
						</p>
					</div>
				</div>
			</div>

			<div class="elodin-bridge-admin__card">
				<div class="elodin-bridge-admin__feature <?php echo $generateblocks_boundary_highlights_enabled ? 'is-enabled' : ''; ?>">
					<label class="elodin-bridge-admin__feature-header" for="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_GENERATEBLOCKS_BOUNDARY_HIGHLIGHTS ); ?>">
						<input
							type="hidden"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_GENERATEBLOCKS_BOUNDARY_HIGHLIGHTS ); ?>"
							value="0"
						/>
						<input
							type="checkbox"
							class="elodin-bridge-admin__toggle-input elodin-bridge-admin__feature-toggle"
							id="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_GENERATEBLOCKS_BOUNDARY_HIGHLIGHTS ); ?>"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_GENERATEBLOCKS_BOUNDARY_HIGHLIGHTS ); ?>"
							value="1"
							<?php checked( $generateblocks_boundary_highlights_enabled ); ?>
						/>
						<span class="elodin-bridge-admin__toggle-track" aria-hidden="true">
							<span class="elodin-bridge-admin__toggle-thumb"></span>
						</span>
						<span class="elodin-bridge-admin__feature-heading-row">
							<span class="elodin-bridge-admin__feature-title"><?php esc_html_e( 'Enable GenerateBlocks boundary highlights in the editor', 'elodin-bridge' ); ?></span>
							<span class="elodin-bridge-admin__requirement-tag"><?php esc_html_e( 'Requires GenerateBlocks', 'elodin-bridge' ); ?></span>
						</span>
					</label>

					<div class="elodin-bridge-admin__feature-body">
						<p class="elodin-bridge-admin__description">
							<?php esc_html_e( 'Adds dashed outlines around GenerateBlocks containers/elements in the block editor to make block boundaries easier to identify while editing.', 'elodin-bridge' ); ?>
						</p>
					</div>
				</div>
			</div>

			<div class="elodin-bridge-admin__card">
				<div class="elodin-bridge-admin__feature <?php echo $prettier_widgets_enabled ? 'is-enabled' : ''; ?>">
					<label class="elodin-bridge-admin__feature-header" for="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_PRETTIER_WIDGETS ); ?>">
						<input
							type="hidden"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_PRETTIER_WIDGETS ); ?>"
							value="0"
						/>
						<input
							type="checkbox"
							class="elodin-bridge-admin__toggle-input elodin-bridge-admin__feature-toggle"
							id="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_PRETTIER_WIDGETS ); ?>"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_PRETTIER_WIDGETS ); ?>"
							value="1"
							<?php checked( $prettier_widgets_enabled ); ?>
						/>
						<span class="elodin-bridge-admin__toggle-track" aria-hidden="true">
							<span class="elodin-bridge-admin__toggle-thumb"></span>
						</span>
						<span class="elodin-bridge-admin__feature-title"><?php esc_html_e( 'Prettier widgets', 'elodin-bridge' ); ?></span>
					</label>

					<div class="elodin-bridge-admin__feature-body">
						<p class="elodin-bridge-admin__description">
							<?php esc_html_e( 'Applies cleaner spacing and full-width layout rules in the Widgets editor for easier backend editing.', 'elodin-bridge' ); ?>
						</p>
					</div>
				</div>
			</div>

			<div class="elodin-bridge-admin__card">
				<div class="elodin-bridge-admin__feature <?php echo ! empty( $image_sizes_settings['enabled'] ) ? 'is-enabled' : ''; ?>">
					<label class="elodin-bridge-admin__feature-header" for="elodin-bridge-image-sizes-enabled">
						<input
							type="hidden"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_IMAGE_SIZES ); ?>[enabled]"
							value="0"
						/>
						<input
							type="checkbox"
							class="elodin-bridge-admin__toggle-input elodin-bridge-admin__feature-toggle"
							id="elodin-bridge-image-sizes-enabled"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_IMAGE_SIZES ); ?>[enabled]"
							value="1"
							<?php checked( ! empty( $image_sizes_settings['enabled'] ) ); ?>
						/>
						<span class="elodin-bridge-admin__toggle-track" aria-hidden="true">
							<span class="elodin-bridge-admin__toggle-thumb"></span>
						</span>
						<span class="elodin-bridge-admin__feature-title"><?php esc_html_e( 'Enable additional image sizes and gallery size controls', 'elodin-bridge' ); ?></span>
					</label>

					<div class="elodin-bridge-admin__feature-body">
						<p class="elodin-bridge-admin__description">
							<?php esc_html_e( 'Registers custom image sizes for your site. Gallery checkboxes add selected custom sizes to the size picker in addition to WordPress defaults.', 'elodin-bridge' ); ?>
						</p>
						<div class="elodin-bridge-admin__image-size-section">
							<h3 class="elodin-bridge-admin__subheading"><?php esc_html_e( 'Custom Image Sizes', 'elodin-bridge' ); ?></h3>
							<p class="elodin-bridge-admin__note">
								<?php esc_html_e( 'Use unique slugs. Width and height must be positive numbers.', 'elodin-bridge' ); ?>
							</p>
							<div
								class="elodin-bridge-admin__image-size-builder"
								data-next-index="<?php echo esc_attr( (string) count( $image_size_rows ) ); ?>"
							>
								<table class="widefat striped elodin-bridge-admin__image-size-table">
									<thead>
										<tr>
											<th scope="col"><?php esc_html_e( 'Slug', 'elodin-bridge' ); ?></th>
											<th scope="col"><?php esc_html_e( 'Label', 'elodin-bridge' ); ?></th>
											<th scope="col"><?php esc_html_e( 'Width', 'elodin-bridge' ); ?></th>
											<th scope="col"><?php esc_html_e( 'Height', 'elodin-bridge' ); ?></th>
											<th scope="col"><?php esc_html_e( 'Hard Crop', 'elodin-bridge' ); ?></th>
											<th scope="col"><?php esc_html_e( 'Allow In Galleries', 'elodin-bridge' ); ?></th>
											<th scope="col"><?php esc_html_e( 'Remove', 'elodin-bridge' ); ?></th>
										</tr>
									</thead>
									<tbody class="elodin-bridge-admin__custom-image-sizes">
										<?php foreach ( $image_size_rows as $index => $size ) : ?>
											<tr class="elodin-bridge-admin__image-size-row">
												<td>
													<input
														type="text"
														class="regular-text code"
														name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_IMAGE_SIZES ); ?>[sizes][<?php echo esc_attr( (string) $index ); ?>][slug]"
														value="<?php echo esc_attr( $size['slug'] ?? '' ); ?>"
														placeholder="hero_large"
													/>
												</td>
												<td>
													<input
														type="text"
														class="regular-text"
														name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_IMAGE_SIZES ); ?>[sizes][<?php echo esc_attr( (string) $index ); ?>][label]"
														value="<?php echo esc_attr( $size['label'] ?? '' ); ?>"
														placeholder="<?php esc_attr_e( 'Hero Large', 'elodin-bridge' ); ?>"
													/>
												</td>
												<td>
													<input
														type="number"
														class="small-text"
														min="1"
														step="1"
														name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_IMAGE_SIZES ); ?>[sizes][<?php echo esc_attr( (string) $index ); ?>][width]"
														value="<?php echo esc_attr( isset( $size['width'] ) ? (string) $size['width'] : '' ); ?>"
													/>
												</td>
												<td>
													<input
														type="number"
														class="small-text"
														min="1"
														step="1"
														name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_IMAGE_SIZES ); ?>[sizes][<?php echo esc_attr( (string) $index ); ?>][height]"
														value="<?php echo esc_attr( isset( $size['height'] ) ? (string) $size['height'] : '' ); ?>"
													/>
												</td>
												<td>
													<input
														type="hidden"
														name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_IMAGE_SIZES ); ?>[sizes][<?php echo esc_attr( (string) $index ); ?>][crop]"
														value="0"
													/>
													<input
														type="checkbox"
														name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_IMAGE_SIZES ); ?>[sizes][<?php echo esc_attr( (string) $index ); ?>][crop]"
														value="1"
														<?php checked( ! empty( $size['crop'] ) ); ?>
													/>
												</td>
												<td>
													<input
														type="hidden"
														name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_IMAGE_SIZES ); ?>[sizes][<?php echo esc_attr( (string) $index ); ?>][gallery]"
														value="0"
													/>
													<input
														type="checkbox"
														name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_IMAGE_SIZES ); ?>[sizes][<?php echo esc_attr( (string) $index ); ?>][gallery]"
														value="1"
														<?php checked( ! empty( $size['gallery'] ) ); ?>
													/>
												</td>
												<td>
													<button type="button" class="button-link-delete elodin-bridge-admin__remove-image-size"><?php esc_html_e( 'Remove', 'elodin-bridge' ); ?></button>
												</td>
											</tr>
										<?php endforeach; ?>
									</tbody>
								</table>
								<button type="button" class="button button-secondary elodin-bridge-admin__add-image-size"><?php esc_html_e( 'Add Custom Size', 'elodin-bridge' ); ?></button>
							</div>
						</div>

						<script type="text/template" id="elodin-bridge-image-size-row-template">
							<tr class="elodin-bridge-admin__image-size-row">
								<td><input type="text" class="regular-text code" name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_IMAGE_SIZES ); ?>[sizes][__INDEX__][slug]" placeholder="hero_large" /></td>
								<td><input type="text" class="regular-text" name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_IMAGE_SIZES ); ?>[sizes][__INDEX__][label]" placeholder="<?php esc_attr_e( 'Hero Large', 'elodin-bridge' ); ?>" /></td>
								<td><input type="number" class="small-text" min="1" step="1" name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_IMAGE_SIZES ); ?>[sizes][__INDEX__][width]" /></td>
								<td><input type="number" class="small-text" min="1" step="1" name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_IMAGE_SIZES ); ?>[sizes][__INDEX__][height]" /></td>
								<td>
									<input type="hidden" name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_IMAGE_SIZES ); ?>[sizes][__INDEX__][crop]" value="0" />
									<input type="checkbox" name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_IMAGE_SIZES ); ?>[sizes][__INDEX__][crop]" value="1" />
								</td>
								<td>
									<input type="hidden" name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_IMAGE_SIZES ); ?>[sizes][__INDEX__][gallery]" value="0" />
									<input type="checkbox" name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_IMAGE_SIZES ); ?>[sizes][__INDEX__][gallery]" value="1" />
								</td>
								<td><button type="button" class="button-link-delete elodin-bridge-admin__remove-image-size"><?php esc_html_e( 'Remove', 'elodin-bridge' ); ?></button></td>
							</tr>
						</script>

						<p class="elodin-bridge-admin__note">
							<strong><?php esc_html_e( 'Important:', 'elodin-bridge' ); ?></strong>
							<?php esc_html_e( 'after enabling or changing image sizes, regenerate thumbnails before those sizes appear in galleries or are available for existing images.', 'elodin-bridge' ); ?>
						</p>
					</div>
				</div>
			</div>

			<div class="elodin-bridge-admin__card">
				<div class="elodin-bridge-admin__feature <?php echo $block_edge_classes_enabled ? 'is-enabled' : ''; ?>">
					<label class="elodin-bridge-admin__feature-header" for="elodin-bridge-block-edge-classes-enabled">
						<input
							type="hidden"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_BLOCK_EDGE_CLASSES ); ?>[enabled]"
							value="0"
						/>
						<input
							type="checkbox"
							class="elodin-bridge-admin__toggle-input elodin-bridge-admin__feature-toggle"
							id="elodin-bridge-block-edge-classes-enabled"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_BLOCK_EDGE_CLASSES ); ?>[enabled]"
							value="1"
							<?php checked( $block_edge_classes_enabled ); ?>
						/>
						<span class="elodin-bridge-admin__toggle-track" aria-hidden="true">
							<span class="elodin-bridge-admin__toggle-thumb"></span>
						</span>
						<span class="elodin-bridge-admin__feature-title"><?php esc_html_e( 'Enable first/last block body classes', 'elodin-bridge' ); ?></span>
					</label>

					<div class="elodin-bridge-admin__feature-body">
						<p class="elodin-bridge-admin__description">
							<?php esc_html_e( 'Adds body classes for the first and/or last top-level block (for example: first-block-is-section, last-block-is-section, first-block-is-core-group, last-block-is-generateblocks-container). These sorts of body classes are useful for conditional styling. For example, you might want a transparent header and apply styles for that only when your first block is full-width, which can be inferred by whether a "section" style block is first or last.', 'elodin-bridge' ); ?>
						</p>

						<div class="elodin-bridge-admin__edge-toggle-list">
							<label class="elodin-bridge-admin__edge-toggle-item">
								<input type="hidden" name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_BLOCK_EDGE_CLASSES ); ?>[enable_first]" value="0" />
								<input
									type="checkbox"
									name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_BLOCK_EDGE_CLASSES ); ?>[enable_first]"
									value="1"
									<?php checked( ! empty( $block_edge_class_settings['enable_first'] ) ); ?>
								/>
								<span><?php esc_html_e( 'Enable first block classes', 'elodin-bridge' ); ?></span>
							</label>
							<label class="elodin-bridge-admin__edge-toggle-item">
								<input type="hidden" name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_BLOCK_EDGE_CLASSES ); ?>[enable_last]" value="0" />
								<input
									type="checkbox"
									name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_BLOCK_EDGE_CLASSES ); ?>[enable_last]"
									value="1"
									<?php checked( ! empty( $block_edge_class_settings['enable_last'] ) ); ?>
								/>
								<span><?php esc_html_e( 'Enable last block classes', 'elodin-bridge' ); ?></span>
							</label>
							<label class="elodin-bridge-admin__edge-toggle-item">
								<input type="hidden" name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_BLOCK_EDGE_CLASSES ); ?>[enable_debug]" value="0" />
								<input
									type="checkbox"
									name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_BLOCK_EDGE_CLASSES ); ?>[enable_debug]"
									value="1"
									<?php checked( ! empty( $block_edge_class_settings['enable_debug'] ) ); ?>
								/>
								<span><?php esc_html_e( 'Show front-end top-level block debug panel', 'elodin-bridge' ); ?></span>
							</label>
						</div>

						<label class="elodin-bridge-admin__edge-textarea-label" for="elodin-bridge-section-blocks">
							<?php esc_html_e( 'Blocks that count as sections (shared for first and last block checks)', 'elodin-bridge' ); ?>
						</label>
						<textarea
							id="elodin-bridge-section-blocks"
							class="large-text code elodin-bridge-admin__edge-textarea"
							rows="8"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_BLOCK_EDGE_CLASSES ); ?>[section_blocks]"
						><?php echo esc_textarea( implode( "\n", $block_edge_class_settings['section_blocks'] ) ); ?></textarea>
						<p class="elodin-bridge-admin__note">
							<?php esc_html_e( 'Enter one block name per line (example: core/group or generateblocks/container).', 'elodin-bridge' ); ?>
						</p>
					</div>
				</div>
			</div>

			<div class="elodin-bridge-admin__actions">
				<?php submit_button( __( 'Save Changes', 'elodin-bridge' ), 'primary', 'submit', false ); ?>
			</div>
		</form>
	</div>
	<?php
}

/**
 * Register the Elodin Bridge settings page in the Appearance menu.
 */
function elodin_bridge_register_admin_menu() {
	add_theme_page(
		__( 'Elodin Bridge', 'elodin-bridge' ),
		__( 'Elodin Bridge', 'elodin-bridge' ),
		'edit_theme_options',
		'elodin-bridge',
		'elodin_bridge_render_admin_page'
	);
}
add_action( 'admin_menu', 'elodin_bridge_register_admin_menu' );

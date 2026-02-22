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
		'desktop' => 'var( --space-l )',
		'tablet'  => 'var( --space-l )',
		'mobile'  => 'var( --space-l )',
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
 * Get default values for last-child button group top margin settings.
 *
 * @return array{enabled:int,value:string}
 */
function elodin_bridge_get_last_child_button_group_top_margin_defaults() {
	return array(
		'enabled' => 1,
		'value'   => 'var( --space-l )',
	);
}

/**
 * Sanitize last-child button group top margin settings.
 *
 * @param mixed $value Raw setting value.
 * @return array{enabled:int,value:string}
 */
function elodin_bridge_sanitize_last_child_button_group_top_margin_settings( $value ) {
	$defaults = elodin_bridge_get_last_child_button_group_top_margin_defaults();
	$value = is_array( $value ) ? $value : array();

	return array(
		'enabled' => elodin_bridge_sanitize_toggle( $value['enabled'] ?? $defaults['enabled'] ),
		'value'   => elodin_bridge_sanitize_css_value( $value['value'] ?? $defaults['value'], $defaults['value'] ),
	);
}

/**
 * Get normalized last-child button group top margin settings.
 *
 * @return array{enabled:int,value:string}
 */
function elodin_bridge_get_last_child_button_group_top_margin_settings() {
	$saved = get_option( ELODIN_BRIDGE_OPTION_LAST_CHILD_BUTTON_GROUP_TOP_MARGIN, null );
	if ( null === $saved || false === $saved ) {
		return elodin_bridge_get_last_child_button_group_top_margin_defaults();
	}

	return elodin_bridge_sanitize_last_child_button_group_top_margin_settings( $saved );
}

/**
 * Get spacing alias tokens and their expected theme.json slugs.
 *
 * @return array<int,array{token:string,label:string,source_slugs:array<int,string>}>
 */
function elodin_bridge_get_spacing_variable_scale() {
	return array(
		array(
			'token'        => 's',
			'label'        => __( 'Small', 'elodin-bridge' ),
			'source_slugs' => array( 'small', 's' ),
		),
		array(
			'token'        => 'm',
			'label'        => __( 'Medium', 'elodin-bridge' ),
			'source_slugs' => array( 'medium', 'm' ),
		),
		array(
			'token'        => 'l',
			'label'        => __( 'Large', 'elodin-bridge' ),
			'source_slugs' => array( 'large', 'l' ),
		),
		array(
			'token'        => 'xl',
			'label'        => __( 'Extra Large', 'elodin-bridge' ),
			'source_slugs' => array( 'x-large', 'xl' ),
		),
		array(
			'token'        => '2xl',
			'label'        => __( '2XL', 'elodin-bridge' ),
			'source_slugs' => array( 'xx-large', '2xl', 'xxl' ),
		),
		array(
			'token'        => '3xl',
			'label'        => __( '3XL', 'elodin-bridge' ),
			'source_slugs' => array( 'xxx-large', '3xl', 'xxxl' ),
		),
		array(
			'token'        => '4xl',
			'label'        => __( '4XL', 'elodin-bridge' ),
			'source_slugs' => array( 'xxxx-large', '4xl', 'xxxxl' ),
		),
	);
}

/**
 * Get the active theme.json file path.
 *
 * Prefers the active stylesheet theme, then falls back to parent template theme.
 *
 * @return string
 */
function elodin_bridge_get_active_theme_json_path() {
	$stylesheet_json = trailingslashit( get_stylesheet_directory() ) . 'theme.json';
	if ( file_exists( $stylesheet_json ) ) {
		return $stylesheet_json;
	}

	$template_json = trailingslashit( get_template_directory() ) . 'theme.json';
	if ( file_exists( $template_json ) ) {
		return $template_json;
	}

	return '';
}

/**
 * Get decoded active theme.json data.
 *
 * @return array<string,mixed>
 */
function elodin_bridge_get_active_theme_json_data() {
	static $loaded = false;
	static $decoded = array();

	if ( $loaded ) {
		return $decoded;
	}

	$loaded = true;
	$theme_json_path = elodin_bridge_get_active_theme_json_path();
	if ( '' === $theme_json_path || ! is_readable( $theme_json_path ) ) {
		return $decoded;
	}

	$raw_json = file_get_contents( $theme_json_path );
	if ( false === $raw_json || '' === $raw_json ) {
		return $decoded;
	}

	$parsed = json_decode( $raw_json, true );
	if ( is_array( $parsed ) ) {
		$decoded = $parsed;
	}

	return $decoded;
}

/**
 * Normalize a theme.json CSS value into a value safe for inline output.
 *
 * Supports `var:preset|...` and `var:custom|...` shorthands.
 *
 * @param mixed $value Raw theme.json value.
 * @return string
 */
function elodin_bridge_normalize_theme_json_css_value( $value ) {
	$value = trim( (string) $value );
	if ( '' === $value ) {
		return '';
	}

	$value = preg_replace_callback(
		'/var:preset\|([a-zA-Z0-9-]+)\|([a-zA-Z0-9-]+)/',
		static function ( $matches ) {
			$group = sanitize_key( $matches[1] ?? '' );
			$slug = sanitize_key( $matches[2] ?? '' );
			if ( '' === $group || '' === $slug ) {
				return '';
			}

			return 'var(--wp--preset--' . $group . '--' . $slug . ')';
		},
		$value
	);

	$value = preg_replace_callback(
		'/var:custom\|([a-zA-Z0-9|_-]+)/',
		static function ( $matches ) {
			$raw_path = trim( (string) ( $matches[1] ?? '' ) );
			if ( '' === $raw_path ) {
				return '';
			}

			$path_segments = explode( '|', $raw_path );
			$sanitized_segments = array();
			foreach ( $path_segments as $segment ) {
				$segment = sanitize_key( $segment );
				if ( '' !== $segment ) {
					$sanitized_segments[] = $segment;
				}
			}

			if ( empty( $sanitized_segments ) ) {
				return '';
			}

			return 'var(--wp--custom--' . implode( '--', $sanitized_segments ) . ')';
		},
		$value
	);

	return elodin_bridge_sanitize_css_value( $value, '' );
}

/**
 * Read normalized button padding values from the active theme.json file.
 *
 * @return array{all:string,top:string,right:string,bottom:string,left:string}
 */
function elodin_bridge_get_theme_button_padding_values() {
	$padding_values = array(
		'all'    => '',
		'top'    => '',
		'right'  => '',
		'bottom' => '',
		'left'   => '',
	);

	$decoded = elodin_bridge_get_active_theme_json_data();
	if ( ! is_array( $decoded ) ) {
		return $padding_values;
	}

	$raw_padding = $decoded['styles']['blocks']['core/button']['spacing']['padding'] ?? null;
	if ( null === $raw_padding ) {
		$raw_padding = $decoded['styles']['elements']['button']['spacing']['padding'] ?? null;
	}

	if ( is_scalar( $raw_padding ) ) {
		$padding_values['all'] = elodin_bridge_normalize_theme_json_css_value( $raw_padding );
		return $padding_values;
	}

	if ( ! is_array( $raw_padding ) ) {
		return $padding_values;
	}

	$padding_values['top'] = elodin_bridge_normalize_theme_json_css_value( $raw_padding['top'] ?? '' );
	$padding_values['right'] = elodin_bridge_normalize_theme_json_css_value( $raw_padding['right'] ?? '' );
	$padding_values['bottom'] = elodin_bridge_normalize_theme_json_css_value( $raw_padding['bottom'] ?? '' );
	$padding_values['left'] = elodin_bridge_normalize_theme_json_css_value( $raw_padding['left'] ?? '' );

	$vertical = elodin_bridge_normalize_theme_json_css_value( $raw_padding['vertical'] ?? '' );
	if ( '' !== $vertical ) {
		if ( '' === $padding_values['top'] ) {
			$padding_values['top'] = $vertical;
		}
		if ( '' === $padding_values['bottom'] ) {
			$padding_values['bottom'] = $vertical;
		}
	}

	$horizontal = elodin_bridge_normalize_theme_json_css_value( $raw_padding['horizontal'] ?? '' );
	if ( '' !== $horizontal ) {
		if ( '' === $padding_values['right'] ) {
			$padding_values['right'] = $horizontal;
		}
		if ( '' === $padding_values['left'] ) {
			$padding_values['left'] = $horizontal;
		}
	}

	return $padding_values;
}

/**
 * Read spacing presets from the active theme.json file.
 *
 * @return array<string,array{slug:string,name:string,size:string}>
 */
function elodin_bridge_get_theme_spacing_size_presets() {
	$decoded = elodin_bridge_get_active_theme_json_data();
	if ( ! is_array( $decoded ) ) {
		return array();
	}

	$raw_presets = $decoded['settings']['spacing']['spacingSizes'] ?? array();
	if ( ! is_array( $raw_presets ) ) {
		return array();
	}

	$presets = array();
	foreach ( $raw_presets as $preset ) {
		if ( ! is_array( $preset ) ) {
			continue;
		}

		$slug = sanitize_key( $preset['slug'] ?? '' );
		$size = elodin_bridge_sanitize_css_value( $preset['size'] ?? '', '' );
		if ( '' === $slug || '' === $size ) {
			continue;
		}

		$name = sanitize_text_field( $preset['name'] ?? '' );
		if ( '' === $name ) {
			$name = $slug;
		}

		$presets[ $slug ] = array(
			'slug' => $slug,
			'name' => $name,
			'size' => $size,
		);
	}

	return $presets;
}

/**
 * Build alias mappings from theme spacing presets to short variable names.
 *
 * @return array<int,array{token:string,label:string,source_slug:string,source_name:string,value:string}>
 */
function elodin_bridge_get_spacing_variable_aliases() {
	$definitions = elodin_bridge_get_spacing_variable_scale();
	$presets = elodin_bridge_get_theme_spacing_size_presets();
	$aliases = array();

	foreach ( $definitions as $definition ) {
		$token = sanitize_key( $definition['token'] ?? '' );
		if ( '' === $token ) {
			continue;
		}

		$label = sanitize_text_field( $definition['label'] ?? '' );
		$source_slugs = isset( $definition['source_slugs'] ) && is_array( $definition['source_slugs'] ) ? $definition['source_slugs'] : array();
		$matched_preset = array();

		foreach ( $source_slugs as $source_slug ) {
			$source_slug = sanitize_key( $source_slug );
			if ( '' === $source_slug || ! isset( $presets[ $source_slug ] ) ) {
				continue;
			}

			$matched_preset = $presets[ $source_slug ];
			break;
		}

		if ( empty( $matched_preset ) && isset( $presets[ $token ] ) ) {
			$matched_preset = $presets[ $token ];
		}

		$aliases[] = array(
			'token'       => $token,
			'label'       => $label,
			'source_slug' => isset( $matched_preset['slug'] ) ? (string) $matched_preset['slug'] : '',
			'source_name' => isset( $matched_preset['name'] ) ? (string) $matched_preset['name'] : '',
			'value'       => isset( $matched_preset['size'] ) ? (string) $matched_preset['size'] : '',
		);
	}

	return $aliases;
}

/**
 * Get font-size alias tokens and their expected theme.json slugs.
 *
 * @return array<int,array{token:string,label:string,source_slugs:array<int,string>}>
 */
function elodin_bridge_get_font_size_variable_scale() {
	return array(
		array(
			'token'        => 'xs',
			'label'        => __( 'Extra Small', 'elodin-bridge' ),
			'source_slugs' => array( 'x-small', 'xs' ),
		),
		array(
			'token'        => 's',
			'label'        => __( 'Small', 'elodin-bridge' ),
			'source_slugs' => array( 'small', 's' ),
		),
		array(
			'token'        => 'b',
			'label'        => __( 'Base', 'elodin-bridge' ),
			'source_slugs' => array( 'base', 'b' ),
		),
		array(
			'token'        => 'm',
			'label'        => __( 'Medium', 'elodin-bridge' ),
			'source_slugs' => array( 'medium', 'm' ),
		),
		array(
			'token'        => 'l',
			'label'        => __( 'Large', 'elodin-bridge' ),
			'source_slugs' => array( 'large', 'l' ),
		),
		array(
			'token'        => 'xl',
			'label'        => __( 'Extra Large', 'elodin-bridge' ),
			'source_slugs' => array( 'x-large', 'xl' ),
		),
		array(
			'token'        => '2xl',
			'label'        => __( '2XL', 'elodin-bridge' ),
			'source_slugs' => array( 'xx-large', '2xl', 'xxl' ),
		),
	);
}

/**
 * Read font-size presets from the active theme.json file.
 *
 * @return array<string,array{slug:string,name:string,size:string}>
 */
function elodin_bridge_get_theme_font_size_presets() {
	$decoded = elodin_bridge_get_active_theme_json_data();
	if ( ! is_array( $decoded ) ) {
		return array();
	}

	$raw_presets = $decoded['settings']['typography']['fontSizes'] ?? array();
	if ( ! is_array( $raw_presets ) ) {
		return array();
	}

	$presets = array();
	foreach ( $raw_presets as $preset ) {
		if ( ! is_array( $preset ) ) {
			continue;
		}

		$slug = sanitize_key( $preset['slug'] ?? '' );
		$size = elodin_bridge_sanitize_css_value( $preset['size'] ?? '', '' );
		if ( '' === $slug || '' === $size ) {
			continue;
		}

		$name = sanitize_text_field( $preset['name'] ?? '' );
		if ( '' === $name ) {
			$name = $slug;
		}

		$presets[ $slug ] = array(
			'slug' => $slug,
			'name' => $name,
			'size' => $size,
		);
	}

	return $presets;
}

/**
 * Build alias mappings from theme font-size presets to short variable names.
 *
 * @return array<int,array{token:string,label:string,source_slug:string,source_name:string,value:string}>
 */
function elodin_bridge_get_font_size_variable_aliases() {
	$definitions = elodin_bridge_get_font_size_variable_scale();
	$presets = elodin_bridge_get_theme_font_size_presets();
	$aliases = array();

	foreach ( $definitions as $definition ) {
		$token = sanitize_key( $definition['token'] ?? '' );
		if ( '' === $token ) {
			continue;
		}

		$label = sanitize_text_field( $definition['label'] ?? '' );
		$source_slugs = isset( $definition['source_slugs'] ) && is_array( $definition['source_slugs'] ) ? $definition['source_slugs'] : array();
		$matched_preset = array();

		foreach ( $source_slugs as $source_slug ) {
			$source_slug = sanitize_key( $source_slug );
			if ( '' === $source_slug || ! isset( $presets[ $source_slug ] ) ) {
				continue;
			}

			$matched_preset = $presets[ $source_slug ];
			break;
		}

		if ( empty( $matched_preset ) && isset( $presets[ $token ] ) ) {
			$matched_preset = $presets[ $token ];
		}

		$aliases[] = array(
			'token'       => $token,
			'label'       => $label,
			'source_slug' => isset( $matched_preset['slug'] ) ? (string) $matched_preset['slug'] : '',
			'source_name' => isset( $matched_preset['name'] ) ? (string) $matched_preset['name'] : '',
			'value'       => isset( $matched_preset['size'] ) ? (string) $matched_preset['size'] : '',
		);
	}

	return $aliases;
}

/**
 * Get default values for spacing variable settings.
 *
 * @return array{enabled:int}
 */
function elodin_bridge_get_spacing_variables_defaults() {
	return array(
		'enabled' => 1,
	);
}

/**
 * Sanitize spacing variable settings.
 *
 * @param mixed $value Raw setting value.
 * @return array{enabled:int}
 */
function elodin_bridge_sanitize_spacing_variables_settings( $value ) {
	$defaults = elodin_bridge_get_spacing_variables_defaults();
	$value = is_array( $value ) ? $value : array();

	return array(
		'enabled' => elodin_bridge_sanitize_toggle( $value['enabled'] ?? $defaults['enabled'] ),
	);
}

/**
 * Get normalized spacing variable settings.
 *
 * @return array{enabled:int}
 */
function elodin_bridge_get_spacing_variables_settings() {
	$saved = get_option( ELODIN_BRIDGE_OPTION_SPACING_VARIABLES, null );
	if ( null === $saved || false === $saved ) {
		return elodin_bridge_get_spacing_variables_defaults();
	}

	return elodin_bridge_sanitize_spacing_variables_settings( $saved );
}

/**
 * Check if spacing variables output is enabled.
 *
 * @return bool
 */
function elodin_bridge_is_spacing_variables_enabled() {
	$settings = elodin_bridge_get_spacing_variables_settings();
	return ! empty( $settings['enabled'] );
}

/**
 * Get default values for font-size variable settings.
 *
 * @return array{enabled:int}
 */
function elodin_bridge_get_font_size_variables_defaults() {
	return array(
		'enabled' => 1,
	);
}

/**
 * Sanitize font-size variable settings.
 *
 * @param mixed $value Raw setting value.
 * @return array{enabled:int}
 */
function elodin_bridge_sanitize_font_size_variables_settings( $value ) {
	$defaults = elodin_bridge_get_font_size_variables_defaults();
	$value = is_array( $value ) ? $value : array();

	return array(
		'enabled' => elodin_bridge_sanitize_toggle( $value['enabled'] ?? $defaults['enabled'] ),
	);
}

/**
 * Get normalized font-size variable settings.
 *
 * @return array{enabled:int}
 */
function elodin_bridge_get_font_size_variables_settings() {
	$saved = get_option( ELODIN_BRIDGE_OPTION_FONT_SIZE_VARIABLES, null );
	if ( null === $saved || false === $saved ) {
		return elodin_bridge_get_font_size_variables_defaults();
	}

	return elodin_bridge_sanitize_font_size_variables_settings( $saved );
}

/**
 * Check if font-size variables output is enabled.
 *
 * @return bool
 */
function elodin_bridge_is_font_size_variables_enabled() {
	$settings = elodin_bridge_get_font_size_variables_settings();
	return ! empty( $settings['enabled'] );
}

/**
 * Get default values for GenerateBlocks layout gap default settings.
 *
 * @return array{enabled:int,column_gap_desktop:string,row_gap_desktop:string,column_gap_tablet:string,row_gap_tablet:string,column_gap_mobile:string,row_gap_mobile:string}
 */
function elodin_bridge_get_generateblocks_layout_gap_defaults_defaults() {
	return array(
		'enabled'            => 1,
		'column_gap_desktop' => 'var( --space-xl )',
		'row_gap_desktop'    => 'var( --space-m )',
		'column_gap_tablet'  => 'var( --space-xl )',
		'row_gap_tablet'     => 'var( --space-m )',
		'column_gap_mobile'  => 'var( --space-xl )',
		'row_gap_mobile'     => 'var( --space-m )',
	);
}

/**
 * Sanitize GenerateBlocks layout gap default settings.
 *
 * @param mixed $value Raw setting value.
 * @return array{enabled:int,column_gap_desktop:string,row_gap_desktop:string,column_gap_tablet:string,row_gap_tablet:string,column_gap_mobile:string,row_gap_mobile:string}
 */
function elodin_bridge_sanitize_generateblocks_layout_gap_defaults_settings( $value ) {
	$defaults = elodin_bridge_get_generateblocks_layout_gap_defaults_defaults();
	$value = is_array( $value ) ? $value : array();

	return array(
		'enabled'            => elodin_bridge_sanitize_toggle( $value['enabled'] ?? $defaults['enabled'] ),
		'column_gap_desktop' => elodin_bridge_sanitize_css_value( $value['column_gap_desktop'] ?? $defaults['column_gap_desktop'], $defaults['column_gap_desktop'] ),
		'row_gap_desktop'    => elodin_bridge_sanitize_css_value( $value['row_gap_desktop'] ?? $defaults['row_gap_desktop'], $defaults['row_gap_desktop'] ),
		'column_gap_tablet'  => elodin_bridge_sanitize_css_value( $value['column_gap_tablet'] ?? $defaults['column_gap_tablet'], $defaults['column_gap_tablet'] ),
		'row_gap_tablet'     => elodin_bridge_sanitize_css_value( $value['row_gap_tablet'] ?? $defaults['row_gap_tablet'], $defaults['row_gap_tablet'] ),
		'column_gap_mobile'  => elodin_bridge_sanitize_css_value( $value['column_gap_mobile'] ?? $defaults['column_gap_mobile'], $defaults['column_gap_mobile'] ),
		'row_gap_mobile'     => elodin_bridge_sanitize_css_value( $value['row_gap_mobile'] ?? $defaults['row_gap_mobile'], $defaults['row_gap_mobile'] ),
	);
}

/**
 * Get normalized GenerateBlocks layout gap default settings.
 *
 * @return array{enabled:int,column_gap_desktop:string,row_gap_desktop:string,column_gap_tablet:string,row_gap_tablet:string,column_gap_mobile:string,row_gap_mobile:string}
 */
function elodin_bridge_get_generateblocks_layout_gap_defaults_settings() {
	$saved = get_option( ELODIN_BRIDGE_OPTION_GENERATEBLOCKS_LAYOUT_GAP_DEFAULTS, null );
	if ( null === $saved || false === $saved ) {
		return elodin_bridge_get_generateblocks_layout_gap_defaults_defaults();
	}

	return elodin_bridge_sanitize_generateblocks_layout_gap_defaults_settings( $saved );
}

/**
 * Check if GenerateBlocks layout gap defaults are enabled.
 *
 * @return bool
 */
function elodin_bridge_is_generateblocks_layout_gap_defaults_enabled() {
	$settings = elodin_bridge_get_generateblocks_layout_gap_defaults_settings();
	return ! empty( $settings['enabled'] );
}

/**
 * Get default values for root-level container padding settings.
 *
 * @return array{enabled:int,desktop:string,tablet:string,mobile:string}
 */
function elodin_bridge_get_root_level_container_padding_defaults() {
	return array(
		'enabled' => 1,
		'desktop' => 'var( --space-xl )',
		'tablet'  => 'var( --space-l )',
		'mobile'  => 'var( --space-m )',
	);
}

/**
 * Sanitize root-level container padding settings.
 *
 * @param mixed $value Raw setting value.
 * @return array{enabled:int,desktop:string,tablet:string,mobile:string}
 */
function elodin_bridge_sanitize_root_level_container_padding_settings( $value ) {
	$defaults = elodin_bridge_get_root_level_container_padding_defaults();
	$value = is_array( $value ) ? $value : array();

	return array(
		'enabled' => elodin_bridge_sanitize_toggle( $value['enabled'] ?? $defaults['enabled'] ),
		'desktop' => elodin_bridge_sanitize_css_value( $value['desktop'] ?? $defaults['desktop'], $defaults['desktop'] ),
		'tablet'  => elodin_bridge_sanitize_css_value( $value['tablet'] ?? $defaults['tablet'], $defaults['tablet'] ),
		'mobile'  => elodin_bridge_sanitize_css_value( $value['mobile'] ?? $defaults['mobile'], $defaults['mobile'] ),
	);
}

/**
 * Get normalized root-level container padding settings.
 *
 * @return array{enabled:int,desktop:string,tablet:string,mobile:string}
 */
function elodin_bridge_get_root_level_container_padding_settings() {
	$saved = get_option( ELODIN_BRIDGE_OPTION_ROOT_LEVEL_CONTAINER_PADDING, null );
	if ( null === $saved || false === $saved ) {
		return elodin_bridge_get_root_level_container_padding_defaults();
	}

	return elodin_bridge_sanitize_root_level_container_padding_settings( $saved );
}

/**
 * Check if root-level container padding is enabled.
 *
 * @return bool
 */
function elodin_bridge_is_root_level_container_padding_enabled() {
	$settings = elodin_bridge_get_root_level_container_padding_settings();
	return ! empty( $settings['enabled'] );
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
 * Check if setting Paragraph as default inserter block is enabled.
 *
 * @return bool
 */
function elodin_bridge_is_default_paragraph_block_enabled() {
	return (bool) get_option( ELODIN_BRIDGE_OPTION_ENABLE_DEFAULT_PARAGRAPH_BLOCK, 1 );
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
 * Check if last-child button group top margin styles are enabled.
 *
 * @return bool
 */
function elodin_bridge_is_last_child_button_group_top_margin_enabled() {
	$settings = elodin_bridge_get_last_child_button_group_top_margin_settings();
	return ! empty( $settings['enabled'] );
}

/**
 * Check if theme.json button padding overrides with !important are enabled.
 *
 * @return bool
 */
function elodin_bridge_is_theme_json_button_padding_important_enabled() {
	return (bool) get_option( ELODIN_BRIDGE_OPTION_ENABLE_THEME_JSON_BUTTON_PADDING_IMPORTANT, 0 );
}

/**
 * Check if mobile fixed-background repair is enabled.
 *
 * @return bool
 */
function elodin_bridge_is_mobile_fixed_background_repair_enabled() {
	return (bool) get_option( ELODIN_BRIDGE_OPTION_ENABLE_MOBILE_FIXED_BACKGROUND_REPAIR, 1 );
}

/**
 * Check if reusable block flow spacing fix is enabled.
 *
 * @return bool
 */
function elodin_bridge_is_reusable_block_flow_spacing_fix_enabled() {
	return (bool) get_option( ELODIN_BRIDGE_OPTION_ENABLE_REUSABLE_BLOCK_FLOW_SPACING_FIX, 1 );
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
		ELODIN_BRIDGE_OPTION_ENABLE_DEFAULT_PARAGRAPH_BLOCK,
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
		ELODIN_BRIDGE_OPTION_SPACING_VARIABLES,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'elodin_bridge_sanitize_spacing_variables_settings',
			'default'           => elodin_bridge_get_spacing_variables_defaults(),
		)
	);

	register_setting(
		'elodin_bridge_settings',
		ELODIN_BRIDGE_OPTION_FONT_SIZE_VARIABLES,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'elodin_bridge_sanitize_font_size_variables_settings',
			'default'           => elodin_bridge_get_font_size_variables_defaults(),
		)
	);

	register_setting(
		'elodin_bridge_settings',
		ELODIN_BRIDGE_OPTION_GENERATEBLOCKS_LAYOUT_GAP_DEFAULTS,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'elodin_bridge_sanitize_generateblocks_layout_gap_defaults_settings',
			'default'           => elodin_bridge_get_generateblocks_layout_gap_defaults_defaults(),
		)
	);

	register_setting(
		'elodin_bridge_settings',
		ELODIN_BRIDGE_OPTION_ROOT_LEVEL_CONTAINER_PADDING,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'elodin_bridge_sanitize_root_level_container_padding_settings',
			'default'           => elodin_bridge_get_root_level_container_padding_defaults(),
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
		ELODIN_BRIDGE_OPTION_LAST_CHILD_BUTTON_GROUP_TOP_MARGIN,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'elodin_bridge_sanitize_last_child_button_group_top_margin_settings',
			'default'           => elodin_bridge_get_last_child_button_group_top_margin_defaults(),
		)
	);

	register_setting(
		'elodin_bridge_settings',
		ELODIN_BRIDGE_OPTION_ENABLE_THEME_JSON_BUTTON_PADDING_IMPORTANT,
		array(
			'type'              => 'boolean',
			'sanitize_callback' => 'elodin_bridge_sanitize_toggle',
			'default'           => 0,
		)
	);

	register_setting(
		'elodin_bridge_settings',
		ELODIN_BRIDGE_OPTION_ENABLE_MOBILE_FIXED_BACKGROUND_REPAIR,
		array(
			'type'              => 'boolean',
			'sanitize_callback' => 'elodin_bridge_sanitize_toggle',
			'default'           => 1,
		)
	);

	register_setting(
		'elodin_bridge_settings',
		ELODIN_BRIDGE_OPTION_ENABLE_REUSABLE_BLOCK_FLOW_SPACING_FIX,
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

require_once ELODIN_BRIDGE_DIR . '/inc/settings-page-admin.php';

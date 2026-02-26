<?php

/**
 * Build main settings page URL.
 *
 * @param string $anchor Optional hash anchor.
 * @return string
 */
function elodin_bridge_get_settings_page_url( $anchor = '' ) {
	$url = add_query_arg(
		array(
			'page' => 'elodin-bridge',
		),
		admin_url( 'themes.php' )
	);

	if ( '' !== $anchor ) {
		$url .= '#' . rawurlencode( ltrim( (string) $anchor, '#' ) );
	}

	return $url;
}

/**
 * Build setup wizard URL.
 *
 * @param string $anchor Optional hash anchor.
 * @return string
 */
function elodin_bridge_get_setup_wizard_url( $anchor = '' ) {
	$url = add_query_arg(
		array(
			'page' => 'elodin-bridge-setup',
		),
		admin_url( 'themes.php' )
	);

	if ( '' !== $anchor ) {
		$url .= '#' . rawurlencode( ltrim( (string) $anchor, '#' ) );
	}

	return $url;
}

/**
 * Setup wizard notice transient key.
 *
 * @return string
 */
function elodin_bridge_get_setup_wizard_notice_key() {
	$user_id = get_current_user_id();
	$user_id = $user_id > 0 ? $user_id : 0;
	return 'elodin_bridge_setup_wizard_notice_' . (string) $user_id;
}

/**
 * Save a setup wizard notice for next page load.
 *
 * @param string $type notice type.
 * @param string $message notice text.
 * @return void
 */
function elodin_bridge_set_setup_wizard_notice( $type, $message ) {
	$allowed_types = array( 'success', 'error', 'warning' );
	$type = in_array( $type, $allowed_types, true ) ? $type : 'success';

	set_transient(
		elodin_bridge_get_setup_wizard_notice_key(),
		array(
			'type'    => $type,
			'message' => (string) $message,
		),
		60
	);
}

/**
 * Read and clear setup wizard notice.
 *
 * @return array{type:string,message:string}
 */
function elodin_bridge_get_setup_wizard_notice() {
	$notice = get_transient( elodin_bridge_get_setup_wizard_notice_key() );
	delete_transient( elodin_bridge_get_setup_wizard_notice_key() );

	if ( ! is_array( $notice ) ) {
		return array(
			'type'    => '',
			'message' => '',
		);
	}

	return array(
		'type'    => sanitize_key( (string) ( $notice['type'] ?? '' ) ),
		'message' => sanitize_text_field( (string) ( $notice['message'] ?? '' ) ),
	);
}

/**
 * Load setup wizard fixture data from plugin data directory.
 *
 * @param string $filename Data filename.
 * @return array<string,mixed>
 */
function elodin_bridge_get_setup_wizard_data( $filename ) {
	static $cache = array();

	$filename = sanitize_file_name( (string) $filename );
	if ( '' === $filename ) {
		return array();
	}

	if ( isset( $cache[ $filename ] ) && is_array( $cache[ $filename ] ) ) {
		return $cache[ $filename ];
	}

	$path = trailingslashit( ELODIN_BRIDGE_DIR ) . 'data/' . $filename;
	if ( ! is_readable( $path ) ) {
		$cache[ $filename ] = array();
		return $cache[ $filename ];
	}

	$raw = file_get_contents( $path );
	if ( false === $raw || '' === $raw ) {
		$cache[ $filename ] = array();
		return $cache[ $filename ];
	}

	$decoded = json_decode( $raw, true );
	$cache[ $filename ] = is_array( $decoded ) ? $decoded : array();
	return $cache[ $filename ];
}

/**
 * Get setup wizard typography defaults.
 *
 * @return array<int,array<string,mixed>>
 */
function elodin_bridge_get_setup_wizard_typography_defaults() {
	$data = elodin_bridge_get_setup_wizard_data( 'setup-wizard-typography.json' );
	$rows = $data['typography'] ?? array();
	return is_array( $rows ) ? array_values( $rows ) : array();
}

/**
 * Get setup wizard global color defaults.
 *
 * @return array<int,array<string,string>>
 */
function elodin_bridge_get_setup_wizard_global_color_defaults() {
	$data = elodin_bridge_get_setup_wizard_data( 'setup-wizard-global-colors.json' );
	$rows = $data['global-colors'] ?? array();
	return is_array( $rows ) ? array_values( $rows ) : array();
}

/**
 * Get setup wizard element template defaults.
 *
 * @return array<int,array<string,mixed>>
 */
function elodin_bridge_get_setup_wizard_element_templates() {
	$data = elodin_bridge_get_setup_wizard_data( 'setup-wizard-elements.json' );
	$rows = $data['elements'] ?? array();
	return is_array( $rows ) ? array_values( $rows ) : array();
}

/**
 * Get first-party hosts whose absolute URLs should be converted to relative paths.
 *
 * @return array<int,string>
 */
function elodin_bridge_get_setup_wizard_element_source_hosts() {
	$data = elodin_bridge_get_setup_wizard_data( 'setup-wizard-elements.json' );
	$hosts = array();
	$seen = array();

	$raw_hosts = isset( $data['source_hosts'] ) && is_array( $data['source_hosts'] ) ? $data['source_hosts'] : array();
	foreach ( $raw_hosts as $raw_host ) {
		$host = strtolower( trim( (string) $raw_host ) );
		if ( '' === $host || isset( $seen[ $host ] ) ) {
			continue;
		}

		$hosts[] = $host;
		$seen[ $host ] = true;
	}

	$site_hosts = array(
		wp_parse_url( home_url( '/' ), PHP_URL_HOST ),
		wp_parse_url( site_url( '/' ), PHP_URL_HOST ),
	);
	foreach ( $site_hosts as $site_host ) {
		$site_host = strtolower( trim( (string) $site_host ) );
		if ( '' === $site_host || isset( $seen[ $site_host ] ) ) {
			continue;
		}

		$hosts[] = $site_host;
		$seen[ $site_host ] = true;
	}

	return $hosts;
}

/**
 * Get available GeneratePress element post type key.
 *
 * @return string
 */
function elodin_bridge_get_setup_wizard_element_post_type() {
	if ( post_type_exists( 'gp_elements' ) ) {
		return 'gp_elements';
	}

	if ( post_type_exists( 'generate_elements' ) ) {
		return 'generate_elements';
	}

	return '';
}

/**
 * Unserialize element meta values when they are stored as serialized strings.
 *
 * @param mixed $value Raw meta value.
 * @return mixed
 */
function elodin_bridge_setup_wizard_unserialize_element_meta_value( $value ) {
	$remaining_passes = 3;
	while ( $remaining_passes > 0 && is_string( $value ) && is_serialized( $value ) ) {
		$decoded = maybe_unserialize( $value );
		if ( $decoded === $value ) {
			break;
		}

		$value = $decoded;
		--$remaining_passes;
	}

	return $value;
}

/**
 * Normalize GeneratePress element condition rows.
 *
 * @param mixed $value Raw condition rows.
 * @return array<int,array{rule:string,object:string}>
 */
function elodin_bridge_setup_wizard_normalize_element_condition_rows( $value ) {
	$value = elodin_bridge_setup_wizard_unserialize_element_meta_value( $value );
	if ( ! is_array( $value ) ) {
		return array();
	}

	if ( isset( $value['rule'] ) && ! isset( $value[0] ) ) {
		$value = array( $value );
	}

	$normalized = array();
	foreach ( $value as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}

		$rule = sanitize_text_field( (string) ( $row['rule'] ?? '' ) );
		if ( '' === $rule ) {
			continue;
		}

		$normalized[] = array(
			'rule'   => $rule,
			'object' => sanitize_key( (string) ( $row['object'] ?? '' ) ),
		);
	}

	return $normalized;
}

/**
 * Normalize GeneratePress element user condition rows.
 *
 * @param mixed $value Raw user condition rows.
 * @return array<int,string>
 */
function elodin_bridge_setup_wizard_normalize_element_user_conditions( $value ) {
	$value = elodin_bridge_setup_wizard_unserialize_element_meta_value( $value );
	if ( ! is_array( $value ) ) {
		return array();
	}

	$normalized = array();
	foreach ( $value as $row ) {
		$rule = sanitize_text_field( (string) $row );
		if ( '' === $rule ) {
			continue;
		}

		$normalized[] = $rule;
	}

	return $normalized;
}

/**
 * Normalize an imported element meta value to GP-compatible storage shape.
 *
 * @param string $meta_key Meta key.
 * @param mixed  $meta_value Raw meta value.
 * @return mixed
 */
function elodin_bridge_setup_wizard_normalize_element_meta_value( $meta_key, $meta_value ) {
	$meta_key = (string) $meta_key;
	if ( '' === $meta_key ) {
		return '';
	}

	if ( in_array( $meta_key, array( '_generate_element_display_conditions', '_generate_element_exclude_conditions' ), true ) ) {
		return elodin_bridge_setup_wizard_normalize_element_condition_rows( $meta_value );
	}

	if ( '_generate_element_user_conditions' === $meta_key ) {
		return elodin_bridge_setup_wizard_normalize_element_user_conditions( $meta_value );
	}

	$meta_value = elodin_bridge_setup_wizard_unserialize_element_meta_value( $meta_value );
	if ( is_array( $meta_value ) ) {
		return $meta_value;
	}

	if ( is_bool( $meta_value ) ) {
		return $meta_value ? '1' : '';
	}

	if ( null === $meta_value ) {
		return '';
	}

	return (string) $meta_value;
}

/**
 * Check if imported element meta key is expected to store arrays.
 *
 * @param string $meta_key Meta key.
 * @return bool
 */
function elodin_bridge_setup_wizard_element_meta_key_requires_array( $meta_key ) {
	return in_array(
		(string) $meta_key,
		array(
			'_generate_element_display_conditions',
			'_generate_element_exclude_conditions',
			'_generate_element_user_conditions',
		),
		true
	);
}

/**
 * Compare normalized element meta values.
 *
 * @param mixed $left First normalized value.
 * @param mixed $right Second normalized value.
 * @return bool
 */
function elodin_bridge_setup_wizard_element_meta_values_match( $left, $right ) {
	if ( is_array( $left ) || is_array( $right ) ) {
		return is_array( $left ) && is_array( $right ) && $left === $right;
	}

	return (string) $left === (string) $right;
}

/**
 * Normalize setup wizard element content for strict equality checks.
 *
 * @param string $content Raw content.
 * @return string
 */
function elodin_bridge_normalize_setup_wizard_element_content_for_compare( $content ) {
	$content = str_replace( array( "\r\n", "\r" ), "\n", (string) $content );
	return trim( $content );
}

/**
 * Get completion state for setup wizard theme.json step.
 *
 * @return array{complete:int,total:int,matched:int}
 */
function elodin_bridge_get_setup_wizard_theme_json_completion() {
	$required_paths = array(
		array( 'settings', 'spacing', 'spacingSizes' ),
		array( 'settings', 'typography', 'fontSizes' ),
		array( 'settings', 'color', 'palette' ),
		array( 'styles', 'blocks', 'core/quote' ),
		array( 'styles', 'blocks', 'core/separator' ),
		array( 'styles', 'blocks', 'core/list' ),
		array( 'styles', 'blocks', 'core/code' ),
		array( 'styles', 'blocks', 'core/table' ),
	);

	$data = elodin_bridge_get_active_theme_json_data();
	$matched = 0;
	foreach ( $required_paths as $path ) {
		$value = elodin_bridge_setup_wizard_get_nested_value( $data, $path );
		if ( null === $value ) {
			continue;
		}

		if ( is_array( $value ) && empty( $value ) ) {
			continue;
		}

		if ( is_string( $value ) && '' === trim( $value ) ) {
			continue;
		}

		++$matched;
	}

	$total = count( $required_paths );
	return array(
		'complete' => ( $total > 0 && $matched === $total ) ? 1 : 0,
		'total'    => $total,
		'matched'  => $matched,
	);
}

/**
 * Get completion state for setup wizard typography step.
 *
 * @return array{complete:int,total:int,matched:int}
 */
function elodin_bridge_get_setup_wizard_typography_completion() {
	$defaults = elodin_bridge_get_setup_wizard_typography_defaults();
	$defaults = is_array( $defaults ) ? array_values( $defaults ) : array();
	$defaults_by_selector = array();
	foreach ( $defaults as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}

		$selector = sanitize_key( (string) ( $row['selector'] ?? '' ) );
		if ( '' === $selector ) {
			continue;
		}

		$defaults_by_selector[ $selector ] = true;
	}

	$total = count( $defaults_by_selector );
	if ( $total < 1 ) {
		return array(
			'complete' => 0,
			'total'    => 0,
			'matched'  => 0,
		);
	}

	$generate_settings = get_option( 'generate_settings', array() );
	$generate_settings = is_array( $generate_settings ) ? $generate_settings : array();
	$existing_rows = isset( $generate_settings['typography'] ) && is_array( $generate_settings['typography'] )
		? array_values( $generate_settings['typography'] )
		: array();
	$selector_index = elodin_bridge_get_setup_wizard_typography_selector_index( $existing_rows );

	$matched = 0;
	foreach ( array_keys( $defaults_by_selector ) as $selector ) {
		if ( isset( $selector_index[ $selector ] ) ) {
			++$matched;
		}
	}

	return array(
		'complete' => ( $matched === $total ) ? 1 : 0,
		'total'    => $total,
		'matched'  => $matched,
	);
}

/**
 * Get completion state for setup wizard global color step.
 *
 * @return array{complete:int,total:int,matched:int,missing_slugs:array<int,string>}
 */
function elodin_bridge_get_setup_wizard_global_colors_completion() {
	$defaults = elodin_bridge_get_setup_wizard_global_color_defaults();
	$defaults = is_array( $defaults ) ? array_values( $defaults ) : array();

	$required_slugs = array();
	foreach ( $defaults as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}

		$slug = sanitize_key( (string) ( $row['slug'] ?? '' ) );
		if ( '' === $slug ) {
			continue;
		}

		$required_slugs[ $slug ] = true;
	}

	$total = count( $required_slugs );
	if ( $total < 1 ) {
		return array(
			'complete'      => 0,
			'total'         => 0,
			'matched'       => 0,
			'missing_slugs' => array(),
		);
	}

	$generate_settings = get_option( 'generate_settings', array() );
	$generate_settings = is_array( $generate_settings ) ? $generate_settings : array();
	$existing = isset( $generate_settings['global_colors'] ) && is_array( $generate_settings['global_colors'] )
		? array_values( $generate_settings['global_colors'] )
		: array();

	$existing_by_slug = array();
	foreach ( $existing as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}

		$slug = sanitize_key( (string) ( $row['slug'] ?? '' ) );
		if ( '' === $slug ) {
			continue;
		}

		$existing_by_slug[ $slug ] = true;
	}

	$matched = 0;
	$missing_slugs = array();
	foreach ( array_keys( $required_slugs ) as $slug ) {
		if ( isset( $existing_by_slug[ $slug ] ) ) {
			++$matched;
		} else {
			$missing_slugs[] = $slug;
		}
	}

	return array(
		'complete'      => ( $matched === $total ) ? 1 : 0,
		'total'         => $total,
		'matched'       => $matched,
		'missing_slugs' => $missing_slugs,
	);
}

/**
 * Check if one bundled element template already exists as an exact match.
 *
 * @param string              $post_type Element post type.
 * @param array<string,mixed> $template Element template row.
 * @param array<int,string>   $source_hosts First-party hosts.
 * @param bool                $repair_meta Whether to repair malformed matching meta rows in place.
 * @return bool
 */
function elodin_bridge_setup_wizard_element_has_exact_match( $post_type, $template, $source_hosts, $repair_meta = false ) {
	global $wpdb;

	$post_type = sanitize_key( (string) $post_type );
	if ( '' === $post_type ) {
		return false;
	}

	$title = sanitize_text_field( (string) ( $template['title'] ?? '' ) );
	if ( '' === $title ) {
		return false;
	}

	$expected_status = sanitize_key( (string) ( $template['status'] ?? 'publish' ) );
	$expected_status = in_array( $expected_status, array( 'publish', 'draft', 'private' ), true ) ? $expected_status : 'publish';
	$expected_content = (string) ( $template['content'] ?? '' );
	$expected_content = elodin_bridge_convert_setup_wizard_element_urls_to_relative( $expected_content, $source_hosts );
	$expected_content = elodin_bridge_normalize_setup_wizard_element_content_for_compare( $expected_content );
	$expected_meta = isset( $template['meta'] ) && is_array( $template['meta'] ) ? $template['meta'] : array();

	$candidate_ids = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_title = %s AND post_status = %s",
			$post_type,
			$title,
			$expected_status
		)
	);

	if ( empty( $candidate_ids ) || ! is_array( $candidate_ids ) ) {
		return false;
	}

	foreach ( $candidate_ids as $candidate_id ) {
		$candidate_id = (int) $candidate_id;
		if ( $candidate_id < 1 ) {
			continue;
		}

		$post = get_post( $candidate_id );
		if ( ! $post instanceof WP_Post || 'trash' === $post->post_status ) {
			continue;
		}

		$current_content = elodin_bridge_normalize_setup_wizard_element_content_for_compare( (string) $post->post_content );
		if ( $current_content !== $expected_content ) {
			continue;
		}

		$all_meta_match = true;
		foreach ( $expected_meta as $meta_key => $meta_value ) {
			$meta_key = (string) $meta_key;
			if ( '' === $meta_key ) {
				continue;
			}

			$expected_meta_value = elodin_bridge_setup_wizard_normalize_element_meta_value( $meta_key, $meta_value );
			$current_meta = get_post_meta( $candidate_id, $meta_key, true );
			$current_normalized_meta = elodin_bridge_setup_wizard_normalize_element_meta_value( $meta_key, $current_meta );

			if ( ! elodin_bridge_setup_wizard_element_meta_values_match( $current_normalized_meta, $expected_meta_value ) ) {
				$all_meta_match = false;
				break;
			}

			if (
				$repair_meta &&
				elodin_bridge_setup_wizard_element_meta_key_requires_array( $meta_key ) &&
				is_array( $expected_meta_value ) &&
				! is_array( $current_meta )
			) {
				update_post_meta( $candidate_id, $meta_key, $expected_meta_value );
			}
		}

		if ( $all_meta_match ) {
			return true;
		}
	}

	return false;
}

/**
 * Get completion state for setup wizard elements step.
 *
 * @param string $post_type Element post type.
 * @return array{complete:int,total:int,matched:int,missing_titles:array<int,string>}
 */
function elodin_bridge_get_setup_wizard_elements_completion( $post_type = '' ) {
	$post_type = '' !== (string) $post_type ? (string) $post_type : elodin_bridge_get_setup_wizard_element_post_type();
	$templates = elodin_bridge_get_setup_wizard_element_templates();
	$templates = is_array( $templates ) ? array_values( $templates ) : array();
	$source_hosts = elodin_bridge_get_setup_wizard_element_source_hosts();

	$total = 0;
	$matched = 0;
	$missing_titles = array();
	foreach ( $templates as $template ) {
		if ( ! is_array( $template ) ) {
			continue;
		}

		$title = sanitize_text_field( (string) ( $template['title'] ?? '' ) );
		if ( '' === $title ) {
			continue;
		}

		++$total;
		if ( '' !== $post_type && elodin_bridge_setup_wizard_element_has_exact_match( $post_type, $template, $source_hosts, true ) ) {
			++$matched;
		} else {
			$missing_titles[] = $title;
		}
	}

	return array(
		'complete'       => ( $total > 0 && $matched === $total ) ? 1 : 0,
		'total'          => $total,
		'matched'        => $matched,
		'missing_titles' => $missing_titles,
	);
}

/**
 * Repair previously imported element meta that was stored in an invalid shape.
 *
 * @return void
 */
function elodin_bridge_maybe_repair_setup_wizard_elements_meta() {
	$repair_version = (string) ELODIN_BRIDGE_VERSION;

	$post_type = elodin_bridge_get_setup_wizard_element_post_type();
	if ( '' === $post_type ) {
		return;
	}

	// This normalization pass is idempotent and lightweight (two bundled templates).
	// Run whenever the post type is available so previously missed repairs can self-heal.
	elodin_bridge_get_setup_wizard_elements_completion( $post_type );
	update_option( 'elodin_bridge_setup_wizard_elements_meta_repaired_version', $repair_version, false );
}
add_action( 'init', 'elodin_bridge_maybe_repair_setup_wizard_elements_meta', 50 );

/**
 * Mark one-time post-activation redirect.
 *
 * @return void
 */
function elodin_bridge_mark_setup_wizard_redirect() {
	update_option( ELODIN_BRIDGE_OPTION_SETUP_WIZARD_REDIRECT, 1, false );
}

/**
 * Redirect users to the main settings page after activation.
 *
 * @return void
 */
function elodin_bridge_maybe_redirect_to_setup_wizard() {
	if ( ! is_admin() || wp_doing_ajax() ) {
		return;
	}

	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	$should_redirect = (int) get_option( ELODIN_BRIDGE_OPTION_SETUP_WIZARD_REDIRECT, 0 );
	if ( 1 !== $should_redirect ) {
		return;
	}

	delete_option( ELODIN_BRIDGE_OPTION_SETUP_WIZARD_REDIRECT );

	if ( isset( $_GET['activate-multi'] ) ) {
		return;
	}

	if ( is_network_admin() ) {
		return;
	}

	wp_safe_redirect( elodin_bridge_get_settings_page_url() );
	exit;
}
add_action( 'admin_init', 'elodin_bridge_maybe_redirect_to_setup_wizard', 5 );

/**
 * Render setup wizard admin screen.
 *
 * @return void
 */
function elodin_bridge_render_setup_wizard_page() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	$notice = elodin_bridge_get_setup_wizard_notice();
	$theme_json_targets = elodin_bridge_get_setup_wizard_theme_json_targets();
	$theme_json_has_writable_target = false;
	foreach ( $theme_json_targets as $target ) {
		if ( ! empty( $target['writable'] ) ) {
			$theme_json_has_writable_target = true;
			break;
		}
	}
	$theme_json_default_target = 'stylesheet';
	if (
		! isset( $theme_json_targets['stylesheet'] ) ||
		empty( $theme_json_targets['stylesheet']['writable'] )
	) {
		foreach ( $theme_json_targets as $target_key => $target ) {
			if ( empty( $target['writable'] ) ) {
				continue;
			}

			$theme_json_default_target = (string) $target_key;
			break;
		}
	}
	$theme_json_download_url = trailingslashit( ELODIN_BRIDGE_URL ) . 'theme-defaults.json';
	$generatepress_available = elodin_bridge_is_generatepress_parent_theme();
	$theme_json_available = elodin_bridge_is_active_theme_json_available();
	$theme_json_completion = elodin_bridge_get_setup_wizard_theme_json_completion();
	$typography_completion = elodin_bridge_get_setup_wizard_typography_completion();
	$global_colors_completion = elodin_bridge_get_setup_wizard_global_colors_completion();
	$backup = get_option( ELODIN_BRIDGE_OPTION_SETUP_WIZARD_BACKUP, array() );
	$backup_timestamp = '';
	if ( is_array( $backup ) ) {
		$backup_timestamp = sanitize_text_field( (string) ( $backup['site_time'] ?? '' ) );
	}

	$element_post_type = elodin_bridge_get_setup_wizard_element_post_type();
	$elements_completion = elodin_bridge_get_setup_wizard_elements_completion( $element_post_type );

	$template_path = ELODIN_BRIDGE_DIR . '/inc/views/setup-wizard-page.php';
	if ( ! file_exists( $template_path ) ) {
		return;
	}

	require $template_path;
}

/**
 * Get writable theme.json targets for setup wizard.
 *
 * @return array<string,array{label:string,path:string,display_path:string,writable:int,exists:int}>
 */
function elodin_bridge_get_setup_wizard_theme_json_targets() {
	$targets = array();
	$target_paths = array(
		'stylesheet' => trailingslashit( get_stylesheet_directory() ) . 'theme.json',
		'template'   => trailingslashit( get_template_directory() ) . 'theme.json',
	);
	$same_directory = wp_normalize_path( $target_paths['stylesheet'] ) === wp_normalize_path( $target_paths['template'] );

	foreach ( $target_paths as $key => $path ) {
		if ( 'template' === $key && $same_directory ) {
			continue;
		}

		$label = 'stylesheet' === $key
			? __( 'Active child/stylesheet theme', 'elodin-bridge' )
			: __( 'Parent/template theme', 'elodin-bridge' );

		$exists = file_exists( $path ) ? 1 : 0;
		$dir_path = dirname( $path );
		$writable = 0;
		if ( $exists && is_writable( $path ) ) {
			$writable = 1;
		} elseif ( ! $exists && is_dir( $dir_path ) && is_writable( $dir_path ) ) {
			$writable = 1;
		}

		$display_path = wp_normalize_path( $path );
		$normalized_root = wp_normalize_path( ABSPATH );
		if ( 0 === strpos( $display_path, $normalized_root ) ) {
			$display_path = ltrim( substr( $display_path, strlen( $normalized_root ) ), '/' );
		}

		$targets[ $key ] = array(
			'label'        => $label,
			'path'         => $path,
			'display_path' => $display_path,
			'writable'     => $writable,
			'exists'       => $exists,
		);
	}

	return $targets;
}

/**
 * Handle setup wizard form submissions.
 *
 * @return void
 */
function elodin_bridge_handle_setup_wizard_action() {
	if ( 'POST' !== strtoupper( (string) ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) ) {
		wp_die( esc_html__( 'Invalid setup wizard request method.', 'elodin-bridge' ) );
	}

	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to manage setup wizard actions.', 'elodin-bridge' ) );
	}

	check_admin_referer( 'elodin_bridge_setup_wizard_action', '_elodin_bridge_setup_wizard_nonce' );

	$step = sanitize_key( wp_unslash( $_POST['step'] ?? '' ) );
	if ( '' === $step ) {
		elodin_bridge_set_setup_wizard_notice( 'error', __( 'No wizard step was provided.', 'elodin-bridge' ) );
		elodin_bridge_redirect_to_setup_wizard();
	}

	if ( 'theme_json' === $step ) {
		$mode = sanitize_key( wp_unslash( $_POST['theme_json_mode'] ?? 'merge' ) );
		$target = sanitize_key( wp_unslash( $_POST['theme_json_target'] ?? 'stylesheet' ) );
		$result = elodin_bridge_run_setup_wizard_theme_json_step( $mode, $target );
		if ( is_wp_error( $result ) ) {
			elodin_bridge_set_setup_wizard_notice( 'error', $result->get_error_message() );
		} else {
			elodin_bridge_set_setup_wizard_notice( 'success', __( 'Theme.json step completed successfully.', 'elodin-bridge' ) );
		}
		elodin_bridge_redirect_to_setup_wizard( 'step-theme-json' );
	}

	if ( 'typography' === $step ) {
		$result = elodin_bridge_run_setup_wizard_typography_step();
		if ( is_wp_error( $result ) ) {
			elodin_bridge_set_setup_wizard_notice( 'error', $result->get_error_message() );
		} else {
			elodin_bridge_set_setup_wizard_notice(
				'success',
				sprintf(
					/* translators: %d: selector count */
					__( 'Typography defaults applied for %d selector(s). Existing font families were preserved.', 'elodin-bridge' ),
					(int) $result
				)
			);
		}
		elodin_bridge_redirect_to_setup_wizard( 'step-typography' );
	}

	if ( 'global_colors' === $step ) {
		$global_colors_completion = elodin_bridge_get_setup_wizard_global_colors_completion();
		if ( elodin_bridge_is_generatepress_parent_theme() && ! empty( $global_colors_completion['complete'] ) ) {
			elodin_bridge_set_setup_wizard_notice(
				'warning',
				__( 'Step 3 is already complete. All bundled global color slugs were already present before this run, so no changes were made.', 'elodin-bridge' )
			);
			elodin_bridge_redirect_to_setup_wizard( 'step-global-colors' );
		}

		$result = elodin_bridge_run_setup_wizard_global_colors_step();
		if ( is_wp_error( $result ) ) {
			elodin_bridge_set_setup_wizard_notice( 'error', $result->get_error_message() );
		} else {
			elodin_bridge_set_setup_wizard_notice(
				'success',
				sprintf(
					/* translators: 1: merged defaults count, 2: preserved extras count */
					__( 'Global colors merged. Updated/added %1$d defaults and preserved %2$d existing custom color(s).', 'elodin-bridge' ),
					(int) ( $result['defaults'] ?? 0 ),
					(int) ( $result['preserved'] ?? 0 )
				)
			);
		}
		elodin_bridge_redirect_to_setup_wizard( 'step-global-colors' );
	}

	if ( 'elements' === $step ) {
		$element_post_type = elodin_bridge_get_setup_wizard_element_post_type();
		$elements_completion = elodin_bridge_get_setup_wizard_elements_completion( $element_post_type );
		if ( ! empty( $elements_completion['complete'] ) ) {
			elodin_bridge_set_setup_wizard_notice(
				'warning',
				__( 'Step 4 is already complete. Matching bundled elements already exist, so no new import was run.', 'elodin-bridge' )
			);
			elodin_bridge_redirect_to_setup_wizard( 'step-elements' );
		}

		$result = elodin_bridge_run_setup_wizard_elements_step();
		if ( is_wp_error( $result ) ) {
			elodin_bridge_set_setup_wizard_notice( 'error', $result->get_error_message() );
		} else {
			elodin_bridge_set_setup_wizard_notice(
				'success',
				sprintf(
					/* translators: %d: element count */
					__( 'Imported %d element template(s). Existing names were duplicated with suffixes when needed.', 'elodin-bridge' ),
					count( $result )
				)
			);
		}
		elodin_bridge_redirect_to_setup_wizard( 'step-elements' );
	}

	elodin_bridge_set_setup_wizard_notice( 'error', __( 'Unknown wizard step.', 'elodin-bridge' ) );
	elodin_bridge_redirect_to_setup_wizard();
}
add_action( 'admin_post_elodin_bridge_setup_wizard', 'elodin_bridge_handle_setup_wizard_action' );

/**
 * Redirect back to setup wizard page.
 *
 * @param string $anchor Optional hash anchor.
 * @return void
 */
function elodin_bridge_redirect_to_setup_wizard( $anchor = '' ) {
	wp_safe_redirect( elodin_bridge_get_setup_wizard_url( (string) $anchor ) );
	exit;
}

/**
 * Backup current GeneratePress settings before setup imports.
 *
 * @return array<string,mixed>
 */
function elodin_bridge_backup_setup_wizard_generatepress_settings() {
	$backup = array(
		'site_time'         => current_time( 'mysql' ),
		'generated_at_gmt'  => gmdate( 'c' ),
		'generate_settings' => get_option( 'generate_settings', array() ),
	);

	update_option( ELODIN_BRIDGE_OPTION_SETUP_WIZARD_BACKUP, $backup, false );
	return $backup;
}

/**
 * Clear GeneratePress dynamic CSS cache after settings writes.
 *
 * @return void
 */
function elodin_bridge_clear_setup_wizard_generatepress_dynamic_css_cache() {
	delete_option( 'generate_dynamic_css_output' );
	delete_option( 'generate_dynamic_css_cached_version' );

	$dynamic_css_data = get_option( 'generatepress_dynamic_css_data', array() );
	$dynamic_css_data = is_array( $dynamic_css_data ) ? $dynamic_css_data : array();
	if ( isset( $dynamic_css_data['updated_time'] ) ) {
		unset( $dynamic_css_data['updated_time'] );
	}
	update_option( 'generatepress_dynamic_css_data', $dynamic_css_data, false );
}

/**
 * Set a nested array value by path.
 *
 * @param array<string,mixed> $data Data array.
 * @param array<int,string>   $path Nested key path.
 * @param mixed               $value Value to set.
 * @return array<string,mixed>
 */
function elodin_bridge_setup_wizard_set_nested_value( $data, $path, $value ) {
	if ( ! is_array( $data ) ) {
		$data = array();
	}

	$ref =& $data;
	$last_index = count( $path ) - 1;
	foreach ( $path as $index => $segment ) {
		$segment = (string) $segment;
		if ( '' === $segment ) {
			continue;
		}

		if ( $index === $last_index ) {
			$ref[ $segment ] = $value;
			continue;
		}

		if ( ! isset( $ref[ $segment ] ) || ! is_array( $ref[ $segment ] ) ) {
			$ref[ $segment ] = array();
		}

		$ref =& $ref[ $segment ];
	}

	return $data;
}

/**
 * Read a nested array value by path.
 *
 * @param array<string,mixed> $data Data array.
 * @param array<int,string>   $path Nested key path.
 * @return mixed
 */
function elodin_bridge_setup_wizard_get_nested_value( $data, $path ) {
	if ( ! is_array( $data ) ) {
		return null;
	}

	$current = $data;
	foreach ( $path as $segment ) {
		$segment = (string) $segment;
		if ( '' === $segment || ! is_array( $current ) || ! array_key_exists( $segment, $current ) ) {
			return null;
		}

		$current = $current[ $segment ];
	}

	return $current;
}

/**
 * Get theme.json decode status details for setup operations.
 *
 * @param string $path theme.json path.
 * @return array{exists:int,readable:int,valid_json:int,data:array<string,mixed>,json_error:string}
 */
function elodin_bridge_get_setup_wizard_theme_json_decode_status( $path ) {
	$status = array(
		'exists'     => 0,
		'readable'   => 0,
		'valid_json' => 0,
		'data'       => array(),
		'json_error' => '',
	);

	$path = (string) $path;
	if ( '' === $path ) {
		return $status;
	}

	if ( file_exists( $path ) ) {
		$status['exists'] = 1;
	}
	if ( is_readable( $path ) ) {
		$status['readable'] = 1;
	}
	if ( empty( $status['exists'] ) ) {
		return $status;
	}
	if ( empty( $status['readable'] ) ) {
		$status['json_error'] = 'not-readable';
		return $status;
	}

	$raw = file_get_contents( $path );
	if ( false === $raw ) {
		$status['json_error'] = 'read-failed';
		return $status;
	}

	if ( '' === trim( $raw ) ) {
		$status['json_error'] = 'empty-file';
		return $status;
	}

	$decoded = json_decode( $raw, true );
	if ( ! is_array( $decoded ) ) {
		$status['json_error'] = (string) json_last_error_msg();
		return $status;
	}

	$status['valid_json'] = 1;
	$status['data'] = $decoded;
	return $status;
}

/**
 * Backup an existing theme.json file before overwriting.
 *
 * @param string $path theme.json path.
 * @return true|WP_Error
 */
function elodin_bridge_backup_setup_wizard_theme_json_file( $path ) {
	$path = (string) $path;
	if ( '' === $path || ! file_exists( $path ) ) {
		return true;
	}

	if ( ! is_readable( $path ) ) {
		return new WP_Error( 'elodin_setup_theme_json_backup_not_readable', __( 'Existing theme.json could not be read for backup.', 'elodin-bridge' ) );
	}

	$raw = file_get_contents( $path );
	if ( false === $raw ) {
		return new WP_Error( 'elodin_setup_theme_json_backup_read_failed', __( 'Existing theme.json could not be read for backup.', 'elodin-bridge' ) );
	}

	$backup_path = $path . '.elodin-bridge.bak';
	$bytes = file_put_contents( $backup_path, $raw );
	if ( false === $bytes ) {
		return new WP_Error(
			'elodin_setup_theme_json_backup_write_failed',
			__( 'Could not create a theme.json backup file before writing.', 'elodin-bridge' )
		);
	}

	return true;
}

/**
 * Merge selected plugin default sections into existing theme.json data.
 *
 * @param array<string,mixed> $existing Existing theme.json data.
 * @param array<string,mixed> $defaults Plugin defaults data.
 * @return array<string,mixed>
 */
function elodin_bridge_merge_setup_wizard_theme_json_defaults( $existing, $defaults ) {
	$existing = is_array( $existing ) ? $existing : array();
	$defaults = is_array( $defaults ) ? $defaults : array();
	$merged = $existing;

	$paths = array(
		array( 'settings', 'spacing', 'spacingSizes' ),
		array( 'settings', 'typography', 'fontSizes' ),
		array( 'settings', 'color', 'palette' ),
		array( 'styles', 'blocks', 'core/quote' ),
		array( 'styles', 'blocks', 'core/separator' ),
		array( 'styles', 'blocks', 'core/list' ),
		array( 'styles', 'blocks', 'core/code' ),
		array( 'styles', 'blocks', 'core/table' ),
	);

	foreach ( $paths as $path ) {
		$value = elodin_bridge_setup_wizard_get_nested_value( $defaults, $path );
		if ( null === $value ) {
			continue;
		}
		$merged = elodin_bridge_setup_wizard_set_nested_value( $merged, $path, $value );
	}

	if ( empty( $merged['version'] ) && ! empty( $defaults['version'] ) ) {
		$merged['version'] = (int) $defaults['version'];
	}

	return $merged;
}

/**
 * Run theme.json setup step.
 *
 * @param string $mode merge|replace.
 * @param string $target_key target key.
 * @return true|WP_Error
 */
function elodin_bridge_run_setup_wizard_theme_json_step( $mode, $target_key ) {
	$mode = in_array( $mode, array( 'merge', 'replace' ), true ) ? $mode : 'merge';
	$targets = elodin_bridge_get_setup_wizard_theme_json_targets();
	if ( ! isset( $targets[ $target_key ] ) ) {
		$target_key = 'stylesheet';
	}

	if ( ! isset( $targets[ $target_key ] ) ) {
		return new WP_Error( 'elodin_setup_target_missing', __( 'No writable theme target could be determined for theme.json.', 'elodin-bridge' ) );
	}

	$target = $targets[ $target_key ];
	$path = (string) ( $target['path'] ?? '' );
	if ( '' === $path ) {
		return new WP_Error( 'elodin_setup_target_invalid', __( 'Theme.json write path is missing.', 'elodin-bridge' ) );
	}

	if ( empty( $target['writable'] ) ) {
		return new WP_Error( 'elodin_setup_target_not_writable', __( 'Theme.json target is not writable. Use the download link and place the file manually in the active theme.', 'elodin-bridge' ) );
	}

	$defaults = elodin_bridge_get_plugin_theme_defaults_data();
	if ( empty( $defaults ) ) {
		return new WP_Error( 'elodin_setup_theme_defaults_missing', __( 'Plugin theme-defaults.json could not be read.', 'elodin-bridge' ) );
	}

	$next_data = array();
	if ( 'replace' === $mode ) {
		$next_data = $defaults;
	} else {
		$decode_status = elodin_bridge_get_setup_wizard_theme_json_decode_status( $path );
		if ( ! empty( $decode_status['exists'] ) && empty( $decode_status['valid_json'] ) ) {
			return new WP_Error(
				'elodin_setup_theme_json_merge_invalid_json',
				__( 'Theme.json merge mode requires a valid existing JSON file. Repair the file or use replace mode.', 'elodin-bridge' )
			);
		}

		$existing = ! empty( $decode_status['data'] ) && is_array( $decode_status['data'] )
			? $decode_status['data']
			: array();
		$next_data = elodin_bridge_merge_setup_wizard_theme_json_defaults( $existing, $defaults );
	}

	$encoded = wp_json_encode( $next_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
	if ( ! is_string( $encoded ) || '' === $encoded ) {
		return new WP_Error( 'elodin_setup_theme_json_encode_failed', __( 'Failed to encode theme.json data for writing.', 'elodin-bridge' ) );
	}

	$directory = dirname( $path );
	if ( ! is_dir( $directory ) && ! wp_mkdir_p( $directory ) ) {
		return new WP_Error( 'elodin_setup_theme_json_dir_failed', __( 'Could not create theme directory for theme.json.', 'elodin-bridge' ) );
	}

	$backup_result = elodin_bridge_backup_setup_wizard_theme_json_file( $path );
	if ( is_wp_error( $backup_result ) ) {
		return $backup_result;
	}

	$bytes = file_put_contents( $path, $encoded . "\n" );
	if ( false === $bytes ) {
		return new WP_Error( 'elodin_setup_theme_json_write_failed', __( 'Could not write theme.json to the selected target.', 'elodin-bridge' ) );
	}

	update_option( ELODIN_BRIDGE_OPTION_THEME_JSON_SOURCE_MODE, 'theme', false );

	return true;
}

/**
 * Build lookup map of typography selector indexes.
 *
 * @param array<int,mixed> $rows Typography rows.
 * @return array<string,int>
 */
function elodin_bridge_get_setup_wizard_typography_selector_index( $rows ) {
	$index = array();
	foreach ( $rows as $row_index => $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}

		$selector = sanitize_key( (string) ( $row['selector'] ?? '' ) );
		if ( '' === $selector ) {
			continue;
		}

		$index[ $selector ] = (int) $row_index;
	}
	return $index;
}

/**
 * Run typography setup step.
 *
 * @return int|WP_Error
 */
function elodin_bridge_run_setup_wizard_typography_step() {
	if ( ! elodin_bridge_is_generatepress_parent_theme() ) {
		return new WP_Error( 'elodin_setup_not_generatepress', __( 'Typography import requires GeneratePress as the active parent theme.', 'elodin-bridge' ) );
	}

	$defaults = elodin_bridge_get_setup_wizard_typography_defaults();
	if ( empty( $defaults ) ) {
		return new WP_Error( 'elodin_setup_typography_defaults_missing', __( 'No setup typography defaults were found.', 'elodin-bridge' ) );
	}

	elodin_bridge_backup_setup_wizard_generatepress_settings();

	$generate_settings = get_option( 'generate_settings', array() );
	$generate_settings = is_array( $generate_settings ) ? $generate_settings : array();
	$existing_rows = isset( $generate_settings['typography'] ) && is_array( $generate_settings['typography'] )
		? array_values( $generate_settings['typography'] )
		: array();

	$selector_index = elodin_bridge_get_setup_wizard_typography_selector_index( $existing_rows );
	$applied_count = 0;

	foreach ( $defaults as $default_row ) {
		if ( ! is_array( $default_row ) ) {
			continue;
		}

		$selector = sanitize_key( (string) ( $default_row['selector'] ?? '' ) );
		if ( '' === $selector ) {
			continue;
		}

		$current_row = array();
		if ( isset( $selector_index[ $selector ] ) ) {
			$current_row = is_array( $existing_rows[ $selector_index[ $selector ] ] )
				? $existing_rows[ $selector_index[ $selector ] ]
				: array();
		}

		$merged_row = array_merge( $current_row, $default_row );
		$merged_row['selector'] = $selector;

		// Preserve existing font family choices exactly; do not import font families.
		$merged_row['fontFamily'] = isset( $current_row['fontFamily'] )
			? (string) $current_row['fontFamily']
			: '';

		if ( isset( $selector_index[ $selector ] ) ) {
			$existing_rows[ $selector_index[ $selector ] ] = $merged_row;
		} else {
			$existing_rows[] = $merged_row;
			$selector_index[ $selector ] = count( $existing_rows ) - 1;
		}

		++$applied_count;
	}

	$generate_settings['typography'] = array_values( $existing_rows );
	update_option( 'generate_settings', $generate_settings, false );
	elodin_bridge_clear_setup_wizard_generatepress_dynamic_css_cache();

	return $applied_count;
}

/**
 * Run global colors setup step.
 *
 * @return array{defaults:int,preserved:int}|WP_Error
 */
function elodin_bridge_run_setup_wizard_global_colors_step() {
	if ( ! elodin_bridge_is_generatepress_parent_theme() ) {
		return new WP_Error( 'elodin_setup_not_generatepress', __( 'Global color import requires GeneratePress as the active parent theme.', 'elodin-bridge' ) );
	}

	$defaults = elodin_bridge_get_setup_wizard_global_color_defaults();
	if ( empty( $defaults ) ) {
		return new WP_Error( 'elodin_setup_global_colors_defaults_missing', __( 'No setup global color defaults were found.', 'elodin-bridge' ) );
	}

	elodin_bridge_backup_setup_wizard_generatepress_settings();

	$generate_settings = get_option( 'generate_settings', array() );
	$generate_settings = is_array( $generate_settings ) ? $generate_settings : array();
	$existing = isset( $generate_settings['global_colors'] ) && is_array( $generate_settings['global_colors'] )
		? array_values( $generate_settings['global_colors'] )
		: array();

	$existing_by_slug = array();
	$existing_without_slug = array();
	foreach ( $existing as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}

		$slug = sanitize_key( (string) ( $row['slug'] ?? '' ) );
		if ( '' === $slug ) {
			$existing_without_slug[] = $row;
			continue;
		}

		$existing_by_slug[ $slug ] = $row;
	}

	$merged = array();
	$defaults_count = 0;
	foreach ( $defaults as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}

		$slug = sanitize_key( (string) ( $row['slug'] ?? '' ) );
		if ( '' === $slug ) {
			continue;
		}

		$normalized_default = array(
			'name'  => sanitize_text_field( (string) ( $row['name'] ?? $slug ) ),
			'slug'  => $slug,
			'color' => sanitize_text_field( (string) ( $row['color'] ?? '' ) ),
		);

		if ( isset( $existing_by_slug[ $slug ] ) && is_array( $existing_by_slug[ $slug ] ) ) {
			$merged[] = array_merge( $existing_by_slug[ $slug ], $normalized_default );
			unset( $existing_by_slug[ $slug ] );
		} else {
			$merged[] = $normalized_default;
		}

		++$defaults_count;
	}

	$preserved_count = count( $existing_by_slug ) + count( $existing_without_slug );
	if ( ! empty( $existing_by_slug ) ) {
		foreach ( $existing_by_slug as $row ) {
			$merged[] = $row;
		}
	}
	if ( ! empty( $existing_without_slug ) ) {
		foreach ( $existing_without_slug as $row ) {
			$merged[] = $row;
		}
	}

	$generate_settings['global_colors'] = array_values( $merged );
	update_option( 'generate_settings', $generate_settings, false );
	elodin_bridge_clear_setup_wizard_generatepress_dynamic_css_cache();

	return array(
		'defaults'  => $defaults_count,
		'preserved' => $preserved_count,
	);
}

/**
 * Get a unique element title by appending import suffixes.
 *
 * @param string $base_title Base title.
 * @param string $post_type Element post type.
 * @return string
 */
function elodin_bridge_get_setup_wizard_unique_element_title( $base_title, $post_type ) {
	global $wpdb;

	$base_title = sanitize_text_field( (string) $base_title );
	if ( '' === $base_title ) {
		$base_title = __( 'Imported Element', 'elodin-bridge' );
	}

	$attempt = 0;
	do {
		++$attempt;
		if ( 1 === $attempt ) {
			$candidate = $base_title;
		} elseif ( 2 === $attempt ) {
			$candidate = $base_title . ' (Imported)';
		} else {
			$candidate = $base_title . ' (Imported ' . (string) ( $attempt - 1 ) . ')';
		}

		$found_id = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_title = %s AND post_status != 'trash' LIMIT 1",
				$post_type,
				$candidate
			)
		);
	} while ( $found_id > 0 && $attempt < 200 );

	return $candidate;
}

/**
 * Convert first-party absolute URLs in imported element markup to relative paths.
 *
 * @param string            $content Element markup content.
 * @param array<int,string> $source_hosts Allowed first-party hosts.
 * @return string
 */
function elodin_bridge_convert_setup_wizard_element_urls_to_relative( $content, $source_hosts ) {
	$content = (string) $content;
	if ( '' === $content || empty( $source_hosts ) ) {
		return $content;
	}

	$allowed_hosts = array();
	foreach ( $source_hosts as $host ) {
		$host = strtolower( trim( (string) $host ) );
		if ( '' === $host ) {
			continue;
		}

		$allowed_hosts[ $host ] = true;
	}

	if ( empty( $allowed_hosts ) ) {
		return $content;
	}

	return preg_replace_callback(
		'/https?:\/\/[^\s"\'<>()]+/i',
		static function ( $matches ) use ( $allowed_hosts ) {
			$url = (string) ( $matches[0] ?? '' );
			if ( '' === $url ) {
				return $url;
			}

			$host = wp_parse_url( $url, PHP_URL_HOST );
			$host = strtolower( trim( (string) $host ) );
			if ( '' === $host || ! isset( $allowed_hosts[ $host ] ) ) {
				return $url;
			}

			$path = (string) wp_parse_url( $url, PHP_URL_PATH );
			if ( '' === $path ) {
				$path = '/';
			} elseif ( '/' !== substr( $path, 0, 1 ) ) {
				$path = '/' . $path;
			}

			$query = wp_parse_url( $url, PHP_URL_QUERY );
			$fragment = wp_parse_url( $url, PHP_URL_FRAGMENT );
			$relative = $path;
			if ( is_string( $query ) && '' !== $query ) {
				$relative .= '?' . $query;
			}
			if ( is_string( $fragment ) && '' !== $fragment ) {
				$relative .= '#' . $fragment;
			}

			return $relative;
		},
		$content
	);
}

/**
 * Run element import setup step.
 *
 * @return array<int,int>|WP_Error
 */
function elodin_bridge_run_setup_wizard_elements_step() {
	$post_type = elodin_bridge_get_setup_wizard_element_post_type();

	if ( '' === $post_type ) {
		return new WP_Error( 'elodin_setup_elements_post_type_missing', __( 'Element import requires GeneratePress Elements (gp_elements post type).', 'elodin-bridge' ) );
	}

	$templates = elodin_bridge_get_setup_wizard_element_templates();
	if ( empty( $templates ) ) {
		return new WP_Error( 'elodin_setup_elements_templates_missing', __( 'No element templates were found for import.', 'elodin-bridge' ) );
	}

	$source_hosts = elodin_bridge_get_setup_wizard_element_source_hosts();
	$inserted_ids = array();
	foreach ( $templates as $template ) {
		if ( ! is_array( $template ) ) {
			continue;
		}

		$title = sanitize_text_field( (string) ( $template['title'] ?? '' ) );
		if ( '' === $title ) {
			continue;
		}

		if ( elodin_bridge_setup_wizard_element_has_exact_match( $post_type, $template, $source_hosts, true ) ) {
			continue;
		}

		$unique_title = elodin_bridge_get_setup_wizard_unique_element_title( $title, $post_type );
		$content = (string) ( $template['content'] ?? '' );
		$content = elodin_bridge_convert_setup_wizard_element_urls_to_relative( $content, $source_hosts );
		$status = sanitize_key( (string) ( $template['status'] ?? 'publish' ) );
		$status = in_array( $status, array( 'publish', 'draft', 'private' ), true ) ? $status : 'publish';

		$inserted_id = wp_insert_post(
			array(
				'post_type'    => $post_type,
				'post_status'  => $status,
				'post_title'   => $unique_title,
				'post_name'    => sanitize_title( $unique_title ),
				'post_content' => wp_slash( $content ),
			),
			true
		);

		if ( is_wp_error( $inserted_id ) ) {
			return $inserted_id;
		}

		$inserted_id = (int) $inserted_id;
		$meta = isset( $template['meta'] ) && is_array( $template['meta'] ) ? $template['meta'] : array();
		foreach ( $meta as $meta_key => $meta_value ) {
			$meta_key = (string) $meta_key;
			if ( '' === $meta_key ) {
				continue;
			}

			$meta_value = elodin_bridge_setup_wizard_normalize_element_meta_value( $meta_key, $meta_value );
			update_post_meta( $inserted_id, $meta_key, $meta_value );
		}

		$inserted_ids[] = $inserted_id;
	}

	if ( empty( $inserted_ids ) ) {
		return new WP_Error( 'elodin_setup_elements_none_imported', __( 'No elements were imported from the bundled templates.', 'elodin-bridge' ) );
	}

	return $inserted_ids;
}

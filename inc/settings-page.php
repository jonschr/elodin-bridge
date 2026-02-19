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
 * Check if heading/paragraph style overrides are enabled.
 *
 * @return bool
 */
function elodin_bridge_is_heading_paragraph_overrides_enabled() {
	return (bool) get_option( ELODIN_BRIDGE_OPTION_ENABLE_HEADING_PARAGRAPH_OVERRIDES, 1 );
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
			<?php settings_fields( 'elodin_bridge_settings' ); ?>

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

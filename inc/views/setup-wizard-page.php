<?php
$theme_json_is_complete = ! empty( $theme_json_completion['complete'] );
$theme_json_button_label = $theme_json_is_complete
	? __( 'Run Step 1 Again', 'elodin-bridge' )
	: __( 'Run Step 1', 'elodin-bridge' );

$typography_is_complete = ! empty( $typography_completion['complete'] );
$typography_is_disabled = ! $generatepress_available;
$typography_button_label = $typography_is_complete
	? __( 'Run Step 2 Again', 'elodin-bridge' )
	: __( 'Run Step 2', 'elodin-bridge' );

$global_colors_is_complete = ! empty( $global_colors_completion['complete'] );
$global_colors_is_locked = $generatepress_available && $global_colors_is_complete;
$global_colors_is_disabled = ! $generatepress_available || $global_colors_is_locked;
$global_colors_button_label = $global_colors_is_locked
	? __( 'Step 3 Complete', 'elodin-bridge' )
	: __( 'Run Step 3', 'elodin-bridge' );

$elements_is_complete = ! empty( $elements_completion['complete'] );
$elements_is_locked = '' !== $element_post_type && $elements_is_complete;
$elements_is_disabled = '' === $element_post_type || $elements_is_locked;
$elements_button_label = $elements_is_locked
	? __( 'Step 4 Complete', 'elodin-bridge' )
	: __( 'Run Step 4', 'elodin-bridge' );
?>
<div class="wrap elodin-bridge-admin elodin-bridge-setup-wizard">
	<div class="elodin-bridge-admin__hero">
		<h1 class="elodin-bridge-admin__title">
			<?php esc_html_e( 'Elodin Bridge Setup Wizard', 'elodin-bridge' ); ?>
			<span class="elodin-bridge-admin__version"><?php echo esc_html( sprintf( 'v%s', ELODIN_BRIDGE_VERSION ) ); ?></span>
		</h1>
		<p class="elodin-bridge-admin__intro">
			<?php esc_html_e( 'Run setup steps in order. Step 1 and Step 2 are rerunnable; Step 3 and Step 4 lock when defaults are already in place.', 'elodin-bridge' ); ?>
		</p>
		<nav class="elodin-bridge-admin__hero-actions" aria-label="<?php esc_attr_e( 'Bridge page navigation', 'elodin-bridge' ); ?>">
			<a class="button button-secondary" href="<?php echo esc_url( elodin_bridge_get_settings_page_url() ); ?>">
				<?php esc_html_e( 'Main Settings', 'elodin-bridge' ); ?>
			</a>
			<a class="button button-primary" href="<?php echo esc_url( elodin_bridge_get_setup_wizard_url() ); ?>">
				<?php esc_html_e( 'Setup Wizard', 'elodin-bridge' ); ?>
			</a>
		</nav>
	</div>

	<?php if ( ! empty( $notice['message'] ) ) : ?>
		<?php $notice_class = 'notice-info'; ?>
		<?php if ( 'success' === $notice['type'] ) : ?>
			<?php $notice_class = 'notice-success'; ?>
		<?php elseif ( 'error' === $notice['type'] ) : ?>
			<?php $notice_class = 'notice-error'; ?>
		<?php elseif ( 'warning' === $notice['type'] ) : ?>
			<?php $notice_class = 'notice-warning'; ?>
		<?php endif; ?>
		<div class="notice <?php echo esc_attr( $notice_class ); ?> is-dismissible">
			<p><?php echo esc_html( $notice['message'] ); ?></p>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $backup_timestamp ) ) : ?>
		<p class="elodin-bridge-admin__note elodin-bridge-admin__note--warning">
			<?php
			echo esc_html(
				sprintf(
					/* translators: %s: date time string */
					__( 'A GeneratePress settings backup was saved at %s before the last import.', 'elodin-bridge' ),
					$backup_timestamp
				)
			);
			?>
		</p>
	<?php endif; ?>

	<div class="elodin-bridge-admin__cards">
		<div id="step-theme-json" class="elodin-bridge-admin__card elodin-bridge-admin__card--wide">
			<div class="elodin-bridge-admin__feature">
				<div class="elodin-bridge-admin__feature-heading-row">
					<span class="elodin-bridge-admin__feature-title"><?php esc_html_e( 'Step 1: Theme.json setup', 'elodin-bridge' ); ?></span>
					<span class="elodin-bridge-admin__status-chip <?php echo $theme_json_is_complete ? 'is-success' : ''; ?>">
						<?php echo esc_html( $theme_json_is_complete ? __( 'Complete', 'elodin-bridge' ) : __( 'Pending', 'elodin-bridge' ) ); ?>
					</span>
				</div>
				<div class="elodin-bridge-admin__feature-body">
					<p class="elodin-bridge-admin__description">
						<?php esc_html_e( 'Write plugin defaults to theme.json so spacing presets, font sizes, and block styles are available site-wide.', 'elodin-bridge' ); ?>
					</p>
					<p class="elodin-bridge-admin__note">
						<?php
						echo esc_html(
							sprintf(
								/* translators: 1: matched section count, 2: total section count */
								__( 'Detected required sections: %1$d of %2$d.', 'elodin-bridge' ),
								(int) ( $theme_json_completion['matched'] ?? 0 ),
								(int) ( $theme_json_completion['total'] ?? 0 )
							)
						);
						?>
					</p>
					<p class="elodin-bridge-admin__note">
						<?php esc_html_e( 'Merge updates only key sections. Replace writes the full plugin defaults file.', 'elodin-bridge' ); ?>
					</p>
					<p class="elodin-bridge-admin__note">
						<?php
						echo wp_kses_post(
							sprintf(
								/* translators: %s: plugin defaults download URL */
								__( '<a href="%s" download="theme.json">Download plugin defaults as theme.json</a> is always available, even if file writes fail.', 'elodin-bridge' ),
								esc_url( $theme_json_download_url )
							)
						);
						?>
					</p>

					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="elodin-bridge-setup-wizard__step-form">
						<input type="hidden" name="action" value="elodin_bridge_setup_wizard" />
						<input type="hidden" name="step" value="theme_json" />
						<?php wp_nonce_field( 'elodin_bridge_setup_wizard_action', '_elodin_bridge_setup_wizard_nonce' ); ?>

						<fieldset class="elodin-bridge-admin__source-mode-group">
							<legend class="elodin-bridge-admin__subheading"><?php esc_html_e( 'Write mode', 'elodin-bridge' ); ?></legend>
							<div class="elodin-bridge-admin__source-mode-list">
								<label class="elodin-bridge-admin__source-mode-item" for="elodin-bridge-setup-theme-json-mode-merge">
									<input type="radio" id="elodin-bridge-setup-theme-json-mode-merge" name="theme_json_mode" value="merge" checked="checked" />
									<span><?php esc_html_e( 'Merge defaults into existing theme.json (recommended)', 'elodin-bridge' ); ?></span>
								</label>
								<label class="elodin-bridge-admin__source-mode-item" for="elodin-bridge-setup-theme-json-mode-replace">
									<input type="radio" id="elodin-bridge-setup-theme-json-mode-replace" name="theme_json_mode" value="replace" />
									<span><?php esc_html_e( 'Replace target theme.json with plugin defaults', 'elodin-bridge' ); ?></span>
								</label>
							</div>
						</fieldset>

						<fieldset class="elodin-bridge-admin__source-mode-group">
							<legend class="elodin-bridge-admin__subheading"><?php esc_html_e( 'Target file', 'elodin-bridge' ); ?></legend>
							<div class="elodin-bridge-admin__source-mode-list">
								<?php foreach ( $theme_json_targets as $target_key => $target ) : ?>
									<?php $target_disabled = empty( $target['writable'] ); ?>
									<label class="elodin-bridge-admin__source-mode-item" for="elodin-bridge-setup-theme-json-target-<?php echo esc_attr( $target_key ); ?>">
									<input
										type="radio"
										id="elodin-bridge-setup-theme-json-target-<?php echo esc_attr( $target_key ); ?>"
										name="theme_json_target"
										value="<?php echo esc_attr( $target_key ); ?>"
										<?php checked( $theme_json_default_target === $target_key ); ?>
										<?php disabled( $target_disabled ); ?>
									/>
										<span>
											<?php echo esc_html( $target['label'] ); ?>
											<code><?php echo esc_html( $target['display_path'] ); ?></code>
											<?php if ( $target_disabled ) : ?>
												<?php esc_html_e( '(not writable)', 'elodin-bridge' ); ?>
											<?php endif; ?>
										</span>
									</label>
								<?php endforeach; ?>
							</div>
						</fieldset>

						<?php if ( ! $theme_json_available ) : ?>
							<p class="elodin-bridge-admin__note elodin-bridge-admin__note--warning">
								<?php esc_html_e( 'No active theme.json file was found. This step can create one if the selected target is writable.', 'elodin-bridge' ); ?>
							</p>
						<?php endif; ?>
						<?php if ( ! $theme_json_has_writable_target ) : ?>
							<p class="elodin-bridge-admin__note elodin-bridge-admin__note--warning">
								<?php esc_html_e( 'No writable theme.json target was detected. Use the download link and add the file manually.', 'elodin-bridge' ); ?>
							</p>
						<?php endif; ?>

						<p>
							<button type="submit" class="button button-primary" <?php disabled( ! $theme_json_has_writable_target ); ?>><?php echo esc_html( $theme_json_button_label ); ?></button>
						</p>
					</form>
				</div>
			</div>
		</div>

		<div id="step-typography" class="elodin-bridge-admin__card">
			<div class="elodin-bridge-admin__feature <?php echo ! $generatepress_available ? 'is-unavailable' : ''; ?>">
				<div class="elodin-bridge-admin__feature-heading-row">
					<span class="elodin-bridge-admin__feature-title"><?php esc_html_e( 'Step 2: GeneratePress typography defaults', 'elodin-bridge' ); ?></span>
					<span class="elodin-bridge-admin__status-chip <?php echo $typography_is_complete ? 'is-success' : ''; ?>">
						<?php echo esc_html( $typography_is_complete ? __( 'Complete', 'elodin-bridge' ) : __( 'Pending', 'elodin-bridge' ) ); ?>
					</span>
				</div>
					<div class="elodin-bridge-admin__feature-body">
						<p class="elodin-bridge-admin__description">
							<?php esc_html_e( 'Applies selected selector defaults while preserving existing font-family values.', 'elodin-bridge' ); ?>
						</p>
						<p class="elodin-bridge-admin__note">
							<?php
							echo esc_html(
								sprintf(
									/* translators: 1: matched selector count, 2: total selector count */
									__( 'Detected selectors: %1$d of %2$d.', 'elodin-bridge' ),
									(int) ( $typography_completion['matched'] ?? 0 ),
									(int) ( $typography_completion['total'] ?? 0 )
								)
							);
							?>
						</p>
					<?php if ( ! $generatepress_available ) : ?>
						<p class="elodin-bridge-admin__note elodin-bridge-admin__note--warning">
							<?php esc_html_e( 'GeneratePress parent theme is required for this step.', 'elodin-bridge' ); ?>
						</p>
					<?php endif; ?>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="elodin-bridge-setup-wizard__step-form">
						<input type="hidden" name="action" value="elodin_bridge_setup_wizard" />
						<input type="hidden" name="step" value="typography" />
						<?php wp_nonce_field( 'elodin_bridge_setup_wizard_action', '_elodin_bridge_setup_wizard_nonce' ); ?>
						<p>
							<button type="submit" class="button button-primary" <?php disabled( $typography_is_disabled ); ?>><?php echo esc_html( $typography_button_label ); ?></button>
						</p>
					</form>
				</div>
			</div>
		</div>

		<div id="step-global-colors" class="elodin-bridge-admin__card">
			<div class="elodin-bridge-admin__feature <?php echo ! $generatepress_available ? 'is-unavailable' : ''; ?>">
				<div class="elodin-bridge-admin__feature-heading-row">
					<span class="elodin-bridge-admin__feature-title"><?php esc_html_e( 'Step 3: Global color palette', 'elodin-bridge' ); ?></span>
					<span class="elodin-bridge-admin__status-chip <?php echo $global_colors_is_complete ? 'is-success' : ''; ?>">
						<?php echo esc_html( $global_colors_is_complete ? __( 'Complete', 'elodin-bridge' ) : __( 'Pending', 'elodin-bridge' ) ); ?>
					</span>
				</div>
					<div class="elodin-bridge-admin__feature-body">
						<p class="elodin-bridge-admin__description">
							<?php esc_html_e( 'Merge the Ollie color system into GeneratePress color settings, preserving any existing colors already set through GeneratePress. Site owners will probably want to review that list and migrate older colors to their corresponding slug next.', 'elodin-bridge' ); ?>
						</p>
						<p class="elodin-bridge-admin__note">
							<?php
							echo esc_html(
								sprintf(
									/* translators: 1: matched color count, 2: total color count */
									__( 'Detected required color slugs: %1$d of %2$d.', 'elodin-bridge' ),
									(int) ( $global_colors_completion['matched'] ?? 0 ),
									(int) ( $global_colors_completion['total'] ?? 0 )
								)
							);
							?>
						</p>
					<?php if ( ! $generatepress_available ) : ?>
						<p class="elodin-bridge-admin__note elodin-bridge-admin__note--warning">
							<?php esc_html_e( 'GeneratePress parent theme is required for this step.', 'elodin-bridge' ); ?>
						</p>
					<?php elseif ( $global_colors_is_locked ) : ?>
						<p class="elodin-bridge-admin__note elodin-bridge-admin__note--warning">
							<?php esc_html_e( 'All bundled global color slugs were already present before this run, so this step is locked to avoid accidental overwrites.', 'elodin-bridge' ); ?>
						</p>
					<?php else : ?>
						<?php $missing_global_color_slugs = isset( $global_colors_completion['missing_slugs'] ) && is_array( $global_colors_completion['missing_slugs'] ) ? $global_colors_completion['missing_slugs'] : array(); ?>
						<?php if ( ! empty( $missing_global_color_slugs ) ) : ?>
							<p class="elodin-bridge-admin__note">
								<?php
								echo esc_html(
									sprintf(
										/* translators: %s: comma-separated color slugs */
										__( 'Missing slugs: %s', 'elodin-bridge' ),
										implode( ', ', $missing_global_color_slugs )
									)
								);
								?>
							</p>
						<?php endif; ?>
					<?php endif; ?>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="elodin-bridge-setup-wizard__step-form">
						<input type="hidden" name="action" value="elodin_bridge_setup_wizard" />
						<input type="hidden" name="step" value="global_colors" />
						<?php wp_nonce_field( 'elodin_bridge_setup_wizard_action', '_elodin_bridge_setup_wizard_nonce' ); ?>
						<p>
							<button type="submit" class="button button-primary" <?php disabled( $global_colors_is_disabled ); ?>><?php echo esc_html( $global_colors_button_label ); ?></button>
						</p>
					</form>
				</div>
			</div>
		</div>

		<div id="step-elements" class="elodin-bridge-admin__card elodin-bridge-admin__card--wide">
			<div class="elodin-bridge-admin__feature <?php echo '' === $element_post_type ? 'is-unavailable' : ''; ?>">
				<div class="elodin-bridge-admin__feature-heading-row">
					<span class="elodin-bridge-admin__feature-title"><?php esc_html_e( 'Step 4: Import elements', 'elodin-bridge' ); ?></span>
					<span class="elodin-bridge-admin__status-chip <?php echo $elements_is_complete ? 'is-success' : ''; ?>">
						<?php echo esc_html( $elements_is_complete ? __( 'Complete', 'elodin-bridge' ) : __( 'Pending', 'elodin-bridge' ) ); ?>
					</span>
				</div>
				<div class="elodin-bridge-admin__feature-body">
					<p class="elodin-bridge-admin__description">
						<?php esc_html_e( 'Imports bundled GeneratePress Elements: layout "Default" and block "Header".', 'elodin-bridge' ); ?>
					</p>
					<p class="elodin-bridge-admin__note">
						<?php esc_html_e( 'If a template title already exists without an exact match, this step creates a duplicate with an import suffix.', 'elodin-bridge' ); ?>
					</p>
					<p class="elodin-bridge-admin__note">
						<?php
						echo esc_html(
							sprintf(
								/* translators: 1: matched element count, 2: total element count */
								__( 'Detected exact template matches: %1$d of %2$d.', 'elodin-bridge' ),
								(int) ( $elements_completion['matched'] ?? 0 ),
								(int) ( $elements_completion['total'] ?? 0 )
							)
						);
						?>
					</p>
					<?php if ( '' === $element_post_type ) : ?>
						<p class="elodin-bridge-admin__note elodin-bridge-admin__note--warning">
							<?php esc_html_e( 'No GeneratePress Elements post type was found. Activate GeneratePress Premium Elements first.', 'elodin-bridge' ); ?>
						</p>
					<?php elseif ( $elements_is_locked ) : ?>
						<p class="elodin-bridge-admin__note elodin-bridge-admin__note--warning">
							<?php esc_html_e( 'Bundled element templates already exist with exact matches, so this step is locked.', 'elodin-bridge' ); ?>
						</p>
					<?php else : ?>
						<p class="elodin-bridge-admin__note">
							<?php
							echo esc_html(
								sprintf(
									/* translators: %s: post type key */
									__( 'Detected element post type: %s', 'elodin-bridge' ),
									$element_post_type
								)
							);
							?>
						</p>
						<?php $missing_element_titles = isset( $elements_completion['missing_titles'] ) && is_array( $elements_completion['missing_titles'] ) ? $elements_completion['missing_titles'] : array(); ?>
						<?php if ( ! empty( $missing_element_titles ) ) : ?>
							<p class="elodin-bridge-admin__note">
								<?php
								echo esc_html(
									sprintf(
										/* translators: %s: comma-separated element titles */
										__( 'Missing templates: %s', 'elodin-bridge' ),
										implode( ', ', $missing_element_titles )
									)
								);
								?>
							</p>
						<?php endif; ?>
					<?php endif; ?>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="elodin-bridge-setup-wizard__step-form">
						<input type="hidden" name="action" value="elodin_bridge_setup_wizard" />
						<input type="hidden" name="step" value="elements" />
						<?php wp_nonce_field( 'elodin_bridge_setup_wizard_action', '_elodin_bridge_setup_wizard_nonce' ); ?>
						<p>
							<button type="submit" class="button button-primary" <?php disabled( $elements_is_disabled ); ?>><?php echo esc_html( $elements_button_label ); ?></button>
						</p>
					</form>
				</div>
			</div>
		</div>
	</div>

	<p>
		<a href="<?php echo esc_url( admin_url( 'themes.php?page=elodin-bridge' ) ); ?>" class="button"><?php esc_html_e( 'Go to Elodin Bridge Settings', 'elodin-bridge' ); ?></a>
	</p>
</div>

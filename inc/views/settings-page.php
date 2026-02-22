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

			<div class="elodin-bridge-admin__toolbar">
				<div class="elodin-bridge-admin__category-nav" role="tablist" aria-label="<?php esc_attr_e( 'Bridge settings categories', 'elodin-bridge' ); ?>">
					<button type="button" class="elodin-bridge-admin__category-button is-active" data-bridge-category="variables" aria-pressed="true"><?php esc_html_e( 'Variables', 'elodin-bridge' ); ?></button>
					<button type="button" class="elodin-bridge-admin__category-button" data-bridge-category="editor" aria-pressed="false"><?php esc_html_e( 'Editor Tweaks', 'elodin-bridge' ); ?></button>
					<button type="button" class="elodin-bridge-admin__category-button" data-bridge-category="style" aria-pressed="false"><?php esc_html_e( 'Style Tweaks', 'elodin-bridge' ); ?></button>
					<button type="button" class="elodin-bridge-admin__category-button" data-bridge-category="misc" aria-pressed="false"><?php esc_html_e( 'Miscellaneous', 'elodin-bridge' ); ?></button>
				</div>
				<div class="elodin-bridge-admin__toolbar-save">
					<span class="elodin-bridge-admin__save-status" data-bridge-save-status data-state="idle" role="status" aria-live="polite">
						<?php esc_html_e( 'Changes save automatically.', 'elodin-bridge' ); ?>
					</span>
					<div class="elodin-bridge-admin__save-debug" data-bridge-save-debug-wrap hidden>
						<strong><?php esc_html_e( 'Autosave debug', 'elodin-bridge' ); ?></strong>
						<pre data-bridge-save-debug></pre>
					</div>
					<noscript>
						<button type="submit" name="submit" id="submit" class="button button-primary"><?php esc_html_e( 'Save Changes', 'elodin-bridge' ); ?></button>
					</noscript>
				</div>
			</div>

			<div class="elodin-bridge-admin__cards">
			<div class="elodin-bridge-admin__card elodin-bridge-admin__card--wide" data-bridge-category="variables">
				<div class="elodin-bridge-admin__feature">
					<div class="elodin-bridge-admin__feature-heading-row">
						<span class="elodin-bridge-admin__feature-title"><?php esc_html_e( 'Theme.json source', 'elodin-bridge' ); ?></span>
					</div>
					<div class="elodin-bridge-admin__feature-body">
						<p class="elodin-bridge-admin__description">
							<?php esc_html_e( 'Choose where Bridge reads spacing presets, font sizes, and button style values.', 'elodin-bridge' ); ?>
						</p>
						<p class="elodin-bridge-admin__source-mode-status">
							<span class="elodin-bridge-admin__status-chip <?php echo ! empty( $theme_json_source_theme_fallback_active ) ? 'is-warning' : ''; ?>">
								<?php if ( ! empty( $theme_json_source_theme_fallback_active ) ) : ?>
									<?php esc_html_e( 'Currently using: Plugin defaults (automatic fallback)', 'elodin-bridge' ); ?>
								<?php elseif ( 'plugin' === $theme_json_source_mode_effective ) : ?>
									<?php esc_html_e( 'Currently using: Plugin defaults', 'elodin-bridge' ); ?>
								<?php else : ?>
									<?php esc_html_e( 'Currently using: Active theme.json', 'elodin-bridge' ); ?>
								<?php endif; ?>
							</span>
						</p>
						<div class="elodin-bridge-admin__source-mode-list">
							<label class="elodin-bridge-admin__source-mode-item" for="elodin-bridge-theme-json-source-theme">
								<input
									type="radio"
									id="elodin-bridge-theme-json-source-theme"
									name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_THEME_JSON_SOURCE_MODE ); ?>"
									value="theme"
									<?php checked( 'theme' === $theme_json_source_mode ); ?>
								/>
								<span><?php esc_html_e( 'Use active theme.json (recommended)', 'elodin-bridge' ); ?></span>
							</label>
							<label class="elodin-bridge-admin__source-mode-item" for="elodin-bridge-theme-json-source-plugin">
								<input
									type="radio"
									id="elodin-bridge-theme-json-source-plugin"
									name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_THEME_JSON_SOURCE_MODE ); ?>"
									value="plugin"
									<?php checked( 'plugin' === $theme_json_source_mode ); ?>
								/>
								<span><?php esc_html_e( 'Use our defaults (if you don\'t have a robust theme.json file)', 'elodin-bridge' ); ?></span>
							</label>
						</div>
						<p class="elodin-bridge-admin__note">
							<?php esc_html_e( 'Bridge automatically falls back to plugin defaults when active theme.json has no button styles, no spacing presets, or no font-size presets.', 'elodin-bridge' ); ?>
						</p>
						<?php if ( ! empty( $theme_json_source_theme_fallback_active ) ) : ?>
							<p class="elodin-bridge-admin__note elodin-bridge-admin__note--warning">
								<?php esc_html_e( 'Active theme has no readable theme.json file. Bridge is currently using plugin defaults automatically.', 'elodin-bridge' ); ?>
							</p>
						<?php endif; ?>
						<p class="elodin-bridge-admin__note <?php echo '' === $variables_theme_json_theme_display_path ? 'elodin-bridge-admin__note--warning' : ''; ?>">
							<?php
							echo wp_kses_post(
								sprintf(
									/* translators: 1: active theme.json path, 2: plugin defaults path, 3: plugin defaults download URL */
									__( 'Theme source path: <code>%1$s</code><br />Plugin defaults path: <code>%2$s</code><br /><a href="%3$s" download="theme.json">Download plugin defaults as theme.json</a>.', 'elodin-bridge' ),
									esc_html( '' !== $variables_theme_json_theme_display_path ? $variables_theme_json_theme_display_path : 'none found' ),
									esc_html( $variables_theme_json_defaults_display_path ),
									esc_url( $variables_theme_json_download_url )
								)
							);
							?>
						</p>
					</div>
				</div>
			</div>

			<div class="elodin-bridge-admin__card elodin-bridge-admin__card--wide" data-bridge-category="variables">
				<div class="elodin-bridge-admin__feature <?php echo ! empty( $spacing_variables_settings['enabled'] ) ? 'is-enabled' : ''; ?>">
					<label class="elodin-bridge-admin__feature-header" for="elodin-bridge-spacing-variables-enabled">
						<input
							type="hidden"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_SPACING_VARIABLES ); ?>[enabled]"
							value="0"
						/>
						<input
							type="checkbox"
							class="elodin-bridge-admin__toggle-input elodin-bridge-admin__feature-toggle"
							id="elodin-bridge-spacing-variables-enabled"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_SPACING_VARIABLES ); ?>[enabled]"
							value="1"
							<?php checked( ! empty( $spacing_variables_settings['enabled'] ) ); ?>
						/>
						<span class="elodin-bridge-admin__toggle-track" aria-hidden="true">
							<span class="elodin-bridge-admin__toggle-thumb"></span>
						</span>
						<span class="elodin-bridge-admin__feature-title"><?php esc_html_e( 'Enable spacing variables', 'elodin-bridge' ); ?></span>
					</label>

						<div class="elodin-bridge-admin__feature-body">
							<p class="elodin-bridge-admin__description">
								<?php esc_html_e( 'Maps your theme.json spacing presets to short CSS aliases on :root for easier typing.', 'elodin-bridge' ); ?>
							</p>
							<?php
							$visible_spacing_aliases = array_values(
								array_filter(
									$spacing_variable_aliases,
									static function ( $alias ) {
										$value = is_array( $alias ) ? (string) ( $alias['value'] ?? '' ) : '';
										return '' !== $value;
									}
								)
							);
							?>
							<?php if ( ! empty( $visible_spacing_aliases ) ) : ?>
								<div class="elodin-bridge-admin__variables-grid elodin-bridge-admin__variables-grid--spacing-compact">
									<?php foreach ( $visible_spacing_aliases as $alias ) : ?>
										<?php
										$token = (string) ( $alias['token'] ?? '' );
										if ( '' === $token ) {
											continue;
										}
										$variable_name = '--space-' . $token;
										$value = (string) ( $alias['value'] ?? '' );
										?>
										<div class="elodin-bridge-admin__variable-field">
											<span class="elodin-bridge-admin__variable-name">
												<code><?php echo esc_html( $variable_name ); ?></code>
											</span>
											<code class="elodin-bridge-admin__variable-value"><?php echo esc_html( $value ); ?></code>
										</div>
									<?php endforeach; ?>
								</div>
							<?php else : ?>
								<p class="elodin-bridge-admin__note elodin-bridge-admin__note--warning">
									<?php esc_html_e( 'No spacing presets were found in theme.json for the mapped aliases.', 'elodin-bridge' ); ?>
								</p>
							<?php endif; ?>
							<p class="elodin-bridge-admin__note">
								<?php if ( 'plugin' === $theme_json_source_mode_effective ) : ?>
									<?php
									echo wp_kses_post(
										sprintf(
											/* translators: %s: plugin defaults download URL */
											__( 'Bridge is currently reading plugin defaults. Do not edit plugin files. <a href="%s" download="theme.json">Download plugin defaults as theme.json</a>, place it in your active theme, and edit <code>settings.spacing.spacingSizes</code> there.', 'elodin-bridge' ),
											esc_url( $variables_theme_json_download_url )
										)
									);
									?>
								<?php else : ?>
									<?php
									echo wp_kses_post(
										sprintf(
											/* translators: %s: theme.json path */
											__( 'Update values in <code>%s</code> under <code>settings.spacing.spacingSizes</code>. These mappings are read-only here.', 'elodin-bridge' ),
											esc_html( $variables_theme_json_display_path )
										)
									);
									?>
								<?php endif; ?>
							</p>
					</div>
				</div>
			</div>

			<div class="elodin-bridge-admin__card elodin-bridge-admin__card--wide" data-bridge-category="variables">
				<div class="elodin-bridge-admin__feature <?php echo ! empty( $font_size_variables_settings['enabled'] ) ? 'is-enabled' : ''; ?>">
					<label class="elodin-bridge-admin__feature-header" for="elodin-bridge-font-size-variables-enabled">
						<input
							type="hidden"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_FONT_SIZE_VARIABLES ); ?>[enabled]"
							value="0"
						/>
						<input
							type="checkbox"
							class="elodin-bridge-admin__toggle-input elodin-bridge-admin__feature-toggle"
							id="elodin-bridge-font-size-variables-enabled"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_FONT_SIZE_VARIABLES ); ?>[enabled]"
							value="1"
							<?php checked( ! empty( $font_size_variables_settings['enabled'] ) ); ?>
						/>
						<span class="elodin-bridge-admin__toggle-track" aria-hidden="true">
							<span class="elodin-bridge-admin__toggle-thumb"></span>
						</span>
						<span class="elodin-bridge-admin__feature-title"><?php esc_html_e( 'Enable font-size variables', 'elodin-bridge' ); ?></span>
					</label>

						<div class="elodin-bridge-admin__feature-body">
							<p class="elodin-bridge-admin__description">
								<?php esc_html_e( 'Maps your theme.json font-size presets to short CSS aliases on :root for easier typing.', 'elodin-bridge' ); ?>
							</p>
							<?php
							$visible_font_size_aliases = array_values(
								array_filter(
									$font_size_variable_aliases,
									static function ( $alias ) {
										$value = is_array( $alias ) ? (string) ( $alias['value'] ?? '' ) : '';
										return '' !== $value;
									}
								)
							);
							?>
							<?php if ( ! empty( $visible_font_size_aliases ) ) : ?>
								<div class="elodin-bridge-admin__variables-grid">
									<?php foreach ( $visible_font_size_aliases as $alias ) : ?>
										<?php
										$token = (string) ( $alias['token'] ?? '' );
										if ( '' === $token ) {
											continue;
										}
										$variable_name = '--font-' . $token;
										$value = (string) ( $alias['value'] ?? '' );
										?>
										<div class="elodin-bridge-admin__variable-field">
											<span class="elodin-bridge-admin__variable-name">
												<code><?php echo esc_html( $variable_name ); ?></code>
											</span>
											<code class="elodin-bridge-admin__variable-value"><?php echo esc_html( $value ); ?></code>
										</div>
									<?php endforeach; ?>
								</div>
							<?php else : ?>
								<p class="elodin-bridge-admin__note elodin-bridge-admin__note--warning">
									<?php esc_html_e( 'No font-size presets were found in theme.json for the mapped aliases.', 'elodin-bridge' ); ?>
								</p>
							<?php endif; ?>
							<p class="elodin-bridge-admin__note">
								<?php if ( 'plugin' === $theme_json_source_mode_effective ) : ?>
									<?php
									echo wp_kses_post(
										sprintf(
											/* translators: %s: plugin defaults download URL */
											__( 'Bridge is currently reading plugin defaults. Do not edit plugin files. <a href="%s" download="theme.json">Download plugin defaults as theme.json</a>, place it in your active theme, and edit <code>settings.typography.fontSizes</code> there.', 'elodin-bridge' ),
											esc_url( $variables_theme_json_download_url )
										)
									);
									?>
								<?php else : ?>
									<?php
									echo wp_kses_post(
										sprintf(
											/* translators: %s: theme.json path */
											__( 'Update values in <code>%s</code> under <code>settings.typography.fontSizes</code>. These mappings are read-only here.', 'elodin-bridge' ),
											esc_html( $variables_theme_json_display_path )
										)
									);
									?>
								<?php endif; ?>
							</p>
					</div>
				</div>
			</div>

			<div class="elodin-bridge-admin__card elodin-bridge-admin__card--wide" data-bridge-category="editor">
				<div class="elodin-bridge-admin__feature has-requirement <?php echo $generateblocks_layout_gap_defaults_enabled ? 'is-enabled' : ''; ?> <?php echo ! $generateblocks_available ? 'is-unavailable' : ''; ?>">
					<label class="elodin-bridge-admin__feature-header <?php echo ! $generateblocks_available ? 'is-disabled' : ''; ?>" for="elodin-bridge-generateblocks-layout-gaps-enabled">
						<input
							type="hidden"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_GENERATEBLOCKS_LAYOUT_GAP_DEFAULTS ); ?>[enabled]"
							value="0"
						/>
						<input
							type="checkbox"
							class="elodin-bridge-admin__toggle-input elodin-bridge-admin__feature-toggle"
							id="elodin-bridge-generateblocks-layout-gaps-enabled"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_GENERATEBLOCKS_LAYOUT_GAP_DEFAULTS ); ?>[enabled]"
							value="1"
							<?php checked( ! empty( $generateblocks_layout_gap_defaults_settings['enabled'] ) ); ?>
						/>
						<span class="elodin-bridge-admin__toggle-track" aria-hidden="true">
							<span class="elodin-bridge-admin__toggle-thumb"></span>
						</span>
						<span class="elodin-bridge-admin__feature-title"><?php esc_html_e( 'Enable GenerateBlocks layout gap defaults', 'elodin-bridge' ); ?></span>
					</label>
					<span class="elodin-bridge-admin__requirement-tag elodin-bridge-admin__requirement-tag--corner"><?php esc_html_e( 'Requires GenerateBlocks', 'elodin-bridge' ); ?></span>

					<div class="elodin-bridge-admin__feature-body">
						<p class="elodin-bridge-admin__description">
							<?php esc_html_e( 'Sets default row and column gap values for new GenerateBlocks containers.', 'elodin-bridge' ); ?>
						</p>
						<?php if ( ! $generateblocks_available ) : ?>
							<p class="elodin-bridge-admin__note">
								<?php esc_html_e( 'This setting is only available when GenerateBlocks is active.', 'elodin-bridge' ); ?>
							</p>
						<?php endif; ?>

						<div class="elodin-bridge-admin__responsive-values">
							<label class="elodin-bridge-admin__responsive-field" for="elodin-bridge-gb-column-gap-desktop">
								<span><?php esc_html_e( 'Column gap (desktop)', 'elodin-bridge' ); ?></span>
								<input
									type="text"
									class="regular-text"
									id="elodin-bridge-gb-column-gap-desktop"
									name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_GENERATEBLOCKS_LAYOUT_GAP_DEFAULTS ); ?>[column_gap_desktop]"
									value="<?php echo esc_attr( $generateblocks_layout_gap_defaults_settings['column_gap_desktop'] ?? 'var( --space-xl )' ); ?>"
								/>
							</label>
							<label class="elodin-bridge-admin__responsive-field" for="elodin-bridge-gb-row-gap-desktop">
								<span><?php esc_html_e( 'Row gap (desktop)', 'elodin-bridge' ); ?></span>
								<input
									type="text"
									class="regular-text"
									id="elodin-bridge-gb-row-gap-desktop"
									name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_GENERATEBLOCKS_LAYOUT_GAP_DEFAULTS ); ?>[row_gap_desktop]"
									value="<?php echo esc_attr( $generateblocks_layout_gap_defaults_settings['row_gap_desktop'] ?? 'var( --space-m )' ); ?>"
								/>
							</label>
							<label class="elodin-bridge-admin__responsive-field" for="elodin-bridge-gb-column-gap-tablet">
								<span><?php esc_html_e( 'Column gap (tablet)', 'elodin-bridge' ); ?></span>
								<input
									type="text"
									class="regular-text"
									id="elodin-bridge-gb-column-gap-tablet"
									name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_GENERATEBLOCKS_LAYOUT_GAP_DEFAULTS ); ?>[column_gap_tablet]"
									value="<?php echo esc_attr( $generateblocks_layout_gap_defaults_settings['column_gap_tablet'] ?? 'var( --space-xl )' ); ?>"
								/>
							</label>
							<label class="elodin-bridge-admin__responsive-field" for="elodin-bridge-gb-row-gap-tablet">
								<span><?php esc_html_e( 'Row gap (tablet)', 'elodin-bridge' ); ?></span>
								<input
									type="text"
									class="regular-text"
									id="elodin-bridge-gb-row-gap-tablet"
									name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_GENERATEBLOCKS_LAYOUT_GAP_DEFAULTS ); ?>[row_gap_tablet]"
									value="<?php echo esc_attr( $generateblocks_layout_gap_defaults_settings['row_gap_tablet'] ?? 'var( --space-m )' ); ?>"
								/>
							</label>
							<label class="elodin-bridge-admin__responsive-field" for="elodin-bridge-gb-column-gap-mobile">
								<span><?php esc_html_e( 'Column gap (mobile)', 'elodin-bridge' ); ?></span>
								<input
									type="text"
									class="regular-text"
									id="elodin-bridge-gb-column-gap-mobile"
									name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_GENERATEBLOCKS_LAYOUT_GAP_DEFAULTS ); ?>[column_gap_mobile]"
									value="<?php echo esc_attr( $generateblocks_layout_gap_defaults_settings['column_gap_mobile'] ?? 'var( --space-xl )' ); ?>"
								/>
							</label>
							<label class="elodin-bridge-admin__responsive-field" for="elodin-bridge-gb-row-gap-mobile">
								<span><?php esc_html_e( 'Row gap (mobile)', 'elodin-bridge' ); ?></span>
								<input
									type="text"
									class="regular-text"
									id="elodin-bridge-gb-row-gap-mobile"
									name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_GENERATEBLOCKS_LAYOUT_GAP_DEFAULTS ); ?>[row_gap_mobile]"
									value="<?php echo esc_attr( $generateblocks_layout_gap_defaults_settings['row_gap_mobile'] ?? 'var( --space-m )' ); ?>"
								/>
							</label>
						</div>

						<p class="elodin-bridge-admin__note">
							<?php esc_html_e( 'Supports CSS values like 1.5rem, var(--space-m), or clamp(0.75rem, 1vw, 1.5rem). Applies to newly inserted GenerateBlocks container blocks.', 'elodin-bridge' ); ?>
						</p>
					</div>
				</div>
			</div>

			<div class="elodin-bridge-admin__card elodin-bridge-admin__card--wide" data-bridge-category="editor">
				<div class="elodin-bridge-admin__feature has-requirement <?php echo $root_level_container_padding_enabled ? 'is-enabled' : ''; ?> <?php echo ! $generateblocks_available ? 'is-unavailable' : ''; ?>">
					<label class="elodin-bridge-admin__feature-header <?php echo ! $generateblocks_available ? 'is-disabled' : ''; ?>" for="elodin-bridge-root-level-container-padding-enabled">
						<input
							type="hidden"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ROOT_LEVEL_CONTAINER_PADDING ); ?>[enabled]"
							value="0"
						/>
						<input
							type="checkbox"
							class="elodin-bridge-admin__toggle-input elodin-bridge-admin__feature-toggle"
							id="elodin-bridge-root-level-container-padding-enabled"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ROOT_LEVEL_CONTAINER_PADDING ); ?>[enabled]"
							value="1"
							<?php checked( ! empty( $root_level_container_padding_settings['enabled'] ) ); ?>
						/>
						<span class="elodin-bridge-admin__toggle-track" aria-hidden="true">
							<span class="elodin-bridge-admin__toggle-thumb"></span>
						</span>
						<span class="elodin-bridge-admin__feature-title"><?php esc_html_e( 'Enable root-level container padding', 'elodin-bridge' ); ?></span>
					</label>
					<span class="elodin-bridge-admin__requirement-tag elodin-bridge-admin__requirement-tag--corner"><?php esc_html_e( 'Requires GenerateBlocks', 'elodin-bridge' ); ?></span>

					<div class="elodin-bridge-admin__feature-body">
						<p class="elodin-bridge-admin__description">
							<?php esc_html_e( 'Applies consistent padding and margin resets to root-level GenerateBlocks containers in editor and front-end contexts, including direct children inside reusable block wrappers.', 'elodin-bridge' ); ?>
						</p>
						<?php if ( ! $generateblocks_available ) : ?>
							<p class="elodin-bridge-admin__note">
								<?php esc_html_e( 'This setting is only available when GenerateBlocks is active.', 'elodin-bridge' ); ?>
							</p>
						<?php endif; ?>

						<div class="elodin-bridge-admin__responsive-values">
							<label class="elodin-bridge-admin__responsive-field" for="elodin-bridge-root-level-padding-desktop-vertical">
								<span><?php esc_html_e( 'Desktop vertical', 'elodin-bridge' ); ?></span>
								<input
									type="text"
									class="regular-text"
									id="elodin-bridge-root-level-padding-desktop-vertical"
									name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ROOT_LEVEL_CONTAINER_PADDING ); ?>[desktop_vertical]"
									value="<?php echo esc_attr( $root_level_container_padding_settings['desktop_vertical'] ?? 'var( --space-2xl )' ); ?>"
								/>
							</label>
							<label class="elodin-bridge-admin__responsive-field" for="elodin-bridge-root-level-padding-tablet-vertical">
								<span><?php esc_html_e( 'Tablet vertical', 'elodin-bridge' ); ?></span>
								<input
									type="text"
									class="regular-text"
									id="elodin-bridge-root-level-padding-tablet-vertical"
									name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ROOT_LEVEL_CONTAINER_PADDING ); ?>[tablet_vertical]"
									value="<?php echo esc_attr( $root_level_container_padding_settings['tablet_vertical'] ?? 'var( --space-xl )' ); ?>"
								/>
							</label>
							<label class="elodin-bridge-admin__responsive-field" for="elodin-bridge-root-level-padding-mobile-vertical">
								<span><?php esc_html_e( 'Mobile vertical', 'elodin-bridge' ); ?></span>
								<input
									type="text"
									class="regular-text"
									id="elodin-bridge-root-level-padding-mobile-vertical"
									name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ROOT_LEVEL_CONTAINER_PADDING ); ?>[mobile_vertical]"
									value="<?php echo esc_attr( $root_level_container_padding_settings['mobile_vertical'] ?? 'var( --space-m )' ); ?>"
								/>
							</label>
							<label class="elodin-bridge-admin__responsive-field" for="elodin-bridge-root-level-padding-desktop-horizontal">
								<span><?php esc_html_e( 'Desktop horizontal', 'elodin-bridge' ); ?></span>
								<input
									type="text"
									class="regular-text"
									id="elodin-bridge-root-level-padding-desktop-horizontal"
									name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ROOT_LEVEL_CONTAINER_PADDING ); ?>[desktop_horizontal]"
									value="<?php echo esc_attr( $root_level_container_padding_settings['desktop_horizontal'] ?? 'var( --space-m )' ); ?>"
								/>
							</label>
							<label class="elodin-bridge-admin__responsive-field" for="elodin-bridge-root-level-padding-tablet-horizontal">
								<span><?php esc_html_e( 'Tablet horizontal', 'elodin-bridge' ); ?></span>
								<input
									type="text"
									class="regular-text"
									id="elodin-bridge-root-level-padding-tablet-horizontal"
									name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ROOT_LEVEL_CONTAINER_PADDING ); ?>[tablet_horizontal]"
									value="<?php echo esc_attr( $root_level_container_padding_settings['tablet_horizontal'] ?? 'var( --space-m )' ); ?>"
								/>
							</label>
							<label class="elodin-bridge-admin__responsive-field" for="elodin-bridge-root-level-padding-mobile-horizontal">
								<span><?php esc_html_e( 'Mobile horizontal', 'elodin-bridge' ); ?></span>
								<input
									type="text"
									class="regular-text"
									id="elodin-bridge-root-level-padding-mobile-horizontal"
									name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ROOT_LEVEL_CONTAINER_PADDING ); ?>[mobile_horizontal]"
									value="<?php echo esc_attr( $root_level_container_padding_settings['mobile_horizontal'] ?? 'var( --space-m )' ); ?>"
								/>
							</label>
						</div>

						<p class="elodin-bridge-admin__note">
							<?php esc_html_e( 'Supports CSS values like var(--space-m), 2rem, or clamp(1rem, 2vw, 2rem). Applies axis-specific values as top/bottom and left/right padding.', 'elodin-bridge' ); ?>
						</p>
					</div>
				</div>
			</div>

			<div class="elodin-bridge-admin__card" data-bridge-category="editor">
				<div class="elodin-bridge-admin__feature has-requirement <?php echo $heading_paragraph_overrides_enabled ? 'is-enabled' : ''; ?> <?php echo ! $heading_paragraph_overrides_available ? 'is-unavailable' : ''; ?>">
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
						/>
						<span class="elodin-bridge-admin__toggle-track" aria-hidden="true">
							<span class="elodin-bridge-admin__toggle-thumb"></span>
						</span>
						<span class="elodin-bridge-admin__feature-title"><?php esc_html_e( 'Enable heading and paragraph style overrides', 'elodin-bridge' ); ?></span>
					</label>
					<span class="elodin-bridge-admin__requirement-tag elodin-bridge-admin__requirement-tag--corner"><?php esc_html_e( 'Requires GeneratePress', 'elodin-bridge' ); ?></span>

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

			<div class="elodin-bridge-admin__card" data-bridge-category="editor">
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

			<div class="elodin-bridge-admin__card elodin-bridge-admin__card--wide" data-bridge-category="editor">
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

			<div class="elodin-bridge-admin__card" data-bridge-category="style">
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
									value="<?php echo esc_attr( $automatic_heading_margins_settings['desktop'] ?? 'var( --space-l )' ); ?>"
								/>
							</label>
							<label class="elodin-bridge-admin__responsive-field" for="elodin-bridge-heading-margin-tablet">
								<span><?php esc_html_e( 'Tablet', 'elodin-bridge' ); ?></span>
								<input
									type="text"
									class="regular-text"
									id="elodin-bridge-heading-margin-tablet"
									name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_AUTOMATIC_HEADING_MARGINS ); ?>[tablet]"
									value="<?php echo esc_attr( $automatic_heading_margins_settings['tablet'] ?? 'var( --space-l )' ); ?>"
								/>
							</label>
							<label class="elodin-bridge-admin__responsive-field" for="elodin-bridge-heading-margin-mobile">
								<span><?php esc_html_e( 'Mobile', 'elodin-bridge' ); ?></span>
								<input
									type="text"
									class="regular-text"
									id="elodin-bridge-heading-margin-mobile"
									name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_AUTOMATIC_HEADING_MARGINS ); ?>[mobile]"
									value="<?php echo esc_attr( $automatic_heading_margins_settings['mobile'] ?? 'var( --space-l )' ); ?>"
								/>
							</label>
						</div>
						<p class="elodin-bridge-admin__note">
							<?php esc_html_e( 'Supports CSS values like 4em, var(--space-heading-top), or clamp(2rem, 3vw, 4rem).', 'elodin-bridge' ); ?>
						</p>
					</div>
				</div>
			</div>

			<div class="elodin-bridge-admin__card elodin-bridge-admin__card--wide" data-bridge-category="style">
				<div class="elodin-bridge-admin__feature has-requirement <?php echo $theme_json_button_padding_important_enabled ? 'is-enabled' : ''; ?>">
					<?php
					$button_style_overrides = is_array( $theme_json_button_style_overrides ) ? $theme_json_button_style_overrides : array();
					$button_base_declarations = isset( $button_style_overrides['base'] ) && is_array( $button_style_overrides['base'] )
						? $button_style_overrides['base']
						: array();
					$button_outline_declarations = isset( $button_style_overrides['outline'] ) && is_array( $button_style_overrides['outline'] )
						? $button_style_overrides['outline']
						: array();
					?>
					<label class="elodin-bridge-admin__feature-header" for="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_THEME_JSON_BUTTON_PADDING_IMPORTANT ); ?>">
						<input
							type="hidden"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_THEME_JSON_BUTTON_PADDING_IMPORTANT ); ?>"
							value="0"
						/>
						<input
							type="checkbox"
							class="elodin-bridge-admin__toggle-input elodin-bridge-admin__feature-toggle"
							id="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_THEME_JSON_BUTTON_PADDING_IMPORTANT ); ?>"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_THEME_JSON_BUTTON_PADDING_IMPORTANT ); ?>"
							value="1"
							<?php checked( $theme_json_button_padding_important_enabled ); ?>
						/>
						<span class="elodin-bridge-admin__toggle-track" aria-hidden="true">
							<span class="elodin-bridge-admin__toggle-thumb"></span>
						</span>
						<span class="elodin-bridge-admin__feature-title"><?php esc_html_e( 'Apply theme.json button styles with higher specificity', 'elodin-bridge' ); ?></span>
					</label>
					<span class="elodin-bridge-admin__requirement-tag elodin-bridge-admin__requirement-tag--corner"><?php esc_html_e( 'Requires GeneratePress', 'elodin-bridge' ); ?></span>

					<div class="elodin-bridge-admin__feature-body">
						<p class="elodin-bridge-admin__description">
							<?php esc_html_e( 'Outputs declarations for .wp-block-button__link using your active theme.json values (spacing, typography, border, color, and shadow) with selectors specific enough to out-rank parent-theme defaults, without using !important.', 'elodin-bridge' ); ?>
						</p>
						<?php if ( ! empty( $button_base_declarations ) || ! empty( $button_outline_declarations ) ) : ?>
							<div class="elodin-bridge-admin__variables-grid elodin-bridge-admin__variables-grid--button-styles">
								<?php foreach ( $button_base_declarations as $property => $value ) : ?>
									<div class="elodin-bridge-admin__variable-field">
										<span class="elodin-bridge-admin__variable-name">
											<code><?php echo esc_html( (string) $property ); ?></code>
											<small><?php esc_html_e( 'Default button', 'elodin-bridge' ); ?></small>
										</span>
										<code class="elodin-bridge-admin__variable-value"><?php echo esc_html( (string) $value ); ?></code>
									</div>
								<?php endforeach; ?>
								<?php foreach ( $button_outline_declarations as $property => $value ) : ?>
									<div class="elodin-bridge-admin__variable-field">
										<span class="elodin-bridge-admin__variable-name">
											<code><?php echo esc_html( (string) $property ); ?></code>
											<small><?php esc_html_e( 'Outline variation', 'elodin-bridge' ); ?></small>
										</span>
										<code class="elodin-bridge-admin__variable-value"><?php echo esc_html( (string) $value ); ?></code>
									</div>
								<?php endforeach; ?>
							</div>
						<?php else : ?>
							<p class="elodin-bridge-admin__note elodin-bridge-admin__note--warning">
								<?php esc_html_e( 'No button style values were found in active theme.json.', 'elodin-bridge' ); ?>
							</p>
						<?php endif; ?>
						<p class="elodin-bridge-admin__note">
							<?php if ( 'plugin' === $theme_json_source_mode_effective ) : ?>
								<?php
								echo wp_kses_post(
									sprintf(
										/* translators: %s: plugin defaults download URL */
										__( 'Bridge is currently reading plugin defaults. Do not edit plugin files. <a href="%s" download="theme.json">Download plugin defaults as theme.json</a>, place it in your active theme, then edit <code>styles.blocks.core/button</code> (fallback: <code>styles.elements.button</code>; variation support: <code>styles.blocks.core/button.variations.outline</code>). Values shown here are read-only. Per-button overrides remain possible.', 'elodin-bridge' ),
										esc_url( $variables_theme_json_download_url )
									)
								);
								?>
							<?php else : ?>
								<?php
								echo wp_kses_post(
									sprintf(
										/* translators: %s: theme.json path */
										__( 'Update values in <code>%s</code> under <code>styles.blocks.core/button</code> (fallback: <code>styles.elements.button</code>; variation support: <code>styles.blocks.core/button.variations.outline</code>). Values shown here are read-only. Per-button overrides remain possible.', 'elodin-bridge' ),
										esc_html( $variables_theme_json_display_path )
									)
								);
								?>
							<?php endif; ?>
						</p>
					</div>
				</div>
			</div>

			<div class="elodin-bridge-admin__card" data-bridge-category="style">
				<div class="elodin-bridge-admin__feature has-requirement <?php echo $generatepress_list_margins_enabled ? 'is-enabled' : ''; ?> <?php echo ! $generatepress_list_margins_available ? 'is-unavailable' : ''; ?>">
					<label class="elodin-bridge-admin__feature-header <?php echo ! $generatepress_list_margins_available ? 'is-disabled' : ''; ?>" for="elodin-bridge-generatepress-list-margins-enabled">
						<input
							type="hidden"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_GENERATEPRESS_LIST_MARGINS ); ?>[enabled]"
							value="0"
						/>
						<input
							type="checkbox"
							class="elodin-bridge-admin__toggle-input elodin-bridge-admin__feature-toggle"
							id="elodin-bridge-generatepress-list-margins-enabled"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_GENERATEPRESS_LIST_MARGINS ); ?>[enabled]"
							value="1"
							<?php checked( ! empty( $generatepress_list_margins_settings['enabled'] ) ); ?>
						/>
						<span class="elodin-bridge-admin__toggle-track" aria-hidden="true">
							<span class="elodin-bridge-admin__toggle-thumb"></span>
						</span>
						<span class="elodin-bridge-admin__feature-title"><?php esc_html_e( 'Override GeneratePress list margins', 'elodin-bridge' ); ?></span>
					</label>
					<span class="elodin-bridge-admin__requirement-tag elodin-bridge-admin__requirement-tag--corner"><?php esc_html_e( 'Requires GeneratePress', 'elodin-bridge' ); ?></span>

					<div class="elodin-bridge-admin__feature-body">
						<p class="elodin-bridge-admin__description">
							<?php esc_html_e( 'GeneratePress parent theme does not respect theme.json values and has no setting for this in its settings.', 'elodin-bridge' ); ?>
						</p>
						<?php if ( ! $generatepress_list_margins_available ) : ?>
							<p class="elodin-bridge-admin__note">
								<?php esc_html_e( 'This setting is only available when GeneratePress is your active parent theme.', 'elodin-bridge' ); ?>
							</p>
						<?php endif; ?>
						<div class="elodin-bridge-admin__responsive-values elodin-bridge-admin__responsive-values--two-by-two">
							<label class="elodin-bridge-admin__responsive-field" for="elodin-bridge-generatepress-list-margin-top">
								<span><?php esc_html_e( 'Margin top', 'elodin-bridge' ); ?></span>
								<input
									type="text"
									class="regular-text"
									id="elodin-bridge-generatepress-list-margin-top"
									name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_GENERATEPRESS_LIST_MARGINS ); ?>[margin_top]"
									value="<?php echo esc_attr( $generatepress_list_margins_settings['margin_top'] ?? '0' ); ?>"
								/>
							</label>
							<label class="elodin-bridge-admin__responsive-field" for="elodin-bridge-generatepress-list-margin-right">
								<span><?php esc_html_e( 'Margin right', 'elodin-bridge' ); ?></span>
								<input
									type="text"
									class="regular-text"
									id="elodin-bridge-generatepress-list-margin-right"
									name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_GENERATEPRESS_LIST_MARGINS ); ?>[margin_right]"
									value="<?php echo esc_attr( $generatepress_list_margins_settings['margin_right'] ?? '0' ); ?>"
								/>
							</label>
							<label class="elodin-bridge-admin__responsive-field" for="elodin-bridge-generatepress-list-margin-bottom">
								<span><?php esc_html_e( 'Margin bottom', 'elodin-bridge' ); ?></span>
								<input
									type="text"
									class="regular-text"
									id="elodin-bridge-generatepress-list-margin-bottom"
									name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_GENERATEPRESS_LIST_MARGINS ); ?>[margin_bottom]"
									value="<?php echo esc_attr( $generatepress_list_margins_settings['margin_bottom'] ?? elodin_bridge_get_generatepress_body_margin_bottom_default() ); ?>"
								/>
							</label>
							<label class="elodin-bridge-admin__responsive-field" for="elodin-bridge-generatepress-list-margin-left">
								<span><?php esc_html_e( 'Margin left', 'elodin-bridge' ); ?></span>
								<input
									type="text"
									class="regular-text"
									id="elodin-bridge-generatepress-list-margin-left"
									name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_GENERATEPRESS_LIST_MARGINS ); ?>[margin_left]"
									value="<?php echo esc_attr( $generatepress_list_margins_settings['margin_left'] ?? 'var( --space-m )' ); ?>"
								/>
							</label>
						</div>
						<p class="elodin-bridge-admin__note">
							<?php esc_html_e( 'Supports CSS values like 0, 1.5em, var(--space-m), or clamp(1rem, 2vw, 2rem).', 'elodin-bridge' ); ?>
						</p>
					</div>
				</div>
			</div>

			<div class="elodin-bridge-admin__card" data-bridge-category="style">
				<div class="elodin-bridge-admin__feature has-requirement <?php echo $generatepress_static_css_experiment_enabled ? 'is-enabled' : ''; ?> <?php echo ! $generatepress_static_css_experiment_available ? 'is-unavailable' : ''; ?>">
					<label class="elodin-bridge-admin__feature-header <?php echo ! $generatepress_static_css_experiment_available ? 'is-disabled' : ''; ?>" for="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_GENERATEPRESS_STATIC_CSS_EXPERIMENT ); ?>">
						<input
							type="hidden"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_GENERATEPRESS_STATIC_CSS_EXPERIMENT ); ?>"
							value="0"
						/>
						<input
							type="checkbox"
							class="elodin-bridge-admin__toggle-input elodin-bridge-admin__feature-toggle"
							id="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_GENERATEPRESS_STATIC_CSS_EXPERIMENT ); ?>"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_GENERATEPRESS_STATIC_CSS_EXPERIMENT ); ?>"
							value="1"
							<?php checked( $generatepress_static_css_experiment_enabled ); ?>
						/>
						<span class="elodin-bridge-admin__toggle-track" aria-hidden="true">
							<span class="elodin-bridge-admin__toggle-thumb"></span>
						</span>
						<span class="elodin-bridge-admin__feature-title"><?php esc_html_e( 'Disable GeneratePress static CSS', 'elodin-bridge' ); ?></span>
					</label>
					<span class="elodin-bridge-admin__requirement-tag elodin-bridge-admin__requirement-tag--corner"><?php esc_html_e( 'Requires GeneratePress', 'elodin-bridge' ); ?></span>

					<div class="elodin-bridge-admin__feature-body">
						<p class="elodin-bridge-admin__description">
							<?php esc_html_e( 'Dequeues GeneratePress static stylesheet files while preserving dynamic inline CSS generated from settings.', 'elodin-bridge' ); ?>
						</p>
						<p class="elodin-bridge-admin__note elodin-bridge-admin__note--warning">
							<?php esc_html_e( 'Experimental: this can cause unexpected visual regressions.', 'elodin-bridge' ); ?>
						</p>
						<p class="elodin-bridge-admin__note">
							<?php esc_html_e( 'Also applies .site.grid-container { max-width: 100% }.', 'elodin-bridge' ); ?>
						</p>
						<?php if ( ! $generatepress_static_css_experiment_available ) : ?>
							<p class="elodin-bridge-admin__note">
								<?php esc_html_e( 'This setting is only available when GeneratePress is your active parent theme.', 'elodin-bridge' ); ?>
							</p>
						<?php endif; ?>
					</div>
				</div>
			</div>

			<div class="elodin-bridge-admin__card" data-bridge-category="style">
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

			<div class="elodin-bridge-admin__card" data-bridge-category="style">
				<div class="elodin-bridge-admin__feature <?php echo $last_child_button_group_top_margin_enabled ? 'is-enabled' : ''; ?>">
					<label class="elodin-bridge-admin__feature-header" for="elodin-bridge-last-child-button-group-top-margin-enabled">
						<input
							type="hidden"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_LAST_CHILD_BUTTON_GROUP_TOP_MARGIN ); ?>[enabled]"
							value="0"
						/>
						<input
							type="checkbox"
							class="elodin-bridge-admin__toggle-input elodin-bridge-admin__feature-toggle"
							id="elodin-bridge-last-child-button-group-top-margin-enabled"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_LAST_CHILD_BUTTON_GROUP_TOP_MARGIN ); ?>[enabled]"
							value="1"
							<?php checked( ! empty( $last_child_button_group_top_margin_settings['enabled'] ) ); ?>
						/>
						<span class="elodin-bridge-admin__toggle-track" aria-hidden="true">
							<span class="elodin-bridge-admin__toggle-thumb"></span>
						</span>
						<span class="elodin-bridge-admin__feature-title"><?php esc_html_e( 'Last-child button group top margin', 'elodin-bridge' ); ?></span>
					</label>

					<div class="elodin-bridge-admin__feature-body">
						<p class="elodin-bridge-admin__description">
							<?php esc_html_e( 'Adds margin-top to .wp-block-buttons when it is the last child, and always sets margin-top: 0 when it is the first child.', 'elodin-bridge' ); ?>
						</p>
						<div class="elodin-bridge-admin__responsive-values">
							<label class="elodin-bridge-admin__responsive-field" for="elodin-bridge-last-child-button-group-top-margin-value">
								<span><?php esc_html_e( 'Margin top', 'elodin-bridge' ); ?></span>
								<input
									type="text"
									class="regular-text"
									id="elodin-bridge-last-child-button-group-top-margin-value"
									name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_LAST_CHILD_BUTTON_GROUP_TOP_MARGIN ); ?>[value]"
									value="<?php echo esc_attr( $last_child_button_group_top_margin_settings['value'] ?? 'var( --space-l )' ); ?>"
								/>
							</label>
						</div>
						<p class="elodin-bridge-admin__note">
							<?php esc_html_e( 'Supports CSS values like var(--space-l), 2rem, or clamp(1rem, 3vw, 3rem). First-child button groups always use margin-top: 0 when this setting is enabled.', 'elodin-bridge' ); ?>
						</p>
					</div>
				</div>
			</div>

			<div class="elodin-bridge-admin__card" data-bridge-category="style">
				<div class="elodin-bridge-admin__feature <?php echo $reusable_block_flow_spacing_fix_enabled ? 'is-enabled' : ''; ?>">
					<label class="elodin-bridge-admin__feature-header" for="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_REUSABLE_BLOCK_FLOW_SPACING_FIX ); ?>">
						<input
							type="hidden"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_REUSABLE_BLOCK_FLOW_SPACING_FIX ); ?>"
							value="0"
						/>
						<input
							type="checkbox"
							class="elodin-bridge-admin__toggle-input elodin-bridge-admin__feature-toggle"
							id="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_REUSABLE_BLOCK_FLOW_SPACING_FIX ); ?>"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_REUSABLE_BLOCK_FLOW_SPACING_FIX ); ?>"
							value="1"
							<?php checked( $reusable_block_flow_spacing_fix_enabled ); ?>
						/>
						<span class="elodin-bridge-admin__toggle-track" aria-hidden="true">
							<span class="elodin-bridge-admin__toggle-thumb"></span>
						</span>
						<span class="elodin-bridge-admin__feature-title"><?php esc_html_e( 'Enable reusable block flow spacing fix', 'elodin-bridge' ); ?></span>
					</label>

					<div class="elodin-bridge-admin__feature-body">
						<p class="elodin-bridge-admin__description">
							<?php esc_html_e( 'Resets top and bottom flow spacing for direct children inside .editor-styles-wrapper .is-layout-flow to prevent reusable block spacing conflicts.', 'elodin-bridge' ); ?>
						</p>
					</div>
				</div>
			</div>

			<div class="elodin-bridge-admin__card" data-bridge-category="style">
				<div class="elodin-bridge-admin__feature <?php echo $mobile_fixed_background_repair_enabled ? 'is-enabled' : ''; ?>">
					<label class="elodin-bridge-admin__feature-header" for="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_MOBILE_FIXED_BACKGROUND_REPAIR ); ?>">
						<input
							type="hidden"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_MOBILE_FIXED_BACKGROUND_REPAIR ); ?>"
							value="0"
						/>
						<input
							type="checkbox"
							class="elodin-bridge-admin__toggle-input elodin-bridge-admin__feature-toggle"
							id="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_MOBILE_FIXED_BACKGROUND_REPAIR ); ?>"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_MOBILE_FIXED_BACKGROUND_REPAIR ); ?>"
							value="1"
							<?php checked( $mobile_fixed_background_repair_enabled ); ?>
						/>
						<span class="elodin-bridge-admin__toggle-track" aria-hidden="true">
							<span class="elodin-bridge-admin__toggle-thumb"></span>
						</span>
						<span class="elodin-bridge-admin__feature-title"><?php esc_html_e( 'Repair fixed-position background images on mobile', 'elodin-bridge' ); ?></span>
					</label>

					<div class="elodin-bridge-admin__feature-body">
						<p class="elodin-bridge-admin__description">
							<?php esc_html_e( 'On mobile breakpoints, scans for elements using fixed background attachments and switches them to non-fixed to avoid known browser rendering bugs.', 'elodin-bridge' ); ?>
						</p>
						<p class="elodin-bridge-admin__note">
							<?php
							echo wp_kses_post(
								sprintf(
									/* translators: %s: Can I Use URL for background-attachment support. */
									__( 'Compatibility note: <a href="%s" target="_blank" rel="noopener noreferrer">background-attachment browser support</a> is mixed on mobile. At present, Safari on iOS and the Android Browser show partial to no support.', 'elodin-bridge' ),
									esc_url( 'https://caniuse.com/background-attachment' )
								)
							);
							?>
						</p>
					</div>
				</div>
			</div>

			<div class="elodin-bridge-admin__card" data-bridge-category="editor">
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
						<span class="elodin-bridge-admin__feature-title"><?php esc_html_e( 'Disable fullscreen mode in the editor', 'elodin-bridge' ); ?></span>
					</label>

					<div class="elodin-bridge-admin__feature-body">
						<p class="elodin-bridge-admin__description">
							<?php esc_html_e( 'Turns fullscreen mode off in the block editor.', 'elodin-bridge' ); ?>
						</p>
					</div>
				</div>
			</div>

			<div class="elodin-bridge-admin__card" data-bridge-category="editor">
				<div class="elodin-bridge-admin__feature <?php echo $editor_publish_sidebar_restriction_enabled ? 'is-enabled' : ''; ?>">
					<label class="elodin-bridge-admin__feature-header" for="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_EDITOR_PUBLISH_SIDEBAR_RESTRICTION ); ?>">
						<input
							type="hidden"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_EDITOR_PUBLISH_SIDEBAR_RESTRICTION ); ?>"
							value="0"
						/>
						<input
							type="checkbox"
							class="elodin-bridge-admin__toggle-input elodin-bridge-admin__feature-toggle"
							id="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_EDITOR_PUBLISH_SIDEBAR_RESTRICTION ); ?>"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_EDITOR_PUBLISH_SIDEBAR_RESTRICTION ); ?>"
							value="1"
							<?php checked( $editor_publish_sidebar_restriction_enabled ); ?>
						/>
						<span class="elodin-bridge-admin__toggle-track" aria-hidden="true">
							<span class="elodin-bridge-admin__toggle-thumb"></span>
						</span>
						<span class="elodin-bridge-admin__feature-title"><?php esc_html_e( 'Disable publish sidebar in the editor', 'elodin-bridge' ); ?></span>
					</label>

					<div class="elodin-bridge-admin__feature-body">
						<p class="elodin-bridge-admin__description">
							<?php esc_html_e( 'Disables the publish sidebar in the block editor.', 'elodin-bridge' ); ?>
						</p>
					</div>
				</div>
			</div>

			<div class="elodin-bridge-admin__card" data-bridge-category="editor">
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

			<div class="elodin-bridge-admin__card" data-bridge-category="misc">
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

			<div class="elodin-bridge-admin__card" data-bridge-category="misc">
				<div class="elodin-bridge-admin__feature <?php echo $css_variable_autowrap_enabled ? 'is-enabled' : ''; ?>">
					<label class="elodin-bridge-admin__feature-header" for="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_CSS_VARIABLE_AUTOWRAP ); ?>">
						<input
							type="hidden"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_CSS_VARIABLE_AUTOWRAP ); ?>"
							value="0"
						/>
						<input
							type="checkbox"
							class="elodin-bridge-admin__toggle-input elodin-bridge-admin__feature-toggle"
							id="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_CSS_VARIABLE_AUTOWRAP ); ?>"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_CSS_VARIABLE_AUTOWRAP ); ?>"
							value="1"
							<?php checked( $css_variable_autowrap_enabled ); ?>
						/>
						<span class="elodin-bridge-admin__toggle-track" aria-hidden="true">
							<span class="elodin-bridge-admin__toggle-thumb"></span>
						</span>
						<span class="elodin-bridge-admin__feature-title"><?php esc_html_e( 'Auto-wrap spacing/font variables with var()', 'elodin-bridge' ); ?></span>
					</label>

					<div class="elodin-bridge-admin__feature-body">
						<p class="elodin-bridge-admin__description">
							<?php esc_html_e( 'When you type a supported token like --space-m or --font-l (or shorthands like --sm and --f2xl) in backend text fields, Bridge instantly expands it to var(...).', 'elodin-bridge' ); ?>
						</p>
					</div>
				</div>
			</div>

			<div class="elodin-bridge-admin__card" data-bridge-category="editor">
				<div class="elodin-bridge-admin__feature has-requirement <?php echo $generateblocks_boundary_highlights_enabled ? 'is-enabled' : ''; ?> <?php echo ! $generateblocks_available ? 'is-unavailable' : ''; ?>">
					<label class="elodin-bridge-admin__feature-header <?php echo ! $generateblocks_available ? 'is-disabled' : ''; ?>" for="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_GENERATEBLOCKS_BOUNDARY_HIGHLIGHTS ); ?>">
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
						<span class="elodin-bridge-admin__feature-title"><?php esc_html_e( 'Enable GenerateBlocks boundary highlights in the editor', 'elodin-bridge' ); ?></span>
					</label>
					<span class="elodin-bridge-admin__requirement-tag elodin-bridge-admin__requirement-tag--corner"><?php esc_html_e( 'Requires GenerateBlocks', 'elodin-bridge' ); ?></span>

					<div class="elodin-bridge-admin__feature-body">
						<p class="elodin-bridge-admin__description">
							<?php esc_html_e( 'Adds dashed outlines around GenerateBlocks containers/elements in the block editor to make block boundaries easier to identify while editing.', 'elodin-bridge' ); ?>
						</p>
						<?php if ( ! $generateblocks_available ) : ?>
							<p class="elodin-bridge-admin__note">
								<?php esc_html_e( 'This setting is only available when GenerateBlocks is active.', 'elodin-bridge' ); ?>
							</p>
						<?php endif; ?>
					</div>
				</div>
			</div>

			<div class="elodin-bridge-admin__card" data-bridge-category="editor">
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

			<div class="elodin-bridge-admin__card elodin-bridge-admin__card--wide" data-bridge-category="misc">
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
								<div class="elodin-bridge-admin__custom-image-sizes">
									<?php foreach ( $image_size_rows as $index => $size ) : ?>
										<div class="elodin-bridge-admin__image-size-row">
											<div class="elodin-bridge-admin__image-size-row-main">
												<label class="elodin-bridge-admin__image-size-field elodin-bridge-admin__image-size-field--slug">
													<span><?php esc_html_e( 'Slug', 'elodin-bridge' ); ?></span>
													<input
														type="text"
														class="regular-text code"
														name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_IMAGE_SIZES ); ?>[sizes][<?php echo esc_attr( (string) $index ); ?>][slug]"
														value="<?php echo esc_attr( $size['slug'] ?? '' ); ?>"
														placeholder="hero_large"
													/>
												</label>
												<label class="elodin-bridge-admin__image-size-field elodin-bridge-admin__image-size-field--label">
													<span><?php esc_html_e( 'Label', 'elodin-bridge' ); ?></span>
													<input
														type="text"
														class="regular-text"
														name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_IMAGE_SIZES ); ?>[sizes][<?php echo esc_attr( (string) $index ); ?>][label]"
														value="<?php echo esc_attr( $size['label'] ?? '' ); ?>"
														placeholder="<?php esc_attr_e( 'Hero Large', 'elodin-bridge' ); ?>"
													/>
												</label>
												<label class="elodin-bridge-admin__image-size-field elodin-bridge-admin__image-size-field--width">
													<span><?php esc_html_e( 'Width', 'elodin-bridge' ); ?></span>
													<input
														type="number"
														class="small-text"
														min="1"
														step="1"
														name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_IMAGE_SIZES ); ?>[sizes][<?php echo esc_attr( (string) $index ); ?>][width]"
														value="<?php echo esc_attr( isset( $size['width'] ) ? (string) $size['width'] : '' ); ?>"
													/>
												</label>
												<label class="elodin-bridge-admin__image-size-field elodin-bridge-admin__image-size-field--height">
													<span><?php esc_html_e( 'Height', 'elodin-bridge' ); ?></span>
													<input
														type="number"
														class="small-text"
														min="1"
														step="1"
														name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_IMAGE_SIZES ); ?>[sizes][<?php echo esc_attr( (string) $index ); ?>][height]"
														value="<?php echo esc_attr( isset( $size['height'] ) ? (string) $size['height'] : '' ); ?>"
													/>
												</label>
											</div>
											<div class="elodin-bridge-admin__image-size-row-options">
												<label class="elodin-bridge-admin__image-size-field elodin-bridge-admin__image-size-field--checkbox elodin-bridge-admin__image-size-field--crop">
													<input
														type="hidden"
														name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_IMAGE_SIZES ); ?>[sizes][<?php echo esc_attr( (string) $index ); ?>][crop]"
														value="0"
													/>
													<span class="elodin-bridge-admin__image-size-switch">
														<input
															type="checkbox"
															class="elodin-bridge-admin__toggle-input elodin-bridge-admin__image-size-toggle-input"
															name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_IMAGE_SIZES ); ?>[sizes][<?php echo esc_attr( (string) $index ); ?>][crop]"
															value="1"
															<?php checked( ! empty( $size['crop'] ) ); ?>
														/>
														<span class="elodin-bridge-admin__toggle-track elodin-bridge-admin__toggle-track--small" aria-hidden="true">
															<span class="elodin-bridge-admin__toggle-thumb"></span>
														</span>
													</span>
													<span><?php esc_html_e( 'Hard Crop', 'elodin-bridge' ); ?></span>
												</label>
												<label class="elodin-bridge-admin__image-size-field elodin-bridge-admin__image-size-field--checkbox elodin-bridge-admin__image-size-field--gallery">
													<input
														type="hidden"
														name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_IMAGE_SIZES ); ?>[sizes][<?php echo esc_attr( (string) $index ); ?>][gallery]"
														value="0"
													/>
													<span class="elodin-bridge-admin__image-size-switch">
														<input
															type="checkbox"
															class="elodin-bridge-admin__toggle-input elodin-bridge-admin__image-size-toggle-input"
															name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_IMAGE_SIZES ); ?>[sizes][<?php echo esc_attr( (string) $index ); ?>][gallery]"
															value="1"
															<?php checked( ! empty( $size['gallery'] ) ); ?>
														/>
														<span class="elodin-bridge-admin__toggle-track elodin-bridge-admin__toggle-track--small" aria-hidden="true">
															<span class="elodin-bridge-admin__toggle-thumb"></span>
														</span>
													</span>
													<span><?php esc_html_e( 'Allow In Galleries', 'elodin-bridge' ); ?></span>
												</label>
												<div class="elodin-bridge-admin__image-size-actions">
													<button type="button" class="button-link-delete elodin-bridge-admin__remove-image-size"><?php esc_html_e( 'Remove', 'elodin-bridge' ); ?></button>
												</div>
											</div>
										</div>
									<?php endforeach; ?>
								</div>
								<button type="button" class="button button-secondary elodin-bridge-admin__add-image-size"><?php esc_html_e( 'Add Custom Size', 'elodin-bridge' ); ?></button>
							</div>
						</div>

						<script type="text/template" id="elodin-bridge-image-size-row-template">
							<div class="elodin-bridge-admin__image-size-row">
								<div class="elodin-bridge-admin__image-size-row-main">
									<label class="elodin-bridge-admin__image-size-field elodin-bridge-admin__image-size-field--slug">
										<span><?php esc_html_e( 'Slug', 'elodin-bridge' ); ?></span>
										<input type="text" class="regular-text code" name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_IMAGE_SIZES ); ?>[sizes][__INDEX__][slug]" placeholder="hero_large" />
									</label>
									<label class="elodin-bridge-admin__image-size-field elodin-bridge-admin__image-size-field--label">
										<span><?php esc_html_e( 'Label', 'elodin-bridge' ); ?></span>
										<input type="text" class="regular-text" name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_IMAGE_SIZES ); ?>[sizes][__INDEX__][label]" placeholder="<?php esc_attr_e( 'Hero Large', 'elodin-bridge' ); ?>" />
									</label>
									<label class="elodin-bridge-admin__image-size-field elodin-bridge-admin__image-size-field--width">
										<span><?php esc_html_e( 'Width', 'elodin-bridge' ); ?></span>
										<input type="number" class="small-text" min="1" step="1" name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_IMAGE_SIZES ); ?>[sizes][__INDEX__][width]" />
									</label>
									<label class="elodin-bridge-admin__image-size-field elodin-bridge-admin__image-size-field--height">
										<span><?php esc_html_e( 'Height', 'elodin-bridge' ); ?></span>
										<input type="number" class="small-text" min="1" step="1" name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_IMAGE_SIZES ); ?>[sizes][__INDEX__][height]" />
									</label>
								</div>
								<div class="elodin-bridge-admin__image-size-row-options">
									<label class="elodin-bridge-admin__image-size-field elodin-bridge-admin__image-size-field--checkbox elodin-bridge-admin__image-size-field--crop">
										<input type="hidden" name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_IMAGE_SIZES ); ?>[sizes][__INDEX__][crop]" value="0" />
										<span class="elodin-bridge-admin__image-size-switch">
											<input type="checkbox" class="elodin-bridge-admin__toggle-input elodin-bridge-admin__image-size-toggle-input" name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_IMAGE_SIZES ); ?>[sizes][__INDEX__][crop]" value="1" />
											<span class="elodin-bridge-admin__toggle-track elodin-bridge-admin__toggle-track--small" aria-hidden="true">
												<span class="elodin-bridge-admin__toggle-thumb"></span>
											</span>
										</span>
										<span><?php esc_html_e( 'Hard Crop', 'elodin-bridge' ); ?></span>
									</label>
									<label class="elodin-bridge-admin__image-size-field elodin-bridge-admin__image-size-field--checkbox elodin-bridge-admin__image-size-field--gallery">
										<input type="hidden" name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_IMAGE_SIZES ); ?>[sizes][__INDEX__][gallery]" value="0" />
										<span class="elodin-bridge-admin__image-size-switch">
											<input type="checkbox" class="elodin-bridge-admin__toggle-input elodin-bridge-admin__image-size-toggle-input" name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_IMAGE_SIZES ); ?>[sizes][__INDEX__][gallery]" value="1" />
											<span class="elodin-bridge-admin__toggle-track elodin-bridge-admin__toggle-track--small" aria-hidden="true">
												<span class="elodin-bridge-admin__toggle-thumb"></span>
											</span>
										</span>
										<span><?php esc_html_e( 'Allow In Galleries', 'elodin-bridge' ); ?></span>
									</label>
									<div class="elodin-bridge-admin__image-size-actions">
										<button type="button" class="button-link-delete elodin-bridge-admin__remove-image-size"><?php esc_html_e( 'Remove', 'elodin-bridge' ); ?></button>
									</div>
								</div>
							</div>
						</script>

						<p class="elodin-bridge-admin__note">
							<strong><?php esc_html_e( 'Important:', 'elodin-bridge' ); ?></strong>
							<?php esc_html_e( 'after enabling or changing image sizes, regenerate thumbnails before those sizes appear in galleries or are available for existing images.', 'elodin-bridge' ); ?>
						</p>
					</div>
				</div>
			</div>

			<div class="elodin-bridge-admin__card elodin-bridge-admin__card--wide" data-bridge-category="style">
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
			</div>

		</form>
	</div>

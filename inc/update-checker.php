<?php

/**
 * Load Plugin Update Checker and wire GitHub updates for Elodin Bridge.
 */
function elodin_bridge_boot_update_checker() {
	$update_checker_file = ELODIN_BRIDGE_DIR . '/vendor/plugin-update-checker/plugin-update-checker.php';
	if ( ! file_exists( $update_checker_file ) ) {
		return;
	}

	require_once $update_checker_file;

	if ( ! class_exists( 'Puc_v4_Factory' ) ) {
		return;
	}

	$repository = apply_filters( 'elodin_bridge_update_checker_repository', ELODIN_BRIDGE_UPDATE_REPOSITORY );
	$branch = apply_filters( 'elodin_bridge_update_checker_branch', ELODIN_BRIDGE_UPDATE_BRANCH );

	if ( empty( $repository ) ) {
		return;
	}

	$update_checker = Puc_v4_Factory::buildUpdateChecker(
		(string) $repository,
		ELODIN_BRIDGE_DIR . '/elodin-bridge.php',
		'elodin-bridge'
	);

	if ( ! empty( $branch ) && method_exists( $update_checker, 'setBranch' ) ) {
		$update_checker->setBranch( (string) $branch );
	}
}
add_action( 'plugins_loaded', 'elodin_bridge_boot_update_checker', 5 );

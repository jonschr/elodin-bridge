<?php
/*
	Plugin Name: Elodin Bridge
	Plugin URI: https://elod.in
    Description: Just another plugin
	Version: 0.1
    Author: Jon Schroeder
    Author URI: https://elod.in

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
    GNU General Public License for more details.
*/

/* Prevent direct access to the plugin */
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Sorry, you are not allowed to access this page directly.' );
}

// Plugin constants.
define( 'ELODIN_BRIDGE_DIR', dirname( __FILE__ ) );
define( 'ELODIN_BRIDGE_URL', plugin_dir_url( __FILE__ ) );
define( 'ELODIN_BRIDGE_VERSION', '0.1' );
define( 'ELODIN_BRIDGE_OPTION_ENABLE_HEADING_PARAGRAPH_OVERRIDES', 'elodin_bridge_enable_heading_paragraph_overrides' );
define( 'ELODIN_BRIDGE_TYPOGRAPHY_RESET', '__elodin_bridge_typography_reset__' );

require_once ELODIN_BRIDGE_DIR . '/inc/settings-page.php';
require_once ELODIN_BRIDGE_DIR . '/inc/heading-paragraph-overrides.php';

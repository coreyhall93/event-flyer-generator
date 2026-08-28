<?php
/**
 * Plugin Name: Event Flyer Generator
 * Description: Front-end form that generates a printable, one-page event flyer for 1-4 events. Add the [event_flyer_form] shortcode to any page.
 * Version: 1.0.0
 * Author: Corey Hall
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: event-flyer-generator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'EFG_PATH', plugin_dir_path( __FILE__ ) );
define( 'EFG_URL', plugin_dir_url( __FILE__ ) );
define( 'EFG_VERSION', '1.0.0' );

require_once EFG_PATH . 'includes/class-shortcode.php';
require_once EFG_PATH . 'includes/class-print-view.php';

add_action(
	'plugins_loaded',
	function () {
		new EFG_Shortcode();
		new EFG_Print_View();
	}
);

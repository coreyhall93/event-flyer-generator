<?php
/**
 * Plugin Name: Event Flyer Generator
 * Plugin URI: https://github.com/coreyhall93/event-flyer-generator
 * Description: Front-end form that generates a printable, one-page event flyer for 1-4 events. Add the [event_flyer_form] shortcode to any page.
 * Version: 1.0.2
 * Author: Corey Hall
 * Requires at least: 6.4
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: event-flyer-generator
 * Domain Path: /languages
 *
 * @package event-flyer-generator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'EFG_PATH' ) ) {
	define( 'EFG_PATH', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'EFG_URL' ) ) {
	define( 'EFG_URL', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'EFG_VERSION' ) ) {
	define( 'EFG_VERSION', '1.0.2' );
}

require_once EFG_PATH . 'includes/class-efg-icons.php';
require_once EFG_PATH . 'includes/class-efg-events.php';
require_once EFG_PATH . 'includes/class-efg-shortcode.php';
require_once EFG_PATH . 'includes/class-efg-picker.php';
require_once EFG_PATH . 'includes/class-efg-print-view.php';

// Top-level on purpose: activation hooks do not fire from inside another hook.
register_activation_hook( __FILE__, array( 'EFG_Picker', 'activate' ) );

add_action(
	'init',
	function () {
		load_plugin_textdomain( 'event-flyer-generator', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}
);

add_action(
	'plugins_loaded',
	function () {
		new EFG_Events();
		new EFG_Shortcode();
		new EFG_Picker();
		new EFG_Print_View();
	}
);

<?php
/**
 * Test bootstrap.
 *
 * Deliberately does NOT load WordPress. The plugin class files guard on
 * ABSPATH and then only declare classes, so defining the constant is enough to
 * require them. Anything needing real WordPress behaviour does not belong in
 * this suite.
 *
 * @package event-flyer-generator
 */

declare( strict_types = 1 );

define( 'ABSPATH', dirname( __DIR__ ) . '/' );

require_once dirname( __DIR__ ) . '/includes/class-efg-shortcode.php';

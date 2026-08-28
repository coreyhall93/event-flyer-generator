<?php
/**
 * Demo content seeder (thin wrapper).
 *
 * The actual seeding lives in EFG_Events::seed_demo_content(), so it works
 * without knowing where the plugin folder ended up. Playground renames a
 * git:directory install to a slug derived from the repo URL, so any hardcoded
 * plugin path is wrong there.
 *
 * Run with:
 *
 *   wp eval-file demo/seed.php
 *   wp eval 'EFG_Events::seed_demo_content();'
 *
 * @package event-flyer-generator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'EFG_Events' ) ) {
	EFG_Events::seed_demo_content();
}

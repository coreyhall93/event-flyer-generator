<?php
/**
 * Removes everything this plugin ever wrote.
 *
 * The plugin stores flyer payloads and rate-limit markers as transients. Those
 * live in wp_options and, once the plugin is gone, nothing would ever expire
 * them through the transient API again — so clear them explicitly here.
 *
 * @package event-flyer-generator
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

$efg_prefixes = array( 'efg_flyer_', 'efg_rl_' );

foreach ( $efg_prefixes as $efg_prefix ) {
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery -- transient cleanup on uninstall; no core API takes a prefix.
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
			$wpdb->esc_like( '_transient_' . $efg_prefix ) . '%',
			$wpdb->esc_like( '_transient_timeout_' . $efg_prefix ) . '%'
		)
	);
}

// Sites on a persistent object cache keep transients out of the options table.
wp_cache_flush();

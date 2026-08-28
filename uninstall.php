<?php
/**
 * Removes this plugin's stored data on uninstall.
 *
 * The plugin stores flyer payloads and rate-limit markers as transients. On a
 * site with no persistent object cache those are rows in wp_options, and once
 * the plugin is gone nothing would expire them through the transient API again,
 * so they are deleted explicitly here.
 *
 * On a site WITH a persistent object cache the transients live in that cache
 * instead, keyed individually, and there is no supported way to enumerate them.
 * They are deliberately left to expire on their own TTL (one hour for flyers,
 * fifteen seconds for throttle markers).
 *
 * Do NOT "fix" that with wp_cache_flush(): it empties the entire object cache
 * for WordPress core and every other plugin, so uninstalling this plugin would
 * cold-cache the whole site. Evicting a handful of short-lived keys is not
 * worth that.
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

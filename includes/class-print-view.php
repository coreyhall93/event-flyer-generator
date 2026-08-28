<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Intercepts requests carrying ?efg_flyer=<token> and renders the
 * print-ready flyer full-page, bypassing the theme entirely.
 */
class EFG_Print_View {

	public function __construct() {
		add_action( 'template_redirect', array( $this, 'maybe_render' ), 1 );
	}

	public function maybe_render() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only view keyed by an unguessable token.
		if ( empty( $_GET['efg_flyer'] ) ) {
			return;
		}

		// Don't replace a feed, robots.txt or favicon with an HTML flyer.
		if ( is_feed() || is_robots() || ( function_exists( 'is_favicon' ) && is_favicon() ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only view keyed by an unguessable token.
		$token = sanitize_key( wp_unslash( $_GET['efg_flyer'] ) );
		$data  = get_transient( 'efg_flyer_' . $token );

		if ( ! $data || empty( $data['events'] ) ) {
			wp_die( esc_html__( 'This flyer preview has expired. Go back and generate a new one.', 'event-flyer-generator' ) );
		}

		// The flyer is transient-backed and expires within the hour, so a page
		// cache must never hold it. handle_404() may already have sent a 404 if
		// the token rode in on a path that doesn't resolve; this is a real document.
		nocache_headers();
		status_header( 200 );

		include EFG_PATH . 'templates/flyer-print.php';
		exit;
	}
}

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
		if ( empty( $_GET['efg_flyer'] ) ) {
			return;
		}

		$token = sanitize_key( wp_unslash( $_GET['efg_flyer'] ) );
		$data  = get_transient( 'efg_flyer_' . $token );

		if ( ! $data || empty( $data['events'] ) ) {
			wp_die( esc_html__( 'This flyer preview has expired. Go back and generate a new one.', 'event-flyer-generator' ) );
		}

		include EFG_PATH . 'templates/flyer-print.php';
		exit;
	}
}

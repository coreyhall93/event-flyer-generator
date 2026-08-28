<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders the front-end flyer-builder form and handles its submission.
 */
class EFG_Shortcode {

	const MAX_EVENTS = 4;

	public function __construct() {
		add_shortcode( 'event_flyer_form', array( $this, 'render_form' ) );
		add_action( 'template_redirect', array( $this, 'handle_submit' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'assets' ) );
	}

	public function assets() {
		if ( ! is_singular() || ! has_shortcode( get_post()->post_content, 'event_flyer_form' ) ) {
			return;
		}
		wp_enqueue_style( 'efg-form', EFG_URL . 'assets/form.css', array(), EFG_VERSION );
		wp_enqueue_script( 'efg-form', EFG_URL . 'assets/form.js', array(), EFG_VERSION, true );
	}

	/**
	 * Catches the form POST before any theme output, validates it, stashes the
	 * flyer data in a transient, and redirects to the print/preview view.
	 */
	public function handle_submit() {
		if ( empty( $_POST['efg_submit'] ) ) {
			return;
		}

		if ( ! isset( $_POST['efg_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['efg_nonce'] ) ), 'efg_create_flyer' ) ) {
			wp_die( esc_html__( 'Security check failed. Go back and try again.', 'event-flyer-generator' ) );
		}

		$program_name = sanitize_text_field( wp_unslash( $_POST['program_name'] ?? 'WordPress Local Connect' ) );
		$footer_line  = sanitize_text_field( wp_unslash( $_POST['footer_line'] ?? 'Open to all · Every skill level' ) );

		$titles = wp_unslash( $_POST['event_title'] ?? array() );
		$dates  = wp_unslash( $_POST['event_date'] ?? array() );
		$times  = wp_unslash( $_POST['event_time'] ?? array() );
		$descs  = wp_unslash( $_POST['event_description'] ?? array() );
		$venues = wp_unslash( $_POST['event_venue'] ?? array() );
		$addrs  = wp_unslash( $_POST['event_address'] ?? array() );
		$icons  = wp_unslash( $_POST['event_icon'] ?? array() );

		$events = array();
		for ( $i = 0; $i < self::MAX_EVENTS; $i++ ) {
			$title = isset( $titles[ $i ] ) ? sanitize_text_field( $titles[ $i ] ) : '';
			if ( '' === $title ) {
				continue;
			}
			$events[] = array(
				'date'        => isset( $dates[ $i ] ) ? sanitize_text_field( $dates[ $i ] ) : '',
				'time'        => isset( $times[ $i ] ) ? sanitize_text_field( $times[ $i ] ) : '',
				'title'       => $title,
				'description' => isset( $descs[ $i ] ) ? sanitize_textarea_field( $descs[ $i ] ) : '',
				'venue'       => isset( $venues[ $i ] ) ? sanitize_text_field( $venues[ $i ] ) : '',
				'address'     => isset( $addrs[ $i ] ) ? sanitize_text_field( $addrs[ $i ] ) : '',
				'icon'        => isset( $icons[ $i ] ) ? sanitize_key( $icons[ $i ] ) : 'tip',
			);
		}

		if ( empty( $events ) ) {
			wp_die(
				wp_kses_post( __( 'Add at least one event before generating a flyer. <a href="javascript:history.back()">Go back</a>', 'event-flyer-generator' ) )
			);
		}

		$token = wp_generate_password( 16, false );
		set_transient(
			'efg_flyer_' . $token,
			array(
				'program_name' => $program_name,
				'footer_line'  => $footer_line,
				'events'       => $events,
			),
			HOUR_IN_SECONDS
		);

		$redirect_base = wp_get_referer() ? wp_get_referer() : home_url( '/' );
		wp_safe_redirect( add_query_arg( 'efg_flyer', $token, $redirect_base ) );
		exit;
	}

	public function render_form() {
		ob_start();
		include EFG_PATH . 'templates/form.php';
		return ob_get_clean();
	}
}

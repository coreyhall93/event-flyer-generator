<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders the front-end flyer-builder form and handles its submission.
 */
class EFG_Shortcode {

	const MAX_EVENTS = 4;

	/** Per-field character caps. Nothing here is prose; a flyer has to fit on one page. */
	const MAX_FIELD_LEN = 200;
	const MAX_DESC_LEN  = 500;

	/** Seconds one IP must wait between flyer generations. */
	const THROTTLE_SECONDS = 15;

	public function __construct() {
		add_shortcode( 'event_flyer_form', array( $this, 'render_form' ) );
		add_action( 'template_redirect', array( $this, 'handle_submit' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'assets' ) );
	}

	public function assets() {
		$post = get_post();
		if ( ! is_singular() || ! $post || ! has_shortcode( (string) $post->post_content, 'event_flyer_form' ) ) {
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
			wp_die(
				esc_html__( 'Security check failed. Go back and try again.', 'event-flyer-generator' ),
				'',
				array( 'back_link' => true )
			);
		}

		// This endpoint is public and writes to the options table, so throttle it.
		// The nonce is not a control here: logged-out visitors all share one nonce
		// that stays valid for the better part of a day.
		$throttle_key = 'efg_rl_' . md5( self::client_ip() );
		if ( get_transient( $throttle_key ) ) {
			wp_die(
				esc_html__( 'You just generated a flyer. Wait a few seconds and try again.', 'event-flyer-generator' ),
				'',
				array( 'back_link' => true )
			);
		}

		$program_name = self::clean( $_POST['program_name'] ?? '' );
		$footer_line  = self::clean( $_POST['footer_line'] ?? '' );

		if ( '' === $program_name ) {
			wp_die(
				esc_html__( 'Add a program name before generating a flyer.', 'event-flyer-generator' ),
				'',
				array( 'back_link' => true )
			);
		}

		$fields = array();
		foreach ( array( 'title', 'date', 'time', 'description', 'venue', 'address', 'icon' ) as $name ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce verified above.
			$fields[ $name ] = (array) ( $_POST[ 'event_' . $name ] ?? array() );
		}

		$events = array();
		for ( $i = 0; $i < self::MAX_EVENTS; $i++ ) {
			$title = self::clean( $fields['title'][ $i ] ?? '' );
			if ( '' === $title ) {
				continue;
			}
			$events[] = array(
				'date'        => self::clean( $fields['date'][ $i ] ?? '' ),
				'time'        => self::clean( $fields['time'][ $i ] ?? '' ),
				'title'       => $title,
				'description' => self::clean( $fields['description'][ $i ] ?? '', true ),
				'venue'       => self::clean( $fields['venue'][ $i ] ?? '' ),
				'address'     => self::clean( $fields['address'][ $i ] ?? '' ),
				'icon'        => sanitize_key( (string) ( $fields['icon'][ $i ] ?? 'tip' ) ),
			);
		}

		if ( empty( $events ) ) {
			wp_die(
				esc_html__( 'Add at least one event before generating a flyer.', 'event-flyer-generator' ),
				'',
				array( 'back_link' => true )
			);
		}

		set_transient( $throttle_key, 1, self::THROTTLE_SECONDS );

		// Must be lowercase hex: the print view reads this token back through
		// sanitize_key(), which lowercases. A mixed-case token (as
		// wp_generate_password() produces) survives that round trip only when the
		// backing store is case-insensitive, so it works on a plain options table
		// but misses on every host running a persistent object cache.
		$token = bin2hex( random_bytes( 16 ) );
		set_transient(
			'efg_flyer_' . $token,
			array(
				'program_name' => $program_name,
				'footer_line'  => $footer_line,
				'events'       => $events,
			),
			HOUR_IN_SECONDS
		);

		// Not wp_get_referer(): the form posts to itself, so that hits its own
		// self-referer check and returns false, landing everyone on the home page.
		// The form carries its own permalink instead, validated before use.
		$redirect_base = wp_validate_redirect(
			esc_url_raw( wp_unslash( $_POST['efg_return'] ?? '' ) ),
			home_url( '/' )
		);

		wp_safe_redirect( add_query_arg( 'efg_flyer', $token, $redirect_base ) );
		exit;
	}

	/**
	 * Sanitize and length-cap one submitted value.
	 *
	 * @param mixed $value    Raw (still slashed) input.
	 * @param bool  $textarea Whether to preserve line breaks.
	 * @return string
	 */
	private static function clean( $value, $textarea = false ) {
		if ( ! is_scalar( $value ) ) {
			return '';
		}
		$value = wp_unslash( (string) $value );
		$value = $textarea ? sanitize_textarea_field( $value ) : sanitize_text_field( $value );
		$limit = $textarea ? self::MAX_DESC_LEN : self::MAX_FIELD_LEN;

		return function_exists( 'mb_substr' ) ? mb_substr( $value, 0, $limit ) : substr( $value, 0, $limit );
	}

	/**
	 * Best-effort client IP, used only as a throttle key.
	 *
	 * @return string
	 */
	private static function client_ip() {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';

		return '' !== $ip ? $ip : 'unknown';
	}

	public function render_form() {
		ob_start();
		include EFG_PATH . 'templates/form.php';
		return ob_get_clean();
	}
}

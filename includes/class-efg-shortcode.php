<?php
/**
 * Front-end flyer-builder form: rendering and submission handling.
 *
 * @package event-flyer-generator
 */

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

	/**
	 * Register the shortcode, the submit handler and the form assets.
	 */
	public function __construct() {
		add_shortcode( 'event_flyer_form', array( $this, 'render_form' ) );
		add_action( 'template_redirect', array( $this, 'handle_submit' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'assets' ) );
	}

	/**
	 * Enqueue the form's CSS and JS, only on a page that renders the shortcode.
	 */
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

		self::throttle_guard();

		// Sanitized inline rather than inside the helper, so the security-relevant
		// step is visible at the point of use (and statically checkable).
		$program_name = self::cap( sanitize_text_field( wp_unslash( $_POST['program_name'] ?? '' ) ) );
		$footer_line  = self::cap( sanitize_text_field( wp_unslash( $_POST['footer_line'] ?? '' ) ) );

		if ( '' === $program_name ) {
			wp_die(
				esc_html__( 'Add a program name before generating a flyer.', 'event-flyer-generator' ),
				'',
				array( 'back_link' => true )
			);
		}

		// Collected raw here and sanitized per element in the loop below, where the
		// field type is known (textarea vs text vs key). Every value that reaches
		// $events has gone through sanitize_textarea_field(), sanitize_text_field()
		// or sanitize_key(); nothing raw is stored or echoed.
		$fields = array();
		foreach ( array( 'title', 'date', 'time', 'description', 'venue', 'address', 'icon' ) as $name ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- nonce verified above; sanitized per element below.
			$raw             = $_POST[ 'event_' . $name ] ?? array();
			$fields[ $name ] = is_array( $raw ) ? $raw : array();
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

		// Not wp_get_referer(): the form posts to itself, so that hits its own
		// self-referer check and returns false, landing everyone on the home page.
		// The form carries its own permalink instead, validated before use.
		$redirect_base = wp_validate_redirect(
			esc_url_raw( wp_unslash( $_POST['efg_return'] ?? '' ) ),
			home_url( '/' )
		);

		self::stash_and_redirect( $program_name, $footer_line, $events, $redirect_base );
	}

	/**
	 * Refuse a second flyer from the same IP too soon after the last one.
	 *
	 * These endpoints are public and write to the options table. The nonce is
	 * not a control here: logged-out visitors all share one nonce that stays
	 * valid for the better part of a day.
	 */
	public static function throttle_guard() {
		$key = 'efg_rl_' . md5( self::client_ip() );

		if ( get_transient( $key ) ) {
			wp_die(
				esc_html__( 'You just generated a flyer. Wait a few seconds and try again.', 'event-flyer-generator' ),
				'',
				array( 'back_link' => true )
			);
		}

		set_transient( $key, 1, self::THROTTLE_SECONDS );
	}

	/**
	 * Stash a flyer payload and redirect to its print view.
	 *
	 * Shared by the manual form and the event picker so both go through exactly
	 * the same token, storage and redirect path.
	 *
	 * @param string $program_name  Flyer headline.
	 * @param string $footer_line   Footer line.
	 * @param array  $events        Flyer rows.
	 * @param string $redirect_base Validated URL to return to.
	 */
	public static function stash_and_redirect( $program_name, $footer_line, $events, $redirect_base ) {
		$token = self::generate_token();

		set_transient(
			'efg_flyer_' . $token,
			array(
				'program_name' => $program_name,
				'footer_line'  => $footer_line,
				'events'       => $events,
			),
			HOUR_IN_SECONDS
		);

		wp_safe_redirect( add_query_arg( 'efg_flyer', $token, $redirect_base ) );
		exit;
	}

	/**
	 * Generate the lookup token for a flyer.
	 *
	 * MUST return only lowercase [a-z0-9]. The print view reads the token back
	 * through sanitize_key(), which lowercases and strips, so any token that is
	 * not already a fixed point of sanitize_key() is written under one key and
	 * read under another. That mismatch is invisible on a plain options table
	 * (case-insensitive collation) and breaks the plugin on every host running a
	 * persistent object cache, where get_transient() uses a case-sensitive
	 * cache key and never falls back to the database.
	 *
	 * wp_generate_password() is mixed case and fails this. Do not use it here.
	 * Covered by tests/TokenTest.php.
	 *
	 * @return string
	 */
	public static function generate_token() {
		return bin2hex( random_bytes( 16 ) );
	}

	/**
	 * Length-cap an already-sanitized value.
	 *
	 * A flyer has to fit on one page, and this endpoint is public, so nothing
	 * unbounded gets stored.
	 *
	 * @param string $value    Sanitized value.
	 * @param bool   $textarea Whether the longer description cap applies.
	 * @return string
	 */
	private static function cap( $value, $textarea = false ) {
		$value = (string) $value;
		$limit = $textarea ? self::MAX_DESC_LEN : self::MAX_FIELD_LEN;

		$length = function_exists( 'mb_strlen' ) ? mb_strlen( $value ) : strlen( $value );
		if ( $length <= $limit ) {
			return $value;
		}

		// Trim on a word boundary and mark it, so a cap reads as deliberate
		// rather than looking like the flyer broke mid-sentence.
		$trimmed = function_exists( 'mb_substr' ) ? mb_substr( $value, 0, $limit - 1 ) : substr( $value, 0, $limit - 1 );
		$space   = function_exists( 'mb_strrpos' ) ? mb_strrpos( $trimmed, ' ' ) : strrpos( $trimmed, ' ' );

		if ( false !== $space && $space > (int) ( $limit * 0.6 ) ) {
			$trimmed = function_exists( 'mb_substr' ) ? mb_substr( $trimmed, 0, $space ) : substr( $trimmed, 0, $space );
		}

		return rtrim( $trimmed, " \t\n\r\0\x0B,;:." ) . '…';
	}

	/**
	 * Unslash, sanitize and length-cap one raw submitted event field.
	 *
	 * Used for the event arrays, where the field type is only known here.
	 * sanitize_text_field()/sanitize_textarea_field() both return '' for arrays
	 * and objects, so a nested payload cannot slip through.
	 *
	 * @param mixed $value    Raw, still-slashed input.
	 * @param bool  $textarea Whether to preserve line breaks.
	 * @return string
	 */
	private static function clean( $value, $textarea = false ) {
		if ( ! is_scalar( $value ) ) {
			return '';
		}
		$value = wp_unslash( (string) $value );
		$value = $textarea ? sanitize_textarea_field( $value ) : sanitize_text_field( $value );

		return self::cap( $value, $textarea );
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

	/**
	 * Render the form.
	 *
	 * @return string Form markup.
	 */
	public function render_form() {
		ob_start();
		include EFG_PATH . 'templates/form.php';
		return ob_get_clean();
	}
}

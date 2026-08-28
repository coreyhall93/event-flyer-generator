<?php
/**
 * "Pick from your events" flyer builder.
 *
 * @package event-flyer-generator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders a list of the site's events and builds a flyer from a selection.
 *
 * Two entry points, matching the two things an organizer actually wants:
 * one flyer for a single event, or one flyer covering the next few.
 */
class EFG_Picker {

	/**
	 * Hook registration.
	 */
	public function __construct() {
		add_shortcode( 'event_flyer_picker', array( $this, 'render' ) );
		add_action( 'template_redirect', array( $this, 'handle_submit' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'assets' ) );
	}

	/**
	 * Load the picker's styles and script only where the shortcode runs.
	 */
	public function assets() {
		$post = get_post();
		if ( ! is_singular() || ! $post || ! has_shortcode( (string) $post->post_content, 'event_flyer_picker' ) ) {
			return;
		}
		wp_enqueue_style( 'efg-form', EFG_URL . 'assets/form.css', array(), EFG_VERSION );
		wp_enqueue_script( 'efg-picker', EFG_URL . 'assets/picker.js', array(), EFG_VERSION, true );
		wp_localize_script(
			'efg-picker',
			'efgPicker',
			array(
				'max'     => EFG_Shortcode::MAX_EVENTS,
				/* translators: %d: maximum number of events on one flyer. */
				'tooMany' => sprintf( __( 'Pick up to %d events for one flyer.', 'event-flyer-generator' ), EFG_Shortcode::MAX_EVENTS ),
			)
		);
	}

	/**
	 * Build a flyer from the selected events and hand off to the print view.
	 */
	public function handle_submit() {
		if ( empty( $_POST['efg_picker_submit'] ) ) {
			return;
		}

		if ( ! isset( $_POST['efg_picker_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['efg_picker_nonce'] ) ), 'efg_picker' ) ) {
			wp_die(
				esc_html__( 'Security check failed. Go back and try again.', 'event-flyer-generator' ),
				'',
				array( 'back_link' => true )
			);
		}

		EFG_Shortcode::throttle_guard();

		// absint() on every element is the sanitizing step; ids are then used
		// only as post lookups, never echoed.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce verified above.
		$ids = isset( $_POST['event_ids'] ) ? array_map( 'absint', (array) wp_unslash( $_POST['event_ids'] ) ) : array();
		$ids = array_slice( array_filter( $ids ), 0, EFG_Shortcode::MAX_EVENTS );

		if ( empty( $ids ) ) {
			wp_die(
				esc_html__( 'Pick at least one event before generating a flyer.', 'event-flyer-generator' ),
				'',
				array( 'back_link' => true )
			);
		}

		$events = array();
		foreach ( $ids as $id ) {
			$row = EFG_Events::to_flyer_row( $id );
			if ( $row ) {
				$events[] = $row;
			}
		}

		if ( empty( $events ) ) {
			wp_die(
				esc_html__( 'Those events could not be found. Go back and try again.', 'event-flyer-generator' ),
				'',
				array( 'back_link' => true )
			);
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce verified above.
		$program_name = sanitize_text_field( wp_unslash( $_POST['program_name'] ?? '' ) );
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce verified above.
		$footer_line = sanitize_text_field( wp_unslash( $_POST['footer_line'] ?? '' ) );

		if ( '' === $program_name ) {
			$program_name = get_bloginfo( 'name' );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce verified above.
		$return = wp_validate_redirect( esc_url_raw( wp_unslash( $_POST['efg_return'] ?? '' ) ), home_url( '/' ) );

		EFG_Shortcode::stash_and_redirect( $program_name, $footer_line, $events, $return );
	}

	/**
	 * Render the picker.
	 *
	 * @return string
	 */
	public function render() {
		ob_start();
		include EFG_PATH . 'templates/picker.php';
		return ob_get_clean();
	}
}

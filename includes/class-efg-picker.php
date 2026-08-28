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
		add_action( 'template_redirect', array( $this, 'handle_add_event' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'assets' ) );
		add_action( 'admin_notices', array( $this, 'activation_notice' ) );
	}

	/** Option holding the id of the page created on activation. */
	const PAGE_OPTION = 'efg_builder_page_id';

	/**
	 * On activation, give the plugin a front-end home.
	 *
	 * Without this, activating does nothing visible: the shortcodes exist but
	 * there is no page carrying them, so the plugin looks broken until you
	 * happen to read the readme. Creates the page only if one is not already
	 * there, and never touches the site's front-page setting.
	 */
	public static function activate() {
		$existing = get_page_by_path( 'flyer-builder' );

		if ( $existing ) {
			update_option( self::PAGE_OPTION, $existing->ID );
			return;
		}

		$page_id = wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_title'   => __( 'Flyer Builder', 'event-flyer-generator' ),
				'post_name'    => 'flyer-builder',
				'post_content' => '<!-- wp:shortcode -->[event_flyer_picker]<!-- /wp:shortcode -->',
				'post_status'  => 'publish',
			)
		);

		if ( $page_id && ! is_wp_error( $page_id ) ) {
			update_option( self::PAGE_OPTION, $page_id );
			set_transient( 'efg_just_activated', 1, MINUTE_IN_SECONDS * 5 );
		}
	}

	/**
	 * Point the admin at the page that was just created.
	 */
	public function activation_notice() {
		if ( ! get_transient( 'efg_just_activated' ) || ! current_user_can( 'edit_posts' ) ) {
			return;
		}

		delete_transient( 'efg_just_activated' );

		$page_id = (int) get_option( self::PAGE_OPTION );
		if ( ! $page_id ) {
			return;
		}

		printf(
			'<div class="notice notice-success is-dismissible"><p>%s <a href="%s">%s</a></p></div>',
			esc_html__( 'Event Flyer Generator is ready.', 'event-flyer-generator' ),
			esc_url( (string) get_permalink( $page_id ) ),
			esc_html__( 'Open your flyer builder', 'event-flyer-generator' )
		);
	}

	/**
	 * Whether the current user may add events.
	 *
	 * Generating a flyer from events that already exist is public. CREATING
	 * events is not: an anonymous write that inserts posts is a spam vector on
	 * any real site. In the Playground demo the visitor is an admin, so the
	 * flow is still visible there.
	 *
	 * @return bool
	 */
	public static function can_add_events() {
		// When GatherPress is running it owns the events. Adding them here would
		// be a second, worse place to manage the same thing, so the built-in
		// form is strictly the no-GatherPress fallback.
		if ( EFG_Events::using_gatherpress() ) {
			return false;
		}

		/**
		 * Filters who may create events from the front end.
		 *
		 * Defaults to users who can already create content. Return true to open
		 * it up — only sensible on a demo or a trusted intranet, since it lets
		 * anonymous visitors insert posts.
		 *
		 * @param bool $allowed Whether the current user may add events.
		 */
		return (bool) apply_filters( 'efg_can_add_events', current_user_can( 'edit_posts' ) );
	}

	/**
	 * Create an event from the inline "Add a new event" form.
	 */
	public function handle_add_event() {
		if ( empty( $_POST['efg_add_event'] ) ) {
			return;
		}

		if ( ! isset( $_POST['efg_add_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['efg_add_nonce'] ) ), 'efg_add_event' ) ) {
			wp_die(
				esc_html__( 'Security check failed. Go back and try again.', 'event-flyer-generator' ),
				'',
				array( 'back_link' => true )
			);
		}

		if ( ! self::can_add_events() ) {
			wp_die(
				esc_html__( 'You do not have permission to add events.', 'event-flyer-generator' ),
				'',
				array( 'back_link' => true )
			);
		}

		$title = sanitize_text_field( wp_unslash( $_POST['new_title'] ?? '' ) );

		if ( '' === $title ) {
			wp_die(
				esc_html__( 'An event needs a title.', 'event-flyer-generator' ),
				'',
				array( 'back_link' => true )
			);
		}

		$id = wp_insert_post(
			array(
				'post_type'    => EFG_Events::POST_TYPE,
				'post_title'   => $title,
				'post_excerpt' => sanitize_textarea_field( wp_unslash( $_POST['new_description'] ?? '' ) ),
				'post_status'  => 'publish',
				'menu_order'   => 100,
			),
			true
		);

		if ( is_wp_error( $id ) ) {
			wp_die( esc_html( $id->get_error_message() ), '', array( 'back_link' => true ) );
		}

		$submitted = array(
			'date'    => sanitize_text_field( wp_unslash( $_POST['new_date'] ?? '' ) ),
			'time'    => sanitize_text_field( wp_unslash( $_POST['new_time'] ?? '' ) ),
			'venue'   => sanitize_text_field( wp_unslash( $_POST['new_venue'] ?? '' ) ),
			'address' => sanitize_text_field( wp_unslash( $_POST['new_address'] ?? '' ) ),
			'icon'    => EFG_Icons::exists( sanitize_key( wp_unslash( $_POST['new_icon'] ?? '' ) ) ) ? sanitize_key( wp_unslash( $_POST['new_icon'] ?? '' ) ) : EFG_Icons::FALLBACK,
		);

		foreach ( EFG_Events::FIELDS as $key => $meta_key ) {
			update_post_meta( $id, $meta_key, $submitted[ $key ] );
		}

		// Come back with the new event already ticked, so it is obvious it landed.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce verified above.
		$return = wp_validate_redirect( esc_url_raw( wp_unslash( $_POST['efg_return'] ?? '' ) ), home_url( '/' ) );

		wp_safe_redirect( add_query_arg( 'efg_added', $id, $return ) );
		exit;
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
				'max'        => EFG_Shortcode::MAX_EVENTS,
				/* translators: 1: number of events chosen. 2: maximum allowed. */
				'countLabel' => __( '%1$d of %2$d selected', 'event-flyer-generator' ),
				'emptyLabel' => __( 'Nothing selected yet.', 'event-flyer-generator' ),
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

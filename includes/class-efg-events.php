<?php
/**
 * The event source the flyer builder reads from.
 *
 * @package event-flyer-generator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers a minimal event post type and maps events into flyer rows.
 *
 * This exists to demonstrate the flow the plugin is actually aiming at: an
 * organizer picks events they have already entered rather than retyping them
 * into a blank form. In a GatherPress integration this class is the seam —
 * GatherPress would supply the events and everything downstream stays the same.
 * It deliberately does NOT pretend to read GatherPress data today.
 */
class EFG_Events {

	const POST_TYPE = 'efg_event';

	/**
	 * Event detail fields, mapped to their post meta keys.
	 *
	 * @var array
	 */
	const FIELDS = array(
		'date'    => '_efg_date',
		'time'    => '_efg_time',
		'venue'   => '_efg_venue',
		'address' => '_efg_address',
		'icon'    => '_efg_icon',
	);

	/**
	 * Hook registration.
	 */
	/** Option set by the demo seeder; never set on a normal install. */
	const DEMO_OPTION = 'efg_demo_mode';

	/**
	 * Hook registration.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_filter( 'efg_can_add_events', array( $this, 'allow_in_demo' ) );
	}

	/**
	 * Let anonymous visitors add events on a seeded demo site only.
	 *
	 * The demo is throwaway and the whole point is that people can try the
	 * flow. Any real install keeps the default capability check.
	 *
	 * @param bool $allowed Current decision.
	 * @return bool
	 */
	public function allow_in_demo( $allowed ) {
		return $allowed || (bool) get_option( self::DEMO_OPTION );
	}

	/**
	 * Register the event post type.
	 */
	public function register_post_type() {
		register_post_type(
			self::POST_TYPE,
			array(
				'labels'       => array(
					'name'          => __( 'Events', 'event-flyer-generator' ),
					'singular_name' => __( 'Event', 'event-flyer-generator' ),
					'add_new_item'  => __( 'Add New Event', 'event-flyer-generator' ),
					'edit_item'     => __( 'Edit Event', 'event-flyer-generator' ),
				),
				'public'       => true,
				'has_archive'  => false,
				'show_in_rest' => true,
				'menu_icon'    => 'dashicons-calendar-alt',
				'supports'     => array( 'title', 'excerpt', 'custom-fields' ),
			)
		);
	}

	/**
	 * Every event, newest first.
	 *
	 * @param int $limit Maximum number to return.
	 * @return WP_Post[]
	 */
	public static function all( $limit = 20 ) {
		return get_posts(
			array(
				'post_type'        => self::POST_TYPE,
				'post_status'      => 'publish',
				'numberposts'      => (int) $limit,
				'orderby'          => 'menu_order date',
				'order'            => 'ASC',
				'suppress_filters' => false,
			)
		);
	}

	/**
	 * Sample events, used only by seed_demo_content().
	 *
	 * @return array
	 */
	public static function demo_events() {
		return array(
			array(
				'title'   => 'Intro to the Block Editor',
				'excerpt' => 'A hands-on walkthrough for anyone new to blocks. Bring questions.',
				'date'    => 'OCT 27',
				'time'    => '7PM',
				'venue'   => 'Public Library',
				'address' => '509 S Dargan St',
				'icon'    => 'tip',
			),
			array(
				'title'   => 'Build Your First Block Theme',
				'excerpt' => 'Bring a laptop. We start from an empty folder and finish with a working theme.',
				'date'    => 'NOV 10',
				'time'    => '6:30PM',
				'venue'   => 'Hive Coworking',
				'address' => '181 W Evans St',
				'icon'    => 'people',
			),
			array(
				'title'   => 'Contributor Day',
				'excerpt' => 'Pick a team, make your first contribution to WordPress. All skill levels.',
				'date'    => 'NOV 22',
				'time'    => '10AM',
				'venue'   => 'Community College',
				'address' => '2715 W Lucas St',
				'icon'    => 'people',
			),
			array(
				'title'   => 'Show and Tell',
				'excerpt' => 'Five minutes each. Show what you built this year.',
				'date'    => 'DEC 8',
				'time'    => '7PM',
				'venue'   => 'Public Library',
				'address' => '509 S Dargan St',
				'icon'    => 'megaphone',
			),
			array(
				'title'   => 'Accessibility Clinic',
				'excerpt' => 'Bring a site. We audit it together and leave with a fix list.',
				'date'    => 'JAN 12',
				'time'    => '6PM',
				'venue'   => 'Hive Coworking',
				'address' => '181 W Evans St',
				'icon'    => 'tip',
			),
			array(
				'title'   => 'Plugin Night',
				'excerpt' => 'Ship something small. We start and finish a plugin in one sitting.',
				'date'    => 'JAN 26',
				'time'    => '7PM',
				'venue'   => 'Public Library',
				'address' => '509 S Dargan St',
				'icon'    => 'megaphone',
			),
		);
	}

	/**
	 * Create sample events and the two builder pages.
	 *
	 * Demo helper. Never hooked — it only runs when called explicitly, by the
	 * Playground blueprint or by hand:
	 *
	 *   wp eval 'EFG_Events::seed_demo_content();'
	 *
	 * Idempotent: existing events and pages are left alone.
	 *
	 * @return int Number of events created.
	 */
	public static function seed_demo_content() {
		$created = 0;
		$order   = 0;

		foreach ( self::demo_events() as $seed ) {
			++$order;

			// get_page_by_title() is deprecated as of WP 6.2.
			$existing = get_posts(
				array(
					'post_type'        => self::POST_TYPE,
					'title'            => $seed['title'],
					'post_status'      => 'any',
					'numberposts'      => 1,
					'fields'           => 'ids',
					'suppress_filters' => false,
				)
			);

			if ( ! empty( $existing ) ) {
				continue;
			}

			$id = wp_insert_post(
				array(
					'post_type'    => self::POST_TYPE,
					'post_title'   => $seed['title'],
					'post_excerpt' => $seed['excerpt'],
					'post_status'  => 'publish',
					'menu_order'   => $order,
				)
			);

			if ( is_wp_error( $id ) || ! $id ) {
				continue;
			}

			foreach ( self::FIELDS as $key => $meta_key ) {
				update_post_meta( $id, $meta_key, $seed[ $key ] );
			}

			++$created;
		}

		self::seed_pages();
		self::clear_demo_clutter();
		update_option( self::DEMO_OPTION, 1 );

		return $created;
	}

	/**
	 * Strip the stock WordPress/theme furniture that distracts from the demo.
	 *
	 * Removes the "Sample Page" (which also takes it out of the nav) and blanks
	 * the theme's footer template part, whose placeholder address and opening
	 * hours read as if they belong to the flyer tool.
	 */
	private static function clear_demo_clutter() {
		$sample = get_page_by_path( 'sample-page' );
		if ( $sample ) {
			wp_delete_post( $sample->ID, true );
		}

		$theme = get_stylesheet();

		$existing = get_posts(
			array(
				'post_type'        => 'wp_template_part',
				'name'             => 'footer',
				'post_status'      => 'any',
				'numberposts'      => 1,
				'suppress_filters' => false,
				'tax_query'        => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- one-off demo setup.
					array(
						'taxonomy' => 'wp_theme',
						'field'    => 'name',
						'terms'    => $theme,
					),
				),
			)
		);

		// An empty group keeps the footer slot valid while rendering nothing.
		$blank = '<!-- wp:group {"layout":{"type":"constrained"}} --><div class="wp-block-group"></div><!-- /wp:group -->';

		if ( ! empty( $existing ) ) {
			wp_update_post(
				array(
					'ID'           => $existing[0]->ID,
					'post_content' => $blank,
				)
			);
			return;
		}

		$id = wp_insert_post(
			array(
				'post_type'    => 'wp_template_part',
				'post_name'    => 'footer',
				'post_title'   => 'Footer',
				'post_content' => $blank,
				'post_status'  => 'publish',
				'tax_input'    => array( 'wp_template_part_area' => array( 'footer' ) ),
			)
		);

		if ( $id && ! is_wp_error( $id ) ) {
			wp_set_object_terms( $id, $theme, 'wp_theme' );
			wp_set_object_terms( $id, 'footer', 'wp_template_part_area' );
		}
	}

	/**
	 * Create the picker and manual-form pages, and put the picker on the front.
	 */
	private static function seed_pages() {
		$pages = array(
			'flyer-builder'           => array( 'Flyer Builder', '[event_flyer_picker]' ),
			'create-your-event-flyer' => array( 'Create Your Event Flyer', '[event_flyer_form]' ),
		);

		$front_id = 0;

		foreach ( $pages as $slug => $page ) {
			$existing = get_page_by_path( $slug );

			if ( $existing ) {
				$page_id = $existing->ID;
				wp_update_post(
					array(
						'ID'           => $page_id,
						'post_content' => $page[1],
						'post_status'  => 'publish',
					)
				);
			} else {
				$page_id = wp_insert_post(
					array(
						'post_type'    => 'page',
						'post_title'   => $page[0],
						'post_name'    => $slug,
						'post_content' => $page[1],
						'post_status'  => 'publish',
					)
				);
			}

			if ( 'flyer-builder' === $slug && $page_id && ! is_wp_error( $page_id ) ) {
				$front_id = $page_id;
			}
		}

		if ( $front_id ) {
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', $front_id );
		}
	}

	/**
	 * Convert one event post into the array shape the flyer template expects.
	 *
	 * @param int|WP_Post $post Event.
	 * @return array|null Null when the post is not an event.
	 */
	public static function to_flyer_row( $post ) {
		$post = get_post( $post );

		if ( ! $post || self::POST_TYPE !== $post->post_type ) {
			return null;
		}

		$row = array(
			'title'       => $post->post_title,
			'description' => $post->post_excerpt,
		);

		foreach ( self::FIELDS as $key => $meta_key ) {
			$row[ $key ] = (string) get_post_meta( $post->ID, $meta_key, true );
		}

		if ( '' === $row['icon'] ) {
			$row['icon'] = 'tip';
		}

		return $row;
	}
}

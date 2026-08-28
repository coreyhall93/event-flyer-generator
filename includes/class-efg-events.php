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

	/** GatherPress's event post type, used when GatherPress is active. */
	const GP_POST_TYPE = 'gatherpress_event';

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
	 * Whether GatherPress is active and usable as the event source.
	 *
	 * @return bool
	 */
	public static function using_gatherpress() {
		return class_exists( '\GatherPress\Core\Event\Event' ) && post_type_exists( self::GP_POST_TYPE );
	}

	/**
	 * Which post type events are read from.
	 *
	 * @return string
	 */
	public static function post_type() {
		return self::using_gatherpress() ? self::GP_POST_TYPE : self::POST_TYPE;
	}

	/**
	 * Every event, soonest first.
	 *
	 * Reads GatherPress events when GatherPress is active, and the plugin's own
	 * events otherwise. Everything downstream is identical either way.
	 *
	 * @param int $limit Maximum number to return.
	 * @return WP_Post[]
	 */
	public static function all( $limit = 20 ) {
		$args = array(
			'post_type'        => self::post_type(),
			'post_status'      => 'publish',
			'numberposts'      => (int) $limit,
			'suppress_filters' => false,
		);

		if ( self::using_gatherpress() ) {
			// GatherPress keeps the start time in post meta, so order by it
			// rather than by publish date.
			$args['meta_key'] = 'gatherpress_datetime_start'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- ordering a short list on a builder screen.
			$args['orderby']  = 'meta_value';
			$args['order']    = 'ASC';
		} else {
			$args['orderby'] = 'menu_order date';
			$args['order']   = 'ASC';
		}

		return get_posts( $args );
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
		if ( self::using_gatherpress() ) {
			$created = self::seed_gatherpress_events();
			self::seed_pages();
			self::clear_demo_clutter();
			update_option( self::DEMO_OPTION, 1 );

			return $created;
		}

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

		// The demo is a tool, not a website. Name it after what it does.
		update_option( 'blogname', 'Event Flyer Generator' );
		update_option( 'blogdescription', '' );

		$theme = get_stylesheet();

		// Header: keep the site title, drop the nav and the theme's stock
		// "Learn more" button, which goes nowhere and reads as part of the tool.
		self::replace_template_part(
			'header',
			$theme,
			'<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|30","bottom":"var:preset|spacing|30"}}},"layout":{"type":"constrained"}} -->'
			. '<div class="wp-block-group alignfull" style="padding-top:var(--wp--preset--spacing--30);padding-bottom:var(--wp--preset--spacing--30)">'
			. '<!-- wp:site-title {"level":0,"isLink":false} /-->'
			. '</div><!-- /wp:group -->'
		);

		// Footer: the theme's placeholder address and opening hours read as if
		// they belong to the flyer tool.
		self::replace_template_part(
			'footer',
			$theme,
			'<!-- wp:group {"layout":{"type":"constrained"}} --><div class="wp-block-group"></div><!-- /wp:group -->'
		);
	}

	/**
	 * Override one of the active theme's template parts.
	 *
	 * @param string $slug    Template part slug, e.g. 'header'.
	 * @param string $theme   Active theme stylesheet.
	 * @param string $content Block markup to use instead.
	 */
	private static function replace_template_part( $slug, $theme, $content ) {
		$existing = get_posts(
			array(
				'post_type'        => 'wp_template_part',
				'name'             => $slug,
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

		if ( ! empty( $existing ) ) {
			wp_update_post(
				array(
					'ID'           => $existing[0]->ID,
					'post_content' => $content,
				)
			);
			return;
		}

		$id = wp_insert_post(
			array(
				'post_type'    => 'wp_template_part',
				'post_name'    => $slug,
				'post_title'   => ucfirst( $slug ),
				'post_content' => $content,
				'post_status'  => 'publish',
			)
		);

		if ( $id && ! is_wp_error( $id ) ) {
			wp_set_object_terms( $id, $theme, 'wp_theme' );
			wp_set_object_terms( $id, $slug, 'wp_template_part_area' );
		}
	}

	/**
	 * Seed the same sample events as real GatherPress events and a venue.
	 *
	 * Demo helper, so the GatherPress path can be shown rather than described.
	 * Writes GatherPress's own meta keys and venue taxonomy term; everything is
	 * then read back through GatherPress's Event API like any other event.
	 *
	 * @return int Number of events created.
	 */
	private static function seed_gatherpress_events() {
		$existing = get_posts(
			array(
				'post_type'        => self::GP_POST_TYPE,
				'post_status'      => 'any',
				'numberposts'      => 1,
				'fields'           => 'ids',
				'suppress_filters' => false,
			)
		);

		if ( ! empty( $existing ) ) {
			return 0;
		}

		$venue_terms = self::seed_gatherpress_venues();

		// Dates are relative so the demo never shows a past programme.
		$offsets = array( 14, 28, 40, 54, 68, 82 );
		$times   = array( '19:00:00', '18:30:00', '10:00:00', '19:00:00', '18:00:00', '19:00:00' );
		$created = 0;

		foreach ( self::demo_events() as $index => $seed ) {
			$start = gmdate( 'Y-m-d', strtotime( '+' . $offsets[ $index ] . ' days' ) ) . ' ' . $times[ $index ];

			$id = wp_insert_post(
				array(
					'post_type'    => self::GP_POST_TYPE,
					'post_title'   => $seed['title'],
					'post_excerpt' => $seed['excerpt'],
					'post_status'  => 'publish',
				)
			);

			if ( is_wp_error( $id ) || ! $id ) {
				continue;
			}

			foreach ( array( 'datetime_start', 'datetime_start_gmt', 'datetime_end', 'datetime_end_gmt' ) as $key ) {
				update_post_meta( $id, 'gatherpress_' . $key, $start );
			}
			update_post_meta( $id, 'gatherpress_timezone', wp_timezone_string() );

			if ( isset( $venue_terms[ $seed['venue'] ] ) ) {
				wp_set_object_terms( $id, array( (int) $venue_terms[ $seed['venue'] ] ), '_gatherpress_venue' );
			}

			++$created;
		}

		return $created;
	}

	/**
	 * Create GatherPress venue posts and their linking terms.
	 *
	 * @return array Venue name => term id.
	 */
	private static function seed_gatherpress_venues() {
		$venues = array();

		foreach ( self::demo_events() as $seed ) {
			$venues[ $seed['venue'] ] = $seed['address'];
		}

		$terms = array();

		foreach ( $venues as $name => $address ) {
			$venue_id = wp_insert_post(
				array(
					'post_type'   => 'gatherpress_venue',
					'post_title'  => $name,
					'post_status' => 'publish',
				)
			);

			if ( is_wp_error( $venue_id ) || ! $venue_id ) {
				continue;
			}

			// GatherPress reads the address from this single meta key.
			update_post_meta( $venue_id, 'gatherpress_address', $address );

			// Venue terms carry a leading underscore; that prefix is how
			// GatherPress tells a real venue from the online-event sentinel.
			$slug = '_' . get_post_field( 'post_name', $venue_id );
			$term = term_exists( $slug, '_gatherpress_venue' );

			if ( ! $term ) {
				$term = wp_insert_term( $name, '_gatherpress_venue', array( 'slug' => $slug ) );
			}

			if ( ! is_wp_error( $term ) && isset( $term['term_id'] ) ) {
				wp_set_object_terms( $venue_id, array( (int) $term['term_id'] ), '_gatherpress_venue' );
				$terms[ $name ] = (int) $term['term_id'];
			}
		}

		return $terms;
	}

	/**
	 * Create the picker and manual-form pages, and put the picker on the front.
	 */
	private static function seed_pages() {
		// One page on purpose. The manual [event_flyer_form] is the fallback for
		// sites with no event source; showing it alongside the picker just poses
		// a question the visitor has no way to answer.
		$pages = array(
			'flyer-builder' => array( 'Flyer Builder', '[event_flyer_picker]' ),
		);

		$stale = get_page_by_path( 'create-your-event-flyer' );
		if ( $stale ) {
			wp_delete_post( $stale->ID, true );
		}

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

		if ( ! $post ) {
			return null;
		}

		if ( self::GP_POST_TYPE === $post->post_type ) {
			return self::gatherpress_row( $post );
		}

		if ( self::POST_TYPE !== $post->post_type ) {
			return null;
		}

		$row = array(
			'title'       => $post->post_title,
			'description' => $post->post_excerpt,
		);

		foreach ( self::FIELDS as $key => $meta_key ) {
			$row[ $key ] = (string) get_post_meta( $post->ID, $meta_key, true );
		}

		if ( ! EFG_Icons::exists( $row['icon'] ) ) {
			$row['icon'] = EFG_Icons::FALLBACK;
		}

		return $row;
	}

	/**
	 * Map a GatherPress event onto a flyer row.
	 *
	 * Uses GatherPress's own Event API rather than reading its meta directly,
	 * so timezone handling and venue resolution stay its business.
	 *
	 * @param WP_Post $post GatherPress event.
	 * @return array
	 */
	private static function gatherpress_row( $post ) {
		$event = new \GatherPress\Core\Event\Event( $post->ID );
		$venue = $event->get_venue_information();

		$excerpt = $post->post_excerpt;
		if ( '' === trim( $excerpt ) ) {
			$excerpt = wp_trim_words( wp_strip_all_tags( (string) $post->post_content ), 26, '…' );
		}

		return array(
			'title'       => $post->post_title,
			'description' => $excerpt,
			// Short, uppercase forms to match the flyer's typographic style.
			'date'        => strtoupper( $event->get_datetime_start( 'M j' ) ),
			'time'        => strtoupper( $event->get_datetime_start( 'g:ia' ) ),
			'venue'       => isset( $venue['name'] ) ? (string) $venue['name'] : '',
			'address'     => isset( $venue['address'] ) ? (string) $venue['address'] : '',
			'icon'        => 'people',
		);
	}
}

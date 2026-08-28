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
	public function __construct() {
		add_action( 'init', array( $this, 'register_post_type' ) );
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

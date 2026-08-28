<?php
/**
 * Demo content seeder.
 *
 * NOT loaded by the plugin. This is run once by the WordPress Playground
 * blueprint (and can be run by hand with WP-CLI) to give the demo something to
 * pick from, so the "choose from your events" flow can be tried without typing
 * six events in first.
 *
 *   wp eval-file demo/seed.php
 *
 * @package event-flyer-generator
 */

if ( ! defined( 'ABSPATH' ) ) {
	require_once '/wordpress/wp-load.php';
}

if ( ! class_exists( 'EFG_Events' ) ) {
	return;
}

$efg_seed_events = array(
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

$efg_order = 0;

foreach ( $efg_seed_events as $efg_seed ) {
	// get_page_by_title() is deprecated as of WP 6.2.
	$efg_existing = get_posts(
		array(
			'post_type'        => EFG_Events::POST_TYPE,
			'title'            => $efg_seed['title'],
			'post_status'      => 'any',
			'numberposts'      => 1,
			'fields'           => 'ids',
			'suppress_filters' => false,
		)
	);

	if ( ! empty( $efg_existing ) ) {
		continue;
	}

	++$efg_order;

	$efg_id = wp_insert_post(
		array(
			'post_type'    => EFG_Events::POST_TYPE,
			'post_title'   => $efg_seed['title'],
			'post_excerpt' => $efg_seed['excerpt'],
			'post_status'  => 'publish',
			'menu_order'   => $efg_order,
		)
	);

	if ( is_wp_error( $efg_id ) || ! $efg_id ) {
		continue;
	}

	foreach ( EFG_Events::FIELDS as $efg_key => $efg_meta_key ) {
		update_post_meta( $efg_id, $efg_meta_key, $efg_seed[ $efg_key ] );
	}
}

// Two pages: the picker (the flow this is really aiming at) as the front page,
// and the manual form kept around as the no-events fallback.
$efg_pages = array(
	'flyer-builder'           => array( 'Flyer Builder', '[event_flyer_picker]' ),
	'create-your-event-flyer' => array( 'Create Your Event Flyer', '[event_flyer_form]' ),
);

$efg_front_id = 0;

foreach ( $efg_pages as $efg_slug => $efg_page ) {
	$efg_page_post = get_page_by_path( $efg_slug );

	if ( $efg_page_post ) {
		$efg_page_id = $efg_page_post->ID;
		wp_update_post(
			array(
				'ID'           => $efg_page_id,
				'post_content' => $efg_page[1],
				'post_status'  => 'publish',
			)
		);
	} else {
		$efg_page_id = wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_title'   => $efg_page[0],
				'post_name'    => $efg_slug,
				'post_content' => $efg_page[1],
				'post_status'  => 'publish',
			)
		);
	}

	if ( 'flyer-builder' === $efg_slug && $efg_page_id && ! is_wp_error( $efg_page_id ) ) {
		$efg_front_id = $efg_page_id;
	}
}

if ( $efg_front_id ) {
	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $efg_front_id );
}

flush_rewrite_rules();

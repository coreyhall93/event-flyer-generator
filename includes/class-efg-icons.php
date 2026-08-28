<?php
/**
 * The icon set offered for events.
 *
 * @package event-flyer-generator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Single source of truth for icons.
 *
 * The picker UI and the printed flyer both read from here, so an icon can never
 * be offered in one place and be missing in the other.
 *
 * Paths are lifted verbatim from @wordpress/icons (GPLv2+), the icon set that
 * ships with the block editor. Do not hand-draw replacements: keeping them
 * identical to core is what makes the flyer look like WordPress.
 */
class EFG_Icons {

	const FALLBACK = 'tip';

	/**
	 * Every selectable icon, keyed by slug.
	 *
	 * @return array
	 */
	public static function all() {
		return array(
			'tip'           => array(
				'label'     => __( 'Idea', 'event-flyer-generator' ),
				'path'      => 'M8 18.25L16 18.25M10 21.25H14M18 9C18 12.3137 15.3137 15 12 15C8.68629 15 6 12.3137 6 9C6 5.68629 8.68629 3 12 3C15.3137 3 18 5.68629 18 9Z',
				'fill_rule' => '',
			),
			'people'        => array(
				'label'     => __( 'Gathering', 'event-flyer-generator' ),
				'path'      => 'M15.5 9.5a1 1 0 100-2 1 1 0 000 2zm0 1.5a2.5 2.5 0 100-5 2.5 2.5 0 000 5zm-2.25 6v-2a2.75 2.75 0 00-2.75-2.75h-4A2.75 2.75 0 003.75 15v2h1.5v-2c0-.69.56-1.25 1.25-1.25h4c.69 0 1.25.56 1.25 1.25v2h1.5zm7-2v2h-1.5v-2c0-.69-.56-1.25-1.25-1.25H15v-1.5h2.5A2.75 2.75 0 0120.25 15zM9.5 8.5a1 1 0 11-2 0 1 1 0 012 0zm1.5 0a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z',
				'fill_rule' => 'evenodd',
			),
			'megaphone'     => array(
				'label'     => __( 'Announcement', 'event-flyer-generator' ),
				'path'      => 'M6.863 13.644L5 13.25h-.5a.5.5 0 01-.5-.5v-3a.5.5 0 01.5-.5H5L18 6.5h2V16h-2l-3.854-.815.026.008a3.75 3.75 0 01-7.31-1.549zm1.477.313a2.251 2.251 0 004.356.921l-4.356-.921zm-2.84-3.28L18.157 8h.343v6.5h-.343L5.5 11.823v-1.146z',
				'fill_rule' => 'evenodd',
			),
			'calendar'      => array(
				'label'     => __( 'Date', 'event-flyer-generator' ),
				'path'      => 'M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm.5 16c0 .3-.2.5-.5.5H5c-.3 0-.5-.2-.5-.5V7h15v12zM9 10H7v2h2v-2zm0 4H7v2h2v-2zm4-4h-2v2h2v-2zm4 0h-2v2h2v-2zm-4 4h-2v2h2v-2zm4 0h-2v2h2v-2z',
				'fill_rule' => '',
			),
			'institution'   => array(
				'label'     => __( 'Venue', 'event-flyer-generator' ),
				'path'      => 'M18.646 9H20V8l-1-.5L12 4 5 7.5 4 8v1h14.646zm-3-1.5L12 5.677 8.354 7.5h7.292zm-7.897 9.44v-6.5h-1.5v6.5h1.5zm5-6.5v6.5h-1.5v-6.5h1.5zm5 0v6.5h-1.5v-6.5h1.5zm2.252 8.81c0 .414-.334.75-.748.75H4.752a.75.75 0 010-1.5h14.5a.75.75 0 01.749.75z',
				'fill_rule' => 'evenodd',
			),
			'tool'          => array(
				'label'     => __( 'Hands-on', 'event-flyer-generator' ),
				'path'      => 'M14.103 7.128l2.26-2.26a4 4 0 00-5.207 4.804L5.828 15a2 2 0 102.828 2.828l5.329-5.328a4 4 0 004.804-5.208l-2.261 2.26-1.912-.512-.513-1.912zm-7.214 9.64a.5.5 0 11.707-.707.5.5 0 01-.707.707z',
				'fill_rule' => '',
			),
			'code'          => array(
				'label'     => __( 'Code', 'event-flyer-generator' ),
				'path'      => 'M7.99997 7L3.70708 11.2929C3.31655 11.6834 3.31655 12.3166 3.70708 12.7071L7.99997 17M16 17L20.2929 12.7071C20.6834 12.3166 20.6834 11.6834 20.2929 11.2929L16 7',
				'fill_rule' => '',
			),
			'comment'       => array(
				'label'     => __( 'Discussion', 'event-flyer-generator' ),
				'path'      => 'M18 4H6c-1.1 0-2 .9-2 2v12.9c0 .6.5 1.1 1.1 1.1.3 0 .5-.1.8-.3L8.5 17H18c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm.5 11c0 .3-.2.5-.5.5H7.9l-2.4 2.4V6c0-.3.2-.5.5-.5h12c.3 0 .5.2.5.5v9z',
				'fill_rule' => '',
			),
			'globe'         => array(
				'label'     => __( 'Online', 'event-flyer-generator' ),
				'path'      => 'M12 4c-4.4 0-8 3.6-8 8s3.6 8 8 8 8-3.6 8-8-3.6-8-8-8Zm6.5 8c0 .6 0 1.2-.2 1.8h-2.7c0-.6.2-1.1.2-1.8s0-1.2-.2-1.8h2.7c.2.6.2 1.1.2 1.8Zm-.9-3.2h-2.4c-.3-.9-.7-1.8-1.1-2.4-.1-.2-.2-.4-.3-.5 1.6.5 3 1.6 3.8 3ZM12.8 17c-.3.5-.6 1-.8 1.3-.2-.3-.5-.8-.8-1.3-.3-.5-.6-1.1-.8-1.7h3.3c-.2.6-.5 1.2-.8 1.7Zm-2.9-3.2c-.1-.6-.2-1.1-.2-1.8s0-1.2.2-1.8H14c.1.6.2 1.1.2 1.8s0 1.2-.2 1.8H9.9ZM11.2 7c.3-.5.6-1 .8-1.3.2.3.5.8.8 1.3.3.5.6 1.1.8 1.7h-3.3c.2-.6.5-1.2.8-1.7Zm-1-1.2c-.1.2-.2.3-.3.5-.4.7-.8 1.5-1.1 2.4H6.4c.8-1.4 2.2-2.5 3.8-3Zm-1.8 8H5.7c-.2-.6-.2-1.1-.2-1.8s0-1.2.2-1.8h2.7c0 .6-.2 1.1-.2 1.8s0 1.2.2 1.8Zm-2 1.4h2.4c.3.9.7 1.8 1.1 2.4.1.2.2.4.3.5-1.6-.5-3-1.6-3.8-3Zm7.4 3c.1-.2.2-.3.3-.5.4-.7.8-1.5 1.1-2.4h2.4c-.8 1.4-2.2 2.5-3.8 3Z',
				'fill_rule' => '',
			),
			'gift'          => array(
				'label'     => __( 'Social', 'event-flyer-generator' ),
				'path'      => 'M15.333 4C16.6677 4 17.75 5.0823 17.75 6.41699V6.75C17.75 7.20058 17.6394 7.62468 17.4473 8H18.5C19.2767 8 19.9154 8.59028 19.9922 9.34668L20 9.5V18.5C20 19.3284 19.3284 20 18.5 20H5.5C4.72334 20 4.08461 19.4097 4.00781 18.6533L4 18.5V9.5L4.00781 9.34668C4.07949 8.64069 4.64069 8.07949 5.34668 8.00781L5.5 8H6.55273C6.36065 7.62468 6.25 7.20058 6.25 6.75V6.41699C6.25 5.0823 7.3323 4 8.66699 4C10.0436 4.00011 11.2604 4.68183 12 5.72559C12.7396 4.68183 13.9564 4.00011 15.333 4ZM5.5 18.5H11.25V9.5H5.5V18.5ZM12.75 18.5H18.5V9.5H12.75V18.5ZM8.66699 5.5C8.16073 5.5 7.75 5.91073 7.75 6.41699V6.75C7.75 7.44036 8.30964 8 9 8H11.2461C11.2021 6.61198 10.0657 5.50017 8.66699 5.5ZM15.333 5.5C13.9343 5.50017 12.7979 6.61198 12.7539 8H15C15.6904 8 16.25 7.44036 16.25 6.75V6.41699C16.25 5.91073 15.8393 5.5 15.333 5.5Z',
				'fill_rule' => '',
			),
			'star_filled'   => array(
				'label'     => __( 'Featured', 'event-flyer-generator' ),
				'path'      => 'M12 5.71576L13.6106 8.97924C13.7563 9.27438 14.0379 9.47895 14.3636 9.52628L17.965 10.0496L15.359 12.5899C15.1233 12.8196 15.0157 13.1506 15.0714 13.475L15.6866 17.0619L12.4653 15.3684C12.174 15.2152 11.826 15.2152 11.5347 15.3684L8.31341 17.0619L8.92861 13.475C8.98425 13.1506 8.8767 12.8196 8.64102 12.5899L6.03497 10.0496L9.63644 9.52628C9.96215 9.47895 10.2437 9.27438 10.3894 8.97924L12 5.71576Z',
				'fill_rule' => '',
			),
			'wordpress'     => array(
				'label'     => __( 'WordPress', 'event-flyer-generator' ),
				'path'      => 'M 22 12 C 22 6.49 17.51 2 12 2 C 6.48 2 2 6.49 2 12 C 2 17.52 6.48 22 12 22 C 17.51 22 22 17.52 22 12 M 9.78 17.37 L 6.37 8.22 C 6.92 8.2 7.54 8.14 7.54 8.14 C 8.04 8.08 7.98 7.01 7.48 7.03 C 7.48 7.03 6.03 7.14 5.11 7.14 C 4.93 7.14 4.74 7.14 4.53 7.13 C 6.12 4.69 8.87 3.11 12 3.11 C 14.33 3.11 16.45 3.98 18.05 5.45 C 17.37 5.34 16.4 5.84 16.4 7.03 C 16.4 7.77 16.85 8.39 17.3 9.13 C 17.65 9.74 17.85 10.49 17.85 11.59 C 17.85 13.08 16.45 16.59 16.45 16.59 L 13.42 8.22 C 13.96 8.2 14.24 8.05 14.24 8.05 C 14.74 8 14.68 6.8 14.18 6.83 C 14.18 6.83 12.74 6.95 11.8 6.95 C 10.93 6.95 9.47 6.83 9.47 6.83 C 8.97 6.8 8.91 8.03 9.41 8.05 L 10.33 8.13 L 11.59 11.54 L 9.78 17.37 M 19.41 12 C 19.65 11.36 20.15 10.13 19.84 7.75 C 20.54 9.04 20.89 10.46 20.89 12 C 20.89 15.29 19.16 18.24 16.49 19.78 C 17.46 17.19 18.43 14.58 19.41 12 M 8.1 20.09 C 5.12 18.65 3.11 15.53 3.11 12 C 3.11 10.7 3.34 9.52 3.83 8.41 C 5.25 12.3 6.67 16.2 8.1 20.09 M 12.13 13.46 L 14.71 20.44 C 13.85 20.73 12.95 20.89 12 20.89 C 11.21 20.89 10.43 20.78 9.71 20.56 C 10.52 18.18 11.33 15.82 12.13 13.46 L 12.13 13.46',
				'fill_rule' => '',
			),
			'capture_photo' => array(
				'label'     => __( 'Photo', 'event-flyer-generator' ),
				'path'      => 'M12 9.2c-2.2 0-3.9 1.8-3.9 4s1.8 4 3.9 4 4-1.8 4-4-1.8-4-4-4zm0 6.5c-1.4 0-2.4-1.1-2.4-2.5s1.1-2.5 2.4-2.5 2.5 1.1 2.5 2.5-1.1 2.5-2.5 2.5zM20.2 8c-.1 0-.3 0-.5-.1l-2.5-.8c-.4-.1-.8-.4-1.1-.8l-1-1.5c-.4-.5-1-.9-1.7-.9h-2.9c-.6.1-1.2.4-1.6 1l-1 1.5c-.3.3-.6.6-1.1.7l-2.5.8c-.2.1-.4.1-.6.1-1 .2-1.7.9-1.7 1.9v8.3c0 1 .9 1.9 2 1.9h16c1.1 0 2-.8 2-1.9V9.9c0-1-.7-1.7-1.8-1.9zm.3 10.1c0 .2-.2.4-.5.4H4c-.3 0-.5-.2-.5-.4V9.9c0-.1.2-.3.5-.4.2 0 .5-.1.8-.2l2.5-.8c.7-.2 1.4-.6 1.8-1.3l1-1.5c.1-.1.2-.2.4-.2h2.9c.2 0 .3.1.4.2l1 1.5c.4.7 1.1 1.1 1.9 1.4l2.5.8c.3.1.6.1.8.2.3 0 .4.2.4.4v8.1z',
				'fill_rule' => '',
			),
			'audio'         => array(
				'label'     => __( 'Audio', 'event-flyer-generator' ),
				'path'      => 'M17.7 4.3c-1.2 0-2.8 0-3.8 1-.6.6-.9 1.5-.9 2.6V14c-.6-.6-1.5-1-2.5-1C8.6 13 7 14.6 7 16.5S8.6 20 10.5 20c1.5 0 2.8-1 3.3-2.3.5-.8.7-1.8.7-2.5V7.9c0-.7.2-1.2.5-1.6.6-.6 1.8-.6 2.8-.6h.3V4.3h-.4z',
				'fill_rule' => '',
			),
			'pin'           => array(
				'label'     => __( 'Pin', 'event-flyer-generator' ),
				'path'      => 'm21.5 9.1-6.6-6.6-4.2 5.6c-1.2-.1-2.4.1-3.6.7-.1 0-.1.1-.2.1-.5.3-.9.6-1.2.9l3.7 3.7-5.7 5.7v1.1h1.1l5.7-5.7 3.7 3.7c.4-.4.7-.8.9-1.2.1-.1.1-.2.2-.3.6-1.1.8-2.4.6-3.6l5.6-4.1zm-7.3 3.5.1.9c.1.9 0 1.8-.4 2.6l-6-6c.8-.4 1.7-.5 2.6-.4l.9.1L15 4.9 19.1 9l-4.9 3.6z',
				'fill_rule' => '',
			),
		);
	}

	/**
	 * Resolve a slug to a real icon, falling back rather than rendering nothing.
	 *
	 * @param string $slug Icon slug.
	 * @return array
	 */
	public static function get( $slug ) {
		$icons = self::all();
		$slug  = (string) $slug;

		return isset( $icons[ $slug ] ) ? $icons[ $slug ] : $icons[ self::FALLBACK ];
	}

	/**
	 * Whether a slug is one we offer.
	 *
	 * @param string $slug Icon slug.
	 * @return bool
	 */
	public static function exists( $slug ) {
		$icons = self::all();

		return isset( $icons[ (string) $slug ] );
	}

	/**
	 * Render one icon as inline SVG.
	 *
	 * @param string $slug Icon slug.
	 * @param int    $size Pixel size for the width/height attributes.
	 * @return string
	 */
	public static function svg( $slug, $size = 24 ) {
		$icon = self::get( $slug );
		$rule = $icon['fill_rule'] ? ' fill-rule="evenodd"' : '';

		return '<svg width="' . (int) $size . '" height="' . (int) $size . '"'
			. ' viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false">'
			. '<path' . $rule . ' d="' . esc_attr( $icon['path'] ) . '"/></svg>';
	}
}

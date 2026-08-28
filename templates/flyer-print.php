<?php
/**
 * Print-ready flyer output. Included by EFG_Print_View::maybe_render().
 * $data is set by the caller: ['program_name' => ..., 'footer_line' => ..., 'events' => [...]]
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$icon_paths = array(
	'tip'       => 'M12 15.8c-3.7 0-6.8-3-6.8-6.8s3-6.8 6.8-6.8c3.7 0 6.8 3 6.8 6.8s-3.1 6.8-6.8 6.8zm0-12C9.1 3.8 6.8 6.1 6.8 9s2.4 5.2 5.2 5.2c2.9 0 5.2-2.4 5.2-5.2S14.9 3.8 12 3.8zM8 17.5h8V19H8zM10 20.5h4V22h-4z',
	'people'    => 'M15.5 9.5a1 1 0 100-2 1 1 0 000 2zm0 1.5a2.5 2.5 0 100-5 2.5 2.5 0 000 5zm-2.25 6v-2a2.75 2.75 0 00-2.75-2.75h-4A2.75 2.75 0 003.75 15v2h1.5v-2c0-.69.56-1.25 1.25-1.25h4c.69 0 1.25.56 1.25 1.25v2h1.5zm7-2v2h-1.5v-2c0-.69-.56-1.25-1.25-1.25H15v-1.5h2.5A2.75 2.75 0 0120.25 15zM9.5 8.5a1 1 0 11-2 0 1 1 0 012 0zm1.5 0a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z',
	'megaphone' => 'M6.863 13.644L5 13.25h-.5a.5.5 0 01-.5-.5v-3a.5.5 0 01.5-.5H5L18 6.5h2V16h-2l-3.854-.815.026.008a3.75 3.75 0 01-7.31-1.549zm1.477.313a2.251 2.251 0 004.356.921l-4.356-.921zm-2.84-3.28L18.157 8h.343v6.5h-.343L5.5 11.823v-1.146z',
	'pin'       => 'm21.5 9.1-6.6-6.6-4.2 5.6c-1.2-.1-2.4.1-3.6.7-.1 0-.1.1-.2.1-.5.3-.9.6-1.2.9l3.7 3.7-5.7 5.7v1.1h1.1l5.7-5.7 3.7 3.7c.4-.4.7-.8.9-1.2.1-.1.1-.2.2-.3.6-1.1.8-2.4.6-3.6l5.6-4.1zm-7.3 3.5.1.9c.1.9 0 1.8-.4 2.6l-6-6c.8-.4 1.7-.5 2.6-.4l.9.1L15 4.9 19.1 9l-4.9 3.6z',
);

$has_fill_rule = array( 'people', 'megaphone' );

$program_name = isset( $data['program_name'] ) ? $data['program_name'] : '';
$footer_line  = isset( $data['footer_line'] ) ? $data['footer_line'] : '';
$events       = isset( $data['events'] ) ? (array) $data['events'] : array();

// Program name renders as two stacked lines: split on the last space, or
// keep it on one line if it's short enough that splitting would look worse.
$name_parts = explode( ' ', $program_name, 2 );
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo esc_html( $program_name ); ?> — <?php esc_html_e( 'Flyer', 'event-flyer-generator' ); ?></title>
	<style>
		/* Fonts are bundled, not fetched from Google: a hotlink would send every
		   visitor's IP to a third party without consent. Both are SIL OFL;
		   see assets/fonts/OFL-Oswald.txt and OFL-Inter.txt. */
		@font-face {
			font-family: 'Oswald';
			src: url('<?php echo esc_url( EFG_URL . 'assets/fonts/oswald-latin-var.woff2' ); ?>') format('woff2');
			font-weight: 200 700;
			font-display: swap;
		}
		@font-face {
			font-family: 'Inter';
			src: url('<?php echo esc_url( EFG_URL . 'assets/fonts/inter-latin-var.woff2' ); ?>') format('woff2');
			font-weight: 100 900;
			font-display: swap;
		}

		* { box-sizing: border-box; }
		body { margin: 0; background: #ccc; font-family: 'Inter', sans-serif; }

		.efg-toolbar {
			max-width: 816px;
			margin: 24px auto;
			display: flex;
			justify-content: space-between;
			align-items: center;
			font-family: 'Inter', sans-serif;
		}
		.efg-toolbar a { color: #000; }
		.efg-toolbar button {
			font-family: 'Inter', sans-serif;
			font-weight: 600;
			font-size: 15px;
			padding: 10px 20px;
			background: #000;
			color: #fff;
			border: none;
			cursor: pointer;
		}

		.efg-page {
			width: 816px;
			height: 1056px;
			margin: 0 auto 40px;
			background: #fff;
			color: #000;
			display: flex;
			flex-direction: column;
			overflow: hidden;
			box-shadow: 0 4px 24px rgba(0,0,0,.25);
		}

		.efg-header { background: #000; color: #fff; padding: 52px 64px 44px; }
		.efg-header h1 {
			margin: 0;
			font-family: 'Oswald', sans-serif;
			font-weight: 700;
			font-size: 72px;
			line-height: 1.0;
			letter-spacing: 0.01em;
			text-transform: uppercase;
			text-wrap: balance;
		}

		.efg-events { flex-grow: 1; display: flex; flex-direction: column; justify-content: space-evenly; padding: 0 64px; }
		.efg-event-row { display: flex; gap: 32px; align-items: flex-start; }
		.efg-events > .efg-event-row:not(:last-child) { border-bottom: 1px solid #000; padding-bottom: 24px; margin-bottom: 0; }

		.efg-when { width: 120px; flex-shrink: 0; }
		.efg-when .date { font-family: 'Oswald', sans-serif; font-weight: 600; font-size: 24px; line-height: 1.25; text-transform: uppercase; text-wrap: balance; }
		.efg-when svg { margin-top: 14px; }

		.efg-what { flex-grow: 1; padding-top: 2px; }
		.efg-what .title { font-family: 'Oswald', sans-serif; font-weight: 600; font-size: 26px; line-height: 1.15; text-transform: uppercase; text-wrap: balance; margin-bottom: 10px; }
		.efg-what .desc { font-size: 17px; line-height: 1.5; text-wrap: pretty; max-width: 400px; }

		.efg-where { width: 190px; flex-shrink: 0; text-align: right; padding-top: 2px; }
		.efg-where svg { margin-bottom: 8px; }
		.efg-where .venue { font-weight: 700; font-size: 16px; line-height: 1.4; }
		.efg-where .address { font-size: 15px; line-height: 1.4; }

		.efg-footer { padding: 0 64px 48px; text-align: center; }
		.efg-footer .rule { border-top: 1px solid #000; margin-bottom: 20px; }
		.efg-footer .line { font-family: 'Oswald', sans-serif; font-weight: 600; font-size: 17px; letter-spacing: 0.08em; text-transform: uppercase; text-wrap: balance; }

		@page { size: letter; margin: 0; }
		@media print {
			* {
				-webkit-print-color-adjust: exact !important;
				print-color-adjust: exact !important;
				color-adjust: exact !important;
			}
			body { background: #fff; }
			.efg-toolbar { display: none; }
			.efg-page { margin: 0; box-shadow: none; }
		}
	</style>
</head>
<body>

	<div class="efg-toolbar">
		<a href="<?php echo esc_url( remove_query_arg( 'efg_flyer' ) ); ?>">&larr; <?php esc_html_e( 'Back to form', 'event-flyer-generator' ); ?></a>
		<button type="button" onclick="window.print()"><?php esc_html_e( 'Print / Save as PDF', 'event-flyer-generator' ); ?></button>
	</div>

	<div class="efg-page">

		<div class="efg-header">
			<h1>
				<?php echo esc_html( $name_parts[0] ); ?><br>
				<?php echo isset( $name_parts[1] ) ? esc_html( $name_parts[1] ) : ''; ?>
			</h1>
		</div>

		<div class="efg-events">
			<?php foreach ( $events as $event ) : ?>
				<?php
				$icon_key  = isset( $icon_paths[ $event['icon'] ] ) ? $event['icon'] : 'tip';
				$icon_path = $icon_paths[ $icon_key ];
				$fill_rule = in_array( $icon_key, $has_fill_rule, true ) ? ' fill-rule="evenodd"' : '';
				?>
				<div class="efg-event-row">
					<div class="efg-when">
						<div class="date"><?php echo esc_html( $event['date'] ); ?><br><?php echo esc_html( $event['time'] ); ?></div>
						<svg width="46" height="46" viewBox="0 0 24 24" fill="#000"><path<?php echo $fill_rule; /* phpcs:ignore -- fixed safe attribute string, not user input */ ?> d="<?php echo esc_attr( $icon_path ); ?>"/></svg>
					</div>
					<div class="efg-what">
						<div class="title"><?php echo esc_html( $event['title'] ); ?></div>
						<?php if ( $event['description'] ) : ?>
							<div class="desc"><?php echo esc_html( $event['description'] ); ?></div>
						<?php endif; ?>
					</div>
					<div class="efg-where">
						<svg width="30" height="30" viewBox="0 0 24 24" fill="#000"><path d="<?php echo esc_attr( $icon_paths['pin'] ); ?>"/></svg>
						<?php if ( $event['venue'] ) : ?>
							<div class="venue"><?php echo esc_html( strtoupper( $event['venue'] ) ); ?></div>
						<?php endif; ?>
						<?php if ( $event['address'] ) : ?>
							<div class="address"><?php echo esc_html( $event['address'] ); ?></div>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<div class="efg-footer">
			<div class="rule"></div>
			<div class="line"><?php echo esc_html( $footer_line ); ?></div>
		</div>

	</div>

</body>
</html>

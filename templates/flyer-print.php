<?php
/**
 * Print-ready flyer output. Included by EFG_Print_View::maybe_render().
 * $data is set by the caller: ['program_name' => ..., 'footer_line' => ..., 'events' => [...]]
 *
 * @package event-flyer-generator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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

		/* Every internal dimension is expressed against --efg-scale so the whole
			flyer can be shrunk to fit one page. The page box itself never scales:
			it is always exactly US Letter. assets/flyer.js lowers --efg-scale
			until the content fits, so content is never silently clipped. */
		.efg-page {
			--efg-scale: 1;
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

		.efg-header {
			background: #000;
			color: #fff;
			flex-shrink: 0;
			padding: calc(52px * var(--efg-scale)) calc(64px * var(--efg-scale)) calc(44px * var(--efg-scale));
		}
		.efg-header h1 {
			margin: 0;
			font-family: 'Oswald', sans-serif;
			font-weight: 700;
			font-size: calc(72px * var(--efg-scale));
			line-height: 1.0;
			letter-spacing: 0.01em;
			text-transform: uppercase;
			text-wrap: balance;
		}

		.efg-events {
			flex-grow: 1;
			display: flex;
			flex-direction: column;
			justify-content: space-evenly;
			padding: 0 calc(64px * var(--efg-scale));
			min-height: 0;
		}
		.efg-event-row { display: flex; gap: calc(32px * var(--efg-scale)); align-items: flex-start; }
		.efg-events > .efg-event-row:not(:last-child) {
			border-bottom: 1px solid #000;
			padding-bottom: calc(24px * var(--efg-scale));
			margin-bottom: 0;
		}

		.efg-when { width: calc(120px * var(--efg-scale)); flex-shrink: 0; }
		.efg-when .date { font-family: 'Oswald', sans-serif; font-weight: 600; font-size: calc(24px * var(--efg-scale)); line-height: 1.25; text-transform: uppercase; text-wrap: balance; }
		.efg-when svg { margin-top: calc(14px * var(--efg-scale)); width: calc(46px * var(--efg-scale)); height: calc(46px * var(--efg-scale)); }

		.efg-what { flex-grow: 1; padding-top: calc(2px * var(--efg-scale)); min-width: 0; }
		.efg-what .title { font-family: 'Oswald', sans-serif; font-weight: 600; font-size: calc(26px * var(--efg-scale)); line-height: 1.15; text-transform: uppercase; text-wrap: balance; margin-bottom: calc(10px * var(--efg-scale)); }
		.efg-what .desc { font-size: calc(17px * var(--efg-scale)); line-height: 1.5; text-wrap: pretty; max-width: calc(400px * var(--efg-scale)); }

		.efg-where { width: calc(190px * var(--efg-scale)); flex-shrink: 0; text-align: right; padding-top: calc(2px * var(--efg-scale)); }
		.efg-where svg { margin-bottom: calc(8px * var(--efg-scale)); width: calc(30px * var(--efg-scale)); height: calc(30px * var(--efg-scale)); }
		.efg-where .venue { font-weight: 700; font-size: calc(16px * var(--efg-scale)); line-height: 1.4; }
		.efg-where .address { font-size: calc(15px * var(--efg-scale)); line-height: 1.4; }

		/* ---------------------------------------------------------------
			Layout per event count. One event and four events are different
			pieces of design, not one layout with more rows: the default
			three-column row leaves a single event stranded in white space,
			and four events need the tightest setting that still reads.
			The base rules above are the four-event case; each block below
			opens the design up as there is more room to spend.
			--------------------------------------------------------------- */

		/* ONE EVENT — a poster. Drop the columns, stack and centre it, and
			let the event title carry the page. */
		.efg-count-1 .efg-events { justify-content: center; }
		.efg-count-1 .efg-event-row {
			flex-direction: column;
			align-items: center;
			text-align: center;
			gap: calc(26px * var(--efg-scale));
		}
		.efg-count-1 .efg-when,
		.efg-count-1 .efg-where { width: auto; }
		.efg-count-1 .efg-what { flex-grow: 0; }
		.efg-count-1 .efg-where { text-align: center; }
		.efg-count-1 .efg-when .date { font-size: calc(38px * var(--efg-scale)); }
		.efg-count-1 .efg-when svg { margin-top: calc(18px * var(--efg-scale)); width: calc(72px * var(--efg-scale)); height: calc(72px * var(--efg-scale)); }
		.efg-count-1 .efg-what .title { font-size: calc(58px * var(--efg-scale)); margin-bottom: calc(18px * var(--efg-scale)); }
		.efg-count-1 .efg-what .desc { font-size: calc(23px * var(--efg-scale)); max-width: calc(540px * var(--efg-scale)); margin: 0 auto; }
		.efg-count-1 .efg-where .venue { font-size: calc(22px * var(--efg-scale)); }
		.efg-count-1 .efg-where .address { font-size: calc(19px * var(--efg-scale)); }
		.efg-count-1 .efg-where svg { margin-bottom: calc(10px * var(--efg-scale)); width: calc(40px * var(--efg-scale)); height: calc(40px * var(--efg-scale)); }

		/* TWO EVENTS — keep the columns, spend the extra room on type. The side
			columns get NARROWER, not wider: bigger titles need the middle. */
		.efg-count-2 .efg-events { justify-content: space-evenly; }
		.efg-count-2 .efg-event-row { gap: calc(24px * var(--efg-scale)); }
		.efg-count-2 .efg-when { width: calc(125px * var(--efg-scale)); }
		.efg-count-2 .efg-when .date { font-size: calc(28px * var(--efg-scale)); }
		.efg-count-2 .efg-when svg { width: calc(56px * var(--efg-scale)); height: calc(56px * var(--efg-scale)); }
		.efg-count-2 .efg-what .title { font-size: calc(32px * var(--efg-scale)); }
		.efg-count-2 .efg-what .desc { font-size: calc(19px * var(--efg-scale)); max-width: none; }
		.efg-count-2 .efg-where { width: calc(170px * var(--efg-scale)); }
		.efg-count-2 .efg-where .venue { font-size: calc(17px * var(--efg-scale)); }
		.efg-count-2 .efg-where .address { font-size: calc(16px * var(--efg-scale)); }

		/* THREE EVENTS — a modest step up from the four-event setting. */
		.efg-count-3 .efg-when .date { font-size: calc(26px * var(--efg-scale)); }
		.efg-count-3 .efg-what .title { font-size: calc(30px * var(--efg-scale)); }
		.efg-count-3 .efg-what .desc { font-size: calc(18px * var(--efg-scale)); }

		/* FOUR EVENTS uses the base rules above: the tightest setting. */

		.efg-footer { flex-shrink: 0; padding: 0 calc(64px * var(--efg-scale)) calc(48px * var(--efg-scale)); text-align: center; }
		.efg-footer .rule { border-top: 1px solid #000; margin-bottom: calc(20px * var(--efg-scale)); }
		.efg-footer .line { font-family: 'Oswald', sans-serif; font-weight: 600; font-size: calc(17px * var(--efg-scale)); letter-spacing: 0.08em; text-transform: uppercase; text-wrap: balance; }

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
		<a href="<?php echo esc_url( remove_query_arg( 'efg_flyer' ) ); ?>">&larr; <?php esc_html_e( 'Back', 'event-flyer-generator' ); ?></a>
		<button type="button" data-efg-print><?php esc_html_e( 'Print / Save as PDF', 'event-flyer-generator' ); ?></button>
	</div>

	<div class="efg-page efg-count-<?php echo (int) min( count( $events ), 4 ); ?>">

		<div class="efg-header">
			<h1>
				<?php echo esc_html( $name_parts[0] ); ?><br>
				<?php echo isset( $name_parts[1] ) ? esc_html( $name_parts[1] ) : ''; ?>
			</h1>
		</div>

		<div class="efg-events">
			<?php foreach ( $events as $event ) : ?>
				<div class="efg-event-row">
					<div class="efg-when">
						<div class="date"><?php echo esc_html( $event['date'] ); ?><br><?php echo esc_html( $event['time'] ); ?></div>
						<?php echo EFG_Icons::svg( $event['icon'], 46 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- EFG_Icons::svg() escapes its own attributes. ?>
					</div>
					<div class="efg-what">
						<div class="title"><?php echo esc_html( $event['title'] ); ?></div>
						<?php if ( $event['description'] ) : ?>
							<div class="desc"><?php echo esc_html( $event['description'] ); ?></div>
						<?php endif; ?>
					</div>
					<div class="efg-where">
						<?php echo EFG_Icons::svg( 'pin', 30 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- EFG_Icons::svg() escapes its own attributes. ?>
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
			<?php if ( '' !== trim( $footer_line ) ) : ?>
				<div class="line"><?php echo esc_html( $footer_line ); ?></div>
			<?php endif; ?>
		</div>

	</div>

	<?php // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript -- this template bypasses the theme and wp_head() entirely, so there is no enqueue pipeline to hook into. ?>
	<script src="<?php echo esc_url( EFG_URL . 'assets/flyer.js?ver=' . EFG_VERSION ); ?>"></script>
</body>
</html>

<?php
/**
 * "Pick from your events" flyer builder. Included by EFG_Picker::render().
 *
 * @package event-flyer-generator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$efg_events = EFG_Events::all();

if ( empty( $efg_events ) ) {
	?>
	<div class="efg-wrap">
		<p class="efg-empty"><?php esc_html_e( 'No events yet. Add one under Events in the admin, or use the manual flyer form.', 'event-flyer-generator' ); ?></p>
	</div>
	<?php
	return;
}

$efg_max = EFG_Shortcode::MAX_EVENTS;
?>
<div class="efg-wrap">
	<form method="post" class="efg-form efg-picker">
		<?php wp_nonce_field( 'efg_picker', 'efg_picker_nonce' ); ?>
		<input type="hidden" name="efg_return" value="<?php echo esc_url( get_permalink() ); ?>" />

		<p class="efg-picker-intro">
			<?php
			echo esc_html(
				sprintf(
					/* translators: %d: maximum number of events on one flyer. */
					__( 'Choose up to %d events. They will print in the order shown.', 'event-flyer-generator' ),
					(int) $efg_max
				)
			);
			?>
		</p>

		<ul class="efg-event-list">
			<?php foreach ( $efg_events as $efg_event ) : ?>
				<?php
				$efg_row   = EFG_Events::to_flyer_row( $efg_event );
				$efg_when  = trim( $efg_row['date'] . ' ' . $efg_row['time'] );
				$efg_where = trim( $efg_row['venue'] );
				?>
				<li class="efg-event-item">
					<label>
						<input type="checkbox" name="event_ids[]" value="<?php echo esc_attr( $efg_event->ID ); ?>" data-efg-pick />
						<span class="efg-event-meta">
							<span class="efg-event-name"><?php echo esc_html( $efg_event->post_title ); ?></span>
							<?php if ( '' !== $efg_when || '' !== $efg_where ) : ?>
								<span class="efg-event-sub">
									<?php echo esc_html( trim( $efg_when . ( '' !== $efg_where ? ' · ' . $efg_where : '' ) ) ); ?>
								</span>
							<?php endif; ?>
						</span>
					</label>

					<button type="submit" name="efg_picker_submit" value="1" class="efg-single" data-efg-single formnovalidate>
						<?php esc_html_e( 'Flyer for just this', 'event-flyer-generator' ); ?>
					</button>
				</li>
			<?php endforeach; ?>
		</ul>

		<p class="efg-picker-count" data-efg-count aria-live="polite"></p>

		<div class="efg-field">
			<label for="efg-picker-name"><?php esc_html_e( 'Flyer headline', 'event-flyer-generator' ); ?></label>
			<input type="text" id="efg-picker-name" name="program_name" maxlength="<?php echo esc_attr( EFG_Shortcode::MAX_FIELD_LEN ); ?>" value="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" />
		</div>

		<div class="efg-field">
			<label for="efg-picker-footer"><?php esc_html_e( 'Footer line', 'event-flyer-generator' ); ?></label>
			<input type="text" id="efg-picker-footer" name="footer_line" maxlength="<?php echo esc_attr( EFG_Shortcode::MAX_FIELD_LEN ); ?>" placeholder="<?php esc_attr_e( 'e.g. Open to all · Every skill level', 'event-flyer-generator' ); ?>" />
		</div>

		<button type="submit" name="efg_picker_submit" value="1" class="efg-submit"><?php esc_html_e( 'Generate flyer from selected', 'event-flyer-generator' ); ?></button>
	</form>
</div>

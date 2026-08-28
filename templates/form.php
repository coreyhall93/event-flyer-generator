<?php
/**
 * Front-end flyer-builder form. Included by EFG_Shortcode::render_form().
 *
 * @package event-flyer-generator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="efg-wrap">
	<form method="post" class="efg-form">
		<?php wp_nonce_field( 'efg_create_flyer', 'efg_nonce' ); ?>
		<input type="hidden" name="efg_return" value="<?php echo esc_url( get_permalink() ); ?>" />

		<div class="efg-field">
			<label for="efg-program-name"><?php esc_html_e( 'Program name', 'event-flyer-generator' ); ?></label>
			<input type="text" id="efg-program-name" name="program_name" maxlength="<?php echo esc_attr( EFG_Shortcode::MAX_FIELD_LEN ); ?>" required />
		</div>

		<div id="efg-events">
			<?php for ( $i = 0; $i < EFG_Shortcode::MAX_EVENTS; $i++ ) : ?>
				<fieldset class="efg-event <?php echo $i > 0 ? 'efg-event--hidden' : ''; ?>" data-efg-event="<?php echo esc_attr( $i ); ?>">
					<legend>
						<?php
						echo esc_html(
							sprintf(
								/* translators: %d: event number, 1 through 4. */
								__( 'Event %d', 'event-flyer-generator' ),
								$i + 1
							)
						);
						?>
					</legend>

					<div class="efg-row">
						<div class="efg-field">
							<label for="efg-date-<?php echo esc_attr( $i ); ?>"><?php esc_html_e( 'Date', 'event-flyer-generator' ); ?></label>
							<input type="text" id="efg-date-<?php echo esc_attr( $i ); ?>" name="event_date[]" placeholder="OCT 27" />
						</div>
						<div class="efg-field">
							<label for="efg-time-<?php echo esc_attr( $i ); ?>"><?php esc_html_e( 'Time', 'event-flyer-generator' ); ?></label>
							<input type="text" id="efg-time-<?php echo esc_attr( $i ); ?>" name="event_time[]" placeholder="7PM" />
						</div>
					</div>

					<div class="efg-field">
						<span class="efg-label"><?php esc_html_e( 'Icon', 'event-flyer-generator' ); ?></span>
						<div class="efg-icon-grid" role="radiogroup" aria-label="<?php esc_attr_e( 'Event icon', 'event-flyer-generator' ); ?>">
							<?php foreach ( EFG_Icons::all() as $efg_slug => $efg_icon ) : ?>
								<label class="efg-icon-choice" title="<?php echo esc_attr( $efg_icon['label'] ); ?>">
									<input type="radio" name="event_icon[<?php echo esc_attr( $i ); ?>]" value="<?php echo esc_attr( $efg_slug ); ?>" <?php checked( EFG_Icons::FALLBACK, $efg_slug ); ?> />
									<span class="efg-icon-swatch">
										<?php echo EFG_Icons::svg( $efg_slug, 22 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- EFG_Icons::svg() escapes its own attributes. ?>
										<span class="screen-reader-text"><?php echo esc_html( $efg_icon['label'] ); ?></span>
									</span>
								</label>
							<?php endforeach; ?>
						</div>
					</div>

					<div class="efg-field">
						<label for="efg-title-<?php echo esc_attr( $i ); ?>"><?php esc_html_e( 'Event title', 'event-flyer-generator' ); ?></label>
						<input type="text" id="efg-title-<?php echo esc_attr( $i ); ?>" name="event_title[]" placeholder="<?php esc_attr_e( 'e.g. Open Source Events with GatherPress', 'event-flyer-generator' ); ?>" />
					</div>

					<div class="efg-field">
						<label for="efg-desc-<?php echo esc_attr( $i ); ?>"><?php esc_html_e( 'Description', 'event-flyer-generator' ); ?></label>
						<textarea id="efg-desc-<?php echo esc_attr( $i ); ?>" name="event_description[]" rows="2"></textarea>
					</div>

					<div class="efg-row">
						<div class="efg-field">
							<label for="efg-venue-<?php echo esc_attr( $i ); ?>"><?php esc_html_e( 'Venue name', 'event-flyer-generator' ); ?></label>
							<input type="text" id="efg-venue-<?php echo esc_attr( $i ); ?>" name="event_venue[]" />
						</div>
						<div class="efg-field">
							<label for="efg-address-<?php echo esc_attr( $i ); ?>"><?php esc_html_e( 'Address', 'event-flyer-generator' ); ?></label>
							<input type="text" id="efg-address-<?php echo esc_attr( $i ); ?>" name="event_address[]" />
						</div>
					</div>

					<?php if ( $i > 0 ) : ?>
						<button type="button" class="efg-remove-event"><?php esc_html_e( 'Remove this event', 'event-flyer-generator' ); ?></button>
					<?php endif; ?>
				</fieldset>
			<?php endfor; ?>
		</div>

		<button type="button" id="efg-add-event">
			<?php
			echo esc_html(
				sprintf(
					/* translators: %d: maximum number of events on one flyer. */
					__( '+ Add another event (up to %d)', 'event-flyer-generator' ),
					(int) EFG_Shortcode::MAX_EVENTS
				)
			);
			?>
		</button>

		<div class="efg-field">
			<label for="efg-footer"><?php esc_html_e( 'Footer line', 'event-flyer-generator' ); ?></label>
			<input type="text" id="efg-footer" name="footer_line" maxlength="<?php echo esc_attr( EFG_Shortcode::MAX_FIELD_LEN ); ?>" placeholder="<?php esc_attr_e( 'e.g. Open to all · Every skill level', 'event-flyer-generator' ); ?>" />
		</div>

		<button type="submit" name="efg_submit" value="1" class="efg-submit"><?php esc_html_e( 'Generate printable flyer', 'event-flyer-generator' ); ?></button>
	</form>
</div>

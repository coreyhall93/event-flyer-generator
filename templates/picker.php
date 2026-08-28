<?php
/**
 * One-page flyer builder: events on the left, the flyer on the right.
 * Included by EFG_Picker::render().
 *
 * @package event-flyer-generator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$efg_events      = EFG_Events::all();
$efg_max         = EFG_Shortcode::MAX_EVENTS;
$efg_can_add     = EFG_Picker::can_add_events();
$efg_gatherpress = EFG_Events::using_gatherpress();

// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display-only highlight of the event just created.
$efg_added = isset( $_GET['efg_added'] ) ? absint( $_GET['efg_added'] ) : 0;

?>
<div class="efg-builder">
	<form method="post" class="efg-builder-form" id="efg-builder-form">
		<?php wp_nonce_field( 'efg_picker', 'efg_picker_nonce' ); ?>
		<input type="hidden" name="efg_return" value="<?php echo esc_url( get_permalink() ); ?>" />

		<div class="efg-cols">

			<section class="efg-col efg-col--events" aria-labelledby="efg-events-heading">
				<div class="efg-col-head">
					<h2 id="efg-events-heading">
						<?php esc_html_e( 'Your events', 'event-flyer-generator' ); ?>
						<?php if ( $efg_gatherpress ) : ?>
							<span class="efg-source"><?php esc_html_e( 'from GatherPress', 'event-flyer-generator' ); ?></span>
						<?php endif; ?>
					</h2>
					<?php if ( $efg_can_add ) : ?>
						<button type="button" class="efg-btn efg-btn--ghost" data-efg-add-toggle aria-expanded="false" aria-controls="efg-add-panel">
							<?php esc_html_e( '+ Add a new event', 'event-flyer-generator' ); ?>
						</button>
					<?php elseif ( $efg_gatherpress && current_user_can( 'edit_posts' ) ) : ?>
						<a class="efg-btn efg-btn--ghost" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=' . EFG_Events::GP_POST_TYPE ) ); ?>">
							<?php esc_html_e( '+ Add event in GatherPress', 'event-flyer-generator' ); ?>
						</a>
					<?php endif; ?>
				</div>

				<p class="efg-col-note">
					<?php
					echo esc_html(
						sprintf(
							/* translators: %d: maximum number of events on one flyer. */
							__( 'Choose up to %d. They print in the order shown.', 'event-flyer-generator' ),
							(int) $efg_max
						)
					);
					?>
				</p>

				<?php if ( empty( $efg_events ) ) : ?>
					<p class="efg-empty">
						<?php
						if ( $efg_can_add ) {
							esc_html_e( 'No events yet. Add your first one above.', 'event-flyer-generator' );
						} elseif ( $efg_gatherpress ) {
							esc_html_e( 'No events in GatherPress yet. Create one there and it will show up here.', 'event-flyer-generator' );
						} else {
							esc_html_e( 'No events yet.', 'event-flyer-generator' );
						}
						?>
					</p>
				<?php else : ?>
					<ul class="efg-event-list">
						<?php foreach ( $efg_events as $efg_event ) : ?>
							<?php
							$efg_row    = EFG_Events::to_flyer_row( $efg_event );
							$efg_when   = trim( $efg_row['date'] . ' ' . $efg_row['time'] );
							$efg_where  = trim( $efg_row['venue'] );
							$efg_is_new = ( $efg_added && (int) $efg_event->ID === $efg_added );
							?>
							<li class="efg-event-item<?php echo $efg_is_new ? ' is-new' : ''; ?>">
								<label class="efg-event-label">
									<input type="checkbox" name="event_ids[]" value="<?php echo esc_attr( $efg_event->ID ); ?>" data-efg-pick <?php checked( $efg_is_new ); ?> />
									<span class="efg-order" data-efg-order aria-hidden="true"></span>
									<span class="efg-event-meta">
										<span class="efg-event-name"><?php echo esc_html( $efg_event->post_title ); ?></span>
										<?php if ( '' !== $efg_when || '' !== $efg_where ) : ?>
											<span class="efg-event-sub"><?php echo esc_html( trim( $efg_when . ( '' !== $efg_where ? ' · ' . $efg_where : '' ) ) ); ?></span>
										<?php endif; ?>
									</span>
								</label>

								<button type="submit" name="efg_picker_submit" value="1" class="efg-btn efg-btn--quiet" data-efg-single formnovalidate>
									<?php esc_html_e( 'Flyer for just this', 'event-flyer-generator' ); ?>
								</button>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</section>

			<aside class="efg-col efg-col--flyer" aria-labelledby="efg-flyer-heading">
				<div class="efg-panel">
					<h2 id="efg-flyer-heading"><?php esc_html_e( 'Your flyer', 'event-flyer-generator' ); ?></h2>

					<p class="efg-count" data-efg-count aria-live="polite"></p>

					<ol class="efg-chosen" data-efg-chosen>
						<li class="efg-chosen-empty"><?php esc_html_e( 'Nothing selected yet.', 'event-flyer-generator' ); ?></li>
					</ol>

					<div class="efg-field">
						<label for="efg-picker-name"><?php esc_html_e( 'Flyer headline', 'event-flyer-generator' ); ?></label>
						<input type="text" id="efg-picker-name" name="program_name" maxlength="<?php echo esc_attr( EFG_Shortcode::MAX_FIELD_LEN ); ?>" placeholder="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" />
					</div>

					<div class="efg-field">
						<label for="efg-picker-footer"><?php esc_html_e( 'Footer line', 'event-flyer-generator' ); ?></label>
						<input type="text" id="efg-picker-footer" name="footer_line" maxlength="<?php echo esc_attr( EFG_Shortcode::MAX_FIELD_LEN ); ?>" placeholder="<?php esc_attr_e( 'e.g. Open to all · Every skill level', 'event-flyer-generator' ); ?>" />
					</div>

					<button type="submit" name="efg_picker_submit" value="1" class="efg-btn efg-btn--primary" data-efg-generate disabled>
						<?php esc_html_e( 'Generate flyer', 'event-flyer-generator' ); ?>
					</button>
				</div>
			</aside>

		</div>
	</form>

	<?php if ( $efg_can_add ) : ?>
		<form method="post" class="efg-add-panel" id="efg-add-panel" hidden>
			<?php wp_nonce_field( 'efg_add_event', 'efg_add_nonce' ); ?>
			<input type="hidden" name="efg_return" value="<?php echo esc_url( get_permalink() ); ?>" />

			<h2><?php esc_html_e( 'Add a new event', 'event-flyer-generator' ); ?></h2>

			<div class="efg-field">
				<label for="efg-new-title"><?php esc_html_e( 'Event title', 'event-flyer-generator' ); ?></label>
				<input type="text" id="efg-new-title" name="new_title" required maxlength="<?php echo esc_attr( EFG_Shortcode::MAX_FIELD_LEN ); ?>" placeholder="<?php esc_attr_e( 'e.g. Intro to the Block Editor', 'event-flyer-generator' ); ?>" />
			</div>

			<div class="efg-row">
				<div class="efg-field">
					<label for="efg-new-date"><?php esc_html_e( 'Date', 'event-flyer-generator' ); ?></label>
					<input type="text" id="efg-new-date" name="new_date" maxlength="<?php echo esc_attr( EFG_Shortcode::MAX_FIELD_LEN ); ?>" placeholder="OCT 27" />
				</div>
				<div class="efg-field">
					<label for="efg-new-time"><?php esc_html_e( 'Time', 'event-flyer-generator' ); ?></label>
					<input type="text" id="efg-new-time" name="new_time" maxlength="<?php echo esc_attr( EFG_Shortcode::MAX_FIELD_LEN ); ?>" placeholder="7PM" />
				</div>
			</div>

			<div class="efg-field">
				<span class="efg-label"><?php esc_html_e( 'Icon', 'event-flyer-generator' ); ?></span>
				<div class="efg-icon-grid" role="radiogroup" aria-label="<?php esc_attr_e( 'Event icon', 'event-flyer-generator' ); ?>">
					<?php foreach ( EFG_Icons::all() as $efg_slug => $efg_icon ) : ?>
						<label class="efg-icon-choice" title="<?php echo esc_attr( $efg_icon['label'] ); ?>">
							<input type="radio" name="new_icon" value="<?php echo esc_attr( $efg_slug ); ?>" <?php checked( EFG_Icons::FALLBACK, $efg_slug ); ?> />
							<span class="efg-icon-swatch">
								<?php echo EFG_Icons::svg( $efg_slug, 22 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- EFG_Icons::svg() escapes its own attributes. ?>
								<span class="screen-reader-text"><?php echo esc_html( $efg_icon['label'] ); ?></span>
							</span>
						</label>
					<?php endforeach; ?>
				</div>
			</div>
			<div class="efg-field">
				<label for="efg-new-desc"><?php esc_html_e( 'Description', 'event-flyer-generator' ); ?></label>
				<textarea id="efg-new-desc" name="new_description" rows="2" maxlength="<?php echo esc_attr( EFG_Shortcode::MAX_DESC_LEN ); ?>"></textarea>
			</div>

			<div class="efg-row">
				<div class="efg-field">
					<label for="efg-new-venue"><?php esc_html_e( 'Venue name', 'event-flyer-generator' ); ?></label>
					<input type="text" id="efg-new-venue" name="new_venue" maxlength="<?php echo esc_attr( EFG_Shortcode::MAX_FIELD_LEN ); ?>" />
				</div>
				<div class="efg-field">
					<label for="efg-new-address"><?php esc_html_e( 'Address', 'event-flyer-generator' ); ?></label>
					<input type="text" id="efg-new-address" name="new_address" maxlength="<?php echo esc_attr( EFG_Shortcode::MAX_FIELD_LEN ); ?>" />
				</div>
			</div>

			<div class="efg-add-actions">
				<button type="submit" name="efg_add_event" value="1" class="efg-btn efg-btn--primary">
					<?php esc_html_e( 'Save event', 'event-flyer-generator' ); ?>
				</button>
				<button type="button" class="efg-btn efg-btn--quiet" data-efg-add-cancel>
					<?php esc_html_e( 'Cancel', 'event-flyer-generator' ); ?>
				</button>
			</div>
		</form>
	<?php endif; ?>
</div>

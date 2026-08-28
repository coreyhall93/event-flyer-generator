( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		var form = document.getElementById( 'efg-builder-form' );
		if ( ! form ) {
			return;
		}

		var config = window.efgPicker || {};
		var max = parseInt( config.max, 10 ) || 4;

		var boxes = Array.prototype.slice.call( form.querySelectorAll( '[data-efg-pick]' ) );
		var countEl = form.querySelector( '[data-efg-count]' );
		var chosenEl = form.querySelector( '[data-efg-chosen]' );
		var generate = form.querySelector( '[data-efg-generate]' );

		function rowOf( box ) {
			return box.closest( '.efg-event-item' );
		}

		function nameOf( box ) {
			var el = rowOf( box ).querySelector( '.efg-event-name' );
			return el ? el.textContent.trim() : '';
		}

		/**
		 * Reflect the current selection everywhere at once: the order badges in
		 * the left column, the running list in the flyer panel, the count, and
		 * whether Generate is available.
		 *
		 * Numbering follows list order, not click order, because that is what
		 * "they print in the order shown" promises.
		 */
		function update() {
			var picked = boxes.filter( function ( b ) {
				return b.checked;
			} );

			var position = 0;
			boxes.forEach( function ( b ) {
				var row = rowOf( b );
				var badge = row.querySelector( '[data-efg-order]' );

				if ( b.checked ) {
					position++;
					if ( badge ) {
						badge.textContent = position;
					}
				} else if ( badge ) {
					badge.textContent = '';
				}

				row.classList.toggle( 'is-picked', b.checked );

				// Stop at the cap in the UI, so nothing is silently dropped later.
				b.disabled = ! b.checked && picked.length >= max;
			} );

			if ( chosenEl ) {
				chosenEl.innerHTML = '';

				if ( ! picked.length ) {
					var empty = document.createElement( 'li' );
					empty.className = 'efg-chosen-empty';
					empty.textContent = config.emptyLabel || 'Nothing selected yet.';
					chosenEl.appendChild( empty );
				} else {
					picked.forEach( function ( b ) {
						var li = document.createElement( 'li' );
						li.textContent = nameOf( b );
						chosenEl.appendChild( li );
					} );
				}
			}

			if ( countEl ) {
				countEl.textContent = ( config.countLabel || '%1$d of %2$d selected' )
					.replace( '%1$d', picked.length )
					.replace( '%2$d', max );
			}

			if ( generate ) {
				generate.disabled = 0 === picked.length;
			}
		}

		boxes.forEach( function ( b ) {
			b.addEventListener( 'change', update );
		} );

		// "Flyer for just this" — select only that row, then submit normally.
		form.querySelectorAll( '[data-efg-single]' ).forEach( function ( button ) {
			button.addEventListener( 'click', function () {
				var row = button.closest( '.efg-event-item' );
				boxes.forEach( function ( b ) {
					b.disabled = false;
					b.checked = false;
				} );
				var own = row ? row.querySelector( '[data-efg-pick]' ) : null;
				if ( own ) {
					own.checked = true;
				}
			} );
		} );

		// Add-event panel.
		var panel = document.getElementById( 'efg-add-panel' );
		var toggle = document.querySelector( '[data-efg-add-toggle]' );

		if ( panel && toggle ) {
			var setOpen = function ( open ) {
				panel.hidden = ! open;
				toggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );

				if ( open ) {
					panel.scrollIntoView( { behavior: 'smooth', block: 'nearest' } );
					var first = panel.querySelector( 'input[type="text"]' );
					if ( first ) {
						first.focus();
					}
				} else {
					toggle.focus();
				}
			};

			toggle.addEventListener( 'click', function () {
				setOpen( panel.hidden );
			} );

			var cancel = panel.querySelector( '[data-efg-add-cancel]' );
			if ( cancel ) {
				cancel.addEventListener( 'click', function () {
					panel.reset();
					setOpen( false );
				} );
			}

			panel.addEventListener( 'keydown', function ( event ) {
				if ( 'Escape' === event.key ) {
					setOpen( false );
				}
			} );
		}

		update();
	} );
} )();

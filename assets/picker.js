( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		var form = document.querySelector( '.efg-picker' );
		if ( ! form ) {
			return;
		}

		var boxes = Array.prototype.slice.call( form.querySelectorAll( '[data-efg-pick]' ) );
		var count = form.querySelector( '[data-efg-count]' );
		var max = ( window.efgPicker && window.efgPicker.max ) ? parseInt( window.efgPicker.max, 10 ) : 4;
		var tooMany = ( window.efgPicker && window.efgPicker.tooMany ) || '';

		function selected() {
			return boxes.filter( function ( b ) {
				return b.checked;
			} );
		}

		function update() {
			var picked = selected().length;

			// Stop at the cap rather than silently dropping the extras server-side.
			boxes.forEach( function ( b ) {
				b.disabled = ! b.checked && picked >= max;
				b.closest( '.efg-event-item' ).classList.toggle( 'is-picked', b.checked );
			} );

			if ( count ) {
				count.textContent = picked >= max ? tooMany : '';
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

		update();
	} );
} )();

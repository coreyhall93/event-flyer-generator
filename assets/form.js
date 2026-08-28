( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		var addButton = document.getElementById( 'efg-add-event' );
		var events = document.querySelectorAll( '[data-efg-event]' );

		function visibleCount() {
			var count = 0;
			events.forEach( function ( el ) {
				if ( ! el.classList.contains( 'efg-event--hidden' ) ) {
					count++;
				}
			} );
			return count;
		}

		function updateAddButton() {
			addButton.style.display = visibleCount() >= events.length ? 'none' : '';
		}

		addButton.addEventListener( 'click', function () {
			for ( var i = 0; i < events.length; i++ ) {
				if ( events[ i ].classList.contains( 'efg-event--hidden' ) ) {
					events[ i ].classList.remove( 'efg-event--hidden' );
					break;
				}
			}
			updateAddButton();
		} );

		document.querySelectorAll( '.efg-remove-event' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				var fieldset = btn.closest( '[data-efg-event]' );
				fieldset.classList.add( 'efg-event--hidden' );
				fieldset.querySelectorAll( 'input, textarea' ).forEach( function ( field ) {
					field.value = '';
				} );
				updateAddButton();
			} );
		} );

		updateAddButton();
	} );
} )();

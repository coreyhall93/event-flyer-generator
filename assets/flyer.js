( function () {
	'use strict';

	var MIN_SCALE = 0.45;
	var STEP = 0.02;

	function overflows( page, events ) {
		// The page box is a fixed-height flex column with overflow hidden, so
		// check the events area too: it can be squeezed by the flex layout and
		// overflow internally without the page's own scrollHeight growing.
		return (
			page.scrollHeight > page.clientHeight + 1 ||
			events.scrollHeight > events.clientHeight + 1
		);
	}

	/**
	 * Shrink the flyer until all of its content fits on one page.
	 *
	 * Without this, long content is silently clipped by `overflow: hidden` —
	 * whole events and the footer just disappear with no warning. Field length
	 * caps cannot prevent that on their own, because whether a flyer fits
	 * depends on the combination of event count and content length.
	 */
	function fit() {
		var page = document.querySelector( '.efg-page' );
		var events = document.querySelector( '.efg-events' );
		if ( ! page || ! events ) {
			return;
		}

		var scale = 1;
		page.style.setProperty( '--efg-scale', scale );

		while ( scale > MIN_SCALE && overflows( page, events ) ) {
			scale = Math.round( ( scale - STEP ) * 1000 ) / 1000;
			page.style.setProperty( '--efg-scale', scale );
		}

		// Nothing should reach this, given the server-side field caps. If it
		// ever does, say so rather than quietly dropping content.
		if ( overflows( page, events ) ) {
			page.setAttribute( 'data-efg-overflow', 'true' );
		} else {
			page.removeAttribute( 'data-efg-overflow' );
		}
	}

	function ready( fn ) {
		if ( 'loading' === document.readyState ) {
			document.addEventListener( 'DOMContentLoaded', fn );
		} else {
			fn();
		}
	}

	ready( function () {
		fit();

		// Webfonts change metrics after first paint; re-fit once they land.
		if ( document.fonts && document.fonts.ready ) {
			document.fonts.ready.then( fit );
		}

		// Belt and braces: the print box must match what was on screen.
		window.addEventListener( 'beforeprint', fit );

		var printButton = document.querySelector( '[data-efg-print]' );
		if ( printButton ) {
			printButton.addEventListener( 'click', function () {
				window.print();
			} );
		}
	} );
} )();

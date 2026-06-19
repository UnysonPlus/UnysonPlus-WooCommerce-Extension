/**
 * Products carousel — arrow navigation for the [wc_products] Carousel layout.
 * Lightweight, dependency-free: scrolls the scroll-snap track by one item.
 */
( function () {
	function step( track ) {
		var item = track.querySelector( '.upw-product' );
		var gap  = parseFloat( getComputedStyle( track ).columnGap || getComputedStyle( track ).gap ) || 0;
		return item ? item.getBoundingClientRect().width + gap : track.clientWidth * 0.8;
	}

	function wire( root ) {
		var track = root.querySelector( '.upw-products__grid' );
		if ( ! track ) {
			return;
		}
		var prev = root.querySelector( '.upw-products__nav--prev' );
		var next = root.querySelector( '.upw-products__nav--next' );
		if ( prev ) {
			prev.addEventListener( 'click', function () {
				track.scrollBy( { left: -step( track ), behavior: 'smooth' } );
			} );
		}
		if ( next ) {
			next.addEventListener( 'click', function () {
				track.scrollBy( { left: step( track ), behavior: 'smooth' } );
			} );
		}
	}

	function init() {
		var nodes = document.querySelectorAll( '.upw-products--carousel' );
		for ( var i = 0; i < nodes.length; i++ ) {
			wire( nodes[ i ] );
		}
	}

	if ( document.readyState !== 'loading' ) {
		init();
	} else {
		document.addEventListener( 'DOMContentLoaded', init );
	}
} )();

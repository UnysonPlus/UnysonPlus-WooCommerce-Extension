/**
 * Mini Cart dropdown — click to toggle (hover handled by CSS).
 */
( function () {
	function init() {
		var carts = document.querySelectorAll( '.upw-minicart[data-trigger="click"]' );
		for ( var i = 0; i < carts.length; i++ ) {
			( function ( cart ) {
				var toggle = cart.querySelector( '.upw-minicart__toggle' );
				if ( ! toggle ) {
					return;
				}
				toggle.addEventListener( 'click', function ( e ) {
					e.preventDefault();
					var open = cart.classList.toggle( 'is-open' );
					toggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
				} );
			} )( carts[ i ] );
		}
		// Close any open click-cart when clicking outside.
		document.addEventListener( 'click', function ( e ) {
			var open = document.querySelectorAll( '.upw-minicart.is-open' );
			for ( var j = 0; j < open.length; j++ ) {
				if ( ! open[ j ].contains( e.target ) ) {
					open[ j ].classList.remove( 'is-open' );
					var t = open[ j ].querySelector( '.upw-minicart__toggle' );
					if ( t ) {
						t.setAttribute( 'aria-expanded', 'false' );
					}
				}
			}
		} );
	}
	if ( document.readyState !== 'loading' ) {
		init();
	} else {
		document.addEventListener( 'DOMContentLoaded', init );
	}
} )();

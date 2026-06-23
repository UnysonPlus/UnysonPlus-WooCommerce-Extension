/**
 * Account dropdown — click to toggle (hover handled by CSS).
 */
( function () {
	function init() {
		var nodes = document.querySelectorAll( '.upwc-account[data-trigger="click"]' );
		for ( var i = 0; i < nodes.length; i++ ) {
			( function ( el ) {
				var toggle = el.querySelector( '.upwc-account__toggle' );
				if ( ! toggle ) { return; }
				toggle.addEventListener( 'click', function ( e ) {
					e.preventDefault();
					var open = el.classList.toggle( 'is-open' );
					toggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
				} );
			} )( nodes[ i ] );
		}
		document.addEventListener( 'click', function ( e ) {
			var open = document.querySelectorAll( '.upwc-account.is-open' );
			for ( var j = 0; j < open.length; j++ ) {
				if ( ! open[ j ].contains( e.target ) ) {
					open[ j ].classList.remove( 'is-open' );
					var t = open[ j ].querySelector( '.upwc-account__toggle' );
					if ( t ) { t.setAttribute( 'aria-expanded', 'false' ); }
				}
			}
		} );
	}
	if ( document.readyState !== 'loading' ) { init(); } else { document.addEventListener( 'DOMContentLoaded', init ); }
} )();

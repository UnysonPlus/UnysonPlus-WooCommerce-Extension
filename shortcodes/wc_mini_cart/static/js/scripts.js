/**
 * Mini Cart interactions.
 *  - Dropdown (data-panel-style="dropdown" or unset): click toggles a contained
 *    flyout; hover handled by CSS; click-outside closes.
 *  - Drawer  (data-panel-style="drawer"): a right slide-out side-cart. The
 *    .upwc-minicart__drawer clipper is PORTALED to <body> so it's viewport-fixed
 *    (escapes any transformed/backdrop-filtered header ancestor) and its
 *    overflow:hidden contains the off-screen slide (no page overflow). Opens on
 *    click; closes on overlay click, the X button, Esc, or (backdrop off)
 *    click-outside. data-backdrop="yes" dims the page + locks body scroll.
 */
( function () {
	function initDropdowns() {
		var carts = document.querySelectorAll( '.upwc-minicart[data-trigger="click"]:not([data-panel-style="drawer"])' );
		for ( var i = 0; i < carts.length; i++ ) {
			( function ( cart ) {
				var toggle = cart.querySelector( '.upwc-minicart__toggle' );
				if ( ! toggle ) { return; }
				toggle.addEventListener( 'click', function ( e ) {
					e.preventDefault();
					var open = cart.classList.toggle( 'is-open' );
					toggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
				} );
			} )( carts[ i ] );
		}
		document.addEventListener( 'click', function ( e ) {
			var open = document.querySelectorAll( '.upwc-minicart.is-open:not([data-panel-style="drawer"])' );
			for ( var j = 0; j < open.length; j++ ) {
				if ( ! open[ j ].contains( e.target ) ) {
					open[ j ].classList.remove( 'is-open' );
					var t = open[ j ].querySelector( '.upwc-minicart__toggle' );
					if ( t ) { t.setAttribute( 'aria-expanded', 'false' ); }
				}
			}
		} );
	}

	function initDrawers() {
		var carts = document.querySelectorAll( '.upwc-minicart[data-panel-style="drawer"]' );
		for ( var i = 0; i < carts.length; i++ ) {
			( function ( cart ) {
				var toggle = cart.querySelector( '.upwc-minicart__toggle' );
				var drawer = cart.querySelector( '.upwc-minicart__drawer' );
				if ( ! toggle || ! drawer ) { return; }

				// Portal the drawer to <body> so it's positioned against the viewport,
				// not a transformed/backdrop-filtered header ancestor.
				if ( drawer.parentNode !== document.body ) {
					document.body.appendChild( drawer );
				}
				var backdrop = drawer.getAttribute( 'data-backdrop' ) !== 'no';
				var overlay  = drawer.querySelector( '.upwc-minicart__overlay' );
				var panel    = drawer.querySelector( '.upwc-minicart__panel' );
				var closeBtn = drawer.querySelector( '.upwc-minicart__close' );

				function open() {
					drawer.classList.add( 'is-open' );
					toggle.setAttribute( 'aria-expanded', 'true' );
					if ( backdrop ) { document.body.classList.add( 'upwc-drawer-open' ); }
				}
				function close() {
					drawer.classList.remove( 'is-open' );
					toggle.setAttribute( 'aria-expanded', 'false' );
					document.body.classList.remove( 'upwc-drawer-open' );
				}

				toggle.addEventListener( 'click', function ( e ) {
					e.preventDefault();
					drawer.classList.contains( 'is-open' ) ? close() : open();
				} );
				if ( overlay ) { overlay.addEventListener( 'click', close ); }
				if ( closeBtn ) { closeBtn.addEventListener( 'click', close ); }
				document.addEventListener( 'keydown', function ( e ) {
					if ( ( e.key === 'Escape' || e.keyCode === 27 ) && drawer.classList.contains( 'is-open' ) ) { close(); }
				} );

				// Backdrop OFF: page stays interactive → close on click outside the
				// panel (and not the toggle). Backdrop ON: the overlay handles it.
				if ( ! backdrop ) {
					document.addEventListener( 'click', function ( e ) {
						if ( ! drawer.classList.contains( 'is-open' ) ) { return; }
						if ( panel && panel.contains( e.target ) ) { return; }
						if ( toggle.contains( e.target ) ) { return; }
						close();
					} );
				}
			} )( carts[ i ] );
		}
	}

	function init() {
		initDropdowns();
		initDrawers();
	}

	if ( document.readyState !== 'loading' ) {
		init();
	} else {
		document.addEventListener( 'DOMContentLoaded', init );
	}
} )();

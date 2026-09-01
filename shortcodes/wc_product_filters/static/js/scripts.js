/**
 * Product Filters — collapsible blocks. Clicking a block title toggles the block
 * (a class on the parent .upwc-pf__block; CSS hides everything but the title).
 * Event-delegated, so it works for any panel present now or added later.
 */
( function () {
	'use strict';

	function toggle( btn ) {
		var block = btn.closest( '.upwc-pf__block' );
		if ( ! block ) {
			return;
		}
		var collapsed = block.classList.toggle( 'is-collapsed' );
		btn.setAttribute( 'aria-expanded', collapsed ? 'false' : 'true' );
	}

	document.addEventListener( 'click', function ( e ) {
		var btn = e.target.closest( '.upwc-product-filters--collapsible .upwc-pf__block-title' );
		if ( btn ) {
			toggle( btn );
		}
	} );
}() );

/**
 * AJAX filtering.
 *
 * A filter widget is just a set of links (and one price form) pointing at the
 * same page with query args. So rather than re-implementing the query, this
 * FETCHES that URL and swaps in the two parts of the response that changed: the
 * product list, and the filter panel itself (so the active-filter chips and
 * price range update too).
 *
 * That keeps it working identically on a shop archive, a product category, and
 * a builder page carrying a [wc_products] element — the server already knows how
 * to render each of those; we just avoid the reload.
 *
 * Every failure path falls back to a normal navigation, so a filter can never
 * end up doing nothing.
 */
( function () {
	'use strict';

	var PANEL = '.upwc-product-filters--ajax';
	var GRID = 'ul.products';

	function panels() {
		return document.querySelectorAll( PANEL );
	}

	/** The element wrapping the product list we should replace. */
	function gridHost() {
		var grid = document.querySelector( '.upwc-products ' + GRID ) || document.querySelector( GRID );
		if ( ! grid ) { return null; }

		// Prefer our own wrapper (it carries the layout classes); otherwise the
		// bare list, which is all a classic archive template gives us.
		return grid.closest( '.upwc-products' ) || grid;
	}

	function setBusy( on ) {
		var host = gridHost();
		if ( host ) { host.classList.toggle( 'upwc-is-filtering', on ); }
		panels().forEach( function ( p ) { p.classList.toggle( 'upwc-is-filtering', on ); } );
	}

	function swap( url, push ) {
		var host = gridHost();
		if ( ! host ) { window.location.href = url; return; }

		setBusy( true );

		fetch( url, { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } } )
			.then( function ( r ) {
				if ( ! r.ok ) { throw new Error( 'HTTP ' + r.status ); }
				return r.text();
			} )
			.then( function ( html ) {
				var doc = new DOMParser().parseFromString( html, 'text/html' );

				var freshGrid = doc.querySelector( '.upwc-products ' + GRID ) || doc.querySelector( GRID );
				var freshHost = freshGrid ? ( freshGrid.closest( '.upwc-products' ) || freshGrid ) : null;

				if ( ! freshHost ) {
					// The filtered view has no products at all — show whatever the
					// page says instead (WooCommerce prints a "no products" notice).
					var notice = doc.querySelector( '.woocommerce-info, .woocommerce-no-products-found' );
					host.innerHTML = notice ? notice.outerHTML : '';
				} else {
					host.replaceWith( freshHost );
				}

				// Repaint the panels so active filters / price ranges are current.
				var freshPanels = doc.querySelectorAll( PANEL );
				panels().forEach( function ( p, i ) {
					if ( freshPanels[ i ] ) { p.replaceWith( freshPanels[ i ] ); }
				} );

				if ( push && window.history && window.history.pushState ) {
					window.history.pushState( { upwcFilter: true }, '', url );
				}

				// Wishlist / compare controls in the new cards start "off".
				document.dispatchEvent( new CustomEvent( 'upwc:products:updated' ) );

				var target = document.querySelector( '.upwc-products, ' + GRID );
				if ( target ) {
					var top = target.getBoundingClientRect().top + window.pageYOffset - 80;
					window.scrollTo( { top: top, behavior: 'smooth' } );
				}
			} )
			.catch( function () {
				// Anything unexpected: let the browser do it the ordinary way.
				window.location.href = url;
			} )
			.finally( function () {
				setBusy( false );
			} );
	}

	document.addEventListener( 'click', function ( e ) {
		var link = e.target.closest( PANEL + ' a[href]' );
		if ( ! link ) { return; }

		// Leave modified clicks alone — someone opening a filter in a new tab
		// means it, and a collapsible block's toggle is a button, not a link.
		if ( e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || 1 !== e.which ) { return; }
		if ( link.target && '_self' !== link.target ) { return; }
		if ( link.origin !== window.location.origin ) { return; }

		e.preventDefault();
		swap( link.href, true );
	} );

	document.addEventListener( 'submit', function ( e ) {
		var form = e.target.closest ? e.target.closest( PANEL + ' form' ) : null;
		if ( ! form ) { return; }

		e.preventDefault();

		var data = new URLSearchParams( new FormData( form ) );
		var action = form.getAttribute( 'action' ) || window.location.pathname;
		swap( action + ( action.indexOf( '?' ) === -1 ? '?' : '&' ) + data.toString(), true );
	} );

	// Back / forward through filtered views.
	window.addEventListener( 'popstate', function ( e ) {
		if ( e.state && e.state.upwcFilter ) { swap( window.location.href, false ); }
	} );
}() );

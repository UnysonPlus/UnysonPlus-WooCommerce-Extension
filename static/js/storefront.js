/**
 * storefront.js — wishlist, compare, back-in-stock, swatches, sticky add-to-cart
 * and the size-guide modal.
 *
 * One file rather than six: they share a nonce, an endpoint style and a page,
 * and six scripts would mean six requests for a few hundred lines.
 *
 * Everything is delegated from `document`, so markup that arrives later — a
 * Load More page, a Quick View modal, an AJAX-filtered grid — works without
 * re-initialising anything.
 *
 * State is CLIENT-hydrated on purpose: the server renders every heart and
 * compare toggle in the "off" state so the markup is identical for everyone and
 * safe to cache, and this script turns on the ones belonging to this visitor.
 */
( function () {
	'use strict';

	var cfg = window.upwcStorefront || {};
	var ajaxUrl = cfg.ajaxUrl;
	var nonce = cfg.nonce;
	var i18n = cfg.i18n || {};

	if ( ! ajaxUrl ) { return; }

	/* ---------------------------------------------------------------- utils */

	function post( action, data ) {
		var body = new URLSearchParams();
		body.set( 'action', action );
		body.set( 'nonce', nonce );
		Object.keys( data || {} ).forEach( function ( k ) { body.set( k, data[ k ] ); } );

		return fetch( ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body: body.toString(),
		} ).then( function ( r ) { return r.json(); } );
	}

	function each( list, fn ) { Array.prototype.forEach.call( list, fn ); }

	/** Announce something without stealing focus. */
	function announce( el, message ) {
		if ( ! el ) { return; }
		el.textContent = message;
	}

	/* ------------------------------------------------------------- wishlist */

	var wishlist = {
		ids: [],

		paint: function () {
			each( document.querySelectorAll( '.upwc-wishlist-btn' ), function ( btn ) {
				var on = wishlist.ids.indexOf( parseInt( btn.dataset.product, 10 ) ) !== -1;
				btn.classList.toggle( 'is-active', on );
				btn.setAttribute( 'aria-pressed', on ? 'true' : 'false' );
				btn.setAttribute( 'title', on ? ( i18n.wishlistRemove || '' ) : ( i18n.wishlistAdd || '' ) );

				var label = btn.querySelector( '.upwc-wishlist-btn__label' );
				if ( label ) { label.textContent = on ? ( i18n.wishlistRemove || '' ) : ( i18n.wishlistAdd || '' ); }
			} );

			each( document.querySelectorAll( '.upwc-wishlist-count' ), function ( el ) {
				el.textContent = wishlist.ids.length;
				el.classList.toggle( 'is-empty', wishlist.ids.length === 0 );
			} );
		},

		hydrate: function () {
			if ( ! document.querySelector( '.upwc-wishlist-btn, .upwc-wishlist-count' ) ) { return; }

			post( 'upwc_wc_wishlist_get', {} ).then( function ( res ) {
				if ( res && res.success ) {
					wishlist.ids = ( res.data.ids || [] ).map( Number );
					wishlist.paint();
				}
			} ).catch( function () { /* offline: hearts stay off, nothing breaks */ } );
		},

		toggle: function ( btn ) {
			var id = parseInt( btn.dataset.product, 10 );
			if ( ! id || btn.classList.contains( 'is-busy' ) ) { return; }

			btn.classList.add( 'is-busy' );

			// Optimistic: the heart responds immediately, and is put back if the
			// request fails. A like button that waits on a round trip feels broken.
			var was = wishlist.ids.indexOf( id ) !== -1;
			wishlist.ids = was ? wishlist.ids.filter( function ( x ) { return x !== id; } ) : [ id ].concat( wishlist.ids );
			wishlist.paint();

			post( 'upwc_wc_wishlist_toggle', { product_id: id } ).then( function ( res ) {
				if ( res && res.success ) {
					wishlist.ids = ( res.data.ids || [] ).map( Number );
				} else {
					wishlist.ids = was ? [ id ].concat( wishlist.ids ) : wishlist.ids.filter( function ( x ) { return x !== id; } );
				}
				wishlist.paint();
			} ).catch( function () {
				wishlist.ids = was ? [ id ].concat( wishlist.ids ) : wishlist.ids.filter( function ( x ) { return x !== id; } );
				wishlist.paint();
			} ).finally( function () {
				btn.classList.remove( 'is-busy' );
			} );
		},
	};

	/* -------------------------------------------------------------- compare */

	var compare = {
		ids: [],
		items: [],

		paint: function () {
			each( document.querySelectorAll( '.upwc-compare-btn' ), function ( btn ) {
				var on = compare.ids.indexOf( parseInt( btn.dataset.product, 10 ) ) !== -1;
				btn.classList.toggle( 'is-active', on );
				btn.setAttribute( 'aria-pressed', on ? 'true' : 'false' );

				var label = btn.querySelector( '.upwc-compare-btn__label' );
				if ( label ) { label.textContent = on ? ( i18n.compareAdded || '' ) : ( i18n.compare || '' ); }
			} );

			var bar = document.querySelector( '.upwc-compare-bar' );
			if ( ! bar ) { return; }

			var slots = bar.querySelector( '.upwc-compare-bar__items' );
			if ( slots ) {
				slots.innerHTML = '';
				compare.items.forEach( function ( item ) {
					var cell = document.createElement( 'div' );
					cell.className = 'upwc-compare-bar__item';
					cell.innerHTML =
						( item.image ? '<img src="' + item.image + '" alt="" />' : '' ) +
						'<span>' + item.name + '</span>' +
						'<button type="button" class="upwc-compare-bar__remove" data-product="' + item.id + '" aria-label="' +
						( i18n.remove || 'Remove' ) + '">&times;</button>';
					slots.appendChild( cell );
				} );
			}

			bar.hidden = compare.items.length === 0;
		},

		hydrate: function () {
			if ( ! document.querySelector( '.upwc-compare-btn, .upwc-compare-bar' ) ) { return; }

			post( 'upwc_wc_compare_get', {} ).then( function ( res ) {
				if ( res && res.success ) {
					compare.ids = ( res.data.ids || [] ).map( Number );
					compare.items = res.data.items || [];
					compare.paint();
				}
			} ).catch( function () {} );
		},

		toggle: function ( id ) {
			if ( ! id ) { return; }

			post( 'upwc_wc_compare_toggle', { product_id: id } ).then( function ( res ) {
				if ( ! res || ! res.success ) { return; }

				compare.ids = ( res.data.ids || [] ).map( Number );
				compare.items = res.data.items || [];
				compare.paint();

				if ( res.data.full && res.data.message ) {
					window.alert( res.data.message );
				}
			} ).catch( function () {} );
		},

		clear: function () {
			post( 'upwc_wc_compare_clear', {} ).then( function () {
				compare.ids = [];
				compare.items = [];
				compare.paint();

				// On the compare page itself, the table is now wrong — reload so it
				// matches the (now empty) selection rather than showing stale columns.
				if ( document.querySelector( '.upwc-compare-table' ) ) { window.location.reload(); }
			} ).catch( function () {} );
		},
	};

	/* ------------------------------------------------------------- swatches */

	/**
	 * Drive WooCommerce's own hidden <select> so variation matching, price,
	 * gallery and add-to-cart all behave natively.
	 */
	function selectSwatch( btn ) {
		var wrap = btn.closest( '.upwc-swatches' );
		if ( ! wrap ) { return; }

		var attribute = btn.dataset.attribute;
		var value = btn.dataset.value;
		var form = btn.closest( 'form.variations_form' ) || document.querySelector( 'form.variations_form' );
		if ( ! form ) { return; }

		var select = form.querySelector( 'select[name="attribute_' + attribute + '"], select[data-attribute_name="attribute_' + attribute + '"]' );
		if ( ! select ) { return; }

		var active = btn.getAttribute( 'aria-pressed' ) === 'true';
		select.value = active ? '' : value;   // pressing the chosen one again clears it
		select.dispatchEvent( new Event( 'change', { bubbles: true } ) );

		each( wrap.querySelectorAll( '.upwc-swatch' ), function ( s ) {
			s.setAttribute( 'aria-pressed', ( ! active && s === btn ) ? 'true' : 'false' );
			s.classList.toggle( 'is-active', ! active && s === btn );
		} );
	}

	/** Keep the swatches in step when WooCommerce changes the select itself. */
	function syncSwatchesFromSelects() {
		each( document.querySelectorAll( '.upwc-swatches--select' ), function ( wrap ) {
			var attribute = wrap.dataset.attribute;
			var form = wrap.closest( 'form.variations_form' ) || document.querySelector( 'form.variations_form' );
			if ( ! form ) { return; }

			var select = form.querySelector( 'select[name="attribute_' + attribute + '"]' );
			if ( ! select ) { return; }

			each( wrap.querySelectorAll( '.upwc-swatch' ), function ( s ) {
				var on = select.value && s.dataset.value === select.value;
				s.setAttribute( 'aria-pressed', on ? 'true' : 'false' );
				s.classList.toggle( 'is-active', !! on );
			} );
		} );
	}

	/* ---------------------------------------------------------- sticky cart */

	function initStickyBar() {
		var bar = document.querySelector( '.upwc-sticky-atc' );
		if ( ! bar ) { return; }

		var anchor = document.querySelector( 'form.cart, .single_add_to_cart_button' );
		if ( ! anchor ) { return; }

		function update() {
			var box = anchor.getBoundingClientRect();
			// Show once the real control has left the viewport upward — never while
			// it is still reachable, or the page would carry two of the same button.
			bar.hidden = ! ( box.bottom < 0 || box.top > window.innerHeight );
		}

		if ( 'IntersectionObserver' in window ) {
			new IntersectionObserver( function ( entries ) {
				bar.hidden = entries[ 0 ].isIntersecting;
			}, { rootMargin: '0px' } ).observe( anchor );
		} else {
			window.addEventListener( 'scroll', update, { passive: true } );
			update();
		}

		var scrollBtn = bar.querySelector( '.upwc-sticky-atc__button--scroll' );
		if ( scrollBtn ) {
			scrollBtn.addEventListener( 'click', function () {
				anchor.scrollIntoView( { behavior: 'smooth', block: 'center' } );
			} );
		}
	}

	/* ----------------------------------------------------------- size guide */

	function sizeGuide( open ) {
		var modal = document.querySelector( '.upwc-size-guide' );
		if ( ! modal ) { return; }

		modal.hidden = ! open;
		document.body.classList.toggle( 'upwc-modal-open', open );

		if ( open ) {
			var close = modal.querySelector( '.upwc-size-guide__close' );
			if ( close ) { close.focus(); }
		}
	}

	/* -------------------------------------------------------- back in stock */

	function backInStock( form ) {
		var wrap = form.closest( '.upwc-bis' );
		var input = form.querySelector( '.upwc-bis__email' );
		var msg = wrap ? wrap.querySelector( '.upwc-bis__message' ) : null;
		var id = wrap ? parseInt( wrap.dataset.product, 10 ) : 0;

		if ( ! input || ! id ) { return; }

		form.classList.add( 'is-busy' );
		announce( msg, i18n.sending || '' );

		post( 'upwc_wc_bis_subscribe', { product_id: id, email: input.value } ).then( function ( res ) {
			if ( res && res.success ) {
				announce( msg, res.data.message );
				form.hidden = true;
			} else {
				announce( msg, ( res && res.data && res.data.message ) || ( i18n.error || '' ) );
			}
		} ).catch( function () {
			announce( msg, i18n.error || '' );
		} ).finally( function () {
			form.classList.remove( 'is-busy' );
		} );
	}

	/* ------------------------------------------------------------ delegation */

	document.addEventListener( 'click', function ( e ) {
		var el;

		if ( ( el = e.target.closest( '.upwc-wishlist-btn' ) ) ) {
			e.preventDefault();
			wishlist.toggle( el );
			return;
		}

		if ( ( el = e.target.closest( '.upwc-compare-btn' ) ) ) {
			e.preventDefault();
			compare.toggle( parseInt( el.dataset.product, 10 ) );
			return;
		}

		if ( ( el = e.target.closest( '.upwc-compare-bar__remove, .upwc-compare-table__remove' ) ) ) {
			e.preventDefault();
			compare.toggle( parseInt( el.dataset.product, 10 ) );
			if ( el.classList.contains( 'upwc-compare-table__remove' ) ) {
				window.setTimeout( function () { window.location.reload(); }, 250 );
			}
			return;
		}

		if ( e.target.closest( '.upwc-compare-bar__clear' ) ) {
			e.preventDefault();
			compare.clear();
			return;
		}

		if ( ( el = e.target.closest( 'button.upwc-swatch' ) ) ) {
			e.preventDefault();
			selectSwatch( el );
			return;
		}

		if ( e.target.closest( '.upwc-size-guide__open' ) ) {
			e.preventDefault();
			sizeGuide( true );
			return;
		}

		if ( e.target.closest( '.upwc-size-guide [data-close]' ) ) {
			e.preventDefault();
			sizeGuide( false );
		}
	} );

	document.addEventListener( 'submit', function ( e ) {
		if ( e.target.classList && e.target.classList.contains( 'upwc-bis__form' ) ) {
			e.preventDefault();
			backInStock( e.target );
		}
	} );

	document.addEventListener( 'keydown', function ( e ) {
		if ( 'Escape' === e.key ) { sizeGuide( false ); }
	} );

	// WooCommerce's variation script rewrites the selects on every change.
	document.addEventListener( 'change', function ( e ) {
		if ( e.target.matches && e.target.matches( 'form.variations_form select' ) ) {
			syncSwatchesFromSelects();
		}
	} );

	function boot() {
		wishlist.hydrate();
		compare.hydrate();
		initStickyBar();
		syncSwatchesFromSelects();
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', boot );
	} else {
		boot();
	}

	// Re-paint after any AJAX that injects cards (Load More, Quick View, filters).
	document.addEventListener( 'upwc:products:updated', function () {
		wishlist.paint();
		compare.paint();
	} );
}() );

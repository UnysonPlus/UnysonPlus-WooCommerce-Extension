/**
 * Products element behaviors: carousel arrows, Load More (AJAX), Quick View (AJAX).
 * Dependency-light; jQuery is used only to trigger WooCommerce's cart events.
 */
( function () {
	var P = window.upwWcProducts || {};
	var $ = window.jQuery;

	/* ---- Carousel ---- */
	function carouselStep( track ) {
		var item = track.querySelector( '.upw-product' );
		var gap  = parseFloat( getComputedStyle( track ).columnGap || getComputedStyle( track ).gap ) || 0;
		return item ? item.getBoundingClientRect().width + gap : track.clientWidth * 0.8;
	}
	function wireCarousel( root ) {
		var track = root.querySelector( '.upw-products__grid' );
		if ( ! track ) { return; }
		var prev = root.querySelector( '.upw-products__nav--prev' );
		var next = root.querySelector( '.upw-products__nav--next' );
		if ( prev ) { prev.addEventListener( 'click', function () { track.scrollBy( { left: -carouselStep( track ), behavior: 'smooth' } ); } ); }
		if ( next ) { next.addEventListener( 'click', function () { track.scrollBy( { left: carouselStep( track ), behavior: 'smooth' } ); } ); }
	}

	/* ---- Load More ---- */
	function ajax( action, data ) {
		var body = new URLSearchParams();
		body.set( 'action', action );
		body.set( 'nonce', P.nonce || '' );
		Object.keys( data ).forEach( function ( k ) { body.set( k, data[ k ] ); } );
		return fetch( P.ajaxUrl, { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: body.toString() } ).then( function ( r ) { return r.json(); } );
	}
	function wireLoadMore( root ) {
		var btn = root.querySelector( '.upw-products__more-btn' );
		var grid = root.querySelector( '.upw-products__grid' );
		if ( ! btn || ! grid ) { return; }
		btn.addEventListener( 'click', function () {
			if ( btn.classList.contains( 'is-loading' ) ) { return; }
			var page = parseInt( btn.getAttribute( 'data-page' ), 10 ) + 1;
			btn.classList.add( 'is-loading' );
			var label = btn.textContent;
			btn.textContent = P.i18n ? P.i18n.loading : 'Loading…';
			ajax( 'upw_wc_products_load_more', { atts: btn.getAttribute( 'data-atts' ), page: page } ).then( function ( res ) {
				btn.classList.remove( 'is-loading' );
				btn.textContent = label;
				if ( ! res || ! res.success ) { return; }
				if ( res.data.html ) { grid.insertAdjacentHTML( 'beforeend', res.data.html ); }
				btn.setAttribute( 'data-page', page );
				if ( ! res.data.has_more ) { btn.parentNode.removeChild( btn ); }
			} ).catch( function () { btn.classList.remove( 'is-loading' ); btn.textContent = label; } );
		} );
	}

	/* ---- Quick View ---- */
	var modal;
	function buildModal() {
		modal = document.createElement( 'div' );
		modal.className = 'upw-qv';
		modal.innerHTML = '<div class="upw-qv__overlay"></div><div class="upw-qv__dialog" role="dialog" aria-modal="true"><button type="button" class="upw-qv__close" aria-label="' + ( P.i18n ? P.i18n.close : 'Close' ) + '">&times;</button><div class="upw-qv__body"></div></div>';
		document.body.appendChild( modal );
		function close() { modal.classList.remove( 'is-open' ); }
		modal.querySelector( '.upw-qv__overlay' ).addEventListener( 'click', close );
		modal.querySelector( '.upw-qv__close' ).addEventListener( 'click', close );
		document.addEventListener( 'keydown', function ( e ) { if ( e.key === 'Escape' ) { close(); } } );
	}
	function openQuickView( productId ) {
		if ( ! modal ) { buildModal(); }
		var body = modal.querySelector( '.upw-qv__body' );
		body.innerHTML = '<div class="upw-qv__loading">' + ( P.i18n ? P.i18n.loading : 'Loading…' ) + '</div>';
		modal.classList.add( 'is-open' );
		ajax( 'upw_wc_quick_view', { product: productId } ).then( function ( res ) {
			if ( res && res.success ) {
				body.innerHTML = res.data.html;
				// Re-init WooCommerce's variation form for variable products.
				if ( $ && $.fn.wc_variation_form ) { $( body ).find( '.variations_form' ).each( function () { $( this ).wc_variation_form(); } ); }
			} else {
				modal.classList.remove( 'is-open' );
			}
		} ).catch( function () { modal.classList.remove( 'is-open' ); } );
	}

	function init() {
		document.querySelectorAll( '.upw-products--carousel' ).forEach( wireCarousel );
		document.querySelectorAll( '.upw-products' ).forEach( wireLoadMore );
		// Quick View buttons (delegated so AJAX-appended cards work too).
		document.addEventListener( 'click', function ( e ) {
			var b = e.target.closest && e.target.closest( '.upw-product__quickview' );
			if ( b ) { e.preventDefault(); openQuickView( b.getAttribute( 'data-product' ) ); }
		} );
	}

	if ( document.readyState !== 'loading' ) { init(); } else { document.addEventListener( 'DOMContentLoaded', init ); }
} )();

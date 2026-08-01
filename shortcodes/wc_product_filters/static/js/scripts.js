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

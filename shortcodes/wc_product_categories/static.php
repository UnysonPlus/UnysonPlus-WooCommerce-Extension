<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

// Make sure WooCommerce's own styles are present so the category grid is styled
// even on a non-shop builder page.
if ( function_exists( 'upwc_wc_enqueue_core_styles' ) ) {
	upwc_wc_enqueue_core_styles();
}

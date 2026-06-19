<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

if ( function_exists( 'upw_wc_enqueue_core_styles' ) ) {
	upw_wc_enqueue_core_styles();
}

// The button is AJAX-capable; ensure WooCommerce's add-to-cart + cart-fragments
// scripts are present so it works (and a Cart Icon updates live) on any page.
if ( wp_script_is( 'wc-add-to-cart', 'registered' ) ) {
	wp_enqueue_script( 'wc-add-to-cart' );
}
if ( wp_script_is( 'wc-cart-fragments', 'registered' ) ) {
	wp_enqueue_script( 'wc-cart-fragments' );
}

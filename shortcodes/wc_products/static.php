<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$wc_ext = fw_ext( 'woocommerce' );
if ( ! $wc_ext ) {
	return;
}

wp_enqueue_style(
	'fw-shortcode-wc-products',
	fw_min_uri( $wc_ext->get_declared_URI( '/shortcodes/wc_products/static/css/styles.css' ) ),
	array(),
	$wc_ext->manifest->get_version()
);

// WooCommerce only auto-loads its add-to-cart scripts on shop / product pages.
// The grid's AJAX add-to-cart buttons can appear on any builder page, so ensure
// the scripts are present (and cart fragments, so a Cart element updates live).
if ( wp_script_is( 'wc-add-to-cart', 'registered' ) ) {
	wp_enqueue_script( 'wc-add-to-cart' );
}
if ( wp_script_is( 'wc-cart-fragments', 'registered' ) ) {
	wp_enqueue_script( 'wc-cart-fragments' );
}

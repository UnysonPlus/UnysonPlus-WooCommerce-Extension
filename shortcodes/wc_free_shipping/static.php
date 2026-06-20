<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$wc_ext = fw_ext( 'woocommerce' );
if ( ! $wc_ext ) {
	return;
}

wp_enqueue_style(
	'fw-shortcode-wc-free-shipping',
	fw_min_uri( $wc_ext->get_declared_URI( '/shortcodes/wc_free_shipping/static/css/styles.css' ) ),
	array(),
	$wc_ext->manifest->get_version()
);

// Live updates as the cart total changes.
if ( wp_script_is( 'wc-cart-fragments', 'registered' ) ) {
	wp_enqueue_script( 'wc-cart-fragments' );
}

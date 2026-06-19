<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$wc_ext = fw_ext( 'woocommerce' );
if ( ! $wc_ext ) {
	return;
}

wp_enqueue_style(
	'fw-shortcode-wc-mini-cart',
	fw_min_uri( $wc_ext->get_declared_URI( '/shortcodes/wc_mini_cart/static/css/styles.css' ) ),
	array(),
	$wc_ext->manifest->get_version()
);

wp_enqueue_script(
	'fw-shortcode-wc-mini-cart',
	$wc_ext->get_declared_URI( '/shortcodes/wc_mini_cart/static/js/scripts.js' ),
	array(),
	$wc_ext->manifest->get_version(),
	true
);

// Live updates of the panel contents + count badge.
if ( wp_script_is( 'wc-cart-fragments', 'registered' ) ) {
	wp_enqueue_script( 'wc-cart-fragments' );
}

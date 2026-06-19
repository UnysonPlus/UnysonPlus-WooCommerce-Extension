<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$wc_ext = fw_ext( 'woocommerce' );
if ( ! $wc_ext ) {
	return;
}

wp_enqueue_style(
	'fw-shortcode-wc-cart-link',
	fw_min_uri( $wc_ext->get_declared_URI( '/shortcodes/wc_cart_link/static/css/styles.css' ) ),
	array(),
	$wc_ext->manifest->get_version()
);

// WooCommerce's AJAX cart fragments script — drives the live count / total
// refresh via the woocommerce_add_to_cart_fragments filter registered in the
// extension class. Safe to enqueue even if WC already queued it.
if ( wp_script_is( 'wc-cart-fragments', 'registered' ) ) {
	wp_enqueue_script( 'wc-cart-fragments' );
}

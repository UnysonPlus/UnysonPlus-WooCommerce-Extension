<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

// WooCommerce core styles (placeholder image, button) + this element's grid CSS.
if ( function_exists( 'upwc_wc_enqueue_core_styles' ) ) {
	upwc_wc_enqueue_core_styles();
}

$wc_ext = fw_ext( 'woocommerce' );
if ( ! $wc_ext ) {
	return;
}

wp_enqueue_style(
	'fw-shortcode-wc-product-categories',
	fw_min_uri( $wc_ext->get_declared_URI( '/shortcodes/wc_product_categories/static/css/styles.css' ) ),
	array(),
	$wc_ext->manifest->get_version()
);

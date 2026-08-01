<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

if ( function_exists( 'upwc_wc_enqueue_core_styles' ) ) {
	upwc_wc_enqueue_core_styles();
}

$wc_ext = fw_ext( 'woocommerce' );
if ( ! $wc_ext ) {
	return;
}

wp_enqueue_style(
	'fw-shortcode-wc-product-filters',
	fw_min_uri( $wc_ext->get_declared_URI( '/shortcodes/wc_product_filters/static/css/styles.css' ) ),
	array(),
	$wc_ext->manifest->get_version()
);

wp_enqueue_script(
	'fw-shortcode-wc-product-filters',
	$wc_ext->get_declared_URI( '/shortcodes/wc_product_filters/static/js/scripts.js' ),
	array(),
	$wc_ext->manifest->get_version(),
	true
);

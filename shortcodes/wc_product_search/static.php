<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$wc_ext = fw_ext( 'woocommerce' );
if ( ! $wc_ext ) {
	return;
}

wp_enqueue_style(
	'fw-shortcode-wc-product-search',
	fw_min_uri( $wc_ext->get_declared_URI( '/shortcodes/wc_product_search/static/css/styles.css' ) ),
	array(),
	$wc_ext->manifest->get_version()
);

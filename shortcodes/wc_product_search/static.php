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

// The submit button can wear a .btn Button Style preset — pull the [button]
// shortcode's base CSS so it renders correctly off-shop. Colors come from the
// theme's globally-output Button presets (Theme Settings → Buttons).
$sc_ext = fw_ext( 'shortcodes' );
if ( $sc_ext ) {
	wp_enqueue_style(
		'fw-shortcode-button',
		fw_min_uri( $sc_ext->get_declared_URI( '/shortcodes/button/static/css/styles.css' ) ),
		array(),
		$sc_ext->manifest->get_version()
	);
}

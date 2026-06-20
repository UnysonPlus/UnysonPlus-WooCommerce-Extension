<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$wc_ext = fw_ext( 'woocommerce' );
if ( ! $wc_ext ) {
	return;
}

if ( function_exists( 'upw_wc_enqueue_core_styles' ) ) {
	upw_wc_enqueue_core_styles();
}

wp_enqueue_style(
	'fw-shortcode-wc-account',
	fw_min_uri( $wc_ext->get_declared_URI( '/shortcodes/wc_account/static/css/styles.css' ) ),
	array(),
	$wc_ext->manifest->get_version()
);

wp_enqueue_script(
	'fw-shortcode-wc-account',
	$wc_ext->get_declared_URI( '/shortcodes/wc_account/static/js/scripts.js' ),
	array(),
	$wc_ext->manifest->get_version(),
	true
);

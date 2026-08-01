<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

if ( function_exists( 'upwc_wc_enqueue_core_styles' ) ) {
	upwc_wc_enqueue_core_styles();
}

// The button wears our .btn presets — pull the [button] shortcode's base CSS
// (icon/line-height normalization + shape helpers) and the hover-fx classes so
// the Style / Shape / Hover options render off-shop too. Colors + sizes come
// from the theme's globally-output Button presets (Theme Settings → Buttons).
$sc_ext = fw_ext( 'shortcodes' );
if ( $sc_ext ) {
	wp_enqueue_style(
		'fw-shortcode-button',
		fw_min_uri( $sc_ext->get_declared_URI( '/shortcodes/button/static/css/styles.css' ) ),
		array(),
		$sc_ext->manifest->get_version()
	);
	wp_enqueue_style(
		'fw-shortcode-button-hover-fx',
		fw_min_uri( $sc_ext->get_declared_URI( '/shortcodes/button/static/css/hover-fx.css' ) ),
		array( 'fw-shortcode-button' ),
		$sc_ext->manifest->get_version()
	);
}

// The button is AJAX-capable; ensure WooCommerce's add-to-cart + cart-fragments
// scripts are present so it works (and a Cart Icon updates live) on any page.
if ( wp_script_is( 'wc-add-to-cart', 'registered' ) ) {
	wp_enqueue_script( 'wc-add-to-cart' );
}
if ( wp_script_is( 'wc-cart-fragments', 'registered' ) ) {
	wp_enqueue_script( 'wc-cart-fragments' );
}

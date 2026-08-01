<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Single Product element assets. It renders through the shared card engine, so it
 * reuses the [wc_products] stylesheet + behavior script (Quick View, AJAX cart).
 */

$wc_ext = fw_ext( 'woocommerce' );
if ( ! $wc_ext ) {
	return;
}

if ( function_exists( 'upwc_wc_enqueue_core_styles' ) ) {
	upwc_wc_enqueue_core_styles();
}

// Shared product-card stylesheet (same handle as [wc_products]).
wp_enqueue_style(
	'fw-shortcode-wc-products',
	fw_min_uri( $wc_ext->get_declared_URI( '/shortcodes/wc_products/static/css/styles.css' ) ),
	array(),
	$wc_ext->manifest->get_version()
);

// Shared behavior: Quick View modal + AJAX add-to-cart (carousel/Load More are inert here).
wp_enqueue_script(
	'fw-shortcode-wc-products',
	$wc_ext->get_declared_URI( '/shortcodes/wc_products/static/js/scripts.js' ),
	array(),
	$wc_ext->manifest->get_version(),
	true
);
wp_localize_script(
	'fw-shortcode-wc-products',
	'upwcWcProducts',
	array(
		'ajaxUrl' => admin_url( 'admin-ajax.php' ),
		'nonce'   => wp_create_nonce( 'upwc_wc_products' ),
		'i18n'    => array(
			'loading' => __( 'Loading…', 'fw' ),
			'close'   => __( 'Close', 'fw' ),
		),
	)
);

// Quick View of variable products needs WooCommerce's variation script.
if ( wp_script_is( 'wc-add-to-cart-variation', 'registered' ) ) {
	wp_enqueue_script( 'wc-add-to-cart-variation' );
}
if ( wp_script_is( 'wc-add-to-cart', 'registered' ) ) {
	wp_enqueue_script( 'wc-add-to-cart' );
}
if ( wp_script_is( 'wc-cart-fragments', 'registered' ) ) {
	wp_enqueue_script( 'wc-cart-fragments' );
}

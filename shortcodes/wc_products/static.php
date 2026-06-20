<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$wc_ext = fw_ext( 'woocommerce' );
if ( ! $wc_ext ) {
	return;
}

wp_enqueue_style(
	'fw-shortcode-wc-products',
	fw_min_uri( $wc_ext->get_declared_URI( '/shortcodes/wc_products/static/css/styles.css' ) ),
	array(),
	$wc_ext->manifest->get_version()
);

// Carousel arrows + Load More + Quick View behaviors.
wp_enqueue_script(
	'fw-shortcode-wc-products',
	$wc_ext->get_declared_URI( '/shortcodes/wc_products/static/js/scripts.js' ),
	array( 'jquery' ),
	$wc_ext->manifest->get_version(),
	true
);
wp_localize_script(
	'fw-shortcode-wc-products',
	'upwWcProducts',
	array(
		'ajaxUrl' => admin_url( 'admin-ajax.php' ),
		'nonce'   => wp_create_nonce( 'upw_wc_products' ),
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

// WooCommerce only auto-loads its add-to-cart scripts on shop / product pages.
// The grid's AJAX add-to-cart buttons can appear on any builder page, so ensure
// the scripts are present (and cart fragments, so a Cart element updates live).
if ( wp_script_is( 'wc-add-to-cart', 'registered' ) ) {
	wp_enqueue_script( 'wc-add-to-cart' );
}
if ( wp_script_is( 'wc-cart-fragments', 'registered' ) ) {
	wp_enqueue_script( 'wc-cart-fragments' );
}

<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
/**
 * Product Page element — emits WooCommerce's [product_page id="…"] shortcode.
 * In scope: $atts.
 */

if ( ! class_exists( 'WooCommerce' ) || ! shortcode_exists( 'product_page' ) ) {
	return;
}

$product_id = isset( $atts['product'] ) ? (int) $atts['product'] : 0;
if ( $product_id < 1 ) {
	return;
}

echo do_shortcode( '[product_page id="' . $product_id . '"]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

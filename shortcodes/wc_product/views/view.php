<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
/**
 * Single Product element — emits WooCommerce's [product id="…"] shortcode.
 * In scope: $atts.
 */

if ( ! class_exists( 'WooCommerce' ) || ! shortcode_exists( 'product' ) ) {
	return;
}

$product_id = isset( $atts['product'] ) ? (int) $atts['product'] : 0;
if ( $product_id < 1 ) {
	return;
}

echo do_shortcode( '[product id="' . $product_id . '"]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

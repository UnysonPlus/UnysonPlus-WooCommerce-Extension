<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
/**
 * Product Page element — emits WooCommerce's [product_page id="…"] shortcode.
 * In scope: $atts.
 */

if ( ! class_exists( 'WooCommerce' ) || ! shortcode_exists( 'product_page' ) ) {
	if ( fw_is_editor_context() && function_exists( 'sc_editor_notice' ) ) {
		echo sc_editor_notice( __( 'WooCommerce is not active, so this element cannot render.', 'fw' ) );
	}
	return;
}

$product_id = isset( $atts['product'] ) ? (int) $atts['product'] : 0;
if ( $product_id < 1 ) {
	if ( fw_is_editor_context() && function_exists( 'sc_editor_notice' ) ) {
		echo sc_editor_notice( __( 'Choose a product.', 'fw' ) );
	}
	return;
}

echo do_shortcode( '[product_page id="' . $product_id . '"]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
/**
 * Cart element — emits WooCommerce's [woocommerce_cart] shortcode (classic cart).
 */

if ( ! class_exists( 'WooCommerce' ) || ! shortcode_exists( 'woocommerce_cart' ) ) {
	return;
}

echo do_shortcode( '[woocommerce_cart]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

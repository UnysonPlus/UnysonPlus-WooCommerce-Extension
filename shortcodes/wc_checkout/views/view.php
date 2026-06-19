<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
/**
 * Checkout element — emits WooCommerce's [woocommerce_checkout] shortcode.
 */

if ( ! class_exists( 'WooCommerce' ) || ! shortcode_exists( 'woocommerce_checkout' ) ) {
	return;
}

echo do_shortcode( '[woocommerce_checkout]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

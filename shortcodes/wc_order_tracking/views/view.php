<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
/**
 * Order Tracking element — emits WooCommerce's [woocommerce_order_tracking] shortcode.
 */

if ( ! class_exists( 'WooCommerce' ) || ! shortcode_exists( 'woocommerce_order_tracking' ) ) {
	return;
}

echo do_shortcode( '[woocommerce_order_tracking]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

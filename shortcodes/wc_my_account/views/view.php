<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
/**
 * My Account element — emits WooCommerce's [woocommerce_my_account] shortcode.
 */

if ( ! class_exists( 'WooCommerce' ) || ! shortcode_exists( 'woocommerce_my_account' ) ) {
	return;
}

echo do_shortcode( '[woocommerce_my_account]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

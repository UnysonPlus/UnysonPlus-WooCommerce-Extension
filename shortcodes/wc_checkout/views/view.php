<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
/**
 * Checkout element — emits WooCommerce's [woocommerce_checkout] shortcode.
 */

if ( ! class_exists( 'WooCommerce' ) || ! shortcode_exists( 'woocommerce_checkout' ) ) {
	return;
}

// Catalog lockdown ("Catalog Mode" + "Disable Purchasing"): nothing can be bought,
// so the checkout has nothing to do — render nothing on the front end,
// and say why in the editor so the element doesn't look broken.
if ( function_exists( 'upwc_wc_catalog_locked' ) && upwc_wc_catalog_locked() ) {
	if ( fw_is_editor_context() && function_exists( 'sc_editor_notice' ) ) {
		echo sc_editor_notice( __( 'Hidden: the shop is in Catalog Mode with purchasing disabled.', 'fw' ) );
	}
	return;
}

echo do_shortcode( '[woocommerce_checkout]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

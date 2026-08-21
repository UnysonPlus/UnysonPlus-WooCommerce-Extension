<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
/**
 * Free Shipping Bar element. Renders nothing when no free-shipping threshold is
 * configured for the cart's shipping zone. In scope: $atts.
 */

if ( ! class_exists( 'WooCommerce' ) || ! function_exists( 'upwc_wc_free_shipping_bar_html' ) ) {
	if ( fw_is_editor_context() && function_exists( 'sc_editor_notice' ) ) {
		echo sc_editor_notice( __( 'WooCommerce is not active, so this element cannot render.', 'fw' ) );
	}
	return;
}

$inner = upwc_wc_free_shipping_bar_html();
if ( $inner === '' ) {
	// No threshold configured, or the cart already qualifies — both render nothing
	// to a visitor, and both look like a broken block in an editor.
	if ( fw_is_editor_context() && function_exists( 'sc_editor_notice' ) ) {
		echo sc_editor_notice( __( 'Nothing to show — set a free-shipping threshold in a WooCommerce shipping zone.', 'fw' ) );
	}
	return;
}

echo '<div class="upwc-freeship">' . $inner . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

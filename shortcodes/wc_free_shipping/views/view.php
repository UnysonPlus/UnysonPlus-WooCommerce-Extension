<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
/**
 * Free Shipping Bar element. Renders nothing when no free-shipping threshold is
 * configured for the cart's shipping zone. In scope: $atts.
 */

if ( ! class_exists( 'WooCommerce' ) || ! function_exists( 'upw_wc_free_shipping_bar_html' ) ) {
	return;
}

$inner = upw_wc_free_shipping_bar_html();
if ( $inner === '' ) {
	return;
}

echo '<div class="upw-freeship">' . $inner . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

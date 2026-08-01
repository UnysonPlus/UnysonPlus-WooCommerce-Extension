<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
/**
 * Mini Cart shortcode — thin wrapper over the shared renderer
 * (includes/mini-cart-render.php, loaded by the extension). The simple Icon select
 * (bag|cart|basket) maps to the built-in SVG; branding + trigger + count pass through.
 * In scope: $atts.
 */
if ( ! function_exists( 'upwc_render_mini_cart' ) ) {
	return;
}
echo upwc_render_mini_cart( $atts ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — renderer escapes internally

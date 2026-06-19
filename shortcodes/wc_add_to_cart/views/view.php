<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
/**
 * Add to Cart Button element — emits WooCommerce's [add_to_cart] shortcode.
 * In scope: $atts.
 */

if ( ! class_exists( 'WooCommerce' ) || ! shortcode_exists( 'add_to_cart' ) ) {
	return;
}

$product_id = isset( $atts['product'] ) ? (int) $atts['product'] : 0;
if ( $product_id < 1 ) {
	return;
}

$quantity = isset( $atts['quantity'] ) ? max( 1, (int) $atts['quantity'] ) : 1;
$truthy   = function_exists( 'upw_wc_truthy' )
	? 'upw_wc_truthy'
	: static function ( $v ) { return $v === 'yes' || $v === true; };
$show_price = ! isset( $atts['show_price'] ) || call_user_func( $truthy, $atts['show_price'] );
$wc_style   = isset( $atts['wc_style'] ) && call_user_func( $truthy, $atts['wc_style'] );

// style="" disables WooCommerce's default bordered box so the theme styles the button.
$sc  = '[add_to_cart id="' . $product_id . '"';
$sc .= ' show_price="' . ( $show_price ? 'true' : 'false' ) . '"';
$sc .= ' quantity="' . $quantity . '"';
if ( ! $wc_style ) {
	$sc .= ' style=""';
}
$sc .= ']';

echo do_shortcode( $sc ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

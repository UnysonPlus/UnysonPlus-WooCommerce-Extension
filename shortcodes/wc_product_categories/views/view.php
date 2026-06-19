<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
/**
 * Product Categories element — emits WooCommerce's [product_categories] shortcode
 * from a friendly options UI. In scope: $atts.
 */

if ( ! class_exists( 'WooCommerce' ) || ! shortcode_exists( 'product_categories' ) ) {
	return;
}

$number     = isset( $atts['number'] ) ? (int) $atts['number'] : 0;
$columns    = isset( $atts['columns'] ) ? max( 1, (int) $atts['columns'] ) : 4;
$orderby    = isset( $atts['orderby'] ) ? preg_replace( '/[^a-z_]/', '', (string) $atts['orderby'] ) : 'name';
$order      = ( isset( $atts['order'] ) && strtoupper( (string) $atts['order'] ) === 'DESC' ) ? 'DESC' : 'ASC';
$parent     = isset( $atts['parent'] ) ? trim( (string) $atts['parent'] ) : '';
$ids        = isset( $atts['ids'] ) ? preg_replace( '/[^0-9,]/', '', (string) $atts['ids'] ) : '';
$hide_empty = ! isset( $atts['hide_empty'] ) || ( function_exists( 'upw_wc_truthy' ) ? upw_wc_truthy( $atts['hide_empty'] ) : $atts['hide_empty'] === 'yes' );

$sc  = '[product_categories';
$sc .= ' columns="' . $columns . '"';
$sc .= ' orderby="' . esc_attr( $orderby ) . '"';
$sc .= ' order="' . $order . '"';
$sc .= ' hide_empty="' . ( $hide_empty ? '1' : '0' ) . '"';
if ( $number > 0 ) {
	$sc .= ' number="' . $number . '"';
}
if ( $ids !== '' ) {
	$sc .= ' ids="' . esc_attr( $ids ) . '"';
}
if ( $parent !== '' && is_numeric( $parent ) ) {
	$sc .= ' parent="' . (int) $parent . '"';
}
$sc .= ']';

echo do_shortcode( $sc ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

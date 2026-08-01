<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
/**
 * Single Product element — frontend render.
 *
 * Renders ONE product through the SHARED card engine (upwc_wc_products_card), so it
 * looks and behaves exactly like a single [wc_products] card: Card Rows layout, Card
 * Box Style, Image Ratio/Size, the shared Rating engine, badges, Quick View, and
 * AJAX add-to-cart. In scope: $atts. Inert when WooCommerce is inactive.
 */

if ( ! class_exists( 'WooCommerce' ) || ! function_exists( 'upwc_wc_products_resolve' ) || ! function_exists( 'upwc_wc_products_card' ) ) {
	return;
}

$product_id = isset( $atts['product'] ) ? (int) $atts['product'] : 0;
if ( $product_id < 1 ) {
	return;
}

$product = wc_get_product( $product_id );
if ( ! $product ) {
	return;
}

// Resolve the card options (grid/query keys default harmlessly for a single product).
$r = upwc_wc_products_resolve( $atts );

/* ---- Wrapper (single card) — same base classes as [wc_products] so the shared CSS
   applies, plus a --single modifier for one-off tweaks. No carousel / Load More. ---- */
$wrap_classes = array(
	'upwc-products',
	'upwc-products--single',
	'upwc-products--gap-' . ( $r['gap'] !== '' ? $r['gap'] : 'md' ),
	'upwc-products--ratio-' . ( $r['image_ratio'] !== '' ? $r['image_ratio'] : 'auto' ),
	'upwc-products--grid',
);
if ( $r['alignment'] !== '' && $r['alignment'] !== 'inherit' ) {
	$wrap_classes[] = 'upwc-products--align-' . $r['alignment'];
}

$wrap_style = ( '' !== $r['image_size'] ) ? ' style="--upwc-img-size:' . esc_attr( $r['image_size'] ) . '"' : '';

echo '<div class="' . esc_attr( implode( ' ', $wrap_classes ) ) . '"' . $wrap_style . '>';
echo '<ul class="products upwc-products__grid upwc-products--cols-1">';
echo upwc_wc_products_card( $product, $r ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo '</ul>';
echo '</div>';

<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
/**
 * Upsells element — frontend render.
 *
 * In scope: $atts. Shows the upsells or cross-sells of the product currently
 * being viewed, so a custom single-product layout built in the builder can put
 * them exactly where it wants rather than wherever the template drops them.
 *
 * Renders nothing off a product page (there is no product to take upsells
 * from), and says so in the editor rather than looking broken.
 */

if ( ! class_exists( 'WooCommerce' ) || ! function_exists( 'wc_get_product' ) ) {
	if ( fw_is_editor_context() && function_exists( 'sc_editor_notice' ) ) {
		echo sc_editor_notice( __( 'WooCommerce is not active, so this element cannot render.', 'fw' ) );
	}
	return;
}

$source  = ( isset( $atts['source'] ) && 'cross_sells' === $atts['source'] ) ? 'cross_sells' : 'upsells';
$heading = isset( $atts['heading'] ) ? trim( (string) $atts['heading'] ) : '';
$columns = isset( $atts['columns'] ) ? max( 2, min( 5, (int) $atts['columns'] ) ) : 4;
$limit   = isset( $atts['limit'] ) ? max( 1, (int) $atts['limit'] ) : 4;

global $product;
$current = ( $product instanceof WC_Product ) ? $product : ( is_product() ? wc_get_product( get_the_ID() ) : null );

if ( ! $current instanceof WC_Product ) {
	if ( fw_is_editor_context() && function_exists( 'sc_editor_notice' ) ) {
		echo sc_editor_notice( __( 'This element shows the upsells of the product being viewed, so it only renders on a single product page (or a product template).', 'fw' ) );
	}
	return;
}

$ids = ( 'cross_sells' === $source ) ? $current->get_cross_sell_ids() : $current->get_upsell_ids();
$ids = array_slice( array_filter( array_map( 'absint', (array) $ids ) ), 0, $limit );

if ( ! $ids ) {
	if ( fw_is_editor_context() && function_exists( 'sc_editor_notice' ) ) {
		echo sc_editor_notice(
			'cross_sells' === $source
				? __( 'This product has no cross-sells yet — set them under Product data → Linked Products.', 'fw' )
				: __( 'This product has no upsells yet — set them under Product data → Linked Products.', 'fw' )
		);
	}
	return;
}

$query = new WP_Query( array(
	'post_type'           => 'product',
	'post_status'         => 'publish',
	'post__in'            => $ids,
	'orderby'             => 'post__in',
	'posts_per_page'      => count( $ids ),
	'ignore_sticky_posts' => true,
) );

if ( ! $query->have_posts() ) {
	wp_reset_postdata();
	return;
}

// The single-product globals must be restored afterwards: this element renders
// INSIDE a product page, and leaving $product pointing at an upsell would break
// every template hook that runs after it.
$outer_product = $product;
$outer_post    = isset( $GLOBALS['post'] ) ? $GLOBALS['post'] : null;

echo '<div class="upwc-upsells woocommerce">';

if ( '' !== $heading ) {
	echo '<h2 class="upwc-upsells__heading">' . esc_html( $heading ) . '</h2>';
}

echo '<ul class="products upwc-products__grid upwc-products--cols-' . (int) $columns . '">';

while ( $query->have_posts() ) {
	$query->the_post();
	$item = wc_get_product( get_the_ID() );
	if ( ! $item instanceof WC_Product ) {
		continue;
	}
	// wc_get_template_part reads the globals, so point them at the item.
	$GLOBALS['product'] = $item;
	wc_get_template_part( 'content', 'product' );
}

echo '</ul></div>';

wp_reset_postdata();
$GLOBALS['product'] = $outer_product;
if ( $outer_post ) {
	$GLOBALS['post'] = $outer_post;
	setup_postdata( $outer_post );
}

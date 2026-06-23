<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
/**
 * Products element — frontend render.
 *
 * In scope: $atts. The query + card markup live in
 * includes/products-render.php so the initial render, Load More and Quick View
 * all share identical output. Inert when WooCommerce is inactive.
 */

if ( ! class_exists( 'WooCommerce' ) || ! function_exists( 'upwc_wc_products_resolve' ) ) {
	return;
}

$r    = upwc_wc_products_resolve( $atts );
$args = upwc_wc_products_query_args( $r, 1 );
if ( $args === false ) {
	return;
}

$query = new WP_Query( $args );
if ( ! $query->have_posts() ) {
	wp_reset_postdata();
	return;
}

/* ---- Wrapper classes ----------------------------------------------------- */
$wrap_classes = array(
	'upwc-products',
	'upwc-products--gap-' . ( $r['gap'] !== '' ? $r['gap'] : 'md' ),
	'upwc-products--ratio-' . ( $r['image_ratio'] !== '' ? $r['image_ratio'] : 'auto' ),
	'upwc-products--' . $r['layout'],
);
if ( $r['alignment'] !== '' && $r['alignment'] !== 'inherit' ) {
	$wrap_classes[] = 'upwc-products--align-' . $r['alignment'];
}

$is_carousel = ( $r['layout'] === 'carousel' );
$load_more   = ( ! $is_carousel && $r['pagination'] === 'load_more' && $query->max_num_pages > 1 );

echo '<div class="' . esc_attr( implode( ' ', $wrap_classes ) ) . '">';

if ( $is_carousel && $r['show_arrows'] ) {
	echo '<button type="button" class="upwc-products__nav upwc-products__nav--prev" aria-label="' . esc_attr__( 'Previous', 'fw' ) . '">&#8249;</button>';
}

echo '<ul class="products upwc-products__grid upwc-products--cols-' . (int) $r['columns'] . '">';
while ( $query->have_posts() ) {
	$query->the_post();
	echo upwc_wc_products_card( wc_get_product( get_the_ID() ), $r ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
echo '</ul>';

if ( $is_carousel && $r['show_arrows'] ) {
	echo '<button type="button" class="upwc-products__nav upwc-products__nav--next" aria-label="' . esc_attr__( 'Next', 'fw' ) . '">&#8250;</button>';
}

if ( $load_more ) {
	// The encoded atts let the AJAX handler rebuild the exact same query.
	$payload = wp_json_encode(
		array(
			'source'          => $r['source'],
			'category'        => $r['category'],
			'posts_per_page'  => $r['per_page'],
			'orderby'         => $r['orderby'],
			'order'           => $r['order'],
			'columns'         => $r['columns'],
			'gap'             => $r['gap'],
			'image_ratio'     => $r['image_ratio'],
			'alignment'       => $r['alignment'],
			'pagination'      => 'load_more',
			'tags'            => $r['tags'],
			'attribute'       => $r['attribute'],
			'attribute_terms' => $r['attribute_terms'],
			'product_ids'     => $r['product_ids'],
			'show_sale_badge'    => $r['show_badge'] ? 'yes' : 'no',
			'show_rating'        => $r['show_rating'] ? 'yes' : 'no',
			'show_price'         => $r['show_price'] ? 'yes' : 'no',
			'show_add_to_cart'   => $r['show_atc'] ? 'yes' : 'no',
			'badge_style'        => $r['badge_style'],
			'show_featured_badge' => $r['show_featured'] ? 'yes' : 'no',
			'show_new_badge'     => $r['show_new'] ? 'yes' : 'no',
			'new_days'           => $r['new_days'],
			'show_stock'         => $r['show_stock'] ? 'yes' : 'no',
			'show_quick_view'    => $r['quick_view'] ? 'yes' : 'no',
		)
	);
	echo '<div class="upwc-products__more">';
	echo '<button type="button" class="upwc-products__more-btn button" data-page="1" data-max="' . (int) $query->max_num_pages . '" data-atts="' . esc_attr( $payload ) . '">' . esc_html__( 'Load More', 'fw' ) . '</button>';
	echo '</div>';
}

echo '</div>';

wp_reset_postdata();

<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
/**
 * Wishlist element — frontend render.
 *
 * In scope: $atts. Renders the saved products as a plain grid of cards, each
 * with its heart (so removing something happens where you are looking at it)
 * and an add-to-cart.
 *
 * This is rendered SERVER-side from the visitor's own cookie / user meta, which
 * means it must not be served from a shared page cache. Anything caching whole
 * pages should exclude the wishlist page — same as a cart page, and for the
 * same reason.
 */

if ( ! class_exists( 'WooCommerce' ) || ! function_exists( 'upwc_wishlist_ids' ) ) {
	if ( fw_is_editor_context() && function_exists( 'sc_editor_notice' ) ) {
		echo sc_editor_notice( __( 'WooCommerce is not active, so this element cannot render.', 'fw' ) );
	}
	return;
}

if ( ! upwc_wishlist_enabled() ) {
	if ( fw_is_editor_context() && function_exists( 'sc_editor_notice' ) ) {
		echo sc_editor_notice( __( 'The wishlist is switched off — turn it on in Unyson+ → WooCommerce → Shopper Tools.', 'fw' ) );
	}
	return;
}

$columns     = isset( $atts['columns'] ) ? max( 2, min( 5, (int) $atts['columns'] ) ) : 4;
$empty_text  = isset( $atts['empty_text'] ) ? (string) $atts['empty_text'] : '';
$empty_link  = isset( $atts['empty_link'] ) ? trim( (string) $atts['empty_link'] ) : '';
$empty_label = isset( $atts['empty_label'] ) ? (string) $atts['empty_label'] : '';

$ids = upwc_wishlist_ids();

// In the builder there is no visitor, so show the empty state rather than a
// blank block — the editor needs to see that the element is working.
if ( ! $ids ) {
	echo '<div class="upwc-wishlist-empty">';
	echo '<p>' . esc_html( '' !== $empty_text ? $empty_text : __( 'You have not saved anything yet.', 'fw' ) ) . '</p>';
	if ( '' !== $empty_link ) {
		echo '<a class="button" href="' . esc_url( $empty_link ) . '">'
			. esc_html( '' !== $empty_label ? $empty_label : __( 'Browse the shop', 'fw' ) )
			. '</a>';
	}
	echo '</div>';
	return;
}

$query = new WP_Query( array(
	'post_type'           => 'product',
	'post_status'         => 'publish',
	'post__in'            => $ids,
	// The stored order is newest-first and meaningful, so preserve it rather
	// than letting WP fall back to date order.
	'orderby'             => 'post__in',
	'posts_per_page'      => count( $ids ),
	'ignore_sticky_posts' => true,
) );

if ( ! $query->have_posts() ) {
	wp_reset_postdata();
	// Every saved product has since been unpublished or deleted.
	echo '<div class="upwc-wishlist-empty"><p>'
		. esc_html__( 'The products you saved are no longer available.', 'fw' )
		. '</p></div>';
	return;
}

echo '<div class="upwc-wishlist-grid woocommerce">';
echo '<ul class="products upwc-products__grid upwc-products--cols-' . (int) $columns . '">';

while ( $query->have_posts() ) {
	$query->the_post();
	$product = wc_get_product( get_the_ID() );
	if ( ! $product instanceof WC_Product ) {
		continue;
	}
	?>
	<li class="product upwc-product">
		<span class="upwc-product__wishlist"><?php echo upwc_wishlist_button_html( $product->get_id() ); // phpcs:ignore ?></span>
		<a class="upwc-product__link" href="<?php echo esc_url( $product->get_permalink() ); ?>">
			<span class="upwc-product__media"><?php echo $product->get_image( 'woocommerce_thumbnail' ); // phpcs:ignore ?></span>
			<span class="upwc-product__title"><?php echo esc_html( $product->get_name() ); ?></span>
		</a>
		<?php if ( $product->get_price_html() ) : ?>
			<div class="upwc-product__price"><?php echo $product->get_price_html(); // phpcs:ignore ?></div>
		<?php endif; ?>
		<div class="upwc-product__cart">
			<?php
			// The loop template respects catalog mode / purchasability on its own.
			woocommerce_template_loop_add_to_cart();
			?>
		</div>
	</li>
	<?php
}

echo '</ul></div>';
wp_reset_postdata();

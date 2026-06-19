<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
/**
 * Product Search element — a product-scoped search form (post_type=product).
 * Self-contained clean markup. In scope: $atts.
 */

if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}

$placeholder = isset( $atts['placeholder'] ) && $atts['placeholder'] !== ''
	? (string) $atts['placeholder']
	: __( 'Search products…', 'fw' );

$icon = '<svg class="upw-product-search__icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><circle cx="7" cy="7" r="5"></circle><line x1="11" y1="11" x2="14.5" y2="14.5"></line></svg>';
?>
<form role="search" method="get" class="upw-product-search" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="upw-product-search-field"><?php esc_html_e( 'Search for products:', 'fw' ); ?></label>
	<input type="search" id="upw-product-search-field" class="upw-product-search__field" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php echo esc_attr( $placeholder ); ?>" />
	<input type="hidden" name="post_type" value="product" />
	<button type="submit" class="upw-product-search__submit" aria-label="<?php esc_attr_e( 'Search', 'fw' ); ?>"><?php echo $icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
</form>

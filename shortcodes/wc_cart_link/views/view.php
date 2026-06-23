<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
/**
 * Cart element — frontend render.
 *
 * In scope: $atts. Renders a cart icon (+ optional count badge / total) linking
 * to the cart page. The count / total carry fragment-target classes so the
 * extension's woocommerce_add_to_cart_fragments filter can refresh them live
 * (no reload) when items are added via AJAX.
 *
 * Inert without WooCommerce — the element can persist in saved header/content.
 */

if ( ! class_exists( 'WooCommerce' ) || ! function_exists( 'WC' ) || ! function_exists( 'wc_get_cart_url' ) ) {
	return;
}

$icon  = isset( $atts['icon'] ) ? (string) $atts['icon'] : 'bag';
$label = isset( $atts['label'] ) ? (string) $atts['label'] : '';

$truthy = static function ( $v ) {
	return $v === true || $v === 'yes' || $v === '1' || $v === 1 || $v === 'true';
};
$show_count = $truthy( isset( $atts['show_count'] ) ? $atts['show_count'] : 'yes' );
$show_total = $truthy( isset( $atts['show_total'] ) ? $atts['show_total'] : 'no' );
$hide_empty = $truthy( isset( $atts['hide_when_empty'] ) ? $atts['hide_when_empty'] : 'no' );

$cart  = WC()->cart;
$count = $cart ? (int) $cart->get_cart_contents_count() : 0;
$total = $cart ? $cart->get_cart_total() : '';

if ( $hide_empty && $count < 1 ) {
	return;
}

// Inline icons (stroke, currentColor — inherits the surrounding text color).
$icons = array(
	'bag'    => '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 7h12l1 13H5L6 7z"/><path d="M9 7a3 3 0 0 1 6 0"/></svg>',
	'cart'   => '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="20" r="1.4"/><circle cx="18" cy="20" r="1.4"/><path d="M2 3h3l2.4 12.2a1.5 1.5 0 0 0 1.5 1.2h8.2a1.5 1.5 0 0 0 1.5-1.2L22 7H6"/></svg>',
	'basket' => '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 9h14l-1.2 10.2a1.5 1.5 0 0 1-1.5 1.3H7.7a1.5 1.5 0 0 1-1.5-1.3L5 9z"/><path d="M9 9l3-5 3 5"/></svg>',
);
$icon_svg = isset( $icons[ $icon ] ) ? $icons[ $icon ] : '';

// Wrapper attrs (Advanced tab id/class + Animation atts), if the helper exists.
$attr_html = '';
$classes   = array( 'upwc-cart' );
if ( function_exists( 'sc_build_wrapper_attr' ) ) {
	$atts['base_class']       = 'upwc-cart';
	$atts['unique_id_prefix'] = 'upwc-cart-';
	$atts['extra_attrs']      = array();
	$attr = sc_build_wrapper_attr( $atts );
	if ( ! empty( $attr['class'] ) ) {
		$classes = array_filter( explode( ' ', $attr['class'] ) );
	}
	unset( $attr['class'] );
	$attr_html = function_exists( 'fw_attr_to_html' ) ? fw_attr_to_html( $attr ) : '';
}

$aria = __( 'View cart', 'fw' );
?>
<a class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" href="<?php echo esc_url( wc_get_cart_url() ); ?>" aria-label="<?php echo esc_attr( $aria ); ?>" <?php echo $attr_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php if ( $label !== '' ) : ?>
		<span class="upwc-cart__label"><?php echo esc_html( $label ); ?></span>
	<?php endif; ?>
	<?php if ( $icon_svg !== '' ) : ?>
		<span class="upwc-cart__icon"><?php echo $icon_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php if ( $show_count ) : ?>
				<span class="upwc-cart__count" aria-hidden="true"><?php echo esc_html( $count ); ?></span>
			<?php endif; ?>
		</span>
	<?php elseif ( $show_count ) : ?>
		<span class="upwc-cart__count"><?php echo esc_html( $count ); ?></span>
	<?php endif; ?>
	<?php if ( $show_total ) : ?>
		<span class="upwc-cart__total"><?php echo wp_kses_post( $total ); ?></span>
	<?php endif; ?>
</a>

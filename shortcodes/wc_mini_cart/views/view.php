<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
/**
 * Mini Cart element — cart icon + dropdown panel rendering woocommerce_mini_cart().
 * The panel (.widget_shopping_cart_content) and the count badge refresh live via
 * WooCommerce AJAX fragments. In scope: $atts.
 */

if ( ! class_exists( 'WooCommerce' ) || ! function_exists( 'WC' ) || ! function_exists( 'wc_get_cart_url' ) ) {
	return;
}

$icon_key = isset( $atts['icon'] ) ? (string) $atts['icon'] : 'bag';
$trigger  = ( isset( $atts['trigger'] ) && $atts['trigger'] === 'hover' ) ? 'hover' : 'click';
$truthy   = function_exists( 'upw_wc_truthy' ) ? 'upw_wc_truthy' : static function ( $v ) { return $v === 'yes' || $v === true; };
$show_count = ! isset( $atts['show_count'] ) || call_user_func( $truthy, $atts['show_count'] );

$cart  = WC()->cart;
$count = $cart ? (int) $cart->get_cart_contents_count() : 0;

$icons = array(
	'bag'    => '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 7h12l1 13H5L6 7z"/><path d="M9 7a3 3 0 0 1 6 0"/></svg>',
	'cart'   => '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="20" r="1.4"/><circle cx="18" cy="20" r="1.4"/><path d="M2 3h3l2.4 12.2a1.5 1.5 0 0 0 1.5 1.2h8.2a1.5 1.5 0 0 0 1.5-1.2L22 7H6"/></svg>',
	'basket' => '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 9h14l-1.2 10.2a1.5 1.5 0 0 1-1.5 1.3H7.7a1.5 1.5 0 0 1-1.5-1.3L5 9z"/><path d="M9 9l3-5 3 5"/></svg>',
);
$icon_svg = isset( $icons[ $icon_key ] ) ? $icons[ $icon_key ] : $icons['bag'];
?>
<div class="upw-minicart" data-trigger="<?php echo esc_attr( $trigger ); ?>">
	<a class="upw-minicart__toggle" href="<?php echo esc_url( wc_get_cart_url() ); ?>" aria-haspopup="true" aria-expanded="false" aria-label="<?php esc_attr_e( 'View cart', 'fw' ); ?>">
		<span class="upw-minicart__icon"><?php echo $icon_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php if ( $show_count ) : ?>
				<span class="upw-minicart__count" aria-hidden="true"><?php echo esc_html( $count ); ?></span>
			<?php endif; ?>
		</span>
	</a>
	<div class="upw-minicart__panel" aria-hidden="true">
		<div class="widget_shopping_cart_content">
			<?php woocommerce_mini_cart(); ?>
		</div>
	</div>
</div>

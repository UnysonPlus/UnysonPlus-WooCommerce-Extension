<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
/**
 * Add to Cart Button element — a themed <a> that keeps WooCommerce's AJAX
 * add-to-cart behavior while wearing our Button Style presets (Style / Size /
 * Shape / Width / Alignment). In scope: $atts. Inert when WooCommerce is inactive.
 */

if ( ! class_exists( 'WooCommerce' ) || ! function_exists( 'wc_get_product' ) ) {
	return;
}

$product_id = isset( $atts['product'] ) ? (int) $atts['product'] : 0;
if ( $product_id < 1 ) {
	if ( fw_is_editor_context() && function_exists( 'sc_editor_notice' ) ) {
		echo sc_editor_notice( __( 'Choose a product for this button.', 'fw' ) );
	}
	return;
}

$product = wc_get_product( $product_id );
if ( ! $product ) {
	// The quiet one: a product deleted after this block was placed.
	if ( fw_is_editor_context() && function_exists( 'sc_editor_notice' ) ) {
		echo sc_editor_notice( __( 'That product no longer exists.', 'fw' ) );
	}
	return;
}

$quantity = isset( $atts['quantity'] ) ? max( 1, (int) $atts['quantity'] ) : 1;
$truthy   = function_exists( 'upwc_wc_truthy' ) ? 'upwc_wc_truthy' : static function ( $v ) { return $v === 'yes' || $v === true; };
$show_price     = isset( $atts['show_price'] ) && call_user_func( $truthy, $atts['show_price'] );
$price_position = ( isset( $atts['price_position'] ) && 'after' === $atts['price_position'] ) ? 'after' : 'before';
$custom_label   = isset( $atts['label'] ) && '' !== trim( (string) $atts['label'] ) ? trim( (string) $atts['label'] ) : '';

/* ---- Button classes from the shared Button Style helper --------------------- */
$btn = function_exists( 'sc_button_style_atts' ) ? sc_button_style_atts( $atts ) : array( 'classes' => array(), 'style' => '', 'align' => '' );

$classes = array_merge( array( 'btn' ), $btn['classes'] );
$classes[] = 'product_type_' . $product->get_type();

// AJAX add-to-cart is available for simple, purchasable, in-stock products that
// support it. Variable / grouped / external products link to the product page.
$ajax = $product->supports( 'ajax_add_to_cart' ) && $product->is_purchasable() && $product->is_in_stock() && ! $product->is_type( 'variable' );
if ( $ajax ) {
	$classes[] = 'add_to_cart_button';
	$classes[] = 'ajax_add_to_cart';
}
$classes = array_values( array_unique( array_filter( $classes ) ) );

// Label: custom → product's own add-to-cart text (contextual: "Add to cart",
// "Select options", "Read more"…).
$label = '' !== $custom_label ? $custom_label : $product->add_to_cart_text();

// href: the add-to-cart URL (?add-to-cart=ID for simple; product permalink otherwise).
$href = $product->add_to_cart_url();

$attr = array(
	'href'            => esc_url( $href ),
	'data-quantity'   => (string) $quantity,
	'data-product_id' => (string) $product->get_id(),
	'rel'             => 'nofollow',
	'aria-label'      => wp_strip_all_tags( $product->add_to_cart_description() ),
);
if ( '' !== $btn['style'] ) {
	$attr['style'] = $btn['style'];
}

$attr_html = '';
foreach ( $attr as $k => $v ) {
	if ( '' === $v ) { continue; }
	$attr_html .= ' ' . $k . '="' . esc_attr( $v ) . '"';
}

$button = '<a class="' . esc_attr( implode( ' ', $classes ) ) . '"' . $attr_html . '>' . esc_html( $label ) . '</a>';

/* ---- Optional price --------------------------------------------------------- */
$price_html = '';
if ( $show_price ) {
	$p = $product->get_price_html();
	if ( $p ) {
		$price_html = '<span class="upwc-atc__price">' . $p . '</span>'; // get_price_html() is already escaped markup.
	}
}

$inner = $button;
if ( '' !== $price_html ) {
	$inner = ( 'before' === $price_position ) ? $price_html . ' ' . $button : $button . ' ' . $price_html;
}

/* ---- Alignment wrapper ------------------------------------------------------ */
$align       = $btn['align'];
$wrap_open   = '<div class="upwc-atc"' . ( '' !== $align ? ' style="text-align:' . esc_attr( $align ) . ';"' : '' ) . '>';
$wrap_close  = '</div>';

echo $wrap_open . $inner . $wrap_close; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

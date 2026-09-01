<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
/**
 * Compare element — frontend render.
 *
 * In scope: $atts. Renders the picked products side by side: image, name,
 * price, availability, rating, then every attribute any of them declares.
 *
 * Server-rendered from the visitor's own cookie, so — like the cart and the
 * wishlist — this page must be excluded from any whole-page cache.
 */

if ( ! class_exists( 'WooCommerce' ) || ! function_exists( 'upwc_compare_ids' ) ) {
	if ( fw_is_editor_context() && function_exists( 'sc_editor_notice' ) ) {
		echo sc_editor_notice( __( 'WooCommerce is not active, so this element cannot render.', 'fw' ) );
	}
	return;
}

if ( ! upwc_compare_enabled() ) {
	if ( fw_is_editor_context() && function_exists( 'sc_editor_notice' ) ) {
		echo sc_editor_notice( __( 'Compare is switched off — turn it on in Unyson+ → WooCommerce → Shopper Tools.', 'fw' ) );
	}
	return;
}

$empty_text  = isset( $atts['empty_text'] ) ? (string) $atts['empty_text'] : '';
$empty_link  = isset( $atts['empty_link'] ) ? trim( (string) $atts['empty_link'] ) : '';
$empty_label = isset( $atts['empty_label'] ) ? (string) $atts['empty_label'] : '';

$table = upwc_compare_table_html( upwc_compare_ids() );

if ( '' === $table ) {
	echo '<div class="upwc-compare-empty">';
	echo '<p>' . esc_html( '' !== $empty_text ? $empty_text : __( 'Pick a few products to compare them here.', 'fw' ) ) . '</p>';
	if ( '' !== $empty_link ) {
		echo '<a class="button" href="' . esc_url( $empty_link ) . '">'
			. esc_html( '' !== $empty_label ? $empty_label : __( 'Browse the shop', 'fw' ) )
			. '</a>';
	}
	echo '</div>';
	return;
}

echo '<div class="woocommerce upwc-compare">' . $table . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput -- built escaped.

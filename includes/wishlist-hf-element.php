<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
/**
 * "Wishlist Link" as a native Header / Footer element.
 *
 * A saved list nobody can find is not much of a feature, so the wishlist gets
 * the same treatment as the cart: a draggable element for the header/footer
 * builder, showing a heart with a live count that links to the wishlist page.
 *
 * The count is rendered as 0 and filled in by the storefront script from the
 * visitor's own list — the header is the most-cached markup on a site, so a
 * server-rendered number would be someone else's.
 *
 * Registered through the theme's `unysonplus_hf_elements` API and rendered on
 * `unysonplus_render_hf_element_wishlist_link`. Required only when the
 * WooCommerce plugin is active, so it simply does not exist otherwise.
 *
 * @package unysonplus
 */

if ( ! function_exists( 'upwc_register_wishlist_hf_element' ) ) :
/**
 * @param array $els
 * @return array
 */
function upwc_register_wishlist_hf_element( $els ) {
	$els['wishlist_link'] = array(
		'label'   => __( 'Wishlist Link', 'fw' ),
		'context' => 'both',
		'options' => array(
			'wl_icon'       => array(
				'type'         => 'icon',
				'label'        => __( 'Icon', 'fw' ),
				'help'         => __( 'The glyph for the wishlist link. Defaults to a heart.', 'fw' ),
				'value'        => array( 'type' => 'svg', 'svg-source' => 'library', 'svg-id' => 'lucide/heart' ),
				'preview_size' => 'small',
				'modal_size'   => 'medium',
			),
			'wl_show_count' => function_exists( 'upwc_wc_switch' )
				? upwc_wc_switch( __( 'Item Count', 'fw' ), __( 'Show how many products are saved.', 'fw' ), 'yes' )
				: array( 'type' => 'switch', 'label' => __( 'Item Count', 'fw' ), 'value' => 'yes' ),
			'wl_label'      => array(
				'type'  => 'text',
				'label' => __( 'Label', 'fw' ),
				'desc'  => __( 'Optional text beside the icon (e.g. "Saved"). Empty = icon only.', 'fw' ),
				'value' => '',
			),
			'wl_url'        => array(
				'type'  => 'text',
				'label' => __( 'Link', 'fw' ),
				'desc'  => __( 'Where it goes. Empty = the Wishlist Page set in Unyson+ → WooCommerce → Shopper Tools.', 'fw' ),
				'value' => '',
			),
		),
	);

	return $els;
}
endif;
add_filter( 'unysonplus_hf_elements', 'upwc_register_wishlist_hf_element' );

if ( ! function_exists( 'upwc_render_wishlist_hf_element' ) ) :
/**
 * @param array  $settings Element settings from the builder.
 * @param array  $element  The raw element config.
 * @param string $where    'header' | 'footer'
 * @internal
 */
function upwc_render_wishlist_hf_element( $settings, $element = array(), $where = 'header' ) {
	if ( ! function_exists( 'upwc_wishlist_enabled' ) || ! upwc_wishlist_enabled() ) {
		return;
	}

	$settings = is_array( $settings ) ? $settings : array();

	$url = isset( $settings['wl_url'] ) ? trim( (string) $settings['wl_url'] ) : '';
	if ( '' === $url && function_exists( 'fw_get_db_ext_settings_option' ) ) {
		$url = trim( (string) fw_get_db_ext_settings_option( 'woocommerce', 'wishlist_page' ) );
	}
	if ( '' === $url ) {
		// Nothing to link to. An icon that goes nowhere is worse than no icon.
		return;
	}

	$label = isset( $settings['wl_label'] ) ? trim( (string) $settings['wl_label'] ) : '';
	$count = ! isset( $settings['wl_show_count'] )
		|| ( function_exists( 'upwc_wc_truthy' ) ? upwc_wc_truthy( $settings['wl_show_count'] ) : 'yes' === $settings['wl_show_count'] );

	$icon = '';
	if ( isset( $settings['wl_icon'] ) && function_exists( 'fw_render_icon' ) ) {
		$icon = fw_render_icon( $settings['wl_icon'] );
	}
	if ( '' === $icon ) {
		$icon = '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor"'
			. ' stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
			. '<path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>';
	}

	echo '<a class="upwc-wishlist-link" href="' . esc_url( $url ) . '">'
		. '<span class="upwc-wishlist-link__icon">' . $icon . '</span>' // phpcs:ignore WordPress.Security.EscapeOutput -- icon markup.
		. ( '' !== $label ? '<span class="upwc-wishlist-link__label">' . esc_html( $label ) . '</span>' : '' )
		. ( $count ? '<span class="upwc-wishlist-count is-empty">0</span>' : '' )
		. '<span class="screen-reader-text">' . esc_html__( 'Your wishlist', 'fw' ) . '</span>'
		. '</a>';
}
endif;
add_action( 'unysonplus_render_hf_element_wishlist_link', 'upwc_render_wishlist_hf_element', 10, 3 );

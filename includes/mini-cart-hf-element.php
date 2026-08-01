<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
/**
 * Mini Cart as a native Header / Footer element.
 *
 * Registers a draggable "Mini Cart" element in the theme's header/footer Add-element
 * popup via the `unysonplus_hf_elements` API, and renders it through the shared
 * renderer (includes/mini-cart-render.php). This file is required by the WooCommerce
 * extension ONLY when the WooCommerce plugin is active, so the element simply does not
 * exist otherwise — no separate guard needed. The icon is an icon-picker (any library
 * glyph), defaulting to a shopping bag; the flyout copy mirrors the shortcode's
 * branding fields.
 *
 * @package unysonplus
 */

if ( ! function_exists( 'upwc_mini_cart_hf_default_icon' ) ) :
/**
 * Default cart glyph for the element's icon-picker: lucide "shopping-bag".
 * @return array `icon` option-type value.
 */
function upwc_mini_cart_hf_default_icon() {
	return array( 'type' => 'svg', 'svg-source' => 'library', 'svg-id' => 'lucide/shopping-bag' );
}
endif;

if ( ! function_exists( 'upwc_register_mini_cart_hf_element' ) ) :
/**
 * Add the Mini Cart element to the header/footer Add-element popup.
 *
 * @param array $els registered elements keyed by slug.
 * @return array
 */
function upwc_register_mini_cart_hf_element( $els ) {
	$switch = function_exists( 'upwc_wc_switch' )
		? upwc_wc_switch( __( 'Item Count', 'fw' ), __( 'Show the item-count badge on the icon.', 'fw' ), 'yes' )
		: array( 'type' => 'switch', 'label' => __( 'Item Count', 'fw' ), 'value' => 'yes' );

	$els['mini_cart'] = array(
		'label'   => __( 'Mini Cart', 'fw' ),
		'context' => 'both', // cart usually rides in the header, but a footer cart is valid too.
		'options' => array(
			'mc_icon' => array(
				'type'         => 'icon',
				'label'        => __( 'Cart Icon', 'fw' ),
				'help'         => __( 'The cart glyph. Pick any library icon (default: a shopping bag). Matches the source site by choosing its equivalent icon.', 'fw' ),
				'value'        => upwc_mini_cart_hf_default_icon(),
				'preview_size' => 'small',
				'modal_size'   => 'medium',
			),
			'mc_panel_style' => array(
				'type'    => 'select',
				'label'   => __( 'Open As', 'fw' ),
				'desc'    => __( 'Dropdown = a small flyout below the icon. Drawer = a right slide-out side-cart (portaled to <body>, scroll-locked when its backdrop is on).', 'fw' ),
				'choices' => array(
					'dropdown' => __( 'Dropdown flyout', 'fw' ),
					'drawer'   => __( 'Drawer (side-cart)', 'fw' ),
				),
				'value'   => 'dropdown',
			),
			'mc_drawer_backdrop' => function_exists( 'upwc_wc_switch' )
				? upwc_wc_switch( __( 'Drawer Backdrop', 'fw' ), __( 'Drawer mode only: dim the page + lock scrolling while open (click the backdrop to close). Off = no dim, page stays interactive.', 'fw' ), 'yes' )
				: array( 'type' => 'switch', 'label' => __( 'Drawer Backdrop', 'fw' ), 'value' => 'yes' ),
			'mc_drawer_backdrop_blur' => array(
				'type'    => 'select',
				'label'   => __( 'Backdrop Blur', 'fw' ),
				'desc'    => __( 'Drawer + Backdrop on: also blur (frost) the page behind the drawer, not just dim it.', 'fw' ),
				'choices' => array(
					'0'  => __( 'None (dim only)', 'fw' ),
					'4'  => __( 'Light', 'fw' ),
					'8'  => __( 'Medium', 'fw' ),
					'12' => __( 'Strong', 'fw' ),
				),
				'value'   => '0',
			),
			'mc_trigger' => array(
				'type'    => 'select',
				'label'   => __( 'Open On', 'fw' ),
				'desc'    => __( 'Dropdown only (Drawer is always click).', 'fw' ),
				'choices' => array(
					'click' => __( 'Click', 'fw' ),
					'hover' => __( 'Hover', 'fw' ),
				),
				'value'   => 'click',
			),
			'mc_show_count'      => $switch,
			'mc_panel_title'     => array(
				'type'  => 'text',
				'label' => __( 'Panel Title', 'fw' ),
				'desc'  => __( 'Heading at the top of the open cart panel (with the icon). Empty = no title.', 'fw' ),
				'value' => '',
			),
			'mc_subtotal_label'  => array(
				'type'  => 'text',
				'label' => __( 'Subtotal Label', 'fw' ),
				'desc'  => __( 'Replaces "Subtotal" in the panel. Empty keeps "Subtotal".', 'fw' ),
				'value' => '',
			),
			'mc_checkout_text'   => array(
				'type'  => 'text',
				'label' => __( 'Checkout Button Text', 'fw' ),
				'desc'  => __( 'Replaces the "Checkout" button label. Empty keeps "Checkout".', 'fw' ),
				'value' => '',
			),
			'mc_footnote'        => array(
				'type'  => 'text',
				'label' => __( 'Footnote', 'fw' ),
				'desc'  => __( 'Small reassurance line under the checkout button. Empty for none.', 'fw' ),
				'value' => '',
			),
			// Empty-cart middle content (WooCommerce has no hook for its empty branch, so
			// these replace the plain "No products in the cart."). Leave all empty to keep
			// the default. Power users can instead hook upwc_mini_cart_empty* in PHP.
			'mc_empty_icon' => array(
				'type'         => 'icon',
				'label'        => __( 'Empty — Icon', 'fw' ),
				'help'         => __( 'Icon/emoji shown when the cart is empty (e.g. a cupcake). Leave blank for none.', 'fw' ),
				'value'        => array(),
				'preview_size' => 'small',
				'modal_size'   => 'medium',
			),
			'mc_empty_heading' => array(
				'type'  => 'text',
				'label' => __( 'Empty — Heading', 'fw' ),
				'desc'  => __( 'Empty-cart heading (e.g. "Your basket is totally empty!").', 'fw' ),
				'value' => '',
			),
			'mc_empty_text' => array(
				'type'  => 'text',
				'label' => __( 'Empty — Text', 'fw' ),
				'desc'  => __( 'Empty-cart sub-text.', 'fw' ),
				'value' => '',
			),
			'mc_empty_button_label' => array(
				'type'  => 'text',
				'label' => __( 'Empty — Button Label', 'fw' ),
				'desc'  => __( 'Empty-cart call-to-action (e.g. "Browse Sweets"). Empty for no button.', 'fw' ),
				'value' => '',
			),
			'mc_empty_button_url' => array(
				'type'  => 'text',
				'label' => __( 'Empty — Button URL', 'fw' ),
				'desc'  => __( 'Where the empty-cart button links (e.g. #flavors or the Shop). Empty = the Shop page.', 'fw' ),
				'value' => '',
			),
		),
	);
	return $els;
}
endif;
add_filter( 'unysonplus_hf_elements', 'upwc_register_mini_cart_hf_element' );

if ( ! function_exists( 'upwc_enqueue_mini_cart_assets' ) ) :
/**
 * Enqueue the mini-cart CSS/JS (same handles as the shortcode's static.php) so the
 * element flyout is styled + interactive. Idempotent.
 */
function upwc_enqueue_mini_cart_assets() {
	$ext = function_exists( 'fw_ext' ) ? fw_ext( 'woocommerce' ) : null;
	if ( ! $ext ) {
		return;
	}
	$ver = $ext->manifest->get_version();
	wp_enqueue_style(
		'fw-shortcode-wc-mini-cart',
		function_exists( 'fw_min_uri' ) ? fw_min_uri( $ext->get_declared_URI( '/shortcodes/wc_mini_cart/static/css/styles.css' ) ) : $ext->get_declared_URI( '/shortcodes/wc_mini_cart/static/css/styles.css' ),
		array(),
		$ver
	);
	wp_enqueue_script(
		'fw-shortcode-wc-mini-cart',
		$ext->get_declared_URI( '/shortcodes/wc_mini_cart/static/js/scripts.js' ),
		array(),
		$ver,
		true
	);
	if ( wp_script_is( 'wc-cart-fragments', 'registered' ) ) {
		wp_enqueue_script( 'wc-cart-fragments' );
	}
}
endif;

if ( ! function_exists( 'upwc_hf_uses_mini_cart' ) ) :
/**
 * Cheap scan: is the Mini Cart element placed in any header/footer section? Used to
 * enqueue its assets on wp_enqueue_scripts (before the header renders) so there is no
 * flash of unstyled flyout.
 * @return bool
 */
function upwc_hf_uses_mini_cart() {
	if ( ! function_exists( 'fw_get_db_settings_option' ) ) {
		return false;
	}
	$opts = array( 'header_main', 'header_topbar', 'header_bottombar', 'pre_footer_columns', 'main_footer_columns', 'post_footer_columns', 'copyright_settings' );
	foreach ( $opts as $opt ) {
		$v = fw_get_db_settings_option( $opt, null );
		if ( is_array( $v ) ) {
			$json = wp_json_encode( $v );
			if ( is_string( $json ) && strpos( $json, 'mini_cart' ) !== false ) {
				return true;
			}
		}
	}
	return false;
}
endif;

add_action( 'wp_enqueue_scripts', function () {
	if ( ! is_admin() && upwc_hf_uses_mini_cart() ) {
		upwc_enqueue_mini_cart_assets();
	}
}, 20 );

if ( ! function_exists( 'upwc_render_mini_cart_hf_element' ) ) :
/**
 * Render the Mini Cart header/footer element from its saved settings.
 *
 * @param array  $settings the element's option values (mc_* keys).
 * @param array  $element  the full element item (unused).
 * @param string $where    'header' | 'footer' (unused).
 */
function upwc_render_mini_cart_hf_element( $settings, $element = array(), $where = 'header' ) {
	if ( ! function_exists( 'upwc_render_mini_cart' ) ) {
		return;
	}
	$settings = is_array( $settings ) ? $settings : array();

	// Icon-picker glyph → HTML (falls back to the shortcode's built-in bag SVG if the
	// shortcodes icon renderer isn't available).
	$icon_html = '';
	$icon_val  = isset( $settings['mc_icon'] ) ? $settings['mc_icon'] : upwc_mini_cart_hf_default_icon();
	if ( function_exists( 'sc_icon_render' ) ) {
		$icon_html = sc_icon_render( $icon_val, array( 'class' => 'upwc-minicart__glyph' ) );
	}

	upwc_enqueue_mini_cart_assets(); // fallback if the pre-scan missed it (idempotent).

	echo upwc_render_mini_cart( array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — renderer escapes internally
		'icon_html'       => $icon_html,
		'panel_style'          => isset( $settings['mc_panel_style'] ) ? $settings['mc_panel_style'] : 'dropdown',
		'drawer_backdrop'      => isset( $settings['mc_drawer_backdrop'] ) ? $settings['mc_drawer_backdrop'] : 'yes',
		'drawer_backdrop_blur' => isset( $settings['mc_drawer_backdrop_blur'] ) ? $settings['mc_drawer_backdrop_blur'] : '0',
		'trigger'         => isset( $settings['mc_trigger'] ) ? $settings['mc_trigger'] : 'click',
		'show_count'      => isset( $settings['mc_show_count'] ) ? $settings['mc_show_count'] : 'yes',
		'panel_title'     => isset( $settings['mc_panel_title'] ) ? $settings['mc_panel_title'] : '',
		'subtotal_label'  => isset( $settings['mc_subtotal_label'] ) ? $settings['mc_subtotal_label'] : '',
		'checkout_text'   => isset( $settings['mc_checkout_text'] ) ? $settings['mc_checkout_text'] : '',
		'footnote'        => isset( $settings['mc_footnote'] ) ? $settings['mc_footnote'] : '',
		'empty_icon'         => isset( $settings['mc_empty_icon'] ) ? $settings['mc_empty_icon'] : array(),
		'empty_heading'      => isset( $settings['mc_empty_heading'] ) ? $settings['mc_empty_heading'] : '',
		'empty_text'         => isset( $settings['mc_empty_text'] ) ? $settings['mc_empty_text'] : '',
		'empty_button_label' => isset( $settings['mc_empty_button_label'] ) ? $settings['mc_empty_button_label'] : '',
		'empty_button_url'   => isset( $settings['mc_empty_button_url'] ) ? $settings['mc_empty_button_url'] : '',
	) );
}
endif;
add_action( 'unysonplus_render_hf_element_mini_cart', 'upwc_render_mini_cart_hf_element', 10, 3 );

<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
/**
 * Shared Mini-Cart renderer.
 *
 * ONE render path for both the `wc_mini_cart` page-builder shortcode (body) and the
 * `mini_cart` Header/Footer element (chrome). Either passes an $atts array; the
 * element additionally passes a pre-rendered `icon_html` (an icon-picker glyph) which
 * overrides the three built-in SVG keys. Keeping the markup here means the flyout,
 * live AJAX fragments, and the branding relabel behave identically wherever it lives.
 *
 * @package unysonplus
 */

if ( ! function_exists( 'upwc_mini_cart_icon_svg' ) ) :
/**
 * The three built-in inline-SVG cart glyphs (used by the shortcode's simple Icon
 * select). The header element uses an icon-picker instead and passes icon_html.
 *
 * @param string $key bag|cart|basket
 * @return string inline SVG markup
 */
function upwc_mini_cart_icon_svg( $key ) {
	$icons = array(
		'bag'    => '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 7h12l1 13H5L6 7z"/><path d="M9 7a3 3 0 0 1 6 0"/></svg>',
		'cart'   => '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="20" r="1.4"/><circle cx="18" cy="20" r="1.4"/><path d="M2 3h3l2.4 12.2a1.5 1.5 0 0 0 1.5 1.2h8.2a1.5 1.5 0 0 0 1.5-1.2L22 7H6"/></svg>',
		'basket' => '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 9h14l-1.2 10.2a1.5 1.5 0 0 1-1.5 1.3H7.7a1.5 1.5 0 0 1-1.5-1.3L5 9z"/><path d="M9 9l3-5 3 5"/></svg>',
	);
	return isset( $icons[ $key ] ) ? $icons[ $key ] : $icons['bag'];
}
endif;

if ( ! function_exists( 'upwc_render_mini_cart' ) ) :
/**
 * Render the mini-cart (icon + live flyout panel).
 *
 * @param array $atts {
 *   @type string $icon            bag|cart|basket (used when icon_html is empty).
 *   @type string $icon_html       pre-rendered icon markup (icon-picker) — overrides $icon.
 *   @type string $trigger         click|hover (forced to click in drawer mode).
 *   @type string $show_count      'yes' to show the count badge.
 *   @type string $panel_style     dropdown (default) | drawer (right slide-out side-cart).
 *   @type string $drawer_backdrop 'yes' (default) to dim the page + lock scroll in drawer mode.
 *   @type string $panel_title     optional flyout heading.
 *   @type string $subtotal_label  optional "Subtotal" relabel.
 *   @type string $checkout_text   optional "Checkout" relabel.
 *   @type string $footnote        optional reassurance line under the button.
 * }
 * @return string markup ('' when WooCommerce is unavailable).
 */
function upwc_render_mini_cart( $atts = array() ) {
	if ( ! class_exists( 'WooCommerce' ) || ! function_exists( 'WC' ) || ! function_exists( 'wc_get_cart_url' ) ) {
		return '';
	}
	$atts = is_array( $atts ) ? $atts : array();

	// Drawer = a right slide-out side-cart (portaled to <body> + scroll-locked); anything
	// else is the contained dropdown flyout. Drawer is always click-triggered.
	$panel_style = ( isset( $atts['panel_style'] ) && $atts['panel_style'] === 'drawer' ) ? 'drawer' : 'dropdown';
	$truthy   = function_exists( 'upwc_wc_truthy' ) ? 'upwc_wc_truthy' : static function ( $v ) { return $v === 'yes' || $v === true; };
	$backdrop = ! isset( $atts['drawer_backdrop'] ) || call_user_func( $truthy, $atts['drawer_backdrop'] );
	// Optional frosted backdrop: blur the page behind the drawer (px). 0 = dim only.
	$blur_px  = isset( $atts['drawer_backdrop_blur'] ) ? (int) $atts['drawer_backdrop_blur'] : 0;
	$blur_px  = max( 0, min( 40, $blur_px ) );
	$trigger  = ( $panel_style === 'drawer' )
		? 'click'
		: ( ( isset( $atts['trigger'] ) && $atts['trigger'] === 'hover' ) ? 'hover' : 'click' );
	$show_count = ! isset( $atts['show_count'] ) || call_user_func( $truthy, $atts['show_count'] );

	// Icon: an explicit icon_html (icon-picker) wins; else the built-in SVG key.
	$icon_svg = ( ! empty( $atts['icon_html'] ) )
		? $atts['icon_html']
		: upwc_mini_cart_icon_svg( isset( $atts['icon'] ) ? (string) $atts['icon'] : 'bag' );

	$cart  = WC()->cart;
	$count = $cart ? (int) $cart->get_cart_contents_count() : 0;

	// Branding overrides (any empty = keep the WooCommerce default).
	$panel_title    = isset( $atts['panel_title'] ) ? trim( (string) $atts['panel_title'] ) : '';
	$subtotal_label = isset( $atts['subtotal_label'] ) ? trim( (string) $atts['subtotal_label'] ) : '';
	$checkout_text  = isset( $atts['checkout_text'] ) ? trim( (string) $atts['checkout_text'] ) : '';
	$footnote       = isset( $atts['footnote'] ) ? trim( (string) $atts['footnote'] ) : '';

	// Empty-cart middle content (icon + heading + text + button). WooCommerce has NO
	// hook for the mini-cart empty branch, so we render our own block here (and re-apply
	// it to the AJAX fragment) when any piece is set OR a child hooks upwc_mini_cart_empty*.
	$empty_icon_html = isset( $atts['empty_icon_html'] ) ? (string) $atts['empty_icon_html'] : '';
	if ( '' === $empty_icon_html && ! empty( $atts['empty_icon'] ) && function_exists( 'sc_icon_render' ) ) {
		$empty_icon_html = sc_icon_render( $atts['empty_icon'], array( 'class' => 'upwc-minicart__empty-glyph' ) );
	}
	$empty = array(
		'icon_html'    => $empty_icon_html,
		'heading'      => isset( $atts['empty_heading'] ) ? trim( (string) $atts['empty_heading'] ) : '',
		'text'         => isset( $atts['empty_text'] ) ? trim( (string) $atts['empty_text'] ) : '',
		'button_label' => isset( $atts['empty_button_label'] ) ? trim( (string) $atts['empty_button_label'] ) : '',
		'button_url'   => isset( $atts['empty_button_url'] ) ? trim( (string) $atts['empty_button_url'] ) : '',
	);
	$has_custom_empty = upwc_mini_cart_has_custom_empty( $empty );

	// Relabel WooCommerce's own "Subtotal:" / "Checkout" strings only while rendering
	// THIS panel (scoped gettext filter — added right before, removed right after).
	$upwc_mc_labels = array();
	if ( '' !== $subtotal_label ) {
		$upwc_mc_labels['Subtotal:'] = $subtotal_label;
		$upwc_mc_labels['Subtotal']  = $subtotal_label;
	}
	if ( '' !== $checkout_text ) {
		$upwc_mc_labels['Checkout'] = $checkout_text;
	}
	$upwc_mc_gettext = static function ( $translated, $text, $domain ) use ( $upwc_mc_labels ) {
		if ( 'woocommerce' === $domain && isset( $upwc_mc_labels[ $text ] ) ) {
			return $upwc_mc_labels[ $text ];
		}
		return $translated;
	};

	// Persist the Subtotal/Checkout overrides so the AJAX cart-fragment refresh (a
	// separate request that re-renders woocommerce_mini_cart()) can reapply them —
	// otherwise the panel reverts to the WooCommerce defaults after add-to-cart.
	if ( $upwc_mc_labels && get_option( 'upwc_minicart_labels' ) !== $upwc_mc_labels ) {
		update_option( 'upwc_minicart_labels', $upwc_mc_labels, false );
	}
	// Persist the empty-state pieces too, so the AJAX fragment refresh can render the
	// custom empty block when the cart is emptied (last-rendered-wins, like the labels).
	if ( $has_custom_empty && get_option( 'upwc_minicart_empty' ) !== $empty ) {
		update_option( 'upwc_minicart_empty', $empty, false );
	}

	// ---- Panel INNER (shared by dropdown + drawer) ----------------------------
	ob_start();
	if ( 'drawer' === $panel_style ) : ?>
		<button type="button" class="upwc-minicart__close" aria-label="<?php esc_attr_e( 'Close cart', 'fw' ); ?>">
			<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
		</button>
	<?php endif; ?>
	<?php if ( '' !== $panel_title ) : ?>
		<div class="upwc-minicart__title">
			<span class="upwc-minicart__title-icon"><?php echo $icon_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<span class="upwc-minicart__title-text"><?php echo esc_html( $panel_title ); ?></span>
		</div>
	<?php endif; ?>
	<div class="widget_shopping_cart_content">
		<?php
			if ( $has_custom_empty && $cart && $cart->is_empty() ) {
				echo upwc_mini_cart_empty_html( $empty ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			} else {
				if ( $upwc_mc_labels ) {
					add_filter( 'gettext', $upwc_mc_gettext, 20, 3 );
				}
				woocommerce_mini_cart();
				if ( $upwc_mc_labels ) {
					remove_filter( 'gettext', $upwc_mc_gettext, 20 );
				}
			}
		?>
	</div>
	<?php if ( '' !== $footnote ) : ?>
		<div class="upwc-minicart__note"><?php echo esc_html( $footnote ); ?></div>
	<?php endif; ?>
	<?php
	$panel_inner = (string) ob_get_clean();

	$toggle = '<a class="upwc-minicart__toggle" href="' . esc_url( wc_get_cart_url() ) . '" aria-haspopup="true" aria-expanded="false" aria-label="' . esc_attr__( 'View cart', 'fw' ) . '">'
		. '<span class="upwc-minicart__icon">' . $icon_svg
		. ( $show_count ? '<span class="upwc-minicart__count' . ( $count < 1 ? ' upwc-minicart__count--empty' : '' ) . '" aria-hidden="true">' . esc_html( $count ) . '</span>' : '' )
		. '</span></a>';

	// ---- Assemble by style ----------------------------------------------------
	if ( 'drawer' === $panel_style ) {
		// Side-cart: the toggle stays inline; the drawer (clipper → overlay + panel) is
		// portaled to <body> by the script so it escapes any transformed/backdrop-filtered
		// header ancestor and the clipper's overflow:hidden contains the off-screen slide
		// (no page overflow). data-backdrop toggles the dim + scroll-lock.
		$drawer_style = ( $backdrop && $blur_px > 0 ) ? ' style="--upwc-drawer-blur:' . $blur_px . 'px"' : '';
		$html = '<div class="upwc-minicart" data-trigger="click" data-panel-style="drawer" data-backdrop="' . ( $backdrop ? 'yes' : 'no' ) . '">'
			. $toggle
			. '<div class="upwc-minicart__drawer" data-backdrop="' . ( $backdrop ? 'yes' : 'no' ) . '"' . $drawer_style . '>'
				. '<div class="upwc-minicart__overlay" aria-hidden="true"></div>'
				. '<aside class="upwc-minicart__panel" role="dialog" aria-modal="' . ( $backdrop ? 'true' : 'false' ) . '" aria-label="' . esc_attr__( 'Shopping cart', 'fw' ) . '">' . $panel_inner . '</aside>'
			. '</div>'
		. '</div>';
	} else {
		$html = '<div class="upwc-minicart" data-trigger="' . esc_attr( $trigger ) . '" data-panel-style="dropdown">'
			. $toggle
			. '<div class="upwc-minicart__panel" aria-hidden="true">' . $panel_inner . '</div>'
		. '</div>';
	}

	return $html;
}
endif;

if ( ! function_exists( 'upwc_mini_cart_has_custom_empty' ) ) :
/**
 * Is the mini-cart's empty state customized — any piece set, or a child hooked into
 * upwc_mini_cart_empty / upwc_mini_cart_empty_html?
 *
 * @param array $empty {icon_html, heading, text, button_label, button_url}
 * @return bool
 */
function upwc_mini_cart_has_custom_empty( $empty ) {
	if ( is_array( $empty ) ) {
		foreach ( array( 'icon_html', 'heading', 'text', 'button_label' ) as $k ) {
			if ( ! empty( $empty[ $k ] ) ) {
				return true;
			}
		}
	}
	return has_action( 'upwc_mini_cart_empty' ) || has_filter( 'upwc_mini_cart_empty_html' );
}
endif;

if ( ! function_exists( 'upwc_mini_cart_empty_html' ) ) :
/**
 * The mini-cart EMPTY-state block (icon + heading + text + button). WooCommerce's
 * mini-cart template has no hook for its empty branch, so this replaces the plain
 * "No products in the cart." — via no-code options OR a child theme's function:
 *   add_action( 'upwc_mini_cart_empty', function ( $e ) { echo '…'; } );   // prepend
 *   add_filter( 'upwc_mini_cart_empty_html', function ( $html, $e ) { return '…'; }, 10, 2 );
 *
 * @param array $empty {icon_html, heading, text, button_label, button_url}
 * @return string
 */
function upwc_mini_cart_empty_html( $empty = array() ) {
	$empty = wp_parse_args( is_array( $empty ) ? $empty : array(), array(
		'icon_html' => '', 'heading' => '', 'text' => '', 'button_label' => '', 'button_url' => '',
	) );

	ob_start();
	// Child action hook (fires first — a theme can output its own block here).
	do_action( 'upwc_mini_cart_empty', $empty );

	$has_pieces = ( '' !== $empty['icon_html'] || '' !== $empty['heading'] || '' !== $empty['text'] || '' !== $empty['button_label'] );
	if ( $has_pieces ) {
		$url = ( '' !== $empty['button_url'] )
			? $empty['button_url']
			: ( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : '#' );
		?>
		<div class="upwc-minicart__empty">
			<?php if ( '' !== $empty['icon_html'] ) : ?>
				<div class="upwc-minicart__empty-icon"><?php echo $empty['icon_html']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
			<?php endif; ?>
			<?php if ( '' !== $empty['heading'] ) : ?>
				<p class="upwc-minicart__empty-heading"><?php echo esc_html( $empty['heading'] ); ?></p>
			<?php endif; ?>
			<?php if ( '' !== $empty['text'] ) : ?>
				<p class="upwc-minicart__empty-text"><?php echo esc_html( $empty['text'] ); ?></p>
			<?php endif; ?>
			<?php if ( '' !== $empty['button_label'] ) : ?>
				<a class="upwc-minicart__empty-btn button" href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $empty['button_label'] ); ?></a>
			<?php endif; ?>
		</div>
		<?php
	}
	$html = (string) ob_get_clean();

	// Nothing produced (no pieces, no hook output) → the WooCommerce default message.
	if ( '' === trim( $html ) ) {
		$html = '<p class="woocommerce-mini-cart__empty-message">' . esc_html__( 'No products in the cart.', 'fw' ) . '</p>';
	}
	return apply_filters( 'upwc_mini_cart_empty_html', $html, $empty );
}
endif;

if ( ! function_exists( 'upwc_mini_cart_empty_fragment' ) ) :
/**
 * Keep the custom empty state through AJAX: when the cart empties (e.g. the last item
 * is removed), WooCommerce refreshes `div.widget_shopping_cart_content` from its
 * template (the plain empty message). Swap in the custom block (persisted to the
 * `upwc_minicart_empty` option on render, last-rendered-wins, like the labels).
 *
 * @param array $fragments
 * @return array
 */
function upwc_mini_cart_empty_fragment( $fragments ) {
	if ( ! class_exists( 'WooCommerce' ) || ! function_exists( 'WC' ) || ! WC()->cart || ! WC()->cart->is_empty() ) {
		return $fragments;
	}
	$empty = get_option( 'upwc_minicart_empty', array() );
	if ( upwc_mini_cart_has_custom_empty( $empty ) ) {
		$fragments['div.widget_shopping_cart_content'] = '<div class="widget_shopping_cart_content">' . upwc_mini_cart_empty_html( $empty ) . '</div>';
	}
	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'upwc_mini_cart_empty_fragment', 20 );
endif;

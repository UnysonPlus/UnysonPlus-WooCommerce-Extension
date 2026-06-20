<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
/**
 * Shared helpers for the WooCommerce extension's elements.
 *
 * Auto-loaded by Unyson (extensions include their own /helpers.php). Guarded so
 * everything is safe to call whether or not WooCommerce is active.
 */

if ( ! function_exists( 'upw_wc_switch' ) ) {
	/**
	 * A predictable yes/no switch option (stored value is always 'yes' or 'no').
	 */
	function upw_wc_switch( $label, $desc = '', $value = 'no' ) {
		return array(
			'type'         => 'switch',
			'label'        => $label,
			'desc'         => $desc,
			'left-choice'  => array( 'value' => 'no', 'label' => __( 'No', 'fw' ) ),
			'right-choice' => array( 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ),
			'value'        => $value,
		);
	}
}

if ( ! function_exists( 'upw_wc_truthy' ) ) {
	/**
	 * Normalize a stored switch/att value to a boolean.
	 */
	function upw_wc_truthy( $v ) {
		return $v === true || $v === 'yes' || $v === '1' || $v === 1 || $v === 'true';
	}
}

if ( ! function_exists( 'upw_wc_product_choices' ) ) {
	/**
	 * Published-product choices for a select: id => "Title (#id)".
	 * Empty (just a placeholder) when WooCommerce is inactive.
	 *
	 * @param int $limit Max products listed (keeps the select manageable).
	 * @return array
	 */
	function upw_wc_product_choices( $limit = 200 ) {
		$choices = array( '' => __( '— Select a product —', 'fw' ) );

		if ( ! function_exists( 'wc_get_products' ) ) {
			return $choices;
		}

		$products = wc_get_products(
			array(
				'status'  => 'publish',
				'limit'   => (int) $limit,
				'orderby' => 'title',
				'order'   => 'ASC',
				'return'  => 'objects',
			)
		);
		if ( is_array( $products ) ) {
			foreach ( $products as $product ) {
				$choices[ $product->get_id() ] = $product->get_name() . ' (#' . $product->get_id() . ')';
			}
		}

		return $choices;
	}
}

if ( ! function_exists( 'upw_wc_enqueue_core_styles' ) ) {
	/**
	 * Ensure WooCommerce's own frontend stylesheets are loaded. WooCommerce
	 * only auto-enqueues them on shop pages, but our wrapper elements (which
	 * emit WC shortcodes like [product_categories], [product], [add_to_cart])
	 * can appear on ANY builder page — so its shortcode output stays styled.
	 */
	function upw_wc_enqueue_core_styles() {
		foreach ( array( 'woocommerce-layout', 'woocommerce-smallscreen', 'woocommerce-general' ) as $handle ) {
			if ( wp_style_is( $handle, 'registered' ) && ! wp_style_is( $handle, 'enqueued' ) ) {
				wp_enqueue_style( $handle );
			}
		}
	}
}

if ( ! function_exists( 'upw_wc_free_shipping_threshold' ) ) {
	/**
	 * The free-shipping spend threshold for the current cart's shipping zone.
	 * 0 when no free-shipping method requires a minimum. Filterable.
	 *
	 * @return float
	 */
	function upw_wc_free_shipping_threshold() {
		$threshold = 0.0;

		if ( function_exists( 'WC' ) && WC()->cart && class_exists( 'WC_Shipping_Zones' ) ) {
			$packages = WC()->cart->get_shipping_packages();
			$package  = is_array( $packages ) && ! empty( $packages ) ? reset( $packages ) : array();
			$zone     = function_exists( 'wc_get_shipping_zone' ) ? wc_get_shipping_zone( $package ) : null;
			if ( $zone ) {
				foreach ( $zone->get_shipping_methods( true ) as $method ) {
					if ( $method->id === 'free_shipping'
						&& in_array( $method->get_option( 'requires' ), array( 'min_amount', 'either', 'both' ), true ) ) {
						$threshold = (float) $method->get_option( 'min_amount' );
						break;
					}
				}
			}
		}

		return (float) apply_filters( 'upw_wc_free_shipping_threshold', $threshold );
	}
}

if ( ! function_exists( 'upw_wc_free_shipping_bar_html' ) ) {
	/**
	 * Inner HTML of the free-shipping progress bar (message + track).
	 * Shared by the element view and the cart-fragment refresh.
	 *
	 * @return string Empty when there is no free-shipping threshold.
	 */
	function upw_wc_free_shipping_bar_html() {
		$threshold = upw_wc_free_shipping_threshold();
		if ( $threshold <= 0 || ! function_exists( 'WC' ) || ! WC()->cart ) {
			return '';
		}

		$current   = (float) WC()->cart->get_displayed_subtotal();
		if ( WC()->cart->display_prices_including_tax() ) {
			// get_displayed_subtotal already includes tax in that mode.
			$current = (float) WC()->cart->get_displayed_subtotal();
		}
		$remaining = max( 0, $threshold - $current );
		$percent   = $threshold > 0 ? min( 100, round( $current / $threshold * 100 ) ) : 0;

		if ( $remaining <= 0 ) {
			$message = '<span class="upw-freeship__msg is-unlocked">' . esc_html__( 'You’ve unlocked free shipping!', 'fw' ) . '</span>';
		} else {
			$message = '<span class="upw-freeship__msg">' . sprintf(
				/* translators: %s: formatted price remaining */
				esc_html__( 'Spend %s more for free shipping', 'fw' ),
				wp_kses_post( wc_price( $remaining ) )
			) . '</span>';
		}

		return '<div class="upw-freeship__inner" data-percent="' . (int) $percent . '">'
			. $message
			. '<span class="upw-freeship__track"><span class="upw-freeship__fill" style="width:' . (int) $percent . '%"></span></span>'
			. '</div>';
	}
}

if ( ! function_exists( 'upw_wc_category_choices' ) ) {
	/**
	 * Product-category choices: slug => Name (with "All Categories" first).
	 */
	function upw_wc_category_choices() {
		$choices = array( '' => __( 'All Categories', 'fw' ) );

		if ( function_exists( 'taxonomy_exists' ) && taxonomy_exists( 'product_cat' ) ) {
			$terms = get_terms(
				array(
					'taxonomy'   => 'product_cat',
					'hide_empty' => false,
				)
			);
			if ( ! is_wp_error( $terms ) ) {
				foreach ( $terms as $term ) {
					$choices[ $term->slug ] = $term->name;
				}
			}
		}

		return $choices;
	}
}

<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
/**
 * Shared helpers for the WooCommerce extension's elements.
 *
 * Auto-loaded by Unyson (extensions include their own /helpers.php). Guarded so
 * everything is safe to call whether or not WooCommerce is active.
 */

if ( ! function_exists( 'upwc_wc_switch' ) ) {
	/**
	 * A predictable yes/no switch option (stored value is always 'yes' or 'no').
	 */
	function upwc_wc_switch( $label, $desc = '', $value = 'no' ) {
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

if ( ! function_exists( 'upwc_wc_truthy' ) ) {
	/**
	 * Normalize a stored switch/att value to a boolean.
	 */
	function upwc_wc_truthy( $v ) {
		return $v === true || $v === 'yes' || $v === '1' || $v === 1 || $v === 'true';
	}
}

if ( ! function_exists( 'upwc_wc_product_choices' ) ) {
	/**
	 * Published-product choices for a select: id => "Title (#id)".
	 * Empty (just a placeholder) when WooCommerce is inactive.
	 *
	 * @param int $limit Max products listed (keeps the select manageable).
	 * @return array
	 */
	function upwc_wc_product_choices( $limit = 200 ) {
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

if ( ! function_exists( 'upwc_wc_enqueue_core_styles' ) ) {
	/**
	 * Ensure WooCommerce's own frontend stylesheets are loaded. WooCommerce
	 * only auto-enqueues them on shop pages, but our wrapper elements (which
	 * emit WC shortcodes like [product_categories], [product], [add_to_cart])
	 * can appear on ANY builder page — so its shortcode output stays styled.
	 */
	function upwc_wc_enqueue_core_styles() {
		foreach ( array( 'woocommerce-layout', 'woocommerce-smallscreen', 'woocommerce-general' ) as $handle ) {
			if ( wp_style_is( $handle, 'registered' ) && ! wp_style_is( $handle, 'enqueued' ) ) {
				wp_enqueue_style( $handle );
			}
		}
	}
}

if ( ! function_exists( 'upwc_wc_free_shipping_threshold' ) ) {
	/**
	 * The free-shipping spend threshold for the current cart's shipping zone.
	 * 0 when no free-shipping method requires a minimum. Filterable.
	 *
	 * @return float
	 */
	function upwc_wc_free_shipping_threshold() {
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

		return (float) apply_filters( 'upwc_wc_free_shipping_threshold', $threshold );
	}
}

if ( ! function_exists( 'upwc_wc_free_shipping_bar_html' ) ) {
	/**
	 * Inner HTML of the free-shipping progress bar (message + track).
	 * Shared by the element view and the cart-fragment refresh.
	 *
	 * @return string Empty when there is no free-shipping threshold.
	 */
	function upwc_wc_free_shipping_bar_html() {
		$threshold = upwc_wc_free_shipping_threshold();
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
			$message = '<span class="upwc-freeship__msg is-unlocked">' . esc_html__( 'You’ve unlocked free shipping!', 'fw' ) . '</span>';
		} else {
			$message = '<span class="upwc-freeship__msg">' . sprintf(
				/* translators: %s: formatted price remaining */
				esc_html__( 'Spend %s more for free shipping', 'fw' ),
				wp_kses_post( wc_price( $remaining ) )
			) . '</span>';
		}

		return '<div class="upwc-freeship__inner" data-percent="' . (int) $percent . '">'
			. $message
			. '<span class="upwc-freeship__track"><span class="upwc-freeship__fill" style="width:' . (int) $percent . '%"></span></span>'
			. '</div>';
	}
}

if ( ! function_exists( 'upwc_wc_category_choices' ) ) {
	/**
	 * Product-category choices: slug => Name (with "All Categories" first).
	 */
	function upwc_wc_category_choices() {
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

/*
|--------------------------------------------------------------------------
| Shared product-CARD option groups (Card tab)
|--------------------------------------------------------------------------
| The Card tab — Card Rows designer + live preview, Card Box Style, Image
| Ratio/Size, Rating style, Badges, and the Add-to-Cart label — is IDENTICAL
| for the [wc_products] grid and the [wc_product] single element. It lives here
| so both shortcodes render the same card engine (upwc_wc_products_card) with
| the same controls and can never drift. Option ids are unchanged from the
| original wc_products layout, so saved instances keep rendering.
*/

if ( ! function_exists( 'upwc_products_switch' ) ) {
	/**
	 * A predictable yes/no switch whose default is ON (used by the Badge toggles).
	 * (Distinct from upwc_wc_switch, whose default is OFF.)
	 */
	function upwc_products_switch( $label, $desc = '', $value = 'yes' ) {
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

if ( ! function_exists( 'upwc_wc_card_layout_img_url' ) ) {
	/**
	 * URL of the shared Card Rows swatch icons (Direction / Distribute / Align).
	 * The assets live under wc_products; both shortcodes point here.
	 */
	function upwc_wc_card_layout_img_url() {
		$ext = function_exists( 'fw_ext' ) ? fw_ext( 'woocommerce' ) : null;
		if ( $ext ) {
			return $ext->get_declared_URI( '/shortcodes/wc_products/static/img/layout' );
		}
		return plugins_url( 'shortcodes/wc_products/static/img/layout', dirname( __FILE__ ) . '/woocommerce.php' );
	}
}

if ( ! function_exists( 'upwc_wc_card_option_groups' ) ) {
	/**
	 * The Card-tab option groups shared by [wc_products] and [wc_product].
	 *
	 * @return array group_layout + group_rating + group_badges + group_cart.
	 */
	function upwc_wc_card_option_groups() {
		$img    = upwc_wc_card_layout_img_url();
		$swatch = function ( $file, $title ) use ( $img ) {
			return array( 'small' => array( 'src' => $img . '/' . $file, 'height' => 34, 'title' => $title ) );
		};

		return array(
			// THE CARD LAYOUT — the row designer is the single card model. Add / drag rows and pick each
			// row's slots; a slot renders when it's in a row and the product has that data (remove the
			// slot to hide it). There are deliberately NO separate show/hide switches — the rows ARE what's
			// on the card and where.
			'group_layout' => array(
				'type'    => 'group',
				'options' => array(
					// LIVE PREVIEW — a schematic wireframe of the card that redraws as the rows /
					// slots below change (pure JS in the builder modal; card-preview.js).
					'card_preview' => array(
						'type'  => 'html-full',
						'label' => false,
						'html'  => '<div class="upwc-cp-wrap">'
							. '<div class="upwc-cp-heading">' . esc_html__( 'Card preview', 'fw' ) . '</div>'
							. '<div class="upwc-cp-mount" data-upwc-card-preview>'
							. '<div class="upwc-cp-card"><div class="upwc-cp-emptycard">' . esc_html__( 'Loading preview…', 'fw' ) . '</div></div>'
							. '</div>'
							. '<div class="upwc-cp-note">' . esc_html__( 'Schematic only — a wireframe of the layout, not real product data.', 'fw' ) . '</div>'
							. '</div>',
					),
					// The card DESIGNER: an addable list of ROWS (add / remove / drag to reorder).
					'card_rows' => array(
						'type'          => 'addable-popup',
						'label'         => __( 'Card Rows', 'fw' ),
						'desc'          => __( 'The card layout. Add / drag rows; in each row pick & order the slots (image, title, price, rating, badges, wishlist, cart…) and set inline/stacked + alignment. A slot appears only when it\'s in a row and the product has that data — remove a slot to hide it. The visual skin (border, corners, shadow, fill + hover) comes from the Card Box Style preset below.', 'fw' ),
						'popup-title'   => __( 'Row', 'fw' ),
						'template'      => '{{= (slots && slots.length ? slots.join(", ") : "Row") }}',
						'popup-options' => array(
							'slots' => array(
								'type'       => 'multi-select',
								'label'      => __( 'Slots', 'fw' ),
								'desc'       => __( 'Pick and order the elements in this row.', 'fw' ),
								'population'  => 'array',
								'choices'    => array(
									'badges'       => __( 'Badge / Ribbon', 'fw' ),
									'wishlist'     => __( 'Wishlist Heart', 'fw' ),
									'media'        => __( 'Image', 'fw' ),
									'title'        => __( 'Title', 'fw' ),
									'excerpt'      => __( 'Description', 'fw' ),
									'rating'       => __( 'Star Rating', 'fw' ),
									'rating_count' => __( 'Rating Number', 'fw' ),
									'price'        => __( 'Price', 'fw' ),
									'cart'         => __( 'Add to Cart', 'fw' ),
									'quickview'    => __( 'Quick View', 'fw' ),
								),
								'value'      => array(),
							),
							'direction' => array(
								'type'    => 'image-picker',
								'label'   => __( 'Direction', 'fw' ),
								'value'   => 'inline',
								'choices' => array(
									'inline' => $swatch( 'dir-inline.svg', __( 'Inline (side by side)', 'fw' ) ),
									'stack'  => $swatch( 'dir-stack.svg',  __( 'Stacked (own lines)', 'fw' ) ),
								),
							),
							'justify' => array(
								'type'    => 'image-picker',
								'label'   => __( 'Distribute', 'fw' ),
								'value'   => 'start',
								'choices' => array(
									'start'   => $swatch( 'just-start.svg',   __( 'Start', 'fw' ) ),
									'center'  => $swatch( 'just-center.svg',  __( 'Center', 'fw' ) ),
									'between' => $swatch( 'just-between.svg', __( 'Space between', 'fw' ) ),
									'end'     => $swatch( 'just-end.svg',     __( 'End', 'fw' ) ),
								),
							),
							'align' => array(
								'type'    => 'image-picker',
								'label'   => __( 'Align', 'fw' ),
								'value'   => 'center',
								'choices' => array(
									'start'   => $swatch( 'align-start.svg',   __( 'Start', 'fw' ) ),
									'center'  => $swatch( 'align-center.svg',  __( 'Center', 'fw' ) ),
									'end'     => $swatch( 'align-end.svg',     __( 'End', 'fw' ) ),
									'stretch' => $swatch( 'align-stretch.svg', __( 'Stretch', 'fw' ) ),
								),
							),
						),
						'value' => array(
							array( 'slots' => array( 'badges', 'wishlist' ),        'direction' => 'inline', 'justify' => 'between', 'align' => 'center' ),
							array( 'slots' => array( 'media', 'title', 'excerpt' ), 'direction' => 'stack',  'justify' => 'start',   'align' => 'center' ),
							array( 'slots' => array( 'rating', 'rating_count' ),    'direction' => 'inline', 'justify' => 'center',  'align' => 'center' ),
							array( 'slots' => array( 'price', 'cart' ),             'direction' => 'inline', 'justify' => 'between', 'align' => 'center' ),
						),
					),
					// Card BOX PRESET — the native, on-brand way for each card to inherit its skin.
					'box_style' => function_exists( 'sc_card_box_style_field' )
						? sc_card_box_style_field( array(
							'label' => __( 'Card Box Style', 'fw' ),
							'desc'  => __( 'Apply a reusable Box Preset (border, corners, shadow, fill + hover effects) to every product card — the native way to skin the card. Manage presets in Theme Settings → Components → Box Presets.', 'fw' ),
						) )
						: array( 'type' => 'text', 'label' => __( 'Card Box Style', 'fw' ), 'value' => '', 'desc' => __( 'Box Presets are unavailable (styling helper not loaded).', 'fw' ) ),
					'image_ratio' => array(
						'type'    => 'select',
						'label'   => __( 'Image Ratio', 'fw' ),
						'desc'    => __( 'Crop product images to a uniform aspect ratio, or keep their natural proportions. (Owns the image SHAPE.)', 'fw' ),
						'choices' => array(
							'auto'      => __( 'Natural', 'fw' ),
							'square'    => __( 'Square (1:1)', 'fw' ),
							'portrait'  => __( 'Portrait (3:4)', 'fw' ),
							'landscape' => __( 'Landscape (4:3)', 'fw' ),
						),
						'value'   => 'auto',
					),
					// Image SCALE. Empty = auto (fills the column).
					'image_size'  => array(
						'type'  => 'unit-input',
						'label' => __( 'Image Size', 'fw' ),
						'desc'  => __( 'Width of the product image. Empty = auto (fills the column). Pairs with Image Ratio (shape) — this sets the scale.', 'fw' ),
						'value' => array( 'value' => '', 'unit' => 'rem' ),
						'units' => array( 'px', 'rem', '%', 'vw', 'em' ),
					),
				),
			),
			// Rating star styling — shared sc_rating_style_field (symbol / colors / size).
			'group_rating' => array(
				'type'    => 'group',
				'options' => function_exists( 'sc_rating_style_field' ) ? sc_rating_style_field() : array(),
			),
			// Which BADGES appear in the "badges" slot.
			'group_badges' => array(
				'type'    => 'group',
				'options' => array(
					'show_ribbon'         => upwc_products_switch( __( 'Ribbon Badge', 'fw' ), __( 'Show a per-product ribbon (e.g. "Best Seller") from the product meta "_upwc_ribbon". Appears in the "badges" slot.', 'fw' ), 'no' ),
					'show_sale_badge'     => upwc_products_switch( __( 'Sale Badge', 'fw' ), __( 'Show a "Sale" badge on discounted products.', 'fw' ) ),
					'badge_style'         => array(
						'type'    => 'select',
						'label'   => __( 'Sale Badge Style', 'fw' ),
						'desc'    => __( 'Text shows "Sale"; Percent shows the discount (e.g. -25%).', 'fw' ),
						'choices' => array(
							'text'    => __( 'Text ("Sale")', 'fw' ),
							'percent' => __( 'Percent ("-25%")', 'fw' ),
						),
						'value'   => 'text',
					),
					'show_featured_badge' => upwc_products_switch( __( 'Featured Badge', 'fw' ), __( 'Show a "Featured" badge on featured products.', 'fw' ), 'no' ),
					'show_new_badge'      => upwc_products_switch( __( 'New Badge', 'fw' ), __( 'Show a "New" badge on recently published products.', 'fw' ), 'no' ),
					'new_days'            => array(
						'type'            => 'text',
						'label'           => __( 'New For (days)', 'fw' ),
						'desc'            => __( 'A product counts as "New" for this many days after publishing.', 'fw' ),
						'value'           => '14',
						'dynamic_content' => false,
					),
				),
			),
			// The Add-to-Cart button label (the "cart" slot's text).
			'group_cart' => array(
				'type'    => 'group',
				'options' => array(
					'add_to_cart_text'  => array(
						'type'            => 'text',
						'label'           => __( 'Add to Cart Label', 'fw' ),
						'desc'            => __( 'Custom text for the add-to-cart button (e.g. "Add to Basket"). Leave empty for the WooCommerce default.', 'fw' ),
						'value'           => '',
						'dynamic_content' => false,
					),
				),
			),
		);
	}
}

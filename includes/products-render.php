<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
/**
 * Shared query + card rendering for the [wc_products] element.
 *
 * Auto-loaded (extensions include their /includes directory). Used by the
 * element's view.php for the initial render AND by the Load More / Quick View
 * AJAX handlers, so the markup and query stay identical everywhere.
 */

if ( ! function_exists( 'upwc_wc_products_resolve' ) ) {
	/**
	 * Normalize raw element atts into a resolved options array.
	 *
	 * @param array $atts
	 * @return array
	 */
	function upwc_wc_products_resolve( $atts ) {
		$atts   = is_array( $atts ) ? $atts : array();
		$truthy = static function ( $v ) {
			return $v === true || $v === 'yes' || $v === '1' || $v === 1 || $v === 'true';
		};
		$on  = static function ( $k ) use ( $atts, $truthy ) {
			return ! array_key_exists( $k, $atts ) ? true : $truthy( $atts[ $k ] );
		};
		$opt = static function ( $k ) use ( $atts, $truthy ) {
			return isset( $atts[ $k ] ) ? $truthy( $atts[ $k ] ) : false;
		};

		return array(
			'source'          => isset( $atts['source'] ) ? (string) $atts['source'] : 'recent',
			'category'        => isset( $atts['category'] ) ? (string) $atts['category'] : '',
			'per_page'        => isset( $atts['posts_per_page'] ) ? (int) $atts['posts_per_page'] : 8,
			'orderby'         => isset( $atts['orderby'] ) ? (string) $atts['orderby'] : 'date',
			'order'           => ( isset( $atts['order'] ) && strtoupper( (string) $atts['order'] ) === 'ASC' ) ? 'ASC' : 'DESC',
			'columns'         => isset( $atts['columns'] ) ? max( 1, (int) $atts['columns'] ) : 4,
			'gap'             => isset( $atts['gap'] ) ? preg_replace( '/[^a-z]/', '', (string) $atts['gap'] ) : 'md',
			'image_ratio'     => isset( $atts['image_ratio'] ) ? preg_replace( '/[^a-z]/', '', (string) $atts['image_ratio'] ) : 'auto',
			// Image Size (width). Empty = auto. A unit-input { value, unit } → a CSS length string,
			// emitted as the --upwc-img-size custom property on the grid wrapper (view.php).
			'image_size'      => ( static function ( $v ) {
				if ( ! is_array( $v ) || ! isset( $v['value'] ) || '' === (string) $v['value'] || null === $v['value'] ) {
					return '';
				}
				$unit = isset( $v['unit'] ) && preg_match( '/^[a-z%]+$/', $v['unit'] ) ? $v['unit'] : 'px';
				return (float) $v['value'] . $unit;
			} )( isset( $atts['image_size'] ) ? $atts['image_size'] : null ),
			'alignment'       => isset( $atts['alignment'] ) ? preg_replace( '/[^a-z]/', '', (string) $atts['alignment'] ) : '',
			'layout'          => ( isset( $atts['layout'] ) && $atts['layout'] === 'carousel' ) ? 'carousel' : 'grid',
			'show_arrows'     => ! array_key_exists( 'carousel_arrows', $atts ) ? true : $truthy( $atts['carousel_arrows'] ),
			'pagination'      => ( isset( $atts['pagination'] ) && $atts['pagination'] === 'load_more' ) ? 'load_more' : 'none',
			'tags'            => isset( $atts['tags'] ) ? (string) $atts['tags'] : '',
			'attribute'       => isset( $atts['attribute'] ) ? (string) $atts['attribute'] : '',
			'attribute_terms' => isset( $atts['attribute_terms'] ) ? (string) $atts['attribute_terms'] : '',
			'product_ids'     => isset( $atts['product_ids'] ) ? (string) $atts['product_ids'] : '',
			'show_badge'      => $on( 'show_sale_badge' ),
			// Per-element PRESENCE toggles removed 2026-08-01: the Card Rows designer is the single
			// control for what's on the card (a slot renders when it's in a row AND has data — remove
			// the slot to hide it). These stay TRUE so a slot placed in a row always renders its content.
			// (show_stock dropped entirely — it only fed the removed Classic markup; there is no stock slot.)
			'show_rating'       => true,
			'show_price'        => true,
			'show_atc'          => true,
			'quick_view'        => true,
			'show_excerpt'      => true,
			'show_wishlist'     => true,
			'show_compare'      => true,
			'show_rating_count' => true,
			'badge_style'     => ( isset( $atts['badge_style'] ) && $atts['badge_style'] === 'percent' ) ? 'percent' : 'text',
			'show_featured'   => $opt( 'show_featured_badge' ),
			'show_new'        => $opt( 'show_new_badge' ),
			'new_days'        => isset( $atts['new_days'] ) ? max( 0, (int) $atts['new_days'] ) : 14,
			'show_ribbon'     => $opt( 'show_ribbon' ),
			// card_rows: the editable row designer (addable-popup) — a list of { slots[], direction,
			// justify, align }. Empty falls back to a preset in the renderer.
			'card_rows'       => ( isset( $atts['card_rows'] ) && is_array( $atts['card_rows'] ) ) ? array_values( $atts['card_rows'] ) : array(),
			// Rating style (shared sc_rating_style_field): symbol + colors + size.
			'rating_symbol'      => isset( $atts['rating_symbol'] ) ? $atts['rating_symbol'] : 'star',
			'rating_fill_color'  => isset( $atts['rating_fill_color'] ) ? $atts['rating_fill_color'] : '',
			'rating_empty_color' => isset( $atts['rating_empty_color'] ) ? $atts['rating_empty_color'] : '',
			'rating_size'        => isset( $atts['rating_size'] ) ? $atts['rating_size'] : 'md',
			'add_to_cart_text' => isset( $atts['add_to_cart_text'] ) ? (string) $atts['add_to_cart_text'] : '',
			// Box Preset class (boxp-{slug}) so each card inherits a reusable Box Preset skin
			// (border/corners/shadow/fill + hover) — the native alternative to hand-CSS.
			'box_class'       => function_exists( 'sc_card_box_style_class' ) ? sc_card_box_style_class( $atts ) : '',
		);
	}
}

if ( ! function_exists( 'upwc_wc_products_query_args' ) ) {
	/**
	 * Build WP_Query args from resolved options.
	 *
	 * @param array $r     Resolved options (from upwc_wc_products_resolve()).
	 * @param int   $paged Page number (Load More).
	 * @return array|false WP_Query args, or false when the source yields nothing.
	 */
	function upwc_wc_products_query_args( $r, $paged = 1 ) {
		$args = array(
			'post_type'           => 'product',
			'post_status'         => 'publish',
			'posts_per_page'      => $r['per_page'],
			'ignore_sticky_posts' => true,
			'order'               => $r['order'],
		);

		if ( $r['pagination'] === 'load_more' ) {
			$args['paged']         = max( 1, (int) $paged );
			$args['no_found_rows'] = false;
		} else {
			$args['no_found_rows'] = true;
		}

		switch ( $r['orderby'] ) {
			case 'price':
				$args['orderby']  = 'meta_value_num';
				$args['meta_key'] = '_price'; // phpcs:ignore
				break;
			case 'popularity':
				$args['orderby']  = 'meta_value_num';
				$args['meta_key'] = 'total_sales'; // phpcs:ignore
				break;
			case 'rating':
				$args['orderby']  = 'meta_value_num';
				$args['meta_key'] = '_wc_average_rating'; // phpcs:ignore
				break;
			case 'title':
				$args['orderby'] = 'title';
				break;
			case 'menu_order':
				$args['orderby'] = 'menu_order title';
				break;
			case 'rand':
				$args['orderby'] = 'rand';
				break;
			default:
				$args['orderby'] = 'date';
				break;
		}

		$tax_query = array(
			array(
				'taxonomy' => 'product_visibility',
				'field'    => 'name',
				'terms'    => array( 'exclude-from-catalog' ),
				'operator' => 'NOT IN',
			),
		);

		switch ( $r['source'] ) {
			case 'featured':
				$tax_query[] = array(
					'taxonomy' => 'product_visibility',
					'field'    => 'name',
					'terms'    => array( 'featured' ),
					'operator' => 'IN',
				);
				break;
			case 'sale':
				$sale_ids = function_exists( 'wc_get_product_ids_on_sale' ) ? wc_get_product_ids_on_sale() : array();
				$sale_ids = array_filter( array_map( 'intval', (array) $sale_ids ) );
				if ( empty( $sale_ids ) ) {
					return false;
				}
				$args['post__in'] = $sale_ids;
				break;
			case 'best_selling':
				$args['orderby']  = 'meta_value_num';
				$args['meta_key'] = 'total_sales'; // phpcs:ignore
				$args['order']    = 'DESC';
				break;
			case 'top_rated':
				$args['orderby']  = 'meta_value_num';
				$args['meta_key'] = '_wc_average_rating'; // phpcs:ignore
				$args['order']    = 'DESC';
				break;
			case 'tag':
				$tags = array_filter( array_map( 'sanitize_title', array_map( 'trim', explode( ',', $r['tags'] ) ) ) );
				if ( ! empty( $tags ) ) {
					$tax_query[] = array(
						'taxonomy' => 'product_tag',
						'field'    => 'slug',
						'terms'    => $tags,
					);
				}
				break;
			case 'attribute':
				$attr = sanitize_title( preg_replace( '/^pa_/', '', $r['attribute'] ) );
				if ( $attr !== '' ) {
					$terms = array_filter( array_map( 'sanitize_title', array_map( 'trim', explode( ',', $r['attribute_terms'] ) ) ) );
					$tq    = array( 'taxonomy' => 'pa_' . $attr, 'field' => 'slug' );
					if ( ! empty( $terms ) ) {
						$tq['terms'] = $terms;
					} else {
						$tq['operator'] = 'EXISTS';
					}
					$tax_query[] = $tq;
				}
				break;
			case 'ids':
				$id_list = array_filter( array_map( 'intval', array_map( 'trim', explode( ',', $r['product_ids'] ) ) ) );
				if ( empty( $id_list ) ) {
					return false;
				}
				$args['post__in']       = $id_list;
				$args['orderby']        = 'post__in';
				$args['posts_per_page'] = count( $id_list );
				break;
			case 'recently_viewed':
				$viewed = empty( $_COOKIE['woocommerce_recently_viewed'] )
					? array()
					: array_filter( array_map( 'absint', explode( '|', wp_unslash( $_COOKIE['woocommerce_recently_viewed'] ) ) ) ); // phpcs:ignore
				$viewed = array_reverse( $viewed );
				if ( empty( $viewed ) ) {
					return false;
				}
				$args['post__in'] = $viewed;
				$args['orderby']  = 'post__in';
				break;
			case 'cross_sells':
				$cross = ( function_exists( 'WC' ) && WC()->cart ) ? WC()->cart->get_cross_sells() : array();
				$cross = array_filter( array_map( 'intval', (array) $cross ) );
				if ( empty( $cross ) ) {
					return false;
				}
				$args['post__in'] = $cross;
				$args['orderby']  = 'post__in';
				break;
		}

		if ( $r['category'] !== '' ) {
			$tax_query[] = array(
				'taxonomy' => 'product_cat',
				'field'    => 'slug',
				'terms'    => array( $r['category'] ),
			);
		}

		$args['tax_query'] = $tax_query; // phpcs:ignore
		return $args;
	}
}

if ( ! function_exists( 'upwc_wc_products_card' ) ) {
	/**
	 * Render one product card. Call inside the loop (sets the global product).
	 *
	 * @param WC_Product $product
	 * @param array      $r Resolved options.
	 * @return string
	 */
	function upwc_wc_products_card( $product, $r ) {
		if ( ! $product instanceof WC_Product ) {
			return '';
		}
		$GLOBALS['product'] = $product;

		// The card is ALWAYS assembled from the Card Rows designer — the row system is the single card
		// model. (The former Classic/Slot "Card Layout" toggle was removed 2026-08-01: it had never been
		// used, and the rows express the same structure.) An empty designer falls back to a default
		// preset in the renderer (upwc_wc_products_card_slotted).
		return upwc_wc_products_card_slotted( $product, $r );
	}
}

if ( ! function_exists( 'upwc_wc_products_card_presets' ) ) {
	/**
	 * Card-layout presets: an ordered list of ROWS, each row a set of SLOTS with a flex
	 * direction + main-axis (justify) + cross-axis (align). This is the schema a slot-based
	 * card is assembled from — and the shape the Site Converter can emit from a captured card
	 * (structure only; the visual skin stays in CSS/tokens). Known slots: badges, wishlist,
	 * media, title, excerpt, rating, rating_count, price, cart, quickview.
	 */
	function upwc_wc_products_card_presets( $key ) {
		$p = array(
			// Classic: header row (badge left / wishlist right), stacked media+title+desc,
			// left-aligned rating, price split from the cart button.
			'classic' => array(
				array( 'slots' => array( 'badges', 'wishlist' ),        'dir' => 'inline', 'justify' => 'between', 'align' => 'center' ),
				array( 'slots' => array( 'media', 'title', 'excerpt' ), 'dir' => 'stack',  'justify' => 'start',   'align' => 'start' ),
				array( 'slots' => array( 'rating', 'rating_count' ),    'dir' => 'inline', 'justify' => 'start',   'align' => 'center' ),
				array( 'slots' => array( 'price', 'cart' ),             'dir' => 'inline', 'justify' => 'between', 'align' => 'center' ),
			),
			// Split-header: the pinky-bites pattern — centred content, centred rating.
			'split-header' => array(
				array( 'slots' => array( 'badges', 'wishlist' ),        'dir' => 'inline', 'justify' => 'between', 'align' => 'center' ),
				array( 'slots' => array( 'media', 'title', 'excerpt' ), 'dir' => 'stack',  'justify' => 'start',   'align' => 'center' ),
				array( 'slots' => array( 'rating', 'rating_count' ),    'dir' => 'inline', 'justify' => 'center',  'align' => 'center' ),
				array( 'slots' => array( 'price', 'cart' ),             'dir' => 'inline', 'justify' => 'between', 'align' => 'center' ),
			),
		);
		return isset( $p[ $key ] ) ? $p[ $key ] : $p['classic'];
	}
}

if ( ! function_exists( 'upwc_wc_products_stars' ) ) {
	/**
	 * Clean star rating — our own markup, NOT wc_get_rating_html(), so there is no
	 * "Rated X out of 5" screen-reader text node leaking into the visible card (a11y is
	 * preserved via aria-label on the element, which is not a text node). A gold fill span
	 * is clipped to avg/5 over an empty 5-star track (styled in styles.css).
	 */
	function upwc_wc_products_stars( $product, $r = array() ) {
		$avg = (float) $product->get_average_rating();
		if ( $avg <= 0 ) {
			return '';
		}

		// Render through the shared rating engine (symbol + colors + size come from the
		// element's Rating options; the two-tone SVG + fractional overlay live there).
		if ( function_exists( 'sc_rating_stars' ) ) {
			$args = function_exists( 'sc_rating_style_from_atts' ) ? sc_rating_style_from_atts( $r, 'rating_' ) : array();
			$args['label'] = sprintf( __( 'Rated %s out of 5', 'fw' ), $avg );
			return sc_rating_stars( $avg, $args );
		}

		// Fallback: a plain gold/grey glyph track if the shared engine isn't loaded.
		$pct = max( 0, min( 100, $avg / 5 * 100 ) );
		return '<span class="upwc-product__stars" role="img" aria-label="'
			. esc_attr( sprintf( __( 'Rated %s out of 5', 'fw' ), $avg ) ) . '"><span class="upwc-product__stars-fill" style="width:'
			. $pct . '%"></span></span>';
	}
}

if ( ! function_exists( 'upwc_wc_products_card_slotted' ) ) {
	/**
	 * Slot-based card renderer. Builds each slot's HTML, then assembles the rows defined by
	 * the resolved card_layout preset. Empty slots (and empty rows) are skipped, so a card
	 * with no rating/excerpt collapses cleanly.
	 */
	function upwc_wc_products_card_slotted( $product, $r ) {
		$id   = $product->get_id();
		$href = esc_url( $product->get_permalink() );

		// --- badges (sale / featured / new / out-of-stock / ribbon) ---
		$badges = array();
		if ( $r['show_badge'] && $product->is_on_sale() ) {
			$label = esc_html__( 'Sale', 'fw' );
			if ( 'percent' === $r['badge_style'] ) {
				$regular = (float) $product->get_regular_price();
				$sale    = (float) $product->get_sale_price();
				if ( $regular > 0 && $sale > 0 && $sale < $regular ) {
					$label = '-' . (int) round( ( $regular - $sale ) / $regular * 100 ) . '%';
				}
			}
			$badges[] = '<span class="upwc-product__badge onsale">' . $label . '</span>';
		}
		if ( $r['show_featured'] && $product->is_featured() ) {
			$badges[] = '<span class="upwc-product__badge featured">' . esc_html__( 'Featured', 'fw' ) . '</span>';
		}
		if ( $r['show_new'] && $r['new_days'] > 0 ) {
			$created = $product->get_date_created();
			if ( $created && $created->getTimestamp() > ( time() - $r['new_days'] * DAY_IN_SECONDS ) ) {
				$badges[] = '<span class="upwc-product__badge is-new">' . esc_html__( 'New', 'fw' ) . '</span>';
			}
		}
		if ( $r['show_ribbon'] ) {
			$ribbon = get_post_meta( $id, '_upwc_ribbon', true );
			if ( '' !== (string) $ribbon ) {
				$badges[] = '<span class="upwc-product__badge ribbon">' . esc_html( $ribbon ) . '</span>';
			}
		}

		$slots = array();
		$slots['badges']   = $badges ? '<span class="upwc-product__badges">' . implode( '', $badges ) . '</span>' : '';
		// A real, clickable heart when the wishlist is on — the slot used to render
		// a decorative aria-hidden span, i.e. a control that promised something the
		// store could not do. It renders nothing at all when the feature is off,
		// rather than a heart that does nothing.
		$slots['wishlist'] = ( $r['show_wishlist'] && function_exists( 'upwc_wishlist_button_html' ) )
			? '<span class="upwc-product__wishlist">' . upwc_wishlist_button_html( $id ) . '</span>'
			: '';

		$slots['compare'] = ( $r['show_compare'] && function_exists( 'upwc_compare_button_html' ) )
			? '<span class="upwc-product__compare">' . upwc_compare_button_html( $id ) . '</span>'
			: '';

		$slots['swatches'] = function_exists( 'upwc_swatches_card_html' )
			? upwc_swatches_card_html( $product )
			: '';
		$slots['media']    = '<a class="upwc-product__link" href="' . $href . '"><span class="upwc-product__media">' . $product->get_image( 'woocommerce_thumbnail' ) . '</span></a>';
		$slots['title']    = '<a class="upwc-product__titlelink" href="' . $href . '"><span class="upwc-product__title">' . esc_html( $product->get_name() ) . '</span></a>';

		$slots['excerpt'] = '';
		if ( $r['show_excerpt'] ) {
			$ex = $product->get_short_description();
			if ( '' !== (string) $ex ) {
				$slots['excerpt'] = '<div class="upwc-product__excerpt">' . wp_kses_post( wpautop( $ex ) ) . '</div>';
			}
		}

		$slots['rating']       = $r['show_rating'] ? upwc_wc_products_stars( $product, $r ) : '';
		$slots['rating_count'] = '';
		if ( $r['show_rating_count'] ) {
			// The number beside the stars = the average rating (e.g. 4.9, or 5), not the review
			// count — that's what product cards conventionally show next to the stars.
			$avg = (float) $product->get_average_rating();
			if ( $avg > 0 ) {
				$num = rtrim( rtrim( number_format( $avg, 1 ), '0' ), '.' ); // 4.9 → "4.9", 5.0 → "5"
				$slots['rating_count'] = '<span class="upwc-product__rating-count">' . esc_html( $num ) . '</span>';
			}
		}

		// Catalog Mode reaches our cards too: it works by unhooking WooCommerce's
		// price / add-to-cart templates, which these slots don't go through — so
		// without this a "lookbook" would still be selling from every grid.
		$catalog_mode = function_exists( 'upwc_wc_catalog_mode' ) && upwc_wc_catalog_mode();

		$slots['price'] = '';
		if ( $r['show_price'] && ! $catalog_mode ) {
			$price_html = $product->get_price_html();
			if ( $price_html ) {
				$slots['price'] = '<div class="upwc-product__price">' . $price_html . '</div>';
			}
		}

		$slots['quickview'] = $r['quick_view']
			? '<button type="button" class="upwc-product__quickview" data-product="' . (int) $id . '">' . esc_html__( 'Quick View', 'fw' ) . '</button>'
			: '';

		$slots['cart'] = '';
		if ( $catalog_mode ) {
			// The enquiry button takes the cart slot when Catalog Mode is on and one
			// is configured; otherwise the slot simply stays empty.
			$enquiry = function_exists( 'upwc_wc_enquiry_html' ) ? upwc_wc_enquiry_html( $product ) : '';
			if ( '' !== $enquiry ) {
				$slots['cart'] = '<div class="upwc-product__cart">' . $enquiry . '</div>';
			}
		} elseif ( $r['show_atc'] && function_exists( 'woocommerce_template_loop_add_to_cart' ) ) {
			$atc_filter = null;
			if ( '' !== $r['add_to_cart_text'] ) {
				$atc_label  = $r['add_to_cart_text'];
				$atc_filter = static function () use ( $atc_label ) { return $atc_label; };
				add_filter( 'woocommerce_product_add_to_cart_text', $atc_filter, 20 );
			}
			ob_start();
			woocommerce_template_loop_add_to_cart();
			$cart_inner = ob_get_clean();
			if ( $atc_filter ) {
				remove_filter( 'woocommerce_product_add_to_cart_text', $atc_filter, 20 );
			}
			// Under the catalog lockdown WooCommerce's loop button resolves to nothing —
			// don't leave an empty wrapper (and its card-row gap) behind.
			$slots['cart'] = '' === trim( (string) $cart_inner )
				? ''
				: '<div class="upwc-product__cart">' . $cart_inner . '</div>';
		}

		// --- assemble rows from the editable designer (card_rows), or a preset fallback ---
		$rows = array();
		foreach ( (array) $r['card_rows'] as $row ) {
			$row_slots = isset( $row['slots'] ) ? array_values( (array) $row['slots'] ) : array();
			if ( empty( $row_slots ) ) {
				continue;
			}
			$rows[] = array(
				'slots'   => $row_slots,
				'dir'     => isset( $row['direction'] ) ? $row['direction'] : 'inline',
				'justify' => isset( $row['justify'] ) ? $row['justify'] : 'start',
				'align'   => isset( $row['align'] ) ? $row['align'] : 'center',
			);
		}
		if ( empty( $rows ) ) {
			// No designer rows saved (empty designer) → use the default preset so the card still renders.
			$rows = upwc_wc_products_card_presets( 'classic' );
		}
		$out  = '<li class="product upwc-product upwc-product--slotted' . ( '' !== $r['box_class'] ? ' ' . esc_attr( $r['box_class'] ) : '' ) . '">';
		foreach ( $rows as $row ) {
			$cells = '';
			foreach ( $row['slots'] as $sk ) {
				if ( ! empty( $slots[ $sk ] ) ) {
					$cells .= $slots[ $sk ];
				}
			}
			if ( '' === $cells ) {
				continue; // no populated slot in this row → skip it entirely
			}
			$cls = 'upwc-product__row'
				. ' upwc-row--' . preg_replace( '/[^a-z]/', '', $row['dir'] )
				. ' upwc-j-' . preg_replace( '/[^a-z]/', '', $row['justify'] )
				. ' upwc-a-' . preg_replace( '/[^a-z]/', '', $row['align'] );
			$out .= '<div class="' . $cls . '">' . $cells . '</div>';
		}
		$out .= '</li>';
		return $out;
	}
}

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
			'alignment'       => isset( $atts['alignment'] ) ? preg_replace( '/[^a-z]/', '', (string) $atts['alignment'] ) : '',
			'layout'          => ( isset( $atts['layout'] ) && $atts['layout'] === 'carousel' ) ? 'carousel' : 'grid',
			'show_arrows'     => ! array_key_exists( 'carousel_arrows', $atts ) ? true : $truthy( $atts['carousel_arrows'] ),
			'pagination'      => ( isset( $atts['pagination'] ) && $atts['pagination'] === 'load_more' ) ? 'load_more' : 'none',
			'tags'            => isset( $atts['tags'] ) ? (string) $atts['tags'] : '',
			'attribute'       => isset( $atts['attribute'] ) ? (string) $atts['attribute'] : '',
			'attribute_terms' => isset( $atts['attribute_terms'] ) ? (string) $atts['attribute_terms'] : '',
			'product_ids'     => isset( $atts['product_ids'] ) ? (string) $atts['product_ids'] : '',
			'show_badge'      => $on( 'show_sale_badge' ),
			'show_rating'     => $on( 'show_rating' ),
			'show_price'      => $on( 'show_price' ),
			'show_atc'        => $on( 'show_add_to_cart' ),
			'badge_style'     => ( isset( $atts['badge_style'] ) && $atts['badge_style'] === 'percent' ) ? 'percent' : 'text',
			'show_featured'   => $opt( 'show_featured_badge' ),
			'show_new'        => $opt( 'show_new_badge' ),
			'new_days'        => isset( $atts['new_days'] ) ? max( 0, (int) $atts['new_days'] ) : 14,
			'show_stock'      => $opt( 'show_stock' ),
			'quick_view'      => $opt( 'show_quick_view' ),
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

		$out  = '<li class="product upwc-product">';
		$out .= '<a class="upwc-product__link" href="' . esc_url( $product->get_permalink() ) . '">';

		$badges = array();
		if ( $r['show_badge'] && $product->is_on_sale() ) {
			$label = esc_html__( 'Sale', 'fw' );
			if ( $r['badge_style'] === 'percent' ) {
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
		if ( $r['show_stock'] && ! $product->is_in_stock() ) {
			$badges[] = '<span class="upwc-product__badge out-of-stock">' . esc_html__( 'Out of stock', 'fw' ) . '</span>';
		}
		if ( ! empty( $badges ) ) {
			$out .= '<span class="upwc-product__badges">' . implode( '', $badges ) . '</span>';
		}

		$out .= '<span class="upwc-product__media">' . $product->get_image( 'woocommerce_thumbnail' ) . '</span>';
		$out .= '<span class="upwc-product__title">' . esc_html( $product->get_name() ) . '</span>';
		$out .= '</a>';

		if ( $r['quick_view'] ) {
			$out .= '<button type="button" class="upwc-product__quickview" data-product="' . (int) $product->get_id() . '">' . esc_html__( 'Quick View', 'fw' ) . '</button>';
		}

		if ( $r['show_rating'] ) {
			$avg = (float) $product->get_average_rating();
			if ( $avg > 0 ) {
				$out .= '<div class="upwc-product__rating">' . wc_get_rating_html( $avg ) . '</div>';
			}
		}

		if ( $r['show_price'] ) {
			$price_html = $product->get_price_html();
			if ( $price_html ) {
				$out .= '<div class="upwc-product__price">' . $price_html . '</div>';
			}
		}

		if ( $r['show_stock'] && $product->is_in_stock() && $product->managing_stock() ) {
			$qty = $product->get_stock_quantity();
			$low = $product->get_low_stock_amount();
			if ( $low === '' || $low === null ) {
				$low = (int) get_option( 'woocommerce_notify_low_stock_amount', 2 );
			}
			$low = max( 1, (int) $low );
			if ( $qty !== null && $qty > 0 && $qty <= $low ) {
				$out .= '<div class="upwc-product__stock low">' . sprintf( esc_html__( 'Only %d left', 'fw' ), (int) $qty ) . '</div>';
			}
		}

		if ( $r['show_atc'] && function_exists( 'woocommerce_template_loop_add_to_cart' ) ) {
			$out .= '<div class="upwc-product__cart">';
			ob_start();
			woocommerce_template_loop_add_to_cart();
			$out .= ob_get_clean();
			$out .= '</div>';
		}

		$out .= '</li>';
		return $out;
	}
}

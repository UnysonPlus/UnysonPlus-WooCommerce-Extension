<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
/**
 * Products element — frontend render.
 *
 * In scope: $atts (parsed + decoded shortcode atts), $content.
 * Renders a WooCommerce product grid using product methods directly (clean,
 * self-contained markup) while keeping the native add-to-cart behavior.
 *
 * Inert when WooCommerce is inactive — the element can exist in saved content
 * even after the plugin is switched off, so we bail rather than fatal.
 */

if ( ! class_exists( 'WooCommerce' ) || ! function_exists( 'wc_get_product' ) ) {
	return;
}

/* ---- Resolve atts -------------------------------------------------------- */
$source      = isset( $atts['source'] ) ? (string) $atts['source'] : 'recent';
$category    = isset( $atts['category'] ) ? (string) $atts['category'] : '';
$per_page    = isset( $atts['posts_per_page'] ) ? (int) $atts['posts_per_page'] : 8;
$orderby_in  = isset( $atts['orderby'] ) ? (string) $atts['orderby'] : 'date';
$order       = ( isset( $atts['order'] ) && strtoupper( (string) $atts['order'] ) === 'ASC' ) ? 'ASC' : 'DESC';
$columns     = isset( $atts['columns'] ) ? max( 1, (int) $atts['columns'] ) : 4;
$gap         = isset( $atts['gap'] ) ? preg_replace( '/[^a-z]/', '', (string) $atts['gap'] ) : 'md';
$image_ratio = isset( $atts['image_ratio'] ) ? preg_replace( '/[^a-z]/', '', (string) $atts['image_ratio'] ) : 'auto';
$alignment   = isset( $atts['alignment'] ) ? preg_replace( '/[^a-z]/', '', (string) $atts['alignment'] ) : '';

$truthy = static function ( $v ) {
	return $v === true || $v === 'yes' || $v === '1' || $v === 1 || $v === 'true';
};
$on = static function ( $key ) use ( $atts, $truthy ) {
	// Missing att (older saves) defaults to ON, matching the options defaults.
	return ! array_key_exists( $key, $atts ) ? true : $truthy( $atts[ $key ] );
};
$show_badge   = $on( 'show_sale_badge' );
$show_rating  = $on( 'show_rating' );
$show_price   = $on( 'show_price' );
$show_atc     = $on( 'show_add_to_cart' );

// Newer toggles default OFF when the att is absent (older saves).
$opt = static function ( $key ) use ( $atts, $truthy ) {
	return isset( $atts[ $key ] ) ? $truthy( $atts[ $key ] ) : false;
};
$badge_style    = ( isset( $atts['badge_style'] ) && $atts['badge_style'] === 'percent' ) ? 'percent' : 'text';
$show_featured  = $opt( 'show_featured_badge' );
$show_new       = $opt( 'show_new_badge' );
$new_days       = isset( $atts['new_days'] ) ? max( 0, (int) $atts['new_days'] ) : 14;
$show_stock     = $opt( 'show_stock' );

$layout      = ( isset( $atts['layout'] ) && $atts['layout'] === 'carousel' ) ? 'carousel' : 'grid';
$show_arrows = ! array_key_exists( 'carousel_arrows', $atts ) ? true : $truthy( $atts['carousel_arrows'] );

/* ---- Build the query ----------------------------------------------------- */
$args = array(
	'post_type'           => 'product',
	'post_status'         => 'publish',
	'posts_per_page'      => $per_page,
	'ignore_sticky_posts' => true,
	'no_found_rows'       => true,
	'order'               => $order,
);

// Hide catalog-excluded products.
$tax_query = array(
	array(
		'taxonomy' => 'product_visibility',
		'field'    => 'name',
		'terms'    => array( 'exclude-from-catalog' ),
		'operator' => 'NOT IN',
	),
);

// Order-by mapping.
switch ( $orderby_in ) {
	case 'price':
		$args['orderby']  = 'meta_value_num';
		$args['meta_key'] = '_price'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		break;
	case 'popularity':
		$args['orderby']  = 'meta_value_num';
		$args['meta_key'] = 'total_sales'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		break;
	case 'rating':
		$args['orderby']  = 'meta_value_num';
		$args['meta_key'] = '_wc_average_rating'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
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
	case 'date':
	default:
		$args['orderby'] = 'date';
		break;
}

// Source.
switch ( $source ) {
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
			return; // Nothing on sale.
		}
		$args['post__in'] = $sale_ids;
		break;
	case 'best_selling':
		$args['orderby']  = 'meta_value_num';
		$args['meta_key'] = 'total_sales'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		$args['order']    = 'DESC';
		break;
	case 'top_rated':
		$args['orderby']  = 'meta_value_num';
		$args['meta_key'] = '_wc_average_rating'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		$args['order']    = 'DESC';
		break;
	case 'tag':
		$tags = isset( $atts['tags'] ) ? array_filter( array_map( 'sanitize_title', array_map( 'trim', explode( ',', (string) $atts['tags'] ) ) ) ) : array();
		if ( ! empty( $tags ) ) {
			$tax_query[] = array(
				'taxonomy' => 'product_tag',
				'field'    => 'slug',
				'terms'    => $tags,
			);
		}
		break;
	case 'attribute':
		$attr = isset( $atts['attribute'] ) ? sanitize_title( preg_replace( '/^pa_/', '', (string) $atts['attribute'] ) ) : '';
		if ( $attr !== '' ) {
			$terms = isset( $atts['attribute_terms'] ) ? array_filter( array_map( 'sanitize_title', array_map( 'trim', explode( ',', (string) $atts['attribute_terms'] ) ) ) ) : array();
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
		$id_list = isset( $atts['product_ids'] ) ? array_filter( array_map( 'intval', array_map( 'trim', explode( ',', (string) $atts['product_ids'] ) ) ) ) : array();
		if ( empty( $id_list ) ) {
			return;
		}
		$args['post__in']       = $id_list;
		$args['orderby']        = 'post__in';
		$args['posts_per_page'] = count( $id_list );
		break;
	case 'recently_viewed':
		$viewed = empty( $_COOKIE['woocommerce_recently_viewed'] )
			? array()
			: array_filter( array_map( 'absint', explode( '|', wp_unslash( $_COOKIE['woocommerce_recently_viewed'] ) ) ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$viewed = array_reverse( $viewed ); // most-recent first
		if ( empty( $viewed ) ) {
			return;
		}
		$args['post__in'] = $viewed;
		$args['orderby']  = 'post__in';
		break;
	case 'cross_sells':
		$cross = ( function_exists( 'WC' ) && WC()->cart ) ? WC()->cart->get_cross_sells() : array();
		$cross = array_filter( array_map( 'intval', (array) $cross ) );
		if ( empty( $cross ) ) {
			return;
		}
		$args['post__in'] = $cross;
		$args['orderby']  = 'post__in';
		break;
	case 'category':
	case 'recent':
	default:
		break;
}

// Category filter (selects the category for source=category; otherwise narrows).
if ( $category !== '' ) {
	$tax_query[] = array(
		'taxonomy' => 'product_cat',
		'field'    => 'slug',
		'terms'    => array( $category ),
	);
}

$args['tax_query'] = $tax_query; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query

$query = new WP_Query( $args );
if ( ! $query->have_posts() ) {
	wp_reset_postdata();
	return;
}

/* ---- Render -------------------------------------------------------------- */
$wrap_classes = array(
	'upw-products',
	'upw-products--gap-' . ( $gap !== '' ? $gap : 'md' ),
	'upw-products--ratio-' . ( $image_ratio !== '' ? $image_ratio : 'auto' ),
);
if ( $alignment !== '' && $alignment !== 'inherit' ) {
	$wrap_classes[] = 'upw-products--align-' . $alignment;
}
$wrap_classes[] = 'upw-products--' . $layout;

echo '<div class="' . esc_attr( implode( ' ', $wrap_classes ) ) . '">';

if ( $layout === 'carousel' && $show_arrows ) {
	echo '<button type="button" class="upw-products__nav upw-products__nav--prev" aria-label="' . esc_attr__( 'Previous', 'fw' ) . '">&#8249;</button>';
}

echo '<ul class="products upw-products__grid upw-products--cols-' . (int) $columns . '">';

while ( $query->have_posts() ) {
	$query->the_post();

	$GLOBALS['product'] = wc_get_product( get_the_ID() );
	$product            = $GLOBALS['product'];
	if ( ! $product instanceof WC_Product ) {
		continue;
	}

	echo '<li class="product upw-product">';

	echo '<a class="upw-product__link" href="' . esc_url( get_permalink() ) . '">';

	$badges = array();
	if ( $show_badge && $product->is_on_sale() ) {
		$sale_label = esc_html__( 'Sale', 'fw' );
		if ( $badge_style === 'percent' ) {
			$regular = (float) $product->get_regular_price();
			$sale    = (float) $product->get_sale_price();
			if ( $regular > 0 && $sale > 0 && $sale < $regular ) {
				$sale_label = '-' . (int) round( ( $regular - $sale ) / $regular * 100 ) . '%';
			}
		}
		$badges[] = '<span class="upw-product__badge onsale">' . $sale_label . '</span>';
	}
	if ( $show_featured && $product->is_featured() ) {
		$badges[] = '<span class="upw-product__badge featured">' . esc_html__( 'Featured', 'fw' ) . '</span>';
	}
	if ( $show_new && $new_days > 0 && get_post_time( 'U', true, $product->get_id() ) > ( time() - $new_days * DAY_IN_SECONDS ) ) {
		$badges[] = '<span class="upw-product__badge is-new">' . esc_html__( 'New', 'fw' ) . '</span>';
	}
	if ( $show_stock && ! $product->is_in_stock() ) {
		$badges[] = '<span class="upw-product__badge out-of-stock">' . esc_html__( 'Out of stock', 'fw' ) . '</span>';
	}
	if ( ! empty( $badges ) ) {
		echo '<span class="upw-product__badges">' . implode( '', $badges ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	echo '<span class="upw-product__media">';
	echo wp_kses_post( $product->get_image( 'woocommerce_thumbnail' ) );
	echo '</span>';

	echo '<span class="upw-product__title">' . esc_html( get_the_title() ) . '</span>';
	echo '</a>';

	if ( $show_rating ) {
		$avg = (float) $product->get_average_rating();
		if ( $avg > 0 ) {
			echo '<div class="upw-product__rating">' . wp_kses_post( wc_get_rating_html( $avg ) ) . '</div>';
		}
	}

	if ( $show_price ) {
		$price_html = $product->get_price_html();
		if ( $price_html ) {
			echo '<div class="upw-product__price">' . wp_kses_post( $price_html ) . '</div>';
		}
	}

	if ( $show_stock && $product->is_in_stock() && $product->managing_stock() ) {
		$qty = $product->get_stock_quantity();
		$low = $product->get_low_stock_amount();
		if ( $low === '' || $low === null ) {
			$low = (int) get_option( 'woocommerce_notify_low_stock_amount', 2 );
		}
		$low = max( 1, (int) $low );
		if ( $qty !== null && $qty > 0 && $qty <= $low ) {
			echo '<div class="upw-product__stock low">' . sprintf( esc_html__( 'Only %d left', 'fw' ), (int) $qty ) . '</div>';
		}
	}

	if ( $show_atc && function_exists( 'woocommerce_template_loop_add_to_cart' ) ) {
		echo '<div class="upw-product__cart">';
		woocommerce_template_loop_add_to_cart();
		echo '</div>';
	}

	echo '</li>';
}

echo '</ul>';

if ( $layout === 'carousel' && $show_arrows ) {
	echo '<button type="button" class="upw-products__nav upw-products__nav--next" aria-label="' . esc_attr__( 'Next', 'fw' ) . '">&#8250;</button>';
}

echo '</div>';

wp_reset_postdata();

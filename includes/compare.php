<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
/**
 * Compare — pick a few products, then see them side by side.
 *
 * Unlike the wishlist, a compare list is *ephemeral*: it answers "which of
 * these three do I want?" and stops mattering the moment that is decided. So it
 * is a session-lifetime cookie for everyone, signed in or not — no user meta, no
 * list to manage later, nothing to clean up.
 *
 * The bar along the bottom of the screen is rendered EMPTY by PHP and filled in
 * by the script from the visitor's own cookie. A server-rendered bar would be
 * baked into a page cache and shown to whoever loaded that page next.
 *
 * @package unysonplus
 */

if ( ! defined( 'UPWC_COMPARE_COOKIE' ) ) {
	define( 'UPWC_COMPARE_COOKIE', 'upwc_compare' );
}

if ( ! function_exists( 'upwc_compare_enabled' ) ) :
/**
 * @return bool
 */
function upwc_compare_enabled() {
	if ( function_exists( 'upwc_wc_catalog_locked' ) && upwc_wc_catalog_locked() ) {
		return false;
	}
	if ( ! function_exists( 'fw_get_db_ext_settings_option' ) ) {
		return false;
	}

	return upwc_wc_truthy( fw_get_db_ext_settings_option( 'woocommerce', 'compare' ) );
}
endif;

if ( ! function_exists( 'upwc_compare_max' ) ) :
/**
 * How many products may be compared at once. Floored at 2 (comparing one
 * product with itself is not a comparison) and capped at 6, past which the
 * table stops being readable on any screen.
 *
 * @return int
 */
function upwc_compare_max() {
	$max = function_exists( 'fw_get_db_ext_settings_option' )
		? (int) fw_get_db_ext_settings_option( 'woocommerce', 'compare_max' )
		: 4;

	return max( 2, min( 6, $max ? $max : 4 ) );
}
endif;

if ( ! function_exists( 'upwc_compare_ids' ) ) :
/**
 * @return int[]
 */
function upwc_compare_ids() {
	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- sanitized here.
	$raw = isset( $_COOKIE[ UPWC_COMPARE_COOKIE ] ) ? wp_unslash( $_COOKIE[ UPWC_COMPARE_COOKIE ] ) : '';
	$ids = array_values( array_unique( array_filter( array_map( 'absint', explode( ',', (string) $raw ) ) ) ) );

	return array_slice( $ids, 0, upwc_compare_max() );
}
endif;

if ( ! function_exists( 'upwc_compare_save' ) ) :
/**
 * @param int[] $ids
 * @return int[]
 */
function upwc_compare_save( $ids ) {
	$ids   = array_slice( array_values( array_unique( array_filter( array_map( 'absint', (array) $ids ) ) ) ), 0, upwc_compare_max() );
	$value = implode( ',', $ids );

	setcookie(
		UPWC_COMPARE_COOKIE,
		$value,
		array(
			// Session cookie (expires 0) — the list dies with the browsing session,
			// which is exactly how long the question it answers lasts.
			'expires'  => 0,
			'path'     => COOKIEPATH ? COOKIEPATH : '/',
			'domain'   => COOKIE_DOMAIN,
			'secure'   => is_ssl(),
			'httponly' => false,
			'samesite' => 'Lax',
		)
	);
	$_COOKIE[ UPWC_COMPARE_COOKIE ] = $value;

	return $ids;
}
endif;

if ( ! function_exists( 'upwc_compare_button_html' ) ) :
/**
 * The compare toggle for one product.
 *
 * @param int $product_id
 * @return string
 */
function upwc_compare_button_html( $product_id ) {
	if ( ! upwc_compare_enabled() ) {
		return '';
	}

	return sprintf(
		'<button type="button" class="upwc-compare-btn" data-product="%d" aria-pressed="false" title="%s">'
		. '<svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2"'
		. ' stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">'
		. '<path d="M3 6h7M3 6l2.5-2.5M3 6l2.5 2.5"/><path d="M21 18h-7M21 18l-2.5-2.5M21 18l-2.5 2.5"/>'
		. '</svg><span class="upwc-compare-btn__label">%s</span></button>',
		(int) $product_id,
		esc_attr__( 'Add to compare', 'fw' ),
		esc_html__( 'Compare', 'fw' )
	);
}
endif;

if ( ! function_exists( 'upwc_compare_single_button' ) ) :
/**
 * @internal
 */
function upwc_compare_single_button() {
	global $product;

	if ( ! $product instanceof WC_Product ) {
		return;
	}

	// phpcs:ignore WordPress.Security.EscapeOutput -- escaped in the helper.
	echo '<div class="upwc-compare-single">' . upwc_compare_button_html( $product->get_id() ) . '</div>';
}
endif;

if ( ! function_exists( 'upwc_compare_bar' ) ) :
/**
 * The empty compare bar. The script shows it and fills it once it knows what
 * the visitor has picked.
 *
 * @internal
 */
function upwc_compare_bar() {
	if ( ! upwc_compare_enabled() || is_admin() ) {
		return;
	}

	$page = function_exists( 'fw_get_db_ext_settings_option' )
		? trim( (string) fw_get_db_ext_settings_option( 'woocommerce', 'compare_page' ) )
		: '';
	?>
	<div class="upwc-compare-bar" hidden aria-live="polite">
		<div class="upwc-compare-bar__inner">
			<span class="upwc-compare-bar__title"><?php esc_html_e( 'Compare', 'fw' ); ?></span>
			<div class="upwc-compare-bar__items"></div>
			<div class="upwc-compare-bar__actions">
				<?php if ( $page ) : ?>
					<a class="button upwc-compare-bar__go" href="<?php echo esc_url( $page ); ?>"><?php esc_html_e( 'Compare', 'fw' ); ?></a>
				<?php endif; ?>
				<button type="button" class="upwc-compare-bar__clear"><?php esc_html_e( 'Clear', 'fw' ); ?></button>
			</div>
		</div>
	</div>
	<?php
}
endif;
add_action( 'wp_footer', 'upwc_compare_bar', 30 );

/* -------------------------------------------------------------------------- *
 * AJAX
 * -------------------------------------------------------------------------- */

if ( ! function_exists( 'upwc_compare_item_payload' ) ) :
/**
 * The minimum a bar entry needs: a thumbnail, a name and a link.
 *
 * @param int[] $ids
 * @return array<int,array>
 */
function upwc_compare_item_payload( $ids ) {
	$out = array();

	foreach ( (array) $ids as $id ) {
		$product = wc_get_product( $id );
		if ( ! $product instanceof WC_Product ) {
			continue;
		}
		$out[] = array(
			'id'    => $product->get_id(),
			'name'  => $product->get_name(),
			'url'   => $product->get_permalink(),
			'image' => wp_get_attachment_image_url( $product->get_image_id(), 'woocommerce_gallery_thumbnail' ),
		);
	}

	return $out;
}
endif;

if ( ! function_exists( 'upwc_compare_ajax_toggle' ) ) :
/**
 * @internal
 */
function upwc_compare_ajax_toggle() {
	check_ajax_referer( 'upwc_wc_storefront', 'nonce' );

	if ( ! upwc_compare_enabled() ) {
		wp_send_json_error( array( 'message' => __( 'Compare is turned off.', 'fw' ) ), 403 );
	}

	$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
	if ( ! $product_id || 'product' !== get_post_type( $product_id ) ) {
		wp_send_json_error( array( 'message' => __( 'Unknown product.', 'fw' ) ), 400 );
	}

	if ( function_exists( 'fw_rate_limit_ajax' ) ) {
		fw_rate_limit_ajax( 'wc_compare_toggle', 60, 60 );
	}

	$ids  = upwc_compare_ids();
	$at   = array_search( $product_id, $ids, true );
	$full = false;

	if ( false !== $at ) {
		unset( $ids[ $at ] );
		$active = false;
	} elseif ( count( $ids ) >= upwc_compare_max() ) {
		// Refuse rather than silently dropping the oldest: the visitor chose
		// those, and swapping one out behind their back is worse than saying no.
		$active = false;
		$full   = true;
	} else {
		$ids[]  = $product_id;
		$active = true;
	}

	$ids = upwc_compare_save( $ids );

	wp_send_json_success( array(
		'ids'    => $ids,
		'items'  => upwc_compare_item_payload( $ids ),
		'active' => $active,
		'full'   => $full,
		'max'    => upwc_compare_max(),
		/* translators: %d: maximum number of products that can be compared. */
		'message' => $full ? sprintf( __( 'You can compare up to %d products — remove one first.', 'fw' ), upwc_compare_max() ) : '',
	) );
}
endif;
add_action( 'wp_ajax_upwc_wc_compare_toggle', 'upwc_compare_ajax_toggle' );
add_action( 'wp_ajax_nopriv_upwc_wc_compare_toggle', 'upwc_compare_ajax_toggle' );

if ( ! function_exists( 'upwc_compare_ajax_get' ) ) :
/**
 * @internal
 */
function upwc_compare_ajax_get() {
	check_ajax_referer( 'upwc_wc_storefront', 'nonce' );

	$ids = upwc_compare_enabled() ? upwc_compare_ids() : array();

	wp_send_json_success( array(
		'ids'   => $ids,
		'items' => upwc_compare_item_payload( $ids ),
		'max'   => upwc_compare_max(),
	) );
}
endif;
add_action( 'wp_ajax_upwc_wc_compare_get', 'upwc_compare_ajax_get' );
add_action( 'wp_ajax_nopriv_upwc_wc_compare_get', 'upwc_compare_ajax_get' );

if ( ! function_exists( 'upwc_compare_ajax_clear' ) ) :
/**
 * @internal
 */
function upwc_compare_ajax_clear() {
	check_ajax_referer( 'upwc_wc_storefront', 'nonce' );
	upwc_compare_save( array() );
	wp_send_json_success( array( 'ids' => array(), 'items' => array() ) );
}
endif;
add_action( 'wp_ajax_upwc_wc_compare_clear', 'upwc_compare_ajax_clear' );
add_action( 'wp_ajax_nopriv_upwc_wc_compare_clear', 'upwc_compare_ajax_clear' );

/* -------------------------------------------------------------------------- *
 * The comparison table (used by the [wc_compare] element)
 * -------------------------------------------------------------------------- */

if ( ! function_exists( 'upwc_compare_table_html' ) ) :
/**
 * Products side by side: image, name, price, availability, rating, then every
 * attribute any of them declares (a product without that attribute gets a dash,
 * so the columns stay aligned and the gap is visible rather than confusing).
 *
 * @param int[] $ids
 * @return string
 */
function upwc_compare_table_html( $ids ) {
	$products = array();
	foreach ( (array) $ids as $id ) {
		$p = wc_get_product( $id );
		if ( $p instanceof WC_Product ) {
			$products[] = $p;
		}
	}

	if ( ! $products ) {
		return '';
	}

	// The union of every attribute in play, in first-seen order.
	$attributes = array();
	foreach ( $products as $p ) {
		foreach ( $p->get_attributes() as $key => $attribute ) {
			if ( ! isset( $attributes[ $key ] ) ) {
				$attributes[ $key ] = wc_attribute_label( $attribute->get_name() );
			}
		}
	}

	$rows = array();

	$cells = array();
	foreach ( $products as $p ) {
		$cells[] = '<a href="' . esc_url( $p->get_permalink() ) . '">'
			. $p->get_image( 'woocommerce_thumbnail' )
			. '<span class="upwc-compare-table__name">' . esc_html( $p->get_name() ) . '</span></a>'
			. '<button type="button" class="upwc-compare-table__remove" data-product="' . (int) $p->get_id() . '">'
			. esc_html__( 'Remove', 'fw' ) . '</button>';
	}
	$rows[] = array( '', $cells, 'upwc-compare-table__head' );

	$cells = array();
	foreach ( $products as $p ) {
		$cells[] = $p->get_price_html() ? $p->get_price_html() : '&mdash;';
	}
	$rows[] = array( __( 'Price', 'fw' ), $cells, '' );

	$cells = array();
	foreach ( $products as $p ) {
		$cells[] = $p->is_in_stock()
			? esc_html__( 'In stock', 'fw' )
			: esc_html__( 'Out of stock', 'fw' );
	}
	$rows[] = array( __( 'Availability', 'fw' ), $cells, '' );

	$cells = array();
	foreach ( $products as $p ) {
		$avg     = (float) $p->get_average_rating();
		$cells[] = $avg > 0 ? wc_get_rating_html( $avg ) : '&mdash;';
	}
	$rows[] = array( __( 'Rating', 'fw' ), $cells, '' );

	foreach ( $attributes as $key => $label ) {
		$cells = array();
		foreach ( $products as $p ) {
			$value    = $p->get_attribute( $key );
			$cells[] = '' !== $value ? esc_html( $value ) : '&mdash;';
		}
		$rows[] = array( $label, $cells, '' );
	}

	$cells = array();
	foreach ( $products as $p ) {
		$cells[] = '<a class="button" href="' . esc_url( $p->get_permalink() ) . '">'
			. esc_html__( 'View product', 'fw' ) . '</a>';
	}
	$rows[] = array( '', $cells, 'upwc-compare-table__foot' );

	$html = '<div class="upwc-compare-table-wrap"><table class="upwc-compare-table"><tbody>';
	foreach ( $rows as $row ) {
		list( $label, $cells, $class ) = $row;
		$html .= '<tr' . ( $class ? ' class="' . esc_attr( $class ) . '"' : '' ) . '>';
		$html .= '<th scope="row">' . esc_html( $label ) . '</th>';
		foreach ( $cells as $cell ) {
			$html .= '<td>' . $cell . '</td>';
		}
		$html .= '</tr>';
	}
	$html .= '</tbody></table></div>';

	return $html;
}
endif;

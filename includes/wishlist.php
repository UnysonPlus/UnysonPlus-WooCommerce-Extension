<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
/**
 * Wishlist — storage, AJAX endpoints and the heart button.
 *
 * The product cards have offered a "Wishlist Heart" slot since the Card Rows
 * engine landed, but it rendered as a decorative `<span aria-hidden="true">`:
 * a heart that could not be clicked, stored nothing, and had no counterpart
 * anywhere else on the site. This makes it real.
 *
 * Storage, in two tiers:
 *
 *   - **Signed-in visitors** get user meta (`upwc_wishlist`), so their list
 *     follows them between devices and survives a cleared browser.
 *   - **Guests** get a cookie (`upwc_wishlist`), which is what a shop can offer
 *     without asking someone to register. It is a plain id list, no personal
 *     data, so it needs no consent banner of its own.
 *
 * When a guest signs in, whatever they collected as a guest is merged into
 * their stored list rather than discarded — losing a wishlist at the login step
 * is the one moment a shopper is most likely to notice and mind.
 *
 * Rendering is deliberately CLIENT-hydrated: the heart markup is identical for
 * everyone and the "on" state is applied in JS from a bootstrap payload. A
 * server-rendered state would be baked into any page cache and shown to the
 * next visitor.
 *
 * @package unysonplus
 */

if ( ! defined( 'UPWC_WISHLIST_COOKIE' ) ) {
	define( 'UPWC_WISHLIST_COOKIE', 'upwc_wishlist' );
}
if ( ! defined( 'UPWC_WISHLIST_META' ) ) {
	define( 'UPWC_WISHLIST_META', 'upwc_wishlist' );
}

/** How long a guest's wishlist cookie lives. A shopping decision can take weeks. */
if ( ! defined( 'UPWC_WISHLIST_COOKIE_DAYS' ) ) {
	define( 'UPWC_WISHLIST_COOKIE_DAYS', 90 );
}

if ( ! function_exists( 'upwc_wishlist_sanitize_ids' ) ) :
/**
 * Normalize an id list: ints, no dupes, no zeros, capped.
 *
 * The cap matters — the cookie tier has ~4KB to work with, and an unbounded
 * list from a crafted request would silently break every subsequent response.
 *
 * @param mixed $ids
 * @return int[]
 */
function upwc_wishlist_sanitize_ids( $ids ) {
	if ( is_string( $ids ) ) {
		$ids = explode( ',', $ids );
	}
	if ( ! is_array( $ids ) ) {
		return array();
	}

	$ids = array_values( array_unique( array_filter( array_map( 'absint', $ids ) ) ) );

	/**
	 * Maximum products one wishlist can hold.
	 *
	 * @param int $max
	 */
	$max = (int) apply_filters( 'upwc_wishlist_max_items', 200 );

	return count( $ids ) > $max ? array_slice( $ids, 0, $max ) : $ids;
}
endif;

if ( ! function_exists( 'upwc_wishlist_ids' ) ) :
/**
 * The current visitor's wishlist, newest first.
 *
 * @return int[]
 */
function upwc_wishlist_ids() {
	if ( is_user_logged_in() ) {
		return upwc_wishlist_sanitize_ids( get_user_meta( get_current_user_id(), UPWC_WISHLIST_META, true ) );
	}

	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- sanitized right here.
	$raw = isset( $_COOKIE[ UPWC_WISHLIST_COOKIE ] ) ? wp_unslash( $_COOKIE[ UPWC_WISHLIST_COOKIE ] ) : '';

	return upwc_wishlist_sanitize_ids( $raw );
}
endif;

if ( ! function_exists( 'upwc_wishlist_save' ) ) :
/**
 * Persist a wishlist for the current visitor.
 *
 * @param int[] $ids
 * @return int[] The list as stored (sanitized).
 */
function upwc_wishlist_save( $ids ) {
	$ids = upwc_wishlist_sanitize_ids( $ids );

	if ( is_user_logged_in() ) {
		update_user_meta( get_current_user_id(), UPWC_WISHLIST_META, $ids );
	} else {
		$value = implode( ',', $ids );
		// Not HttpOnly on purpose: the same list is read by the front-end script
		// to paint the hearts, and a second source of truth would drift.
		setcookie(
			UPWC_WISHLIST_COOKIE,
			$value,
			array(
				'expires'  => $ids ? time() + DAY_IN_SECONDS * UPWC_WISHLIST_COOKIE_DAYS : time() - 3600,
				'path'     => COOKIEPATH ? COOKIEPATH : '/',
				'domain'   => COOKIE_DOMAIN,
				'secure'   => is_ssl(),
				'httponly' => false,
				'samesite' => 'Lax',
			)
		);
		$_COOKIE[ UPWC_WISHLIST_COOKIE ] = $value;
	}

	/**
	 * Fires after the wishlist changes (add, remove or merge).
	 *
	 * @param int[] $ids
	 */
	do_action( 'upwc_wishlist_saved', $ids );

	return $ids;
}
endif;

if ( ! function_exists( 'upwc_wishlist_has' ) ) :
/**
 * @param int $product_id
 * @return bool
 */
function upwc_wishlist_has( $product_id ) {
	return in_array( (int) $product_id, upwc_wishlist_ids(), true );
}
endif;

if ( ! function_exists( 'upwc_wishlist_toggle' ) ) :
/**
 * Add a product if absent, remove it if present.
 *
 * New items go to the FRONT: a wishlist is read newest-first, like a
 * shortlist someone is still building.
 *
 * @param int $product_id
 * @return array{ids:int[],active:bool}
 */
function upwc_wishlist_toggle( $product_id ) {
	$product_id = (int) $product_id;
	$ids        = upwc_wishlist_ids();
	$at         = array_search( $product_id, $ids, true );

	if ( false !== $at ) {
		unset( $ids[ $at ] );
		$active = false;
	} else {
		array_unshift( $ids, $product_id );
		$active = true;
	}

	return array(
		'ids'    => upwc_wishlist_save( $ids ),
		'active' => $active,
	);
}
endif;

if ( ! function_exists( 'upwc_wishlist_enabled' ) ) :
/**
 * The wishlist is opt-in per store (Shop Behavior setting), and switched off
 * entirely under the catalog lockdown — saving items you cannot buy is a dead
 * end, and the heart would just be decoration again.
 *
 * @return bool
 */
function upwc_wishlist_enabled() {
	if ( function_exists( 'upwc_wc_catalog_locked' ) && upwc_wc_catalog_locked() ) {
		return false;
	}
	if ( ! function_exists( 'fw_get_db_ext_settings_option' ) ) {
		return false;
	}

	return upwc_wc_truthy( fw_get_db_ext_settings_option( 'woocommerce', 'wishlist' ) );
}
endif;

if ( ! function_exists( 'upwc_wishlist_button_html' ) ) :
/**
 * The heart button for one product.
 *
 * Always rendered in the "off" state; the script turns on the ones that belong
 * to this visitor once it has the bootstrap payload. It is a real <button> with
 * aria-pressed, so it is reachable by keyboard and announced as a toggle.
 *
 * @param int $product_id
 * @return string
 */
function upwc_wishlist_button_html( $product_id ) {
	if ( ! upwc_wishlist_enabled() ) {
		return '';
	}

	$product_id = (int) $product_id;

	return sprintf(
		'<button type="button" class="upwc-wishlist-btn" data-product="%d" aria-pressed="false"'
		. ' title="%s" aria-label="%s">'
		. '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor"'
		. ' stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">'
		. '<path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>'
		. '</svg><span class="screen-reader-text upwc-wishlist-btn__label">%s</span></button>',
		$product_id,
		esc_attr__( 'Save to wishlist', 'fw' ),
		esc_attr__( 'Save to wishlist', 'fw' ),
		esc_html__( 'Save to wishlist', 'fw' )
	);
}
endif;

/* -------------------------------------------------------------------------- *
 * Guest list -> account list on sign-in
 * -------------------------------------------------------------------------- */

if ( ! function_exists( 'upwc_wishlist_merge_on_login' ) ) :
/**
 * Merge whatever a guest collected into the account they just signed into.
 *
 * @param string  $user_login
 * @param WP_User $user
 * @internal
 */
function upwc_wishlist_merge_on_login( $user_login, $user = null ) {
	if ( ! $user instanceof WP_User ) {
		return;
	}

	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- sanitized below.
	$guest = isset( $_COOKIE[ UPWC_WISHLIST_COOKIE ] ) ? wp_unslash( $_COOKIE[ UPWC_WISHLIST_COOKIE ] ) : '';
	$guest = upwc_wishlist_sanitize_ids( $guest );
	if ( ! $guest ) {
		return;
	}

	$stored = upwc_wishlist_sanitize_ids( get_user_meta( $user->ID, UPWC_WISHLIST_META, true ) );
	update_user_meta( $user->ID, UPWC_WISHLIST_META, upwc_wishlist_sanitize_ids( array_merge( $guest, $stored ) ) );

	// Clear the guest cookie so the two tiers cannot disagree afterwards.
	setcookie(
		UPWC_WISHLIST_COOKIE,
		'',
		array(
			'expires'  => time() - 3600,
			'path'     => COOKIEPATH ? COOKIEPATH : '/',
			'domain'   => COOKIE_DOMAIN,
			'secure'   => is_ssl(),
			'httponly' => false,
			'samesite' => 'Lax',
		)
	);
	unset( $_COOKIE[ UPWC_WISHLIST_COOKIE ] );
}
endif;
add_action( 'wp_login', 'upwc_wishlist_merge_on_login', 10, 2 );

/* -------------------------------------------------------------------------- *
 * AJAX
 * -------------------------------------------------------------------------- */

if ( ! function_exists( 'upwc_wishlist_ajax_toggle' ) ) :
/**
 * Toggle one product. Responds with the full list so the page can repaint every
 * heart for that product at once (a grid can show the same product twice).
 *
 * @internal
 */
function upwc_wishlist_ajax_toggle() {
	check_ajax_referer( 'upwc_wc_storefront', 'nonce' );

	if ( ! upwc_wishlist_enabled() ) {
		wp_send_json_error( array( 'message' => __( 'The wishlist is turned off.', 'fw' ) ), 403 );
	}

	$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
	if ( ! $product_id || 'product' !== get_post_type( $product_id ) ) {
		wp_send_json_error( array( 'message' => __( 'Unknown product.', 'fw' ) ), 400 );
	}

	if ( function_exists( 'fw_rate_limit_ajax' ) ) {
		fw_rate_limit_ajax( 'wc_wishlist_toggle', 60, 60 );
	}

	$result = upwc_wishlist_toggle( $product_id );

	wp_send_json_success( array(
		'ids'    => $result['ids'],
		'active' => $result['active'],
		'count'  => count( $result['ids'] ),
	) );
}
endif;
add_action( 'wp_ajax_upwc_wc_wishlist_toggle', 'upwc_wishlist_ajax_toggle' );
add_action( 'wp_ajax_nopriv_upwc_wc_wishlist_toggle', 'upwc_wishlist_ajax_toggle' );

if ( ! function_exists( 'upwc_wishlist_ajax_get' ) ) :
/**
 * The current list. Used to hydrate the hearts on a page served from cache,
 * where the markup cannot carry a per-visitor state.
 *
 * @internal
 */
function upwc_wishlist_ajax_get() {
	check_ajax_referer( 'upwc_wc_storefront', 'nonce' );

	$ids = upwc_wishlist_enabled() ? upwc_wishlist_ids() : array();

	wp_send_json_success( array( 'ids' => $ids, 'count' => count( $ids ) ) );
}
endif;
add_action( 'wp_ajax_upwc_wc_wishlist_get', 'upwc_wishlist_ajax_get' );
add_action( 'wp_ajax_nopriv_upwc_wc_wishlist_get', 'upwc_wishlist_ajax_get' );

/* -------------------------------------------------------------------------- *
 * Single product — the heart beside the add-to-cart
 * -------------------------------------------------------------------------- */

if ( ! function_exists( 'upwc_wishlist_single_button' ) ) :
/**
 * @internal
 */
function upwc_wishlist_single_button() {
	global $product;

	if ( ! $product instanceof WC_Product ) {
		return;
	}

	$html = upwc_wishlist_button_html( $product->get_id() );
	if ( '' === $html ) {
		return;
	}

	$page = function_exists( 'fw_get_db_ext_settings_option' )
		? trim( (string) fw_get_db_ext_settings_option( 'woocommerce', 'wishlist_page' ) )
		: '';

	echo '<div class="upwc-wishlist-single">'
		. $html // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in the helper.
		. '<span class="upwc-wishlist-single__text">' . esc_html__( 'Save to wishlist', 'fw' ) . '</span>'
		. ( $page
			? ' <a class="upwc-wishlist-single__link" href="' . esc_url( $page ) . '">' . esc_html__( 'View your wishlist', 'fw' ) . '</a>'
			: '' )
		. '</div>';
}
endif;

<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
/**
 * Back-in-stock notifications.
 *
 * An out-of-stock product is a dead end: the shopper wanted it, the shop has
 * nothing to offer them, and neither side gets told when that changes. This
 * replaces the "Out of stock" line with an email field, remembers who asked,
 * and mails them the moment the product is restocked.
 *
 * Storage is post meta on the product (`_upwc_bis_emails`) — a plain list of
 * addresses. Keeping it with the product means it is deleted with the product,
 * exported with it, and needs no table of its own.
 *
 * Sending happens on `woocommerce_product_set_stock_status`, which fires however
 * the stock changed: the product editor, a CSV import, a REST call, or an order
 * being refunded back into stock.
 *
 * @package unysonplus
 */

if ( ! defined( 'UPWC_BIS_META' ) ) {
	define( 'UPWC_BIS_META', '_upwc_bis_emails' );
}

if ( ! function_exists( 'upwc_bis_enabled' ) ) :
/**
 * @return bool
 */
function upwc_bis_enabled() {
	if ( function_exists( 'upwc_wc_catalog_locked' ) && upwc_wc_catalog_locked() ) {
		return false;
	}
	if ( ! function_exists( 'fw_get_db_ext_settings_option' ) ) {
		return false;
	}

	return upwc_wc_truthy( fw_get_db_ext_settings_option( 'woocommerce', 'back_in_stock' ) );
}
endif;

if ( ! function_exists( 'upwc_bis_emails' ) ) :
/**
 * @param int $product_id
 * @return string[]
 */
function upwc_bis_emails( $product_id ) {
	$list = get_post_meta( (int) $product_id, UPWC_BIS_META, true );

	return is_array( $list ) ? array_values( array_filter( $list, 'is_email' ) ) : array();
}
endif;

if ( ! function_exists( 'upwc_bis_subscribe' ) ) :
/**
 * Record an address against a product. Idempotent — signing up twice is a thing
 * people do when they are not sure the first one worked, and it should not mean
 * two emails.
 *
 * @param int    $product_id
 * @param string $email
 * @return bool True when stored (or already present).
 */
function upwc_bis_subscribe( $product_id, $email ) {
	$product_id = (int) $product_id;
	$email      = sanitize_email( $email );

	if ( ! $product_id || ! is_email( $email ) ) {
		return false;
	}

	$list = upwc_bis_emails( $product_id );
	if ( in_array( $email, $list, true ) ) {
		return true;
	}

	/**
	 * Cap the sign-ups one product will hold, so a script cannot grow a single
	 * meta row without bound.
	 *
	 * @param int $max
	 */
	$max = (int) apply_filters( 'upwc_bis_max_subscribers', 2000 );
	if ( count( $list ) >= $max ) {
		return false;
	}

	$list[] = $email;
	update_post_meta( $product_id, UPWC_BIS_META, $list );

	return true;
}
endif;

if ( ! function_exists( 'upwc_bis_form' ) ) :
/**
 * The sign-up form, shown in place of WooCommerce's stock line on an
 * out-of-stock product.
 *
 * @internal
 */
function upwc_bis_form() {
	global $product;

	if ( ! upwc_bis_enabled() || ! $product instanceof WC_Product || $product->is_in_stock() ) {
		return;
	}

	$heading = function_exists( 'fw_get_db_ext_settings_option' )
		? trim( (string) fw_get_db_ext_settings_option( 'woocommerce', 'back_in_stock_label' ) )
		: '';
	if ( '' === $heading ) {
		$heading = __( 'Email me when this is back', 'fw' );
	}

	$current = is_user_logged_in() ? wp_get_current_user()->user_email : '';
	?>
	<div class="upwc-bis" data-product="<?php echo (int) $product->get_id(); ?>">
		<p class="upwc-bis__heading"><?php echo esc_html( $heading ); ?></p>
		<form class="upwc-bis__form" method="post">
			<label class="screen-reader-text" for="upwc-bis-email-<?php echo (int) $product->get_id(); ?>">
				<?php esc_html_e( 'Your email address', 'fw' ); ?>
			</label>
			<input type="email"
			       id="upwc-bis-email-<?php echo (int) $product->get_id(); ?>"
			       class="upwc-bis__email"
			       name="email"
			       value="<?php echo esc_attr( $current ); ?>"
			       placeholder="<?php esc_attr_e( 'you@example.com', 'fw' ); ?>"
			       required />
			<button type="submit" class="button upwc-bis__submit"><?php esc_html_e( 'Notify me', 'fw' ); ?></button>
		</form>
		<p class="upwc-bis__message" role="status"></p>
	</div>
	<?php
}
endif;

if ( ! function_exists( 'upwc_bis_ajax_subscribe' ) ) :
/**
 * @internal
 */
function upwc_bis_ajax_subscribe() {
	check_ajax_referer( 'upwc_wc_storefront', 'nonce' );

	if ( ! upwc_bis_enabled() ) {
		wp_send_json_error( array( 'message' => __( 'Notifications are turned off.', 'fw' ) ), 403 );
	}

	// Tighter than the other endpoints: this one writes an address someone else
	// will be emailed at, so it is the one worth throttling hard.
	if ( function_exists( 'fw_rate_limit_ajax' ) ) {
		fw_rate_limit_ajax( 'wc_bis_subscribe', 10, 60 );
	}

	$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
	$email      = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';

	if ( ! $product_id || 'product' !== get_post_type( $product_id ) ) {
		wp_send_json_error( array( 'message' => __( 'Unknown product.', 'fw' ) ), 400 );
	}
	if ( ! is_email( $email ) ) {
		wp_send_json_error( array( 'message' => __( 'That does not look like an email address.', 'fw' ) ), 400 );
	}

	if ( ! upwc_bis_subscribe( $product_id, $email ) ) {
		wp_send_json_error( array( 'message' => __( 'Sorry — that could not be saved. Please try again later.', 'fw' ) ), 500 );
	}

	wp_send_json_success( array(
		'message' => __( 'Done. We will email you the moment it is back.', 'fw' ),
	) );
}
endif;
add_action( 'wp_ajax_upwc_wc_bis_subscribe', 'upwc_bis_ajax_subscribe' );
add_action( 'wp_ajax_nopriv_upwc_wc_bis_subscribe', 'upwc_bis_ajax_subscribe' );

if ( ! function_exists( 'upwc_bis_notify_on_restock' ) ) :
/**
 * Mail everyone waiting when a product comes back into stock, then clear the
 * list — a sign-up is for the next restock, not a standing subscription.
 *
 * The list is cleared BEFORE sending so a slow mail run cannot be re-entered by
 * a second stock change and send everything twice.
 *
 * @param int    $product_id
 * @param string $status
 * @internal
 */
function upwc_bis_notify_on_restock( $product_id, $status ) {
	if ( 'instock' !== $status ) {
		return;
	}

	$product_id = (int) $product_id;
	$emails     = upwc_bis_emails( $product_id );
	if ( ! $emails ) {
		return;
	}

	$product = wc_get_product( $product_id );
	if ( ! $product instanceof WC_Product ) {
		return;
	}

	delete_post_meta( $product_id, UPWC_BIS_META );

	$subject = function_exists( 'fw_get_db_ext_settings_option' )
		? trim( (string) fw_get_db_ext_settings_option( 'woocommerce', 'back_in_stock_subject' ) )
		: '';
	if ( '' === $subject ) {
		/* translators: %s: product name. */
		$subject = __( '{product} is back in stock', 'fw' );
	}
	$subject = str_replace( '{product}', $product->get_name(), $subject );

	$body = sprintf(
		/* translators: 1: product name, 2: product URL. */
		__( "Good news — %1\$s is available again.\n\n%2\$s\n\nYou are getting this because you asked to be told when it was back. We will not email you about it again.", 'fw' ),
		$product->get_name(),
		$product->get_permalink()
	);

	/**
	 * Filter the restock notification before it is sent.
	 *
	 * @param array      $mail    subject / message / headers
	 * @param WC_Product $product
	 * @param string[]   $emails
	 */
	$mail = apply_filters( 'upwc_bis_notification', array(
		'subject' => $subject,
		'message' => $body,
		'headers' => array(),
	), $product, $emails );

	foreach ( $emails as $email ) {
		// One message per recipient rather than a shared BCC: these addresses
		// belong to different people who never agreed to be in a list together.
		wp_mail( $email, $mail['subject'], $mail['message'], $mail['headers'] );
	}
}
endif;
add_action( 'woocommerce_product_set_stock_status', 'upwc_bis_notify_on_restock', 10, 2 );

if ( ! function_exists( 'upwc_bis_variation_restock' ) ) :
/**
 * A variation coming back into stock counts as the parent being back, for
 * anyone who signed up on the parent's page.
 *
 * @param int    $variation_id
 * @param string $status
 * @internal
 */
function upwc_bis_variation_restock( $variation_id, $status ) {
	if ( 'instock' !== $status ) {
		return;
	}

	$variation = wc_get_product( $variation_id );
	if ( $variation instanceof WC_Product && $variation->get_parent_id() ) {
		upwc_bis_notify_on_restock( $variation->get_parent_id(), 'instock' );
	}
}
endif;
add_action( 'woocommerce_variation_set_stock_status', 'upwc_bis_variation_restock', 10, 2 );

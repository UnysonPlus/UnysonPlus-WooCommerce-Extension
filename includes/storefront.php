<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
/**
 * Two single-product conveniences that are too small for files of their own.
 *
 *   - **Sticky add-to-cart bar.** On a long product page the buy button is
 *     somewhere near the top and everything persuading you to press it is
 *     further down, so by the time someone is convinced the control is gone.
 *     A compact bar slides in once the real one scrolls out of view.
 *   - **Size guide.** A modal beside the add-to-cart, with per-product content
 *     falling back to a store-wide default. Guessing a size is the single
 *     biggest cause of a return in anything worn.
 *
 * @package unysonplus
 */

if ( ! defined( 'UPWC_SIZE_GUIDE_META' ) ) {
	define( 'UPWC_SIZE_GUIDE_META', '_upwc_size_guide' );
}

/* -------------------------------------------------------------------------- *
 * Sticky add to cart
 * -------------------------------------------------------------------------- */

if ( ! function_exists( 'upwc_sticky_atc_enabled' ) ) :
/**
 * @return bool
 */
function upwc_sticky_atc_enabled() {
	if ( function_exists( 'upwc_wc_catalog_locked' ) && upwc_wc_catalog_locked() ) {
		return false;
	}
	if ( function_exists( 'upwc_wc_catalog_mode' ) && upwc_wc_catalog_mode() ) {
		// In a lookbook there is no button to be sticky about.
		return false;
	}
	if ( ! function_exists( 'fw_get_db_ext_settings_option' ) ) {
		return false;
	}

	return upwc_wc_truthy( fw_get_db_ext_settings_option( 'woocommerce', 'sticky_atc' ) );
}
endif;

if ( ! function_exists( 'upwc_sticky_atc_bar' ) ) :
/**
 * The bar itself. Hidden until the script decides the real add-to-cart has gone
 * off screen, so it never covers the button it is standing in for.
 *
 * For a variable product the bar's button scrolls back to the form rather than
 * pretending to add something — the visitor still has to choose a variation, and
 * a bar that silently failed would be worse than one that takes them there.
 *
 * @internal
 */
function upwc_sticky_atc_bar() {
	global $product;

	if ( ! is_product() || ! upwc_sticky_atc_enabled() || ! $product instanceof WC_Product ) {
		return;
	}
	if ( ! $product->is_purchasable() && ! $product->is_type( 'external' ) ) {
		return;
	}

	$settings = function_exists( 'fw_get_db_ext_settings_option' ) ? 'fw_get_db_ext_settings_option' : null;
	$position = $settings ? (string) call_user_func( $settings, 'woocommerce', 'sticky_atc_position' ) : 'bottom';
	$position = ( 'top' === $position ) ? 'top' : 'bottom';
	$show_img = ! $settings || upwc_wc_truthy( call_user_func( $settings, 'woocommerce', 'sticky_atc_image' ) );

	$simple = $product->is_type( 'simple' ) && $product->is_purchasable() && $product->is_in_stock();
	?>
	<div class="upwc-sticky-atc upwc-sticky-atc--<?php echo esc_attr( $position ); ?>" hidden>
		<div class="upwc-sticky-atc__inner">
			<?php if ( $show_img && $product->get_image_id() ) : ?>
				<span class="upwc-sticky-atc__media"><?php echo $product->get_image( 'woocommerce_gallery_thumbnail' ); // phpcs:ignore ?></span>
			<?php endif; ?>

			<span class="upwc-sticky-atc__text">
				<span class="upwc-sticky-atc__name"><?php echo esc_html( $product->get_name() ); ?></span>
				<?php if ( $product->get_price_html() ) : ?>
					<span class="upwc-sticky-atc__price"><?php echo $product->get_price_html(); // phpcs:ignore ?></span>
				<?php endif; ?>
			</span>

			<?php if ( $simple ) : ?>
				<a href="<?php echo esc_url( $product->add_to_cart_url() ); ?>"
				   class="button upwc-sticky-atc__button add_to_cart_button ajax_add_to_cart product_type_simple"
				   data-product_id="<?php echo (int) $product->get_id(); ?>"
				   data-quantity="1"
				   rel="nofollow"><?php echo esc_html( $product->add_to_cart_text() ); ?></a>
			<?php else : ?>
				<button type="button" class="button upwc-sticky-atc__button upwc-sticky-atc__button--scroll">
					<?php echo esc_html( $product->add_to_cart_text() ); ?>
				</button>
			<?php endif; ?>
		</div>
	</div>
	<?php
}
endif;
add_action( 'wp_footer', 'upwc_sticky_atc_bar', 30 );

/* -------------------------------------------------------------------------- *
 * Size guide
 * -------------------------------------------------------------------------- */

if ( ! function_exists( 'upwc_size_guide_enabled' ) ) :
/**
 * @return bool
 */
function upwc_size_guide_enabled() {
	if ( ! function_exists( 'fw_get_db_ext_settings_option' ) ) {
		return false;
	}

	return upwc_wc_truthy( fw_get_db_ext_settings_option( 'woocommerce', 'size_guide' ) );
}
endif;

if ( ! function_exists( 'upwc_size_guide_content' ) ) :
/**
 * This product's size guide, or the store-wide default.
 *
 * @param int $product_id
 * @return string
 */
function upwc_size_guide_content( $product_id ) {
	$own = get_post_meta( (int) $product_id, UPWC_SIZE_GUIDE_META, true );
	if ( is_string( $own ) && '' !== trim( $own ) ) {
		return $own;
	}

	return function_exists( 'fw_get_db_ext_settings_option' )
		? (string) fw_get_db_ext_settings_option( 'woocommerce', 'size_guide_content' )
		: '';
}
endif;

if ( ! function_exists( 'upwc_size_guide_link' ) ) :
/**
 * The trigger, printed beside the add-to-cart.
 *
 * @internal
 */
function upwc_size_guide_link() {
	global $product;

	if ( ! upwc_size_guide_enabled() || ! $product instanceof WC_Product ) {
		return;
	}

	$content = upwc_size_guide_content( $product->get_id() );
	if ( '' === trim( wp_strip_all_tags( $content ) ) ) {
		// No content anywhere — a link opening an empty modal is worse than none.
		return;
	}

	$label = function_exists( 'fw_get_db_ext_settings_option' )
		? trim( (string) fw_get_db_ext_settings_option( 'woocommerce', 'size_guide_label' ) )
		: '';
	if ( '' === $label ) {
		$label = __( 'Size guide', 'fw' );
	}
	?>
	<button type="button" class="upwc-size-guide__open" aria-haspopup="dialog">
		<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"
		     stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
			<path d="M2 9h20v6H2z"/><path d="M6 9v3M10 9v4M14 9v3M18 9v4"/>
		</svg>
		<?php echo esc_html( $label ); ?>
	</button>
	<?php
}
endif;

if ( ! function_exists( 'upwc_size_guide_modal' ) ) :
/**
 * The modal, printed once in the footer.
 *
 * @internal
 */
function upwc_size_guide_modal() {
	global $product;

	if ( ! is_product() || ! upwc_size_guide_enabled() || ! $product instanceof WC_Product ) {
		return;
	}

	$content = upwc_size_guide_content( $product->get_id() );
	if ( '' === trim( wp_strip_all_tags( $content ) ) ) {
		return;
	}

	$label = function_exists( 'fw_get_db_ext_settings_option' )
		? trim( (string) fw_get_db_ext_settings_option( 'woocommerce', 'size_guide_label' ) )
		: '';
	if ( '' === $label ) {
		$label = __( 'Size guide', 'fw' );
	}
	?>
	<div class="upwc-size-guide" hidden>
		<div class="upwc-size-guide__backdrop" data-close></div>
		<div class="upwc-size-guide__panel" role="dialog" aria-modal="true" aria-label="<?php echo esc_attr( $label ); ?>">
			<button type="button" class="upwc-size-guide__close" data-close aria-label="<?php esc_attr_e( 'Close', 'fw' ); ?>">&times;</button>
			<h2 class="upwc-size-guide__title"><?php echo esc_html( $label ); ?></h2>
			<div class="upwc-size-guide__content"><?php echo wp_kses_post( wpautop( $content ) ); ?></div>
		</div>
	</div>
	<?php
}
endif;
add_action( 'wp_footer', 'upwc_size_guide_modal', 30 );

/* -------------------------------------------------------------------------- *
 * Per-product size guide (product edit screen)
 * -------------------------------------------------------------------------- */

if ( ! function_exists( 'upwc_size_guide_metabox' ) ) :
/**
 * @internal
 */
function upwc_size_guide_metabox() {
	if ( ! upwc_size_guide_enabled() ) {
		return;
	}

	add_meta_box(
		'upwc-size-guide',
		__( 'Size Guide', 'fw' ),
		'upwc_size_guide_metabox_render',
		'product',
		'normal',
		'default'
	);
}
endif;
add_action( 'add_meta_boxes', 'upwc_size_guide_metabox' );

if ( ! function_exists( 'upwc_size_guide_metabox_render' ) ) :
/**
 * @param WP_Post $post
 * @internal
 */
function upwc_size_guide_metabox_render( $post ) {
	wp_nonce_field( 'upwc_size_guide_save', 'upwc_size_guide_nonce' );

	$value = get_post_meta( $post->ID, UPWC_SIZE_GUIDE_META, true );
	?>
	<p class="description" style="margin:0 0 .8em">
		<?php esc_html_e( 'Shown in the size-guide modal for this product. Leave empty to use the store-wide default from Unyson+ → WooCommerce → Shopper Tools.', 'fw' ); ?>
	</p>
	<?php
	wp_editor(
		is_string( $value ) ? $value : '',
		'upwc_size_guide_content',
		array( 'textarea_rows' => 8, 'media_buttons' => true, 'teeny' => true )
	);
}
endif;

if ( ! function_exists( 'upwc_size_guide_save' ) ) :
/**
 * @param int $post_id
 * @internal
 */
function upwc_size_guide_save( $post_id ) {
	if ( ! isset( $_POST['upwc_size_guide_nonce'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['upwc_size_guide_nonce'] ) ), 'upwc_size_guide_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$content = isset( $_POST['upwc_size_guide_content'] )
		? wp_kses_post( wp_unslash( $_POST['upwc_size_guide_content'] ) )
		: '';

	if ( '' === trim( wp_strip_all_tags( $content ) ) ) {
		delete_post_meta( $post_id, UPWC_SIZE_GUIDE_META );
	} else {
		update_post_meta( $post_id, UPWC_SIZE_GUIDE_META, $content );
	}
}
endif;
add_action( 'save_post_product', 'upwc_size_guide_save' );

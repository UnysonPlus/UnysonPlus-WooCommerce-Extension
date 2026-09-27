<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * AI Assistant abilities for WooCommerce (the UnysonPlus integration).
 *
 *   woo-settings           read the UnysonPlus shop settings (catalog, catalog mode, wishlist, compare,
 *                          swatches, sticky add-to-cart, back in stock, size guide …) with their schema
 *   woo-settings-update    change them — validated against the settings page's own definitions
 *   woo-list-products      products with price, stock, categories and image
 *   woo-save-product       create or update a SIMPLE product through WooCommerce's own product objects
 *                          (price, sale price, SKU, stock, description, categories, images, ribbon,
 *                          size guide); new products are drafts
 *
 * Nothing here touches orders or customers. Every write is snapshotted first for the AI Assistant's
 * undo-change; restoring a product re-saves it through WooCommerce so its caches and lookup tables
 * match again.
 */

if ( ! function_exists( 'fw_ext_woocommerce_ai_register' ) ) :

	/** @return array id => option — the settings page's leaves. */
	function fw_ext_woocommerce_ai_settings_schema() {
		$ext = fw_ext( 'woocommerce' );
		$out = array();
		foreach ( fw_extract_only_options( (array) $ext->get_settings_options() ) as $id => $opt ) {
			if ( ! in_array( $opt['type'] ?? '', FW_AI_Schema::UI_TYPES, true ) ) {
				$out[ $id ] = $opt;
			}
		}
		return $out;
	}

	/** Product meta keys a save can touch (snapshotted for undo). */
	function fw_ext_woocommerce_ai_product_meta() {
		return array( '_regular_price', '_sale_price', '_price', '_sku', '_stock', '_manage_stock', '_stock_status', '_thumbnail_id', '_product_image_gallery', '_featured', '_upwc_ribbon', '_upwc_size_guide' );
	}

	/**
	 * @param WC_Product $p
	 * @return array
	 */
	function fw_ext_woocommerce_ai_row( $p ) {
		return array(
			'id'             => $p->get_id(),
			'name'           => $p->get_name(),
			'type'           => $p->get_type(),
			'status'         => $p->get_status(),
			'url'            => get_permalink( $p->get_id() ),
			'regular_price'  => $p->get_regular_price(),
			'sale_price'     => $p->get_sale_price(),
			'sku'            => $p->get_sku(),
			'stock_status'   => $p->get_stock_status(),
			'stock_quantity' => $p->get_manage_stock() ? $p->get_stock_quantity() : null,
			'featured'       => $p->get_featured(),
			'categories'     => wp_get_object_terms( $p->get_id(), 'product_cat', array( 'fields' => 'names' ) ),
			'image_id'       => (int) $p->get_image_id(),
			'ribbon'         => (string) get_post_meta( $p->get_id(), '_upwc_ribbon', true ),
		);
	}

	function fw_ext_woocommerce_ai_register() {
		if ( ! function_exists( 'fw_ai_register_ability' ) || ! class_exists( 'WooCommerce' ) || ! fw_ext( 'woocommerce' ) ) {
			return;
		}

		fw_ai_register_ability( 'woo-settings', array(
			'label'       => __( 'Read the shop settings', 'fw' ),
			'description' => 'The UnysonPlus WooCommerce settings — catalog (columns, products per page, sidebar, gallery), behaviour (sale badge, AJAX add to cart, sticky add-to-cart), catalog mode (hide prices / quote requests), shopper tools (wishlist, compare, back-in-stock, swatches, size guide) — each with its type, choices and current value. Switches take "yes" / "no".',
			'permission'  => 'manage_woocommerce',
			'readonly'    => true,
			'execute'     => function () {
				$current = (array) fw_get_db_ext_settings_option( 'woocommerce' );
				$out     = array();
				foreach ( fw_ext_woocommerce_ai_settings_schema() as $id => $opt ) {
					$row = array( 'id' => (string) $id, 'type' => (string) $opt['type'] );
					if ( ! empty( $opt['label'] ) && is_string( $opt['label'] ) ) {
						$row['label'] = wp_strip_all_tags( $opt['label'] );
					}
					if ( in_array( $opt['type'], FW_AI_Schema::CHOICE_TYPES, true ) ) {
						$row['choices'] = array_keys( FW_AI_Schema::flat_choices( $opt['choices'] ?? array() ) );
					}
					$row['value'] = array_key_exists( $id, $current ) ? $current[ $id ] : ( $opt['value'] ?? null );
					$out[]        = $row;
				}
				return array( 'settings' => $out );
			},
		) );

		fw_ai_register_ability( 'woo-settings-update', array(
			'label'       => __( 'Change the shop settings', 'fw' ),
			'description' => 'Changes UnysonPlus WooCommerce settings by id (see woo_settings), e.g. { wishlist: "yes", wishlist_page: "/wishlist/", shop_columns: "4", catalog_mode: "yes" }. Validated against the settings page; live immediately; undo with undo_change.',
			'input'       => array( 'values' => array( 'type' => 'object' ) ),
			'required'    => array( 'values' ),
			'permission'  => 'manage_woocommerce',
			'idempotent'  => true,
			'execute'     => function ( $in ) {
				$schema = fw_ext_woocommerce_ai_settings_schema();
				$values = (array) json_decode( wp_json_encode( $in['values'] ), true );
				$errors = array();
				foreach ( $values as $k => $v ) {
					if ( ! isset( $schema[ $k ] ) ) {
						$errors[] = sprintf( '%s: not a shop setting (see woo_settings).', $k );
						continue;
					}
					FW_AI_Schema::check_deep( $schema[ $k ], $v, $k, $errors );
				}
				if ( $errors || ! $values ) {
					return new WP_Error( 'fw_woo_ai_invalid', 'Nothing was changed: ' . ( $errors ? implode( ' | ', $errors ) : 'pass values.' ) );
				}
				$rev     = fw_ai_snapshot( array( 'options' => array( 'fw_ext_settings_options:woocommerce' ) ), 'unysonplus/woo-settings-update', 'Changed shop settings: ' . implode( ', ', array_keys( $values ) ) );
				$current = (array) fw_get_db_ext_settings_option( 'woocommerce' );
				fw_set_db_ext_settings_option( 'woocommerce', null, array_merge( $current, $values ) );
				return array( 'ok' => true, 'message' => 'Shop settings saved: ' . implode( ', ', array_keys( $values ) ) . '.', 'undo_revision_id' => $rev );
			},
		) );

		fw_ai_register_ability( 'woo-list-products', array(
			'label'       => __( 'List products', 'fw' ),
			'description' => 'Products (drafts included, up to 100) with type, status, prices, SKU, stock, featured flag, categories, image id and ribbon; search filters by name. Also the existing product categories.',
			'input'       => array( 'search' => array( 'type' => 'string' ) ),
			'permission'  => 'edit_products',
			'readonly'    => true,
			'execute'     => function ( $in ) {
				$args = array( 'limit' => 100, 'status' => array( 'publish', 'draft', 'pending', 'private' ), 'orderby' => 'date', 'order' => 'DESC' );
				if ( ! empty( $in['search'] ) ) {
					$args['s'] = (string) $in['search'];
				}
				return array(
					'products'   => array_map( 'fw_ext_woocommerce_ai_row', wc_get_products( $args ) ),
					'categories' => get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => false, 'fields' => 'names' ) ),
				);
			},
		) );

		fw_ai_register_ability( 'woo-save-product', array(
			'label'       => __( 'Create or update a product', 'fw' ),
			'description' => 'Creates a SIMPLE product (omit product_id; a DRAFT unless status says otherwise) or updates any product\'s basics: name, regular_price / sale_price (numbers as strings, e.g. "24.00"; sale below regular), sku (unique), manage_stock + stock_quantity (or stock_status instock | outofstock | onbackorder), description / short_description (HTML), categories (names, created if new), image_id and gallery_ids (Media Library ids), featured, ribbon (a small label such as "Best Seller"), size_guide (HTML shown by the size-guide tool). Variable products and orders are out of scope. Undo with undo_change.',
			'input'       => array(
				'product_id'        => array( 'type' => 'integer' ),
				'name'              => array( 'type' => 'string' ),
				'status'            => array( 'type' => 'string', 'enum' => array( 'draft', 'publish', 'pending', 'private' ) ),
				'regular_price'     => array( 'type' => 'string' ),
				'sale_price'        => array( 'type' => 'string' ),
				'sku'               => array( 'type' => 'string' ),
				'manage_stock'      => array( 'type' => 'boolean' ),
				'stock_quantity'    => array( 'type' => 'integer', 'minimum' => 0 ),
				'stock_status'      => array( 'type' => 'string', 'enum' => array( 'instock', 'outofstock', 'onbackorder' ) ),
				'description'       => array( 'type' => 'string' ),
				'short_description' => array( 'type' => 'string' ),
				'categories'        => array( 'type' => 'array', 'items' => array( 'type' => 'string' ) ),
				'image_id'          => array( 'type' => 'integer', 'minimum' => 0 ),
				'gallery_ids'       => array( 'type' => 'array', 'items' => array( 'type' => 'integer' ) ),
				'featured'          => array( 'type' => 'boolean' ),
				'ribbon'            => array( 'type' => 'string' ),
				'size_guide'        => array( 'type' => 'string' ),
			),
			'permission'  => function ( $in ) {
				$id = (int) ( $in['product_id'] ?? 0 );
				return $id ? ( get_post_type( $id ) === 'product' && current_user_can( 'edit_product', $id ) ) : current_user_can( 'edit_products' );
			},
			'execute'     => 'fw_ext_woocommerce_ai_save_product',
		) );
	}
	add_action( 'fw_ai_assistant_register_abilities', 'fw_ext_woocommerce_ai_register' );

	/**
	 * @param array $in
	 * @return array|WP_Error
	 */
	function fw_ext_woocommerce_ai_save_product( $in ) {
		$id = (int) ( $in['product_id'] ?? 0 );

		// Validate before touching anything.
		$errors = array();
		foreach ( array( 'regular_price', 'sale_price' ) as $k ) {
			if ( isset( $in[ $k ] ) && $in[ $k ] !== '' && ! is_numeric( $in[ $k ] ) ) {
				$errors[] = sprintf( '%s must be a number such as "24.00".', $k );
			}
		}
		if ( isset( $in['regular_price'], $in['sale_price'] ) && $in['sale_price'] !== '' && is_numeric( $in['sale_price'] ) && is_numeric( $in['regular_price'] ) && (float) $in['sale_price'] >= (float) $in['regular_price'] ) {
			$errors[] = 'sale_price must be lower than regular_price.';
		}
		foreach ( array_merge( array( (int) ( $in['image_id'] ?? 0 ) ), (array) ( $in['gallery_ids'] ?? array() ) ) as $att ) {
			if ( $att && get_post_type( (int) $att ) !== 'attachment' ) {
				$errors[] = sprintf( '%d is not a Media Library attachment id.', (int) $att );
			}
		}
		if ( ! empty( $in['sku'] ) ) {
			$other = wc_get_product_id_by_sku( (string) $in['sku'] );
			if ( $other && $other !== $id ) {
				$errors[] = sprintf( 'SKU "%s" is already used by product %d.', $in['sku'], $other );
			}
		}
		if ( ! $id && empty( $in['name'] ) ) {
			$errors[] = 'A new product needs a name.';
		}
		$product = $id ? wc_get_product( $id ) : new WC_Product_Simple();
		if ( ! $product ) {
			$errors[] = 'No product with that product_id.';
		}
		if ( $errors ) {
			return new WP_Error( 'fw_woo_ai_invalid', 'Nothing was changed: ' . implode( ' | ', $errors ) );
		}

		if ( $id ) {
			$rev = fw_ai_snapshot( array(
				'post_fields' => array( $id => array( 'post_title', 'post_content', 'post_excerpt', 'post_status' ) ),
				'post_meta'   => array( $id => fw_ext_woocommerce_ai_product_meta() ),
				'post_terms'  => array( $id => array( 'product_cat', 'product_visibility' ) ),
			), 'unysonplus/woo-save-product', sprintf( 'Changed product "%s"', $product->get_name() ) );
		} else {
			$product->set_status( 'draft' );
		}

		if ( isset( $in['name'] ) ) {
			$product->set_name( sanitize_text_field( (string) $in['name'] ) );
		}
		if ( isset( $in['status'] ) ) {
			$product->set_status( (string) $in['status'] );
		}
		foreach ( array( 'regular_price', 'sale_price' ) as $k ) {
			if ( isset( $in[ $k ] ) ) {
				$product->{'set_' . $k}( wc_format_decimal( (string) $in[ $k ] ) );
			}
		}
		if ( isset( $in['sku'] ) ) {
			$product->set_sku( sanitize_text_field( (string) $in['sku'] ) );
		}
		if ( isset( $in['manage_stock'] ) ) {
			$product->set_manage_stock( (bool) $in['manage_stock'] );
		}
		if ( isset( $in['stock_quantity'] ) ) {
			$product->set_manage_stock( true );
			$product->set_stock_quantity( (int) $in['stock_quantity'] );
		}
		if ( isset( $in['stock_status'] ) ) {
			$product->set_stock_status( (string) $in['stock_status'] );
		}
		if ( isset( $in['description'] ) ) {
			$product->set_description( wp_kses_post( (string) $in['description'] ) );
		}
		if ( isset( $in['short_description'] ) ) {
			$product->set_short_description( wp_kses_post( (string) $in['short_description'] ) );
		}
		if ( isset( $in['image_id'] ) ) {
			$product->set_image_id( (int) $in['image_id'] );
		}
		if ( isset( $in['gallery_ids'] ) ) {
			$product->set_gallery_image_ids( array_map( 'intval', (array) $in['gallery_ids'] ) );
		}
		if ( isset( $in['featured'] ) ) {
			$product->set_featured( (bool) $in['featured'] );
		}
		if ( isset( $in['categories'] ) ) {
			$ids = array();
			foreach ( (array) $in['categories'] as $name ) {
				$name = sanitize_text_field( (string) $name );
				$term = get_term_by( 'name', $name, 'product_cat' );
				if ( ! $term ) {
					$made = wp_insert_term( $name, 'product_cat' );
					$ids[] = is_wp_error( $made ) ? 0 : (int) $made['term_id'];
				} else {
					$ids[] = (int) $term->term_id;
				}
			}
			$product->set_category_ids( array_values( array_filter( $ids ) ) );
		}
		$saved = $product->save();
		if ( ! $saved ) {
			return new WP_Error( 'fw_woo_ai_save', 'WooCommerce could not save the product.' );
		}
		if ( ! $id ) {
			$rev = fw_ai_snapshot( array( 'created_posts' => array( $saved ) ), 'unysonplus/woo-save-product', sprintf( 'Created product "%s"', $product->get_name() ) );
		}
		if ( isset( $in['ribbon'] ) ) {
			$in['ribbon'] !== '' ? update_post_meta( $saved, '_upwc_ribbon', sanitize_text_field( (string) $in['ribbon'] ) ) : delete_post_meta( $saved, '_upwc_ribbon' );
		}
		if ( isset( $in['size_guide'] ) ) {
			$in['size_guide'] !== '' ? update_post_meta( $saved, '_upwc_size_guide', wp_kses_post( (string) $in['size_guide'] ) ) : delete_post_meta( $saved, '_upwc_size_guide' );
		}
		return array(
			'ok'               => true,
			'message'          => sprintf( '%s product "%s".', $id ? 'Updated' : 'Created', $product->get_name() ),
			'post_id'          => (int) $saved,
			'undo_revision_id' => $rev,
		) + fw_ext_woocommerce_ai_row( wc_get_product( $saved ) );
	}

	/**
	 * After an AI undo restored a product's meta, re-save it through WooCommerce so its caches and
	 * lookup tables agree with the restored values.
	 *
	 * @param array $rev
	 */
	function fw_ext_woocommerce_ai_after_restore( $rev ) {
		if ( ! function_exists( 'wc_get_product' ) ) {
			return;
		}
		foreach ( array_keys( (array) ( $rev['values']['post_meta'] ?? array() ) ) as $post_id ) {
			if ( get_post_type( (int) $post_id ) === 'product' ) {
				wc_delete_product_transients( (int) $post_id );
				$p = wc_get_product( (int) $post_id );
				if ( $p ) {
					$p->save();
				}
			}
		}
	}
	add_action( 'fw_ai_assistant_change_restored', 'fw_ext_woocommerce_ai_after_restore' );

endif;

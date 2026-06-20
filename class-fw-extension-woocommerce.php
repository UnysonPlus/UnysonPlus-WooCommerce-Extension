<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * WooCommerce integration extension.
 *
 * Phase 1 — foundation. Makes the framework WooCommerce-aware and provides a
 * theme-agnostic baseline so any active theme renders WooCommerce reasonably:
 *
 *   - Inert unless WooCommerce is active (class_exists guard in _init()).
 *   - Generic theme-support fallback: if the ACTIVE theme hasn't declared
 *     WooCommerce support itself, this extension declares it (+ the product
 *     gallery features) and ships a small generic stylesheet. When a
 *     WooCommerce-aware theme is active (e.g. unysonplus-theme, which has its
 *     own compat layer), the extension steps aside and the theme leads.
 *
 * Later phases add the page-builder shop elements (shortcodes/), a settings
 * page wired to the theme's unysonplus_woocommerce_* filters, and widgets.
 */
class FW_Extension_Woocommerce extends FW_Extension {

	/**
	 * True when THIS extension declared WooCommerce theme support because the
	 * active theme did not. Gates the generic fallback stylesheet so we never
	 * double up on a theme that already integrates WooCommerce.
	 *
	 * @var bool
	 */
	private $declared_support = false;

	/**
	 * @internal
	 */
	public function _init() {
		// Hide WooCommerce shop elements from the page builder when the
		// WooCommerce plugin isn't active (the extension can be enabled before
		// WooCommerce is installed). Registered unconditionally so the filter
		// applies even on the early-return path below.
		add_filter( 'fw_ext_shortcodes_disable_shortcodes', array( $this, '_filter_disable_shortcodes' ) );

		// Completely inert without WooCommerce — the theme / site is unaffected.
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		// The framework boots inside after_setup_theme (priority 10) and the
		// plugin loads before the theme, so this _init() runs BEFORE the theme's
		// own add_theme_support(). Defer the "did the theme declare support?"
		// check to a late after_setup_theme priority so the theme has had its say.
		add_action( 'after_setup_theme', array( $this, '_action_theme_support_fallback' ), 99 );

		if ( ! is_admin() ) {
			add_action( 'wp_enqueue_scripts', array( $this, '_action_enqueue_generic_styles' ), 20 );
		}

		// Live-refresh the Cart element's count / total via WooCommerce AJAX
		// fragments (works for any [wc_cart_link] currently in the DOM).
		add_filter( 'woocommerce_add_to_cart_fragments', array( $this, '_filter_cart_fragments' ) );

		// Shop behavior settings (catalog mode, breadcrumb, badge style, AJAX cart,
		// product-gallery features).
		add_action( 'after_setup_theme', array( $this, '_action_apply_gallery_settings' ), 100 );
		add_action( 'wp', array( $this, '_action_apply_shop_behavior' ) );
		add_filter( 'woocommerce_enable_ajax_add_to_cart', array( $this, '_filter_ajax_add_to_cart' ) );
		add_filter( 'woocommerce_sale_flash', array( $this, '_filter_sale_flash' ), 10, 3 );

		// AJAX: Products Load More + Quick View.
		add_action( 'wp_ajax_upw_wc_products_load_more', array( $this, '_ajax_load_more' ) );
		add_action( 'wp_ajax_nopriv_upw_wc_products_load_more', array( $this, '_ajax_load_more' ) );
		add_action( 'wp_ajax_upw_wc_quick_view', array( $this, '_ajax_quick_view' ) );
		add_action( 'wp_ajax_nopriv_upw_wc_quick_view', array( $this, '_ajax_quick_view' ) );
	}

	/**
	 * AJAX: return the next page of product cards for the Products grid.
	 *
	 * @internal
	 */
	public function _ajax_load_more() {
		check_ajax_referer( 'upw_wc_products', 'nonce' );

		if ( ! function_exists( 'upw_wc_products_resolve' ) ) {
			wp_send_json_error();
		}

		$atts = isset( $_POST['atts'] ) ? json_decode( wp_unslash( $_POST['atts'] ), true ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		if ( ! is_array( $atts ) ) {
			wp_send_json_error();
		}
		$page = isset( $_POST['page'] ) ? max( 2, (int) $_POST['page'] ) : 2;

		$r               = upw_wc_products_resolve( $atts );
		$r['pagination'] = 'load_more';
		$args            = upw_wc_products_query_args( $r, $page );
		if ( $args === false ) {
			wp_send_json_success( array( 'html' => '', 'has_more' => false ) );
		}

		$query = new WP_Query( $args );
		$html  = '';
		while ( $query->have_posts() ) {
			$query->the_post();
			$html .= upw_wc_products_card( wc_get_product( get_the_ID() ), $r );
		}
		wp_reset_postdata();

		wp_send_json_success(
			array(
				'html'     => $html,
				'has_more' => $page < (int) $query->max_num_pages,
			)
		);
	}

	/**
	 * AJAX: return the Quick View contents for a product.
	 *
	 * @internal
	 */
	public function _ajax_quick_view() {
		check_ajax_referer( 'upw_wc_products', 'nonce' );

		$id      = isset( $_POST['product'] ) ? (int) $_POST['product'] : 0;
		$product = $id ? wc_get_product( $id ) : null;
		if ( ! $product instanceof WC_Product || $product->get_status() !== 'publish' ) {
			wp_send_json_error();
		}

		$GLOBALS['post'] = get_post( $id );
		setup_postdata( $GLOBALS['post'] );
		$GLOBALS['product'] = $product;

		ob_start();
		?>
		<div class="upw-qv__media"><?php echo $product->get_image( 'woocommerce_single' ); // phpcs:ignore ?></div>
		<div class="upw-qv__summary">
			<h2 class="upw-qv__title"><?php echo esc_html( $product->get_name() ); ?></h2>
			<?php if ( (float) $product->get_average_rating() > 0 ) : ?>
				<div class="upw-qv__rating"><?php echo wc_get_rating_html( $product->get_average_rating() ); // phpcs:ignore ?></div>
			<?php endif; ?>
			<div class="upw-qv__price"><?php echo $product->get_price_html(); // phpcs:ignore ?></div>
			<div class="upw-qv__excerpt"><?php echo wp_kses_post( wpautop( $product->get_short_description() ) ); ?></div>
			<?php woocommerce_template_single_add_to_cart(); ?>
			<a class="upw-qv__link" href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php esc_html_e( 'View full details', 'fw' ); ?></a>
		</div>
		<?php
		$html = ob_get_clean();
		wp_reset_postdata();

		wp_send_json_success( array( 'html' => $html ) );
	}

	/**
	 * Remove product-gallery theme support for any feature disabled in settings.
	 * Runs after the theme (p10) and the support fallback (p99) have declared theirs.
	 *
	 * @internal
	 */
	public function _action_apply_gallery_settings() {
		$map = array(
			'gallery_zoom'     => 'wc-product-gallery-zoom',
			'gallery_lightbox' => 'wc-product-gallery-lightbox',
			'gallery_slider'   => 'wc-product-gallery-slider',
		);
		foreach ( $map as $key => $feature ) {
			$value = $this->get_setting( $key, null );
			if ( $value !== null && ! upw_wc_truthy( $value ) ) {
				remove_theme_support( $feature );
			}
		}
	}

	/**
	 * Apply catalog mode + breadcrumb toggle once the main query is known.
	 *
	 * @internal
	 */
	public function _action_apply_shop_behavior() {
		if ( $this->get_setting( 'show_breadcrumb', 'yes' ) !== 'yes' ) {
			remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
		}

		if ( upw_wc_truthy( $this->get_setting( 'catalog_mode', 'no' ) ) ) {
			remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
			remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
			remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
			remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
		}
	}

	/**
	 * Override AJAX add-to-cart on shop archives from the setting (when set).
	 *
	 * @param bool $enabled
	 * @return bool
	 * @internal
	 */
	public function _filter_ajax_add_to_cart( $enabled ) {
		$value = $this->get_setting( 'ajax_add_to_cart', null );
		return $value === null ? $enabled : upw_wc_truthy( $value );
	}

	/**
	 * Show the discount percentage in the sale flash when the setting asks for it.
	 *
	 * @param string $html
	 * @param WP_Post $post
	 * @param WC_Product $product
	 * @return string
	 * @internal
	 */
	public function _filter_sale_flash( $html, $post, $product ) {
		if ( $this->get_setting( 'sale_badge_style', 'text' ) !== 'percent' || ! $product instanceof WC_Product ) {
			return $html;
		}
		$regular = (float) $product->get_regular_price();
		$sale    = (float) $product->get_sale_price();
		if ( $regular > 0 && $sale > 0 && $sale < $regular ) {
			$pct = (int) round( ( $regular - $sale ) / $regular * 100 );
			return '<span class="onsale">-' . $pct . '%</span>';
		}
		return $html;
	}

	/**
	 * Provide cart-count / cart-total fragments so the Cart element updates
	 * without a page reload when items are added via AJAX.
	 *
	 * @param array $fragments
	 * @return array
	 * @internal
	 */
	public function _filter_cart_fragments( $fragments ) {
		if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
			return $fragments;
		}
		$count = (int) WC()->cart->get_cart_contents_count();
		$total = WC()->cart->get_cart_total();

		$fragments['.upw-cart .upw-cart__count']         = '<span class="upw-cart__count" aria-hidden="true">' . esc_html( $count ) . '</span>';
		$fragments['.upw-cart .upw-cart__total']         = '<span class="upw-cart__total">' . wp_kses_post( $total ) . '</span>';
		$fragments['.upw-minicart .upw-minicart__count'] = '<span class="upw-minicart__count" aria-hidden="true">' . esc_html( $count ) . '</span>';

		if ( function_exists( 'upw_wc_free_shipping_bar_html' ) ) {
			$bar = upw_wc_free_shipping_bar_html();
			if ( $bar !== '' ) {
				$fragments['.upw-freeship .upw-freeship__inner'] = $bar;
			}
		}

		return $fragments;
	}

	/**
	 * Disable this extension's shop shortcodes when WooCommerce is not active,
	 * so they don't show in the page-builder element list (and can't render).
	 *
	 * @param array $disabled Shortcode tags to disable.
	 * @return array
	 * @internal
	 */
	public function _filter_disable_shortcodes( $disabled ) {
		if ( ! class_exists( 'WooCommerce' ) ) {
			$disabled = array_merge(
				$disabled,
				array(
					'wc_products',
					'wc_cart_link',
					'wc_product_categories',
					'wc_product',
					'wc_product_page',
					'wc_add_to_cart',
					'wc_cart',
					'wc_checkout',
					'wc_my_account',
					'wc_order_tracking',
					'wc_product_search',
					'wc_mini_cart',
					'wc_product_filters',
					'wc_account',
					'wc_free_shipping',
				)
			);
		}
		return $disabled;
	}

	/**
	 * Declare WooCommerce theme support only when the active theme hasn't.
	 *
	 * @internal
	 */
	public function _action_theme_support_fallback() {
		if ( current_theme_supports( 'woocommerce' ) ) {
			// A WooCommerce-aware theme is active (e.g. unysonplus-theme). It
			// owns the wrapper / layout integration; we feed our catalog
			// settings into ITS filters rather than WooCommerce's directly.
			$this->register_catalog_settings_bridge( false );
			return;
		}

		add_theme_support( 'woocommerce' );
		add_theme_support( 'wc-product-gallery-zoom' );
		add_theme_support( 'wc-product-gallery-lightbox' );
		add_theme_support( 'wc-product-gallery-slider' );

		$this->declared_support = true;

		// No theme integration present — drive WooCommerce's own filters.
		$this->register_catalog_settings_bridge( true );
	}

	/**
	 * Read one WooCommerce-extension setting, falling back to $default when the
	 * stored value is empty.
	 *
	 * @param string $key
	 * @param mixed  $default
	 * @return mixed
	 */
	public function get_setting( $key, $default = null ) {
		$value = fw_get_db_ext_settings_option( $this->get_name(), $key );
		return ( $value === null || $value === '' ) ? $default : $value;
	}

	/**
	 * Bridge the catalog settings to the right filters.
	 *
	 * @param bool $fallback True when a non-WooCommerce-aware theme is active,
	 *                       so we hook WooCommerce's own filters; false when a
	 *                       theme exposes the unysonplus_woocommerce_* contract.
	 */
	private function register_catalog_settings_bridge( $fallback ) {
		$columns    = (int) $this->get_setting( 'shop_columns', 3 );
		$per_page   = (int) $this->get_setting( 'products_per_page', 12 );
		$sidebar    = (string) $this->get_setting( 'shop_sidebar', 'none' );
		$related    = (int) $this->get_setting( 'related_count', 3 );
		$thumb_cols = (int) $this->get_setting( 'gallery_thumbnail_columns', 4 );

		if ( $fallback ) {
			add_filter( 'loop_shop_columns', static function () use ( $columns ) {
				return $columns;
			} );
			add_filter( 'loop_shop_per_page', static function () use ( $per_page ) {
				return $per_page;
			}, 20 );
			add_filter( 'woocommerce_product_thumbnails_columns', static function () use ( $thumb_cols ) {
				return $thumb_cols;
			} );
			add_filter( 'woocommerce_output_related_products_args', static function ( $args ) use ( $related, $columns ) {
				$args['posts_per_page'] = $related;
				$args['columns']        = $columns;
				return $args;
			} );
			return;
		}

		add_filter( 'unysonplus_woocommerce_loop_columns', static function () use ( $columns ) {
			return $columns;
		} );
		add_filter( 'unysonplus_woocommerce_products_per_page', static function () use ( $per_page ) {
			return $per_page;
		} );
		add_filter( 'unysonplus_woocommerce_sidebar', static function () use ( $sidebar ) {
			return $sidebar;
		} );
		add_filter( 'unysonplus_woocommerce_related_count', static function () use ( $related ) {
			return $related;
		} );
		add_filter( 'unysonplus_woocommerce_thumbnail_columns', static function () use ( $thumb_cols ) {
			return $thumb_cols;
		} );
	}

	/**
	 * Whether the extension supplied the WooCommerce theme support itself
	 * (i.e. the active theme has no WooCommerce integration of its own).
	 *
	 * @return bool
	 */
	public function declared_support() {
		return $this->declared_support;
	}

	/**
	 * Enqueue the generic fallback stylesheet — ONLY for themes that don't
	 * integrate WooCommerce on their own (otherwise the theme's styles lead).
	 *
	 * @internal
	 */
	public function _action_enqueue_generic_styles() {
		if ( ! $this->declared_support ) {
			return;
		}

		$uri = $this->locate_css_URI( 'style' );
		if ( $uri ) {
			wp_enqueue_style(
				'fw-ext-woocommerce-generic',
				$uri,
				array(),
				$this->manifest->get_version()
			);
		}
	}
}

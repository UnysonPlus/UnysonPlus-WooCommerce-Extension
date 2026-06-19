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

		$fragments['.upw-cart .upw-cart__count'] = '<span class="upw-cart__count" aria-hidden="true">' . esc_html( $count ) . '</span>';
		$fragments['.upw-cart .upw-cart__total'] = '<span class="upw-cart__total">' . wp_kses_post( $total ) . '</span>';

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
			$disabled[] = 'wc_products';
			$disabled[] = 'wc_cart_link';
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

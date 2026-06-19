<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$manifest = [];

$manifest['name']        = __( 'WooCommerce', 'fw' );
$manifest['slug']        = 'unysonplus-woocommerce';
$manifest['description'] = __(
	'Integrates WooCommerce with the Unyson+ framework. Makes any active theme WooCommerce-aware and (in later phases) adds page-builder shop elements, a settings page, and widgets. Inert until WooCommerce is installed and active.',
	'fw'
);

$manifest['version']     = '1.0.5';
$manifest['display']     = true;
$manifest['standalone']  = true;

// Repository Info
$manifest['github_update'] = 'UnysonPlus/UnysonPlus-Woocommerce-Extension';
$manifest['github_repo']   = 'https://github.com/UnysonPlus/UnysonPlus-Woocommerce-Extension';
$manifest['github_branch'] = 'master';

// Author Info
$manifest['author']     = 'UnysonPlus';
$manifest['author_uri'] = 'https://www.lastimosa.com.ph/unysonplus';

// Meta
$manifest['license']      = 'GPL-2.0-or-later';
$manifest['text_domain']  = 'fw';
$manifest['requires_php'] = '7.4';
$manifest['requires_wp']  = '5.8';

// NOTE: a `requirements => [ 'extensions' => [ 'shortcodes' => [] ] ]` gate will
// be added in Phase 2, when this extension ships its page-builder shop elements
// (which depend on the shortcodes extension). Phase 1 only provides the
// theme-agnostic WooCommerce-support fallback, which has no such dependency.

/**
 * Changelog
 * -----------------------------------------------------------------------------
 * 1.0.4 - Added the "Cart" element (Header/Footer Elements tab): a cart icon
 *         (bag / cart / basket) with an optional live item-count badge and
 *         total, linking to the cart. The count / total refresh without a page
 *         reload via WooCommerce AJAX fragments (woocommerce_add_to_cart_fragments
 *         + the wc-cart-fragments script). Tag is `wc_cart_link`.
 *
 * 1.0.2 - Added a WooCommerce settings page (Shop Catalog + Single Product
 *         boxes): products per row, products per page, shop sidebar position,
 *         gallery thumbnail columns, and related-products count. The values are
 *         bridged automatically to the active integration — a WooCommerce-aware
 *         theme's unysonplus_woocommerce_* filters when present, otherwise
 *         WooCommerce's own filters (loop_shop_columns, loop_shop_per_page,
 *         woocommerce_product_thumbnails_columns, woocommerce_output_related_products_args)
 *         so the same settings work under any theme.
 *
 * 1.0.1 - Added the "Products" page-builder element (Content Elements tab). It
 *         renders a WooCommerce product grid by source — recent, featured, on
 *         sale, best-selling, top-rated, or by category — with column / gap /
 *         image-ratio / alignment controls and toggles for the sale badge,
 *         star rating, price, and add-to-cart button. Markup is clean and
 *         self-contained (CSS grid, neutralizing WooCommerce's float rules)
 *         while the add-to-cart button stays native so AJAX / variable-product
 *         behavior is preserved. The element is hidden from the builder and
 *         inert on the frontend when the WooCommerce plugin is inactive.
 *
 * 1.0.0 - Initial release (Phase 1 — foundation). Adds a WooCommerce
 *         integration extension that is completely inert until WooCommerce is
 *         active. When active, it makes the framework WooCommerce-aware: if the
 *         CURRENT theme has not declared WooCommerce support itself, the
 *         extension declares it (plus the product-gallery zoom/lightbox/slider
 *         features) and enqueues a small generic stylesheet, so any theme
 *         renders a reasonable shop out of the box. When a WooCommerce-aware
 *         theme is active (e.g. unysonplus-theme, which ships its own wrapper /
 *         sidebar / styling compat layer), the extension steps aside and the
 *         theme leads. Later phases add page-builder shop elements, a settings
 *         page wired to the theme's unysonplus_woocommerce_* filters, and
 *         widgets.
 */

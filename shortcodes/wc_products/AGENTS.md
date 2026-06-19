---
type: shortcode
name: wc_products
since: woocommerce extension 1.0.1
provides: content-element
requires: WooCommerce
---

# Products (`[wc_products]`)

Renders a grid of WooCommerce products. **Tag is `wc_products`, NOT `products`** —
WooCommerce core already registers a `[products]` shortcode, and Unyson registers
its shortcodes at `init:11` (after WC's `init:10`), so an un-prefixed `products`
folder would silently clobber WooCommerce's own shortcode site-wide. **All WC
elements in this extension must be `wc_`-prefixed** to avoid collisions with WC's
core tags (`products`, `product`, `product_category`, `add_to_cart`,
`featured_products`, `sale_products`, `best_selling_products`, `top_rated_products`, …). A **simple content element** (lives in a
column, no class file) — distinct from the section-like recipe in
`framework/extensions/shortcodes/shortcodes/AGENTS.md`. It queries products by a
chosen source and outputs clean, self-contained card markup using `WC_Product`
methods, while keeping the **native** add-to-cart button (`woocommerce_template_loop_add_to_cart()`)
so AJAX / variable-product behavior is preserved.

Ships in the **WooCommerce extension** (`framework/extensions/woocommerce/shortcodes/wc_products/`),
auto-discovered by the shortcodes loader because it iterates every active
extension's `shortcodes/` folder. Available in the builder only when the
WooCommerce *extension* is active; hidden (via `fw_ext_shortcodes_disable_shortcodes`)
and inert in `view.php` when the WooCommerce *plugin* is not loaded.

## Options schema (atts)

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `source` | select | `recent` | `recent` \| `featured` \| `sale` \| `best_selling` \| `top_rated` \| `category` |
| `category` | select | `''` | product_cat **slug**; `''` = all. Selects the category for `source=category`, otherwise narrows the result set. |
| `posts_per_page` | text (int) | `8` | Count; `-1` = all. |
| `orderby` | select | `date` | `date` \| `title` \| `price` \| `popularity` \| `rating` \| `menu_order` \| `rand`. Ignored by `best_selling` / `top_rated` / `sale` which set their own order. |
| `order` | select | `DESC` | `DESC` \| `ASC`. |
| `show_sale_badge` | switch | `yes` | `yes` \| `no`. |
| `show_rating` | switch | `yes` | `yes` \| `no`. |
| `show_price` | switch | `yes` | `yes` \| `no`. |
| `show_add_to_cart` | switch | `yes` | `yes` \| `no`. |
| `columns` | select | `4` | `2`–`6` desktop columns. |
| `gap` | select | `md` | `sm` \| `md` \| `lg`. |
| `image_ratio` | select | `auto` | `auto` \| `square` \| `portrait` \| `landscape`. |
| `alignment` | alignment / select | `''` | `''` (inherit) \| `left` \| `center` \| `right`. |

Plus the shared **Animations** (`sc_get_animation_fields()`) and **Advanced**
(`sc_get_advanced_tab()`) tabs.

## Rendering

`views/view.php` builds a `WP_Query` (post_type `product`, visibility-filtered),
mapping `source` → tax/meta query (featured → `product_visibility:featured`,
sale → `wc_get_product_ids_on_sale()`, best_selling/top_rated → `total_sales` /
`_wc_average_rating` meta order). Markup:

```
.upw-products.upw-products--gap-*.upw-products--ratio-*[.upw-products--align-*]
  ul.products.upw-products__grid.upw-products--cols-N
    li.product.upw-product
      a.upw-product__link  (badge + media + title)
      .upw-product__rating .upw-product__price .upw-product__cart
```

`styles.css` lays this out as CSS grid and **neutralizes WooCommerce's float
rules** on the shared `ul.products` element. Sale source returns nothing when
no products are on sale (no empty grid).

## Pitfalls

1. **Switch values are `yes`/`no`** (explicit `left-choice`/`right-choice` in
   `options.php`) — `view.php`'s `$truthy` also tolerates `true`/`1`. Missing
   att defaults to ON.
2. **`ul.products` is shared with WooCommerce** — its float/clearfix CSS must be
   neutralized (done in `styles.css`); don't drop those `!important` resets.
3. **Inert without WooCommerce** — `view.php` bails on `! class_exists('WooCommerce')`
   because the element can persist in saved content after the plugin is disabled.
4. **`$GLOBALS['product']` must be set** before `woocommerce_template_loop_add_to_cart()`
   — it reads the global, not the loop post.

## Files

- `config.php` — page-builder config (Content Elements tab, title template)
- `options.php` — edit-modal fields (atts schema above)
- `static.php` — enqueues `static/css/styles.css`
- `views/view.php` — query + render
- `static/css/styles.css` — grid + card styles
- `static/img/page_builder.svg` — 16×16 builder icon

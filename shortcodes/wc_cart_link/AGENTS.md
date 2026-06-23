---
type: shortcode
name: wc_cart_link
since: woocommerce extension 1.0.4
provides: header-footer-element
requires: WooCommerce
---

# Cart (`[wc_cart_link]`)

A cart icon (bag / cart / basket) with an optional live **item-count badge** and
**total**, linking to the cart page. Appears under the builder's **Header/Footer
Elements** tab, so it drops into the header/footer builder (its primary use), but
works in normal content too. `wc_`-prefixed to avoid WooCommerce's core
`[woocommerce_cart]` and other tags.

## Live updates

The count / total carry classes (`.upwc-cart__count`, `.upwc-cart__total`) that the
extension's `woocommerce_add_to_cart_fragments` filter
(`class-fw-extension-woocommerce.php::_filter_cart_fragments`) refreshes via
WooCommerce's `wc-cart-fragments` script (enqueued in `static.php`). So adding a
product via AJAX updates the badge with no reload — **as long as the element is in
the DOM**. Keep `hide_when_empty` OFF if you want the badge to appear when the
first item is added off-screen.

## Options schema (atts)

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `icon` | select | `bag` | `bag` \| `cart` \| `basket` \| `none`. |
| `label` | text | `''` | Optional text beside the icon. |
| `show_count` | switch | `yes` | `yes` \| `no` — item-count badge. |
| `show_total` | switch | `no` | `yes` \| `no` — cart total amount. |
| `hide_when_empty` | switch | `no` | `yes` \| `no` — hide element while cart empty. |

Plus shared **Animations** / **Advanced** tabs (wrapper id/class via
`sc_build_wrapper_attr`).

## Rendering

`<a class="upwc-cart" href="{cart}"> [label] <span class="upwc-cart__icon">{svg}
<span class="upwc-cart__count">N</span></span> [<span class="upwc-cart__total">…]</a>`.
Icons are inline stroke SVGs using `currentColor`. The badge uses a fixed accent
(`--upwc-cart-badge-bg`/`-fg` custom props, overridable) so it stays visible on
light- or dark-text headers.

## Pitfalls

1. **Fragment selectors must match the DOM** — `_filter_cart_fragments` keys on
   `.upwc-cart .upwc-cart__count` / `.upwc-cart .upwc-cart__total`; the view must keep
   those classes.
2. **Inert without WooCommerce** — `view.php` bails on `! class_exists('WooCommerce')`;
   also disabled from the builder via `fw_ext_shortcodes_disable_shortcodes`.

## Files

- `config.php` — Header/Footer Elements tab config
- `options.php` — edit-modal fields (atts schema)
- `static.php` — enqueues styles.css + `wc-cart-fragments`
- `views/view.php` — render
- `static/css/styles.css` — icon + badge styles
- `static/img/page_builder.svg` — 16×16 builder icon

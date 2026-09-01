<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$manifest = [];

$manifest['name']        = __( 'WooCommerce', 'fw' );
$manifest['slug']        = 'unysonplus-woocommerce';
$manifest['description'] = __(
	'Integrates WooCommerce with the Unyson+ framework. Makes any active theme WooCommerce-aware and adds a WooCommerce Elements tab to the page builder — product grids & carousels, single product, categories, add-to-cart, cart icon & mini-cart, wishlist, compare, upsells, the cart / checkout / account / order-tracking pages, product search and AJAX filters — plus shopper tools (wishlist, compare, variation swatches, sticky add-to-cart, back-in-stock notifications, size guide), catalog mode, widgets and a shop settings page. Inert until WooCommerce is installed and active.',
	'fw'
);

$manifest['version']     = '1.0.68';
$manifest['display']     = true;
$manifest['standalone']  = true;
$manifest['thumbnail']   = 'thumbnail.svg';

// Repository Info
$manifest['github_update'] = 'UnysonPlus/UnysonPlus-WooCommerce-Extension';
$manifest['github_repo']   = 'https://github.com/UnysonPlus/UnysonPlus-WooCommerce-Extension';
$manifest['github_branch'] = 'master';

// Author Info
$manifest['author']     = 'UnysonPlus';
$manifest['author_uri'] = 'https://www.lastimosa.com.ph/unysonplus';

// Meta
$manifest['license']      = 'GPL-2.0-or-later';
$manifest['text_domain']  = 'fw';
$manifest['requires_php'] = '7.4';
$manifest['requires_wp']  = '5.8';

// The page-builder shop elements in shortcodes/ are registered through the
// shortcodes extension, so it is a hard dependency: without it the elements
// simply would not exist, with nothing on screen explaining why.
$manifest['requirements'] = array(
	'extensions' => array(
		'shortcodes' => array(),
	),
);

/**
 * Changelog
 *
 * 1.0.68 - Settings page reorganised into TABS (Catalog / Behavior / Catalog Mode / Shopper
 *          Tools) — one flat column of boxes had stopped being navigable now that the
 *          extension carries four groups of settings. Every panel lives in one form, so
 *          switching tabs never loses an edit and Save covers the whole page; the tab is
 *          in the URL hash, so a particular tab can be linked. Also declared the
 *          shortcodes extension as a hard requirement in the manifest: the 18 page-builder
 *          elements are registered through it, and without it they silently did not exist.
 *
 * 1.0.67 - New "Upsells" element. Shows the upsells (or cross-sells) of the product being
 *          viewed, so a single-product layout built in the page builder can place them
 *          deliberately instead of accepting wherever the template drops them. Restores
 *          the single-product globals afterwards, so every template hook that runs after
 *          it still sees the right product.
 *
 * 1.0.66 - Classic WIDGETS: Mini Cart, Products and Wishlist. Everything the extension
 *          offered was page-builder only, which left nothing for a theme's real sidebar or
 *          a footer widget area. Each wraps the same renderer its element uses, so a
 *          widget and an element cannot drift apart.
 *
 * 1.0.65 - AJAX filtering for the Product Filters panel (on by default, per element). A
 *          filter widget is a set of links to the same page with query args, so the panel
 *          now FETCHES that URL and swaps in the product list and the panel itself — which
 *          means it works identically on a shop archive, a category, and a builder page
 *          carrying a [wc_products] grid, with no new server endpoint. The browser URL
 *          still updates (linkable, bookmarkable, back/forward works) and every failure
 *          path falls back to an ordinary page load, so a filter can never do nothing.
 *
 * 1.0.64 - SIZE GUIDE: a link beside the add-to-cart opening measurements in a modal, with
 *          per-product content (a Size Guide box on the product editor) falling back to a
 *          store-wide default. The link is not rendered at all when neither carries
 *          content, so it can never open an empty modal.
 *
 * 1.0.63 - STICKY ADD-TO-CART bar on single products: a compact bar with the product name,
 *          price and buy button that slides in once the real add-to-cart has scrolled out
 *          of view — on a long product page everything that persuades someone to buy is
 *          below the control they need. Top or bottom, optional thumbnail. A variable
 *          product's bar scrolls back to the form rather than pretending to add something
 *          before a variation has been chosen.
 *
 * 1.0.62 - VARIATION SWATCHES. Colour dots, image swatches or labelled buttons in place of
 *          the variation dropdowns, chosen from the term data rather than a per-attribute
 *          setting screen (and reading the meta keys the common swatch plugins write, so a
 *          store migrating from one keeps its swatches). Optionally on shop cards too,
 *          where each swatch opens the product with that option preselected. Past a
 *          threshold of options the dropdown stays, because sixty swatches is worse than
 *          the select it replaced. The swatches drive WooCommerce's OWN hidden select, so
 *          variation matching, price, gallery and add-to-cart all behave natively — none
 *          of the variation logic is reimplemented here.
 *
 * 1.0.61 - BACK-IN-STOCK notifications. An out-of-stock product now offers an email field
 *          instead of a dead "Out of stock" line, and everyone who signed up is mailed
 *          automatically the moment it is restocked — including when the restock comes
 *          from a CSV import, the REST API or a refunded order, since it hooks the stock
 *          status rather than the product editor. A variation coming back counts as the
 *          parent being back. The list is cleared before sending, so a second stock change
 *          mid-run cannot send everything twice, and each recipient gets their own message
 *          rather than sharing a BCC with strangers.
 *
 * 1.0.60 - COMPARE. Pick a few products, see them side by side with their price, stock,
 *          rating and every attribute any of them declares. A bar along the bottom of the
 *          screen tracks the selection and the new Compare element renders the table.
 *          Session-lifetime cookie for everyone: the question it answers stops mattering
 *          once it is answered, so there is no list to manage afterwards. Refuses to go
 *          past the configured maximum rather than silently dropping the oldest pick.
 *
 * 1.0.59 - The WISHLIST is real. The "Wishlist Heart" card slot has existed since the Card
 *          Rows engine landed but rendered as a decorative aria-hidden span — a control
 *          that promised something the store could not do. It is now a working toggle on
 *          product cards, shop-loop cards and single products, with a Wishlist element to
 *          view the list, a Wishlist Link header/footer element and a widget. Signed-in
 *          shoppers keep their list on their account; guests keep a 90-day cookie, and it
 *          MERGES into their account when they sign in rather than being lost at the login
 *          step. State is hydrated client-side so the markup stays identical for everyone
 *          and safe to cache.
 *
 * 1.0.58 - Catalog Mode grew the two things a lookbook actually needs. An optional
 *          ENQUIRY BUTTON (Enquiry Button / Text / Link settings) drops into the exact
 *          slot the hidden add-to-cart vacated on shop archives, single products and our
 *          own [wc_products] cards, carrying the product id / name / permalink as query
 *          args so a contact form can prefill (a mailto: link gets them as a subject
 *          instead). And a CLOSED-SHOP MESSAGE setting replaces the Disable-Purchasing
 *          redirect on Cart / Checkout with a message plus a "Continue browsing" link —
 *          someone who followed a link to the cart is told the shop is not taking orders
 *          rather than silently bounced; leaving it empty keeps the redirect. Catalog
 *          Mode also now reaches the [wc_products] element itself: it works by unhooking
 *          WooCommerce's price / add-to-cart templates, which our cards call directly, so
 *          until now a "lookbook" still sold from every page-builder grid on the site.
 *
 * 1.0.57 - The shop settings now have their own "WooCommerce" entry in the Unyson+ admin
 *          menu (FW_Woocommerce_Settings_Page) instead of only being reachable through
 *          Extensions -> WooCommerce -> Settings, which is where nobody looks for a
 *          catalog layout option. Same schema and same store (settings-options.php,
 *          fw_get_db_ext_settings_option), so nothing that reads a setting changes; the
 *          page renders the options natively and saves on the load- hook, and the
 *          Extensions-manager card's Settings link is redirected here via
 *          fw_ext_manager_settings_url so there is one settings screen rather than two.
 *          The menu entry only appears when the WooCommerce plugin is active.
 *
 * 1.0.56 - Catalog Mode gained a "Disable Purchasing" companion switch (Shop Behavior
 *          settings). Catalog Mode alone only unhooks the price / add-to-cart templates,
 *          so a store left in it could still be bought from through a crafted
 *          ?add-to-cart= URL, a cached AJAX button, or a bookmarked /cart/. With the new
 *          switch on, the store is genuinely closed: woocommerce_is_purchasable (and the
 *          variation equivalent) return false, add-to-cart validation refuses every route
 *          including AJAX and the Store API, WooCommerce's own wp_loaded add-to-cart
 *          handler is unhooked and the request parameter scrubbed, price HTML resolves to
 *          an empty string store-wide (so third-party widgets and our own product cards go
 *          quiet too), and the Cart / Checkout pages redirect to the shop — while the
 *          order-received and order-pay endpoints stay reachable so orders placed before
 *          the switch can still be paid and viewed. The shop-only elements (Cart icon,
 *          Mini Cart, Add to Cart button, Cart and Checkout pages) render nothing on the
 *          front end in this state, with an editor notice explaining why, via the new
 *          shared upwc_wc_catalog_locked() helper.
 *
 * 1.0.52 - Product Filters element is now a filter PANEL, not a single widget. [wc_product_filters]
 *          previously rendered one Woo filter widget (price OR rating OR one attribute…) per element.
 *          It now takes an ordered, drag-sortable STACK of filter blocks (add Price, Rating, Active
 *          Filters, and one block per Attribute — e.g. price + Color + Size in one sidebar element),
 *          each with its own optional heading, wrapped in a styled panel: a Panel Title, a Card Box
 *          Style preset skin, optional dividers between blocks, and optional Collapsible blocks (each
 *          title toggles its block, a tiny event-delegated script). The attribute is now picked from a
 *          select of the store's product attributes. Legacy single-filter instances still render via a
 *          back-compat fallback. Filter widgets still only function on shop / product-category pages,
 *          where they filter the listing. Inert when WooCommerce is inactive.
 *
 * 1.0.51 - Product Search element gains layout + style controls. [wc_product_search] had only a
 *          placeholder; it now offers three layouts (Attached button, Button below, Compact icon),
 *          field Shape (default / pill / rounded / square) and Size, a themed submit Button Style
 *          preset (Attached / Below), Width (auto / full) and Alignment, plus a custom Button Label
 *          and Button Icon (icon-picker, default magnifier). Still self-contained, product-scoped
 *          (post_type=product) markup. Inert when WooCommerce is inactive.
 *
 * 1.0.50 - Add to Cart Button element gains the full Button Style system. [wc_add_to_cart] previously
 *          emitted WooCommerce's raw [add_to_cart] (a bare button styled only by the theme). It now
 *          renders our own themed <a> — keeping the WooCommerce AJAX add-to-cart behavior (the
 *          add_to_cart_button / ajax_add_to_cart classes + data attributes; variable / grouped /
 *          external products link to the product page) — while wearing the same Button Style presets,
 *          Size, Shape, Width and Alignment as the [button] shortcode, via the shared
 *          sc_button_style_field() / sc_button_style_atts() helpers. Adds a custom Button Label and a
 *          Show Price (before / after) option. Inert when WooCommerce is inactive.
 *
 * 1.0.49 - Product Categories element rebuilt on the flexible CARD model. [wc_product_categories]
 *          previously emitted WooCommerce's raw [product_categories] (a fixed, unstyled loop grid).
 *          It now composes each category card from the shared Card Rows designer (Image / Name /
 *          Product Count / Button slots) with a live wireframe preview, a Card Box Style preset
 *          (border / corners / shadow / fill + hover), Image Ratio/Size, and Grid controls (columns,
 *          gap, alignment) — the same building blocks as Products, but with category data and its own
 *          lightweight grid stylesheet. Query controls (number, order, parent, specific IDs, hide
 *          empty) stay in the Content tab. Inert when WooCommerce is inactive.
 *
 * 1.0.48 - Single Product element rebuilt on the shared card engine. [wc_product] previously just
 *          emitted WooCommerce's raw [product id] (an unstyled loop tile with no options). It now
 *          renders ONE product through the SAME engine as [wc_products] (upwc_wc_products_card), so
 *          it gains the full Card tab: the Card Rows layout designer + live wireframe preview, Card
 *          Box Style presets, Image Ratio/Size, the shared Rating engine (symbol / colors / size),
 *          badges, Quick View and AJAX add-to-cart. The grid-only concerns (source query, columns,
 *          pagination, carousel) are omitted since they don't apply to one product, so the element
 *          exposes Content (product picker) + Card + Animations + Advanced. The Card-tab option
 *          groups were extracted to a shared helper (upwc_wc_card_option_groups() in helpers.php)
 *          that both elements call, so their card model is one source of truth and can never drift.
 *
 * 1.0.33 - Products element: a LIVE card preview in the builder. The Card tab now shows a schematic
 *          wireframe of the product card above the Card Rows designer, redrawing in real time as you
 *          add / reorder rows and slots and change each row's direction (inline / stacked),
 *          distribution and alignment. It reflects structure — which slots and where — not real
 *          product data; pure client-side (card-preview.js, no AJAX), enqueued only on the
 *          post-edit screens. Helps make the abstract row/slot model legible while editing.
 *
 * 1.0.30 - Products element: the product CARD is now ROWS-ONLY, plus a Box Preset skin. The Card
 *          tab's Classic/Slot "Card Layout" toggle and the per-element PRESENCE switches (Price,
 *          Star Rating, Rating Number, Short Description, Quick View, Add to Cart, Wishlist, Stock)
 *          were REMOVED — they had never been used and double-controlled what the Card Rows designer
 *          already owns. Presence is now simply "is the slot in a row" (a slot renders when it's in a
 *          row and the product has that data; remove the slot to hide it). A new "Card Box Style"
 *          option applies a reusable Box Preset (border, corners, shadow, fill + hover effects) to
 *          every card — the native, on-brand way to skin the card instead of hand-CSS (managed in
 *          Theme Settings -> Components -> Box Presets). The Card tab is now Card Layout (Card Rows +
 *          Box Style + Image Ratio/Size), Badges (which badge TYPES show in the badges slot), and Add
 *          to Cart (label) — the kept toggles configure content within a slot, not presence.
 *
 * 1.0.28 - Products element: an "Image Size" option (Card tab → Card Layout) sets the product
 *          image width — empty = auto (fills the column, unchanged); a width sizes the image on a
 *          slot layout and caps it on the classic layout. It pairs with Image Ratio (ratio owns the
 *          shape, size owns the scale), emitted as a --upwc-img-size custom property on the grid.
 *          The Products options were also reorganised by concern into Content (which products),
 *          Grid (how the grid arranges), and Card (Elements / Badges / Card Layout) tabs — a
 *          UI-only regrouping; every option id is unchanged, so saved values are unaffected.
 *
 * 1.0.24 - Products element: a slot-based Card Layout DESIGNER. Set "Card Layout" (Style tab) to
 *          "Slot layout" to build the product card from an editable, drag-sortable list of ROWS
 *          ("Card Rows" addable-popup). Each row picks & orders its SLOTS (Badge/Ribbon, Wishlist,
 *          Image, Title, Description, Star Rating, Rating Number, Price, Add to Cart, Quick View
 *          — via a multi-select) and sets how they lay out: Direction (inline / stacked), Distribute
 *          (start / center / space-between / end) and Align. It ships seeded with a four-row default
 *          (badge+wishlist header · stacked media+title+desc · rating+score · price+cart), and the
 *          empty "Classic" default keeps the fixed legacy markup for back-compat. Slotted cards
 *          reset the legacy absolute-badge / aspect-ratio CSS so the row layout and the skin control
 *          freely. Also adds a "Rating Number" option (the average score, e.g. 4.9, next to the stars)
 *          and replaces the WooCommerce star markup with our own (no "Rated X out of 5" screen-reader
 *          text leaking into the visible card). The row schema is intentionally the shape the Site
 *          Converter can emit from a captured product card — structure here, visual skin in CSS/tokens.
 * -----------------------------------------------------------------------------
 * 1.0.22 - Mini Cart custom empty state. WooCommerce's mini-cart template has no
 *          hook for its empty branch (just a hardcoded "No products in the cart."),
 *          so the mini-cart (shortcode + element) now renders its own empty block
 *          from options: Empty Icon (icon-picker, incl. emoji), Empty Heading,
 *          Empty Text, Empty Button Label + URL. Power users can instead hook it in
 *          PHP: the upwc_mini_cart_empty action (prepend) or the upwc_mini_cart_empty_html
 *          filter (replace). The block is re-applied to the AJAX cart fragment so it
 *          survives add/remove-to-cart (persisted like the branding labels). All
 *          pieces empty + no hook = the WooCommerce default is kept.
 *
 * 1.0.21 - Mini Cart "Open As" — Dropdown or Drawer. The mini-cart (shortcode
 *          and header/footer element) gains an Open-As choice: the contained
 *          Dropdown flyout, or a right slide-out Drawer side-cart. The drawer is
 *          portaled to <body> (so it escapes any transformed/backdrop-filtered
 *          header ancestor) inside a fixed, viewport-sized clipper whose
 *          overflow:hidden contains the off-screen slide — so a hidden drawer
 *          never adds a horizontal scrollbar. A Backdrop toggle dims the page +
 *          locks body scroll (close via backdrop / X / Esc), a Backdrop Blur
 *          option frosts the page behind it, and the panel offsets below the WP
 *          admin bar when logged in. One shared renderer drives both modes.
 *
 * 1.0.20 - Mini Cart as a native Header/Footer element. The cart is now a
 *          first-class draggable element in the theme's header/footer Add-element
 *          popup (registered via the theme's unysonplus_hf_elements API), not a
 *          custom_html wrapper around the shortcode. Its icon is an icon-picker
 *          (any library glyph, default a shopping bag) so it matches a source
 *          site's cart icon, plus the same Panel Title / Subtotal Label / Checkout
 *          Text / Footnote branding and Open-On / Item-Count controls. Both the
 *          shortcode and the element now render through one shared function,
 *          upwc_render_mini_cart() (includes/mini-cart-render.php), and the
 *          element's CSS/JS are enqueued when a header/footer section uses it.
 *          Registered only when WooCommerce is active, so it's absent otherwise.
 *
 * 1.0.19 - Products element: richer cards. New Content options — Short
 *          Description (shows each product's excerpt), Ribbon Badge (a
 *          per-product corner ribbon read from the "_upwc_ribbon" post meta,
 *          e.g. "Best Seller"), Wishlist Heart (a decorative corner heart), and
 *          a custom Add to Cart Label (e.g. "Add to Basket") applied via a
 *          scoped woocommerce_product_add_to_cart_text filter. The Mini Cart
 *          element also gained Panel Title / Subtotal Label / Checkout Text /
 *          Footnote branding options (relabelled through a scoped gettext filter
 *          that also reapplies on the AJAX fragment refresh).
 *
 * 1.0.14 - Added two elements: Account (a header account link with a dropdown —
 *          login form for guests, account menu + logout for logged-in users)
 *          and Free Shipping Bar (a progress bar toward the free-shipping spend
 *          threshold that updates live via cart fragments).
 *
 * 1.0.13 - Products element: added AJAX "Load More" pagination and a "Quick
 *          View" modal (image, price, rating, short description, add-to-cart,
 *          and a full-details link; variable products supported). The grid's
 *          query + card markup were extracted to includes/products-render.php
 *          so the initial render, Load More and Quick View share identical
 *          output.
 *
 * 1.0.12 - Catalog polish + shop behavior settings. The Products element gained
 *          percentage sale badges and optional Featured / New / Out-of-stock
 *          badges, low-stock ("Only N left") notices, and two new sources:
 *          Recently Viewed and Cross-sells. New "Shop Behavior" settings:
 *          Catalog Mode (hide prices + add-to-cart store-wide), Sale Badge Style
 *          (text or percent), AJAX add-to-cart toggle, shop breadcrumb toggle,
 *          and product-gallery zoom / lightbox / slider toggles.
 *
 * 1.0.9 - Added three utility elements (WooCommerce Elements tab): Product
 *         Search (a product-scoped search form), Mini Cart (an icon with a
 *         live-updating dropdown of cart contents + subtotal), and Product
 *         Filters (a shop price / attribute / rating / active-filter widget).
 *         Also extended the Products element with a Carousel layout (a
 *         dependency-free scroll-snap track with arrows) and three new sources:
 *         By Tag, By Attribute, and Specific Products (by ID).
 *
 * 1.0.8 - Added the commerce-page elements (WooCommerce Elements tab): Cart,
 *         Checkout, My Account, and Order Tracking. Each wraps the matching
 *         classic WooCommerce shortcode so a store's cart / checkout / account
 *         pages can be built in the page builder with surrounding content. Use
 *         them on the pages assigned under WooCommerce → Settings → Advanced.
 *
 * 1.0.7 - Introduced a dedicated "WooCommerce Elements" builder tab and moved
 *         the Products and Cart Icon elements into it. Added four catalog
 *         elements (each a friendly wrapper around the matching WooCommerce
 *         shortcode): Product Categories (category-card grid), Single Product
 *         (one product card), Product Page (full single-product layout), and
 *         Add to Cart Button (standalone buy button with optional price). A
 *         shared helpers.php provides product / category select choices and
 *         ensures WooCommerce's own styles + add-to-cart scripts load wherever
 *         these elements are placed (not just on shop pages).
 *
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

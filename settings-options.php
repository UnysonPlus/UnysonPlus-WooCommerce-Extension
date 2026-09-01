<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
/**
 * WooCommerce extension settings.
 *
 * These values drive the catalog layout, the single-product page, and the
 * storefront behaviors. The extension class reads them and bridges the layout
 * ones to either the theme's `unysonplus_woocommerce_*` filters (when a
 * WooCommerce-aware theme like unysonplus-theme is active) or WooCommerce's own
 * filters (loop_shop_columns, loop_shop_per_page, …) when a non-aware theme is
 * active — see class-fw-extension-woocommerce.php::register_catalog_settings_bridge().
 *
 * Layout: TABS at the top level, each holding one or more `box`es whose fields
 * are wrapped in a border-less `group` (metabox-holder + grouped fields). The
 * settings page (includes/class-fw-woocommerce-settings-page.php) renders one
 * panel per tab; the tabs also render correctly in the Extensions manager's own
 * settings form, so both routes to these settings agree.
 *
 * Adding a setting: put it in the tab it belongs to, give it a real default, and
 * document it in the docs site's extensions/woocommerce/settings.md table.
 */

$cols_choices = array(
	'2' => '2',
	'3' => '3',
	'4' => '4',
	'5' => '5',
	'6' => '6',
);

// Predictable yes/no switch (stored value is always 'yes' or 'no').
$sw = function ( $label, $desc = '', $value = 'no' ) {
	if ( function_exists( 'upwc_wc_switch' ) ) {
		return upwc_wc_switch( $label, $desc, $value );
	}
	return array(
		'type'         => 'switch',
		'label'        => $label,
		'desc'         => $desc,
		'left-choice'  => array( 'value' => 'no', 'label' => __( 'No', 'fw' ) ),
		'right-choice' => array( 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ),
		'value'        => $value,
	);
};

$options = array(

	/* ---------------------------------------------------------------------- *
	 * Catalog — how the shop and product pages are laid out
	 * ---------------------------------------------------------------------- */
	'catalog_tab' => array(
		'title'   => __( 'Catalog', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'catalog_box' => array(
				'title'   => __( 'Shop Catalog', 'fw' ),
				'type'    => 'box',
				'options' => array(
					'group_catalog' => array(
						'type'    => 'group',
						'options' => array(
							'shop_columns'      => array(
								'label'   => __( 'Products per Row', 'fw' ),
								'desc'    => __( 'Columns in the shop / category product grid on desktop.', 'fw' ),
								'type'    => 'select',
								'choices' => $cols_choices,
								'value'   => '3',
							),
							'products_per_page' => array(
								'label' => __( 'Products per Page', 'fw' ),
								'desc'  => __( 'How many products to show before pagination on shop / category pages.', 'fw' ),
								'type'  => 'text',
								'value' => '12',
							),
							'shop_sidebar'      => array(
								'label'   => __( 'Shop Sidebar', 'fw' ),
								'desc'    => __( 'Sidebar position on WooCommerce pages. Applies with a WooCommerce-aware theme (e.g. UnysonPlus Theme); other themes manage their own sidebar.', 'fw' ),
								'type'    => 'select',
								'choices' => array(
									'none'  => __( 'None (full width)', 'fw' ),
									'left'  => __( 'Left', 'fw' ),
									'right' => __( 'Right', 'fw' ),
								),
								'value'   => 'none',
							),
						),
					),
				),
			),

			'single_box' => array(
				'title'   => __( 'Single Product', 'fw' ),
				'type'    => 'box',
				'options' => array(
					'group_single' => array(
						'type'    => 'group',
						'options' => array(
							'gallery_thumbnail_columns' => array(
								'label'   => __( 'Gallery Thumbnail Columns', 'fw' ),
								'desc'    => __( 'Number of thumbnail columns below the main product image.', 'fw' ),
								'type'    => 'select',
								'choices' => $cols_choices,
								'value'   => '4',
							),
							'related_count'             => array(
								'label' => __( 'Related Products', 'fw' ),
								'desc'  => __( 'How many related products to show on a single product page. Use 0 to hide them.', 'fw' ),
								'type'  => 'text',
								'value' => '3',
							),
							'gallery_zoom'              => $sw( __( 'Gallery Zoom', 'fw' ), __( 'Magnify the product image on hover.', 'fw' ), 'yes' ),
							'gallery_lightbox'          => $sw( __( 'Gallery Lightbox', 'fw' ), __( 'Open product images in a fullscreen lightbox.', 'fw' ), 'yes' ),
							'gallery_slider'            => $sw( __( 'Gallery Slider', 'fw' ), __( 'Use a thumbnail slider for the product gallery.', 'fw' ), 'yes' ),
						),
					),
				),
			),
		),
	),

	/* ---------------------------------------------------------------------- *
	 * Behavior — how the shop acts
	 * ---------------------------------------------------------------------- */
	'behavior_tab' => array(
		'title'   => __( 'Behavior', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'behavior_box' => array(
				'title'   => __( 'Shop Behavior', 'fw' ),
				'type'    => 'box',
				'options' => array(
					'group_behavior' => array(
						'type'    => 'group',
						'options' => array(
							'sale_badge_style' => array(
								'label'   => __( 'Sale Badge Style', 'fw' ),
								'desc'    => __( 'How the "Sale" flash shows on shop / product pages.', 'fw' ),
								'type'    => 'select',
								'choices' => array(
									'text'    => __( 'Text ("Sale")', 'fw' ),
									'percent' => __( 'Percent ("-25%")', 'fw' ),
								),
								'value'   => 'text',
							),
							'ajax_add_to_cart' => $sw( __( 'AJAX Add to Cart', 'fw' ), __( 'Add simple products to the cart from shop archives without a page reload.', 'fw' ), 'yes' ),
							'show_breadcrumb'  => $sw( __( 'Shop Breadcrumb', 'fw' ), __( 'Show the WooCommerce breadcrumb above shop / product content.', 'fw' ), 'yes' ),
						),
					),
				),
			),

			'sticky_box' => array(
				'title'   => __( 'Sticky Add to Cart', 'fw' ),
				'type'    => 'box',
				'options' => array(
					'group_sticky' => array(
						'type'    => 'group',
						'options' => array(
							'sticky_atc'          => $sw(
								__( 'Sticky Add to Cart Bar', 'fw' ),
								__( 'On single product pages, slide in a compact bar with the product name, price and an add-to-cart button once the real one has scrolled out of view.', 'fw' ),
								'no'
							),
							'sticky_atc_position' => array(
								'label'   => __( 'Bar Position', 'fw' ),
								'desc'    => __( 'Where the bar appears once it slides in.', 'fw' ),
								'type'    => 'select',
								'choices' => array(
									'bottom' => __( 'Bottom of the screen', 'fw' ),
									'top'    => __( 'Top of the screen', 'fw' ),
								),
								'value'   => 'bottom',
							),
							'sticky_atc_image'    => $sw( __( 'Show Product Image', 'fw' ), __( 'Include a thumbnail in the bar.', 'fw' ), 'yes' ),
						),
					),
				),
			),
		),
	),

	/* ---------------------------------------------------------------------- *
	 * Catalog Mode — lookbook and closed-shop
	 * ---------------------------------------------------------------------- */
	'catalog_mode_tab' => array(
		'title'   => __( 'Catalog Mode', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'catalog_mode_box' => array(
				'title'   => __( 'Catalog Mode', 'fw' ),
				'type'    => 'box',
				'options' => array(
					'group_catalog_mode' => array(
						'type'    => 'group',
						'options' => array(
							'catalog_mode'            => $sw( __( 'Catalog Mode', 'fw' ), __( 'Hide prices and add-to-cart buttons across the shop (turn the store into a lookbook).', 'fw' ), 'no' ),
							'catalog_lock_purchasing' => $sw(
								__( 'Disable Purchasing', 'fw' ),
								__( 'Applies with Catalog Mode on. Goes beyond hiding: makes every product non-purchasable, blocks add-to-cart requests (direct ?add-to-cart= URLs and AJAX included), blanks price output everywhere, and redirects the Cart / Checkout pages to the shop. Order confirmation / pay-for-order links keep working, so existing orders are unaffected.', 'fw' ),
								'no'
							),
							'catalog_closed_notice'   => array(
								'label' => __( 'Closed-Shop Message', 'fw' ),
								'desc'  => __( 'Applies with Disable Purchasing on. Shown in place of the Cart / Checkout page content. Leave empty to redirect those pages to the shop instead (the default).', 'fw' ),
								'type'  => 'textarea',
								'value' => '',
							),
						),
					),
				),
			),

			'enquiry_box' => array(
				'title'   => __( 'Enquiry Button', 'fw' ),
				'type'    => 'box',
				'options' => array(
					'group_enquiry' => array(
						'type'    => 'group',
						'options' => array(
							'catalog_enquiry'       => $sw(
								__( 'Enquiry Button', 'fw' ),
								__( 'Applies with Catalog Mode on. Puts a link where the add-to-cart button used to be — on shop archives and single products — so a lookbook can still take enquiries.', 'fw' ),
								'no'
							),
							'catalog_enquiry_label' => array(
								'label' => __( 'Enquiry Button Text', 'fw' ),
								'desc'  => __( 'Label for the enquiry button.', 'fw' ),
								'type'  => 'text',
								'value' => __( 'Request a Quote', 'fw' ),
							),
							'catalog_enquiry_url'   => array(
								'label' => __( 'Enquiry Link', 'fw' ),
								'desc'  => __( 'Where the enquiry button goes — usually a contact page. The product id, name and permalink are appended as query args (product_id, product, product_url) so a form there can prefill. A mailto: address works too. Required: without it the button is not shown.', 'fw' ),
								'type'  => 'text',
								'value' => '',
							),
						),
					),
				),
			),
		),
	),

	/* ---------------------------------------------------------------------- *
	 * Shopper tools — wishlist, compare, back-in-stock, swatches, size guide
	 * ---------------------------------------------------------------------- */
	'tools_tab' => array(
		'title'   => __( 'Shopper Tools', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'wishlist_box' => array(
				'title'   => __( 'Wishlist', 'fw' ),
				'type'    => 'box',
				'options' => array(
					'group_wishlist' => array(
						'type'    => 'group',
						'options' => array(
							'wishlist'      => $sw(
								__( 'Wishlist', 'fw' ),
								__( 'Let visitors save products with the heart button on product cards and single products. Signed-in shoppers keep their list on their account; guests keep it in a cookie for 90 days, and it merges into their account when they sign in. Show a saved list anywhere with the Wishlist element.', 'fw' ),
								'no'
							),
							'wishlist_page' => array(
								'label' => __( 'Wishlist Page', 'fw' ),
								'desc'  => __( 'URL of the page holding your Wishlist element. Used by the heart\'s "view your list" link and the Wishlist Link header element. Leave empty to omit those links.', 'fw' ),
								'type'  => 'text',
								'value' => '',
							),
						),
					),
				),
			),

			'compare_box' => array(
				'title'   => __( 'Compare', 'fw' ),
				'type'    => 'box',
				'options' => array(
					'group_compare' => array(
						'type'    => 'group',
						'options' => array(
							'compare'      => $sw(
								__( 'Compare', 'fw' ),
								__( 'Add a compare toggle to product cards and single products, and a bar along the bottom of the screen listing what has been picked. The Compare element shows the products side by side with their attributes.', 'fw' ),
								'no'
							),
							'compare_page' => array(
								'label' => __( 'Compare Page', 'fw' ),
								'desc'  => __( 'URL of the page holding your Compare element — the compare bar\'s "Compare" button goes here. Leave empty to omit the button.', 'fw' ),
								'type'  => 'text',
								'value' => '',
							),
							'compare_max'  => array(
								'label' => __( 'Maximum Products', 'fw' ),
								'desc'  => __( 'How many products can be compared at once. More than four rarely fits a phone screen.', 'fw' ),
								'type'  => 'text',
								'value' => '4',
							),
						),
					),
				),
			),

			'stock_box' => array(
				'title'   => __( 'Back in Stock', 'fw' ),
				'type'    => 'box',
				'options' => array(
					'group_stock' => array(
						'type'    => 'group',
						'options' => array(
							'back_in_stock'          => $sw(
								__( 'Back-in-Stock Notifications', 'fw' ),
								__( 'On an out-of-stock product, offer an email field instead of a dead "Out of stock" line. Everyone who signed up is emailed automatically the moment the product is restocked.', 'fw' ),
								'no'
							),
							'back_in_stock_label'    => array(
								'label' => __( 'Sign-up Heading', 'fw' ),
								'desc'  => __( 'Shown above the email field on an out-of-stock product.', 'fw' ),
								'type'  => 'text',
								'value' => __( 'Email me when this is back', 'fw' ),
							),
							'back_in_stock_subject'  => array(
								'label' => __( 'Notification Subject', 'fw' ),
								'desc'  => __( 'Subject of the email sent when the product is restocked. {product} is replaced with its name.', 'fw' ),
								'type'  => 'text',
								'value' => __( '{product} is back in stock', 'fw' ),
							),
						),
					),
				),
			),

			'swatches_box' => array(
				'title'   => __( 'Variation Swatches', 'fw' ),
				'type'    => 'box',
				'options' => array(
					'group_swatches' => array(
						'type'    => 'group',
						'options' => array(
							'swatches'       => $sw(
								__( 'Variation Swatches', 'fw' ),
								__( 'Replace the variation dropdowns on variable products with swatches — colour dots, image thumbnails or labelled buttons, chosen per attribute. Falls back to a dropdown for any attribute with more options than fit.', 'fw' ),
								'no'
							),
							'swatches_cards' => $sw(
								__( 'Swatches on Product Cards', 'fw' ),
								__( 'Also show the swatches on shop / grid cards. Picking one opens the product with that variation already selected.', 'fw' ),
								'no'
							),
							'swatches_shape' => array(
								'label'   => __( 'Swatch Shape', 'fw' ),
								'type'    => 'select',
								'choices' => array(
									'circle' => __( 'Circle', 'fw' ),
									'square' => __( 'Square', 'fw' ),
								),
								'value'   => 'circle',
							),
						),
					),
				),
			),

			'size_guide_box' => array(
				'title'   => __( 'Size Guide', 'fw' ),
				'type'    => 'box',
				'options' => array(
					'group_size_guide' => array(
						'type'    => 'group',
						'options' => array(
							'size_guide'         => $sw(
								__( 'Size Guide', 'fw' ),
								__( 'Add a "Size guide" link beside the add-to-cart on single products, opening your measurements in a modal. Per-product content overrides the default below (Product → Size Guide).', 'fw' ),
								'no'
							),
							'size_guide_label'   => array(
								'label' => __( 'Link Text', 'fw' ),
								'type'  => 'text',
								'value' => __( 'Size guide', 'fw' ),
							),
							'size_guide_content' => array(
								'label' => __( 'Default Size Guide', 'fw' ),
								'desc'  => __( 'Shown for any product without its own. A table works well here.', 'fw' ),
								'type'  => 'wp-editor',
								'value' => '',
							),
						),
					),
				),
			),
		),
	),
);

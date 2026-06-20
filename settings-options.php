<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
/**
 * WooCommerce extension settings.
 *
 * These values drive the catalog layout. The extension class reads them and
 * bridges them to either the theme's `unysonplus_woocommerce_*` filters (when a
 * WooCommerce-aware theme like unysonplus-theme is active) or WooCommerce's own
 * filters (loop_shop_columns, loop_shop_per_page, …) when a non-aware theme is
 * active — see class-fw-extension-woocommerce.php::register_catalog_settings_bridge().
 *
 * Layout follows the house convention: each section is a `box` whose fields are
 * wrapped in a border-less `group` (metabox-holder + grouped fields).
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
	if ( function_exists( 'upw_wc_switch' ) ) {
		return upw_wc_switch( $label, $desc, $value );
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

	'catalog_box' => array(
		'title'   => __( 'Shop Catalog', 'fw' ),
		'type'    => 'box',
		'options' => array(
			'group_catalog' => array(
				'type'    => 'group',
				'options' => array(
					'shop_columns'     => array(
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
					'shop_sidebar'     => array(
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

	'behavior_box' => array(
		'title'   => __( 'Shop Behavior', 'fw' ),
		'type'    => 'box',
		'options' => array(
			'group_behavior' => array(
				'type'    => 'group',
				'options' => array(
					'catalog_mode'     => $sw( __( 'Catalog Mode', 'fw' ), __( 'Hide prices and add-to-cart buttons across the shop (turn the store into a lookbook).', 'fw' ), 'no' ),
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
);

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
				),
			),
		),
	),
);

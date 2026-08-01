<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Product Categories element options.
 *
 * Like [wc_products], the category grid is built on a flexible CARD model: each
 * category card is composed from the shared Card Rows designer (image / name /
 * product count / button), skinned with a Card Box Style preset, with its own
 * Grid controls (columns, gap, alignment) and Image Ratio/Size — instead of Woo's
 * fixed [product_categories] markup. The query controls (which categories) live in
 * the Content tab.
 */

// Category slots available to the Card Rows designer.
$upwc_cat_slots = array(
	'image'  => __( 'Image', 'fw' ),
	'title'  => __( 'Name', 'fw' ),
	'count'  => __( 'Product Count', 'fw' ),
	'button' => __( 'Button (View)', 'fw' ),
);

// Seeded default card: image · name · count (stacked, centered).
$upwc_cat_rows_default = array(
	array( 'slots' => array( 'image' ), 'direction' => 'stack', 'justify' => 'start', 'align' => 'center' ),
	array( 'slots' => array( 'title' ), 'direction' => 'stack', 'justify' => 'start', 'align' => 'center' ),
	array( 'slots' => array( 'count' ), 'direction' => 'stack', 'justify' => 'start', 'align' => 'center' ),
);

$options = array(

	'tab_content' => array(
		'title'   => __( 'Content', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'group_cats' => array(
				'type'    => 'group',
				'options' => array(
					'number'     => array(
						'type'            => 'text',
						'label'           => __( 'Number', 'fw' ),
						'desc'            => __( 'How many categories to show. Use 0 for all.', 'fw' ),
						'value'           => '0',
						'dynamic_content' => false,
					),
					'orderby'    => array(
						'type'    => 'select',
						'label'   => __( 'Order By', 'fw' ),
						'choices' => array(
							'name'       => __( 'Name', 'fw' ),
							'slug'       => __( 'Slug', 'fw' ),
							'count'      => __( 'Product Count', 'fw' ),
							'menu_order' => __( 'Menu Order', 'fw' ),
						),
						'value'   => 'name',
					),
					'order'      => array(
						'type'    => 'select',
						'label'   => __( 'Order', 'fw' ),
						'choices' => array( 'ASC' => __( 'Ascending', 'fw' ), 'DESC' => __( 'Descending', 'fw' ) ),
						'value'   => 'ASC',
					),
					'parent'     => array(
						'type'            => 'text',
						'label'           => __( 'Parent Category ID', 'fw' ),
						'desc'            => __( 'Show only sub-categories of this category ID. Use 0 for top-level only; leave empty for all.', 'fw' ),
						'value'           => '',
						'dynamic_content' => false,
					),
					'ids'        => array(
						'type'            => 'text',
						'label'           => __( 'Specific Category IDs', 'fw' ),
						'desc'            => __( 'Comma-separated category IDs to show (overrides Number / Parent). Leave empty to ignore.', 'fw' ),
						'value'           => '',
						'dynamic_content' => false,
					),
					'hide_empty' => function_exists( 'upwc_wc_switch' )
						? upwc_wc_switch( __( 'Hide Empty', 'fw' ), __( 'Hide categories that have no products.', 'fw' ), 'yes' )
						: array( 'type' => 'switch', 'label' => __( 'Hide Empty', 'fw' ), 'value' => 'yes' ),
				),
			),
		),
	),

	'tab_grid' => array(
		'title'   => __( 'Grid', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'group_grid' => array(
				'type'    => 'group',
				'options' => array(
					'columns'   => array(
						'type'    => 'select',
						'label'   => __( 'Columns', 'fw' ),
						'choices' => array( '1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6' ),
						'value'   => '4',
					),
					'gap'       => array(
						'type'    => 'select',
						'label'   => __( 'Gap', 'fw' ),
						'desc'    => __( 'Space between the category cards.', 'fw' ),
						'choices' => array(
							'sm' => __( 'Small', 'fw' ),
							'md' => __( 'Medium', 'fw' ),
							'lg' => __( 'Large', 'fw' ),
						),
						'value'   => 'md',
					),
					'alignment' => array(
						'type'    => 'select',
						'label'   => __( 'Card Alignment', 'fw' ),
						'desc'    => __( 'Horizontal alignment of the card contents.', 'fw' ),
						'choices' => array(
							''       => __( 'Inherit', 'fw' ),
							'start'  => __( 'Left', 'fw' ),
							'center' => __( 'Center', 'fw' ),
							'end'    => __( 'Right', 'fw' ),
						),
						'value'   => '',
					),
				),
			),
		),
	),

	'tab_card' => array(
		'title'   => __( 'Card', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'group_layout' => array(
				'type'    => 'group',
				'options' => array(
					// Live wireframe preview of the card (shared card-preview engine).
					'card_preview' => array(
						'type'  => 'html-full',
						'label' => false,
						'html'  => function_exists( 'sc_card_preview_mount_html' ) ? sc_card_preview_mount_html() : '',
					),
					'card_rows' => function_exists( 'sc_card_rows_field' )
						? sc_card_rows_field( array(
							'label' => __( 'Card Rows', 'fw' ),
							'desc'  => __( 'The category card layout — add / drag rows and pick each row\'s slots (Image, Name, Product Count, Button). A slot shows only when it\'s in a row and the category has that data. Set each row inline/stacked + alignment; the visual skin comes from the Card Box Style preset below.', 'fw' ),
							'slots' => $upwc_cat_slots,
							'value' => $upwc_cat_rows_default,
						) )
						: array( 'type' => 'text', 'label' => __( 'Card Rows', 'fw' ), 'value' => '' ),
					'box_style' => function_exists( 'sc_card_box_style_field' )
						? sc_card_box_style_field( array(
							'label' => __( 'Card Box Style', 'fw' ),
							'desc'  => __( 'Apply a reusable Box Preset (border, corners, shadow, fill + hover effects) to every category card. Manage presets in Theme Settings → Components → Box Presets.', 'fw' ),
						) )
						: array( 'type' => 'text', 'label' => __( 'Card Box Style', 'fw' ), 'value' => '' ),
					'image_ratio' => array(
						'type'    => 'select',
						'label'   => __( 'Image Ratio', 'fw' ),
						'desc'    => __( 'Crop category images to a uniform aspect ratio, or keep their natural proportions.', 'fw' ),
						'choices' => array(
							'auto'      => __( 'Natural', 'fw' ),
							'square'    => __( 'Square (1:1)', 'fw' ),
							'portrait'  => __( 'Portrait (3:4)', 'fw' ),
							'landscape' => __( 'Landscape (4:3)', 'fw' ),
						),
						'value'   => 'auto',
					),
					'image_size'  => array(
						'type'  => 'unit-input',
						'label' => __( 'Image Size', 'fw' ),
						'desc'  => __( 'Width of the category image. Empty = auto (fills the column). Pairs with Image Ratio (shape).', 'fw' ),
						'value' => array( 'value' => '', 'unit' => 'rem' ),
						'units' => array( 'px', 'rem', '%', 'vw', 'em' ),
					),
				),
			),
			'group_button' => array(
				'type'    => 'group',
				'options' => array(
					'button_text' => array(
						'type'            => 'text',
						'label'           => __( 'Button Label', 'fw' ),
						'desc'            => __( 'Text for the "Button" slot (links to the category). Leave empty for "View".', 'fw' ),
						'value'           => '',
						'dynamic_content' => false,
					),
				),
			),
		),
	),

	'tab_advanced' => array(
		'title'   => __( 'Advanced', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'advanced_settings' => array(
				'type'    => 'group',
				'options' => function_exists( 'sc_get_advanced_tab' ) ? sc_get_advanced_tab() : array(),
			),
		),
	),
);

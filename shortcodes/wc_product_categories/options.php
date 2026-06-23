<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

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
					'columns'    => array(
						'type'    => 'select',
						'label'   => __( 'Columns', 'fw' ),
						'choices' => array( '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6' ),
						'value'   => '4',
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

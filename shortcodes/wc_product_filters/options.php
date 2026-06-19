<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$options = array(

	'tab_content' => array(
		'title'   => __( 'Content', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'group_filter' => array(
				'type'    => 'group',
				'options' => array(
					'filter'       => array(
						'type'    => 'select',
						'label'   => __( 'Filter', 'fw' ),
						'desc'    => __( 'Which filter widget to display.', 'fw' ),
						'choices' => array(
							'price'     => __( 'Price', 'fw' ),
							'attribute' => __( 'Attribute', 'fw' ),
							'rating'    => __( 'Rating', 'fw' ),
							'active'    => __( 'Active Filters', 'fw' ),
						),
						'value'   => 'price',
					),
					'title'        => array(
						'type'            => 'text',
						'label'           => __( 'Title', 'fw' ),
						'desc'            => __( 'Optional heading above the filter. Leave empty for none.', 'fw' ),
						'value'           => '',
						'dynamic_content' => false,
					),
					'attribute'    => array(
						'type'            => 'text',
						'label'           => __( 'Attribute', 'fw' ),
						'desc'            => __( 'Filter "Attribute": attribute slug without the "pa_" prefix (e.g. color).', 'fw' ),
						'value'           => '',
						'dynamic_content' => false,
					),
					'display_type' => array(
						'type'    => 'select',
						'label'   => __( 'Attribute Display', 'fw' ),
						'desc'    => __( 'Filter "Attribute": how to show the terms.', 'fw' ),
						'choices' => array(
							'list'     => __( 'List', 'fw' ),
							'dropdown' => __( 'Dropdown', 'fw' ),
						),
						'value'   => 'list',
					),
					'query_type'   => array(
						'type'    => 'select',
						'label'   => __( 'Attribute Logic', 'fw' ),
						'desc'    => __( 'Filter "Attribute": AND narrows by every selected term; OR widens to any.', 'fw' ),
						'choices' => array(
							'and' => __( 'AND', 'fw' ),
							'or'  => __( 'OR', 'fw' ),
						),
						'value'   => 'and',
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

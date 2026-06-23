<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$upwc_product_choices = function_exists( 'upwc_wc_product_choices' )
	? upwc_wc_product_choices()
	: array( '' => __( '— Select a product —', 'fw' ) );

$options = array(

	'tab_content' => array(
		'title'   => __( 'Content', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'group_product' => array(
				'type'    => 'group',
				'options' => array(
					'product' => array(
						'type'    => 'select',
						'label'   => __( 'Product', 'fw' ),
						'desc'    => __( 'The product to display.', 'fw' ),
						'choices' => $upwc_product_choices,
						'value'   => '',
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

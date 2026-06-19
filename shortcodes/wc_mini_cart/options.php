<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$options = array(

	'tab_content' => array(
		'title'   => __( 'Content', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'group_minicart' => array(
				'type'    => 'group',
				'options' => array(
					'icon'       => array(
						'type'    => 'select',
						'label'   => __( 'Icon', 'fw' ),
						'choices' => array(
							'bag'    => __( 'Shopping Bag', 'fw' ),
							'cart'   => __( 'Shopping Cart', 'fw' ),
							'basket' => __( 'Basket', 'fw' ),
						),
						'value'   => 'bag',
					),
					'trigger'    => array(
						'type'    => 'select',
						'label'   => __( 'Open On', 'fw' ),
						'choices' => array(
							'click' => __( 'Click', 'fw' ),
							'hover' => __( 'Hover', 'fw' ),
						),
						'value'   => 'click',
					),
					'show_count' => function_exists( 'upw_wc_switch' )
						? upw_wc_switch( __( 'Item Count', 'fw' ), __( 'Show the item-count badge on the icon.', 'fw' ), 'yes' )
						: array( 'type' => 'switch', 'label' => __( 'Item Count', 'fw' ), 'value' => 'yes' ),
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

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
			'group_atc' => array(
				'type'    => 'group',
				'options' => array(
					'product'    => array(
						'type'    => 'select',
						'label'   => __( 'Product', 'fw' ),
						'desc'    => __( 'The product to add to the cart.', 'fw' ),
						'choices' => $upwc_product_choices,
						'value'   => '',
					),
					'quantity'   => array(
						'type'            => 'text',
						'label'           => __( 'Quantity', 'fw' ),
						'desc'            => __( 'Quantity added per click.', 'fw' ),
						'value'           => '1',
						'dynamic_content' => false,
					),
					'show_price' => function_exists( 'upwc_wc_switch' )
						? upwc_wc_switch( __( 'Show Price', 'fw' ), __( 'Show the price beside the button.', 'fw' ), 'yes' )
						: array( 'type' => 'switch', 'label' => __( 'Show Price', 'fw' ), 'value' => 'yes' ),
					'wc_style'   => function_exists( 'upwc_wc_switch' )
						? upwc_wc_switch( __( "WooCommerce's Default Box", 'fw' ), __( "Keep WooCommerce's bordered box around the button. Off = a plain button styled by the theme.", 'fw' ), 'no' )
						: array( 'type' => 'switch', 'label' => __( "WooCommerce's Default Box", 'fw' ), 'value' => 'no' ),
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

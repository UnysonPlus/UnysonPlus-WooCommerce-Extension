<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

// The bar reads the free-shipping threshold from WooCommerce shipping settings;
// it has no content options of its own beyond the Advanced tab.
$options = array(
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

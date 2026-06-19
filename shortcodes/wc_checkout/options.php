<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

// Renders the WooCommerce checkout; no content options of its own.
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

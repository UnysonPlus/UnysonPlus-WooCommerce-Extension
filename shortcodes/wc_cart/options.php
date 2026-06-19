<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

// This element renders the WooCommerce cart; it has no content options of its
// own. The Advanced tab still provides visibility / custom class & ID.
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

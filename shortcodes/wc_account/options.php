<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$options = array(

	'tab_content' => array(
		'title'   => __( 'Content', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'group_account' => array(
				'type'    => 'group',
				'options' => array(
					'show_label' => function_exists( 'upw_wc_switch' )
						? upw_wc_switch( __( 'Show Label', 'fw' ), __( 'Show "Login" / "Hi, name" text beside the icon.', 'fw' ), 'yes' )
						: array( 'type' => 'switch', 'label' => __( 'Show Label', 'fw' ), 'value' => 'yes' ),
					'trigger'    => array(
						'type'    => 'select',
						'label'   => __( 'Open On', 'fw' ),
						'choices' => array(
							'click' => __( 'Click', 'fw' ),
							'hover' => __( 'Hover', 'fw' ),
						),
						'value'   => 'click',
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

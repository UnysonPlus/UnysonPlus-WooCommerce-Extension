<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

// Predictable yes/no switch (stored value is always 'yes' or 'no').
if ( ! function_exists( 'upw_cart_switch' ) ) {
	function upw_cart_switch( $label, $desc = '', $value = 'no' ) {
		return array(
			'type'         => 'switch',
			'label'        => $label,
			'desc'         => $desc,
			'left-choice'  => array( 'value' => 'no', 'label' => __( 'No', 'fw' ) ),
			'right-choice' => array( 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ),
			'value'        => $value,
		);
	}
}

$options = array(

	'tab_content' => array(
		'title'   => __( 'Content', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'group_cart' => array(
				'type'    => 'group',
				'options' => array(
					'icon'            => array(
						'type'    => 'select',
						'label'   => __( 'Icon', 'fw' ),
						'choices' => array(
							'bag'    => __( 'Shopping Bag', 'fw' ),
							'cart'   => __( 'Shopping Cart', 'fw' ),
							'basket' => __( 'Basket', 'fw' ),
							'none'   => __( 'No Icon', 'fw' ),
						),
						'value'   => 'bag',
					),
					'label'           => array(
						'type'            => 'text',
						'label'           => __( 'Label', 'fw' ),
						'desc'            => __( 'Optional text shown beside the icon (e.g. "Cart"). Leave empty for icon only.', 'fw' ),
						'value'           => '',
						'dynamic_content' => false,
					),
					'show_count'      => upw_cart_switch( __( 'Item Count', 'fw' ), __( 'Show the number of items as a badge. Updates live when items are added.', 'fw' ), 'yes' ),
					'show_total'      => upw_cart_switch( __( 'Cart Total', 'fw' ), __( 'Show the cart total amount beside the icon.', 'fw' ), 'no' ),
					'hide_when_empty' => upw_cart_switch( __( 'Hide When Empty', 'fw' ), __( 'Hide the element entirely while the cart is empty. (Note: with live updates off-screen, keep this OFF so the count can appear when the first item is added.)', 'fw' ), 'no' ),
				),
			),
		),
	),

	'tab_animation' => array(
		'title'   => __( 'Animations', 'fw' ),
		'type'    => 'tab',
		'options' => function_exists( 'sc_get_animation_fields' ) ? sc_get_animation_fields() : array(),
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

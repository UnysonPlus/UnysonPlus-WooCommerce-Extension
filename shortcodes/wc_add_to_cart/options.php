<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Add to Cart Button element options.
 *
 * Renders a standalone add-to-cart button through our own themed <a> (keeping the
 * WooCommerce AJAX behavior), so it gains the full Button STYLE system — the same
 * Theme Settings → Buttons presets, Size, Shape, Width and Alignment as the [button]
 * shortcode — via the shared sc_button_style_field() helper.
 */

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
					'label'      => array(
						'type'            => 'text',
						'label'           => __( 'Button Label', 'fw' ),
						'desc'            => __( 'Custom button text. Leave empty for WooCommerce\'s default (e.g. "Add to cart", or "Select options" for variable products).', 'fw' ),
						'value'           => '',
						'dynamic_content' => false,
					),
					'show_price' => function_exists( 'upwc_wc_switch' )
						? upwc_wc_switch( __( 'Show Price', 'fw' ), __( 'Show the price beside the button.', 'fw' ), 'no' )
						: array( 'type' => 'switch', 'label' => __( 'Show Price', 'fw' ), 'value' => 'no' ),
					'price_position' => array(
						'type'    => 'select',
						'label'   => __( 'Price Position', 'fw' ),
						'desc'    => __( 'Where the price sits relative to the button (when Show Price is on).', 'fw' ),
						'choices' => array(
							'before' => __( 'Before the button', 'fw' ),
							'after'  => __( 'After the button', 'fw' ),
						),
						'value'   => 'before',
					),
				),
			),
		),
	),

	'tab_style' => array(
		'title'   => __( 'Style', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'group_button' => array(
				'type'    => 'group',
				'options' => function_exists( 'sc_button_style_field' ) ? sc_button_style_field() : array(),
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

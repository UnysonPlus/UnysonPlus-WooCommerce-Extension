<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$options = array(
	'source'  => array(
		'label'   => __( 'Source', 'fw' ),
		'desc'    => __( 'Upsells are the better/pricier alternatives set on the product; cross-sells are the "goes well with" products, set per product and normally shown in the cart.', 'fw' ),
		'type'    => 'select',
		'choices' => array(
			'upsells'     => __( 'Upsells', 'fw' ),
			'cross_sells' => __( 'Cross-sells', 'fw' ),
		),
		'value'   => 'upsells',
	),
	'heading' => array(
		'label' => __( 'Heading', 'fw' ),
		'desc'  => __( 'Leave empty for no heading.', 'fw' ),
		'type'  => 'text',
		'value' => __( 'You may also like', 'fw' ),
	),
	'columns' => array(
		'label'   => __( 'Products per Row', 'fw' ),
		'type'    => 'select',
		'choices' => array( '2' => '2', '3' => '3', '4' => '4', '5' => '5' ),
		'value'   => '4',
	),
	'limit'   => array(
		'label' => __( 'Maximum Products', 'fw' ),
		'type'  => 'text',
		'value' => '4',
	),
);

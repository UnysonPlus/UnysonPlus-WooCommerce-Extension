<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'          => __( 'Order Tracking', 'fw' ),
	'description'    => __( 'A form where customers enter an order ID + email to check their order status.', 'fw' ),
	'tab'            => __( 'WooCommerce Elements', 'fw' ),
	'popup_size'     => 'small',
	'title_template' => '<strong>' . __( 'Order Tracking', 'fw' ) . '</strong>',
);

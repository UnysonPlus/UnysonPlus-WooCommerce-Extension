<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'          => __( 'Checkout', 'fw' ),
	'description'    => __( 'The classic WooCommerce checkout (billing, shipping, order review, payment). Place it on the page set as your WooCommerce Checkout.', 'fw' ),
	'tab'            => __( 'WooCommerce Elements', 'fw' ),
	'popup_size'     => 'small',
	'title_template' => '<strong>' . __( 'Checkout', 'fw' ) . '</strong>',
);

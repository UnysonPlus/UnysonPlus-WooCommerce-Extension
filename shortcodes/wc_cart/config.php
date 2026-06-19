<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'          => __( 'Cart', 'fw' ),
	'description'    => __( 'The classic WooCommerce cart (items table + totals). Place it on the page set as your WooCommerce Cart.', 'fw' ),
	'tab'            => __( 'WooCommerce Elements', 'fw' ),
	'popup_size'     => 'small',
	'title_template' => '<strong>' . __( 'Cart', 'fw' ) . '</strong>',
);

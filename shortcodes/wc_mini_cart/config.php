<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'          => __( 'Mini Cart', 'fw' ),
	'description'    => __( 'A cart icon that opens a dropdown with the cart contents, subtotal and checkout button. Updates live.', 'fw' ),
	'tab'            => __( 'WooCommerce Elements', 'fw' ),
	'popup_size'     => 'small',
	'title_template' => '<strong>' . __( 'Mini Cart', 'fw' ) . '</strong>',
);

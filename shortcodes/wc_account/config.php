<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'          => __( 'Account', 'fw' ),
	'description'    => __( 'A header account link with a dropdown — login form for guests, account menu for logged-in users.', 'fw' ),
	'tab'            => __( 'WooCommerce Elements', 'fw' ),
	'popup_size'     => 'small',
	'title_template' => '<strong>' . __( 'Account', 'fw' ) . '</strong>',
);

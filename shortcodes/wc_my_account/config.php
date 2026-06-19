<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'          => __( 'My Account', 'fw' ),
	'description'    => __( 'The WooCommerce account area (login / register for guests; dashboard, orders, addresses for logged-in users). Place it on the page set as your WooCommerce My Account.', 'fw' ),
	'tab'            => __( 'WooCommerce Elements', 'fw' ),
	'popup_size'     => 'small',
	'title_template' => '<strong>' . __( 'My Account', 'fw' ) . '</strong>',
);

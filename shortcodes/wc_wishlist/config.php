<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'          => __( 'Wishlist', 'fw' ),
	'description'    => __( 'The products this visitor has saved. Put it on the page you set as the Wishlist Page.', 'fw' ),
	'tab'            => __( 'WooCommerce Elements', 'fw' ),
	'popup_size'     => 'small',
	'title_template' => '<strong>' . __( 'Wishlist', 'fw' ) . '</strong>',
);

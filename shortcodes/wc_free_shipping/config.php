<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'          => __( 'Free Shipping Bar', 'fw' ),
	'description'    => __( 'A progress bar showing how much more a customer needs to spend for free shipping. Updates live.', 'fw' ),
	'tab'            => __( 'WooCommerce Elements', 'fw' ),
	'popup_size'     => 'small',
	'title_template' => '<strong>' . __( 'Free Shipping Bar', 'fw' ) . '</strong>',
);

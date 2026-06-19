<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'          => __( 'Single Product', 'fw' ),
	'description'    => __( 'Show one product as a compact card (image, title, price, add-to-cart).', 'fw' ),
	'tab'            => __( 'WooCommerce Elements', 'fw' ),
	'popup_size'     => 'small',
	'title_template' => '<strong>{{= ( o["product"] ? "#" + o["product"] : "Pick a product" ) }}</strong>',
);

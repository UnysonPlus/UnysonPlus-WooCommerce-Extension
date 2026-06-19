<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'          => __( 'Add to Cart Button', 'fw' ),
	'description'    => __( 'A standalone add-to-cart button (with optional price) for one product.', 'fw' ),
	'tab'            => __( 'WooCommerce Elements', 'fw' ),
	'popup_size'     => 'small',
	'title_template' => '<strong>{{= ( o["product"] ? "#" + o["product"] : "Pick a product" ) }}</strong>',
);

<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'          => __( 'Product Page', 'fw' ),
	'description'    => __( 'Embed the full single-product layout (gallery, summary, tabs, related) for one product.', 'fw' ),
	'tab'            => __( 'WooCommerce Elements', 'fw' ),
	'popup_size'     => 'small',
	'title_template' => '<strong>{{= ( o["product"] ? "#" + o["product"] : "Pick a product" ) }}</strong>',
);

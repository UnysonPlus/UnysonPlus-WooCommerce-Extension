<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'          => __( 'Product Categories', 'fw' ),
	'description'    => __( 'A grid of WooCommerce product-category cards.', 'fw' ),
	'tab'            => __( 'WooCommerce Elements', 'fw' ),
	'popup_size'     => 'medium',
	'title_template' => '<strong>{{= ( o["columns"] || 4 ) }} cols</strong>',
);

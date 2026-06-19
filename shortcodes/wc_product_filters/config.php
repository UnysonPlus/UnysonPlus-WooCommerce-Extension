<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'          => __( 'Product Filters', 'fw' ),
	'description'    => __( 'A shop filter widget (price, attribute, rating, or active filters). Best placed in a shop / category sidebar.', 'fw' ),
	'tab'            => __( 'WooCommerce Elements', 'fw' ),
	'popup_size'     => 'small',
	'title_template' => '<strong>{{= ( o["filter"] || "price" ) }}</strong> <span style="opacity:.6">filter</span>',
);

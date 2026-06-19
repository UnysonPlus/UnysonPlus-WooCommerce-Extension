<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'          => __( 'Product Search', 'fw' ),
	'description'    => __( 'A search form scoped to products — for shop headers or sidebars.', 'fw' ),
	'tab'            => __( 'WooCommerce Elements', 'fw' ),
	'popup_size'     => 'small',
	'title_template' => '<strong>' . __( 'Product Search', 'fw' ) . '</strong>',
);

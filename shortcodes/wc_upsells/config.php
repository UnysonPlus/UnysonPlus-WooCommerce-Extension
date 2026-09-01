<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'          => __( 'Upsells', 'fw' ),
	'description'    => __( 'The upsells (or cross-sells) of the product being viewed — the "you might prefer this" row WooCommerce already knows about.', 'fw' ),
	'tab'            => __( 'WooCommerce Elements', 'fw' ),
	'popup_size'     => 'small',
	'title_template' => '<strong>' . __( 'Upsells', 'fw' ) . '</strong>',
);

<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'          => __( 'Compare', 'fw' ),
	'description'    => __( 'The products this visitor picked to compare, side by side with their attributes. Put it on the page you set as the Compare Page.', 'fw' ),
	'tab'            => __( 'WooCommerce Elements', 'fw' ),
	'popup_size'     => 'small',
	'title_template' => '<strong>' . __( 'Compare', 'fw' ) . '</strong>',
);

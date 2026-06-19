<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'          => __( 'Cart', 'fw' ),
	'description'    => __( 'A cart icon with a live item count (and optional total) linking to the cart — ideal for headers.', 'fw' ),
	'tab'            => __( 'Header/Footer Elements', 'fw' ),
	'popup_size'     => 'small',
	'title_template' => '<span class="fw-wc-cart">' . __( 'Cart', 'fw' ) . '</span>',
);

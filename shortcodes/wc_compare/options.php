<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$options = array(
	'empty_text'  => array(
		'label' => __( 'Empty Message', 'fw' ),
		'desc'  => __( 'Shown when nothing has been picked to compare.', 'fw' ),
		'type'  => 'text',
		'value' => __( 'Pick a few products to compare them here.', 'fw' ),
	),
	'empty_link'  => array(
		'label' => __( 'Empty Button Link', 'fw' ),
		'desc'  => __( 'Where the button under the empty message goes — usually the shop. Leave empty to omit it.', 'fw' ),
		'type'  => 'text',
		'value' => '',
	),
	'empty_label' => array(
		'label' => __( 'Empty Button Text', 'fw' ),
		'type'  => 'text',
		'value' => __( 'Browse the shop', 'fw' ),
	),
);

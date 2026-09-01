<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$options = array(
	'columns'     => array(
		'label'   => __( 'Products per Row', 'fw' ),
		'type'    => 'select',
		'choices' => array( '2' => '2', '3' => '3', '4' => '4', '5' => '5' ),
		'value'   => '4',
	),
	'empty_text'  => array(
		'label' => __( 'Empty Message', 'fw' ),
		'desc'  => __( 'Shown when nothing has been saved yet.', 'fw' ),
		'type'  => 'text',
		'value' => __( 'You have not saved anything yet.', 'fw' ),
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

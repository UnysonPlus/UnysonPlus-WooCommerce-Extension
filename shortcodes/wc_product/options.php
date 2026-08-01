<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Single Product element options.
 *
 * [wc_product] is the single-product counterpart to [wc_products]: it renders ONE
 * product through the SAME card engine (upwc_wc_products_card), so it shares the
 * whole Card tab (Card Rows designer + preview, Card Box Style, Image Ratio/Size,
 * Rating style, Badges, Add-to-Cart label) via the shared upwc_wc_card_option_groups()
 * helper. The grid-only concerns (source query, columns, pagination, carousel) don't
 * apply to a single product, so this element only exposes Content + Card + Advanced.
 */

$upwc_product_choices = function_exists( 'upwc_wc_product_choices' )
	? upwc_wc_product_choices()
	: array( '' => __( '— Select a product —', 'fw' ) );

$options = array(

	'tab_content' => array(
		'title'   => __( 'Content', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'group_product' => array(
				'type'    => 'group',
				'options' => array(
					'product' => array(
						'type'    => 'select',
						'label'   => __( 'Product', 'fw' ),
						'desc'    => __( 'The product to display.', 'fw' ),
						'choices' => $upwc_product_choices,
						'value'   => '',
					),
				),
			),
		),
	),

	// The SAME Card tab as [wc_products] — one source of truth, so the two elements
	// share the identical card model and can never drift.
	'tab_card' => array(
		'title'   => __( 'Card', 'fw' ),
		'type'    => 'tab',
		'options' => function_exists( 'upwc_wc_card_option_groups' ) ? upwc_wc_card_option_groups() : array(),
	),

	'tab_animation' => array(
		'title'   => __( 'Animations', 'fw' ),
		'type'    => 'tab',
		'options' => function_exists( 'sc_get_animation_fields' ) ? sc_get_animation_fields() : array(),
	),

	'tab_advanced' => array(
		'title'   => __( 'Advanced', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'advanced_settings' => array(
				'type'    => 'group',
				'options' => function_exists( 'sc_get_advanced_tab' ) ? sc_get_advanced_tab() : array(),
			),
		),
	),
);

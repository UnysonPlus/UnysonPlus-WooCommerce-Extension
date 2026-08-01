<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Product Filters element options.
 *
 * Instead of one Woo filter widget per element, this renders a whole FILTER PANEL:
 * an ordered list of filter blocks (Price / Attribute / Rating / Active Filters —
 * add as many as you like, e.g. price + several attributes), wrapped in a styled,
 * optionally-collapsible panel with a Card Box Style skin. The legacy single-filter
 * options are still read when no blocks are configured, so old instances keep working.
 */

// Product attribute taxonomies → a select (slug without the pa_ prefix).
$upwc_attr_choices = array( '' => __( '— Select attribute —', 'fw' ) );
if ( function_exists( 'wc_get_attribute_taxonomies' ) ) {
	foreach ( wc_get_attribute_taxonomies() as $tax ) {
		$slug                       = $tax->attribute_name;
		$upwc_attr_choices[ $slug ] = '' !== $tax->attribute_label ? $tax->attribute_label : $slug;
	}
}

$options = array(

	'tab_content' => array(
		'title'   => __( 'Content', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'group_filter' => array(
				'type'    => 'group',
				'options' => array(
					// The filter STACK — add / drag blocks. Each block is one Woo filter widget.
					'filters' => array(
						'type'          => 'addable-popup',
						'label'         => __( 'Filters', 'fw' ),
						'desc'          => __( 'The filter blocks to show, in order. Add Price, Rating, Active Filters, or one Attribute block per attribute (e.g. Color, Size). Note: filter widgets only work on shop / product-category pages, where they filter the listing.', 'fw' ),
						'popup-title'   => __( 'Filter Block', 'fw' ),
						'template'      => '{{= ( type === "attribute" && attribute ? ( "Attribute: " + attribute ) : ( type || "price" ) ) }}',
						'popup-options' => array(
							'type'         => array(
								'type'    => 'select',
								'label'   => __( 'Filter Type', 'fw' ),
								'choices' => array(
									'price'     => __( 'Price', 'fw' ),
									'attribute' => __( 'Attribute', 'fw' ),
									'rating'    => __( 'Rating', 'fw' ),
									'active'    => __( 'Active Filters', 'fw' ),
								),
								'value'   => 'price',
							),
							'title'        => array(
								'type'            => 'text',
								'label'           => __( 'Block Title', 'fw' ),
								'desc'            => __( 'Heading above this block. Leave empty for none.', 'fw' ),
								'value'           => '',
								'dynamic_content' => false,
							),
							'attribute'    => array(
								'type'    => 'select',
								'label'   => __( 'Attribute', 'fw' ),
								'desc'    => __( 'For the "Attribute" type — which product attribute to filter by.', 'fw' ),
								'choices' => $upwc_attr_choices,
								'value'   => '',
							),
							'display_type' => array(
								'type'    => 'select',
								'label'   => __( 'Attribute Display', 'fw' ),
								'choices' => array(
									'list'     => __( 'List', 'fw' ),
									'dropdown' => __( 'Dropdown', 'fw' ),
								),
								'value'   => 'list',
							),
							'query_type'   => array(
								'type'    => 'select',
								'label'   => __( 'Attribute Logic', 'fw' ),
								'desc'    => __( 'AND narrows by every selected term; OR widens to any.', 'fw' ),
								'choices' => array(
									'and' => __( 'AND', 'fw' ),
									'or'  => __( 'OR', 'fw' ),
								),
								'value'   => 'and',
							),
						),
						'value'         => array(
							array( 'type' => 'price' ),
							array( 'type' => 'rating' ),
							array( 'type' => 'active' ),
						),
					),
				),
			),
		),
	),

	'tab_style' => array(
		'title'   => __( 'Style', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'group_panel' => array(
				'type'    => 'group',
				'options' => array(
					'panel_title' => array(
						'type'            => 'text',
						'label'           => __( 'Panel Title', 'fw' ),
						'desc'            => __( 'Optional heading above the whole filter panel. Leave empty for none.', 'fw' ),
						'value'           => '',
						'dynamic_content' => false,
					),
					'collapsible' => function_exists( 'upwc_wc_switch' )
						? upwc_wc_switch( __( 'Collapsible Blocks', 'fw' ), __( 'Make each block\'s title a toggle that expands / collapses it.', 'fw' ), 'no' )
						: array( 'type' => 'switch', 'label' => __( 'Collapsible Blocks', 'fw' ), 'value' => 'no' ),
					'box_style'   => function_exists( 'sc_card_box_style_field' )
						? sc_card_box_style_field( array(
							'label' => __( 'Panel Box Style', 'fw' ),
							'desc'  => __( 'Apply a reusable Box Preset (border, corners, shadow, fill) to the filter panel. Manage presets in Theme Settings → Components → Box Presets.', 'fw' ),
						) )
						: array( 'type' => 'text', 'label' => __( 'Panel Box Style', 'fw' ), 'value' => '' ),
					'divider'     => function_exists( 'upwc_wc_switch' )
						? upwc_wc_switch( __( 'Block Dividers', 'fw' ), __( 'Show a thin divider line between filter blocks.', 'fw' ), 'yes' )
						: array( 'type' => 'switch', 'label' => __( 'Block Dividers', 'fw' ), 'value' => 'yes' ),
				),
			),
		),
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

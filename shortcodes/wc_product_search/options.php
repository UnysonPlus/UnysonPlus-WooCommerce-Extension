<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Product Search element options.
 *
 * A product-scoped search form with flexible layout (attached button / button
 * below / compact icon), field shape + size, a themed submit button (Button Style
 * presets), width and alignment — instead of the single placeholder it had before.
 */

$sc_button_style_choices = function_exists( 'sc_get_button_style_choices' ) ? sc_get_button_style_choices() : array();
$sc_button_style_default = function_exists( 'sc_get_button_style_default' ) ? sc_get_button_style_default() : '';

$options = array(

	'tab_content' => array(
		'title'   => __( 'Content', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'group_search' => array(
				'type'    => 'group',
				'options' => array(
					'placeholder' => array(
						'type'            => 'text',
						'label'           => __( 'Placeholder', 'fw' ),
						'value'           => __( 'Search products…', 'fw' ),
						'dynamic_content' => false,
					),
					'button_text' => array(
						'type'            => 'text',
						'label'           => __( 'Button Label', 'fw' ),
						'desc'            => __( 'Text on the search button. Leave empty for an icon-only button.', 'fw' ),
						'value'           => '',
						'dynamic_content' => false,
					),
					'button_icon' => array(
						'type'         => 'icon',
						'label'        => __( 'Button Icon', 'fw' ),
						'desc'         => __( 'Icon shown on the button (before the label, or alone when no label). Leave empty for the default magnifier.', 'fw' ),
						'preview_size' => 'small',
					),
				),
			),
		),
	),

	'tab_style' => array(
		'title'   => __( 'Style', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'group_layout' => array(
				'type'    => 'group',
				'options' => array(
					'layout' => array(
						'type'    => 'select',
						'label'   => __( 'Layout', 'fw' ),
						'desc'    => __( 'Attached: button beside the field. Below: full-width button under the field. Compact: a plain icon button inside the field edge.', 'fw' ),
						'choices' => array(
							'attached' => __( 'Attached (button beside)', 'fw' ),
							'below'    => __( 'Button below', 'fw' ),
							'compact'  => __( 'Compact (icon inside)', 'fw' ),
						),
						'value'   => 'attached',
					),
					'field_shape' => array(
						'type'    => 'select',
						'label'   => __( 'Field Shape', 'fw' ),
						'desc'    => __( 'Corner rounding of the search field / bar.', 'fw' ),
						'choices' => array(
							'default' => __( 'Default', 'fw' ),
							'pill'    => __( 'Pill', 'fw' ),
							'rounded' => __( 'Rounded', 'fw' ),
							'square'  => __( 'Square', 'fw' ),
						),
						'value'   => 'default',
					),
					'size' => array(
						'type'    => 'select',
						'label'   => __( 'Size', 'fw' ),
						'choices' => array(
							'sm' => __( 'Small', 'fw' ),
							'md' => __( 'Medium', 'fw' ),
							'lg' => __( 'Large', 'fw' ),
						),
						'value'   => 'md',
					),
					'button_style' => array(
						'label'        => __( 'Button Style', 'fw' ),
						'desc'         => __( 'Themed style for the submit button (Attached / Below layouts). Sourced from Theme Settings → Buttons.', 'fw' ),
						'type'         => 'button-style-picker',
						'choices'      => $sc_button_style_choices,
						'value'        => $sc_button_style_default,
						'allow_none'   => true,
						'preview_text' => __( 'Search', 'fw' ),
					),
					'width' => array(
						'type'    => 'select',
						'label'   => __( 'Width', 'fw' ),
						'choices' => array(
							''      => __( 'Auto (max 22rem)', 'fw' ),
							'full'  => __( 'Full Width', 'fw' ),
						),
						'value'   => '',
					),
					'alignment' => array(
						'type'    => 'select',
						'label'   => __( 'Alignment', 'fw' ),
						'desc'    => __( 'Aligns the form within its container (no effect at Full Width).', 'fw' ),
						'choices' => array(
							''       => __( 'Default (inherit)', 'fw' ),
							'left'   => __( 'Left', 'fw' ),
							'center' => __( 'Center', 'fw' ),
							'right'  => __( 'Right', 'fw' ),
						),
						'value'   => '',
					),
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

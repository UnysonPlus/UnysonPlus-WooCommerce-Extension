<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$options = array(

	'tab_content' => array(
		'title'   => __( 'Content', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'group_minicart' => array(
				'type'    => 'group',
				'options' => array(
					'icon'       => array(
						'type'    => 'select',
						'label'   => __( 'Icon', 'fw' ),
						'choices' => array(
							'bag'    => __( 'Shopping Bag', 'fw' ),
							'cart'   => __( 'Shopping Cart', 'fw' ),
							'basket' => __( 'Basket', 'fw' ),
						),
						'value'   => 'bag',
					),
					'panel_style' => array(
						'type'    => 'select',
						'label'   => __( 'Open As', 'fw' ),
						'desc'    => __( 'Dropdown = a small flyout below the icon. Drawer = a right slide-out side-cart (portaled, scroll-locked when its backdrop is on).', 'fw' ),
						'choices' => array(
							'dropdown' => __( 'Dropdown flyout', 'fw' ),
							'drawer'   => __( 'Drawer (side-cart)', 'fw' ),
						),
						'value'   => 'dropdown',
					),
					'drawer_backdrop' => function_exists( 'upwc_wc_switch' )
						? upwc_wc_switch( __( 'Drawer Backdrop', 'fw' ), __( 'Drawer mode only: dim the page + lock scrolling while the drawer is open (click the backdrop to close). Off = no dim, page stays interactive.', 'fw' ), 'yes' )
						: array( 'type' => 'switch', 'label' => __( 'Drawer Backdrop', 'fw' ), 'value' => 'yes' ),
					'drawer_backdrop_blur' => array(
						'type'    => 'select',
						'label'   => __( 'Backdrop Blur', 'fw' ),
						'desc'    => __( 'Drawer + Backdrop on: also blur (frost) the page behind the drawer, not just dim it.', 'fw' ),
						'choices' => array(
							'0'  => __( 'None (dim only)', 'fw' ),
							'4'  => __( 'Light', 'fw' ),
							'8'  => __( 'Medium', 'fw' ),
							'12' => __( 'Strong', 'fw' ),
						),
						'value'   => '0',
					),
					'trigger'    => array(
						'type'    => 'select',
						'label'   => __( 'Open On', 'fw' ),
						'desc'    => __( 'Dropdown only (Drawer is always click).', 'fw' ),
						'choices' => array(
							'click' => __( 'Click', 'fw' ),
							'hover' => __( 'Hover', 'fw' ),
						),
						'value'   => 'click',
					),
					'show_count' => function_exists( 'upwc_wc_switch' )
						? upwc_wc_switch( __( 'Item Count', 'fw' ), __( 'Show the item-count badge on the icon.', 'fw' ), 'yes' )
						: array( 'type' => 'switch', 'label' => __( 'Item Count', 'fw' ), 'value' => 'yes' ),
				),
			),
			// Branding — override the flyout copy so a store can theme the mini-cart
			// (e.g. a bakery calling it "Sweet Basket" / "Total Sweetness"). Any field
			// left empty keeps the WooCommerce default for that piece.
			'group_branding' => array(
				'type'    => 'group',
				'options' => array(
					'panel_title'    => array(
						'type'            => 'text',
						'label'           => __( 'Panel Title', 'fw' ),
						'desc'            => __( 'Heading shown at the top of the open cart panel (with the icon). Leave empty for no title.', 'fw' ),
						'value'           => '',
						'dynamic_content' => false,
					),
					'subtotal_label' => array(
						'type'            => 'text',
						'label'           => __( 'Subtotal Label', 'fw' ),
						'desc'            => __( 'Replaces the word "Subtotal" in the panel (e.g. "Total Sweetness"). Leave empty to keep "Subtotal".', 'fw' ),
						'value'           => '',
						'dynamic_content' => false,
					),
					'checkout_text'  => array(
						'type'            => 'text',
						'label'           => __( 'Checkout Button Text', 'fw' ),
						'desc'            => __( 'Replaces the "Checkout" button label (e.g. "Check Out Sweetness"). Leave empty to keep "Checkout".', 'fw' ),
						'value'           => '',
						'dynamic_content' => false,
					),
					'footnote'       => array(
						'type'            => 'text',
						'label'           => __( 'Footnote', 'fw' ),
						'desc'            => __( 'Small reassurance line under the checkout button (e.g. a free-gift or delivery note). Leave empty for none.', 'fw' ),
						'value'           => '',
						'dynamic_content' => false,
					),
				),
			),
			// Empty-cart middle content — WooCommerce has no hook for its mini-cart empty
			// branch, so these replace the plain "No products in the cart." (all empty =
			// keep default; power users can hook upwc_mini_cart_empty* in PHP instead).
			'group_empty' => array(
				'type'    => 'group',
				'options' => array(
					'empty_icon'         => array(
						'type'         => 'icon',
						'label'        => __( 'Empty — Icon', 'fw' ),
						'help'         => __( 'Icon/emoji shown when the cart is empty (e.g. a cupcake). Leave blank for none.', 'fw' ),
						'value'        => array(),
						'preview_size' => 'small',
						'modal_size'   => 'medium',
					),
					'empty_heading'      => array(
						'type'  => 'text',
						'label' => __( 'Empty — Heading', 'fw' ),
						'desc'  => __( 'Empty-cart heading (e.g. "Your basket is totally empty!").', 'fw' ),
						'value' => '',
					),
					'empty_text'         => array(
						'type'  => 'text',
						'label' => __( 'Empty — Text', 'fw' ),
						'desc'  => __( 'Empty-cart sub-text.', 'fw' ),
						'value' => '',
					),
					'empty_button_label' => array(
						'type'  => 'text',
						'label' => __( 'Empty — Button Label', 'fw' ),
						'desc'  => __( 'Empty-cart call-to-action (e.g. "Browse Sweets"). Empty for no button.', 'fw' ),
						'value' => '',
					),
					'empty_button_url'   => array(
						'type'  => 'text',
						'label' => __( 'Empty — Button URL', 'fw' ),
						'desc'  => __( 'Where the empty-cart button links (e.g. #flavors or the Shop). Empty = the Shop page.', 'fw' ),
						'value' => '',
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

<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

// SVG flex-diagram icons for the Card Rows designer (Direction / Distribute / Align pickers).
// Derive the URL from this file's location (robust whether or not the WooCommerce extension
// object is resolvable at options-parse time).
$upwc_layout_img = plugins_url( 'static/img/layout', __FILE__ );
$upwc_swatch     = function ( $file, $title ) use ( $upwc_layout_img ) {
	return array( 'small' => array( 'src' => $upwc_layout_img . '/' . $file, 'height' => 34, 'title' => $title ) );
};

/*
|--------------------------------------------------------------------------
| Product category choices — populated from the product_cat taxonomy.
| Guarded so this options file is safe to load when WooCommerce is inactive
| (the taxonomy won't exist, so we just offer "All Categories").
|--------------------------------------------------------------------------
*/
$wc_cat_choices = array( '' => __( 'All Categories', 'fw' ) );
if ( function_exists( 'taxonomy_exists' ) && taxonomy_exists( 'product_cat' ) ) {
	$wc_terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
		)
	);
	if ( ! is_wp_error( $wc_terms ) ) {
		foreach ( $wc_terms as $wc_term ) {
			$wc_cat_choices[ $wc_term->slug ] = $wc_term->name;
		}
	}
}

// Explicit yes/no switch so the stored att value is always predictable in view.php.
if ( ! function_exists( 'upwc_products_switch' ) ) {
	function upwc_products_switch( $label, $desc = '', $value = 'yes' ) {
		return array(
			'type'         => 'switch',
			'label'        => $label,
			'desc'         => $desc,
			'left-choice'  => array( 'value' => 'no', 'label' => __( 'No', 'fw' ) ),
			'right-choice' => array( 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ),
			'value'        => $value,
		);
	}
}

/*
 * Tab map (organised by concern):
 *   Content    — WHICH products (source + order / query).
 *   Grid       — HOW the grid arranges (layout, columns, gap, alignment, pagination, arrows).
 *   Card       — WHAT each card shows: Elements (content bits) · Badges (corner overlays) ·
 *                Card Layout (the slot designer + image ratio & size).
 *   Animations / Advanced — framework tabs.
 * Option ids are unchanged from the previous single-tab layout — this is a UI regrouping only,
 * so saved values and the renderer are unaffected.
 */
$options = array(

	'tab_content' => array(
		'title'   => __( 'Content', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'group_source' => array(
				'type'    => 'group',
				'options' => array(
					'source'         => array(
						'type'    => 'select',
						'label'   => __( 'Source', 'fw' ),
						'desc'    => __( 'Which products to display.', 'fw' ),
						'choices' => array(
							'recent'       => __( 'Recent', 'fw' ),
							'featured'     => __( 'Featured', 'fw' ),
							'sale'         => __( 'On Sale', 'fw' ),
							'best_selling' => __( 'Best Selling', 'fw' ),
							'top_rated'    => __( 'Top Rated', 'fw' ),
							'category'        => __( 'By Category', 'fw' ),
							'tag'             => __( 'By Tag', 'fw' ),
							'attribute'       => __( 'By Attribute', 'fw' ),
							'ids'             => __( 'Specific Products', 'fw' ),
							'recently_viewed' => __( 'Recently Viewed', 'fw' ),
							'cross_sells'     => __( 'Cross-sells (Cart)', 'fw' ),
						),
						'value'   => 'recent',
					),
					'category'       => array(
						'type'    => 'select',
						'label'   => __( 'Category', 'fw' ),
						'desc'    => __( 'With source "By Category" this picks the category; for other sources it further filters the results. "All Categories" applies no filter.', 'fw' ),
						'choices' => $wc_cat_choices,
						'value'   => '',
					),
					'tags'           => array(
						'type'            => 'text',
						'label'           => __( 'Tags', 'fw' ),
						'desc'            => __( 'Source "By Tag": comma-separated product tag slugs (e.g. summer, new).', 'fw' ),
						'value'           => '',
						'dynamic_content' => false,
					),
					'attribute'      => array(
						'type'            => 'text',
						'label'           => __( 'Attribute', 'fw' ),
						'desc'            => __( 'Source "By Attribute": attribute slug without the "pa_" prefix (e.g. color).', 'fw' ),
						'value'           => '',
						'dynamic_content' => false,
					),
					'attribute_terms' => array(
						'type'            => 'text',
						'label'           => __( 'Attribute Terms', 'fw' ),
						'desc'            => __( 'Source "By Attribute": comma-separated term slugs of that attribute (e.g. red, blue). Leave empty for any.', 'fw' ),
						'value'           => '',
						'dynamic_content' => false,
					),
					'product_ids'    => array(
						'type'            => 'text',
						'label'           => __( 'Product IDs', 'fw' ),
						'desc'            => __( 'Source "Specific Products": comma-separated product IDs, in the order to show them.', 'fw' ),
						'value'           => '',
						'dynamic_content' => false,
					),
					'posts_per_page' => array(
						'type'            => 'text',
						'label'           => __( 'Number of Products', 'fw' ),
						'desc'            => __( 'How many products to show. Use -1 for all. (Ignored for "Specific Products".)', 'fw' ),
						'value'           => '8',
						'dynamic_content' => false,
					),
				),
			),
			'group_order'   => array(
				'type'    => 'group',
				'options' => array(
					'orderby' => array(
						'type'    => 'select',
						'label'   => __( 'Order By', 'fw' ),
						'desc'    => __( 'Best Selling / Top Rated / On Sale sources set their own ordering and ignore this.', 'fw' ),
						'choices' => array(
							'date'       => __( 'Date', 'fw' ),
							'title'      => __( 'Title', 'fw' ),
							'price'      => __( 'Price', 'fw' ),
							'popularity' => __( 'Popularity (sales)', 'fw' ),
							'rating'     => __( 'Average Rating', 'fw' ),
							'menu_order' => __( 'Menu Order', 'fw' ),
							'rand'       => __( 'Random', 'fw' ),
						),
						'value'   => 'date',
					),
					'order'   => array(
						'type'    => 'select',
						'label'   => __( 'Order', 'fw' ),
						'choices' => array(
							'DESC' => __( 'Descending', 'fw' ),
							'ASC'  => __( 'Ascending', 'fw' ),
						),
						'value'   => 'DESC',
					),
				),
			),
		),
	),

	'tab_grid' => array(
		'title'   => __( 'Grid', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'group_grid' => array(
				'type'    => 'group',
				'options' => array(
					'layout'      => array(
						'type'    => 'image-picker',
						'label'   => __( 'Layout', 'fw' ),
						'desc'    => __( 'Grid wraps products into rows; Carousel lays them in a horizontal swipe/scroll row with optional arrows.', 'fw' ),
						'value'   => 'grid',
						'choices' => array(
							'grid'     => array( 'small' => array( 'src' => $upwc_layout_img . '/grid.svg',     'height' => 52, 'title' => __( 'Grid', 'fw' ) ) ),
							'carousel' => array( 'small' => array( 'src' => $upwc_layout_img . '/carousel.svg', 'height' => 52, 'title' => __( 'Carousel', 'fw' ) ) ),
						),
					),
					'columns'     => array(
						'type'    => 'select',
						'label'   => __( 'Columns', 'fw' ),
						'desc'    => __( 'Products per row (Grid) or visible slides (Carousel) on desktop; collapses on smaller screens.', 'fw' ),
						'choices' => array( '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6' ),
						'value'   => '4',
					),
					'gap'         => array(
						'type'    => 'select',
						'label'   => __( 'Gap', 'fw' ),
						'choices' => array(
							'sm' => __( 'Small', 'fw' ),
							'md' => __( 'Medium', 'fw' ),
							'lg' => __( 'Large', 'fw' ),
						),
						'value'   => 'md',
					),
					'alignment'   => function_exists( 'sc_alignment_field' )
						? sc_alignment_field(
							array(
								'label'   => __( 'Text Alignment', 'fw' ),
								'inherit' => true,
								'desc'    => __( 'Alignment of the title / price / button inside each card. Inherit follows the theme.', 'fw' ),
							)
						)
						: array(
							'type'    => 'select',
							'label'   => __( 'Text Alignment', 'fw' ),
							'choices' => array(
								''       => __( 'Inherit', 'fw' ),
								'left'   => __( 'Left', 'fw' ),
								'center' => __( 'Center', 'fw' ),
								'right'  => __( 'Right', 'fw' ),
							),
							'value'   => '',
						),
					'pagination'  => array(
						'type'    => 'select',
						'label'   => __( 'Pagination', 'fw' ),
						'desc'    => __( 'Grid layout only: add a "Load More" button to reveal more products via AJAX.', 'fw' ),
						'choices' => array(
							'none'      => __( 'None', 'fw' ),
							'load_more' => __( 'Load More', 'fw' ),
						),
						'value'   => 'none',
					),
					'carousel_arrows' => upwc_products_switch( __( 'Carousel Arrows', 'fw' ), __( 'Show prev / next arrows on the Carousel layout.', 'fw' ), 'yes' ),
				),
			),
		),
	),

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

<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
/**
 * Product Filters element — renders a PANEL of WooCommerce filter widgets
 * (via the_widget()). These widgets are designed for shop / product-taxonomy
 * archives; on other pages they may render little or nothing. In scope: $atts.
 */

if ( ! class_exists( 'WooCommerce' )  ) {
	if ( fw_is_editor_context() && function_exists( 'sc_editor_notice' ) ) {
		echo sc_editor_notice( __( 'WooCommerce is not active, so this element cannot render.', 'fw' ) );
	}
	return;
}

$widget_map = array(
	'price'     => 'WC_Widget_Price_Filter',
	'rating'    => 'WC_Widget_Rating_Filter',
	'active'    => 'WC_Widget_Layered_Nav_Filters',
	'attribute' => 'WC_Widget_Layered_Nav',
);

/* ---- Build the list of blocks ---------------------------------------------- */
$blocks = array();
$raw    = isset( $atts['filters'] ) && is_array( $atts['filters'] ) ? $atts['filters'] : array();
if ( $raw ) {
	foreach ( $raw as $b ) {
		if ( is_array( $b ) && ! empty( $b['type'] ) ) {
			$blocks[] = $b;
		}
	}
}
// Legacy fallback: a single flat filter (pre-panel instances).
if ( ! $blocks ) {
	$blocks[] = array(
		'type'         => isset( $atts['filter'] ) ? (string) $atts['filter'] : 'price',
		'title'        => isset( $atts['title'] ) ? (string) $atts['title'] : '',
		'attribute'    => isset( $atts['attribute'] ) ? (string) $atts['attribute'] : '',
		'display_type' => isset( $atts['display_type'] ) ? (string) $atts['display_type'] : 'list',
		'query_type'   => isset( $atts['query_type'] ) ? (string) $atts['query_type'] : 'and',
	);
}

/* ---- Panel styling ---------------------------------------------------------- */
$truthy      = function_exists( 'upwc_wc_truthy' ) ? 'upwc_wc_truthy' : static function ( $v ) { return $v === 'yes' || $v === true; };
$collapsible = isset( $atts['collapsible'] ) && call_user_func( $truthy, $atts['collapsible'] );
$divider     = ! isset( $atts['divider'] ) || call_user_func( $truthy, $atts['divider'] );
$panel_title = isset( $atts['panel_title'] ) ? trim( (string) $atts['panel_title'] ) : '';
$box_class   = function_exists( 'sc_card_box_style_class' ) ? sc_card_box_style_class( $atts ) : '';

$panel_classes = array( 'upwc-product-filters' );
if ( $collapsible ) { $panel_classes[] = 'upwc-product-filters--collapsible'; }
if ( $divider ) { $panel_classes[] = 'upwc-product-filters--divided'; }
if ( '' !== $box_class ) { $panel_classes[] = $box_class; }

/* ---- Render each block, capturing widget output ----------------------------- */
$blocks_html = '';
foreach ( $blocks as $b ) {
	$type         = (string) $b['type'];
	$widget_class = isset( $widget_map[ $type ] ) ? $widget_map[ $type ] : '';
	if ( '' === $widget_class || ! class_exists( $widget_class ) ) {
		continue;
	}

	$instance   = array();
	$block_title = isset( $b['title'] ) ? trim( (string) $b['title'] ) : '';

	if ( 'attribute' === $type ) {
		$attr = isset( $b['attribute'] ) ? sanitize_title( preg_replace( '/^pa_/', '', (string) $b['attribute'] ) ) : '';
		if ( '' === $attr ) {
			continue;
		}
		$instance['attribute']    = $attr;
		$instance['display_type'] = ( isset( $b['display_type'] ) && 'dropdown' === $b['display_type'] ) ? 'dropdown' : 'list';
		$instance['query_type']   = ( isset( $b['query_type'] ) && 'or' === $b['query_type'] ) ? 'or' : 'and';
	}

	// Let the widget print its OWN title (as a toggle button when collapsible) so the
	// heading is inside the block wrapper; empty title → no heading from the widget.
	$title_open  = $collapsible
		? '<button type="button" class="upwc-pf__block-title" aria-expanded="true">'
		: '<h3 class="upwc-pf__block-title">';
	$title_close = $collapsible ? '</button>' : '</h3>';

	$args = array(
		'before_widget' => '<div class="upwc-pf__block">',
		'after_widget'  => '</div>',
		'before_title'  => $title_open,
		'after_title'   => $title_close,
	);
	if ( '' !== $block_title ) {
		$instance['title'] = $block_title;
	}

	ob_start();
	// The widget prints its title (if any) then a body; wrap the body so collapsible
	// can hide it. We can't hook between them, so wrap the whole widget and let CSS
	// target the first heading vs. the rest.
	the_widget( $widget_class, $instance, $args );
	$blocks_html .= ob_get_clean();
}

if ( '' === trim( $blocks_html ) ) {
	// The filter widgets render nothing when there is nothing to filter — no
	// products, or no attributes and categories in use yet.
	if ( fw_is_editor_context() && function_exists( 'sc_editor_notice' ) ) {
		echo sc_editor_notice( __( 'No filters to show yet — filters are built from the attributes and categories your products use.', 'fw' ) );
	}
	return;
}

echo '<div class="' . esc_attr( implode( ' ', $panel_classes ) ) . '">';
if ( '' !== $panel_title ) {
	echo '<h2 class="upwc-product-filters__title">' . esc_html( $panel_title ) . '</h2>';
}
echo $blocks_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — widget output.
echo '</div>';

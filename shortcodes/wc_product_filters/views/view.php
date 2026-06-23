<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
/**
 * Product Filters element — renders a WooCommerce filter widget via the_widget().
 * These widgets are designed for shop / product-taxonomy archives; on other
 * pages they may render little or nothing. In scope: $atts.
 */

if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}

$type = isset( $atts['filter'] ) ? (string) $atts['filter'] : 'price';

$map = array(
	'price'     => 'WC_Widget_Price_Filter',
	'rating'    => 'WC_Widget_Rating_Filter',
	'active'    => 'WC_Widget_Layered_Nav_Filters',
	'attribute' => 'WC_Widget_Layered_Nav',
);
$widget_class = isset( $map[ $type ] ) ? $map[ $type ] : '';
if ( $widget_class === '' || ! class_exists( $widget_class ) ) {
	return;
}

$instance = array();
$title    = isset( $atts['title'] ) ? (string) $atts['title'] : '';
if ( $title !== '' ) {
	$instance['title'] = $title;
}

if ( $type === 'attribute' ) {
	$attr = isset( $atts['attribute'] ) ? sanitize_title( preg_replace( '/^pa_/', '', (string) $atts['attribute'] ) ) : '';
	if ( $attr === '' ) {
		return;
	}
	$instance['attribute']    = $attr;
	$instance['display_type'] = ( isset( $atts['display_type'] ) && $atts['display_type'] === 'dropdown' ) ? 'dropdown' : 'list';
	$instance['query_type']   = ( isset( $atts['query_type'] ) && $atts['query_type'] === 'or' ) ? 'or' : 'and';
}

$args = array(
	'before_widget' => '<div class="upwc-product-filters widget">',
	'after_widget'  => '</div>',
	'before_title'  => '<h3 class="upwc-product-filters__title">',
	'after_title'   => '</h3>',
);

the_widget( $widget_class, $instance, $args );

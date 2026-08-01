<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
/**
 * Product Categories element — frontend render.
 *
 * Builds a grid of category cards from the shared Card Rows designer (image / name /
 * count / button), skinned with a Card Box Style preset. In scope: $atts. Inert when
 * WooCommerce is inactive.
 */

if ( ! class_exists( 'WooCommerce' ) || ! taxonomy_exists( 'product_cat' ) ) {
	return;
}

/* ---- Resolve options -------------------------------------------------------- */
$number     = isset( $atts['number'] ) ? max( 0, (int) $atts['number'] ) : 0;
$columns    = isset( $atts['columns'] ) ? max( 1, (int) $atts['columns'] ) : 4;
$orderby    = isset( $atts['orderby'] ) ? preg_replace( '/[^a-z_]/', '', (string) $atts['orderby'] ) : 'name';
$order      = ( isset( $atts['order'] ) && strtoupper( (string) $atts['order'] ) === 'DESC' ) ? 'DESC' : 'ASC';
$parent     = isset( $atts['parent'] ) ? trim( (string) $atts['parent'] ) : '';
$ids_raw    = isset( $atts['ids'] ) ? preg_replace( '/[^0-9,]/', '', (string) $atts['ids'] ) : '';
$hide_empty = ! isset( $atts['hide_empty'] ) || ( function_exists( 'upwc_wc_truthy' ) ? upwc_wc_truthy( $atts['hide_empty'] ) : $atts['hide_empty'] === 'yes' );
$gap        = isset( $atts['gap'] ) ? preg_replace( '/[^a-z]/', '', (string) $atts['gap'] ) : 'md';
$alignment  = isset( $atts['alignment'] ) ? preg_replace( '/[^a-z]/', '', (string) $atts['alignment'] ) : '';
$image_ratio = isset( $atts['image_ratio'] ) ? preg_replace( '/[^a-z]/', '', (string) $atts['image_ratio'] ) : 'auto';
$button_text = isset( $atts['button_text'] ) && '' !== trim( (string) $atts['button_text'] ) ? trim( (string) $atts['button_text'] ) : __( 'View', 'fw' );

// Image Size (width) → a CSS length for the --upwc-img-size custom property.
$image_size = '';
if ( isset( $atts['image_size'] ) && is_array( $atts['image_size'] ) && isset( $atts['image_size']['value'] ) && '' !== (string) $atts['image_size']['value'] ) {
	$unit       = ( isset( $atts['image_size']['unit'] ) && preg_match( '/^[a-z%]+$/', $atts['image_size']['unit'] ) ) ? $atts['image_size']['unit'] : 'px';
	$image_size = (float) $atts['image_size']['value'] . $unit;
}

$box_class = function_exists( 'sc_card_box_style_class' ) ? sc_card_box_style_class( $atts ) : '';

/* ---- Query the categories --------------------------------------------------- */
$q_args = array(
	'taxonomy'   => 'product_cat',
	'orderby'    => in_array( $orderby, array( 'name', 'slug', 'count', 'menu_order' ), true ) ? $orderby : 'name',
	'order'      => $order,
	'hide_empty' => $hide_empty,
);
if ( 'menu_order' === $q_args['orderby'] ) {
	// WooCommerce stores category menu order as term meta; use its ordering helper key.
	$q_args['orderby']  = 'meta_value_num';
	$q_args['meta_key'] = 'order'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
}
if ( '' !== $ids_raw ) {
	$q_args['include'] = array_filter( array_map( 'intval', explode( ',', $ids_raw ) ) );
} else {
	if ( $number > 0 ) {
		$q_args['number'] = $number;
	}
	if ( '' !== $parent && is_numeric( $parent ) ) {
		$q_args['parent'] = (int) $parent;
	}
}

$terms = get_terms( $q_args );
if ( is_wp_error( $terms ) || empty( $terms ) ) {
	return;
}

/* ---- Card Rows model -------------------------------------------------------- */
$rows = function_exists( 'sc_card_rows_value' ) ? sc_card_rows_value( $atts, 'card_rows' ) : array();

/* ---- Wrapper ---------------------------------------------------------------- */
$wrap_classes = array(
	'upwc-cats',
	'upwc-cats--gap-' . ( '' !== $gap ? $gap : 'md' ),
	'upwc-cats--ratio-' . ( '' !== $image_ratio ? $image_ratio : 'auto' ),
);
if ( '' !== $alignment ) {
	$wrap_classes[] = 'upwc-cats--align-' . $alignment;
}
$wrap_style = '--upwc-cat-cols:' . (int) $columns . ';';
if ( '' !== $image_size ) {
	$wrap_style .= '--upwc-img-size:' . $image_size . ';';
}

echo '<div class="' . esc_attr( implode( ' ', $wrap_classes ) ) . '" style="' . esc_attr( $wrap_style ) . '">';

foreach ( $terms as $term ) {
	if ( ! is_object( $term ) || empty( $term->term_id ) ) {
		continue;
	}
	$link = get_term_link( $term );
	if ( is_wp_error( $link ) ) {
		$link = '#';
	}

	// Thumbnail (Woo stores the category image as the 'thumbnail_id' term meta).
	$thumb_id  = (int) get_term_meta( $term->term_id, 'thumbnail_id', true );
	$img_html  = $thumb_id ? wp_get_attachment_image( $thumb_id, 'woocommerce_thumbnail' ) : '';
	if ( '' === $img_html && function_exists( 'wc_placeholder_img' ) ) {
		$img_html = wc_placeholder_img( 'woocommerce_thumbnail' );
	}

	// Slot map (a slot renders only when it has content).
	$slots = array(
		'image'  => '' !== $img_html ? '<a class="upwc-cat__img" href="' . esc_url( $link ) . '">' . $img_html . '</a>' : '',
		'title'  => '<h3 class="upwc-cat__title"><a href="' . esc_url( $link ) . '">' . esc_html( $term->name ) . '</a></h3>',
		'count'  => '<span class="upwc-cat__count">' . esc_html( sprintf( _n( '%s product', '%s products', (int) $term->count, 'fw' ), number_format_i18n( (int) $term->count ) ) ) . '</span>',
		'button' => '<a class="upwc-cat__button button" href="' . esc_url( $link ) . '">' . esc_html( $button_text ) . '</a>',
	);

	if ( $rows && function_exists( 'sc_card_rows_render' ) ) {
		$card_body = sc_card_rows_render( $rows, $slots, 'upwc-cat' );
	} else {
		// Fallback: image · name · count stacked.
		$card_body = $slots['image'] . $slots['title'] . $slots['count'];
	}

	$card_classes = 'upwc-cat-card';
	if ( '' !== $box_class ) {
		$card_classes .= ' ' . $box_class;
	}

	echo '<div class="' . esc_attr( $card_classes ) . '">' . $card_body . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

echo '</div>';

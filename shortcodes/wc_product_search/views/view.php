<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
/**
 * Product Search element — a product-scoped search form (post_type=product) with
 * flexible layout, field shape/size, a themed submit button, width and alignment.
 * Self-contained clean markup. In scope: $atts.
 */

if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}

$placeholder = isset( $atts['placeholder'] ) && '' !== $atts['placeholder']
	? (string) $atts['placeholder']
	: __( 'Search products…', 'fw' );

$layout      = isset( $atts['layout'] ) && in_array( $atts['layout'], array( 'attached', 'below', 'compact' ), true ) ? $atts['layout'] : 'attached';
$field_shape = isset( $atts['field_shape'] ) && in_array( $atts['field_shape'], array( 'default', 'pill', 'rounded', 'square' ), true ) ? $atts['field_shape'] : 'default';
$size        = isset( $atts['size'] ) && in_array( $atts['size'], array( 'sm', 'md', 'lg' ), true ) ? $atts['size'] : 'md';
$width       = ( isset( $atts['width'] ) && 'full' === $atts['width'] ) ? 'full' : '';
$align       = isset( $atts['alignment'] ) && in_array( $atts['alignment'], array( 'left', 'center', 'right' ), true ) ? $atts['alignment'] : '';
$btn_style   = isset( $atts['button_style'] ) ? trim( (string) $atts['button_style'] ) : '';
$button_text = isset( $atts['button_text'] ) ? trim( (string) $atts['button_text'] ) : '';

// Button icon: chosen icon → shared renderer, else the default magnifier SVG.
$icon_html = '';
if ( ! empty( $atts['button_icon'] ) && function_exists( 'sc_icon_render' ) ) {
	$icon_html = sc_icon_render( $atts['button_icon'], array( 'aria_hidden' => true ) );
}
if ( '' === $icon_html ) {
	$icon_html = '<svg class="upwc-product-search__icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><circle cx="7" cy="7" r="5"></circle><line x1="11" y1="11" x2="14.5" y2="14.5"></line></svg>';
}

// Submit button contents + classes. Compact = icon-only plain button; Attached /
// Below = a real button that can wear a Button Style preset + shows the label.
$submit_classes = array( 'upwc-product-search__submit' );
if ( 'compact' === $layout ) {
	$submit_content = $icon_html;
} else {
	$submit_content = $icon_html;
	if ( '' !== $button_text ) {
		$submit_content .= '<span class="upwc-product-search__btntext">' . esc_html( $button_text ) . '</span>';
	}
	if ( '' !== $btn_style ) {
		$submit_classes[] = 'btn';
		$submit_classes[] = $btn_style;
	}
}
$submit_label = '' !== $button_text ? $button_text : __( 'Search', 'fw' );

// Form wrapper classes.
$form_classes = array(
	'upwc-product-search',
	'upwc-product-search--' . $layout,
	'upwc-product-search--' . $size,
	'upwc-product-search--shape-' . $field_shape,
);
if ( 'full' === $width ) {
	$form_classes[] = 'upwc-product-search--full';
}

// Alignment wrapper (moot at full width).
$align_open  = '';
$align_close = '';
if ( '' !== $align && 'full' !== $width ) {
	$align_open  = '<div class="upwc-product-search-align" style="text-align:' . esc_attr( $align ) . ';">';
	$align_close = '</div>';
}

echo $align_open; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
?>
<form role="search" method="get" class="<?php echo esc_attr( implode( ' ', $form_classes ) ); ?>" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="upwc-product-search-field"><?php esc_html_e( 'Search for products:', 'fw' ); ?></label>
	<input type="search" id="upwc-product-search-field" class="upwc-product-search__field" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php echo esc_attr( $placeholder ); ?>" />
	<input type="hidden" name="post_type" value="product" />
	<button type="submit" class="<?php echo esc_attr( implode( ' ', $submit_classes ) ); ?>" aria-label="<?php echo esc_attr( $submit_label ); ?>"><?php echo $submit_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
</form>
<?php
echo $align_close; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

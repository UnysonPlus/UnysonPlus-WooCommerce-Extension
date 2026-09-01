<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
/**
 * Variation swatches.
 *
 * A dropdown is the worst possible control for "which colour?" — it hides the
 * options behind a click and describes them in words. This turns the variation
 * dropdowns on a variable product into swatches, and can put the same swatches
 * on shop cards so a colour can be chosen before the product page is opened.
 *
 * How an attribute is drawn is decided from the DATA, not from a per-attribute
 * setting screen:
 *
 *   - a global attribute whose terms carry a colour (term meta, in the key any
 *     of the common swatch plugins use) -> colour dots;
 *   - terms carrying an image -> image swatches;
 *   - anything else -> labelled buttons ("S", "M", "42").
 *
 * Past a threshold the list stops being scannable, so the dropdown stays — a
 * swatch grid of sixty options is worse than the select it replaced.
 *
 * The single-product swatches drive WooCommerce's OWN hidden `<select>`: they
 * set its value and dispatch `change`, so variation matching, price updates,
 * gallery switching and add-to-cart all keep working exactly as they do
 * natively. Nothing here re-implements variation logic.
 *
 * @package unysonplus
 */

if ( ! function_exists( 'upwc_swatches_enabled' ) ) :
/**
 * @return bool
 */
function upwc_swatches_enabled() {
	if ( ! function_exists( 'fw_get_db_ext_settings_option' ) ) {
		return false;
	}

	return upwc_wc_truthy( fw_get_db_ext_settings_option( 'woocommerce', 'swatches' ) );
}
endif;

if ( ! function_exists( 'upwc_swatches_on_cards' ) ) :
/**
 * @return bool
 */
function upwc_swatches_on_cards() {
	return upwc_swatches_enabled()
		&& function_exists( 'fw_get_db_ext_settings_option' )
		&& upwc_wc_truthy( fw_get_db_ext_settings_option( 'woocommerce', 'swatches_cards' ) );
}
endif;

if ( ! function_exists( 'upwc_swatches_max_options' ) ) :
/**
 * Above this many options an attribute keeps its dropdown.
 *
 * @return int
 */
function upwc_swatches_max_options() {
	return (int) apply_filters( 'upwc_swatches_max_options', 15 );
}
endif;

if ( ! function_exists( 'upwc_swatch_term_visual' ) ) :
/**
 * The colour or image a term carries, if any.
 *
 * Reads the meta keys the common swatch plugins write, so a store migrating
 * from one of them keeps its swatches without re-entering anything.
 *
 * @param WP_Term $term
 * @return array{type:string,value:string}
 */
function upwc_swatch_term_visual( $term ) {
	if ( ! $term instanceof WP_Term ) {
		return array( 'type' => 'label', 'value' => '' );
	}

	$color_keys = apply_filters( 'upwc_swatch_color_meta_keys', array(
		'upwc_swatch_color', 'product_attribute_color', 'pa_color_swatches_id',
		'_swatch_color', 'color',
	) );
	foreach ( (array) $color_keys as $key ) {
		$value = get_term_meta( $term->term_id, $key, true );
		if ( is_string( $value ) && preg_match( '/^#?[0-9a-f]{3,8}$/i', trim( $value ) ) ) {
			$value = trim( $value );
			return array( 'type' => 'color', 'value' => ( '#' === $value[0] ? $value : '#' . $value ) );
		}
	}

	$image_keys = apply_filters( 'upwc_swatch_image_meta_keys', array(
		'upwc_swatch_image', 'product_attribute_image', '_swatch_image', 'thumbnail_id',
	) );
	foreach ( (array) $image_keys as $key ) {
		$value = get_term_meta( $term->term_id, $key, true );
		if ( $value ) {
			$url = is_numeric( $value ) ? wp_get_attachment_image_url( (int) $value, 'thumbnail' ) : $value;
			if ( $url ) {
				return array( 'type' => 'image', 'value' => $url );
			}
		}
	}

	return array( 'type' => 'label', 'value' => '' );
}
endif;

if ( ! function_exists( 'upwc_swatch_item_html' ) ) :
/**
 * One swatch.
 *
 * @param string $attribute Attribute key (e.g. pa_color, or a custom name).
 * @param string $slug      Option value as WooCommerce stores it.
 * @param string $label     Human label.
 * @param array  $visual    From upwc_swatch_term_visual().
 * @param bool   $as_link   Card mode: a link to the product with the option preselected.
 * @param string $href      Used when $as_link.
 * @return string
 */
function upwc_swatch_item_html( $attribute, $slug, $label, $visual, $as_link = false, $href = '' ) {
	$classes = array( 'upwc-swatch', 'upwc-swatch--' . $visual['type'] );
	$style   = '';
	$inner   = esc_html( $label );

	if ( 'color' === $visual['type'] ) {
		$style = ' style="--upwc-swatch-color:' . esc_attr( $visual['value'] ) . '"';
		$inner = '<span class="screen-reader-text">' . esc_html( $label ) . '</span>';
	} elseif ( 'image' === $visual['type'] ) {
		$inner = '<img src="' . esc_url( $visual['value'] ) . '" alt="" aria-hidden="true" />'
			. '<span class="screen-reader-text">' . esc_html( $label ) . '</span>';
	}

	$attrs = ' class="' . esc_attr( implode( ' ', $classes ) ) . '"'
		. ' data-attribute="' . esc_attr( $attribute ) . '"'
		. ' data-value="' . esc_attr( $slug ) . '"'
		. ' title="' . esc_attr( $label ) . '"'
		. $style;

	return $as_link
		? '<a href="' . esc_url( $href ) . '"' . $attrs . '>' . $inner . '</a>'
		: '<button type="button" aria-pressed="false"' . $attrs . '>' . $inner . '</button>';
}
endif;

if ( ! function_exists( 'upwc_swatches_dropdown_html' ) ) :
/**
 * Render swatches alongside WooCommerce's variation dropdown.
 *
 * Hooked on `woocommerce_dropdown_variation_attribute_options_html`, so the
 * original `<select>` is preserved (hidden by CSS) and keeps doing the work —
 * see the file header.
 *
 * @param string $html
 * @param array  $args
 * @return string
 * @internal
 */
function upwc_swatches_dropdown_html( $html, $args ) {
	if ( ! upwc_swatches_enabled() ) {
		return $html;
	}

	$options   = isset( $args['options'] ) ? (array) $args['options'] : array();
	$attribute = isset( $args['attribute'] ) ? (string) $args['attribute'] : '';
	$product   = isset( $args['product'] ) ? $args['product'] : null;

	if ( ! $options || ! $attribute || ! $product instanceof WC_Product ) {
		return $html;
	}
	if ( count( $options ) > upwc_swatches_max_options() ) {
		return $html;
	}

	$swatches = '';

	if ( taxonomy_exists( $attribute ) ) {
		$terms = wc_get_product_terms( $product->get_id(), $attribute, array( 'fields' => 'all' ) );
		foreach ( $terms as $term ) {
			if ( ! in_array( $term->slug, $options, true ) ) {
				continue;
			}
			$swatches .= upwc_swatch_item_html( $attribute, $term->slug, $term->name, upwc_swatch_term_visual( $term ) );
		}
	} else {
		// A product-level (custom) attribute: plain values, so labelled buttons.
		foreach ( $options as $option ) {
			$swatches .= upwc_swatch_item_html(
				$attribute,
				$option,
				apply_filters( 'woocommerce_variation_option_name', $option ),
				array( 'type' => 'label', 'value' => '' )
			);
		}
	}

	if ( '' === $swatches ) {
		return $html;
	}

	return '<div class="upwc-swatches upwc-swatches--select" data-attribute="' . esc_attr( $attribute ) . '">'
		. $swatches . '</div>' . $html;
}
endif;
add_filter( 'woocommerce_dropdown_variation_attribute_options_html', 'upwc_swatches_dropdown_html', 20, 2 );

if ( ! function_exists( 'upwc_swatches_card_html' ) ) :
/**
 * Swatches for a product CARD.
 *
 * These are links, not buttons: there is no variation form on a card, so each
 * one opens the product with that option preselected via the query string
 * WooCommerce itself reads (`?attribute_pa_color=red`).
 *
 * Only the FIRST swatchable attribute is shown — a card is a summary, and two
 * rows of swatches on a grid tile is noise.
 *
 * @param WC_Product $product
 * @return string
 */
function upwc_swatches_card_html( $product ) {
	if ( ! upwc_swatches_on_cards() || ! $product instanceof WC_Product || ! $product->is_type( 'variable' ) ) {
		return '';
	}

	foreach ( $product->get_variation_attributes() as $attribute => $options ) {
		$options = (array) $options;
		if ( ! $options || count( $options ) > upwc_swatches_max_options() ) {
			continue;
		}

		$key   = taxonomy_exists( $attribute ) ? $attribute : sanitize_title( $attribute );
		$items = '';

		if ( taxonomy_exists( $attribute ) ) {
			$terms = wc_get_product_terms( $product->get_id(), $attribute, array( 'fields' => 'all' ) );
			foreach ( $terms as $term ) {
				if ( ! in_array( $term->slug, $options, true ) ) {
					continue;
				}
				$items .= upwc_swatch_item_html(
					$key,
					$term->slug,
					$term->name,
					upwc_swatch_term_visual( $term ),
					true,
					add_query_arg( 'attribute_' . $key, $term->slug, $product->get_permalink() )
				);
			}
		} else {
			foreach ( $options as $option ) {
				$items .= upwc_swatch_item_html(
					$key,
					$option,
					$option,
					array( 'type' => 'label', 'value' => '' ),
					true,
					add_query_arg( 'attribute_' . $key, $option, $product->get_permalink() )
				);
			}
		}

		if ( '' !== $items ) {
			return '<div class="upwc-swatches upwc-swatches--card">' . $items . '</div>';
		}
	}

	return '';
}
endif;

<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'          => __( 'Add to Cart Button', 'fw' ),
	'description'    => __( 'A standalone add-to-cart button (with optional price) for one product — themed with the full Button Style presets.', 'fw' ),
	'tab'            => __( 'WooCommerce Elements', 'fw' ),
	'popup_size'     => 'medium',
	'title_template' => '
		{{
			var pid   = o["product"] || "";
			var label = ( o["label"] && ("" + o["label"]).replace(/^\s+|\s+$/g, "") ) || "Add to Cart";
		}}
		<span style="display:inline-flex;align-items:center;gap:8px;">
			<strong>{{= ( pid ? "Product #" + pid : "Pick a product" ) }}</strong>
			<span style="background:#2271b1;color:#fff;font-weight:600;font-size:12px;padding:3px 11px;border-radius:5px;">{{- label }}</span>
		</span>
	',
);

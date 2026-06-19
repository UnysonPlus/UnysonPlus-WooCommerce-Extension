<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'       => __( 'Products', 'fw' ),
	'description' => __( 'Display a grid of WooCommerce products — recent, featured, on-sale, best-selling, top-rated, or by category.', 'fw' ),
	'tab'         => __( 'WooCommerce Elements', 'fw' ),
	'popup_size'  => 'large',

	'title_template' => '
		{{ if ( o ) {
			var src   = o["source"]          || "recent";
			var count = o["posts_per_page"]  || 8;
			var cols  = o["columns"]         || 4;
		}}
			<div style="margin-top:.5rem; display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
				<strong>{{- src }}</strong>
				<span style="opacity:.6;">×</span>
				<span>{{- count }}</span>
				<span style="opacity:.4;">|</span>
				<em style="opacity:.7;">{{- cols }} cols</em>
			</div>
		{{ } }}
	',
);

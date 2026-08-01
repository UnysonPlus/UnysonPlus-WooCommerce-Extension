<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'          => __( 'Product Filters', 'fw' ),
	'description'    => __( 'A shop filter PANEL — stack Price, Attribute, Rating and Active-filter blocks in one styled, optionally-collapsible widget. Best placed in a shop / category sidebar.', 'fw' ),
	'tab'            => __( 'WooCommerce Elements', 'fw' ),
	'popup_size'     => 'medium',
	'title_template' => '
		{{
			var f = ( o["filters"] && o["filters"].length ) ? o["filters"] : [];
			var names = [];
			for ( var i = 0; i < f.length; i++ ) {
				var t = ( f[i] && f[i].type ) ? f[i].type : "price";
				if ( t === "attribute" && f[i].attribute ) { t = f[i].attribute; }
				names.push( t );
			}
			var label = names.length ? names.join( " · " ) : "price";
		}}
		<strong>{{- label }}</strong> <span style="opacity:.6">filter panel</span>
	',
);

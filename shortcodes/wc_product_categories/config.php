<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'       => __( 'Product Categories', 'fw' ),
	'description' => __( 'A grid of WooCommerce product-category cards — with the same flexible Card Rows layout and Card Box Style as Products.', 'fw' ),
	'tab'         => __( 'WooCommerce Elements', 'fw' ),
	'popup_size'  => 'large',

	// Builder label: columns + a mini WIREFRAME of the category card, honouring the
	// Card Rows (direction / distribute / align). Slots render as styled placeholders.
	'title_template' => '
		{{
			var cols = parseInt( o["columns"] || 4, 10 ) || 4;
			var rows = ( o["card_rows"] && o["card_rows"].length ) ? o["card_rows"] : [];

			function slot( s ) {
				if ( s === "image" || s === "media" ) {
					return \'<span style="display:block;flex:1 1 100%;height:80px;border-radius:6px;background:repeating-linear-gradient(45deg,#e7eaee,#e7eaee 8px,#eef1f4 8px,#eef1f4 16px)"></span>\';
				}
				var m = {
					title:  [ \'Category Name\',   \'font-weight:600;color:#1d2327\' ],
					count:  [ \'12 products\',      \'color:#787c82;font-size:11px\' ],
					button: [ \'View\',             \'background:#2271b1;color:#fff;font-weight:600;padding:3px 11px;border-radius:5px\' ]
				};
				var d = m[ s ] || [ ( "" + s ), \'background:#eef0f2;color:#3c434a;padding:1px 8px;border-radius:5px\' ];
				return \'<span style="display:inline-block;font-size:13px;line-height:1.5;\' + d[1] + \'">\' + d[0] + \'</span>\';
			}
			function rowStyle( r ) {
				var dir = ( r && r.direction === "stack" ) ? "flex-direction:column;" : "";
				var jc  = ( r && r.justify === "between" ) ? "space-between" : ( ( r && r.justify === "center" ) ? "center" : ( ( r && r.justify === "end" ) ? "flex-end" : "flex-start" ) );
				var ai  = ( r && r.align === "start" ) ? "flex-start" : ( ( r && r.align === "end" ) ? "flex-end" : ( ( r && r.align === "stretch" ) ? "stretch" : "center" ) );
				return dir + "justify-content:" + jc + ";align-items:" + ai + ";";
			}
		}}
		<style>.pb-item-type-simple:has(.upwc-cp-pb)>span{flex:1 1 auto;width:100%;text-align:center;}</style>
		<div class="upwc-cp-pb" style="margin-top:.5rem; text-align:center; font-size:13px;">
			<div style="display:flex; align-items:center; justify-content:center; gap:6px; flex-wrap:wrap;">
				<strong>{{= cols }} cols</strong>
				<span style="opacity:.35;">|</span>
				<em style="opacity:.7;">Category cards</em>
			</div>
			{{ if ( rows.length ) { }}
			<div style="margin-top:11px; display:inline-block; text-align:left; vertical-align:top;">
				<div style="width:190px; border:1px solid #e3e5e8; border-radius:8px; padding:11px; background:#fff; display:flex; flex-direction:column; gap:7px;">
					{{ for ( var i = 0; i < rows.length; i++ ) {
							var r = rows[i] || {};
							var slots = ( r.slots && r.slots.length ) ? r.slots : [];
					}}
					<div style="display:flex; gap:6px; flex-wrap:wrap; {{= rowStyle( r ) }}">
						{{ for ( var j = 0; j < slots.length; j++ ) { }}{{= slot( slots[j] ) }}{{ } }}
					</div>
					{{ } }}
				</div>
			</div>
			{{ } }}
		</div>
	',
);

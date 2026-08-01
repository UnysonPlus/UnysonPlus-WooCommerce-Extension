<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'       => __( 'Products', 'fw' ),
	'description' => __( 'Display a grid of WooCommerce products — recent, featured, on-sale, best-selling, top-rated, or by category.', 'fw' ),
	'tab'         => __( 'WooCommerce Elements', 'fw' ),
	'popup_size'  => 'large',

	// Builder label: the QUERY summary + a mini WIREFRAME GRID of the product card,
	// repeated by the product count in the configured columns. Each slot renders as a
	// styled placeholder (image block, bold title, green price, cart button, stars,
	// SALE badge, heart…) so it reads like a real product card, and each row honours
	// its direction / distribute / align. Real product data can't render client-side.
	'title_template' => '
		{{
			var src    = o["source"]         || "recent";
			var cat    = o["category"]       || "";
			var count  = parseInt( o["posts_per_page"] || 8, 10 ) || 8;
			var cols   = parseInt( o["columns"] || 4, 10 ) || 4;
			var layout = o["layout"]         || "grid";
			var rows   = ( o["card_rows"] && o["card_rows"].length ) ? o["card_rows"] : [];
			var srcLabel = ( src === "category" && cat ) ? ( "Category: " + cat ) : src;
			var CAP    = 12;
			var shown  = Math.max( 1, Math.min( count, CAP ) );
			var gridCols = cols < 1 ? 1 : cols;

			// One slot → styled placeholder HTML (mirrors the Card preview look).
			function slot( s ) {
				if ( s === "media" || s === "image" || s === "avatar" ) {
					return \'<span style="display:block;flex:1 1 100%;height:88px;border-radius:6px;background:repeating-linear-gradient(45deg,#e7eaee,#e7eaee 8px,#eef1f4 8px,#eef1f4 16px)"></span>\';
				}
				var m = {
					title:        [ \'Product Title\',                          \'font-weight:600;color:#1d2327\' ],
					name:         [ \'Product Title\',                          \'font-weight:600;color:#1d2327\' ],
					excerpt:      [ \'Short product description text.\',         \'color:#787c82;font-size:11px\' ],
					desc:         [ \'Short product description text.\',         \'color:#787c82;font-size:11px\' ],
					price:        [ \'$29.00\',                                  \'background:#e8f5ec;color:#135e29;font-weight:700;padding:2px 9px;border-radius:5px\' ],
					cart:         [ \'Add to Cart\',                             \'background:#2271b1;color:#fff;font-weight:600;padding:3px 11px;border-radius:5px\' ],
					button:       [ \'Button\',                                  \'background:#2271b1;color:#fff;font-weight:600;padding:3px 11px;border-radius:5px\' ],
					badges:       [ \'SALE\',                                    \'background:#d63638;color:#fff;font-weight:700;font-size:10px;letter-spacing:.03em;padding:2px 7px;border-radius:5px\' ],
					wishlist:     [ \'&#9829;\',                                 \'color:#d63638;border:1px solid #e3e5e8;border-radius:50%;width:22px;height:22px;display:inline-flex;align-items:center;justify-content:center\' ],
					rating:       [ \'&#9733;&#9733;&#9733;&#9733;&#9734;\',     \'color:#e6a817;letter-spacing:1px\' ],
					rating_count: [ \'(12)\',                                    \'color:#a7aaad;font-size:11px\' ],
					meta:         [ \'Author &middot; Date\',                    \'color:#a7aaad;font-size:11px\' ],
					cats:         [ \'Category\',                                \'border:1px solid #c3c4c7;color:#50575e;padding:1px 8px;border-radius:5px;font-size:11px\' ],
					readmore:     [ \'Read more &rarr;\',                        \'color:#2271b1;font-size:11px\' ],
					quickview:    [ \'Quick View\',                             \'border:1px solid #c3c4c7;color:#50575e;padding:2px 9px;border-radius:5px\' ],
					social:       [ \'in  f  x\',                               \'border:1px solid #c3c4c7;color:#50575e;letter-spacing:.1em;padding:2px 9px;border-radius:5px\' ]
				};
				var d = m[ s ] || [ ( "" + s ), \'background:#eef0f2;color:#3c434a;padding:1px 8px;border-radius:5px\' ];
				return \'<span style="display:inline-block;font-size:13px;line-height:1.5;\' + d[1] + \'">\' + d[0] + \'</span>\';
			}

			// One row → flex line honouring direction / distribute / align.
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
				<strong style="text-transform:capitalize;">{{- srcLabel }}</strong>
				<span style="opacity:.5;">×</span>
				<span>{{- count }}</span>
				<span style="opacity:.35;">|</span>
				<em style="opacity:.7; text-transform:capitalize;">{{- layout }} &middot; {{- cols }} cols</em>
			</div>
			{{ if ( rows.length ) { }}
			<div style="margin-top:11px; display:inline-grid; grid-template-columns:repeat({{= gridCols }},minmax(155px,205px)); gap:12px; text-align:left; vertical-align:top;">
				{{ for ( var n = 0; n < shown; n++ ) { }}
				<div style="border:1px solid #e3e5e8; border-radius:8px; padding:11px; background:#fff; display:flex; flex-direction:column; gap:7px;">
					{{ for ( var i = 0; i < rows.length; i++ ) {
							var r = rows[i] || {};
							var slots = ( r.slots && r.slots.length ) ? r.slots : [];
					}}
					<div style="display:flex; gap:6px; flex-wrap:wrap; {{= rowStyle( r ) }}">
						{{ for ( var j = 0; j < slots.length; j++ ) { }}{{= slot( slots[j] ) }}{{ } }}
					</div>
					{{ } }}
				</div>
				{{ } }}
			</div>
			{{ if ( count > shown ) { }}
			<div style="margin-top:6px; font-size:11px; opacity:.6;">+{{- ( count - shown ) }} more product{{= ( count - shown ) === 1 ? "" : "s" }}</div>
			{{ } }}
			{{ } }}
		</div>
	',
);

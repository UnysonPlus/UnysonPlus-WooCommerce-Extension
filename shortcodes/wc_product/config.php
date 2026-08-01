<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'       => __( 'Single Product', 'fw' ),
	'description' => __( 'Show one product as a fully designed card — the single-product counterpart to Products, with the same Card Rows layout, Card Box Style, rating, badges and Quick View.', 'fw' ),
	'tab'         => __( 'WooCommerce Elements', 'fw' ),
	'popup_size'  => 'large',

	// Builder label: which product + a mini WIREFRAME of the single card, honouring the
	// Card Rows (direction / distribute / align). Each slot renders as a styled placeholder
	// (image block, bold title, green price, cart button, stars, SALE badge, heart…) so it
	// reads like a real product card. Real product data can't render client-side.
	'title_template' => '
		{{
			var pid  = o["product"] || "";
			var rows = ( o["card_rows"] && o["card_rows"].length ) ? o["card_rows"] : [];

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
					quickview:    [ \'Quick View\',                             \'border:1px solid #c3c4c7;color:#50575e;padding:2px 9px;border-radius:5px\' ]
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
				<strong>{{= ( pid ? "Product #" + pid : "Pick a product" ) }}</strong>
			</div>
			{{ if ( rows.length ) { }}
			<div style="margin-top:11px; display:inline-block; text-align:left; vertical-align:top;">
				<div style="width:205px; border:1px solid #e3e5e8; border-radius:8px; padding:11px; background:#fff; display:flex; flex-direction:column; gap:7px;">
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

<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
/**
 * Classic widgets.
 *
 * Everything this extension offers has been page-builder only, which is fine
 * until you want a mini-cart in a theme's real sidebar, or a product grid in a
 * footer widget area. These wrap the SAME renderers the elements use, so a
 * widget and an element produce identical markup and neither can drift.
 *
 * Deliberately few: only the pieces that make sense in a narrow, repeated
 * column. Anything needing real layout control belongs in the builder.
 *
 * @package unysonplus
 */

if ( ! class_exists( 'UPWC_Widget_Base' ) ) :
/**
 * Shared plumbing: a title field, and the standard title markup.
 */
abstract class UPWC_Widget_Base extends WP_Widget {

	/**
	 * Extra fields beyond the title, as [ id => [ label, type, default, desc ] ].
	 *
	 * @return array
	 */
	protected function fields() {
		return array();
	}

	/**
	 * @param array $instance
	 * @return string
	 */
	public function form( $instance ) {
		$title = isset( $instance['title'] ) ? $instance['title'] : '';
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'fw' ); ?></label>
			<input class="widefat"
			       id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
			       name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
			       type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<?php
		foreach ( $this->fields() as $id => $field ) {
			$value = isset( $instance[ $id ] ) ? $instance[ $id ] : $field['default'];
			?>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( $id ) ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
				<?php if ( 'select' === $field['type'] ) : ?>
					<select class="widefat"
					        id="<?php echo esc_attr( $this->get_field_id( $id ) ); ?>"
					        name="<?php echo esc_attr( $this->get_field_name( $id ) ); ?>">
						<?php foreach ( $field['choices'] as $key => $label ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $value, $key ); ?>>
								<?php echo esc_html( $label ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				<?php else : ?>
					<input class="widefat"
					       id="<?php echo esc_attr( $this->get_field_id( $id ) ); ?>"
					       name="<?php echo esc_attr( $this->get_field_name( $id ) ); ?>"
					       type="<?php echo esc_attr( $field['type'] ); ?>"
					       value="<?php echo esc_attr( $value ); ?>" />
				<?php endif; ?>
				<?php if ( ! empty( $field['desc'] ) ) : ?>
					<small><?php echo esc_html( $field['desc'] ); ?></small>
				<?php endif; ?>
			</p>
			<?php
		}

		return '';
	}

	/**
	 * @param array $new
	 * @param array $old
	 * @return array
	 */
	public function update( $new, $old ) {
		$out          = $old;
		$out['title'] = isset( $new['title'] ) ? sanitize_text_field( $new['title'] ) : '';

		foreach ( $this->fields() as $id => $field ) {
			$out[ $id ] = isset( $new[ $id ] ) ? sanitize_text_field( $new[ $id ] ) : $field['default'];
		}

		return $out;
	}

	/**
	 * @param array $args
	 * @param array $instance
	 */
	protected function open( $args, $instance ) {
		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput

		$title = isset( $instance['title'] ) ? $instance['title'] : '';
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		if ( '' !== $title ) {
			echo $args['before_title'] . esc_html( $title ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput
		}
	}

	/**
	 * @param array $args
	 */
	protected function close( $args ) {
		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput
	}
}
endif;

if ( ! class_exists( 'UPWC_Widget_Mini_Cart' ) ) :
/**
 * The mini-cart, in a sidebar.
 */
class UPWC_Widget_Mini_Cart extends UPWC_Widget_Base {

	public function __construct() {
		parent::__construct(
			'upwc_mini_cart',
			__( 'UnysonPlus — Mini Cart', 'fw' ),
			array( 'description' => __( 'Cart icon with a live flyout of the cart contents.', 'fw' ) )
		);
	}

	protected function fields() {
		return array(
			'panel_style' => array(
				'label'   => __( 'Open as:', 'fw' ),
				'type'    => 'select',
				'default' => 'dropdown',
				'choices' => array(
					'dropdown' => __( 'Dropdown flyout', 'fw' ),
					'drawer'   => __( 'Drawer (side-cart)', 'fw' ),
				),
			),
		);
	}

	public function widget( $args, $instance ) {
		if ( ! function_exists( 'upwc_render_mini_cart' ) ) {
			return;
		}

		$html = upwc_render_mini_cart( array(
			'panel_style' => isset( $instance['panel_style'] ) ? $instance['panel_style'] : 'dropdown',
			'show_count'  => 'yes',
		) );

		if ( '' === $html ) {
			return;
		}

		$this->open( $args, $instance );
		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput -- built escaped by the renderer.
		$this->close( $args );
	}
}
endif;

if ( ! class_exists( 'UPWC_Widget_Products' ) ) :
/**
 * A small product list, from any of the Products element's sources.
 *
 * Column count is fixed at one: this is a sidebar, and a two-column grid in a
 * 260px column is a row of thumbnails nobody can read.
 */
class UPWC_Widget_Products extends UPWC_Widget_Base {

	public function __construct() {
		parent::__construct(
			'upwc_products',
			__( 'UnysonPlus — Products', 'fw' ),
			array( 'description' => __( 'A short list of products: recent, featured, on sale, best selling or top rated.', 'fw' ) )
		);
	}

	protected function fields() {
		return array(
			'source' => array(
				'label'   => __( 'Show:', 'fw' ),
				'type'    => 'select',
				'default' => 'recent',
				'choices' => array(
					'recent'       => __( 'Recent', 'fw' ),
					'featured'     => __( 'Featured', 'fw' ),
					'sale'         => __( 'On Sale', 'fw' ),
					'best_selling' => __( 'Best Selling', 'fw' ),
					'top_rated'    => __( 'Top Rated', 'fw' ),
				),
			),
			'limit'  => array(
				'label'   => __( 'How many:', 'fw' ),
				'type'    => 'number',
				'default' => '4',
			),
		);
	}

	public function widget( $args, $instance ) {
		if ( ! function_exists( 'upwc_wc_products_resolve' ) || ! function_exists( 'upwc_wc_products_query_args' ) ) {
			return;
		}

		$r = upwc_wc_products_resolve( array(
			'source'         => isset( $instance['source'] ) ? $instance['source'] : 'recent',
			'posts_per_page' => isset( $instance['limit'] ) ? max( 1, (int) $instance['limit'] ) : 4,
			'columns'        => 1,
		) );

		$query_args = upwc_wc_products_query_args( $r, 1 );
		if ( false === $query_args ) {
			return;
		}

		$query = new WP_Query( $query_args );
		if ( ! $query->have_posts() ) {
			wp_reset_postdata();
			return;
		}

		$this->open( $args, $instance );

		echo '<ul class="products upwc-products__grid upwc-products--cols-1 upwc-products--widget">';
		while ( $query->have_posts() ) {
			$query->the_post();
			echo upwc_wc_products_card( wc_get_product( get_the_ID() ), $r ); // phpcs:ignore WordPress.Security.EscapeOutput
		}
		echo '</ul>';

		wp_reset_postdata();
		$this->close( $args );
	}
}
endif;

if ( ! class_exists( 'UPWC_Widget_Wishlist' ) ) :
/**
 * A compact wishlist: the count plus a link to the full list.
 *
 * The count is filled in by the storefront script — a widget appears in the
 * most-cached parts of a page, so the number cannot be rendered server-side.
 */
class UPWC_Widget_Wishlist extends UPWC_Widget_Base {

	public function __construct() {
		parent::__construct(
			'upwc_wishlist',
			__( 'UnysonPlus — Wishlist', 'fw' ),
			array( 'description' => __( 'How many products this visitor has saved, linking to the wishlist page.', 'fw' ) )
		);
	}

	public function widget( $args, $instance ) {
		if ( ! function_exists( 'upwc_wishlist_enabled' ) || ! upwc_wishlist_enabled() ) {
			return;
		}

		$url = function_exists( 'fw_get_db_ext_settings_option' )
			? trim( (string) fw_get_db_ext_settings_option( 'woocommerce', 'wishlist_page' ) )
			: '';

		if ( '' === $url ) {
			return;
		}

		$this->open( $args, $instance );
		?>
		<a class="upwc-wishlist-link" href="<?php echo esc_url( $url ); ?>">
			<span class="upwc-wishlist-link__label"><?php esc_html_e( 'Saved products', 'fw' ); ?></span>
			<span class="upwc-wishlist-count is-empty">0</span>
		</a>
		<?php
		$this->close( $args );
	}
}
endif;

if ( ! function_exists( 'upwc_register_widgets' ) ) :
/**
 * @internal
 */
function upwc_register_widgets() {
	register_widget( 'UPWC_Widget_Mini_Cart' );
	register_widget( 'UPWC_Widget_Products' );
	register_widget( 'UPWC_Widget_Wishlist' );
}
endif;
add_action( 'widgets_init', 'upwc_register_widgets' );

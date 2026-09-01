<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * The WooCommerce shop settings page, under Unyson+ rather than buried three
 * clicks deep in the Extensions manager (Extensions → WooCommerce → Settings).
 *
 * Settings are still the extension's own — the same `settings-options.php`
 * schema and the same `fw_get_db_ext_settings_option()` store — so nothing that
 * reads a setting has to know this page exists. Only the way you reach them
 * changes, and the Extensions-manager card is pointed here too (via
 * `fw_ext_manager_settings_url`) so there is ONE settings screen rather than two
 * that can disagree.
 *
 * Follows the SEO extension's page shape (native WordPress screen, the options
 * rendered into a metabox-holder, save on `load-` before any output) because a
 * settings screen sitting next to one that styles itself differently reads as
 * broken.
 *
 * Only registered when the WooCommerce plugin is active — the extension is
 * inert without it, so an empty shop-settings menu entry would be a dead end.
 */
class FW_Woocommerce_Settings_Page {

	const PARENT_SLUG = 'fw-extensions';
	const PAGE_SLUG   = 'fw-woocommerce-settings';
	const CAPABILITY  = 'manage_options';
	const NONCE       = 'fw_woocommerce_settings_save';

	/** @var FW_Extension_Woocommerce */
	protected $extension;

	/** @var string|null Hook suffix returned by add_submenu_page(). */
	protected $hook_suffix = null;

	/**
	 * @param FW_Extension_Woocommerce $extension
	 */
	public function __construct( $extension ) {
		$this->extension = $extension;

		// Late-ish, so the Unyson+ parent menu is already registered.
		add_action( 'admin_menu', array( $this, '_action_admin_menu' ), 20 );

		add_filter( 'fw_ext_manager_settings_url', array( $this, '_filter_manager_url' ), 10, 2 );
	}

	/**
	 * @return string
	 */
	public static function url() {
		return admin_url( 'admin.php?page=' . self::PAGE_SLUG );
	}

	/**
	 * Send the Extensions-manager card's "Settings" link here.
	 *
	 * @param string $url
	 * @param string $name
	 * @return string
	 * @internal
	 */
	public function _filter_manager_url( $url, $name ) {
		return 'woocommerce' === $name ? self::url() : $url;
	}

	/**
	 * @internal
	 */
	public function _action_admin_menu() {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			return;
		}

		$this->hook_suffix = add_submenu_page(
			self::PARENT_SLUG,
			__( 'WooCommerce Settings', 'fw' ),
			__( 'WooCommerce', 'fw' ),
			self::CAPABILITY,
			self::PAGE_SLUG,
			array( $this, 'render' )
		);

		if ( ! $this->hook_suffix ) {
			return;
		}

		// Saving happens on `load-`, before any output, so the redirect works.
		add_action( 'load-' . $this->hook_suffix, array( $this, '_action_maybe_save' ) );
		add_action( 'admin_enqueue_scripts', array( $this, '_action_enqueue' ) );
	}

	/**
	 * The option types render nothing usable without their own JS/CSS.
	 *
	 * @param string $hook
	 * @internal
	 */
	public function _action_enqueue( $hook ) {
		if ( $hook !== $this->hook_suffix ) {
			return;
		}

		fw()->backend->enqueue_options_static( $this->extension->get_settings_options() );
	}

	/**
	 * @internal
	 */
	public function _action_maybe_save() {
		if ( 'POST' !== ( isset( $_SERVER['REQUEST_METHOD'] ) ? $_SERVER['REQUEST_METHOD'] : '' ) ) {
			return;
		}

		if ( ! current_user_can( self::CAPABILITY ) ) {
			return;
		}

		check_admin_referer( self::NONCE );

		$before = (array) fw_get_db_ext_settings_option( 'woocommerce' );

		// Merged over what is stored, not written wholesale: a future partial form
		// must not silently drop the keys it never showed.
		$values = array_merge(
			$before,
			fw_get_options_values_from_input( $this->extension->get_settings_options() )
		);

		fw_set_db_ext_settings_option( 'woocommerce', null, $values );

		/**
		 * Preserved from the Extensions-manager save path, so anything already
		 * listening for these settings keeps working now the form lives elsewhere.
		 */
		do_action( 'fw_extension_settings_form_saved:woocommerce', $before );

		wp_safe_redirect( add_query_arg( 'fw-saved', '1', self::url() ) );
		exit;
	}

	/**
	 * The schema's top-level tabs. Anything not in a tab (an older schema, or a
	 * setting added at the top level by mistake) is collected under a General
	 * tab rather than silently dropped from the page.
	 *
	 * @return array<string,array>
	 */
	protected function tabs() {
		$tabs  = array();
		$loose = array();

		foreach ( (array) $this->extension->get_settings_options() as $key => $entry ) {
			if ( is_array( $entry ) && isset( $entry['type'] ) && 'tab' === $entry['type'] ) {
				$tabs[ $key ] = $entry;
			} else {
				$loose[ $key ] = $entry;
			}
		}

		if ( $loose ) {
			$tabs['general_tab'] = array(
				'title'   => __( 'General', 'fw' ),
				'options' => $loose,
			);
		}

		return $tabs;
	}

	/**
	 * @internal
	 */
	public function render() {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			return;
		}

		$tabs   = $this->tabs();
		$values = (array) fw_get_db_ext_settings_option( 'woocommerce' );
		$first  = true;
		?>
		<div class="wrap fw-ext-woocommerce-settings">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'WooCommerce Settings', 'fw' ); ?></h1>
			<p class="description">
				<?php esc_html_e( 'Shop catalog layout, single-product gallery, shop behavior and shopper tools. Every field has a working default — you only need to change what you want to differ from what the store already does.', 'fw' ); ?>
			</p>

			<?php if ( isset( $_GET['fw-saved'] ) ) : ?>
				<div class="notice notice-success is-dismissible">
					<p><?php esc_html_e( 'Settings saved.', 'fw' ); ?></p>
				</div>
			<?php endif; ?>

			<h2 class="nav-tab-wrapper fw-wc-tabs">
				<?php foreach ( $tabs as $tab_id => $tab ) : ?>
					<a href="#<?php echo esc_attr( $tab_id ); ?>"
					   class="nav-tab<?php echo $first ? ' nav-tab-active' : ''; ?>"
					   data-tab="<?php echo esc_attr( $tab_id ); ?>"><?php echo esc_html( isset( $tab['title'] ) ? $tab['title'] : $tab_id ); ?></a>
					<?php $first = false; ?>
				<?php endforeach; ?>
			</h2>

			<form method="post" action="">
				<?php wp_nonce_field( self::NONCE ); ?>
				<?php $first = true; foreach ( $tabs as $tab_id => $tab ) : ?>
					<div class="fw-wc-panel<?php echo $first ? ' is-active' : ''; ?>" id="panel-<?php echo esc_attr( $tab_id ); ?>">
						<div class="metabox-holder">
							<?php
							// phpcs:ignore WordPress.Security.EscapeOutput -- the options renderer escapes.
							echo fw()->backend->render_options( (array) ( isset( $tab['options'] ) ? $tab['options'] : array() ), $values );
							?>
						</div>
					</div>
					<?php $first = false; ?>
				<?php endforeach; ?>

				<p class="submit">
					<button type="submit" class="button button-primary"><?php esc_html_e( 'Save Changes', 'fw' ); ?></button>
				</p>
			</form>
		</div>

		<style>
		.fw-ext-woocommerce-settings .fw-wc-tabs{margin:.6em 0 0;border-bottom:1px solid #c3c4c7}
		.fw-ext-woocommerce-settings .fw-wc-tabs .nav-tab{border-radius:.25rem .25rem 0 0}
		.fw-ext-woocommerce-settings .metabox-holder{margin-top:1.2em}
		.fw-ext-woocommerce-settings .description{max-width:46em}
		.fw-ext-woocommerce-settings .fw-wc-panel{display:none}
		.fw-ext-woocommerce-settings .fw-wc-panel.is-active{display:block}
		</style>
		<script>
		( function () {
			var wrap = document.querySelector( '.fw-ext-woocommerce-settings' );
			if ( ! wrap ) { return; }

			function each( list, fn ) { Array.prototype.forEach.call( list, fn ); }

			function activate( tab ) {
				each( wrap.querySelectorAll( '.fw-wc-tabs .nav-tab' ), function ( a ) {
					a.classList.toggle( 'nav-tab-active', a.getAttribute( 'data-tab' ) === tab );
				} );
				each( wrap.querySelectorAll( '.fw-wc-panel' ), function ( p ) {
					p.classList.toggle( 'is-active', p.id === 'panel-' + tab );
				} );
			}

			each( wrap.querySelectorAll( '.fw-wc-tabs .nav-tab' ), function ( a ) {
				a.addEventListener( 'click', function ( e ) {
					e.preventDefault();
					var tab = a.getAttribute( 'data-tab' );
					activate( tab );
					// Deep-linkable, so "check the Catalog Mode tab" can be a URL.
					if ( window.history && window.history.replaceState ) {
						window.history.replaceState( null, '', '#' + tab );
					}
				} );
			} );

			// Every panel is in ONE form, so a hidden panel still posts its fields —
			// switching tabs never loses an edit, and Save covers the whole page.
			var hash = ( window.location.hash || '' ).replace( /^#/, '' );
			if ( hash && wrap.querySelector( '#panel-' + hash ) ) { activate( hash ); }
		}() );
		</script>
		<?php
	}
}

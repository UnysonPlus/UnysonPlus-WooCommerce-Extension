<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
/**
 * Account element — header account link + dropdown.
 * Logged out: login form. Logged in: account menu + logout. In scope: $atts.
 */

if ( ! class_exists( 'WooCommerce' ) || ! function_exists( 'wc_get_page_permalink' ) ) {
	return;
}

$truthy     = function_exists( 'upw_wc_truthy' ) ? 'upw_wc_truthy' : static function ( $v ) { return $v === 'yes' || $v === true; };
$show_label = ! isset( $atts['show_label'] ) || call_user_func( $truthy, $atts['show_label'] );
$trigger    = ( isset( $atts['trigger'] ) && $atts['trigger'] === 'hover' ) ? 'hover' : 'click';
$logged_in  = is_user_logged_in();
$my_account = wc_get_page_permalink( 'myaccount' );

$icon = '<svg class="upw-account__icon" viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.5-6 8-6s8 2 8 6"/></svg>';

if ( $logged_in ) {
	$user  = wp_get_current_user();
	$label = $user->first_name ? $user->first_name : $user->display_name;
} else {
	$label = __( 'Login', 'fw' );
}
?>
<div class="upw-account" data-trigger="<?php echo esc_attr( $trigger ); ?>">
	<a class="upw-account__toggle" href="<?php echo esc_url( $my_account ?: '#' ); ?>" aria-haspopup="true" aria-expanded="false">
		<?php echo $icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php if ( $show_label ) : ?>
			<span class="upw-account__label"><?php echo $logged_in ? esc_html( sprintf( __( 'Hi, %s', 'fw' ), $label ) ) : esc_html( $label ); ?></span>
		<?php endif; ?>
	</a>
	<div class="upw-account__panel" aria-hidden="true">
		<?php if ( $logged_in ) : ?>
			<ul class="upw-account__menu">
				<?php
				foreach ( wc_get_account_menu_items() as $endpoint => $menu_label ) {
					printf(
						'<li><a href="%s">%s</a></li>',
						esc_url( wc_get_account_endpoint_url( $endpoint ) ),
						esc_html( $menu_label )
					);
				}
				?>
			</ul>
		<?php else : ?>
			<div class="upw-account__login">
				<?php
				if ( function_exists( 'woocommerce_login_form' ) ) {
					woocommerce_login_form( array( 'redirect' => $my_account ) );
				}
				?>
				<p class="upw-account__register">
					<a href="<?php echo esc_url( $my_account ?: '#' ); ?>"><?php esc_html_e( 'Create an account', 'fw' ); ?></a>
				</p>
			</div>
		<?php endif; ?>
	</div>
</div>

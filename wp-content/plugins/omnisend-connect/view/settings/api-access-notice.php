<?php
/**
 * Omnisend API Access Notice View
 *
 * @package OmnisendPlugin
 */

defined( 'ABSPATH' ) || exit;

function omnisend_display_api_access_notice() {
	if ( ! Omnisend_Helper::is_woocommerce_api_access_granted() ) {
		omnisend_display_notice();
	} else {
		omnisend_display_success();
	}
}

function omnisend_display_notice() {
	$omnisend_account_id = get_option( 'omnisend_account_id', null );

	if ( $omnisend_account_id !== null ) {
		$full_url = Omnisend_Helper::get_wc_auth_url( $omnisend_account_id );
	}
	?>
	<div>
		<div class="omnisend-notice omnisend-notice-error margin-top">
			<div class="omnisend-notice-content-container">
				<img src="<?php echo esc_url( plugin_dir_url( __NAMESPACE__ ) . 'omnisend-connect/assets/img/warning_24.svg' ); ?>">
				<div class="omnisend-notice-texts-container">
					<p class="omnisend-notice-title">Add missing permissions to keep Omnisend working with WooCommerce</p>
					<p class="omnisend-notice-desc">Without your permissions features like, abandoned cart recovery, will not work.</p>
				</div>
			</div>
			<?php
			if ( Omnisend_Helper::are_permalinks_correct() && $omnisend_account_id !== null ) {
				?>
				<a id="omnisend-auth-notice-link" style="text-decoration: none;" href="<?php echo esc_url( $full_url ); ?>"><button class="omnisend-primary-button danger">Add permissions</button></a>
				<?php
			} else {
				?>
				<button class="omnisend-primary-button danger disabled">Add permissions</button>
				<?php
			}
			?>
		</div>
	</div>
	<?php
}

function omnisend_display_success() {

	if ( isset( $_GET['_wpnonce'] ) ) {
		check_admin_referer( 'omnisend-oauth', '_wpnonce' );
	} else {
		return;
	}

	if ( ! isset( $_GET['success'] ) ) {
		return;
	}

	?>
	<div class="notice updated is-dismissible api-key-success-notice omnisend-notice">
		<p>You’re all set. Permissions added successfully.</p>
	</div>
	<?php
}

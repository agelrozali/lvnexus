<?php
/**
 * Omnisend Settings Page
 *
 * @package OmnisendPlugin
 */

defined( 'ABSPATH' ) || exit;

/**
 * Render settings page
 */
function omnisend_show_settings_page() {
	if ( ! Omnisend_Helper::is_woocommerce_plugin_activated() ) {
		Omnisend_Notifications::set_viewed( Omnisend_Notifications::WOOCOMMERCE_NOTIFICATION );
		display_woocommerce_not_installed_or_disabled();
		return;
	}

	if ( ! Omnisend_Helper::check_wp_wc_compatibility() ) {
		display_unsupported_wordpress_version();
		return;
	}

	if ( ! Omnisend_Helper::is_omnisend_connected() ) {
		Omnisend_Notifications::set_viewed( Omnisend_Notifications::CONNECTION_NOTIFICATION );
		Omnisend_Notifications::set_connection_notification();
		display_connection();
		return;
	}

	if ( get_option( 'omnisend_initial_sync', null ) == null ) {
		Omnisend_Manager_Assistant::init_sync();
		update_option( 'omnisend_initial_sync', gmdate( DATE_ATOM, time() ) );
	}

	display_settings();
}

function omnisend_display_omnisend_logo() {
	?>
	<div class="omnisend-logo">
		<a href="<?php echo esc_url( OMNISEND_APP_URL ); ?>" target="_blank">
			<img src="<?php echo esc_url( plugin_dir_url( __FILE__ ) ) . 'assets/img/logo.svg'; ?>">
		</a>
	</div>
	<?php
}

function display_settings() {
	?>
	<div class="settings-page">
		<?php
		omnisend_display_omnisend_connected();
		omnisend_display_tabs();
		omnisend_display_permalink_notice( 'top' );
		omnisend_display_api_access_notice();
		omnisend_display_account_information();
		omnisend_display_checkout_opt_in_checkbox_settings();
		omnisend_display_tag_settings();
		display_plugin_version();
		?>
	</div>
	<?php
}

function display_connection() {
	?>
	<div class="connection-container">
		<?php
		omnisend_display_permalink_notice( 'bottom' );
		omnisend_display_connection_error_notice();
		omnisend_display_connection_info();
		?>
	</div>
	<?php
	require_once __DIR__ . '/view/settings/landing-page.html';
	if ( Omnisend_Helper::are_permalinks_correct() ) {
		?>
			<script type="text/javascript">
				<?php require_once __DIR__ . '/assets/js/omnisend-connection-listeners-script.js'; ?>
			</script>
		<?php
	}
	display_plugin_version();
}

function display_unsupported_wordpress_version() {
	?>
	<div class="settings-page">
		<?php
		omnisend_display_omnisend_logo();
		?>
		<div class="settings-section">
			<div class="omnisend-notice">
				<div class="omnisend-notice-content-container">
					<img src="<?php echo esc_url( plugin_dir_url( __NAMESPACE__ ) ) . 'omnisend-connect/assets/img/warning_24.svg'; ?>">
					<div class="omnisend-notice-texts-container">
						<p>Your current WordPress version needs an update to support the latest WooCommerce version.</p>
					</div>
				</div>
			</div>
		</div>
		<?php
		display_plugin_version();
		?>
	</div>
	<?php
}

function display_woocommerce_not_installed_or_disabled() {
	?>
	<div class="settings-page">
		<?php
		omnisend_display_omnisend_logo();
		?>
		<div class="settings-section">
			<div class="omnisend-notice">
				<div class="omnisend-notice-content-container">
					<img src="<?php echo esc_url( plugin_dir_url( __NAMESPACE__ ) ) . 'omnisend-connect/assets/img/warning_24.svg'; ?>">
					<div class="omnisend-notice-texts-container">
						<p>Omnisend goes hand-in-hand with WooCommerce. Make sure you have <a href=" <?php echo esc_url( network_admin_url( 'plugin-install.php?s=woocommerce&tab=search&type=term' ) ); ?>">WooCommerce</a> installed and activated</p>
					</div>
				</div>
			</div>
		</div>
		<?php
		display_plugin_version();
		?>
	</div>
	<?php
}

function display_plugin_version() {
	$omnisend_plugin_version = Omnisend_Helper::omnisend_plugin_version();
	?>
	<div class="plugin-version">
		<p>
			Omnisend Plugin for Woocommerce - v.<?php echo esc_html( $omnisend_plugin_version ); ?>
		<p>
	</div>
	<?php
}

?>

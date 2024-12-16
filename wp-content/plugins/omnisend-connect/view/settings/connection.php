<?php
/**
 * Omnisend Connection View
 *
 * @package OmnisendPlugin
 */

defined( 'ABSPATH' ) || exit;

function omnisend_display_connection_info() {
	?>
<div class="connection-info-container">
	<div class="connection-content-left">
		<p class="title-text">Welcome to Omnisend</p>
		<p class="sub-title-text">Send personalized and automated emails, collect subscribers, and turn 1st-time visitors into lifelong customers.</p>
		<div class="connection-btns">
			<a 
			target="_blank" 
			href="<?php echo esc_url( Omnisend_Install::get_registration_url() ); ?>" 
			class="
				<?php
				if ( ! Omnisend_Helper::are_permalinks_correct() ) {
					echo 'disabled';}
				?>
			">
				<button id="create-new-account" class="create-account-btn omnisend-connect-action">Create new account</button>
			</a>
			<a 
			target="_blank" 
			href="<?php echo esc_url( Omnisend_Install::get_connecting_url() ); ?>" 
			class="
				<?php
				if ( ! Omnisend_Helper::are_permalinks_correct() ) {
					echo 'disabled';}
				?>
			">
				<button id="connect-your-account" class="connect-account-btn omnisend-connect-action">Connect your account</button>
			</a>
		</div>
		<a target="_blank" href="https://support.omnisend.com/en/articles/1636174-omnisend-for-woocommerce-wordpress" class="omnisend-kb-link">
			Check out article how to integrate with Omnisend 
			<img src="<?php echo esc_url( plugin_dir_url( __NAMESPACE__ ) ) . 'omnisend-connect/assets/img/kb-icon.svg'; ?>">
		</a>
	</div>
	<div class="connection-content-right">
		<img class="connection-image" src="<?php echo esc_url( plugin_dir_url( __NAMESPACE__ ) ) . 'omnisend-connect/assets/img/connection.png'; ?>">
	</div>
</div>
	<?php
}

?>

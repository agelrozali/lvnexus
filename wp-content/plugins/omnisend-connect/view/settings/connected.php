<?php
/**
 * Omnisend Connected View
 *
 * @package OmnisendPlugin
 */

defined( 'ABSPATH' ) || exit;

function omnisend_display_omnisend_connected() {
	?>
	<div class="connected">
		<div class="connected-left-block">
			<a href="<?php echo esc_url( OMNISEND_APP_URL ); ?>" target="_blank">
				<img src="<?php echo esc_url( plugin_dir_url( __NAMESPACE__ ) ) . 'omnisend-connect/assets/img/logo.svg'; ?>">
			</a>
			<div class="tag">Connected to Omnisend</div>
		</div>
		<a href="<?php echo esc_url( OMNISEND_ONBOARDING_URL ); ?>" target="_blank">
			<button class="omnisend-secondary-button">
				Go to Omnisend <img src="<?php echo esc_url( plugin_dir_url( __NAMESPACE__ ) ) . 'omnisend-connect/assets/img/kb-icon.svg'; ?>">
			</button>
		</a>
	</div>
	<?php
}

<?php
/**
 * Omnisend account information view
 *
 * @package OmnisendPlugin
 */

defined( 'ABSPATH' ) || exit;

function omnisend_display_account_information() {
	$brand_info = Omnisend_Manager::get_brand_info();

	?>
		<div class="settings-section">
			<h3 class="omnisend-content-lead strong setting-title">Account information</h3>
			<div class="setting-control-container">
				<div>
					<span class="omnisend-content-body strong">Brand name:</span>
					<span id="omnisend-account-brand-name-value" class="omnisend-content-body">
						<?php echo esc_attr( $brand_info['name'] ); ?>
					</span>
				<div>
					<span class="omnisend-content-body strong">Brand ID:</span>
					<span id="omnisend-account-brand-id-value" class="omnisend-content-body">
						<?php echo esc_attr( get_option( 'omnisend_account_id' ) ); ?>
					</span>
				</div>
			</div>
		</div>
	<?php
}

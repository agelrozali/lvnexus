<?php
/**
 * Omnisend Permalink Notice View
 *
 * @package OmnisendPlugin
 */

defined( 'ABSPATH' ) || exit;

function omnisend_display_permalink_notice( $margin_type = 'bottom' ) {
	if ( Omnisend_Helper::are_permalinks_correct() ) {
		return;
	}

	$permalink_settings_url = admin_url( 'options-permalink.php?settings-updated=true' )
	?>
	<div>
		<div class="omnisend-notice margin-<?php echo esc_attr( $margin_type ); ?>">
			<div class="omnisend-notice-content-container">
				<img src="<?php echo esc_url( plugin_dir_url( __NAMESPACE__ ) . 'omnisend-connect/assets/img/warning_24.svg' ); ?>">
				<div class="omnisend-notice-texts-container">
					<p class="omnisend-notice-title">Your site’s permalinks are set to “plain”. Select a different option in your <a id="permalink-notice-link" href="<?php echo esc_url( $permalink_settings_url ); ?>">WordPress settings</a>.</p>
					<a href="<?php echo esc_url( OMNISEND_DISCOUNTS_KB_ARTICLE_URL ); ?>" target="_blank" class="omnisend-kb-link">
						Learn how to troubleshoot settings
						<img src="<?php echo esc_url( plugin_dir_url( __NAMESPACE__ ) . 'omnisend-connect/assets/img/kb-icon.svg' ); ?>">
					</a>
				</div>
			</div>
		</div>
	</div>
	<?php
}

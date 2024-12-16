<?php
/**
 * Omnisend Settings Title View
 *
 * @package OmnisendPlugin
 */

defined( 'ABSPATH' ) || exit;

function omnisend_display_tabs( $active = 'General' ) {
	?>
	<div class="settings-section">
		<div class="omnisend-navigation-tabs">
			<a class="omnisend-tab <?php echo ( $active === 'General' ? 'active' : '' ); ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=' . OMNISEND_SETTINGS_PAGE ) ); ?>">
				General
			</a>
			<a class="omnisend-tab <?php echo ( $active === 'Sync' ? 'active' : '' ); ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=' . OMNISEND_SETTINGS_PAGE . '&tab=' . OMNISEND_SYNC_PAGE ) ); ?>">
				Sync
			</a>
			<a class="omnisend-tab <?php echo ( $active === 'Logs' ? 'active' : '' ); ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=' . OMNISEND_SETTINGS_PAGE . '&tab=' . OMNISEND_LOGS_PAGE ) ); ?>">
				Logs
			</a>
		</div>
	</div>
	<?php
}

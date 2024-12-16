<?php
/**
 * Omnisend Connection Error Notice View
 *
 * @package OmnisendPlugin
 */

defined( 'ABSPATH' ) || exit;

function omnisend_display_connection_error_notice() {

	if ( isset( $_GET['_wpnonce'] ) ) {
		check_admin_referer( 'omnisend-oauth', '_wpnonce' );
	} else {
		return;
	}

	if ( ! isset( $_GET['error'] ) ) {
		return;
	}

	$code = sanitize_text_field( wp_unslash( $_GET['error'] ) );

	$error = new Omnisend_Connection_Error( $code );
	?>
	<div>
		<div class="omnisend-notice omnisend-notice-error margin-bottom">
			<div class="omnisend-notice-content-container">
				<img src="<?php echo esc_url( plugin_dir_url( __NAMESPACE__ ) ) . 'omnisend-connect/assets/img/warning_24.svg'; ?>">
				<div class="omnisend-notice-texts-container">
					<p>
						<?php
						echo wp_kses(
							$error->get_message(),
							array(
								'a' => array(
									'id'     => array(),
									'href'   => array(),
									'target' => array(),
								),
							)
						);
						?>
					</p>
				</div>
			</div>
		</div>
	</div>
	<?php
}

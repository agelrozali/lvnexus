<?php
/**
 * Omnisend Logs Page
 *
 * @package OmnisendPlugin
 */

defined( 'ABSPATH' ) || exit;

/**
 * Render logs page
 */
function omnisend_show_logs() {
	if ( ! class_exists( 'WP_List_Table' ) ) {
		include_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
	}

	if ( isset( $_GET['action'] ) && check_admin_referer( 'omnisend_logs' ) ) {
		if ( $_GET['action'] == 'log_options' ) {
			if ( isset( $_GET['enable'] ) && '1' === $_GET['enable'] ) {
				Omnisend_Logger::enable_logging();
			} else {
				Omnisend_Logger::disable_logging();
			}
		} elseif ( $_GET['action'] == 'clean_log' ) {
			Omnisend_Logger::remove_all_logs();
			wp_safe_redirect( admin_url( 'admin.php?page=' . OMNISEND_SETTINGS_PAGE . '&tab=' . OMNISEND_LOGS_PAGE ) );
			exit;
		}
	}
	// phpcs:enable

	$logging_enabled = Omnisend_Logger::is_logging_enabled();
	?>
	<div class="settings-page">
		<?php
		omnisend_display_omnisend_connected();
		omnisend_display_tabs( 'Logs' );

		if ( ! empty( $_SERVER['REQUEST_URI'] ) ) {
			if ( $logging_enabled ) {
				echo "<div class='logging_status logging_enabled'>Logs are  enabled. <a href='" . esc_url(
					add_query_arg(
						array(
							'enable'   => 0,
							'_wpnonce' => wp_create_nonce( 'omnisend_logs' ),
						),
						admin_url( 'admin.php?page=' . OMNISEND_SETTINGS_PAGE . '&tab=' . OMNISEND_LOGS_PAGE . '&action=log_options' )
					)
				) . "'>Disable</a> </div>";
			} else {
				echo "<div class='logging_status logging_disabled'>Logs are  disabled. <a href='" . esc_url(
					add_query_arg(
						array(
							'enable'   => 1,
							'_wpnonce' => wp_create_nonce( 'omnisend_logs' ),
						),
						admin_url( 'admin.php?page=' . OMNISEND_SETTINGS_PAGE . '&tab=' . OMNISEND_LOGS_PAGE . '&action=log_options' )
					)
				) . "'>Enable</a></div>";
			}
		}

		echo "<p><a href='" . esc_url(
			add_query_arg(
				array(
					'_wpnonce' => wp_create_nonce( 'omnisend_logs' ),
				),
				admin_url( 'admin.php?page=' . OMNISEND_SETTINGS_PAGE . '&tab=' . OMNISEND_LOGS_PAGE . '&action=clean_log' )
			)
		) . "' class='button button-primary clean-log'>Clean log</a></p>";

		$logs = Omnisend_Logger::get_all_logs();
		if ( count( $logs ) == 0 ) {
			echo '<div>Logfile is clean!</div>';
		} else {
			echo "<table class='wp-list-table widefat fixed striped posts'>
        <thead>
            <tr>
                <td class='fixed_date'>Date, GMT</td>
                <td class='fixed_type'>Type</td>
                <td class='fixed_endpoint'>Endpoint</td>
                <td class='fixed_url'>Url</td>
                <td>Message</td>
            </tr>
        </thead>";
			foreach ( $logs as $log ) {
				echo "<tr><td class='fixed_date'>" . esc_html( $log->date ) . "</td>
            <td class='fixed_type omnisend-" . esc_attr( $log->type ) . "'>" . esc_html( $log->type ) . "</td>
            <td class='fixed_endpoint'>" . esc_html( $log->endpoint ) . "</td>
            <td class='fixed_url'>" . esc_html( $log->url ) . '</td>
            <td ' . ( $log->type == 'hook' ? " class='omnisend-hook-message'" : '' ) . '>' . esc_html( $log->message ) . '</td></tr>';
			}
			echo '</table>';

			?>
		<div>
			<?php
		}
		?>
		</div>
	</div>
	<?php
}

?>

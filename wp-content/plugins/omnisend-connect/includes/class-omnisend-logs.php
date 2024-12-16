<?php
/**
 * Omnisend Logs Class
 *
 * @package OmnisendPlugin
 */

defined( 'ABSPATH' ) || exit;

/**
 * Trigger log synchronization
 *
 * @return void
 */
function omnisend_sync_logs() {
	Omnisend_Logs::sync();
}

add_action( Omnisend_Logs::HOOK_SYNCHRONIZE_LOGS, 'omnisend_sync_logs' );

/**
 * Omnisend Logs Class
 *
 * @package OmnisendPlugin
 */
class Omnisend_Logs {
	public const HOOK_SYNCHRONIZE_LOGS = 'omnisend_sync_logs';

	const OPTION_LAST_SYNCED_LOG_ID = 'omnisend_last_synced_log_id';
	const LIMIT                     = 1000;

	/**
	 * Check if synchronization is scheduled
	 *
	 * @return void
	 */
	public static function init() {
		if ( ! wp_next_scheduled( self::HOOK_SYNCHRONIZE_LOGS ) ) {
			wp_schedule_event( time(), 'omnisend_every_five_minutes', self::HOOK_SYNCHRONIZE_LOGS );
		}
	}

	/**
	 * Check if logging is enabled and start syncing logs
	 *
	 * @return void
	 */
	public static function sync() {
		if ( ! Omnisend_Logger::is_logging_enabled() ) {
			return;
		}

		self::send();

		// Remove the oldest logs after all logs are synced.
		$last_synced_log_id = intval( get_option( self::OPTION_LAST_SYNCED_LOG_ID, 0 ), 10 );

		Omnisend_Logger::remove_old_synced_logs( $last_synced_log_id );
	}

	/**
	 * Send logs to Omnisend backend
	 *
	 * @return void
	 */
	private static function send() {
		$last_synced_log_id = intval( get_option( self::OPTION_LAST_SYNCED_LOG_ID, 0 ), 10 );

		$logs = Omnisend_Logger::get_logs( $last_synced_log_id, self::LIMIT );

		if ( ! is_array( $logs ) || count( $logs ) === 0 ) {
			return;
		}

		$success = Omnisend_Logs_Sender::send( $logs );

		if ( ! $success ) {
			return;
		}

		update_option( self::OPTION_LAST_SYNCED_LOG_ID, self::get_last_log_id( $logs ) );

		if ( count( $logs ) === self::LIMIT ) {
			self::send();
		}
	}

	/**
	 * Return last log id
	 *
	 * @param array $logs Logs from _omnisend_logs.
	 *
	 * @return int
	 */
	private static function get_last_log_id( $logs ) {
		$last_log = end( $logs );

		return intval( $last_log->id, 10 );
	}
}

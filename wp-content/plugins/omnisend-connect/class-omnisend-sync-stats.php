<?php
/**
 * Omnisend Sync Stats Class
 *
 * @package OmnisendPlugin
 */

defined( 'ABSPATH' ) || exit;

class Omnisend_Sync_Stats {
	public $total;
	public $unique;
	public $synced;
	public $not_synced;
	public $skipped;
	public $error;

	public function __construct( $total, $unique, $synced, $not_synced, $skipped, $error ) {
		$this->total      = $total;
		$this->unique     = $unique;
		$this->synced     = $synced;
		$this->not_synced = $not_synced;
		$this->skipped    = $skipped;
		$this->error      = $error;
	}
}

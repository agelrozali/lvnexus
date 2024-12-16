<?php
/**
 * Omnisend All Sync Stats Class
 *
 * @package OmnisendPlugin
 */

defined( 'ABSPATH' ) || exit;

class Omnisend_All_Sync_Stats {
	/**
	 * @var Omnisend_Sync_Stats
	 */
	public $contacts;
	/**
	 * @var Omnisend_Sync_Stats
	 */
	public $orders;
	/**
	 * @var Omnisend_Sync_Stats
	 */
	public $products;
	/**
	 * @var Omnisend_Sync_Stats
	 */
	public $categories;

	public function __construct( Omnisend_Sync_Stats $contacts, Omnisend_Sync_Stats $orders, Omnisend_Sync_Stats $products, Omnisend_Sync_Stats $categories ) {
		$this->contacts   = $contacts;
		$this->orders     = $orders;
		$this->products   = $products;
		$this->categories = $categories;
	}
}

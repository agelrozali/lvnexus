<?php
/**
 * Omnisend Category Class
 *
 * @package OmnisendPlugin
 */

defined( 'ABSPATH' ) || exit;

class Omnisend_Category {

	/**
	 * @var string
	 */
	public $id;

	/**
	 * @var string
	 */
	public $title;

	/**
	 * @param int $id
	 *
	 * @return Omnisend_Category|null
	 */
	public static function create_from_id( $id ) {
		try {
			$term  = get_term( $id );
			$title = $term && is_object( $term ) && property_exists( $term, 'name' ) ? $term->name : '';

			return new Omnisend_Category( $id, $title );
		} catch ( Omnisend_Empty_Required_Fields_Exception $exception ) {
			return null;
		}
	}

	public function to_array() {
		return array(
			'categoryID' => $this->id,
			'title'      => $this->title,
		);
	}

	/**
	 * @throws Omnisend_Empty_Required_Fields_Exception
	 */
	private function __construct( $id, $title ) {
		$this->id    = (string) $id;
		$this->title = $title;

		if ( empty( $this->id ) || empty( $this->title ) ) {
			throw new Omnisend_Empty_Required_Fields_Exception();
		}
	}
}

<?php
/**
 * Core class
 *
 * @since 1.0
 */
defined( 'WPINC' ) || exit ;

class DoQRCode
{
	private static $_instance ;

	/**
	 * Init
	 *
	 * @since  1.0
	 * @access private
	 */
	private function __construct()
	{
		add_shortcode( 'qrcode', array( $this, 'shortcode_handler' ) ) ;
	}

	/**
	 * Shortcode handler
	 *
	 * @since  1.0
	 */
	public function shortcode_handler( $atts, $content )
	{
		require_once DOQRCODE_DIR . 'phpqrcode.lib.php' ;

		$size = 3 ;
		if ( ! empty( $atts[ 'size' ] ) ) {
			$size = (int) $atts[ 'size' ] ;
		}

		$margin = 3 ;
		if ( ! empty( $atts[ 'margin' ] ) ) {
			$margin = (int) $atts[ 'margin' ] ;
		}

		$svg = \WPDO\DoQRCode\QRcode::svg( $content, false, false, $size, $margin ) ;
		$svg = str_replace( "\n", '', $svg ) ;

		// convert svg to base64
		$svg = 'data:image/svg+xml;base64,' . base64_encode( $svg ) ;

		return "<img src='$svg' />" ;
	}


	/**
	 * Get the current instance object.
	 *
	 * @since 1.0
	 * @access public
	 */
	public static function get_instance()
	{
		if ( ! isset( self::$_instance ) ) {
			self::$_instance = new self() ;
		}

		return self::$_instance ;
	}

}

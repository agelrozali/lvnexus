<?php
/**
 * Plugin Name:       QRCode
 * Plugin URI:        https://github.com/wpdo/doqrcode
 * Description:       WordPress QR Code generator
 * Version:           1.2.1
 * Author:            WPDO
 * License:           GPLv3
 * License URI:       http://www.gnu.org/licenses/gpl.html
 * Text Domain:       doqrcode
 *
 * Copyright (C) 2020 WPDO
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.

 */
defined( 'WPINC' ) || exit ;

if ( defined( 'DOQRCODE_V' ) ) {
	return ;
}

define( 'DOQRCODE_V', '1.2.1' ) ;

! defined( 'DOQRCODE_DIR' ) && define( 'DOQRCODE_DIR', dirname( __FILE__ ) . '/' ) ;// Full absolute path '/usr/local/***/wp-content/plugins/doqrcode/' or MU

require_once DOQRCODE_DIR . 'core.cls.php' ;

$__core = DoQRCode::get_instance() ;


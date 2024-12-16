<?php
define( 'WP_CACHE', true );
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'u938774898_MXt1U' );

/** Database username */
define( 'DB_USER', 'u938774898_L72T1' );

/** Database password */
define( 'DB_PASSWORD', '344H819yKe' );

/** Database hostname */
define( 'DB_HOST', '127.0.0.1' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',          'I$Odv~lAi2xliQJ>Q^pb|s4D7)a{G(eSMdev%+!gSVh(VjRDEt$V9(yPe|#5`Hr^' );
define( 'SECURE_AUTH_KEY',   '$}tpH1Kml!$Fzm6];_]y$r:xS:04pu+QO7f9^ybZFt-nv08Fs&b~rwb5%[b8kc#%' );
define( 'LOGGED_IN_KEY',     'v6k95.WyceOx7`<%3GSHo%hpgzUn*i56cZ:uyr*ErTDrkblwfO(-xHVPP0YcL(v!' );
define( 'NONCE_KEY',         ':qV(qcM-.RGRv/dlK<AG0}_8$dB$Qsk_w<h.>CU8?Q,LQBj:(5k?7M%`;C-iUk[;' );
define( 'AUTH_SALT',         'o.l]j?8;NW+G62FGj6t0O;$})Ra(B/dL(oF~|f-fSAb(`YzcA0)tDtCJ0mF[3L@e' );
define( 'SECURE_AUTH_SALT',  'yfS1-yzV89l?AcU|*g{~:Gru_^390PJ]ij_ri6Qk.GweFs@~l76bY+%}T5p{3 qZ' );
define( 'LOGGED_IN_SALT',    'Ij,}UOlA}N;t+w&%F1;bNQzgPlwxCy[K(=gmb`w3f*q3:Xb7/9:a7xdQ6Y6b`J!D' );
define( 'NONCE_SALT',        'KVE:r{l,Oa!u<ez1x]66aYBAjl<2B-%*(n(<_vMER6I;#<E).l&=}CaX^R@zGNTZ' );
define( 'WP_CACHE_KEY_SALT', ';lcT K3?/RpBdN2vmJvf6yqjsOyKV]uZGm$jQH!:|g]~X^24!e(!S$|{Mp1&Xsrl' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


/* Add any custom values between this line and the "stop editing" line. */



/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}

define( 'FS_METHOD', 'direct' );
define( 'WP_AUTO_UPDATE_CORE', 'minor' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';

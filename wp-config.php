<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wordpress' );

/** MySQL database username */
define( 'DB_USER', 'blharris' );

/** MySQL database password */
define( 'DB_PASSWORD', 'Success#1986!' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

define('WP_HOME','https://localhost');
define('WP_SITEURL','https://localhost');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '+3oF^tz. K>/o=;w<tocq_%ls%,_; 2D;1$b[a=*X/@:!b*:@u6,Pvg)}i8ns_V:' );
define( 'SECURE_AUTH_KEY',  ':6Uzt%dtry/mY:a/9FyT95tWQP3Y8GDH:+&?7v;9#Tsq(#e_kLN9M{xRj0Bn/WBD' );
define( 'LOGGED_IN_KEY',    'rg_y0`zF4/Vv6RZ-^c1aqm8G~[x<l{aLnbYePs>DHCimc*TUgT5GEeozA!E/{:1n' );
define( 'NONCE_KEY',        '<=FHFxZkM5xD3:-zH7hM:{N?vI$N~j-<su8>PS:T{qc1eB!5En{lz8~#9oS]myHJ' );
define( 'AUTH_SALT',        'fiSSg3 &ICxd7$y+_D,(Q@[B)s ?Rb4JzNJ)5tnZu&-p,orR;PTv m<?wPzvm/2+' );
define( 'SECURE_AUTH_SALT', '_B{cA;k!2(vk0`GKdsUTS2`@+a;61;8O6TMNgCAxR;j*c9*B!Dt6DF{y/ruO{}$X' );
define( 'LOGGED_IN_SALT',   '@TQ(L#?D7yN.ILM6so^*#yBH=j6rMs+~JziCDzm<gn|cnd:>lyf;j0&-jxIsvhX9' );
define( 'NONCE_SALT',       'lu@LQ>0$LCJ0 ;ALJ[=yA(oG?@IoRgyG<|{ogv$>%uOwS~oA@Ns&r{Cpa c-QNYM' );

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';
// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */

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
define( 'WP_DEBUG', true );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';

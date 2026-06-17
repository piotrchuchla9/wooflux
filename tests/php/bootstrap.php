<?php
/**
 * PHPUnit bootstrap for WooFlux unit tests (Brain\Monkey / no database).
 */

require_once dirname( __DIR__, 2 ) . '/vendor/autoload.php';

// Stub ABSPATH so plugin files don't die().
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __DIR__, 2 ) . '/' );
}

// Stub the plugin's own constants so includes don't die().
if ( ! defined( 'WOOFLUX_DIR' ) ) {
	define( 'WOOFLUX_DIR', dirname( __DIR__, 2 ) . '/' );
}
if ( ! defined( 'WOOFLUX_VERSION' ) ) {
	define( 'WOOFLUX_VERSION', '1.0.0' );
}

// Load only the classes under test (not the main plugin bootstrap).
require_once WOOFLUX_DIR . 'includes/class-wooflux-query.php';
require_once WOOFLUX_DIR . 'includes/class-wooflux-rest.php';

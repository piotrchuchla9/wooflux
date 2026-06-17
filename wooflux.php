<?php
/**
 * Plugin Name:       Live Product Filter — WooFlux
 * Plugin URI:        https://wooflux.vercel.app
 * Description:       Lightning-fast WooCommerce product filtering built on the WordPress Interactivity API. Zero jQuery. No conflicts.
 * Version:           1.0.0
 * Requires at least: 6.5
 * Requires PHP:      8.0
 * Author:            Your Name
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       live-product-filter-wooflux
 * Domain Path:       /languages
 * WC requires at least: 8.0
 * WC tested up to:   10.x
 *
 * @package WooFlux
 */

defined( 'ABSPATH' ) || exit;

define( 'WOOFLUX_VERSION', '1.0.0' );
define( 'WOOFLUX_FILE', __FILE__ );
define( 'WOOFLUX_DIR', plugin_dir_path( __FILE__ ) );
define( 'WOOFLUX_URL', plugin_dir_url( __FILE__ ) );
define( 'WOOFLUX_MIN_WP', '6.5' );
define( 'WOOFLUX_MIN_WC', '8.0' );
define( 'WOOFLUX_MIN_PHP', '8.0' );

// Freemius bootstrap (must run before main class).
if ( ! function_exists( 'wooflux_fs' ) ) {
	/**
	 * Returns the Freemius instance, or null if the SDK is not present.
	 *
	 * @return Freemius|null
	 */
	function wooflux_fs() {
		global $wooflux_fs;

		if ( ! isset( $wooflux_fs ) ) {
			$freemius_path = WOOFLUX_DIR . 'freemius/start.php';
			if ( file_exists( $freemius_path ) ) {
				require_once $freemius_path;
				$wooflux_fs = fs_dynamic_init(
					array(
						'id'             => 'YOUR_FREEMIUS_PRODUCT_ID',
						'slug'           => 'wooflux',
						'type'           => 'plugin',
						'public_key'     => 'pk_YOUR_PUBLIC_KEY',
						'is_premium'     => false,
						'has_addons'     => false,
						'has_paid_plans' => true,
						'menu'           => array(
							'slug'   => 'wooflux',
							'parent' => array( 'slug' => 'woocommerce' ),
						),
					)
				);
			}
		}

		return isset( $wooflux_fs ) ? $wooflux_fs : null;
	}

	wooflux_fs();
	do_action( 'wooflux_fs_loaded' );
}

require_once WOOFLUX_DIR . 'includes/class-wooflux-freemius.php';
require_once WOOFLUX_DIR . 'includes/class-wooflux.php';

add_action( 'plugins_loaded', array( 'WooFlux', 'get_instance' ) );

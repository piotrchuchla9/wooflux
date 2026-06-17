<?php
/**
 * Main plugin class.
 *
 * @package WooFlux
 */

defined( 'ABSPATH' ) || exit;

/**
 * Bootstraps the plugin: checks requirements, loads files, registers hooks.
 */
class WooFlux {

	/**
	 * Singleton instance.
	 *
	 * @var WooFlux|null
	 */
	private static ?WooFlux $instance = null;

	/**
	 * Returns (and creates if needed) the singleton instance.
	 *
	 * @return self
	 */
	public static function get_instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor — private to enforce singleton.
	 */
	private function __construct() {
		if ( ! $this->check_requirements() ) {
			return;
		}
		$this->load_dependencies();
		$this->init_hooks();
	}

	/**
	 * Verifies PHP and WooCommerce requirements. Adds admin notices on failure.
	 *
	 * @return bool True if all requirements are met.
	 */
	private function check_requirements(): bool {
		if ( version_compare( PHP_VERSION, WOOFLUX_MIN_PHP, '<' ) ) {
			add_action( 'admin_notices', array( $this, 'notice_php_version' ) );
			return false;
		}

		if ( ! class_exists( 'WooCommerce' ) ) {
			add_action( 'admin_notices', array( $this, 'notice_woocommerce' ) );
			return false;
		}

		return true;
	}

	/**
	 * Requires all plugin class files.
	 */
	private function load_dependencies(): void {
		require_once WOOFLUX_DIR . 'includes/class-wooflux-query.php';
		require_once WOOFLUX_DIR . 'includes/class-wooflux-blocks.php';
		require_once WOOFLUX_DIR . 'includes/class-wooflux-rest.php';
		require_once WOOFLUX_DIR . 'includes/class-wooflux-settings.php';
		require_once WOOFLUX_DIR . 'includes/class-wooflux-assets.php';
	}

	/**
	 * Registers all WordPress action and filter hooks.
	 */
	private function init_hooks(): void {
		add_action( 'init', array( $this, 'load_textdomain' ) );
		add_action( 'init', array( new WooFlux_Blocks(), 'register' ) );
		add_action( 'rest_api_init', array( new WooFlux_REST(), 'register_routes' ) );
		add_action( 'admin_menu', array( new WooFlux_Settings(), 'add_menu' ) );

		$assets = new WooFlux_Assets();
		add_action( 'wp_enqueue_scripts', array( $assets, 'enqueue_frontend' ) );
		add_action( 'admin_enqueue_scripts', array( $assets, 'enqueue_admin' ) );

		add_action( 'save_post_product', array( $this, 'flush_cache' ) );
		add_action( 'woocommerce_product_set_stock_status', array( $this, 'flush_cache' ) );

		add_action(
			'before_woocommerce_init',
			function () {
				if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
					\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
						'custom_order_tables',
						WOOFLUX_FILE,
						true
					);
				}
			}
		);
	}

	/**
	 * Loads the plugin text domain for translations.
	 */
	public function load_textdomain(): void {
		load_plugin_textdomain(
			'live-product-filter-wooflux',
			false,
			dirname( plugin_basename( WOOFLUX_FILE ) ) . '/languages'
		);
	}

	/**
	 * Deletes all WooFlux transients from the options table.
	 */
	public function flush_cache(): void {
		global $wpdb;
		$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
				$wpdb->esc_like( '_transient_wooflux_' ) . '%',
				$wpdb->esc_like( '_transient_timeout_wooflux_' ) . '%'
			)
		);
	}

	/**
	 * Admin notice shown when the PHP version requirement is not met.
	 */
	public function notice_php_version(): void {
		printf(
			'<div class="notice notice-error"><p>%s</p></div>',
			esc_html(
				sprintf(
					/* translators: %s: required PHP version */
					__( 'WooFlux requires PHP %s or higher. Please update your PHP version.', 'live-product-filter-wooflux' ),
					WOOFLUX_MIN_PHP
				)
			)
		);
	}

	/**
	 * Admin notice shown when WooCommerce is not active.
	 */
	public function notice_woocommerce(): void {
		echo '<div class="notice notice-error"><p>' .
			esc_html__( 'WooFlux requires WooCommerce to be installed and active.', 'live-product-filter-wooflux' ) .
			'</p></div>';
	}
}

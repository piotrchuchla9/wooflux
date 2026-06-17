<?php
/**
 * Asset enqueueing for frontend and admin.
 *
 * @package WooFlux
 */

defined( 'ABSPATH' ) || exit;

/**
 * Handles enqueuing of frontend and admin stylesheets and scripts.
 */
class WooFlux_Assets {

	/**
	 * Enqueues frontend stylesheet on pages that contain WooFlux blocks.
	 */
	public function enqueue_frontend(): void {
		if ( ! $this->is_wooflux_page() ) {
			return;
		}

		wp_enqueue_style(
			'wooflux-frontend',
			WOOFLUX_URL . 'assets/css/frontend.css',
			array(),
			WOOFLUX_VERSION
		);
	}

	/**
	 * Enqueues admin stylesheet and script on WooFlux settings pages.
	 *
	 * @param string $hook Current admin page hook suffix.
	 */
	public function enqueue_admin( string $hook ): void {
		if ( false === strpos( $hook, 'live-product-filter-wooflux' ) ) {
			return;
		}

		wp_enqueue_style(
			'wooflux-admin',
			WOOFLUX_URL . 'assets/css/admin.css',
			array(),
			WOOFLUX_VERSION
		);

		wp_enqueue_script(
			'wooflux-admin',
			WOOFLUX_URL . 'assets/js/admin.js',
			array(),
			WOOFLUX_VERSION,
			true
		);
	}

	/**
	 * Returns true if the current page should load WooFlux frontend assets.
	 *
	 * @return bool
	 */
	private function is_wooflux_page(): bool {
		// Blocks handle their own script enqueueing via viewScriptModule.
		// This is only needed for global frontend styles when blocks are present.
		return true;
	}
}

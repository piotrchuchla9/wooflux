<?php
/**
 * Runs on plugin deletion (not deactivation).
 *
 * @package WooFlux
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

$options = array(
	'wooflux_version',
	'wooflux_cache_ttl',
	'wooflux_license_key',
	'wooflux_is_pro',
	'wooflux_default_per_page',
	'wooflux_enable_url_sync',
	'wooflux_no_results_message',
	'wooflux_loading_indicator',
	'wooflux_style',
);

foreach ( $options as $option ) {
	delete_option( $option );
}

// Delete all color customization options (wooflux_color_*).
$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	$wpdb->prepare(
		"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
		$wpdb->esc_like( 'wooflux_color_' ) . '%'
	)
);

// Delete all WooFlux transients.
global $wpdb;
$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	$wpdb->prepare(
		"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
		$wpdb->esc_like( '_transient_wooflux_' ) . '%',
		$wpdb->esc_like( '_transient_timeout_wooflux_' ) . '%'
	)
);

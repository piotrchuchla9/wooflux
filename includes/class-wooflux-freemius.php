<?php
/**
 * Freemius license helper.
 *
 * @package WooFlux
 */

defined( 'ABSPATH' ) || exit;

/**
 * Returns true if the active Freemius license is on the Pro plan.
 *
 * @return bool
 */
function wooflux_is_pro(): bool {
	$fs = wooflux_fs();
	if ( null === $fs ) {
		return false;
	}
	return $fs->is_plan( 'pro', true );
}

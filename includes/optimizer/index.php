<?php
/**
 * Asset Manager (optimizer) subsystem entry point.
 *
 * Single place that loads the optimizer:
 *   1. Defines the asset-mode helper functions below.
 *   2. Loads the Optimizer engine class.
 *
 * Required early from the plugin bootstrap (sky-elementor-addons.php) — before
 * Elementor — so the helpers are available to modules-manager, plugin.php, the
 * optimizer class, and the admin settings handler with no ordering dependency.
 *
 * The Optimizer is only *instantiated* (hooks registered) from plugin.php,
 * which runs solely when Elementor is active. Loading the class here is cheap:
 * its constructor — not the file — pulls in asset-manager.php.
 *
 * @package Sky_Addons
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/class-optimizer.php';

if ( ! function_exists( 'sky_addons_asset_mode' ) ) {

	/**
	 * Return the current Asset Manager delivery mode.
	 *
	 * 'generated'  — custom uploads bundle (active-widget-only, smallest size).
	 * 'full'       — plugin-shipped combined bundle (instant, no generation).
	 * 'per-widget' — individual per-widget files, loaded on demand per page.
	 *
	 * Handles backward-compat with the old boolean 'on'/'off' values.
	 * Default: 'per-widget' (used until a mode is saved on the Advanced tab).
	 */
	function sky_addons_asset_mode() {
		$settings = get_option( 'sky_addons_advanced_settings', [] );
		$raw      = isset( $settings['asset_manager'] ) ? $settings['asset_manager'] : 'per-widget';

		// Backward-compat: old on/off saved values.
		if ( 'on' === $raw ) {
			return 'generated';
		}
		if ( 'off' === $raw ) {
			return 'per-widget';
		}

		return in_array( $raw, [ 'generated', 'full', 'per-widget' ], true ) ? $raw : 'per-widget';
	}
}

if ( ! function_exists( 'sky_addons_is_asset_optimization_enabled' ) ) {

	/**
	 * Whether the Asset Manager is using a combined bundle (generated or full).
	 *
	 * Returns true for 'generated' and 'full' — both modes alias per-widget
	 * handles to the combined bundle via modules-manager. Returns false for
	 * 'per-widget' only.
	 */
	function sky_addons_is_asset_optimization_enabled() {
		$enabled = in_array( sky_addons_asset_mode(), [ 'generated', 'full' ], true );

		return (bool) apply_filters( 'sky-addons/optimization/asset_manager', $enabled );
	}
}

<?php

namespace Sky_Addons\Features;

defined( 'ABSPATH' ) || exit;

class Init {
	private static $instance = null;

	private function __construct() {
		$features = get_option( 'sky_addons_inactive_extensions', [] );

		/**
		 * Duplicator
		 */
		if ( ! in_array( 'duplicator', $features ) ) {
			require_once SKY_ADDONS_INC_PATH . 'features/class-duplicator.php';
			\Sky_Addons\Features\Duplicator::get_instance();
		}

		/**
		 * SVG Support
		 */
		if ( ! in_array( 'svg-support', $features ) ) {
			require_once SKY_ADDONS_INC_PATH . 'features/class-svg-support.php';
			\Sky_Addons\Features\Svg_Support::get_instance();
		}
	}

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}

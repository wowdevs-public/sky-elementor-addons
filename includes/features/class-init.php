<?php

namespace Sky_Addons\Features;

defined( 'ABSPATH' ) || exit;

class Init {
	private static $instance = null;

	private function __construct() {
		/**
		 * Duplicator
		 */
		if ( \Sky_Addons\Managers::is_advanced_feature_active( 'duplicator' ) ) {
			require_once SKY_ADDONS_INC_PATH . 'features/class-duplicator.php';
			\Sky_Addons\Features\Duplicator::get_instance();
		}

		/**
		 * SVG Support
		 */
		if ( \Sky_Addons\Managers::is_advanced_feature_active( 'svg-support' ) ) {
			require_once SKY_ADDONS_INC_PATH . 'features/class-svg-support.php';
			\Sky_Addons\Features\Svg_Support::get_instance();
		}

		/**
		 * Video Link
		 */
		if ( \Sky_Addons\Managers::is_advanced_feature_active( 'video-link' ) ) {
			require_once SKY_ADDONS_INC_PATH . 'features/class-video-link.php';
			\Sky_Addons\Features\Video_Link::get_instance();
		}

		/**
		 * Menu Duplicator — ships disabled by default.
		 */
		if ( \Sky_Addons\Managers::is_advanced_feature_active( 'menu-duplicator' ) ) {
			require_once SKY_ADDONS_INC_PATH . 'features/class-menu-duplicator.php';
			\Sky_Addons\Features\Menu_Duplicator::get_instance();
		}
	}

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}

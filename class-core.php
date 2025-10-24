<?php
/**
 * Core File
 *
 * @package Sky_Addons
 * @since 3.0.0
 */

namespace Sky_Addons;

defined( 'ABSPATH' ) || exit;

/**
 * Plugin Core
 * Register Files / Layouts
 *
 * @since 3.0.0
 * @author Shahidul Islam
 */
final class Core {

	/**
	 * Instance
	 *
	 * @var object
	 * @since 3.0.0
	 */
	private static $instance;

	/**
	 * Instance
	 *
	 * @return object
	 * @since 3.0.0
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
			self::$instance->init();

			do_action( 'skyaddons_loaded' );
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 *
	 * @return void
	 * @since 3.0.0
	 */
	public function __construct() {
	}

	/**
	 * Init
	 *
	 * @return void
	 * @since 3.0.0
	 */
	public function init() {
		$this->include_files();
	}

	/**
	 * Include Files
	 *
	 * @return void
	 * @since 3.0.0
	 */
	public function include_files() {
		// require_once SKY_ADDONS_INC_PATH . 'admin/Classes/class-dashboard.php';
		// require_once SKY_ADDONS_INC_PATH . 'admin/Classes/class-widgets-settings.php';
		// require_once SKY_ADDONS_INC_PATH . 'admin/class-menu.php';
		// require_once SKY_ADDONS_INC_PATH . 'admin/class-admin.php';
		// new Admin();

		// /**
		// * Admin Files Only
		// */
		// if ( is_admin() ) {
		// require_once SKY_ADDONS_INC_PATH . 'class-admin-feeds.php';
		// }
	}
}

if ( class_exists( 'Sky_Addons\Core' ) ) {
	new \Sky_Addons\Core();
}

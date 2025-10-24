<?php
/**
 * Admin class
 *
 * @package Sky_Addons
 * @since 2.7.0
 */

namespace Sky_Addons;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Description of Menu
 *
 * @since 2.7.0
 */

class Admin {
	public function __construct() {
		$this->dispatch_actions();
		new Admin\Menu();
	}

	/**
	 * Dispatch Actions
	 *
	 * @since 1.0.0
	 */
	public function dispatch_actions() {
		new Classes\Dashboard();
		new Classes\Widgets_Settings();
	}
}

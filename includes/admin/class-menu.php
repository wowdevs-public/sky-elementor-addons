<?php
/**
 * Menu class
 *
 * @package Sky_Addons\Admin
 * @since 2.7.0
 */

namespace Sky_Addons\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Description of Menu
 *
 * @since 2.7.0
 */
class Menu {
	/**
	 * Constructor
	 *
	 * @return void
	 * @since 2.7.0
	 */
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'admin_menu' ] );
	}

	/**
	 * Register admin menu
	 *
	 * @return void
	 * @since 2.6.5
	 */
	public function admin_menu() {
		$parent_slug = 'sky-addons';
		$capability  = 'manage_options';
		add_menu_page( 'Sky Addons', 'Sky Addons', $capability, $parent_slug, [ $this, 'plugin_layout' ], $this->get_b64_icon(), 59 );

		add_submenu_page( $parent_slug, esc_html__( 'Dashboard', 'sky-elementor-addons' ), esc_html__( 'Dashboard', 'sky-elementor-addons' ), $capability, $parent_slug, [
			$this,
			'plugin_layout',
		] );

		add_submenu_page( $parent_slug, esc_html__( 'Widgets', 'sky-elementor-addons' ), esc_html__( 'Widgets', 'sky-elementor-addons' ), $capability, $parent_slug . '#widgets', [
			$this,
			'plugin_layout',
		] );

		add_submenu_page( $parent_slug, esc_html__( 'Extensions', 'sky-elementor-addons' ), esc_html__( 'Extensions', 'sky-elementor-addons' ), $capability, $parent_slug . '#extensions', [
			$this,
			'plugin_layout',
		] );

		add_submenu_page( $parent_slug, esc_html__( '3rd Party', 'sky-elementor-addons' ), esc_html__( '3rd Party', 'sky-elementor-addons' ), $capability, $parent_slug . '#thirdparty', [
			$this,
			'plugin_layout',
		] );

		// add_submenu_page( $parent_slug, esc_html__( 'API Data', 'sky-elementor-addons' ), esc_html__( 'API Data', 'sky-elementor-addons' ), $capability, $parent_slug . '#api', [
		// $this,
		// 'plugin_layout',
		// ] );

		add_submenu_page( $parent_slug, esc_html__( 'Theme Builder', 'sky-elementor-addons' ), esc_html__( 'Theme Builder', 'sky-elementor-addons' ), $capability, $parent_slug . '#theme_builder', [
			$this,
			'plugin_layout',
		] );

		if ( ! _is_sky_addons_pro_activated() ) {
			add_submenu_page( $parent_slug, esc_html__( 'Get PRO', 'sky-elementor-addons' ), esc_html__( 'Get PRO', 'sky-elementor-addons' ), $capability, $parent_slug . '#license', [
				$this,
				'plugin_layout',
			] );
		}

		if ( _is_sky_addons_pro_activated() ) {
			add_submenu_page( $parent_slug, esc_html__( 'License', 'sky-elementor-addons' ), esc_html__( 'License', 'sky-elementor-addons' ), $capability, $parent_slug . '#license', [
				$this,
				'plugin_layout',
			] );
		}
	}

	/**
	 * Plugin Layout
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function plugin_layout() {
		echo '<div id="sky-addons" class="wrap sky-addons"> <h2>Loading...</h2> </div>';
	}

	public static function get_dashboard_link( $suffix = '#' ) {
		return add_query_arg( [ 'page' => 'sky-addons' . $suffix ], admin_url( 'admin.php' ) );
	}

	public static function get_b64_icon() {
		return 'data:image/svg+xml;base64,' . base64_encode( file_get_contents( SKY_ADDONS_ASSETS_PATH . 'images/sky-top-menu-logo.svg' ) );
	}
}

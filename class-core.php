<?php
/**
 * Core File — Admin Bootstrap
 *
 * Responsible for all admin matters: menu, dashboard REST API,
 * custom scripts CPT, and React dashboard asset enqueuing.
 * Runs with or without Elementor.
 *
 * @package Sky_Addons
 * @since   3.0.0
 */

namespace Sky_Addons;

defined( 'ABSPATH' ) || exit;

/**
 * Plugin Core
 *
 * @since 3.0.0
 */
final class Core {

	/**
	 * @var Core
	 */
	private static $instance;

	/**
	 * @return Core
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
			self::$instance->init();
		}

		return self::$instance;
	}

	public function __construct() {}

	/**
	 * Boot admin subsystems.
	 *
	 * @return void
	 */
	public function init() {
		$this->include_files();
		$this->setup_hooks();
	}

	/**
	 * Load all admin-only PHP files.
	 * None of these files have an Elementor dependency.
	 *
	 * @return void
	 */
	private function include_files() {

		/**
		 * Admin REST API + settings handlers.
		 * Not wrapped in is_admin() — REST requests bypass the admin flag.
		 */
		require_once SKY_ADDONS_INC_PATH . 'admin/Classes/class-sky-addons-admin.php';
		require_once SKY_ADDONS_INC_PATH . 'admin/Classes/class-dashboard.php';
		require_once SKY_ADDONS_INC_PATH . 'admin/Classes/class-widgets-settings.php';
		require_once SKY_ADDONS_INC_PATH . 'admin/class-menu.php';
		require_once SKY_ADDONS_INC_PATH . 'admin/class-admin.php';
		new Admin();

		/**
		 * Custom Scripts CPT, REST endpoint, and frontend loader.
		 * Must register even without Elementor so the dashboard REST calls resolve.
		 */
		require_once SKY_ADDONS_INC_PATH . 'custom-scripts/class-custom-scripts-data.php';
		require_once SKY_ADDONS_INC_PATH . 'custom-scripts/class-custom-scripts-loader.php';

		if ( is_admin() ) {
			require_once SKY_ADDONS_INC_PATH . 'class-admin-feeds.php';
		}
	}

	/**
	 * Register admin-facing hooks.
	 *
	 * @return void
	 */
	private function setup_hooks() {
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_styles' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'theme_builder_scripts' ] );
	}

	// -------------------------------------------------------------------------
	// Asset enqueuing
	// -------------------------------------------------------------------------

	/**
	 * React dashboard — CSS.
	 *
	 * @param string $hook_suffix
	 * @return void
	 */
	public function enqueue_styles( $hook_suffix ) {
		if ( 'toplevel_page_sky-addons' !== $hook_suffix && 'sky-addons_page_sky-addons-pro' !== $hook_suffix ) {
			return;
		}
		wp_enqueue_style( 'wp-components' );
		wp_register_style( 'sky-addons-admin', SKY_ADDONS_URL . 'build/admin/index.css', [], SKY_ADDONS_VERSION );
		wp_enqueue_style( 'sky-addons-admin' );
	}

	/**
	 * React dashboard — JS + SkyAddonsConfig.
	 *
	 * @param string $hook_suffix
	 * @return void
	 */
	public function enqueue_scripts( $hook_suffix ) {
		if ( 'toplevel_page_sky-addons' !== $hook_suffix ) {
			return;
		}

		$asset_file = SKY_ADDONS_PATH . 'build/admin/index.asset.php';
		if ( ! file_exists( $asset_file ) ) {
			return;
		}

		$asset = include $asset_file;
		wp_register_script( 'sky-addons-admin', SKY_ADDONS_URL . 'build/admin/index.js', $asset['dependencies'], $asset['version'], true );
		wp_enqueue_script( 'sky-addons-admin' );
		wp_localize_script( 'sky-addons-admin', 'SkyAddonsConfig', $this->localize_config() );
	}

	/**
	 * Theme Builder post editor — JS.
	 *
	 * @param string $hook
	 * @return void
	 */
	public function theme_builder_scripts( $hook ) {
		//phpcs:ignore WordPress.PHP.StrictInArray
		if ( ! in_array( $hook, [ 'post.php', 'post-new.php' ] ) ) {
			return;
		}
		global $post_type;
		if ( 'wowdevs-hooks' !== $post_type ) {
			return;
		}

		wp_register_script( 'wowdevs-hooks', SKY_ADDONS_URL . 'build/theme-builder/index.js', [], SKY_ADDONS_VERSION, true );
		wp_localize_script( 'wowdevs-hooks', 'SkyAddonsConfig', $this->localize_config() );
		wp_enqueue_script( 'wowdevs-hooks' );
	}

	/**
	 * Shared JS config passed to all admin React apps.
	 *
	 * @return array
	 */
	public function localize_config() {
		$config = [
			'web_url'     => esc_url( home_url() ),
			'ajax_url'    => esc_url( admin_url( 'admin-ajax.php' ) ),
			'rest_url'    => esc_url( rest_url() ),
			'version'     => SKY_ADDONS_VERSION,
			'plugin_name' => esc_html__( 'Sky Addons', 'sky-elementor-addons' ),
			'plugin_slug' => defined( 'SKY_ADDONS_SLUG' ) ? SKY_ADDONS_SLUG : '',
			'admin_url'   => esc_url( admin_url() ),
			'pro_version' => defined( 'SKY_ADDONS_PRO_VERSION' ) ? SKY_ADDONS_PRO_VERSION : '',
			'nonce'       => wp_create_nonce( 'sky_addons_nonce' ),
			'assets_url'  => SKY_ADDONS_ASSETS_URL,
			'logo'        => SKY_ADDONS_ASSETS_URL . 'images/sky-logo-gradient.png',
			'root_url'    => SKY_ADDONS_URL,
			'pro_init'    => apply_filters( 'sky_addons_pro_init', false ),
			'system_info' => $this->get_system_info(),
			'current_user' => [
				'domain'       => esc_url( home_url() ),
				'display_name' => wp_get_current_user()->display_name,
				'email'        => wp_get_current_user()->user_email,
				'id'           => wp_get_current_user()->ID,
				'avatar'       => get_avatar_url( wp_get_current_user()->ID ),
			],
		];

		/**
		 * Filters the config shared with the admin React apps (Sky Addons Pro's White Label changes name/logo here).
		 *
		 * @param array $config
		 */
		return apply_filters( 'sky_addons/admin/config', $config );
	}

	/**
	 * Server / environment info for the Help & Support page.
	 *
	 * Each row: label, value (display string), type (text|bool), status (ok|warn|off)
	 * so the dashboard can colour it. Read-only — nothing here changes state.
	 *
	 * @return array
	 */
	public function get_system_info() {
		$upload_dir       = wp_upload_dir();
		$uploads_writable = ! empty( $upload_dir['basedir'] ) && wp_is_writable( $upload_dir['basedir'] );
		$gzip             = function_exists( 'gzencode' );
		$debug            = defined( 'WP_DEBUG' ) && WP_DEBUG;
		$php_ok           = version_compare( PHP_VERSION, '7.4', '>=' );

		return [
			[
				'label'  => esc_html__( 'PHP Version', 'sky-elementor-addons' ),
				'value'  => PHP_VERSION,
				'type'   => 'text',
				'status' => $php_ok ? 'ok' : 'warn',
			],
			[
				'label'  => esc_html__( 'WordPress Version', 'sky-elementor-addons' ),
				'value'  => get_bloginfo( 'version' ),
				'type'   => 'text',
				'status' => 'ok',
			],
			[
				'label'  => esc_html__( 'Max Execution Time', 'sky-elementor-addons' ),
				'value'  => ini_get( 'max_execution_time' ) . 's',
				'type'   => 'text',
				'status' => (int) ini_get( 'max_execution_time' ) >= 30 || 0 === (int) ini_get( 'max_execution_time' ) ? 'ok' : 'warn',
			],
			[
				'label'  => esc_html__( 'Memory Limit', 'sky-elementor-addons' ),
				'value'  => ini_get( 'memory_limit' ),
				'type'   => 'text',
				'status' => 'ok',
			],
			[
				'label'  => esc_html__( 'Max Post Limit', 'sky-elementor-addons' ),
				'value'  => ini_get( 'post_max_size' ),
				'type'   => 'text',
				'status' => 'ok',
			],
			[
				'label'  => esc_html__( 'Max Upload Size', 'sky-elementor-addons' ),
				'value'  => ini_get( 'upload_max_filesize' ),
				'type'   => 'text',
				'status' => 'ok',
			],
			[
				'label'  => esc_html__( 'Uploads Folder Writable', 'sky-elementor-addons' ),
				'value'  => $uploads_writable,
				'type'   => 'bool',
				'status' => $uploads_writable ? 'ok' : 'warn',
			],
			[
				'label'  => esc_html__( 'MultiSite', 'sky-elementor-addons' ),
				'value'  => is_multisite() ? esc_html__( 'Multisite', 'sky-elementor-addons' ) : esc_html__( 'Single Site', 'sky-elementor-addons' ),
				'type'   => 'text',
				'status' => 'ok',
			],
			[
				'label'  => esc_html__( 'GZip Enabled', 'sky-elementor-addons' ),
				'value'  => $gzip,
				'type'   => 'bool',
				'status' => $gzip ? 'ok' : 'off',
			],
			[
				'label'  => esc_html__( 'Debug Mode', 'sky-elementor-addons' ),
				'value'  => $debug ? esc_html__( 'Turned On', 'sky-elementor-addons' ) : esc_html__( 'Turned Off', 'sky-elementor-addons' ),
				'type'   => 'text',
				'status' => $debug ? 'warn' : 'ok',
			],
		];
	}
}

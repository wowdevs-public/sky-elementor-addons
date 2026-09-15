<?php

namespace Sky_Addons;

use Elementor\Plugin;
use Elementor\Controls_Manager;
use Elementor\Elements_Manager;
use Sky_Addons\Includes\WPML_Init;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main class plugin -> Sky_Addons
 */
class Sky_Addons_Plugin {

	/**
	 * @var Plugin -> Sky_Addons
	 */
	private static $_instance;

	/**
	 * Modules Manager
	 *
	 * @var Managers
	 */
	private $_modules_manager;

	/**
	 * @var array
	 */
	private $_localize_settings = [];

	/**
	 * @return string
	 */
	public function get_version() {
		return SKY_ADDONS_VERSION;
	}

	/**
	 * Throw error on object clone
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object therefore, we don't want the object to be cloned.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function __clone() {
		// Cloning instances of the class is forbidden
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'sky-elementor-addons' ), '1.0.0' );
	}

	/**
	 * Disable unserializing of the class
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function __wakeup() {
		// Unserializing instances of the class is forbidden
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'sky-elementor-addons' ), '1.0.0' );
	}

	/**
	 * @return Plugin
	 */
	public static function elementor() {
		return Plugin::$instance;
	}

	/**
	 * @return Plugin -> Sky_Addons
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();

			/**
			 * Fire this action on the load time
			 * This method will catch by PRO
			 * Pro will not work without this method
			 */
			do_action( 'skyaddons_loaded' );
			self::$_instance->add_actions();
			self::$_instance->includes();
			self::$_instance->wpml_compatibility()->init();
		}

		return self::$_instance;
	}

	public function wpml_compatibility() {
		return WPML_Init::get_instance();
	}

	private function includes() {

		require SKY_ADDONS_PATH . 'includes/modules-manager.php';
		/**
		 * Utils Files
		 */
		require SKY_ADDONS_PATH . 'includes/utils.php';

		require_once SKY_ADDONS_PATH . 'traits/global-swiper-controls.php';
		require_once SKY_ADDONS_PATH . 'traits/global-widget-controls.php';
		require_once SKY_ADDONS_PATH . 'traits/global-widget-functions.php';
		require_once SKY_ADDONS_PATH . 'traits/theme-builder-context.php';

		/**
		 * Select Control
		 *
		 * @since 1.1.0
		 */
		require_once SKY_ADDONS_INC_PATH . 'controls/select-input/dynamic-input-module.php';
		require_once SKY_ADDONS_INC_PATH . 'controls/select-input/dynamic-select.php';
		require_once SKY_ADDONS_INC_PATH . 'controls/widget-list/widget-list.php';

		/**
		 * Dynamic Content Tags
		 */
		if ( Managers::is_advanced_feature_active( 'dynamic-tags' ) ) {
			require_once SKY_ADDONS_INC_PATH . 'dynamic-tags/utils.php';
			require_once SKY_ADDONS_INC_PATH . 'dynamic-tags/index.php';
		}

		/**
		 * Templates Library
		 */
		if ( Managers::is_advanced_feature_active( 'templates-library' ) ) {
			require_once SKY_ADDONS_INC_PATH . 'templates/index.php';
		}

		/**
		 * Themes Builder
		 */
		require_once SKY_ADDONS_INC_PATH . 'theme-builder/class-theme-builder.php';

		/**
		 * Features
		 */
		require_once SKY_ADDONS_INC_PATH . 'features/class-init.php';
		\Sky_Addons\Features\Init::get_instance();

		/**
		 * WPML
		 */
		require_once SKY_ADDONS_INC_PATH . 'class-wpml-init.php';

		/**
		 * Asset Manager (optimizer) — class + helpers already loaded early via
		 * includes/optimizer/index.php (bootstrap). Only activate the engine
		 * here, where Elementor is guaranteed active.
		 */
		\Sky_Addons\Optimizer\Optimizer::instance();
	}

	public function autoload( $_class ) {
		if ( 0 !== strpos( $_class, __NAMESPACE__ ) ) {
			return;
		}

		$filename = strtolower(
			preg_replace(
				[ '/^' . __NAMESPACE__ . '\\\/', '/([a-z])([A-Z])/', '/_/', '/\\\/' ],
				[
					'',
					'$1-$2',
					'-',
					DIRECTORY_SEPARATOR,
				],
				$_class
			)
		);
		$filename = SKY_ADDONS_PATH . $filename . '.php';

		if ( is_readable( $filename ) ) {
			include $filename;
		}
	}

	public function get_localize_settings() {
		return $this->_localize_settings;
	}

	public function add_localize_settings( $setting_key, $setting_value = null ) {
		if ( is_array( $setting_key ) ) {
			$this->_localize_settings = array_replace_recursive( $this->_localize_settings, $setting_key );

			return;
		}

		if ( ! is_array( $setting_value ) || ! isset( $this->_localize_settings[ $setting_key ] ) || ! is_array( $this->_localize_settings[ $setting_key ] ) ) {
			$this->_localize_settings[ $setting_key ] = $setting_value;

			return;
		}

		$this->_localize_settings[ $setting_key ] = array_replace_recursive( $this->_localize_settings[ $setting_key ], $setting_value );
	}

	public function enqueue_styles() {
		$direction_suffix = is_rtl() ? '.rtl' : '';

		wp_register_style(
			'sky-addons',
			SKY_ADDONS_URL . 'assets/css/sky-addons' . $direction_suffix . '.css',
			[],
			SKY_ADDONS_VERSION
		);

		// Shared utility CSS (margin/text helpers, post/wc classes, keyframes) compiled
		// from src/less/utils/** alone. Standalone handle so each per-widget stylesheet
		// can depend on it instead of re-bundling the utils. Same `sky-addons-base`
		// name is reused on the script side (separate WP registry) for the shared JS.
		wp_register_style(
			'sky-addons-base',
			SKY_ADDONS_URL . 'assets/css/sky-addons-base' . $direction_suffix . '.css',
			[],
			SKY_ADDONS_VERSION
		);

		// AM-on / editor: combined bundle carries utils + every widget's CSS.
		// per-widget: no combined bundle; enqueue base utilities CSS directly so
		// widgets with has_style=false still get global keyframes/helpers.
		if ( 'per-widget' === sky_addons_asset_mode() && ! sky_addons_editor_mode() ) {
			wp_enqueue_style( 'sky-addons-base' );
		}
	}

	public function enqueue_editor_styles() {
		$direction_suffix = is_rtl() ? '.rtl' : '';

		wp_register_style( 'sky-addons-widget-icons', SKY_ADDONS_ASSETS_URL . 'css/sky-widget-icons' . $direction_suffix . '.css', [], SKY_ADDONS_VERSION );
		wp_enqueue_style( 'sky-addons-widget-icons' );

		wp_register_style( 'sky-addons-editor', SKY_ADDONS_ASSETS_URL . 'css/sky-editor' . $direction_suffix . '.css', [], SKY_ADDONS_VERSION );

		wp_enqueue_style( 'sky-addons-editor' );
	}

	public function enqueue_scripts() {

		if ( self::elementor()->preview->is_preview_mode() || self::elementor()->editor->is_edit_mode() ) {
			// tippyjs — used by Logo Carousel + Logo Grid widgets.
			if (
				Managers::is_widget_active( 'logo-carousel' )
				|| Managers::is_widget_active( 'logo-grid' )
			) {
				wp_enqueue_script( 'tippyjs' );
			}

			// anime — shared by Floating Effects + Reveal Effects extensions (revealFx depends on anime).
			if (
				Managers::is_extension_active( 'floating-effects' )
				|| Managers::is_extension_active( 'reveal-effects' )
			) {
				wp_enqueue_script( 'anime' );
			}

			// Per-extension handlers + their dedicated vendors.
			if ( Managers::is_extension_active( 'equal-height' ) ) {
				wp_enqueue_script( 'equal-height' );
			}
			if ( Managers::is_extension_active( 'reveal-effects' ) ) {
				wp_enqueue_script( 'revealFx' );
			}
			if ( Managers::is_extension_active( 'ripples-effect' ) ) {
				wp_enqueue_script( 'ripples' );
			}
			if ( Managers::is_extension_active( 'simple-parallax' ) ) {
				wp_enqueue_script( 'simple-parallax' );
			}
			if ( Managers::is_extension_active( 'animated-gradient-bg' ) ) {
				wp_enqueue_script( 'granim' );
			}
		}

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		// Shared JS helpers (skyAddonsObserver, widgetGlobalCarousel) — standalone
		// `sky-addons-base` handle (built from src/js/common.js) so widgets, extensions,
		// the optimizer bundle and the pro plugin can all depend on it without
		// re-bundling the same code. Mirrors the `sky-addons-base` style handle above.
		wp_register_script(
			'sky-addons-base',
			SKY_ADDONS_URL . 'assets/js/sky-addons-base' . $suffix . '.js',
			[ 'jquery' ],
			SKY_ADDONS_VERSION,
			true
		);

		// Frontend config — attached to the shared `sky-addons-base` helper (a dependency
		// of both `sky-addons` and the optimizer bundle `sky-addons-scripts`) so it is
		// printed regardless of which delivery path loads on the page.
		wp_localize_script(
			'sky-addons-base',
			'SkyAddonsFrontendConfig', // This is used in the js file to group all of your scripts together
			[
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'sky_addons_nonce' ),
			]
		);

		wp_register_script(
			'sky-addons',
			SKY_ADDONS_URL . 'assets/js/sky-addons' . $suffix . '.js',
			[
				'jquery',
				'sky-addons-base',
				'elementor-frontend',
			],
			SKY_ADDONS_VERSION,
			true
		);

		// Single dispatch point for the widget/extension bundle.
		// AM-on or editor/preview → load the optimizer's combined bundle (Tier 1 uploads
		// / Tier 2 plugin combined), which carries every widget's JS + CSS.
		// AM-off frontend → nothing global: each rendered widget's `sa-{id}` handle loads
		// only its own file and pulls the shared `sky-addons-base` helpers via its
		// dependency, so a page ships only the assets of the widgets actually on it.
		// (`sky-addons` stays registered above for back-compat; it is no longer enqueued.)
		if ( sky_addons_editor_mode() || sky_addons_is_asset_optimization_enabled() ) {
			$this->enqueue_optimized_bundle();
		}
	}

	/**
	 * Asset Manager bundle — frontend / Elementor preview enqueue.
	 *
	 * Tier 1 — uploads bundle (generated, active-widgets-only, minified).
	 * Tier 2 — plugin combined file (shipped, all widgets, instant fallback).
	 * Editor  — always Tier 2 (fast, always available regardless of optimizer state).
	 *
	 * Registration + enqueue for every Sky Addons asset lives in this file; the
	 * Optimizer class owns only the bundle build/regeneration pipeline.
	 */
	public function enqueue_optimized_bundle() {
		if ( sky_addons_editor_mode() ) {
			$this->register_and_enqueue_bundle( false );
			return;
		}

		$mode = sky_addons_asset_mode();

		switch ( $mode ) {
			case 'per-widget':
				return;

			case 'full':
				$this->register_and_enqueue_bundle( false );
				return;

			case 'generated':
			default:
				$this->register_and_enqueue_bundle( $this->is_uploads_bundle_ready() );
				return;
		}
	}

	/**
	 * Whether a usable uploads bundle exists on disk (Tier 1).
	 */
	private function is_uploads_bundle_ready() {
		if ( ! get_option( 'sky_addons_minified_asset_version', false ) ) {
			return false;
		}

		$dir = \Sky_Addons\Optimizer\Asset_Manager::get_upload_dir();

		return file_exists( $dir . 'css/sky-addons.css' ) && file_exists( $dir . 'js/sky-addons.js' );
	}

	/**
	 * Register sky-addons-styles / sky-addons-scripts from the correct source,
	 * then enqueue both by handle name.
	 *
	 * @param bool $use_uploads_bundle True → Tier 1 (uploads), False → Tier 2 (plugin).
	 */
	private function register_and_enqueue_bundle( $use_uploads_bundle ) {
		if ( $use_uploads_bundle ) {
			// Tier 1: uploads bundle is self-contained — minify_js() prepends sky-addons-base.min.js
			// and minify_css() prepends sky-addons-base.css into the generated files.
			// Do NOT dep on sky-addons-base: it is already baked in, loading it separately is duplicate.
			$deps_js  = [ 'jquery', 'elementor-frontend' ];
			$base_dir = \Sky_Addons\Optimizer\Asset_Manager::get_upload_dir();
			$base_url = \Sky_Addons\Optimizer\Asset_Manager::get_upload_url();
			$version  = get_option( 'sky_addons_minified_asset_version' );
			$css_file = ( is_rtl() && file_exists( $base_dir . 'css/sky-addons.rtl.css' ) )
				? 'css/sky-addons.rtl.css'
				: 'css/sky-addons.css';
			$css_url  = $base_url . $css_file;
			$js_url   = $base_url . 'js/sky-addons.js';
		} else {
			// Tier 2: plugin bundle JS (sky-addons.min.js) contains widget handlers only —
			// sky-addons-base helpers are NOT included, so the dep is required here.
			$deps_js = [ 'jquery', 'elementor-frontend', 'sky-addons-base' ];
			$suffix  = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			$rtl     = is_rtl() && file_exists( SKY_ADDONS_ASSETS_PATH . 'css/sky-addons.rtl.css' );
			$version = SKY_ADDONS_VERSION;
			$css_url = SKY_ADDONS_ASSETS_URL . 'css/sky-addons' . ( $rtl ? '.rtl' : '' ) . '.css';
			$js_url  = SKY_ADDONS_ASSETS_URL . 'js/sky-addons' . $suffix . '.js';
		}

		wp_register_style( 'sky-addons-styles', $css_url, [], $version );
		wp_register_script( 'sky-addons-scripts', $js_url, $deps_js, $version, true );

		// Tier 1: sky-addons-base won't load as a dep (content is baked in the bundle),
		// so the frontend config that is normally localized on sky-addons-base won't print.
		// Attach it here so it is always available when the bundle executes.
		if ( $use_uploads_bundle ) {
			wp_localize_script(
				'sky-addons-scripts',
				'SkyAddonsFrontendConfig',
				[
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
					'nonce'   => wp_create_nonce( 'sky_addons_nonce' ),
				]
			);
		}

		wp_enqueue_style( 'sky-addons-styles' );
		wp_enqueue_script( 'sky-addons-scripts' );
	}

	/**
	 * Third-party libraries ship minified only — the un-minified sources were removed
	 * from `src/vendor/js` to cut ~666 KB from the plugin. Do not reintroduce a
	 * SCRIPT_DEBUG suffix here: the plain `.js` files no longer exist and every handle
	 * below would 404 on any site with SCRIPT_DEBUG on. `$suffix` still applies to our
	 * own bundles (`sky-addons-base`, `sa-{slug}`), which do ship both builds.
	 */
	public function register_vendor_scripts() {
		wp_register_script(
			'image-compare-viewer',
			SKY_ADDONS_ASSETS_URL . 'vendor/js/image-compare-viewer.min.js',
			[
				'jquery',
				'elementor-frontend',
			],
			'1.0.0',
			true
		);
		wp_register_script( 'momentum', SKY_ADDONS_ASSETS_URL . 'vendor/js/momentum-slider.min.js', [], '1.0.0', true );
		wp_register_script( 'wowdevs-accordion', SKY_ADDONS_ASSETS_URL . 'vendor/js/accordion.min.js', [], '3.1.1', true );
		wp_register_script(
			'anime',
			SKY_ADDONS_ASSETS_URL . 'vendor/js/anime.min.js',
			[
				'jquery',
			],
			'3.2.1',
			true
		);
		wp_register_script( 'popper', SKY_ADDONS_ASSETS_URL . 'vendor/js/popper.min.js', [], '2.10.1', true );
		wp_register_script( 'tippyjs', SKY_ADDONS_ASSETS_URL . 'vendor/js/tippy-bundle.umd.min.js', [], '6.3.1', true );

		wp_register_script( 'countUp', SKY_ADDONS_ASSETS_URL . 'vendor/js/countUp.min.js', [], '2.0.4', true );
		wp_register_script( 'metis-menu', SKY_ADDONS_ASSETS_URL . 'vendor/js/metis-menu.min.js', [ 'jquery' ], '3.0.7', true );
		wp_register_script( 'equal-height', SKY_ADDONS_ASSETS_URL . 'vendor/js/jquery.matchHeight.min.js', [ 'jquery' ], '0.7.2', true );
		wp_register_script( 'pdfobject', SKY_ADDONS_ASSETS_URL . 'vendor/js/pdfobject.min.js', [ 'jquery' ], 'v2.2.7', true );
		wp_register_script( 'granim', SKY_ADDONS_ASSETS_URL . 'vendor/js/granim.min.js', [], 'v2.0.0', true );
		wp_register_script( 'ripples', SKY_ADDONS_ASSETS_URL . 'vendor/js/jquery.ripples.min.js', [ 'jquery' ], 'v0.5.3', true );
		wp_register_script( 'slinky', SKY_ADDONS_ASSETS_URL . 'vendor/js/slinky.min.js', [ 'jquery' ], '1.0.0', true );
		wp_register_script( 'revealFx', SKY_ADDONS_ASSETS_URL . 'vendor/js/revealFx.min.js', [ 'jquery', 'anime' ], '0.0.2', true );
		wp_register_script( 'typed', SKY_ADDONS_ASSETS_URL . 'vendor/js/typed.min.js', [], 'v2.0.12', true );
		wp_register_script( 'morphext', SKY_ADDONS_ASSETS_URL . 'vendor/js/morphext.min.js', [], 'v2.4.4', true );
		wp_register_script( 'plyr', SKY_ADDONS_ASSETS_URL . 'vendor/js/plyr.min.js', [], '3.8.4', true );

		/**
		 * Plyr defaults to cdn.plyr.io for its icon sprite and blank video. Both are
		 * shipped locally instead — every handler passes these two URLs into `new Plyr()`
		 * so no page ever makes a third-party request.
		 */
		wp_localize_script( 'plyr', 'skyAddonsPlyr', [
			'iconUrl'    => SKY_ADDONS_ASSETS_URL . 'vendor/svg/plyr.svg',
			'blankVideo' => SKY_ADDONS_ASSETS_URL . 'others/blank.mp4',
		] );
		wp_register_script( 'simple-parallax', SKY_ADDONS_ASSETS_URL . 'vendor/js/simpleParallax.min.js', [], '7.0.0', true );
		wp_register_script( 'tocbot', SKY_ADDONS_ASSETS_URL . 'vendor/js/tocbot.min.js', [], '4.21.1', true );
	}

	public function register_vendor_styles() {
		$direction_suffix = is_rtl() ? '.rtl' : '.min';
		wp_register_style( 'wowdevs-accordion', SKY_ADDONS_ASSETS_URL . 'vendor/css/accordion' . $direction_suffix . '.css', [], '3.1.1' );
		wp_register_style( 'tippy', SKY_ADDONS_ASSETS_URL . 'vendor/css/tippy-animation' . $direction_suffix . '.css', [], '6.3.1' );
		wp_register_style( 'momentum', SKY_ADDONS_ASSETS_URL . 'vendor/css/momentum-slider' . $direction_suffix . '.css', [], '1.0.0' );
		wp_register_style( 'metis-menu', SKY_ADDONS_ASSETS_URL . 'vendor/css/metis-menu' . $direction_suffix . '.css', [], '13.0.7' );
		wp_register_style( 'slinky', SKY_ADDONS_ASSETS_URL . 'vendor/css/slinky' . $direction_suffix . '.css', [], '1.0.0' );
		wp_register_style( 'plyr', SKY_ADDONS_ASSETS_URL . 'vendor/css/plyr' . $direction_suffix . '.css', [], '3.8.4' );
	}

	public function enqueue_editor_scripts() {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_register_script(
			'sky-addons-editor',
			SKY_ADDONS_ASSETS_URL . 'js/sky-addons-editor' . $suffix . '.js',
			[
				'backbone-marionette',
				'elementor-common-modules',
				'elementor-editor-modules',
			],
			SKY_ADDONS_VERSION,
			true
		);

		$localize_data = [
			'pro_installed'       => _is_sky_addons_pro_activated(),
			'promotional_widgets' => [],
		];

		if ( ! _is_sky_addons_pro_activated() ) {
			$pro_widget_map                       = new \Sky_Addons\Includes\Pro_Widget_Map();
			$localize_data['promotional_widgets'] = $pro_widget_map->get_pro_widget_map();
		}

		wp_localize_script( 'sky-addons-editor', 'SkyAddonsEditorConfig', $localize_data );

		wp_enqueue_script( 'sky-addons-editor' );
	}

	public function elementor_init() {
		// Register sky-addons-base handles before Managers::__construct() so every
		// sa-{slug} handle can safely declare them as deps (WP 6.9.1 strict dep check).
		$direction_suffix = is_rtl() ? '.rtl' : '';
		$suffix           = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		wp_register_style( 'sky-addons-base', SKY_ADDONS_URL . 'assets/css/sky-addons-base' . $direction_suffix . '.css', [], SKY_ADDONS_VERSION );
		wp_register_script( 'sky-addons-base', SKY_ADDONS_URL . 'assets/js/sky-addons-base' . $suffix . '.js', [ 'jquery' ], SKY_ADDONS_VERSION, true );

		$this->_modules_manager = new Managers();

		/**
		 * Add element category in panel
		 */
		Plugin::instance()->elements_manager->add_category(
			'sky-elementor-addons',
			[
				// Sky Addons Pro's White Label can rename it (Pro includes/white-label/hooks/class-elementor-categories.php).
				'title' => apply_filters( 'sky_addons/white_label/category_title', esc_html__( 'Sky Addons', 'sky-elementor-addons' ), 'sky-elementor-addons' ),
				'icon'  => 'font',
			]
		);

		if (
			class_exists( 'Sky_Addons\Templates\Init_Templates' )
			&& Managers::is_advanced_feature_active( 'templates-library' )
		) {
			\Sky_Addons\Templates\Import_Template::instance()->load();
			\Sky_Addons\Templates\Library_Load::instance()->load();
			\Sky_Addons\Templates\Init_Templates::instance()->init();
		}
	}

	/**
	 * Show the Sky Addons and Sky Addons Pro panel categories right after Elementor's "Basic".
	 *
	 * They are added at `elementor/init`, after Elementor has already appended "WordPress", so
	 * they would sit at the bottom of the panel. Elementor has no API to reorder categories, so
	 * this moves the two keys inside the private Elements_Manager::$categories. If Elementor ever
	 * renames that property this does nothing and the categories stay at the bottom.
	 */
	public function order_panel_categories() {
		$move = function () {
			if ( ! isset( $this->categories ) || ! is_array( $this->categories ) ) {
				return;
			}

			$after = isset( $this->categories['basic'] ) ? 'basic' : 'general';
			$ours  = array_intersect_key( $this->categories, array_flip( [ 'sky-elementor-addons', 'sky-elementor-addons-pro' ] ) );

			if ( empty( $ours ) || ! isset( $this->categories[ $after ] ) ) {
				return;
			}

			$rest     = array_diff_key( $this->categories, $ours );
			$position = array_search( $after, array_keys( $rest ), true ) + 1;

			$this->categories = array_merge(
				array_slice( $rest, 0, $position, true ),
				$ours,
				array_slice( $rest, $position, null, true )
			);
		};

		\Closure::bind( $move, Plugin::instance()->elements_manager, Elements_Manager::class )();
	}

	public static function sky_addons_file() {
		return SKY_ADDONS__FILE__;
	}

	public static function sky_addons_url() {
		return trailingslashit( plugin_dir_url( self::sky_addons_file() ) );
	}

	public static function sky_addons_dir() {
		return trailingslashit( plugin_dir_path( self::sky_addons_file() ) );
	}

	/**
	 * Dont touch this actions without Permisssion
	 */
	protected function add_actions() {
		add_action( 'elementor/init', [ $this, 'elementor_init' ] );
		// Late, so it runs after Core and Pro add their categories and after other addons reorder theirs.
		add_action( 'elementor/init', [ $this, 'order_panel_categories' ], 9999 );

		add_action( 'elementor/editor/after_enqueue_styles', [ $this, 'enqueue_editor_styles' ], 991 );
		add_action( 'elementor/editor/after_enqueue_scripts', [ $this, 'enqueue_editor_scripts' ] );

		add_action( 'elementor/frontend/before_register_styles', [ $this, 'register_vendor_styles' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'register_vendor_scripts' ] );

		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_styles' ], 998 );
		add_action( 'elementor/frontend/before_enqueue_scripts', [ $this, 'enqueue_scripts' ], 998 );
	}

	/**
	 * Plugin-> Sky_Addons constructor.
	 */
	private function __construct() {
		spl_autoload_register( [ $this, 'autoload' ] );
	}
}

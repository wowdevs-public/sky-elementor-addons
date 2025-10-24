<?php

namespace Sky_Addons\Admin;

use Elementor\Modules\Usage\Module;
use Elementor\Tracker;

defined( 'ABSPATH' ) || exit;

/**
 * The Admin class
 */
class Sky_Addons_Admin {

	const WIDGETS_DB_KEY = 'sky_addons_inactive_widgets';
	const WIDGETS_3RD_PARTY_DB_KEY = 'sky_addons_inactive_3rd_party_widgets';
	const EXTENSIONS_DB_KEY = 'sky_addons_inactive_extensions';
	const API_DB_KEY = 'sky_addons_api';

	public static $widget_list = null;
	public static $widgets_name = null;

	private function __construct() {
		$this->dispatch_actions();
		// add_action( 'wp_ajax_sky_black_friday_notice_dismiss', [ $this, 'sky_black_friday_notice_dismiss' ] );
	}

	public function dispatch_actions() {

		// add_action('sky_addons_license_manager', 'sky_addons_license_content');
		// admin js
		add_action( 'admin_enqueue_scripts', [ $this, 'load_admin_scripts' ] );

		add_filter( 'plugin_action_links_' . plugin_basename( SKY_ADDONS__FILE__ ), [ $this, 'add_action_links' ] );

		if ( class_exists( 'Tracker' ) && ! Tracker::is_allow_track() ) {
			// add_action( 'sky_allow_tracker_notice', [ $this, 'allow_tracker_notice' ], 10, 3 );
		}

		// add_action( 'admin_notices', [ $this, 'black_friday_notice' ] );
	}

	/**
	 * Notice for 70% Black Friday & Cyber Monday Deal
	 * Link: https://skyaddons.com/pricing/
	 * Notice will be not show after 10 Dec 2024
	 *
	 * Success notice
	 * Transient for 3 days
	 */
	public function black_friday_notice() {
		$black_friday_date = strtotime( '2024-12-10' );
		$today = strtotime( gmdate( 'Y-m-d' ) );

		// Check if the transient is set, and display the notice
		$transitent = get_transient( 'sky_black_friday_notice' );
		if ( $transitent ) {
			return;
		}

		if ( $today < $black_friday_date ) {
			?>
			<div class="notice notice-success sky_black_friday_notice is-dismissible">
				<p><?php echo esc_html__( 'Get 70% OFF on Sky Addons Pro. Limited Time Offer! ', 'sky-elementor-addons' ); ?><a href="https://skyaddons.com/pricing/?coupon=BFCY2024" target="_blank"><?php echo esc_html__( 'Get Pro', 'sky-elementor-addons' ); ?></a></p>
			</div>
			<?php
		}
	}

	/**
	 * Dismiss Black Friday Notice
	 */
	public function sky_black_friday_notice_dismiss() {
		set_transient( 'sky_black_friday_notice', true, 3 * DAY_IN_SECONDS );
	}

	public function allow_tracker_notice() {
		?>
		<div class="sa-allow-tracker sa-d-flex sa-align-items-center sa-p-3 sa-mb-2 sa-border sa-rounded">
			<?php
			echo wp_kses_post( __( '<strong>Widgets Analytics not working. </strong> Please activate Data Sharing features to make it workable from here - <strong> Elementor > Settings > General > Usage Data Sharing</strong>', 'sky-elementor-addons' ) );
			?>
		</div>
		<?php
	}

	public function load_admin_scripts() {
		wp_enqueue_script( 'sky-admin-js', SKY_ADDONS_ASSETS_URL . 'admin/sky-admin.js', [
			'jquery',
		], SKY_ADDONS_VERSION, true );

		$direction_suffix = is_rtl() ? '.rtl' : '';

		// wp_enqueue_style( 'sky-admin-css', SKY_ADDONS_ASSETS_URL . 'admin/sky-admin' . $direction_suffix . '.css', [], SKY_ADDONS_VERSION );
		wp_enqueue_style( 'sky-widget-icons', SKY_ADDONS_ASSETS_URL . 'css/sky-widget-icons' . $direction_suffix . '.css', [], SKY_ADDONS_VERSION );
	}

	public static function modules_demo_server() {
		return 'https://skyaddons.com/';
	}

	public static function get_inactive_widgets() {
		return get_option( self::WIDGETS_DB_KEY, [] );
	}

	public static function get_inactive_3rd_party_widgets() {
		return get_option( self::WIDGETS_3RD_PARTY_DB_KEY, [] );
	}

	public static function get_inactive_extensions() {
		return get_option( self::EXTENSIONS_DB_KEY, [] );
	}

	public static function get_saved_api() {
		return get_option( self::API_DB_KEY, [] );
	}

	public function admin_menu() {
		$parent_slug = 'sky-elementor-addons';
		$capability = 'manage_options';

		add_menu_page( esc_html__( 'Sky Addons', 'sky-elementor-addons' ), esc_html__( 'Sky Addons', 'sky-elementor-addons' ), $capability, $parent_slug, [
			$this,
			'admin_settings',
		], SKY_ADDONS_ASSETS_URL . 'images/sky-top-menu-logo.svg', 59 );

		add_submenu_page( $parent_slug, esc_html__( 'Dashboard', 'sky-elementor-addons' ), esc_html__( 'Dashboard', 'sky-elementor-addons' ), $capability, $parent_slug, [
			$this,
			'admin_settings',
		] );

		add_submenu_page( $parent_slug, esc_html__( 'Widgets', 'sky-elementor-addons' ), esc_html__( 'Widgets', 'sky-elementor-addons' ), $capability, $parent_slug . '#widgets', [
			$this,
			'admin_settings',
		] );

		add_submenu_page( $parent_slug, esc_html__( 'Extensions', 'sky-elementor-addons' ), esc_html__( 'Extensions', 'sky-elementor-addons' ), $capability, $parent_slug . '#extensions', [
			$this,
			'admin_settings',
		] );

		add_submenu_page( $parent_slug, esc_html__( 'API Data', 'sky-elementor-addons' ), esc_html__( 'API Data', 'sky-elementor-addons' ), $capability, $parent_slug . '#api', [
			$this,
			'admin_settings',
		] );

		add_submenu_page( $parent_slug, esc_html__( 'Analytics', 'sky-elementor-addons' ), esc_html__( 'Analytics', 'sky-elementor-addons' ), $capability, $parent_slug . '#analytics-used-widgets', [
			$this,
			'admin_settings',
		] );

		add_submenu_page( $parent_slug, esc_html__( 'Get PRO', 'sky-elementor-addons' ), esc_html__( 'Get PRO', 'sky-elementor-addons' ), $capability, $parent_slug . '#pro', [
			$this,
			'admin_settings',
		] );
	}

	public static function add_action_links( $links ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return $links;
		}

		$links = array_merge( [
			sprintf(
				'<a href="%s">%s</a>',
				sky_addons_dashboard_link(),
				esc_html__( 'Settings', 'sky-elementor-addons' )
			),
		], $links );
		if ( sky_addons_init_pro() !== true ) {
			$links = array_merge( $links, [
				sprintf(
					'<a target="_blank" style="color:#E0528D; font-weight: bold;" href="%s" title="%s">%s</a>',
					'https://skyaddons.com/pricing/?coupon=SKYADDONS30',
					esc_html__( 'Get 30% OFF!', 'sky-elementor-addons' ),
					esc_html__( 'Get Pro', 'sky-elementor-addons' )
				),
			] );
		}
		return $links;
	}

	public function admin_settings() {
		// if ( is_readable( sky_addons_core()->templates_dir . 'admin/dashboard.php' ) ) {
		// require_once sky_addons_core()->templates_dir . 'admin/dashboard.php';
		// }
	}

	/**
	 * Get Used modules.
	 *
	 * @access public
	 * @return array
	 * @since 1.0.6
	 */
	public static function get_used_widgets() {

		$used_widgets = [];

		if ( class_exists( 'Elementor\Modules\Usage\Module' ) ) {

			$module = Module::instance();
			$elements = $module->get_formatted_usage( 'raw' );
			$widgets = self::get_widgets_names();

			if ( is_array( $elements ) || is_object( $elements ) ) {

				foreach ( $elements as $post_type => $data ) {
					foreach ( $data['elements'] as $element => $count ) {
						if ( in_array( $element, $widgets, true ) ) {
							if ( isset( $used_widgets[ $element ] ) ) {
								$used_widgets[ $element ] += $count;
							} else {
								$used_widgets[ $element ] = $count;
							}
						}
					}
				}
			}
		}

		return $used_widgets;
	}

	/**
	 * Get Unused Widgets.
	 *
	 * @access public
	 * @return array
	 * @since 1.0.6
	 */
	public static function get_unused_widgets() {

		if ( ! current_user_can( 'install_plugins' ) ) {
			die();
		}

		$widgets = self::get_widgets_names();

		$used_widgets = self::get_used_widgets();

		$unused_widgets = array_diff( $widgets, array_keys( $used_widgets ) );

		return $unused_widgets;
	}

	/**
	 * Get Widgets Name
	 *
	 * @access public
	 * @return array
	 * @since 1.0.6
	 */
	public static function get_widgets_names() {
		$names = self::$widgets_name;

		if ( null === $names ) {
			$names = array_map(
				function ( $item ) {
					return isset( $item['name'] ) ? 'sky-' . str_replace( '_', '-', $item['name'] ) : 'none';
				},
				self::$widget_list
			);
		}

		return $names;
	}

	/**
	 * Elements List
	 */
	public static function get_element_list() {

		$inactive_widgets = self::get_inactive_widgets();
		$inactive_3rd_party_widgets = self::get_inactive_3rd_party_widgets();
		$inactive_extensions = self::get_inactive_extensions();
		$saved_api = self::get_saved_api();

		$widgets_fields = [
			'sky_addons_widgets' => [
				[
					'name'         => 'advanced-accordion',
					'label'        => esc_html__( 'Advanced Accordion', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'advanced-accordion', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/advanced-accordion-widget/',
				],
				[
					'name'         => 'advanced-counter',
					'label'        => esc_html__( 'Advanced Counter', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'advanced-counter', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/advanced-counter-widget/',
				],
				[
					'name'         => 'advanced-skill-bars',
					'label'        => esc_html__( 'Advanced Skill Bars', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'advanced-skill-bars', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/advanced-skill-bars-widget/',
				],
				[
					'name'         => 'advanced-slider',
					'label'        => esc_html__( 'Advanced Slider', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'advanced-slider', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/advanced-slider-widget/',
				],
				[
					'name'         => 'animated-heading',
					'label'        => esc_html__( 'Animated Heading', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'animated-heading', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/animated-heading-widget/',
				],
				[
					'name'         => 'audio-player',
					'label'        => esc_html__( 'Audio Player', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'audio-player', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/audio-player-widget/',
				],
				[
					'name'         => 'breadcrumbs',
					'label'        => esc_html__( 'Breadcrumbs', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'breadcrumbs', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/breadcrumbs-widget/',
				],
				[
					'name'         => 'card',
					'label'        => esc_html__( 'Card', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'card', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => 'https://youtu.be/Ib9jDrC2caQ',
					'content_type' => 'custom',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/card-widget/',
				],
				[
					'name'         => 'changelog',
					'label'        => esc_html__( 'Changelog', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'changelog', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/changelog-widget/',
				],
				[
					'name'         => 'content-switcher',
					'label'        => esc_html__( 'Content Switcher', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'content-switcher', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/content-switcher-widget/',
				],
				[
					'name'         => 'dark-mode',
					'label'        => esc_html__( 'Dark Mode', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'dark-mode', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/dark-mode-widget/',
				],
				[
					'name'         => 'diamond-gallery',
					'label'        => esc_html__( 'Diamond Gallery', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'diamond-gallery', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom new',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/diamond-gallery-widget/',
				],
				[
					'name'         => 'data-table',
					'label'        => esc_html__( 'Data Table', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'data-table', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/data-table-widget/',
				],
				[
					'name'         => 'dual-button',
					'label'        => esc_html__( 'Dual Button', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'dual-button', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/dual-button-widget/',
				],
				[
					'name'         => 'fellow-slider',
					'label'        => esc_html__( 'Fellow Slider', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'fellow-slider', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'post',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/fellow-slider-widget/',
				],
				[
					'name'         => 'flow-slider',
					'label'        => esc_html__( 'Flow Slider', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'flow-slider', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/flow-slider-widget/',
				],
				[
					'name'         => 'form-builder',
					'label'        => esc_html__( 'Form Builder', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'form-builder', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/form-builder-widget/',
				],
				[
					'name'         => 'fullpage-menu',
					'label'        => esc_html__( 'Full Page Menu', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'fullpage-menu', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom new',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/full-page-menu-widget/',
				],
				[
					'name'         => 'hover-video',
					'label'        => esc_html__( 'Hover Video', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'hover-video', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/hover-video-widget/',
				],
				[
					'name'         => 'generic-grid',
					'label'        => esc_html__( 'Generic Grid', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'generic-grid', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'post',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/generic-grid-widget/',
				],
				[
					'name'         => 'generic-carousel',
					'label'        => esc_html__( 'Generic Carousel', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'generic-carousel', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'post',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/generic-carousel-widget/',
				],
				[
					'name'         => 'glory-slider',
					'label'        => esc_html__( 'Glory Slider', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'glory-slider', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/glory-slider-widget/',
				],
				[
					'name'         => 'iframe',
					'label'        => esc_html__( 'Iframe', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'iframe', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/iframe-widget/',
				],
				[
					'name'         => 'image-accordion',
					'label'        => esc_html__( 'Image Accordion', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'image-accordion', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom new',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/image-accordion-widget/',
				],
				[
					'name'         => 'nav-menu',
					'label'        => esc_html__( 'Nav Menu', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'nav-menu', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom new',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/nav-menu-widget/',
				],
				[
					'name'         => 'news-ticker',
					'label'        => esc_html__( 'News Ticker', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'news-ticker', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom new',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/news-ticker-widget/',
				],
				[
					'name'         => 'off-canvas-menu',
					'label'        => esc_html__( 'Off Canvas Menu', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'off-canvas-menu', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/off-canvas-menu-widget/',
				],
				[
					'name'         => 'image-compare',
					'label'        => esc_html__( 'Image Compare', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'image-compare', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/image-compare-widget/',
				],
				[
					'name'         => 'info-box',
					'label'        => esc_html__( 'Info Box', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'info-box', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/info-box-widget/',
				],
				[
					'name'         => 'list-group',
					'label'        => esc_html__( 'List Group', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'list-group', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/list-group-widget/',
				],
				[
					'name'         => 'logo-carousel',
					'label'        => esc_html__( 'Logo Carousel', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'logo-carousel', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/logo-carousel-widget/',
				],
				[
					'name'         => 'logo-grid',
					'label'        => esc_html__( 'Logo Grid', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'logo-grid', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/logo-grid-widget/',
				],
				[
					'name'         => 'luster-grid',
					'label'        => esc_html__( 'Luster Grid', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'luster-grid', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'post',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/luster-grid-widget/',
				],
				[
					'name'         => 'luster-carousel',
					'label'        => esc_html__( 'Luster Carousel', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'luster-carousel', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'post',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/luster-carousel-widget/',
				],
				[
					'name'         => 'mate-list',
					'label'        => esc_html__( 'Mate List', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'mate-list', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'post',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/mate-list-widget/',
				],
				[
					'name'         => 'mate-slider',
					'label'        => esc_html__( 'Mate Slider', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'mate-slider', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'post',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/mate-slider-widget/',
				],
				[
					'name'         => 'mate-carousel',
					'label'        => esc_html__( 'Mate Carousel', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'mate-carousel', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'post',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/mate-carousel-widget/',
				],
				[
					'name'         => 'momentum-slider',
					'label'        => esc_html__( 'Momentum Slider', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'momentum-slider', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/momentum-slider-widget/',
				],
				[
					'name'         => 'naive-list',
					'label'        => esc_html__( 'Naive List', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'naive-list', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'post',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/naive-list-widget/',
				],
				[
					'name'         => 'naive-carousel',
					'label'        => esc_html__( 'Naive Carousel', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'naive-carousel', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'post',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/naive-carousel-widget/',
				],
				[
					'name'         => 'number',
					'label'        => esc_html__( 'Number', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'number', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/number-widget/',
				],
				[
					'name'         => 'offcanvas',
					'label'        => esc_html__( 'Off-Canvas', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'offcanvas', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom new',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/offcanvas-widget/',
				],
				[
					'name'         => 'offcanvas-menu',
					'label'        => esc_html__( 'Off-Canvas Menu', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'offcanvas-menu', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom new',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/offcanvas-menu-widget/',
				],
				[
					'name'         => 'pace-slider',
					'label'        => esc_html__( 'Pace Slider', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'pace-slider', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/pace-slider-widget/',
				],
				[
					'name'         => 'panel-slider',
					'label'        => esc_html__( 'Panel Slider', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'panel-slider', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/panel-slider-widget/',
				],
				[
					'name'         => 'pdf-viewer',
					'label'        => esc_html__( 'PDF Viewer', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'pdf-viewer', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/pdf-viewer-widget/',
				],
				[
					'name'         => 'post-list',
					'label'        => esc_html__( 'Post List', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'post-list', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'post',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/post-list-widget/',
				],
				[
					'name'         => 'portion-effect',
					'label'        => esc_html__( 'Portion Effect', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'portion-effect', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/portion-effect-widget/',
				],
				[
					'name'         => 'pricing-table',
					'label'        => esc_html__( 'Pricing Table', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'pricing-table', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/pricing-table-widget/',
				],
				[
					'name'         => 'qr-code',
					'label'        => esc_html__( 'QR Code', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'qr-code', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/qr-code-widget/',
				],
				[
					'name'         => 'reading-progress',
					'label'        => esc_html__( 'Reading Progress', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'reading-progress', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/reading-progress-widget/',
				],
				[
					'name'         => 'review',
					'label'        => esc_html__( 'Review', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'review', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/review-widget/',
				],
				[
					'name'         => 'review-carousel',
					'label'        => esc_html__( 'Review Carousel', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'review-carousel', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/review-carousel-widget/',
				],
				[
					'name'         => 'remote-arrows',
					'label'        => esc_html__( 'Remote Arrows', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'remote-arrows', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/remote-arrows-widget/',
				],
				[
					'name'         => 'remote-pagination',
					'label'        => esc_html__( 'Remote Pagination', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'remote-pagination', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/remote-pagination-widget/',
				],
				[
					'name'         => 'remote-thumbs',
					'label'        => esc_html__( 'Remote Thumbs', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'remote-thumbs', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/remote-thumbs-widget/',
				],
				[
					'name'         => 'rounded-cursor',
					'label'        => esc_html__( 'Rounded Cursor', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'rounded-cursor', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom new',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/rounded-cursor-widget/',
				],
				[
					'name'         => 'reveal-gallery',
					'label'        => esc_html__( 'Reveal Gallery', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'reveal-gallery', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom new',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/reveal-gallery-widget/',
				],
				[
					'name'         => 'scrolling-gallery',
					'label'        => esc_html__( 'Scrolling Gallery', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'scrolling-gallery', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom new',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/scrolling-gallery-widget/',
				],
				[
					'name'         => 'sapling-grid',
					'label'        => esc_html__( 'Sapling Grid', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'sapling-grid', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'post',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/sapling-grid-widget/',
				],
				[
					'name'         => 'sapling-carousel',
					'label'        => esc_html__( 'Sapling Carousel', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'sapling-carousel', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'post',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/sapling-carousel-widget/',
				],
				[
					'name'         => 'slinky-menu',
					'label'        => esc_html__( 'Slinky Menu (Vertical)', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'slinky-menu', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/slinky-menu-widget/',
				],
				[
					'name'         => 'social-icons',
					'label'        => esc_html__( 'Social Icons', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'social-icons', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/social-icons-widget/',
				],
				[
					'name'         => 'social-share',
					'label'        => esc_html__( 'Social Share', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'social-share', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom new',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/social-share-widget/',
				],
				[
					'name'         => 'stellar-slider',
					'label'        => esc_html__( 'Stellar Blog Slider', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'stellar-slider', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/stellar-slider-widget/',
				],
				[
					'name'         => 'step-flow',
					'label'        => esc_html__( 'Step Flow', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'step-flow', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/step-flow-widget/',
				],
				[
					'name'         => 'table-of-contents',
					'label'        => esc_html__( 'Table of Contents', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'table-of-contents', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/table-of-content-widget/',
				],
				[
					'name'         => 'team-member',
					'label'        => esc_html__( 'Team Member', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'team-member', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/team-member-widget/',
				],
				[
					'name'         => 'testimonial',
					'label'        => esc_html__( 'Testimonial', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'testimonial', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/testimonial-widget/',
				],
				[
					'name'         => 'testimonial-carousel',
					'label'        => esc_html__( 'Testimonial Carousel', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'testimonial-carousel', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/testimonial-carousel-widget/',
				],
				[
					'name'         => 'tidy-list',
					'label'        => esc_html__( 'Tidy List', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'tidy-list', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/tidy-list-widget/',
				],
				[
					'name'         => 'ultra-grid',
					'label'        => esc_html__( 'Ultra Grid', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'ultra-grid', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'post',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/ultra-grid-widget/',
				],
				[
					'name'         => 'ultra-carousel',
					'label'        => esc_html__( 'Ultra Carousel', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'ultra-carousel', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'post',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/ultra-carousel-widget/',
				],
				[
					'name'         => 'tags-cloud',
					'label'        => esc_html__( 'Tags Cloud', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'tags-cloud', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/tags-cloud-widget/',
				],
				[
					'name'         => 'vertical-menu',
					'label'        => esc_html__( 'Vertical Menu', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'vertical-menu', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom new',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/vertical-menu-widget/',
				],
				[
					'name'         => 'video-gallery',
					'label'        => esc_html__( 'Video Gallery', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'video-gallery', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/video-gallery-widget/',
				],
				[
					'name'         => 'bar-chart',
					'label'        => esc_html__( 'Bar Chart', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'bar-chart', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/bar-chart-widget/',
				],
				[
					'name'         => 'line-chart',
					'label'        => esc_html__( 'Line Chart', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'line-chart', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/line-chart-widget/',
				],
				[
					'name'         => 'polar-chart',
					'label'        => esc_html__( 'Polar Area Chart', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'polar-chart', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/polar-chart-widget/',
				],
				[
					'name'         => 'pie-chart',
					'label'        => esc_html__( 'Pie & Doughnut Chart', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'pie-chart', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/pie-and-doughnut-chart-widget/',
				],
				[
					'name'         => 'radar-chart',
					'label'        => esc_html__( 'Radar Chart', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'radar-chart', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/radar-chart-widget/',
				],
				[
					'name'         => 'loop-grid',
					'label'        => esc_html__( 'Loop Grid', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'loop-grid', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom new',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/loop-grid-widget/',
				],
				[
					'name'         => 'loop-carousel',
					'label'        => esc_html__( 'Loop Carousel', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'loop-carousel', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom new',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/loop-carousel-widget/',
				],
			],
			'sky_addons_3rd_party_widget' => [
				[
					'name'         => 'wc-category',
					'label'        => esc_html__( 'WooCommerce Category Grid', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'wc-category', $inactive_3rd_party_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/woocommerce-category/',
				],
				[
					'name'         => 'wc-category-carousel',
					'label'        => esc_html__( 'WooCommerce Category Carousel', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'wc-category-carousel', $inactive_3rd_party_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/woocommerce-category-carousel/',
				],
				[
					'name'         => 'wc-products',
					'label'        => esc_html__( 'WooCommerce Products', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'wc-products', $inactive_3rd_party_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/woocommerce-products/',
				],
				[
					'name'         => 'wc-products-carousel',
					'label'        => esc_html__( 'WooCommerce Products Carousel', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'wc-products-carousel', $inactive_3rd_party_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/woocommerce-products-carousel/',
				],
				[
					'name'         => 'edd-grid',
					'label'        => esc_html__( 'EDD Product Grid', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'edd-grid', $inactive_3rd_party_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom new',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/woocommerce-products-carousel/',
				],
				[
					'name'         => 'edd-carousel',
					'label'        => esc_html__( 'EDD Product Carousel', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'edd-carousel', $inactive_3rd_party_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom new',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/woocommerce-products-carousel/',
				],
				[
					'name'         => 'edd-category-grid',
					'label'        => esc_html__( 'EDD Category Grid', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'edd-category-grid', $inactive_3rd_party_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom new',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/woocommerce-products-carousel/',
				],
				[
					'name'         => 'edd-category-carousel',
					'label'        => esc_html__( 'EDD Category Carousel', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'edd-category-carousel', $inactive_3rd_party_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom new',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/woocommerce-products-carousel/',
				],
				[
					'name'         => 'cf7',
					'label'        => esc_html__( 'Contact Form 7', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'cf7', $inactive_3rd_party_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'new',
					'feature_type' => 'free',
					'demo_url'     => '#',
				],
				[
					'name'         => 'fluent-form',
					'label'        => esc_html__( 'Fluent Form', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'fluent-form', $inactive_3rd_party_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'new',
					'feature_type' => 'free',
					'demo_url'     => '#',
				],
				[
					'name'         => 'gravity-forms',
					'label'        => esc_html__( 'Gravity Forms', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'gravity-forms', $inactive_3rd_party_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'new',
					'feature_type' => 'free',
					'demo_url'     => '#',
				],
				[
					'name'         => 'ninja-forms',
					'label'        => esc_html__( 'Ninja Forms', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'ninja-forms', $inactive_3rd_party_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'new',
					'feature_type' => 'free',
					'demo_url'     => '#',
				],
				[
					'name'         => 'we-forms',
					'label'        => esc_html__( 'weForms', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'we-forms', $inactive_3rd_party_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'new',
					'feature_type' => 'free',
					'demo_url'     => '#',
				],
				[
					'name'         => 'wp-forms',
					'label'        => esc_html__( 'WP Forms', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'wp-forms', $inactive_3rd_party_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'new',
					'feature_type' => 'free',
					'demo_url'     => '#',
				],
			],
			'sky_addons_extensions' => [
				[
					'name'         => 'advanced-tooltip',
					'label'        => esc_html__( 'Advanced Tooltip', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'advanced-tooltip', $inactive_extensions ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/advanced-tooltip-extensions/',
				],
				[
					'name'         => 'animated-gradient-bg',
					'label'        => esc_html__( 'Animated Gradient Background', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'animated-gradient-bg', $inactive_extensions ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/animated-gradient-background-extensions/',
				],
				[
					'name'         => 'backdrop-filter',
					'label'        => esc_html__( 'Backdrop Filter', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'backdrop-filter', $inactive_extensions ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/backdrop-filter-extensions/',
				],
				[
					'name'         => 'confetti-effects',
					'label'        => esc_html__( 'Confetti Effects', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'confetti-effects', $inactive_extensions ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/confetti-effects-extensions/',
				],
				[
					'name'         => 'custom-clip-path',
					'label'        => esc_html__( 'Custom Clip Path', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'custom-clip-path', $inactive_extensions ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/custom-clip-path-extensions/',
				],
				[
					'name'         => 'custom-scripts',
					'label'        => esc_html__( 'Custom Scripts', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'custom-scripts', $inactive_extensions ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/custom-scripts-extensions/',
				],
				[
					'name'         => 'duplicator',
					'label'        => esc_html__( 'Duplicator', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'duplicator', $inactive_extensions ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/duplicator-extensions/',
				],
				[
					'name'         => 'equal-height',
					'label'        => esc_html__( 'Equal Height', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'equal-height', $inactive_extensions ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/equal-height-extensions/',
				],
				[
					'name'         => 'floating-effects',
					'label'        => esc_html__( 'Floating Effects', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'floating-effects', $inactive_extensions ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/floating-effects-extensions/',
				],
				[
					'name'         => 'gradient-text',
					'label'        => esc_html__( 'Gradient Text', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'gradient-text', $inactive_extensions ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/gradient-text-extensions/',
				],
				[
					'name'         => 'particles',
					'label'        => esc_html__( 'Particles', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'particles', $inactive_extensions ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/particle-effects-extensions/',
				],
				[
					'name'         => 'reveal-effects',
					'label'        => esc_html__( 'Reveal Effects', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'reveal-effects', $inactive_extensions ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/reveal-effects-extensions/',
				],
				[
					'name'         => 'ripples-effect',
					'label'        => esc_html__( 'Ripples Effect', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'ripples-effect', $inactive_extensions ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/ripples-effect-extensions/',
				],
				[
					'name'         => 'simple-parallax',
					'label'        => esc_html__( 'Simple Parallax', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'simple-parallax', $inactive_extensions ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/parallax-effects-extensions/',
				],
				[
					'name'         => 'svg-support',
					'label'        => esc_html__( 'SVG Support', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'svg-support', $inactive_extensions ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/svg-support-extensions/',
				],
				[
					'name'         => 'smooth-scroll',
					'label'        => esc_html__( 'Smooth Scroll', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'smooth-scroll', $inactive_extensions ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/smooth-scroll-extensions/',
				],
				[
					'name'         => 'display-conditions',
					'label'        => esc_html__( 'Display Conditions', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'display-conditions', $inactive_extensions ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/display-conditions-extensions/',
				],
				[
					'name'         => 'wrapper-link',
					'label'        => esc_html__( 'Wrapper Link', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'wrapper-link', $inactive_extensions ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/wrapper-link-extensions/',
				],
			],
			'sky_addons_api' => [
				'form_builder_group' => [
					'input_box'    => [
						[
							'name'        => 'form_builder_email_to',
							'label'       => esc_html__( 'Form Builder Emails Receiver', 'sky-elementor-addons' ),
							'placeholder' => esc_html__( 'Email Address', 'sky-elementor-addons' ),
							'description' => esc_html__( 'By default, the form builder sends emails to the admin email. If you\'d like to send emails to a different address, you can configure it here.', 'sky-elementor-addons' ),
							'type'        => 'input',
							'value'       => ! empty( $saved_api['form_builder_email_to'] ) ? $saved_api['form_builder_email_to'] : null,
						],
					],
					'feature_type' => 'pro',
				],
				'sky_addons_api_google_map_group' => [
					'input_box'    => [
						[
							'name'        => 'google_map_key',
							'label'       => esc_html__( 'Google Map', 'sky-elementor-addons' ),
							'placeholder' => esc_html__( 'API Key', 'sky-elementor-addons' ),
							'description' => esc_html__( 'Google Maps API is a service that offers detailed maps and other geographic information for use in online and offline map applications, and websites.', 'sky-elementor-addons' ),
							'type'        => 'input',
							'value'       => ! empty( $saved_api['google_map_key'] ) ? $saved_api['google_map_key'] : null,
						],
					],
					'feature_type' => 'pro',
				],
				'sky_addons_api_mailchimp_group' => [
					'input_box'    => [
						[
							'name'        => 'mailchimp_api_key',
							'label'       => esc_html__( 'Mailchimp API Key', 'sky-elementor-addons' ),
							'placeholder' => esc_html__( 'Access Key', 'sky-elementor-addons' ),
							'description' => esc_html__( 'Mailchimp is a popular marketing and automation platform for small businesses.', 'sky-elementor-addons' ),
							'type'        => 'input',
							'value'       => ! empty( $saved_api['mailchimp_api_key'] ) ? $saved_api['mailchimp_api_key'] : null,
						],
						[
							'name'        => 'mailchimp_list_id',
							'label'       => esc_html__( 'Audience ID', 'sky-elementor-addons' ),
							'placeholder' => esc_html__( 'Audience ID', 'sky-elementor-addons' ),
							'description' => esc_html__( 'Each Mailchimp audience has a unique audience ID (sometimes called a list ID) .', 'sky-elementor-addons' ),
							'type'        => 'input',
							'value'       => ! empty( $saved_api['mailchimp_list_id'] ) ? $saved_api['mailchimp_list_id'] : null,
						],
					],
					'feature_type' => 'pro',
				],
				'sky_addons_api_instagram_group' => [
					'input_box'    => [
						[

							'name'        => 'instagram_app_id',
							'label'       => esc_html__( 'Instagram', 'sky-elementor-addons' ),
							'placeholder' => esc_html__( 'App Id', 'sky-elementor-addons' ),
							'description' => '',
							'type'        => 'input',
							'value'       => ! empty( $saved_api['instagram_app_id'] ) ? $saved_api['instagram_app_id'] : null,
						],
						[

							'name'        => 'instagram_app_secret',
							'label'       => esc_html__( 'App Secret', 'sky-elementor-addons' ),
							'placeholder' => esc_html__( 'App Secret', 'sky-elementor-addons' ),
							'description' => '',
							'type'        => 'input',
							'value'       => ! empty( $saved_api['instagram_app_secret'] ) ? $saved_api['instagram_app_secret'] : null,
						],
						[

							'name'        => 'instagram_access_token',
							'label'       => esc_html__( 'Access Token', 'sky-elementor-addons' ),
							'placeholder' => esc_html__( 'Access Token', 'sky-elementor-addons' ),
							'description' => '',
							'type'        => 'input',
							'value'       => ! empty( $saved_api['instagram_access_token'] ) ? $saved_api['instagram_access_token'] : null,
						],
					],
					'feature_type' => 'pro',
				],
			],
		];

		self::$widget_list = $widgets_fields['sky_addons_widgets'];
		self::$widget_list = array_merge( self::$widget_list, $widgets_fields['sky_addons_3rd_party_widget'] );

		$used_widgets = self::get_used_widgets();
		$widgets_fields['sky_addons_widgets'] = array_map(function( $widget ) use ( $used_widgets ) {
			$widget_name = $widget['name'];
			$widget['total_used'] = isset( $used_widgets[ 'sky-' . $widget_name ] ) ? $used_widgets[ 'sky-' . $widget_name ] : 0;
			return $widget;
		}, $widgets_fields['sky_addons_widgets']);

		$widgets_fields['sky_addons_3rd_party_widget'] = array_map(
			function ( $widget ) use ( $used_widgets ) {
				$widget_name          = $widget['name'];
				$widget['total_used'] = isset( $used_widgets[ 'sky-' . $widget_name ] ) ? $used_widgets[ 'sky-' . $widget_name ] : 0;
				return $widget;
			},
			$widgets_fields['sky_addons_3rd_party_widget']
		);

		return $widgets_fields;
	}

	public static function init() {
		static $instance = false;

		if ( ! $instance ) {
			$instance = new self();
		}

		return $instance;
	}
}

function sky_addons_admin() {
	return Sky_Addons_Admin::init();
}

// kick-off the admin class
sky_addons_admin();

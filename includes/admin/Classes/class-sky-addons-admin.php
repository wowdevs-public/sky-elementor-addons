<?php

namespace Sky_Addons\Admin;

use Elementor\Modules\Usage\Module;
use Elementor\Tracker;

defined( 'ABSPATH' ) || exit;

/**
 * The Admin class
 */
class Sky_Addons_Admin {

	const WIDGETS_DB_KEY           = 'sky_addons_inactive_widgets';
	const WIDGETS_3RD_PARTY_DB_KEY = 'sky_addons_inactive_3rd_party_widgets';
	const EXTENSIONS_DB_KEY        = 'sky_addons_inactive_extensions';
	const API_DB_KEY               = 'sky_addons_api';
	const ADVANCED_DB_KEY          = 'sky_addons_advanced_settings';

	public static $widget_list  = null;
	public static $widgets_name = null;

	private function __construct() {
		$this->dispatch_actions();
		// add_action( 'wp_ajax_sky_black_friday_notice_dismiss', [ $this, 'sky_black_friday_notice_dismiss' ] );
	}

	public function dispatch_actions() {

		// add_action('sky_addons_license_manager', 'sky_addons_license_content');
		// admin js
		add_action( 'admin_enqueue_scripts', [ $this, 'load_admin_scripts' ] );

		// Keep the saved Instagram token fresh (refreshes any long-lived token, however it was entered).
		add_action( 'sky_addons_ig_refresh_event', [ $this, 'ig_refresh_token' ] );
		if ( is_admin() && ! wp_next_scheduled( 'sky_addons_ig_refresh_event' ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'sky_addons_ig_refresh_event' );
		}

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
		$today             = strtotime( gmdate( 'Y-m-d' ) );

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
		wp_register_script( 'sky-admin-js', SKY_ADDONS_ASSETS_URL . 'admin/sky-admin.js', [
			'jquery',
		], SKY_ADDONS_VERSION, true );

		wp_enqueue_script( 'sky-admin-js' );

		$direction_suffix = is_rtl() ? '.rtl' : '';

		wp_register_style( 'sky-addons-widget-icons', SKY_ADDONS_ASSETS_URL . 'css/sky-widget-icons' . $direction_suffix . '.css', [], SKY_ADDONS_VERSION );
		wp_enqueue_style( 'sky-addons-widget-icons' );
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

	/** Daily cron: refresh the long-lived token when it's within 10 days of expiry. */
	public function ig_refresh_token() {
		$api   = self::get_saved_api();
		$token = is_array( $api ) && ! empty( $api['instagram_access_token'] ) ? $api['instagram_access_token'] : '';
		if ( '' === $token ) {
			return;
		}

		$expires = isset( $api['instagram_token_expires'] ) ? (int) $api['instagram_token_expires'] : 0;
		// Only refresh tokens that are valid, >24h old conceptually, and near expiry.
		if ( $expires && ( $expires - time() ) > ( 10 * DAY_IN_SECONDS ) ) {
			return;
		}

		$response = wp_remote_get(
			'https://graph.instagram.com/refresh_access_token?' . http_build_query( [
				'grant_type'   => 'ig_refresh_token',
				'access_token' => $token,
			] ),
			[ 'timeout' => 30 ]
		);

		if ( is_wp_error( $response ) ) {
			return;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( empty( $body['access_token'] ) ) {
			return;
		}

		$api['instagram_access_token']  = sanitize_text_field( $body['access_token'] );
		$api['instagram_token_expires'] = time() + ( isset( $body['expires_in'] ) ? (int) $body['expires_in'] : 5184000 );
		update_option( self::API_DB_KEY, $api );
	}

	public static function get_inactive_advanced_features() {
		$settings = get_option( self::ADVANCED_DB_KEY, [] );
		return (array) ( $settings['inactive'] ?? [] );
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

			$module   = Module::instance();
			$elements = $module->get_formatted_usage( 'raw' );
			$widgets  = self::get_widgets_names();

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

		$inactive_widgets           = self::get_inactive_widgets();
		$inactive_3rd_party_widgets = self::get_inactive_3rd_party_widgets();
		$inactive_extensions        = self::get_inactive_extensions();
		$inactive_advanced          = self::get_inactive_advanced_features();
		$saved_api                  = self::get_saved_api();

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
					'category'     => 'content',
					'tags'         => 'accordion faq toggle collapse expand question',
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
					'category'     => 'content',
					'tags'         => 'counter number odometer stats animate count',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/advanced-counter-widget/',
				],
				[
					'name'         => 'advanced-search',
					'label'        => esc_html__( 'Advanced Search', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'advanced-search', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom new',
					'category'     => 'navigation',
					'tags'         => 'search ajax live filter find query',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/advanced-search-widget/',
				],
				[
					'name'         => 'advanced-skill-bars',
					'label'        => esc_html__( 'Advanced Skill Bars', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'advanced-skill-bars', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'category'     => 'content',
					'tags'         => 'skill progress bar meter percentage chart',
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
					'category'     => 'slider',
					'tags'         => 'slider slideshow carousel swiper banner hero',
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
					'category'     => 'content',
					'tags'         => 'heading title typewriter typed rotate text animate',
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
					'category'     => 'media',
					'tags'         => 'audio music mp3 podcast sound player plyr',
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
					'category'     => 'navigation',
					'tags'         => 'breadcrumb trail path navigation seo',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/breadcrumbs-widget/',
				],
				[
					'name'         => 'calculator',
					'label'        => esc_html__( 'Calculator', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'calculator', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom new',
					'category'     => 'content',
					'tags'         => 'calculator form quote estimate price math',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/calculator-widget/',
				],
				[
					'name'         => 'card',
					'label'        => esc_html__( 'Card', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'card', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => 'https://youtu.be/Ib9jDrC2caQ',
					'content_type' => 'custom',
					'category'     => 'content',
					'tags'         => 'card box tile panel content',
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
					'category'     => 'content',
					'tags'         => 'changelog release version history updates log',
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
					'category'     => 'content',
					'tags'         => 'switcher toggle tab monthly yearly pricing switch',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/content-switcher-widget/',
				],
				[
					'name'         => 'circle-hub',
					'label'        => esc_html__( 'Circle Hub', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'circle-hub', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom new',
					'category'     => 'content',
					'tags'         => 'circle hub circle info info circle interactive circle orbit rotate spin carousel infographic feature service process steps showcase',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/circle-hub-widget/',
				],
				[
					'name'         => 'comparison-table',
					'label'        => esc_html__( 'Comparison Table', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'comparison-table', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom new',
					'category'     => 'content',
					'tags'         => 'comparison table compare pricing plan features',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/comparison-table-widget/',
				],
				[
					'name'         => 'diamond-gallery',
					'label'        => esc_html__( 'Diamond Gallery', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'diamond-gallery', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'category'     => 'media',
					'tags'         => 'gallery diamond image grid photos masonry',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/diamond-gallery-widget/',
				],
				[
					'name'         => 'table',
					'label'        => esc_html__( 'Table', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'table', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom new',
					'category'     => 'content',
					'tags'         => 'table data grid rows columns spreadsheet csv',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/table-widget/',
				],
				[
					'name'         => 'dual-button',
					'label'        => esc_html__( 'Dual Button', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'dual-button', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'category'     => 'content',
					'tags'         => 'button dual double cta pair call to action',
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
					'category'     => 'post',
					'tags'         => 'post blog slider article loop query news',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/fellow-slider-widget/',
				],
				[
					'name'         => 'facebook-feed-carousel',
					'label'        => esc_html__( 'Facebook Feed Carousel', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'facebook-feed-carousel', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom new',
					'category'     => 'social',
					'tags'         => 'facebook feed social carousel posts page',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/facebook-feed-carousel-widget/',
				],
				[
					'name'         => 'fancy-testimonial',
					'label'        => esc_html__( 'Fancy Testimonial', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'fancy-testimonial', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'new',
					'category'     => 'slider',
					'tags'         => 'fancy testimonial review rating carousel slider stars feedback avatar wall wave',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/fancy-testimonial-widget/',
				],
				[
					'name'         => 'flow-slider',
					'label'        => esc_html__( 'Flow Slider', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'flow-slider', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'category'     => 'slider',
					'tags'         => 'slider flow carousel slideshow banner',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/flow-slider-widget/',
				],
				[
					'name'         => 'facebook-feed',
					'label'        => esc_html__( 'Facebook Feed', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'facebook-feed', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom new',
					'category'     => 'social',
					'tags'         => 'facebook feed social posts page timeline',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/facebook-feed-widget/',
				],
				[
					'name'         => 'form-builder',
					'label'        => esc_html__( 'Form Builder', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'form-builder', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'category'     => 'forms',
					'tags'         => 'form builder contact input fields submit',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/form-builder-widget/',
				],
				[
					'name'         => 'google-reviews',
					'label'        => esc_html__( 'Google Reviews', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'google-reviews', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom new',
					'category'     => 'social',
					'tags'         => 'google reviews rating testimonial stars business',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/google-reviews-grid-widget/',
				],
				[
					'name'         => 'google-reviews-carousel',
					'label'        => esc_html__( 'Google Reviews Carousel', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'google-reviews-carousel', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom new',
					'category'     => 'social',
					'tags'         => 'google reviews rating carousel testimonial stars',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/google-reviews-carousel-widget/',
				],
				[
					'name'         => 'fullpage-menu',
					'label'        => esc_html__( 'Full Page Menu', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'fullpage-menu', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'category'     => 'navigation',
					'tags'         => 'menu fullscreen fullpage navigation overlay nav',
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
					'category'     => 'media',
					'tags'         => 'video hover preview play mp4 autoplay',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/hover-video-widget/',
				],
				[
					'name'         => 'horizontal-scroll',
					'label'        => esc_html__( 'Horizontal Scroll', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'horizontal-scroll', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom new',
					'category'     => 'interactive',
					'tags'         => 'horizontal scroll sideways panels sections',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/horizontal-scroll-widget/',
				],
				[
					'name'         => 'generic-grid',
					'label'        => esc_html__( 'Generic Grid', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'generic-grid', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'post',
					'category'     => 'post',
					'tags'         => 'post blog grid article loop query news',
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
					'category'     => 'post',
					'tags'         => 'post blog carousel article loop query news',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/generic-carousel-widget/',
				],
				[
					'name'         => 'google-maps',
					'label'        => esc_html__( 'Google Maps', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'google-maps', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom new',
					'category'     => 'interactive',
					'tags'         => 'map google location marker address directions',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/google-maps-widget/',
				],
				[
					'name'         => 'glory-slider',
					'label'        => esc_html__( 'Glory Slider', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'glory-slider', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'category'     => 'slider',
					'tags'         => 'slider glory carousel slideshow banner hero',
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
					'category'     => 'content',
					'tags'         => 'iframe embed external frame url page',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/iframe-widget/',
				],
				[
					'name'         => 'instagram-feed',
					'label'        => esc_html__( 'Instagram Feed', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'instagram-feed', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom new',
					'category'     => 'social',
					'tags'         => 'instagram feed social photos gallery ig',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/instagram-feed-widget/',
				],
				[
					'name'         => 'instagram-feed-carousel',
					'label'        => esc_html__( 'Instagram Feed Carousel', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'instagram-feed-carousel', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom new',
					'category'     => 'social',
					'tags'         => 'instagram feed social carousel photos ig',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/instagram-feed-carousel-widget/',
				],
				[
					'name'         => 'timeline',
					'label'        => esc_html__( 'Interactive Timeline', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'timeline', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'new',
					'category'     => 'content',
					'tags'         => 'timeline history roadmap steps chronology events',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/timeline-widget/',
				],
				[
					'name'         => 'image-accordion',
					'label'        => esc_html__( 'Image Accordion', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'image-accordion', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'category'     => 'media',
					'tags'         => 'image accordion gallery expand hover photos',
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
					'content_type' => 'custom',
					'category'     => 'navigation',
					'tags'         => 'menu nav navigation header links dropdown',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/nav-menu-widget/',
				],
				[
					'name'         => 'image-stack',
					'label'        => esc_html__( 'Image Stack', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'image-stack', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom new',
					'category'     => 'media',
					'tags'         => 'image stack gallery photos pile cards',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/image-stack-widget/',
				],
				[
					'name'         => 'news-ticker',
					'label'        => esc_html__( 'News Ticker', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'news-ticker', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'category'     => 'content',
					'tags'         => 'ticker news marquee scroll headlines breaking',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/news-ticker-widget/',
				],
				[
					'name'         => 'image-compare',
					'label'        => esc_html__( 'Image Compare', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'image-compare', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'category'     => 'media',
					'tags'         => 'image compare before after slider photos',
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
					'category'     => 'content',
					'tags'         => 'info box icon feature service description',
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
					'category'     => 'content',
					'tags'         => 'list group items bullet checklist',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/list-group-widget/',
				],
				[
					'name'         => 'login',
					'label'        => esc_html__( 'Login Form', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'login', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom new',
					'category'     => 'forms',
					'tags'         => 'login form signin user account authentication',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/login-form-widget/',
				],
				[
					'name'         => 'logo-carousel',
					'label'        => esc_html__( 'Logo Carousel', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'logo-carousel', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'category'     => 'slider',
					'tags'         => 'logo carousel brand client partner slider',
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
					'category'     => 'media',
					'tags'         => 'logo grid brand client partner',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/logo-grid-widget/',
				],
				[
					'name'         => 'modal',
					'label'        => esc_html__( 'Modal', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'modal', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom new',
					'category'     => 'content',
					'tags'         => 'modal popup lightbox dialog overlay window',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/modal-widget/',
				],
				[
					'name'         => 'luster-grid',
					'label'        => esc_html__( 'Luster Grid', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'luster-grid', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'post',
					'category'     => 'post',
					'tags'         => 'post blog grid article loop query news',
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
					'category'     => 'post',
					'tags'         => 'post blog carousel article loop query news',
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
					'category'     => 'post',
					'tags'         => 'post blog list article loop query news',
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
					'category'     => 'post',
					'tags'         => 'post blog slider article loop query news',
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
					'category'     => 'post',
					'tags'         => 'post blog carousel article loop query news',
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
					'category'     => 'slider',
					'tags'         => 'slider momentum carousel slideshow drag',
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
					'category'     => 'post',
					'tags'         => 'post blog list article loop query news',
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
					'category'     => 'post',
					'tags'         => 'post blog carousel article loop query news',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/naive-carousel-widget/',
				],
				[
					'name'         => 'offcanvas',
					'label'        => esc_html__( 'Off-Canvas', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'offcanvas', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'category'     => 'navigation',
					'tags'         => 'offcanvas sidebar drawer panel slide',
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
					'content_type' => 'custom',
					'category'     => 'navigation',
					'tags'         => 'offcanvas menu sidebar drawer mobile nav',
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
					'category'     => 'slider',
					'tags'         => 'slider pace carousel slideshow banner',
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
					'category'     => 'slider',
					'tags'         => 'slider panel carousel slideshow accordion',
					'feature_type' => 'free',
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
					'category'     => 'media',
					'tags'         => 'pdf viewer document file embed reader',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/pdf-viewer-widget/',
				],
				[
					'name'         => 'post-comments',
					'label'        => esc_html__( 'Post Comments', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'post-comments', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'post',
					'category'     => 'post',
					'tags'         => 'comments post discussion replies blog',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/post-comments-widget/',
				],
				[
					'name'         => 'post-list',
					'label'        => esc_html__( 'Post List', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'post-list', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'post',
					'category'     => 'post',
					'tags'         => 'post blog list article loop query news',
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
					'category'     => 'interactive',
					'tags'         => 'portion effect reveal hover image split',
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
					'category'     => 'content',
					'tags'         => 'pricing table price plan package subscription',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/pricing-table-widget/',
				],
				[
					'name'         => 'register',
					'label'        => esc_html__( 'Register Form', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'register', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom new',
					'category'     => 'forms',
					'tags'         => 'register form signup user account create',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/registration-form-widget/',
				],
				[
					'name'         => 'qr-code',
					'label'        => esc_html__( 'QR Code', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'qr-code', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'category'     => 'content',
					'tags'         => 'qr code barcode scan link generator',
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
					'category'     => 'navigation',
					'tags'         => 'reading progress bar scroll indicator article',
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
					'category'     => 'content',
					'tags'         => 'review rating testimonial stars feedback',
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
					'category'     => 'slider',
					'tags'         => 'review rating testimonial carousel stars feedback',
					'feature_type' => 'free',
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
					'category'     => 'slider',
					'tags'         => 'arrows navigation carousel slider remote prev next',
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
					'category'     => 'slider',
					'tags'         => 'pagination dots carousel slider remote bullets',
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
					'category'     => 'slider',
					'tags'         => 'thumbnails carousel slider remote thumbs preview',
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
					'content_type' => 'custom',
					'category'     => 'interactive',
					'tags'         => 'cursor mouse pointer custom follow',
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
					'content_type' => 'custom',
					'category'     => 'media',
					'tags'         => 'gallery reveal image photos hover grid',
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
					'content_type' => 'custom',
					'category'     => 'media',
					'tags'         => 'gallery scrolling image photos marquee',
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
					'category'     => 'post',
					'tags'         => 'post blog grid article loop query news',
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
					'category'     => 'post',
					'tags'         => 'post blog carousel article loop query news',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/sapling-carousel-widget/',
				],
				[
					'name'         => 'showcase-flow',
					'label'        => esc_html__( 'Showcase Flow', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'showcase-flow', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'new',
					'category'     => 'slider',
					'tags'         => 'showcase flow slider carousel portfolio',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/showcase-flow-widget/',
				],
				[
					'name'         => 'showcase-wall',
					'label'        => esc_html__( 'Showcase Wall', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'showcase-wall', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'new',
					'category'     => 'media',
					'tags'         => 'showcase wall gallery parallax grid screenshot template marquee tilt',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/showcase-wall-widget/',
				],
				[
					'name'         => 'slinky-menu',
					'label'        => esc_html__( 'Slinky Menu (Vertical)', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'slinky-menu', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'category'     => 'navigation',
					'tags'         => 'menu vertical slinky nav navigation multilevel',
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
					'category'     => 'social',
					'tags'         => 'social icons share links facebook twitter profiles',
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
					'content_type' => 'custom',
					'category'     => 'social',
					'tags'         => 'social share buttons facebook twitter whatsapp',
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
					'category'     => 'post',
					'tags'         => 'post blog slider article loop query news',
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
					'category'     => 'content',
					'tags'         => 'steps flow process timeline how it works',
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
					'category'     => 'navigation',
					'tags'         => 'table of contents toc index headings anchor',
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
					'category'     => 'content',
					'tags'         => 'team member staff person profile employee',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/team-member-widget/',
				],
				[
					'name'         => 'team-member-carousel',
					'label'        => esc_html__( 'Team Member Carousel', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'team-member-carousel', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'category'     => 'slider',
					'tags'         => 'team member staff carousel person profile',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/team-member-carousel-widget/',
				],
				[
					'name'         => 'testimonial',
					'label'        => esc_html__( 'Testimonial', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'testimonial', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'category'     => 'content',
					'tags'         => 'testimonial review quote feedback client',
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
					'category'     => 'slider',
					'tags'         => 'testimonial review carousel quote feedback client',
					'feature_type' => 'free',
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
					'category'     => 'content',
					'tags'         => 'list tidy items icon bullet checklist',
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
					'category'     => 'post',
					'tags'         => 'post blog grid article loop query news',
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
					'category'     => 'post',
					'tags'         => 'post blog carousel article loop query news',
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
					'category'     => 'content',
					'tags'         => 'tags cloud terms taxonomy keywords archive',
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
					'content_type' => 'custom',
					'category'     => 'navigation',
					'tags'         => 'menu vertical nav navigation sidebar links',
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
					'category'     => 'media',
					'tags'         => 'video gallery playlist youtube vimeo player',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/video-gallery-widget/',
				],
				[
					'name'         => 'video-player',
					'label'        => esc_html__( 'Video Player', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'video-player', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom new',
					'category'     => 'media',
					'tags'         => 'video player youtube vimeo mp4 chapters sticky embed',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/video-player-widget/',
				],
				[
					'name'         => 'whatsapp-button',
					'label'        => esc_html__( 'WhatsApp Button', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'whatsapp-button', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'new',
					'category'     => 'social',
					'tags'         => 'whatsapp chat button contact message float',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/whatsapp-button-widget/',
				],
				[
					'name'         => 'bar-chart',
					'label'        => esc_html__( 'Bar Chart', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'bar-chart', $inactive_widgets ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom',
					'category'     => 'charts',
					'tags'         => 'chart bar graph data statistics visualize',
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
					'category'     => 'charts',
					'tags'         => 'chart line graph data statistics trend',
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
					'category'     => 'charts',
					'tags'         => 'chart polar area graph data statistics',
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
					'category'     => 'charts',
					'tags'         => 'chart pie doughnut graph data percentage',
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
					'category'     => 'charts',
					'tags'         => 'chart radar spider graph data web',
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
					'content_type' => 'custom',
					'category'     => 'post',
					'tags'         => 'loop grid post query builder template repeater',
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
					'content_type' => 'custom',
					'category'     => 'post',
					'tags'         => 'loop carousel post query builder template repeater',
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
					'default'      => 'off',
					'video_url'    => '#',
					'content_type' => 'custom',
					'category'     => 'ecommerce',
					'tags'         => 'woocommerce category grid shop product store',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/docs/sky-addons/wc-widgets/wc-category/',
				],
				[
					'name'         => 'wc-category-carousel',
					'label'        => esc_html__( 'WooCommerce Category Carousel', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'wc-category-carousel', $inactive_3rd_party_widgets ) ? 'on' : 'off',
					'default'      => 'off',
					'video_url'    => '#',
					'content_type' => 'custom',
					'category'     => 'ecommerce',
					'tags'         => 'woocommerce category carousel shop product store',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/docs/sky-addons/wc-widgets/wc-category-carousel/',
				],
				[
					'name'         => 'wc-products',
					'label'        => esc_html__( 'WooCommerce Products', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'wc-products', $inactive_3rd_party_widgets ) ? 'on' : 'off',
					'default'      => 'off',
					'video_url'    => '#',
					'content_type' => 'custom',
					'category'     => 'ecommerce',
					'tags'         => 'woocommerce products grid shop store items',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/docs/sky-addons/wc-widgets/wc-products/',
				],
				[
					'name'         => 'wc-products-carousel',
					'label'        => esc_html__( 'WooCommerce Products Carousel', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'wc-products-carousel', $inactive_3rd_party_widgets ) ? 'on' : 'off',
					'default'      => 'off',
					'video_url'    => '#',
					'content_type' => 'custom',
					'category'     => 'ecommerce',
					'tags'         => 'woocommerce products carousel shop store items',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/docs/sky-addons/wc-widgets/wc-products-carousel/',
				],
				[
					'name'         => 'wc-mini-cart',
					'label'        => esc_html__( 'WooCommerce Mini Cart', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'wc-mini-cart', $inactive_3rd_party_widgets ) ? 'on' : 'off',
					'default'      => 'off',
					'video_url'    => '#',
					'content_type' => 'custom new',
					'category'     => 'ecommerce',
					'tags'         => 'woocommerce cart mini basket checkout shop',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/docs/sky-addons/wc-widgets/',
				],
				[
					'name'         => 'edd-grid',
					'label'        => esc_html__( 'EDD Product Grid', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'edd-grid', $inactive_3rd_party_widgets ) ? 'on' : 'off',
					'default'      => 'off',
					'video_url'    => '#',
					'content_type' => 'custom',
					'category'     => 'ecommerce',
					'tags'         => 'edd easy digital downloads product grid shop',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/docs/sky-addons/widgets/edd-grid/',
				],
				[
					'name'         => 'edd-carousel',
					'label'        => esc_html__( 'EDD Product Carousel', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'edd-carousel', $inactive_3rd_party_widgets ) ? 'on' : 'off',
					'default'      => 'off',
					'video_url'    => '#',
					'content_type' => 'custom',
					'category'     => 'ecommerce',
					'tags'         => 'edd easy digital downloads product carousel shop',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/docs/sky-addons/widgets/edd-carousel/',
				],
				[
					'name'         => 'edd-category-grid',
					'label'        => esc_html__( 'EDD Category Grid', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'edd-category-grid', $inactive_3rd_party_widgets ) ? 'on' : 'off',
					'default'      => 'off',
					'video_url'    => '#',
					'content_type' => 'custom',
					'category'     => 'ecommerce',
					'tags'         => 'edd easy digital downloads category grid shop',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/docs/sky-addons/widgets/edd-category-grid/',
				],
				[
					'name'         => 'edd-category-carousel',
					'label'        => esc_html__( 'EDD Category Carousel', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'edd-category-carousel', $inactive_3rd_party_widgets ) ? 'on' : 'off',
					'default'      => 'off',
					'video_url'    => '#',
					'content_type' => 'custom',
					'category'     => 'ecommerce',
					'tags'         => 'edd easy digital downloads category carousel shop',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/docs/sky-addons/widgets/edd-category-carousel/',
				],
				[
					'name'         => 'cf7',
					'label'        => esc_html__( 'Contact Form 7', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'cf7', $inactive_3rd_party_widgets ) ? 'on' : 'off',
					'default'      => 'off',
					'video_url'    => '#',
					'content_type' => '',
					'category'     => 'forms',
					'tags'         => 'contact form 7 cf7 contact form email',
					'feature_type' => 'free',
					'demo_url'     => '#',
				],
				[
					'name'         => 'fluent-form',
					'label'        => esc_html__( 'Fluent Form', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'fluent-form', $inactive_3rd_party_widgets ) ? 'on' : 'off',
					'default'      => 'off',
					'video_url'    => '#',
					'content_type' => '',
					'category'     => 'forms',
					'tags'         => 'fluent form contact form email',
					'feature_type' => 'free',
					'demo_url'     => '#',
				],
				[
					'name'         => 'gravity-forms',
					'label'        => esc_html__( 'Gravity Forms', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'gravity-forms', $inactive_3rd_party_widgets ) ? 'on' : 'off',
					'default'      => 'off',
					'video_url'    => '#',
					'content_type' => '',
					'category'     => 'forms',
					'tags'         => 'gravity form contact form email',
					'feature_type' => 'free',
					'demo_url'     => '#',
				],
				[
					'name'         => 'ninja-forms',
					'label'        => esc_html__( 'Ninja Forms', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'ninja-forms', $inactive_3rd_party_widgets ) ? 'on' : 'off',
					'default'      => 'off',
					'video_url'    => '#',
					'content_type' => '',
					'category'     => 'forms',
					'tags'         => 'ninja form contact form email',
					'feature_type' => 'free',
					'demo_url'     => '#',
				],
				[
					'name'         => 'we-forms',
					'label'        => esc_html__( 'weForms', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'we-forms', $inactive_3rd_party_widgets ) ? 'on' : 'off',
					'default'      => 'off',
					'video_url'    => '#',
					'content_type' => '',
					'category'     => 'forms',
					'tags'         => 'weforms form contact form email',
					'feature_type' => 'free',
					'demo_url'     => '#',
				],
				[
					'name'         => 'wp-forms',
					'label'        => esc_html__( 'WP Forms', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'wp-forms', $inactive_3rd_party_widgets ) ? 'on' : 'off',
					'default'      => 'off',
					'video_url'    => '#',
					'content_type' => '',
					'category'     => 'forms',
					'tags'         => 'wpforms form contact form email',
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
					'name'         => 'background-reveal',
					'label'        => esc_html__( 'Background Reveal', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'background-reveal', $inactive_extensions ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'new',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/background-reveal-extensions/',
				],
				[
					'name'         => 'button-effects',
					'label'        => esc_html__( 'Button Effects', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'button-effects', $inactive_extensions ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'new',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/button-effects-extension/',
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
					'demo_url'     => 'https://skyaddons.com/docs/sky-addons/extensions/custom-scripts-css-js/',
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
					'demo_url'     => 'https://skyaddons.com/elementor/gradient-text-extension/',
				],
				[
					'name'         => 'grid-canvas',
					'label'        => esc_html__( 'Grid Canvas', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'grid-canvas', $inactive_extensions ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'custom new',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/grid-canvas-extension/',
				],
				[
					'name'         => 'element-parallax',
					'label'        => esc_html__( 'Element Parallax', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'element-parallax', $inactive_extensions ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'new',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/element-parallax-extension/',
				],
				[
					'name'         => 'cursor-effects',
					'label'        => esc_html__( 'Cursor Effects', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'cursor-effects', $inactive_extensions ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'new',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/cursor-effects-extension/',
				],
				[
					'name'         => 'image-curtain',
					'label'        => esc_html__( 'Image Curtain', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'image-curtain', $inactive_extensions ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'new',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/image-curtain-extension/',
				],
				[
					'name'         => 'parallax',
					'label'        => esc_html__( 'Background Parallax', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'parallax', $inactive_extensions ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'new',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/background-parallax-extension/',
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
					'name'         => 'scroll-fill',
					'label'        => esc_html__( 'Scroll Fill', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'scroll-fill', $inactive_extensions ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'new',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/scroll-fill-extensions/',
				],
				[
					'name'         => 'scroll-stack',
					'label'        => esc_html__( 'Scroll Stack', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'scroll-stack', $inactive_extensions ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'new',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/scroll-stack-extensions/',
				],
				[
					'name'         => 'text-animation',
					'label'        => esc_html__( 'Text Animation', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'text-animation', $inactive_extensions ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'new',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/elementor/text-animation-extension/',
				],
				[
					'name'         => 'sticky',
					'label'        => esc_html__( 'Sticky', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'sticky', $inactive_extensions ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'new',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/sticky-element-extension/',
				],
				[
					'name'         => 'tilt-effect',
					'label'        => esc_html__( 'Tilt Effect', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'tilt-effect', $inactive_extensions ) ? 'on' : 'off',
					'default'      => 'on',
					'video_url'    => '#',
					'content_type' => 'new',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/elementor/tilt-effect-extension/',
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
			'sky_addons_advanced_settings' => [
				[
					'name'         => 'dynamic-tags',
					'label'        => esc_html__( 'Dynamic Tags', 'sky-elementor-addons' ),
					'desc'         => esc_html__( 'Pull dynamic content (post, author, site, ACF and more) into widgets via Elementor dynamic tags.', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'dynamic-tags', $inactive_advanced ) ? 'on' : 'off',
					'default'      => 'on',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/docs/sky-addons/',
				],
				[
					'name'         => 'svg-support',
					'label'        => esc_html__( 'SVG Support', 'sky-elementor-addons' ),
					'desc'         => esc_html__( 'Allow SVG files to be uploaded to the media library.', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'svg-support', $inactive_advanced ) ? 'on' : 'off',
					'default'      => 'off',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/docs/sky-addons/extensions/secure-svg-support/',
				],
				[
					'name'         => 'templates-library',
					'label'        => esc_html__( 'Templates Library', 'sky-elementor-addons' ),
					'desc'         => esc_html__( 'Add the Sky Addons template library to the Elementor editor.', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'templates-library', $inactive_advanced ) ? 'on' : 'off',
					'default'      => 'on',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/docs/sky-addons/',
				],
				[
					'name'         => 'duplicator',
					'label'        => esc_html__( 'Duplicator', 'sky-elementor-addons' ),
					'desc'         => esc_html__( 'Add a one-click duplicate action to posts, pages and templates.', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'duplicator', $inactive_advanced ) ? 'on' : 'off',
					'default'      => 'on',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/docs/sky-addons/extensions/duplicator/',
				],
				[
					'name'         => 'video-link',
					'label'        => esc_html__( 'Video Link', 'sky-elementor-addons' ),
					'desc'         => esc_html__( 'Add a Video Link field to the post editor, so post widgets can show a play button without any custom field plugin.', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'video-link', $inactive_advanced ) ? 'on' : 'off',
					'default'      => 'on',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/docs/sky-addons/',
				],
				[
					'name'         => 'menu-duplicator',
					'label'        => esc_html__( 'Menu Duplicator', 'sky-elementor-addons' ),
					'desc'         => esc_html__( 'Add a one-click Duplicate Menu button to Appearance → Menus that clones a menu with all its items.', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'menu-duplicator', $inactive_advanced ) ? 'on' : 'off',
					'default'      => 'on',
					'feature_type' => 'free',
					'demo_url'     => 'https://skyaddons.com/docs/sky-addons/',
				],
				[
					'name'         => 'smooth-scroll',
					'label'        => esc_html__( 'Smooth Scroll', 'sky-elementor-addons' ),
					'desc'         => esc_html__( 'Make your pages scroll smoothly instead of jumping — gives your site a polished, app-like feel.', 'sky-elementor-addons' ),
					'type'         => 'checkbox',
					'value'        => ! in_array( 'smooth-scroll', $inactive_advanced ) ? 'on' : 'off',
					'default'      => 'on',
					'feature_type' => 'pro',
					'demo_url'     => 'https://skyaddons.com/docs/sky-addons/extensions/smooth-scroll/',
				],
			],
			'sky_addons_api' => [
				'form_builder_group' => [
					'label'        => esc_html__( 'Form Builder', 'sky-elementor-addons' ),
					'icon_key'     => 'envelope',
					'input_box'    => [
						[
							'name'        => 'form_builder_email_to',
							'label'       => esc_html__( 'Receiver Email', 'sky-elementor-addons' ),
							'placeholder' => esc_html__( 'Email Address', 'sky-elementor-addons' ),
							'description' => esc_html__( 'By default, the form builder sends emails to the admin email. Configure a different address here.', 'sky-elementor-addons' ),
							'type'        => 'input',
							'value'       => ! empty( $saved_api['form_builder_email_to'] ) ? $saved_api['form_builder_email_to'] : null,
						],
						[
							'name'        => 'form_builder_webhooks',
							'label'       => esc_html__( 'Webhook Endpoints', 'sky-elementor-addons' ),
							'description' => esc_html__( 'Where Form Builder submissions can be sent. Pick one by name on the widget — the URL and secret never leave the server.', 'sky-elementor-addons' ),
							'type'        => 'repeater',
							'add_label'   => esc_html__( 'Add Endpoint', 'sky-elementor-addons' ),
							'empty_label' => esc_html__( 'No endpoints yet.', 'sky-elementor-addons' ),
							// The column schema is the whole contract: it drives the UI, the sanitizing
							// and which values a row cannot be saved without. Adding a column later
							// is a one-line change here — nothing else needs to know about it.
							'row_fields'  => [
								[
									'name'        => 'label',
									'label'       => esc_html__( 'Name', 'sky-elementor-addons' ),
									'placeholder' => esc_html__( 'Orders CRM', 'sky-elementor-addons' ),
									'required'    => true,
								],
								[
									'name'        => 'url',
									'label'       => esc_html__( 'URL', 'sky-elementor-addons' ),
									'placeholder' => 'https://hooks.example.com/abc123',
									'type'        => 'url',
									'required'    => true,
								],
								[
									'name'        => 'secret',
									'label'       => esc_html__( 'Signing Secret', 'sky-elementor-addons' ),
									'placeholder' => esc_html__( 'Optional', 'sky-elementor-addons' ),
									'type'        => 'password',
								],
							],
							// Stored and returned verbatim. What these rows *mean* belongs to Pro,
							// which owns the Form Builder — this plugin only keeps them safely.
							'value'       => ! empty( $saved_api['form_builder_webhooks'] ) ? $saved_api['form_builder_webhooks'] : '',
						],
					],
					'feature_type' => 'pro',
				],
				'sky_addons_api_google_map_group' => [
					'label'        => esc_html__( 'Google Maps', 'sky-elementor-addons' ),
					'icon_key'     => 'map-location-dot',
					'guide'        => [
						'title' => __( 'Google Maps API key — quick setup', 'sky-elementor-addons' ),
						'steps' => [
							__( 'In Google Cloud Console, create or pick a project, then enable billing (required — maps stay free within a generous monthly credit).', 'sky-elementor-addons' ),
							__( 'APIs & Services → Library: enable Maps JavaScript API, Geocoding API and Places API.', 'sky-elementor-addons' ),
							__( 'APIs & Services → Credentials → Create credentials → API key, then copy it.', 'sky-elementor-addons' ),
							__( 'Paste the key below, then restrict it to your domain (HTTP referrers) to keep it safe.', 'sky-elementor-addons' ),
						],
						'note'  => __( 'Maps JavaScript draws the map, Geocoding resolves address markers, Places powers the search box.', 'sky-elementor-addons' ),
						'links' => [
							[
								'label' => __( 'Google Cloud Console', 'sky-elementor-addons' ),
								'url'   => 'https://console.cloud.google.com/',
							],
							[
								'label' => __( 'Enable Maps APIs', 'sky-elementor-addons' ),
								'url'   => 'https://console.cloud.google.com/apis/library',
							],
						],
					],
					'input_box'    => [
						[
							'name'        => 'google_map_key',
							'label'       => esc_html__( 'API Key', 'sky-elementor-addons' ),
							'placeholder' => esc_html__( 'API Key', 'sky-elementor-addons' ),
							'description' => esc_html__( 'Powers the Google Maps widget. Follow the steps above to get your key.', 'sky-elementor-addons' ),
							'type'        => 'password',
							'value'       => ! empty( $saved_api['google_map_key'] ) ? $saved_api['google_map_key'] : null,
						],
					],
					'feature_type' => 'pro',
				],
				'sky_addons_api_mailchimp_group' => [
					'label'        => esc_html__( 'Mailchimp', 'sky-elementor-addons' ),
					'icon_key'     => 'mailchimp',
					'input_box'    => [
						[
							'name'        => 'mailchimp_api_key',
							'label'       => esc_html__( 'API Key', 'sky-elementor-addons' ),
							'placeholder' => esc_html__( 'API Key', 'sky-elementor-addons' ),
							'description' => esc_html__( 'Mailchimp is a popular email marketing and automation platform.', 'sky-elementor-addons' ),
							'type'        => 'password',
							'value'       => ! empty( $saved_api['mailchimp_api_key'] ) ? $saved_api['mailchimp_api_key'] : null,
						],
						[
							'name'        => 'mailchimp_list_id',
							'label'       => esc_html__( 'Audience ID', 'sky-elementor-addons' ),
							'placeholder' => esc_html__( 'Audience ID', 'sky-elementor-addons' ),
							'description' => esc_html__( 'Each Mailchimp audience has a unique audience ID (sometimes called a list ID).', 'sky-elementor-addons' ),
							'type'        => 'input',
							'value'       => ! empty( $saved_api['mailchimp_list_id'] ) ? $saved_api['mailchimp_list_id'] : null,
						],
					],
					'feature_type' => 'pro',
				],
				'sky_addons_api_instagram_group' => [
					'label'        => esc_html__( 'Instagram', 'sky-elementor-addons' ),
					'icon_key'     => 'instagram',
					'guide' => [
						'title' => __( 'Instagram access token — quick & no Facebook', 'sky-elementor-addons' ),
						'steps' => [
							__( 'Your Instagram must be a Business or Creator account (Instagram → Settings → switch to professional). Personal accounts have no API.', 'sky-elementor-addons' ),
							__( 'In your Meta app, add the Instagram product → API setup with Instagram login, and add your account under App roles → Instagram Testers (accept the invite in Instagram).', 'sky-elementor-addons' ),
							__( 'Open the Generate access tokens panel, generate a token, and copy the token (and the Instagram user ID).', 'sky-elementor-addons' ),
							__( 'Paste the token into Access Token (the Account ID is optional). Save. The token auto-refreshes so it never expires.', 'sky-elementor-addons' ),
						],
						'note'  => __( 'Tokens last ~60 days; Sky Addons refreshes yours automatically on a daily schedule.', 'sky-elementor-addons' ),
						'links' => [
							[
								'label' => __( 'Meta App Dashboard', 'sky-elementor-addons' ),
								'url'   => 'https://developers.facebook.com/apps',
							],
						],
					],
					'input_box' => [
						[
							'name'        => 'instagram_account_id',
							'label'       => esc_html__( 'Instagram Account ID', 'sky-elementor-addons' ),
							'placeholder' => esc_html__( 'Optional — blank uses the token account', 'sky-elementor-addons' ),
							'description' => esc_html__( 'The numeric Instagram user ID. Optional — leave blank to use the token own account. Used by the Instagram Feed widget.', 'sky-elementor-addons' ),
							'type'        => 'input',
							'value'       => ! empty( $saved_api['instagram_account_id'] ) ? $saved_api['instagram_account_id'] : null,
						],
						[
							'name'        => 'instagram_access_token',
							'label'       => esc_html__( 'Access Token', 'sky-elementor-addons' ),
							'placeholder' => esc_html__( 'Long-lived Instagram access token', 'sky-elementor-addons' ),
							'description' => esc_html__( 'A long-lived Instagram access token (scope instagram_business_basic) from your Meta app Generate access tokens panel. Keep this private.', 'sky-elementor-addons' ),
							'type'        => 'password',
							'value'       => ! empty( $saved_api['instagram_access_token'] ) ? $saved_api['instagram_access_token'] : null,
						],
					],
					'feature_type' => 'pro',
				],
				'sky_addons_api_facebook_group' => [
					'label'        => esc_html__( 'Facebook', 'sky-elementor-addons' ),
					'icon_key'     => 'facebook',
					'guide'        => [
						'title' => __( 'Page Access Token — the easy free way (~4 clicks)', 'sky-elementor-addons' ),
						'steps' => [
							__( 'Open the Graph API Explorer (button below).', 'sky-elementor-addons' ),
							__( 'Top-right: pick your App, then the Get Page Access Token option.', 'sky-elementor-addons' ),
							__( 'Select your Page and approve the permissions.', 'sky-elementor-addons' ),
							__( 'Copy the token into the field below. Tip: extend it in the Token Debugger so it never expires.', 'sky-elementor-addons' ),
						],
						'note'  => __( 'Page ID: open me?fields=id in the Explorer with that token, or paste your page username in the Page ID field.', 'sky-elementor-addons' ),
						'links' => [
							[
								'label' => __( 'Graph API Explorer', 'sky-elementor-addons' ),
								'url'   => 'https://developers.facebook.com/tools/explorer',
							],
							[
								'label' => __( 'Make it permanent (Token Debugger → Extend)', 'sky-elementor-addons' ),
								'url'   => 'https://developers.facebook.com/tools/debug/accesstoken',
							],
						],
					],
					'input_box'    => [
						[
							'name'        => 'facebook_page_id',
							'label'       => esc_html__( 'Page ID', 'sky-elementor-addons' ),
							'placeholder' => esc_html__( 'Page ID', 'sky-elementor-addons' ),
							'description' => esc_html__( 'The numeric ID (or username) of the Facebook page to pull posts from. Used by the Facebook Feed widget.', 'sky-elementor-addons' ),
							'type'        => 'input',
							'value'       => ! empty( $saved_api['facebook_page_id'] ) ? $saved_api['facebook_page_id'] : null,
						],
						[
							'name'        => 'facebook_access_token',
							'label'       => esc_html__( 'Page Access Token', 'sky-elementor-addons' ),
							'placeholder' => esc_html__( 'Page Access Token', 'sky-elementor-addons' ),
							'description' => esc_html__( 'A Facebook Page access token with permission to read the page feed. Keep this private.', 'sky-elementor-addons' ),
							'type'        => 'password',
							'value'       => ! empty( $saved_api['facebook_access_token'] ) ? $saved_api['facebook_access_token'] : null,
						],
					],
					'feature_type' => 'pro',
				],
				'sky_addons_api_twitter_group' => [
					'label'        => esc_html__( 'Twitter / X', 'sky-elementor-addons' ),
					'icon_key'     => 'x-twitter',
					'input_box'    => [
						[
							'name'        => 'twitter_api_key',
							'label'       => esc_html__( 'API Key', 'sky-elementor-addons' ),
							'placeholder' => esc_html__( 'API Key', 'sky-elementor-addons' ),
							'description' => '',
							'type'        => 'password',
							'value'       => ! empty( $saved_api['twitter_api_key'] ) ? $saved_api['twitter_api_key'] : null,
						],
						[
							'name'        => 'twitter_api_secret',
							'label'       => esc_html__( 'API Secret', 'sky-elementor-addons' ),
							'placeholder' => esc_html__( 'API Secret', 'sky-elementor-addons' ),
							'description' => '',
							'type'        => 'password',
							'value'       => ! empty( $saved_api['twitter_api_secret'] ) ? $saved_api['twitter_api_secret'] : null,
						],
						[
							'name'        => 'twitter_bearer_token',
							'label'       => esc_html__( 'Bearer Token', 'sky-elementor-addons' ),
							'placeholder' => esc_html__( 'Bearer Token', 'sky-elementor-addons' ),
							'description' => '',
							'type'        => 'password',
							'value'       => ! empty( $saved_api['twitter_bearer_token'] ) ? $saved_api['twitter_bearer_token'] : null,
						],
					],
					'feature_type' => 'free',
				],
				'sky_addons_api_captcha_group' => [
					'label'        => esc_html__( 'Login CAPTCHA', 'sky-elementor-addons' ),
					'icon_key'     => 'shield-halved',
					'input_box'    => [
						[
							'name'        => 'login_captcha_provider',
							'label'       => esc_html__( 'Provider', 'sky-elementor-addons' ),
							'description' => esc_html__( 'Spam/bot protection for the Login and Register forms. Configure the matching key pair below.', 'sky-elementor-addons' ),
							'type'        => 'select',
							'default'     => 'none',
							'options'     => [
								[
									'value' => 'none',
									'label' => esc_html__( 'None', 'sky-elementor-addons' ),
								],
								[
									'value' => 'recaptcha_v3',
									'label' => esc_html__( 'Google reCAPTCHA v3', 'sky-elementor-addons' ),
								],
								[
									'value' => 'turnstile',
									'label' => esc_html__( 'Cloudflare Turnstile', 'sky-elementor-addons' ),
								],
							],
							'value'       => ! empty( $saved_api['login_captcha_provider'] ) ? $saved_api['login_captcha_provider'] : 'none',
						],
						[
							'name'        => 'recaptcha_v3_site_key',
							'label'       => esc_html__( 'reCAPTCHA v3 Site Key', 'sky-elementor-addons' ),
							'placeholder' => esc_html__( 'Site Key', 'sky-elementor-addons' ),
							'description' => esc_html__( 'From Google reCAPTCHA admin (v3). The site key is public.', 'sky-elementor-addons' ),
							'type'        => 'input',
							'value'       => ! empty( $saved_api['recaptcha_v3_site_key'] ) ? $saved_api['recaptcha_v3_site_key'] : null,
						],
						[
							'name'        => 'recaptcha_v3_secret_key',
							'label'       => esc_html__( 'reCAPTCHA v3 Secret Key', 'sky-elementor-addons' ),
							'placeholder' => esc_html__( 'Secret Key', 'sky-elementor-addons' ),
							'description' => esc_html__( 'Keep this private — used only for server-side verification.', 'sky-elementor-addons' ),
							'type'        => 'password',
							'value'       => ! empty( $saved_api['recaptcha_v3_secret_key'] ) ? $saved_api['recaptcha_v3_secret_key'] : null,
						],
						[
							'name'        => 'turnstile_site_key',
							'label'       => esc_html__( 'Turnstile Site Key', 'sky-elementor-addons' ),
							'placeholder' => esc_html__( 'Site Key', 'sky-elementor-addons' ),
							'description' => esc_html__( 'From the Cloudflare Turnstile dashboard. The site key is public.', 'sky-elementor-addons' ),
							'type'        => 'input',
							'value'       => ! empty( $saved_api['turnstile_site_key'] ) ? $saved_api['turnstile_site_key'] : null,
						],
						[
							'name'        => 'turnstile_secret_key',
							'label'       => esc_html__( 'Turnstile Secret Key', 'sky-elementor-addons' ),
							'placeholder' => esc_html__( 'Secret Key', 'sky-elementor-addons' ),
							'description' => esc_html__( 'Keep this private — used only for server-side verification.', 'sky-elementor-addons' ),
							'type'        => 'password',
							'value'       => ! empty( $saved_api['turnstile_secret_key'] ) ? $saved_api['turnstile_secret_key'] : null,
						],
					],
					'feature_type' => 'pro',
				],
				'sky_addons_api_google_login_group' => [
					'label'        => esc_html__( 'Google Login', 'sky-elementor-addons' ),
					'icon_key'     => 'google',
					'input_box'    => [
						[
							'name'        => 'google_login_client_id',
							'label'       => esc_html__( 'OAuth Client ID', 'sky-elementor-addons' ),
							// phpcs:ignore PluginCheck.CodeAnalysis.Offloading.OffloadedContent -- Placeholder text showing the Client ID format, not a remote asset.
							'placeholder' => esc_html__( 'xxxx.apps.googleusercontent.com', 'sky-elementor-addons' ),
							'description' => esc_html__( 'From a Google Cloud OAuth 2.0 Client (Web). Add your site URL to the Authorized JavaScript origins. The client ID is public.', 'sky-elementor-addons' ),
							'type'        => 'input',
							'value'       => ! empty( $saved_api['google_login_client_id'] ) ? $saved_api['google_login_client_id'] : null,
						],
						[
							'name'        => 'google_login_client_secret',
							'label'       => esc_html__( 'OAuth Client Secret', 'sky-elementor-addons' ),
							'placeholder' => esc_html__( 'Client Secret', 'sky-elementor-addons' ),
							'description' => esc_html__( 'Optional — sign-in verification uses the ID token, so the secret is not required for login.', 'sky-elementor-addons' ),
							'type'        => 'password',
							'value'       => ! empty( $saved_api['google_login_client_secret'] ) ? $saved_api['google_login_client_secret'] : null,
						],
					],
					'feature_type' => 'pro',
				],
				'sky_addons_api_openai_group' => [
					'label'        => esc_html__( 'AI Provider', 'sky-elementor-addons' ),
					'icon_key'     => 'robot',
					'input_box'    => [
						[
							'name'    => 'ai_provider',
							'label'   => esc_html__( 'Provider', 'sky-elementor-addons' ),
							'type'    => 'select',
							'default' => 'openrouter',
							'options' => [
								[
									'value' => 'openrouter',
									'label' => 'OpenRouter',
								],
								[
									'value' => 'openai',
									'label' => 'OpenAI',
								],
							],
							'value'   => ! empty( $saved_api['ai_provider'] ) ? $saved_api['ai_provider'] : 'openrouter',
						],
						[
							'name'        => 'openai_api_key',
							'label'       => esc_html__( 'API Key', 'sky-elementor-addons' ),
							'placeholder' => esc_html__( 'API Key', 'sky-elementor-addons' ),
							'description' => esc_html__( 'API key for your selected AI provider.', 'sky-elementor-addons' ),
							'type'        => 'password',
							'value'       => ! empty( $saved_api['openai_api_key'] ) ? $saved_api['openai_api_key'] : null,
						],
					],
					'feature_type' => 'pro',
				],
			],
		];

		self::$widget_list = $widgets_fields['sky_addons_widgets'];
		self::$widget_list = array_merge( self::$widget_list, $widgets_fields['sky_addons_3rd_party_widget'] );

		$used_widgets                         = self::get_used_widgets();
		$widgets_fields['sky_addons_widgets'] = array_map(function ( $widget ) use ( $used_widgets ) {
			$widget_name          = $widget['name'];
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

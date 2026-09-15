<?php

namespace Sky_Addons\Modules\SimpleParallax;

use Elementor\Controls_Manager;
use Sky_Addons\Base\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Module extends Module_Base {

	public function __construct() {
		parent::__construct();
		$this->add_actions();
	}

	public function get_name() {
		return 'sky-simple-parallax';
	}

	public function register_section( $element ) {
		$element->start_controls_section(
			'section_sky_addons_sp_controls',
			[
				'tab'   => Controls_Manager::TAB_ADVANCED,
				'label' => esc_html__( 'Simple Parallax', 'sky-elementor-addons' ) . sky_addons_get_icon(),
			]
		);
		$element->end_controls_section();
	}

	public function register_controls( $widget, $args ) {

		$widget->add_control(
			'sa_sp_notice',
			[
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => esc_html__( 'Adds a scroll parallax to every image or video inside this widget — the media drifts within its own frame as the page scrolls, so you get depth without the layout moving. Best on a large image or a media-heavy section; skip it for logos, icons and small thumbnails.', 'sky-elementor-addons' ),
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
			]
		);

		$widget->add_control(
			'sa_sp_enable',
			[
				'label'              => esc_html__( 'Enable', 'sky-elementor-addons' ),
				'type'               => Controls_Manager::SWITCHER,
				'render_type'        => 'template',
				'frontend_available' => true,
			]
		);

		$widget->add_control(
			'sa_sp_media_type',
			[
				'label'              => esc_html__( 'Media Type', 'sky-elementor-addons' ),
				'type'               => Controls_Manager::SELECT,
				'default'            => 'image',
				'options'            => [
					'image' => esc_html__( 'Image', 'sky-elementor-addons' ),
					'video' => esc_html__( 'Video', 'sky-elementor-addons' ),
				],
				'frontend_available' => true,
				'condition'          => [
					'sa_sp_enable' => 'yes',
				],
			]
		);

		$widget->add_control(
			'sa_sp_scale',
			[
				'label'              => esc_html__( 'Scale', 'sky-elementor-addons' ),
				'type'               => Controls_Manager::SLIDER,
				'description'        => esc_html__( 'Higher scale = stronger effect, but lower image quality. Must stay above 1.', 'sky-elementor-addons' ),
				'range'              => [
					'px' => [
						'min'  => 1,
						'max'  => 3,
						'step' => 0.1,
					],
				],
				'default'            => [
					'unit' => 'px',
					'size' => 1.4,
				],
				'frontend_available' => true,
				'condition'          => [
					'sa_sp_enable' => 'yes',
				],
			]
		);

		$widget->add_control(
			'sa_sp_orientation',
			[
				'label'              => esc_html__( 'Orientation', 'sky-elementor-addons' ),
				'type'               => Controls_Manager::SELECT,
				'default'            => 'up',
				'options'            => [
					'up'         => esc_html__( 'Up', 'sky-elementor-addons' ),
					'down'       => esc_html__( 'Down', 'sky-elementor-addons' ),
					'left'       => esc_html__( 'Left', 'sky-elementor-addons' ),
					'right'      => esc_html__( 'Right', 'sky-elementor-addons' ),
					'up-left'    => esc_html__( 'Up Left', 'sky-elementor-addons' ),
					'up-right'   => esc_html__( 'Up Right', 'sky-elementor-addons' ),
					'down-left'  => esc_html__( 'Down Left', 'sky-elementor-addons' ),
					'down-right' => esc_html__( 'Down Right', 'sky-elementor-addons' ),
				],
				'frontend_available' => true,
				'condition'          => [
					'sa_sp_enable' => 'yes',
				],
			]
		);

		$widget->add_control(
			'sa_sp_delay',
			[
				'label'              => esc_html__( 'Delay', 'sky-elementor-addons' ),
				'type'               => Controls_Manager::SLIDER,
				'description'        => esc_html__( 'Inertia after scroll stops (seconds). Set Transition to control the easing.', 'sky-elementor-addons' ),
				'range'              => [
					'px' => [
						'min'  => 0,
						'max'  => 5,
						'step' => 0.1,
					],
				],
				'default'            => [
					'unit' => 'px',
					'size' => 0,
				],
				'frontend_available' => true,
				'condition'          => [
					'sa_sp_enable' => 'yes',
				],
			]
		);

		$widget->add_control(
			'sa_sp_transition',
			[
				'label'              => esc_html__( 'Transition', 'sky-elementor-addons' ),
				'type'               => Controls_Manager::SELECT,
				'default'            => 'cubic-bezier(0,0,0,1)',
				'description'        => esc_html__( 'Easing curve for the Delay effect. Only applies when Delay > 0.', 'sky-elementor-addons' ),
				'options'            => [
					'cubic-bezier(0,0,0,1)'               => esc_html__( 'Default (Smooth Inertia)', 'sky-elementor-addons' ),
					'linear'                              => esc_html__( 'Linear', 'sky-elementor-addons' ),
					'ease'                                => esc_html__( 'Ease', 'sky-elementor-addons' ),
					'ease-in'                             => esc_html__( 'Ease In', 'sky-elementor-addons' ),
					'ease-out'                            => esc_html__( 'Ease Out', 'sky-elementor-addons' ),
					'ease-in-out'                         => esc_html__( 'Ease In Out', 'sky-elementor-addons' ),
					'cubic-bezier(0.25,0.46,0.45,0.94)'   => esc_html__( 'Ease Out Quad', 'sky-elementor-addons' ),
					'cubic-bezier(0.215,0.61,0.355,1)'    => esc_html__( 'Ease Out Cubic', 'sky-elementor-addons' ),
					'cubic-bezier(0.23,1,0.32,1)'         => esc_html__( 'Ease Out Quart', 'sky-elementor-addons' ),
					'cubic-bezier(0.19,1,0.22,1)'         => esc_html__( 'Ease Out Expo', 'sky-elementor-addons' ),
					'cubic-bezier(0.68,-0.55,0.265,1.55)' => esc_html__( 'Spring', 'sky-elementor-addons' ),
					'cubic-bezier(0.87,0,0.13,1)'         => esc_html__( 'Ease In Out Expo', 'sky-elementor-addons' ),
					'cubic-bezier(0.34,1.56,0.64,1)'      => esc_html__( 'Bounce (Soft)', 'sky-elementor-addons' ),
					'custom'                              => esc_html__( 'Custom', 'sky-elementor-addons' ),
				],
				'frontend_available' => true,
				'condition'          => [
					'sa_sp_enable' => 'yes',
				],
			]
		);

		$widget->add_control(
			'sa_sp_transition_custom',
			[
				'label'              => esc_html__( 'Custom Easing', 'sky-elementor-addons' ),
				'type'               => Controls_Manager::TEXT,
				'default'            => '',
				'placeholder'        => 'cubic-bezier(0.25, 0.1, 0.25, 1)',
				'frontend_available' => true,
				'condition'          => [
					'sa_sp_enable'     => 'yes',
					'sa_sp_transition' => 'custom',
				],
			]
		);

		$widget->add_control(
			'sa_sp_max_transition',
			[
				'label'              => esc_html__( 'Max Transition', 'sky-elementor-addons' ),
				'type'               => Controls_Manager::SLIDER,
				'description'        => esc_html__( 'Caps the parallax travel at this viewport %. 0 = no cap.', 'sky-elementor-addons' ),
				'range'              => [
					'px' => [
						'min'  => 0,
						'max'  => 99,
						'step' => 1,
					],
				],
				'default'            => [
					'unit' => 'px',
					'size' => 0,
				],
				'frontend_available' => true,
				'condition'          => [
					'sa_sp_enable' => 'yes',
				],
			]
		);

		$widget->add_control(
			'sa_sp_overflow',
			[
				'label'              => esc_html__( 'Overflow', 'sky-elementor-addons' ),
				'type'               => Controls_Manager::SWITCHER,
				'default'            => '',
				'frontend_available' => true,
				'condition'          => [
					'sa_sp_enable' => 'yes',
				],
			]
		);

		$widget->add_control(
			'sa_sp_custom_container',
			[
				'label'              => esc_html__( 'Custom Container', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'               => Controls_Manager::TEXT,
				'default'            => '',
				'placeholder'        => esc_html__( 'CSS selector, e.g. .my-scroll-box', 'sky-elementor-addons' ),
				'description'        => esc_html__( 'Set when the widget is inside a custom scrollable container.', 'sky-elementor-addons' ),
				'frontend_available' => true,
				'condition'          => [
					'sa_sp_enable' => 'yes',
				],
			]
		);

		$widget->add_control(
			'sa_sp_custom_wrapper',
			[
				'label'              => esc_html__( 'Custom Wrapper', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.0.0' ),
				'type'               => Controls_Manager::TEXT,
				'default'            => '',
				'placeholder'        => esc_html__( 'CSS selector, e.g. .my-wrapper', 'sky-elementor-addons' ),
				'description'        => esc_html__( 'Use an existing ancestor as the clipping wrapper instead of letting the library inject one. Ignored when Overflow is on.', 'sky-elementor-addons' ),
				'frontend_available' => true,
				'condition'          => [
					'sa_sp_enable'    => 'yes',
					'sa_sp_overflow!' => 'yes',
				],
			]
		);
	}

	public function simple_parallax( $widget ) {
		$settings = $widget->get_settings_for_display();
		if ( isset( $settings['sa_sp_enable'] ) && 'yes' === $settings['sa_sp_enable'] ) {
			wp_enqueue_script( 'simple-parallax' );
			wp_enqueue_script( 'sa-simple-parallax' );
		}
	}

	protected function add_actions() {
		add_action( 'elementor/element/common/_section_style/after_section_end', [ $this, 'register_section' ] );
		add_action( 'elementor/element/common/section_sky_addons_sp_controls/before_section_end', [ $this, 'register_controls' ], 10, 2 );
		add_action( 'elementor/frontend/widget/before_render', [ $this, 'simple_parallax' ], 10, 1 );
	}
}

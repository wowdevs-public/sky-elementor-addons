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
				'label' => esc_html__( 'Parallax Effects', 'sky-elementor-addons' ) . sky_addons_get_icon(),
			]
		);
		$element->end_controls_section();
	}

	public function register_controls( $widget, $args ) {

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
			'sa_sp_speed',
			[
				'label'              => esc_html__( 'Speed', 'sky-elementor-addons' ),
				'type'               => Controls_Manager::NUMBER,
				'default'            => 1.5,
				'min'                => 0.1,
				'max'                => 5,
				'step'               => 0.1,
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
				'type'               => Controls_Manager::NUMBER,
				'default'            => 1.3,
				'min'                => 1,
				'max'                => 3,
				'step'               => 0.1,
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
				'type'               => Controls_Manager::NUMBER,
				'default'            => 0,
				'min'                => 0,
				'max'                => 5,
				'step'               => 0.1,
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
				'type'               => Controls_Manager::TEXT,
				'default'            => '',
				'frontend_available' => true,
				'condition'          => [
					'sa_sp_enable' => 'yes',
				],
			]
		);

		$widget->add_control(
			'sa_sp_max_transition',
			[
				'label'              => esc_html__( 'Max Transition', 'sky-elementor-addons' ),
				'type'               => Controls_Manager::NUMBER,
				'default'            => 0,
				'min'                => 1,
				'max'                => 99,
				'step'               => 1,
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
	}

	public function simple_parallax( $widget ) {
		$settings = $widget->get_settings_for_display();
		if ( 'yes' === $settings['sa_sp_enable'] ) {
			wp_enqueue_script( 'simple-parallax' );
		}
	}

	protected function add_actions() {
		add_action( 'elementor/element/common/_section_style/after_section_end', [ $this, 'register_section' ] );
		add_action( 'elementor/element/common/section_sky_addons_sp_controls/before_section_end', [ $this, 'register_controls' ], 10, 2 );
		add_action( 'elementor/frontend/widget/before_render', [ $this, 'simple_parallax' ], 10, 1 );
	}
}

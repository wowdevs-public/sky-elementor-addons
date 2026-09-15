<?php

namespace Sky_Addons\Modules\TiltEffect;

use Elementor\Controls_Manager;
use Sky_Addons\Base\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Tilt Effect extension.
 *
 * Adds a "Tilt Effect" section (Advanced tab) to every widget: the element tilts
 * in 3D toward/away from the cursor on hover, with optional scale and a glare
 * sheen that follows the pointer. No library and no stylesheet — the handler
 * writes every style inline. Skips touch-only devices and honors
 * prefers-reduced-motion. Widgets only (containers out of v1).
 */
class Module extends Module_Base {

	public function __construct() {
		parent::__construct();
		$this->add_actions();
	}

	public function get_name() {
		return 'sky-tilt-effect';
	}

	public function register_section( $element ) {
		$element->start_controls_section(
			'section_sky_addons_tilt_controls',
			[
				'tab'   => Controls_Manager::TAB_ADVANCED,
				'label' => esc_html__( 'Tilt Effect', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ) . sky_addons_get_icon(),
			]
		);
		$element->end_controls_section();
	}

	public function register_controls( $widget, $args ) {

		$widget->add_control(
			'sa_tilt_enable',
			[
				'label'              => esc_html__( 'Enable', 'sky-elementor-addons' ),
				'type'               => Controls_Manager::SWITCHER,
				'render_type'        => 'template',
				'frontend_available' => true,
			]
		);

		$widget->add_control(
			'sa_tilt_max',
			[
				'label'              => esc_html__( 'Max Tilt', 'sky-elementor-addons' ),
				'type'               => Controls_Manager::SLIDER,
				'size_units'         => [ 'deg' ],
				'range'              => [
					'deg' => [
						'min'  => 0,
						'max'  => 45,
						'step' => 1,
					],
				],
				'default'            => [
					'unit' => 'deg',
					'size' => 15,
				],
				'render_type'        => 'none',
				'frontend_available' => true,
				'condition'          => [ 'sa_tilt_enable' => 'yes' ],
			]
		);

		$widget->add_control(
			'sa_tilt_perspective',
			[
				'label'              => esc_html__( 'Perspective', 'sky-elementor-addons' ),
				'type'               => Controls_Manager::SLIDER,
				'size_units'         => [ 'px' ],
				'range'              => [
					'px' => [
						'min'  => 300,
						'max'  => 2000,
						'step' => 50,
					],
				],
				'default'            => [
					'unit' => 'px',
					'size' => 1000,
				],
				'description'        => esc_html__( 'Lower values make the 3D effect more dramatic.', 'sky-elementor-addons' ),
				'render_type'        => 'none',
				'frontend_available' => true,
				'condition'          => [ 'sa_tilt_enable' => 'yes' ],
			]
		);

		$widget->add_control(
			'sa_tilt_speed',
			[
				'label'              => esc_html__( 'Speed (ms)', 'sky-elementor-addons' ),
				'type'               => Controls_Manager::SLIDER,
				'size_units'         => [ 'px' ],
				'range'              => [
					'px' => [
						'min'  => 100,
						'max'  => 2000,
						'step' => 50,
					],
				],
				'default'            => [
					'unit' => 'px',
					'size' => 300,
				],
				'render_type'        => 'none',
				'frontend_available' => true,
				'condition'          => [ 'sa_tilt_enable' => 'yes' ],
			]
		);

		$widget->add_control(
			'sa_tilt_easing',
			[
				'label'              => esc_html__( 'Easing', 'sky-elementor-addons' ),
				'type'               => Controls_Manager::SELECT,
				'default'            => 'cubic-bezier(.03,.98,.52,.99)',
				'options'            => [
					'cubic-bezier(.03,.98,.52,.99)' => esc_html__( 'Smooth', 'sky-elementor-addons' ),
					'ease'                          => esc_html__( 'Ease', 'sky-elementor-addons' ),
					'ease-out'                      => esc_html__( 'Ease Out', 'sky-elementor-addons' ),
					'ease-in-out'                   => esc_html__( 'Ease In Out', 'sky-elementor-addons' ),
					'linear'                        => esc_html__( 'Linear', 'sky-elementor-addons' ),
				],
				'render_type'        => 'none',
				'frontend_available' => true,
				'condition'          => [ 'sa_tilt_enable' => 'yes' ],
			]
		);

		$widget->add_control(
			'sa_tilt_scale',
			[
				'label'              => esc_html__( 'Scale', 'sky-elementor-addons' ),
				'type'               => Controls_Manager::SLIDER,
				'size_units'         => [ 'px' ],
				'range'              => [
					'px' => [
						'min'  => 1,
						'max'  => 1.5,
						'step' => 0.01,
					],
				],
				'default'            => [
					'unit' => 'px',
					'size' => 1,
				],
				'description'        => esc_html__( 'Zoom while hovered. 1 = no zoom.', 'sky-elementor-addons' ),
				'render_type'        => 'none',
				'frontend_available' => true,
				'condition'          => [ 'sa_tilt_enable' => 'yes' ],
			]
		);

		$widget->add_control(
			'sa_tilt_reverse',
			[
				'label'              => esc_html__( 'Reverse', 'sky-elementor-addons' ),
				'type'               => Controls_Manager::SWITCHER,
				'description'        => esc_html__( 'Tilt away from the cursor instead of toward it.', 'sky-elementor-addons' ),
				'render_type'        => 'none',
				'frontend_available' => true,
				'condition'          => [ 'sa_tilt_enable' => 'yes' ],
			]
		);

		$widget->add_control(
			'sa_tilt_axis',
			[
				'label'              => esc_html__( 'Axis', 'sky-elementor-addons' ),
				'type'               => Controls_Manager::SELECT,
				'default'            => 'both',
				'options'            => [
					'both' => esc_html__( 'Both', 'sky-elementor-addons' ),
					'x'    => esc_html__( 'X Only (vertical mouse)', 'sky-elementor-addons' ),
					'y'    => esc_html__( 'Y Only (horizontal mouse)', 'sky-elementor-addons' ),
				],
				'render_type'        => 'none',
				'frontend_available' => true,
				'condition'          => [ 'sa_tilt_enable' => 'yes' ],
			]
		);

		$widget->add_control(
			'sa_tilt_glare',
			[
				'label'              => esc_html__( 'Glare', 'sky-elementor-addons' ),
				'type'               => Controls_Manager::SWITCHER,
				'separator'          => 'before',
				'description'        => esc_html__( 'A light sheen that follows the cursor.', 'sky-elementor-addons' ),
				'render_type'        => 'none',
				'frontend_available' => true,
				'condition'          => [ 'sa_tilt_enable' => 'yes' ],
			]
		);

		$widget->add_control(
			'sa_tilt_glare_color',
			[
				'label'              => esc_html__( 'Glare Color', 'sky-elementor-addons' ),
				'type'               => Controls_Manager::COLOR,
				'default'            => '',
				'description'        => esc_html__( 'Leave empty for a white sheen.', 'sky-elementor-addons' ),
				'render_type'        => 'none',
				'frontend_available' => true,
				'condition'          => [
					'sa_tilt_enable' => 'yes',
					'sa_tilt_glare'  => 'yes',
				],
			]
		);

		$widget->add_control(
			'sa_tilt_glare_opacity',
			[
				'label'              => esc_html__( 'Glare Opacity', 'sky-elementor-addons' ),
				'type'               => Controls_Manager::SLIDER,
				'size_units'         => [ 'px' ],
				'range'              => [
					'px' => [
						'min'  => 0,
						'max'  => 1,
						'step' => 0.05,
					],
				],
				'default'            => [
					'unit' => 'px',
					'size' => 0.4,
				],
				'render_type'        => 'none',
				'frontend_available' => true,
				'condition'          => [
					'sa_tilt_enable' => 'yes',
					'sa_tilt_glare'  => 'yes',
				],
			]
		);
	}

	public function tilt_before_render( $element ) {
		$settings = $element->get_settings_for_display();
		if ( 'yes' === ( $settings['sa_tilt_enable'] ?? '' ) ) {
			wp_enqueue_script( 'sa-tilt-effect' );
		}
	}

	protected function add_actions() {
		// `common` covers widgets only — a container is its own element type and never sees
		// those hooks, which is why the Tilt Effect section was missing from containers and
		// sections entirely. Each type is registered explicitly, the same way grid-canvas,
		// sticky and reveal-effects do it.
		//
		// The anchor section only decides registration ORDER; the panel tab comes from
		// `'tab' => TAB_ADVANCED` in register_section(), so Tilt still lands under Advanced
		// for every type. `section_layout` is used for container/section because every one of
		// them has it — `_section_style` exists on widgets only.
		// `common` ONLY — never also `common-optimized`. Elementor already covers the optimized
		// stack for us: Controls_Stack::should_manually_trigger_common_action() re-fires the
		// `common` alias whenever the stack name is `common-optimized`
		// (elementor/includes/base/controls-stack.php:1745). Hooking both makes every callback
		// run twice, which registers the section twice, which fires before_section_end twice —
		// four registrations and a wall of "Cannot redeclare control" notices.
		add_action( 'elementor/element/common/_section_style/after_section_end', [ $this, 'register_section' ] );
		add_action( 'elementor/element/common/section_sky_addons_tilt_controls/before_section_end', [ $this, 'register_controls' ], 10, 2 );

		add_action( 'elementor/element/container/section_layout/after_section_end', [ $this, 'register_section' ] );
		add_action( 'elementor/element/container/section_sky_addons_tilt_controls/before_section_end', [ $this, 'register_controls' ], 10, 2 );

		add_action( 'elementor/element/section/section_layout/after_section_end', [ $this, 'register_section' ] );
		add_action( 'elementor/element/section/section_sky_addons_tilt_controls/before_section_end', [ $this, 'register_controls' ], 10, 2 );

		// before_render is fired per element TYPE, so the widget hook alone never enqueued the
		// script for a tilted container.
		add_action( 'elementor/frontend/widget/before_render', [ $this, 'tilt_before_render' ], 10, 1 );
		add_action( 'elementor/frontend/container/before_render', [ $this, 'tilt_before_render' ], 10, 1 );
		add_action( 'elementor/frontend/section/before_render', [ $this, 'tilt_before_render' ], 10, 1 );
	}
}

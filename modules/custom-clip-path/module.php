<?php

namespace Sky_Addons\Modules\CustomClipPath;

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
		return 'sky-custom-clip-path';
	}

	/**
	 * All clip-path preset shapes sourced from https://bennettfeely.com/clippy/
	 * To add a new preset: append one entry here — both Normal and Hover tabs update automatically.
	 *
	 * Keys are the full CSS clip-path rule (output verbatim via {{VALUE}}).
	 * Note: circle() and ellipse() cannot CSS-interpolate with polygon() shapes — hover
	 * transitions will snap rather than morph when mixing these types.
	 */
	protected function get_preset_shapes() {
		return [
			// ── Regular Polygons ─────────────────────────────────────────────────────────────────────────
			'clip-path: polygon(50% 0%, 0% 100%, 100% 100%)'
				=> esc_html__( 'Triangle', 'sky-elementor-addons' ),
			'clip-path: polygon(20% 0%, 80% 0%, 100% 100%, 0% 100%)'
				=> esc_html__( 'Trapezoid', 'sky-elementor-addons' ),
			'clip-path: polygon(25% 0%, 100% 0%, 75% 100%, 0% 100%)'
				=> esc_html__( 'Parallelogram', 'sky-elementor-addons' ),
			'clip-path: polygon(50% 0%, 100% 50%, 50% 100%, 0% 50%)'
				=> esc_html__( 'Rhombus', 'sky-elementor-addons' ),
			'clip-path: polygon(50% 0%, 100% 38%, 82% 100%, 18% 100%, 0% 38%)'
				=> esc_html__( 'Pentagon', 'sky-elementor-addons' ),
			'clip-path: polygon(50% 0%, 100% 25%, 100% 75%, 50% 100%, 0% 75%, 0% 25%)'
				=> esc_html__( 'Hexagon', 'sky-elementor-addons' ),
			'clip-path: polygon(50% 0%, 90% 20%, 100% 60%, 75% 100%, 25% 100%, 0% 60%, 10% 20%)'
				=> esc_html__( 'Heptagon', 'sky-elementor-addons' ),
			'clip-path: polygon(30% 0%, 70% 0%, 100% 30%, 100% 70%, 70% 100%, 30% 100%, 0% 70%, 0% 30%)'
				=> esc_html__( 'Octagon', 'sky-elementor-addons' ),
			'clip-path: polygon(50% 0%, 83% 12%, 100% 43%, 94% 78%, 68% 100%, 32% 100%, 6% 78%, 0% 43%, 17% 12%)'
				=> esc_html__( 'Nonagon', 'sky-elementor-addons' ),
			'clip-path: polygon(50% 0%, 80% 10%, 100% 35%, 100% 70%, 80% 90%, 50% 100%, 20% 90%, 0% 70%, 0% 35%, 20% 10%)'
				=> esc_html__( 'Decagon', 'sky-elementor-addons' ),
			'clip-path: polygon(20% 0%, 80% 0%, 100% 20%, 100% 80%, 80% 100%, 20% 100%, 0% 80%, 0% 20%)'
				=> esc_html__( 'Bevel', 'sky-elementor-addons' ),
			'clip-path: polygon(0% 15%, 15% 15%, 15% 0%, 85% 0%, 85% 15%, 100% 15%, 100% 85%, 85% 85%, 85% 100%, 15% 100%, 15% 85%, 0% 85%)'
				=> esc_html__( 'Rabbet', 'sky-elementor-addons' ),
			// ── Arrows & Points ──────────────────────────────────────────────────────────────────────────
			'clip-path: polygon(40% 0%, 40% 20%, 100% 20%, 100% 80%, 40% 80%, 40% 100%, 0% 50%)'
				=> esc_html__( 'Arrow Left', 'sky-elementor-addons' ),
			'clip-path: polygon(0% 20%, 60% 20%, 60% 0%, 100% 50%, 60% 100%, 60% 80%, 0% 80%)'
				=> esc_html__( 'Arrow Right', 'sky-elementor-addons' ),
			'clip-path: polygon(25% 0%, 100% 1%, 100% 100%, 25% 100%, 0% 50%)'
				=> esc_html__( 'Point Left', 'sky-elementor-addons' ),
			'clip-path: polygon(0% 0%, 75% 0%, 100% 50%, 75% 100%, 0% 100%)'
				=> esc_html__( 'Point Right', 'sky-elementor-addons' ),
			'clip-path: polygon(100% 0%, 75% 50%, 100% 100%, 25% 100%, 0% 50%, 25% 0%)'
				=> esc_html__( 'Chevron Left', 'sky-elementor-addons' ),
			'clip-path: polygon(75% 0%, 100% 50%, 75% 100%, 0% 100%, 25% 50%, 0% 0%)'
				=> esc_html__( 'Chevron Right', 'sky-elementor-addons' ),
			// ── Decorative ────────────────────────────────────────────────────────────────────────────────
			'clip-path: polygon(50% 0%, 61% 35%, 98% 35%, 68% 57%, 79% 91%, 50% 70%, 21% 91%, 32% 57%, 2% 35%, 39% 35%)'
				=> esc_html__( 'Star', 'sky-elementor-addons' ),
			'clip-path: polygon(10% 25%, 35% 25%, 35% 0%, 65% 0%, 65% 25%, 90% 25%, 90% 50%, 65% 50%, 65% 100%, 35% 100%, 35% 50%, 10% 50%)'
				=> esc_html__( 'Cross', 'sky-elementor-addons' ),
			'clip-path: polygon(0% 0%, 100% 0%, 100% 75%, 75% 75%, 75% 100%, 50% 75%, 0% 75%)'
				=> esc_html__( 'Message', 'sky-elementor-addons' ),
			'clip-path: polygon(20% 0%, 0% 20%, 30% 50%, 0% 80%, 20% 100%, 50% 70%, 80% 100%, 100% 80%, 70% 50%, 100% 20%, 80% 0%, 50% 30%)'
				=> esc_html__( 'Close', 'sky-elementor-addons' ),
			'clip-path: polygon(0% 0%, 0% 100%, 25% 100%, 25% 25%, 75% 25%, 75% 75%, 25% 75%, 25% 100%, 100% 100%, 100% 0%)'
				=> esc_html__( 'Frame', 'sky-elementor-addons' ),
			// ── Curves (cannot interpolate with polygon shapes) ──────────────────────────────────────────
			'clip-path: circle(50% at 50% 50%)'
				=> esc_html__( 'Circle', 'sky-elementor-addons' ),
			'clip-path: ellipse(25% 40% at 50% 50%)'
				=> esc_html__( 'Ellipse', 'sky-elementor-addons' ),
			// ── Inset ─────────────────────────────────────────────────────────────────────────────────────
			'clip-path: inset(5% 20% 15% 10%)'
				=> esc_html__( 'Inset', 'sky-elementor-addons' ),
		];
	}

	public function register_section( $element ) {
		$element->start_controls_section(
			'section_sky_addons_custom_cp_controls',
			[
				'tab'   => Controls_Manager::TAB_STYLE,
				'label' => esc_html__( 'Custom Clip Path', 'sky-elementor-addons' ) . sky_addons_get_icon(),
			]
		);

		$element->end_controls_section();
	}

	public function register_controls( $widget, $args ) {

		$widget->add_control(
			'sa_custom_cp_enable',
			[
				'label'              => esc_html__( 'Enable', 'sky-elementor-addons' ),
				'type'               => Controls_Manager::SWITCHER,
				'render_type'        => 'template',
				'frontend_available' => true,
			]
		);

		$preset_options = $this->get_preset_shapes();

		$widget->start_controls_tabs(
			'sa_custom_cp_tabs',
			[
				'condition' => [
					'sa_custom_cp_enable' => 'yes',
				],
			]
		);

		// ── Normal Tab ────────────────────────────────────────────────────────────────────────────────────

		$widget->start_controls_tab(
			'sa_custom_cp_normal_tab',
			[
				'label' => esc_html__( 'Normal', 'sky-elementor-addons' ),
			]
		);

		$widget->add_control(
			'sa_custom_cp_mode_normal',
			[
				'label'   => esc_html__( 'Mode', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'custom',
				'options' => [
					'custom' => esc_html__( 'Custom CSS', 'sky-elementor-addons' ),
					'preset' => esc_html__( 'Presets', 'sky-elementor-addons' ),
				],
			]
		);

		$widget->add_control(
			'sa_custom_cp_preset_normal',
			[
				'label'   => esc_html__( 'Shape Preset', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'clip-path: circle(50% at 50% 50%)',
				'options' => $preset_options,
				'selectors' => [
					'{{WRAPPER}} img' => '{{VALUE}};',
				],
				'condition' => [
					'sa_custom_cp_mode_normal' => 'preset',
				],
			]
		);

		$widget->add_control(
			'sa_custom_cp_css_normal',
			[
				'label'       => esc_html__( 'Clip-path', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 3,
				'default'     => 'clip-path: polygon(50% 0%, 90% 20%, 100% 60%, 75% 100%, 25% 100%, 0% 60%, 10% 20%);',
				'placeholder' => esc_html__( 'clip-path: polygon(50% 0%, 90% 20%, 100% 60%, 75% 100%, 25% 100%, 0% 60%, 10% 20%);', 'sky-elementor-addons' ),
				'selectors'   => [
					'{{WRAPPER}} img' => '{{VALUE}};',
				],
				'condition'   => [
					'sa_custom_cp_mode_normal' => 'custom',
				],
			]
		);

		$widget->end_controls_tab();

		// ── Hover Tab ─────────────────────────────────────────────────────────────────────────────────────

		$widget->start_controls_tab(
			'sa_custom_cp_hover_tab',
			[
				'label' => esc_html__( 'Hover', 'sky-elementor-addons' ),
			]
		);

		$widget->add_control(
			'sa_custom_cp_mode_hover',
			[
				'label'   => esc_html__( 'Mode', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'custom',
				'options' => [
					'custom' => esc_html__( 'Custom CSS', 'sky-elementor-addons' ),
					'preset' => esc_html__( 'Presets', 'sky-elementor-addons' ),
				],
			]
		);

		$widget->add_control(
			'sa_custom_cp_preset_hover',
			[
				'label'   => esc_html__( 'Shape Preset', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'clip-path: circle(50% at 50% 50%)',
				'options' => $preset_options,
				'selectors' => [
					'{{WRAPPER}}:hover img' => '{{VALUE}};',
				],
				'condition' => [
					'sa_custom_cp_mode_hover' => 'preset',
				],
			]
		);

		$widget->add_control(
			'sa_custom_cp_css_hover',
			[
				'label'       => esc_html__( 'Clip-path', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 3,
				'placeholder' => esc_html__( 'clip-path: polygon(50% 0%, 90% 20%, 100% 60%, 75% 100%, 25% 100%, 0% 60%, 10% 20%);', 'sky-elementor-addons' ),
				'selectors'   => [
					'{{WRAPPER}}:hover img' => '{{VALUE}};',
				],
				'condition'   => [
					'sa_custom_cp_mode_hover' => 'custom',
				],
			]
		);

		$widget->end_controls_tab();

		$widget->end_controls_tabs();

		// ── Transition ────────────────────────────────────────────────────────────────────────────────────

		$widget->add_control(
			'sa_custom_cp_transition_heading',
			[
				'label'     => esc_html__( 'Transition', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					'sa_custom_cp_enable' => 'yes',
				],
			]
		);

		$widget->add_control(
			'sa_custom_cp_transition',
			[
				'label' => esc_html__( 'Duration (s)', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'max'  => 3,
						'step' => 0.1,
					],
				],
				'selectors' => [
					'{{WRAPPER}}' => '--sky-ccp-transition: {{SIZE}};',
				],
				'condition' => [
					'sa_custom_cp_enable' => 'yes',
				],
			]
		);

		$widget->add_control(
			'sa_custom_cp_transition_type',
			[
				'label'   => esc_html__( 'Easing', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'ease-in-out',
				'options' => [
					'ease'        => esc_html__( 'Ease', 'sky-elementor-addons' ),
					'linear'      => esc_html__( 'Linear', 'sky-elementor-addons' ),
					'ease-in'     => esc_html__( 'Ease In', 'sky-elementor-addons' ),
					'ease-out'    => esc_html__( 'Ease Out', 'sky-elementor-addons' ),
					'ease-in-out' => esc_html__( 'Ease In Out', 'sky-elementor-addons' ),
				],
				'selectors' => [
					'{{WRAPPER}}' => '--sky-ccp-transition-type: {{VALUE}};',
				],
				'condition' => [
					'sa_custom_cp_enable' => 'yes',
				],
			]
		);

		$widget->add_control(
			'sa_custom_cp_transition_delay',
			[
				'label' => esc_html__( 'Delay (s)', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'max'  => 3,
						'step' => 0.1,
					],
				],
				'selectors' => [
					'{{WRAPPER}}' => '--sky-ccp-transition-delay: {{SIZE}};',
				],
				'condition' => [
					'sa_custom_cp_enable' => 'yes',
				],
			]
		);

		$widget->add_control(
			'sa_custom_cp_output',
			[
				'type'        => Controls_Manager::HIDDEN,
				'default'     => '1',
				'selectors'   => [
					'{{WRAPPER}} img' => 'transition: clip-path var(--sky-ccp-transition, 0.3)s var(--sky-ccp-transition-type, ease-in) var(--sky-ccp-transition-delay, 0.1)s;',
				],
				'render_type' => 'template',
				'condition'   => [
					'sa_custom_cp_enable' => 'yes',
				],
			]
		);

		// ── Helper ────────────────────────────────────────────────────────────────────────────────────────

		$widget->add_control(
			'sa_custom_cp_css_link_desc',
			[
				'label'     => sprintf(
					/* translators: %s: Clippy tool URL */
					esc_html__( 'Use %s to generate custom clip-path coordinates.', 'sky-elementor-addons' ),
					'<a href="https://bennettfeely.com/clippy/" target="_blank">Clippy</a>'
				),
				'type'      => Controls_Manager::RAW_HTML,
				'separator' => 'before',
				'condition' => [
					'sa_custom_cp_enable'      => 'yes',
					'sa_custom_cp_mode_normal' => 'custom',
				],
			]
		);
	}

	protected function add_actions() {

		add_action(
			'elementor/element/image/section_style_image/after_section_end',
			[
				$this,
				'register_section',
			]
		);

		add_action(
			'elementor/element/image/section_sky_addons_custom_cp_controls/before_section_end',
			[
				$this,
				'register_controls',
			],
			10,
			2
		);
	}
}

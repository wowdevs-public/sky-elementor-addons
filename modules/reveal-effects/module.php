<?php

namespace Sky_Addons\Modules\RevealEffects;

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
			return 'sky-reveal-effects';
	}

	public function register_section( $element ) {
			$name = $element->get_name();

			$tab = ( 'section' === $name || 'container' === $name )
				? Controls_Manager::TAB_LAYOUT
				: Controls_Manager::TAB_CONTENT;

			$element->start_controls_section(
				'section_sky_addons_reveal_fx_controls',
				[
					'tab'   => $tab,
					'label' => esc_html__( 'Reveal Effects', 'sky-elementor-addons' ) . sky_addons_get_icon(),
				]
			);
			$element->end_controls_section();
	}

	public function register_controls( $widget, $args ) {

			/**
			 * ── Enable ────────────────────────────────────────
			 */
			$widget->add_control(
				'sa_reveal_fx_enable',
				[
					'label'              => esc_html__( 'Enable', 'sky-elementor-addons' ),
					'type'               => Controls_Manager::SWITCHER,
					'render_type'        => 'template',
					'frontend_available' => true,
				]
			);

			/**
			 * ── Animation ─────────────────────────────────────
			 */
			$widget->add_control(
				'sa_reveal_fx_direction',
				[
					'label'              => esc_html__( 'Direction', 'sky-elementor-addons' ),
					'type'               => Controls_Manager::SELECT,
					'default'            => 'lr',
					'options'            => [
						'lr' => esc_html__( 'Left → Right', 'sky-elementor-addons' ),
						'rl' => esc_html__( 'Right → Left', 'sky-elementor-addons' ),
						'tb' => esc_html__( 'Top → Bottom', 'sky-elementor-addons' ),
						'bt' => esc_html__( 'Bottom → Top', 'sky-elementor-addons' ),
					],
					'separator'          => 'before',
					'render_type'        => 'none',
					'frontend_available' => true,
					'condition'          => [
						'sa_reveal_fx_enable' => 'yes',
					],
				]
			);

			$widget->add_control(
				'sa_reveal_fx_duration',
				[
					'label'              => esc_html__( 'Duration (ms)', 'sky-elementor-addons' ),
					'description'        => esc_html__( 'Total time in milliseconds for one reveal phase. Higher values produce a slower animation.', 'sky-elementor-addons' ),
					'type'               => Controls_Manager::SLIDER,
					'range'              => [
						'px' => [
							'min'  => 200,
							'max'  => 5000,
							'step' => 100,
						],
					],
					'default'            => [
						'unit' => 'px',
						'size' => 600,
					],
					'render_type'        => 'none',
					'frontend_available' => true,
					'condition'          => [
						'sa_reveal_fx_enable' => 'yes',
					],
				]
			);

			$widget->add_control(
				'sa_reveal_fx_easing',
				[
					'label'              => esc_html__( 'Easing', 'sky-elementor-addons' ),
					'description'        => esc_html__( 'Controls the acceleration curve of the reveal animation.', 'sky-elementor-addons' ),
					'type'               => Controls_Manager::SELECT,
					'default'            => '',
					'options'            => [
						''                => esc_html__( 'Default (easeInOutQuint)', 'sky-elementor-addons' ),
						'easeInQuart'     => esc_html__( 'easeInQuart', 'sky-elementor-addons' ),
						'easeInQuint'     => esc_html__( 'easeInQuint', 'sky-elementor-addons' ),
						'easeInSine'      => esc_html__( 'easeInSine', 'sky-elementor-addons' ),
						'easeInExpo'      => esc_html__( 'easeInExpo', 'sky-elementor-addons' ),
						'easeInCirc'      => esc_html__( 'easeInCirc', 'sky-elementor-addons' ),
						'easeInBack'      => esc_html__( 'easeInBack', 'sky-elementor-addons' ),
						'easeInBounce'    => esc_html__( 'easeInBounce', 'sky-elementor-addons' ),
						'easeOutQuart'    => esc_html__( 'easeOutQuart', 'sky-elementor-addons' ),
						'easeOutQuint'    => esc_html__( 'easeOutQuint', 'sky-elementor-addons' ),
						'easeOutSine'     => esc_html__( 'easeOutSine', 'sky-elementor-addons' ),
						'easeOutExpo'     => esc_html__( 'easeOutExpo', 'sky-elementor-addons' ),
						'easeOutCirc'     => esc_html__( 'easeOutCirc', 'sky-elementor-addons' ),
						'easeOutBack'     => esc_html__( 'easeOutBack', 'sky-elementor-addons' ),
						'easeOutBounce'   => esc_html__( 'easeOutBounce', 'sky-elementor-addons' ),
						'easeInOutQuart'  => esc_html__( 'easeInOutQuart', 'sky-elementor-addons' ),
						'easeInOutQuint'  => esc_html__( 'easeInOutQuint', 'sky-elementor-addons' ),
						'easeInOutSine'   => esc_html__( 'easeInOutSine', 'sky-elementor-addons' ),
						'easeInOutExpo'   => esc_html__( 'easeInOutExpo', 'sky-elementor-addons' ),
						'easeInOutCirc'   => esc_html__( 'easeInOutCirc', 'sky-elementor-addons' ),
						'easeInOutBack'   => esc_html__( 'easeInOutBack', 'sky-elementor-addons' ),
						'easeInOutBounce' => esc_html__( 'easeInOutBounce', 'sky-elementor-addons' ),
						'easeOutInQuart'  => esc_html__( 'easeOutInQuart', 'sky-elementor-addons' ),
						'easeOutInQuint'  => esc_html__( 'easeOutInQuint', 'sky-elementor-addons' ),
						'easeOutInSine'   => esc_html__( 'easeOutInSine', 'sky-elementor-addons' ),
						'easeOutInExpo'   => esc_html__( 'easeOutInExpo', 'sky-elementor-addons' ),
						'easeOutInCirc'   => esc_html__( 'easeOutInCirc', 'sky-elementor-addons' ),
						'easeOutInBack'   => esc_html__( 'easeOutInBack', 'sky-elementor-addons' ),
						'easeOutInBounce' => esc_html__( 'easeOutInBounce', 'sky-elementor-addons' ),
					],
					'render_type'        => 'none',
					'frontend_available' => true,
					'condition'          => [
						'sa_reveal_fx_enable' => 'yes',
					],
				]
			);

			/**
			 * ── Layers ────────────────────────────────────────
			 */
			$widget->add_control(
				'sa_reveal_fx_layers',
				[
					'label'              => esc_html__( 'Layers', 'sky-elementor-addons' ),
					'description'        => esc_html__( 'Number of colored overlay bands shown during the animation. Use multiple layers for a striped reveal effect.', 'sky-elementor-addons' ),
					'type'               => Controls_Manager::SLIDER,
					'range'              => [
						'px' => [
							'min'  => 1,
							'max'  => 10,
							'step' => 1,
						],
					],
					'default'            => [
						'unit' => 'px',
						'size' => 1,
					],
					'separator'          => 'before',
					'render_type'        => 'none',
					'frontend_available' => true,
					'condition'          => [
						'sa_reveal_fx_enable' => 'yes',
					],
				]
			);

			$widget->add_control(
				'sa_reveal_fx_bg_colors',
				[
					'label'              => esc_html__( 'Layer Colors', 'sky-elementor-addons' ),
					'description'        => esc_html__( 'Comma-separated colors for each layer (e.g. #111, #e74c3c, rgba(0,0,0,0.8)). Add one color per layer. Unmatched layers reuse the last color.', 'sky-elementor-addons' ),
					'type'               => Controls_Manager::TEXTAREA,
					'rows'               => 3,
					'placeholder'        => esc_html__( '#111, #e74c3c, #3498db', 'sky-elementor-addons' ),
					'render_type'        => 'none',
					'frontend_available' => true,
					'condition'          => [
						'sa_reveal_fx_enable' => 'yes',
					],
				]
			);

			$widget->add_control(
				'sa_reveal_fx_delay',
				[
					'label'              => esc_html__( 'Layer Delay (ms)', 'sky-elementor-addons' ),
					'description'        => esc_html__( 'Stagger delay in milliseconds between each layer animation. Only effective when Layers > 1.', 'sky-elementor-addons' ),
					'type'               => Controls_Manager::SLIDER,
					'range'              => [
						'px' => [
							'min'  => 0,
							'max'  => 2000,
							'step' => 50,
						],
					],
					'default'            => [
						'unit' => 'px',
						'size' => 100,
					],
					'render_type'        => 'none',
					'frontend_available' => true,
					'condition'          => [
						'sa_reveal_fx_enable' => 'yes',
					],
				]
			);

			/**
			 * ── Behaviour ─────────────────────────────────────
			 */
			$widget->add_control(
				'sa_reveal_fx_content_show',
				[
					'label'              => esc_html__( 'Show Content During Animation', 'sky-elementor-addons' ),
					'description'        => esc_html__( 'When enabled, the element content remains visible while the overlay animates. When disabled, content appears only after the overlay fully passes.', 'sky-elementor-addons' ),
					'type'               => Controls_Manager::SWITCHER,
					'separator'          => 'before',
					'render_type'        => 'none',
					'frontend_available' => true,
					'condition'          => [
						'sa_reveal_fx_enable' => 'yes',
					],
				]
			);

			$widget->add_control(
				'sa_reveal_fx_cover_area',
				[
					'label'              => esc_html__( 'Cover Area (%)', 'sky-elementor-addons' ),
					'description'        => esc_html__( 'Percentage of the element left covered by the overlay after the animation completes. Set to 0 for a full reveal.', 'sky-elementor-addons' ),
					'type'               => Controls_Manager::NUMBER,
					'min'                => 0,
					'max'                => 100,
					'step'               => 1,
					'default'            => 0,
					'render_type'        => 'none',
					'frontend_available' => true,
					'condition'          => [
						'sa_reveal_fx_enable' => 'yes',
					],
				]
			);

			$widget->add_control(
				'sa_reveal_fx_loop',
				[
					'label'              => esc_html__( 'Replay on Scroll', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
					'description'        => esc_html__( 'When enabled, the reveal animation replays every time the element scrolls back into view.', 'sky-elementor-addons' ),
					'type'               => Controls_Manager::SWITCHER,
					'render_type'        => 'none',
					'frontend_available' => true,
					'condition'          => [
						'sa_reveal_fx_enable' => 'yes',
					],
				]
			);

			$widget->add_control(
				'sa_reveal_fx_threshold',
				[
					'label'              => esc_html__( 'Trigger Threshold (%)', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
					'description'        => esc_html__( 'Percentage of the element that must be visible in the viewport before the animation triggers. Lower values trigger earlier.', 'sky-elementor-addons' ),
					'type'               => Controls_Manager::SLIDER,
					'range'              => [
						'px' => [
							'min'  => 5,
							'max'  => 100,
							'step' => 5,
						],
					],
					'default'            => [
						'unit' => 'px',
						'size' => 80,
					],
					'render_type'        => 'none',
					'frontend_available' => true,
					'condition'          => [
						'sa_reveal_fx_enable' => 'yes',
					],
				]
			);

			/**
			 * ── Targeting ─────────────────────────────────────
			 */
			$widget->add_control(
				'sa_reveal_fx_select_type',
				[
					'label'              => esc_html__( 'Target Selector', 'sky-elementor-addons' ),
					'description'        => esc_html__( 'Choose which element to apply the reveal effect to. Use Custom to target inner items individually (e.g. grid cards, list items).', 'sky-elementor-addons' ),
					'type'               => Controls_Manager::SELECT,
					'default'            => 'default',
					'options'            => [
						'default' => esc_html__( 'Default (Entire Element)', 'sky-elementor-addons' ),
						'custom'  => esc_html__( 'Custom Selector', 'sky-elementor-addons' ),
					],
					'separator'          => 'before',
					'render_type'        => 'none',
					'frontend_available' => true,
					'condition'          => [
						'sa_reveal_fx_enable' => 'yes',
					],
				]
			);

			$sa_reveal_fx_selector = ( sky_addons_init_pro() === true || sky_addons_editor_mode() === true ) ? true : false;

			$widget->add_control(
				'sa_reveal_fx_selector',
				[
					'label'              => esc_html__( 'CSS Selector', 'sky-elementor-addons' ) . sky_addons_control_indicator_pro(),
					'description'        => esc_html__( 'CSS selector for the inner elements to animate individually (e.g. .post-item, .sa-card). Each matched element gets its own reveal animation.', 'sky-elementor-addons' ),
					'type'               => Controls_Manager::TEXT,
					'ai'                 => [ 'active' => false ],
					'render_type'        => 'none',
					'frontend_available' => $sa_reveal_fx_selector,
					'condition'          => [
						'sa_reveal_fx_enable'      => 'yes',
						'sa_reveal_fx_select_type' => 'custom',
					],
				]
			);

			/**
			 * ── Advanced ──────────────────────────────────────
			 */
			$widget->add_control(
				'sa_reveal_fx_z_index',
				[
					'label'       => esc_html__( 'Overlay Z-Index', 'sky-elementor-addons' ),
					'description' => esc_html__( 'Stacking order of the reveal overlay. Increase this value if the overlay appears hidden behind other elements.', 'sky-elementor-addons' ),
					'type'        => Controls_Manager::NUMBER,
					'separator'   => 'before',
					'render_type' => 'none',
					'condition'   => [
						'sa_reveal_fx_enable' => 'yes',
					],
					'selectors'   => [
						'{{WRAPPER}} .block-revealer__element' => 'z-index: {{VALUE}} !important;',
					],
				]
			);
	}

	public function widget_reveal_fx_before_render( $widget ) {
			$settings = $widget->get_settings_for_display();
		if ( isset( $settings['sa_reveal_fx_enable'] ) && 'yes' === $settings['sa_reveal_fx_enable'] ) {
				wp_enqueue_script( 'anime' );
				wp_enqueue_script( 'revealFx' );
				wp_enqueue_script( 'sa-reveal-effects' );
		}
	}

	protected function add_actions() {
			// Widgets — Content tab
			add_action( 'elementor/element/common/_section_style/after_section_end', [ $this, 'register_section' ] );
			add_action( 'elementor/element/common/section_sky_addons_reveal_fx_controls/before_section_end', [ $this, 'register_controls' ], 10, 2 );

			// Sections — Layout tab
			add_action( 'elementor/element/section/section_layout/after_section_end', [ $this, 'register_section' ] );
			add_action( 'elementor/element/section/section_sky_addons_reveal_fx_controls/before_section_end', [ $this, 'register_controls' ], 10, 2 );

			// Containers — Layout tab
			add_action( 'elementor/element/container/section_layout/after_section_end', [ $this, 'register_section' ] );
			add_action( 'elementor/element/container/section_sky_addons_reveal_fx_controls/before_section_end', [ $this, 'register_controls' ], 10, 2 );

			// Script enqueue on frontend render
			add_action( 'elementor/frontend/widget/before_render', [ $this, 'widget_reveal_fx_before_render' ], 10, 1 );
			add_action( 'elementor/frontend/section/before_render', [ $this, 'widget_reveal_fx_before_render' ], 10, 1 );
			add_action( 'elementor/frontend/container/before_render', [ $this, 'widget_reveal_fx_before_render' ], 10, 1 );
	}
}

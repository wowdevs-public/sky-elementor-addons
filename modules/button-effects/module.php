<?php

namespace Sky_Addons\Modules\ButtonEffects;

use Elementor\Controls_Manager;
use Sky_Addons\Base\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Button Effects
 *
 * Adds pure-CSS hover mechanisms and an offset halo ring to Elementor's core Button widget.
 *
 * Every option is a class on the widget wrapper (`prefix_class`) or a CSS custom property
 * (`selectors`), so the editor's JS template renders the effect exactly like the frontend —
 * the core Button has a `content_template()`, so PHP-added render attributes would be
 * invisible while editing. No effect relies on duplicated text or added markup.
 */
class Module extends Module_Base {

	public function __construct() {
		parent::__construct();
		$this->add_actions();
	}

	public function get_name() {
		return 'sky-button-effects';
	}

	public function register_content_section( $element ) {
		$element->start_controls_section(
			'section_sa_btnfx_controls',
			[
				'tab'   => Controls_Manager::TAB_CONTENT,
				'label' => esc_html__( 'Button Effects', 'sky-elementor-addons' ) . sky_addons_get_icon() . sky_addons_label_badge( 'new', '4.0.0' ),
			]
		);
		$element->end_controls_section();
	}

	public function register_content_controls( $widget, $args ) {
		$widget->add_control(
			'sa_btnfx_enable',
			[
				'label'        => esc_html__( 'Enable Button Effects', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'sky-elementor-addons' ),
				'label_off'    => esc_html__( 'No', 'sky-elementor-addons' ),
				'return_value' => 'active',
				'prefix_class' => 'sa-btnfx-',
			]
		);

		$widget->add_control(
			'sa_btnfx_notice',
			[
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => esc_html__( 'Elementor\'s own Hover Animation runs on the same element and will fight this effect. Set it to None.', 'sky-elementor-addons' ),
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
				'condition'       => [
					'sa_btnfx_enable' => 'active',
					'hover_animation!' => '',
				],
			]
		);

		$widget->add_control(
			'sa_btnfx_effect',
			[
				'label'        => esc_html__( 'Effect', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SELECT,
				'default'      => 'sweep',
				'options'      => [
					'none'      => esc_html__( 'None (ring only)', 'sky-elementor-addons' ),
					'sweep'     => esc_html__( 'Sweep Fill', 'sky-elementor-addons' ),
					'skew'      => esc_html__( 'Diagonal Sweep', 'sky-elementor-addons' ),
					'split'     => esc_html__( 'Split Fill', 'sky-elementor-addons' ),
					'border'    => esc_html__( 'Border Draw', 'sky-elementor-addons' ),
					'shine'     => esc_html__( 'Shine Pass', 'sky-elementor-addons' ),
					'radial'    => esc_html__( 'Radial Reveal', 'sky-elementor-addons' ),
					'underline' => esc_html__( 'Underline Grow', 'sky-elementor-addons' ),
					'swap'      => esc_html__( 'Text Swap', 'sky-elementor-addons' ),
					'glitch'    => esc_html__( 'Glitch', 'sky-elementor-addons' ),
					'icon'      => esc_html__( 'Icon Reveal', 'sky-elementor-addons' ),
				],
				'prefix_class' => 'sa-btnfx--',
				'condition'    => [
					'sa_btnfx_enable' => 'active',
				],
			]
		);

		$widget->add_control(
			'sa_btnfx_icon_note',
			[
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => esc_html__( 'Icon Reveal needs an icon set in the Button section above.', 'sky-elementor-addons' ),
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
				'condition'       => [
					'sa_btnfx_enable'    => 'active',
					'sa_btnfx_effect'    => 'icon',
					'selected_icon[value]' => '',
				],
			]
		);

		$widget->add_control(
			'sa_btnfx_direction',
			[
				'label'        => esc_html__( 'Direction', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SELECT,
				'default'      => 'left',
				'options'      => [
					'left'   => esc_html__( 'From Left', 'sky-elementor-addons' ),
					'right'  => esc_html__( 'From Right', 'sky-elementor-addons' ),
					'center' => esc_html__( 'From Center', 'sky-elementor-addons' ),
					'top'    => esc_html__( 'From Top', 'sky-elementor-addons' ),
					'bottom' => esc_html__( 'From Bottom', 'sky-elementor-addons' ),
				],
				'description'  => esc_html__( 'On Underline Grow, From Top moves the line to the top edge and From Bottom keeps it below.', 'sky-elementor-addons' ),
				'prefix_class' => 'sa-btnfx-dir--',
				'condition'    => [
					'sa_btnfx_enable' => 'active',
					'sa_btnfx_effect' => [ 'sweep', 'underline' ],
				],
			]
		);

		$widget->add_control(
			'sa_btnfx_duration',
			[
				'label'      => esc_html__( 'Duration', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'ms' ],
				'default'    => [
					'unit' => 'ms',
					'size' => 400,
				],
				'range'      => [
					'ms' => [
						'min'  => 100,
						'max'  => 2000,
						'step' => 50,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .elementor-button' => '--sa-btnfx-dur: {{SIZE}}ms;',
				],
				'condition'  => [
					'sa_btnfx_enable' => 'active',
				],
			]
		);

	}

	public function register_style_section( $element ) {
		$element->start_controls_section(
			'section_sa_btnfx_style',
			[
				'tab'       => Controls_Manager::TAB_STYLE,
				'label'     => esc_html__( 'Button Effects', 'sky-elementor-addons' ) . sky_addons_get_icon() . sky_addons_label_badge( 'new', '4.0.0' ),
				'condition' => [
					'sa_btnfx_enable' => 'active',
				],
			]
		);
		$element->end_controls_section();
	}

	public function register_style_controls( $widget, $args ) {
		$widget->add_control(
			'sa_btnfx_color',
			[
				// A default is required, not decorative. Seven of the ten effects paint a fill
				// UNDER the button text, so the stylesheet's `currentColor` fallback would match
				// the fill to the text and render it invisible — white on white on a dark button.
				// Clearing the field falls back to currentColor on purpose: that suits the line
				// effects (Border Draw, Underline Grow), which never sit behind the text.
				'label'     => esc_html__( 'Effect Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#F04E23',
				'selectors' => [
					'{{WRAPPER}} .elementor-button' => '--sa-btnfx-c1: {{VALUE}};',
				],
				'condition' => [
					'sa_btnfx_effect!' => 'none',
				],
			]
		);

		$widget->add_control(
			'sa_btnfx_ring',
			[
				'label'        => esc_html__( 'Halo Ring', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'On', 'sky-elementor-addons' ),
				'label_off'    => esc_html__( 'Off', 'sky-elementor-addons' ),
				'return_value' => 'on',
				'separator'    => 'before',
				'prefix_class' => 'sa-btnfx-ring-',
			]
		);

		$widget->add_control(
			'sa_btnfx_ring_shape',
			[
				'label'        => esc_html__( 'Ring Shape', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SELECT,
				'default'      => 'solid',
				'options'      => [
					'solid'   => esc_html__( 'Solid', 'sky-elementor-addons' ),
					'notched' => esc_html__( 'Notched', 'sky-elementor-addons' ),
					'orbit'   => esc_html__( 'Orbit', 'sky-elementor-addons' ),
				],
				'prefix_class' => 'sa-btnfx-ring--',
				'condition'    => [
					'sa_btnfx_ring' => 'on',
				],
			]
		);

		$widget->add_control(
			'sa_btnfx_ring_state',
			[
				'label'        => esc_html__( 'Ring Visibility', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SELECT,
				'default'      => 'hover',
				'options'      => [
					'hover'   => esc_html__( 'On Hover', 'sky-elementor-addons' ),
					'always'  => esc_html__( 'Always', 'sky-elementor-addons' ),
					'retract' => esc_html__( 'Retract on Hover', 'sky-elementor-addons' ),
				],
				'description'  => esc_html__( 'Retract shows a full ring at rest that shrinks into a notch at the top on hover. The notch uses Ring Color (Hover), falling back to the effect colour.', 'sky-elementor-addons' ),
				'prefix_class' => 'sa-btnfx-ring-state--',
				'condition'    => [
					'sa_btnfx_ring' => 'on',
				],
			]
		);

		$widget->add_control(
			'sa_btnfx_ring_color',
			[
				'label'     => esc_html__( 'Ring Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-button' => '--sa-btnfx-ring-c: {{VALUE}};',
				],
				'condition' => [
					'sa_btnfx_ring' => 'on',
				],
			]
		);

		$widget->add_control(
			'sa_btnfx_ring_color_hover',
			[
				// Emitted on the base selector, not `:hover`. The stylesheet does the swap, so
				// Retract on Hover can paint its arc in this colour while the track underneath
				// keeps the rest colour — impossible if `:hover` overwrote the one variable.
				'label'     => esc_html__( 'Ring Color (Hover)', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-button' => '--sa-btnfx-ring-ch: {{VALUE}};',
				],
				'condition' => [
					'sa_btnfx_ring' => 'on',
				],
			]
		);

		$widget->add_responsive_control(
			'sa_btnfx_ring_width',
			[
				'label'      => esc_html__( 'Ring Thickness', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min'  => 1,
						'max'  => 12,
						'step' => 1,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .elementor-button' => '--sa-btnfx-ring-w: {{SIZE}}{{UNIT}};',
				],
				'condition'  => [
					'sa_btnfx_ring' => 'on',
				],
			]
		);

		$widget->add_responsive_control(
			'sa_btnfx_ring_gap',
			[
				'label'      => esc_html__( 'Ring Gap', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 40,
						'step' => 1,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .elementor-button' => '--sa-btnfx-ring-gap: {{SIZE}}{{UNIT}};',
				],
				'condition'  => [
					'sa_btnfx_ring' => 'on',
				],
			]
		);
	}

	/**
	 * Load the stylesheet only on pages that actually use an effect.
	 *
	 * In the editor and preview the Asset Manager registers `sa-button-effects` as a virtual
	 * handle aliased to the combined bundle, so this call is a no-op there — the CSS arrives
	 * with `sky-addons.css` instead.
	 */
	public function enqueue_effect_style( $widget ) {
		if ( 'button' !== $widget->get_name() ) {
			return;
		}

		$settings = $widget->get_settings_for_display();

		if ( empty( $settings['sa_btnfx_enable'] ) || 'active' !== $settings['sa_btnfx_enable'] ) {
			return;
		}

		wp_enqueue_style( 'sa-button-effects' );
	}

	protected function add_actions() {
		add_action( 'elementor/element/button/section_button/after_section_end', [ $this, 'register_content_section' ] );
		add_action( 'elementor/element/button/section_sa_btnfx_controls/before_section_end', [ $this, 'register_content_controls' ], 10, 2 );

		add_action( 'elementor/element/button/section_style/after_section_end', [ $this, 'register_style_section' ] );
		add_action( 'elementor/element/button/section_sa_btnfx_style/before_section_end', [ $this, 'register_style_controls' ], 10, 2 );

		add_action( 'elementor/frontend/widget/before_render', [ $this, 'enqueue_effect_style' ], 10, 1 );
	}
}

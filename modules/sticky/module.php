<?php

namespace Sky_Addons\Modules\Sticky;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Sky_Addons\Base\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sticky extension.
 *
 * Adds a "Sticky" settings section (Layout tab on Containers, Advanced tab on Widgets)
 * plus a "Sticky" style section on the Style tab. A lightweight engine fixes the element
 * and drops a placeholder in its slot (no jump), sticking it Top/Bottom until an optional
 * "Stick Until" element pushes it up. Show-on-Scroll-Up + entrance animation included.
 * Active-state styling (background/padding/border/radius/shadow) is pure CSS via the
 * `.sa-stuck` class. No library. Frontend only (fixing would trap the editor).
 */
class Module extends Module_Base {

	public function __construct() {
		parent::__construct();
		$this->add_actions();
	}

	public function get_name() {
		return 'sky-sticky';
	}

	/* ── Section shells ─────────────────────────────────────────────────── */

	public function register_sections_container( $element ) {
		$this->settings_section( $element, Controls_Manager::TAB_LAYOUT );
		$this->style_section( $element );
	}

	public function register_sections_widget( $element ) {
		$this->settings_section( $element, Controls_Manager::TAB_ADVANCED );
		$this->style_section( $element );
	}

	private function settings_section( $element, $tab ) {
		$element->start_controls_section(
			'section_sky_addons_sticky_controls',
			[
				'tab'   => $tab,
				'label' => esc_html__( 'Sticky', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ) . sky_addons_get_icon(),
			]
		);
		$element->end_controls_section();
	}

	private function style_section( $element ) {
		$element->start_controls_section(
			'section_sky_addons_sticky_style',
			[
				'tab'       => Controls_Manager::TAB_STYLE,
				'label'     => esc_html__( 'Sticky', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ) . sky_addons_get_icon(),
				'condition' => [ 'sa_sticky_enable' => 'yes' ],
			]
		);
		$element->end_controls_section();
	}

	/* ── Settings controls (behaviour) ──────────────────────────────────── */

	public function register_controls( $widget, $args ) {

		$widget->add_control(
			'sa_sticky_enable',
			[
				'label'              => esc_html__( 'Enable', 'sky-elementor-addons' ),
				'type'               => Controls_Manager::SWITCHER,
				'render_type'        => 'template',
				'frontend_available' => true,
			]
		);

		$widget->add_control(
			'sa_sticky_editor_notice',
			[
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => esc_html__( 'Note: Runs on the frontend only (disabled in the editor so you can keep editing). If you use this, avoid Elementor Pro\'s own sticky on the same element to prevent conflicts.', 'sky-elementor-addons' ),
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
				'condition'       => [ 'sa_sticky_enable' => 'yes' ],
			]
		);

		$widget->add_control(
			'sa_sticky_stick_to',
			[
				'label'              => esc_html__( 'Stick To', 'sky-elementor-addons' ),
				'type'               => Controls_Manager::SELECT,
				'default'            => 'top',
				'options'            => [
					'top'    => esc_html__( 'Top', 'sky-elementor-addons' ),
					'bottom' => esc_html__( 'Bottom', 'sky-elementor-addons' ),
				],
				'render_type'        => 'none',
				'frontend_available' => true,
				'condition'          => [ 'sa_sticky_enable' => 'yes' ],
			]
		);

		$widget->add_control(
			'sa_sticky_offset',
			[
				'label'              => esc_html__( 'Offset', 'sky-elementor-addons' ),
				'type'               => Controls_Manager::SLIDER,
				'size_units'         => [ 'px' ],
				'range'              => [
					'px' => [
						'min'  => 0,
						'max'  => 300,
						'step' => 1,
					],
				],
				'default'            => [
					'unit' => 'px',
					'size' => 0,
				],
				'description'        => esc_html__( 'Gap from the edge of the viewport while stuck.', 'sky-elementor-addons' ),
				'render_type'        => 'none',
				'frontend_available' => true,
				'condition'          => [ 'sa_sticky_enable' => 'yes' ],
			]
		);

		$widget->add_control(
			'sa_sticky_until',
			[
				'label'              => esc_html__( 'Stick Until', 'sky-elementor-addons' ),
				'type'               => Controls_Manager::TEXT,
				'ai'                 => [ 'active' => false ],
				'default'            => '',
				'placeholder'        => 'footer',
				'description'        => esc_html__( 'ID of the element where it unsticks — just the ID, no # (e.g. footer). Leave empty to stick to the bottom of the page.', 'sky-elementor-addons' ),
				'render_type'        => 'none',
				'frontend_available' => true,
				'condition'          => [
					'sa_sticky_enable'   => 'yes',
					'sa_sticky_stick_to' => 'top',
				],
			]
		);

		$widget->add_control(
			'sa_sticky_scroll_up',
			[
				'label'              => esc_html__( 'Show on Scroll Up', 'sky-elementor-addons' ),
				'type'               => Controls_Manager::SWITCHER,
				'default'            => 'no',
				'description'        => esc_html__( 'Hide the sticky element while scrolling down, slide it back in when scrolling up.', 'sky-elementor-addons' ),
				'render_type'        => 'none',
				'frontend_available' => true,
				'condition'          => [ 'sa_sticky_enable' => 'yes' ],
			]
		);

		$widget->add_control(
			'sa_sticky_anim',
			[
				'label'              => esc_html__( 'Entrance Animation', 'sky-elementor-addons' ),
				'type'               => Controls_Manager::SELECT,
				'default'            => 'none',
				'options'            => [
					'none'  => esc_html__( 'None', 'sky-elementor-addons' ),
					'slide' => esc_html__( 'Slide In', 'sky-elementor-addons' ),
					'fade'  => esc_html__( 'Fade In', 'sky-elementor-addons' ),
				],
				'description'        => esc_html__( 'Plays each time the element becomes stuck.', 'sky-elementor-addons' ),
				'render_type'        => 'none',
				'frontend_available' => true,
				'condition'          => [ 'sa_sticky_enable' => 'yes' ],
			]
		);

		$widget->add_control(
			'sa_sticky_zindex',
			[
				'label'              => esc_html__( 'Z-Index', 'sky-elementor-addons' ),
				'type'               => Controls_Manager::NUMBER,
				'default'            => 99,
				'separator'          => 'before',
				'description'        => esc_html__( 'Stacking order while stuck — raise it if the sticky element slips behind other elements.', 'sky-elementor-addons' ),
				'render_type'        => 'none',
				'frontend_available' => true,
				'condition'          => [ 'sa_sticky_enable' => 'yes' ],
			]
		);

		$widget->add_control(
			'sa_sticky_disable_tablet',
			[
				'label'              => esc_html__( 'Disable on Tablet', 'sky-elementor-addons' ),
				'type'               => Controls_Manager::SWITCHER,
				'default'            => 'no',
				'render_type'        => 'none',
				'frontend_available' => true,
				'condition'          => [ 'sa_sticky_enable' => 'yes' ],
			]
		);

		$widget->add_control(
			'sa_sticky_disable_mobile',
			[
				'label'              => esc_html__( 'Disable on Mobile', 'sky-elementor-addons' ),
				'type'               => Controls_Manager::SWITCHER,
				'default'            => 'no',
				'render_type'        => 'none',
				'frontend_available' => true,
				'condition'          => [ 'sa_sticky_enable' => 'yes' ],
			]
		);
	}

	/* ── Style controls (active state, while stuck — pure CSS) ───────────── */

	public function register_style_controls( $widget, $args ) {

		$widget->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'sa_sticky_active_bg',
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}}.sa-stuck',
			]
		);

		$widget->add_responsive_control(
			'sa_sticky_active_padding',
			[
				'label'      => esc_html__( 'Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'separator'  => 'before',
				'selectors'  => [
					'{{WRAPPER}}.sa-stuck' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$widget->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'sa_sticky_active_border',
				'selector' => '{{WRAPPER}}.sa-stuck',
			]
		);

		$widget->add_responsive_control(
			'sa_sticky_active_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}}.sa-stuck' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$widget->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'sa_sticky_active_shadow',
				'selector' => '{{WRAPPER}}.sa-stuck',
			]
		);
	}

	public function sticky_before_render( $element ) {
		$settings = $element->get_settings_for_display();
		if ( 'yes' === ( $settings['sa_sticky_enable'] ?? '' ) ) {
			wp_enqueue_script( 'sa-sticky' );
		}
	}

	protected function add_actions() {
		// Containers — settings on Layout tab, style on Style tab.
		add_action( 'elementor/element/container/section_layout/after_section_end', [ $this, 'register_sections_container' ] );
		add_action( 'elementor/element/container/section_sky_addons_sticky_controls/before_section_end', [ $this, 'register_controls' ], 10, 2 );
		add_action( 'elementor/element/container/section_sky_addons_sticky_style/before_section_end', [ $this, 'register_style_controls' ], 10, 2 );
		add_action( 'elementor/frontend/container/before_render', [ $this, 'sticky_before_render' ], 10, 1 );

		// All widgets — settings on Advanced tab, style on Style tab.
		add_action( 'elementor/element/common/_section_style/after_section_end', [ $this, 'register_sections_widget' ] );
		add_action( 'elementor/element/common/section_sky_addons_sticky_controls/before_section_end', [ $this, 'register_controls' ], 10, 2 );
		add_action( 'elementor/element/common/section_sky_addons_sticky_style/before_section_end', [ $this, 'register_style_controls' ], 10, 2 );
		add_action( 'elementor/frontend/widget/before_render', [ $this, 'sticky_before_render' ], 10, 1 );
	}
}

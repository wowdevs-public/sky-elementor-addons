<?php

namespace Sky_Addons\Modules\TableOfContents\Widgets;

use Elementor\Controls_Manager;
use Elementor\Widget_Base;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Text_Shadow;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Table_Of_Contents extends Widget_Base {

	public function get_name() {
		return 'sky-table-of-contents';
	}

	public function get_title() {
		return esc_html__( 'Table Of Contents', 'sky-elementor-addons' );
	}

	public function get_icon() {
		return 'sky-icon-table-of-contents';
	}

	public function get_categories() {
		return [ 'sky-elementor-addons' ];
	}

	public function get_keywords() {
		return [ 'sky', 'table-of-contents', 'toc', 'contents' ];
	}

	public function get_style_depends() {
		if ( sky_addons_editor_mode() ) {
			return [ 'sky-addons-styles' ];
		}

		return [ 'sa-table-of-contents' ];
	}

	public function get_script_depends() {
		if ( sky_addons_editor_mode() ) {
			return [ 'tocbot', 'sky-addons-scripts' ];
		}

		return [ 'tocbot', 'sa-table-of-contents' ];
	}

	public function is_reload_preview_required() {
		return true;
	}

	public function has_widget_inner_wrapper(): bool {
		return ! \Elementor\Plugin::$instance->experiments->is_feature_active( 'e_optimized_markup' );
	}

	protected function register_controls() {

		// =====================================================================
		// CONTENT TAB
		// =====================================================================

		$this->start_controls_section(
			'section_layout',
			[
				'label' => esc_html__( 'Table Of Contents', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'select_layout',
			[
				'label'       => esc_html__( 'Style', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'minimal',
				'render_type' => 'template',
				'options'     => [
					'minimal'   => esc_html__( 'Minimal', 'sky-elementor-addons' ),
					'accordion' => esc_html__( 'Accordion', 'sky-elementor-addons' ),
					'timeline'  => esc_html__( 'Timeline', 'sky-elementor-addons' ),
				],
			]
		);

		$this->add_control(
			'parent_selector',
			[
				'label'       => esc_html__( 'Container Selector', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => false,
				'default'     => '.elementor',
				'placeholder' => '.elementor',
				'separator'   => 'before',
			]
		);

		$this->add_control(
			'heading_selectors',
			[
				'label'    => esc_html__( 'Heading Tags', 'sky-elementor-addons' ),
				'type'     => Controls_Manager::SELECT2,
				'multiple' => true,
				'default'  => [ 'h2', 'h3', 'h4' ],
				'options'  => sky_addons_title_tags(),
			]
		);

		$this->add_control(
			'ignore_selector',
			[
				'label'       => esc_html__( 'Ignore Selector', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => false,
				'placeholder' => '.js-toc-ignore',
				'description' => esc_html__( 'Headings matching this CSS selector will be skipped.', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'parent_selector_note',
			[
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => esc_html__( 'Note: Class or ID of container or post main area. Example: .elementor, #post', 'sky-elementor-addons' ),
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
			]
		);

		$this->end_controls_section();

		// ---- Behavior ----

		$this->start_controls_section(
			'section_behavior',
			[
				'label' => esc_html__( 'Behavior', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'ordered_list',
			[
				'label'       => esc_html__( 'Ordered List', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::SWITCHER,
				'description' => esc_html__( 'Use <ol> instead of <ul> for list items.', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'collapse_depth',
			[
				'label'       => esc_html__( 'Collapse Depth', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 1,
				'min'         => 0,
				'max'         => 6,
				'description' => esc_html__( 'Heading levels auto-expanded on scroll. 0 = all collapsed, 6 = all visible. Use 1 for accordion-style auto-open.', 'sky-elementor-addons' ),
				'separator'   => 'before',
			]
		);

		$this->add_control(
			'expand_on_hover',
			[
				'label'        => esc_html__( 'Expand On Hover', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'prefix_class' => 'sa-toc-hover-',
				'description'  => esc_html__( 'Auto-expand parent items when hovered.', 'sky-elementor-addons' ),
				'separator'    => 'before',
			]
		);

		$this->add_control(
			'has_inner_containers',
			[
				'label'       => esc_html__( 'Has Inner Containers', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::SWITCHER,
				'default'     => 'yes',
				'description' => esc_html__( 'Enable for headings inside relative or absolute positioned containers (required for Elementor Flexbox Containers).', 'sky-elementor-addons' ),
				'separator'   => 'before',
			]
		);

		$this->add_control(
			'include_html',
			[
				'label'       => esc_html__( 'Include HTML', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::SWITCHER,
				'description' => esc_html__( 'Include HTML markup from the heading instead of plain text.', 'sky-elementor-addons' ),
				'separator'   => 'before',
			]
		);

		$this->add_control(
			'include_title_tags',
			[
				'label'       => esc_html__( 'Include Title Tags', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::SWITCHER,
				'description' => esc_html__( 'Set the HTML title attribute on links to match the heading text.', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'ignore_hidden_elements',
			[
				'label'       => esc_html__( 'Ignore Hidden Elements', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::SWITCHER,
				'description' => esc_html__( 'Skip headings that are hidden in the DOM.', 'sky-elementor-addons' ),
			]
		);

		$this->end_controls_section();

		// ---- Scroll ----

		$this->start_controls_section(
			'section_scroll',
			[
				'label' => esc_html__( 'Scroll', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'scroll_smooth',
			[
				'label'   => esc_html__( 'Smooth Scroll', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'animate_time',
			[
				'label'     => esc_html__( 'Duration (ms)', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 420,
				'condition' => [ 'scroll_smooth' => 'yes' ],
			]
		);

		$this->add_control(
			'scroll_smooth_offset',
			[
				'label'       => esc_html__( 'Scroll Offset (px)', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 0,
				'description' => esc_html__( 'Adjust final scroll position. Use negative values for fixed headers (e.g. −80).', 'sky-elementor-addons' ),
				'condition'   => [ 'scroll_smooth' => 'yes' ],
			]
		);

		$this->add_control(
			'headings_offset',
			[
				'label'       => esc_html__( 'Headings Offset (px)', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 100,
				'description' => esc_html__( 'Offset from viewport top used for active link detection. Increase if active link switches too eagerly. Match your sticky header height.', 'sky-elementor-addons' ),
				'separator'   => 'before',
			]
		);

		$this->add_control(
			'enable_url_hash',
			[
				'label'       => esc_html__( 'Update URL Hash', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::SWITCHER,
				'description' => esc_html__( 'Update the URL hash as the user scrolls to each heading.', 'sky-elementor-addons' ),
				'separator'   => 'before',
			]
		);

		$this->add_control(
			'scroll_handler_type',
			[
				'label'     => esc_html__( 'Scroll Handler', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'auto',
				'options'   => [
					'auto'     => esc_html__( 'Auto', 'sky-elementor-addons' ),
					'debounce' => esc_html__( 'Debounce', 'sky-elementor-addons' ),
					'throttle' => esc_html__( 'Throttle', 'sky-elementor-addons' ),
				],
				'separator' => 'before',
			]
		);

		$this->end_controls_section();

		// ---- Fixed Header ----

		$this->start_controls_section(
			'section_fixed_header',
			[
				'label' => esc_html__( 'Fixed Header', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'position_fixed_selector',
			[
				'label'       => esc_html__( 'Fixed Element Selector', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => false,
				'placeholder' => '.site-header',
				'description' => esc_html__( 'CSS selector for the element that receives the fixed position class on scroll.', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'fixed_sidebar_offset',
			[
				'label'       => esc_html__( 'Fixed Sidebar Offset', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => 'auto',
				'label_block' => false,
				'description' => esc_html__( 'Pixel value or "auto" (uses the sidebar\'s offsetTop on init).', 'sky-elementor-addons' ),
			]
		);

		$this->end_controls_section();

		// ---- Additional ----

		$this->start_controls_section(
			'section_additional',
			[
				'label' => esc_html__( 'Additional', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'show_marker',
			[
				'label'        => esc_html__( 'Show Marker', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'prefix_class' => 'sa-toc-marker-',
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'marker_style',
			[
				'label'     => esc_html__( 'Marker Style', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'disc',
				'options'   => [
					'disc'        => esc_html__( 'Disc', 'sky-elementor-addons' ),
					'circle'      => esc_html__( 'Circle', 'sky-elementor-addons' ),
					'square'      => esc_html__( 'Square', 'sky-elementor-addons' ),
					'decimal'     => esc_html__( 'Decimal', 'sky-elementor-addons' ),
					'lower-alpha' => esc_html__( 'Lower Alpha', 'sky-elementor-addons' ),
					'upper-alpha' => esc_html__( 'Upper Alpha', 'sky-elementor-addons' ),
					'lower-roman' => esc_html__( 'Lower Roman', 'sky-elementor-addons' ),
					'upper-roman' => esc_html__( 'Upper Roman', 'sky-elementor-addons' ),
				],
				'selectors' => [
					'{{WRAPPER}}.sa-toc-marker-yes' => '--toc-marker-type: {{VALUE}};',
				],
				'condition' => [ 'show_marker' => 'yes' ],
			]
		);

		$this->end_controls_section();

		// ---- Sticky ----

		$this->start_controls_section(
			'section_sticky',
			[
				'label' => esc_html__( 'Sticky', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'sticky_desktop',
			[
				'label'        => esc_html__( 'Sticky on Desktop', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'prefix_class' => 'sa-toc-sticky-desktop-',
			]
		);

		$this->add_control(
			'sticky_tablet',
			[
				'label'        => esc_html__( 'Sticky on Tablet', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'prefix_class' => 'sa-toc-sticky-tablet-',
			]
		);

		$this->add_control(
			'sticky_mobile',
			[
				'label'        => esc_html__( 'Sticky on Mobile', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'prefix_class' => 'sa-toc-sticky-mobile-',
			]
		);

		$this->add_responsive_control(
			'sticky_offset',
			[
				'label'      => esc_html__( 'Top Offset', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'vh' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 500,
					],
					'em' => [
						'min'  => 0,
						'max'  => 30,
						'step' => 0.1,
					],
					'vh' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default'    => [
					'size' => 0,
					'unit' => 'px',
				],
				'selectors'  => [
					'{{WRAPPER}}' => '--sa-toc-sticky-offset: {{SIZE}}{{UNIT}};',
				],
				'separator'  => 'before',
			]
		);

		$this->add_control(
			'sticky_z_index',
			[
				'label'   => esc_html__( 'Z-Index', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 10,
				'min'     => 0,
				'max'     => 9999,
				'selectors' => [
					'{{WRAPPER}}' => '--sa-toc-sticky-z: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'sticky_note',
			[
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => esc_html__( 'Note: parent column / container must not have overflow: hidden/auto for sticky to work. The element sticks within its parent\'s height.', 'sky-elementor-addons' ),
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
			]
		);

		$this->end_controls_section();

		// =====================================================================
		// STYLE TAB
		// =====================================================================

		// ---- Container ----

		$this->start_controls_section(
			'style_container',
			[
				'label' => esc_html__( 'Container', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'container_background',
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .sa-toc',
			]
		);

		$this->add_responsive_control(
			'container_padding',
			[
				'label'      => esc_html__( 'Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-toc' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'container_border',
				'selector' => '{{WRAPPER}} .sa-toc',
			]
		);

		$this->add_responsive_control(
			'container_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-toc' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'container_box_shadow',
				'selector' => '{{WRAPPER}} .sa-toc',
			]
		);

		$this->end_controls_section();

		// ---- Links ----

		$this->start_controls_section(
			'style_links',
			[
				'label' => esc_html__( 'Links', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'links_typography',
				'selector' => '{{WRAPPER}} .sa-toc-link',
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'links_text_shadow',
				'selector' => '{{WRAPPER}} .sa-toc-link',
			]
		);

		$this->add_responsive_control(
			'links_padding',
			[
				'label'      => esc_html__( 'Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-toc-link' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'items_row_gap',
			[
				'label'      => esc_html__( 'Items Row Gap', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 24,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-toc-item:not(:last-child)' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'nested_indent',
			[
				'label'      => esc_html__( 'Nested Indent', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 60,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-toc-list .sa-toc-list' => 'padding-left: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'      => 'links_border',
				'selector'  => '{{WRAPPER}} .sa-toc-link',
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'links_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-toc-link' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition'  => [ 'select_layout!' => 'minimal' ],
			]
		);

		$this->start_controls_tabs( 'style_links_tabs' );

		// Normal

		$this->start_controls_tab(
			'style_links_normal_tab',
			[ 'label' => esc_html__( 'Normal', 'sky-elementor-addons' ) ]
		);

		$this->add_control(
			'links_color',
			[
				'label' => esc_html__( 'Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-toc-link' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'links_background',
			[
				'label' => esc_html__( 'Background', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-toc-link' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'links_box_shadow',
				'selector' => '{{WRAPPER}} .sa-toc-link',
			]
		);

		$this->end_controls_tab();

		// Hover

		$this->start_controls_tab(
			'style_links_hover_tab',
			[ 'label' => esc_html__( 'Hover', 'sky-elementor-addons' ) ]
		);

		$this->add_control(
			'links_color_hover',
			[
				'label' => esc_html__( 'Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-toc-link:hover, {{WRAPPER}} .sa-toc-link:focus' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'links_background_hover',
			[
				'label' => esc_html__( 'Background', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-toc-link:hover, {{WRAPPER}} .sa-toc-link:focus' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'links_border_color_hover',
			[
				'label'     => esc_html__( 'Border Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-toc-link:hover, {{WRAPPER}} .sa-toc-link:focus' => 'border-color: {{VALUE}};',
				],
				'condition' => [ 'links_border_border!' => '' ],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'links_box_shadow_hover',
				'selector' => '{{WRAPPER}} .sa-toc-link:hover, {{WRAPPER}} .sa-toc-link:focus',
			]
		);

		$this->end_controls_tab();

		// Active

		$this->start_controls_tab(
			'style_links_active_tab',
			[ 'label' => esc_html__( 'Active', 'sky-elementor-addons' ) ]
		);

		$this->add_control(
			'links_color_active',
			[
				'label' => esc_html__( 'Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .is-active-link' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'links_background_active',
			[
				'label' => esc_html__( 'Background', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .is-active-link' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'links_border_color_active',
			[
				'label'     => esc_html__( 'Border Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .is-active-link' => 'border-color: {{VALUE}};',
				],
				'condition' => [ 'links_border_border!' => '' ],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'links_box_shadow_active',
				'selector' => '{{WRAPPER}} .is-active-link',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		// ---- Minimal ----

		$this->start_controls_section(
			'style_minimal',
			[
				'label'     => esc_html__( 'Minimal', 'sky-elementor-addons' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [ 'select_layout' => 'minimal' ],
			]
		);

		$this->add_control(
			'heading_minimal_bar',
			[
				'type'  => Controls_Manager::HEADING,
				'label' => esc_html__( 'Left Bar', 'sky-elementor-addons' ),
			]
		);

		$this->start_controls_tabs( 'style_minimal_bar_tabs' );

		// Normal

		$this->start_controls_tab(
			'style_minimal_bar_normal_tab',
			[ 'label' => esc_html__( 'Normal', 'sky-elementor-addons' ) ]
		);

		$this->add_control(
			'minimal_bar_color',
			[
				'label' => esc_html__( 'Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-toc--minimal .sa-toc-link' => 'border-left-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'minimal_bar_width',
			[
				'label'      => esc_html__( 'Width', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 1,
						'max' => 8,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-toc--minimal .sa-toc-link' => 'border-left-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_tab();

		// Hover

		$this->start_controls_tab(
			'style_minimal_bar_hover_tab',
			[ 'label' => esc_html__( 'Hover', 'sky-elementor-addons' ) ]
		);

		$this->add_control(
			'minimal_bar_color_hover',
			[
				'label' => esc_html__( 'Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-toc--minimal .sa-toc-link:hover' => 'border-left-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		// Active

		$this->start_controls_tab(
			'style_minimal_bar_active_tab',
			[ 'label' => esc_html__( 'Active', 'sky-elementor-addons' ) ]
		);

		$this->add_control(
			'minimal_bar_color_active',
			[
				'label' => esc_html__( 'Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-toc--minimal .is-active-link' => 'border-left-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'minimal_bar_width_active',
			[
				'label'      => esc_html__( 'Width', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 1,
						'max' => 8,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-toc--minimal .is-active-link' => 'border-left-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		// ---- Accordion ----

		$this->start_controls_section(
			'style_accordion',
			[
				'label'     => esc_html__( 'Accordion', 'sky-elementor-addons' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [ 'select_layout' => 'accordion' ],
			]
		);

		$this->add_control(
			'heading_accordion_links',
			[
				'type'  => Controls_Manager::HEADING,
				'label' => esc_html__( 'Links', 'sky-elementor-addons' ),
			]
		);

		$this->add_responsive_control(
			'links_alignment',
			[
				'label' => esc_html__( 'Alignment', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::CHOOSE,
				'options' => [
					'flex-start'    => [
						'title' => esc_html__( 'Start', 'sky-elementor-addons' ),
						'icon'  => 'eicon-align-start-h',
					],
					'center'        => [
						'title' => esc_html__( 'Center', 'sky-elementor-addons' ),
						'icon'  => 'eicon-align-center-h',
					],
					'flex-end'      => [
						'title' => esc_html__( 'End', 'sky-elementor-addons' ),
						'icon'  => 'eicon-align-end-h',
					],
					'space-between' => [
						'title' => esc_html__( 'Space Between', 'sky-elementor-addons' ),
						'icon'  => 'eicon-h-align-stretch',
					],
				],
				'selectors' => [
					'{{WRAPPER}} .sa-toc--accordion .sa-toc-link' => 'justify-content: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'links_gap',
			[
				'label'      => esc_html__( 'Gap', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 60,
					],
					'em' => [
						'min'  => 0,
						'max'  => 4,
						'step' => 0.1,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-toc--accordion .sa-toc-link' => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'heading_accordion_nav',
			[
				'type'      => Controls_Manager::HEADING,
				'label'     => esc_html__( 'Nav Container', 'sky-elementor-addons' ),
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'accordion_nav_background',
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .sa-toc--accordion .sa-toc-nav',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'accordion_nav_border',
				'selector' => '{{WRAPPER}} .sa-toc--accordion .sa-toc-nav',
			]
		);

		$this->add_responsive_control(
			'accordion_nav_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-toc--accordion .sa-toc-nav' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow: hidden;',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'accordion_nav_box_shadow',
				'selector' => '{{WRAPPER}} .sa-toc--accordion .sa-toc-nav',
			]
		);

		$this->add_control(
			'heading_accordion_nested',
			[
				'type'      => Controls_Manager::HEADING,
				'label'     => esc_html__( 'Nested Rows', 'sky-elementor-addons' ),
				'separator' => 'before',
			]
		);

		$this->add_control(
			'accordion_nested_background',
			[
				'label' => esc_html__( 'Background', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-toc--accordion .sa-toc-list .sa-toc-list' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'heading_accordion_chevron',
			[
				'type'      => Controls_Manager::HEADING,
				'label'     => esc_html__( 'Chevron', 'sky-elementor-addons' ),
				'separator' => 'before',
			]
		);

		$this->add_control(
			'accordion_chevron_color',
			[
				'label' => esc_html__( 'Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-toc--accordion .sa-toc-item:has(>.is-collapsible)>.sa-toc-link::after' => 'border-left-color: {{VALUE}}; border-bottom-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'accordion_chevron_color_hover',
			[
				'label' => esc_html__( 'Hover Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-toc--accordion .sa-toc-item:has(>.is-collapsible)>.sa-toc-link:hover::after'                  => 'border-left-color: {{VALUE}}; border-bottom-color: {{VALUE}};',
					'{{WRAPPER}} .sa-toc--accordion .sa-toc-item:has(>.is-collapsible:not(.is-collapsed))>.sa-toc-link::after' => 'border-left-color: {{VALUE}}; border-bottom-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		// ---- Timeline ----

		$this->start_controls_section(
			'style_timeline',
			[
				'label'     => esc_html__( 'Timeline', 'sky-elementor-addons' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [ 'select_layout' => 'timeline' ],
			]
		);

		$this->add_control(
			'heading_timeline_track',
			[
				'type'  => Controls_Manager::HEADING,
				'label' => esc_html__( 'Track', 'sky-elementor-addons' ),
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'timeline_track_background',
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .sa-toc--timeline::before',
			]
		);

		$this->add_control(
			'heading_timeline_dots',
			[
				'type'      => Controls_Manager::HEADING,
				'label'     => esc_html__( 'Dots', 'sky-elementor-addons' ),
				'separator' => 'before',
			]
		);

		$this->add_control(
			'timeline_dot_bg_color',
			[
				'label' => esc_html__( 'Background', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-toc--timeline .sa-toc-link::before' => 'background: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'timeline_dot_border_color',
			[
				'label' => esc_html__( 'Border Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-toc--timeline .sa-toc-link::before' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'heading_timeline_active_dot',
			[
				'type'      => Controls_Manager::HEADING,
				'label'     => esc_html__( 'Active Dot', 'sky-elementor-addons' ),
				'separator' => 'before',
			]
		);

		$this->add_control(
			'timeline_dot_color_active',
			[
				'label' => esc_html__( 'Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-toc--timeline .is-active-link::before'                                                                       => 'background: {{VALUE}}; border-color: {{VALUE}};',
					'{{WRAPPER}} .sa-toc--timeline .sa-toc-item:has(.is-active-link)>.sa-toc-link:not(.is-active-link)::before' => 'background: {{VALUE}}; border-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		// ---- Marker ----

		$this->start_controls_section(
			'style_marker',
			[
				'label'     => esc_html__( 'Marker', 'sky-elementor-addons' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [ 'show_marker' => 'yes' ],
			]
		);

		$this->add_responsive_control(
			'marker_size',
			[
				'label'      => esc_html__( 'Size', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range'      => [
					'px' => [
						'min' => 8,
						'max' => 32,
					],
					'em' => [
						'min'  => 0.5,
						'max'  => 2,
						'step' => 0.1,
					],
				],
				'selectors'  => [
					'{{WRAPPER}}.sa-toc-marker-yes .sa-toc-link::before' => 'font-size: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'marker_spacing',
			[
				'label'      => esc_html__( 'Spacing', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 32,
					],
					'em' => [
						'min'  => 0,
						'max'  => 2,
						'step' => 0.1,
					],
				],
				'selectors'  => [
					'{{WRAPPER}}.sa-toc-marker-yes .sa-toc-link::before' => 'margin-right: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'marker_color',
			[
				'label' => esc_html__( 'Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}.sa-toc-marker-yes .sa-toc-link::before' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings  = $this->get_settings_for_display();
		$widget_id = $this->get_id();
		$nav_id    = 'sa-toc-nav-' . $widget_id;
		$layout    = ! empty( $settings['select_layout'] ) ? $settings['select_layout'] : 'minimal';

		$heading_selectors = ! empty( $settings['heading_selectors'] )
			? implode( ', ', $settings['heading_selectors'] )
			: 'h2, h3, h4';

		$fixed_sidebar_offset_raw = isset( $settings['fixed_sidebar_offset'] ) ? trim( $settings['fixed_sidebar_offset'] ) : 'auto';
		$fixed_sidebar_offset     = ( '' === $fixed_sidebar_offset_raw || 'auto' === $fixed_sidebar_offset_raw )
			? 'auto'
			: (int) $fixed_sidebar_offset_raw;

		$toc_settings = [
			'tocSelector'                 => '#' . $nav_id,
			'contentSelector'             => ! empty( $settings['parent_selector'] ) ? $settings['parent_selector'] : '.elementor',
			'headingSelector'             => $heading_selectors,
			'hasInnerContainers'          => 'yes' === ( $settings['has_inner_containers'] ?? '' ),
			'orderedList'                 => 'yes' === ( $settings['ordered_list'] ?? '' ),
			'collapseDepth'               => isset( $settings['collapse_depth'] ) && '' !== $settings['collapse_depth'] ? (int) $settings['collapse_depth'] : 1,
			'includeHtml'                 => 'yes' === ( $settings['include_html'] ?? '' ),
			'includeTitleTags'            => 'yes' === ( $settings['include_title_tags'] ?? '' ),
			'ignoreHiddenElements'        => 'yes' === ( $settings['ignore_hidden_elements'] ?? '' ),
			'scrollSmooth'                => 'yes' === ( $settings['scroll_smooth'] ?? 'yes' ),
			'scrollSmoothDuration'        => ! empty( $settings['animate_time'] ) ? (int) $settings['animate_time'] : 420,
			'scrollSmoothOffset'          => isset( $settings['scroll_smooth_offset'] ) && '' !== $settings['scroll_smooth_offset'] ? (int) $settings['scroll_smooth_offset'] : 0,
			'headingsOffset'              => isset( $settings['headings_offset'] ) && '' !== $settings['headings_offset'] ? (int) $settings['headings_offset'] : 100,
			'enableUrlHashUpdateOnScroll' => 'yes' === ( $settings['enable_url_hash'] ?? '' ),
			'scrollHandlerType'           => ! empty( $settings['scroll_handler_type'] ) ? $settings['scroll_handler_type'] : 'auto',
			'fixedSidebarOffset'          => $fixed_sidebar_offset,
			'listClass'                   => 'sa-toc-list',
			'listItemClass'               => 'sa-toc-item',
			'linkClass'                   => 'sa-toc-link',
			'activeLinkClass'             => 'is-active-link',
			'activeListItemClass'         => 'is-active-li',
			'isCollapsedClass'            => 'is-collapsed',
			'collapsibleClass'            => 'is-collapsible',
		];

		if ( ! empty( $settings['ignore_selector'] ) ) {
			$toc_settings['ignoreSelector'] = $settings['ignore_selector'];
		}

		if ( ! empty( $settings['position_fixed_selector'] ) ) {
			$toc_settings['positionFixedSelector'] = $settings['position_fixed_selector'];
		}

		$this->add_render_attribute( 'wrapper', [
			'class'         => [ 'sa-toc', 'sa-toc--' . esc_attr( $layout ) ],
			'data-settings' => wp_json_encode( $toc_settings ),
		] );

		?>
		<div <?php $this->print_render_attribute_string( 'wrapper' ); ?>>
			<div class="sa-toc-nav" id="<?php echo esc_attr( $nav_id ); ?>"></div>
		</div>
		<?php
	}
}

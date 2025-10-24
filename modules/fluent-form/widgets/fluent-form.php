<?php

namespace Sky_Addons\Modules\FluentForm\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class FluentForm extends Widget_Base {

	public function get_name() {
		return 'sky-fluent-form';
	}

	public function get_title() {
		return __( 'Fluent Form', 'sky-elementor-addons' );
	}

	public function get_icon() {
		return 'sky-icon-fluent-form';
	}

	public function get_categories() {
		return [ 'sky-elementor-addons' ];
	}

	public function get_keywords() {
		return [ 'sky', 'fluent form', 'contact form', 'form builder', 'elementor form', 'feedback', 'survey', 'sky addons' ];
	}

	public function get_custom_help_url() {
		return 'https://skyaddons.com/docs/sky-addons/forms/fluent-form/';
	}

	public function is_reload_preview_required() {
		return true;
	}

	protected function register_controls() {

		$this->start_controls_section(
			'_section_fluent_form',
			[
				'label' => sky_addons_is_fluent_form_activated() ? __( 'Fluent Form', 'sky-elementor-addons' ) : __( 'Missing Notice', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		if ( ! sky_addons_is_fluent_form_activated() ) {

			$this->add_control(
				'_fluent_form_missing_notice',
				[
					'type'            => Controls_Manager::RAW_HTML,
					'raw'             => sprintf(
						__( 'Hello %2$s, the %1$s plugin is required for this widget to work properly. Please install and activate %1$s to access the full functionality of this widget. After installation, refresh this page to start using the form builder.', 'sky-elementor-addons' ),
						'<a href="' . esc_url( admin_url( 'plugin-install.php?s=fluentform&tab=search&type=term' ) )
						. '" target="_blank" rel="noopener">Fluent Form</a>',
						sky_addons_get_current_user_display_name()
					),
					'content_classes' => 'elementor-panel-alert elementor-panel-alert-danger',
				]
			);

			$this->add_control(
				'_fluent_form_install',
				[
					'type' => Controls_Manager::RAW_HTML,
					'raw'  => '<a href="' . esc_url( admin_url( 'plugin-install.php?s=fluentform+7&tab=search&type=term' ) ) . '" target="_blank" rel="noopener" class="elementor-button elementor-button-success">Install & Activate Fluent Form Plugin</a>',
				]
			);

		} else {

			$this->add_control(
				'form_id',
				[
					'label'       => __( 'Select Fluent Form', 'sky-elementor-addons' ),
					'type'        => Controls_Manager::SELECT,
					'label_block' => true,
					'options'     => [ '' => __( '-- Select a Form --', 'sky-elementor-addons' ) ] + \sky_addons_fluent_forms(),
					'description' => sprintf(
						__( 'Create or edit forms in the %1$sFluent Forms dashboard%2$s', 'sky-elementor-addons' ),
						'<a href="' . esc_url( admin_url( 'admin.php?page=fluent_forms' ) ) . '" target="_blank">',
						'</a>'
					),
				]
			);

		}

		$this->end_controls_section();

		$this->__fields_style_controls();
		$this->__fields_label_style_controls();
		$this->__submit_btn_style_controls();
		$this->__break_style_controls();
	}

	protected function __fields_style_controls() {

		$this->start_controls_section(
			'_section_fields_style',
			[
				'label' => __( 'Form Input Fields', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'field_margin',
			[
				'label'      => __( 'Spacing Bottom', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .ff-el-group:not(.ff_submit_btn_wrapper)' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'field_padding',
			[
				'label'      => __( 'Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .ff-el-form-control:not(select)' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'field_border_radius',
			[
				'label'      => __( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .ff-el-form-control' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'hr',
			[
				'type'  => Controls_Manager::DIVIDER,
				'style' => 'thick',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'field_typography',
				'label'    => __( 'Typography', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .ff-el-form-control',
				'global'   => [
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				],
			]
		);

		$this->add_control(
			'field_color',
			[
				'label'     => __( 'Text Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ff-el-form-control' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'field_placeholder_color',
			[
				'label'     => __( 'Placeholder Text Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} ::-webkit-input-placeholder' => 'color: {{VALUE}};',
					'{{WRAPPER}} ::-moz-placeholder'      => 'color: {{VALUE}};',
					'{{WRAPPER}} ::-ms-input-placeholder' => 'color: {{VALUE}};',
				],
			]
		);

		$this->start_controls_tabs( 'tabs_field_state' );

		$this->start_controls_tab(
			'tab_field_normal',
			[
				'label' => __( 'Normal', 'sky-elementor-addons' ),
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'field_border',
				'selector' => '{{WRAPPER}} .ff-el-form-control',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'field_box_shadow',
				'selector' => '{{WRAPPER}} .ff-el-form-control:not(select)',
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'field_bg_color',
				'selector' => '{{WRAPPER}} .ff-el-form-control',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_field_focus',
			[
				'label' => __( 'Focus', 'sky-elementor-addons' ),
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'field_focus_border',
				'selector' => '{{WRAPPER}} .ff-el-form-control:focus',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'field_focus_box_shadow',
				'exclude'  => [
					'box_shadow_position',
				],
				'selector' => '{{WRAPPER}} .ff-el-form-control:not(select):focus',
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'field_focus_background',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .ff-el-form-control:focus',
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	protected function __fields_label_style_controls() {

		$this->start_controls_section(
			'form_labels_section',
			[
				'label' => __( 'Field Labels & Help Text', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'label_margin',
			[
				'label'      => __( 'Spacing Bottom', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .ff-el-input--label' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'hr3',
			[
				'type'  => Controls_Manager::DIVIDER,
				'style' => 'thick',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'label_typography',
				'label'    => __( 'Typography', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .ff-el-input--label label',
				'global'   => [
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				],
			]
		);

		$this->add_control(
			'label_color',
			[
				'label'     => __( 'Text Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ff-el-input--label label' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'check_box_label_color',
			[
				'label'     => __( 'Check Box Label Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ff-el-form-check .ff-el-form-check-label' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'required_label_color',
			[
				'label'     => __( 'Required Label Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ff-el-input--label label:before' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'help_text_heading',
			[
				'label'     => __( 'Help Text', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'help_text_padding',
			[
				'label'      => __( 'Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .ff-el-tooltip:before' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'help_text_border',
				'selector' => '{{WRAPPER}} .ff-el-tooltip:before',
			]
		);

		$this->add_control(
			'help_text_border_radius',
			[
				'label'      => __( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .ff-el-tooltip:before' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'help_text_typography',
				'label'    => __( 'Typography', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .ff-el-tooltip:before',
				'global'   => [
					'default' => Global_Typography::TYPOGRAPHY_ACCENT,
				],
			]
		);

		$this->add_control(
			'help_text_icon_color',
			[
				'label'     => __( 'Icon Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ff-el-tooltip' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'help_text_color',
			[
				'label'     => __( 'Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ff-el-tooltip:before' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'help_text_arrow_color',
			[
				'label'     => __( 'Arrow Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ff-el-tooltip:after' => 'border-top-color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'help_text_background',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .ff-el-tooltip:before',
			]
		);

		$this->end_controls_section();
	}

	protected function __submit_btn_style_controls() {

		$this->start_controls_section(
			'submit_button_section',
			[
				'label' => __( 'Submit Button', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'submit_margin',
			[
				'label'      => __( 'Margin', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .ff-btn-submit' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'submit_padding',
			[
				'label'      => __( 'Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .ff-btn-submit' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'submit_typography',
				'selector' => '{{WRAPPER}} .ff-btn-submit',
				'global'   => [
					'default' => Global_Typography::TYPOGRAPHY_ACCENT,
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'submit_border',
				'selector' => '{{WRAPPER}} .ff-btn-submit',
			]
		);

		$this->add_control(
			'submit_border_radius',
			[
				'label'      => __( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .ff-btn-submit' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'submit_box_shadow',
				'selector' => '{{WRAPPER}} .ff-btn-submit',
			]
		);

		$this->add_control(
			'hr4',
			[
				'type'  => Controls_Manager::DIVIDER,
				'style' => 'thick',
			]
		);

		$this->start_controls_tabs( 'tabs_button_style' );

		$this->start_controls_tab(
			'tab_button_normal',
			[
				'label' => __( 'Normal', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'submit_text_color',
			[
				'label'     => __( 'Text Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#FFFFFF',
				'selectors' => [
					'{{WRAPPER}} .ff-btn-submit' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'submit_button_background',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .ff-btn-submit',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_button_hover',
			[
				'label' => __( 'Hover', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'submit_hover_text_color',
			[
				'label'     => __( 'Text Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#FFFFFF',
				'selectors' => [
					'{{WRAPPER}} .ff-btn-submit:hover, {{WRAPPER}} .ff-btn-submit:focus' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'submit_button_hover_background',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .ff-btn-submit:hover, {{WRAPPER}} .ff-btn-submit:focus',
			]
		);

		$this->add_control(
			'submit_hover_border_color',
			[
				'label'     => __( 'Border Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ff-btn-submit:hover, {{WRAPPER}} .ff-btn-submit:focus' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	protected function __break_style_controls() {

		$this->start_controls_section(
			'section_breaks_styling',
			[
				'label' => esc_html__( 'Section Breaks', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'section_break',
			[
				'label' => __( 'Section Break', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::HEADING,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'section_break_title_typography',
				'label'    => __( 'Title Typography', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .ff-el-section-break .ff-el-section-title',
				'global'   => [
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'section_break_description_typography',
				'label'    => __( 'Description Typography', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .ff-el-section-break .ff-section_break_desk',
				'global'   => [
					'default' => Global_Typography::TYPOGRAPHY_ACCENT,
				],
			]
		);

		$this->start_controls_tabs( 'tabs_section_break_style' );

		$this->start_controls_tab(
			'section_break__title',
			[
				'label' => __( 'Title', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'section_break_title_color',
			[
				'label'     => __( 'Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ff-el-section-break .ff-el-section-title' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'section_break_tab_description',
			[
				'label' => __( 'Description', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'section_break_description_color',
			[
				'label'     => __( 'Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ff-el-section-break .ff-section_break_desk' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	protected function render() {
		if ( ! sky_addons_is_fluent_form_activated() ) {
			sky_addons_show_plugin_missing_alert( __( 'Fluent Form', 'sky-elementor-addons' ) );
			return;
		}

		$settings = $this->get_settings_for_display();

		if ( ! empty( $settings['form_id'] ) ) {
			echo sky_addons_do_shortcode( 'fluentform', [
				'id' => $settings['form_id'],
			] );
		}
	}
}

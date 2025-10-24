<?php

namespace Sky_Addons\Modules\WeForms\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WeForms extends Widget_Base {

	public function get_name() {
		return 'sky-we-forms';
	}

	public function get_title() {
		return esc_html__( 'weForms', 'sky-elementor-addons' );
	}

	public function get_icon() {
		return 'sky-icon-we-forms';
	}

	public function get_categories() {
		return [ 'sky-elementor-addons' ];
	}

	public function get_keywords() {
		return [ 'sky', 'we', 'forms', 'weforms', 'contact', 'form', 'mail', 'email', 'message', 'contact form' ];
	}

	public function get_custom_help_url() {
		return 'https://skyaddons.com/docs/sky-addons/forms/weforms/';
	}

	public function is_reload_preview_required() {
		return true;
	}

	protected function register_controls() {
		$this->start_controls_section(
			'section_weforms',
			[
				'label' => sky_addons_is_weforms_activated() ? __( 'weForms Configuration', 'sky-elementor-addons' ) : __( 'Missing Notice', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		if ( ! sky_addons_is_weforms_activated() ) {

			$this->add_control(
				'_weforms_missing_notice',
				[
					'type'            => Controls_Manager::RAW_HTML,
					'raw'             => sprintf(
						__( 'Hello %2$s, looks like %1$s is missing in your site. Please click on the link below and install/activate %1$s. Make sure to refresh this page after installation or activation.', 'sky-elementor-addons' ),
						'<a href="' . esc_url( admin_url( 'plugin-install.php?s=weForms&tab=search&type=term' ) ) . '" target="_blank" rel="noopener">weForms</a>',
						sky_addons_get_current_user_display_name()
					),
					'content_classes' => 'elementor-panel-alert elementor-panel-alert-danger',
				]
			);

			$this->add_control(
				'_weforms_install',
				[
					'type' => Controls_Manager::RAW_HTML,
					'raw'  => '<a href="' . esc_url( admin_url( 'plugin-install.php?s=weForms&tab=search&type=term' ) ) . '" target="_blank" rel="noopener">Click to install or activate weForms</a>',
				]
			);

		} else {

			$this->add_control(
				'form_id',
				[
					'label'       => __( 'Select Form', 'sky-elementor-addons' ),
					'type'        => Controls_Manager::SELECT,
					'label_block' => true,
					'options'     => [ '' => __( '— Select a Form —', 'sky-elementor-addons' ) ] + \sky_addons_get_we_forms(),
					'description' => __( 'Choose the weForms form you want to display on this page.', 'sky-elementor-addons' ),
				]
			);

			$this->add_control(
				'form_notice',
				[
					'type'            => Controls_Manager::RAW_HTML,
					'raw'             => __( '<strong>Note:</strong> Make sure to configure your form settings in weForms before displaying it.', 'sky-elementor-addons' ),
					'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
					'condition'       => [
						'form_id!' => '',
					],
				]
			);

		}

		$this->end_controls_section();

		$this->__fields_style_controls();
		$this->__label_style_controls();
		$this->__submit_style_controls();
		$this->__section_break_style_controls();
	}

	protected function __fields_style_controls() {

		$this->start_controls_section(
			'_section_fields_style',
			[
				'label' => __( 'Form Fields', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'large_field_width',
			[
				'label'       => __( 'Large Field Width', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::SLIDER,
				'size_units'  => [ 'px', '%' ],
				'default'     => [
					'unit' => '%',
					'size' => 99,
				],
				'range'       => [
					'%' => [
						'min' => 1,
						'max' => 100,
					],
					'px' => [
						'min' => 1,
						'max' => 800,
					],
				],
				'selectors'   => [
					'{{WRAPPER}} .wpuf-form > li.wpuf-el.field-size-large > .wpuf-fields input:not([type=radio]):not([type=checkbox])' => 'width: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .wpuf-form > li.wpuf-el.field-size-large > .wpuf-fields textarea' => 'width: {{SIZE}}{{UNIT}};',
				],
				'description' => __( 'Controls the width of large form fields.', 'sky-elementor-addons' ),
			]
		);

		$this->add_responsive_control(
			'field_margin',
			[
				'label'       => __( 'Field Spacing', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::DIMENSIONS,
				'size_units'  => [ 'px', '%' ],
				'selectors'   => [
					'{{WRAPPER}} .wpuf-el:not(.wpuf-submit)' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'description' => __( 'Controls the spacing between form fields.', 'sky-elementor-addons' ),
			]
		);

		$this->add_responsive_control(
			'field_padding',
			[
				'label'      => __( 'Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .wpuf-fields input:not(.weforms_submit_btn)' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
					'{{WRAPPER}} .wpuf-form-add.wpuf-style ul.wpuf-form .wpuf-fields textarea' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
					'{{WRAPPER}} .wpuf-fields input:not(.weforms_submit_btn), {{WRAPPER}} .wpuf-fields textarea' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'field_typography',
				'label'    => __( 'Typography', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .wpuf-form-add.wpuf-style ul.wpuf-form .wpuf-fields input:not(.weforms_submit_btn), .wpuf-form-add.wpuf-style ul.wpuf-form .wpuf-fields textarea',
				'global'   => [
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				],
			]
		);

		$this->add_control(
			'field_textcolor',
			[
				'label'     => __( 'Field Text Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpuf-form-add.wpuf-style ul.wpuf-form .wpuf-fields input:not(.weforms_submit_btn), {{WRAPPER}} .wpuf-form-add.wpuf-style ul.wpuf-form .wpuf-fields textarea' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'field_placeholder_color',
			[
				'label'     => __( 'Field Placeholder Color', 'sky-elementor-addons' ),
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
				'selector' => '{{WRAPPER}} .wpuf-form-add.wpuf-style ul.wpuf-form .wpuf-fields input:not(.weforms_submit_btn), {{WRAPPER}} .wpuf-form-add.wpuf-style ul.wpuf-form .wpuf-fields textarea',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'field_box_shadow',
				'selector' => '{{WRAPPER}} .wpuf-form-add.wpuf-style ul.wpuf-form .wpuf-fields input:not(.weforms_submit_btn), {{WRAPPER}} .wpuf-form-add.wpuf-style ul.wpuf-form .wpuf-fields textarea',
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'field_background',
				'label'    => __( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .wpuf-form-add.wpuf-style ul.wpuf-form .wpuf-fields input:not(.weforms_submit_btn), {{WRAPPER}} .wpuf-form-add.wpuf-style ul.wpuf-form .wpuf-fields textarea',
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
				'selector' => '{{WRAPPER}} .wpuf-form-add.wpuf-style ul.wpuf-form .wpuf-fields input:focus:not(.weforms_submit_btn), {{WRAPPER}} .wpuf-form-add.wpuf-style ul.wpuf-form .wpuf-fields textarea:focus',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'field_focus_box_shadow',
				'exclude'  => [
					'box_shadow_position',
				],
				'selector' => '{{WRAPPER}} .wpuf-form-add.wpuf-style ul.wpuf-form .wpuf-fields input:focus:not(.weforms_submit_btn), {{WRAPPER}} .wpuf-form-add.wpuf-style ul.wpuf-form .wpuf-fields textarea:focus',
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'field_focus_background',
				'label'    => __( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .wpuf-form-add.wpuf-style ul.wpuf-form .wpuf-fields input:focus:not(.weforms_submit_btn), {{WRAPPER}} .wpuf-form-add.wpuf-style ul.wpuf-form .wpuf-fields textarea:focus',
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	protected function __label_style_controls() {

		$this->start_controls_section(
			'we-form-label',
			[
				'label' => __( 'Form Field Labels', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'label_margin',
			[
				'label'      => __( 'Margin', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .wpuf-label label' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'label_padding',
			[
				'label'      => __( 'Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .wpuf-label label' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
				'label'    => __( 'Label Typography', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .wpuf-label label, {{WRAPPER}} .wpuf-form-sub-label',
				'global'   => [
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'desc_typography',
				'label'    => __( 'Help Text Typography', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .wpuf-fields .wpuf-help',
				'global'   => [
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				],
			]
		);

		$this->add_control(
			'label_color',
			[
				'label'     => __( 'Label Text Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpuf-label label, {{WRAPPER}} .wpuf-form-sub-label' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'requered_label',
			[
				'label'     => __( 'Required Label Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpuf-label .required' => 'color: {{VALUE}} !important',
				],
			]
		);

		$this->add_control(
			'desc_color',
			[
				'label'     => __( 'Help Text Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpuf-fields .wpuf-help' => 'color: {{VALUE}}',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function __submit_style_controls() {

		$this->start_controls_section(
			'submit',
			[
				'label' => __( 'Submit Button', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'submit_btn_width',
			[
				'label'        => __( 'Full Width Button', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'sky-elementor-addons' ),
				'label_off'    => __( 'No', 'sky-elementor-addons' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'description'  => __( 'Make the submit button span the full width of its container.', 'sky-elementor-addons' ),
			]
		);

		$this->add_responsive_control(
			'button_width',
			[
				'label'      => __( 'Button Width', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'condition'  => [
					'submit_btn_width' => 'yes',
				],
				'default'    => [
					'unit' => '%',
					'size' => 100,
				],
				'range'      => [
					'%' => [
						'min' => 1,
						'max' => 100,
					],
					'px' => [
						'min' => 1,
						'max' => 800,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .wpuf-form-add.wpuf-style ul.wpuf-form .wpuf-submit .weforms_submit_btn' => 'display: block; width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'submit_btn_position',
			[
				'label'           => __( 'Button Position', 'sky-elementor-addons' ),
				'type'            => Controls_Manager::CHOOSE,
				'options'         => [
					'left' => [
						'title' => __( 'Left', 'sky-elementor-addons' ),
						'icon'  => 'eicon-h-align-left',
					],
					'center' => [
						'title' => __( 'Center', 'sky-elementor-addons' ),
						'icon'  => 'eicon-h-align-center',
					],
					'right' => [
						'title' => __( 'Right', 'sky-elementor-addons' ),
						'icon'  => 'eicon-h-align-right',
					],
				],
				'condition'       => [
					'submit_btn_width' => '',
				],
				'desktop_default' => 'left',
				'toggle'          => false,
				'prefix_class'    => 'ha-form-btn--%s',
				'selectors'       => [
					'{{WRAPPER}} .wpuf-form-add.wpuf-style ul.wpuf-form .wpuf-submit' => 'text-align: {{Value}};',
				],
			]
		);

		$this->add_responsive_control(
			'submit_margin',
			[
				'label'      => __( 'Margin', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .wpuf-form-add.wpuf-style ul.wpuf-form .wpuf-submit input[type=submit]' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'submit_padding',
			[
				'label'      => __( 'Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .wpuf-form-add.wpuf-style ul.wpuf-form .wpuf-submit input[type=submit]' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'submit_typography',
				'selector' => '{{WRAPPER}} .wpuf-form-add.wpuf-style ul.wpuf-form .wpuf-submit input[type=submit]',
				'global'   => [
					'default' => Global_Typography::TYPOGRAPHY_ACCENT,
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'submit_border',
				'selector' => '{{WRAPPER}} .wpuf-form-add.wpuf-style ul.wpuf-form .wpuf-submit input[type=submit]',
			]
		);

		$this->add_control(
			'submit_border_radius',
			[
				'label'      => __( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .wpuf-form-add.wpuf-style ul.wpuf-form .wpuf-submit input[type=submit]' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'submit_box_shadow',
				'selector' => '{{WRAPPER}} .wpuf-form-add.wpuf-style ul.wpuf-form .wpuf-submit input[type=submit]',
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'submit_text_shadow',
				'selector' => '{{WRAPPER}} .wpuf-form-add.wpuf-style ul.wpuf-form .wpuf-submit input[type=submit]',
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
			'submit_color',
			[
				'label'     => __( 'Text Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .wpuf-form-add.wpuf-style ul.wpuf-form .wpuf-submit input[type=submit]' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'submit_background',
				'label'    => __( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .wpuf-form-add.wpuf-style ul.wpuf-form .wpuf-submit input[type=submit]',
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
			'submit_hover_color',
			[
				'label'     => __( 'Text Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpuf-form-add.wpuf-style ul.wpuf-form .wpuf-submit input[type=submit]:hover, {{WRAPPER}} .wpuf-form-add.wpuf-style ul.wpuf-form .wpuf-submit input[type=submit]:focus' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'submit_hover_background',
				'label'    => __( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .wpuf-form-add.wpuf-style ul.wpuf-form .wpuf-submit input[type=submit]:hover, {{WRAPPER}} .wpuf-form-add.wpuf-style ul.wpuf-form .wpuf-submit input[type=submit]:focus',
			]
		);

		$this->add_control(
			'submit_hover_border_color',
			[
				'label'     => __( 'Border Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpuf-form-add.wpuf-style ul.wpuf-form .wpuf-submit input[type=submit]:hover, {{WRAPPER}} .wpuf-form-add.wpuf-style ul.wpuf-form .wpuf-submit input[type=submit]:focus' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	protected function __section_break_style_controls() {

		$this->start_controls_section(
			'section_break',
			[
				'label' => __( 'Section Break', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'break_title_typography',
				'label'    => __( 'Title Typography', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .section_break .wpuf-section-title',
				'global'   => [
					'default' => Global_Typography::TYPOGRAPHY_SECONDARY,
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'break_description_typography',
				'label'    => __( 'Description Typography', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .section_break .wpuf-section-details',
				'global'   => [
					'default' => Global_Typography::TYPOGRAPHY_ACCENT,
				],
			]
		);

		$this->start_controls_tabs( 'tabs_section_break_style' );

		$this->start_controls_tab(
			'tab_break_title',
			[
				'label' => __( 'Title', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'break_title_color',
			[
				'label'     => __( 'Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .section_break .wpuf-section-title' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_break_description',
			[
				'label' => __( 'Description', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'break_description_color',
			[
				'label'     => __( 'Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .section_break .wpuf-section-details' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	protected function render() {
		if ( ! sky_addons_is_weforms_activated() ) {
			sky_addons_show_plugin_missing_alert( __( 'weForms', 'sky-elementor-addons' ) );
			return;
		}

		$settings = $this->get_settings_for_display();

		if ( ! empty( $settings['form_id'] ) ) {
			echo sky_addons_do_shortcode( 'weforms', [
				'id' => $settings['form_id'],
			] );
		} elseif ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
			// Show a helpful message when no form is selected (only in editor)
			echo '<div style="text-align: center; padding: 20px; background: #f9f9f9; border: 2px dashed #ddd; color: #666;">';
			echo '<p>' . __( 'Please select a weForms form from the widget settings to display it here.', 'sky-elementor-addons' ) . '</p>';
			echo '</div>';
		}
	}
}

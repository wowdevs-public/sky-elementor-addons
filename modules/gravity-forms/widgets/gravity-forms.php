<?php

namespace Sky_Addons\Modules\GravityForms\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GravityForms extends Widget_Base {

	public function get_name() {
		return 'sky-gravity-forms';
	}

	public function get_title() {
		return esc_html__( 'Gravity Forms', 'sky-elementor-addons' );
	}

	public function get_icon() {
		return 'sky-icon-gravity-forms';
	}

	public function get_categories() {
		return [ 'sky-elementor-addons' ];
	}

	public function get_keywords() {
		return [ 'sky', 'gravity', 'forms', 'form', 'contact', 'widget' ];
	}

	public function get_custom_help_url() {
		return 'https://skyaddons.com/docs/sky-addons/forms/gravity-forms/';
	}

	public function is_reload_preview_required() {
		return false;
	}

	public function has_widget_inner_wrapper(): bool {
		return ! \Elementor\Plugin::$instance->experiments->is_feature_active( 'e_optimized_markup' );
	}

	protected function register_controls() {

		$this->start_controls_section(
			'section_gravityforms',
			[
				'label' => sky_addons_is_gravityforms_activated() ? __( 'Gravity Forms', 'sky-elementor-addons' ) : __( 'Missing Notice',
				'sky-elementor-addons' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		if ( ! sky_addons_is_gravityforms_activated() ) {

			$this->add_control(
				'gravityforms_missing_notice',
				[
					'type'            => Controls_Manager::RAW_HTML,
					'raw'             => sprintf(
						/* translators: %1$s: current user's display name. */
						__( 'Hello %1$s, looks like Gravity Forms is missing in your site. Please install/activate Gravity Forms. Make sure to refresh this page after installation or activation.', 'sky-elementor-addons' ),
						sky_addons_get_current_user_display_name()
					),
					'content_classes' => 'elementor-panel-alert elementor-panel-alert-danger',
				]
			);

		} else {

			$this->add_control(
				'form_id',
				[
					'label'       => esc_html__( 'Form Selection', 'sky-elementor-addons' ),
					'type'        => Controls_Manager::SELECT,
					'label_block' => true,
					'options'     => [ '' => '' ] + \sky_addons_get_gravity_forms(),
				]
			);

			$this->add_control(
				'form_title_show',
				[
					'label'        => esc_html__( 'Display Form Title', 'sky-elementor-addons' ),
					'type'         => Controls_Manager::SWITCHER,
					'separator'    => 'before',
					'label_on'     => __( 'Show', 'sky-elementor-addons' ),
					'label_off'    => __( 'Hide', 'sky-elementor-addons' ),
					'return_value' => 'yes',
					'default'      => 'yes',
				]
			);

			$this->add_control(
				'ajax',
				[
					'label'        => esc_html__( 'Enable AJAX Submission', 'sky-elementor-addons' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_on'     => __( 'Yes', 'sky-elementor-addons' ),
					'label_off'    => __( 'No', 'sky-elementor-addons' ),
					'return_value' => 'yes',
					'default'      => 'no',
				]
			);

		}

		$this->end_controls_section();

		$this->__form_fields_style_controls();
		$this->__form_fields_label_style_controls();
		$this->__form_fields_submit_style_controls();
		$this->__form_fields_break_style_controls();
		$this->__form_fields_list_style_controls();
		$this->__form_container_style_controls();
		$this->__form_fields_error_style_controls();
		$this->__form_consent_style_controls();
		$this->__form_rich_text_style_controls();
	}

	protected function __form_fields_style_controls() {

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
				'label'      => esc_html__( 'Field Width (Large)', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range'      => [
					'%'  => [
						'min' => 1,
						'max' => 100,
					],
					'px' => [
						'min' => 1,
						'max' => 800,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .gform_body .gfield input.large' => 'width: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .gform_body .gfield  textarea.large' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'field_margin',
			[
				'label'      => esc_html__( 'Field Spacing', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .gform_body .gform_fields .gfield' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'field_padding',
			[
				'label'      => esc_html__( 'Field Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .gform_body .gfield .ginput_container:not(.ginput_container_fileupload) > input:not(.ginput_quantity)' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .gform_body .gfield .ginput_container.ginput_complex > input:not([type=radio]):not([type=checkbox]):not([type=submit]):not([type=button]):not([type=image]):not([type=file])' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .gform_body .gfield textarea' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'field_border_radius',
			[
				'label'      => esc_html__( 'Field Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .gfield .ginput_container:not(.ginput_container_fileupload) > input' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .gfield .ginput_container.ginput_complex input' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .gform_body .gfield textarea' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'field_typography',
				'label'    => esc_html__( 'Field Typography', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .gfield .ginput_container > input, {{WRAPPER}} .gform_body .gfield textarea, {{WRAPPER}} .gfield .ginput_container.ginput_complex input',
				'global'   => [
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				],
			]
		);

		$this->add_control(
			'field_textcolor',
			[
				'label' => esc_html__( 'Field Text Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .gfield .ginput_container > input' => 'color: {{VALUE}};',
					'{{WRAPPER}} .gfield .ginput_container.ginput_complex input' => 'color: {{VALUE}};',
					'{{WRAPPER}} .gform_body .gfield textarea' => 'color: {{VALUE}};',
					'{{WRAPPER}} .gform_body .gfield select' => 'color: {{VALUE}};',
					'{{WRAPPER}} .gfield_list tbody td input' => 'color: {{VALUE}};',
					'{{WRAPPER}} .ginput_container_address input' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'field_placeholder_color',
			[
				'label' => esc_html__( 'Placeholder Text Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
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
				'name' => 'field_border',
				'selector' => '{{WRAPPER}} .gfield .ginput_container:not(.ginput_container_fileupload) > input,
				{{WRAPPER}} .gfield .ginput_complex input,
				{{WRAPPER}} .gfield .ginput_container_address input,
				{{WRAPPER}} .gfield_list_cell input,
				{{WRAPPER}} .gfield .ginput_container select,
				{{WRAPPER}} .gform_body .gfield textarea',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'field_box_shadow',
				'selector' => '{{WRAPPER}} .gfield .ginput_container:not(.ginput_container_fileupload) > input,
				{{WRAPPER}} .gfield .ginput_complex input,
				{{WRAPPER}} .gfield .ginput_container_address input,
				{{WRAPPER}} .gform_body .gfield textarea',
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'field_background',
				'label'    => __( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .gfield .ginput_container:not(.ginput_container_fileupload) > input, {{WRAPPER}} .gfield .ginput_complex input, {{WRAPPER}} .gfield .ginput_container_address input, {{WRAPPER}} .gfield .ginput_container_list input, {{WRAPPER}} .gform_body .gfield textarea, {{WRAPPER}} .gform_body .gfield select',
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
				'name' => 'field_focus_border',
				'selector' => '{{WRAPPER}} .gfield .ginput_container > input:focus,
				{{WRAPPER}} .gfield .ginput_complex input:focus,
				{{WRAPPER}} .gfield .ginput_container_address input:focus,
				{{WRAPPER}} .gfield_list_cell input:focus,
				{{WRAPPER}} .gform_body .gfield textarea:focus',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'field_focus_box_shadow',
				'selector' => '{{WRAPPER}} .gfield .ginput_container > input:focus,
				{{WRAPPER}} .gfield .ginput_complex input:focus,
				{{WRAPPER}} .gfield .ginput_container_address input:focus,
				{{WRAPPER}} .gform_body .gfield textarea:focus',
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'field_focus_background',
				'label'    => __( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .gfield .ginput_container > input:focus, {{WRAPPER}} .gfield .ginput_complex input:focus, {{WRAPPER}} .gform_body .gfield textarea:focus',
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	protected function __form_fields_label_style_controls() {

		$this->start_controls_section(
			'form_fields_label_section',
			[
				'label' => __( 'Form Fields Label', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'label_margin',
			[
				'label'      => esc_html__( 'Label Margin', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .gform_body .gfield .gfield_label' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'label_padding',
			[
				'label'      => esc_html__( 'Label Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .gform_body .gfield .gfield_label' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} table.gfield_list thead th' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'sub_label_margin',
			[
				'label'      => esc_html__( 'Sub-label Margin', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'separator'  => 'after',
				'selectors'  => [
					'{{WRAPPER}} .gform_body .gfield .gfield_description' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'label_typography',
				'label'    => esc_html__( 'Label Typography', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .gform_body .gfield .gfield_label, {{WRAPPER}} table.gfield_list thead th',
				'global'   => [
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'sub_label_typography',
				'label'    => esc_html__( 'Sub-label Typography', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .gform_body .gfield .gfield_description',
				'global'   => [
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				],
			]
		);

		$this->add_control(
			'label_color',
			[
				'label' => esc_html__( 'Label Text Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .gform_body .gfield .gfield_label' => 'color: {{VALUE}}',
					'{{WRAPPER}} .gform_body .gfield .ginput_complex label' => 'color: {{VALUE}}',
					'{{WRAPPER}} table.gfield_list thead th' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'sub_label_color',
			[
				'label' => esc_html__( 'Sub-label Text Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .gform_body .gfield .gfield_description' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'requered_label',
			[
				'label' => esc_html__( 'Required Label Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .gform_body .gfield .gfield_label .gfield_required' => 'color: {{VALUE}}',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function __form_fields_submit_style_controls() {

		$this->start_controls_section(
			'form_fields_submit_sectionsubmit',
			[
				'label' => __( 'Submit Button', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'submit_btn_width',
			[
				'label'        => esc_html__( 'Submit Button Full Width', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'sky-elementor-addons' ),
				'label_off'    => __( 'No', 'sky-elementor-addons' ),
				'return_value' => 'yes',
				'default'      => '',
			]
		);

		$this->add_responsive_control(
			'button_width',
			[
				'label'      => esc_html__( 'Submit Button Width', 'sky-elementor-addons' ),
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
					'%'  => [
						'min' => 1,
						'max' => 100,
					],
					'px' => [
						'min' => 1,
						'max' => 800,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .gform_wrapper .gform_button' => 'width: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}}.elementor-widget-sky-gravity-forms .gform_wrapper input[type="submit"].gform_button' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'submit_btn_position',
			[
				'label'   => esc_html__( 'Submit Button Position', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::CHOOSE,
				'options' => [
					'left'   => [
						'title' => __( 'Left', 'sky-elementor-addons' ),
						'icon'  => 'eicon-h-align-left',
					],
					'center' => [
						'title' => __( 'Center', 'sky-elementor-addons' ),
						'icon'  => 'eicon-h-align-center',
					],
					'right'  => [
						'title' => __( 'Right', 'sky-elementor-addons' ),
						'icon'  => 'eicon-h-align-right',
					],
				],
				'condition' => [
					'submit_btn_width' => '',
				],
				'default' => 'left',
				'selectors_dictionary' => [
					'left'   => 'justify-content: flex-start; text-align: left;',
					'center' => 'justify-content: center; text-align: center;',
					'right'  => 'justify-content: flex-end; text-align: right;',
				],
				'selectors' => [
					'{{WRAPPER}} .gform_wrapper .gform_footer' => '{{VALUE}};',
					'{{WRAPPER}}.elementor-widget-sky-gravity-forms .gform_wrapper .gform_footer' => '{{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'submit_margin',
			[
				'label'      => esc_html__( 'Submit Button Margin', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .gform_wrapper .gform_footer' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'submit_padding',
			[
				'label'      => esc_html__( 'Submit Button Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .gform_wrapper .gform_button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}}.elementor-widget-sky-gravity-forms .gform_wrapper input[type="submit"].gform_button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'submit_typography',
				'selector' => '{{WRAPPER}} .gform_wrapper .gform_button, {{WRAPPER}}.elementor-widget-sky-gravity-forms .gform_wrapper input[type="submit"].gform_button',
				'global'   => [
					'default' => Global_Typography::TYPOGRAPHY_ACCENT,
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'submit_border',
				'selector' => '{{WRAPPER}} .gform_wrapper .gform_button, {{WRAPPER}}.elementor-widget-sky-gravity-forms .gform_wrapper input[type="submit"].gform_button',
			]
		);

		$this->add_control(
			'submit_border_radius',
			[
				'label'      => esc_html__( 'Submit Button Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .gform_wrapper .gform_button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}}.elementor-widget-sky-gravity-forms .gform_wrapper input[type="submit"].gform_button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'      => 'submit_box_shadow',
				'selector'  => '{{WRAPPER}} .gform_wrapper .gform_button, {{WRAPPER}}.elementor-widget-sky-gravity-forms .gform_wrapper input[type="submit"].gform_button',
				'separator' => 'after',
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
				'label'   => esc_html__( 'Submit Button Text Color (Normal)', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .gform_wrapper .gform_button' => 'color: {{VALUE}};',
					'{{WRAPPER}}.elementor-widget-sky-gravity-forms .gform_wrapper input[type="submit"].gform_button' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'submit_background',
				'label'    => esc_html__( 'Submit Button Background (Normal)', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .gform_wrapper .gform_button, {{WRAPPER}}.elementor-widget-sky-gravity-forms .gform_wrapper input[type="submit"].gform_button',
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
				'label' => esc_html__( 'Submit Button Text Color (Hover)', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .gform_wrapper .gform_button:hover' => 'color: {{VALUE}};',
					'{{WRAPPER}} .gform_wrapper .gform_button:focus' => 'color: {{VALUE}};',
					'{{WRAPPER}}.elementor-widget-sky-gravity-forms .gform_wrapper input[type="submit"].gform_button:hover' => 'color: {{VALUE}};',
					'{{WRAPPER}}.elementor-widget-sky-gravity-forms .gform_wrapper input[type="submit"].gform_button:focus' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'submit_hover_background',
				'label'    => esc_html__( 'Submit Button Background (Hover)', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .gform_wrapper .gform_button:hover, {{WRAPPER}} .gform_wrapper .gform_button:focus, {{WRAPPER}}.elementor-widget-sky-gravity-forms .gform_wrapper input[type="submit"].gform_button:hover, {{WRAPPER}}.elementor-widget-sky-gravity-forms .gform_wrapper input[type="submit"].gform_button:focus',
			]
		);

		$this->add_control(
			'submit_hover_border_color',
			[
				'label' => esc_html__( 'Submit Button Border Color (Hover)', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .gform_wrapper .gform_button:hover' => 'border-color: {{VALUE}};',
					'{{WRAPPER}} .gform_wrapper .gform_button:focus' => 'border-color: {{VALUE}};',
					'{{WRAPPER}}.elementor-widget-sky-gravity-forms .gform_wrapper input[type="submit"].gform_button:hover' => 'border-color: {{VALUE}};',
					'{{WRAPPER}}.elementor-widget-sky-gravity-forms .gform_wrapper input[type="submit"].gform_button:focus' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	protected function __form_fields_break_style_controls() {

		$this->start_controls_section(
			'form_fields_break_section',
			[
				'label' => __( 'Break', 'sky-elementor-addons' ),
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
				'label'    => esc_html__( 'Section Break Title Typography', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .gsection .gsection_title',
				'global'   => [
					'default' => Global_Typography::TYPOGRAPHY_SECONDARY,
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'section_break_description_typography',
				'label'    => esc_html__( 'Section Break Description Typography', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .gsection .gsection_description',
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
				'label' => esc_html__( 'Section Break Title Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .gsection .gsection_title' => 'color: {{VALUE}};',
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
				'label' => esc_html__( 'Section Break Description Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .gsection .gsection_description' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_control(
			'page_break',
			[
				'label'     => __( 'Page Break', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'page_break_progress_bar_background',
				'label'    => esc_html__( 'Page Break Progress Bar Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .gform_wrapper .percentbar_blue',
			]
		);

		$this->add_control(
			'page_break_button_paddding',
			[
				'label'      => esc_html__( 'Page Break Button Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .gform_next_button.button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .gform_previous_button.button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'page_break_button_box_shadow',
				'label'    => esc_html__( 'Page Break Button Box Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .gform_next_button.button, {{WRAPPER}} .gform_previous_button.button',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'page_break_button_border',
				'selector' => '{{WRAPPER}} .gform_next_button.button, {{WRAPPER}} .gform_previous_button.button',
			]
		);

		$this->add_control(
			'page_break_button_border_radius',
			[
				'label'      => esc_html__( 'Page Break Button Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .gform_next_button.button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .gform_previous_button.button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'page_break_button_typography',
				'label'    => esc_html__( 'Page Break Button Typography', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .gform_next_button.button, {{WRAPPER}} .gform_previous_button.button',
				'global'   => [
					'default' => Global_Typography::TYPOGRAPHY_ACCENT,
				],
			]
		);

		$this->start_controls_tabs( 'page_break_tabs_button_style' );

		$this->start_controls_tab(
			'page_break_tab_button_normal',
			[
				'label' => __( 'Normal', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'page_break_color',
			[
				'label'   => esc_html__( 'Page Break Button Text Color (Normal)', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .gform_next_button.button' => 'color: {{VALUE}};',
					'{{WRAPPER}} .gform_previous_button.button' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'page_break_background',
				'label'    => esc_html__( 'Page Break Button Background (Normal)', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .gform_next_button.button, {{WRAPPER}} .gform_previous_button.button',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'page_break_tab_button_hover',
			[
				'label' => __( 'Hover', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'page_break_hover_color',
			[
				'label' => esc_html__( 'Page Break Button Text Color (Hover)', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .gform_next_button.button:hover' => 'color: {{VALUE}};',
					'{{WRAPPER}} .gform_next_button.button:focus' => 'color: {{VALUE}};',
					'{{WRAPPER}} .gform_previous_button.button:hover' => 'color: {{VALUE}};',
					'{{WRAPPER}} .gform_previous_button.button:focus' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'page_break_hover_background',
				'label'    => esc_html__( 'Page Break Button Background (Hover)', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .gform_next_button.button:hover, {{WRAPPER}} .gform_next_button.button:focus, {{WRAPPER}} .gform_previous_button.button:hover, {{WRAPPER}} .gform_previous_button.button:focus',
			]
		);

		$this->add_control(
			'page_break_hover_border_color',
			[
				'label' => esc_html__( 'Page Break Button Border Color (Hover)', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .gform_next_button.button:hover' => 'border-color: {{VALUE}};',
					'{{WRAPPER}} .gform_next_button.button:focus' => 'border-color: {{VALUE}};',
					'{{WRAPPER}} .gform_previous_button.button:hover' => 'border-color: {{VALUE}};',
					'{{WRAPPER}} .gform_previous_button.button:focus' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	protected function __form_fields_list_style_controls() {

		$this->start_controls_section(
			'form_fields_list_section',
			[
				'label' => __( 'List', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'list_button_size',
			[
				'label'      => esc_html__( 'List Button Size', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'default'    => [
					'unit' => 'px',
					'size' => 16,
				],
				'range'      => [
					'px' => [
						'min' => 5,
						'max' => 50,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .gfield_list .gfield_list_icons img' => 'width: {{SIZE}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'list_even_background',
				'label'    => esc_html__( 'List Background (Even)', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .gfield_list .gfield_list_row_even td',
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'list_odd_background',
				'label'    => esc_html__( 'List Background (Odd)', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .gfield_list .gfield_list_row_odd td',
			]
		);

		$this->end_controls_section();
	}

	protected function __form_container_style_controls() {

		$this->start_controls_section(
			'form_container_section',
			[
				'label' => __( 'Form Container', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'container_padding',
			[
				'label'      => esc_html__( 'Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-gravity-form-wrapper' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'container_margin',
			[
				'label'      => esc_html__( 'Margin', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-gravity-form-wrapper' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'container_background',
				'label'    => __( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .sa-gravity-form-wrapper',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'container_border',
				'selector' => '{{WRAPPER}} .sa-gravity-form-wrapper',
			]
		);

		$this->add_responsive_control(
			'container_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-gravity-form-wrapper' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'container_box_shadow',
				'label'    => esc_html__( 'Box Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-gravity-form-wrapper',
			]
		);

		$this->end_controls_section();
	}

	protected function __form_fields_error_style_controls() {

		$this->start_controls_section(
			'form_error_section',
			[
				'label' => __( 'Validation Error', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'  => 'error_field_border',
				'label' => esc_html__( 'Error Field Border', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .gfield.gfield_error .ginput_container,
					{{WRAPPER}} .gfield.gfield_error input:not([type=submit]),
					{{WRAPPER}} .gfield.gfield_error textarea,
					{{WRAPPER}} .gfield.gfield_error select',
			]
		);

		$this->add_control(
			'error_field_text_color',
			[
				'label' => esc_html__( 'Error Text Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .gfield.gfield_error .ginput_container' => 'color: {{VALUE}};',
					'{{WRAPPER}} .gfield.gfield_error .validation_message' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'  => 'error_field_background',
				'label' => esc_html__( 'Error Field Background', 'sky-elementor-addons' ),
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .gfield.gfield_error .ginput_container,
					{{WRAPPER}} .gfield.gfield_error input:not([type=submit]),
					{{WRAPPER}} .gfield.gfield_error textarea,
					{{WRAPPER}} .gfield.gfield_error select',
			]
		);

		$this->add_control(
			'error_label_color',
			[
				'label'     => esc_html__( 'Error Label Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'separator' => 'before',
				'selectors' => [
					'{{WRAPPER}} .gfield.gfield_error .gfield_label' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'error_message_color',
			[
				'label' => esc_html__( 'Error Message Text Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .validation_message' => 'color: {{VALUE}};',
					'{{WRAPPER}} .gfield_error .gfield_required' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'error_message_padding',
			[
				'label'      => esc_html__( 'Error Message Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .validation_message' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'error_message_typography',
				'label'    => esc_html__( 'Error Message Typography', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .validation_message',
				'global'   => [
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				],
			]
		);

		$this->add_control(
			'form_error_heading',
			[
				'label'     => __( 'Form Error', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'form_error_text_color',
			[
				'label' => esc_html__( 'Form Error Text Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .gform_wrapper .validation_error' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'form_error_bg_color',
			[
				'label' => esc_html__( 'Form Error Background Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .gform_wrapper .validation_error' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'form_error_border',
				'selector' => '{{WRAPPER}} .gform_wrapper .validation_error',
			]
		);

		$this->add_responsive_control(
			'form_error_border_radius',
			[
				'label'      => esc_html__( 'Form Error Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .gform_wrapper .validation_error' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'form_error_padding',
			[
				'label'      => esc_html__( 'Form Error Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .gform_wrapper .validation_error' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function __form_consent_style_controls() {

		$this->start_controls_section(
			'form_consent_section',
			[
				'label' => __( 'Consent / Privacy', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'consent_field_padding',
			[
				'label'      => esc_html__( 'Consent Field Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .gfield.gfield--type-consent' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'consent_field_margin',
			[
				'label'      => esc_html__( 'Consent Field Margin', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .gfield.gfield--type-consent' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'consent_border',
				'label'    => esc_html__( 'Consent Border', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .gfield.gfield--type-consent',
			]
		);

		$this->add_responsive_control(
			'consent_border_radius',
			[
				'label'      => esc_html__( 'Consent Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .gfield.gfield--type-consent' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'consent_box_shadow',
				'label'    => esc_html__( 'Consent Box Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .gfield.gfield--type-consent',
			]
		);

		$this->add_control(
			'consent_label_heading',
			[
				'label'     => __( 'Consent Label', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'  => 'consent_label_typography',
				'label' => esc_html__( 'Consent Label Typography', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .gfield.gfield--type-consent .gfield_label,
					{{WRAPPER}} .gfield.gfield--type-consent .ginput_container_consent label',
				'global' => [
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				],
			]
		);

		$this->add_control(
			'consent_label_color',
			[
				'label' => esc_html__( 'Consent Label Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .gfield.gfield--type-consent .gfield_label' => 'color: {{VALUE}};',
					'{{WRAPPER}} .gfield.gfield--type-consent .ginput_container_consent label' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'consent_text_color',
			[
				'label' => esc_html__( 'Consent Text Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .gfield.gfield--type-consent .ginput_container_consent' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'consent_background',
				'label'    => __( 'Consent Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .gfield.gfield--type-consent',
			]
		);

		$this->end_controls_section();
	}

	protected function __form_rich_text_style_controls() {

		$this->start_controls_section(
			'form_rich_text_section',
			[
				'label' => __( 'Rich Text / HTML', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'rich_text_padding',
			[
				'label'      => esc_html__( 'Rich Text Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .gfield_html' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'rich_text_margin',
			[
				'label'      => esc_html__( 'Rich Text Margin', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .gfield_html' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'rich_text_background',
				'label'    => __( 'Rich Text Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .gfield_html',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'rich_text_border',
				'label'    => esc_html__( 'Rich Text Border', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .gfield_html',
			]
		);

		$this->add_responsive_control(
			'rich_text_border_radius',
			[
				'label'      => esc_html__( 'Rich Text Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .gfield_html' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'rich_text_box_shadow',
				'label'    => esc_html__( 'Rich Text Box Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .gfield_html',
			]
		);

		$this->add_control(
			'rich_text_label_heading',
			[
				'label'     => __( 'Rich Text Label', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'rich_text_label_typography',
				'label'    => esc_html__( 'Rich Text Label Typography', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .gfield_html .gfield-label',
				'global'   => [
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				],
			]
		);

		$this->add_control(
			'rich_text_label_color',
			[
				'label' => esc_html__( 'Rich Text Label Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .gfield_html .gfield-label' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'rich_text_heading',
			[
				'label'     => __( 'Rich Text Content', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'rich_text_typography',
				'label'    => esc_html__( 'Rich Text Typography', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .gfield_html',
				'global'   => [
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				],
			]
		);

		$this->add_control(
			'rich_text_color',
			[
				'label' => esc_html__( 'Rich Text Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .gfield_html' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		if ( ! sky_addons_is_gravityforms_activated() ) {
			sky_addons_show_plugin_missing_alert( __( 'Gravity Forms', 'sky-elementor-addons' ) );
			return;
		}

		$settings = $this->get_settings_for_display();

		if ( empty( $settings['form_id'] ) ) {
			?>
			<div class="elementor-alert elementor-alert-warning">
				<?php esc_html_e( 'Please select a Gravity Form to display.', 'sky-elementor-addons' ); ?>
			</div>
			<?php
			return;
		}

		$ajax = ( 'yes' === $settings['ajax'] );

		$this->add_render_attribute(
			'gravity-form-wrapper',
			[
				'class' => 'sa-gravity-form-wrapper',
			]
		);

		?>
		<div <?php $this->print_render_attribute_string( 'gravity-form-wrapper' ); ?>>
			<?php
			ob_start();
			gravity_form( $settings['form_id'], $settings['form_title_show'], true, false, null, $ajax );
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Form markup from Gravity Forms, escaped by that plugin; wp_kses_post() would strip the form fields.
			echo ob_get_clean();
			?>
		</div>
		<?php
	}
}

<?php

namespace Sky_Addons\Modules\NinjaForms\Widgets;

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

class NinjaForms extends Widget_Base {

	public function get_name() {
		return 'sky-ninja-forms';
	}

	public function get_title() {
		return esc_html__( 'Ninja Forms', 'sky-elementor-addons' );
	}

	public function get_icon() {
		return 'sky-icon-ninja-forms';
	}

	public function get_categories() {
		return [ 'sky-elementor-addons' ];
	}

	public function get_keywords() {
		return [ 'sky', 'ninja', 'forms', 'ninjaforms', 'contact', 'form', 'mail', 'email', 'message' ];
	}

	public function get_custom_help_url() {
		return 'https://skyaddons.com/docs/sky-addons/forms/ninja-forms/';
	}

	public function is_reload_preview_required() {
		return false;
	}

	public function has_widget_inner_wrapper(): bool {
		return ! \Elementor\Plugin::$instance->experiments->is_feature_active( 'e_optimized_markup' );
	}

	protected function register_controls() {

		$this->start_controls_section(
			'section_ninjaforms',
			[
				'label' => sky_addons_is_ninjaforms_activated() ? esc_html__( 'Ninja Forms', 'sky-elementor-addons' ) : esc_html__( 'Missing Notice', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		if ( ! sky_addons_is_ninjaforms_activated() ) {

			$this->add_control(
				'ninjaforms_missing_notice',
				[
					'type'            => Controls_Manager::RAW_HTML,
					'raw'             => sprintf(
						/* translators: 1: plugin link, 2: user display name */
						esc_html__( 'Hello %2$s! It looks like %1$s is not installed or activated on your site. Please install and activate it to use this widget.', 'sky-elementor-addons' ),
						'<a href="' . esc_url( admin_url( 'plugin-install.php?s=Ninja+Forms&tab=search&type=term' ) ) . '" target="_blank" rel="noopener"><strong>Ninja Forms</strong></a>',
						esc_html( sky_addons_get_current_user_display_name() )
					),
					'content_classes' => 'elementor-panel-alert elementor-panel-alert-danger',
				]
			);

			$this->add_control(
				'ninjaforms_install_link',
				[
					'type' => Controls_Manager::RAW_HTML,
					'raw'  => '<a href="' . esc_url( admin_url( 'plugin-install.php?s=Ninja+Forms&tab=search&type=term' ) ) . '" target="_blank" rel="noopener">' . esc_html__( 'Click to install or activate Ninja Forms', 'sky-elementor-addons' ) . '</a>',
				]
			);

		} else {

			$this->add_control(
				'form_id',
				[
					'label'       => esc_html__( 'Select Ninja Form', 'sky-elementor-addons' ),
					'type'        => Controls_Manager::SELECT,
					'label_block' => true,
					'options'     => [ '' => esc_html__( 'Choose a form...', 'sky-elementor-addons' ) ] + \sky_addons_get_ninjaform(),
					'description' => esc_html__( 'Select the Ninja Form you want to display.', 'sky-elementor-addons' ),
				]
			);

		}

		$this->end_controls_section();

		$this->__fields_style_controls();
		$this->__label_style_controls();
		$this->__submit_style_controls();
		$this->__container_style_controls();
		$this->__error_style_controls();
		$this->__success_style_controls();
	}

	protected function __fields_style_controls() {

		$this->start_controls_section(
			'section_fields_style',
			[
				'label' => esc_html__( 'Input Fields', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'field_margin',
			[
				'label'      => esc_html__( 'Field Spacing', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .nf-field-container:not(.submit-container)' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'field_padding',
			[
				'label'      => esc_html__( 'Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .nf-field-element input[type=text],
					{{WRAPPER}} .nf-field-element input[type=email],
					{{WRAPPER}} .nf-field-element input[type=tel],
					{{WRAPPER}} .nf-field-element input[type=number],
					{{WRAPPER}} .nf-field-element input[type=url],
					{{WRAPPER}} .nf-field-element input[type=date],
					{{WRAPPER}} .nf-field-element textarea,
					{{WRAPPER}} .nf-field-element select' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'field_height',
			[
				'label'      => esc_html__( 'Field Height', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 30,
						'max' => 100,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .nf-field-element input[type=text],
					{{WRAPPER}} .nf-field-element input[type=email],
					{{WRAPPER}} .nf-field-element input[type=tel],
					{{WRAPPER}} .nf-field-element input[type=number],
					{{WRAPPER}} .nf-field-element input[type=url],
					{{WRAPPER}} .nf-field-element input[type=date],
					{{WRAPPER}} .nf-field-element select' => 'height: {{SIZE}}{{UNIT}};',
				],
				'separator'  => 'before',
			]
		);

		$this->add_responsive_control(
			'field_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .nf-field-element input[type=text],
					{{WRAPPER}} .nf-field-element input[type=email],
					{{WRAPPER}} .nf-field-element input[type=tel],
					{{WRAPPER}} .nf-field-element input[type=number],
					{{WRAPPER}} .nf-field-element input[type=url],
					{{WRAPPER}} .nf-field-element input[type=date],
					{{WRAPPER}} .nf-field-element textarea,
					{{WRAPPER}} .nf-field-element select' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'field_typography',
				'selector' => '{{WRAPPER}} .nf-field-element input[type=text],
					{{WRAPPER}} .nf-field-element input[type=email],
					{{WRAPPER}} .nf-field-element input[type=tel],
					{{WRAPPER}} .nf-field-element input[type=number],
					{{WRAPPER}} .nf-field-element input[type=url],
					{{WRAPPER}} .nf-field-element input[type=date],
					{{WRAPPER}} .nf-field-element textarea,
					{{WRAPPER}} .nf-field-element select',
				'global' => [
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				],
			]
		);

		$this->add_control(
			'field_text_color',
			[
				'label' => esc_html__( 'Text Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nf-field-element input[type=text],
					{{WRAPPER}} .nf-field-element input[type=email],
					{{WRAPPER}} .nf-field-element input[type=tel],
					{{WRAPPER}} .nf-field-element input[type=number],
					{{WRAPPER}} .nf-field-element input[type=url],
					{{WRAPPER}} .nf-field-element input[type=date],
					{{WRAPPER}} .nf-field-element textarea,
					{{WRAPPER}} .nf-field-element select' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'field_placeholder_color',
			[
				'label' => esc_html__( 'Placeholder Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} ::-webkit-input-placeholder' => 'color: {{VALUE}};',
					'{{WRAPPER}} ::-moz-placeholder'      => 'color: {{VALUE}};',
					'{{WRAPPER}} ::-ms-input-placeholder' => 'color: {{VALUE}};',
					'{{WRAPPER}} ::placeholder'           => 'color: {{VALUE}};',
				],
			]
		);

		$this->start_controls_tabs( 'tabs_field_state' );

		$this->start_controls_tab(
			'tab_field_normal',
			[
				'label' => esc_html__( 'Normal', 'sky-elementor-addons' ),
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'field_border',
				'selector' => '{{WRAPPER}} .nf-field-element input[type=text],
					{{WRAPPER}} .nf-field-element input[type=email],
					{{WRAPPER}} .nf-field-element input[type=tel],
					{{WRAPPER}} .nf-field-element input[type=number],
					{{WRAPPER}} .nf-field-element input[type=url],
					{{WRAPPER}} .nf-field-element input[type=date],
					{{WRAPPER}} .nf-field-element textarea,
					{{WRAPPER}} .nf-field-element select',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'field_box_shadow',
				'selector' => '{{WRAPPER}} .nf-field-element input[type=text],
					{{WRAPPER}} .nf-field-element input[type=email],
					{{WRAPPER}} .nf-field-element input[type=tel],
					{{WRAPPER}} .nf-field-element input[type=number],
					{{WRAPPER}} .nf-field-element input[type=url],
					{{WRAPPER}} .nf-field-element input[type=date],
					{{WRAPPER}} .nf-field-element textarea,
					{{WRAPPER}} .nf-field-element select',
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'  => 'field_background',
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .nf-field-element input[type=text],
					{{WRAPPER}} .nf-field-element input[type=email],
					{{WRAPPER}} .nf-field-element input[type=tel],
					{{WRAPPER}} .nf-field-element input[type=number],
					{{WRAPPER}} .nf-field-element input[type=url],
					{{WRAPPER}} .nf-field-element input[type=date],
					{{WRAPPER}} .nf-field-element textarea,
					{{WRAPPER}} .nf-field-element select',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_field_focus',
			[
				'label' => esc_html__( 'Focus', 'sky-elementor-addons' ),
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'field_focus_border',
				'selector' => '{{WRAPPER}} .nf-field-element input[type=text]:focus,
					{{WRAPPER}} .nf-field-element input[type=email]:focus,
					{{WRAPPER}} .nf-field-element input[type=tel]:focus,
					{{WRAPPER}} .nf-field-element input[type=number]:focus,
					{{WRAPPER}} .nf-field-element input[type=url]:focus,
					{{WRAPPER}} .nf-field-element input[type=date]:focus,
					{{WRAPPER}} .nf-field-element textarea:focus,
					{{WRAPPER}} .nf-field-element select:focus',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'field_focus_box_shadow',
				'selector' => '{{WRAPPER}} .nf-field-element input[type=text]:focus,
					{{WRAPPER}} .nf-field-element input[type=email]:focus,
					{{WRAPPER}} .nf-field-element input[type=tel]:focus,
					{{WRAPPER}} .nf-field-element input[type=number]:focus,
					{{WRAPPER}} .nf-field-element input[type=url]:focus,
					{{WRAPPER}} .nf-field-element input[type=date]:focus,
					{{WRAPPER}} .nf-field-element textarea:focus,
					{{WRAPPER}} .nf-field-element select:focus',
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'  => 'field_focus_background',
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .nf-field-element input[type=text]:focus,
					{{WRAPPER}} .nf-field-element input[type=email]:focus,
					{{WRAPPER}} .nf-field-element input[type=tel]:focus,
					{{WRAPPER}} .nf-field-element input[type=number]:focus,
					{{WRAPPER}} .nf-field-element input[type=url]:focus,
					{{WRAPPER}} .nf-field-element input[type=date]:focus,
					{{WRAPPER}} .nf-field-element textarea:focus,
					{{WRAPPER}} .nf-field-element select:focus',
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	protected function __label_style_controls() {

		$this->start_controls_section(
			'section_label_style',
			[
				'label' => esc_html__( 'Field Labels', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'label_margin',
			[
				'label'      => esc_html__( 'Margin', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .nf-field-label label' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'label_padding',
			[
				'label'      => esc_html__( 'Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .nf-field-label label' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'label_divider',
			[
				'type'  => Controls_Manager::DIVIDER,
				'style' => 'thick',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'label_typography',
				'selector' => '{{WRAPPER}} .nf-field-label label',
				'global'   => [
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'desc_typography',
				'label'    => esc_html__( 'Description Typography', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .nf-field-description p',
				'global'   => [
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				],
			]
		);

		$this->add_control(
			'label_color',
			[
				'label' => esc_html__( 'Label Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nf-field-label label' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'required_label_color',
			[
				'label' => esc_html__( 'Required Symbol Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ninja-forms-req-symbol' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'desc_color',
			[
				'label' => esc_html__( 'Description Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nf-field-description p' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function __submit_style_controls() {

		$this->start_controls_section(
			'section_submit_style',
			[
				'label' => esc_html__( 'Submit Button', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'submit_btn_position',
			[
				'label'   => esc_html__( 'Button Position', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::CHOOSE,
				'options' => [
					'left'   => [
						'title' => esc_html__( 'Left', 'sky-elementor-addons' ),
						'icon'  => 'eicon-h-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'sky-elementor-addons' ),
						'icon'  => 'eicon-h-align-center',
					],
					'right'  => [
						'title' => esc_html__( 'Right', 'sky-elementor-addons' ),
						'icon'  => 'eicon-h-align-right',
					],
				],
				'default' => 'left',
				'toggle'  => false,
				'selectors_dictionary' => [
					'left'   => 'text-align: left;',
					'center' => 'text-align: center;',
					'right'  => 'text-align: right;',
				],
				'selectors' => [
					'{{WRAPPER}} .field-wrap.submit-wrap' => '{{VALUE}}',
				],
			]
		);

		$this->add_responsive_control(
			'submit_margin',
			[
				'label'      => esc_html__( 'Margin', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .submit-container input[type=submit]' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'submit_padding',
			[
				'label'      => esc_html__( 'Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .submit-container input[type=submit]' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'submit_typography',
				'selector' => '{{WRAPPER}} .submit-container input[type=submit]',
				'global'   => [
					'default' => Global_Typography::TYPOGRAPHY_ACCENT,
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'submit_border',
				'selector' => '{{WRAPPER}} .submit-container input[type=submit]',
			]
		);

		$this->add_responsive_control(
			'submit_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .submit-container input[type=submit]' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'      => 'submit_box_shadow',
				'selector'  => '{{WRAPPER}} .submit-container input[type=submit]',
				'separator' => 'after',
			]
		);

		$this->start_controls_tabs( 'tabs_button_style' );

		$this->start_controls_tab(
			'tab_button_normal',
			[
				'label' => esc_html__( 'Normal', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'submit_color',
			[
				'label' => esc_html__( 'Text Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .submit-container input[type=submit]' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'submit_background',
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .submit-container input[type=submit]',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_button_hover',
			[
				'label' => esc_html__( 'Hover', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'submit_hover_color',
			[
				'label' => esc_html__( 'Text Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .submit-container input[type=submit]:hover,
					{{WRAPPER}} .submit-container input[type=submit]:focus' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'  => 'submit_hover_background',
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .submit-container input[type=submit]:hover,
					{{WRAPPER}} .submit-container input[type=submit]:focus',
			]
		);

		$this->add_control(
			'submit_hover_border_color',
			[
				'label' => esc_html__( 'Border Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .submit-container input[type=submit]:hover,
					{{WRAPPER}} .submit-container input[type=submit]:focus' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	protected function __container_style_controls() {

		$this->start_controls_section(
			'section_container_style',
			[
				'label' => esc_html__( 'Form Container', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
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
					'{{WRAPPER}} .sa-ninja-form-wrapper' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
					'{{WRAPPER}} .sa-ninja-form-wrapper' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'container_background',
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .sa-ninja-form-wrapper',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'container_border',
				'selector' => '{{WRAPPER}} .sa-ninja-form-wrapper',
			]
		);

		$this->add_responsive_control(
			'container_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-ninja-form-wrapper' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'container_box_shadow',
				'selector' => '{{WRAPPER}} .sa-ninja-form-wrapper',
			]
		);

		$this->end_controls_section();
	}

	protected function __error_style_controls() {

		$this->start_controls_section(
			'section_error_style',
			[
				'label' => esc_html__( 'Validation Error', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'error_field_border',
				'selector' => '{{WRAPPER}} .nf-error .nf-field-element input,
					{{WRAPPER}} .nf-error .nf-field-element textarea,
					{{WRAPPER}} .nf-error .nf-field-element select',
			]
		);

		$this->add_control(
			'error_field_text_color',
			[
				'label' => esc_html__( 'Error Text Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nf-error .nf-field-element input,
					{{WRAPPER}} .nf-error .nf-field-element textarea,
					{{WRAPPER}} .nf-error .nf-field-element select' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'  => 'error_field_background',
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .nf-error .nf-field-element input,
					{{WRAPPER}} .nf-error .nf-field-element textarea,
					{{WRAPPER}} .nf-error .nf-field-element select',
			]
		);

		$this->add_control(
			'error_message_heading',
			[
				'label'     => esc_html__( 'Error Message', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'error_message_typography',
				'selector' => '{{WRAPPER}} .nf-error-msg',
				'global'   => [
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				],
			]
		);

		$this->add_control(
			'error_message_color',
			[
				'label' => esc_html__( 'Message Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nf-error-msg' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'error_message_padding',
			[
				'label'      => esc_html__( 'Message Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .nf-error-msg' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function __success_style_controls() {

		$this->start_controls_section(
			'section_success_style',
			[
				'label' => esc_html__( 'Success Message', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'success_message_padding',
			[
				'label'      => esc_html__( 'Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .nf-response-msg' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'success_message_margin',
			[
				'label'      => esc_html__( 'Margin', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .nf-response-msg' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'success_message_typography',
				'selector' => '{{WRAPPER}} .nf-response-msg',
				'global'   => [
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				],
			]
		);

		$this->add_control(
			'success_message_color',
			[
				'label' => esc_html__( 'Text Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nf-response-msg' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'success_message_background',
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .nf-response-msg',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'success_message_border',
				'selector' => '{{WRAPPER}} .nf-response-msg',
			]
		);

		$this->add_responsive_control(
			'success_message_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .nf-response-msg' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'success_message_box_shadow',
				'selector' => '{{WRAPPER}} .nf-response-msg',
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		if ( ! sky_addons_is_ninjaforms_activated() ) {
			sky_addons_show_plugin_missing_alert( esc_html__( 'Ninja Forms', 'sky-elementor-addons' ) );
			return;
		}

		$settings = $this->get_settings_for_display();

		if ( empty( $settings['form_id'] ) ) {
			?>
			<div class="elementor-alert elementor-alert-warning">
				<?php esc_html_e( 'Please select a Ninja Form from the widget settings.', 'sky-elementor-addons' ); ?>
			</div>
			<?php
			return;
		}

		$this->add_render_attribute( 'ninja-form-wrapper', 'class', 'sa-ninja-form-wrapper' );

		$output = sky_addons_do_shortcode( 'ninja_form', [ 'id' => absint( $settings['form_id'] ) ] );

		if ( false === $output ) {
			return;
		}

		?>
		<div <?php $this->print_render_attribute_string( 'ninja-form-wrapper' ); ?>>
			<?php echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Ninja Forms shortcode output ?>
		</div>
		<?php
	}
}

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
	exit; // Exit if accessed directly
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

	protected function register_controls() {

		$this->start_controls_section(
			'_section_ninjaforms',
			[
				'label' => sky_addons_is_ninjaforms_activated() ? __( 'Ninja Forms Settings', 'sky-elementor-addons' ) : __( 'Missing Notice', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		if ( ! sky_addons_is_ninjaforms_activated() ) {

			$this->add_control(
				'_ninjaforms_missing_notice',
				[
					'type'            => Controls_Manager::RAW_HTML,
					'raw'             => sprintf(
						__( 'Hello %2$s! It looks like %1$s is not installed or activated on your site. Please install and activate it to use this widget. Click the link below to install/activate %1$s, then refresh this page.', 'sky-elementor-addons' ),
						'<a href="' . esc_url( admin_url( 'plugin-install.php?s=Ninja+Forms&tab=search&type=term' ) ) . '" target="_blank" rel="noopener"><strong>Ninja Forms</strong></a>',
						sky_addons_get_current_user_display_name()
					),
					'content_classes' => 'elementor-panel-alert elementor-panel-alert-danger',
				]
			);

			$this->add_control(
				'_ninjaforms_install',
				[
					'type' => Controls_Manager::RAW_HTML,
					'raw'  => '<a href="' . esc_url( admin_url( 'plugin-install.php?s=Ninja+Forms&tab=search&type=term' ) ) . '" target="_blank" rel="noopener">Click to install or activate Ninja Forms</a>',
				]
			);

		} else {

			$this->add_control(
				'form_id',
				[
					'label'       => __( 'Select Ninja Form', 'sky-elementor-addons' ),
					'type'        => Controls_Manager::SELECT,
					'label_block' => true,
					'options'     => [ '' => __( 'Choose a form...', 'sky-elementor-addons' ) ] + \sky_addons_get_ninjaform(),
					'description' => __( 'Select the Ninja Form you want to display.', 'sky-elementor-addons' ),
				]
			);

		}
		$this->end_controls_section();

		$this->__fields_style_controls();
		$this->__label_style_controls();
		$this->__submit_style_controls();
	}

	protected function __fields_style_controls() {

		$this->start_controls_section(
			'_section_fields_style',
			[
				'label' => __( 'Input Fields', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'field_margin',
			[
				'label'      => __( 'Field Spacing', 'sky-elementor-addons' ),
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
				'label'      => __( 'Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .textbox-wrap input[type=text], {{WRAPPER}} .email-wrap input, {{WRAPPER}} .textarea-wrap textarea' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'field_height',
			[
				'label'      => __( 'Field Height', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 30,
						'max' => 80,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .textbox-wrap input[type=text], {{WRAPPER}} .email-wrap input' => 'height: {{SIZE}}{{UNIT}};',
				],
				'separator'  => 'before',
			]
		);

		$this->add_responsive_control(
			'field_border_radius',
			[
				'label'      => __( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .textbox-wrap input[type=text], {{WRAPPER}} .email-wrap input, {{WRAPPER}} .textarea-wrap textarea' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'field_typography',
				'label'    => __( 'Typography', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .textbox-wrap input[type=text], {{WRAPPER}} .email-wrap input, {{WRAPPER}} .textarea-wrap textarea',
				'global'   => [
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				],
			]
		);

		$this->add_control(
			'field_textcolor',
			[
				'label'     => __( 'Text Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .textbox-wrap input[type=text], {{WRAPPER}} .email-wrap input, {{WRAPPER}} .textarea-wrap textarea' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'field_placeholder_color',
			[
				'label'     => __( 'Placeholder Color', 'sky-elementor-addons' ),
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
				'selector' => '{{WRAPPER}} .textbox-wrap input[type=text], {{WRAPPER}} .email-wrap input, {{WRAPPER}} .textarea-wrap textarea',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'field_box_shadow',
				'selector' => '{{WRAPPER}} .textbox-wrap input[type=text], {{WRAPPER}} .email-wrap input, {{WRAPPER}} .textarea-wrap textarea',
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'field_bg_color',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .textbox-wrap input[type=text], {{WRAPPER}} .email-wrap input, {{WRAPPER}} .textarea-wrap textarea',
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
				'selector' => '{{WRAPPER}} .textbox-wrap input[type=text]:focus, {{WRAPPER}} .email-wrap input:focus, {{WRAPPER}} .textarea-wrap textarea:focus',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'field_focus_box_shadow',
				'exclude'  => [
					'box_shadow_position',
				],
				'selector' => '{{WRAPPER}} .textbox-wrap input[type=text]:focus, {{WRAPPER}} .email-wrap input:focus, {{WRAPPER}} .textarea-wrap textarea:focus',
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'field_focus_bg_color',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .textbox-wrap input[type=text]:focus, {{WRAPPER}} .email-wrap input:focus, {{WRAPPER}} .textarea-wrap textarea:focus',
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	protected function __label_style_controls() {

		$this->start_controls_section(
			'ninjaf-form-label',
			[
				'label' => __( 'Field Labels', 'sky-elementor-addons' ),
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
					'{{WRAPPER}} .textbox-wrap label, {{WRAPPER}} .email-wrap label, {{WRAPPER}} .textarea-wrap label' => 'display: inline-block; padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
					'{{WRAPPER}} .textbox-wrap label, {{WRAPPER}} .email-wrap label, {{WRAPPER}} .textarea-wrap label' => 'display: inline-block; padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
				'selector' => '{{WRAPPER}} .textbox-wrap label, {{WRAPPER}} .email-wrap label, {{WRAPPER}} .textarea-wrap label',
				'global'   => [
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'desc_typography',
				'label'    => __( 'Description Typography', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .nf-field-description p',
				'global'   => [
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				],
			]
		);

		$this->add_control(
			'label_color',
			[
				'label'     => __( 'Label Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .textbox-wrap label, {{WRAPPER}} .email-wrap label, {{WRAPPER}} .textarea-wrap label' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'requered_label',
			[
				'label'     => __( 'Required Symbol Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ninja-forms-req-symbol' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'desc_color',
			[
				'label'     => __( 'Description Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nf-field-description p' => 'color: {{VALUE}}',
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
				'desktop_default' => 'left',
				'toggle'          => false,
				'prefix_class'    => 'sky-form-btn--%s',
				'selectors'       => [
					'{{WRAPPER}} .field-wrap.submit-wrap' => 'text-align: {{Value}};',
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
					'{{WRAPPER}} .submit-container input' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
					'{{WRAPPER}} .submit-container input' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'submit_typography',
				'selector' => '{{WRAPPER}} .submit-container input',
				'global'   => [
					'default' => Global_Typography::TYPOGRAPHY_ACCENT,
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'submit_border',
				'selector' => '{{WRAPPER}} .submit-container input',
			]
		);

		$this->add_control(
			'submit_border_radius',
			[
				'label'      => __( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .submit-container input' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'submit_box_shadow',
				'selector' => '{{WRAPPER}} .submit-container input',
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
					'{{WRAPPER}} .submit-container input' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'submit_bg_color',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .submit-container input',
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
					'{{WRAPPER}} .submit-container input:hover, {{WRAPPER}} .submit-container input:focus' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'submit_hover_bg_color',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .submit-container input:hover, {{WRAPPER}} .submit-container input:focus',
			]
		);

		$this->add_control(
			'submit_hover_border_color',
			[
				'label'     => __( 'Border Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .submit-container input:hover, {{WRAPPER}} .submit-container input:focus' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	protected function render() {
		if ( ! sky_addons_is_ninjaforms_activated() ) {
			sky_addons_show_plugin_missing_alert( __( 'Ninja Forms', 'sky-elementor-addons' ) );
			return;
		}

		$settings = $this->get_settings_for_display();

		if ( empty( $settings['form_id'] ) ) {
			echo '<div class="elementor-alert elementor-alert-warning">';
			echo __( 'Please select a Ninja Form from the widget settings.', 'sky-elementor-addons' );
			echo '</div>';
			return;
		}

		echo sky_addons_do_shortcode( 'ninja_form', [
			'id' => $settings['form_id'],
		] );
	}
}

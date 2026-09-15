<?php

namespace Sky_Addons\Modules\Cf7\Widgets;

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

class Cf7 extends Widget_Base {

	public function get_name() {
		return 'sky-cf7';
	}

	public function get_title() {
		return esc_html__( 'Contact Form 7', 'sky-elementor-addons' );
	}

	public function get_icon() {
		return 'sky-icon-cf7';
	}

	public function get_categories() {
		return [ 'sky-elementor-addons' ];
	}

	public function get_keywords() {
		return [ 'sky', 'cf7', 'contact', 'form', 'seven', 'mail', 'email', 'message' ];
	}

	public function get_custom_help_url() {
		return 'https://skyaddons.com/docs/sky-addons/forms/contact-form-7/';
	}

	public function has_widget_inner_wrapper(): bool {
		return ! \Elementor\Plugin::$instance->experiments->is_feature_active( 'e_optimized_markup' );
	}

	protected function register_controls() {

		$this->start_controls_section(
			'section_cf7_form',
			[
				'label' => sky_addons_is_cf7_activated() ? esc_html__( 'Contact Form Settings', 'sky-elementor-addons' ) : esc_html__( 'Missing Notice', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		if ( ! sky_addons_is_cf7_activated() ) {

			$this->add_control(
				'_cf7_missing_notice',
				[
					'type'            => Controls_Manager::RAW_HTML,
					'raw'             => sprintf(
						/* translators: %1$s: plugin name linked to its install page, %2$s: current user's display name. */
						esc_html__( 'Hello %2$s! It looks like %1$s is not installed or activated on your site. Please install and activate it to use this widget. Click the link below to install/activate %1$s, then refresh this page.', 'sky-elementor-addons' ),
						'<a href="' . esc_url( admin_url( 'plugin-install.php?s=Contact+Form+7&tab=search&type=term' ) ) . '" target="_blank" rel="noopener"><strong>Contact Form 7</strong></a>',
						sky_addons_get_current_user_display_name()
					),
					'content_classes' => 'elementor-panel-alert elementor-panel-alert-danger',
				]
			);

			$this->add_control(
				'_cf7_install',
				[
					'type' => Controls_Manager::RAW_HTML,
					'raw'  => '<a href="' . esc_url( admin_url( 'plugin-install.php?s=Contact+Form+7&tab=search&type=term' ) ) . '" target="_blank" rel="noopener">' . esc_html__( 'Click to install or activate Contact Form 7', 'sky-elementor-addons' ) . '</a>',
				]
			);

		} else {

			$this->add_control(
				'form_id',
				[
					'label'       => esc_html__( 'Select Contact Form', 'sky-elementor-addons' ),
					'type'        => Controls_Manager::SELECT,
					'label_block' => true,
					'options'     => [ '' => esc_html__( 'Choose a form...', 'sky-elementor-addons' ) ] + \sky_addons_get_cf7_forms(),
					'description' => esc_html__( 'Select the Contact Form 7 form you want to display.', 'sky-elementor-addons' ),
				]
			);

			$this->add_control(
				'html_class',
				[
					'label'       => esc_html__( 'Custom CSS Class', 'sky-elementor-addons' ),
					'type'        => Controls_Manager::TEXT,
					'label_block' => true,
					'placeholder' => esc_html__( 'my-custom-class', 'sky-elementor-addons' ),
					'description' => esc_html__( 'Add a custom CSS class to the form wrapper for additional styling.', 'sky-elementor-addons' ),
				]
			);

		}

		$this->end_controls_section();

		/**
		 * Style Tab: Input Fields
		 */
		$this->start_controls_section(
			'_section_fields_style',
			[
				'label' => esc_html__( 'Input Fields', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'field_width',
			[
				'label'          => esc_html__( 'Field Width', 'sky-elementor-addons' ),
				'type'           => Controls_Manager::SLIDER,
				'default'        => [ 'unit' => '%' ],
				'tablet_default' => [ 'unit' => '%' ],
				'mobile_default' => [ 'unit' => '%' ],
				'size_units'     => [ '%', 'px' ],
				'range'          => [
					'%'  => [
						'min' => 1,
						'max' => 100,
					],
					'px' => [
						'min' => 1,
						'max' => 800,
					],
				],
				'selectors'      => [
					'{{WRAPPER}} .wpcf7-form-control:not(.wpcf7-submit)' => 'width: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .sky-cf7-form label' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'field_margin',
			[
				'label'      => esc_html__( 'Field Spacing', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .wpcf7-form-control:not(.wpcf7-submit)' => 'margin-bottom: {{SIZE}}{{UNIT}};',
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
						'max' => 150,
					],
				],
				// line-height matches height so single-line text stays vertically centered
				'selectors'  => [
					'{{WRAPPER}} .wpcf7-form-control:not(.wpcf7-submit):not(.wpcf7-textarea)' => 'height: {{SIZE}}{{UNIT}}; line-height: {{SIZE}}{{UNIT}};',
				],
				'separator'  => 'before',
			]
		);

		$this->add_responsive_control(
			'field_padding',
			[
				'label'      => esc_html__( 'Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', 'rem', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .wpcf7-form-control:not(.wpcf7-submit)' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'field_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .wpcf7-form-control:not(.wpcf7-submit)' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
				'label'    => esc_html__( 'Typography', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .wpcf7-form-control:not(.wpcf7-submit)',
				'global'   => [
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				],
			]
		);

		$this->add_control(
			'field_color',
			[
				'label' => esc_html__( 'Text Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpcf7-form-control:not(.wpcf7-submit)' => 'color: {{VALUE}};',
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
				'name'     => 'field_border',
				'selector' => '{{WRAPPER}} .wpcf7-form-control:not(.wpcf7-submit)',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'field_box_shadow',
				'selector' => '{{WRAPPER}} .wpcf7-form-control:not(.wpcf7-submit)',
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'field_bg_color',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .wpcf7-form-control:not(.wpcf7-submit)',
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
				'name'     => 'field_focus_border',
				'selector' => '{{WRAPPER}} .wpcf7-form-control:not(.wpcf7-submit):focus',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'field_focus_box_shadow',
				'exclude'  => [ 'box_shadow_position' ],
				'selector' => '{{WRAPPER}} .wpcf7-form-control:not(.wpcf7-submit):focus',
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'field_focus_bg_color',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .wpcf7-form-control:not(.wpcf7-submit):focus',
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();

		/**
		 * Style Tab: Field Labels
		 */
		$this->start_controls_section(
			'form_labels',
			[
				'label' => esc_html__( 'Field Labels', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'label_margin',
			[
				'label'      => esc_html__( 'Label Spacing', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .sky-cf7-form label' => 'margin-top: {{SIZE}}{{UNIT}};',
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
				'label'    => esc_html__( 'Typography', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sky-cf7-form label',
				'global'   => [
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				],
			]
		);

		$this->add_control(
			'label_color',
			[
				'label' => esc_html__( 'Text Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sky-cf7-form label' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		/**
		 * Style Tab: Submit Button
		 */
		$this->start_controls_section(
			'submit',
			[
				'label' => esc_html__( 'Submit Button', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'submit_width',
			[
				'label'      => esc_html__( 'Button Width', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range'      => [
					'px' => [
						'min' => 50,
						'max' => 500,
					],
					'%'  => [
						'min' => 1,
						'max' => 100,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .wpcf7-submit' => 'width: {{SIZE}}{{UNIT}};',
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
					'{{WRAPPER}} .wpcf7-submit' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'submit_padding',
			[
				'label'      => esc_html__( 'Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', 'rem', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .wpcf7-submit' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'submit_typography',
				'selector' => '{{WRAPPER}} .wpcf7-submit',
				'global'   => [
					'default' => Global_Typography::TYPOGRAPHY_ACCENT,
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'submit_border',
				'selector' => '{{WRAPPER}} .wpcf7-submit',
			]
		);

		$this->add_responsive_control(
			'submit_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .wpcf7-submit' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'submit_box_shadow',
				'selector' => '{{WRAPPER}} .wpcf7-submit',
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
				'label' => esc_html__( 'Normal', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'submit_color',
			[
				'label' => esc_html__( 'Text Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpcf7-submit' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'submit_bg_color',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .wpcf7-submit',
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
					'{{WRAPPER}} .wpcf7-submit:hover, {{WRAPPER}} .wpcf7-submit:focus' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'submit_hover_bg_color',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .wpcf7-submit:hover, {{WRAPPER}} .wpcf7-submit:focus',
			]
		);

		$this->add_control(
			'submit_hover_border_color',
			[
				'label' => esc_html__( 'Border Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpcf7-submit:hover, {{WRAPPER}} .wpcf7-submit:focus' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'submit_hover_box_shadow',
				'selector' => '{{WRAPPER}} .wpcf7-submit:hover, {{WRAPPER}} .wpcf7-submit:focus',
			]
		);

		$this->add_control(
			'submit_hover_transition',
			[
				'label'      => esc_html__( 'Transition Duration', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 's', 'ms' ],
				'default'    => [
					'unit' => 's',
					'size' => 0.3,
				],
				'range'      => [
					's'  => [
						'min'  => 0,
						'max'  => 3,
						'step' => 0.1,
					],
					'ms' => [
						'min'  => 0,
						'max'  => 3000,
						'step' => 50,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .wpcf7-submit' => 'transition-duration: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();

		/**
		 * Style Tab: Response Message
		 */
		$this->start_controls_section(
			'section_response_style',
			[
				'label' => esc_html__( 'Response Message', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'response_padding',
			[
				'label'      => esc_html__( 'Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', 'rem', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .wpcf7-response-output' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'response_margin',
			[
				'label'      => esc_html__( 'Margin', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', 'rem', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .wpcf7-response-output' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'response_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .wpcf7-response-output' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'response_typography',
				'selector' => '{{WRAPPER}} .wpcf7-response-output',
				'global'   => [
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				],
			]
		);

		$this->add_control(
			'hr_response',
			[
				'type'  => Controls_Manager::DIVIDER,
				'style' => 'thick',
			]
		);

		$this->start_controls_tabs( 'tabs_response_state' );

		$this->start_controls_tab(
			'tab_response_success',
			[
				'label' => esc_html__( 'Success', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'response_success_color',
			[
				'label' => esc_html__( 'Text Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpcf7-form.sent .wpcf7-response-output' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'response_success_bg_color',
			[
				'label' => esc_html__( 'Background Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpcf7-form.sent .wpcf7-response-output' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'response_success_border_color',
			[
				'label' => esc_html__( 'Border Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpcf7-form.sent .wpcf7-response-output' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_response_error',
			[
				'label' => esc_html__( 'Error', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'response_error_color',
			[
				'label' => esc_html__( 'Text Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpcf7-form.invalid .wpcf7-response-output, {{WRAPPER}} .wpcf7-form.spam .wpcf7-response-output, {{WRAPPER}} .wpcf7-form.failed .wpcf7-response-output' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'response_error_bg_color',
			[
				'label' => esc_html__( 'Background Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpcf7-form.invalid .wpcf7-response-output, {{WRAPPER}} .wpcf7-form.spam .wpcf7-response-output, {{WRAPPER}} .wpcf7-form.failed .wpcf7-response-output' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'response_error_border_color',
			[
				'label' => esc_html__( 'Border Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpcf7-form.invalid .wpcf7-response-output, {{WRAPPER}} .wpcf7-form.spam .wpcf7-response-output, {{WRAPPER}} .wpcf7-form.failed .wpcf7-response-output' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	protected function render() {
		if ( ! sky_addons_is_cf7_activated() ) {
			sky_addons_show_plugin_missing_alert( esc_html__( 'Contact Form 7', 'sky-elementor-addons' ) );
			return;
		}

		$settings = $this->get_settings_for_display();

		if ( empty( $settings['form_id'] ) ) {
			printf(
				'<div class="elementor-alert elementor-alert-warning">%s</div>',
				esc_html__( 'Please select a Contact Form 7 form from the widget settings.', 'sky-elementor-addons' )
			);
			return;
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Form markup from Contact Form 7's shortcode, escaped by that plugin; wp_kses_post() would strip the form fields.
		echo sky_addons_do_shortcode( 'contact-form-7', [
			'id'         => $settings['form_id'],
			'html_class' => 'sky-cf7-form ' . sky_addons_sanitize_html_class_param( $settings['html_class'] ),
		] );
	}
}

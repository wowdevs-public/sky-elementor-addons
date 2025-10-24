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
	exit; // Exit if accessed directly
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

	protected function register_controls() {

		$this->start_controls_section(
			'section_cf7_form',
			[
				'label' => sky_addons_is_cf7_activated() ? __( 'Contact Form Settings', 'sky-elementor-addons' ) : __( 'Missing Notice', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		if ( ! sky_addons_is_cf7_activated() ) {

			$this->add_control(
				'_cf7_missing_notice',
				[
					'type'            => Controls_Manager::RAW_HTML,
					'raw'             => sprintf(
						__( 'Hello %2$s! It looks like %1$s is not installed or activated on your site. Please install and activate it to use this widget. Click the link below to install/activate %1$s, then refresh this page.', 'sky-elementor-addons' ),
						'<a href="' . esc_url( admin_url( 'plugin-install.php?s=Contact+Form+7&tab=search&type=term' ) )
						. '" target="_blank" rel="noopener"><strong>Contact Form 7</strong></a>',
						sky_addons_get_current_user_display_name()
					),
					'content_classes' => 'elementor-panel-alert elementor-panel-alert-danger',
				]
			);

				$this->add_control(
					'_cf7_install',
					[
						'type' => Controls_Manager::RAW_HTML,
						'raw'  => '<a href="' . esc_url( admin_url( 'plugin-install.php?s=Contact+Form+7&tab=search&type=term' ) ) . '" target="_blank" rel="noopener">Click to install or activate Contact Form 7</a>',
					]
				);

		} else {

			$this->add_control(
				'form_id',
				[
					'label'       => __( 'Select Contact Form', 'sky-elementor-addons' ),
					'type'        => Controls_Manager::SELECT,
					'label_block' => true,
					'options'     => [ '' => __( 'Choose a form...', 'sky-elementor-addons' ) ] + \sky_addons_get_cf7_forms(),
					'description' => __( 'Select the Contact Form 7 form you want to display.', 'sky-elementor-addons' ),
				]
			);

			$this->add_control(
				'html_class',
				[
					'label'       => __( 'Custom CSS Class', 'sky-elementor-addons' ),
					'type'        => Controls_Manager::TEXT,
					'label_block' => true,
					'placeholder' => __( 'my-custom-class', 'sky-elementor-addons' ),
					'description' => __( 'Add a custom CSS class to the form wrapper for additional styling.', 'sky-elementor-addons' ),
				]
			);

		}

		$this->end_controls_section();

		$this->start_controls_section(
			'_section_fields_style',
			[
				'label' => __( 'Input Fields', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'field_width',
			[
				'label'          => __( 'Field Width', 'sky-elementor-addons' ),
				'type'           => Controls_Manager::SLIDER,
				'default'        => [
					'unit' => '%',
				],
				'tablet_default' => [
					'unit' => '%',
				],
				'mobile_default' => [
					'unit' => '%',
				],
				'size_units'     => [ '%', 'px' ],
				'range'          => [
					'%' => [
						'min' => 1,
						'max' => 100,
					],
					'px' => [
						'min' => 1,
						'max' => 500,
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
				'label'      => __( 'Field Spacing', 'sky-elementor-addons' ),
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
					'{{WRAPPER}} .wpcf7-form-control:not(.wpcf7-submit):not(.wpcf7-textarea)' => 'height: {{SIZE}}{{UNIT}};',
				],
				'separator'  => 'before',
			]
		);

		$this->add_responsive_control(
			'field_padding',
			[
				'label'      => __( 'Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .wpcf7-form-control:not(.wpcf7-submit)' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
				'label'    => __( 'Typography', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .wpcf7-form-control:not(.wpcf7-submit)',
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
					'{{WRAPPER}} .wpcf7-form-control:not(.wpcf7-submit)' => 'color: {{VALUE}}',
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
				'label' => __( 'Focus', 'sky-elementor-addons' ),
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
				'exclude'  => [
					'box_shadow_position',
				],
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

		$this->start_controls_section(
			'form_labels',
			[
				'label' => __( 'Field Labels', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'label_margin',
			[
				'label'      => __( 'Label Spacing', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .wpcf7-form-control:not(.wpcf7-submit)' => 'margin-top: {{SIZE}}{{UNIT}};',
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
				'selector' => '{{WRAPPER}} label',
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
					'{{WRAPPER}} label' => 'color: {{VALUE}}',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'submit',
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
					'{{WRAPPER}} .wpcf7-submit' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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

		$this->add_control(
			'submit_border_radius',
			[
				'label'      => __( 'Border Radius', 'sky-elementor-addons' ),
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
				'label' => __( 'Hover', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'submit_hover_color',
			[
				'label'     => __( 'Text Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
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
				'label'     => __( 'Border Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpcf7-submit:hover, {{WRAPPER}} .wpcf7-submit:focus' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	protected function render() {
		if ( ! sky_addons_is_cf7_activated() ) {
			sky_addons_show_plugin_missing_alert( __( 'Contact Form 7', 'sky-elementor-addons' ) );
			return;
		}

		$settings = $this->get_settings_for_display();

		if ( empty( $settings['form_id'] ) ) {
			echo '<div class="elementor-alert elementor-alert-warning">';
			echo __( 'Please select a Contact Form 7 form from the widget settings.', 'sky-elementor-addons' );
			echo '</div>';
			return;
		}

		echo sky_addons_do_shortcode( 'contact-form-7', [
			'id'         => $settings['form_id'],
			'html_class' => 'sky-cf7-form ' . sky_addons_sanitize_html_class_param( $settings['html_class'] ),
		] );
	}
}

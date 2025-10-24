<?php

namespace Sky_Addons\Modules\WpForms\Widgets;

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

class WpForms extends Widget_Base {

	public function get_name() {
		return 'sky-wp-forms';
	}

	public function get_title() {
		return esc_html__( 'WP Forms', 'sky-elementor-addons' );
	}

	public function get_icon() {
		return 'sky-icon-wp-forms';
	}

	public function get_categories() {
		return [ 'sky-elementor-addons' ];
	}

	public function get_keywords() {
		return [ 'sky', 'wp', 'forms', 'wpforms', 'contact', 'form', 'mail', 'email', 'message', 'contact form', 'newsletter', 'subscription' ];
	}

	public function get_custom_help_url() {
		return 'https://skyaddons.com/docs/sky-addons/forms/wp-forms/';
	}

	public function is_reload_preview_required() {
		return true;
	}

	protected function register_controls() {

		$this->start_controls_section(
			'section_wpforms',
			[
				'label' => sky_addons_is_wpforms_activated() ? __( 'WPForms', 'sky-elementor-addons' ) : __( 'Missing Notice',
				'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		if ( ! sky_addons_is_wpforms_activated() ) {

			$this->add_control(
				'_wpforms_missing_notice',
				[
					'type'            => Controls_Manager::RAW_HTML,
					'raw'             => sprintf(
						__( 'Hello %2$s, looks like %1$s is missing in your site. Please click on the link below and install/activate %1$s. Make sure to refresh this page after installation or activation.', 'sky-elementor-addons' ),
						'<a href="' . esc_url( admin_url( 'plugin-install.php?s=WPForms&tab=search&type=term' ) ) . '" target="_blank" rel="noopener">WPForms</a>',
						sky_addons_get_current_user_display_name()
					),
					'content_classes' => 'elementor-panel-alert elementor-panel-alert-danger',
				]
			);

			$this->add_control(
				'_wpforms_install',
				[
					'type' => Controls_Manager::RAW_HTML,
					'raw'  => '<a href="' . esc_url( admin_url( 'plugin-install.php?s=WPForms&tab=search&type=term' ) ) . '" target="_blank" rel="noopener">Click to install or activate WPForms</a>',
				]
			);

		} else {

			$this->add_control(
				'form_id',
				[
					'label'       => __( 'Select Form', 'sky-elementor-addons' ),
					'type'        => Controls_Manager::SELECT,
					'label_block' => true,
					'options'     => [ '' => __( 'Select a WPForm', 'sky-elementor-addons' ) ] + \sky_addons_get_wpforms(),
					'description' => __( 'Choose the WPForm you want to display on this page.', 'sky-elementor-addons' ),
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
				'label' => __( 'Form Fields', 'sky-elementor-addons' ),
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
					'{{WRAPPER}} .wpforms-field:not(.wpforms-submit), .wpforms-field-required:not(.wpforms-submit)' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
					'{{WRAPPER}} .wpforms-field input'    => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .wpforms-field textarea' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
					'{{WRAPPER}} .wpforms-field input'    => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .wpforms-field textarea' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'field_typography',
				'label'    => __( 'Typography', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .wpforms-field input, {{WRAPPER}} .wpforms-field-textarea textarea',
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
					'{{WRAPPER}} .wpforms-field input, {{WRAPPER}} .wpforms-field-textarea textarea' => 'color: {{VALUE}}',
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
				'label' => __( 'Normal State', 'sky-elementor-addons' ),
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'field_border',
				'selector' => '{{WRAPPER}} .wpforms-field input, {{WRAPPER}} .wpforms-field-textarea textarea',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'field_box_shadow',
				'selector' => '{{WRAPPER}} .wpforms-field input, {{WRAPPER}} .wpforms-field-textarea textarea',
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'field_background',
				'label'    => __( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'exclude'  => [ 'image' ],
				'selector' => '{{WRAPPER}} .wpforms-field input, {{WRAPPER}} .wpforms-field-textarea textarea',
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
				'selector' => '{{WRAPPER}} .wpforms-field input:focus, {{WRAPPER}} .wpforms-field-textarea textarea:focus',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'field_focus_box_shadow',
				'exclude'  => [
					'box_shadow_position',
				],
				'selector' => '{{WRAPPER}} .wpforms-field input:focus, {{WRAPPER}} .wpforms-field-textarea textarea:focus',
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'field_focus_background',
				'label'    => __( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'exclude'  => [ 'image' ],
				'selector' => '{{WRAPPER}} .wpforms-field input:focus, {{WRAPPER}} .wpforms-field-textarea textarea:focus',
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	protected function __label_style_controls() {

		$this->start_controls_section(
			'wpf-form-label',
			[
				'label' => __( 'Form Labels', 'sky-elementor-addons' ),
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
					'{{WRAPPER}} .wpforms-field-container label.wpforms-field-label' => 'display: inline-block; padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
					'{{WRAPPER}} .wpforms-field-container label.wpforms-field-label' => 'display: inline-block; padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
				'selector' => '{{WRAPPER}} .wpforms-field-container label.wpforms-field-label',
				'global'   => [
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'sublabel_typography',
				'label'    => __( 'Sub Label Typography', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .wpforms-field-sublabel',
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
				'selector' => '{{WRAPPER}} .wpforms-field-description',
				'global'   => [
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				],
			]
		);

		$this->add_control(
			'label_color_popover',
			[
				'label'        => __( 'Colors', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::POPOVER_TOGGLE,
				'label_off'    => __( '', 'sky-elementor-addons' ),
				'label_on'     => __( 'Custom', 'sky-elementor-addons' ),
				'return_value' => 'yes',
			]
		);

		$this->start_popover();

		$this->add_control(
			'label_color',
			[
				'label'     => __( 'Label Text Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpforms-field-container label.wpforms-field-label' => 'color: {{VALUE}}',
				],
				'condition' => [
					'label_color_popover' => 'yes',
				],
			]
		);

		$this->add_control(
			'requered_label',
			[
				'label'     => __( 'Required Label Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpforms-required-label' => 'color: {{VALUE}}',
				],
				'condition' => [
					'label_color_popover' => 'yes',
				],
			]
		);

		$this->add_control(
			'sublabel_color',
			[
				'label'     => __( 'Sub Label Text Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpforms-field-sublabel' => 'color: {{VALUE}}',
				],
				'condition' => [
					'label_color_popover' => 'yes',
				],
			]
		);

		$this->add_control(
			'desc_label_color',
			[
				'label'     => __( 'Description Text Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpforms-field-description' => 'color: {{VALUE}}',
				],
				'condition' => [
					'label_color_popover' => 'yes',
				],
			]
		);

		$this->end_popover();

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
					'{{WRAPPER}} .wpforms-submit' => 'display: block; width: {{SIZE}}{{UNIT}};',
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
				'prefix_class'    => 'sky-form-btn--%s',
				'selectors'       => [
					'{{WRAPPER}} .wpforms-submit-container' => 'text-align: {{Value}};',
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
					'{{WRAPPER}} .wpforms-submit' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
					'{{WRAPPER}} .wpforms-submit' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'submit_typography',
				'selector' => '{{WRAPPER}} .wpforms-submit',
				'global'   => [
					'default' => Global_Typography::TYPOGRAPHY_ACCENT,
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'submit_border',
				'selector' => '{{WRAPPER}} .wpforms-form .wpforms-submit-container button[type=submit]',
			]
		);

		$this->add_control(
			'submit_border_radius',
			[
				'label'      => __( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .wpforms-submit' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'submit_box_shadow',
				'selector' => '{{WRAPPER}} .wpforms-submit',
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
					'{{WRAPPER}} .wpforms-container .wpforms-submit' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'submit_background',
				'label'    => __( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .wpforms-container .wpforms-submit',
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
					'{{WRAPPER}} .wpforms-container .wpforms-submit:hover, {{WRAPPER}} .wpforms-container .wpforms-submit:focus' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'submit_hover_background',
				'label'    => __( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .wpforms-container .wpforms-submit:hover, {{WRAPPER}} .wpforms-container .wpforms-submit:focus',
			]
		);

		$this->add_control(
			'submit_hover_border_color',
			[
				'label'     => __( 'Border Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpforms-container .wpforms-submit:hover, {{WRAPPER}} .wpforms-container .wpforms-submit:focus' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	protected function render() {
		if ( ! sky_addons_is_wpforms_activated() ) {
			sky_addons_show_plugin_missing_alert( __( 'WPForms', 'sky-elementor-addons' ) );
			return;
		}

		$settings = $this->get_settings_for_display();

		if ( ! empty( $settings['form_id'] ) ) {
			echo sky_addons_do_shortcode( 'wpforms', [
				'id' => $settings['form_id'],
			] );
		} else {
			echo '<div class="elementor-alert elementor-alert-warning">' . esc_html__( 'Please select a WPForm from the widget settings.', 'sky-elementor-addons' ) . '</div>';
		}
	}
}

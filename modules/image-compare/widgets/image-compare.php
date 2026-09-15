<?php

namespace Sky_Addons\Modules\ImageCompare\Widgets;

use Elementor\Utils;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Group_Control_Image_Size;
use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Image_Compare extends Widget_Base {


	public function get_name() {
		return 'sky-image-compare';
	}

	public function get_title() {
		return esc_html__( 'Image Compare', 'sky-elementor-addons' );
	}

	public function get_icon() {
		return 'sky-icon-image-compare';
	}

	public function get_categories() {
		return [ 'sky-elementor-addons' ];
	}

	public function get_keywords() {
		return [ 'sky', 'image', 'compare' ];
	}
	public function get_style_depends() {
		if ( sky_addons_editor_mode() ) {
			return [ 'sky-addons-styles' ];
		}

		return [ 'sa-image-compare' ];
	}

	public function get_script_depends() {
		if ( sky_addons_editor_mode() ) {
			return [ 'image-compare-viewer', 'sky-addons-scripts' ];
		}

		return [ 'image-compare-viewer', 'sa-image-compare' ];
	}

	public function get_custom_help_url() {
		return 'https://skyaddons.com/docs/sky-addons/widgets/image-compare/';
	}

	public function has_widget_inner_wrapper(): bool {
		return ! \Elementor\Plugin::$instance->experiments->is_feature_active( 'e_optimized_markup' );
	}

	protected function register_controls() {

		$this->start_controls_section(
			'section_ic_layout',
			[
				'label' => esc_html__( 'Image Compare', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'image_before',
			[
				'label'   => esc_html__( 'Before Image', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::MEDIA,
				'default' => [
					'url' => Utils::get_placeholder_image_src(),
				],
				'dynamic' => [ 'active' => true ],
			]
		);

		$this->add_control(
			'image_after',
			[
				'label'   => esc_html__( 'After Image', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::MEDIA,
				'default' => [
					'url' => Utils::get_placeholder_image_src(),
				],
				'dynamic' => [ 'active' => true ],
			]
		);

		$this->add_group_control(
			Group_Control_Image_Size::get_type(),
			[
				'name'      => 'thumbnail', // Usage: `{name}_size` and `{name}_custom_dimension`, in this case `image_size` and `image_custom_dimension`.
				'default'   => 'large',
				'separator' => 'none',
			]
		);

		$this->add_control(
			'show_labels',
			[
				'label'     => esc_html__( 'Show Labels', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'separator' => 'before',
			]
		);

		$this->add_control(
			'before_text',
			[
				'label'   => esc_html__( 'Before Text', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Before', 'sky-elementor-addons' ),
				'dynamic' => [ 'active' => true ],
				'condition' => [
					'show_labels' => 'yes',
				],
			]
		);

		$this->add_control(
			'after_text',
			[
				'label'   => esc_html__( 'After Text', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'After', 'sky-elementor-addons' ),
				'dynamic' => [ 'active' => true ],
				'condition' => [
					'show_labels' => 'yes',
				],
			]
		);

		$this->add_control(
			'label_options_on_hover',
			[
				'label'        => esc_html__( 'Show Label Options on Hover', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'prefix_class' => 'sa-labels-on-hover-',
				'condition'    => [
					'show_labels' => 'yes',
				],
				'render_type'  => 'template',
			]
		);

		// vertical_mode

		$this->add_control(
			'labels_position',
			[
				'label'          => esc_html__( 'Labels Position', 'sky-elementor-addons' ),
				'type'           => Controls_Manager::CHOOSE,
				'label_block'    => false,
				'options'        => [
					'top'    => [
						'title' => esc_html__( 'Top', 'sky-elementor-addons' ),
						'icon'  => 'eicon-v-align-top',
					],
					'middle' => [
						'title' => esc_html__( 'Middle', 'sky-elementor-addons' ),
						'icon'  => 'eicon-v-align-middle',
					],
					'bottom' => [
						'title' => esc_html__( 'Bottom', 'sky-elementor-addons' ),
						'icon'  => 'eicon-v-align-bottom',
					],
				],
				'style_transfer' => true,
				'toggle'         => false,
				'default'        => 'bottom',
				// 'desktop_default'      => 'bottom',
				// 'tablet_default'       => 'bottom',
				// 'mobile_default'       => 'bottom',
				// 'selectors'            => [
				// '{{WRAPPER}} .icv__label' => '{{VALUE}};',
				// ],
				// 'prefix_class'         => 'sa-list-layout-%s-',
				'prefix_class'   => 'sa-labels-position-',
				// 'selectors_dictionary' => [
				// 'top'  => 'top: 1rem; bottom: unset;',
				// 'middle' => 'top: 50%; transform: translateY(-50%); bottom: unset;',
				// 'bottom' => 'bottom: 1rem; top:unset;',
				// ],
				'condition'      => [
					'vertical_mode!' => 'yes',
				],
			]
		);

		$this->add_control(
			'labels_position_vertical',
			[
				'label'          => esc_html__( 'Labels Position Vertical', 'sky-elementor-addons' ),
				'type'           => Controls_Manager::CHOOSE,
				'label_block'    => false,
				'options'        => [
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
				'style_transfer' => true,
				'toggle'         => true,
				'default'        => 'left',
				'prefix_class'   => 'sa-labels-position-vertical-',
				'condition'      => [
					'vertical_mode' => 'yes',
				],
			]
		);

		$this->add_control(
			'show_content',
			[
				'label'     => esc_html__( 'Show Content', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SWITCHER,
				'separator' => 'before',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_ic_content',
			[
				'label' => esc_html__( 'Content', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
				'condition' => [
					'show_content' => 'yes',
				],
			]
		);

		$this->add_control(
			'title',
			[
				'label'   => esc_html__( 'Title', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Before & After', 'sky-elementor-addons' ),
				'dynamic' => [ 'active' => true ],
			]
		);

		$this->add_control(
			'title_tag',
			[
				'label'   => esc_html__( 'Title HTML Tag', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'h3',
				'options' => sky_addons_title_tags(),
			]
		);

		$this->add_control(
			'content',
			[
				'label'       => esc_html__( 'Content', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 5,
				'default'     => esc_html__( '-Lorem ipsum dolor sit amet, consectetur adipiscing elit-', 'sky-elementor-addons' ),
				'placeholder' => esc_html__( 'Type your content here', 'sky-elementor-addons' ),
				'dynamic'     => [ 'active' => true ],
			]
		);

		$this->add_responsive_control(
			'content_wrapper_position',
			[
				'label'          => esc_html__( 'Content Area Position', 'sky-elementor-addons' ),
				'type'           => Controls_Manager::CHOOSE,
				'label_block'    => false,
				'options' => [
					'top'    => [
						'title' => esc_html__( 'Top', 'sky-elementor-addons' ),
						'icon'  => 'eicon-v-align-top',
					],
					'middle' => [
						'title' => esc_html__( 'Middle', 'sky-elementor-addons' ),
						'icon'  => 'eicon-v-align-middle',
					],
					'bottom' => [
						'title' => esc_html__( 'Bottom', 'sky-elementor-addons' ),
						'icon'  => 'eicon-v-align-bottom',
					],
				],
				'style_transfer' => true,
				'toggle'         => false,
				// 'desktop_default'      => 'middle',
				// 'tablet_default'       => 'middle',
				// 'mobile_default'       => 'middle',
				'default'        => 'middle',
				'selectors' => [
					'{{WRAPPER}} .sa-content-wrapper' => '{{VALUE}};',
				],
				// 'prefix_class'         => 'sa-list-layout-%s-',
				// 'prefix_class'   => 'sa-labels-position-',
				'selectors_dictionary' => [
					'top'    => 'top: 1.5em;left: 50%;transform: translateX(-50%); bottom: unset;',
					'middle' => 'top: 50%;transform: translate(-50%, -50%);left: 50%; bottom: unset;',
					'bottom' => 'bottom: 1.5em;left: 50%;transform: translateX(-50%); top: unset;',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_ic_settings',
			[
				'label' => esc_html__( 'Settings', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'vertical_mode',
			[
				'label'       => esc_html__( 'Vertical Mode', 'sky-elementor-addons' ),
				'description' => esc_html__( 'Enable to show the slider handle vertically instead of horizontally.', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::SWITCHER,
			]
		);

		// UI Theme Defaults

		$this->add_control(
			'ui_options_heading',
			[
				'label'     => esc_html__( 'U I   O P T I O N S', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'control_color',
			[
				'label'       => esc_html__( 'Handle Color', 'sky-elementor-addons' ),
				'description' => esc_html__( 'Set the color of the slider handle and arrows.', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::COLOR,
			]
		);

		$this->add_control(
			'control_shadow',
			[
				'label'       => esc_html__( 'Handle Shadow', 'sky-elementor-addons' ),
				'description' => esc_html__( 'Add a drop shadow to the slider handle for better visibility.', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::SWITCHER,
				'default'     => 'yes',
			]
		);

		// Circle

		$this->add_control(
			'circle_heading',
			[
				'label'     => esc_html__( 'C I R C L E', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'add_circle',
			[
				'label'       => esc_html__( 'Add Circle', 'sky-elementor-addons' ),
				'description' => esc_html__( 'Show a circular ring around the slider handle.', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::SWITCHER,
			]
		);

		$this->add_control(
			'add_circle_blur',
			[
				'label'       => esc_html__( 'Circle Blur', 'sky-elementor-addons' ),
				'description' => esc_html__( 'Apply a blur effect to the circular ring for a soft glow appearance.', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::SWITCHER,
				'condition'   => [
					'add_circle' => 'yes',
				],
			]
		);

		$this->add_control(
			'circle_size',
			[
				'label'       => esc_html__( 'Circle Size', 'sky-elementor-addons' ),
				'description' => esc_html__( 'Adjust the diameter of the circular ring around the handle.', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::SLIDER,
				'size_units'  => [ 'px' ],
				'range'       => [
					'px' => [
						'min' => 20,
						'max' => 100,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 50,
				],
				'condition' => [
					'add_circle' => 'yes',
				],
				'selectors' => [
					'{{WRAPPER}} .icv__circle' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'circle_vertical_offset',
			[
				'label'       => esc_html__( 'Circle Vertical Offset', 'sky-elementor-addons' ),
				'description' => esc_html__( 'Shift the circle ring up or down relative to the handle center.', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::SLIDER,
				'size_units'  => [ 'px' ],
				'range'       => [
					'px' => [
						'min' => -20,
						'max' => 20,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 0,
				],
				'condition' => [
					'add_circle' => 'yes',
				],
			]
		);

		// Smoothing

		$this->add_control(
			'smoothing_option_heading',
			[
				'label'     => esc_html__( 'S M O O T H I N G', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'smoothing',
			[
				'label'       => esc_html__( 'Smoothing', 'sky-elementor-addons' ),
				'description' => esc_html__( 'Smooth the slider movement with a gradual easing transition.', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::SWITCHER,
				'default'     => 'yes',
			]
		);

		$this->add_control(
			'smoothing_amount',
			[
				'label'       => esc_html__( 'Smoothing Amount', 'sky-elementor-addons' ),
				'description' => esc_html__( 'Control how smooth the transition is. Higher values produce a slower, more gradual effect.', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::SLIDER,
				'range'       => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 100,
				],
				'condition' => [
					'smoothing' => 'yes',
				],
			]
		);

		$this->add_control(
			'smoothing_ease',
			[
				'label'       => esc_html__( 'Smoothing Curve', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'description' => esc_html__( 'Choose the easing curve that controls the acceleration and deceleration of the slider.', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => [
					'ease-out'    => esc_html__( 'Ease Out', 'sky-elementor-addons' ),
					'ease-in-out' => esc_html__( 'Ease In Out', 'sky-elementor-addons' ),
					'linear'      => esc_html__( 'Linear', 'sky-elementor-addons' ),
					'ease-in'     => esc_html__( 'Ease In', 'sky-elementor-addons' ),
				],
				'default'     => 'ease-out',
				'condition' => [
					'smoothing' => 'yes',
				],
			]
		);

		// Other options

		$this->add_control(
			'other_options_heading',
			[
				'label'     => esc_html__( 'B E H A V I O R', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'hover_start',
			[
				'label'       => esc_html__( 'Activate Slider on Hover', 'sky-elementor-addons' ),
				'description' => esc_html__( 'Start dragging the slider by hovering over the image instead of clicking first.', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::SWITCHER,
				'default'     => 'yes',
			]
		);

		$this->add_control(
			'starting_point',
			[
				'label'       => esc_html__( 'Initial Slider Position', 'sky-elementor-addons' ),
				'description' => esc_html__( 'Set where the slider handle starts as a percentage of the image width. 50 = centered.', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::SLIDER,
				'range'       => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 50,
				],
			]
		);

		$this->add_responsive_control(
			'mobile_starting_point',
			[
				'label'       => esc_html__( 'Mobile Slider Position', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'description' => esc_html__( 'Override the initial slider position specifically on mobile devices.', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::SLIDER,
				'range'       => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 50,
				],
			]
		);

		$this->add_control(
			'fluid_mode',
			[
				'label'       => esc_html__( 'Fluid Mode', 'sky-elementor-addons' ),
				'description' => esc_html__( 'Allow custom height instead of being fixed by the image height. Enable this to set your own height below.', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::SWITCHER,
			]
		);

		$this->add_responsive_control(
			'fluid_mode_height',
			[
				'label'      => esc_html__( 'Height', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', '%' ],
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 600,
						'step' => 5,
					],
					'%'  => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 300,
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-image-compare' => ' width: 100%; height: {{SIZE}}{{UNIT}};',
				],
				'condition'  => [
					'fluid_mode' => 'yes',
				],
			]
		);

		$this->add_control(
			'animate_on_load',
			[
				'label'       => esc_html__( 'Entry Animation', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'description' => esc_html__( 'Animate the slider handle sliding in from the initial position on page load.', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::SWITCHER,
				'default'     => 'yes',
			]
		);

		$this->add_control(
			'entry_animation_duration',
			[
				'label'       => esc_html__( 'Animation Duration (ms)', 'sky-elementor-addons' ),
				'description' => esc_html__( 'How long the entry animation takes to complete in milliseconds.', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::SLIDER,
				'size_units'  => [ 'ms' ],
				'range'       => [
					'ms' => [
						'min'  => 300,
						'max'  => 3000,
						'step' => 50,
					],
				],
				'default' => [
					'unit' => 'ms',
					'size' => 800,
				],
				'condition' => [
					'animate_on_load' => 'yes',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_ic_overlay_style',
			[
				'label' => esc_html__( 'Overlay', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'overlay',
			[
				'label'        => esc_html__( 'Overlay', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'prefix_class' => 'sa-overlay-',
			]
		);

		$this->add_control(
			'overlay_color',
			[
				'label' => esc_html__( 'Overlay Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-image-compare:before' => 'background-color: {{VALUE}}',
				],
				'condition' => [
					'overlay' => 'yes',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_ic_image_style',
			[
				'label' => esc_html__( 'Image', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'image_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .icv__img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_ic_labels_style',
			[
				'label' => esc_html__( 'Labels', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_labels' => 'yes',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'labels_typography',
				'label'    => esc_html__( 'Typography', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .icv__label',
			]
		);

		$this->add_control(
			'labels_color',
			[
				'label' => esc_html__( 'Text Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .icv__label' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'labels_background',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient', 'video' ],
				'selector' => '{{WRAPPER}} .icv__label',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'labels_border',
				'label'    => esc_html__( 'Border', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .icv__label',
			]
		);

		$this->add_responsive_control(
			'labels_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .icv__label' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		$this->add_responsive_control(
			'labels_padding',
			[
				'label'      => esc_html__( 'Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .icv__label' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'label_opacity',
			[
				'label'       => esc_html__( 'Opacity', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'description' => esc_html__( 'Set the transparency of the before/after labels. 1 = fully opaque, 0 = fully transparent.', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::SLIDER,
				'range'       => [
					'px' => [
						'min'  => 0,
						'max'  => 1,
						'step' => 0.05,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .icv__label' => 'opacity: {{SIZE}};',
				],
			]
		);

		$this->add_control(
			'label_fade_duration',
			[
				'label'       => esc_html__( 'Fade Animation Duration', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'description' => esc_html__( 'How long the label takes to fade in when hovering over the image.', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::SLIDER,
				'size_units'  => [ 's' ],
				'range'       => [
					's' => [
						'min'  => 0.1,
						'max'  => 1,
						'step' => 0.05,
					],
				],
				'default' => [
					'unit' => 's',
					'size' => 0.25,
				],
				'condition' => [
					'label_options_on_hover' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'label_vertical_offset',
			[
				'label'       => esc_html__( 'Vertical Offset', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'description' => esc_html__( 'Shift the labels up or down from their default position.', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::SLIDER,
				'size_units'  => [ 'px' ],
				'range'       => [
					'px' => [
						'min' => -30,
						'max' => 30,
					],
				],
				'selectors'   => [
					'{{WRAPPER}} .icv__label:not(.vertical)' => 'bottom: calc(1rem + {{SIZE}}{{UNIT}});',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_content_style',
			[
				'label' => esc_html__( 'Content', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_content' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'content_wrapper_width',
			[
				'label' => esc_html__( 'Width', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 40,
						'max' => 100,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 70,
				],
				'selectors' => [
					'{{WRAPPER}} .sa-content-wrapper' => 'width: {{SIZE}}%;',
				],
			]
		);

		$this->add_responsive_control(
			'title_spacing',
			[
				'label'      => esc_html__( 'Spacing', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 50,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-content-wrapper  .sa-title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'content_wrapper_align',
			[
				'label'           => esc_html__( 'Alignment', 'sky-elementor-addons' ),
				'type'            => Controls_Manager::CHOOSE,
				'label_block'     => false,
				'options' => [
					'left'    => [
						'title' => esc_html__( 'Left', 'sky-elementor-addons' ),
						'icon'  => 'eicon-text-align-left',
					],
					'center'  => [
						'title' => esc_html__( 'Center', 'sky-elementor-addons' ),
						'icon'  => 'eicon-text-align-center',
					],
					'right'   => [
						'title' => esc_html__( 'Right', 'sky-elementor-addons' ),
						'icon'  => 'eicon-text-align-right',
					],
					'justify' => [
						'title' => esc_html__( 'Justify', 'sky-elementor-addons' ),
						'icon'  => 'eicon-text-align-justify',
					],
				],
				'toggle'          => false,
				'desktop_default' => 'top',
				'tablet_default'  => 'top',
				'mobile_default'  => 'top',
				// 'prefix_class'         => 'sa-card-%s-',
				'style_transfer'  => true,
				'selectors' => [
					'{{WRAPPER}} .sa-content-wrapper' => '{{VALUE}};',
				],
				'selectors_dictionary' => [
					'left'    => 'text-align: left;',
					'center'  => 'text-align: center;',
					'right'   => 'text-align: right;',
					'justify' => 'text-align: justify;',
				],
			]
		);

		$this->add_responsive_control(
			'content_wrapper_padding',
			[
				'label'      => esc_html__( 'Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-content-wrapper' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'content_wrapper_margin',
			[
				'label'      => esc_html__( 'Margin', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-content-wrapper' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'content_wrapper_background',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .sa-content-wrapper',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'content_wrapper_border',
				'label'    => esc_html__( 'Border', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-content-wrapper',
			]
		);

		$this->add_responsive_control(
			'content_wrapper_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-content-wrapper' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'content_wrapper_box_shadow',
				'label'    => esc_html__( 'Box Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-content-wrapper',
			]
		);

		$this->start_controls_tabs( 'content_tabs' );

		$this->start_controls_tab(
			'content_tab_title',
			[
				'label' => esc_html__( 'Title', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'title_color',
			[
				'label' => esc_html__( 'Text Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-content-wrapper .sa-title' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'title_background',
			[
				'label' => esc_html__( 'Background', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-content-wrapper  .sa-title' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_responsive_control(
			'title_padding',
			[
				'label'      => esc_html__( 'Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-content-wrapper  .sa-title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'title_typography',
				'label'    => esc_html__( 'Typography', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-content-wrapper  .sa-title',
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'title_text_shadow',
				'label'    => esc_html__( 'Text Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-content-wrapper  .sa-title',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'content_tab_text',
			[
				'label' => esc_html__( 'Text', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'content_color',
			[
				'label' => esc_html__( 'Text Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-content-wrapper .sa-content' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'content_background',
			[
				'label' => esc_html__( 'Background', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-content-wrapper .sa-content' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_responsive_control(
			'content_padding',
			[
				'label'      => esc_html__( 'Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-content-wrapper .sa-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'content_typography',
				'label'    => esc_html__( 'Typography', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-content-wrapper .sa-content',
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'content_text_shadow',
				'label'    => esc_html__( 'Text Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-content-wrapper  .sa-content',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	protected function content() {
		$settings = $this->get_settings_for_display();
		?>
		<div class="keep sa-content-wrapper sa-p-3">
			<?php
			if ( ! empty( $settings['title'] ) ) {
				printf(
					'<%1$s class="%2$s">%3$s</%1$s>',
					esc_attr( Utils::validate_html_tag( $settings['title_tag'] ) ),
					'sa--title sa-title sa-fs-2 sa-fw-bolder sa-mt-0 sa-mb-2',
					wp_kses_post( $settings['title'] )
				);
			}
			?>
			<?php if ( ! empty( $settings['content'] ) ) : ?>
				<div class="sa-content sa-fw-bolder">
					<?php echo esc_html( $settings['content'] ); ?>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$wrapper_class = 'sa-image-compare';
		if ( 'yes' === $settings['animate_on_load'] ) {
			$wrapper_class .= ' sa-animate-on-load';
		}

		$this->add_render_attribute(
			[
				'image-compare' => [
					'id'    => 'sky-ic-' . $this->get_id(),
					'class' => $wrapper_class,
					'data-settings' => [
						wp_json_encode(
							[
								'id'                     => 'sky-ic-' . $this->get_id(),
								// Label Defaults
								'showLabels'             => ( 'yes' === $settings['show_labels'] ) ? true : false,
								// The library reads these nested under labelOptions and merges shallowly, so flat keys never reach it.
								'labelOptions'           => [
									'before'  => ! empty( $settings['before_text'] ) ? wp_kses_post( $settings['before_text'] ) : 'Before',
									'after'   => ! empty( $settings['after_text'] ) ? wp_kses_post( $settings['after_text'] ) : 'After',
									'onHover' => 'yes' === $settings['label_options_on_hover'] ? true : false,
								],
								'labelFadeDuration'      => ! empty( $settings['label_fade_duration']['size'] ) ? $settings['label_fade_duration']['size'] : 0.25,
								'labelVerticalOffset'    => ! empty( $settings['label_vertical_offset']['size'] ) ? $settings['label_vertical_offset']['size'] : 0,
								// UI Theme Defaults
								// The library concatenates this into SVG markup via innerHTML — keep only colour-syntax characters.
								'controlColor'           => ! empty( $settings['control_color'] ) ? preg_replace( '/[^#a-zA-Z0-9(),.%\s-]/', '', $settings['control_color'] ) : '#FFFFFF',
								'controlShadow'          => 'yes' === $settings['control_shadow'] ? true : false,
								'addCircle'              => 'yes' === $settings['add_circle'] ? true : false,
								'addCircleBlur'          => ( isset( $settings['add_circle_blur'] ) && 'yes' === $settings['add_circle_blur'] ) ? true : false,
								'circleSize'             => ! empty( $settings['circle_size']['size'] ) ? $settings['circle_size']['size'] : 50,
								'circleVerticalOffset'   => ! empty( $settings['circle_vertical_offset']['size'] ) ? $settings['circle_vertical_offset']['size'] : 0,
								// Smoothing
								'smoothing'              => ( 'yes' === $settings['smoothing'] ) ? true : false ,
								'smoothingAmount'        => ( 'yes' === $settings['smoothing'] ) && ! empty( $settings['smoothing_amount']['size'] ) ? $settings['smoothing_amount']['size'] : 100,
								'smoothingEase'          => ! empty( $settings['smoothing_ease'] ) ? $settings['smoothing_ease'] : 'ease-out',
								// Other options
								'hoverStart'             => 'yes' === $settings['hover_start'] ? true : false,
								'verticalMode'           => 'yes' === $settings['vertical_mode'] ? true : false,
								'startingPoint'          => ! empty( $settings['starting_point']['size'] ) ? $settings['starting_point']['size'] : 50,
								'mobileStartingPoint'    => ! empty( $settings['mobile_starting_point']['size'] ) ? $settings['mobile_starting_point']['size'] : 50,
								'animateOnLoad'          => 'yes' === $settings['animate_on_load'] ? true : false,
								'entryAnimationDuration' => ! empty( $settings['entry_animation_duration']['size'] ) ? $settings['entry_animation_duration']['size'] : 0.8,
								'fluidMode'              => 'yes' === $settings['fluid_mode'] ? true : false,
							],
							// Labels reach innerHTML. HEX_AMP stops an entity-encoded payload (&lt;img onerror&gt;) from being decoded back into markup by the attribute.
							JSON_HEX_TAG | JSON_HEX_AMP
						),
					],
				],
			]
		);

		?>
		<div <?php $this->print_render_attribute_string( 'image-compare' ); ?>>
			<?php
			if ( 'yes' === $settings['show_content'] ) {
				$this->content();
			}
			echo wp_kses_post( Group_Control_Image_Size::get_attachment_image_html( $settings, 'thumbnail', 'image_before' ) );
			echo wp_kses_post( Group_Control_Image_Size::get_attachment_image_html( $settings, 'thumbnail', 'image_after' ) );
			?>
		</div>
		<?php
	}
}

<?php

namespace Sky_Addons\Modules\TestimonialCarousel\Widgets;

use Elementor\Utils;
use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Widget_Base;

use Sky_Addons\Traits\Global_Swiper_Controls;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Testimonial_Carousel extends Widget_Base {


	use Global_Swiper_Controls;

	public function get_name() {
		return 'sky-testimonial-carousel';
	}

	public function get_title() {
		return esc_html__( 'Testimonial Carousel', 'sky-elementor-addons' );
	}

	public function get_icon() {
		return 'sky-icon-testimonial-carousel';
	}

	public function get_categories() {
		return [ 'sky-elementor-addons' ];
	}

	public function get_keywords() {
		return [ 'sky', 'testimonial', 'review', 'clients', 'rating' ];
	}

	public function get_style_depends() {
		if ( sky_addons_editor_mode() ) {
			return [ 'swiper', 'sky-addons-styles' ];
		}

		return [ 'swiper', 'sa-testimonial' ];
	}

	public function get_script_depends() {
		if ( sky_addons_editor_mode() ) {
			return [ 'swiper', 'sky-addons-scripts' ];
		}

		return [ 'swiper', 'sa-testimonial-carousel' ];
	}

	public function get_custom_help_url() {
		return 'https://wowdevs.com/docs/sky-addons/carousel-slider/testimonial-carousel/';
	}

	public function has_widget_inner_wrapper(): bool {
		return ! \Elementor\Plugin::$instance->experiments->is_feature_active( 'e_optimized_markup' );
	}

	protected function register_controls() {

		$this->start_controls_section(
			'section_testimonial',
			[
				'label' => esc_html__( 'Testimonial', 'sky-elementor-addons' ),
			]
		);

		$this->add_responsive_control(
			'columns',
			[
				'label'          => esc_html__( 'Columns', 'sky-elementor-addons' ),
				'type'           => Controls_Manager::SELECT,
				'options'        => [
					1 => esc_html__( '1 Column', 'sky-elementor-addons' ),
					2 => esc_html__( '2 Columns', 'sky-elementor-addons' ),
					3 => esc_html__( '3 Columns', 'sky-elementor-addons' ),
					4 => esc_html__( '4 Columns', 'sky-elementor-addons' ),
					5 => esc_html__( '5 Columns', 'sky-elementor-addons' ),
					6 => esc_html__( '6 Columns', 'sky-elementor-addons' ),
				],
				'default'        => 3,
				'tablet_default' => 2,
				'mobile_default' => 1,
				'render_type'    => 'template',
			]
		);

		$repeater = new Repeater();

		$repeater->start_controls_tabs(
			'testimonial_tabs'
		);

		$repeater->start_controls_tab(
			'testimonial_tab',
			[
				'label' => esc_html__( 'Testimonial', 'sky-elementor-addons' ),
			]
		);

		$repeater->add_control(
			'testimonial_text',
			[
				'label' => esc_html__( 'Testimonial', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::TEXTAREA,
				'dynamic' => [
					'active' => true,
				],
				'rows'  => '10',
			]
		);

		$repeater->end_controls_tab();

		$repeater->start_controls_tab(
			'reviewer_tab',
			[
				'label' => esc_html__( 'Reviewer', 'sky-elementor-addons' ),
			]
		);

		$repeater->add_control(
			'testimonial_image',
			[
				'label' => esc_html__( 'Choose Image', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::MEDIA,
				'dynamic' => [
					'active' => true,
				],
				'default' => [
					'url' => Utils::get_placeholder_image_src(),
				],
			]
		);

		$repeater->add_group_control(
			Group_Control_Image_Size::get_type(),
			[
				'name'      => 'thumbnail',
				'default'   => 'medium',
				'separator' => 'none',
			]
		);

		$repeater->add_control(
			'testimonial_name',
			[
				'label'       => esc_html__( 'Name', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'dynamic'     => [
					'active' => true,
				],
			]
		);

		$repeater->add_control(
			'testimonial_job',
			[
				'label'       => esc_html__( 'Title', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'dynamic'     => [
					'active' => true,
				],
			]
		);

		$repeater->add_control(
			'link',
			[
				'label'       => esc_html__( 'Link', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::URL,
				'label_block' => true,
				'dynamic'     => [
					'active' => true,
				],
				'placeholder' => esc_html__( 'https://your-link.com', 'sky-elementor-addons' ),
			]
		);

		$repeater->end_controls_tab();

		$repeater->end_controls_tabs();

		$this->add_control(
			'testimonial_list',
			[
				'label'       => esc_html__( 'Testimonial List', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'separator'   => 'before',
				'default'     => [
					[
						'testimonial_name' => 'John Doe',
						'testimonial_job'  => 'Designer',
						'testimonial_text' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.',
						'sky-elementor-addons',
						'testimonial_image' => [
							'url' => Utils::get_placeholder_image_src(),
						],
					],
					[
						'testimonial_name' => 'Mark Doe',
						'testimonial_job'  => 'Software Engineer',
						'testimonial_text' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.',
						'sky-elementor-addons',
						'testimonial_image' => [
							'url' => Utils::get_placeholder_image_src(),
						],
					],
					[
						'testimonial_name' => 'Nec Joe',
						'testimonial_job'  => 'Writer',
						'testimonial_text' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.',
						'sky-elementor-addons',
						'testimonial_image' => [
							'url' => Utils::get_placeholder_image_src(),
						],
					],
					[
						'testimonial_name' => 'Leo Do.',
						'testimonial_job'  => 'Manager',
						'testimonial_text' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.',
						'sky-elementor-addons',
						'testimonial_image' => [
							'url' => Utils::get_placeholder_image_src(),
						],
					],
				],
				'title_field' => '{{{ testimonial_name }}}',
			]
		);

		$this->end_controls_section();

		/**
		 * Global Carousel Settings
		 */

		$this->start_controls_section(
			'section_carousel_settings',
			[
				'label' => esc_html__( 'Carousel Settings', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_responsive_control(
			'carousel_height',
			[
				'label'      => esc_html__( 'Height', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range'      => [
					'px' => [
						'min'  => 100,
						'max'  => 500,
						'step' => 5,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .swiper' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->register_carousel_settings_controls( 'testimonial-carousel' );

		$this->end_controls_section();

		/**
		 * Global Navigation Controls
		 */

		$this->start_controls_section(
			'section_carousel_navigation',
			[
				'label' => esc_html__( 'Navigation', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->register_navigation_controls();

		$this->end_controls_section();

		/**
		 * Global Pagination Controls
		 */

		$this->start_controls_section(
			'section_carousel_pagination',
			[
				'label' => esc_html__( 'Pagination', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->register_pagination_controls( 'testimonial-carousel' );

		$this->end_controls_section();

		$this->start_controls_section(
			'section_carousel_item_style',
			[
				'label' => esc_html__( 'Carousel Item', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'carousel_item_alignment',
			[
				'label'        => esc_html__( 'Alignment', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::CHOOSE,
				'options'      => [
					'left'   => [
						'title' => esc_html__( 'Left', 'sky-elementor-addons' ),
						'icon'  => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'sky-elementor-addons' ),
						'icon'  => 'eicon-text-align-center',
					],
					'right'  => [
						'title' => esc_html__( 'Right', 'sky-elementor-addons' ),
						'icon'  => 'eicon-text-align-right',
					],
				],
				'prefix_class' => 'sa-carousel-align-%s-',
				'toggle'       => false,
				'default'      => 'left',
				'selectors'    => [
					'{{WRAPPER}} .sa-testimonial-carousel .sa-carousel-item' => 'text-align: {{VALUE}} !important;',
				],
			]
		);

		$this->add_responsive_control(
			'carousel_item_match_padding',
			[
				'label'      => esc_html__( 'Match Spacing', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range'      => [
					'px' => [
						'max' => 100,
						'min' => 0,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-testimonial-carousel .swiper' => 'padding: {{SIZE}}{{UNIT}}; margin:0 {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'carousel_item_padding',
			[
				'label'      => esc_html__( 'Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-testimonial-carousel .sa-carousel-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'carousel_item_border',
				'label'    => esc_html__( 'Border', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-testimonial-carousel .sa-carousel-item',
			]
		);

		$this->add_responsive_control(
			'carousel_item_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-testimonial-carousel .sa-carousel-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->start_controls_tabs( 'carousel_item_tabs' );

		$this->start_controls_tab(
			'carousel_item_tab_normal',
			[
				'label' => esc_html__( 'Normal', 'sky-elementor-addons' ),
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'carousel_item_bg',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .sa-testimonial-carousel .sa-carousel-item',
			]
		);

		// TODO Border Color also in active

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'carousel_item_box_shadow',
				'label'    => esc_html__( 'Box Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-testimonial-carousel .sa-carousel-item',
			]
		);

		$this->add_control(
			'carousel_item_opacity_normal',
			[
				'label' => esc_html__( 'Opacity', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'max'  => 1,
						'min'  => 0.10,
						'step' => 0.01,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .sa-testimonial-carousel .sa-carousel-item' => 'opacity: {{SIZE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'carousel_item_tab_hover',
			[
				'label' => esc_html__( 'Hover', 'sky-elementor-addons' ),
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'carousel_item_bg_hover',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .sa-testimonial-carousel .sa-carousel-item:hover',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'carousel_item_box_shadow_hover',
				'label'    => esc_html__( 'Box Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-testimonial-carousel .sa-carousel-item:hover',
			]
		);

		$this->add_control(
			'carousel_item_opacity_hover',
			[
				'label' => esc_html__( 'Opacity', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'max'  => 1,
						'min'  => 0.10,
						'step' => 0.01,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .sa-testimonial-carousel .sa-carousel-item:hover ' => 'opacity: {{SIZE}};',
				],
			]
		);

		// $this->add_control(
		// 'carousel_item_transition',
		// [
		// 'label'     => esc_html__('Transition Duration (s)', 'sky-elementor-addons'),
		// 'type'      => Controls_Manager::SLIDER,
		// 'range'     => [
		// 'px' => [
		// 'max'  => 3,
		// 'step' => 0.1,
		// ],
		// ],
		// 'selectors' => [
		// '{{WRAPPER}} .sa-testimonial-carousel .sa-carousel-item' => 'transition-duration: {{SIZE}}s',
		// ],
		// ]
		// );

		$this->end_controls_tab();

		$this->start_controls_tab(
			'carousel_item_tab_active',
			[
				'label' => esc_html__( 'Active', 'sky-elementor-addons' ),
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'carousel_item_bg_active',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .sa-testimonial-carousel .sa-carousel-item.swiper-slide-active',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'carousel_item_box_shadow_active',
				'label'    => esc_html__( 'Box Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-testimonial-carousel .sa-carousel-item.swiper-slide-active',
			]
		);

		$this->add_control(
			'carousel_item_opacity_active',
			[
				'label' => esc_html__( 'Opacity', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'max'  => 1,
						'min'  => 0.10,
						'step' => 0.01,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .sa-testimonial-carousel .sa-carousel-item.swiper-slide-active ' => 'opacity: {{SIZE}};',
				],
			]
		);

		// $this->add_control(
		// 'carousel_item_transition_active',
		// [
		// 'label'     => esc_html__('Transition Duration (s)', 'sky-elementor-addons'),
		// 'type'      => Controls_Manager::SLIDER,
		// 'range'     => [
		// 'px' => [
		// 'max'  => 3,
		// 'step' => 0.1,
		// ],
		// ],
		// 'selectors' => [
		// '{{WRAPPER}} .sa-testimonial-carousel .sa-carousel-item.swiper-slide-active' => 'transition-duration: {{SIZE}}s',
		// ],
		// ]
		// );

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		$this->start_controls_section(
			'section_testimonial_style',
			[
				'label' => esc_html__( 'Testimonial', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'testimonial_alignment',
			[
				'label'        => esc_html__( 'Alignment', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::CHOOSE,
				'options'      => [
					'left'   => [
						'title' => esc_html__( 'Left', 'sky-elementor-addons' ),
						'icon'  => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'sky-elementor-addons' ),
						'icon'  => 'eicon-text-align-center',
					],
					'right'  => [
						'title' => esc_html__( 'Right', 'sky-elementor-addons' ),
						'icon'  => 'eicon-text-align-right',
					],
				],
				'prefix_class' => 'sa-testimonial-%s-',
				'toggle'       => false,
				'default'      => 'left',
				'selectors'    => [
					'{{WRAPPER}} .sa-testimonial' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'testimonial_padding',
			[
				'label'      => esc_html__( 'Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-testimonial-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'testimonial_spacing',
			[
				'label'      => esc_html__( 'Bottom Spacing', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 50,
						'step' => 1,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-testimonial-content' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'testimonial_typography',
				'label'    => esc_html__( 'Typography', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-testimonial-content',
			]
		);

		$this->add_control(
			'testimonial_color',
			[
				'label' => esc_html__( 'Text Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-testimonial-content' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'testimonial_background',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .sa-testimonial-content',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'testimonial_border',
				'label'    => esc_html__( 'Border', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-testimonial-content',
			]
		);

		$this->add_responsive_control(
			'testimonial_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-testimonial-content' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow: hidden;',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'testimonial_box_shadow',
				'label'    => esc_html__( 'Box Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-testimonial-content',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_image_style',
			[
				'label' => esc_html__( 'Image', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'image_size',
			[
				'label'      => esc_html__( 'Size', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', '%' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 200,
					],
					'%'  => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default'    => [
					'size' => 65,
					'unit' => 'px',
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-testimonial' => '--sky-media-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}}.sa-carousel-align--left .sa-reviewer-meta' => 'flex: 0 0 calc(100% - var(--sky-media-size, 65px)); width: calc(100% - var(--sky-media-size, 65px));',
					'{{WRAPPER}}.sa-carousel-align--right .sa-reviewer-meta' => 'flex: 0 0 calc(100% - var(--sky-media-size, 65px)); width: calc(100% - var(--sky-media-size, 65px));',
				],
			]
		);

		$this->add_responsive_control(
			'image_spacing',
			[
				'label'      => esc_html__( 'Spacing', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 50,
						'step' => 1,
					],
				],
				'selectors'  => [
					'{{WRAPPER}}.sa-testimonial--left .sa-reviewer-meta'   => 'padding-left: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}}.sa-testimonial--right .sa-reviewer-meta'  => 'padding-right: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}}.sa-testimonial--center .sa-reviewer-meta' => 'padding-top: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'image_border',
				'label'    => esc_html__( 'Border', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-reviewer-thumb img',
			]
		);

		$this->add_responsive_control(
			'image_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-reviewer-thumb img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition'  => [
					'show_adv_border_radius!' => 'yes',
				],
			]
		);

		$this->add_control(
			'show_adv_border_radius',
			[
				'label' => esc_html__( 'Advanced Border Radius', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::SWITCHER,
			]
		);

		$this->add_control(
			'adv_border_radius',
			[
				'label'   => esc_html__( 'Radius', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( '30% 70% 70% 30% / 30% 30% 70% 70% ', 'sky-elementor-addons' ),
				'dynamic' => [ 'active' => true ],
				'selectors' => [
					'{{WRAPPER}} .sa-reviewer-thumb img' => 'border-radius: {{VALUE}};',
				],
				'condition' => [
					'show_adv_border_radius' => 'yes',
				],
			]
		);

		$this->add_control(
			'adv_border_radius_note',
			[
				'type'            => Controls_Manager::RAW_HTML,
				/* translators: %1s: URL of the border-radius generator. */
				'raw'             => sprintf( esc_html__( "You can easily generate Radius value from this link <a href='%1s' target='_blank'> Go </a>.", 'sky-elementor-addons' ), 'https://9elements.github.io/fancy-border-radius/' ),
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
				'condition'       => [
					'show_adv_border_radius' => 'yes',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'image_shadow',
				'label'    => esc_html__( 'Box Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-reviewer-thumb img',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_reviewer_style',
			[
				'label' => esc_html__( 'Reviewer', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'name_spacing',
			[
				'label'      => esc_html__( 'Spacing', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 50,
						'step' => 1,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-testimonial-job' => 'margin-top: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'name_heading',
			[
				'label'     => esc_html__( 'Name', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'name_color',
			[
				'label' => esc_html__( 'Text Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-testimonial-name' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'name_typography',
				'label'    => esc_html__( 'Typography', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-testimonial-name',
			]
		);

		$this->add_control(
			'job_heading',
			[
				'label'     => esc_html__( 'Title', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'job_color',
			[
				'label' => esc_html__( 'Text Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-testimonial-job' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'job_typography',
				'label'    => esc_html__( 'Typography', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-testimonial-job',
			]
		);

		$this->end_controls_section();

		/**
		 * Global Navigation Style Controls
		 */
		$this->register_navigation_style_controls( 'testimonial-carousel' );

		/**
		 * Global Pagination Controls
		 */
		$this->register_pagination_style_controls( 'testimonial-carousel' );
	}

	protected function render_item() {
		$settings = $this->get_settings_for_display();

		foreach ( $settings['testimonial_list'] as $index => $item ) :

			$has_content = ! ! $item['testimonial_text'];
			$has_image   = ! ! $item['testimonial_image']['url'];
			$has_name    = ! ! $item['testimonial_name'];
			$has_job     = ! ! $item['testimonial_job'];

			if ( ! $has_content && ! $has_image && ! $has_name && ! $has_job ) {
				continue;
			}

			$has_link = ! empty( $item['link']['url'] );

			if ( $has_link ) {
				$this->add_link_attributes( 'link' . $index, $item['link'] );
			}
			?>
			<div class="swiper-slide sa-carousel-item sa-testimonial">
				<?php
				if ( $has_content ) :
					$this->add_render_attribute( 'testimonial_text' . $index, 'class', 'sa-testimonial-content sa-mb-4 sa-fs-6' );
					?>
					<div <?php $this->print_render_attribute_string( 'testimonial_text' . $index ); ?>>
						<?php echo wp_kses_post( $this->parse_text_editor( $item['testimonial_text'] ) ); ?>
					</div>
				<?php endif; ?>
				<div class="sa-reviewer">
					<?php if ( $has_image ) : ?>
						<a class="sa-d-block sa-text-decoration-none" <?php $this->print_render_attribute_string( 'link' . $index ); ?>>
							<div class="sa-reviewer-thumb">
								<?php echo wp_kses_post( Group_Control_Image_Size::get_attachment_image_html( $item, 'thumbnail', 'testimonial_image' ) ); ?>
							</div>
						</a>
					<?php endif; ?>
					<div class="sa-reviewer-meta">
						<?php
						if ( $has_name ) :
							$this->add_render_attribute( 'testimonial_name' . $index, 'class', 'sa-testimonial-name sa-d-block sa-mb-2 sa-text-decoration-none' );

							if ( $has_link ) :
								$this->add_link_attributes( 'testimonial_name' . $index, $item['link'] );
								?>
								<a <?php $this->print_render_attribute_string( 'testimonial_name' . $index ); ?>>
									<?php echo wp_kses_post( $item['testimonial_name'] ); ?>
								</a>
								<?php
							else :
								?>
								<div <?php $this->print_render_attribute_string( 'testimonial_name' . $index ); ?>>
									<?php echo wp_kses_post( $item['testimonial_name'] ); ?>
								</div>
								<?php
							endif;
						endif;

						if ( $has_job ) :
							$this->add_render_attribute( 'testimonial_job' . $index, 'class', 'sa-testimonial-job sa-d-block sa-text-decoration-none' );

							if ( $has_link ) :
								$this->add_link_attributes( 'testimonial_job' . $index, $item['link'] );
								?>
								<a <?php $this->print_render_attribute_string( 'testimonial_job' . $index ); ?>>
									<?php echo esc_html( $item['testimonial_job'] ); ?>
								</a>
								<?php
							else :
								?>
								<div <?php $this->print_render_attribute_string( 'testimonial_job' . $index ); ?>>
									<?php echo esc_html( $item['testimonial_job'] ); ?>
								</div>
								<?php
							endif;
						endif;
						?>
					</div>
				</div>
			</div>
			<?php
		endforeach;
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$this->render_header();

		$this->render_item();

		/**
		 * global function
		 */
		$this->render_footer();
	}

	public function render_header() {
		$settings = $this->get_settings_for_display();
		$id       = 'sa-testimonial-carousel-' . $this->get_id();

		/**
		 * global function
		 */
		$this->render_header_attributes( 'testimonial-carousel' );

		$this->add_render_attribute(
			[
				'carousel' => [
					'class' => [ 'sa-testimonial-carousel', 'sa-swiper-global-carousel' ],
					'id'    => $id,
				],
			]
		);

		?>

		<div <?php $this->print_render_attribute_string( 'carousel' ); ?>>
			<div class="swiper">
				<div class="swiper-wrapper">

			<?php
	}
}

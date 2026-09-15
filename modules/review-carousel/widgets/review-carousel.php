<?php

namespace Sky_Addons\Modules\ReviewCarousel\Widgets;

use Elementor\Utils;
use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Css_Filter;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Widget_Base;

use Sky_Addons\Traits\Global_Swiper_Controls;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Review_Carousel extends Widget_Base {

	use Global_Swiper_Controls;

	public function get_name() {
		return 'sky-review-carousel';
	}

	public function get_title() {
		return esc_html__( 'Review Carousel', 'sky-elementor-addons' );
	}

	public function get_icon() {
		return 'sky-icon-review-carousel';
	}

	public function get_categories() {
		return [ 'sky-elementor-addons' ];
	}

	public function get_keywords() {
		return [ 'sky', 'review', 'carousel', 'testimonial', 'clients' ];
	}

	public function get_style_depends() {
		if ( sky_addons_editor_mode() ) {
			return [ 'swiper', 'sky-addons-styles' ];
		}

		// 'elementor-icons' — see Review::get_style_depends(); the stars are eicons
		// glyphs and this widget shares review.less.
		return [ 'swiper', 'sa-review', 'elementor-icons' ];
	}

	public function get_script_depends() {
		if ( sky_addons_editor_mode() ) {
			return [ 'swiper', 'sky-addons-scripts' ];
		}

		return [ 'swiper', 'sa-review-carousel' ];
	}

	public function get_custom_help_url() {
		return 'https://wowdevs.com/docs/sky-addons/carousel-slider/review-carousel/';
	}

	public function has_widget_inner_wrapper(): bool {
		return ! \Elementor\Plugin::$instance->experiments->is_feature_active( 'e_optimized_markup' );
	}

	protected function register_controls() {

		$this->start_controls_section(
			'section_review_layout',
			[
				'label' => esc_html__( 'Layout', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
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
			'review_tabs'
		);

		$repeater->start_controls_tab(
			'review_tab',
			[
				'label' => esc_html__( 'Review', 'sky-elementor-addons' ),
			]
		);

		$repeater->add_control(
			'image',
			[
				'label'   => esc_html__( 'Choose Image', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::MEDIA,
				'default' => [
					'url' => Utils::get_placeholder_image_src(),
				],
				'dynamic' => [ 'active' => true ],
				// 'separator' => 'before'
			]
		);

		$repeater->add_control(
			'name',
			[
				'label'       => esc_html__( 'Name', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXTAREA,
				'placeholder' => esc_html__( 'Type your Name here', 'sky-elementor-addons' ),
				'dynamic'     => [ 'active' => true ],
				'separator'   => 'before',
			]
		);

		$repeater->add_control(
			'designation',
			[
				'label'       => esc_html__( 'Designation', 'sky-elementor-addons' ),
				'label_block' => true,
				'type'        => Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'Type your Designation here', 'sky-elementor-addons' ),
				'dynamic'     => [ 'active' => true ],
			]
		);

		$repeater->add_control(
			'review',
			[
				'label'       => esc_html__( 'Review', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 10,
				'placeholder' => esc_html__( 'Type your review here', 'sky-elementor-addons' ),
				'description' => esc_html__( 'You can also put basic html tags on this input field.', 'sky-elementor-addons' ),
				'dynamic'     => [ 'active' => true ],
				'separator'   => 'before',
			]
		);

		$repeater->end_controls_tab();

		$repeater->start_controls_tab(
			'rating_tab',
			[
				'label' => esc_html__( 'Rating', 'sky-elementor-addons' ),
			]
		);

		$repeater->add_control(
			'rating_scale',
			[
				'label'   => esc_html__( 'Rating Scale', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					5  => '0-5',
					10 => '0-10',
				],
				'default' => '5',
			]
		);

		$repeater->add_control(
			'rating',
			[
				'label'   => esc_html__( 'Rating', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::NUMBER,
				'min'     => 0,
				'max'     => 10,
				'step'    => 0.1,
				'default' => 5,
				'dynamic' => [
					'active' => true,
				],
			]
		);

		$repeater->add_control(
			'star_style',
			[
				'label'        => esc_html__( 'Icon', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SELECT,
				'options'      => [
					'star_fontawesome' => 'Font Awesome',
					'star_unicode'     => 'Unicode',
				],
				'default'      => 'star_fontawesome',
				'render_type'  => 'template',
				'prefix_class' => 'elementor--star-style-',
				'separator'    => 'before',
			]
		);

		$repeater->add_control(
			'unmarked_star_style',
			[
				'label'   => esc_html__( 'Unmarked Style', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::CHOOSE,
				'options' => [
					'solid'   => [
						'title' => esc_html__( 'Solid', 'sky-elementor-addons' ),
						'icon'  => 'eicon-star',
					],
					'outline' => [
						'title' => esc_html__( 'Outline', 'sky-elementor-addons' ),
						'icon'  => 'eicon-star-o',
					],
				],
				'default' => 'solid',
			]
		);

		$repeater->end_controls_tab();

		$repeater->end_controls_tabs();

		$this->add_control(
			'review_list',
			[
				'label'       => esc_html__( 'Review List', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'separator'   => 'before',
				'default'     => [
					[
						'name'        => 'Name Surname',
						'designation' => 'Your Position',
						'review'      => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus.',
						'default'     => [
							'image' => Utils::get_placeholder_image_src(),
						],
					],
					[
						'name'        => 'John Doe',
						'designation' => 'Your Position',
						'review'      => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus.',
						'default'     => [
							'image' => Utils::get_placeholder_image_src(),
						],
					],
					[
						'name'        => 'Mark Doe',
						'designation' => 'Your Position',
						'review'      => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus.',
						'default'     => [
							'image' => Utils::get_placeholder_image_src(),
						],
					],
					[
						'name'        => 'Robert G.',
						'designation' => 'Your Position',
						'review'      => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus.',
						'default'     => [
							'image' => Utils::get_placeholder_image_src(),
						],
					],

				],
				'title_field' => '{{{ name }}}',
			]
		);

		$this->add_control(
			'name_tag',
			[
				'label'     => esc_html__( 'Name HTML Tag', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'h3',
				'options'   => sky_addons_title_tags(),
				'separator' => 'before',
			]
		);

		$this->add_control(
			'designation_tag',
			[
				'label'   => esc_html__( 'Designation HTML Tag', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'h6',
				'options' => sky_addons_title_tags(),
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_additional_layout',
			[
				'label' => esc_html__( 'Additional', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'show_designation',
			[
				'label'   => esc_html__( 'Show Designation', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'show_photo',
			[
				'label'   => esc_html__( 'Show Photo', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
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

		// $this->add_responsive_control(
		// 'media_position',
		// [
		// 'label'                => esc_html__('Media Position', 'sky-elementor-addons'),
		// 'type'                 => Controls_Manager::CHOOSE,
		// 'label_block'          => false,
		// 'options'              => [
		// 'left'  => [
		// 'title' => esc_html__('Left', 'sky-elementor-addons'),
		// 'icon'  => 'eicon-h-align-left',
		// ],
		// 'top'   => [
		// 'title' => esc_html__('Top', 'sky-elementor-addons'),
		// 'icon'  => 'eicon-v-align-top',
		// ],
		// 'right' => [
		// 'title' => esc_html__('Right', 'sky-elementor-addons'),
		// 'icon'  => 'eicon-h-align-right',
		// ],
		// ],
		// 'toggle'               => false,
		// 'desktop_default'      => 'top',
		// 'tablet_default'       => 'top',
		// 'mobile_default'       => 'top',
		// 'prefix_class'         => 'sa-review-media-%s-',
		// 'style_transfer'       => true,
		// 'selectors'            => [
		// '{{WRAPPER}} .sa-review' => '{{VALUE}};',
		// ],
		// 'selectors_dictionary' => [
		// 'left'  => 'display: flex; flex-direction: row; text-align: left;',
		// 'top'   => 'text-align: left; display: block; flex-direction: unset; flex-flow: unset;',
		// 'right' => 'display: flex; flex-direction: row-reverse; text-align: right;'
		// ]
		// ]
		// );

		$this->add_control(
			'review_position',
			[
				'label'   => esc_html__( 'Review Position', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'after',
				'options' => [
					'before' => esc_html__( 'Before Rating', 'sky-elementor-addons' ),
					'after'  => esc_html__( 'After Rating', 'sky-elementor-addons' ),
				],
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

		/**
		 * The Name is done by mistake, but we keep it for backward compatibility
		 * Don't change it to 'review-carousel'
		 */
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

		$this->register_pagination_controls( 'review-carousel' );

		$this->end_controls_section();

		/**
		 * Global Item Style Controls
		 */

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
				'label' => esc_html__( 'Alignment', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::CHOOSE,
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
						'title' => esc_html__( 'Justified', 'sky-elementor-addons' ),
						'icon'  => 'eicon-text-align-justify',
					],
				],
				'selectors' => [
					'{{WRAPPER}} .sa-review-carousel .sa-carousel-item' => 'text-align: {{VALUE}} !important;',
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
					'{{WRAPPER}} .sa-review-carousel .swiper' => 'padding: {{SIZE}}{{UNIT}}; margin:0 {{SIZE}}{{UNIT}};',
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
					'{{WRAPPER}} .sa-review-carousel .sa-carousel-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'carousel_item_border',
				'label'    => esc_html__( 'Border', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-review-carousel .sa-carousel-item',
			]
		);

		$this->add_responsive_control(
			'carousel_item_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-review-carousel .sa-carousel-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
				'selector' => '{{WRAPPER}} .sa-review-carousel .sa-carousel-item',
			]
		);

		// TODO Border Color also in active

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'carousel_item_box_shadow',
				'label'    => esc_html__( 'Box Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-review-carousel .sa-carousel-item',
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
					'{{WRAPPER}} .sa-review-carousel .sa-carousel-item' => 'opacity: {{SIZE}};',
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
				'selector' => '{{WRAPPER}} .sa-review-carousel .sa-carousel-item:hover',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'carousel_item_box_shadow_hover',
				'label'    => esc_html__( 'Box Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-review-carousel .sa-carousel-item:hover',
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
					'{{WRAPPER}} .sa-review-carousel .sa-carousel-item:hover ' => 'opacity: {{SIZE}};',
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
		// '{{WRAPPER}} .sa-review-carousel .sa-carousel-item' => 'transition-duration: {{SIZE}}s',
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
				'selector' => '{{WRAPPER}} .sa-review-carousel .sa-carousel-item.swiper-slide-active',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'carousel_item_box_shadow_active',
				'label'    => esc_html__( 'Box Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-review-carousel .sa-carousel-item.swiper-slide-active',
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
					'{{WRAPPER}} .sa-review-carousel .sa-carousel-item.swiper-slide-active ' => 'opacity: {{SIZE}};',
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
		// '{{WRAPPER}} .sa-review-carousel .sa-carousel-item.swiper-slide-active' => 'transition-duration: {{SIZE}}s',
		// ],
		// ]
		// );

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		$this->start_controls_section(
			'section_review_style',
			[
				'label' => esc_html__( 'Review Carousel', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'content_padding',
			[
				'label'      => esc_html__( 'Text Box Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-review .sa-review-body' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_img_style',
			[
				'label' => esc_html__( 'Photo', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'img_height',
			[
				'label'      => esc_html__( 'Height', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range'      => [
					'px' => [
						'min'  => 50,
						'max'  => 200,
						'step' => 1,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-review-figure' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'img_width',
			[
				'label'      => esc_html__( 'Width', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', '%' ],
				'range'      => [
					'px' => [
						'min'  => 50,
						'max'  => 200,
						'step' => 1,
					],
					'%'  => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} ' => '--sa-review-media-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'img_offset_popover',
			[
				'label'        => esc_html__( 'Offset', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::POPOVER_TOGGLE,
				'label_off'    => esc_html__( 'Default', 'sky-elementor-addons' ),
				'label_on'     => esc_html__( 'Custom', 'sky-elementor-addons' ),
				'return_value' => 'yes',
			]
		);

		$this->start_popover();

		$this->add_responsive_control(
			'img_horizontal_offset',
			[
				'label'       => esc_html__( 'Horizontal Offset', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::SLIDER,
				'default' => [
					'size' => 0,
				],
				'tablet_default' => [
					'size' => 0,
				],
				'mobile_default' => [
					'size' => 0,
				],
				'range' => [
					'px' => [
						'min'  => -300,
						'step' => 2,
						'max'  => 300,
					],
				],
				'render_type' => 'ui',
				'condition' => [
					'img_offset_popover' => 'yes',
				],
				'selectors' => [
					'{{WRAPPER}} .sa-review' => '--sky-media-h-offset: {{SIZE}}px;',
				],
			]
		);

		$this->add_responsive_control(
			'img_vertical_offset',
			[
				'label'       => esc_html__( 'Vertical Offset', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::SLIDER,
				'default' => [
					'size' => 0,
				],
				'tablet_default' => [
					'size' => 0,
				],
				'mobile_default' => [
					'size' => 0,
				],
				'range' => [
					'px' => [
						'min'  => -300,
						'step' => 2,
						'max'  => 300,
					],
				],
				'render_type' => 'ui',
				'condition' => [
					'img_offset_popover' => 'yes',
				],
				'selectors' => [
					'{{WRAPPER}} .sa-review' => '--sky-media-v-offset: {{SIZE}}px;',
				],
			]
		);

		$this->add_responsive_control(
			'img_rotate',
			[
				'label'       => esc_html__( 'Rotate', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::SLIDER,
				'devices'     => [ 'desktop', 'tablet', 'mobile' ],
				'default' => [
					'size' => 0,
				],
				'tablet_default' => [
					'size' => 0,
				],
				'mobile_default' => [
					'size' => 0,
				],
				'range' => [
					'px' => [
						'min'  => -360,
						'max'  => 360,
						'step' => 5,
					],
				],
				'condition' => [
					'img_offset_popover' => 'yes',
				],
				'render_type' => 'ui',
				'selectors' => [
					'{{WRAPPER}} .sa-review' => '--sky-media-rotate: {{SIZE}}deg;',
				],
			]
		);

		$this->end_popover();

		$this->add_responsive_control(
			'img_padding',
			[
				'label'      => esc_html__( 'Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-review .sa-review-figure img ' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'img_border',
				'label'    => esc_html__( 'Border', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-review .sa-review-figure img',
			]
		);

		$this->add_responsive_control(
			'img_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-review .sa-review-figure img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'img_box_shadow',
				'label'    => esc_html__( 'Box Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-review .sa-review-figure img',
			]
		);

		$this->start_controls_tabs( 'tabs_img_style' );

		$this->start_controls_tab(
			'tab_img_normal',
			[
				'label' => esc_html__( 'Normal', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'img_opacity',
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
					'{{WRAPPER}} .sa-review .sa-review-figure img' => 'opacity: {{SIZE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			[
				'name'     => 'img_css_filters',
				'selector' => '{{WRAPPER}} .sa-review .sa-review-figure img',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_img_hover',
			[
				'label' => esc_html__( 'Hover', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'img_opacity_hover',
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
					'{{WRAPPER}} .sa-review .sa-review-figure img:hover' => 'opacity: {{SIZE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			[
				'name'     => 'img_css_filters_hover',
				'selector' => '{{WRAPPER}} .sa-review .sa-review-figure img:hover',
			]
		);

		$this->add_control(
			'img_transition',
			[
				'label' => esc_html__( 'Transition Duration (s)', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'max'  => 3,
						'step' => 0.1,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .sa-review .sa-review-figure img' => 'transition-duration: {{SIZE}}s',
				],
			]
		);

		$this->add_control(
			'img_hover_animation',
			[
				'label' => esc_html__( 'Hover Animation', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::HOVER_ANIMATION,
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		$this->start_controls_section(
			'section_name_style',
			[
				'label' => esc_html__( 'Name', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'name_spacing',
			[
				'label'      => esc_html__( 'Spacing', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', '%' ],
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					],
					'%'  => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-review .sa-name' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'name_typography',
				'label'    => esc_html__( 'Typography', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-review .sa-name',
			]
		);

		$this->start_controls_tabs( 'tabs_name_style' );

		$this->start_controls_tab(
			'tab_name_normal',
			[
				'label' => esc_html__( 'Normal', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'name_color',
			[
				'label' => esc_html__( 'Text Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-review .sa-name' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'name_text_shadow',
				'label'    => esc_html__( 'Text Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-review .sa-name',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_name_active',
			[
				'label' => esc_html__( 'Active', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'name_color_active',
			[
				'label' => esc_html__( 'Text Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .swiper-slide-active .sa-name' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'name_text_shadow_active',
				'label'    => esc_html__( 'Text Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .swiper-slide-active .sa-name',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		$this->start_controls_section(
			'section_designation_style',
			[
				'label' => esc_html__( 'Designation', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_designation' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'designation_spacing',
			[
				'label'      => esc_html__( 'Spacing', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', '%' ],
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					],
					'%'  => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-review .sa-designation' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'designation_typography',
				'label'    => esc_html__( 'Typography', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-review .sa-designation',
			]
		);

		$this->start_controls_tabs( 'tabs_designation_style' );

		$this->start_controls_tab(
			'tab_designation_normal',
			[
				'label' => esc_html__( 'Normal', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'designation_color',
			[
				'label' => esc_html__( 'Text Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-review .sa-designation' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'designation_text_shadow',
				'label'    => esc_html__( 'Text Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-review .sa-designation',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_designation_active',
			[
				'label' => esc_html__( 'Active', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'designation_color_active',
			[
				'label' => esc_html__( 'Text Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .swiper-slide-active .sa-designation' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'designation_text_shadow_active',
				'label'    => esc_html__( 'Text Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .swiper-slide-active .sa-designation',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		$this->start_controls_section(
			'section_review_text_style',
			[
				'label' => esc_html__( 'Review', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'review_spacing',
			[
				'label'      => esc_html__( 'Spacing', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', '%' ],
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					],
					'%'  => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-review .sa-review-desc' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'review_typography',
				'label'    => esc_html__( 'Typography', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-review .sa-review-desc',
			]
		);

		$this->start_controls_tabs( 'review_tabs' );

		$this->start_controls_tab(
			'review_tab_normal',
			[
				'label' => esc_html__( 'Normal', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'review_color',
			[
				'label' => esc_html__( 'Text Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-review .sa-review-desc' => 'color: {{VALUE}}',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'review_tab_active',
			[
				'label' => esc_html__( 'Active', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'review_color_active',
			[
				'label' => esc_html__( 'Text Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .swiper-slide-active .sa-review-desc' => 'color: {{VALUE}}',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		$this->start_controls_section(
			'section_rating_style',
			[
				'label' => esc_html__( 'Rating', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'rating_spacing',
			[
				'label'      => esc_html__( 'Spacing', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', '%' ],
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					],
					'%'  => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .review-star-rating' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'rating_size',
			[
				'label' => esc_html__( 'Size', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .review-star-rating' => 'font-size: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->add_responsive_control(
			'rating_icon_space',
			[
				'label' => esc_html__( 'Space Between', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 50,
					],
				],
				'selectors' => [
					'body:not(.rtl) {{WRAPPER}} .review-star-rating i:not(:last-of-type)' => 'margin-right: {{SIZE}}{{UNIT}}',
					'body.rtl {{WRAPPER}} .review-star-rating i:not(:last-of-type)'       => 'margin-left: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->add_control(
			'stars_color',
			[
				'label'     => esc_html__( 'Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .review-star-rating i:before' => 'color: {{VALUE}}',
				],
				'separator' => 'before',
			]
		);

		$this->add_control(
			'stars_unmarked_color',
			[
				'label' => esc_html__( 'Unmarked Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .review-star-rating i' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'rating_background',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .review-star-rating',
			]
		);

		$this->add_responsive_control(
			'rating_padding',
			[
				'label'      => esc_html__( 'Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .review-star-rating' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'rating_border',
				'label'    => esc_html__( 'Border', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .review-star-rating',
			]
		);

		$this->add_responsive_control(
			'rating_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .review-star-rating' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow: hidden;',
				],
			]
		);

		$this->end_controls_section();

		/**
		 * Global Navigation Style Controls
		 */
		$this->register_navigation_style_controls( 'review-carousel' );

		/**
		 * Global Pagination Controls
		 */
		$this->register_pagination_style_controls( 'review-carousel' );
	}

	protected function get_rating( $item ) {
		$rating_scale = (int) $item['rating_scale'];
		$rating       = (float) $item['rating'] > $rating_scale ? $rating_scale : $item['rating'];

		return [ $rating, $rating_scale ];
	}

	protected function render_stars( $item, $icon ) {
		$rating_data    = $this->get_rating( $item );
		$rating         = (float) $rating_data[0];
		$floored_rating = floor( $rating );
		$stars_html     = '';

		for ( $stars = 1.0; $stars <= $rating_data[1]; $stars++ ) {
			if ( $stars <= $floored_rating ) {
				$stars_html .= '<i class="review-star-full">' . $icon . '</i>';
			} elseif ( $floored_rating + 1 === $stars && $rating !== $floored_rating ) {
				$stars_html .= '<i class="review-star-' . (int) round( ( $rating - $floored_rating ) * 10 ) . '">' . $icon . '</i>';
			} else {
				$stars_html .= '<i class="review-star-empty">' . $icon . '</i>';
			}
		}

		return $stars_html;
	}

	protected function render_item() {
		$settings = $this->get_settings_for_display();

		foreach ( $settings['review_list'] as $index => $item ) :

			// star rating
			$rating_data    = $this->get_rating( $item );
			$textual_rating = $rating_data[0] . '/' . $rating_data[1];
			$icon           = '&#xE934;';

			if ( 'star_fontawesome' === $item['star_style'] ) {
				if ( 'outline' === $item['unmarked_star_style'] ) {
					$icon = '&#xE933;';
				}
			} elseif ( 'star_unicode' === $item['star_style'] ) {
				$icon = '&#9733;';

				if ( 'outline' === $item['unmarked_star_style'] ) {
					$icon = '&#9734;';
				}
			}

			$this->add_render_attribute( 'icon_wrapper' . $index, [
				'class'     => 'review-star-rating',
				'title'     => $textual_rating,
				'itemtype'  => 'http://schema.org/Rating',
				'itemscope' => '',
				'itemprop'  => 'reviewRating',
			] );

			$schema_rating = '<span itemprop="ratingValue" class="elementor-screen-only">' . $textual_rating . '</span>';
			$stars_element = '<div ' . $this->get_render_attribute_string( 'icon_wrapper' . $index ) . '>' . $this->render_stars( $item, $icon ) . ' ' . $schema_rating . '</div>';

			?>
			<div class="swiper-slide sa-carousel-item sa-review">
				<?php if ( 'yes' === $settings['show_photo'] && ! empty( $item['image']['url'] ) ) : ?>
					<figure class="sa-review-figure">
						<?php echo wp_kses_post( Group_Control_Image_Size::get_attachment_image_html( $item, 'thumbnail', 'image' ) ); ?>
					</figure>
				<?php endif; ?>


				<div class="sa-review-body">
					<?php
					if ( 'before' === $settings['review_position'] ) :
						if ( ! empty( $item['review'] ) ) :
							?>
							<div class="sa-review-desc sa-mt-4">
								<?php echo wp_kses_post( $item['review'] ); ?>
							</div>
							<?php
						endif;
					endif;
					?>
					<div class="sa-reviewer sa-mt-3">
						<?php

						if ( ! empty( $item['name'] ) ) {
							$this->add_render_attribute( 'name' . $index, 'class', 'sa-name sa-mb-2 sa--text-title sa-mt-0' );
							printf(
								'<%1$s %2$s>%3$s</%1$s>',
								esc_attr( Utils::validate_html_tag( $settings['name_tag'] ) ),
								wp_kses_post( $this->get_render_attribute_string( 'name' . $index ) ),
								wp_kses_post( $item['name'] )
							);
						}

						if ( 'yes' === $settings['show_designation'] && ! empty( $item['designation'] ) ) {
							$this->add_render_attribute( 'designation' . $index, 'class', 'sa-designation  sa-mt-0 sa-mb-2' );
							printf(
								'<%1$s %2$s>%3$s</%1$s>',
								esc_attr( Utils::validate_html_tag( $settings['designation_tag'] ) ),
								wp_kses_post( $this->get_render_attribute_string( 'designation' . $index ) ),
								esc_html( $item['designation'] )
							);
						}
						echo wp_kses_post( $stars_element );
						?>


					</div>

					<?php

					if ( 'after' === $settings['review_position'] ) :
						if ( ! empty( $item['review'] ) ) :
							?>
							<div class="sa-review-desc sa-mt-4">
								<?php echo wp_kses_post( $item['review'] ); ?>
							</div>
							<?php
						endif;
					endif;
					?>

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
		$id = 'sa-review-carousel-' . $this->get_id();

		/**
		 * global function
		 */
		$this->render_header_attributes( 'review-carousel' );

		$this->add_render_attribute(
			[
				'carousel' => [
					'class' => [ 'sa-review-carousel', 'sa-swiper-global-carousel' ],
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

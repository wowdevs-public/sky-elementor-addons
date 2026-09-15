<?php

namespace Sky_Addons\Modules\SaplingCarousel\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Css_Filter;
use Elementor\Widget_Base;

use Sky_Addons\Includes\Controls\GroupQuery\Group_Control;
use Sky_Addons\Traits\Global_Widget_Functions;
use Sky_Addons\Traits\Global_Widget_Controls;

use Sky_Addons\Traits\Global_Swiper_Controls;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Sapling_Carousel extends Widget_Base {

	use Group_Control;
	use Global_Widget_Functions;
	use Global_Widget_Controls;

	use Global_Swiper_Controls;

	private $_query = null;

	public function get_name() {
		return 'sky-sapling-carousel';
	}

	public function get_title() {
		return esc_html__( 'Sapling Carousel', 'sky-elementor-addons' );
	}

	public function get_icon() {
		return 'sky-icon-sapling-carousel';
	}

	public function get_categories() {
		return [ 'sky-elementor-addons' ];
	}

	public function get_keywords() {
		return [ 'sky', 'post', 'list', 'blogs', 'carousel' ];
	}

	public function get_style_depends() {
		if ( sky_addons_editor_mode() ) {
			return [ 'swiper', 'elementor-icons-fa-solid', 'sky-addons-styles' ];
		}

		return [ 'swiper', 'elementor-icons-fa-solid', 'sa-sapling-carousel' ];
	}

	public function get_script_depends() {
		if ( sky_addons_editor_mode() ) {
			return [ 'swiper', 'sky-addons-scripts' ];
		}

		return [ 'swiper', 'sa-sapling-carousel' ];
	}

	public function get_query() {
		return $this->_query;
	}

	public function has_widget_inner_wrapper(): bool {
		return ! \Elementor\Plugin::$instance->experiments->is_feature_active( 'e_optimized_markup' );
	}

	protected function register_controls() {

		$this->start_controls_section(
			'section_sapling_layout',
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

		$this->add_group_control(
			Group_Control_Image_Size::get_type(),
			[
				'name'    => 'primary_thumbnail',
				'exclude' => [ 'custom' ],
				'default' => 'large',
			]
		);

		$this->add_responsive_control(
			'content_layout',
			[
				'label'   => esc_html__( 'Content Layout', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'column-reverse' => esc_html__( 'Content Top', 'sky-elementor-addons' ),
					'column'         => esc_html__( 'Content Bottom', 'sky-elementor-addons' ),
				],
				'default' => 'column-reverse',
				'selectors' => [
					'{{WRAPPER}} .sa-post-item' => '{{VALUE}};',
				],
				'selectors_dictionary' => [
					'column-reverse' => 'flex-direction: column-reverse;',
					'column'         => 'flex-direction: column;',
				],
			]
		);

		$this->add_responsive_control(
			'content_alignment',
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
				'selectors_dictionary' => [
					'left'    => 'text-align: left; justify-content: left; align-items: flex-start',
					'center'  => 'text-align: center; justify-content: center; align-items: center',
					'right'   => 'text-align: right; justify-content: right; align-items: flex-end',
					'justify' => 'text-align: justify;',
				],
				'selectors' => [
					'{{WRAPPER}} .sa-post-item, {{WRAPPER}} .sa-post-meta' => '{{VALUE}}',
					'{{WRAPPER}} .sa-post-img-wrapper, {{WRAPPER}} .sa-post-category' => '{{VALUE}}',
				],
			]
		);

		$this->end_controls_section();

		/**
		 * Global Query Builder Settings
		 */
		$this->start_controls_section(
			'section_post_query_builder',
			[
				'label' => __( 'Query', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->register_query_builder_controls();

		// $this->update_control(
		// 'posts_per_page',
		// [
		// 'default' => 6,
		// ]
		// );

		$this->end_controls_section();

		$this->start_controls_section(
			'section_additional',
			[
				'label' => esc_html__( 'Additional', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'show_title',
			[
				'label'   => esc_html__( 'Show Title', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'title_tag',
			[
				'label'   => esc_html__( 'Title HTML Tag', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'h3',
				'options' => sky_addons_title_tags(),
				'condition' => [
					'show_title' => 'yes',
				],
			]
		);

		$this->add_control(
			'title_truncate',
			[
				'label' => esc_html__( 'Title Truncate', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::SWITCHER,
			]
		);

		$this->add_control(
			'show_image',
			[
				'label'   => esc_html__( 'Show Image', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'show_category',
			[
				'label'   => esc_html__( 'Show Category', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'show_author',
			[
				'label' => esc_html__( 'Show Author', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::SWITCHER,
				// 'default'   => 'yes',
			]
		);

		$this->add_control(
			'show_excerpt',
			[
				'label'     => esc_html__( 'Show Text', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SWITCHER,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'excerpt_length',
			[
				'label'       => esc_html__( 'Text Limit', 'sky-elementor-addons' ),
				'description' => esc_html__( 'This is for the main content, but not for excerpts. If you set the offset to 0, then you\'ll get the full text instead.', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 15,
				'condition'   => [
					'show_excerpt' => 'yes',
				],
			]
		);

		$this->add_control(
			'strip_shortcode',
			[
				'label'   => esc_html__( 'Strip ShortCode', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
				'condition' => [
					'show_excerpt' => 'yes',
				],
			]
		);

		/**
		 * Global Date Controls
		 */

		$this->add_control(
			'show_date',
			[
				'label'     => esc_html__( 'Show Date', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'separator' => 'before',
			]
		);

		$this->register_post_date_controls();

		$this->add_control(
			'show_video',
			[
				'label'     => esc_html__( 'Show Video', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'separator' => 'before',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_post_video_settings',
			[
				'label' => esc_html__( 'Video Settings', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
				'condition' => [
					'show_video' => 'yes',
				],
			]
		);

		/**
		 * Global Video Lightbox Control
		 */
		$this->video_lightbox_controls();

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
						'min' => 100,
						'max' => 1000,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .swiper' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->register_carousel_settings_controls( 'sapling-carousel' );

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

		$this->register_pagination_controls( 'sapling-carousel' );

		$this->end_controls_section();

		$this->start_controls_section(
			'section_sapling_style',
			[
				'label' => esc_html__( 'Sapling Carousel', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'item_padding',
			[
				'label'      => esc_html__( 'Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', 'rem', '%' ],
				// The panel read empty while .sa-post-item was already padded by the `sa-p-2`
				// class in render() — so a value typed here replaced an invisible baseline
				// instead of adding to it, and the same number meant different things from one
				// widget to the next. This default makes the control state the truth.
				//
				// The class deliberately STAYS in the markup. Elementor serves a page's CSS
				// from a cached file that only rebuilds on save, so a page saved before this
				// default existed would otherwise render with no padding at all. The control
				// always wins when it emits anything — `body .sa-p-2` is (0,1,1) against
				// Elementor's (0,4,0) — including when set to 0, so the class only ever acts
				// as the stale-cache fallback.
				//
				// No tablet/mobile default here: nothing in this widget's stylesheet overrides
				// .sa-post-item padding at a breakpoint, so there is no rule to preserve.
				//
				// rem, not the 8px it usually resolves to, so it matches the class whatever
				// root font-size the theme sets.
				'default'    => [
					'top'      => '0.5',
					'right'    => '0.5',
					'bottom'   => '0.5',
					'left'     => '0.5',
					'unit'     => 'rem',
					'isLinked' => true,
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-post-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'content_padding',
			[
				'label'      => esc_html__( 'Content Padding', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.0.0' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', 'rem', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-post-content-wrapper' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'item_border',
				'label'    => esc_html__( 'Border', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-post-item',
			]
		);

		$this->add_responsive_control(
			'item_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-post-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow: hidden;',
				],
			]
		);

		$this->start_controls_tabs(
			'item_style_tabs'
		);

		$this->start_controls_tab(
			'item_style_normal_tab',
			[
				'label' => esc_html__( 'Normal', 'sky-elementor-addons' ),
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'item_background',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .sa-post-item',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'item_box_shadow',
				'label'    => esc_html__( 'Box Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-post-item',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'item_style_hover_tab',
			[
				'label' => esc_html__( 'Hover', 'sky-elementor-addons' ),
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'item_background_hover',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .sa-post-item:hover',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'item_box_shadow_hover',
				'label'    => esc_html__( 'Box Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-post-item:hover',
			]
		);

		$this->add_control(
			'item_border_color_hover',
			[
				'label' => esc_html__( 'Border Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-post-item:hover' => 'border-color: {{VALUE}};',
				],
				'condition' => [
					'item_border_border!' => '',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		$this->start_controls_section(
			'section_image_style',
			[
				'label' => esc_html__( 'Image', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_image' => 'yes',
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
						'min' => 50,
						'max' => 500,
					],
					'%'  => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-post-img-wrapper' => 'min-width: {{SIZE}}{{UNIT}}; max-width: {{SIZE}}{{UNIT}};',
				],
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
						'min' => 50,
						'max' => 800,
					],
				],
				'selectors'  => [
					// `height` as well as min/max — without it the box is sized but the percentage
					// inside it is not. `.sa-post-img-wrapper img` is `height: 100%` (base.less), and a
					// percentage height resolves against the containing block's `height` property. With
					// only min/max-height set, `height` stays `auto`, the percentage is indeterminate and
					// collapses to `auto` — so the image kept its natural ratio inside a taller box,
					// leaving dead space, and `object-fit: cover` never engaged. min/max are kept so the
					// box is still pinned if anything else tries to stretch it.
					'{{WRAPPER}} .sa-post-img-wrapper' => 'height: {{SIZE}}{{UNIT}}; min-height: {{SIZE}}{{UNIT}}; max-height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'img_padding',
			[
				'label'      => esc_html__( 'Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-post-img' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'img_margin',
			[
				'label'      => esc_html__( 'Margin', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-post-img-wrapper' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'img_border',
				'label'    => esc_html__( 'Border', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-post-img',
			]
		);

		$this->add_responsive_control(
			'img_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-post-img, {{WRAPPER}} .sa-post-img-wrapper' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'img_box_shadow',
				'label'    => esc_html__( 'Box Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-post-img',
			]
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			[
				'name'     => 'img_css_filters',
				'selector' => '{{WRAPPER}} .sa-post-img',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_title_style',
			[
				'label' => esc_html__( 'Title', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_title' => 'yes',
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
					'{{WRAPPER}} .sa-post-title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		/**
		 * Global Title
		 */
		$this->register_post_title_controls_style();

		$this->end_controls_section();

		$this->start_controls_section(
			'section_text_style',
			[
				'label'     => esc_html__( 'Text', 'sky-elementor-addons' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [ 'show_excerpt' => 'yes' ],
			]
		);

		/**
		 * Global Text Controls
		 */
		$this->register_post_text_controls_style();

		$this->end_controls_section();

		$this->start_controls_section(
			'section_category_style',
			[
				'label' => esc_html__( 'Category', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_category' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'category_spacing',
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
					'{{WRAPPER}} .sa-post-category' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'category_space_between',
			[
				'label'      => esc_html__( 'Space Between', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 50,
					],
				],
				'selectors'  => [
					'{{WRAPPER}}' => '--sa-post-category-spacing: {{SIZE}}{{UNIT}};',
				],
			]
		);

		/**
		 * Global Category
		 */

		$this->register_post_category_controls_style();

		$this->end_controls_section();

		$this->start_controls_section(
			'section_meta_style',
			[
				'label' => esc_html__( 'Meta', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
				'conditions' => [
					'relation' => 'or',
					'terms'    => [
						[
							'name'  => 'show_author',
							'value' => 'yes',
						],
						[
							'name'  => 'show_date',
							'value' => 'yes',
						],
					],
				],
			]
		);

		$this->add_responsive_control(
			'meta_spacing',
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
					'{{WRAPPER}} .sa-post-meta' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'meta_space_between',
			[
				'label'      => esc_html__( 'Space Between', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 50,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-post-meta' => 'grid-gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		/**
		 * Global Controls Meta
		 */

		$this->register_post_meta_controls_style();

		$this->end_controls_section();

		$this->start_controls_section(
			'play_btn_style',
			[
				'label' => esc_html__( 'Play Button', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_video' => 'yes',
				],
			]
		);

		/**
		 * Global Controls
		 */
		$this->player_button_style( [
			'prefix'   => 'play_button',
			'selector' => '.sa-post-play-button',
		] );

		$this->end_controls_section();

		/**
		 * Global Navigation Style Controls
		 */
		$this->register_navigation_style_controls( 'sapling-carousel' );

		/**
		 * Global Pagination Controls
		 */
		$this->register_pagination_style_controls( 'sapling-carousel' );
	}

	public function get_taxonomies() {
		$taxonomies = get_taxonomies( [ 'show_in_nav_menus' => true ], 'objects' );

		$options = [ '' => '' ];

		foreach ( $taxonomies as $taxonomy ) {
			$options[ $taxonomy->name ] = $taxonomy->label;
		}

		return $options;
	}

	public function get_posts_tags() {
		$taxonomy = $this->get_settings( 'taxonomy' );

		foreach ( $this->_query->posts as $post ) {
			if ( ! $taxonomy ) {
				$post->tags = [];

				continue;
			}

			$tags = wp_get_post_terms( $post->ID, $taxonomy );

			$tags_slugs = [];

			foreach ( $tags as $tag ) {
				$tags_slugs[ $tag->term_id ] = $tag;
			}

			$post->tags = $tags_slugs;
		}
	}

	/**
	 * Get post query builder arguments
	 */
	public function query_posts( $posts_per_page ) {
		$settings = $this->get_settings();

		$args = [];
		if ( $posts_per_page ) {
			$args['posts_per_page'] = $posts_per_page;
			$args['paged']          = max( 1, get_query_var( 'paged' ), get_query_var( 'page' ) );
		}

		$default = $this->getGroupControlQueryArgs();
		$args    = array_merge( $default, $args );

		$this->_query = new \WP_Query( $args );
	}

	protected function render_author() {
		$settings = $this->get_settings_for_display();
		if ( 'yes' !== $settings['show_author'] ) {
			return;
		}

		?>
		<div class="sa-post-author-wrapper sa-d-flex">
			<a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>"
				class="sa-d-inline-flex sa-align-items-center">
				<div class="sa-icon-wrap sa-me-1">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000" aria-hidden="true">
						<path d="M313 792C367 825 433 846 500 846 571 846 633 825 688 792 667 767 642 746 617 729 583 708 542 700 500 700 425 700 354 733 313 792ZM229 721C296 642 392 592 500 592 558 592 617 608 671 637 708 658 742 687 771 721 821 662 850 583 850 500 850 308 696 150 500 150S150 308 150 500C150 583 183 662 229 721ZM500 958C246 958 42 754 42 500S246 42 500 42 958 246 958 500 754 958 500 958ZM500 575C400 575 321 496 321 396S400 217 500 217 679 296 679 396 600 575 500 575ZM500 467C538 467 571 433 571 396S538 325 500 325 429 358 429 396 463 467 500 467Z"></path>
					</svg>
				</div>
				<span class="sa-post-author-text">
					<?php echo get_the_author(); ?>
				</span>
			</a>
		</div>
		<?php
	}

	protected function render_date() {
		$settings = $this->get_settings_for_display();
		if ( 'yes' !== $settings['show_date'] ) {
			return;
		}
		?>
		<div class="sa-post-date-wrapper sa-d-flex sa-align-items-center">
			<?php if ( 'yes' === $settings['show_author'] ) : ?>
				<div class="sa-icon-wrap sa-me-1">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000" aria-hidden="true">
						<path d="M917 246V883C917 933 875 971 829 971H171C125 967 83 929 83 879V246C83 196 125 158 171 158H258V62C263 50 271 42 283 42H358C371 42 379 50 379 62V158H617V62C617 50 625 42 638 42H713C725 42 733 50 733 62V158H821C875 158 917 196 917 246ZM829 871V329H171V867C171 871 175 879 183 879H817C821 879 829 875 829 871ZM358 504H283C271 504 263 496 263 483V408C263 396 271 387 283 387H358C371 387 379 396 379 408V479C379 492 371 504 358 504ZM558 483C558 496 550 504 538 504H463C450 504 442 496 442 483V408C442 396 450 387 463 387H538C550 387 558 396 558 408V483ZM738 483C738 496 729 504 717 504H642C629 504 621 496 621 483V408C621 396 629 387 642 387H717C729 387 738 396 738 408V483ZM558 642C558 654 550 662 538 662H463C450 662 442 654 442 642V571C442 558 450 550 463 550H538C550 550 558 558 558 571V642ZM379 642C379 654 371 662 358 662H283C271 662 263 654 263 642V571C263 558 271 550 283 550H358C371 550 379 558 379 571V642ZM738 642C738 654 729 662 717 662H642C629 662 621 654 621 642V571C621 558 629 550 642 550H717C729 550 738 558 738 571V642ZM558 800C558 812 550 821 538 821H463C450 821 442 812 442 800V729C442 717 450 708 463 708H538C550 708 558 717 558 729V800ZM379 800C379 812 371 821 358 821H283C271 821 263 812 263 800V729C263 717 271 708 283 708H358C371 708 379 717 379 729V800ZM738 800C738 812 729 821 717 821H642C629 821 621 812 621 800V729C621 717 629 708 642 708H717C729 708 738 717 738 729V800Z"></path>
					</svg>
				</div>
			<?php endif; ?>
			<?php
			$this->render_post_date();
			?>
		</div>
		<?php
	}

	protected function render_item( $post_id, $image_size, $excerpt_length ) {
		// global $post;
		$settings = $this->get_settings_for_display();
		?>
		<div class="swiper-slide">
			<div class="sa-post-item sa-d-flex sa-p-2 sa-w-100 sa-h-100">

				<?php $this->render_post_thumb_with_video( $post_id, $image_size, [ 'play_class' => 'sa-p-3' ] ); ?>

				<div class="sa-post-content-wrapper sa-d-flex sa-flex-column">
					<?php
					$this->render_post_category( [
						'wrapper_class' => 'sa-post-category-hover-bg sa-text-uppercase sa-mb-3',
					] );

					$title_class = 'sa-mb-4';
					if ( 'yes' === $settings['title_truncate'] ) {
						$title_class = 'sa-text-truncate sa-w-100 sa-mb-4';
					}

					$this->render_post_title( [
						'wrapper_class' => $title_class,
					] );

					$this->render_post_excerpt( $excerpt_length );
					?>
					<div class="sa-post-meta sa-d-flex">

						<?php $this->render_author(); ?>

						<?php $this->render_date(); ?>

					</div>
				</div>
			</div>
		</div>
		<?php
	}

	public function render_header() {
		$settings = $this->get_settings_for_display();
		$id       = 'sa-sapling-carousel-' . $this->get_id();

		/**
		 * global function
		 */
		$this->render_header_attributes( 'sapling-carousel' );

		$this->add_render_attribute(
			[
				'carousel' => [
					'class' => [ 'sa-sapling-carousel', 'sa-swiper-global-carousel', 'sa-img-effect-1' ],
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
	protected function render() {
		$settings = $this->get_settings_for_display();

		$this->query_posts( $settings['posts_per_page'] );
		$wp_query = $this->get_query();

		if ( ! $wp_query->found_posts ) {
			return;
		}

		$this->render_header();

		while ( $wp_query->have_posts() ) :
			$wp_query->the_post();

			$thumbnail_size = $settings['primary_thumbnail_size'];

			$this->get_posts_tags();

			$this->render_item( get_the_ID(), $thumbnail_size, $settings['excerpt_length'] );

		endwhile;

		wp_reset_postdata();

		/**
		 * global function
		 */
		$this->render_footer();
	}
}

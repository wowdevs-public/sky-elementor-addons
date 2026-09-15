<?php

namespace Sky_Addons\Modules\UltraGrid\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Css_Filter;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Widget_Base;
use Sky_Addons\Includes\Controls\GroupQuery\Group_Control;
use Sky_Addons\Traits\Global_Widget_Controls;
use Sky_Addons\Traits\Global_Widget_Functions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// Exit if accessed directly

class Ultra_Grid extends Widget_Base {
	use Group_Control;
	use Global_Widget_Functions;
	use Global_Widget_Controls;

	private $_query = null;

	public function get_name() {
		return 'sky-ultra-grid';
	}

	public function get_title() {
		return esc_html__( 'Ultra Grid', 'sky-elementor-addons' );
	}

	public function get_icon() {
		return 'sky-icon-ultra-grid';
	}

	public function get_categories() {
		return [ 'sky-elementor-addons' ];
	}

	public function get_keywords() {
		return [ 'sky', 'post', 'list', 'blogs', 'grid' ];
	}

	public function get_style_depends() {
		if ( sky_addons_editor_mode() ) {
			return [ 'elementor-icons-fa-solid', 'sky-addons-styles' ];
		}

		return [ 'elementor-icons-fa-solid', 'sa-ultra-grid' ];
	}

	public function get_query() {
		return $this->_query;
	}

	public function has_widget_inner_wrapper(): bool {
		return ! \Elementor\Plugin::$instance->experiments->is_feature_active( 'e_optimized_markup' );
	}

	protected function register_controls() {
		$this->start_controls_section(
			'section_ultra_grid_layout',
			[
				'label' => esc_html__( 'Layout', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'content_layout',
			[
				'label'          => esc_html__( 'Select Layout', 'sky-elementor-addons' ),
				'type'           => Controls_Manager::SELECT,
				'options'        => [
					'default'  => esc_html__( 'Default', 'sky-elementor-addons' ),
					'layout_1' => esc_html__( 'Layout 1', 'sky-elementor-addons' ),
					'layout_2' => esc_html__( 'Layout 2', 'sky-elementor-addons' ),
					'layout_3' => esc_html__( 'Layout 3', 'sky-elementor-addons' ),
				],
				'default'        => 'default',
				'tablet_default' => 'default',
				'mobile_default' => 'default',
				'prefix_class'   => 'sa-ultra-grid-',
			]
		);

		$this->add_responsive_control(
			'item_min_height',
			[
				'label'      => esc_html__( 'Min Height', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.0.0' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'vh', 'rem' ],
				'range'      => [
					'px'  => [
						'min' => 100,
						'max' => 900,
					],
					'vh'  => [
						'min' => 10,
						'max' => 100,
					],
					'rem' => [
						'min' => 5,
						'max' => 60,
					],
				],
				// No default on purpose. The item's height comes from `grid-auto-rows: 1fr` on an
				// auto-height grid, so it resolves to the tallest card's intrinsic image height —
				// this control puts a floor under that. Left empty it writes nothing and the
				// stylesheet's own floors still apply (360px for layout_3 above 1025px, 320px
				// below 768px); set, it out-specifies both, which is the point.
				//
				// The item is a flex container, so nothing below needs a height of its own: the
				// image wrapper is a flex item and the default `align-items: stretch` sizes it to
				// whatever this resolves to. See the stretch note in the stylesheet.
				'selectors'  => [
					'{{WRAPPER}} .sa-post-item' => 'min-height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'row_gap',
			[
				'label'      => esc_html__( 'Row Gap', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 50,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-ultra-grid' => 'grid-row-gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'column_gap',
			[
				'label'      => esc_html__( 'Column Gap', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 50,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-ultra-grid' => 'grid-column-gap: {{SIZE}}{{UNIT}};',
				],
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
				'selectors' => [
					'{{WRAPPER}} .sa-post-item' => 'text-align: {{VALUE}};',
					// `.sa-post-category` is `display: inline-flex` and shrink-wraps its chips, so it
					// has no free space for `justify-content` to distribute — that clause was inert.
					// The `text-align` above already moves it, since an inline-flex box is inline-level.
					'{{WRAPPER}} .sa-post-meta' => 'justify-content: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		/*
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

		$this->update_control(
			'posts_per_page',
			[
				'default' => 3,
			]
		);

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
			'show_image',
			[
				'label'   => esc_html__( 'Show Image', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::HIDDEN,
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
				'label'   => esc_html__( 'Show Author', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'no',
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

		/*
		 * Global Date Controls
		 */

		$this->add_control(
			'show_date',
			[
				'label'     => esc_html__( 'Show Date', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'no',
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

		$this->add_control(
			'show_pagination',
			[
				'label'     => esc_html__( 'Show Pagination', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SWITCHER,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'show_button',
			[
				'label'     => esc_html__( 'Show Read More', 'sky-elementor-addons' ),
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

		/*
		 * Global Video Lightbox Control
		 */
		$this->video_lightbox_controls();

		$this->end_controls_section();

		$this->start_controls_section(
			'section_button',
			[
				'label' => esc_html__( 'Read More', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
				'condition' => [
					'show_button' => 'yes',
				],
			]
		);

		$this->add_control(
			'button_text',
			[
				'label'   => esc_html__( 'Button Text', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'READ MORE', 'sky-elementor-addons' ),
				'dynamic' => [ 'active' => true ],
			]
		);

		$this->add_control(
			'button_icon',
			[
				'label' => esc_html__( 'Icon', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::ICONS,
			]
		);

		$this->add_control(
			'button_icon_position',
			[
				'label'          => esc_html__( 'Icon Position', 'sky-elementor-addons' ),
				'type'           => Controls_Manager::CHOOSE,
				'label_block'    => false,
				'options'        => [
					'before' => [
						'title' => esc_html__( 'Before', 'sky-elementor-addons' ),
						'icon'  => 'eicon-h-align-left',
					],
					'after'  => [
						'title' => esc_html__( 'After', 'sky-elementor-addons' ),
						'icon'  => 'eicon-h-align-right',
					],
				],
				'default'        => 'after',
				'toggle'         => false,
				'condition'      => [
					'button_icon[value]!' => '',
				],
				'style_transfer' => true,
			]
		);

		$this->add_responsive_control(
			'button_icon_spacing',
			[
				'label'      => esc_html__( 'Icon Spacing', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 20,
					],
				],
				'condition'  => [
					'button_icon[value]!' => '',
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-button-icon-before .sa-button-icon' => 'margin-right: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .sa-button-icon-after .sa-button-icon' => 'margin-left: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_ultra_grid_style',
			[
				'label' => esc_html__( 'Ultra Grid', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'item_padding',
			[
				'label'      => esc_html__( 'Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				// `.sa-post-content-wrapper` is position:absolute, so it resolves against the item's
				// PADDING box and never sees the padding above — only the image gets inset. Re-apply
				// the same values as offsets so the overlay stays inside the padded area. These reuse
				// the placeholders the `padding` shorthand already carries, so they add no new failure
				// mode: an unlinked dimension with a blank side makes Elementor skip the entire control
				// (core/files/css/base.php:401), and the stylesheet's `left: 0; right: 0; width: auto`
				// baseline stands in.
				'selectors'  => [
					'{{WRAPPER}} .sa-post-item'            => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .sa-post-content-wrapper' => 'left: {{LEFT}}{{UNIT}}; right: {{RIGHT}}{{UNIT}}; bottom: {{BOTTOM}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'content_padding',
			[
				'label'      => esc_html__( 'Content Padding', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.0.0' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', 'rem', '%' ],
				// The markup already puts `sa-p-4` (1.5rem) on .sa-post-content-wrapper, which
				// this control could not see. Default states it; the class stays as the
				// stale-cache fallback. See CONTROLS-REFERENCE.md § Dimensions with Default Values.
				'default'    => [
					'top'      => '1.5',
					'right'    => '1.5',
					'bottom'   => '1.5',
					'left'     => '1.5',
					'unit'     => 'rem',
					'isLinked' => true,
				],
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
				'default'    => [
					'top'      => '.8',
					'right'    => '.8',
					'bottom'   => '.8',
					'left'     => '.8',
					'unit'     => 'em',
					'isLinked' => true,
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-post-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow: hidden;',
				],
			]
		);

		$this->start_controls_tabs( 'item_style_tabs' );

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
				// Overlay layout: the image fills .sa-post-item, so this fill only shows where
				// `item_padding` pulls the image away from the frame. That is what makes Padding
				// a distinct control here rather than a second Item Gap.
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.0.0' ),
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
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.0.0' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .sa-post-item:hover',
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

		// Sizing first, appearance after. The image is stretched to the item by
		// `width/height: 100%` in base.less and cropped with `object-fit: cover`, neither of
		// which was reachable from the panel — so how the photo sat inside the card was the one
		// thing this section could not touch. Pair these with Item -> Min Height.
		$this->add_responsive_control(
			'img_object_fit',
			[
				'label'     => esc_html__( 'Image Fit', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.0.0' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '',
				'options'   => [
					''        => esc_html__( 'Default', 'sky-elementor-addons' ),
					'cover'   => esc_html__( 'Cover', 'sky-elementor-addons' ),
					'contain' => esc_html__( 'Contain', 'sky-elementor-addons' ),
					'fill'    => esc_html__( 'Fill', 'sky-elementor-addons' ),
					'none'    => esc_html__( 'None', 'sky-elementor-addons' ),
				],
				'selectors' => [
					'{{WRAPPER}} .sa-post-img' => 'object-fit: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'img_object_position',
			[
				'label'     => esc_html__( 'Image Position', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.0.0' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '',
				'options'   => [
					''              => esc_html__( 'Default', 'sky-elementor-addons' ),
					'center center' => esc_html__( 'Center Center', 'sky-elementor-addons' ),
					'center left'   => esc_html__( 'Center Left', 'sky-elementor-addons' ),
					'center right'  => esc_html__( 'Center Right', 'sky-elementor-addons' ),
					'top center'    => esc_html__( 'Top Center', 'sky-elementor-addons' ),
					'top left'      => esc_html__( 'Top Left', 'sky-elementor-addons' ),
					'top right'     => esc_html__( 'Top Right', 'sky-elementor-addons' ),
					'bottom center' => esc_html__( 'Bottom Center', 'sky-elementor-addons' ),
					'bottom left'   => esc_html__( 'Bottom Left', 'sky-elementor-addons' ),
					'bottom right'  => esc_html__( 'Bottom Right', 'sky-elementor-addons' ),
				],
				'selectors' => [
					'{{WRAPPER}} .sa-post-img' => 'object-position: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'img_hover_zoom',
			[
				'label'     => esc_html__( 'Hover Zoom', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.0.0' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min'  => 1,
						'max'  => 2,
						'step' => 0.05,
					],
				],
				// Mirrors the `scale(1.1)` the shared `.sa-img-effect-1-1` utility already applies
				// on item hover, so stating it changes nothing until the user moves the handle.
				// 1 disables the zoom.
				'default'   => [ 'size' => 1.1 ],
				'selectors' => [
					'{{WRAPPER}} .sa-post-item:hover .sa-post-img' => 'transform: scale({{SIZE}});',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'      => 'image_overlay',
				'label'     => esc_html__( 'Image Overlay', 'sky-elementor-addons' ),
				'types'     => [ 'gradient' ],
				'separator' => 'before',
				'exclude'   => [ 'image' ],
				'fields_options' => [
					'background' => [
						'label' => esc_html__( 'Image Overlay', 'sky-elementor-addons' ),
					],
				],
				'selector'  => '{{WRAPPER}} .sa-post-img-wrapper:after',
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
					'{{WRAPPER}} .sa-post-img'          => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .sa-post-img-wrapper:after' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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

		/*
		 * Global Title
		 */

		$this->register_post_title_controls_style();

		$this->add_responsive_control(
			'title_padding',
			[
				'label'      => esc_html__( 'Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				// 'default'    => [
				// 'size'
				// ]
				'selectors'  => [
					'{{WRAPPER}} .sa-post-title a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'      => 'title_background',
				'label'     => esc_html__( 'Title Background', 'sky-elementor-addons' ),
				'types'     => [ 'classic', 'gradient' ],
				'separator' => 'before',
				'exclude'   => [ 'image' ],
				'fields_options' => [
					'background' => [
						'label' => 'Title Background',
					],
				],
				'selector'  => '{{WRAPPER}} .sa-post-title a',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_text_style',
			[
				'label'     => esc_html__( 'Text', 'sky-elementor-addons' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [ 'show_excerpt' => 'yes' ],
			]
		);

		/*
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

		/*
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

		/*
		 * Global Pagination
		 *
		 */
		$this->register_post_pagination_controls_style();

		$this->start_controls_section(
			'section_button_style',
			[
				'label' => esc_html__( 'Read More', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_button' => 'yes',
				],
			]
		);

		/**
		 * Global Read more
		 */
		$this->general_button_controls_style();

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

		/*
		 * Global Controls
		 */
		$this->player_button_style( [
			'prefix'   => 'play_button',
			'selector' => '.sa-post-play-button',
		] );

		$this->end_controls_section();
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
	 * Get post query builder arguments.
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
		} ?>
		<div class="sa-post-author-wrapper sa-d-flex">
			<a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>"
				class="sa-d-inline-flex sa-align-items-center">
				<div class="sa-icon-wrap sa-me-1">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000" aria-hidden="true">
						<path d="M313 792C367 825 433 846 500 846 571 846 633 825 688 792 667 767 642 746 617 729 583 708 542 700 500 700 425 700 354 733 313 792ZM229 721C296 642 392 592 500 592 558 592 617 608 671 637 708 658 742 687 771 721 821 662 850 583 850 500 850 308 696 150 500 150S150 308 150 500C150 583 183 662 229 721ZM500 958C246 958 42 754 42 500S246 42 500 42 958 246 958 500 754 958 500 958ZM500 575C400 575 321 496 321 396S400 217 500 217 679 296 679 396 600 575 500 575ZM500 467C538 467 571 433 571 396S538 325 500 325 429 358 429 396 463 467 500 467Z"></path>
					</svg>
				</div>
				<span class="sa-post-author-text">
					<?php echo wp_kses_post( get_the_author() ); ?>
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
			<div class="sa-icon-wrap sa-me-1">
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000" aria-hidden="true">
					<path d="M917 246V883C917 933 875 971 829 971H171C125 967 83 929 83 879V246C83 196 125 158 171 158H258V62C263 50 271 42 283 42H358C371 42 379 50 379 62V158H617V62C617 50 625 42 638 42H713C725 42 733 50 733 62V158H821C875 158 917 196 917 246ZM829 871V329H171V867C171 871 175 879 183 879H817C821 879 829 875 829 871ZM358 504H283C271 504 263 496 263 483V408C263 396 271 387 283 387H358C371 387 379 396 379 408V479C379 492 371 504 358 504ZM558 483C558 496 550 504 538 504H463C450 504 442 496 442 483V408C442 396 450 387 463 387H538C550 387 558 396 558 408V483ZM738 483C738 496 729 504 717 504H642C629 504 621 496 621 483V408C621 396 629 387 642 387H717C729 387 738 396 738 408V483ZM558 642C558 654 550 662 538 662H463C450 662 442 654 442 642V571C442 558 450 550 463 550H538C550 550 558 558 558 571V642ZM379 642C379 654 371 662 358 662H283C271 662 263 654 263 642V571C263 558 271 550 283 550H358C371 550 379 558 379 571V642ZM738 642C738 654 729 662 717 662H642C629 662 621 654 621 642V571C621 558 629 550 642 550H717C729 550 738 558 738 571V642ZM558 800C558 812 550 821 538 821H463C450 821 442 812 442 800V729C442 717 450 708 463 708H538C550 708 558 717 558 729V800ZM379 800C379 812 371 821 358 821H283C271 821 263 812 263 800V729C263 717 271 708 283 708H358C371 708 379 717 379 729V800ZM738 800C738 812 729 821 717 821H642C629 821 621 812 621 800V729C621 717 629 708 642 708H717C729 708 738 717 738 729V800Z"></path>
				</svg>
			</div>
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
		<div class="sa-post-item sa-d-flex">

			<?php $this->render_post_thumb_with_video( $post_id, $image_size ); ?>

			<div class="sa-post-content-wrapper sa-w-100 sa-p-4">
				<div class="sa-post-content-main">
					<?php
					// Only emit the meta row when something goes in it. Category, title and
					// excerpt each carry their own margin and disappear with their toggle, but
					// this wrapper was unconditional — with Author and Date both off it left an
					// empty flex box still holding `sa-mb-1` plus whatever Meta → Spacing set,
					// so the content sat lower than the remaining elements accounted for.
					$has_meta = ( 'yes' === $settings['show_author'] ) || ( 'yes' === $settings['show_date'] );

					if ( $has_meta ) :
						?>
						<div class="sa-post-meta sa-d-flex sa-mb-1">

							<?php $this->render_author(); ?>

							<?php $this->render_date(); ?>

						</div>
						<?php
					endif;

					$this->render_post_category( [
						'wrapper_class' => 'sa-post-category-style-1 sa-mb-3',
					] );

					$this->render_post_title( [
						'wrapper_class' => 'sa-mb-2',
					] );

					$this->render_post_excerpt( $excerpt_length );
					?>
				</div>

				<div class="sa-post-content-cta">
					<?php
					// Outside `.sa-post-content-main` so the two can share one grid cell — see
					// the stacking note in the stylesheet. The button needs this wrapper rather
					// than being the grid item itself: a grid item is placed by `justify-self`,
					// which the Alignment control's `text-align` cannot reach. The wrapper
					// stretches, the inline-block anchor inside it follows `text-align`.
					$this->render_post_general_button();
					?>
				</div>
			</div>
		</div>
		<?php
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$this->add_render_attribute( 'ultra-grid', [
			'class' => 'sa-ultra-grid sa-img-effect-1-1',
		] );

		$this->query_posts( $settings['posts_per_page'] );
		$wp_query = $this->get_query();

		if ( ! $wp_query->found_posts ) {
			return;
		}

		?>
		<div <?php $this->print_render_attribute_string( 'ultra-grid' ); ?>>
			<?php
			while ( $wp_query->have_posts() ) :
				$wp_query->the_post();

				$thumbnail_size = $settings['primary_thumbnail_size'];

				$this->get_posts_tags();

				$this->render_item( get_the_ID(), $thumbnail_size, $settings['excerpt_length'] );

			endwhile;
			?>
		</div>

		<?php

		if ( 'yes' === $settings['show_pagination'] ) {
			if ( function_exists( 'sky_addons_post_pagination' ) ) {
				sky_addons_post_pagination( $wp_query, $this->get_id() );
			}
		}

		wp_reset_postdata();
	}
}

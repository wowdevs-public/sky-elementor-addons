<?php

namespace Sky_Addons\Modules\TidyList\Widgets;

use Elementor\Utils;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Icons_Manager;
use Elementor\Repeater;
use Elementor\Widget_Base;
use Sky_Addons\Includes\Controls\GroupQuery\Group_Control;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Tidy_List extends Widget_Base {

	use Group_Control;

	private $_query = null;

	public function get_query() {
		return $this->_query;
	}

	public function query_posts( $posts_per_page ) {
		$settings = $this->get_settings();
		$args     = [];

		if ( $posts_per_page ) {
			$args['posts_per_page'] = $posts_per_page;
			$args['paged']          = max( 1, get_query_var( 'paged' ), get_query_var( 'page' ) );
		}

		$default      = $this->getGroupControlQueryArgs();
		$args         = array_merge( $default, $args );
		$this->_query = new \WP_Query( $args );
	}

	public function get_name() {
		return 'sky-tidy-list';
	}

	public function get_title() {
		return esc_html__( 'Tidy List', 'sky-elementor-addons' );
	}

	public function get_icon() {
		return 'sky-icon-tidy-list';
	}

	public function get_categories() {
		return [ 'sky-elementor-addons' ];
	}

	public function get_keywords() {
		return [ 'sky', 'list', 'listgroup' ];
	}

	public function get_style_depends() {
		if ( sky_addons_editor_mode() ) {
			return [ 'sky-addons-styles' ];
		}

		return [ 'sa-tidy-list' ];
	}

	public function get_custom_help_url() {
		return 'https://skyaddons.com/docs/sky-addons/widgets/tidy-list/';
	}

	public function has_widget_inner_wrapper(): bool {
		return ! \Elementor\Plugin::$instance->experiments->is_feature_active( 'e_optimized_markup' );
	}

	protected function register_controls() {

		$this->start_controls_section(
			'section_tidy_list',
			[
				'label' => esc_html__( 'Tidy List', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_responsive_control(
			'list_layout',
			[
				'label'           => esc_html__( 'Layout', 'sky-elementor-addons' ),
				'type'            => Controls_Manager::CHOOSE,
				'label_block'     => false,
				'options'         => [
					'block'  => [
						'title' => esc_html__( 'Block', 'sky-elementor-addons' ),
						'icon'  => 'eicon-editor-list-ul',
					],
					'inline' => [
						'title' => esc_html__( 'Inline', 'sky-elementor-addons' ),
						'icon'  => 'eicon-navigation-horizontal',
					],
				],
				'style_transfer'  => true,
				'toggle'          => false,
				'desktop_default' => 'block',
				'tablet_default'  => 'block',
				'mobile_default'  => 'block',
				'selectors'       => [
					'{{WRAPPER}} .sa-list-item' => '{{VALUE}};',
				],
				'prefix_class'    => 'sa-list-layout-%s-',
				'selectors_dictionary' => [
					'block'  => 'display: block; width: 100%;',
					'inline' => 'display: inline-block; width: auto;',
				],
			]
		);

		$this->add_control(
			'content_type',
			[
				'label'     => esc_html__( 'Content Source', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'repeater',
				'options'   => [
					'repeater' => esc_html__( 'Custom (Repeater)', 'sky-elementor-addons' ),
					'posts'    => esc_html__( 'Dynamic Posts', 'sky-elementor-addons' ),
					'acf'      => esc_html__( 'ACF Fields', 'sky-elementor-addons' ),
				],
				'separator' => 'after',
			]
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'media_type',
			[
				'label'          => esc_html__( 'Media Type', 'sky-elementor-addons' ),
				'type'           => Controls_Manager::CHOOSE,
				'label_block'    => false,
				'options'        => [
					'icon'   => [
						'title' => esc_html__( 'Icon', 'sky-elementor-addons' ),
						'icon'  => 'eicon-check',
					],
					'image'  => [
						'title' => esc_html__( 'Image', 'sky-elementor-addons' ),
						'icon'  => 'eicon-image',
					],
					'number' => [
						'title' => esc_html__( 'Number', 'sky-elementor-addons' ),
						'icon'  => 'fas fa-sort-numeric-down',
					],
				],
				'default'        => 'icon',
				'toggle'         => true,
				'style_transfer' => true,
			]
		);

		$repeater->add_control(
			'list_icon',
			[
				'type'        => Controls_Manager::ICONS,
				'label_block' => true,
				'condition'   => [
					'media_type' => 'icon',
				],
			]
		);

		$repeater->add_control(
			'list_image',
			[
				'label'   => esc_html__( 'Choose Image', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::MEDIA,
				'default' => [
					'url' => Utils::get_placeholder_image_src(),
				],
				'condition' => [
					'media_type' => 'image',
				],
				'dynamic' => [ 'active' => true ],
			]
		);

		$repeater->add_control(
			'list_number',
			[
				'label'   => esc_html__( 'Number', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( '1', 'sky-elementor-addons' ),
				'dynamic' => [ 'active' => true ],
				'condition' => [
					'media_type' => 'number',
				],
			]
		);

		$repeater->add_control(
			'list_title',
			[
				'label'       => esc_html__( 'Title', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'List Title', 'sky-elementor-addons' ),
				'label_block' => true,
				'dynamic'     => [ 'active' => true ],
				'separator'   => 'before',
			]
		);

		$repeater->add_control(
			'list_text',
			[
				'label'       => esc_html__( 'Text', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXTAREA,
				'default'     => esc_html__( 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'sky-elementor-addons' ),
				'label_block' => true,
				'dynamic'     => [ 'active' => true ],
			]
		);

		$repeater->add_control(
			'list_link',
			[
				'label'         => esc_html__( 'Link', 'sky-elementor-addons' ),
				'type'          => Controls_Manager::URL,
				'placeholder'   => esc_html__( 'https://your-link.com', 'sky-elementor-addons' ),
				'show_external' => true,
				'default'       => [
					'url'         => '',
					'is_external' => false,
				],
				'dynamic'       => [ 'active' => true ],
			]
		);

		$this->add_control(
			'list',
			[
				'label'       => '',
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => [
					[
						'list_title' => esc_html__( 'List Title #1', 'sky-elementor-addons' ),
						'list_text'  => esc_html__( 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'sky-elementor-addons' ),
						'list_icon'  => [
							'value'   => 'fas fa-check',
							'library' => 'fa-solid',
						],
					],
					[
						'list_title' => esc_html__( 'List Title #2', 'sky-elementor-addons' ),
						'list_text'  => esc_html__( 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'sky-elementor-addons' ),
						'list_icon'  => [
							'value'   => 'fas fa-check',
							'library' => 'fa-solid',
						],
					],
					[
						'list_title' => esc_html__( 'List Title #3', 'sky-elementor-addons' ),
						'list_text'  => esc_html__( 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'sky-elementor-addons' ),
						'list_icon'  => [
							'value'   => 'fas fa-check',
							'library' => 'fa-solid',
						],
					],
				],
				'title_field' => '{{{ list_title }}}',
				'condition'   => [ 'content_type' => 'repeater' ],
			]
		);

		$this->add_control(
			'show_text',
			[
				'label' => esc_html__( 'Show Text', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::SWITCHER,
			]
		);

		$this->add_control(
			'text_length',
			[
				'label'   => esc_html__( 'Content Length', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 20,
				'min'     => 1,
				'max'     => 200,
				'condition' => [
					'show_text'     => 'yes',
					'content_type!' => 'repeater',
				],
			]
		);

		$this->add_control(
			'text_more',
			[
				'label'       => esc_html__( 'Read More Text', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'Read more', 'sky-elementor-addons' ),
				'condition'   => [
					'show_text'     => 'yes',
					'content_type!' => 'repeater',
				],
			]
		);

		$this->add_control(
			'show_image',
			[
				'label'   => esc_html__( 'Show Image', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_responsive_control(
			'media_position',
			[
				'label'          => esc_html__( 'Media Position', 'sky-elementor-addons' ),
				'type'           => Controls_Manager::CHOOSE,
				'label_block'    => false,
				'options'        => [
					'left'   => [
						'title' => esc_html__( 'Left', 'sky-elementor-addons' ),
						'icon'  => 'eicon-h-align-left',
					],
					'top'    => [
						'title' => esc_html__( 'Top', 'sky-elementor-addons' ),
						'icon'  => 'eicon-v-align-top',
					],
					'bottom' => [
						'title' => esc_html__( 'Bottom', 'sky-elementor-addons' ),
						'icon'  => 'eicon-v-align-bottom',
					],
					'right'  => [
						'title' => esc_html__( 'Right', 'sky-elementor-addons' ),
						'icon'  => 'eicon-h-align-right',
					],
				],
				'style_transfer' => true,
				'toggle'         => true,
				'default'        => 'left',
				'prefix_class'   => 'sa-media-position-%s-',
				'selectors'      => [
					'{{WRAPPER}} .sa-list-wrapper' => '{{VALUE}};',
				],
				'selectors_dictionary' => [
					'top'    => 'flex-direction: column; align-items: initial;',
					'bottom' => 'flex-direction: column-reverse; align-items: initial;',
					'left'   => 'flex-direction: initial; align-items: center;',
					'right'  => 'flex-direction: row-reverse; align-items: center;',
				],
			]
		);

		$this->add_responsive_control(
			'content_alignment',
			[
				'label'          => esc_html__( 'Content Alignment', 'sky-elementor-addons' ),
				'type'           => Controls_Manager::CHOOSE,
				'label_block'    => false,
				'options'        => [
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
				'style_transfer' => true,
				'default'        => 'left',
				'selectors'      => [
					'{{WRAPPER}} .sa-link' => '{{VALUE}};',
					'{{WRAPPER}} .sa-tidy-list .sa-media-wrapper' => '{{VALUE}};',
				],
				'selectors_dictionary' => [
					'left'   => 'justify-content: flex-start; text-align: left;',
					'center' => 'justify-content: center; text-align: center;',
					'right'  => 'justify-content: flex-end; text-align: right;',
				],
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

		$this->end_controls_section();

		$this->start_controls_section(
			'section_post_query_builder',
			[
				'label'     => esc_html__( 'Query', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'tab'       => Controls_Manager::TAB_CONTENT,
				'condition' => [ 'content_type' => 'posts' ],
			]
		);

		$this->register_query_builder_controls();

		$this->end_controls_section();

		$this->start_controls_section(
			'section_acf_settings',
			[
				'label'     => esc_html__( 'ACF Settings', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'tab'       => Controls_Manager::TAB_CONTENT,
				'condition' => [ 'content_type' => 'acf' ],
			]
		);

		$this->add_control(
			'acf_repeater_field',
			[
				'label'       => esc_html__( 'ACF Repeater Field', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'Enter repeater field name', 'sky-elementor-addons' ),
				'description' => esc_html__( 'Enter the machine name of the ACF repeater field.', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'acf_title_field',
			[
				'label'       => esc_html__( 'Title Field Mapping', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'Enter sub-field name for title', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'acf_content_field',
			[
				'label'       => esc_html__( 'Content Field Mapping', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'Enter sub-field name for content', 'sky-elementor-addons' ),
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_list_style',
			[
				'label' => esc_html__( 'List', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'list_alignment',
			[
				'label'          => esc_html__( 'Alignment', 'sky-elementor-addons' ),
				'type'           => Controls_Manager::CHOOSE,
				'label_block'    => false,
				'options'        => [
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
				'style_transfer' => true,
				'default'        => 'left',
				'selectors' => [
					'{{WRAPPER}} .sa-list-ul' => '{{VALUE}};',
				],
				'selectors_dictionary' => [
					'left'   => 'text-align: left; justify-content: flex-start;',
					'center' => 'text-align: center; justify-content: center;',
					'right'  => 'text-align: right; justify-content: flex-end;',
				],
				'condition' => [
					'list_layout' => 'inline',
				],
			]
		);

		$this->add_responsive_control(
			'space_between',
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
					'{{WRAPPER}} .sa-tidy-list' => '--list-space-between: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'min_height',
			[
				'label'      => esc_html__( 'Height', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 300,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-link' => 'min-height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'list_padding',
			[
				'label'      => esc_html__( 'Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-link' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'list_border',
				'label'    => esc_html__( 'Border', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-link',
			]
		);

		$this->add_responsive_control(
			'list_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-link' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->start_controls_tabs( 'list_tabs' );

		$this->start_controls_tab(
			'list_tab_normal',
			[
				'label' => esc_html__( 'Normal', 'sky-elementor-addons' ),
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'list_background',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .sa-link',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'list_shadow',
				'label'    => esc_html__( 'Box Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-link',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'list_tab_hover',
			[
				'label' => esc_html__( 'Hover', 'sky-elementor-addons' ),
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'list_background_hover',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .sa-link:hover',
			]
		);

		$this->add_control(
			'list_border_color_hover',
			[
				'label' => esc_html__( 'Border Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-link:hover' => 'border-color: {{VALUE}}',
				],
				'condition' => [
					'list_border_border!' => '',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'list_shadow_hover',
				'label'    => esc_html__( 'Box Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-link:hover',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		$this->start_controls_section(
			'section_list_media_style',
			[
				'label' => esc_html__( 'Media', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'media_size',
			[
				'label'      => esc_html__( 'Size', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 300,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-tidy-list' => '--media-size: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'media_spacing',
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
					'{{WRAPPER}} .sa-tidy-list' => '--tidy-media-spacing: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'media_padding',
			[
				'label'      => esc_html__( 'Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-media-wrapper img,
                            {{WRAPPER}} .sa-media-wrapper .sa-icon-wrap,
                            {{WRAPPER}} .sa-media-wrapper .sa-number' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'  => 'media_border',
				'label' => esc_html__( 'Border', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-media-wrapper img,
                            {{WRAPPER}} .sa-media-wrapper .sa-icon-wrap,
                            {{WRAPPER}} .sa-media-wrapper .sa-number',
			]
		);

		$this->add_responsive_control(
			'media_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-media-wrapper img,
                            {{WRAPPER}} .sa-media-wrapper .sa-icon-wrap,
                            {{WRAPPER}} .sa-media-wrapper .sa-number' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'media_number_icon_heading',
			[
				'label'     => esc_html__( 'Number / Icon', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->start_controls_tabs( 'media_tabs' );

		$this->start_controls_tab(
			'media_tab_normal',
			[
				'label' => esc_html__( 'Normal', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'num_icon_color',
			[
				'label' => esc_html__( 'Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-number, {{WRAPPER}} .sa-media-wrapper .sa-icon-wrap'  => 'color: {{VALUE}};',
					'{{WRAPPER}} .sa-media-wrapper .sa-icon-wrap *' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'  => 'media_background',
				'label' => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .sa-media-wrapper img,
                            {{WRAPPER}} .sa-media-wrapper .sa-icon-wrap,
                            {{WRAPPER}} .sa-media-wrapper .sa-number',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'media_tab_hover',
			[
				'label' => esc_html__( 'Hover', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'num_icon_color_hover',
			[
				'label' => esc_html__( 'Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-link:hover .sa-number, {{WRAPPER}} .sa-link:hover .sa-media-wrapper .sa-icon-wrap'  => 'color: {{VALUE}};',
					'{{WRAPPER}} .sa-link:hover .sa-media-wrapper .sa-icon-wrap *' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'num_icon_background_hover',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .sa-link:hover .sa-number, {{WRAPPER}} .sa-link:hover .sa-media-wrapper .sa-icon-wrap',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		$this->start_controls_section(
			'section_list_title_style',
			[
				'label' => esc_html__( 'Title', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'title_spacing',
			[
				'label'      => esc_html__( 'Bottom Spacing', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 50,
					],
					'%'  => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-title:has(+.sa-text)' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'title_color',
			[
				'label' => esc_html__( 'Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-title' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'title_color_hover',
			[
				'label' => esc_html__( 'Hover Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-link:hover .sa-title' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'title_typography',
				'label'    => esc_html__( 'Typography', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-title',
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'title_text_shadow',
				'label'    => esc_html__( 'Text Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-title',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_list_text_style',
			[
				'label' => esc_html__( 'Text', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_text' => 'yes',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'text_typography',
				'label'    => esc_html__( 'Typography', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-text',
			]
		);

		$this->add_control(
			'text_color',
			[
				'label' => esc_html__( 'Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-text' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'text_color_hover',
			[
				'label' => esc_html__( 'Hover Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-link:hover .sa-text' => 'color: {{VALUE}}',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_read_more_style',
			[
				'label' => esc_html__( 'Read More', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'tab'   => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_text'  => 'yes',
					'text_more!' => '',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'read_more_typography',
				'label'    => esc_html__( 'Typography', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-read-more',
			]
		);

		$this->add_responsive_control(
			'read_more_padding',
			[
				'label'      => esc_html__( 'Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-read-more' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'read_more_border',
				'label'    => esc_html__( 'Border', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-read-more',
			]
		);

		$this->add_responsive_control(
			'read_more_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-read-more' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->start_controls_tabs( 'read_more_tabs' );

		$this->start_controls_tab(
			'read_more_tab_normal',
			[
				'label' => esc_html__( 'Normal', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'read_more_color',
			[
				'label' => esc_html__( 'Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-read-more' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'read_more_background',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .sa-read-more',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'read_more_tab_hover',
			[
				'label' => esc_html__( 'Hover', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'read_more_color_hover',
			[
				'label' => esc_html__( 'Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-link:hover .sa-read-more' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'read_more_background_hover',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .sa-link:hover .sa-read-more',
			]
		);

		$this->add_control(
			'read_more_border_color_hover',
			[
				'label'     => esc_html__( 'Border Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-link:hover .sa-read-more' => 'border-color: {{VALUE}};',
				],
				'condition' => [ 'read_more_border_border!' => '' ],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	protected function render_media( array $item, array $settings ): void {
		$has_media = ( 'icon' === $item['media_type'] && ! empty( $item['list_icon']['value'] ) )
					|| ( 'image' === $item['media_type'] && ! empty( $item['list_image']['url'] ) && 'yes' === $settings['show_image'] )
					|| ( 'number' === $item['media_type'] && ! empty( $item['list_number'] ) );

		if ( ! $has_media ) {
			return;
		}
		?>
		<div class="sa-media-wrapper">

			<?php if ( 'icon' === $item['media_type'] ) : ?>

				<div class="sa-icon-wrap sa-text-center sa-d-flex sa-align-items-center">
					<?php Icons_Manager::render_icon( $item['list_icon'], [ 'aria-hidden' => 'true' ] ); ?>
				</div>

			<?php elseif ( 'image' === $item['media_type'] ) : ?>

				<div class="sa-img-wrap sa-d-inline-block">
					<?php
					if ( $item['list_image']['id'] ) {
						print( wp_get_attachment_image(
							$item['list_image']['id'],
							'medium',
							false,
							[ 'alt' => esc_html( $item['list_title'] ) ]
						) );
					} else {
						printf( '<img src="%1$s" alt="%2$s">', esc_url( $item['list_image']['url'] ), esc_html( $item['list_title'] ) );
					}
					?>
				</div>

			<?php elseif ( 'number' === $item['media_type'] ) : ?>

				<span class="sa-number">
					<?php echo esc_html( $item['list_number'] ); ?>
				</span>

			<?php endif; ?>

		</div>
		<?php
	}

	protected function render_title( array $item, array $settings ): void {
		if ( empty( $item['list_title'] ) ) {
			return;
		}

		printf(
			'<%s class="sa-title sa--title sa--text-title sa-mt-0 sa-mb-0">%s</%s>',
			esc_attr( Utils::validate_html_tag( $settings['title_tag'] ) ),
			esc_html( $item['list_title'] ),
			esc_attr( Utils::validate_html_tag( $settings['title_tag'] ) )
		);
	}

	protected function render_text( array $item, array $settings ): void {
		if ( empty( $item['list_text'] ) || 'yes' !== $settings['show_text'] ) {
			return;
		}
		$is_repeater = ( $settings['content_type'] ?? 'repeater' ) === 'repeater';
		$length      = $is_repeater ? 0 : (int) ( $settings['text_length'] ?? 20 );
		$more_text   = trim( $settings['text_more'] ?? '' );
		$more        = ! empty( $more_text ) ? ' <span class="sa-read-more">' . esc_html( $more_text ) . '</span>' : ' &hellip;';
		$text        = $length > 0 ? wp_trim_words( $item['list_text'], $length, $more ) : $item['list_text'];
		?>
		<div class="sa--text sa--text-info sa-text">
			<?php echo wp_kses_post( $text ); ?>
		</div>
		<?php
	}

	protected function render_item( array $item, array $settings ): void {
		$item_key = 'list_item_' . $item['_id'];
		$link_key = 'link_attr_' . $item['_id'];

		$this->add_render_attribute( $item_key, 'class', [
			'sa-list-item',
			'elementor-repeater-item-' . $item['_id'],
		] );

		$this->add_render_attribute( $link_key, 'class', 'sa-link sa-d-block sa-text-decoration-none' );
		$tag = 'div';

		if ( ! empty( $item['list_link']['url'] ) ) {
			$this->add_render_attribute( $link_key, 'href', esc_url( $item['list_link']['url'] ) );

			if ( $item['list_link']['is_external'] ) {
				$this->add_render_attribute( $link_key, 'target', '_blank' );
			}

			if ( $item['list_link']['nofollow'] ) {
				$this->add_render_attribute( $link_key, 'rel', 'nofollow' );
			}
			$tag = 'a';
		}
		?>
		<li <?php $this->print_render_attribute_string( $item_key ); ?>>
			<<?php echo esc_attr( $tag ); ?> <?php $this->print_render_attribute_string( $link_key ); ?>>
				<div class="sa-list-wrapper sa-d-flex sa-align-items-center">

					<?php $this->render_media( $item, $settings ); ?>

					<div class="sa-content-wrapper">
						<?php
						$this->render_title( $item, $settings );
						$this->render_text( $item, $settings );
						?>
					</div>

				</div>
			</<?php echo esc_attr( $tag ); ?>>
		</li>
		<?php
	}

	/**
	 * Dispatches to the right data source and returns a normalized item array.
	 * render() calls only this — it never touches the source directly.
	 */
	protected function collect_items( array $settings ): array {
		switch ( $settings['content_type'] ?? 'repeater' ) {
			case 'posts':
				return $this->collect_post_items( $settings );
			case 'acf':
				return $this->collect_acf_items( $settings );
			default:
				return $settings['list'] ?? [];
		}
	}

	/**
	 * Runs a WP_Query using the query-builder settings and maps each post to the
	 * normalized item shape. Featured image sets media_type='image' when present,
	 * which render_media() respects along with the global show_image toggle.
	 */
	protected function collect_post_items( array $settings ): array {
		$this->query_posts( (int) ( $settings['posts_per_page'] ?? 6 ) );
		$query = $this->get_query();

		if ( ! $query->have_posts() ) {
			return [];
		}

		$items = [];

		while ( $query->have_posts() ) {
			$query->the_post();
			$post_id  = get_the_ID();
			$thumb_id = (int) get_post_thumbnail_id( $post_id );
			$image    = $thumb_id ? [
				'id'  => $thumb_id,
				'url' => (string) wp_get_attachment_image_url( $thumb_id, 'medium' ),
			] : [];

			$items[] = $this->normalize_item(
				(string) $post_id,
				get_the_title(),
				wp_strip_all_tags( get_the_excerpt() ),
				get_permalink(),
				$image
			);
		}

		wp_reset_postdata();

		return $items;
	}

	/**
	 * Reads a named ACF repeater field on the current post and maps each row's
	 * title/content sub-fields to the normalized item shape.
	 */
	protected function collect_acf_items( array $settings ): array {
		if ( ! function_exists( 'get_field' ) ) {
			return [];
		}

		$repeater_field = sanitize_text_field( $settings['acf_repeater_field'] ?? '' );
		$post_id        = $this->resolve_post_id();

		if ( empty( $repeater_field ) || ! $post_id ) {
			return [];
		}

		$rows = get_field( $repeater_field, $post_id );
		if ( empty( $rows ) || ! is_array( $rows ) ) {
			return [];
		}

		$title_key   = sanitize_text_field( $settings['acf_title_field'] ?? '' );
		$content_key = sanitize_text_field( $settings['acf_content_field'] ?? '' );
		$items       = [];

		foreach ( $rows as $index => $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$items[] = $this->normalize_item(
				(string) $index,
				$row[ $title_key ] ?? '',
				$row[ $content_key ] ?? ''
			);
		}

		return $items;
	}

	/**
	 * Returns the canonical item shape consumed by render_item() and its sub-methods.
	 * Both dynamic sources call this so the array structure is defined in one place.
	 *
	 * @param string $id    Unique key — post ID for posts source, row index for ACF.
	 * @param string $title Raw unescaped title. render_title() applies esc_html() at output.
	 * @param string $text  Raw text / excerpt. render_text() applies wp_trim_words() then wp_kses_post() at output.
	 * @param string $url   Card link URL. Empty string renders the card as <div>.
	 * @param array  $image Associative ['id' => int, 'url' => string]. Empty = no media.
	 */
	private function normalize_item( string $id, string $title, string $text, string $url = '', array $image = [] ): array {
		return [
			'_id'         => $id,
			'list_title'  => $title,
			'list_text'   => $text,
			'list_link' => [
				'url'         => $url,
				'is_external' => false,
				'nofollow'    => false,
			],
			'media_type'  => empty( $image ) ? '' : 'image',
			'list_icon'   => [
				'value'   => '',
				'library' => '',
			],
			'list_image'  => $image ?: [
				'id'  => 0,
				'url' => '',
			],
			'list_number' => '',
		];
	}

	/**
	 * Returns the authoritative post ID for ACF field lookup.
	 * Elementor preview passes the real post ID via ?preview_id — get_the_ID() can
	 * return a revision ID in that context. Outside preview, revision parents are
	 * resolved so ACF fields are always read from the published post.
	 */
	private function resolve_post_id(): int {
		if ( isset( $_GET['preview_id'], $_GET['preview'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return absint( $_GET['preview_id'] ); // phpcs:ignore WordPress.Security.NonceVerification
		}
		$post_id   = (int) get_the_ID();
		$parent_id = (int) wp_is_post_revision( $post_id );
		return $parent_id ?: $post_id;
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$items    = $this->collect_items( $settings );
		?>
		<div class="sa-tidy-list">
			<ul class="sa-list-ul sa-d-flex sa-flex-wrap">
				<?php foreach ( $items as $item ) : ?>
					<?php $this->render_item( $item, $settings ); ?>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php
	}
}

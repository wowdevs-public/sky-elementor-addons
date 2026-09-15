<?php

namespace Sky_Addons\Modules\AdvancedAccordion\Widgets;

use Elementor\Utils;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Repeater;
use Elementor\Icons_Manager;
use Elementor\Widget_Base;
use Sky_Addons\Includes\Controls\GroupQuery\Group_Control;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Advanced_Accordion extends Widget_Base {

	use Group_Control;

	private $_query = null;

	/**
	 * Page-level FAQ state. All three are shared by every accordion on the request, which is
	 * the point: schema.org allows one FAQPage per URL, so the questions have to be pooled
	 * across widgets rather than each widget describing itself.
	 *
	 * @var array Question => [ question, answer ], keyed so a repeated question collapses.
	 */
	protected static $faq_entries = [];

	/** @var bool Whether some accordion already owns the inline FAQPage scope. */
	protected static $faq_scope_claimed = false;

	/** @var bool Whether the wp_footer emitter is hooked. */
	protected static $faq_footer_hooked = false;

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
		return 'sky-advanced-accordion';
	}

	public function get_title() {
		return esc_html__( 'Advanced Accordion', 'sky-elementor-addons' );
	}

	public function get_icon() {
		return 'sky-icon-advanced-accordion';
	}

	public function get_categories() {
		return [ 'sky-elementor-addons' ];
	}

	public function get_keywords() {
		return [ 'sky', 'advanced', 'accordion', 'collapse' ];
	}

	public function get_style_depends() {
		if ( sky_addons_editor_mode() ) {
			return [ 'wowdevs-accordion', 'elementor-icons-fa-solid', 'sky-addons-styles' ];
		}

		return [ 'wowdevs-accordion', 'elementor-icons-fa-solid' ];
	}

	public function get_script_depends() {
		if ( sky_addons_editor_mode() ) {
			return [ 'wowdevs-accordion', 'sky-addons-scripts' ];
		}

		return [ 'wowdevs-accordion', 'sa-advanced-accordion' ];
	}

	public function get_custom_help_url() {
		return 'https://skyaddons.com/docs/sky-addons/widgets/advanced-accordion/';
	}

	public function has_widget_inner_wrapper(): bool {
		return ! \Elementor\Plugin::$instance->experiments->is_feature_active( 'e_optimized_markup' );
	}

	protected function register_controls() {

		$this->start_controls_section(
			'section_ad_acc',
			[
				'label' => esc_html__( 'Layout', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'content_type',
			[
				'label'   => esc_html__( 'Content Type', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'repeater',
				'options' => [
					'repeater' => esc_html__( 'Default (Repeater)', 'sky-elementor-addons' ),
					'posts'    => esc_html__( 'Dynamic Posts', 'sky-elementor-addons' ),
					'acf'      => esc_html__( 'ACF Fields', 'sky-elementor-addons' ),
				],
			]
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'title',
			[
				'label'       => esc_html__( 'Title', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 2,
				'default'     => esc_html__( 'Accordion Title', 'sky-elementor-addons' ),
				'label_block' => true,
			]
		);

		$repeater->add_control(
			'title_icon',
			[
				'label'       => esc_html__( 'Title Icon', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::ICONS,
				'skin'        => 'inline',
				'label_block' => false,
				'separator'   => 'before',
			]
		);

		$repeater->add_control(
			'content_source',
			[
				'label'   => esc_html__( 'Choose Source', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'custom',
				'options' => [
					'custom'    => esc_html__( 'Custom Content', 'sky-elementor-addons' ),
					'elementor' => esc_html__( 'Elementor Template', 'sky-elementor-addons' ),
					'anywhere'  => esc_html__( 'AE Template', 'sky-elementor-addons' ),
				],
			]
		);

		$repeater->add_control(
			'custom_content',
			[
				'label'       => esc_html__( 'Custom Content', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::WYSIWYG,
				'default'     => esc_html__( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.', 'sky-elementor-addons' ),
				'placeholder' => esc_html__( 'Type your description here', 'sky-elementor-addons' ),
				'dynamic'     => [ 'active' => true ],
				'condition'   => [ 'content_source' => 'custom' ],
			]
		);

		$repeater->add_control(
			'template_id',
			[
				'label'       => esc_html__( 'Select Template', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => '0',
				'options'     => sky_addons_elementor_template_settings(),
				'label_block' => 'true',
				'condition'   => [ 'content_source' => 'elementor' ],
			]
		);

		$repeater->add_control(
			'anywhere_id',
			[
				'label'       => esc_html__( 'Select Template', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => '0',
				'options'     => sky_addons_anywhere_template_settings(),
				'label_block' => 'true',
				'condition'   => [ 'content_source' => 'anywhere' ],
			]
		);

		$this->add_control(
			'acc_list',
			[
				'label'       => '',
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => [
					[
						'title' => esc_html__( 'Add Your Title Here #1', 'sky-elementor-addons' ),
					],
					[
						'title' => esc_html__( 'Add Your Title Here #2', 'sky-elementor-addons' ),
					],
					[
						'title' => esc_html__( 'Add Your Title Here #3', 'sky-elementor-addons' ),
					],
					[
						'title' => esc_html__( 'Add Your Title Here #4', 'sky-elementor-addons' ),
					],
				],
				'title_field' => '{{{ title }}}',
				'condition'   => [ 'content_type' => 'repeater' ],
			]
		);

		$this->add_responsive_control(
			'columns',
			[
				'label'          => esc_html__( 'Grid Columns', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'           => Controls_Manager::SELECT,
				'default'        => '1',
				// Without this, mobile inherits the desktop value and a 3-column accordion
				// stays 3 columns at 390px — three ~130px tracks in a 354px column.
				'mobile_default' => '1',
				'options'        => [
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
				],
				'selectors'      => [
					// `1fr` is `minmax(auto, 1fr)`, so a track grows to its widest item's
					// min-content instead of being capped — one panel holding a wide table or
					// a swiper pushed the whole accordion past the column and scrolled the page
					// sideways. `minmax(0, 1fr)` lets the track shrink below that.
					'{{WRAPPER}} .sa-advanced-accordion' => 'display: grid; grid-template-columns: repeat({{VALUE}}, minmax(0, 1fr)); align-items: start;',
					'{{WRAPPER}} .sa-advanced-accordion .sa-ac-item' => 'margin-top: 0;',
				],
				'separator'      => 'before',
			]
		);

		$this->add_control(
			'title_tag',
			[
				'label'     => esc_html__( 'Title HTML Tag', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'h3',
				'options'   => sky_addons_title_tags(),
				'separator' => 'before',
			]
		);

		$this->add_control(
			'show_title_icon',
			[
				'label'     => esc_html__( 'Show Title Icon?', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SWITCHER,
				'condition' => [ 'content_type' => 'repeater' ],
			]
		);

		$this->add_control(
			'icon_align',
			[
				'label'        => esc_html__( 'Icon Alignment', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::CHOOSE,
				'options'      => [
					'left'  => [
						'title' => esc_html__( 'Start', 'sky-elementor-addons' ),
						'icon'  => 'eicon-h-align-left',
					],
					'right' => [
						'title' => esc_html__( 'End', 'sky-elementor-addons' ),
						'icon'  => 'eicon-h-align-right',
					],
				],
				'default'      => is_rtl() ? 'left' : 'right',
				'toggle'       => false,
				'separator'    => 'before',
				'prefix_class' => 'sa-icon-direction-',
			]
		);

		$this->add_control(
			'selected_icon',
			[
				'label'       => esc_html__( 'Icon', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::ICONS,
				'default'     => [
					'value'   => 'fas fa-chevron-down',
					'library' => 'fa-solid',
				],
				'recommended' => [
					'fa-solid'   => [
						'chevron-down',
						'angle-down',
						'angle-double-down',
						'caret-down',
						'caret-square-down',
						'plus',
					],
					'fa-regular' => [
						'caret-square-down',
					],
				],
				'skin'        => 'inline',
				'label_block' => false,
			]
		);

		$this->add_control(
			'selected_active_icon',
			[
				'label'       => esc_html__( 'Active Icon', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::ICONS,
				'default'     => [
					'value'   => 'fas fa-chevron-up',
					'library' => 'fa-solid',
				],
				'recommended' => [
					'fa-solid'   => [
						'chevron-up',
						'angle-up',
						'angle-double-up',
						'caret-up',
						'caret-square-up',
						'minus',
					],
					'fa-regular' => [
						'caret-square-up',
					],
				],
				'skin'        => 'inline',
				'label_block' => false,
				'condition'   => [
					'selected_icon[value]!' => '',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_post_query_builder',
			[
				'label' => esc_html__( 'Query', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
				'condition' => [
					'content_type' => 'posts',
				],
			]
		);

		$this->register_query_builder_controls();

		$this->end_controls_section();

		$this->start_controls_section(
			'section_acf_settings',
			[
				'label' => esc_html__( 'ACF Settings', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
				'condition' => [
					'content_type' => 'acf',
				],
			]
		);

		$this->add_control(
			'acf_repeater_field',
			[
				'label'       => esc_html__( 'ACF Repeater Field', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'        => Controls_Manager::TEXT,
				'dynamic'     => [ 'active' => true ],
				'label_block' => true,
				'placeholder' => esc_html__( 'Enter repeater field name', 'sky-elementor-addons' ),
				'description' => esc_html__( 'Enter the name of the ACF repeater field.', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'acf_title_field',
			[
				'label'       => esc_html__( 'Title Field Mapping', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'        => Controls_Manager::TEXT,
				'dynamic'     => [ 'active' => true ],
				'label_block' => true,
				'placeholder' => esc_html__( 'Enter sub-field name for title', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'acf_content_field',
			[
				'label'       => esc_html__( 'Content Field Mapping', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'        => Controls_Manager::TEXT,
				'dynamic'     => [ 'active' => true ],
				'label_block' => true,
				'placeholder' => esc_html__( 'Enter sub-field name for content', 'sky-elementor-addons' ),
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_acc_settings',
			[
				'label' => esc_html__( 'Settings', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'acc_duration',
			[
				'label' => esc_html__( 'Duration', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 400,
						'max' => 1000,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 400,
				],
			]
		);

		$this->add_control(
			'acc_collapse',
			[
				'label'   => esc_html__( 'Collapse', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'acc_show_multiple',
			[
				'label' => esc_html__( 'Show Multiple?', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::SWITCHER,
			]
		);

		$this->add_control(
			'acc_open_default',
			[
				'label'       => esc_html__( 'Open Item Default', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => esc_html__( '1 or 1, 2, 3', 'sky-elementor-addons' ),
				'dynamic'     => [ 'active' => true ],
				'condition'   => [ 'content_type' => 'repeater' ],
			]
		);

		$this->add_control(
			'faq_schema',
			[
				'label'       => esc_html__( 'FAQ Schema', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::SWITCHER,
				'separator'   => 'before',
				'description' => esc_html__( 'Describe the accordion as a Schema.org FAQPage so search engines and AI answer engines can read the questions and answers. Rows using an Elementor or AE template are skipped — their answer is a layout, not prose.', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'faq_schema_output',
			[
				'label'       => esc_html__( 'Schema Output', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'both',
				'options'     => [
					'both'      => esc_html__( 'Both', 'sky-elementor-addons' ),
					'jsonld'    => esc_html__( 'JSON-LD', 'sky-elementor-addons' ),
					'microdata' => esc_html__( 'Microdata', 'sky-elementor-addons' ),
				],
				'description' => esc_html__( 'JSON-LD is written once per page and merges every accordion on it — this is the format Google prefers and the one most AI answer engines read. Microdata is inline in the markup; a page can only hold one FAQPage, so it is carried by the first accordion on the page.', 'sky-elementor-addons' ),
				'condition'   => [ 'faq_schema' => 'yes' ],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_item_style',
			[
				'label' => esc_html__( 'Item', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'item_spacing',
			[
				'label'      => esc_html__( 'Item Spacing', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem' ],
				'default' => [
					'unit' => 'px',
					'size' => 10,
				],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
					'em' => [
						'min'  => 0,
						'max'  => 10,
						'step' => 0.1,
					],
					'rem' => [
						'min'  => 0,
						'max'  => 10,
						'step' => 0.1,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-advanced-accordion .sa-ac-item' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'column_gap',
			[
				'label'      => esc_html__( 'Column Gap', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
					'em' => [
						'min'  => 0,
						'max'  => 10,
						'step' => 0.1,
					],
					'rem' => [
						'min'  => 0,
						'max'  => 10,
						'step' => 0.1,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .sa-advanced-accordion' => 'column-gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'item_border',
				'label'    => esc_html__( 'Border', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-ac-item',
			]
		);

		$this->add_responsive_control(
			'item_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-ac-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'item_background',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .sa-ac-item',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'item_box_shadow',
				'label'    => esc_html__( 'Box Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-ac-item',
			]
		);

		$this->start_controls_tabs( 'item_tabs' );

		$this->start_controls_tab(
			'item_tab_hover',
			[
				'label' => esc_html__( 'Hover', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'item_border_color_hover',
			[
				'label' => esc_html__( 'Border Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-ac-item:hover' => 'border-color: {{VALUE}};',
				],
				'condition' => [
					'item_border_border!' => '',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'item_box_shadow_hover',
				'label'    => esc_html__( 'Box Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-ac-item:hover',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'item_tab_active',
			[
				'label' => esc_html__( 'Active', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'item_border_color_active',
			[
				'label' => esc_html__( 'Border Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-ac-item.is-active' => 'border-color: {{VALUE}};',
				],
				'condition' => [
					'item_border_border!' => '',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'item_box_shadow_active',
				'label'    => esc_html__( 'Box Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-ac-item.is-active',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		$this->start_controls_section(
			'section_title_style',
			[
				'label' => esc_html__( 'Title', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'title_typography',
				'label'    => esc_html__( 'Typography', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa--title:not(.sa-ac-panel .sa--title)',
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'title_text_shadow',
				'label'    => esc_html__( 'Text Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa--title:not(.sa-ac-panel .sa--title)',
			]
		);

		$this->add_responsive_control(
			'title_padding',
			[
				'label'      => esc_html__( 'Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-ac-item .sa-ac-trigger:not(.sa-ac-panel .sa-ac-trigger)' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'title_border',
				'label'    => esc_html__( 'Border', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-ac-item .sa-ac-trigger:not(.sa-ac-panel .sa-ac-trigger)',
			]
		);

		$this->add_responsive_control(
			'title_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-ac-item .sa-ac-trigger:not(.sa-ac-panel .sa-ac-trigger)' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->start_controls_tabs( 'title_tabs' );

		$this->start_controls_tab(
			'title_tab_normal',
			[
				'label' => esc_html__( 'Normal', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'title_color',
			[
				'label' => esc_html__( 'Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa--title:not(.sa-ac-panel .sa--title)' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'title_bg',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .sa-ac-item .sa-ac-trigger:not(.sa-ac-panel .sa-ac-trigger)',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'title_box_shadow',
				'label'    => esc_html__( 'Box Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-ac-item .sa-ac-trigger:not(.sa-ac-panel .sa-ac-trigger)',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'title_tab_hover',
			[
				'label' => esc_html__( 'Hover', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'title_color_hover',
			[
				'label' => esc_html__( 'Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-ac-trigger:hover .sa--title:not(.sa-ac-panel .sa--title)' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'title_border_color_hover',
			[
				'label' => esc_html__( 'Border Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-ac-trigger:hover' => 'border-color: {{VALUE}};',
				],
				'condition' => [
					'title_border_border!' => '',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'title_bg_hover',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .sa-ac-item .sa-ac-trigger:hover:not(.sa-ac-panel .sa-ac-trigger)',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'title_box_shadow_hover',
				'label'    => esc_html__( 'Box Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-ac-item .sa-ac-trigger:hover',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'title_tab_active',
			[
				'label' => esc_html__( 'Active', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'title_color_active',
			[
				'label' => esc_html__( 'Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-ac-item.is-active .sa-ac-trigger .sa--title:not(.sa-ac-panel .sa--title)' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'title_border_color_active',
			[
				'label' => esc_html__( 'Border Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-ac-item.is-active .sa-ac-trigger:not(.sa-ac-panel .sa-ac-trigger)' => 'border-color: {{VALUE}};',
				],
				'condition' => [
					'title_border_border!' => '',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'title_bg_active',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .sa-ac-item.is-active .sa-ac-trigger:not(.sa-ac-panel .sa-ac-trigger)',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'title_box_shadow_active',
				'label'    => esc_html__( 'Box Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-ac-item.is-active .sa-ac-trigger:not(.sa-ac-panel .sa-ac-trigger)',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		$this->start_controls_section(
			'section_title_icon_style',
			[
				'label' => esc_html__( 'Title Icon', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
				'conditions' => [
					'relation' => 'and',
					'terms'    => [
						[
							'name'     => 'content_type',
							'operator' => '===',
							'value'    => 'repeater',
						],
						[
							'name'     => 'show_title_icon',
							'operator' => '===',
							'value'    => 'yes',
						],
					],
				],
			]
		);

		$this->add_responsive_control(
			'title_icon_size',
			[
				'label'      => esc_html__( 'Size', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range'      => [
					'px' => [
						'min' => 6,
						'max' => 40,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-title-icon.sa-icon-wrap' => 'font-size: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'title_icon_spacing',
			[
				'label'      => esc_html__( 'Spacing', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', '%' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-ac-trigger' => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'title_icon_padding',
			[
				'label'      => esc_html__( 'Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-title-icon.sa-icon-wrap' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'title_icon_border',
				'label'    => esc_html__( 'Border', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-title-icon.sa-icon-wrap',
			]
		);

		$this->add_responsive_control(
			'title_icon_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-title-icon.sa-icon-wrap' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->start_controls_tabs( 'title_icon_tabs' );

		$this->start_controls_tab(
			'title_icon_tab_normal',
			[
				'label' => esc_html__( 'Normal', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'title_icon_color',
			[
				'label' => esc_html__( 'Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-title-icon.sa-icon-wrap' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'title_icon_background',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .sa-title-icon.sa-icon-wrap',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'title_icon_box_shadow',
				'label'    => esc_html__( 'Box Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-title-icon.sa-icon-wrap',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'title_icon_tab_hover',
			[
				'label' => esc_html__( 'Hover', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'title_icon_color_hover',
			[
				'label' => esc_html__( 'Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-ac-item:hover .sa-title-icon.sa-icon-wrap' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'title_icon_border_color_hover',
			[
				'label' => esc_html__( 'Border Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-ac-item:hover .sa-title-icon.sa-icon-wrap' => 'border-color: {{VALUE}};',
				],
				'condition' => [
					'title_icon_border_border!' => '',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'title_icon_bg_hover',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .sa-ac-item:hover .sa-title-icon.sa-icon-wrap',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'title_icon_box_shadow_hover',
				'label'    => esc_html__( 'Box Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-ac-item:hover .sa-title-icon.sa-icon-wrap',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'title_icon_tab_active',
			[
				'label' => esc_html__( 'Active', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'title_icon_color_active',
			[
				'label' => esc_html__( 'Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-ac-item.is-active .sa-title-icon.sa-icon-wrap' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'title_icon_border_color_active',
			[
				'label' => esc_html__( 'Border Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-ac-item.is-active .sa-title-icon.sa-icon-wrap' => 'border-color: {{VALUE}};',
				],
				'condition' => [
					'title_icon_border_border!' => '',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'title_icon_bg_active',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .sa-ac-item.is-active .sa-title-icon.sa-icon-wrap',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'title_icon_box_shadow_active',
				'label'    => esc_html__( 'Box Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-ac-item.is-active .sa-title-icon.sa-icon-wrap',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		$this->start_controls_section(
			'section_icon_style',
			[
				'label' => esc_html__( 'Icon', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'icon_size',
			[
				'label'      => esc_html__( 'Size', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range'      => [
					'px' => [
						'min' => 6,
						'max' => 40,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-trigger-icon .sa-icon-wrap' => 'font-size: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'icon_spacing',
			[
				'label' => esc_html__( 'Spacing', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} ' => '--sa-acc-icon-spacing: {{SIZE}}px;',
				],
			]
		);

		$this->add_responsive_control(
			'icon_padding',
			[
				'label'      => esc_html__( 'Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-trigger-icon.sa-icon-wrapper' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'icon_border',
				'label'    => esc_html__( 'Border', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-trigger-icon.sa-icon-wrapper',
			]
		);

		$this->add_responsive_control(
			'icon_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-trigger-icon.sa-icon-wrapper' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->start_controls_tabs( 'icon_tabs' );

		$this->start_controls_tab(
			'icon_tab_normal',
			[
				'label' => esc_html__( 'Normal', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'icon_color',
			[
				'label' => esc_html__( 'Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-trigger-icon.sa-icon-wrapper' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'icon_background',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .sa-trigger-icon.sa-icon-wrapper',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'icon_box_shadow',
				'label'    => esc_html__( 'Box Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-trigger-icon.sa-icon-wrapper',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'icon_tab_hover',
			[
				'label' => esc_html__( 'Hover', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'icon_color_hover',
			[
				'label' => esc_html__( 'Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-ac-item:hover .sa-trigger-icon.sa-icon-wrapper' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'icon_border_color_hover',
			[
				'label' => esc_html__( 'Border Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-ac-item:hover .sa-trigger-icon.sa-icon-wrapper' => 'border-color: {{VALUE}};',
				],
				'condition' => [
					'icon_border_border!' => '',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'icon_bg_hover',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .sa-ac-item:hover .sa-trigger-icon.sa-icon-wrapper',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'icon_box_shadow_hover',
				'label'    => esc_html__( 'Box Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-ac-item:hover .sa-trigger-icon.sa-icon-wrapper',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'icon_tab_active',
			[
				'label' => esc_html__( 'Active', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'icon_color_active',
			[
				'label' => esc_html__( 'Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-ac-item.is-active .sa-trigger-icon.sa-icon-wrapper' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'icon_border_color_active',
			[
				'label' => esc_html__( 'Border Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-ac-item.is-active .sa-trigger-icon.sa-icon-wrapper' => 'border-color: {{VALUE}};',
				],
				'condition' => [
					'icon_border_border!' => '',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'icon_bg_active',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .sa-ac-item.is-active .sa-trigger-icon.sa-icon-wrapper',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'icon_box_shadow_active',
				'label'    => esc_html__( 'Box Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-ac-item.is-active .sa-trigger-icon.sa-icon-wrapper',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		$this->start_controls_section(
			'section_content_style',
			[
				'label' => esc_html__( 'Content', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
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
					'{{WRAPPER}} .sa-ac-content' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'content_color',
			[
				'label' => esc_html__( 'Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-ac-content' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'content_background',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient', 'video' ],
				'selector' => '{{WRAPPER}} .sa-ac-content',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'content_typography',
				'label'    => esc_html__( 'Typography', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-ac-content',
			]
		);

		$this->add_responsive_control(
			'content_padding',
			[
				'label'      => esc_html__( 'Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-ac-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'content_border',
				'label'    => esc_html__( 'Border', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-ac-panel',
			]
		);

		$this->add_responsive_control(
			'content_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-ac-panel' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'content_box_shadow',
				'label'    => esc_html__( 'Box Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-ac-panel',
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$columns  = max( 1, (int) ( $settings['columns'] ?? 1 ) );

		$acc_open      = explode( ',', $settings['acc_open_default'] ?? '' );
		$open_init_arr = array_map( fn( $v ) => (int) $v - 1, $acc_open );

		$this->add_render_attribute(
			[
				'advanced-accordion' => [
					'id'    => 'sa-advanced-acc-' . $this->get_id(),
					'class' => 'sa-advanced-accordion',
					'data-settings' => [
						wp_json_encode( [
							'id'           => 'sa-advanced-acc-' . $this->get_id(),
							'duration'     => ! empty( $settings['acc_duration']['size'] ) ? (int) $settings['acc_duration']['size'] : 400,
							'collapse'     => 'yes' === $settings['acc_collapse'],
							'showMultiple' => 'yes' === $settings['acc_show_multiple'],
							'openOnInit'   => ! empty( $settings['acc_open_default'] ) ? $open_init_arr : [],
						] ),
					],
				],
			]
		);

		$faq_schema = isset( $settings['faq_schema'] ) && 'yes' === $settings['faq_schema'];
		$faq_output = $settings['faq_schema_output'] ?? 'both';
		$faq_micro  = $faq_schema && in_array( $faq_output, [ 'microdata', 'both' ], true );
		$faq_json   = $faq_schema && in_array( $faq_output, [ 'jsonld', 'both' ], true );

		// A page is one FAQPage. Every accordion used to open a scope of its own, so two of
		// them — a demo section plus a global block, say — put two FAQPage scopes on one URL
		// and a parser has to guess which is the page's FAQ; usually it takes neither. The
		// first accordion on the page claims the scope, and the rest emit no microdata at
		// all: their Question items would have no FAQPage to belong to. Their rows still
		// reach the JSON-LD document, which merges the whole page into one FAQ.
		if ( $faq_micro && ! self::$faq_scope_claimed ) {
			self::$faq_scope_claimed = true;
			$this->add_render_attribute( 'advanced-accordion', [
				'itemscope' => 'itemscope',
				'itemtype'  => 'https://schema.org/FAQPage',
			] );
		} else {
			$faq_micro = false;
		}

		if ( 'posts' === $settings['content_type'] ) {
			$items     = $this->collect_post_items( $settings );
			$empty_msg = esc_html__( 'No posts found.', 'sky-elementor-addons' );
		} elseif ( 'acf' === $settings['content_type'] ) {
			$items     = $this->collect_acf_items( $settings );
			$empty_msg = esc_html__( 'No ACF rows found.', 'sky-elementor-addons' );
		} else {
			$items     = $settings['acc_list'] ?? [];
			$empty_msg = '';
		}

		if ( $faq_json ) {
			$this->collect_faq_schema( $items );
		}

		?>
		<div <?php $this->print_render_attribute_string( 'advanced-accordion' ); ?>>
			<?php if ( empty( $items ) ) : ?>
				<?php if ( $empty_msg ) : ?>
					<p class="sa-no-posts"><?php echo esc_html( $empty_msg ); ?></p>
				<?php endif; ?>
			<?php elseif ( $columns > 1 ) : ?>
				<?php
				$chunks = array_chunk( $items, (int) ceil( count( $items ) / $columns ) );
				foreach ( $chunks as $chunk ) :
					?>
					<div class="sa-acc-col">
						<?php foreach ( $chunk as $item ) : ?>
							<?php $this->render_item( $item, $settings, $faq_micro ); ?>
						<?php endforeach; ?>
					</div>
					<?php
				endforeach;
				?>
			<?php else : ?>
				<?php foreach ( $items as $item ) : ?>
					<?php $this->render_item( $item, $settings, $faq_micro ); ?>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
		<?php
	}

	protected function collect_post_items( $settings ) {
		$posts_per_page = isset( $settings['posts_per_page'] ) ? (int) $settings['posts_per_page'] : 6;
		$this->query_posts( $posts_per_page );
		$query = $this->get_query();
		$items = [];

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$items[] = [
					'title'          => get_the_title(),
					'custom_content' => wpautop( get_the_content() ),
					'content_source' => 'custom',
					'title_icon'     => [],
					'_raw_content'   => true,
				];
			}
			wp_reset_postdata();
		}

		return $items;
	}

	protected function collect_acf_items( $settings ) {
		if ( ! function_exists( 'get_field' ) ) {
			return [];
		}

		$repeater_field = sanitize_text_field( $settings['acf_repeater_field'] ?? '' );
		if ( empty( $repeater_field ) ) {
			return [];
		}

		// In Elementor/WordPress preview the preview_id param is the authoritative post ID.
		// get_the_ID() may return a revision or 0 in preview context, so use preview_id directly.
		if ( isset( $_GET['preview_id'] ) && isset( $_GET['preview'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$post_id = absint( $_GET['preview_id'] ); // phpcs:ignore WordPress.Security.NonceVerification
		} else {
			$post_id   = get_the_ID();
			$parent_id = wp_is_post_revision( $post_id );
			if ( $parent_id ) {
				$post_id = $parent_id;
			}
		}

		if ( ! $post_id ) {
			return [];
		}

		$rows = get_field( $repeater_field, $post_id );
		if ( empty( $rows ) || ! is_array( $rows ) ) {
			return [];
		}

		$title_key   = sanitize_text_field( $settings['acf_title_field'] ?? '' );
		$content_key = sanitize_text_field( $settings['acf_content_field'] ?? '' );
		$items       = [];

		foreach ( $rows as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$items[] = [
				'title'          => $row[ $title_key ] ?? '',
				'custom_content' => $row[ $content_key ] ?? '',
				'content_source' => 'custom',
				'title_icon'     => [],
				'_raw_content'   => true,
			];
		}

		return $items;
	}

	/**
	 * Pool this widget's rows into the page's FAQ document.
	 *
	 * @param array $items Rows as render() built them.
	 */
	protected function collect_faq_schema( $items ) {
		foreach ( $items as $item ) {
			// Template rows render a whole layout — containers, widgets, sometimes their own
			// style and script — so the "answer" would be markup rather than prose. Nothing
			// on the page shows it is wrong, which is why it has to be skipped here.
			if ( 'custom' !== ( $item['content_source'] ?? 'custom' ) ) {
				continue;
			}

			$question = $this->faq_plain_text( $item['title'] ?? '' );
			$answer   = $this->faq_plain_text( $item['custom_content'] ?? '' );

			if ( '' === $question || '' === $answer ) {
				continue;
			}

			// Two accordions can carry the same question — a demo section and a global block,
			// the same FAQ reused. One question, one entry, and the first answer on the page
			// wins: assigning unconditionally would let a later duplicate silently replace an
			// earlier, fuller answer.
			$key = md5( $question );

			if ( ! isset( self::$faq_entries[ $key ] ) ) {
				self::$faq_entries[ $key ] = [
					'question' => $question,
					'answer'   => $answer,
				];
			}
		}

		if ( ! self::$faq_footer_hooked && ! empty( self::$faq_entries ) ) {
			self::$faq_footer_hooked = true;
			add_action( 'wp_footer', [ __CLASS__, 'print_faq_schema' ], 99 );
		}
	}

	/**
	 * Reduce stored HTML to the prose a schema consumer expects.
	 *
	 * @param string $html Raw row content.
	 * @return string
	 */
	protected function faq_plain_text( $html ) {
		// Drop script/style bodies first — wp_strip_all_tags would keep their contents as
		// text once the tags around them are gone.
		$text = preg_replace( '#<(script|style)\b[^>]*>.*?</\1>#is', ' ', (string) $html );
		// A space per tag, not an empty string: "<p>one</p><p>two</p>" must not read "onetwo".
		$text = preg_replace( '/<[^>]*>/', ' ', $text );
		$text = html_entity_decode( (string) $text, ENT_QUOTES, 'UTF-8' );

		// The /u pass returns null on malformed UTF-8; cast so trim() never gets null.
		return trim( (string) preg_replace( '/\s+/u', ' ', $text ) );
	}

	/**
	 * Write the page's single FAQPage document.
	 *
	 * JSON-LD rather than inline microdata because it is the format Google documents as
	 * preferred, and because most AI answer engines read JSON-LD first — several read
	 * nothing else.
	 */
	public static function print_faq_schema() {
		if ( empty( self::$faq_entries ) ) {
			return;
		}

		$main_entity = [];

		foreach ( self::$faq_entries as $entry ) {
			$main_entity[] = [
				'@type' => 'Question',
				'name'  => $entry['question'],
				'acceptedAnswer' => [
					'@type' => 'Answer',
					'text'  => $entry['answer'],
				],
			];
		}

		// Emitted once; clear so a second wp_footer pass cannot duplicate the document.
		self::$faq_entries = [];

		// The HEX flags matter here rather than being defensive noise: an answer holding a
		// literal "<" or "&" would otherwise be written raw inside a <script> block.
		$json = wp_json_encode(
			[
				'@context'   => 'https://schema.org',
				'@type'      => 'FAQPage',
				'mainEntity' => $main_entity,
			],
			JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
		);

		if ( ! $json ) {
			return;
		}

		echo '<script type="application/ld+json">' . $json . '</script>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_json_encode with the HEX flags is the escaping.
	}

	protected function render_item( $item, $settings, $faq_schema ) {
		// itemprop="text" used to wrap whatever the row rendered, so on a template row the
		// marked-up "answer" was an entire layout instead of prose — invisible on the page
		// and wrong to every consumer. A template row now carries no microdata at all, the
		// same rows the JSON-LD document skips.
		$faq_schema = $faq_schema && 'custom' === ( $item['content_source'] ?? 'custom' );
		?>
		<?php if ( $faq_schema ) : ?>
		<div class="sa-ac-item" itemscope itemprop="mainEntity" itemtype="https://schema.org/Question">
		<?php else : ?>
		<div class="sa-ac-item">
		<?php endif; ?>
			<div class="sa-ac-trigger">
				<div class="sa-title-wrapper">
					<?php if ( 'yes' === $settings['show_title_icon'] && ! empty( $item['title_icon']['value'] ) ) : ?>
						<div class="sa-title-icon sa-icon-wrap sa-me-2">
							<?php Icons_Manager::render_icon( $item['title_icon'] ); ?>
						</div>
					<?php endif; ?>
					<?php
					printf(
						'<%1$s class="sa--title sa--text-title sa-ac-title sa-m-0 sa-p-0"%3$s> %2$s </%1$s>',
						esc_attr( Utils::validate_html_tag( $settings['title_tag'] ) ),
						esc_html( $item['title'] ),
						$faq_schema ? ' itemprop="name"' : ''
					);
					?>
				</div>
				<span class="sa-trigger-icon sa-icon-wrapper sa-ac-icon-<?php echo esc_attr( $settings['icon_align'] ); ?>">
					<span class="sa-ac-icon-closed sa-icon-wrap">
						<?php
						if ( ! empty( $settings['selected_icon']['value'] ) ) {
							Icons_Manager::render_icon( $settings['selected_icon'] );
						}
						?>
					</span>
					<span class="sa-ac-icon-opened sa-icon-wrap">
						<?php
						if ( ! empty( $settings['selected_active_icon']['value'] ) ) {
							Icons_Manager::render_icon( $settings['selected_active_icon'] );
						}
						?>
					</span>
				</span>
			</div>
			<?php if ( $faq_schema ) : ?>
			<div class="sa-ac-panel" itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
				<div class="sa-ac-content" itemprop="text">
			<?php else : ?>
			<div class="sa-ac-panel">
				<div class="sa-ac-content">
			<?php endif; ?>
					<?php
					if ( 'custom' === $item['content_source'] ) :
						if ( ! empty( $item['_raw_content'] ) ) :
							echo wp_kses_post( $item['custom_content'] );
						else :
							echo wp_kses_post( $this->parse_text_editor( $item['custom_content'] ) );
						endif;
					elseif ( 'elementor' === $item['content_source'] && ! empty( $item['template_id'] ) ) :
						sky_addons_display_el_tem_by_id( $item['template_id'] );
					elseif ( 'anywhere' === $item['content_source'] && ! empty( $item['anywhere_id'] ) ) :
						sky_addons_display_el_tem_by_id( $item['anywhere_id'] );
					endif;
					?>
				</div>
			</div>
		</div>
		<?php
	}
}

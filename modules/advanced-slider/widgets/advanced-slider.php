<?php

namespace Sky_Addons\Modules\AdvancedSlider\Widgets;

use Elementor\Utils;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Group_Control_Css_Filter;
use Elementor\Group_Control_Image_Size;
use Elementor\Icons_Manager;
use Elementor\Control_Media;
use Elementor\Group_Control_Text_Stroke;
use Elementor\Repeater;
use Elementor\Widget_Base;
use Sky_Addons\Sky_Addons_Plugin;
use Sky_Addons\Includes\Controls\GroupQuery\Group_Control;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Advanced_Slider extends Widget_Base {

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
		return 'sky-advanced-slider';
	}

	public function get_title() {
		return esc_html__( 'Advanced Slider', 'sky-elementor-addons' );
	}

	public function get_icon() {
		return 'sky-icon-advanced-slider';
	}

	public function get_categories() {
		return [ 'sky-elementor-addons' ];
	}

	public function get_keywords() {
		return [ 'sky', 'slider', 'advanced', 'image', 'photo', 'carousel' ];
	}

	public function get_style_depends() {
		if ( sky_addons_editor_mode() ) {
			return [ 'swiper', 'sky-addons-styles' ];
		}

		return [ 'swiper', 'sa-advanced-slider' ];
	}

	public function get_script_depends() {
		if ( sky_addons_editor_mode() ) {
			return [ 'swiper', 'sky-addons-scripts' ];
		}

		return [ 'swiper', 'sa-advanced-slider' ];
	}

	public function get_custom_help_url() {
		return 'https://skyaddons.com/docs/sky-addons/widgets/advanced-slider/';
	}

	public function has_widget_inner_wrapper(): bool {
		return ! \Elementor\Plugin::$instance->experiments->is_feature_active( 'e_optimized_markup' );
	}

	protected function register_controls() {

		$this->start_controls_section(
			'section_sliders',
			[
				'label' => esc_html__( 'Sliders', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$repeater = new Repeater();

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
			'sub_title',
			[
				'label'       => esc_html__( 'Sub Title', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 2,
				'label_block' => true,
				'condition'   => [ 'content_source' => 'custom' ],
			]
		);

		$repeater->add_control(
			'title',
			[
				'label'       => esc_html__( 'Title', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 2,
				'default'     => esc_html__( 'Slide Title', 'sky-elementor-addons' ),
				'label_block' => true,
				'condition'   => [ 'content_source' => 'custom' ],
			]
		);

		$repeater->add_control(
			'custom_text',
			[
				'label'       => esc_html__( 'Text', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::WYSIWYG,
				'default'     => esc_html__( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.', 'sky-elementor-addons' ),
				'placeholder' => esc_html__( 'Type your description here', 'sky-elementor-addons' ),
				'dynamic'     => [ 'active' => true ],
				'condition'   => [ 'content_source' => 'custom' ],
			]
		);

		$repeater->add_control(
			'slider_image',
			[
				'label'     => esc_html__( 'Image', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::MEDIA,
				'dynamic'   => [ 'active' => true ],
				'condition' => [ 'content_source' => 'custom' ],
			]
		);

		$repeater->add_control(
			'link',
			[
				'label'         => esc_html__( 'Link', 'sky-elementor-addons' ),
				'type'          => Controls_Manager::URL,
				'placeholder'   => esc_html__( 'https://your-link.com', 'sky-elementor-addons' ),
				'show_external' => true,
				'default'       => [
					'url'         => '',
					'is_external' => false,
					'nofollow'    => false,
				],
				'dynamic'       => [ 'active' => true ],
				'condition'     => [ 'content_source' => 'custom' ],
			]
		);

		$repeater->add_control(
			'slide_video_type',
			[
				'label'     => esc_html__( 'Background Video', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '',
				'options'   => [
					''           => esc_html__( 'None', 'sky-elementor-addons' ),
					'youtube'    => esc_html__( 'YouTube', 'sky-elementor-addons' ),
					'vimeo'      => esc_html__( 'Vimeo', 'sky-elementor-addons' ),
					'hosted'     => esc_html__( 'Self Hosted', 'sky-elementor-addons' ),
					'remote_url' => esc_html__( 'Remote URL', 'sky-elementor-addons' ),
				],
				'separator' => 'before',
				'condition' => [ 'content_source' => 'custom' ],
			]
		);

		$repeater->add_control(
			'slide_video_youtube',
			[
				'label'       => esc_html__( 'YouTube URL', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'placeholder' => 'https://www.youtube.com/watch?v=...',
				'condition'   => [
					'content_source'   => 'custom',
					'slide_video_type' => 'youtube',
				],
			]
		);

		$repeater->add_control(
			'slide_video_vimeo',
			[
				'label'       => esc_html__( 'Vimeo URL', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'placeholder' => 'https://vimeo.com/...',
				'condition'   => [
					'content_source'   => 'custom',
					'slide_video_type' => 'vimeo',
				],
			]
		);

		$repeater->add_control(
			'slide_video_hosted',
			[
				'label'      => esc_html__( 'Hosted Video', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::MEDIA,
				'media_type' => 'video',
				'condition'  => [
					'content_source'   => 'custom',
					'slide_video_type' => 'hosted',
				],
			]
		);

		$repeater->add_control(
			'slide_video_remote_url',
			[
				'label'       => esc_html__( 'Remote Video URL', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'placeholder' => 'https://example.com/video.mp4',
				'condition'   => [
					'content_source'   => 'custom',
					'slide_video_type' => 'remote_url',
				],
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

		$this->add_control(
			'slider_list',
			[
				'label'       => '',
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'condition'   => [ 'content_type' => 'repeater' ],
				'default'     => [
					[
						'sub_title'   => esc_html__( 'Welcome to our story', 'sky-elementor-addons' ),
						'title'       => esc_html__( 'Elevate Your Brand to New Heights', 'sky-elementor-addons' ),
						'custom_text' => esc_html__( 'We craft exceptional digital experiences that connect your brand with the people who matter most. Ready to make your mark?', 'sky-elementor-addons' ),
					],
					[
						'sub_title'   => esc_html__( 'Our expertise', 'sky-elementor-addons' ),
						'title'       => esc_html__( 'Design That Speaks for Itself', 'sky-elementor-addons' ),
						'custom_text' => esc_html__( 'Every pixel is intentional. Every interaction is thoughtful. Great design is not just what it looks like — it\'s how it works.', 'sky-elementor-addons' ),
					],
					[
						'sub_title'   => esc_html__( 'Start today', 'sky-elementor-addons' ),
						'title'       => esc_html__( 'Build Something Truly Extraordinary', 'sky-elementor-addons' ),
						'custom_text' => esc_html__( 'From concept to launch, we partner with ambitious teams who want to create products the world has never seen before.', 'sky-elementor-addons' ),
					],
				],
				'title_field' => '{{{ title }}}',
			]
		);

		$this->add_group_control(
			Group_Control_Image_Size::get_type(),
			[
				'name'      => 'thumbnail', // Usage: `{name}_size` and `{name}_custom_dimension`, in this case `image_size` and `image_custom_dimension`.
				'default'   => 'full',
				'separator' => 'none',
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
				'label'       => esc_html__( 'ACF Repeater Field', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'Enter repeater field name', 'sky-elementor-addons' ),
				'description' => esc_html__( 'Enter the name of the ACF repeater field.', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'acf_sub_title_field',
			[
				'label'       => esc_html__( 'Sub Title Field Mapping', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'Enter sub-field name for sub title', 'sky-elementor-addons' ),
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
			'acf_text_field',
			[
				'label'       => esc_html__( 'Text Field Mapping', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'Enter sub-field name for body text', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'acf_image_field',
			[
				'label'       => esc_html__( 'Image Field Mapping', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'Enter sub-field name for image', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'acf_link_field',
			[
				'label'       => esc_html__( 'Link Field Mapping', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'Enter sub-field name for link', 'sky-elementor-addons' ),
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_slider_layout',
			[
				'label' => esc_html__( 'Slider Layout', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_responsive_control(
			'height',
			[
				'label'      => esc_html__( 'Slider Height', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', '%', 'vh' ],
				'range'      => [
					'px' => [
						'min' => 200,
						'max' => 1000,
					],
					'%'  => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .swiper' => 'height: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .swiper-vertical .swiper-slide' => 'height: {{SIZE}}{{UNIT}} !important;',
				],
				'condition'  => [
					'slider_aspect_ratio' => '',
				],
			]
		);

		$this->add_control(
			'slider_aspect_ratio',
			[
				'label'        => esc_html__( 'Aspect Ratio', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'         => Controls_Manager::SELECT,
				'default'      => '',
				'options'      => [
					''    => esc_html__( 'None', 'sky-elementor-addons' ),
					'11'  => '1:1',
					'21'  => '2:1',
					'32'  => '3:2',
					'43'  => '4:3',
					'85'  => '8:5',
					'169' => '16:9',
					'219' => '21:9',
					'916' => '9:16',
				],
				'prefix_class' => 'sa-as-ratio-yes sa-ratio-',
				'render_type'  => 'template',
			]
		);

		$this->add_responsive_control(
			'content_position',
			[
				'label'          => esc_html__( 'Content Position', 'sky-elementor-addons' ),
				'type'           => Controls_Manager::SELECT,
				'label_block'    => false,
				'options' => [
					'top-left'      => esc_html__( 'Top Left', 'sky-elementor-addons' ),
					'top-center'    => esc_html__( 'Top Center', 'sky-elementor-addons' ),
					'top-right'     => esc_html__( 'Top Right', 'sky-elementor-addons' ),
					'middle-left'   => esc_html__( 'Middle Left', 'sky-elementor-addons' ),
					'middle-center' => esc_html__( 'Middle Center', 'sky-elementor-addons' ),
					'middle-right'  => esc_html__( 'Middle Right', 'sky-elementor-addons' ),
					'bottom-left'   => esc_html__( 'Bottom Left', 'sky-elementor-addons' ),
					'bottom-center' => esc_html__( 'Bottom Center', 'sky-elementor-addons' ),
					'bottom-right'  => esc_html__( 'Bottom Right', 'sky-elementor-addons' ),
				],
				// 'toggle'               => false,
				'default'        => 'middle-center',
				// 'desktop_default'       => 'middle-center',
				// 'tablet_default'       => 'middle-center',
				// 'mobile_default'       => 'middle-center',
				// 'prefix_class'         => 'sa-slider-%s-',
				'style_transfer' => true,
				'selectors' => [
					'{{WRAPPER}} .sa-advanced-slider .sa-slider-content-wrapper' => '{{VALUE}};',
				],
				'selectors_dictionary' => [
					'top-left'      => 'top: 0%; left: 0; transform: translate(0%, 0%); bottom: unset;',
					'top-center'    => 'top: 0; left: 50%; transform: translate(-50%, 0%); bottom: unset;',
					'top-right'     => 'top: 0%; right: 0; transform: translate(0%, 0%); bottom: unset;',
					'middle-left'   => 'top: 50%; left: 0; transform: translate(0,-50%); bottom: unset;',
					'middle-center' => 'top: 50%; left: 50%; transform: translate(-50%, -50%); bottom: unset;',
					'middle-right'  => 'top: 50%; right: 0; transform: translate(0,-50%); bottom: unset;',
					'bottom-left'   => 'bottom: 0%; left: 0%; transform: translate(0%, 0%); top: unset;',
					'bottom-center' => 'bottom: 0%; left: 50%; transform: translate(-50%, 0%); top: unset;',
					'bottom-right'  => 'bottom: 0%; right: 0%; transform: translate(0%, 0%); top: unset;',
				],
			]
		);

		$this->add_control(
			'text_effect',
			[
				'label'        => esc_html__( 'Text Effects', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SELECT,
				'default'      => 'default',
				'options'      => [
					'none'    => esc_html__( 'None', 'sky-elementor-addons' ),
					'default' => esc_html__( 'Right To Left', 'sky-elementor-addons' ),
					'l-to-r'  => esc_html__( 'Left To Right', 'sky-elementor-addons' ),
					't-to-b'  => esc_html__( 'Top To Bottom', 'sky-elementor-addons' ),
					'b-to-t'  => esc_html__( 'Bottom To Top', 'sky-elementor-addons' ),
				],
				'prefix_class' => 'sa-slider-text-effect-',
			]
		);

		$this->add_control(
			'link_on',
			[
				'label'    => esc_html__( 'Link On', 'sky-elementor-addons' ),
				'type'     => Controls_Manager::SELECT2,
				'multiple' => true,
				'options'  => [
					'title' => esc_html__( 'Title', 'sky-elementor-addons' ),
					'item'  => esc_html__( 'Item', 'sky-elementor-addons' ),
				],
			]
		);

		$this->add_control(
			'link_on_note',
			[
				'label'           => '',
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => esc_html__( 'Note: Links will work on Buttons normally but if you want extra more link options like as on Title or Full Item, you can use this option easily.', 'sky-elementor-addons' ),
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
			]
		);

		$this->add_control(
			'show_title',
			[
				'label'     => esc_html__( 'Show Title', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'separator' => 'before',
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
			'show_sub_title',
			[
				'label'     => esc_html__( 'Show Sub Title', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'separator' => 'before',
			]
		);

		$this->add_control(
			'sub_title_tag',
			[
				'label'   => esc_html__( 'Sub Title HTML Tag', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'h5',
				'options' => sky_addons_title_tags(),
				'condition' => [
					'show_sub_title' => 'yes',
				],
			]
		);

		$this->add_control(
			'show_text',
			[
				'label'     => esc_html__( 'Show Text', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'separator' => 'before',
			]
		);

		$this->add_control(
			'posts_text_source',
			[
				'label'   => esc_html__( 'Text Source', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'excerpt',
				'options' => [
					'excerpt' => esc_html__( 'Excerpt', 'sky-elementor-addons' ),
					'content' => esc_html__( 'Full Content', 'sky-elementor-addons' ),
				],
				'condition' => [
					'content_type' => 'posts',
					'show_text'    => 'yes',
				],
			]
		);

		$this->add_control(
			'text_length',
			[
				'label'       => esc_html__( 'Text Word Limit', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::NUMBER,
				'min'         => 0,
				'max'         => 200,
				'step'        => 1,
				'placeholder' => esc_html__( '0 = no limit', 'sky-elementor-addons' ),
				'condition'   => [
					'show_text'    => 'yes',
					'content_type' => [ 'posts', 'acf' ],
				],
			]
		);

		$this->add_control(
			'read_more_text',
			[
				'label'       => esc_html__( 'Read More Text', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Read More', 'sky-elementor-addons' ),
				'placeholder' => esc_html__( 'Read More', 'sky-elementor-addons' ),
				'condition'   => [
					'show_text'    => 'yes',
					'content_type' => [ 'posts', 'acf' ],
					'text_length!' => [ '', '0' ],
				],
			]
		);

		$this->add_control(
			'show_button',
			[
				'label'   => esc_html__( 'Show Button / Link', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'show_navigation',
			[
				'label'     => esc_html__( 'Show Navigation', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'separator' => 'before',
			]
		);

		$this->add_control(
			'show_pagination',
			[
				'label'   => esc_html__( 'Show Pagination', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_slider_settings',
			[
				'label' => esc_html__( 'Settings', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'direction',
			[
				'label'       => esc_html__( 'Direction', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'horizontal',
				'options'     => [
					'horizontal' => esc_html__( 'Horizontal', 'sky-elementor-addons' ),
					'vertical'   => esc_html__( 'Vertical', 'sky-elementor-addons' ),
				],
				'render_type' => 'template',
			]
		);

		$this->add_control(
			'transition_effect',
			[
				'label'   => esc_html__( 'Transition Effect', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'slide',
				'options' => [
					'slide'     => esc_html__( 'Slide', 'sky-elementor-addons' ),
					'fade'      => esc_html__( 'Fade', 'sky-elementor-addons' ),
					'flip'      => esc_html__( 'Flip', 'sky-elementor-addons' ),
					'cube'      => esc_html__( 'Cube', 'sky-elementor-addons' ),
					'coverflow' => esc_html__( 'Coverflow', 'sky-elementor-addons' ),
				],
			]
		);

		$this->add_control(
			'cross_fade',
			[
				'label'   => esc_html__( 'Cross Fade', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
				'condition' => [
					'transition_effect' => 'fade',
				],
			]
		);

		$this->add_control(
			'coverflow_rotate',
			[
				'label' => esc_html__( 'Rotate', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 30,
				],
				'condition' => [
					'transition_effect' => 'coverflow',
				],
			]
		);

		$this->add_control(
			'slide_shadows',
			[
				'label' => esc_html__( 'Slide Shadows', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::SWITCHER,
				'condition' => [
					'transition_effect' => [ 'coverflow', 'flip', 'cube' ],
				],
			]
		);

		$this->add_control(
			'autoplay',
			[
				'label'   => esc_html__( 'Autoplay', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'autoplay_speed',
			[
				'label' => esc_html__( 'Autoplay Speed (sec)', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min'  => 0,
						'max'  => 10,
						'step' => .5,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 5,
				],
				'condition' => [
					'autoplay' => 'yes',
				],
			]
		);

		$this->add_control(
			'loop',
			[
				'label'   => esc_html__( 'Loop', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'speed',
			[
				'label' => esc_html__( 'Slide Speed (sec)', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min'  => 0,
						'max'  => 10,
						'step' => .5,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => .3,
				],
			]
		);

		$this->add_control(
			'pause_on_hover',
			[
				'label' => esc_html__( 'Pause On Hover', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::SWITCHER,
				'condition' => [
					'autoplay' => 'yes',
				],
			]
		);

		$this->add_control(
			'observer',
			[
				'label'       => esc_html__( 'Observer', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::SWITCHER,
				'default'     => 'yes',
				'separator'   => 'before',
				'description' => esc_html__( 'Note: Please use it when you using slider on a hidden element.', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'hash_navigation',
			[
				'label'     => esc_html__( 'Hash Navigation', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SWITCHER,
				'separator' => 'before',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_video_settings',
			[
				'label' => esc_html__( 'Video Background', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'video_autoplay',
			[
				'label'   => esc_html__( 'Autoplay', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'video_mute',
			[
				'label'   => esc_html__( 'Mute', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'video_loop',
			[
				'label'   => esc_html__( 'Loop', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'video_play_on_mobile',
			[
				'label'       => esc_html__( 'Play on Mobile', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::SWITCHER,
				'default'     => '',
				'description' => esc_html__( 'Video is disabled on mobile by default to save bandwidth. Enable to force-play.', 'sky-elementor-addons' ),
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_button',
			[
				'label' => esc_html__( 'Button', 'sky-elementor-addons' ),
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
				'default' => esc_html__( 'Click here', 'sky-elementor-addons' ),
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
						'min'  => 0,
						'max'  => 20,
						'step' => 1,
					],
				],
				'condition'  => [
					'button_icon[value]!' => '',
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-button-left'  => 'margin-right: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .sa-button-right' => 'margin-left: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_navigation',
			[
				'label'     => esc_html__( 'Navigation', 'sky-elementor-addons' ),
				'tab'       => Controls_Manager::TAB_CONTENT,
				'condition' => [ 'show_navigation' => 'yes' ],
			]
		);

		$this->add_control(
			'prev_icon',
			[
				'label' => esc_html__( 'Prev Icon', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::ICONS,
			]
		);

		$this->add_control(
			'next_icon',
			[
				'label' => esc_html__( 'Next Icon', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::ICONS,
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_pagination',
			[
				'label'     => esc_html__( 'Pagination', 'sky-elementor-addons' ),
				'tab'       => Controls_Manager::TAB_CONTENT,
				'condition' => [ 'show_pagination' => 'yes' ],
			]
		);

		$this->add_control(
			'pagination_type',
			[
				'label'   => esc_html__( 'Pagination Type', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'bullets',
				'options' => [
					'none'        => esc_html__( 'None', 'sky-elementor-addons' ),
					'bullets'     => esc_html__( 'Bullets', 'sky-elementor-addons' ),
					'fraction'    => esc_html__( 'Fraction', 'sky-elementor-addons' ),
					'progressbar' => esc_html__( 'Progress Bar', 'sky-elementor-addons' ),
				],
			]
		);

		$this->add_control(
			'dynamic_bullets',
			[
				'label'     => esc_html__( 'Dynamic Bullets', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SWITCHER,
				'condition' => [ 'pagination_type' => 'bullets' ],
			]
		);

		$this->add_control(
			'progressbar_position',
			[
				'label'   => esc_html__( 'Progress Bar Position', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'bottom',
				'options' => [
					'bottom' => esc_html__( 'Bottom', 'sky-elementor-addons' ),
					'top'    => esc_html__( 'Top', 'sky-elementor-addons' ),
				],
				'selectors' => [
					'{{WRAPPER}} .swiper-horizontal > .swiper-pagination-progressbar' => '{{VALUE}}',
				],
				'selectors_dictionary' => [
					'bottom' => '',
					'top'    => 'top: 0; bottom: unset;',
				],
				'condition' => [
					'pagination_type' => 'progressbar',
					'direction'       => 'horizontal',
				],
			]
		);

		$this->add_control(
			'progressbar_position_vertical',
			[
				'label'   => esc_html__( 'Progress Bar Position', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'left',
				'options' => [
					'left'  => esc_html__( 'Left', 'sky-elementor-addons' ),
					'right' => esc_html__( 'Right', 'sky-elementor-addons' ),
				],
				'selectors' => [
					'{{WRAPPER}} .swiper-vertical > .swiper-pagination-progressbar' => '{{VALUE}}',
				],
				'selectors_dictionary' => [
					'left'  => '',
					'right' => 'right: 0; left: unset;',
				],
				'condition' => [
					'pagination_type' => 'progressbar',
					'direction'       => 'vertical',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_sliders_style',
			[
				'label' => esc_html__( 'Sliders', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'sliders_bg',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'exclude'  => [ 'image' ],
				'fields_options' => [
					'background' => [
						'label'   => esc_html__( 'Background', 'sky-elementor-addons' ),
						'default' => 'classic',
					],
					'color' => [
						'default' => '#0f172a',
					],
				],
				'selector' => '{{WRAPPER}} .swiper-slide',
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'sliders_adv_bg_overlay',
				'label'    => esc_html__( 'Background Overlay', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'exclude'  => [ 'image' ],
				'fields_options' => [
					'background' => [
						'label'   => esc_html__( 'Background Overlay', 'sky-elementor-addons' ),
						'default' => 'classic',
					],
					'color' => [
						'default' => 'rgba(0,0,0,0.4)',
					],
				],
				'selector' => '{{WRAPPER}} .swiper-slide:before',
			]
		);

		// Deprecated — kept registered so saved values still render; hidden from UI.
		$this->add_control(
			'sliders_bg_overlay',
			[
				'label'   => esc_html__( 'Background Overlay (Legacy)', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::COLOR,
				'classes' => 'elementor-hidden',
				'selectors' => [
					'{{WRAPPER}} .swiper-slide:before' => 'background: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'sliders_border',
				'label'    => esc_html__( 'Border', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .swiper-slide',
			]
		);

		$this->add_responsive_control(
			'sliders_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .swiper-slide' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_content_box',
			[
				'label' => esc_html__( 'Content Box', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		// $this->add_responsive_control(
		// 'content_wrapper_width',
		// [
		// 'label'     => esc_html__('Box Width (%)', 'sky-elementor-addons'),
		// 'type'      => Controls_Manager::SLIDER,
		// 'range'     => [
		// 'px' => [
		// 'min' => 50,
		// 'max' => 100,
		// ],
		// ],
		// 'selectors' => [
		// '{{WRAPPER}} .sa-slider-content-wrapper' => 'width: {{SIZE}}%;',
		// ],
		// ]
		// );

		$this->add_responsive_control(
			'content_wrapper_width',
			[
				'label'      => esc_html__( 'Box Width (% is Perfect)', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range'      => [
					'px' => [
						'min' => 500,
						'max' => 1300,
					],
					'%'  => [
						'min' => 50,
						'max' => 100,
					],
				],
				'default'    => [
					'unit' => '%',
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-slider-content-wrapper' => 'width: {{SIZE}}{{UNIT}};',
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
				'selectors' => [
					'{{WRAPPER}} .sa-slider-content-wrapper' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'content_wrapper_bg',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .sa-slider-content-wrapper',
			]
		);

		$this->add_responsive_control(
			'content_wrapper_padding',
			[
				'label'      => esc_html__( 'Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-slider-content-wrapper' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
					'{{WRAPPER}} .sa-slider-content-wrapper' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'content_wrapper_border',
				'label'    => esc_html__( 'Border', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-slider-content-wrapper',
			]
		);

		$this->add_responsive_control(
			'content_wrapper_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-slider-content-wrapper' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_image_style',
			[
				'label' => esc_html__( 'Slider Image', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'img_padding',
			[
				'label'      => esc_html__( 'Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-slider-img-wrapper' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->start_controls_tabs( 'img_tabs' );

		$this->start_controls_tab(
			'img_tab_normal',
			[
				'label' => esc_html__( 'Normal', 'sky-elementor-addons' ),
			]
		);

		$this->add_responsive_control(
			'img_opacity',
			[
				'label' => esc_html__( 'Image Opacity', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min'  => 0,
						'max'  => 1,
						'step' => .1,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .sa-advanced-slider .sa-slider-img-wrapper img' => 'opacity: {{SIZE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			[
				'name'     => 'img_css_filters',
				'selector' => '{{WRAPPER}} .sa-advanced-slider .sa-slider-img-wrapper img',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'img_tab_hover',
			[
				'label' => esc_html__( 'Hover', 'sky-elementor-addons' ),
			]
		);

		$this->add_responsive_control(
			'img_opacity_hover',
			[
				'label' => esc_html__( 'Image Opacity', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min'  => 0,
						'max'  => 1,
						'step' => .1,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .sa-advanced-slider .sa-slider-img-wrapper:hover img' => 'opacity: {{SIZE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			[
				'name'     => 'img_css_filters_hover',
				'selector' => '{{WRAPPER}} .sa-advanced-slider .sa-slider-img-wrapper:hover img',
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
			'section_title_style',
			[
				'label' => esc_html__( 'Title', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_title' => 'yes',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Text_Stroke::get_type(),
			[
				'name'     => 'title_text_stroke',
				'selector' => '{{WRAPPER}} .sa-title',
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
					'{{WRAPPER}} .sa-title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
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

		$this->add_control(
			'title_color',
			[
				'label' => esc_html__( 'Text Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-title, {{WRAPPER}} .sa-title *' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'title_background',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
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

		$this->add_responsive_control(
			'title_padding',
			[
				'label'      => esc_html__( 'Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'title_border',
				'label'    => esc_html__( 'Border', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-title',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_sub_title_style',
			[
				'label' => esc_html__( 'Sub Title', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_sub_title' => 'yes',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Text_Stroke::get_type(),
			[
				'name'     => 'sub_title_text_stroke',
				'selector' => '{{WRAPPER}} .sa-sub-title',
			]
		);

		$this->add_responsive_control(
			'sub_title_spacing',
			[
				'label'      => esc_html__( 'Bottom Spacing', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
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
					'{{WRAPPER}} .sa-sub-title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'sub_title_typography',
				'label'    => esc_html__( 'Typography', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-sub-title',
			]
		);

		$this->add_control(
			'sub_title_color',
			[
				'label' => esc_html__( 'Text Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-sub-title, {{WRAPPER}} .sa-sub-title *' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'sub_title_background',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .sa-sub-title',
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'sub_title_text_shadow',
				'label'    => esc_html__( 'Text Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-sub-title',
			]
		);

		$this->add_responsive_control(
			'sub_title_padding',
			[
				'label'      => esc_html__( 'Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-sub-title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'sub_title_border',
				'label'    => esc_html__( 'Border', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-sub-title',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_text_style',
			[
				'label' => esc_html__( 'Text', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_text' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'text_spacing',
			[
				'label'      => esc_html__( 'Bottom Spacing', 'sky-elementor-addons' ),
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
					'{{WRAPPER}} .sa-content + .sa-link-wrapper' => 'margin-top: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'text_typography',
				'label'    => esc_html__( 'Typography', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-content',
			]
		);

		$this->add_control(
			'text_color',
			[
				'label' => esc_html__( 'Text Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-content, {{WRAPPER}} .sa-content *' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'text_background',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .sa-content',
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'text_text_shadow',
				'label'    => esc_html__( 'Text Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-content',
			]
		);

		$this->add_responsive_control(
			'text_padding',
			[
				'label'      => esc_html__( 'Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_button_style',
			[
				'label' => esc_html__( 'Button / Link', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_button' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'button_padding',
			[
				'label'      => esc_html__( 'Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'button_typography',
				'label'    => esc_html__( 'Typography', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-button',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'button_border',
				'label'    => esc_html__( 'Border', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-button',
			]
		);

		$this->add_responsive_control(
			'button_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow: hidden;',
				],
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
			'button_color',
			[
				'label' => esc_html__( 'Text Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-button' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'button_background',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .sa-button',
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'button_text_shadow',
				'label'    => esc_html__( 'Text Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-button',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'button_box_shadow',
				'label'    => esc_html__( 'Box Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-button',
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
			'button_color_hover',
			[
				'label' => esc_html__( 'Text Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-button:hover' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'button_background_hover',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .sa-button:hover',
			]
		);

		$this->add_control(
			'button_border_color_hover',
			[
				'label' => esc_html__( 'Border Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-button:hover' => 'border-color: {{VALUE}};',
				],
				'condition' => [
					'button_border_border!' => '',
				],
			]
		);

		$this->add_responsive_control(
			'button_border_radius_hover',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-button:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow: hidden;',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'button_text_shadow_hover',
				'label'    => esc_html__( 'Text Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-button:hover',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'button_box_shadow_hover',
				'label'    => esc_html__( 'Box Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-button:hover',
			]
		);

		$this->add_control(
			'button_hover_animation',
			[
				'label'   => esc_html__( 'Animation', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::HOVER_ANIMATION,
				'default' => 'grow',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		// $this->start_controls_section(
		// 'section_navigation_style',
		// [
		// 'label'     => esc_html__('Navigation', 'sky-elementor-addons'),
		// 'tab'       => Controls_Manager::TAB_STYLE,
		// 'condition' => [
		// 'show_navigation' => 'yes'
		// ]
		// ]
		// );

		// $this->add_responsive_control(
		// 'navigation_size',
		// [
		// 'label'      => esc_html__('Size', 'sky-elementor-addons'),
		// 'type'       => Controls_Manager::SLIDER,
		// 'size_units' => ['px', 'em'],
		// 'range'      => [
		// 'px' => [
		// 'min' => 5,
		// 'max' => 50,
		// ],
		// ],
		// 'selectors'  => [
		// '{{WRAPPER}} ' => '--sa-navigation-size: {{SIZE}}{{UNIT}};',
		// ],
		// ]
		// );

		// $this->add_responsive_control(
		// 'navigation_spacing',
		// [
		// 'label'      => esc_html__('Spacing', 'sky-elementor-addons'),
		// 'type'       => Controls_Manager::SLIDER,
		// 'size_units' => ['px', 'em'],
		// 'range'      => [
		// 'px' => [
		// 'min' => 10,
		// 'max' => 100,
		// ],
		// ],
		// 'selectors'  => [
		// '{{WRAPPER}} ' => '--sa-navigation-spacing: {{SIZE}}{{UNIT}};',
		// ],
		// ]
		// );

		// $this->add_responsive_control(
		// 'navigation_padding',
		// [
		// 'label'      => esc_html__('Padding', 'sky-elementor-addons'),
		// 'type'       => Controls_Manager::DIMENSIONS,
		// 'size_units' => ['px', 'em', '%'],
		// 'selectors'  => [
		// '{{WRAPPER}} .sa-swiper-button-prev, {{WRAPPER}} .sa-swiper-button-next' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
		// ],
		// ]
		// );

		// $this->add_group_control(
		// Group_Control_Border::get_type(),
		// [
		// 'name'     => 'navigation_border',
		// 'label'    => esc_html__('Border', 'sky-elementor-addons'),
		// 'selector' => '{{WRAPPER}} .sa-swiper-button-prev, {{WRAPPER}} .sa-swiper-button-next',
		// ]
		// );

		// $this->add_responsive_control(
		// 'navigation_border_radius',
		// [
		// 'label'      => esc_html__('Border Radius', 'sky-elementor-addons'),
		// 'type'       => Controls_Manager::DIMENSIONS,
		// 'size_units' => ['px', 'em', '%'],
		// 'selectors'  => [
		// '{{WRAPPER}} .sa-swiper-button-prev, {{WRAPPER}} .sa-swiper-button-next' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
		// ],
		// ]
		// );

		// $this->start_controls_tabs('navigation_tabs');

		// $this->start_controls_tab(
		// 'navigation_tab_normal',
		// [
		// 'label' => esc_html__('Normal', 'sky-elementor-addons'),
		// ]
		// );

		// $this->add_control(
		// 'navigation_color',
		// [
		// 'label'     => esc_html__('Color', 'sky-elementor-addons'),
		// 'type'      => Controls_Manager::COLOR,
		// 'selectors' => [
		// '{{WRAPPER}} .sa-swiper-button-prev, {{WRAPPER}} .sa-swiper-button-next'             => 'color: {{VALUE}}',
		// '{{WRAPPER}} .sa-swiper-button-prev svg *, {{WRAPPER}} .sa-swiper-button-next svg *' => 'fill: {{VALUE}}',
		// ],
		// ]
		// );

		// $this->add_group_control(
		// Group_Control_Background::get_type(),
		// [
		// 'name'     => 'navigation_bg',
		// 'label'    => esc_html__('Background', 'sky-elementor-addons'),
		// 'types'    => ['classic', 'gradient'],
		// 'selector' => '{{WRAPPER}} .sa-swiper-button-prev, {{WRAPPER}} .sa-swiper-button-next',
		// ]
		// );

		// $this->add_group_control(
		// Group_Control_Text_Shadow::get_type(),
		// [
		// 'name'     => 'navigation_text_shadow',
		// 'label'    => esc_html__('Text Shadow', 'sky-elementor-addons'),
		// 'selector' => '{{WRAPPER}} .sa-swiper-button-prev, {{WRAPPER}} .sa-swiper-button-next',
		// ]
		// );

		// $this->add_group_control(
		// Group_Control_Box_Shadow::get_type(),
		// [
		// 'name'     => 'navigation_box_shadow',
		// 'label'    => esc_html__('Box Shadow', 'sky-elementor-addons'),
		// 'selector' => '{{WRAPPER}} .sa-swiper-button-prev, {{WRAPPER}} .sa-swiper-button-next',
		// ]
		// );

		// $this->end_controls_tab();

		// $this->start_controls_tab(
		// 'navigation_tab_hover',
		// [
		// 'label' => esc_html__('Hover', 'sky-elementor-addons'),
		// ]
		// );

		// $this->add_control(
		// 'navigation_color_hover',
		// [
		// 'label'     => esc_html__('Color', 'sky-elementor-addons'),
		// 'type'      => Controls_Manager::COLOR,
		// 'selectors' => [
		// '{{WRAPPER}} .sa-swiper-button-prev:hover, {{WRAPPER}} .sa-swiper-button-next:hover'             => 'color: {{VALUE}}',
		// '{{WRAPPER}} .sa-swiper-button-prev:hover svg *, {{WRAPPER}} .sa-swiper-button-next:hover svg *' => 'fill: {{VALUE}}',
		// ],
		// ]
		// );

		// $this->add_group_control(
		// Group_Control_Background::get_type(),
		// [
		// 'name'     => 'navigation_bg_hover',
		// 'label'    => esc_html__('Background', 'sky-elementor-addons'),
		// 'types'    => ['classic', 'gradient'],
		// 'selector' => '{{WRAPPER}} .sa-swiper-button-prev:hover, {{WRAPPER}} .sa-swiper-button-next:hover',
		// ]
		// );

		// $this->add_control(
		// 'navigation_border_color_hover',
		// [
		// 'label'     => esc_html__('Border Color', 'sky-elementor-addons'),
		// 'type'      => Controls_Manager::COLOR,
		// 'selectors' => [
		// '{{WRAPPER}} .sa-swiper-button-prev:hover, {{WRAPPER}} .sa-swiper-button-next:hover' => 'border-color: {{VALUE}};',
		// ],
		// 'condition' => [
		// 'navigation_border_border!' => '',
		// ],
		// ]
		// );

		// $this->add_group_control(
		// Group_Control_Text_Shadow::get_type(),
		// [
		// 'name'     => 'navigation_text_shadow_hover',
		// 'label'    => esc_html__('Text Shadow', 'sky-elementor-addons'),
		// 'selector' => '{{WRAPPER}} .sa-swiper-button-prev:hover, {{WRAPPER}} .sa-swiper-button-next:hover',
		// ]
		// );

		// $this->add_group_control(
		// Group_Control_Box_Shadow::get_type(),
		// [
		// 'name'     => 'navigation_box_shadow_hover',
		// 'label'    => esc_html__('Box Shadow', 'sky-elementor-addons'),
		// 'selector' => '{{WRAPPER}} .sa-swiper-button-prev:hover, {{WRAPPER}} .sa-swiper-button-next:hover',
		// ]
		// );

		// $this->end_controls_tab();

		// $this->end_controls_tabs();

		// $this->end_controls_section();

		$this->start_controls_section(
			'section_navigation_style',
			[
				'label' => esc_html__( 'Navigation', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_navigation' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'navigation_bottom_spacing',
			[
				'label' => esc_html__( 'Bottom Spacing', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .sa-navigation-wrapper' => 'bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'navigation_size',
			[
				'label'      => esc_html__( 'Size', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range'      => [
					'px' => [
						'min' => 5,
						'max' => 50,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} ' => '--sa-navigation-size: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'navigation_spacing',
			[
				'label'      => esc_html__( 'Spacing', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} ' => '--sa-navigation-spacing: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'navigation_padding',
			[
				'label'      => esc_html__( 'Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-swiper-button-prev, {{WRAPPER}} .sa-swiper-button-next' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'navigation_border',
				'label'    => esc_html__( 'Border', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-swiper-button-prev, {{WRAPPER}} .sa-swiper-button-next',
			]
		);

		$this->start_controls_tabs( 'navigation_tabs' );

		$this->start_controls_tab(
			'navigation_tab_normal',
			[
				'label' => esc_html__( 'Normal', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'navigation_color',
			[
				'label' => esc_html__( 'Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-swiper-button-prev, {{WRAPPER}} .sa-swiper-button-next' => 'color: {{VALUE}}',
					'{{WRAPPER}} .sa-swiper-button-prev svg *, {{WRAPPER}} .sa-swiper-button-next svg *' => 'fill: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'navigation_bg',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .sa-swiper-button-prev, {{WRAPPER}} .sa-swiper-button-next',
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'navigation_text_shadow',
				'label'    => esc_html__( 'Text Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-swiper-button-prev, {{WRAPPER}} .sa-swiper-button-next',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'navigation_box_shadow',
				'label'    => esc_html__( 'Box Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-swiper-button-prev, {{WRAPPER}} .sa-swiper-button-next',
			]
		);

		$this->add_responsive_control(
			'navigation_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-swiper-button-prev, {{WRAPPER}} .sa-swiper-button-next' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'navigation_tab_hover',
			[
				'label' => esc_html__( 'Hover', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'navigation_color_hover',
			[
				'label' => esc_html__( 'Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-swiper-button-prev:hover, {{WRAPPER}} .sa-swiper-button-next:hover' => 'color: {{VALUE}}',
					'{{WRAPPER}} .sa-swiper-button-prev:hover svg *, {{WRAPPER}} .sa-swiper-button-next:hover svg *' => 'fill: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'navigation_bg_hover',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .sa-swiper-button-prev:hover, {{WRAPPER}} .sa-swiper-button-next:hover',
			]
		);

		$this->add_control(
			'navigation_border_color_hover',
			[
				'label' => esc_html__( 'Border Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-swiper-button-prev:hover, {{WRAPPER}} .sa-swiper-button-next:hover' => 'border-color: {{VALUE}};',
				],
				'condition' => [
					'navigation_border_border!' => '',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'navigation_text_shadow_hover',
				'label'    => esc_html__( 'Text Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-swiper-button-prev:hover, {{WRAPPER}} .sa-swiper-button-next:hover',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'navigation_box_shadow_hover',
				'label'    => esc_html__( 'Box Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-swiper-button-prev:hover, {{WRAPPER}} .sa-swiper-button-next:hover',
			]
		);

		$this->add_responsive_control(
			'navigation_border_radius_hover',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-swiper-button-prev:hover, {{WRAPPER}} .sa-swiper-button-next:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		$this->start_controls_section(
			'section_pagination_style',
			[
				'label' => esc_html__( 'Pagination', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_pagination' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'pagination_bottom_spacing',
			[
				'label' => esc_html__( 'Bottom Spacing', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors' => [
					// Every pagination type carries `.swiper-pagination`; the per-type list this
					// replaced existed only to out-specify Swiper's own two-class
					// `.swiper-horizontal > .swiper-pagination-bullets` (0,2,0). Going through
					// `.swiper` makes this (0,3,0), which beats it with one clause.
					'{{WRAPPER}} .swiper .swiper-pagination' => 'bottom: {{SIZE}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_responsive_control(
			'bullet_size',
			[
				'label'      => esc_html__( 'Bullet Height', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range'      => [
					'px' => [
						'min'  => 1,
						'max'  => 50,
						'step' => .5,
					],
				],
				'selectors'  => [
					'{{WRAPPER}}' => '--sa-pagination-bullet-height: {{SIZE}}{{UNIT}};',
					// '{{WRAPPER}} .swiper-pagination-bullet' => 'width: {{SIZE}}{{UNIT}};', // for fix old issue
				],
				'condition'  => [ 'pagination_type' => 'bullets' ],
			]
		);

		$this->add_responsive_control(
			'bullet_width',
			[
				'label'      => esc_html__( 'Bullet Width', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range'      => [
					'px' => [
						'min'  => 1,
						'max'  => 50,
						'step' => .5,
					],
				],
				'selectors'  => [
					'{{WRAPPER}}' => '--sa-pagination-bullet-width: {{SIZE}}{{UNIT}};',
					// '{{WRAPPER}} .swiper-pagination-bullet' => 'width: {{SIZE}}{{UNIT}} !important;', // for fix old issue
				],
				'condition'  => [ 'pagination_type' => 'bullets' ],
			]
		);

		$this->add_responsive_control(
			'bullet_spacing',
			[
				'label'      => esc_html__( 'Bullet Spacing', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range'      => [
					'px' => [
						'min'  => 4,
						'max'  => 20,
						'step' => .5,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .swiper-pagination-bullets .swiper-pagination-bullet' => 'margin: 0px {{SIZE}}{{UNIT}};',
				],
				'condition'  => [ 'pagination_type' => 'bullets' ],
			]
		);

		$this->start_controls_tabs(
			'style_pagination_tabs'
		);

		$this->start_controls_tab(
			'style_pagination_normal_tab',
			[
				'label' => esc_html__( 'Normal', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'fraction_color',
			[
				'label'   => esc_html__( 'Pagination Color', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::COLOR,
				'default' => '#ddd',
				'selectors' => [
					'{{WRAPPER}} .swiper-pagination.swiper-pagination-fraction' => 'color: {{VALUE}}',
				],
				'condition' => [
					'pagination_type' => 'fraction',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'      => 'pagination_fraction_typography',
				'label'     => esc_html__( 'Typography', 'sky-elementor-addons' ),
				'selector'  => '{{WRAPPER}} .swiper-pagination-fraction',
				'condition' => [ 'pagination_type' => 'fraction' ],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'pagination_color',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'exclude'  => [ 'image' ],
				'fields_options' => [
					'background' => [
						'label' => esc_html__( 'Pagination Color', 'sky-elementor-addons' ),
					],
				],
				'selector' => '{{WRAPPER}} .swiper-pagination-bullet, {{WRAPPER}} .swiper-pagination-progressbar',
				'conditions' => [
					'relation' => 'or',
					'terms'    => [
						[
							'name'     => 'pagination_type',
							'operator' => '===',
							'value'    => 'bullets',
						],
						[
							'name'     => 'pagination_type',
							'operator' => '===',
							'value'    => 'progressbar',
						],
					],
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'      => 'pagination_border',
				'label'     => esc_html__( 'Border', 'sky-elementor-addons' ),
				'selector'  => '{{WRAPPER}} .swiper-pagination-bullet',
				'condition' => [ 'pagination_type' => 'bullets' ],
				// 'fields_options' => [
				// 'border' => [
				// 'default' => 'solid',
				// ],
				// 'width' => [
				// 'default' => [
				// 'top'      => '8',
				// 'right'    => '8',
				// 'bottom'   => '8',
				// 'left'     => '8',
				// 'unit'     => 'px',
				// 'isLinked' => false,
				// ],
				// ],
				// 'color' => [
				// 'default' => '#0A0A0AC4',
				// ],
				// ],
			]
		);

		$this->add_responsive_control(
			'pagination_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .swiper-pagination-bullet' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition'  => [ 'pagination_type' => 'bullets' ],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'style_pagination_active_tab',
			[
				'label' => esc_html__( 'Active', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'fraction_active_color',
			[
				'label'     => esc_html__( 'Pagination Active Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#fff',
				'selectors' => [
					'{{WRAPPER}} .swiper-pagination-fraction .swiper-pagination-current' => 'color: {{VALUE}}',
				],
				'condition' => [ 'pagination_type' => 'fraction' ],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'      => 'pagination_fraction_typography_active',
				'label'     => esc_html__( 'Typography', 'sky-elementor-addons' ),
				'selector'  => '{{WRAPPER}} .swiper-pagination-fraction .swiper-pagination-current',
				'condition' => [ 'pagination_type' => 'fraction' ],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'pagination_active_color',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'exclude'  => [ 'image' ],
				'fields_options' => [
					'background' => [
						'label' => esc_html__( 'Pagination Active Color', 'sky-elementor-addons' ),
					],
				],
				'selector' => '{{WRAPPER}} .swiper-pagination-bullet.swiper-pagination-bullet-active, {{WRAPPER}} .swiper-pagination-progressbar-fill',
				'conditions' => [
					'relation' => 'or',
					'terms'    => [
						[
							'name'     => 'pagination_type',
							'operator' => '===',
							'value'    => 'bullets',
						],
						[
							'name'     => 'pagination_type',
							'operator' => '===',
							'value'    => 'progressbar',
						],
					],
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'      => 'pagination_border_active',
				'label'     => esc_html__( 'Border', 'sky-elementor-addons' ),
				'selector'  => '{{WRAPPER}} .swiper-pagination-bullet.swiper-pagination-bullet-active',
				'condition' => [ 'pagination_type' => 'bullets' ],
			]
		);

		$this->add_responsive_control(
			'pagination_border_radius_active',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .swiper-pagination-bullet.swiper-pagination-bullet-active' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition'  => [ 'pagination_type' => 'bullets' ],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	protected function render_title( array $item ) {
		$settings   = $this->get_settings_for_display();
		$title_link = ! empty( $settings['link_on'] ) && in_array( 'title', $settings['link_on'] );

		// Auto-link titles for dynamic content (posts/ACF) — link URL is always the post permalink.
		if ( ! $title_link && ! empty( $item['_raw_content'] ) && ! empty( $item['link']['url'] ) ) {
			$title_link = true;
		}

		$title_tag     = Utils::validate_html_tag( $settings['title_tag'] );
		$title_content = $item['title'];

		if ( true === $title_link && ! empty( $item['link']['url'] ) ) :
			$this->add_render_attribute( 'title-link-attr', 'href', esc_url( $item['link']['url'] ), true );

			if ( $item['link']['is_external'] ) {
				$this->add_render_attribute( 'title-link-attr', 'target', '_blank', true );
			}

			if ( $item['link']['nofollow'] ) {
				$this->add_render_attribute( 'title-link-attr', 'rel', 'nofollow', true );
			}
			$title_content = '<a ' . $this->get_render_attribute_string( 'title-link-attr' ) . '>' . $title_content . '</a>';
		endif;
		printf(
			'<%1$s class="sa--title sa-title sa-mt-0 sa-mb-3">%2$s</%1$s>',
			esc_attr( Utils::validate_html_tag( $settings['title_tag'] ) ),
			wp_kses_post( $title_content )
		);
	}

	protected function render_sub_title( array $item ) {
		$settings = $this->get_settings_for_display();

		printf( '<%1$s class="sa--title sa-sub-title sa-mt-0 sa-mb-2">%2$s</%1$s>',
			esc_attr( Utils::validate_html_tag( $settings['sub_title_tag'] ) ),
			wp_kses_post( $item['sub_title'] )
		);
	}

	protected function render_button( $link ) {
		$settings = $this->get_settings_for_display();

		if ( 'yes' === $settings['show_button'] ) :

			$this->add_render_attribute( 'link_attr', 'class', 'sa-button sa-d-inline-flex sa-align-items-center sa-text-decoration-none sa-fw-bold', true );

			if ( ! empty( $link['url'] ) ) {
				$this->add_render_attribute( 'link_attr', 'href', esc_url( $link['url'] ), true );

				if ( $link['is_external'] ) {
					$this->add_render_attribute( 'link_attr', 'target', '_blank', true );
				}

				if ( $link['nofollow'] ) {
					$this->add_render_attribute( 'link_attr', 'rel', 'nofollow', true );
				}
			} else {
				$this->add_render_attribute( 'link_attr', 'target', '_self', true );
				$this->add_render_attribute( 'link_attr', 'rel', 'follow', true );
				$this->add_render_attribute( 'link_attr', 'href', 'javascript:void(0);', true );
			}

			if ( $settings['button_hover_animation'] ) {
				$this->add_render_attribute( 'link_attr', 'class', 'elementor-animation-' . $settings['button_hover_animation'] );
			}
			?>
			<a <?php $this->print_render_attribute_string( 'link_attr' ); ?>>
				<?php
				if ( ! empty( $settings['button_icon']['value'] ) && 'before' === $settings['button_icon_position'] ) {
					echo '<span class="sa-icon-wrap sa-button-icon">';
					Icons_Manager::render_icon( $settings['button_icon'], [
						'aria-hidden' => 'true',
						'class'       => 'sa-button-left sa-me-2',
					] );
					echo '</span>';
				}

				$this->add_inline_editing_attributes( 'button_text', 'none' );

				printf(
					'<span %1$s>%2$s</span>',
					wp_kses_post( $this->get_render_attribute_string( 'button_text' ) ),
					esc_html( $settings['button_text'] )
				);

				if ( ! empty( $settings['button_icon']['value'] ) && 'after' === $settings['button_icon_position'] ) {
					echo '<span class="sa-icon-wrap sa-button-icon">';
					Icons_Manager::render_icon( $settings['button_icon'], [
						'aria-hidden' => 'true',
						'class'       => 'sa-button-right sa-ms-2',
					] );
					echo '</span>';
				}
				?>
			</a>
			<?php
		endif;
	}

	protected function render_pagination() {
		?>
		<!-- If we need pagination -->
		<div class="swiper-pagination"></div>
		<?php
	}

	protected function render_navigation() {
		$settings = $this->get_settings_for_display();
		?>
		<!-- If we need navigation buttons -->
		<div class="sa-swiper-button-prev sa-slider-navigation sa-icon-wrap">
			<?php
			if ( ! empty( $settings['prev_icon']['value'] ) ) :
				Icons_Manager::render_icon( $settings['prev_icon'], [
					'aria-hidden' => 'true',
					'class'       => 'fa-fw',
				] );
			else :
				?>
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 27 44">
					<path d="M0,22L22,0l2.1,2.1L4.2,22l19.9,19.9L22,44L0,22L0,22L0,22z">
				</svg>
				<?php
			endif;
			?>

		</div>
		<div class="sa-swiper-button-next sa-slider-navigation sa-icon-wrap">
			<?php
			if ( ! empty( $settings['next_icon']['value'] ) ) :
				Icons_Manager::render_icon( $settings['next_icon'], [
					'aria-hidden' => 'true',
					'class'       => 'fa-fw',
				] );
			else :
				?>
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 27 44">
					<path d="M27,22L27,22L5,44l-2.1-2.1L22.8,22L2.9,2.1L5,0L27,22L27,22z">
				</svg>
				<?php
			endif;
			?>
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
				$thumb_id    = get_post_thumbnail_id();
				$thumb_url   = $thumb_id ? wp_get_attachment_url( $thumb_id ) : '';
				$text_source = $settings['posts_text_source'] ?? 'excerpt';
				$items[]     = [
					'content_source' => 'custom',
					'title'          => get_the_title(),
					'sub_title'      => '',
					'custom_text'    => 'content' === $text_source ? get_the_content() : get_the_excerpt(),
					'slider_image'   => [
						'id'  => $thumb_id,
						'url' => $thumb_url,
					],
					'link'           => [
						'url'         => get_permalink(),
						'is_external' => false,
						'nofollow'    => false,
					],
					'template_id'    => '',
					'anywhere_id'    => '',
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

		$sub_title_key = sanitize_text_field( $settings['acf_sub_title_field'] ?? '' );
		$title_key     = sanitize_text_field( $settings['acf_title_field'] ?? '' );
		$text_key      = sanitize_text_field( $settings['acf_text_field'] ?? '' );
		$image_key     = sanitize_text_field( $settings['acf_image_field'] ?? '' );
		$link_key      = sanitize_text_field( $settings['acf_link_field'] ?? '' );
		$items         = [];

		foreach ( $rows as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}

			$image_val = $image_key ? ( $row[ $image_key ] ?? '' ) : '';
			if ( is_numeric( $image_val ) && $image_val ) {
				$img = [
					'id'  => (int) $image_val,
					'url' => wp_get_attachment_url( (int) $image_val ) ?: '',
				];
			} elseif ( is_array( $image_val ) ) {
				$img = [
					'id'  => $image_val['ID'] ?? 0,
					'url' => $image_val['url'] ?? '',
				];
			} else {
				$img = [
					'id'  => 0,
					'url' => esc_url( (string) $image_val ),
				];
			}

			$link_val = $link_key ? ( $row[ $link_key ] ?? '' ) : '';
			if ( is_array( $link_val ) ) {
				$link = [
					'url'         => esc_url( $link_val['url'] ?? '' ),
					'is_external' => ! empty( $link_val['target'] ) && '_blank' === $link_val['target'],
					'nofollow'    => false,
				];
			} else {
				$link = [
					'url'         => esc_url( (string) $link_val ),
					'is_external' => false,
					'nofollow'    => false,
				];
			}

			$items[] = [
				'content_source' => 'custom',
				'title'          => $title_key ? ( $row[ $title_key ] ?? '' ) : '',
				'sub_title'      => $sub_title_key ? ( $row[ $sub_title_key ] ?? '' ) : '',
				'custom_text'    => $text_key ? ( $row[ $text_key ] ?? '' ) : '',
				'slider_image'   => $img,
				'link'           => $link,
				'template_id'    => '',
				'anywhere_id'    => '',
				'_raw_content'   => true,
			];
		}

		return $items;
	}

	protected function render_slide( $item, $index, $settings, $item_link_on ) {
		$hash = null;
		if ( 'yes' === $settings['hash_navigation'] ) {
			$hash = 'data-hash="' . sanitize_title( $item['title'] ) . '-' . $this->get_id() . $index . '"';
		}

		$item_link = '';
		if ( true === $item_link_on && ! empty( $item['link']['url'] ) ) {
			$target    = $item['link']['is_external'] ? '_blank' : '_self';
			// esc_url() emits `'` as &#039;, which the browser decodes back inside the attribute and breaks out of the JS string — esc_js() escapes it instead.
			$item_link = 'onclick="window.open(\'' . esc_js( esc_url_raw( $item['link']['url'] ) ) . '\', \'' . $target . '\')"';
		}
		?>
		<!-- Slides -->
		<div class="swiper-slide" <?php echo esc_html( $hash ) . ' ' . wp_kses_post( $item_link ); ?>>
			<?php if ( 'custom' === $item['content_source'] ) : ?>

				<?php
				if ( ! empty( $item['slider_image']['url'] ) ) {
					?>
					<div class="sa-slider-img-wrapper">
						<?php
						print ( wp_get_attachment_image(
							$item['slider_image']['id'],
							$settings['thumbnail_size'],
							false,
							[
								'class' => $settings['img_hover_animation'] ? 'elementor-animation-' . $settings['img_hover_animation'] : 'sa-',
								'alt'   => ! empty( $item['title'] ) ? esc_html( $item['title'] ) : Control_Media::get_image_alt( $item['slider_image'] ),
							]
						) );
						?>
					</div>
					<?php
				}

				if ( ! empty( $item['slide_video_type'] ) ) {
					$this->render_slide_video( $item );
				}
				?>

				<div class="sa-slider-content-wrapper">
					<?php
					if ( ( 'yes' === $settings['show_sub_title'] ) && ! empty( $item['sub_title'] ) ) :
						$this->render_sub_title( [ 'sub_title' => $item['sub_title'] ] );
					endif;

					if ( ( 'yes' === $settings['show_title'] ) && ! empty( $item['title'] ) ) :
						if ( ! empty( $item['link']['url'] ) ) :
							$this->render_title( [
								'title' => $item['title'],
								'link'  => $item['link'],
							] );
						else :
							$this->render_title( [ 'title' => $item['title'] ] );
						endif;
					endif;
					?>

					<?php
					if ( ( 'yes' === $settings['show_text'] ) && ! empty( $item['custom_text'] ) ) :
						$text_length = (int) ( $settings['text_length'] ?? 0 );
						if ( $text_length > 0 && ! empty( $item['_raw_content'] ) ) {
							$trimmed        = wp_trim_words( $item['custom_text'], $text_length, '' );
							$read_more_text = sanitize_text_field( $settings['read_more_text'] ?? '' );
							if ( $read_more_text && ! empty( $item['link']['url'] ) ) {
								$content = $trimmed . '&hellip; <a class="sa-read-more" href="' . esc_url( $item['link']['url'] ) . '">' . esc_html( $read_more_text ) . '</a>';
							} else {
								$content = $trimmed . ( $trimmed ? '&hellip;' : '' );
							}
						} elseif ( ! empty( $item['_raw_content'] ) ) {
							$content = $item['custom_text'];
						} else {
							$content = $this->parse_text_editor( $item['custom_text'] );
						}
						printf( '<div class="sa-content">%1$s</div>', wp_kses_post( $content ) );
					endif;
					?>

					<?php if ( 'yes' === $settings['show_button'] ) : ?>
						<div class="sa-link-wrapper">
							<?php $this->render_button( $item['link'] ); ?>
						</div>
					<?php endif; ?>
				</div>

			<?php elseif ( 'elementor' === $item['content_source'] && ! empty( $item['template_id'] ) ) : ?>
				<?php sky_addons_display_el_tem_by_id( $item['template_id'] ); ?>
			<?php elseif ( 'anywhere' === $item['content_source'] && ! empty( $item['anywhere_id'] ) ) : ?>
				<?php sky_addons_display_el_tem_by_id( $item['anywhere_id'] ); ?>
			<?php else : ?>
				<?php echo esc_html__( 'Sorry, You are doing something wrong!', 'sky-elementor-addons' ); ?>
			<?php endif; ?>
		</div>
		<?php
	}

	protected function render_slide_video( array $item ) {
		$settings = $this->get_settings_for_display();
		$type     = $item['slide_video_type'] ?? '';
		$autoplay = 'yes' === ( $settings['video_autoplay'] ?? 'yes' );
		$mute     = 'yes' === ( $settings['video_mute'] ?? 'yes' );
		$loop     = 'yes' === ( $settings['video_loop'] ?? 'yes' );
		$mobile   = 'yes' === ( $settings['video_play_on_mobile'] ?? '' );

		$hide_class = ! $mobile ? ' sa-video-hide-mobile' : '';

		if ( 'youtube' === $type ) {
			$video_id = $this->get_youtube_id( $item['slide_video_youtube'] ?? '' );
			if ( ! $video_id ) {
				return;
			}
			$params = [
				'autoplay'       => $autoplay ? 1 : 0,
				'mute'           => $mute ? 1 : 0,
				'loop'           => $loop ? 1 : 0,
				'playlist'       => $loop ? $video_id : '',
				'controls'       => 0,
				'showinfo'       => 0,
				'rel'            => 0,
				'enablejsapi'    => 1,
				'iv_load_policy' => 3,
				'modestbranding' => 1,
			];
			$src    = 'https://www.youtube.com/embed/' . $video_id . '?' . http_build_query( $params );
			?>
			<div class="sa-slide-video-wrapper sa-slide-video-iframe<?php echo esc_attr( $hide_class ); ?>" data-video-type="youtube">
				<iframe data-src="<?php echo esc_url( $src ); ?>" frameborder="0" allow="autoplay; fullscreen" allowfullscreen></iframe>
			</div>
			<?php

		} elseif ( 'vimeo' === $type ) {
			$video_id = $this->get_vimeo_id( $item['slide_video_vimeo'] ?? '' );
			if ( ! $video_id ) {
				return;
			}
			$params = [
				'autoplay'   => $autoplay ? 1 : 0,
				'muted'      => $mute ? 1 : 0,
				'loop'       => $loop ? 1 : 0,
				'background' => 1,
				'controls'   => 0,
			];
			$src    = 'https://player.vimeo.com/video/' . $video_id . '?' . http_build_query( $params );
			?>
			<div class="sa-slide-video-wrapper sa-slide-video-iframe<?php echo esc_attr( $hide_class ); ?>" data-video-type="vimeo">
				<iframe data-src="<?php echo esc_url( $src ); ?>" frameborder="0" allow="autoplay; fullscreen" allowfullscreen></iframe>
			</div>
			<?php

		} elseif ( in_array( $type, [ 'hosted', 'remote_url' ], true ) ) {
			$url = 'hosted' === $type
				? ( ! empty( $item['slide_video_hosted']['url'] ) ? $item['slide_video_hosted']['url'] : '' )
				: ( ! empty( $item['slide_video_remote_url'] ) ? $item['slide_video_remote_url'] : '' );
			if ( ! $url ) {
				return;
			}
			$attrs = [];
			if ( $autoplay ) {
				$attrs[] = 'autoplay';
			}
			if ( $mute ) {
				$attrs[] = 'muted';
			}
			if ( $loop ) {
				$attrs[] = 'loop';
			}
			$attrs[] = 'playsinline';
			$attrs[] = 'disablepictureinpicture';
			$attrs[] = 'disableremoteplayback';
			?>
			<div class="sa-slide-video-wrapper<?php echo esc_attr( $hide_class ); ?>" data-video-type="html5">
				<video <?php echo implode( ' ', $attrs ); // phpcs:ignore WordPress.Security.EscapeOutput -- boolean HTML attributes ?>>
					<source src="<?php echo esc_url( $url ); ?>">
				</video>
			</div>
			<?php
		}
	}

	private function get_youtube_id( string $url ): string {
		preg_match( '/(?:youtu\.be\/|youtube\.com\/(?:watch\?(?:.*&)?v=|embed\/|shorts\/))([a-zA-Z0-9_-]{11})/', $url, $m );
		return $m[1] ?? '';
	}

	private function get_vimeo_id( string $url ): string {
		preg_match( '/vimeo\.com\/(?:video\/)?(\d+)/', $url, $m );
		return $m[1] ?? '';
	}

	protected function render_scrollbar() {
		?>
		<!-- If we need scrollbar -->
		<!--<div class="swiper-scrollbar"></div>-->
		<?php
	}

	protected function render_header() {
		$settings = $this->get_settings_for_display();
		$id       = 'sa-' . $this->get_id();
		$this->add_render_attribute(
			[
				'slider' => [
					'class' => 'sa-advanced-slider',
					'id'    => $id,
					'data-settings' => [
						wp_json_encode( array_filter( [
							'direction'    => $settings['direction'],
							'autoplay' => 'yes' === $settings['autoplay'] ? [
								'delay' => $settings['autoplay_speed']['size'] * 1000,
							] : false,
							'loop'         => ( 'yes' === $settings['loop'] ) ? true : false,
							'speed'        => ( ! empty( $settings['speed']['size'] ) ) ? $settings['speed']['size'] * 1000 : 300,
							'pauseOnHover' => ( 'yes' === $settings['autoplay'] && 'yes' === $settings['pause_on_hover'] ) ? true : false,
							'effect'       => $settings['transition_effect'],
							'fadeEffect'   => ( isset( $settings['cross_fade'] ) && 'yes' === $settings['cross_fade'] ) ? true : false,
							'coverflowEffect' => [
								'rotate'       => ( isset( $settings['coverflow_rotate'] ) && ! empty( $settings['coverflow_rotate']['size'] ) ) ? $settings['coverflow_rotate']['size'] : false,
								'slideShadows' => ( isset( $settings['slide_shadows'] ) && 'yes' === $settings['slide_shadows'] ) ? true : false,
							],
							'flipEffect' => [
								'slideShadows' => ( isset( $settings['slide_shadows'] ) && 'yes' === $settings['slide_shadows'] ) ? true : false,
							],
							'cubeEffect' => [
								'slideShadows' => ( isset( $settings['slide_shadows'] ) && 'yes' === $settings['slide_shadows'] ) ? true : false,
							],
							'hashNavigation' => ( 'yes' === $settings['hash_navigation'] ) ? [
								'replaceState' => true,
							] : false,
							'observer'     => 'yes' === $settings['observer'] ? true : false,
							'navigation' => [
								'nextEl' => "#$id .sa-swiper-button-next",
								'prevEl' => "#$id .sa-swiper-button-prev",
							],
							'pagination' => [
								'el'             => "#$id .swiper-pagination",
								'clickable'      => true,
								'type'           => 'none' !== $settings['pagination_type'] ? $settings['pagination_type'] : false,
								'dynamicBullets' => ( isset( $settings['dynamic_bullets'] ) && ( 'yes' === $settings['dynamic_bullets'] ) ) ? true : false,
							],
							// scrollbar: {
							// el: '.swiper-scrollbar',
							// },
						] ) ),
					],
				],
			]
		);
		?>
		<div <?php $this->print_render_attribute_string( 'slider' ); ?>>
			<!-- Slider main container -->
			<div class="swiper">
				<?php
	}

	protected function render_footer() {
		?>
			</div>
		</div>
		<?php
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$item_link_on = false;
		if ( ! empty( $settings['link_on'] ) && in_array( 'item', $settings['link_on'] ) ) {
			$item_link_on = true;
			$this->add_render_attribute( '_wrapper', [ 'class' => 'sa-slider-item-link' ] );
		}

		$content_type = $settings['content_type'] ?? 'repeater';
		if ( 'posts' === $content_type ) {
			$items = $this->collect_post_items( $settings );
		} elseif ( 'acf' === $content_type ) {
			$items = $this->collect_acf_items( $settings );
		} else {
			$items = $settings['slider_list'] ?? [];
		}

		$this->render_header();
		?>

		<!-- Additional required wrapper -->
		<div class="swiper-wrapper">
			<?php foreach ( $items as $index => $item ) : ?>
				<?php $this->render_slide( $item, $index, $settings, $item_link_on ); ?>
			<?php endforeach; ?>
		</div>

		<?php
		if ( 'yes' === $settings['show_navigation'] ) :
			$this->render_navigation();
		endif;

		if ( 'yes' === $settings['show_pagination'] ) :
			$this->render_pagination();
		endif;

		$this->render_footer();
	}
}

<?php

namespace Sky_Addons\Modules\GlorySlider\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Embed;
use Elementor\Widget_Base;
use Elementor\Utils;
use Elementor\Modules\DynamicTags\Module as TagsModule;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Glory_Slider extends Widget_Base {

	public function get_name() {
		return 'sky-glory-slider';
	}

	public function get_title() {
		return esc_html__( 'Glory Slider (Image / Video)', 'sky-elementor-addons' );
	}

	public function get_icon() {
		return 'sky-icon-glory-slider';
	}

	public function get_categories() {
		return [ 'sky-elementor-addons' ];
	}

	public function get_keywords() {
		return [ 'sky', 'glory', 'video', 'gallery', 'creative', 'slider' ];
	}

	public function get_style_depends() {
		if ( sky_addons_editor_mode() ) {
			return [ 'swiper', 'elementor-icons-fa-solid', 'sky-addons-styles' ];
		}

		return [ 'swiper', 'elementor-icons-fa-solid', 'sa-glory-slider' ];
	}

	public function get_script_depends() {
		if ( sky_addons_editor_mode() ) {
			return [ 'swiper', 'sky-addons-scripts' ];
		}

		return [ 'swiper', 'sa-glory-slider' ];
	}

	public function get_custom_help_url() {
		return 'https://skyaddons.com/docs/sky-addons/audio-video-widgets/glory-slider/';
	}

	public function has_widget_inner_wrapper(): bool {
		return ! \Elementor\Plugin::$instance->experiments->is_feature_active( 'e_optimized_markup' );
	}

	protected function register_controls() {

		$this->start_controls_section(
			'section_glory_layout',
			[
				'label' => esc_html__( 'Glory Video Slider', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'slider_note',
			[
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => esc_html__( 'Note: We recommend that you select full width for your Section and disable any gaps between Columns.', 'sky-elementor-addons' ),
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',

			]
		);

		$this->add_responsive_control(
			'slider_size',
			[
				'label'       => esc_html__( 'Size Height', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::SLIDER,
				'size_units'  => [ 'px', 'em', 'vh' ],
				'range'       => [
					'px' => [
						'min' => 400,
						'max' => 1000,
					],
				],
				'selectors'   => [
					'{{WRAPPER}} .sa-glory-slider' => 'height: {{SIZE}}{{UNIT}}; --sa-glory-slider-size: {{SIZE}}{{UNIT}};',
				],
				'render_type' => 'template',
			]
		);

		$repeater = new Repeater();

		$repeater->start_controls_tabs( 'video_tabs' );

		$repeater->start_controls_tab(
			'tab_video',
			[
				'label' => esc_html__( 'Video', 'sky-elementor-addons' ),
			]
		);

		$repeater->add_control(
			'video_type',
			[
				'label'   => esc_html__( 'Source', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'youtube',
				'options' => [
					'youtube'     => esc_html__( 'YouTube', 'sky-elementor-addons' ),
					'vimeo'       => esc_html__( 'Vimeo', 'sky-elementor-addons' ),
					'dailymotion' => esc_html__( 'Dailymotion', 'sky-elementor-addons' ),
					'hosted'      => esc_html__( 'Self Hosted', 'sky-elementor-addons' ),
				],
			]
		);

		$repeater->add_control(
			'youtube_url',
			[
				'label'       => esc_html__( 'Link', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'dynamic'     => [
					'active' => true,
					'categories' => [
						TagsModule::POST_META_CATEGORY,
						TagsModule::URL_CATEGORY,
					],
				],
				'placeholder' => esc_html__( 'Enter your URL', 'sky-elementor-addons' ) . ' (YouTube)',
				'default'     => 'https://youtu.be/aqz-KE-bpKQ',
				'label_block' => true,
				'condition'   => [
					'video_type' => 'youtube',
				],
			]
		);

		$repeater->add_control(
			'vimeo_url',
			[
				'label'       => esc_html__( 'Link', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'dynamic'     => [
					'active' => true,
					'categories' => [
						TagsModule::POST_META_CATEGORY,
						TagsModule::URL_CATEGORY,
					],
				],
				'placeholder' => esc_html__( 'Enter your URL', 'sky-elementor-addons' ) . ' (Vimeo)',
				'default'     => 'https://vimeo.com/226020936',
				'label_block' => true,
				'condition'   => [
					'video_type' => 'vimeo',
				],
			]
		);

		$repeater->add_control(
			'dailymotion_url',
			[
				'label'       => esc_html__( 'Link', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'dynamic'     => [
					'active' => true,
					'categories' => [
						TagsModule::POST_META_CATEGORY,
						TagsModule::URL_CATEGORY,
					],
				],
				'placeholder' => esc_html__( 'Enter your URL', 'sky-elementor-addons' ) . ' (Dailymotion)',
				'default'     => 'https://www.dailymotion.com/video/x6tqhqb',
				'label_block' => true,
				'condition'   => [
					'video_type' => 'dailymotion',
				],
			]
		);

		$repeater->add_control(
			'external_url_set',
			[
				'label' => esc_html__( 'External URL', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::SWITCHER,
				'condition' => [
					'video_type' => 'hosted',
				],
			]
		);

		$repeater->add_control(
			'external_url',
			[
				'label'        => esc_html__( 'URL', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::URL,
				'autocomplete' => false,
				'options'      => false,
				'label_block'  => true,
				'show_label'   => false,
				'dynamic'      => [
					'active' => true,
					'categories' => [
						TagsModule::POST_META_CATEGORY,
						TagsModule::URL_CATEGORY,
					],
				],
				'media_type'   => 'video',
				'placeholder'  => esc_html__( 'Enter your URL', 'sky-elementor-addons' ),
				'condition'    => [
					'video_type'       => 'hosted',
					'external_url_set' => 'yes',
				],
			]
		);

		$repeater->add_control(
			'hosted_url',
			[
				'label'      => esc_html__( 'Choose File', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::MEDIA,
				'dynamic'    => [
					'active' => true,
					'categories' => [
						TagsModule::MEDIA_CATEGORY,
					],
				],
				'media_type' => 'video',
				'condition'  => [
					'video_type'        => 'hosted',
					'external_url_set!' => 'yes',
				],
			]
		);

		$repeater->end_controls_tab();

		$repeater->start_controls_tab(
			'tab_content',
			[
				'label' => esc_html__( 'Content', 'sky-elementor-addons' ),
			]
		);

		$repeater->add_control(
			'title',
			[
				'label'       => esc_html__( 'Title', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXTAREA,
				'default'     => esc_html__( 'List Title', 'sky-elementor-addons' ),
				'label_block' => true,
				'rows'        => 4,
				'dynamic'     => [ 'active' => true ],
			]
		);

		$repeater->add_control(
			'credit',
			[
				'label'       => esc_html__( 'Credit / Subtitle', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'by John Doe', 'sky-elementor-addons' ),
				'label_block' => true,
				'dynamic'     => [ 'active' => true ],
			]
		);

		$repeater->add_control(
			'add_credit_url',
			[
				'label' => esc_html__( 'Add Credit URL', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::SWITCHER,
			]
		);

		$repeater->add_control(
			'credit_url',
			[
				'label'         => esc_html__( 'Credit URL', 'sky-elementor-addons' ),
				'type'          => Controls_Manager::URL,
				'placeholder'   => esc_html__( 'https://your-link.com', 'sky-elementor-addons' ),
				'show_external' => true,
				'default'       => [
					'url'         => '',
					'is_external' => false,
				],
				'dynamic'       => [ 'active' => true ],
				'condition'     => [ 'add_credit_url' => 'yes' ],
			]
		);

		$repeater->end_controls_tab();

		$repeater->start_controls_tab(
			'tab_poster',
			[
				'label' => esc_html__( 'Thumbnail', 'sky-elementor-addons' ),
			]
		);

		$repeater->add_control(
			'poster',
			[
				'label'       => esc_html__( 'Thumbnail', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::MEDIA,
				'dynamic'     => [ 'active' => true ],
				'label_block' => true,
			]
		);

		$repeater->end_controls_tab();

		$repeater->end_controls_tabs();

		$this->add_control(
			'video_list',
			[
				'label'       => '',
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'separator'   => 'before',
				'default'     => [
					[
						'title'  => esc_html__( 'Youtube Video Title #1', 'sky-elementor-addons' ),
						'credit' => esc_html__( 'by John Doe', 'sky-elementor-addons' ),
					],
					[
						'title'      => esc_html__( 'Vimeo Video Title #2', 'sky-elementor-addons' ),
						'credit'     => esc_html__( 'by Mark Doe', 'sky-elementor-addons' ),
						'video_type' => 'vimeo',
					],
					[
						'title'      => esc_html__( 'Dailymotion Video Title #3', 'sky-elementor-addons' ),
						'credit'     => esc_html__( 'by Fatin Rushdi ', 'sky-elementor-addons' ),
						'video_type' => 'dailymotion',
					],
					[
						'title'  => esc_html__( 'Remote Video Title #4', 'sky-elementor-addons' ),
						'credit' => esc_html__( 'by Mike Ross', 'sky-elementor-addons' ),
					],
					[
						'title'  => esc_html__( 'Youtube Video Title #5', 'sky-elementor-addons' ),
						'credit' => esc_html__( 'by John Doe', 'sky-elementor-addons' ),
					],
					[
						'title'  => esc_html__( 'Youtube Video Title #6', 'sky-elementor-addons' ),
						'credit' => esc_html__( 'by Mike Doe', 'sky-elementor-addons' ),
					],
					[
						'title'      => esc_html__( 'Youtube Video Title #7', 'sky-elementor-addons' ),
						'credit'     => esc_html__( 'by Michael N. Chiles', 'sky-elementor-addons' ),
						'video_type' => 'dailymotion',
					],
				],
				'title_field' => '{{{ title }}}',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_player_settings',
			[
				'label' => esc_html__( 'Player Settings', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'speed',
			[
				'label' => esc_html__( 'Slide Speed (ms)', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min'  => 100,
						'max'  => 5000,
						'step' => 100,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 1200,
				],
			]
		);

		$this->add_control(
			'coverflow_toggle',
			[
				'label'        => esc_html__( 'Coverflow Effect', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::POPOVER_TOGGLE,
				'return_value' => 'yes',
				// 'condition'    => [
				// 'transition_effect' => 'coverflow'
				// ]
			]
		);

		$this->start_popover();

		$this->add_control(
			'coverflow_depth',
			[
				'label'       => esc_html__( 'Depth', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::SLIDER,
				'range'       => [
					'px' => [
						'min' => 1,
						'max' => 10,
					],
				],
				'default'     => [
					'unit' => 'px',
					'size' => 100,
				],
				'render_type' => 'template',
				'condition'   => [
					'coverflow_toggle' => 'yes',
				],
			]
		);

		$this->add_control(
			'coverflow_modifier',
			[
				'label'       => esc_html__( 'Modifier', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::SLIDER,
				'range'       => [
					'px' => [
						'min' => 1,
						'max' => 10,
					],
				],
				'default'     => [
					'unit' => 'px',
					'size' => 1,
				],
				'render_type' => 'template',
				'condition'   => [
					'coverflow_toggle' => 'yes',
				],
			]
		);

		$this->add_control(
			'coverflow_rotate',
			[
				'label'       => esc_html__( 'Rotate', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::SLIDER,
				'range'       => [
					'px' => [
						'min'  => -360,
						'max'  => 360,
						'step' => 5,
					],
				],
				'default'     => [
					'unit' => 'px',
					'size' => 50,
				],
				'render_type' => 'template',
				'condition'   => [
					'coverflow_toggle' => 'yes',
				],
			]
		);

		$this->add_control(
			'coverflow_stretch',
			[
				'label'       => esc_html__( 'Stretch', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::SLIDER,
				'range'       => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default'     => [
					'unit' => 'px',
					'size' => 0,
				],
				'render_type' => 'template',
				'condition'   => [
					'coverflow_toggle' => 'yes',
				],
			]
		);

		$this->add_control(
			'slide_shadows',
			[
				'label' => esc_html__( 'Slide Shadows', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::SWITCHER,
			]
		);

		$this->end_popover();

		$this->add_control(
			'autoplay',
			[
				'label'     => esc_html__( 'Autoplay', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'no',
				'separator' => 'before',
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
			'show_navigation',
			[
				'label'     => esc_html__( 'Show Navigation', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'separator' => 'before',
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

		$this->add_responsive_control(
			'player_content_position',
			[
				'label'           => esc_html__( 'Text Position', 'sky-elementor-addons' ),
				'type'            => Controls_Manager::SELECT,
				'label_block'     => false,
				'options' => [
					'top-left'      => esc_html__( 'Top Left', 'sky-elementor-addons' ),
					'top-center'    => esc_html__( 'Top Center', 'sky-elementor-addons' ),
					'top-right'     => esc_html__( 'Top Right', 'sky-elementor-addons' ),
					'bottom-left'   => esc_html__( 'Bottom Left', 'sky-elementor-addons' ),
					'bottom-center' => esc_html__( 'Bottom Center', 'sky-elementor-addons' ),
					'bottom-right'  => esc_html__( 'Bottom Right', 'sky-elementor-addons' ),
				],
				'desktop_default' => 'top-left',
				'tablet_default'  => 'top-left',
				'mobile_default'  => 'top-left',
				'style_transfer'  => true,
				'selectors' => [
					'{{WRAPPER}} .sa-glory-player .sa-player-content-wrapper' => '{{VALUE}};',
				],
				'selectors_dictionary' => [
					'top-left'      => 'top: 0%; left: 0; transform: translate(0%, 0%);right: auto;',
					'top-center'    => 'top: 0; left: 50%; transform: translate(-50%, 0%);',
					'top-right'     => 'top: 0%; right: 0; transform: translate(0%, 0%);left: auto;',
					'bottom-left'   => 'bottom: 0; left: 0%; transform: translate(0%, 0%);top: auto;',
					'bottom-center' => 'bottom: 0; left: 50%; transform: translate(-50%, 0%);top: auto;',
					'bottom-right'  => 'bottom: 0; right: 0%; transform: translate(0%, 0%);top: auto;left:auto;',
				],
			]
		);

		$this->add_responsive_control(
			'player_content_alignment',
			[
				'label' => esc_html__( 'Text Alignment', 'sky-elementor-addons' ),
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
					'{{WRAPPER}} .sa-glory-player .sa-player-content-wrapper' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'show_play_button_on_hover',
			[
				'label'        => esc_html__( 'Show Play Button On Hover', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'prefix_class' => 'sa-play-button-on-hover-',
			]
		);

		$this->add_control(
			'show_playlist',
			[
				'label'        => esc_html__( 'Show Playlist', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'sky-elementor-addons' ),
				'label_off'    => esc_html__( 'Hide', 'sky-elementor-addons' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'separator'    => 'before',
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
			'section_playlist_settings',
			[
				'label'     => esc_html__( 'Playlist Settings', 'sky-elementor-addons' ),
				'tab'       => Controls_Manager::TAB_CONTENT,
				'condition' => [ 'show_playlist' => 'yes' ],
			]
		);

		$this->add_responsive_control(
			'item_gap',
			[
				'label' => esc_html__( 'Item Gap', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::SLIDER,
				'default' => [
					'size' => 20,
				],
				'tablet_default' => [
					'size' => 20,
				],
				'mobile_default' => [
					'size' => 10,
				],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
			]
		);

		$this->add_control(
			'playlist_mouse_wheel',
			[
				'label'   => esc_html__( 'Mouse Wheel', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->end_controls_section();

		// $this->start_controls_section(
		// 'video_player_style',
		// [
		// 'label' => esc_html__('Video Player', 'sky-elementor-addons'),
		// 'tab'   => Controls_Manager::TAB_STYLE,
		// ]
		// );

		// $this->add_group_control(
		// Group_Control_Border::get_type(),
		// [
		// 'name'     => 'player_border',
		// 'label'    => esc_html__('Border', 'sky-elementor-addons'),
		// 'selector' => '{{WRAPPER}} .swiper-slide-active .sa-player-wrapper',
		// ]
		// );

		// $this->add_responsive_control(
		// 'player_border_radius',
		// [
		// 'label'      => esc_html__('Border Radius', 'sky-elementor-addons'),
		// 'type'       => Controls_Manager::DIMENSIONS,
		// 'size_units' => ['px', 'em', '%'],
		// 'selectors'  => [
		// '{{WRAPPER}} .swiper-slide-active .sa-player-wrapper' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow: hidden;',
		// ],
		// ]
		// );

		// $this->end_controls_section();

		$this->start_controls_section(
			'video_content_style',
			[
				'label' => esc_html__( 'Player Content', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'poster_overlay',
			[
				'label' => esc_html__( 'Poster Overlay', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .swiper-slide-active .sa-player-wrapper::after' => 'background: {{VALUE}}',
				],
			]
		);

		$this->add_responsive_control(
			'player_content_padding',
			[
				'label'      => esc_html__( 'Content Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-player-content-wrapper' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'video_title_heading',
			[
				'type'  => Controls_Manager::HEADING,
				'label' => esc_html__( 'T I T L E', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'video_title_color',
			[
				'label' => esc_html__( 'Text Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-player-title' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'video_title_background',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .sa-player-title',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'video_title_typography',
				'label'    => esc_html__( 'Typography', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-player-title',
			]
		);

		$this->add_responsive_control(
			'video_title_padding',
			[
				'label'      => esc_html__( 'Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-player-title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'video_title_text_shadow',
				'label'    => esc_html__( 'Text Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-player-title',
			]
		);

		$this->add_control(
			'video_credit_heading',
			[
				'type'  => Controls_Manager::HEADING,
				'label' => esc_html__( 'C R E D I T / S U B T I T L E', 'sky-elementor-addons' ),
			]
		);

		$this->add_responsive_control(
			'video_credit_spacing',
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
					'{{WRAPPER}} .sa-player-credit' => 'margin-top: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'video_credit_color',
			[
				'label' => esc_html__( 'Text Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-player-credit' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'video_credit_background',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .sa-player-credit',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'video_credit_typography',
				'label'    => esc_html__( 'Typography', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-player-credit',
			]
		);

		$this->add_responsive_control(
			'video_credit_padding',
			[
				'label'      => esc_html__( 'Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-player-credit' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'video_credit_text_shadow',
				'label'    => esc_html__( 'Text Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-player-credit',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_play_button_style',
			[
				'label' => esc_html__( 'Play Button', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'play_button_icon_size',
			[
				'label'      => esc_html__( 'Icon Size', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range'      => [
					'px' => [
						'min' => 6,
						'max' => 100,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-play-button' => 'font-size: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'play_button_padding',
			[
				'label'      => esc_html__( 'Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-play-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'play_button_border',
				'label'    => esc_html__( 'Border', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-play-button',
			]
		);

		$this->add_responsive_control(
			'play_button_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-play-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow: hidden;',
				],
			]
		);

		$this->start_controls_tabs( 'tabs_play_button_style' );

		$this->start_controls_tab(
			'tab_play_button_normal',
			[
				'label' => esc_html__( 'Normal', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'play_button_color',
			[
				'label' => esc_html__( 'Text Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-play-button' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'play_button_background',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .sa-play-button',
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'play_button_text_shadow',
				'label'    => esc_html__( 'Text Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-play-button',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'play_button_box_shadow',
				'label'    => esc_html__( 'Box Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-play-button',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_play_button_hover',
			[
				'label' => esc_html__( 'Hover', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'play_button_color_hover',
			[
				'label' => esc_html__( 'Text Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-play-button:hover' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'play_button_background_hover',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .sa-play-button:hover',
			]
		);

		$this->add_control(
			'play_button_border_color_hover',
			[
				'label' => esc_html__( 'Border Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-play-button:hover' => 'border-color: {{VALUE}};',
				],
				'condition' => [
					'play_button_border_border!' => '',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'play_button_text_shadow_hover',
				'label'    => esc_html__( 'Text Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-play-button:hover',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'play_button_box_shadow_hover',
				'label'    => esc_html__( 'Box Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-play-button:hover',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		$this->start_controls_section(
			'section_thumbs_style',
			[
				'label' => esc_html__( 'Thumbnail', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'thumbs_padding',
			[
				'label'      => esc_html__( 'Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-thumb-wrapper' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		// $this->add_group_control(
		// Group_Control_Background::get_type(),
		// [
		// 'name'     => 'playlist_navigation_background',
		// 'label'    => esc_html__('Background', 'sky-elementor-addons'),
		// 'types'    => ['classic', 'gradient'],
		// 'selector' => '{{WRAPPER}} .sa-video-gallery-playlist .sa-playlist-prev, {{WRAPPER}} .sa-video-gallery-playlist .sa-playlist-next',
		// ]
		// );

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'thumbs_border',
				'label'    => esc_html__( 'Border', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-thumb-wrapper',
			]
		);

		$this->add_responsive_control(
			'thumbs_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-thumb-wrapper' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow: hidden;',
				],
			]
		);

		$this->add_control(
			'play_button_border_color_active',
			[
				'label'     => esc_html__( 'Border Color Active', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-glory-thumbs .swiper-slide.swiper-slide-active .sa-thumb-wrapper' => 'border-color: {{VALUE}};',
				],
				// 'condition' => [
				// 'thumbs_border_border!' => '',
				// ],
				'separator' => 'before',
			]
		);

		$this->end_controls_section();

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
			'navigation_size',
			[
				'label'      => esc_html__( 'Size', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range' => [
					'px' => [
						'min' => 5,
						'max' => 50,
					],
				],
				'selectors' => [
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
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors' => [
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
				'selectors' => [
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
				'selectors' => [
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
				'selectors' => [
					'{{WRAPPER}} .sa-swiper-button-prev:hover, {{WRAPPER}} .sa-swiper-button-next:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	protected function re_arrange_video_url( $video_url = [] ) {
		$video_url = Embed::get_embed_url( $video_url, [ 'autplay' => 1 ], [] );
		return $video_url;
	}

	protected function get_video_thum( $video_type, $video_url ) {
		$thumb_url        = '';
		$video_properties = Embed::get_video_properties( $video_url );
		$video_id         = isset( $video_properties['video_id'] ) ? $video_properties['video_id'] : false;

		if ( 'youtube' === $video_type ) {
			$thumb_url = '//img.youtube.com/vi/' . $video_id . '/0.jpg';
		} elseif ( 'dailymotion' === $video_type ) {
			$thumb_url = '//www.dailymotion.com/thumbnail/video/' . $video_id;
		} elseif ( 'vimeo' === $video_type ) {
			$thumb_url = '//vumbnail.com/' . $video_id . '.jpg';
		} else {
			$thumb_url = Utils::get_placeholder_image_src();
		}

		return $thumb_url;
	}

	protected function get_playlist_settings( $viewport_md, $viewport_lg ) {
		$settings = $this->get_settings_for_display();

		$item_gap        = ! empty( $settings['item_gap']['size'] ) || ( 0 === $settings['item_gap']['size'] ) ? (int) $settings['item_gap']['size'] : 16;
		$item_gap_tablet = ! empty( $settings['item_gap_tablet']['size'] ) || ( 0 === $settings['item_gap']['size'] ) ? (int) $settings['item_gap_tablet']['size'] : 16;
		$item_gap_mobile = ! empty( $settings['item_gap_mobile']['size'] ) || ( 0 === $settings['item_gap']['size'] ) ? (int) $settings['item_gap_mobile']['size'] : 10;

		$speed = ( ! empty( $settings['speed']['size'] ) ) ? $settings['speed']['size'] : 1200;

		return [
			'slidesPerView'         => 'auto',
			'spaceBetween'          => $item_gap_mobile,
			'centeredSlides'        => true,
			'freeMode'              => false,
			'loop'                  => true,
			'speed'                 => $speed,
			'loopedSlides'          => 10,
			'watchSlidesVisibility' => true,
			'watchSlidesProgress'   => true,
			'slideToClickedSlide'   => true,
			'mousewheel'            => ( 'yes' === $settings['playlist_mouse_wheel'] ) ? true : false,
			'breakpoints'           => [
				(int) $viewport_md => [
					'spaceBetween' => $item_gap_tablet,
				],
				(int) $viewport_lg => [
					'spaceBetween' => $item_gap,
				],
			],
		];
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$id       = 'sa-glory-slider-' . $this->get_id();

		$swiper_breakpoints = sky_addons_get_swiper_breakpoints();
		$viewport_lg        = $swiper_breakpoints['lg'];
		$viewport_md        = $swiper_breakpoints['md'];

		$speed = ( ! empty( $settings['speed']['size'] ) ) ? $settings['speed']['size'] : 1200;

		if ( 'yes' !== $settings['show_playlist'] ) {
			$this->add_render_attribute( 'player-settings', 'class', 'sa-playlist-hidden' );
		}

		$this->add_render_attribute(
			[
				'player-settings' => [
					'class' => 'sa-glory-slider',
					'id'    => $id,
					'data-player-settings' => [
						wp_json_encode( array_filter( [
							'autoplay' => 'yes' === $settings['autoplay'] ? [
								'delay' => $settings['autoplay_speed']['size'] * 1000,
							] : false,
							'loop'           => ( 'yes' === $settings['loop'] ) ? true : false,
							'effect'         => 'coverflow',
							'slidesPerView'  => 1.2,
							// 'touchRatio'          => 0.8,
							// 'slideToClickedSlide' => true,
							'centeredSlides' => true,
							'speed'          => $speed,
							'loopedSlides'   => 10,
							'spaceBetween'   => 0,
							'freeMode'       => false,
							'lazy'           => true,
							'parallax'       => true,
							'observer'       => true,
							'observeParents' => true,
							'coverflowEffect' => [
								// `||`, not `&&` — see the same fix in traits/global-swiper-controls.php.
								// With `&&` the guard is unsatisfiable (`! empty( 0 )` is false, `0 === 900`
								// is false), so Depth and Modifier always fell through to the defaults.
								'depth'        => ( 'yes' === $settings['coverflow_toggle'] && ( ! empty( $settings['coverflow_depth']['size'] ) || 0 === $settings['coverflow_depth']['size'] ) ) ? $settings['coverflow_depth']['size'] : 900,
								'modifier'     => ( 'yes' === $settings['coverflow_toggle'] && ( ! empty( $settings['coverflow_modifier']['size'] ) || 0 === $settings['coverflow_modifier']['size'] ) ) ? $settings['coverflow_modifier']['size'] : 1,
								'rotate'       => ( 'yes' === $settings['coverflow_toggle'] && ( ! empty( $settings['coverflow_rotate']['size'] ) || 0 === $settings['coverflow_rotate']['size'] ) ) ? $settings['coverflow_rotate']['size'] : 30,
								'stretch'      => ( 'yes' === $settings['coverflow_toggle'] && ( ! empty( $settings['coverflow_stretch']['size'] ) || 0 === $settings['coverflow_stretch']['size'] ) ) ? $settings['coverflow_stretch']['size'] : 20,

								'slideShadows' => ( isset( $settings['slide_shadows'] ) && 'yes' === $settings['slide_shadows'] ) ? true : false,
							],
							'navigation' => [
								'nextEl' => "#$id .sa-swiper-button-next",
								'prevEl' => "#$id .sa-swiper-button-prev",
							],
							'breakpoints' => [
								(int) $viewport_md => [
									'slidesPerView' => 1.4,
								],
								(int) $viewport_lg => [
									'slidesPerView' => 1.7,
								],
							],
						] ) ),
					],
				],
			]
		);

		if ( 'yes' === $settings['show_playlist'] ) {
			$this->add_render_attribute( 'player-settings', 'data-thumbs-settings', [ wp_json_encode( array_filter( $this->get_playlist_settings( $viewport_md, $viewport_lg ) ) ) ] );
		}
		?>
		<div <?php $this->print_render_attribute_string( 'player-settings' ); ?>>

			<div class="swiper sa-glory-player">
				<div class="swiper-wrapper">
					<?php
					foreach ( $settings['video_list'] as $index => $item ) :

						$poster = $item['poster']['url'];

						if ( 'youtube' === $item['video_type'] ) {
							$video_url = $this->re_arrange_video_url( $item['youtube_url'] );
							$poster    = ( empty( $poster ) ) ? $this->get_video_thum( 'youtube', $item['youtube_url'] ) : $poster;
						} elseif ( 'vimeo' === $item['video_type'] ) {
							$video_url = $this->re_arrange_video_url( $item['vimeo_url'] );
							$poster    = ( empty( $poster ) ) ? $this->get_video_thum( 'vimeo', $item['vimeo_url'] ) : $poster;
						} elseif ( 'dailymotion' === $item['video_type'] ) {
							$video_url = $this->re_arrange_video_url( $item['dailymotion_url'] );
							$poster    = ( empty( $poster ) ) ? $this->get_video_thum( 'dailymotion', $item['dailymotion_url'] ) : $poster;
						} elseif ( 'hosted' === $item['video_type'] ) {
							if ( 'yes' === $item['external_url_set'] ) {
								$video_url = $item['external_url']['url'];
							} else {
								$video_url = $item['hosted_url']['url'];
							}
							$poster = ( empty( $poster ) ) ? $this->get_video_thum( 'hosted', '' ) : $poster;
						} else {
							$video_url = '';
						}
						?>
						<div class="swiper-slide">
							<div class="sa-player-wrapper sa-rounded">

								<img class="sa-player-poster" src="<?php echo esc_url( $poster ); ?>"
									alt="<?php echo esc_attr( ( isset( $item['poster']['alt'] ) && ! empty( $item['poster']['alt'] ) ) ? $item['poster']['alt'] : $item['title'] ); ?>" />

								<?php if ( ! empty( $video_url ) ) : ?>
									<div class="sa-play-button-wrapper">
										<a class="sa-play-button sa-icon-wrap sa-text-decoration-none sa-d-flex sa-justify-content-center sa-align-content-center sa-p-3"
											href="javascript:void(0);" data-src="<?php echo esc_url( $video_url ); ?>">
											<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
												<path d="M8 5v14l11-7z" />
											</svg>
										</a>
									</div>
								<?php endif; ?>

								<div class="sa-player-content-wrapper">
									<?php
									if ( 'yes' === $settings['show_title'] ) :
										printf( '<%s class="sa-player-title sa-fw-bold sa-m-0">%s</%s>',
											esc_attr( Utils::validate_html_tag( $settings['title_tag'] ) ),
											wp_kses_post( $item['title'] ),
											esc_attr( Utils::validate_html_tag( $settings['title_tag'] ) )
										);
									endif;

									if ( ! empty( $item['credit_url']['url'] ) ) {
										$target     = $item['credit_url']['is_external'] ? '_blank' : '_self';
										$nofollow   = $item['credit_url']['nofollow'] ? ' rel="nofollow"' : '';
										$credit_url = ! empty( $item['credit_url']['url'] ) ? $item['credit_url']['url'] : 'javascript:void(0);';
									} else {
										$target     = 'target="_self"';
										$nofollow   = '';
										$credit_url = 'javascript:void(0);';
									}
									?>
									<a class="sa-player-credit sa-d-inline-block sa-text-decoration-none"
										href="<?php echo esc_url( $credit_url ); ?>" target="<?php echo esc_attr( $target ); ?>" <?php echo wp_kses_post( $nofollow ); ?>>
										<?php echo wp_kses_post( $item['credit'] ); ?>
									</a>
								</div>

								<div class="sa-video-player">
									<iframe src="about:blank" class="sa-player-iframe" allow="autoplay;" frameborder="no"
										webkitallowfullscreen="true" mozallowfullscreen="true" allowfullscreen="true"></iframe>
								</div>

							</div>

						</div>
					<?php endforeach; ?>
				</div>
				<?php
				if ( 'yes' === $settings['show_navigation'] ) :
					$this->render_navigation();
					endif;
				?>
								</div>

			<?php if ( 'yes' === $settings['show_playlist'] ) : ?>
			<div class="swiper sa-glory-thumbs">
				<div class="swiper-wrapper">
					<?php
					foreach ( $settings['video_list'] as $index => $item ) :
						$poster = $item['poster']['url'];

						if ( 'youtube' === $item['video_type'] ) {
							$poster = ( empty( $poster ) ) ? $this->get_video_thum( 'youtube', $item['youtube_url'] ) : $poster;
						} elseif ( 'vimeo' === $item['video_type'] ) {
							$poster = ( empty( $poster ) ) ? $this->get_video_thum( 'vimeo', $item['vimeo_url'] ) : $poster;
						} elseif ( 'dailymotion' === $item['video_type'] ) {
							$poster = ( empty( $poster ) ) ? $this->get_video_thum( 'dailymotion', $item['dailymotion_url'] ) : $poster;
						} elseif ( 'hosted' === $item['video_type'] ) {
							$poster = ( empty( $poster ) ) ? $this->get_video_thum( 'hosted', '' ) : $poster;
						} else {
						}
						?>
						<div class="swiper-slide">
							<div class="sa-thumb-wrapper sa-rounded">
								<img src="<?php echo esc_url( $poster ); ?>"
									alt="<?php echo esc_attr( ( isset( $item['poster']['alt'] ) && ! empty( $item['poster']['alt'] ) ) ? $item['poster']['alt'] : $item['title'] ); ?>" />
							</div>
						</div>
					<?php endforeach; ?>
				</div>

			</div>
			<?php endif; ?>

		</div>

		<?php
	}

	protected function render_navigation() {
		$settings = $this->get_settings_for_display();
		?>
			<!-- If we need navigation buttons -->
			<div class="sa-swiper-button-prev sa-slider-navigation sa-icon-wrap">
				<?php
				if ( ! empty( $settings['prev_icon']['value'] ) ) :
					Icons_Manager::render_icon($settings['prev_icon'], [
						'aria-hidden' => 'true',
						'class'       => 'fa-fw',
					]);
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
					Icons_Manager::render_icon($settings['next_icon'], [
						'aria-hidden' => 'true',
						'class'       => 'fa-fw',
					]);
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
}

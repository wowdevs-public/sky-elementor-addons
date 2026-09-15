<?php

namespace Sky_Addons\Modules\StellarSlider\Widgets;

use Elementor\Utils;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Css_Filter;
use Elementor\Group_Control_Text_Stroke;
use Elementor\Icons_Manager;
use Elementor\Repeater;
use Elementor\Widget_Base;

use Sky_Addons\Includes\Controls\GroupQuery\Group_Control;
use Sky_Addons\Traits\Global_Widget_Functions;
use Sky_Addons\Traits\Global_Widget_Controls;


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Stellar_Slider extends Widget_Base {

	use Group_Control;
	use Global_Widget_Functions;
	use Global_Widget_Controls;

	private $_query = null;

	public function get_name() {
		return 'sky-stellar-slider';
	}

	public function get_title() {
		return esc_html__( 'Stellar Blog Slider', 'sky-elementor-addons' );
	}

	public function get_icon() {
		return 'sky-icon-stellar-slider';
	}

	public function get_categories() {
		return [ 'sky-elementor-addons' ];
	}

	public function get_keywords() {
		return [ 'sky', 'blog', 'stellar', 'slider' ];
	}

	public function get_style_depends() {
		if ( sky_addons_editor_mode() ) {
			return [ 'swiper', 'elementor-icons-fa-solid', 'sky-addons-styles' ];
		}

		return [ 'swiper', 'elementor-icons-fa-solid', 'sa-stellar-slider' ];
	}

	public function get_script_depends() {
		if ( sky_addons_editor_mode() ) {
			return [ 'swiper', 'sky-addons-scripts' ];
		}

		return [ 'swiper', 'sa-stellar-slider' ];
	}

	public function get_query() {
		return $this->_query;
	}

	public function get_custom_help_url() {
		return 'https://skyaddons.com/docs/sky-addons/carousel-slider/stellar-blog-slider/';
	}

	public function has_widget_inner_wrapper(): bool {
		return ! \Elementor\Plugin::$instance->experiments->is_feature_active( 'e_optimized_markup' );
	}

	protected function register_controls() {

		$this->start_controls_section(
			'section_layout',
			[
				'label' => esc_html__( 'Layout', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'slider_aspect_ratio',
			[
				'label'        => esc_html__( 'Aspect Ratio', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SELECT,
				'default'      => '169',
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
				'prefix_class' => 'sa-ss-ratio-yes sa-ratio-',
				'render_type'  => 'template',
			]
		);

		$this->add_responsive_control(
			'height',
			[
				'label'       => esc_html__( 'Min Height', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::SLIDER,
				'size_units'  => [ 'px', 'vh' ],
				'default' => [
					'size' => 700,
					'unit' => 'px',
				],
				'range' => [
					'px' => [
						'min' => 200,
						'max' => 1200,
					],
					'vh' => [
						'min' => 20,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .swiper' => 'height: {{SIZE}}{{UNIT}}; min-height: {{SIZE}}{{UNIT}};',
				],
				'render_type' => 'template',
			]
		);

		$this->add_group_control(
			Group_Control_Image_Size::get_type(),
			[
				'name'    => 'primary_thumbnail',
				'exclude' => [ 'custom' ],
				'default' => 'full',
			]
		);

		$this->add_responsive_control(
			'content_alignment',
			[
				'label'           => esc_html__( 'Content Alignment', 'sky-elementor-addons' ),
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
						'title' => esc_html__( 'Justified', 'sky-elementor-addons' ),
						'icon'  => 'eicon-text-align-justify',
					],
				],
				'toggle'          => true,
				'desktop_default' => 'left',
				'tablet_default'  => 'left',
				'mobile_default'  => 'left',
				// 'prefix_class'         => 'sa-ss-%s-',
				'style_transfer'  => true,
				'selectors' => [
					'{{WRAPPER}} .sa-content-container' => '{{VALUE}};',
					'{{WRAPPER}} .sa-post-meta'         => '{{VALUE}};',
					'{{WRAPPER}} .sa-buttons-wrapper'   => '{{VALUE}};',
				],
				'selectors_dictionary' => [
					'left'    => 'text-align: left; justify-content: flex-start; align-items: left;',
					'center'  => 'text-align: center; justify-content: center; align-items: center;',
					'right'   => 'text-align: right; justify-content: right; align-items: flex-end;',
					'justify' => 'text-align: left; justify-content: left; align-items: flex-start;',
				],
			]
		);

		$this->add_control(
			'show_social_icons',
			[
				'label'   => esc_html__( 'Show Social Icons', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_motion',
			[
				'label' => esc_html__( 'Motion', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.0.0' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'image_animation',
			[
				'label'        => esc_html__( 'Image Animation', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SELECT,
				'default'      => 'none',
				'options' => [
					'none'     => esc_html__( 'None', 'sky-elementor-addons' ),
					'zoom-in'  => esc_html__( 'Ken Burns — Zoom In', 'sky-elementor-addons' ),
					'zoom-out' => esc_html__( 'Ken Burns — Zoom Out', 'sky-elementor-addons' ),
				],
				'prefix_class' => 'sa-ss-img-',
				'render_type'  => 'template',
			]
		);

		$this->add_control(
			'image_animation_duration',
			[
				'label'     => esc_html__( 'Image Animation Duration (sec)', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min'  => 3,
						'max'  => 25,
						'step' => .5,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 8,
				],
				'selectors' => [
					'{{WRAPPER}}' => '--sa-ss-img-duration: {{SIZE}}s;',
				],
				'condition' => [ 'image_animation!' => 'none' ],
			]
		);

		$this->add_control(
			'content_animation',
			[
				'label'        => esc_html__( 'Content Animation', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SELECT,
				'default'      => 'none',
				'separator'    => 'before',
				'options' => [
					'none'     => esc_html__( 'None', 'sky-elementor-addons' ),
					'fade-up'  => esc_html__( 'Fade Up', 'sky-elementor-addons' ),
					'slide-up' => esc_html__( 'Slide Up', 'sky-elementor-addons' ),
				],
				'description'  => esc_html__( 'Replaces the built-in parallax motion with a staggered entrance on the active slide.', 'sky-elementor-addons' ),
				'prefix_class' => 'sa-ss-anim-',
				'render_type'  => 'template',
			]
		);

		$this->add_control(
			'content_animation_duration',
			[
				'label'     => esc_html__( 'Animation Duration (sec)', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min'  => .2,
						'max'  => 3,
						'step' => .1,
					],
				],
				'default'   => [
					'unit' => 'px',
					'size' => .8,
				],
				'selectors' => [
					'{{WRAPPER}}' => '--sa-ss-anim-duration: {{SIZE}}s;',
				],
				'condition' => [ 'content_animation!' => 'none' ],
			]
		);

		$this->add_control(
			'content_animation_stagger',
			[
				'label'     => esc_html__( 'Stagger Delay (ms)', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min'  => 0,
						'max'  => 500,
						'step' => 10,
					],
				],
				'default'   => [
					'unit' => 'px',
					'size' => 120,
				],
				'selectors' => [
					'{{WRAPPER}}' => '--sa-ss-anim-stagger: {{SIZE}}ms;',
				],
				'condition' => [ 'content_animation!' => 'none' ],
			]
		);

		$this->add_control(
			'content_animation_easing',
			[
				'label'     => esc_html__( 'Easing', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'cubic-bezier(.16,1,.3,1)',
				'options'   => [
					'cubic-bezier(.16,1,.3,1)'      => esc_html__( 'Smooth (Expo Out)', 'sky-elementor-addons' ),
					'cubic-bezier(.25,.46,.45,.94)' => esc_html__( 'Gentle', 'sky-elementor-addons' ),
					'cubic-bezier(.34,1.56,.64,1)'  => esc_html__( 'Overshoot', 'sky-elementor-addons' ),
					'ease-out'                      => esc_html__( 'Ease Out', 'sky-elementor-addons' ),
					'linear'                        => esc_html__( 'Linear', 'sky-elementor-addons' ),
				],
				'selectors' => [
					'{{WRAPPER}}' => '--sa-ss-anim-ease: {{VALUE}};',
				],
				'condition' => [ 'content_animation!' => 'none' ],
			]
		);

		$this->add_control(
			'parallax_effect',
			[
				'label'     => esc_html__( 'Parallax Content', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'separator' => 'before',
				'condition' => [ 'content_animation' => 'none' ],
			]
		);

		$this->end_controls_section();

		/**
		 * Global Query Builder Settings
		 */
		$this->start_controls_section(
			'section_post_query_builder',
			[
				'label' => esc_html__( 'Query', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->register_query_builder_controls();

		$this->update_control(
			'posts_per_page',
			[
				'default' => 4,
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_social_icon',
			[
				'label' => esc_html__( 'Social Icons', 'sky-elementor-addons' ),
				'condition' => [
					'show_social_icons' => 'yes',
				],
			]
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'social_icon',
			[
				'label' => esc_html__( 'Icon', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::ICONS,
				'default' => [
					'value'   => 'fab fa-facebook-f',
					'library' => 'fa-brands',
				],
				'recommended' => [
					'fa-brands' => [
						'android',
						'apple',
						'behance',
						'bitbucket',
						'codepen',
						'delicious',
						'deviantart',
						'digg',
						'dribbble',
						'sky-elementor-addons',
						'facebook',
						'facebook-f',
						'flickr',
						'foursquare',
						'free-code-camp',
						'github',
						'gitlab',
						'globe',
						'houzz',
						'instagram',
						'jsfiddle',
						'linkedin',
						'linkedin-in',
						'medium',
						'meetup',
						'mix',
						'mixcloud',
						'odnoklassniki',
						'pinterest',
						'product-hunt',
						'reddit',
						'shopping-cart',
						'skype',
						'slideshare',
						'snapchat',
						'soundcloud',
						'spotify',
						'stack-overflow',
						'steam',
						'telegram',
						'thumb-tack',
						'tripadvisor',
						'tumblr',
						'twitch',
						'twitter',
						'viber',
						'vimeo',
						'vk',
						'weibo',
						'weixin',
						'whatsapp',
						'wordpress',
						'xing',
						'yelp',
						'youtube',
						'500px',
					],
					'fa-solid'  => [
						'envelope',
						'link',
						'rss',
					],
				],
			]
		);

		$repeater->add_control(
			'link',
			[
				'label'       => esc_html__( 'Link', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::URL,
				'default'     => [
					'is_external' => 'true',
				],
				'dynamic'     => [
					'active' => true,
				],
				'placeholder' => esc_html__( 'https://your-link.com', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'social_icon_list',
			[
				'label'       => esc_html__( 'Social Icons', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => [
					[
						'social_icon' => [
							'value'   => 'fab fa-facebook-f',
							'library' => 'fa-brands',
						],
					],
					[
						'social_icon' => [
							'value'   => 'fab fa-twitter',
							'library' => 'fa-brands',
						],
					],
					[
						'social_icon' => [
							'value'   => 'fab fa-youtube',
							'library' => 'fa-brands',
						],
					],
				],
				'title_field' => '{{{ elementor.helpers.renderIcon( this, social_icon, {}, "i", "panel" ) || \'<i class="{{ icon }}" aria-hidden="true"></i>\' }}}<# print(elementor.helpers.getSocialNetworkNameFromIcon( social_icon )); #>',
			]
		);

		$this->add_control(
			'social_icon_default_color',
			[
				'label'        => esc_html__( 'Default Color', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'prefix_class' => 'sa-social-default-color-',
				'render_type'  => 'template',
			]
		);

		$this->add_responsive_control(
			'social_icons_visibility',
			[
				'label'           => esc_html__( 'Visibility', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.0.0' ),
				'type'            => Controls_Manager::SELECT,
				'options' => [
					'flex' => esc_html__( 'Show', 'sky-elementor-addons' ),
					'none' => esc_html__( 'Hide', 'sky-elementor-addons' ),
				],
				'desktop_default' => 'flex',
				'tablet_default'  => 'none',
				'mobile_default'  => 'none',
				'separator'       => 'before',
				'selectors' => [
					'{{WRAPPER}} .sky-social-icons' => 'display: {{VALUE}};',
				],
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
			'title_tag',
			[
				'label'   => esc_html__( 'Title HTML Tag', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'h3',
				'options' => sky_addons_title_tags(),
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
				'default' => 'yes',
			]
		);

		$this->add_control(
			'show_excerpt',
			[
				'label'     => esc_html__( 'Show Text', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'separator' => 'before',
			]
		);

		$this->add_control(
			'excerpt_length',
			[
				'label'       => esc_html__( 'Text Limit', 'sky-elementor-addons' ),
				'description' => esc_html__( 'This is for the main content, but not for excerpts. If you set the offset to 0, then you\'ll get the full text instead.', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 30,
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

		$this->end_controls_section();

		$this->start_controls_section(
			'section_slider_settings',
			[
				'label' => esc_html__( 'Settings', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'transition_effect',
			[
				'label'   => esc_html__( 'Transition Effect', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'fade',
				// coverflow / flip / cube restored. `coverflow_rotate` (conditioned on
				// 'coverflow') and `slide_shadows` (on 'coverflow', 'flip', 'cube') were both
				// already registered below but could never appear, because this list had lost
				// the values they key off. Additive: existing widgets keep their saved
				// slide/fade value, and Swiper ignores an effect's config unless `effect`
				// selects it.
				'options' => [
					'slide'     => esc_html__( 'Slide', 'sky-elementor-addons' ),
					'fade'      => esc_html__( 'Fade', 'sky-elementor-addons' ),
					'coverflow' => esc_html__( 'Coverflow', 'sky-elementor-addons' ),
					'flip'      => esc_html__( 'Flip', 'sky-elementor-addons' ),
					'cube'      => esc_html__( 'Cube', 'sky-elementor-addons' ),
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
						'min'  => 1,
						'max'  => 10,
						'step' => .5,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 1,
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

		$this->add_control(
			'navigation_hover_animation',
			[
				'label'     => esc_html__( 'Hover Animation', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.0.0' ),
				'type'      => Controls_Manager::HOVER_ANIMATION,
				'separator' => 'before',
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
					'none'     => esc_html__( 'None', 'sky-elementor-addons' ),
					'bullets'  => esc_html__( 'Bullets', 'sky-elementor-addons' ),
					'fraction' => esc_html__( 'Fraction', 'sky-elementor-addons' ),
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

		$this->end_controls_section();

		$this->start_controls_section(
			'section_buttons',
			[
				'label' => esc_html__( 'Buttons', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'button_text',
			[
				'label'   => esc_html__( 'Read More Button Text', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'READ MORE', 'sky-elementor-addons' ),
				'dynamic' => [ 'active' => true ],
			]
		);

		$this->add_control(
			'button_icon',
			[
				// The button renders this icon twice — one copy waits off-canvas at the left,
				// the other sits at the right and exits on hover.
				'label'              => esc_html__( 'Read More Icon', 'sky-elementor-addons' ),
				'type'               => Controls_Manager::ICONS,
				'default' => [
					'value'   => 'fas fa-arrow-right',
					'library' => 'fa-solid',
				],
				'skin'               => 'inline',
				'label_block'        => false,
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'button_hover_animation',
			[
				'label'     => esc_html__( 'Hover Animation', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.0.0' ),
				'type'      => Controls_Manager::HOVER_ANIMATION,
				'separator' => 'before',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_common_style',
			[
				'label' => esc_html__( 'Common', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'content_width',
			[
				'label'      => esc_html__( 'Content Width', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ '%', 'px' ],
				'range' => [
					'%'  => [
						'min' => 10,
						'max' => 100,
					],
					'px' => [
						'min' => 100,
						'max' => 1200,
					],
				],
				'tablet_default' => [
					'size' => 100,
					'unit' => '%',
				],
				'mobile_default' => [
					'size' => 100,
					'unit' => '%',
				],
				'selectors' => [
					'{{WRAPPER}} .sa-content' => 'max-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'container_width',
			[
				'label'      => esc_html__( 'Container Width', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', '%' ],
				'range'      => [
					'px' => [
						'min' => 500,
						'max' => 2000,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-content-container' => 'max-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'container_padding',
			[
				'label'      => esc_html__( 'Container Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'tablet_default' => [
					'top'      => '2',
					'right'    => '2',
					'bottom'   => '2',
					'left'     => '2',
					'unit'     => 'rem',
					'isLinked' => true,
				],
				'mobile_default' => [
					'top'      => '1.5',
					'right'    => '1.5',
					'bottom'   => '1.5',
					'left'     => '1.5',
					'unit'     => 'rem',
					'isLinked' => true,
				],
				'selectors' => [
					'{{WRAPPER}} .sa-content-container' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'image_background',
				'label'    => esc_html__( 'Overlay', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'exclude'  => [ 'image' ],
				'fields_options' => [
					'background' => [
						'label'   => esc_html__( 'Overlay', 'sky-elementor-addons' ),
						'default' => 'classic',
					],
					'color'      => [
						'default' => '#0504046B',
					],
				],
				'selector' => '{{WRAPPER}} .sa-stellar-slider .swiper-slide:before',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_content_box_style',
			[
				'label' => esc_html__( 'Content Box', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.0.0' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'content_position',
			[
				'label'       => esc_html__( 'Vertical Position', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::CHOOSE,
				'label_block' => false,
				'options'     => [
					'flex-start' => [
						'title' => esc_html__( 'Top', 'sky-elementor-addons' ),
						'icon'  => 'eicon-v-align-top',
					],
					'center'     => [
						'title' => esc_html__( 'Middle', 'sky-elementor-addons' ),
						'icon'  => 'eicon-v-align-middle',
					],
					'flex-end'   => [
						'title' => esc_html__( 'Bottom', 'sky-elementor-addons' ),
						'icon'  => 'eicon-v-align-bottom',
					],
				],
				'default'     => 'center',
				'selectors' => [
					'{{WRAPPER}} .sa-content-wrapper' => 'align-items: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'content_background',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'exclude'  => [ 'image' ],
				'selector' => '{{WRAPPER}} .sa-content-container',
			]
		);

		$this->add_responsive_control(
			'content_blur',
			[
				'label'      => esc_html__( 'Background Blur', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 40,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-content-container' => '-webkit-backdrop-filter: blur({{SIZE}}px); backdrop-filter: blur({{SIZE}}px);',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'content_border',
				'label'    => esc_html__( 'Border', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-content-container',
			]
		);

		$this->add_responsive_control(
			'content_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-content-container' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'content_box_shadow',
				'label'    => esc_html__( 'Box Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-content-container',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_title_style',
			[
				'label' => esc_html__( 'Title', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
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
					'{{WRAPPER}} .sa-post-title-wrapper' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'title_color',
			[
				'label' => esc_html__( 'Text Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-post-title' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'title_color_hover',
			[
				'label' => esc_html__( 'Text Color Hover', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-post-title:hover' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'title_typography',
				'label'    => esc_html__( 'Typography', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-post-title',
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'title_text_shadow',
				'label'    => esc_html__( 'Text Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-post-title',
			]
		);

		$this->add_group_control(
			Group_Control_Text_Stroke::get_type(),
			[
				'name'     => 'title_text_stroke',
				'selector' => '{{WRAPPER}} .sa-post-title',
			]
		);

		$this->add_control(
			'title_divider',
			[
				'label'        => esc_html__( 'Divider', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.0.0' ),
				'type'         => Controls_Manager::SWITCHER,
				'separator'    => 'before',
				'prefix_class' => 'sa-ss-divider-',
				'render_type'  => 'template',
			]
		);

		$this->add_responsive_control(
			'title_divider_width',
			[
				'label'      => esc_html__( 'Divider Width', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.0.0' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range'      => [
					'px' => [
						'min' => 10,
						'max' => 600,
					],
					'%'  => [
						'min' => 5,
						'max' => 100,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 64,
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-post-title-wrapper:after' => 'width: {{SIZE}}{{UNIT}};',
				],
				'condition'  => [ 'title_divider' => 'yes' ],
			]
		);

		$this->add_responsive_control(
			'title_divider_height',
			[
				'label'      => esc_html__( 'Divider Thickness', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.0.0' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 1,
						'max' => 20,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 2,
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-post-title-wrapper:after' => 'height: {{SIZE}}{{UNIT}};',
				],
				'condition'  => [ 'title_divider' => 'yes' ],
			]
		);

		$this->add_control(
			'title_divider_color',
			[
				'label'     => esc_html__( 'Divider Color', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.0.0' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .sa-post-title-wrapper:after' => 'background: {{VALUE}};',
				],
				'condition' => [ 'title_divider' => 'yes' ],
			]
		);

		$this->add_responsive_control(
			'title_divider_spacing',
			[
				'label'      => esc_html__( 'Divider Spacing', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.0.0' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 20,
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-post-title-wrapper:after' => 'margin-top: {{SIZE}}{{UNIT}};',
				],
				'condition'  => [ 'title_divider' => 'yes' ],
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

		$this->add_responsive_control(
			'text_spacing',
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
					'{{WRAPPER}} .sa-post-text' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
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

		// $this->add_responsive_control(
		// 'category_space_between',
		// [
		// 'label'      => esc_html__('Space Between', 'sky-elementor-addons'),
		// 'type'       => Controls_Manager::SLIDER,
		// 'size_units' => ['px', 'em'],
		// 'range'      => [
		// 'px' => [
		// 'min' => 0,
		// 'max' => 50,
		// ],
		// ],
		// 'selectors'  => [
		// '{{WRAPPER}}'  => '--sa-post-category-spacing: {{SIZE}}{{UNIT}};',
		// ],
		// ]
		// );

		/**
		 * Global Category
		 */

		$this->register_post_category_controls_style();

		$this->end_controls_section();

		$this->start_controls_section(
			'section_author_style',
			[
				'label' => esc_html__( 'Author', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_author' => 'yes',
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

		$this->add_control(
			'author_color',
			[
				'label' => esc_html__( 'Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-post-author-text a' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'author_color_hover',
			[
				'label' => esc_html__( 'Color Hover', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-post-author-text:hover  a' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'author_typography',
				'label'    => esc_html__( 'Typography', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-post-author-text',
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'author_text_shadow',
				'label'    => esc_html__( 'Text Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-post-author-text',
			]
		);

		$this->add_control(
			'author_heading_style',
			[
				'label'     => esc_html__( 'Image', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'author_img_width',
			[
				'label'      => esc_html__( 'Size', 'sky-elementor-addons' ),
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
					'{{WRAPPER}} .sa-post-author-thumb' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'author_img_padding',
			[
				'label'      => esc_html__( 'Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-post-author-thumb' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'author_img_border',
				'label'    => esc_html__( 'Border', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-post-author-thumb',
				'fields_options' => [
					'border' => [
						'default' => 'solid',
					],
					'width'  => [
						'default' => [
							'top'      => '2',
							'right'    => '2',
							'bottom'   => '2',
							'left'     => '2',
							'unit'     => 'px',
							'isLinked' => false,
						],
					],
					'color'  => [
						'default' => '#fff',
					],
				],
			]
		);

		$this->add_responsive_control(
			'author_img_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-post-author-thumb' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'author_img_box_shadow',
				'label'    => esc_html__( 'Box Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-post-author-thumb',
			]
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			[
				'name'     => 'author_img_css_filters',
				'selector' => '{{WRAPPER}} .sa-post-author-thumb',
			]
		);

		$this->add_control(
			'author_date_heading_style',
			[
				'label'     => esc_html__( 'Date', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'author_date_color',
			[
				'label' => esc_html__( 'Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}' => '--sa-post-author-date-color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'author_date_typography',
				'label'    => esc_html__( 'Typography', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-post-date, {{WRAPPER}} .sa-icon-wrap',
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'author_date_text_shadow',
				'label'    => esc_html__( 'Text Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-post-date, {{WRAPPER}} .sa-icon-wrap',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_buttons_style',
			[
				'label' => esc_html__( 'Buttons', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'button_icon_spacing',
			[
				// Both icons are absolutely positioned, so this is their inset from the
				// button edge — the resting position of the right icon, and where the left
				// one lands on hover. 16px matches the padding-to-icon gap of the old
				// flex layout at the default padding.
				'label'      => esc_html__( 'Icon Spacing', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.0.0' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 50,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 16,
				],
				'selectors'  => [
					'{{WRAPPER}}' => '--sa-ss-btn-icon-inset: {{SIZE}}{{UNIT}};',
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

		$this->start_controls_tabs( 'tabs_buttons_style' );

		$this->start_controls_tab(
			'tab_buttons_read_more',
			[
				'label' => esc_html__( 'Read More', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'button_color',
			[
				'label' => esc_html__( 'Text Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-read-more, {{WRAPPER}} .sa-read-more:focus' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'button_background',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .sa-read-more, {{WRAPPER}} .sa-read-more:focus',
			]
		);

		$this->add_control(
			'button_fill_color',
			[
				// The circle cannot use `currentColor`: on hover the label flips to the hover
				// text colour while the circle has to stay the accent, so it needs its own value.
				'label'   => esc_html__( 'Fill Color', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.0.0' ),
				'type'    => Controls_Manager::COLOR,
				'default' => '#e0528d',
				'selectors' => [
					'{{WRAPPER}} .sa-read-more' => '--sa-ss-btn-fill: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_border_color_override',
			[
				'label' => esc_html__( 'Border Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-read-more' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_hover',
			[
				'label'     => esc_html__( 'H O V E R', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'button_color_hover',
			[
				'label' => esc_html__( 'Text Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					// Writes the variable as well as the property: the LESS hover ink rule
					// ties this on specificity, so the var is what guarantees the control wins.
					'{{WRAPPER}} .sa-read-more:hover' => 'color: {{VALUE}}; --sa-ss-btn-ink: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'button_background_hover',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .sa-read-more:hover',
			]
		);

		$this->add_control(
			'button_border_color_hover',
			[
				'label' => esc_html__( 'Border Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-read-more:hover' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'button_border_radius_hover',
			[
				// The pill tightening into a rounded rectangle is half the effect, so the
				// hover radius is a control rather than the hardcoded 12px of the original.
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.0.0' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-read-more:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

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

		$this->add_control(
			'navigation_cluster_heading',
			[
				'label' => esc_html__( 'Cluster', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.0.0' ),
				'type'  => Controls_Manager::HEADING,
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'navigation_cluster_background',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'exclude'  => [ 'image' ],
				'selector' => '{{WRAPPER}} .sa-navigation-wrapper',
			]
		);

		$this->add_responsive_control(
			'navigation_cluster_blur',
			[
				'label'      => esc_html__( 'Background Blur', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.0.0' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 40,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-navigation-wrapper' => '-webkit-backdrop-filter: blur({{SIZE}}px); backdrop-filter: blur({{SIZE}}px);',
				],
			]
		);

		$this->add_responsive_control(
			'navigation_cluster_padding',
			[
				'label'      => esc_html__( 'Padding', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.0.0' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-navigation-wrapper' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'navigation_cluster_border',
				'label'    => esc_html__( 'Border', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-navigation-wrapper',
			]
		);

		$this->add_responsive_control(
			'navigation_cluster_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.0.0' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-navigation-wrapper' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
			'navigation_right_offset',
			[
				'label'      => esc_html__( 'Right Offset', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.0.0' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', '%' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 200,
					],
				],
				'tablet_default' => [
					'size' => 32,
					'unit' => 'px',
				],
				'mobile_default' => [
					'size' => 20,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .sa-navigation-wrapper' => 'right: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'navigation_buttons_heading',
			[
				'label'     => esc_html__( 'Buttons', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.0.0' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'navigation_button_size',
			[
				'label'      => esc_html__( 'Button Size', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.0.0' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range'      => [
					'px' => [
						'min' => 20,
						'max' => 100,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 38,
				],
				'selectors'  => [
					'{{WRAPPER}}' => '--sa-nav-button-size: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'navigation_size',
			[
				'label'      => esc_html__( 'Icon Size', 'sky-elementor-addons' ),
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
				'fields_options' => [
					'border' => [
						'default' => 'solid',
					],
					'width'  => [
						'default' => [
							'top'      => '2',
							'right'    => '2',
							'bottom'   => '2',
							'left'     => '2',
							'unit'     => 'px',
							'isLinked' => false,
						],
					],
					'color'  => [
						'default' => '#e0528d',
					],
				],
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
				'fields_options' => [
					'background' => [
						'label'   => esc_html__( 'Background', 'sky-elementor-addons' ),
						'default' => 'classic',
					],
					'color'      => [
						'default' => '#fff',
					],
				],
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
				'fields_options' => [
					'background' => [
						'label'   => esc_html__( 'Background', 'sky-elementor-addons' ),
						'default' => 'classic',
					],
					'color'      => [
						'default' => '#e0528d',
					],
				],
			]
		);

		$this->add_control(
			'navigation_hover_fill',
			[
				'label'       => esc_html__( 'Hover Fill', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.0.0' ),
				'type'        => Controls_Manager::COLOR,
				'default'     => 'rgba(255, 255, 255, 0.18)',
				'selectors' => [
					'{{WRAPPER}}' => '--sa-nav-sweep-color: {{VALUE}};',
				],
				'description' => esc_html__( 'Colour of the circle that sweeps out from the centre on hover.', 'sky-elementor-addons' ),
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
					'{{WRAPPER}} .swiper .swiper-pagination' => 'bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'pagination_left_offset',
			[
				'label'      => esc_html__( 'Left Offset', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.0.0' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', '%' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 200,
					],
				],
				'tablet_default' => [
					'size' => 32,
					'unit' => 'px',
				],
				'mobile_default' => [
					'size' => 20,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .swiper-pagination' => 'left: {{SIZE}}{{UNIT}};',
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
				'label'     => esc_html__( 'Pagination Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ddd',
				'selectors' => [
					'{{WRAPPER}} .swiper-pagination.swiper-pagination-fraction' => 'color: {{VALUE}}',
				],
				'condition' => [ 'pagination_type' => 'fraction' ],
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
				'name'      => 'pagination_color',
				'label'     => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'     => [ 'classic', 'gradient' ],
				'exclude'   => [ 'image' ],
				'fields_options' => [
					'background' => [
						'label' => esc_html__( 'Pagination Color', 'sky-elementor-addons' ),
					],
				],
				'selector'  => '{{WRAPPER}} .swiper-pagination-bullet',
				'condition' => [ 'pagination_type' => 'bullets' ],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'      => 'pagination_border',
				'label'     => esc_html__( 'Border', 'sky-elementor-addons' ),
				'selector'  => '{{WRAPPER}} .swiper-pagination-bullet',
				'condition' => [ 'pagination_type' => 'bullets' ],
				'fields_options' => [
					'border' => [
						'default' => 'solid',
					],
					'width'  => [
						'default' => [
							'top'      => '8',
							'right'    => '8',
							'bottom'   => '8',
							'left'     => '8',
							'unit'     => 'px',
							'isLinked' => false,
						],
					],
					'color'  => [
						'default' => '#0A0A0AC4',
					],
				],
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
				'name'      => 'pagination_active_color',
				'label'     => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'     => [ 'classic', 'gradient' ],
				'exclude'   => [ 'image' ],
				'fields_options' => [
					'background' => [
						'label' => esc_html__( 'Pagination Active Color', 'sky-elementor-addons' ),
					],
				],
				'selector'  => '{{WRAPPER}} .swiper-pagination-bullet.swiper-pagination-bullet-active',
				'condition' => [ 'pagination_type' => 'bullets' ],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'      => 'pagination_border_active',
				'label'     => esc_html__( 'Border', 'sky-elementor-addons' ),
				'selector'  => '{{WRAPPER}} .swiper-pagination-bullet.swiper-pagination-bullet-active',
				'condition' => [ 'pagination_type' => 'bullets' ],
				'fields_options' => [
					'border' => [
						'default' => 'solid',
					],
					'width'  => [
						'default' => [
							'top'      => '6',
							'right'    => '6',
							'bottom'   => '6',
							'left'     => '6',
							'unit'     => 'px',
							'isLinked' => false,
						],
					],
					'color'  => [
						'default' => '#fff',
					],
				],
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

		$this->start_controls_section(
			'section_social_icons_style',
			[
				'label' => esc_html__( 'Social Icons', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_social_icons' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'social_icons_left_offset',
			[
				'label'      => esc_html__( 'Left Offset', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.0.0' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', '%' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 200,
					],
				],
				'tablet_default' => [
					'size' => 32,
					'unit' => 'px',
				],
				'mobile_default' => [
					'size' => 20,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .sky-social-icons' => 'left: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'social_icons_icon_size',
			[
				'label'      => esc_html__( 'Icon Size', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .sky-social-icons .sa-icon-wrap' => 'font-size: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'social_icons_spacing',
			[
				'label'      => esc_html__( 'Spacing', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 50,
						'step' => 1,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .sky-social-icons' => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'social_icons_padding',
			[
				'label'      => esc_html__( 'Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sky-social-icons .sa-link' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'social_icons_border',
				'label'    => esc_html__( 'Border', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sky-social-icons .sa-link',
			]
		);

		$this->add_responsive_control(
			'social_icons_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sky-social-icons .sa-link' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition'  => [
					'show_socials_adv_border_radius!' => 'yes',
				],
			]
		);

		$this->add_control(
			'show_socials_adv_border_radius',
			[
				'label' => esc_html__( 'Advanced Border Radius', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::SWITCHER,
			]
		);

		$this->add_control(
			'socials_adv_border_radius',
			[
				'label'   => esc_html__( 'Radius', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( '30% 70% 70% 30% / 30% 30% 70% 70% ', 'sky-elementor-addons' ),
				'dynamic' => [ 'active' => true ],
				'selectors' => [
					'{{WRAPPER}} .sky-social-icons .sa-link' => 'border-radius: {{VALUE}};',
				],
				'condition' => [
					'show_socials_adv_border_radius' => 'yes',
				],
			]
		);

		$this->start_controls_tabs( 'social_icons_tabs' );

		$this->start_controls_tab(
			'social_icons_tab_normal',
			[
				'label' => esc_html__( 'Normal', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'social_icons_color',
			[
				'label' => esc_html__( 'Icons Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sky-social-icons .sa-link' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'social_icons_bg',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .sky-social-icons .sa-link',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'social_icons_box_shadow',
				'label'    => esc_html__( 'Box Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sky-social-icons .sa-link',
			]
		);

		$this->add_control(
			'social_icons_opacity',
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
					'{{WRAPPER}} .sky-social-icons .sa-link' => 'opacity: {{SIZE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'social_icons_tab_hover',
			[
				'label' => esc_html__( 'Hover', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'social_icons_color_hover',
			[
				'label' => esc_html__( 'Icons Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sky-social-icons .sa-link:hover' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'social_icons_bg_hover',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .sky-social-icons .sa-link:hover',
			]
		);

		$this->add_control(
			'social_icons_border_color_hover',
			[
				'label' => esc_html__( 'Border Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sky-social-icons .sa-link:hover' => 'border-color: {{VALUE}};',
				],
				'condition' => [
					'social_icons_border_border!' => '',
				],
			]
		);

		$this->add_responsive_control(
			'social_icons_border_radius_hover',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sky-social-icons .sa-link:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition'  => [
					'show_socials_adv_border_radius!' => 'yes',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'social_icons_shadow_hover',
				'label'    => esc_html__( 'Text Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sky-social-icons .sa-link:hover',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'social_icons_box_shadow_hover',
				'label'    => esc_html__( 'Box Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sky-social-icons .sa-link:hover',
			]
		);

		$this->add_control(
			'social_icons_opacity_hover',
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
					'{{WRAPPER}} .sky-social-icons .sa-link:hover' => 'opacity: {{SIZE}};',
				],
			]
		);

		$this->add_control(
			'icons_hover_animation',
			[
				'label' => esc_html__( 'Hover Animation', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::HOVER_ANIMATION,
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

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

	protected function render_social_icons() {
		$settings = $this->get_settings_for_display();

		$class_animation = '';

		if ( ! empty( $settings['icons_hover_animation'] ) ) {
			$class_animation = ' elementor-animation-' . $settings['icons_hover_animation'];
		}
		?>
		<div
			class="sky-social-icons sa-position-absolute sa-d-flex sa-flex-column sa-align-items-center sa-justify-content-center">
			<?php
			foreach ( $settings['social_icon_list'] as $index => $item ) :

				$social = '';
				if ( ! empty( $item['social_icon']['value'] ) && ( 'yes' === $settings['social_icon_default_color'] ) ) {
					$social = explode( ' ', $item['social_icon']['value'], 2 );

					$social = str_replace( 'fa-', '', $social[1] );
				}

				$link_key = 'link_' . $index;
				$this->add_render_attribute( $link_key, 'class', [
					'sa-link sa-icon-wrap',
					$class_animation,
					'elementor-repeater-item-' . $item['_id'],
					'elementor-social-icon-' . $social,
				] );

				$this->add_link_attributes( $link_key, $item['link'] );
				?>
				<a <?php $this->print_render_attribute_string( $link_key ); ?>>
					<?php
					Icons_Manager::render_icon( $item['social_icon'] );
					?>
				</a>
			<?php endforeach; ?>
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

	protected function render_author_thumb() {
		$settings = $this->get_settings_for_display();
		if ( 'yes' !== $settings['show_author'] ) {
			return;
		}

		?>
		<div class="sa-post-author-wrapper sa-d-flex" data-swiper-parallax="-350">
			<div class="sa-d-inline-flex sa-align-items-center">
				<a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>">
					<div class="sa-post-author-thumb sa-me-3 sa-rounded-circle sa-overflow-hidden">
						<?php echo get_avatar( get_the_author_meta( 'ID' ), 48 ); ?>
					</div>
				</a>
				<div>
					<div class="sa-post-author-text">
						<a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>">
							<?php echo esc_html( get_the_author() ); ?>
						</a>
					</div>
					<?php $this->render_date(); ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * One of the button's two icon slots. Clearing the Icon control falls back to a plain
	 * arrow rather than an empty slot, because the effect needs something to slide.
	 */
	private function render_button_icon( $settings, $slot_class ) {
		?>
		<span class="sa-button-icon sa-icon-wrap <?php echo esc_attr( $slot_class ); ?>" aria-hidden="true">
			<?php
			if ( ! empty( $settings['button_icon']['value'] ) ) :
				Icons_Manager::render_icon( $settings['button_icon'], [ 'aria-hidden' => 'true' ] );
			else :
				// Plugin-chosen mark, so it is a literal SVG — routing it through Icons_Manager
				// would pull the whole Font Awesome stylesheet in for one arrow.
				?>
				<svg class="sa-ss-btn-arrow" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true">
					<path d="M16.1716 10.9999L10.8076 5.63589L12.2218 4.22168L20 11.9999L12.2218 19.778L10.8076 18.3638L16.1716 12.9999H4V10.9999H16.1716Z" />
				</svg>
				<?php
			endif;
			?>
		</span>
		<?php
	}

	protected function get_button_animation_class() {
		$animation = $this->get_settings_for_display( 'button_hover_animation' );

		return empty( $animation ) ? '' : 'elementor-animation-' . $animation;
	}

	protected function render_item( $post_id, $image_size, $excerpt_length ) {
		$settings  = $this->get_settings_for_display();
		$_title_id = $post_id . $this->get_id();
		?>
		<div class="swiper-slide sa-position-relative">
			<div class="sa-img-wrap sa-position-absolute sa-w-100 sa-h-100">
				<?php
				$this->render_post_image( [
					'wrapper_class'  => 'sa-cover',
					'image_id'       => get_post_thumbnail_id( $post_id ),
					'thumbnail_size' => $image_size,
				] );
				?>
			</div>
			<div
				class="sa-content-wrapper sa-position-absolute sa-w-100 sa-d-flex sa-align-items-center sa-justify-content-center">
				<div class="sa-content-container sa-d-flex sa-flex-column">
					<div class="sa-content">
						<?php
						if ( 'yes' === $settings['show_category'] ) :
							$cat_attr = [
								'class'                => 'sa-post-category sa-post-category-style-1 sa-mb-4 sa-ss-anim-item',
								'style'                => '--sa-ss-i: 0;',
								'data-swiper-parallax' => -220,
							];
							$this->render_post_category_attr( 'cat' . $_title_id, $cat_attr );
						endif;
						?>
						<div class="sa-post-title-wrapper sa-mb-4 sa-ss-anim-item" style="--sa-ss-i: 1;" data-swiper-parallax="-200">
							<?php
							printf(
								'<%1$s class="%2$s">%3$s</%1$s>',
								esc_attr( Utils::validate_html_tag( $settings['title_tag'] ) ),
								'sa-post-title sa-m-0',
								esc_html( get_the_title() )
							);
							?>
						</div>
						<div class="sa-post-meta sa-d-flex sa-mb-4 sa-ss-anim-item" style="--sa-ss-i: 2;">

							<?php $this->render_author_thumb(); ?>

						</div>
						<?php if ( 'yes' === $settings['show_excerpt'] ) : ?>
						<div class="sa-mb-5 sa-ss-anim-item" style="--sa-ss-i: 3;" data-swiper-parallax="-150">
							<?php $this->render_post_excerpt( $excerpt_length ); ?>
						</div>
						<?php endif; ?>

						<div class="sa-buttons-wrapper sa-d-flex sa-ss-anim-item" style="--sa-ss-i: 4;" data-swiper-parallax="-100">
							<?php
							// Lead icon → label → fill circle → trailing icon. Both icon slots are
							// occupied by design, so the icon has no configurable side. `sa-rounded`
							// (0.25rem in base.less) is deliberately dropped: the pill radius is now
							// a LESS default that the Border Radius control overrides.
							?>
							<a href="<?php echo esc_url( get_permalink() ); ?>"
								class="sa-read-more sa-button sa-text-decoration-none <?php echo esc_attr( $this->get_button_animation_class() ); ?>">
								<?php $this->render_button_icon( $settings, 'sa-icon-lead' ); ?>
								<span class="sa-button-text">
									<?php echo esc_html( $settings['button_text'] ); ?>
								</span>
								<span class="sa-ss-btn-fill" aria-hidden="true"></span>
								<?php $this->render_button_icon( $settings, 'sa-icon-after' ); ?>
							</a>
						</div>

					</div>
				</div>
			</div>
		</div>
		<?php
	}


	protected function render_navigation() {
		$settings = $this->get_settings_for_display();

		$nav_animation = empty( $settings['navigation_hover_animation'] ) ? '' : 'elementor-animation-' . $settings['navigation_hover_animation'];
		?>
		<!-- If we need navigation buttons -->
		<div class="sa-swiper-button-prev sa-slider-navigation sa-icon-wrap <?php echo esc_attr( $nav_animation ); ?>">
			<?php
			if ( ! empty( $settings['prev_icon']['value'] ) ) :
				Icons_Manager::render_icon( $settings['prev_icon'], [
					'aria-hidden' => 'true',
					'class'       => 'fa-fw',
				] );
			else :
				?>
				<svg class="sa-nav-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" aria-hidden="true">
					<path d="M15 5l-7 7 7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
				</svg>
				<?php
			endif;
			?>

		</div>
		<div class="sa-swiper-button-next sa-slider-navigation sa-icon-wrap <?php echo esc_attr( $nav_animation ); ?>">
			<?php
			if ( ! empty( $settings['next_icon']['value'] ) ) :
				Icons_Manager::render_icon( $settings['next_icon'], [
					'aria-hidden' => 'true',
					'class'       => 'fa-fw',
				] );
			else :
				?>
				<svg class="sa-nav-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" aria-hidden="true">
					<path d="M9 5l7 7-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
				</svg>
				<?php
			endif;
			?>
		</div>
		<?php
	}

	protected function render_pagination() {
		?>
		<div class="swiper-pagination"></div>
		<?php
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$id       = 'sa-stellar-slider' . $this->get_id();

		$this->query_posts( $settings['posts_per_page'] );
		$wp_query = $this->get_query();

		if ( ! $wp_query->found_posts ) {
			return;
		}

		$pagination_type = $settings['pagination_type'];

		if ( 'none' === $pagination_type ) {
			$pagination_type = false;
		}

		// Parallax and the content entrance animation both drive `transform` on the same
		// elements, so only one can own them. A saved widget has `content_animation` = none
		// and keeps parallax exactly as before.
		$use_parallax = ( 'none' === $settings['content_animation'] ) && ( 'yes' === $settings['parallax_effect'] );

		$this->add_render_attribute(
			[
				'slider' => [
					'class' => 'sa-stellar-slider',
					'id'    => $id,
					'data-settings' => [
						wp_json_encode( array_filter( [
							'effect'       => $settings['transition_effect'],
							'fadeEffect'   => ( isset( $settings['cross_fade'] ) && 'yes' === $settings['cross_fade'] ) ? [ 'crossFade' => true ] : false,
							// The Rotate and Slide Shadows controls existed but nothing consumed
							// them — only `effect` and `fadeEffect` were emitted, so both were dead
							// even once their effect values were selectable. Swiper reads an effect
							// block only when `effect` names it, so these are inert otherwise.
							'coverflowEffect' => ( 'coverflow' === $settings['transition_effect'] ) ? [
								'rotate'       => ( ! empty( $settings['coverflow_rotate']['size'] ) || 0 === $settings['coverflow_rotate']['size'] ) ? $settings['coverflow_rotate']['size'] : 30,
								'slideShadows' => ( isset( $settings['slide_shadows'] ) && 'yes' === $settings['slide_shadows'] ),
							] : false,
							'flipEffect'   => ( 'flip' === $settings['transition_effect'] ) ? [
								'slideShadows' => ( isset( $settings['slide_shadows'] ) && 'yes' === $settings['slide_shadows'] ),
							] : false,
							'cubeEffect'   => ( 'cube' === $settings['transition_effect'] ) ? [
								'slideShadows' => ( isset( $settings['slide_shadows'] ) && 'yes' === $settings['slide_shadows'] ),
							] : false,
							'autoplay'     => 'yes' === $settings['autoplay'] ? [
								'delay' => $settings['autoplay_speed']['size'] * 1000,
							] : false,
							'loop'         => ( 'yes' === $settings['loop'] ) ? true : false,
							'speed'        => ( ! empty( $settings['speed']['size'] ) ) ? $settings['speed']['size'] * 1000 : 2000,
							'pauseOnHover' => ( 'yes' === $settings['autoplay'] && 'yes' === $settings['pause_on_hover'] ) ? true : false,
							'observer'     => 'yes' === $settings['observer'] ? true : false,
							'parallax'     => $use_parallax,
							'navigation'   => [
								'nextEl' => "#$id .sa-swiper-button-next",
								'prevEl' => "#$id .sa-swiper-button-prev",
							],
							'pagination'   => [
								'el'             => "#$id .swiper-pagination",
								'clickable'      => true,
								'type'           => $pagination_type,
								'dynamicBullets' => ( 'bullets' === $settings['pagination_type'] && isset( $settings['dynamic_bullets'] ) && ( 'yes' === $settings['dynamic_bullets'] ) ) ? true : false,
							],

						] ) ),
					],
				],
			]
		);

		?>
		<div <?php $this->print_render_attribute_string( 'slider' ); ?>>
			<div class="swiper">
				<div class="swiper-wrapper">
					<?php

					while ( $wp_query->have_posts() ) :
						$wp_query->the_post();

						$thumbnail_size = $settings['primary_thumbnail_size'];

						$this->render_item( get_the_ID(), $thumbnail_size, $settings['excerpt_length'] );

					endwhile;

					wp_reset_postdata();

					?>
				</div>
				<?php
				if ( 'yes' === $settings['show_social_icons'] ) :
					$this->render_social_icons();
				endif;

				if ( 'yes' === $settings['show_pagination'] ) :
					$this->render_pagination();
				endif;

				if ( 'yes' === $settings['show_navigation'] ) :
					?>
					<div class="sa-navigation-wrapper sa-position-absolute sa-d-flex">
						<?php $this->render_navigation(); ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}
}

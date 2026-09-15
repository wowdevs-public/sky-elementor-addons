<?php

namespace Sky_Addons\Modules\TeamMemberCarousel\Widgets;

use Elementor\Utils;
use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Icons_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Css_Filter;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Widget_Base;

use Sky_Addons\Traits\Global_Swiper_Controls;
use Sky_Addons\Includes\Controls\GroupQuery\Group_Control;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Team_Member_Carousel extends Widget_Base {

	use Global_Swiper_Controls;
	use Group_Control;

	private $_query = null;

	public function get_query() {
		return $this->_query;
	}

	public function query_posts( $posts_per_page ) {
		$args = [];

		if ( $posts_per_page ) {
			$args['posts_per_page'] = $posts_per_page;
			$args['paged']          = max( 1, get_query_var( 'paged' ), get_query_var( 'page' ) );
		}

		$default      = $this->getGroupControlQueryArgs();
		$args         = array_merge( $default, $args );
		$this->_query = new \WP_Query( $args );
	}

	public function get_name() {
		return 'sky-team-member-carousel';
	}

	public function get_title() {
		return esc_html__( 'Team Member Carousel', 'sky-elementor-addons' );
	}

	public function get_icon() {
		return 'sky-icon-team-member-carousel';
	}

	public function get_categories() {
		return [ 'sky-elementor-addons' ];
	}

	public function get_keywords() {
		return [ 'sky', 'team', 'member', 'carousel', 'slider', 'profile' ];
	}

	public function get_style_depends() {
		if ( sky_addons_editor_mode() ) {
			return [ 'elementor-icons-fa-solid', 'elementor-icons-fa-brands', 'swiper', 'sky-addons-styles' ];
		}

		return [ 'elementor-icons-fa-solid', 'elementor-icons-fa-brands', 'swiper', 'sa-team-member' ];
	}

	public function get_script_depends() {
		if ( sky_addons_editor_mode() ) {
			return [ 'swiper', 'sky-addons-scripts' ];
		}

		return [ 'swiper', 'sa-team-member-carousel' ];
	}

	public function get_custom_help_url() {
		return 'https://skyaddons.com/docs/sky-addons/carousel-slider/team-member-carousel/';
	}

	public function has_widget_inner_wrapper(): bool {
		return ! \Elementor\Plugin::$instance->experiments->is_feature_active( 'e_optimized_markup' );
	}

	/**
	 * Predefined social platforms used by each member.
	 */
	protected function get_social_platforms() {
		return [
			'social_facebook'  => [
				'label' => esc_html__( 'Facebook', 'sky-elementor-addons' ),
				'icon'  => [
					'value'   => 'fab fa-facebook-f',
					'library' => 'fa-brands',
				],
			],
			'social_twitter'   => [
				'label' => esc_html__( 'Twitter', 'sky-elementor-addons' ),
				'icon'  => [
					'value'   => 'fab fa-twitter',
					'library' => 'fa-brands',
				],
			],
			'social_linkedin'  => [
				'label' => esc_html__( 'LinkedIn', 'sky-elementor-addons' ),
				'icon'  => [
					'value'   => 'fab fa-linkedin-in',
					'library' => 'fa-brands',
				],
			],
			'social_instagram' => [
				'label' => esc_html__( 'Instagram', 'sky-elementor-addons' ),
				'icon'  => [
					'value'   => 'fab fa-instagram',
					'library' => 'fa-brands',
				],
			],
			'social_youtube'   => [
				'label' => esc_html__( 'YouTube', 'sky-elementor-addons' ),
				'icon'  => [
					'value'   => 'fab fa-youtube',
					'library' => 'fa-brands',
				],
			],
			'social_website'   => [
				'label' => esc_html__( 'Website', 'sky-elementor-addons' ),
				'icon'  => [
					'value'   => 'fas fa-globe',
					'library' => 'fa-solid',
				],
			],
		];
	}

	protected function register_controls() {

		/**
		 * Layout
		 */
		$this->start_controls_section(
			'section_layout',
			[
				'label' => esc_html__( 'Layout', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'content_type',
			[
				'label'   => esc_html__( 'Content Source', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'repeater',
				'options' => [
					'repeater' => esc_html__( 'Default (Repeater)', 'sky-elementor-addons' ),
					'posts'    => esc_html__( 'Dynamic Posts', 'sky-elementor-addons' ),
				],
			]
		);

		$this->add_control(
			'style_select',
			[
				'label'       => esc_html__( 'Select Style', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'default',
				'options'     => [
					'default' => esc_html__( 'Default', 'sky-elementor-addons' ),
					'ardent'  => esc_html__( 'Ardent', 'sky-elementor-addons' ),
					'folk'    => esc_html__( 'Folk', 'sky-elementor-addons' ),
					'folker'  => esc_html__( 'Folker', 'sky-elementor-addons' ),
					'slide'   => esc_html__( 'Slide', 'sky-elementor-addons' ),
					'mold'    => esc_html__( 'Mold', 'sky-elementor-addons' ),
				],
				'render_type' => 'template',
			]
		);

		$this->add_control(
			'slide_effect',
			[
				'label'       => esc_html__( 'Slide Effect', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'top-to-bottom',
				'options'     => [
					'top-to-bottom' => esc_html__( 'Top to Bottom', 'sky-elementor-addons' ),
					'bottom-to-top' => esc_html__( 'Bottom to Top', 'sky-elementor-addons' ),
					'left-to-right' => esc_html__( 'Left To Right', 'sky-elementor-addons' ),
					'right-to-left' => esc_html__( 'Right to Left', 'sky-elementor-addons' ),
				],
				'render_type' => 'template',
				'condition'   => [
					'style_select' => 'slide',
				],
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

		$this->add_responsive_control(
			'image_position',
			[
				'label'          => esc_html__( 'Image Position', 'sky-elementor-addons' ),
				'type'           => Controls_Manager::CHOOSE,
				'label_block'    => false,
				'options' => [
					'left'  => [
						'title' => esc_html__( 'Left', 'sky-elementor-addons' ),
						'icon'  => 'eicon-h-align-left',
					],
					'top'   => [
						'title' => esc_html__( 'Top', 'sky-elementor-addons' ),
						'icon'  => 'eicon-v-align-top',
					],
					'right' => [
						'title' => esc_html__( 'Right', 'sky-elementor-addons' ),
						'icon'  => 'eicon-h-align-right',
					],
				],
				'default'        => 'top',
				'toggle'         => false,
				'style_transfer' => true,
				'selectors' => [
					'{{WRAPPER}} .sa-team-member' => '{{VALUE}};',
				],
				'selectors_dictionary' => [
					'left'  => 'display: flex; flex-direction: row; text-align: left;',
					'top'   => 'text-align: left; display: block; flex-direction: unset; flex-flow: unset;',
					'right' => 'display: flex; flex-direction: row-reverse; text-align: right;',
				],
				'condition' => [
					'style_select' => 'default',
				],
				'separator'      => 'before',
			]
		);

		$this->add_control(
			'image_heading',
			[
				'label'     => esc_html__( 'Image', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Image_Size::get_type(),
			[
				'name'           => 'thumbnail',
				'default'        => 'large',
				'separator'      => 'none',
				'fields_options' => [
					'size' => [
						'label' => esc_html__( 'Image Resolution', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
					],
				],
			]
		);

		$this->add_control(
			'show_alter_image',
			[
				'label'        => esc_html__( 'Alternative Image', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'prefix_class' => 'sa-alter-img-',
				'render_type'  => 'template',
				'condition'    => [
					'content_type' => 'repeater',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Image_Size::get_type(),
			[
				'name'           => 'alter_thumbnail',
				'default'        => 'large',
				'separator'      => 'none',
				'fields_options' => [
					'size' => [
						'label' => esc_html__( 'Alternative Image Resolution', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
					],
				],
				'condition'      => [
					'content_type'     => 'repeater',
					'show_alter_image' => 'yes',
				],
			]
		);

		$this->add_control(
			'elements_heading',
			[
				'label'     => esc_html__( 'Elements', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'name_tag',
			[
				'label'   => esc_html__( 'Name HTML Tag', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'h3',
				'options' => sky_addons_title_tags(),
			]
		);

		$this->add_control(
			'show_job_title',
			[
				'label'   => esc_html__( 'Show Job Title', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'job_title_tag',
			[
				'label'   => esc_html__( 'Job Title HTML Tag', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'h6',
				'options' => sky_addons_title_tags(),
				'condition' => [
					'show_job_title' => 'yes',
				],
			]
		);

		$this->add_control(
			'show_socials',
			[
				'label'   => esc_html__( 'Show Social Icons', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'show_button',
			[
				'label' => esc_html__( 'Show Button', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::SWITCHER,
			]
		);

		$this->add_control(
			'mold_note',
			[
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => esc_html__( 'In this style (mold), the Short Text and Button will not be visible for design purposes.', 'sky-elementor-addons' ),
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-warning',
				'condition'       => [
					'style_select' => 'mold',
				],
			]
		);

		$this->end_controls_section();

		/**
		 * Members
		 */
		$this->start_controls_section(
			'section_members',
			[
				'label' => esc_html__( 'Members', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
				'condition' => [
					'content_type' => 'repeater',
				],
			]
		);

		$repeater = new Repeater();

		$repeater->start_controls_tabs( 'member_tabs' );

		$repeater->start_controls_tab(
			'member_tab_content',
			[
				'label' => esc_html__( 'Member', 'sky-elementor-addons' ),
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
			]
		);

		$repeater->add_control(
			'alter_image',
			[
				'label'       => esc_html__( 'Alternative Image', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::MEDIA,
				'default'     => [
					'url' => Utils::get_placeholder_image_src(),
				],
				'dynamic'     => [ 'active' => true ],
				'description' => esc_html__( 'Shown on hover when "Alternative Image" is enabled in the Layout section.', 'sky-elementor-addons' ),
			]
		);

		$repeater->add_control(
			'name',
			[
				'label'       => esc_html__( 'Name', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXTAREA,
				'default'     => esc_html__( 'John Doe', 'sky-elementor-addons' ),
				'placeholder' => esc_html__( 'Type name here', 'sky-elementor-addons' ),
				'dynamic'     => [ 'active' => true ],
			]
		);

		$repeater->add_control(
			'job_title',
			[
				'label'       => esc_html__( 'Job Title', 'sky-elementor-addons' ),
				'label_block' => true,
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Software Engineer', 'sky-elementor-addons' ),
				'placeholder' => esc_html__( 'Type your job title here', 'sky-elementor-addons' ),
				'dynamic'     => [ 'active' => true ],
			]
		);

		$repeater->add_control(
			'text',
			[
				'label'       => esc_html__( 'Short Text', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXTAREA,
				'placeholder' => esc_html__( 'Type your text here', 'sky-elementor-addons' ),
				'dynamic'     => [ 'active' => true ],
			]
		);

		$repeater->end_controls_tab();

		$repeater->start_controls_tab(
			'member_tab_links',
			[
				'label' => esc_html__( 'Links', 'sky-elementor-addons' ),
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
					'is_external' => true,
				],
				'dynamic'       => [ 'active' => true ],
			]
		);

		$repeater->add_control(
			'button_text',
			[
				'label'   => esc_html__( 'Button Text', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Click here', 'sky-elementor-addons' ),
				'dynamic' => [ 'active' => true ],
			]
		);

		foreach ( $this->get_social_platforms() as $key => $platform ) {
			$repeater->add_control(
				$key,
				[
					'label'         => $platform['label'],
					'type'          => Controls_Manager::URL,
					'placeholder'   => esc_html__( 'https://your-link.com', 'sky-elementor-addons' ),
					'show_external' => true,
					'dynamic'       => [ 'active' => true ],
				]
			);
		}

		$repeater->end_controls_tab();

		$repeater->end_controls_tabs();

		$this->add_control(
			'member_list',
			[
				'label'       => esc_html__( 'Members', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => [
					[
						'name'            => esc_html__( 'John Doe', 'sky-elementor-addons' ),
						'job_title'       => esc_html__( 'Software Engineer', 'sky-elementor-addons' ),
						'text'            => esc_html__( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.', 'sky-elementor-addons' ),
						'image'           => [ 'url' => Utils::get_placeholder_image_src() ],
						'social_facebook' => [ 'url' => 'https://www.facebook.com/' ],
						'social_twitter'  => [ 'url' => 'https://www.twitter.com/' ],
						'social_linkedin' => [ 'url' => 'https://www.linkedin.com/' ],
					],
					[
						'name'            => esc_html__( 'Mark Doe', 'sky-elementor-addons' ),
						'job_title'       => esc_html__( 'Designer', 'sky-elementor-addons' ),
						'text'            => esc_html__( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.', 'sky-elementor-addons' ),
						'image'           => [ 'url' => Utils::get_placeholder_image_src() ],
						'social_facebook' => [ 'url' => 'https://www.facebook.com/' ],
						'social_twitter'  => [ 'url' => 'https://www.twitter.com/' ],
						'social_linkedin' => [ 'url' => 'https://www.linkedin.com/' ],
					],
					[
						'name'            => esc_html__( 'Nec Joe', 'sky-elementor-addons' ),
						'job_title'       => esc_html__( 'Manager', 'sky-elementor-addons' ),
						'text'            => esc_html__( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.', 'sky-elementor-addons' ),
						'image'           => [ 'url' => Utils::get_placeholder_image_src() ],
						'social_facebook' => [ 'url' => 'https://www.facebook.com/' ],
						'social_twitter'  => [ 'url' => 'https://www.twitter.com/' ],
						'social_linkedin' => [ 'url' => 'https://www.linkedin.com/' ],
					],
				],
				'title_field' => '{{{ name }}}',
			]
		);

		$this->end_controls_section();

		/**
		 * Query (Dynamic Posts)
		 */
		$this->start_controls_section(
			'section_query',
			[
				'label' => esc_html__( 'Query', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
				'condition' => [
					'content_type' => 'posts',
				],
			]
		);

		$this->register_query_builder_controls();

		$this->add_control(
			'posts_job_meta_key',
			[
				'label'       => esc_html__( 'Job Title Meta Key', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'separator'   => 'before',
				'dynamic'     => [ 'active' => true ],
				'description' => esc_html__( 'Optional custom field key used as each member job title. Leave empty to hide it. The post excerpt is used as the short text.', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'posts_social_heading',
			[
				'label'       => esc_html__( 'Social Links (Meta Keys)', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::HEADING,
				'separator'   => 'before',
				'description' => esc_html__( 'Optional. Map each social platform to a custom field key. Each post meta value should hold a URL.', 'sky-elementor-addons' ),
			]
		);

		foreach ( $this->get_social_platforms() as $platform_key => $platform ) {
			$this->add_control(
				'posts_' . $platform_key . '_meta_key',
				[
					// translators: %s is the social platform name (Facebook, Twitter, etc.).
					'label'   => sprintf( esc_html__( '%s Meta Key', 'sky-elementor-addons' ), $platform['label'] ),
					'type'    => Controls_Manager::TEXT,
					'dynamic' => [ 'active' => true ],
				]
			);
		}

		$this->end_controls_section();

		/**
		 * Button
		 */
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
			'posts_button_text',
			[
				'label'       => esc_html__( 'Button Text', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Read More', 'sky-elementor-addons' ),
				'dynamic'     => [ 'active' => true ],
				'description' => esc_html__( 'Button label for every post. In Repeater mode each member has its own button text.', 'sky-elementor-addons' ),
				'condition'   => [
					'content_type' => 'posts',
				],
			]
		);

		$this->add_control(
			'button_position',
			[
				'label'   => esc_html__( 'Button Position', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'after_socials',
				'options' => [
					'after_socials'  => esc_html__( 'After Social Icons', 'sky-elementor-addons' ),
					'before_socials' => esc_html__( 'Before Social Icons', 'sky-elementor-addons' ),
				],
				'condition' => [
					'style_select!' => 'ardent',
				],
			]
		);

		$this->add_control(
			'button_full_width',
			[
				'label'     => esc_html__( 'Full Width', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SWITCHER,
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'button_alignment',
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
				'condition' => [
					'button_full_width' => 'yes',
				],
				'selectors' => [
					'{{WRAPPER}} .sa-team-member .sa-button' => 'justify-content: {{VALUE}}; text-align: {{VALUE}};',
				],
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
						'title' => esc_html__( 'Left', 'sky-elementor-addons' ),
						'icon'  => 'eicon-h-align-left',
					],
					'after'  => [
						'title' => esc_html__( 'Right', 'sky-elementor-addons' ),
						'icon'  => 'eicon-h-align-right',
					],
					'top'    => [
						'title' => esc_html__( 'Top', 'sky-elementor-addons' ),
						'icon'  => 'eicon-v-align-top',
					],
					'bottom' => [
						'title' => esc_html__( 'Bottom', 'sky-elementor-addons' ),
						'icon'  => 'eicon-v-align-bottom',
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
					'{{WRAPPER}} .sa-button' => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		/**
		 * Carousel Settings
		 */
		$this->start_controls_section(
			'section_carousel_settings',
			[
				'label' => esc_html__( 'Carousel Settings', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->register_carousel_settings_controls( 'team-member-carousel' );

		$this->end_controls_section();

		/**
		 * Navigation
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
		 * Pagination
		 */
		$this->start_controls_section(
			'section_carousel_pagination',
			[
				'label' => esc_html__( 'Pagination', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->register_pagination_controls( 'team-member-carousel' );

		$this->end_controls_section();

		/**
		 * Style: Carousel Item
		 */
		$this->start_controls_section(
			'section_carousel_item_style',
			[
				'label' => esc_html__( 'Carousel Item', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
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
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-team-member-carousel .swiper' => 'padding: {{SIZE}}{{UNIT}}; margin: 0 {{SIZE}}{{UNIT}};',
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
					'{{WRAPPER}} .sa-team-member-carousel .sa-carousel-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'carousel_item_border',
				'label'    => esc_html__( 'Border', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-team-member-carousel .sa-carousel-item',
			]
		);

		$this->add_responsive_control(
			'carousel_item_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-team-member-carousel .sa-carousel-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow: hidden;',
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
				'selector' => '{{WRAPPER}} .sa-team-member-carousel .sa-carousel-item',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'carousel_item_box_shadow',
				'label'    => esc_html__( 'Box Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-team-member-carousel .sa-carousel-item',
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
					'{{WRAPPER}} .sa-team-member-carousel .sa-carousel-item' => 'opacity: {{SIZE}};',
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
				'selector' => '{{WRAPPER}} .sa-team-member-carousel .sa-carousel-item:hover',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'carousel_item_box_shadow_hover',
				'label'    => esc_html__( 'Box Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-team-member-carousel .sa-carousel-item:hover',
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
					'{{WRAPPER}} .sa-team-member-carousel .sa-carousel-item:hover' => 'opacity: {{SIZE}};',
				],
			]
		);

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
				'selector' => '{{WRAPPER}} .sa-team-member-carousel .sa-carousel-item.swiper-slide-active',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'carousel_item_box_shadow_active',
				'label'    => esc_html__( 'Box Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-team-member-carousel .sa-carousel-item.swiper-slide-active',
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
					'{{WRAPPER}} .sa-team-member-carousel .sa-carousel-item.swiper-slide-active' => 'opacity: {{SIZE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		/**
		 * Style: Member
		 */
		$this->start_controls_section(
			'section_member_style',
			[
				'label' => esc_html__( 'Member', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'member_alignment',
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
					'{{WRAPPER}} .sa-team-member' => 'text-align: {{VALUE}} !important;',
				],
			]
		);

		$this->add_responsive_control(
			'content_padding',
			[
				'label'      => esc_html__( 'Content Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', 'rem', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-team-member .sa-content-area' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'overlay_content_padding',
			[
				'label'      => esc_html__( 'Overlay Content Padding', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', 'rem', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-team-member .sa-overlay-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition'  => [
					'style_select' => [ 'folk', 'folker', 'slide' ],
				],
			]
		);

		$this->end_controls_section();

		/**
		 * Style: Mold Content
		 */
		$this->start_controls_section(
			'section_mold_Content_style',
			[
				'label' => esc_html__( 'Mold Content', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
				'condition' => [
					'style_select' => [ 'mold' ],
				],
			]
		);

		$this->start_controls_tabs( 'content_tabs' );

		$this->start_controls_tab(
			'content_tab_normal',
			[
				'label' => esc_html__( 'Normal', 'sky-elementor-addons' ),
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'content_bg',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .sa-content-area',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'content_tab_hover',
			[
				'label' => esc_html__( 'Hover', 'sky-elementor-addons' ),
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'content_bg_hover',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .sa-team-member:hover .sa-content-area::before',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		/**
		 * Style: Overlay
		 */
		$this->start_controls_section(
			'section_ardent_overlay_style',
			[
				'label' => esc_html__( 'Overlay', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
				'condition' => [
					'style_select' => [ 'ardent', 'folk', 'slide' ],
				],
			]
		);

		$this->add_control(
			'ardent_overlay_color',
			[
				'label' => esc_html__( 'Overlay Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-overlay-area' => 'background: linear-gradient(to bottom, rgba(0,0,0,0) 0%,{{VALUE}} 100%)',
					'{{WRAPPER}} .style-folk .sa-overlay-area, {{WRAPPER}} .style-slide .sa-overlay-area' => 'background: {{VALUE}}',
				],
			]
		);

		$this->add_responsive_control(
			'ardent_overlay_padding',
			[
				'label'      => esc_html__( 'Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-overlay-area' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		/**
		 * Style: Image
		 */
		$this->start_controls_section(
			'section_img_style',
			[
				'label' => esc_html__( 'Image', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'img_spacing',
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
					'{{WRAPPER}} .sa-img-area' => 'margin-bottom: {{SIZE}}{{UNIT}} !important;',
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
					'{{WRAPPER}} .sa-img-area' => '--sa-team-member-img-area-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'img_height',
			[
				'label'      => esc_html__( 'Height', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'vh' ],
				'range'      => [
					'px' => [
						'min' => 150,
						'max' => 800,
					],
					'vh' => [
						'min' => 10,
						'max' => 100,
					],
				],
				'selectors'  => [
					// Goes through the variable, not `height` — `.sa-team-member-carousel
					// .sa-team-member .sa-img-area` in team-member.less is the same specificity
					// as this selector, so a direct `height` here lost on source order and the
					// image kept the default 400px box (object-fit never re-cropped).
					'{{WRAPPER}}' => '--sa-team-member-img-height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'img_object_fit',
			[
				'label'     => esc_html__( 'Image Fit', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '',
				'options'   => [
					''        => esc_html__( 'Default (Cover)', 'sky-elementor-addons' ),
					'cover'   => esc_html__( 'Cover', 'sky-elementor-addons' ),
					'contain' => esc_html__( 'Contain', 'sky-elementor-addons' ),
					'fill'    => esc_html__( 'Fill', 'sky-elementor-addons' ),
					'none'    => esc_html__( 'None', 'sky-elementor-addons' ),
				],
				'selectors' => [
					'{{WRAPPER}}' => '--sa-team-member-img-fit: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'img_object_position',
			[
				'label'     => esc_html__( 'Image Position', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '',
				'options'   => [
					''              => esc_html__( 'Default (Top Center)', 'sky-elementor-addons' ),
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
					'{{WRAPPER}}' => '--sa-team-member-img-position: {{VALUE}};',
				],
				'condition' => [
					'img_object_fit!' => [ 'fill', 'none' ],
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
				'label'          => esc_html__( 'Horizontal Offset', 'sky-elementor-addons' ),
				'type'           => Controls_Manager::SLIDER,
				'default'        => [ 'size' => 0 ],
				'tablet_default' => [ 'size' => 0 ],
				'mobile_default' => [ 'size' => 0 ],
				'range'          => [
					'px' => [
						'min' => -300,
						'max' => 300,
					],
				],
				'render_type'    => 'ui',
				'condition'      => [
					'img_offset_popover' => 'yes',
				],
				'selectors'      => [
					'{{WRAPPER}} .sa-team-member' => '--sky-media-h-offset: {{SIZE}}px;',
				],
			]
		);

		$this->add_responsive_control(
			'img_vertical_offset',
			[
				'label'          => esc_html__( 'Vertical Offset', 'sky-elementor-addons' ),
				'type'           => Controls_Manager::SLIDER,
				'default'        => [ 'size' => 0 ],
				'tablet_default' => [ 'size' => 0 ],
				'mobile_default' => [ 'size' => 0 ],
				'range'          => [
					'px' => [
						'min' => -300,
						'max' => 300,
					],
				],
				'render_type'    => 'ui',
				'condition'      => [
					'img_offset_popover' => 'yes',
				],
				'selectors'      => [
					'{{WRAPPER}} .sa-team-member' => '--sky-media-v-offset: {{SIZE}}px;',
				],
			]
		);

		$this->add_responsive_control(
			'img_rotate',
			[
				'label'          => esc_html__( 'Rotate', 'sky-elementor-addons' ),
				'type'           => Controls_Manager::SLIDER,
				'devices'        => [ 'desktop', 'tablet', 'mobile' ],
				'default'        => [ 'size' => 0 ],
				'tablet_default' => [ 'size' => 0 ],
				'mobile_default' => [ 'size' => 0 ],
				'range'          => [
					'px' => [
						'min' => -360,
						'max' => 360,
					],
				],
				'condition'      => [
					'img_offset_popover' => 'yes',
				],
				'render_type'    => 'ui',
				'selectors'      => [
					'{{WRAPPER}} .sa-team-member' => '--sky-media-rotate: {{SIZE}}deg;',
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
					'{{WRAPPER}} .sa-img-area img' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'img_border',
				'label'    => esc_html__( 'Border', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-img-area img',
			]
		);

		$this->add_responsive_control(
			'img_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-img-area img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'img_box_shadow',
				'label'    => esc_html__( 'Box Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-img-area img',
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
					'{{WRAPPER}} .sa-img-area img' => 'opacity: {{SIZE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			[
				'name'     => 'img_css_filters',
				'selector' => '{{WRAPPER}} .sa-img-area img',
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
					'{{WRAPPER}} .sa-img-area img:hover' => 'opacity: {{SIZE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			[
				'name'     => 'img_css_filters_hover',
				'selector' => '{{WRAPPER}} .sa-img-area img:hover',
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
					'{{WRAPPER}} .sa-img-area img' => 'transition-duration: {{SIZE}}s',
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

		/**
		 * Style: Name
		 */
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
				'label'      => esc_html__( 'Bottom Spacing', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', '%' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
					'%'  => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors'  => [
					// `:not(:last-child)` on every Bottom Spacing control in this widget —
					// with the job title, text, socials and button all optional, an
					// unconditional margin-bottom left dead space under whichever element
					// happened to end the card. team-member.less zeroes the utility classes'
					// own trailing margin the same way.
					'{{WRAPPER}} .sa-name:not(:last-child)' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'name_typography',
				'label'    => esc_html__( 'Typography', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-name',
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
					'{{WRAPPER}} .sa-name' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'name_text_shadow',
				'label'    => esc_html__( 'Text Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-name',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_name_hover',
			[
				'label' => esc_html__( 'Hover', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'name_color_hover',
			[
				'label' => esc_html__( 'Text Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-team-member:hover .sa-name' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'name_text_shadow_hover',
				'label'    => esc_html__( 'Text Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-team-member:hover .sa-name',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		/**
		 * Style: Job Title
		 */
		$this->start_controls_section(
			'section_job_title_style',
			[
				'label' => esc_html__( 'Job Title', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_job_title' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'job_title_spacing',
			[
				'label'      => esc_html__( 'Bottom Spacing', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', '%' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
					'%'  => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-job-title:not(:last-child)' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'job_title_typography',
				'label'    => esc_html__( 'Typography', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-job-title',
			]
		);

		$this->start_controls_tabs( 'tabs_job_title_style' );

		$this->start_controls_tab(
			'tab_job_title_normal',
			[
				'label' => esc_html__( 'Normal', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'job_title_color',
			[
				'label' => esc_html__( 'Text Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-job-title' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'job_title_text_shadow',
				'label'    => esc_html__( 'Text Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-job-title',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_job_title_hover',
			[
				'label' => esc_html__( 'Hover', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'job_title_color_hover',
			[
				'label' => esc_html__( 'Text Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-team-member:hover .sa-job-title' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'job_title_text_shadow_hover',
				'label'    => esc_html__( 'Text Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-team-member:hover .sa-job-title',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		/**
		 * Style: Text
		 */
		$this->start_controls_section(
			'section_text_style',
			[
				'label' => esc_html__( 'Text', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
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
						'min' => 0,
						'max' => 100,
					],
					'%'  => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-text:not(:last-child)' => 'margin-bottom: {{SIZE}}{{UNIT}};',
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

		$this->start_controls_tabs( 'tabs_text_style' );

		$this->start_controls_tab(
			'tab_text_normal',
			[
				'label' => esc_html__( 'Normal', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'text_color',
			[
				'label' => esc_html__( 'Text Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-text' => 'color: {{VALUE}}',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_text_hover',
			[
				'label' => esc_html__( 'Hover', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'text_color_hover',
			[
				'label' => esc_html__( 'Text Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-team-member:hover .sa-text' => 'color: {{VALUE}}',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		/**
		 * Style: Social Icons
		 */
		$this->start_controls_section(
			'section_social_icons_style',
			[
				'label' => esc_html__( 'Social Icons', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_socials' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'social_icons_spacing',
			[
				'label'      => esc_html__( 'Spacing', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 50,
					],
				],
				'selectors'  => [
					// Feeds the list's `gap`. It used to write `margin-right` on the link AND
					// on the svg — the svg margin sat inside the link's own padding/background,
					// so the icon was pushed off-centre inside its coloured box.
					'{{WRAPPER}} .sky-social-icons-wrapper' => '--sa-social-icons-gap: {{SIZE}}{{UNIT}};',
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
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .sky-social-icons-wrapper .sa-link'     => 'font-size: {{SIZE}}{{UNIT}};',
					// Square box, not `width: auto` — Elementor's icon SVGs carry per-glyph
					// viewBoxes (facebook-f is 320x512, twitter 512x512), so `auto` gave every
					// platform a different width and the padded pills came out uneven. The
					// glyph still keeps its ratio inside the box via preserveAspectRatio.
					'{{WRAPPER}} .sky-social-icons-wrapper .sa-link svg' => 'height: {{SIZE}}{{UNIT}}; width: {{SIZE}}{{UNIT}};',
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
					'{{WRAPPER}} .sky-social-icons-wrapper .sa-link' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'social_icons_border',
				'label'    => esc_html__( 'Border', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sky-social-icons-wrapper .sa-link, {{WRAPPER}} .sky-social-icons-wrapper .sa-link svg',
			]
		);

		$this->add_responsive_control(
			'social_icon_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sky-social-icons-wrapper .sa-link'     => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .sky-social-icons-wrapper .sa-link svg' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
					'{{WRAPPER}} .sky-social-icons-wrapper .sa-link'     => 'border-radius: {{VALUE}};',
					'{{WRAPPER}} .sky-social-icons-wrapper .sa-link svg' => 'border-radius: {{VALUE}};',
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
					'{{WRAPPER}} .sky-social-icons-wrapper .sa-link'       => 'color: {{VALUE}}',
					'{{WRAPPER}} .sky-social-icons-wrapper .sa-link svg *' => 'fill: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'social_icons_bg',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .sky-social-icons-wrapper .sa-link, {{WRAPPER}} .sky-social-icons-wrapper .sa-link svg',
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
					'{{WRAPPER}} .sky-social-icons-wrapper .sa-link:hover'       => 'color: {{VALUE}}',
					'{{WRAPPER}} .sky-social-icons-wrapper .sa-link:hover svg *' => 'fill: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'social_icons_bg_hover',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .sky-social-icons-wrapper .sa-link:hover, {{WRAPPER}} .sky-social-icons-wrapper .sa-link:hover svg',
			]
		);

		$this->add_control(
			'social_icons_border_color_hover',
			[
				'label' => esc_html__( 'Border Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sky-social-icons-wrapper .sa-link:hover'     => 'border-color: {{VALUE}};',
					'{{WRAPPER}} .sky-social-icons-wrapper .sa-link:hover svg' => 'border-color: {{VALUE}};',
				],
				'condition' => [
					'social_icons_border_border!' => '',
				],
			]
		);

		$this->add_responsive_control(
			'social_icon_border_radius_hover',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sky-social-icons-wrapper .sa-link:hover'     => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .sky-social-icons-wrapper .sa-link:hover svg' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition'  => [
					'show_socials_adv_border_radius!' => 'yes',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		/**
		 * Style: Button
		 */
		$this->start_controls_section(
			'section_button_style',
			[
				'label' => esc_html__( 'Button', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_button' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'button_margin',
			[
				'label'      => esc_html__( 'Margin', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-button' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
				'label' => esc_html__( 'Animation', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::HOVER_ANIMATION,
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		/**
		 * Global Navigation Style Controls
		 */
		$this->register_navigation_style_controls( 'team-member-carousel' );

		/**
		 * Global Pagination Style Controls
		 */
		$this->register_pagination_style_controls( 'team-member-carousel' );
	}

	/**
	 * Render image (with optional alternative image on hover).
	 */
	protected function render_image( $item, $index, $settings ) {
		if ( ! empty( $settings['img_hover_animation'] ) ) {
			$item['hover_animation'] = $settings['img_hover_animation'];
		}

		$has_link  = ! empty( $item['link']['url'] );
		$has_alter = 'yes' === $settings['show_alter_image'] && ! empty( $item['alter_image']['url'] );

		// Image resolution lives on the widget (Layout section), not per member.
		foreach ( [ 'thumbnail', 'alter_thumbnail' ] as $size_key ) {
			$item[ $size_key . '_size' ]             = isset( $settings[ $size_key . '_size' ] ) ? $settings[ $size_key . '_size' ] : 'large';
			$item[ $size_key . '_custom_dimension' ] = isset( $settings[ $size_key . '_custom_dimension' ] ) ? $settings[ $size_key . '_custom_dimension' ] : '';
		}

		// Both images stay direct siblings (inside a single <a> when linked) so the
		// `.sa-img-area img:nth-child(2)` rule reliably targets the alternative image.
		if ( $has_link ) {
			$key = 'img_link_' . $index;
			$this->add_link_attributes( $key, $item['link'] );
		}
		?>
		<figure class="sa-img-area">
			<?php if ( $has_link ) : ?>
				<a <?php $this->print_render_attribute_string( $key ); ?>>
			<?php endif; ?>

			<?php echo wp_kses_post( Group_Control_Image_Size::get_attachment_image_html( $item, 'thumbnail', 'image' ) ); ?>

			<?php
			if ( $has_alter ) {
				echo wp_kses_post( Group_Control_Image_Size::get_attachment_image_html( $item, 'alter_thumbnail', 'alter_image' ) );
			}
			?>

			<?php if ( $has_link ) : ?>
				</a>
			<?php endif; ?>
		</figure>
		<?php
	}

	protected function render_name( $item, $settings ) {
		if ( empty( $item['name'] ) ) {
			return;
		}
		printf(
			'<%1$s class="sa--title sa-name sa-mt-0 sa-mb-0">%2$s</%1$s>',
			esc_attr( Utils::validate_html_tag( $settings['name_tag'] ) ),
			wp_kses_post( $item['name'] )
		);
	}

	protected function render_job_title( $item, $settings ) {
		if ( 'yes' !== $settings['show_job_title'] || empty( $item['job_title'] ) ) {
			return;
		}
		printf(
			'<%1$s class="sa--sub-title sa-job-title sa-mt-0 sa-mb-2 sa-fs-6">%2$s</%1$s>',
			esc_attr( Utils::validate_html_tag( $settings['job_title_tag'] ) ),
			wp_kses_post( $item['job_title'] )
		);
	}

	protected function render_text( $item ) {
		if ( empty( $item['text'] ) ) {
			return;
		}
		printf(
			'<div class="sa--text sa--text-info sa-text sa-mb-2 sa-fs-6">%s</div>',
			wp_kses_post( $item['text'] )
		);
	}

	protected function render_info( $item, $settings ) {
		$this->render_name( $item, $settings );
		$this->render_job_title( $item, $settings );
	}

	protected function render_button( $item, $index, $settings ) {
		$key = 'button_' . $index;

		$this->add_render_attribute( $key, 'class', [
			'sa-button',
			'sa-text-decoration-none',
			'sa-my-2',
			'sa-button-icon-' . $settings['button_icon_position'],
		] );

		if ( 'yes' === $settings['button_full_width'] ) {
			$this->add_render_attribute( $key, 'class', 'sa-button--full' );
		}

		if ( ! empty( $item['link']['url'] ) ) {
			$this->add_render_attribute( $key, 'href', esc_url( $item['link']['url'] ) );

			if ( $item['link']['is_external'] ) {
				$this->add_render_attribute( $key, 'target', '_blank' );
			}

			if ( $item['link']['nofollow'] ) {
				$this->add_render_attribute( $key, 'rel', 'nofollow' );
			}
		} else {
			$this->add_render_attribute( $key, 'href', 'javascript:void(0);' );
		}

		if ( $settings['button_hover_animation'] ) {
			$this->add_render_attribute( $key, 'class', 'elementor-animation-' . $settings['button_hover_animation'] );
		}
		?>
		<a <?php $this->print_render_attribute_string( $key ); ?>>
			<?php
			if ( ! empty( $settings['button_icon']['value'] ) ) :
				Icons_Manager::render_icon( $settings['button_icon'], [
					'aria-hidden' => 'true',
					'class'       => 'sa-button-icon',
				] );
			endif;
			?>
			<?php if ( ! empty( $item['button_text'] ) ) : ?>
				<span class="sa-button-text"><?php echo esc_html( $item['button_text'] ); ?></span>
			<?php endif; ?>
		</a>
		<?php
	}

	protected function render_socials( $item, $index ) {
		$platforms = $this->get_social_platforms();

		$has_social = false;
		foreach ( $platforms as $platform_key => $platform ) {
			if ( ! empty( $item[ $platform_key ]['url'] ) ) {
				$has_social = true;
				break;
			}
		}

		if ( ! $has_social ) {
			return;
		}
		?>
		<div class="sky-social-icons-wrapper">
			<ul class="sa-m-0 sa-p-0">
				<?php
				foreach ( $platforms as $platform_key => $platform ) :
					$link = isset( $item[ $platform_key ] ) ? $item[ $platform_key ] : [];
					if ( empty( $link['url'] ) ) {
						continue;
					}

					$key = 'social_' . $index . '_' . $platform_key;
					$this->add_render_attribute( $key, 'class', [ 'sa-link', 'sa-text-decoration-none' ] );
					$this->add_render_attribute( $key, 'href', esc_url( $link['url'] ) );

					if ( ! empty( $link['is_external'] ) ) {
						$this->add_render_attribute( $key, 'target', '_blank' );
					}

					if ( ! empty( $link['nofollow'] ) ) {
						$this->add_render_attribute( $key, 'rel', 'nofollow' );
					}
					?>
					<li>
						<a <?php $this->print_render_attribute_string( $key ); ?>>
							<?php
							Icons_Manager::render_icon( $platform['icon'], [
								'aria-hidden' => 'true',
							] );
							?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php
	}

	protected function render_socials_and_button( $item, $index, $settings ) {
		if ( 'yes' === $settings['show_button'] && 'before_socials' === $settings['button_position'] ) {
			$this->render_button( $item, $index, $settings );
		}
		if ( 'yes' === $settings['show_socials'] ) {
			$this->render_socials( $item, $index );
		}
		if ( 'yes' === $settings['show_button'] && 'after_socials' === $settings['button_position'] ) {
			$this->render_button( $item, $index, $settings );
		}
	}

	protected function style_default( $item, $index, $settings ) {
		if ( ! empty( $item['image']['url'] ) ) {
			$this->render_image( $item, $index, $settings );
		}
		?>
		<div class="sa-content-area sa-pt-3">
			<?php
			$this->render_info( $item, $settings );
			$this->render_text( $item );
			$this->render_socials_and_button( $item, $index, $settings );
			?>
		</div>
		<?php
	}

	protected function style_ardent( $item, $index, $settings ) {
		if ( ! empty( $item['image']['url'] ) ) {
			$this->render_image( $item, $index, $settings );
		}
		?>
		<div class="sa-overlay-area">
			<div class="sa-content-area">
				<?php $this->render_info( $item, $settings ); ?>
			</div>
			<?php
			if ( 'yes' === $settings['show_socials'] ) {
				$this->render_socials( $item, $index );
			}
			$this->render_text( $item );
			if ( 'yes' === $settings['show_button'] ) {
				$this->render_button( $item, $index, $settings );
			}
			?>
		</div>
		<?php
	}

	protected function style_folk( $item, $index, $settings ) {
		?>
		<div class="sa-overlay-wrapper">
			<?php
			if ( ! empty( $item['image']['url'] ) ) {
				$this->render_image( $item, $index, $settings );
			}
			?>
			<div class="sa-overlay-area">
				<div class="sa-overlay-content sa-p-4">
					<?php
					$this->render_text( $item );
					$this->render_socials_and_button( $item, $index, $settings );
					?>
				</div>
			</div>
		</div>
		<div class="sa-content-area sa-p-3">
			<?php $this->render_info( $item, $settings ); ?>
		</div>
		<?php
	}

	protected function style_folker( $item, $index, $settings ) {
		?>
		<div class="sa-overlay-wrapper">
			<?php
			if ( ! empty( $item['image']['url'] ) ) {
				$this->render_image( $item, $index, $settings );
			}
			?>
			<div class="sa-overlay-area">
				<div class="sa-overlay-content sa-p-4">
					<div class="sa-content-area sa-p-3">
						<?php
						$this->render_text( $item );
						$this->render_socials_and_button( $item, $index, $settings );
						?>
					</div>
				</div>
			</div>
		</div>
		<div class="sa-content-area sa-p-3">
			<?php $this->render_info( $item, $settings ); ?>
		</div>
		<?php
	}

	protected function style_slide( $item, $index, $settings ) {
		if ( ! empty( $item['image']['url'] ) ) {
			$this->render_image( $item, $index, $settings );
		}
		?>
		<div class="sa-overlay-area <?php echo esc_attr( $settings['slide_effect'] ); ?>">
			<div class="sa-overlay-content sa-p-4">
				<div class="sa-content-area sa-p-3">
					<?php
					$this->render_info( $item, $settings );
					$this->render_text( $item );
					$this->render_socials_and_button( $item, $index, $settings );
					?>
				</div>
			</div>
		</div>
		<?php
	}

	protected function style_mold( $item, $index, $settings ) {
		if ( ! empty( $item['image']['url'] ) ) {
			$this->render_image( $item, $index, $settings );
		}
		?>
		<div class="sa-content-area sa-p-3">
			<?php
			$this->render_info( $item, $settings );
			if ( 'yes' === $settings['show_socials'] ) {
				$this->render_socials( $item, $index );
			}
			?>
		</div>
		<?php
	}

	protected function collect_post_items( $settings ) {
		$posts_per_page = isset( $settings['posts_per_page'] ) ? (int) $settings['posts_per_page'] : 6;

		$this->query_posts( $posts_per_page );
		$query = $this->get_query();
		$items = [];

		if ( ! $query || ! $query->have_posts() ) {
			return $items;
		}

		$job_meta_key = ! empty( $settings['posts_job_meta_key'] ) ? sanitize_text_field( $settings['posts_job_meta_key'] ) : '';

		// Resolve which meta key feeds each social platform.
		$social_meta_keys = [];
		foreach ( $this->get_social_platforms() as $platform_key => $platform ) {
			$control = 'posts_' . $platform_key . '_meta_key';
			if ( ! empty( $settings[ $control ] ) ) {
				$social_meta_keys[ $platform_key ] = sanitize_text_field( $settings[ $control ] );
			}
		}

		while ( $query->have_posts() ) {
			$query->the_post();
			$post_id  = get_the_ID();
			$thumb_id = get_post_thumbnail_id( $post_id );

			$item = [
				'name'           => get_the_title(),
				'job_title'      => $job_meta_key ? (string) get_post_meta( $post_id, $job_meta_key, true ) : '',
				'text'           => get_the_excerpt(),
				'button_text'    => isset( $settings['posts_button_text'] ) ? $settings['posts_button_text'] : '',
				'image'          => [
					'id'  => $thumb_id ? $thumb_id : '',
					'url' => $thumb_id ? (string) wp_get_attachment_image_url( $thumb_id, 'full' ) : '',
				],
				'link'           => [
					'url'         => get_permalink( $post_id ),
					'is_external' => '',
					'nofollow'    => '',
				],
			];

			foreach ( $social_meta_keys as $platform_key => $meta_key ) {
				$url = (string) get_post_meta( $post_id, $meta_key, true );
				if ( '' !== $url ) {
					$item[ $platform_key ] = [
						'url'         => $url,
						'is_external' => '',
						'nofollow'    => '',
					];
				}
			}

			$items[] = $item;
		}
		wp_reset_postdata();

		return $items;
	}

	protected function render_item() {
		$settings = $this->get_settings_for_display();

		$style  = ! empty( $settings['style_select'] ) ? $settings['style_select'] : 'default';
		$method = 'style_' . $style;

		if ( ! method_exists( $this, $method ) ) {
			$style  = 'default';
			$method = 'style_default';
		}

		if ( 'posts' === $settings['content_type'] ) {
			$members = $this->collect_post_items( $settings );
		} else {
			$members = ! empty( $settings['member_list'] ) ? $settings['member_list'] : [];
		}

		if ( empty( $members ) ) {
			return;
		}

		foreach ( $members as $index => $item ) {
			$has_content = ! empty( $item['name'] ) || ! empty( $item['job_title'] ) || ! empty( $item['text'] ) || ! empty( $item['image']['url'] );

			if ( ! $has_content ) {
				continue;
			}

			$slide_class = [ 'swiper-slide', 'sa-carousel-item', 'sa-team-member', 'style-' . $style ];

			if ( 'default' === $style ) {
				$slide_class[] = 'sa-p-3';
			}
			?>
			<div class="<?php echo esc_attr( implode( ' ', $slide_class ) ); ?>">
				<?php $this->{$method}( $item, $index, $settings ); ?>
			</div>
			<?php
		}
	}

	public function render_header() {
		$id = 'sa-team-member-carousel-' . $this->get_id();

		/**
		 * Global function (Global_Swiper_Controls trait)
		 */
		$this->render_header_attributes( 'team-member-carousel' );

		$this->add_render_attribute(
			[
				'carousel' => [
					'class' => [ 'sa-team-member-carousel', 'sa-swiper-global-carousel' ],
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
		$this->render_header();

		$this->render_item();

		/**
		 * Global function (Global_Swiper_Controls trait)
		 */
		$this->render_footer();
	}
}

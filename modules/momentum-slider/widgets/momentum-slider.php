<?php

namespace Sky_Addons\Modules\MomentumSlider\Widgets;

use Elementor\Utils;
use Elementor\Icons_Manager;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Repeater;
use Elementor\Group_Control_Css_Filter;
use Elementor\Widget_Base;
use Sky_Addons\Includes\Controls\GroupQuery\Group_Control;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Momentum Slider.
 *
 * Four tracks that move as one: a number, a title and a link on the left, the images on the
 * right. The images track is the only interactive one; the other three follow it.
 *
 * Ported from the abandoned MomentumSlider vendor library to Swiper. Three things changed
 * for the better and are worth knowing before editing:
 *
 * 1. The markup is SERVER RENDERED. The old build shipped an empty container and let the
 *    library inject every slide from a JSON blob, so the widget was blank without JS and
 *    carried no crawlable text. Every slide is now real HTML.
 * 2. Everything is scoped to this widget's id. The old JS reached for
 *    `document.querySelector('.momentum-slider-pagination')`, so a second widget on the
 *    same page drove the first one's pagination.
 * 3. EVERY control ID from the old widget is preserved byte-for-byte, and only the
 *    `selectors` were repointed at the new classes. That is what keeps saved client
 *    settings applying after the update — Elementor stores values against the control ID,
 *    never the selector. Renaming any existing ID silently drops that value on every live
 *    site, so do not "tidy" them.
 */
class Momentum_Slider extends Widget_Base {

	use Group_Control;

	/**
	 * Set by query_posts() in Dynamic Posts mode.
	 *
	 * The Group_Control trait supplies the query CONTROLS and turns them into WP_Query args,
	 * but it does not run or hold the query — every consumer owns that itself. Follow
	 * modules/post-list/widgets/post-list.php if this ever needs extending.
	 *
	 * @var \WP_Query|null
	 */
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

		$this->_query = new \WP_Query( array_merge( $this->getGroupControlQueryArgs(), $args ) );
	}

	public function get_name() {
		return 'sky-momentum-slider';
	}

	public function get_title() {
		return esc_html__( 'Momentum Slider', 'sky-elementor-addons' );
	}

	public function get_icon() {
		return 'sky-icon-momentum-slider';
	}

	public function get_categories() {
		return [ 'sky-elementor-addons' ];
	}

	public function get_keywords() {
		return [ 'sky', 'momentum', 'slider', 'carousel', 'portfolio' ];
	}

	/**
	 * Swiper replaces the old `momentum` vendor handle in both lists. The vendor CSS carried
	 * every `.ms-*` rule, so it is gone from here on purpose — the widget's own stylesheet
	 * now owns the layout.
	 */
	public function get_style_depends() {
		if ( sky_addons_editor_mode() ) {
			return [ 'swiper', 'sky-addons-styles' ];
		}

		return [ 'swiper', 'sa-momentum-slider' ];
	}

	public function get_script_depends() {
		if ( sky_addons_editor_mode() ) {
			return [ 'swiper', 'sky-addons-scripts' ];
		}

		return [ 'swiper', 'sa-momentum-slider' ];
	}

	public function get_custom_help_url() {
		return 'https://skyaddons.com/docs/sky-addons/carousel-slider/momentum-slider/';
	}

	public function has_widget_inner_wrapper(): bool {
		return ! \Elementor\Plugin::$instance->experiments->is_feature_active( 'e_optimized_markup' );
	}

	protected function register_controls() {

		/*
		=====================================================================
		 * CONTENT — Slides
		 * ===================================================================== */

		$this->start_controls_section(
			'section_momentum_slider_layout',
			[
				'label' => esc_html__( 'Momentum Slider', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		// Repeater stays the default, so every existing slider keeps rendering its saved slides
		// untouched. Dynamic Posts swaps the source only — the same four tracks, the same
		// styling, filled from a query instead of by hand.
		$this->add_control(
			'content_type',
			[
				'label'   => esc_html__( 'Content Type', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.0.0' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'repeater',
				'options' => [
					'repeater' => esc_html__( 'Default (Repeater)', 'sky-elementor-addons' ),
					'posts'    => esc_html__( 'Dynamic Posts', 'sky-elementor-addons' ),
				],
			]
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'slider_title',
			[
				'label'       => esc_html__( 'Title', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Slider Title', 'sky-elementor-addons' ),
				'label_block' => true,
				'dynamic'     => [ 'active' => true ],
			]
		);

		$repeater->add_control(
			'slider_subtitle',
			[
				'label'       => esc_html__( 'Subtitle', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'dynamic'     => [ 'active' => true ],
			]
		);

		$repeater->add_control(
			'slider_image',
			[
				'label'   => esc_html__( 'Slider Image', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::MEDIA,
				'default' => [
					'url' => Utils::get_placeholder_image_src(),
				],
				'dynamic' => [ 'active' => true ],
			]
		);

		$repeater->add_control(
			'slider_link',
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
			'momentum_list',
			[
				'label'       => '',
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => [
					[
						'slider_title' => esc_html__( 'Add Your Text Here #1', 'sky-elementor-addons' ),
					],
					[
						'slider_title' => esc_html__( 'Add Your Text Here #2', 'sky-elementor-addons' ),
					],
					[
						'slider_title' => esc_html__( 'Add Your Text Here #3', 'sky-elementor-addons' ),
					],
					[
						'slider_title' => esc_html__( 'Add Your Text Here #4', 'sky-elementor-addons' ),
					],
				],
				'title_field' => '{{{ slider_title }}}',
				'condition'   => [ 'content_type' => 'repeater' ],
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

		// Sits with the title it governs. The aside is a fixed column, so one long headline
		// pushes the whole arrangement out of shape — most visible in Dynamic Posts mode, where
		// the text is not hand-written, but a typed title breaks it the same way. Characters
		// rather than words, because the column is sized in characters: two four-word titles can
		// differ by 20 characters and only one of them wraps.
		$this->add_control(
			'title_length',
			[
				'label'       => esc_html__( 'Title Character Limit', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.0.0' ),
				'type'        => Controls_Manager::NUMBER,
				'min'         => 0,
				'step'        => 1,
				'default'     => 0,
				'placeholder' => esc_html__( 'No limit', 'sky-elementor-addons' ),
				'description' => esc_html__( 'Trim titles longer than this many characters and end them with an ellipsis. 0 prints the full title.', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'number_format',
			[
				'label'   => esc_html__( 'Number Format', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'padded',
				'options' => [
					'padded' => esc_html__( 'Padded (01, 02)', 'sky-elementor-addons' ),
					'total'  => esc_html__( 'Of Total (01 / 04)', 'sky-elementor-addons' ),
				],
				'condition' => [
					'show_number' => 'yes',
				],
			]
		);

		$this->add_control(
			'number_style',
			[
				'label'       => esc_html__( 'Number Style', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'stacked',
				'options'     => [
					'stacked'   => esc_html__( 'Stacked (above the title)', 'sky-elementor-addons' ),
					'watermark' => esc_html__( 'Watermark (behind the title)', 'sky-elementor-addons' ),
				],
				'prefix_class' => 'sa-ms-num-',
				'condition'    => [
					'show_number' => 'yes',
				],
			]
		);

		// Positive switch, on by default. It replaces the old inverse `hide_number`, whose value is
		// still read in render() so a site that hid its numbers keeps them hidden.
		$this->add_control(
			'show_number',
			[
				'label'   => esc_html__( 'Show Number', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
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


		$this->end_controls_section();

		/*
		=====================================================================
		 * CONTENT — Query
		 * ===================================================================== */

		$this->start_controls_section(
			'section_momentum_slider_query',
			[
				'label'     => esc_html__( 'Query', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.0.0' ),
				'tab'       => Controls_Manager::TAB_CONTENT,
				'condition' => [ 'content_type' => 'posts' ],
			]
		);

		$this->register_query_builder_controls();

		$this->end_controls_section();

		/*
		=====================================================================
		 * CONTENT — Layout
		 * ===================================================================== */

		// Direction, height, column width, gap and alignment are decisions about the arrangement,
		// not about paint, so they sit on the Content tab beside the slides they arrange.
		//
		// The section ID keeps its `_style` suffix on purpose — it is what Elementor stores the
		// section's collapsed/expanded state against, and renaming it is churn for no gain.
		$this->start_controls_section(
			'section_momentum_slider_style',
			[
				'label' => esc_html__( 'Layout', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		// Per device, because the side-by-side layout runs out of room long before a phone: on a
		// tablet the text column was down to a two-word line. Ships stacked from tablet down.
		$this->add_responsive_control(
			'layout_direction',
			[
				'label'          => esc_html__( 'Direction', 'sky-elementor-addons' ),
				'type'           => Controls_Manager::CHOOSE,
				'options'        => [
					'row'            => [
						'title' => esc_html__( 'Text Left', 'sky-elementor-addons' ),
						'icon'  => 'eicon-h-align-left',
					],
					'row-reverse'    => [
						'title' => esc_html__( 'Text Right', 'sky-elementor-addons' ),
						'icon'  => 'eicon-h-align-right',
					],
					'column'         => [
						'title' => esc_html__( 'Text Above', 'sky-elementor-addons' ),
						'icon'  => 'eicon-v-align-top',
					],
					'column-reverse' => [
						'title' => esc_html__( 'Text Below', 'sky-elementor-addons' ),
						'icon'  => 'eicon-v-align-bottom',
					],
				],
				'default'              => 'row',
				'tablet_default'       => 'column-reverse',
				'mobile_default'       => 'column-reverse',
				'style_transfer'       => true,
				'selectors'            => [
					'{{WRAPPER}}' => '{{VALUE}}',
				],
				// Every value writes EVERY variable, including the ones it is resetting. The
				// desktop rule carries no media query, so a value that only set the stacked
				// variables would leave them set at a breakpoint that has gone back to a row.
				'selectors_dictionary' => [
					'row'            => '--sa-ms-dir: row; --sa-ms-aside-flex: 0 0 var(--sa-ms-aside-size, 32%); --sa-ms-aside-max: var(--sa-ms-aside-size, 32%); --sa-ms-img-w: auto; --sa-ms-minh-stack: var(--sa-ms-minh, 430px);',
					'row-reverse'    => '--sa-ms-dir: row-reverse; --sa-ms-aside-flex: 0 0 var(--sa-ms-aside-size, 32%); --sa-ms-aside-max: var(--sa-ms-aside-size, 32%); --sa-ms-img-w: auto; --sa-ms-minh-stack: var(--sa-ms-minh, 430px);',
					'column'         => '--sa-ms-dir: column; --sa-ms-aside-flex: 1 1 auto; --sa-ms-aside-max: 100%; --sa-ms-img-w: 100%; --sa-ms-minh-stack: 0;',
					'column-reverse' => '--sa-ms-dir: column-reverse; --sa-ms-aside-flex: 1 1 auto; --sa-ms-aside-max: 100%; --sa-ms-img-w: 100%; --sa-ms-minh-stack: 0;',
				],
			]
		);

		$this->add_responsive_control(
			'slider_height',
			[
				'label'      => esc_html__( 'Slider Height', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', '%' ],
				'range'      => [
					'px' => [
						'min'  => 300,
						'max'  => 1000,
						'step' => 5,
					],
					'%'  => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors'  => [
					'{{WRAPPER}}' => '--sa-ms-minh: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'aside_width',
			[
				'label'      => esc_html__( 'Text Column Width', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range'      => [
					'px' => [
						'min' => 160,
						'max' => 700,
					],
					'%'  => [
						'min' => 10,
						'max' => 70,
					],
				],
				'default'    => [
					'unit' => '%',
					'size' => 32,
				],
				'selectors'  => [
					'{{WRAPPER}}' => '--sa-ms-aside-size: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'aside_gap',
			[
				'label'      => esc_html__( 'Column Gap', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 200,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 40,
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-ms__layout' => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'aside_valign',
			[
				'label'     => esc_html__( 'Text Column Alignment', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
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
				// Bottom, not middle. The aside and the images track share a stretched row, so
				// bottom-aligning puts the controls row on the same baseline as the bottom edge
				// of the images — the two columns read as one composition instead of two blocks
				// that happen to sit beside each other.
				'default'   => 'flex-end',
				'selectors' => [
					'{{WRAPPER}} .sa-ms__aside' => 'justify-content: {{VALUE}};',
				],
			]
		);

		// The other axis, and per device — a stacked phone layout usually wants the column centred
		// while the desktop one stays left. prefix_class rather than selectors because `text-align`
		// alone cannot reach the controls row: that row is flex, so it needs `justify-content`, and
		// the rail has to stop growing before there is any free space to distribute.
		$this->add_responsive_control(
			'aside_align',
			[
				'label'        => esc_html__( 'Text Alignment', 'sky-elementor-addons' ),
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
				'style_transfer'       => true,
				'selectors'            => [
					'{{WRAPPER}}' => '{{VALUE}}',
				],
				'selectors_dictionary' => [
					'left'   => '--sa-ms-align: left; --sa-ms-justify: flex-start; --sa-ms-title-mi: 0; --sa-ms-rail-flex: 1 1 auto;',
					'center' => '--sa-ms-align: center; --sa-ms-justify: center; --sa-ms-title-mi: auto; --sa-ms-rail-flex: 0 1 auto;',
					'right'  => '--sa-ms-align: right; --sa-ms-justify: flex-end; --sa-ms-title-mi: auto 0 0 auto; --sa-ms-rail-flex: 0 1 auto;',
				],
			]
		);

		$this->end_controls_section();

		/*
		=====================================================================
		 * CONTENT — Settings
		 *
		 * Every ID in this section is NEW. None of them existed on the old widget, so none
		 * can collide with a saved value.
		 * ===================================================================== */

		$this->start_controls_section(
			'section_slider_settings',
			[
				'label' => esc_html__( 'Settings', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_responsive_control(
			'images_per_view',
			[
				'label'       => esc_html__( 'Images Per View', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::SLIDER,
				'range'       => [
					'px' => [
						'min'  => 1,
						'max'  => 4,
						'step' => 0.1,
					],
				],
				'default'     => [
					'size' => 1.4,
				],
				'tablet_default' => [
					'size' => 1.2,
				],
				'mobile_default' => [
					'size' => 1,
				],
				'description' => esc_html__( 'A fractional value lets the next image peek in from the edge.', 'sky-elementor-addons' ),
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
					'coverflow' => esc_html__( 'Coverflow', 'sky-elementor-addons' ),
				],
			]
		);

		$this->add_control(
			'image_parallax',
			[
				'label'       => esc_html__( 'Image Parallax', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::SWITCHER,
				'default'     => 'yes',
				'description' => esc_html__( 'The image drifts inside its frame as the slide moves — the old build\'s zoom-out effect, done properly.', 'sky-elementor-addons' ),
				'condition'   => [
					'transition_effect' => 'slide',
				],
			]
		);

		$this->add_control(
			'autoplay',
			[
				'label'     => esc_html__( 'Autoplay', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SWITCHER,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'autoplay_speed',
			[
				'label'     => esc_html__( 'Autoplay Speed (sec)', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min'  => 1,
						'max'  => 10,
						'step' => .5,
					],
				],
				'default'   => [
					'size' => 5,
				],
				'condition' => [
					'autoplay' => 'yes',
				],
			]
		);

		$this->add_control(
			'pause_on_hover',
			[
				'label'     => esc_html__( 'Pause On Hover', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'condition' => [
					'autoplay' => 'yes',
				],
			]
		);

		$this->add_control(
			'loop',
			[
				'label'     => esc_html__( 'Loop', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SWITCHER,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'speed',
			[
				'label'   => esc_html__( 'Slide Speed (sec)', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::SLIDER,
				'range'   => [
					'px' => [
						'min'  => 0,
						'max'  => 3,
						'step' => .1,
					],
				],
				'default' => [
					'size' => .6,
				],
			]
		);

		$this->add_control(
			'drag_to_slide',
			[
				'label'     => esc_html__( 'Drag To Slide', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'separator' => 'before',
			]
		);

		$this->add_control(
			'grab_cursor',
			[
				'label'     => esc_html__( 'Grab Cursor', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'condition' => [
					'drag_to_slide' => 'yes',
				],
			]
		);

		$this->add_control(
			'keyboard_control',
			[
				'label' => esc_html__( 'Keyboard Control', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::SWITCHER,
			]
		);

		$this->add_control(
			'mousewheel_control',
			[
				'label' => esc_html__( 'Mousewheel Control', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::SWITCHER,
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

		$this->end_controls_section();

		/*
		=====================================================================
		 * CONTENT — Button / Link
		 * ===================================================================== */

		$this->start_controls_section(
			'section_button',
			[
				'label'     => esc_html__( 'Button / Link', 'sky-elementor-addons' ),
				'tab'       => Controls_Manager::TAB_CONTENT,
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
				'default' => esc_html__( 'Read more', 'sky-elementor-addons' ),
				'dynamic' => [ 'active' => true ],
			]
		);

		// The expanding circle is the only thing in this widget that cannot take its colour from
		// `currentColor`: the label and the icons flip to the hover colour while the circle has to
		// stay the accent behind them, so it needs a value of its own.
		$this->add_control(
			'button_fill_color',
			[
				'label'     => esc_html__( 'Fill Color', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-ms-link' => '--sa-ms-btn-fill: {{VALUE}};',
				],
			]
		);

		// Default is EMPTY on purpose. It used to be `fas fa-long-arrow-alt-right`, which only
		// renders if the site loads FontAwesome — on a kit that has it disabled the button showed
		// a blank box. Empty falls through to the inline SVG in render_button_inner(), so the
		// default works everywhere with no icon-font dependency. A site that explicitly saved the
		// old FontAwesome value still renders exactly that.
		$this->add_control(
			'button_icon',
			[
				'label'       => esc_html__( 'Icon', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::ICONS,
				'label_block' => true,
				'description' => esc_html__( 'Leave empty to use the built-in arrow.', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'button_hide_icon',
			[
				'label' => esc_html__( 'Hide Icon', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'  => Controls_Manager::SWITCHER,
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
				'default'    => [
					'unit' => 'px',
					'size' => 5,
				],
				'condition'  => [
					'button_icon[value]!' => '',
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-ms-link .sa-icon-before' => 'margin-right: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .sa-ms-link .sa-icon-after'  => 'margin-left: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();


		/*
		=====================================================================
		 * CONTENT — Navigation & Pagination
		 * ===================================================================== */

		$this->start_controls_section(
			'section_navigation',
			[
				'label' => esc_html__( 'Navigation & Pagination', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		// Defaults ON. This control is new, so the default reaches every existing site — arrows
		// will appear on pages that previously had none. That is the intended modernisation;
		// switch it off per-widget if a layout needs the old pagination-only footer.
		$this->add_control(
			'show_navigation',
			[
				'label'   => esc_html__( 'Show Navigation', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		// Positive switch, on by default — the same shape as Show Navigation above it. It replaces
		// the old inverse `hide_pagination`, whose value is still read in render() so a site that
		// hid its pagination before this change keeps it hidden.
		$this->add_control(
			'show_pagination',
			[
				'label'   => esc_html__( 'Show Pagination', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'prev_icon',
			[
				'label'     => esc_html__( 'Previous Icon', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::ICONS,
				'condition' => [
					'show_navigation' => 'yes',
				],
			]
		);

		$this->add_control(
			'next_icon',
			[
				'label'     => esc_html__( 'Next Icon', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::ICONS,
				'condition' => [
					'show_navigation' => 'yes',
				],
			]
		);

		$this->end_controls_section();


		/*
		=====================================================================
		 * STYLE — Number
		 * ===================================================================== */

		$this->start_controls_section(
			'section_number_style',
			[
				'label'     => esc_html__( 'Number', 'sky-elementor-addons' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_number' => 'yes',
				],
			]
		);

		$this->add_control(
			'number_color',
			[
				'label'     => esc_html__( 'Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-ms-number' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'number_background',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .sa-ms-number',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'number_typography',
				'label'    => esc_html__( 'Typography', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-ms-number',
			]
		);

		$this->add_responsive_control(
			'number_offset_x',
			[
				'label'      => esc_html__( 'Horizontal Offset', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range'      => [
					'px' => [
						'min' => -300,
						'max' => 300,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-ms-numbers' => 'margin-left: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'number_offset_y',
			[
				'label'      => esc_html__( 'Vertical Offset', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range'      => [
					'px' => [
						'min' => -300,
						'max' => 300,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-ms-numbers' => 'margin-top: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		/*
		=====================================================================
		 * STYLE — Title
		 * ===================================================================== */

		$this->start_controls_section(
			'section_title_style',
			[
				'label' => esc_html__( 'Title', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'title_color',
			[
				'label'     => esc_html__( 'Text Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-ms-title' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'title_background',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .sa-ms-titles .swiper-slide',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'title_typography',
				'label'    => esc_html__( 'Typography', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-ms-title',
			]
		);

		// A measure control. em, not px, so the value holds when the font size changes and at
		// every breakpoint — 10em is roughly 18 characters on a typical sans. `ch` would say this
		// more literally, but Elementor's classic slider has no range for it (only the v4 atomic
		// widgets know the unit), so it misbehaves in the panel.
		$this->add_responsive_control(
			'title_max_width',
			[
				'label'      => esc_html__( 'Max Width', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'em', 'px', '%' ],
				'range'      => [
					'em' => [
						'min'  => 3,
						'max'  => 30,
						'step' => .5,
					],
					'px' => [
						'min' => 80,
						'max' => 900,
					],
					'%'  => [
						'min' => 20,
						'max' => 100,
					],
				],
				'default'    => [
					'unit' => 'em',
					'size' => 10,
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-ms-title' => 'max-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'title_padding',
			[
				'label'      => esc_html__( 'Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-ms-title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'subtitle_heading',
			[
				'label'     => esc_html__( 'Subtitle', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'subtitle_color',
			[
				'label'     => esc_html__( 'Text Color', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-ms-subtitle' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'subtitle_typography',
				'label'    => esc_html__( 'Typography', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'selector' => '{{WRAPPER}} .sa-ms-subtitle',
			]
		);

		$this->add_responsive_control(
			'subtitle_spacing',
			[
				'label'      => esc_html__( 'Spacing', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 60,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-ms-subtitle' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		/*
		=====================================================================
		 * STYLE — Button / Link
		 * ===================================================================== */

		$this->start_controls_section(
			'section_button_style',
			[
				'label'     => esc_html__( 'Button / Link', 'sky-elementor-addons' ),
				'tab'       => Controls_Manager::TAB_STYLE,
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
					'{{WRAPPER}} .sa-ms-link' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'button_typography',
				'label'    => esc_html__( 'Typography', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-ms-link',
			]
		);

		$this->start_controls_tabs( 'tabs_button_style' );

		$this->start_controls_tab(
			'tab_button_normal',
			[
				'label' => esc_html__( 'Normal', 'sky-elementor-addons' ),
			]
		);

		$this->add_responsive_control(
			'button_width',
			[
				'label'      => esc_html__( 'Width', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', '%' ],
				'range'      => [
					'px' => [
						'min' => 50,
						'max' => 400,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-ms-link' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'button_color',
			[
				'label'     => esc_html__( 'Text Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-ms-link' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'button_background',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .sa-ms-link',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'button_border',
				'label'    => esc_html__( 'Border', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-ms-link',
			]
		);

		$this->add_responsive_control(
			'button_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-ms-link' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow: hidden;',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'button_text_shadow',
				'label'    => esc_html__( 'Text Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-ms-link',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'button_box_shadow',
				'label'    => esc_html__( 'Box Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-ms-link',
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
				'label'     => esc_html__( 'Text Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					// The var is for the Modern style only. Its hover rule has to out-rank a theme
					// reset (`a:not([href]):hover { color: inherit }`), which puts it above this
					// control's own specificity — so it reads the value from here instead.
					'{{WRAPPER}} .sa-ms-link:hover' => 'color: {{VALUE}}; --sa-ms-btn-ink: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'button_background_hover',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .sa-ms-link:hover',
			]
		);

		$this->add_control(
			'button_border_color_hover',
			[
				'label'     => esc_html__( 'Border Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-ms-link:hover' => 'border-color: {{VALUE}};',
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
					'{{WRAPPER}} .sa-ms-link:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow: hidden;',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'button_text_shadow_hover',
				'label'    => esc_html__( 'Text Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-ms-link:hover',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'button_box_shadow_hover',
				'label'    => esc_html__( 'Box Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-ms-link:hover',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		/*
		=====================================================================
		 * STYLE — Images
		 * ===================================================================== */

		$this->start_controls_section(
			'section_img_style',
			[
				'label' => esc_html__( 'Images', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		// img_width and img_spacing are carried over from the pre-Swiper build. Their old
		// selectors are gone, but the saved numbers still mean what they meant, so both are
		// re-pointed rather than dropped — removing either would silently discard a value on
		// every site that set it.
		//
		// img_width sized `.ms--images .ms-slide` directly. Swiper owns the slide width now
		// (slidesPerView), so it caps the image frame INSIDE the slide instead — same visual
		// question, "how wide is the picture".
		$this->add_responsive_control(
			'img_width',
			[
				'label'      => esc_html__( 'Width', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				// 'em' is kept because the old control offered it. Dropping a unit an existing
				// site already saved leaves that value un-editable in the panel.
				'size_units' => [ 'px', 'em', '%' ],
				'range'      => [
					'px' => [
						'min' => 200,
						'max' => 1000,
					],
					'%'  => [
						'min' => 10,
						'max' => 100,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-ms-image-frame' => 'max-width: {{SIZE}}{{UNIT}}; margin-inline: auto;',
				],
			]
		);

		// The ratio is what actually gives the images a size. Height is optional and overrides
		// it; with neither, the slide has no intrinsic height and collapsed to nothing as soon
		// as the columns stacked on mobile — the images simply vanished.
		$this->add_responsive_control(
			'img_ratio',
			[
				'label'   => esc_html__( 'Image Ratio', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '4/3',
				'options' => [
					'1/1'   => esc_html__( 'Square (1:1)', 'sky-elementor-addons' ),
					'4/3'   => esc_html__( 'Landscape (4:3)', 'sky-elementor-addons' ),
					'3/2'   => esc_html__( 'Classic (3:2)', 'sky-elementor-addons' ),
					'16/9'  => esc_html__( 'Wide (16:9)', 'sky-elementor-addons' ),
					'3/4'   => esc_html__( 'Portrait (3:4)', 'sky-elementor-addons' ),
					'2/3'   => esc_html__( 'Tall (2:3)', 'sky-elementor-addons' ),
				],
				'selectors' => [
					'{{WRAPPER}} .sa-momentum-slider' => '--sa-ms-image-ratio: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'img_height',
			[
				'label'       => esc_html__( 'Height', 'sky-elementor-addons' ),
				'description' => esc_html__( 'Optional. Overrides the ratio above.', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'vh' ],
				'range'      => [
					'px' => [
						'min' => 200,
						'max' => 1000,
					],
				],
				// Writes the ratio off as well: an explicit height plus a 100% width would leave
				// both axes definite, and `aspect-ratio` would be ignored rather than overridden.
				'selectors'  => [
					'{{WRAPPER}} .sa-momentum-slider' => '--sa-ms-image-h: {{SIZE}}{{UNIT}}; --sa-ms-image-ratio: auto;',
				],
			]
		);

		$this->add_responsive_control(
			'img_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-ms-image-frame' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'img_opacity',
			[
				'label'     => esc_html__( 'Opacity', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'max'  => 1,
						'min'  => 0.10,
						'step' => 0.01,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .sa-ms-image' => 'opacity: {{SIZE}};',
				],
			]
		);

		$this->add_control(
			'img_dim_inactive',
			[
				'label'       => esc_html__( 'Dim Inactive Slides', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'        => Controls_Manager::SLIDER,
				'range'       => [
					'px' => [
						'max'  => 1,
						'min'  => 0,
						'step' => 0.05,
					],
				],
				'description' => esc_html__( 'Opacity applied to every slide except the active one.', 'sky-elementor-addons' ),
				'selectors'   => [
					'{{WRAPPER}} .sa-ms-images .swiper-slide:not(.swiper-slide-active) .sa-ms-image-frame' => 'opacity: {{SIZE}};',
				],
			]
		);

		// img_spacing used to fake the gap with `width: calc(100% - Npx)` on the image
		// container. Swiper has a real option for it, so the value is fed to spaceBetween in
		// get_slider_settings() instead of writing CSS — hence no `selectors` here.
		$this->add_control(
			'img_spacing',
			[
				'label'      => esc_html__( 'Space Between', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				// See img_width: 'em' stays for the sake of already-saved values, even though
				// Swiper's spaceBetween below only consumes the number.
				'size_units' => [ 'px', 'em' ],
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
				'render_type' => 'template',
			]
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			[
				'name'     => 'img_css_filters',
				'selector' => '{{WRAPPER}} .sa-ms-image',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'img_box_shadow',
				'label'    => esc_html__( 'Box Shadow', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'selector' => '{{WRAPPER}} .sa-ms-image-frame',
			]
		);

		$this->end_controls_section();

		/*
		=====================================================================
		 * STYLE — Pagination
		 *
		 * pagination_height / pagination_width / pagination_color / pagination_color_active
		 * keep their old IDs. They used to paint `.pagination__button` and its two
		 * pseudo-elements; they now paint Swiper's bullets, so a saved value still reads as
		 * the same visual choice.
		 * ===================================================================== */

		$this->start_controls_section(
			'section_pagination_style',
			[
				'label'     => esc_html__( 'Pagination', 'sky-elementor-addons' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_pagination' => 'yes',
				],
			]
		);

		$this->add_control(
			'pagination_shape',
			[
				'label'   => esc_html__( 'Shape', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'rail',
				'options' => [
					'rail' => esc_html__( 'Rail (one line, active segment fills)', 'sky-elementor-addons' ),
					'pill' => esc_html__( 'Pill (active expands)', 'sky-elementor-addons' ),
				],
				'prefix_class' => 'sa-ms-bullet-',
			]
		);

		$this->add_control(
			'pagination_timeline_notice',
			[
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => esc_html__( 'The Rail fills over the Autoplay Speed. With Autoplay off the active segment fills in half a second on each slide change.', 'sky-elementor-addons' ),
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
				'condition'       => [
					'pagination_shape' => 'rail',
				],
			]
		);

		$this->add_responsive_control(
			'pagination_height',
			[
				'label'      => esc_html__( 'Height', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range'      => [
					'px' => [
						'min' => 1,
						'max' => 50,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-momentum-slider .swiper-pagination-bullet' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'pagination_width',
			[
				'label'      => esc_html__( 'Width', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range'      => [
					'px' => [
						'min' => 4,
						'max' => 100,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-momentum-slider .swiper-pagination-bullet' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'pagination_spacing',
			[
				'label'      => esc_html__( 'Spacing', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 40,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-momentum-slider .swiper-pagination' => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		// Both colour controls keep their original IDs and deliberately cover ALL three
		// pagination types — an existing site's saved colour stays meaningful whichever type
		// it later switches to, instead of appearing to do nothing.
		$this->add_control(
			'pagination_color',
			[
				'label'     => esc_html__( 'Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					// `color` as well as `background-color`: a Numbers bullet is text with no
					// background, and the Timeline fill is painted with currentColor — both
					// would ignore a background-only rule.
					'{{WRAPPER}} .sa-momentum-slider .swiper-pagination-bullet' => 'background-color: {{VALUE}}; color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'pagination_color_active',
			[
				'label'     => esc_html__( 'Active Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-momentum-slider .swiper-pagination-bullet-active' => 'background-color: {{VALUE}}; color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		/*
		=====================================================================
		 * STYLE — Navigation
		 * ===================================================================== */

		$this->start_controls_section(
			'section_navigation_style',
			[
				'label'     => esc_html__( 'Navigation', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'tab'       => Controls_Manager::TAB_STYLE,
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
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 20,
						'max' => 100,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-momentum-slider .sa-swiper-button-prev, {{WRAPPER}} .sa-momentum-slider .sa-swiper-button-next' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'navigation_icon_size',
			[
				'label'      => esc_html__( 'Icon Size', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 6,
						'max' => 40,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-momentum-slider .sa-swiper-button-prev, {{WRAPPER}} .sa-momentum-slider .sa-swiper-button-next' => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .sa-momentum-slider .sa-swiper-button-prev svg, {{WRAPPER}} .sa-momentum-slider .sa-swiper-button-next svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->start_controls_tabs( 'tabs_navigation_style' );

		$this->start_controls_tab(
			'tab_navigation_normal',
			[
				'label' => esc_html__( 'Normal', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'navigation_color',
			[
				'label'     => esc_html__( 'Icon Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-momentum-slider .sa-swiper-button-prev, {{WRAPPER}} .sa-momentum-slider .sa-swiper-button-next' => 'color: {{VALUE}}; fill: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'navigation_bg',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .sa-momentum-slider .sa-swiper-button-prev, {{WRAPPER}} .sa-momentum-slider .sa-swiper-button-next',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'navigation_border',
				'label'    => esc_html__( 'Border', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-momentum-slider .sa-swiper-button-prev, {{WRAPPER}} .sa-momentum-slider .sa-swiper-button-next',
			]
		);

		$this->add_responsive_control(
			'navigation_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-momentum-slider .sa-swiper-button-prev, {{WRAPPER}} .sa-momentum-slider .sa-swiper-button-next' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_navigation_hover',
			[
				'label' => esc_html__( 'Hover', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'navigation_color_hover',
			[
				'label'     => esc_html__( 'Icon Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-momentum-slider .sa-swiper-button-prev:hover, {{WRAPPER}} .sa-momentum-slider .sa-swiper-button-next:hover' => 'color: {{VALUE}}; fill: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'navigation_bg_hover',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .sa-momentum-slider .sa-swiper-button-prev:hover, {{WRAPPER}} .sa-momentum-slider .sa-swiper-button-next:hover',
			]
		);

		$this->add_control(
			'navigation_border_color_hover',
			[
				'label'     => esc_html__( 'Border Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-momentum-slider .sa-swiper-button-prev:hover, {{WRAPPER}} .sa-momentum-slider .sa-swiper-button-next:hover' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	/**
	 * How many digits a slide number is padded to.
	 *
	 * The old build hard-coded `'0' . $i`, which made slide 10 read "010". Padding to the width of
	 * the total fixes that without changing anything for the 1–9 case every existing site is in.
	 *
	 * The JS re-pads on every slide change and reads this width from `data-pad`, so the rule
	 * lives here only.
	 */
	private function get_number_pad( $total ) {
		return max( 2, strlen( (string) $total ) );
	}

	private function pad_number( $number, $pad ) {
		return str_pad( (string) $number, (int) $pad, '0', STR_PAD_LEFT );
	}

	/**
	 * Swiper config, written once onto the root element.
	 *
	 * Only the images track is described here. The three text tracks are created by the JS
	 * with a fixed config and bound to this one through Swiper's controller module, so they
	 * cannot drift out of sync with it.
	 */
	private function get_slider_settings( $settings, $id ) {
		// Null-coalesced throughout. get_settings_for_display() does fill in defaults for
		// registered controls, but a page saved by an older version can still reach render()
		// with a stale settings array during the same request that registers the new ones,
		// and a PHP warning on a client's front page is not worth the two characters saved.
		$settings = array_merge(
			[
				'transition_effect'  => 'slide',
				'loop'               => '',
				'autoplay'           => '',
				'pause_on_hover'     => '',
				'drag_to_slide'      => 'yes',
				'grab_cursor'        => 'yes',
				'keyboard_control'   => '',
				'mousewheel_control' => '',
				'observer'           => 'yes',
			],
			array_filter(
				$settings,
				static function ( $value ) {
					return null !== $value;
				}
			)
		);

		$effect = ! empty( $settings['transition_effect'] ) ? $settings['transition_effect'] : 'slide';

		$per_view        = ! empty( $settings['images_per_view']['size'] ) ? (float) $settings['images_per_view']['size'] : 1.4;
		$per_view_tablet = ! empty( $settings['images_per_view_tablet']['size'] ) ? (float) $settings['images_per_view_tablet']['size'] : 1.2;
		$per_view_mobile = ! empty( $settings['images_per_view_mobile']['size'] ) ? (float) $settings['images_per_view_mobile']['size'] : 1;

		$breakpoints = sky_addons_get_swiper_breakpoints();

		// Fade and coverflow both break with a fractional slidesPerView — fade stacks every
		// slide on the same spot, and coverflow needs whole slides to rotate around.
		if ( in_array( $effect, [ 'fade', 'coverflow' ], true ) ) {
			$per_view        = 1;
			$per_view_tablet = 1;
			$per_view_mobile = 1;
		}

		$config = [
			'effect'         => $effect,
			'slidesPerView'  => $per_view_mobile,
			'spaceBetween'   => isset( $settings['img_spacing']['size'] ) ? (int) $settings['img_spacing']['size'] : 20,
			'speed'          => isset( $settings['speed']['size'] ) ? (int) ( $settings['speed']['size'] * 1000 ) : 600,
			'loop'           => 'yes' === $settings['loop'],
			'grabCursor'     => 'yes' === $settings['drag_to_slide'] && 'yes' === $settings['grab_cursor'],
			'allowTouchMove' => 'yes' === $settings['drag_to_slide'],
			'observer'       => 'yes' === $settings['observer'],
			'observeParents' => 'yes' === $settings['observer'],
			'watchSlidesProgress' => true,
			'autoplay'       => 'yes' === $settings['autoplay'] ? [
				'delay'                => isset( $settings['autoplay_speed']['size'] ) ? (int) ( $settings['autoplay_speed']['size'] * 1000 ) : 5000,
				'disableOnInteraction' => false,
			] : false,
			'pauseOnHover'   => 'yes' === $settings['autoplay'] && 'yes' === $settings['pause_on_hover'],
			'keyboard'       => 'yes' === $settings['keyboard_control'] ? [ 'enabled' => true ] : false,
			'mousewheel'     => 'yes' === $settings['mousewheel_control'],
			'breakpoints'    => [
				(int) $breakpoints['md'] => [ 'slidesPerView' => $per_view_tablet ],
				(int) $breakpoints['lg'] => [ 'slidesPerView' => $per_view ],
			],
			'navigation'     => [
				'nextEl' => "#$id .sa-swiper-button-next",
				'prevEl' => "#$id .sa-swiper-button-prev",
			],
			'pagination'     => [
				'el'        => "#$id .swiper-pagination",
				'clickable' => true,
				'type'      => 'bullets',
			],
		];

		if ( 'fade' === $effect ) {
			$config['fadeEffect'] = [ 'crossFade' => true ];
		}

		return $config;
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		// Dynamic Posts builds the same item shape the repeater saves, so everything below this
		// line stays source-agnostic — one render path, not two.
		$items = 'posts' === ( $settings['content_type'] ?? 'repeater' )
			? $this->collect_post_items( $settings )
			: ( ! empty( $settings['momentum_list'] ) ? $settings['momentum_list'] : [] );

		if ( empty( $items ) ) {
			// A repeater with no rows stays silent, as it always has. A query that matched nothing
			// says so, because in the editor an empty widget reads as a broken one.
			if ( 'posts' === ( $settings['content_type'] ?? 'repeater' ) ) {
				echo '<p class="sa-no-posts">' . esc_html__( 'No posts found.', 'sky-elementor-addons' ) . '</p>';
			}

			return;
		}

		$title_chars = isset( $settings['title_length'] ) ? (int) $settings['title_length'] : 0;

		$id          = 'sa-ms-' . $this->get_id();
		$total       = count( $items );
		$title_tag   = Utils::validate_html_tag( $settings['title_tag'] ?? 'h3' );
		$show_button = 'yes' === ( $settings['show_button'] ?? '' );
		$has_number  = 'yes' === ( $settings['show_number'] ?? 'yes' ) && 'yes' !== ( $settings['hide_number'] ?? '' );
		// The legacy `hide_pagination` still wins when it is on: a site that turned the old inverse
		// switch off keeps its pagination hidden instead of having it reappear on upgrade.
		$show_pager  = 'yes' === ( $settings['show_pagination'] ?? 'yes' ) && 'yes' !== ( $settings['hide_pagination'] ?? '' );
		$show_nav    = 'yes' === ( $settings['show_navigation'] ?? '' );
		$parallax    = 'yes' === ( $settings['image_parallax'] ?? '' ) && 'slide' === ( $settings['transition_effect'] ?? 'slide' );

		$this->add_render_attribute(
			'momentum-slider',
			[
				'class'         => 'sa-momentum-slider',
				'id'            => $id,
				'data-settings' => wp_json_encode( $this->get_slider_settings( $settings, $id ) ),
			]
		);
		?>
		<div <?php $this->print_render_attribute_string( 'momentum-slider' ); ?>>
			<div class="sa-ms__layout">

				<div class="sa-ms__aside">

					<?php if ( $has_number ) : ?>
						<?php
						/*
						 * A COUNTER, not a track. This used to be a fourth Swiper carried
						 * sideways with the title; it now stays put and only its digits change.
						 * data-pad sends the padding width rather than the format, so the rule
						 * lives in get_number_pad() and is not reimplemented in JS.
						 */
						$number_format = $settings['number_format'] ?? 'padded';
						$pad           = $this->get_number_pad( $total );
						?>
						<div class="sa-ms-numbers" data-pad="<?php echo esc_attr( $pad ); ?>">
							<span class="sa-ms-number">
								<span class="sa-ms-number-current"><?php echo esc_html( $this->pad_number( 1, $pad ) ); ?></span>
								<?php if ( 'total' === $number_format ) : ?>
									<span class="sa-ms-number-sep" aria-hidden="true">/</span>
									<span class="sa-ms-number-total"><?php echo esc_html( $this->pad_number( $total, $pad ) ); ?></span>
								<?php endif; ?>
							</span>
						</div>
					<?php endif; ?>

					<div class="sa-ms-titles swiper">
						<div class="swiper-wrapper">
							<?php foreach ( $items as $index => $item ) : ?>
								<div class="swiper-slide">
									<?php if ( ! empty( $item['slider_subtitle'] ) ) : ?>
										<div class="sa-ms-subtitle"><?php echo esc_html( $item['slider_subtitle'] ); ?></div>
									<?php endif; ?>
									<<?php echo esc_attr( $title_tag ); ?> class="sa-ms-title"><?php echo esc_html( $this->get_slide_title( $item['slider_title'] ?? '', $title_chars ) ); ?></<?php echo esc_attr( $title_tag ); ?>>
								</div>
							<?php endforeach; ?>
						</div>
					</div>

					<?php if ( $show_button ) : ?>
						<div class="sa-ms-links swiper">
							<div class="swiper-wrapper">
								<?php
								foreach ( $items as $index => $item ) :
									$link_key = 'momentum_link_' . $index;
									$this->add_render_attribute( $link_key, 'class', 'sa-ms-link sa-link sa-text-decoration-none' );
									$this->add_link_attributes( $link_key, $item['slider_link'] ?? [], true );
									?>
									<div class="swiper-slide">
										<a <?php $this->print_render_attribute_string( $link_key ); ?>>
											<?php $this->render_button_inner( $settings ); ?>
										</a>
									</div>
								<?php endforeach; ?>
							</div>
						</div>
					<?php endif; ?>

					<?php if ( $show_pager || $show_nav ) : ?>
						<div class="sa-ms__controls">
							<?php if ( $show_nav ) : ?>
								<?php $this->render_navigation( $settings ); ?>
							<?php endif; ?>
							<?php if ( $show_pager ) : ?>
								<div class="swiper-pagination"></div>
							<?php endif; ?>
						</div>
					<?php endif; ?>

				</div>

				<div class="sa-ms-images swiper">
					<div class="swiper-wrapper">
						<?php
						foreach ( $items as $index => $item ) :
							// A post with no featured image would otherwise render url('') and paint
							// nothing but the frame.
							$image_url = ! empty( $item['slider_image']['url'] ) ? $item['slider_image']['url'] : Utils::get_placeholder_image_src();
							?>
							<div class="swiper-slide">
								<div class="sa-ms-image-frame">
									<div class="sa-ms-image"
										<?php if ( $parallax ) : ?>data-swiper-parallax="20%"<?php endif; ?>
										style="background-image: url('<?php echo esc_url( $image_url ); ?>');"
										role="img"
										aria-label="<?php echo esc_attr( $item['slider_title'] ?? '' ); ?>"></div>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>

			</div>
		</div>
		<?php
	}

	/**
	 * Dynamic Posts mode: turn the query into the shape the repeater saves.
	 *
	 * Title comes from the post, subtitle from its first term, image from the featured image
	 * and the link from the permalink, so a slider built by hand and one built from a query
	 * render through exactly the same code below.
	 */
	protected function collect_post_items( $settings ) {
		$this->query_posts( isset( $settings['posts_per_page'] ) ? (int) $settings['posts_per_page'] : 6 );

		$query = $this->get_query();
		$items = [];

		if ( ! $query || ! $query->have_posts() ) {
			return $items;
		}

		while ( $query->have_posts() ) {
			$query->the_post();

			$image_id = get_post_thumbnail_id();

			$items[] = [
				'slider_title'    => get_the_title(),
				'slider_subtitle' => $this->get_post_primary_term( get_the_ID() ),
				'slider_image'    => [
					'id'  => $image_id,
					'url' => $image_id ? wp_get_attachment_image_url( $image_id, 'full' ) : '',
				],
				'slider_link'     => [
					'url'         => get_permalink(),
					'is_external' => '',
					'nofollow'    => '',
				],
			];
		}

		wp_reset_postdata();

		return $items;
	}

	/**
	 * The subtitle in Dynamic Posts mode: the post's first term.
	 *
	 * `category` when the post type has it, otherwise the first PUBLIC taxonomy registered to
	 * that post type — a custom post type gets its own label rather than an empty line, and
	 * skipping the non-public ones keeps `post_format` out of the subtitle on plain posts.
	 */
	protected function get_post_primary_term( $post_id ) {
		$taxonomies = get_object_taxonomies( get_post_type( $post_id ), 'objects' );

		if ( empty( $taxonomies ) ) {
			return '';
		}

		$taxonomy = '';

		if ( isset( $taxonomies['category'] ) ) {
			$taxonomy = 'category';
		} else {
			foreach ( $taxonomies as $name => $object ) {
				if ( ! empty( $object->public ) && 'post_format' !== $name ) {
					$taxonomy = $name;
					break;
				}
			}
		}

		if ( ! $taxonomy ) {
			return '';
		}

		$terms = get_the_terms( $post_id, $taxonomy );

		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return '';
		}

		$term = reset( $terms );

		return $term->name;
	}

	/**
	 * Title, trimmed to the Character Limit when one is set.
	 *
	 * Cutting goes through mb_substr, never substr: a multibyte title cut on a byte boundary
	 * comes back with a broken character on the end. The ellipsis is the literal character rather than the &hellip;
	 * entity, because the caller escapes with esc_html() and the entity would print as text.
	 * It is appended only when the title was actually longer than the limit, so a short title
	 * ends clean, and the cut is right-trimmed first so the ellipsis never follows a space.
	 */
	protected function get_slide_title( $title, $char_limit ) {
		$title = wp_strip_all_tags( $title );

		if ( $char_limit < 1 || mb_strlen( $title ) <= $char_limit ) {
			return $title;
		}

		return rtrim( mb_substr( $title, 0, $char_limit ) ) . '…';
	}

	/**
	 * Button label + icon.
	 *
	 * Icons_Manager, not a hand-built `<i>` tag. The old build read
	 * `$settings['button_icon']['value']` straight into a class attribute, which silently
	 * dropped every SVG and custom-library icon — those store an array, not a class string.
	 */
	private function render_button_inner( $settings ) {
		$show_icon = 'yes' !== ( $settings['button_hide_icon'] ?? '' );
		$custom    = ! empty( $settings['button_icon']['value'] );

		// TWO icons — one parked off the left edge, one leaving to the right — with the label and
		// the expanding circle between them. There is no Icon Position control because both sides
		// are occupied by design.
		if ( $show_icon ) {
			$this->render_button_icon( $settings, 'sa-icon-lead', $custom );
		}

		if ( ! empty( $settings['button_text'] ) ) {
			echo '<span class="sa-button-text">' . wp_kses_post( $settings['button_text'] ) . '</span>';
		}

		echo '<span class="sa-ms-link-fill" aria-hidden="true"></span>';

		if ( $show_icon ) {
			$this->render_button_icon( $settings, 'sa-icon-after', $custom );
		}
	}

	/**
	 * The button's icon slot.
	 *
	 * Falls back to an inline stroked arrow when no icon is chosen, so the shipped default
	 * needs no icon font. It is drawn with `stroke: currentColor`, which means the Text Color
	 * control drives it exactly like the label — a filled glyph would ignore that.
	 */
	private function render_button_icon( $settings, $icon_class, $custom ) {
		?>
		<span class="sa-icon-wrap <?php echo esc_attr( $icon_class ); ?>">
			<?php
			if ( $custom ) {
				Icons_Manager::render_icon( $settings['button_icon'], [ 'aria-hidden' => 'true' ] );
			} else {
				?>
				<svg class="sa-ms-link-arrow" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"></path><path d="m13 6 6 6-6 6"></path></svg>
				<?php
			}
			?>
		</span>
		<?php
	}

	/**
	 * Prev/next buttons. Class names match every other Sky slider
	 * (`.sa-swiper-button-prev` / `-next`) so the shared navigation CSS applies.
	 */
	private function render_navigation( $settings ) {
		?>
		<div class="sa-ms__nav">
			<div class="sa-swiper-button-prev sa-slider-navigation sa-icon-wrap" role="button" tabindex="0" aria-label="<?php echo esc_attr__( 'Previous slide', 'sky-elementor-addons' ); ?>">
				<?php
				if ( ! empty( $settings['prev_icon']['value'] ) ) {
					Icons_Manager::render_icon( $settings['prev_icon'], [ 'aria-hidden' => 'true' ] );
				} else {
					?>
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5"></path><path d="m11 18-6-6 6-6"></path></svg>
					<?php
				}
				?>
			</div>
			<div class="sa-swiper-button-next sa-slider-navigation sa-icon-wrap" role="button" tabindex="0" aria-label="<?php echo esc_attr__( 'Next slide', 'sky-elementor-addons' ); ?>">
				<?php
				if ( ! empty( $settings['next_icon']['value'] ) ) {
					Icons_Manager::render_icon( $settings['next_icon'], [ 'aria-hidden' => 'true' ] );
				} else {
					?>
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"></path><path d="m13 6 6 6-6 6"></path></svg>
					<?php
				}
				?>
			</div>
		</div>
		<?php
	}
}

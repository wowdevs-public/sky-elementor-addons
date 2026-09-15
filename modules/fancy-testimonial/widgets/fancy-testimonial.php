<?php

namespace Sky_Addons\Modules\FancyTestimonial\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Repeater;
use Elementor\Utils;
use Elementor\Widget_Base;
use Sky_Addons\Includes\Controls\GroupQuery\Group_Control;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Fancy Testimonial — a testimonial carousel with two avatar layouts around a Swiper
 * (fade) slider: Wave Path and Cluster Wall. Avatars hold their positions in both; the
 * active one lights up in place, one after the next, and clicking any avatar jumps the
 * slider to that testimonial.
 *
 * Wave: the avatars are a Swiper of their own riding a curve this class also draws, and
 * the testimonial slider attaches to it through Swiper's `thumbs` module.
 * Cluster: a wrapping wall of avatars above one featured testimonial, whose big avatar
 * rides inside the slide so the crossfade swaps it with the text.
 *
 * NOTE: control IDs still use the `review*` vocabulary (`reviews_list`, `review`,
 * `review_color`…). That is deliberate — renaming a control ID discards saved content on
 * every placed widget, and Elementor never shows an ID to the user.
 */
class Fancy_Testimonial extends Widget_Base {

	use Group_Control; // query builder: register_query_builder_controls(), getGroupControlQueryArgs()

	private const WAVE_CYCLES = 2;

	/**
	 * Default testimonial length, in WORDS, before the Read More link appears — the length
	 * of a typical two-line testimonial, so the default keeps every card the same height
	 * without cutting an ordinary one short.
	 */
	private const WORD_LIMIT = 16;

	/**
	 * Size rhythm for the Wave rail. Inline custom properties survive Swiper's loop
	 * clones (the clone copies the style attribute), which is why this is baked per
	 * slide here rather than driven by `nth-child` in CSS — the clones would restart
	 * the CSS cycle mid-rail and the sizes would visibly jump on every loop.
	 */
	private const WAVE_SCALES = [ 1, 0.84, 1.12, 0.9 ];

	public function get_name() {
		return 'sky-fancy-testimonial';
	}

	public function get_title() {
		return esc_html__( 'Fancy Testimonial', 'sky-elementor-addons' );
	}

	public function get_icon() {
		// TODO: add a dedicated `sky-icon-fancy-testimonial` glyph to the icon font; reuse the closest for now.
		return 'eicon-testimonial-carousel';
	}

	public function get_categories() {
		return [ 'sky-elementor-addons' ];
	}

	public function get_keywords() {
		return [ 'sky', 'testimonial', 'review', 'carousel', 'slider', 'rating', 'avatar', 'fancy', 'wall', 'wave' ];
	}

	public function get_style_depends() {
		if ( sky_addons_editor_mode() ) {
			return [ 'swiper', 'sky-addons-styles' ];
		}
		return [ 'swiper', 'sa-fancy-testimonial' ];
	}

	public function get_script_depends() {
		if ( sky_addons_editor_mode() ) {
			return [ 'swiper', 'sky-addons-scripts' ];
		}
		return [ 'swiper', 'sa-fancy-testimonial' ];
	}

	public function has_widget_inner_wrapper(): bool {
		return ! \Elementor\Plugin::$instance->experiments->is_feature_active( 'e_optimized_markup' );
	}

	protected function register_controls() {

		$this->start_controls_section(
			'section_reviews',
			[
				'label' => esc_html__( 'Testimonials', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'content_source',
			[
				'label'       => esc_html__( 'Source', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'repeater',
				'options'     => [
					'repeater'      => esc_html__( 'Default Repeater', 'sky-elementor-addons' ),
					'dynamic_posts' => esc_html__( 'Dynamic Posts', 'sky-elementor-addons' ),
				],
				'description' => esc_html__( 'Default Repeater: write the testimonials by hand. Dynamic Posts: pull them from a post query (set it in the Query tab) — the post title becomes the name, the first category the designation, and the excerpt the testimonial.', 'sky-elementor-addons' ),
				'render_type' => 'template',
			]
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'image',
			[
				'label'   => esc_html__( 'Author Image', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::MEDIA,
				'dynamic' => [ 'active' => true ],
				'default' => [
					'url' => Utils::get_placeholder_image_src(),
				],
			]
		);

		$repeater->add_control(
			'name',
			[
				'label'       => esc_html__( 'Name', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Client Name', 'sky-elementor-addons' ),
				'label_block' => true,
				'dynamic'     => [ 'active' => true ],
			]
		);

		$repeater->add_control(
			'designation',
			[
				'label'       => esc_html__( 'Designation', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Product Designer', 'sky-elementor-addons' ),
				'label_block' => true,
				'dynamic'     => [ 'active' => true ],
			]
		);

		$repeater->add_control(
			'review',
			[
				'label'       => esc_html__( 'Testimonial', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 5,
				'default'     => esc_html__( 'Working with this team completely changed how we ship. The result exceeded every expectation we had.', 'sky-elementor-addons' ),
				'dynamic'     => [ 'active' => true ],
			]
		);

		$repeater->add_control(
			'rating',
			[
				'label'   => esc_html__( 'Rating', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::NUMBER,
				'min'     => 0,
				'max'     => 5,
				'step'    => 0.1,
				'default' => 5,
				'dynamic' => [ 'active' => true ],
			]
		);

		$this->add_control(
			'reviews_list',
			[
				'label'       => '',
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => [
					[
						'name'        => 'Ariana Gomez',
						'designation' => esc_html__( 'Product Designer, Northwind', 'sky-elementor-addons' ),
						'review'      => esc_html__( 'The attention to detail is unreal. Our conversion rate jumped 40% within the first month of launch.', 'sky-elementor-addons' ),
						'rating'      => 5,
					],
					[
						'name'        => 'David Chen',
						'designation' => esc_html__( 'CTO, Nova Labs', 'sky-elementor-addons' ),
						'review'      => esc_html__( 'Clean, fast, and beautifully engineered. Integration took a single afternoon instead of the week we budgeted.', 'sky-elementor-addons' ),
						'rating'      => 5,
					],
					[
						'name'        => 'Sofia Rahman',
						'designation' => esc_html__( 'Marketing Lead, Brightside', 'sky-elementor-addons' ),
						'review'      => esc_html__( 'Every campaign page we build now starts here. The team support is as good as the product itself.', 'sky-elementor-addons' ),
						'rating'      => 4.5,
					],
					[
						'name'        => 'James Carter',
						'designation' => esc_html__( 'Founder, Atlas Studio', 'sky-elementor-addons' ),
						'review'      => esc_html__( 'I have tried every alternative out there. Nothing comes close in polish or performance.', 'sky-elementor-addons' ),
						'rating'      => 5,
					],
					[
						'name'        => 'Mina Park',
						'designation' => esc_html__( 'UX Researcher, Loopwork', 'sky-elementor-addons' ),
						'review'      => esc_html__( 'Our users noticed the difference immediately. Session time nearly doubled after the redesign.', 'sky-elementor-addons' ),
						'rating'      => 4.5,
					],
					[
						'name'        => 'Liam Turner',
						'designation' => esc_html__( 'Ecommerce Manager, Vela', 'sky-elementor-addons' ),
						'review'      => esc_html__( 'Reliable, elegant and genuinely fun to work with. It simply never gets in the way.', 'sky-elementor-addons' ),
						'rating'      => 5,
					],
				],
				'title_field' => '{{{ name }}}',
				'condition'   => [ 'content_source' => 'repeater' ],
			]
		);

		$this->add_control(
			'rating_meta_key',
			[
				'label'       => esc_html__( 'Rating Custom Field', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'description' => esc_html__( 'Custom field holding a 0-5 rating for each post. Leave empty to show 5 stars.', 'sky-elementor-addons' ),
				'dynamic'     => [ 'active' => true ],
				'condition'   => [ 'content_source' => 'dynamic_posts' ],
			]
		);

		$this->add_control(
			'designation_meta_key',
			[
				'label'       => esc_html__( 'Designation Custom Field', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'description' => esc_html__( 'Custom field holding the designation for each post. Leave empty to fall back to category, then author name.', 'sky-elementor-addons' ),
				'dynamic'     => [ 'active' => true ],
				'condition'   => [ 'content_source' => 'dynamic_posts' ],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_query',
			[
				'label'     => esc_html__( 'Query', 'sky-elementor-addons' ),
				'tab'       => Controls_Manager::TAB_CONTENT,
				'condition' => [ 'content_source' => 'dynamic_posts' ],
			]
		);

		$this->register_query_builder_controls();
		$this->update_control( 'posts_per_page', [ 'default' => 8 ] );

		$this->end_controls_section();

		$this->start_controls_section(
			'section_layout',
			[
				'label' => esc_html__( 'Layout', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'layout',
			[
				'label'       => esc_html__( 'Layout', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'wave',
				'options'     => [
					'wave'    => esc_html__( 'Wave Path', 'sky-elementor-addons' ),
					'cluster' => esc_html__( 'Cluster Wall', 'sky-elementor-addons' ),
				],
				'description' => esc_html__( 'Wave Path: avatars ride a curve above the testimonial. Cluster Wall: a wall of avatars above a featured testimonial.', 'sky-elementor-addons' ),
				'render_type' => 'template',
			]
		);

		$this->add_responsive_control(
			'avatar_size',
			[
				'label'     => esc_html__( 'Avatar Size', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min' => 28,
						'max' => 140,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .sa-fancy-testimonial' => '--sa-ft-avatar-size: {{SIZE}}px;',
				],
			]
		);

		$this->add_responsive_control(
			'avatars_per_view',
			[
				'label'     => esc_html__( 'Avatars Per View', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min'  => 3,
						'max'  => 11,
						'step' => 2,
					],
				],
				'default'   => [
					'size' => 7,
				],
				'tablet_default' => [
					'size' => 5,
				],
				'mobile_default' => [
					'size' => 3,
				],
				'condition' => [ 'layout' => 'wave' ],
			]
		);

		$this->add_control(
			'wave_depth',
			[
				'label'       => esc_html__( 'Wave Depth', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::SLIDER,
				'description' => esc_html__( 'How far the avatars rise and fall along the curve.', 'sky-elementor-addons' ),
				'range'       => [
					'px' => [
						'min' => 0,
						'max' => 80,
					],
				],
				'default'     => [
					'size' => 62,
				],
				'condition'   => [ 'layout' => 'wave' ],
			]
		);

		$this->add_control(
			'show_wave_path',
			[
				'label'     => esc_html__( 'Show Path Line', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'condition' => [ 'layout' => 'wave' ],
			]
		);

		$this->add_responsive_control(
			'feature_avatar_size',
			[
				'label'       => esc_html__( 'Featured Avatar Size', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::SLIDER,
				'range'       => [
					'px' => [
						'min' => 80,
						'max' => 320,
					],
				],
				'condition' => [ 'layout' => 'cluster' ],
				'selectors' => [
					'{{WRAPPER}} .sa-fancy-testimonial' => '--sa-ft-feature-size: {{SIZE}}px;',
				],
			]
		);

		$this->add_responsive_control(
			'wall_columns',
			[
				'label'          => esc_html__( 'Wall Columns', 'sky-elementor-addons' ),
				'type'           => Controls_Manager::SELECT,
				'description'    => esc_html__( 'Even counts only — the stagger offsets every second column, and an odd count would shift that pattern by one on each new row.', 'sky-elementor-addons' ),
				'options'        => [
					'4'  => esc_html__( '4 Columns', 'sky-elementor-addons' ),
					'6'  => esc_html__( '6 Columns', 'sky-elementor-addons' ),
					'8'  => esc_html__( '8 Columns', 'sky-elementor-addons' ),
					'10' => esc_html__( '10 Columns', 'sky-elementor-addons' ),
					'12' => esc_html__( '12 Columns', 'sky-elementor-addons' ),
				],
				'default'        => '10',
				'tablet_default' => '6',
				'mobile_default' => '4',
				'condition'      => [ 'layout' => 'cluster' ],
				'selectors'      => [
					'{{WRAPPER}} .sa-fancy-testimonial' => '--sa-ft-wall-cols: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'wall_stagger',
			[
				'label'       => esc_html__( 'Column Stagger', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::SLIDER,
				'description' => esc_html__( 'How far every second column drops. 0 gives a flat grid.', 'sky-elementor-addons' ),
				'range'       => [
					'px' => [
						'min' => 0,
						'max' => 120,
					],
				],
				'default'     => [
					'size' => 46,
				],
				'condition'   => [ 'layout' => 'cluster' ],
				'selectors'   => [
					'{{WRAPPER}} .sa-fancy-testimonial' => '--sa-ft-wall-stagger: {{SIZE}}px;',
				],
			]
		);

		$this->add_responsive_control(
			'cluster_align',
			[
				'label'                => esc_html__( 'Featured Alignment', 'sky-elementor-addons' ),
				'type'                 => Controls_Manager::CHOOSE,
				'options'              => [
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
				'default'              => 'left',
				'tablet_default'       => 'center',
				'mobile_default'       => 'center',
				'description'          => esc_html__( 'Centering also pulls the quote marks into the flow, so they stop hanging off the edge once the testimonial stacks under the avatar.', 'sky-elementor-addons' ),
				'condition'            => [ 'layout' => 'cluster' ],
				// The quote marks hang outside the card when the review sits beside the
				// avatar; once it stacks they have to rejoin the flow, so the alignment
				// switches their positioning along with the text.
				'selectors_dictionary' => [
					'left'   => 'text-align: left; --sa-ft-quote-pos: absolute; --sa-ft-quote-gap: 58px;',
					'center' => 'text-align: center; --sa-ft-quote-pos: static; --sa-ft-quote-gap: 0px;',
					'right'  => 'text-align: right; --sa-ft-quote-pos: static; --sa-ft-quote-gap: 0px;',
				],
				'selectors'            => [
					'{{WRAPPER}} .sa-fancy-testimonial--cluster .sa-fancy-testimonial__card' => '{{VALUE}}',
				],
			]
		);

		$this->add_responsive_control(
			'wall_gap',
			[
				'label'     => esc_html__( 'Wall Spacing', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min' => 4,
						'max' => 60,
					],
				],
				'condition' => [ 'layout' => 'cluster' ],
				'selectors' => [
					'{{WRAPPER}} .sa-fancy-testimonial' => '--sa-ft-wall-gap: {{SIZE}}px;',
				],
			]
		);

		$this->add_control(
			'show_quote_icon',
			[
				'label'     => esc_html__( 'Show Quote Icon', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'separator' => 'before',
			]
		);

		$this->add_control(
			'show_rating',
			[
				'label'   => esc_html__( 'Show Rating', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'show_navigation',
			[
				'label' => esc_html__( 'Show Navigation', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::SWITCHER,
			]
		);

		$this->add_control(
			'text_limit',
			[
				'label'       => esc_html__( 'Testimonial Length (words)', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::NUMBER,
				'min'         => 0,
				'default'     => self::WORD_LIMIT,
				'description' => esc_html__( 'Longer testimonials are trimmed to this many words and get a Read More link. Set 0 to always show the full testimonial.', 'sky-elementor-addons' ),
				'dynamic'     => [ 'active' => true ],
				'separator'   => 'before',
			]
		);

		$this->add_control(
			'read_more_text',
			[
				'label'     => esc_html__( 'Read More Label', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( '[read more]', 'sky-elementor-addons' ),
				'dynamic'   => [ 'active' => true ],
				'condition' => [ 'text_limit!' => 0 ],
			]
		);

		$this->add_control(
			'read_less_text',
			[
				'label'     => esc_html__( 'Read Less Label', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( '[read less]', 'sky-elementor-addons' ),
				'dynamic'   => [ 'active' => true ],
				'condition' => [ 'text_limit!' => 0 ],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_settings',
			[
				'label' => esc_html__( 'Slider Settings', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
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
				'label'     => esc_html__( 'Autoplay Speed (ms)', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min'  => 1000,
						'max'  => 10000,
						'step' => 500,
					],
				],
				'default'   => [
					'unit' => 'px',
					'size' => 5000,
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
			'speed',
			[
				'label'   => esc_html__( 'Transition Speed (ms)', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::SLIDER,
				'range'   => [
					'px' => [
						'min'  => 300,
						'max'  => 3000,
						'step' => 100,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 800,
				],
			]
		);


		$this->end_controls_section();

		$this->start_controls_section(
			'section_card_style',
			[
				'label' => esc_html__( 'Testimonial Card', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'card_background',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .sa-fancy-testimonial__card',
			]
		);

		$this->add_responsive_control(
			'card_padding',
			[
				'label'      => esc_html__( 'Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-fancy-testimonial__card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'card_border',
				'label'    => esc_html__( 'Border', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-fancy-testimonial__card',
			]
		);

		$this->add_responsive_control(
			'card_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-fancy-testimonial__card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'card_box_shadow',
				'label'    => esc_html__( 'Box Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-fancy-testimonial__card',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_content_style',
			[
				'label' => esc_html__( 'Testimonial Content', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'quote_icon_color',
			[
				'label'     => esc_html__( 'Quote Icon Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'condition' => [
					'show_quote_icon' => 'yes',
				],
				'selectors' => [
					'{{WRAPPER}} .sa-fancy-testimonial__quote' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'review_heading',
			[
				'label'     => esc_html__( 'Testimonial Text', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'review_color',
			[
				'label'     => esc_html__( 'Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-fancy-testimonial__text' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'review_typography',
				'selector' => '{{WRAPPER}} .sa-fancy-testimonial__text',
			]
		);

		$this->add_control(
			'read_more_heading',
			[
				'label'     => esc_html__( 'Read More Link', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [ 'text_limit!' => 0 ],
			]
		);

		$this->add_control(
			'read_more_color',
			[
				'label'     => esc_html__( 'Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'condition' => [ 'text_limit!' => 0 ],
				'selectors' => [
					'{{WRAPPER}} .sa-fancy-testimonial' => '--sa-ft-more-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'read_more_color_hover',
			[
				'label'     => esc_html__( 'Hover Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'condition' => [ 'text_limit!' => 0 ],
				'selectors' => [
					'{{WRAPPER}} .sa-fancy-testimonial' => '--sa-ft-more-color-hover: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'      => 'read_more_typography',
				'selector'  => '{{WRAPPER}} .sa-fancy-testimonial__more',
				'condition' => [ 'text_limit!' => 0 ],
			]
		);

		$this->add_control(
			'name_heading',
			[
				'label'     => esc_html__( 'Name', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'name_color',
			[
				'label'     => esc_html__( 'Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-fancy-testimonial__name' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'name_typography',
				'selector' => '{{WRAPPER}} .sa-fancy-testimonial__name',
			]
		);

		$this->add_control(
			'designation_heading',
			[
				'label'     => esc_html__( 'Designation', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'designation_color',
			[
				'label'     => esc_html__( 'Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-fancy-testimonial__role' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'designation_typography',
				'selector' => '{{WRAPPER}} .sa-fancy-testimonial__role',
			]
		);

		$this->add_control(
			'rating_heading',
			[
				'label'     => esc_html__( 'Rating', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					'show_rating' => 'yes',
				],
			]
		);

		$this->add_control(
			'rating_color',
			[
				'label'     => esc_html__( 'Star Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'condition' => [
					'show_rating' => 'yes',
				],
				'selectors' => [
					'{{WRAPPER}} .sa-fancy-testimonial' => '--sa-ft-rating-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'rating_unmarked_color',
			[
				'label'     => esc_html__( 'Unmarked Star Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'condition' => [
					'show_rating' => 'yes',
				],
				'selectors' => [
					'{{WRAPPER}} .sa-fancy-testimonial' => '--sa-ft-rating-unmarked-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'rating_size',
			[
				'label'     => esc_html__( 'Star Size', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min' => 10,
						'max' => 40,
					],
				],
				'condition' => [
					'show_rating' => 'yes',
				],
				'selectors' => [
					'{{WRAPPER}} .sa-fancy-testimonial' => '--sa-ft-rating-size: {{SIZE}}px;',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_avatars_style',
			[
				'label' => esc_html__( 'Avatars', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		/*
		 * Both shape controls also drive the Cluster layout's big featured avatar. It is a
		 * different element (`__feature-avatar`, rendered inside the slide) and was keeping
		 * the stylesheet's `border-radius: 50%` / `border: 6px`, so a square-ish avatar
		 * setting applied to the wall and left the feature stubbornly round.
		 *
		 * The value is applied LITERALLY rather than scaled up for the larger avatar: a %
		 * radius already scales itself, and a px radius that silently grew would stop
		 * meaning what every other Border Radius control in the plugin means.
		 */
		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'avatar_border',
				'label'    => esc_html__( 'Border', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-fancy-testimonial__avatar-img, {{WRAPPER}} .sa-fancy-testimonial__feature-avatar',
			]
		);

		$this->add_responsive_control(
			'avatar_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-fancy-testimonial__avatar-img, {{WRAPPER}} .sa-fancy-testimonial__feature-avatar' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'avatar_inactive_opacity',
			[
				'label'     => esc_html__( 'Inactive Opacity', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min'  => 0.1,
						'max'  => 1,
						'step' => 0.05,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .sa-fancy-testimonial' => '--sa-ft-inactive-opacity: {{SIZE}};',
				],
			]
		);

		$this->add_control(
			'avatar_active_heading',
			[
				'label'     => esc_html__( 'Active', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'avatar_active_scale',
			[
				'label'     => esc_html__( 'Scale', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min'  => 1,
						'max'  => 2,
						'step' => 0.02,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .sa-fancy-testimonial' => '--sa-ft-active-scale: {{SIZE}};',
				],
			]
		);

		$this->add_control(
			'avatar_active_border_color',
			[
				'label'     => esc_html__( 'Border Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-fancy-testimonial' => '--sa-ft-active-border: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'avatar_active_ring_color',
			[
				'label'       => esc_html__( 'Glow Ring Color', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::COLOR,
				'description' => esc_html__( 'Soft ring drawn around the active avatar.', 'sky-elementor-addons' ),
				'selectors'   => [
					'{{WRAPPER}} .sa-fancy-testimonial' => '--sa-ft-active-ring: {{VALUE}};',
				],
			]
		);


		$this->end_controls_section();

		/*
		 * The curve and its node dots were stylesheet-only (`--sa-ft-path-color` /
		 * `--sa-ft-dot-color`, both near-black), so on a dark section they dropped to about
		 * 1:1 contrast and the whole wave vanished. The section carries the conditions, so
		 * the two controls inside need none of their own.
		 */
		$this->start_controls_section(
			'section_wave_style',
			[
				'label'     => esc_html__( 'Wave Path', 'sky-elementor-addons' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'layout'         => 'wave',
					'show_wave_path' => 'yes',
				],
			]
		);

		$this->add_control(
			'wave_path_color',
			[
				'label'     => esc_html__( 'Path Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-fancy-testimonial' => '--sa-ft-path-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'wave_dot_color',
			[
				'label'       => esc_html__( 'Node Dot Color', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::COLOR,
				'description' => esc_html__( 'The markers where the curve crosses the middle line.', 'sky-elementor-addons' ),
				'selectors'   => [
					'{{WRAPPER}} .sa-fancy-testimonial' => '--sa-ft-dot-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_navigation_style',
			[
				'label'     => esc_html__( 'Navigation', 'sky-elementor-addons' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_navigation' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'navigation_size',
			[
				'label'     => esc_html__( 'Size', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min' => 28,
						'max' => 80,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .sa-fancy-testimonial' => '--sa-ft-nav-size: {{SIZE}}px;',
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
				'label'     => esc_html__( 'Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-fancy-testimonial__nav-btn' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'navigation_background',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .sa-fancy-testimonial__nav-btn',
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
				'label'     => esc_html__( 'Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-fancy-testimonial__nav-btn:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'navigation_background_hover',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .sa-fancy-testimonial__nav-btn:hover',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	/**
	 * Build the Swiper config plus the widget-only `pauseOnHover` key.
	 * Swiper ignores the extra keys; the JS handler reads them off the same object.
	 *
	 * @param array  $settings Widget settings.
	 * @param string $id       Wrapper id (used for the navigation selectors).
	 * @return array
	 */
	protected function get_slider_settings( $settings, $id ) {
		return array_filter([
			'autoplay'       => ( 'yes' === $settings['autoplay'] ) ? [
				'delay'                => ! empty( $settings['autoplay_speed']['size'] ) ? $settings['autoplay_speed']['size'] : 5000,
				'disableOnInteraction' => false,
			] : false,
			'speed'          => ! empty( $settings['speed']['size'] ) ? $settings['speed']['size'] : 800,
			'pauseOnHover'   => ( 'yes' === $settings['autoplay'] && 'yes' === $settings['pause_on_hover'] ),

			// default
			'loop'           => true,
			'effect'         => 'fade',
			'fadeEffect'     => [
				'crossFade' => true,
			],
			'autoHeight'     => true,
			'slidesPerView'  => 1,
			'observer'       => true,
			'observeParents' => true,

			'layout'         => $settings['layout'],

			// Read More is applied in JS, after Swiper has made its loop clones — a
			// server-side trim would hide the rest of the review from the markup and
			// leave nothing to expand.
			'wordLimit'      => isset( $settings['text_limit'] ) ? (int) $settings['text_limit'] : self::WORD_LIMIT,
			'readMore'       => ! empty( $settings['read_more_text'] ) ? $settings['read_more_text'] : esc_html__( '[read more]', 'sky-elementor-addons' ),
			'readLess'       => ! empty( $settings['read_less_text'] ) ? $settings['read_less_text'] : esc_html__( '[read less]', 'sky-elementor-addons' ),

			'navigation'     => ( 'yes' === $settings['show_navigation'] || 'wave' === $settings['layout'] ) ? [
				'nextEl' => "#$id .sa-fancy-testimonial__nav-btn--next",
				'prevEl' => "#$id .sa-fancy-testimonial__nav-btn--prev",
			] : false,
		]);
	}

	/**
	 * Config for the avatar rail of the Wave layout — a second Swiper the main slider
	 * attaches to through the `thumbs` module, which keeps the highlight in sync and pulls
	 * the rail to the active avatar. Clicking an avatar is wired up in JS instead; Swiper's
	 * own thumbs click mapping does not survive a looped rail, and `slideToClickedSlide`
	 * below is inert for the same reason — Thumbs.init() force-writes it to false. It stays
	 * only so the rail still responds to a click if the `thumbs` link is ever dropped.
	 * `waveDepth` is not a Swiper option; the handler reads it to place each avatar on the
	 * curve.
	 *
	 * @param array $settings Widget settings.
	 * @return array
	 */
	protected function get_wave_settings( $settings, $count ) {
		$swiper_breakpoints = sky_addons_get_swiper_breakpoints();
		$viewport_lg        = $swiper_breakpoints['lg'];
		$viewport_md        = $swiper_breakpoints['md'];

		$per_view = static function ( $key, $fallback ) use ( $settings ) {
			return ! empty( $settings[ $key ]['size'] ) ? (int) $settings[ $key ]['size'] : $fallback;
		};

		return [
			'slidesPerView'       => $per_view( 'avatars_per_view_mobile', 3 ),
			'centeredSlides'      => true,
			'loop'                => true,
			// Without this, a rail showing more avatars than there are reviews runs out
			// of duplicates mid-loop and the strip jumps.
			'loopedSlides'        => $count,
			'slideToClickedSlide' => true,
			'watchSlidesProgress' => true,
			'speed'               => ! empty( $settings['speed']['size'] ) ? $settings['speed']['size'] : 800,
			'breakpoints'         => [
				$viewport_md => [ 'slidesPerView' => $per_view( 'avatars_per_view_tablet', 5 ) ],
				$viewport_lg => [ 'slidesPerView' => $per_view( 'avatars_per_view', 7 ) ],
			],
			'waveDepth'           => ! empty( $settings['wave_depth']['size'] ) ? (int) $settings['wave_depth']['size'] : 45,
		];
	}

	/**
	 * The curve the Wave avatars ride: two sine periods across a 1000x200 viewBox.
	 * The SVG is stretched horizontally (`preserveAspectRatio="none"`) but pinned to
	 * 200px tall, so one viewBox unit stays one pixel vertically — the JS uses the very
	 * same formula in pixels and the avatars land exactly on the drawn line.
	 *
	 * @param int $depth Wave amplitude in pixels.
	 * @return string
	 */
	protected function get_wave_path( $depth ) {
		$points = [];

		for ( $x = 0; $x <= 1000; $x += 10 ) {
			$y        = 100 - $depth * sin( 2 * M_PI * self::WAVE_CYCLES * $x / 1000 );
			$points[] = sprintf( '%1$s %2$s', $x, round( $y, 2 ) );
		}

		return 'M' . implode( ' L', $points );
	}

	/**
	 * Resolve reviews for the active source.
	 *
	 * Both sources return the SAME item shape (image, name, designation, review,
	 * rating, _id) so every render method downstream stays source-agnostic.
	 *
	 * @param array $settings Widget settings.
	 * @return array
	 */
	protected function get_review_items( $settings ) {
		if ( 'dynamic_posts' === $settings['content_source'] ) {
			return $this->get_post_items( $settings );
		}

		return ! empty( $settings['reviews_list'] ) ? $settings['reviews_list'] : [];
	}

	/**
	 * Build reviews from the query builder: post title = name, first category =
	 * designation (author name when the post has no category, which is what a custom
	 * post type usually hits), excerpt = review, featured image = avatar.
	 *
	 * The excerpt is NOT trimmed here — the Review Length control and its Read More
	 * link already handle that on the front end, and trimming twice would leave the
	 * link with nothing to reveal.
	 *
	 * @param array $settings Widget settings.
	 * @return array
	 */
	protected function get_post_items( $settings ) {
		$per_page = ! empty( $settings['posts_per_page'] ) ? absint( $settings['posts_per_page'] ) : 8;
		$query    = new \WP_Query( array_merge( $this->getGroupControlQueryArgs(), [ 'posts_per_page' => $per_page ] ) );
		$meta_key            = ! empty( $settings['rating_meta_key'] ) ? $settings['rating_meta_key'] : '';
		$designation_meta_key = ! empty( $settings['designation_meta_key'] ) ? $settings['designation_meta_key'] : '';
		$items               = [];

		foreach ( $query->posts as $post ) {
			$thumb_id   = get_post_thumbnail_id( $post );
			$categories = get_the_category( $post->ID );
			$rating     = $meta_key ? get_post_meta( $post->ID, $meta_key, true ) : '';

			if ( $designation_meta_key ) {
				$designation = get_post_meta( $post->ID, $designation_meta_key, true );
			} elseif ( ! empty( $categories ) ) {
				$designation = $categories[0]->name;
			} else {
				$designation = get_the_author_meta( 'display_name', $post->post_author );
			}

			$items[] = [
				'_id'         => '',
				'image'       => [
					'id'  => $thumb_id ? $thumb_id : '',
					'url' => $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'medium' ) : '',
				],
				'name'        => get_the_title( $post ),
				'designation' => $designation,
				'review'      => get_the_excerpt( $post ),
				'rating'      => ( '' !== $rating ) ? (float) $rating : 5,
			];
		}

		wp_reset_postdata();

		return $items;
	}

	/**
	 * Per-item class for the repeater's own styling. Dynamic posts have no repeater row,
	 * so they get nothing rather than a dangling `elementor-repeater-item-` class.
	 *
	 * @param array $item Resolved item.
	 * @return string
	 */
	protected function item_class( $item ) {
		return empty( $item['_id'] ) ? '' : ' elementor-repeater-item-' . $item['_id'];
	}

	/**
	 * One avatar: the clickable image plus the active/countdown ring. Shared by all
	 * three layouts — only the element that positions it differs.
	 *
	 * @param array $item Repeater item.
	 */
	protected function render_avatar_button( $item ) {
		$name = ! empty( $item['name'] ) ? $item['name'] : esc_html__( 'Client', 'sky-elementor-addons' );
		?>
		<button type="button" class="sa-fancy-testimonial__avatar"
			aria-label="<?php echo esc_attr( sprintf( /* translators: %s: reviewer name */ __( 'Show testimonial by %s', 'sky-elementor-addons' ), $name ) ); ?>">
			<span class="sa-fancy-testimonial__avatar-img">
				<?php $this->render_avatar_image( $item, $name ); ?>
			</span>
		</button>
		<?php
	}

	/**
	 * @param array  $item Repeater item.
	 * @param string $name Reviewer name, used as the alt text.
	 */
	protected function render_avatar_image( $item, $name ) {
		if ( ! empty( $item['image']['id'] ) ) {
			echo wp_get_attachment_image(
				$item['image']['id'],
				'medium',
				false,
				[
					'alt'     => esc_attr( $name ),
					'loading' => 'lazy',
				]
			);
			return;
		}

		if ( ! empty( $item['image']['url'] ) ) {
			printf( '<img src="%1$s" alt="%2$s" loading="lazy">', esc_url( $item['image']['url'] ), esc_attr( $name ) );
		}
	}

	/**
	 * Wave layout: the avatars are a Swiper of their own, riding the drawn curve. The
	 * review slider attaches to it via `thumbs`, which owns the active state and scrolls
	 * the rail; click-to-review is bound in JS, as it is for Cluster.
	 *
	 * @param array $settings Widget settings.
	 * @param array $reviews  Repeater items.
	 */
	protected function render_wave( $settings, $reviews ) {
		$depth = ! empty( $settings['wave_depth']['size'] ) ? (int) $settings['wave_depth']['size'] : 62;
		?>
		<div class="sa-fancy-testimonial__wave">
			<?php if ( 'yes' === $settings['show_wave_path'] ) : ?>
				<svg class="sa-fancy-testimonial__wave-path" viewBox="0 0 1000 200" preserveAspectRatio="none" aria-hidden="true">
					<path d="<?php echo esc_attr( $this->get_wave_path( $depth ) ); ?>"/>
				</svg>
				<?php
				/*
				 * Node dots sit on the curve's zero crossings — the points where it cuts
				 * the middle line. They are plain elements rather than SVG circles on
				 * purpose: the path is stretched with `preserveAspectRatio="none"`, which
				 * would squash any circle inside it into an ellipse.
				 */
				for ( $k = 1; $k < 2 * self::WAVE_CYCLES; $k++ ) :
					?>
					<span class="sa-fancy-testimonial__wave-dot"
						style="<?php echo esc_attr( sprintf( 'left: %s%%;', $k * 100 / ( 2 * self::WAVE_CYCLES ) ) ); ?>"
						aria-hidden="true"></span>
					<?php
				endfor;
				?>
			<?php endif; ?>

			<div class="sa-fancy-testimonial__rail">
				<div class="swiper-wrapper">
					<?php foreach ( $reviews as $index => $item ) : ?>
						<div class="swiper-slide sa-fancy-testimonial__spot sa-fancy-testimonial__spot--wave<?php echo esc_attr( $this->item_class( $item ) ); ?>"
							style="<?php echo esc_attr( sprintf( '--sa-ft-scale: %s;', self::WAVE_SCALES[ $index % count( self::WAVE_SCALES ) ] ) ); ?>"
							data-index="<?php echo esc_attr( $index ); ?>">
							<?php $this->render_avatar_button( $item ); ?>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>

		<div class="sa-fancy-testimonial__wave-bottom">
			<?php $this->render_nav_button( 'prev' ); ?>
			<?php $this->render_slider( $settings, $reviews ); ?>
			<?php $this->render_nav_button( 'next' ); ?>
		</div>
		<?php
	}

	/**
	 * Cluster layout: a wall of avatars above one featured review. The featured avatar
	 * lives inside each slide, so Swiper's crossfade swaps it with the text — no JS.
	 *
	 * @param array $settings Widget settings.
	 * @param array $reviews  Repeater items.
	 */
	protected function render_cluster( $settings, $reviews ) {
		?>
		<div class="sa-fancy-testimonial__wall">
			<?php foreach ( $reviews as $index => $item ) : ?>
				<?php
				$spot_class = 'sa-fancy-testimonial__spot sa-fancy-testimonial__spot--wall' . $this->item_class( $item );
				if ( 0 === $index ) {
					$spot_class .= ' is-active';
				}
				?>
				<div class="<?php echo esc_attr( $spot_class ); ?>" data-index="<?php echo esc_attr( $index ); ?>">
					<?php $this->render_avatar_button( $item ); ?>
				</div>
			<?php endforeach; ?>
		</div>

		<?php $this->render_slider( $settings, $reviews ); ?>

		<?php
		if ( 'yes' === $settings['show_navigation'] ) {
			$this->render_navigation();
		}
	}

	/**
	 * The review slider itself — identical markup for every layout.
	 *
	 * @param array $settings Widget settings.
	 * @param array $reviews  Repeater items.
	 */
	protected function render_slider( $settings, $reviews ) {
		?>
		<div class="sa-fancy-testimonial__slider">
			<div class="swiper-wrapper">
				<?php
				foreach ( $reviews as $item ) {
					$this->render_card( $settings, $item );
				}
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render one review card slide: quote icon, text, rating stars, name, role.
	 * The stars are a clipped-overlay pair, so fractional ratings (4.5) render exactly.
	 *
	 * @param array $settings Widget settings.
	 * @param array $item     Repeater item.
	 */
	protected function render_card( $settings, $item ) {
		?>
		<div class="swiper-slide">
			<?php if ( 'cluster' === $settings['layout'] ) : ?>
				<div class="sa-fancy-testimonial__feature">
					<div class="sa-fancy-testimonial__feature-avatar">
						<?php $this->render_avatar_image( $item, ! empty( $item['name'] ) ? $item['name'] : '' ); ?>
					</div>
				</div>
			<?php endif; ?>
			<div class="sa-fancy-testimonial__card">
				<?php if ( 'yes' === $settings['show_quote_icon'] ) : ?>
					<span class="sa-fancy-testimonial__quote" aria-hidden="true">
						<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 24" width="32" height="24" fill="currentColor">
							<path d="M13.4 0 8.2 10.1c1.9.4 3.4 1.3 4.4 2.6 1 1.3 1.5 2.8 1.5 4.4 0 2-.7 3.6-2 4.9-1.3 1.3-3 2-4.9 2-2 0-3.6-.7-5-2C.7 20.7 0 19 0 17c0-1 .2-2.1.7-3.2L7 0h6.4Zm17.9 0-5.2 10.1c1.9.4 3.4 1.3 4.4 2.6 1 1.3 1.5 2.8 1.5 4.4 0 2-.7 3.6-2 4.9-1.3 1.3-3 2-4.9 2-2 0-3.7-.7-5-2-1.3-1.3-2-3-2-5 0-1 .2-2.1.7-3.2L24.9 0h6.4Z"/>
						</svg>
					</span>
				<?php endif; ?>

				<?php if ( ! empty( $item['review'] ) ) : ?>
					<div class="sa-fancy-testimonial__text"><?php echo wp_kses_post( $item['review'] ); ?></div>
				<?php endif; ?>

				<?php
				if ( 'yes' === $settings['show_rating'] && isset( $item['rating'] ) && '' !== $item['rating'] ) :
					$rating  = min( 5, max( 0, (float) $item['rating'] ) );
					$percent = $rating / 5 * 100;
					?>
					<div class="sa-fancy-testimonial__rating" role="img"
						aria-label="<?php echo esc_attr( sprintf( /* translators: %s: rating value */ __( 'Rated %s out of 5', 'sky-elementor-addons' ), $rating ) ); ?>">
						<span class="sa-fancy-testimonial__stars" aria-hidden="true">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
						<span class="sa-fancy-testimonial__stars sa-fancy-testimonial__stars--filled" aria-hidden="true"
							style="<?php echo esc_attr( sprintf( 'width: %s%%;', $percent ) ); ?>">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
					</div>
				<?php endif; ?>

				<div class="sa-fancy-testimonial__meta">
					<?php if ( ! empty( $item['name'] ) ) : ?>
						<div class="sa-fancy-testimonial__name"><?php echo esc_html( $item['name'] ); ?></div>
					<?php endif; ?>
					<?php if ( ! empty( $item['designation'] ) ) : ?>
						<div class="sa-fancy-testimonial__role"><?php echo esc_html( $item['designation'] ); ?></div>
					<?php endif; ?>
				</div>

				<?php if ( 'cluster' === $settings['layout'] && 'yes' === $settings['show_quote_icon'] ) : ?>
					<span class="sa-fancy-testimonial__quote sa-fancy-testimonial__quote--close" aria-hidden="true">
						<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 24" width="32" height="24" fill="currentColor">
							<path d="M13.4 0 8.2 10.1c1.9.4 3.4 1.3 4.4 2.6 1 1.3 1.5 2.8 1.5 4.4 0 2-.7 3.6-2 4.9-1.3 1.3-3 2-4.9 2-2 0-3.6-.7-5-2C.7 20.7 0 19 0 17c0-1 .2-2.1.7-3.2L7 0h6.4Zm17.9 0-5.2 10.1c1.9.4 3.4 1.3 4.4 2.6 1 1.3 1.5 2.8 1.5 4.4 0 2-.7 3.6-2 4.9-1.3 1.3-3 2-4.9 2-2 0-3.7-.7-5-2-1.3-1.3-2-3-2-5 0-1 .2-2.1.7-3.2L24.9 0h6.4Z"/>
						</svg>
					</span>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * @param string $direction `prev` or `next`.
	 */
	protected function render_nav_button( $direction ) {
		$paths = [
			'prev' => 'M0,22L22,0l2.1,2.1L4.2,22l19.9,19.9L22,44L0,22L0,22L0,22z',
			'next' => 'M27,22L27,22L5,44l-2.1-2.1L22.8,22L2.9,2.1L5,0L27,22L27,22z',
		];

		$label = 'prev' === $direction
			? esc_attr__( 'Previous testimonial', 'sky-elementor-addons' )
			: esc_attr__( 'Next testimonial', 'sky-elementor-addons' );

		/*
		 * An anchor, not a <button>: theme and kit rules target `button` heavily, and a
		 * `button:focus` colour repainted the arrow out of sight after every click (the
		 * glyph is `fill="currentColor"`). The href keeps it keyboard-operable — Enter
		 * fires a click on an anchor that has one, which `role="button"` alone does not.
		 */
		?>
		<a href="javascript:void(0);" role="button"
			class="sa-fancy-testimonial__nav-btn sa-fancy-testimonial__nav-btn--<?php echo esc_attr( $direction ); ?>"
			aria-label="<?php echo esc_attr( $label ); ?>">
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 27 44" fill="currentColor">
				<path d="<?php echo esc_attr( $paths[ $direction ] ); ?>"/>
			</svg>
		</a>
		<?php
	}

	protected function render_navigation() {
		?>
		<div class="sa-fancy-testimonial__nav">
			<?php
			$this->render_nav_button( 'prev' );
			$this->render_nav_button( 'next' );
			?>
		</div>
		<?php
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$reviews  = $this->get_review_items( $settings );

		if ( empty( $reviews ) ) {
			return;
		}

		$id     = 'sa-fancy-testimonial-' . $this->get_id();
		$layout = ( 'cluster' === $settings['layout'] ) ? 'cluster' : 'wave';

		$classes = [ 'sa-fancy-testimonial', 'sa-fancy-testimonial--' . $layout ];

		$this->add_render_attribute(
			[
				'fancy-testimonial' => [
					'class'         => $classes,
					'id'            => $id,
					'data-settings' => [ wp_json_encode( $this->get_slider_settings( $settings, $id ) ) ],
				],
			]
		);

		if ( 'wave' === $layout ) {
			$this->add_render_attribute( 'fancy-testimonial', 'data-rail-settings', wp_json_encode( $this->get_wave_settings( $settings, count( $reviews ) ) ) );
		}

		?>
		<div <?php $this->print_render_attribute_string( 'fancy-testimonial' ); ?>>
			<?php
			if ( 'cluster' === $layout ) {
				$this->render_cluster( $settings, $reviews );
			} else {
				$this->render_wave( $settings, $reviews );
			}
			?>
		</div>
		<?php
	}
}

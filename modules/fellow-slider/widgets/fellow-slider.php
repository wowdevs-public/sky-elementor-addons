<?php

namespace Sky_Addons\Modules\FellowSlider\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Group_Control_Css_Filter;
use Elementor\Widget_Base;

use Elementor\Plugin;

use Sky_Addons\Includes\Controls\GroupQuery\Group_Control;
use Sky_Addons\Traits\Global_Widget_Functions;
use Sky_Addons\Traits\Global_Widget_Controls;



if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Fellow_Slider extends Widget_Base {

	use Group_Control;
	use Global_Widget_Functions;
	use Global_Widget_Controls;

	private $_query = null;

	public function get_name() {
		return 'sky-fellow-slider';
	}

	public function get_title() {
		return esc_html__( 'Fellow Slider', 'sky-elementor-addons' );
	}

	public function get_icon() {
		return 'sky-icon-fellow-slider';
	}

	public function get_categories() {
		return [ 'sky-elementor-addons' ];
	}

	public function get_keywords() {
		return [ 'sky', 'post', 'list', 'blogs', 'fellow', 'slider', 'video' ];
	}

	public function get_query() {
		return $this->_query;
	}

	public function get_style_depends() {
		if ( sky_addons_editor_mode() ) {
			return [ 'swiper', 'elementor-icons-fa-solid', 'sky-addons-styles' ];
		}

		return [ 'swiper', 'elementor-icons-fa-solid', 'sa-fellow-slider' ];
	}

	public function get_script_depends() {
		if ( sky_addons_editor_mode() ) {
			return [ 'swiper', 'sky-addons-scripts' ];
		}

		return [ 'swiper', 'sa-fellow-slider' ];
	}

	public function has_widget_inner_wrapper(): bool {
		return ! \Elementor\Plugin::$instance->experiments->is_feature_active( 'e_optimized_markup' );
	}

	protected function register_controls() {

		$this->start_controls_section(
			'section_fellow_slider_layout',
			[
				'label' => esc_html__( 'Layout', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
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
				'default'    => [
					'unit' => 'px',
					'size' => 20,
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-fellow-slider' => 'grid-gap: {{SIZE}}{{UNIT}};',
				],
				// This is the gap BETWEEN the player and the playlist columns. With the
				// playlist off there is only one column, so there is nothing to space.
				'condition'  => [
					'show_playlist' => 'yes',
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
				// The rows inside a slide are flex containers, so text-align alone never moved
				// them. Rather than listing every one here, the value is published as a custom
				// property and the stylesheet applies it wherever a row needs aligning — new
				// rows then inherit the behaviour instead of silently ignoring the control.
				//
				// `justify` is deliberately allowed to fall through: it is valid for text-align
				// but NOT for justify-content, so the flex rows keep their default while the
				// paragraph text justifies. That is the sane reading of "justified".
				'selectors' => [
					'{{WRAPPER}} .sa-post-item' => 'text-align: {{VALUE}}; --sa-fellow-align: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'content_vertical_position',
			[
				'label'     => esc_html__( 'Text Position', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
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
				'selectors' => [
					'{{WRAPPER}} .sa-post-item .sa-post-content-wrapper' => 'justify-content: {{VALUE}};',
					// The small items lay their image and text out in a ROW, so the text block's
					// vertical placement is the parent's align-items, not its own justify-content.
					'{{WRAPPER}} .sa-fellow-items .sa-post-item' => 'align-items: {{VALUE}};',
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
		// 'default' => 8,
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

		// Was Controls_Manager::HIDDEN, which is why there was no way to turn the title off:
		// render_post_title() in traits/global-widget-functions.php has always gated on this
		// value, but the control had no UI. SWITCHER with the same ID and the same 'yes'
		// default, so nothing changes for an existing site — the toggle simply becomes visible.
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
				'label'     => esc_html__( 'Title HTML Tag', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'h3',
				'options'   => sky_addons_title_tags(),
				'condition' => [
					'show_title' => 'yes',
				],
			]
		);

		// Same story as show_title — gated in render_item_thumbnail(), but with no UI.
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
				'default'     => 20, // 30
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
			'section_playlist_settings',
			[
				'label' => esc_html__( 'Slider Settings', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_responsive_control(
			'carousel_height',
			[
				'label'      => esc_html__( 'Height', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.0.0' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range'      => [
					'px' => [
						'min' => 100,
						'max' => 1000,
					],
				],
				// Targets the wrapper, not `.swiper`. The height that defines this widget's
				// box lives on `.sa-fellow-slider` (the two-column grid holding the player
				// and the playlist); both swipers only stretch to fill it. Sizing `.swiper`
				// instead would shrink the slides and leave the grid at its 600px.
				'selectors'  => [
					'{{WRAPPER}} .sa-fellow-slider' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'direction',
			[
				'label'   => esc_html__( 'Direction', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'horizontal',
				'options' => [
					'horizontal' => esc_html__( 'Horizontal', 'sky-elementor-addons' ),
					// 'vertical'   => esc_html__('Vertical', 'sky-elementor-addons'),
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
				'label' => esc_html__( 'Autoplay Speed (ms)', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 1000,
						'max' => 10000,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 5000,
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
				'label' => esc_html__( 'Slide Speed (ms)', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min'  => 500,
						'max'  => 5000,
						'step' => 500,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 1500,
				],
			]
		);

		$this->add_control(
			'pause_on_hover',
			[
				'label'     => esc_html__( 'Pause On Hover', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SWITCHER,
				// render() already gates this on autoplay ('yes' === autoplay && 'yes' ===
				// pause_on_hover), so with autoplay off the switch was live in the panel and
				// dead in the output.
				'condition' => [
					'autoplay' => 'yes',
				],
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
					// 'fade'      => esc_html__('Fade', 'sky-elementor-addons'),
					'coverflow' => esc_html__( 'Coverflow', 'sky-elementor-addons' ),
				],
			]
		);

		// $this->add_control(
		// 'show_play_button_on_hover',
		// [
		// 'label'        => esc_html__('Show Play Button On Hover', 'sky-elementor-addons'),
		// 'type'         => Controls_Manager::SWITCHER,
		// 'prefix_class' => 'sa-play-button-on-hover-'
		// ]
		// );

		$this->add_control(
			'playlist_heading',
			[
				'label'     => esc_html__( 'Playlist', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'show_playlist',
			[
				'label'       => esc_html__( 'Show Playlist', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.0.0' ),
				'type'        => Controls_Manager::SWITCHER,
				'default'     => 'yes',
				'description' => esc_html__( 'Turn off to show the player on its own, full width.', 'sky-elementor-addons' ),
			]
		);

		/**
		 * `playlist_mouse_wheel` and `playlist_free_mode` used to live here and have been
		 * removed. Both configured the playlist's Swiper, which no longer exists — the list
		 * scrolls natively, so wheel and touch momentum are unconditional and neither switch
		 * had anything left to turn off. Their stored values simply go unread; nothing is
		 * deleted, and re-adding a control with either ID would pick the old value back up.
		 */

		$this->add_control(
			'playlist_show_scrollbar',
			[
				'label'     => esc_html__( 'Show Scrollbar', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				// The old note here warned the scrollbar was unsupported with Loop. That was
				// a Swiper scrollbar limitation; the playlist scrolls natively now, so Loop
				// no longer has anything to do with it.
				'condition' => [
					'show_playlist' => 'yes',
				],
			]
		);

		// $this->add_control(
		// 'playlist_show_navigation',
		// [
		// 'label'   => esc_html__('Show Navigation', 'sky-elementor-addons'),
		// 'type'    => Controls_Manager::SWITCHER,
		// 'default' => 'yes',
		// ]
		// );

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

		$this->start_controls_section(
			'section_fellow_slider_style',
			[
				'label' => esc_html__( 'Fellow Slider', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'item_spacing',
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
				// Was `.sa-fellow .sa-fellow-slider`, which matched nothing — .sa-fellow is a
				// CHILD of .sa-fellow-slider, so that descendant selector can never resolve.
				// The intent is the gap inside an item, between the image and the text
				// (the stylesheet's `.sa-post-item { grid-gap: 20px }`), which is also what
				// the Padding/Border/Radius controls beside it target.
				'selectors'  => [
					'{{WRAPPER}} .sa-post-item' => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'item_padding',
			[
				'label'      => esc_html__( 'Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', 'rem', '%' ],
				// The panel read empty while .sa-post-item was already padded by the `sa-p-4`
				// class in render() — so a value typed here replaced an invisible baseline
				// instead of adding to it, and the same number meant different things from one
				// widget to the next. This default makes the control state the truth.
				//
				// The class deliberately STAYS in the markup. Elementor serves a page's CSS
				// from a cached file that only rebuilds on save, so a page saved before this
				// default existed would otherwise render with no padding at all. The control
				// always wins when it emits anything — `body .sa-p-4` is (0,1,1) against
				// Elementor's (0,4,0) — including when set to 0, so the class only ever acts
				// as the stale-cache fallback.
				//
				// No tablet/mobile default here: nothing in this widget's stylesheet overrides
				// .sa-post-item padding at a breakpoint, so there is no rule to preserve.
				//
				// rem, not the 24px it usually resolves to, so it matches the class whatever
				// root font-size the theme sets.
				'default'    => [
					'top'      => '1.5',
					'right'    => '1.5',
					'bottom'   => '1.5',
					'left'     => '1.5',
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
				'fields_options' => [
					'border' => [
						'default' => 'solid',
					],
					'width'  => [
						'default' => [
							'top'      => '1',
							'right'    => '1',
							'bottom'   => '1',
							'left'     => '1',
							'unit'     => 'px',
							'isLinked' => false,
						],
					],
					'color'  => [
						'default' => '#eaeaea',
					],
				],
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
					'top'      => '.25',
					'right'    => '.25',
					'bottom'   => '.25',
					'left'     => '.25',
					'unit'     => 'em',
					'isLinked' => true,
				],
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

		/**
		 * Active — the playlist row whose post the player is currently showing.
		 *
		 * Every selector is scoped through `.sa-fellow-row`, which only exists in the
		 * playlist, so none of this can reach the player's own item.
		 */
		$this->start_controls_tab(
			'item_style_active_tab',
			[
				'label'     => esc_html__( 'Active', 'sky-elementor-addons' ),
				// "Active" only means anything for a playlist row, so the whole tab goes
				// when the playlist does. Elementor treats a tab as a control, so a
				// condition here hides the tab itself rather than emptying it.
				'condition' => [
					'show_playlist' => 'yes',
				],
			]
		);

		$this->add_control(
			'active_indicator',
			[
				'label'       => esc_html__( 'Indicator Bar', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.0.0' ),
				'type'        => Controls_Manager::SWITCHER,
				'default'     => 'yes',
				'description' => esc_html__( 'Accent bar down the edge of the row the player is on.', 'sky-elementor-addons' ),
				// Drives the `sa-has-indicator` class in render(), NOT a `selectors` rule.
				// Elementor caches a page's generated CSS to uploads/elementor/css/post-*.css
				// and only rebuilds it when the page is saved or CSS is regenerated — so a
				// brand new selector is invisible on an existing page until someone re-saves
				// it. Driving it from markup means the widget stylesheet alone is enough and
				// the bar shows on the very next page load.
			]
		);

		$this->add_responsive_control(
			'active_indicator_width',
			[
				'label'      => esc_html__( 'Indicator Width', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.0.0' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range'      => [
					'px' => [
						'min' => 1,
						'max' => 20,
					],
				],
				// Custom property on the row, read by the ::before — a pseudo-element
				// inherits from its originating element, so one declaration covers both.
				'selectors'  => [
					'{{WRAPPER}} .sa-fellow-row.sa-active .sa-post-item' => '--sa-fellow-indicator-width: {{SIZE}}{{UNIT}};',
				],
				'condition'  => [
					'active_indicator' => 'yes',
				],
			]
		);

		$this->add_control(
			'active_indicator_color',
			[
				'label'     => esc_html__( 'Indicator Color', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.0.0' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-fellow-row.sa-active .sa-post-item' => '--sa-fellow-indicator-color: {{VALUE}};',
				],
				'condition' => [
					'active_indicator' => 'yes',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'item_background_active',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .sa-fellow-row.sa-active .sa-post-item',
			]
		);

		$this->add_control(
			'item_border_color_active',
			[
				'label'     => esc_html__( 'Border Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-fellow-row.sa-active .sa-post-item' => 'border-color: {{VALUE}};',
				],
				// Mirrors the Hover tab: nothing to colour unless a border exists.
				'condition' => [
					'item_border_border!' => '',
				],
			]
		);

		$this->add_control(
			'item_title_color_active',
			[
				'label'     => esc_html__( 'Title Color', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.0.0' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-fellow-row.sa-active .sa-post-title'   => 'color: {{VALUE}};',
					'{{WRAPPER}} .sa-fellow-row.sa-active .sa-post-title a' => 'color: {{VALUE}};',
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

		$this->start_controls_tabs(
			'style_img_tabs'
		);

		$this->start_controls_tab(
			'style_img_tab',
			[
				'label' => esc_html__( 'Feature Image', 'sky-elementor-addons' ),
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'img_border',
				'label'    => esc_html__( 'Border', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-fellow .sa-post-img',
			]
		);

		$this->add_responsive_control(
			'img_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-fellow .sa-post-img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'img_box_shadow',
				'label'    => esc_html__( 'Box Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-fellow .sa-post-img',
			]
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			[
				'name'     => 'img_css_filters',
				'selector' => '{{WRAPPER}} .sa-fellow .sa-post-img',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'style_list_img_tab',
			[
				'label'     => esc_html__( 'List Image', 'sky-elementor-addons' ),
				// Every control in here targets .sa-fellow-items — nothing to style once
				// the playlist is off.
				'condition' => [
					'show_playlist' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'list_img_width',
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
					'{{WRAPPER}} .sa-fellow-items .sa-post-img-wrapper' => 'min-width: {{SIZE}}{{UNIT}}; max-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'list_img_border',
				'label'    => esc_html__( 'Border', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-fellow-items .sa-post-img',
			]
		);

		$this->add_responsive_control(
			'list_img_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-fellow-items .sa-post-img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'list_img_box_shadow',
				'label'    => esc_html__( 'Box Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-fellow-items .sa-post-img',
			]
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			[
				'name'     => 'list_img_css_filters',
				'selector' => '{{WRAPPER}} .sa-fellow-items .sa-post-img',
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

		$this->start_controls_tabs(
			'style_title_tabs'
		);

		$this->start_controls_tab(
			'style_title_tab',
			[
				'label' => esc_html__( 'Title', 'sky-elementor-addons' ),
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
					'{{WRAPPER}} .sa-fellow .sa-post-title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'title_color',
			[
				'label' => esc_html__( 'Text Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-fellow .sa-post-title a' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'title_color_hover',
			[
				'label' => esc_html__( 'Text Color Hover', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-fellow .sa-post-title a:hover' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'title_typography',
				'label'    => esc_html__( 'Typography', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-fellow .sa-post-title',
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'title_text_shadow',
				'label'    => esc_html__( 'Text Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-fellow .sa-post-title a',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'style_list_title_tab',
			[
				'label'     => esc_html__( 'List Title', 'sky-elementor-addons' ),
				// Targets .sa-fellow-items titles only.
				'condition' => [
					'show_playlist' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'list_title_spacing',
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
					'{{WRAPPER}} .sa-fellow-items .sa-post-title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'list_title_color',
			[
				'label' => esc_html__( 'Text Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-fellow-items .sa-post-title a' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'list_title_color_hover',
			[
				'label' => esc_html__( 'Text Color Hover', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-fellow-items .sa-post-title a:hover' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'list_title_typography',
				'label'    => esc_html__( 'Typography', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-fellow-items .sa-post-title',
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'list_title_text_shadow',
				'label'    => esc_html__( 'Text Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-fellow-items .sa-post-title a',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

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
			'section_author_style',
			[
				'label' => esc_html__( 'Author', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_author' => 'yes',
				],
			]
		);

		$this->add_control(
			'author_color',
			[
				'label' => esc_html__( 'Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-fellow .sa-post-author-text' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'author_color_hover',
			[
				'label' => esc_html__( 'Color Hover', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-fellow .sa-post-author-wrapper:hover .sa-post-author-text' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'author_typography',
				'label'    => esc_html__( 'Typography', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-fellow .sa-post-author-text',
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'author_text_shadow',
				'label'    => esc_html__( 'Text Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-fellow .sa-post-author-text',
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
					'{{WRAPPER}} .sa-fellow .sa-post-author-thumb' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'author_img_border',
				'label'    => esc_html__( 'Border', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-fellow .sa-post-author-thumb',
			]
		);

		$this->add_responsive_control(
			'author_img_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-fellow .sa-post-author-thumb' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'author_img_box_shadow',
				'label'    => esc_html__( 'Box Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-fellow .sa-post-author-thumb',
			]
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			[
				'name'     => 'author_img_css_filters',
				'selector' => '{{WRAPPER}} .sa-fellow .sa-post-author-thumb',
			]
		);

		/**
		 * Date sub-group. The section itself is gated on show_author, but everything below
		 * styles the DATE — with Date switched off these were live in the panel and had
		 * nothing to paint.
		 */
		$this->add_control(
			'author_date_heading_style',
			[
				'label'     => esc_html__( 'Date', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					'show_date' => 'yes',
				],
			]
		);

		$this->add_control(
			'author_date_color',
			[
				'label'     => esc_html__( 'Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}' => '--sa-post-author-date-color: {{VALUE}}',
				],
				'condition' => [
					'show_date' => 'yes',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'      => 'author_date_typography',
				'label'     => esc_html__( 'Typography', 'sky-elementor-addons' ),
				'selector'  => '{{WRAPPER}} .sa-fellow .sa-post-date, {{WRAPPER}} .sa-fellow .sa-icon-wrap',
				'condition' => [
					'show_date' => 'yes',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'      => 'author_date_text_shadow',
				'label'     => esc_html__( 'Text Shadow', 'sky-elementor-addons' ),
				'selector'  => '{{WRAPPER}} .sa-fellow .sa-post-date, {{WRAPPER}} .sa-fellow .sa-icon-wrap',
				'condition' => [
					'show_date' => 'yes',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'list_scrollbar_style',
			[
				'label' => esc_html__( 'Scrollbar', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_playlist'           => 'yes',
					'playlist_show_scrollbar' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'list_scrollbar_size',
			[
				'label'      => esc_html__( 'Size', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range'      => [
					'px' => [
						'min'  => 1,
						'max'  => 10,
						'step' => .5,
					],
				],
				// Native scrollbar now. Control IDs are untouched, so every saved value
				// carries over — only the selector it writes to has moved.
				//
				// All three controls publish a custom property instead of styling the
				// scrollbar directly. Two reasons: Firefox takes both colours in one
				// `scrollbar-color` shorthand so they have to be composed somewhere, and a
				// control writing `background` straight onto ::-webkit-scrollbar-thumb would
				// outrank the stylesheet and defeat the hide-until-hover behaviour.
				'selectors'  => [
					'{{WRAPPER}} .sa-fellow-items' => '--sa-fellow-bar-size: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'list_scrollbar_color',
			[
				'label' => esc_html__( 'Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-fellow-items' => '--sa-fellow-track: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'list_scrollbar_drag_color',
			[
				'label' => esc_html__( 'Drag Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-fellow-items' => '--sa-fellow-thumb: {{VALUE}};',
				],
			]
		);

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
			'selector' => '.sa-fellow .sa-play-button',
		] );

		$this->end_controls_section();

		$this->start_controls_section(
			'item_play_btn_style',
			[
				'label' => esc_html__( 'Play Button Items', 'sky-elementor-addons' ),
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
			'prefix'   => 'play_button_item',
			'selector' => '.sa-fellow-items .sa-play-button',
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

	protected function render_item_thumbnail( $post_id, $image_size = 'full', $feature = '' ) {
		$settings = $this->get_settings_for_display();

		if ( 'yes' !== $settings['show_image'] ) {
			return;
		}

		/**
		 * Video Feature enabled
		 */

		$video_url = $this->get_post_video_url( $post_id );

		if ( 'yes' === $settings['show_video'] ) {
			$tag = 'div';
			$id  = $this->get_id() . '-' . $post_id . $feature;

			/**
			 * Lightbox
			 */

			$this->render_post_video_lightbox( $video_url, $id );

			if ( 'file' === $settings['video_open'] ) {
				$tag = 'a';
			}
		}

		?>
		<div class="sa-post-img-wrapper sa-d-inline-block sa-overflow-hidden">
			<?php if ( ( 'fellow' === $feature ) && ( 'yes' !== $settings['show_video'] || empty( $video_url ) ) ) : ?>
				<!-- Extra - Link added in Image -->
				<a href="<?php echo esc_url( get_permalink() ); ?>" title="<?php echo esc_html( get_the_title() ); ?>">
					<?php
					$this->render_post_image( [
						'image_id'       => get_post_thumbnail_id( $post_id ),
						'thumbnail_size' => $image_size,
					] );
					?>
				</a>
			<?php else : ?>
				<?php
				$this->render_post_image( [
					'image_id'       => get_post_thumbnail_id( $post_id ),
					'thumbnail_size' => $image_size,
				] );
				?>
			<?php endif; ?>

			<?php
			if ( 'yes' === $settings['show_video'] && ! empty( $video_url ) ) :
				$this->add_render_attribute( 'lightbox-attr-' . $id, [
					'class' => 'sa-play-button sa-icon-wrap sa-link',
				] );
				?>
				<div class="sa-play-button-wrapper">
					<<?php echo esc_attr( $tag ); ?>
						<?php $this->print_render_attribute_string( 'lightbox-attr-' . $id ); ?>>
						<!-- <i class="fas fa-play"></i> -->
						<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
							<path
								d="M424.4 214.7L72.4 6.6C43.8-10.3 0 6.1 0 47.9V464c0 37.5 40.7 60.1 72.4 41.3l352-208c31.4-18.5 31.5-64.1 0-82.6z" />
						</svg>
					</<?php echo esc_attr( $tag ); ?>>
				</div>
			<?php endif; ?>
		</div>
		<?php
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
					<?php echo wp_kses_post( get_the_author() ); ?>
				</span>
			</a>
		</div>
		<?php
	}

	protected function render_author_with_thumb() {
		$settings = $this->get_settings_for_display();
		if ( 'yes' !== $settings['show_author'] ) {
			return;
		}

		?>
		<div class="sa-post-author-wrapper sa-d-flex">
			<a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>"
				class="sa-d-inline-flex sa-align-items-center">
				<div class="sa-post-author-thumb sa-me-3 sa-rounded-circle sa-overflow-hidden">
					<?php echo get_avatar( get_the_author_meta( 'ID' ), 48 ); ?>
				</div>
				<div>
					<div class="sa-post-author-text">
						<?php echo get_the_author(); ?>
					</div>
					<?php $this->render_date(); ?>
				</div>
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

	protected function render_item( $post_id, $image_size, $row_index = 0 ) {
		// global $post;
		$settings = $this->get_settings_for_display();
		?>
		<?php // data-index pairs the row with the player slide it selects — see fellow-slider.js. ?>
		<div class="sa-fellow-row" data-index="<?php echo absint( $row_index ); ?>">
			<div class="sa-post-item sa-d-flex sa-p-4">

				<?php $this->render_item_thumbnail( $post_id, $image_size, 'item' ); ?>

				<div class="sa-post-content-wrapper">
					<?php
					$this->render_post_category( [
						'wrapper_class' => 'sa-post-category-style-1 sa-mb-2',
					] );

					$this->render_post_title( [
						'wrapper_class' => 'sa-mb-2',
					] );
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

	protected function render_item_feature( $post_id, $image_size, $excerpt_length ) {
		$settings = $this->get_settings_for_display();

		?>
		<div class="swiper-slide">
			<div class="sa-post-item sa-d-flex sa-p-4">

				<?php
				$this->render_item_thumbnail( $post_id, $image_size, 'fellow' );
				?>

				<div class="sa-post-content-wrapper" data-swiper-parallax="-200">
					<?php
					$this->render_post_category( [
						'wrapper_class' => 'sa-post-category-style-1 sa-mb-2',
					] );

					$this->render_post_title( [
						'wrapper_class' => 'sa-mb-2',
					] );

					$this->render_post_excerpt( $excerpt_length );
					$this->render_author_with_thumb();
					?>
				</div>
			</div>
		</div>
		<?php
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$id       = 'sa-fellow-slider-' . $this->get_id();

		$this->query_posts( $settings['posts_per_page'] );
		$wp_query = $this->get_query();

		if ( ! $wp_query->found_posts ) {
			return;
		}

		// Widgets saved before this control existed have no stored value, so Elementor
		// hands back the 'yes' default and they keep the playlist.
		$show_playlist = 'yes' === ( $settings['show_playlist'] ?? 'yes' );

		$this->add_render_attribute(
			[
				'fellow-slider' => [
					// sa-no-playlist collapses the two-column grid to one. Done with a class
					// rather than a control selector because Elementor serves a page's
					// generated CSS from a cached file that only rebuilds on save — the
					// layout has to be right on the very next load.
					'class' => 'sa-fellow-slider' . ( $show_playlist ? '' : ' sa-no-playlist' ),
					'id'    => $id,
					'data-player-settings' => [
						wp_json_encode( array_filter( [
							// 'autoHeight'    => true,
							'direction'     => $settings['direction'],
							'loop'          => ( 'yes' === $settings['loop'] ) ? true : false,
							'autoplay'      => 'yes' === $settings['autoplay'] ? [ 'delay' => $settings['autoplay_speed']['size'] ] : false,
							'speed'         => ( ! empty( $settings['speed']['size'] ) ) ? $settings['speed']['size'] : 1500,
							'pauseOnHover'  => ( 'yes' === $settings['autoplay'] && 'yes' === $settings['pause_on_hover'] ) ? true : false,
							'effect'        => $settings['transition_effect'],
							'slidesPerView' => 1,
							'loopedSlides'  => 4,
							'spaceBetween'  => 0,

							'parallax'      => true,
						] ) ),
					],
					// No data-playlist-settings: the playlist is a plain scrolling list now, so
					// there is no second Swiper to configure. Wheel and touch momentum come
					// from the browser, which is why the old mousewheel/freeMode entries have
					// no replacement — they are simply always on.
				],
			]
		);
		?>
		<div <?php $this->print_render_attribute_string( 'fellow-slider' ); ?>>
			<div class="sa-fellow swiper sa-w-100">
				<div class="swiper-wrapper sa-w-100 sa-h-100">
					<?php
					while ( $wp_query->have_posts() ) :
						$wp_query->the_post();

						$thumbnail_size = $settings['primary_thumbnail_size'];

						$this->get_posts_tags();

						$this->render_item_feature( get_the_ID(), $thumbnail_size, $settings['excerpt_length'] );

					endwhile;
					?>
				</div>
			</div>
			<?php
			/**
			 * The playlist is a scrolling list, not a carousel — no Swiper here.
			 *
			 * Swiper keeps a row's height and the distance it travels as two separate
			 * numbers, and any CSS that touches slide height desynchronises them, which is
			 * what sliced the rows in half. Native overflow scrolling has no travel
			 * distance: position IS layout, so it cannot drift. It also brings wheel,
			 * touch momentum, keyboard and screen-reader scrolling for free, and drops a
			 * whole Swiper instance (plus the two-way controller link) off the page.
			 */
			?>
			<?php
			// Show Playlist off — the rows are not rendered at all, rather than hidden with
			// CSS, so the second query loop and all that markup cost nothing.
			if ( $show_playlist ) :

				$items_class = 'sa-fellow-items sa-w-100 sa-h-100';

				if ( 'yes' !== $settings['playlist_show_scrollbar'] ) {
					$items_class .= ' sa-scrollbar-hidden';
				}

				// Widgets saved before this control existed have no stored value, so Elementor
				// hands back the 'yes' default and they get the bar too.
				if ( 'yes' === ( $settings['active_indicator'] ?? 'yes' ) ) {
					$items_class .= ' sa-has-indicator';
				}
				?>
			<div class="<?php echo esc_attr( $items_class ); ?>">
				<div class="sa-fellow-list">
					<?php
					$row_index = 0;

					while ( $wp_query->have_posts() ) :
						$wp_query->the_post();

						$thumbnail_size = $settings['primary_thumbnail_size'];

						$this->get_posts_tags();

						$this->render_item( get_the_ID(), $thumbnail_size, $row_index );

						$row_index++;

					endwhile;
					?>
				</div>
			</div>
				<?php endif; ?>
		</div>

		<?php

		wp_reset_postdata();
	}
}

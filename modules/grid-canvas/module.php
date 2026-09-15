<?php

namespace Sky_Addons\Modules\GridCanvas;

use Elementor\Controls_Manager;
use Sky_Addons\Base\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Module extends Module_Base {

	/**
	 * Box styles for the injected `.sa-gc-layer`.
	 *
	 * Emitted as a `style` attribute on the element itself, not as a stylesheet rule:
	 * the layer is a real DOM node from the first byte of HTML, so it must be out of
	 * flow before any stylesheet is parsed — otherwise it lands in the flex/grid flow
	 * of its host for one frame (or forever, on a page whose cached Elementor CSS has
	 * not been regenerated). Keeping it here also means the module ships no global
	 * `.sa-gc-layer` rule on pages that never use the grid.
	 *
	 * Deliberately no z-index / opacity / transform — the layer must not form a
	 * stacking context, or the pseudo-elements mix-blend-mode has nothing to blend
	 * against.
	 */
	const LAYER_STYLE = 'position:absolute;inset:0;pointer-events:none;border-radius:inherit';

	/**
	 * Memoized settings of the document being viewed (Page Settings variant).
	 *
	 * @var array|null
	 */
	private $page_settings = null;

	/**
	 * Guard so the page-level layer is printed once, whichever hook gets there first.
	 *
	 * @var bool
	 */
	private $page_layer_printed = false;

	public function __construct() {
		parent::__construct();
		$this->add_actions();
	}

	public function get_name() {
		return 'sky-grid-canvas';
	}

	public function register_section( $element ) {
		$element->start_controls_section(
			'section_sky_addons_gc_controls',
			[
				'tab'   => Controls_Manager::TAB_STYLE,
				'label' => esc_html__( 'Grid Canvas', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ) . sky_addons_get_icon(),
			]
		);
		$element->end_controls_section();
	}

	/**
	 * Page Settings variant — draws the grid across the whole page.
	 *
	 * On a page document Elementor resolves {{WRAPPER}} to `body.elementor-page-{id}`
	 * and adds the enable switcher's prefix_class to <body>, so the exact same
	 * selectors used for sections/containers apply to the page. Unlike the
	 * section/container path there is no core `section_background` to inject after,
	 * so the section is opened, filled and closed in one call here.
	 */
	public function register_page_controls( $document ) {
		// Only real page/post documents get the whole-page grid. `register_controls`
		// is also bound to `elementor/element/{section,container}/…/before_section_end`;
		// opening `section_sky_addons_gc_controls` on a section- or container-named
		// document would re-fire that hook and add every control a second time
		// ("Cannot redeclare control"). Restricting the type avoids that entirely and
		// keeps Page Settings off library/header/footer/kit documents.
		if ( ! in_array( $document->get_name(), [ 'wp-page', 'wp-post' ], true ) ) {
			return;
		}

		$document->start_controls_section(
			'section_sky_addons_gc_controls',
			[
				'tab'   => Controls_Manager::TAB_STYLE,
				'label' => esc_html__( 'Grid Canvas', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ) . sky_addons_get_icon(),
			]
		);

		$this->register_controls( $document, [] );

		// ── Exclude / Escape (page settings only) ────────────────────────────
		$document->add_control(
			'sa_gc_escape_heading',
			[
				'label'     => esc_html__( 'Exclude Sections', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [ 'sa_gc_enable' => 'yes' ],
			]
		);

		$document->add_control(
			'sa_gc_escape',
			[
				'label'       => esc_html__( 'Exclude Selectors', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXTAREA,
				'placeholder' => '.site-footer, header, .no-grid',
				'description' => esc_html__( 'Comma-separated CSS selectors. The page grid is hidden inside these elements — they are painted over with the Escape Background color. Frontend only (not previewed live in the editor).', 'sky-elementor-addons' ),
				'condition'   => [ 'sa_gc_enable' => 'yes' ],
			]
		);

		$document->add_control(
			'sa_gc_escape_bg',
			[
				'label'       => esc_html__( 'Escape Background', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::COLOR,
				'default'     => '#FFFFFF',
				'description' => esc_html__( 'Color painted over the grid inside excluded elements. Set it to your page background so the grid cleanly disappears there.', 'sky-elementor-addons' ),
				'condition'   => [
					'sa_gc_enable'  => 'yes',
					'sa_gc_escape!' => '',
				],
				'selectors'   => [ '{{WRAPPER}}' => '--sa-gc-escape-bg: {{VALUE}};' ],
			]
		);

		$document->end_controls_section();
	}

	/**
	 * Add the grid's prefix classes to <body> for the Page Settings variant.
	 *
	 * Elementor applies `prefix_class` only to rendered elements (via
	 * Element_Base::add_render_attributes) — never to a page document. So the
	 * enable/type/animation/mask classes the CSS selectors depend on never reach
	 * <body> on their own. We add them here from the page's own settings; the CSS
	 * custom properties and structural rules are already emitted by the control
	 * `selectors` against `{{WRAPPER}}` (= `body.elementor-page-{id}`).
	 */
	public function add_body_classes( $classes ) {
		$settings = $this->get_page_settings();
		if ( empty( $settings['sa_gc_enable'] ) || 'yes' !== $settings['sa_gc_enable'] ) {
			return $classes;
		}

		$type = ! empty( $settings['sa_gc_type'] ) ? $settings['sa_gc_type'] : 'vertical';
		$anim = ! empty( $settings['sa_gc_animation'] ) ? $settings['sa_gc_animation'] : 'none';

		$classes[] = 'sa-grid-canvas-yes';
		$classes[] = 'sa-gc-type-' . $type;
		$classes[] = 'sa-gc-anim-' . $anim;

		if ( in_array( $type, [ 'horizontal', 'both' ], true ) ) {
			$classes[] = 'sa-gc-horizontal-yes';
		}

		if ( ! empty( $settings['sa_gc_mask'] ) && 'yes' === $settings['sa_gc_mask'] ) {
			$classes[] = 'sa-gc-mask-yes';
		}

		return $classes;
	}

	public function register_controls( $element, $args ) {
		$element->add_control(
			'sa_gc_enable',
			[
				'label'        => esc_html__( 'Enable', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'render_type'  => 'template',
				'prefix_class' => 'sa-grid-canvas-',
			]
		);

		// ── Line Type ───────────────────────────────────────────────────────────
		$element->add_control(
			'sa_gc_type',
			[
				'label'        => esc_html__( 'Line Type', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SELECT,
				'default'      => 'vertical',
				'options'      => [
					'vertical'   => esc_html__( 'Vertical', 'sky-elementor-addons' ),
					'horizontal' => esc_html__( 'Horizontal', 'sky-elementor-addons' ),
					'both'       => esc_html__( 'Both (Grid)', 'sky-elementor-addons' ),
				],
				'condition'    => [ 'sa_gc_enable' => 'yes' ],
				'render_type'  => 'template',
				'prefix_class' => 'sa-gc-type-',
			]
		);

		$element->add_control(
			'sa_gc_horizontal_class',
			[
				'type'         => Controls_Manager::HIDDEN,
				'default'      => 'yes',
				'prefix_class' => 'sa-gc-horizontal-',
				'condition'    => [
					'sa_gc_enable' => 'yes',
					'sa_gc_type'   => [ 'horizontal', 'both' ],
				],
				'render_type'  => 'template',
			]
		);

		// ── Columns / Rows ──────────────────────────────────────────────────────
		$element->add_responsive_control(
			'sa_gc_columns',
			[
				'label'          => esc_html__( 'Columns', 'sky-elementor-addons' ),
				'type'           => Controls_Manager::SLIDER,
				'range'          => [
					'px' => [
						'min'  => 2,
						'max'  => 24,
						'step' => 1,
					],
				],
				'default'        => [ 'size' => 12 ],
				'tablet_default' => [ 'size' => 8 ],
				'mobile_default' => [ 'size' => 4 ],
				'condition'      => [ 'sa_gc_enable' => 'yes' ],
				'selectors'      => [ '{{WRAPPER}}' => '--sa-gc-columns: {{SIZE}};' ],
			]
		);

		$element->add_responsive_control(
			'sa_gc_rows',
			[
				'label'          => esc_html__( 'Rows', 'sky-elementor-addons' ),
				'type'           => Controls_Manager::SLIDER,
				'range'          => [
					'px' => [
						'min'  => 2,
						'max'  => 24,
						'step' => 1,
					],
				],
				'default'        => [ 'size' => 8 ],
				'tablet_default' => [ 'size' => 6 ],
				'mobile_default' => [ 'size' => 4 ],
				'condition'      => [
					'sa_gc_enable' => 'yes',
					'sa_gc_type'   => [ 'horizontal', 'both' ],
				],
				'selectors'      => [ '{{WRAPPER}}' => '--sa-gc-rows: {{SIZE}};' ],
			]
		);

		// ── Colors ──────────────────────────────────────────────────────────────
		$element->add_control(
			'sa_gc_line_color',
			[
				'label'     => esc_html__( 'Line Start Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => 'rgba(255, 255, 255, 0.12)',
				'condition' => [ 'sa_gc_enable' => 'yes' ],
				'selectors' => [ '{{WRAPPER}}' => '--sa-gc-line-color: {{VALUE}};' ],
			]
		);

		$element->add_control(
			'sa_gc_line_color_end',
			[
				'label'       => esc_html__( 'Line End Color', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::COLOR,
				'description' => esc_html__( 'Optional. Lines blend from the start color to this color across the grid. Overrides Column Fill.', 'sky-elementor-addons' ),
				'condition'   => [ 'sa_gc_enable' => 'yes' ],
				'render_type' => 'template',
				'selectors'   => [ '{{WRAPPER}}' => '--sa-gc-line-color-end: {{VALUE}};' ],
			]
		);

		$element->add_control(
			'sa_gc_col_color',
			[
				'label'       => esc_html__( 'Column Fill', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::COLOR,
				'description' => esc_html__( 'Tints the area between lines. Not available while Line End Color is set.', 'sky-elementor-addons' ),
				'condition'   => [
					'sa_gc_enable'         => 'yes',
					'sa_gc_line_color_end' => '',
				],
				'selectors'   => [ '{{WRAPPER}}' => '--sa-gc-col-color: {{VALUE}};' ],
			]
		);

		// ── Line Width / Max Width ───────────────────────────────────────────────
		$element->add_responsive_control(
			'sa_gc_width',
			[
				'label'      => esc_html__( 'Line Width', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 1,
						'max' => 10,
					],
				],
				'default' => [
					'size' => 1,
					'unit' => 'px',
				],
				'condition'  => [ 'sa_gc_enable' => 'yes' ],
				'selectors'  => [ '{{WRAPPER}}' => '--sa-gc-width: {{SIZE}}{{UNIT}};' ],
			]
		);

		$element->add_responsive_control(
			'sa_gc_max_width',
			[
				'label'      => esc_html__( 'Max Width', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 3000,
					],
					'%'  => [
						'min' => 0,
						'max' => 100,
					],
				],
				'condition'  => [ 'sa_gc_enable' => 'yes' ],
				'selectors'  => [ '{{WRAPPER}}' => '--sa-gc-max-width: {{SIZE}}{{UNIT}};' ],
			]
		);

		// ── Appearance ──────────────────────────────────────────────────────────
		$element->add_control(
			'sa_gc_opacity',
			[
				'label'     => esc_html__( 'Opacity', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min'  => 0,
						'max'  => 1,
						'step' => 0.01,
					],
				],
				'default'   => [ 'size' => 1 ],
				'separator' => 'before',
				'condition' => [ 'sa_gc_enable' => 'yes' ],
				'selectors' => [ '{{WRAPPER}}' => '--sa-gc-opacity: {{SIZE}};' ],
			]
		);

		$element->add_control(
			'sa_gc_blend',
			[
				'label'     => esc_html__( 'Blend Mode', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'normal',
				'options'   => [
					'normal'      => esc_html__( 'Normal', 'sky-elementor-addons' ),
					'multiply'    => esc_html__( 'Multiply', 'sky-elementor-addons' ),
					'screen'      => esc_html__( 'Screen', 'sky-elementor-addons' ),
					'overlay'     => esc_html__( 'Overlay', 'sky-elementor-addons' ),
					'lighten'     => esc_html__( 'Lighten', 'sky-elementor-addons' ),
					'color-dodge' => esc_html__( 'Color Dodge', 'sky-elementor-addons' ),
				],
				'condition' => [ 'sa_gc_enable' => 'yes' ],
				'selectors' => [ '{{WRAPPER}}' => '--sa-gc-blend: {{VALUE}};' ],
			]
		);

		$element->add_control(
			'sa_gc_z_index',
			[
				'label'       => esc_html__( 'Z-Index', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::NUMBER,
				'description' => esc_html__( 'Stacking order inside this element. At -1 the grid sits behind the content and behind a Background Overlay; raise it to 0 or more to draw the lines on top of the overlay.', 'sky-elementor-addons' ),
				'min'         => -1,
				'max'         => 99,
				'default'     => -1,
				'condition'   => [ 'sa_gc_enable' => 'yes' ],
				'selectors'   => [ '{{WRAPPER}}' => '--sa-gc-z-index: {{VALUE}};' ],
			]
		);

		// ── Gradient Mask ────────────────────────────────────────────────────────
		$element->add_control(
			'sa_gc_mask_heading',
			[
				'label'     => esc_html__( 'Gradient Mask', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [ 'sa_gc_enable' => 'yes' ],
			]
		);

		$element->add_control(
			'sa_gc_mask',
			[
				'label'        => esc_html__( 'Enable', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'render_type'  => 'template',
				'prefix_class' => 'sa-gc-mask-',
				'condition'    => [ 'sa_gc_enable' => 'yes' ],
			]
		);

		$element->add_control(
			'sa_gc_mask_direction',
			[
				'label'     => esc_html__( 'Direction', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'to bottom',
				'options'   => [
					'to bottom' => esc_html__( 'Top → Bottom', 'sky-elementor-addons' ),
					'to top'    => esc_html__( 'Bottom → Top', 'sky-elementor-addons' ),
					'to right'  => esc_html__( 'Left → Right', 'sky-elementor-addons' ),
					'to left'   => esc_html__( 'Right → Left', 'sky-elementor-addons' ),
				],
				'condition' => [
					'sa_gc_enable' => 'yes',
					'sa_gc_mask'   => 'yes',
				],
				'selectors' => [ '{{WRAPPER}}' => '--sa-gc-mask-dir: {{VALUE}};' ],
			]
		);

		$element->add_control(
			'sa_gc_mask_start_stop',
			[
				'label'      => esc_html__( 'Visible Until', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ '%' ],
				'range'      => [
					'%' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default'    => [
					'size' => 30,
					'unit' => '%',
				],
				'condition'  => [
					'sa_gc_enable' => 'yes',
					'sa_gc_mask'   => 'yes',
				],
				'selectors'  => [ '{{WRAPPER}}' => '--sa-gc-mask-start-stop: {{SIZE}}{{UNIT}};' ],
			]
		);

		$element->add_control(
			'sa_gc_mask_end_stop',
			[
				'label'      => esc_html__( 'Hidden From', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ '%' ],
				'range'      => [
					'%' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default'    => [
					'size' => 100,
					'unit' => '%',
				],
				'condition'  => [
					'sa_gc_enable' => 'yes',
					'sa_gc_mask'   => 'yes',
				],
				'selectors'  => [ '{{WRAPPER}}' => '--sa-gc-mask-end-stop: {{SIZE}}{{UNIT}};' ],
			]
		);

		// ── Animation ────────────────────────────────────────────────────────────
		$element->add_control(
			'sa_gc_anim_heading',
			[
				'label'     => esc_html__( 'Animation', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [ 'sa_gc_enable' => 'yes' ],
			]
		);

		$element->add_control(
			'sa_gc_animation',
			[
				'label'        => esc_html__( 'Mode', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SELECT,
				'default'      => 'none',
				'options'      => [
					'none'    => esc_html__( 'None', 'sky-elementor-addons' ),
					'fade-in' => esc_html__( 'Fade In (On Scroll)', 'sky-elementor-addons' ),
					'draw-in' => esc_html__( 'Draw In (On Scroll)', 'sky-elementor-addons' ),
					'pulse'   => esc_html__( 'Pulse (Loop)', 'sky-elementor-addons' ),
				],
				'condition'    => [ 'sa_gc_enable' => 'yes' ],
				'render_type'  => 'template',
				'prefix_class' => 'sa-gc-anim-',
			]
		);

		$element->add_control(
			'sa_gc_anim_duration',
			[
				'label'       => esc_html__( 'Pulse Speed (s)', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::SLIDER,
				'description' => esc_html__( 'Seconds per pulse cycle. Lower is faster.', 'sky-elementor-addons' ),
				'range'       => [
					'px' => [
						'min'  => 0.3,
						'max'  => 10,
						'step' => 0.1,
					],
				],
				'default'     => [ 'size' => 3 ],
				'condition'   => [
					'sa_gc_enable'    => 'yes',
					'sa_gc_animation' => 'pulse',
				],
				'selectors'   => [ '{{WRAPPER}}' => '--sa-gc-anim-duration: {{SIZE}}s;' ],
			]
		);

		$element->add_control(
			'sa_gc_anim_delay',
			[
				'label'     => esc_html__( 'Delay (s)', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min'  => 0,
						'max'  => 5,
						'step' => 0.1,
					],
				],
				'default'   => [ 'size' => 0 ],
				'condition' => [
					'sa_gc_enable'    => 'yes',
					'sa_gc_animation' => 'pulse',
				],
				'selectors' => [ '{{WRAPPER}}' => '--sa-gc-anim-delay: {{SIZE}}s;' ],
			]
		);

		// ── Output (base structural CSS) ─────────────────────────────────────────
		// Everything paints into `> .sa-gc-layer` (emitted server-side by
		// close_layer_buffer() / render_page_layer()), never into the host own
		// ::before/::after. Elementor container Background Overlay owns
		// `.e-con::before`; painting there silently replaced it. The layer itself must
		// stay free of z-index / opacity / transform so it does NOT form a stacking
		// context — the pseudo-elements keep their own z-index and mix-blend-mode,
		// which must still resolve against the host `isolation: isolate` group to
		// blend the way they did before.
		$element->add_control(
			'sa_gc_output',
			[
				'type'        => Controls_Manager::HIDDEN,
				'default'     => '1',
				'condition'   => [ 'sa_gc_enable' => 'yes' ],
				'render_type' => 'template',
				'selectors'   => [
					'{{WRAPPER}}.sa-grid-canvas-yes' => 'position: relative; isolation: isolate;',
					'{{WRAPPER}}.sa-grid-canvas-yes > .sa-gc-layer::before, {{WRAPPER}}.sa-grid-canvas-yes > .sa-gc-layer::after' => 'content: ""; position: absolute; inset: 0; pointer-events: none; border-radius: inherit; z-index: var(--sa-gc-z-index, -1); opacity: var(--sa-gc-opacity, 1); mix-blend-mode: var(--sa-gc-blend, normal); background-repeat: no-repeat; background-position: center;',
					'{{WRAPPER}}.sa-grid-canvas-yes > .sa-gc-layer::before' => 'background-image: linear-gradient(to left, var(--sa-gc-line-color, rgba(255,255,255,0.12)) 0, var(--sa-gc-line-color, rgba(255,255,255,0.12)) var(--sa-gc-width, 1px), transparent var(--sa-gc-width, 1px)), repeating-linear-gradient(90deg, var(--sa-gc-line-color, rgba(255,255,255,0.12)) 0, var(--sa-gc-line-color, rgba(255,255,255,0.12)) var(--sa-gc-width, 1px), var(--sa-gc-col-color, transparent) var(--sa-gc-width, 1px), var(--sa-gc-col-color, transparent) calc(100% / var(--sa-gc-columns, 12))); background-size: var(--sa-gc-max-width, 100%) 100%;',
					'{{WRAPPER}}.sa-grid-canvas-yes.sa-gc-type-vertical > .sa-gc-layer::after' => 'display: none;',
					'{{WRAPPER}}.sa-grid-canvas-yes.sa-gc-type-horizontal > .sa-gc-layer::before' => 'display: none;',
					'{{WRAPPER}}.sa-grid-canvas-yes.sa-gc-horizontal-yes > .sa-gc-layer::after' => 'background-image: linear-gradient(to bottom, var(--sa-gc-line-color, rgba(255,255,255,0.12)) 0, var(--sa-gc-line-color, rgba(255,255,255,0.12)) var(--sa-gc-width, 1px), transparent var(--sa-gc-width, 1px)), repeating-linear-gradient(0deg, var(--sa-gc-line-color, rgba(255,255,255,0.12)) 0, var(--sa-gc-line-color, rgba(255,255,255,0.12)) var(--sa-gc-width, 1px), var(--sa-gc-col-color, transparent) var(--sa-gc-width, 1px), var(--sa-gc-col-color, transparent) calc(100% / var(--sa-gc-rows, 8))); background-size: 100% var(--sa-gc-max-width, 100%);',
					'{{WRAPPER}}.sa-grid-canvas-yes.sa-gc-mask-yes > .sa-gc-layer::before, {{WRAPPER}}.sa-grid-canvas-yes.sa-gc-mask-yes > .sa-gc-layer::after' => '-webkit-mask-image: linear-gradient(var(--sa-gc-mask-dir, to bottom), rgba(0,0,0,1) var(--sa-gc-mask-start-stop, 30%), rgba(0,0,0,0) var(--sa-gc-mask-end-stop, 100%)); mask-image: linear-gradient(var(--sa-gc-mask-dir, to bottom), rgba(0,0,0,1) var(--sa-gc-mask-start-stop, 30%), rgba(0,0,0,0) var(--sa-gc-mask-end-stop, 100%));',
				],
			]
		);

		/**
		 * Color interpolation — the line pattern becomes a mask over a start→end
		 * gradient, so line color blends smoothly across the whole grid instead of
		 * stepping per line. Overrides Column Fill (the gaps are masked out).
		 * Emitted only when an end color is set. Mask size/position mirror the
		 * background box so lines stay put when Max Width is set. The guaranteed
		 * `.sa-grid-canvas-yes` class is repeated in every selector to raise
		 * specificity one notch above the base sa_gc_output rules, so this wins
		 * regardless of the order Elementor emits control CSS (it does NOT emit in
		 * registration order). Repeating a class for specificity is intentional.
		 */
		$element->add_control(
			'sa_gc_color_end_output',
			[
				'type'        => Controls_Manager::HIDDEN,
				'default'     => '1',
				'render_type' => 'template',
				'condition'   => [
					'sa_gc_enable'          => 'yes',
					'sa_gc_line_color_end!' => '',
				],
				'selectors'   => [
					'{{WRAPPER}}.sa-grid-canvas-yes.sa-grid-canvas-yes > .sa-gc-layer::before' => 'background-image: linear-gradient(90deg, var(--sa-gc-line-color, rgba(255,255,255,0.12)), var(--sa-gc-line-color-end)); background-size: var(--sa-gc-max-width, 100%) 100%; -webkit-mask-image: linear-gradient(to left, #000 0, #000 var(--sa-gc-width, 1px), transparent var(--sa-gc-width, 1px)), repeating-linear-gradient(90deg, #000 0, #000 var(--sa-gc-width, 1px), transparent var(--sa-gc-width, 1px), transparent calc(100% / var(--sa-gc-columns, 12))); mask-image: linear-gradient(to left, #000 0, #000 var(--sa-gc-width, 1px), transparent var(--sa-gc-width, 1px)), repeating-linear-gradient(90deg, #000 0, #000 var(--sa-gc-width, 1px), transparent var(--sa-gc-width, 1px), transparent calc(100% / var(--sa-gc-columns, 12))); -webkit-mask-size: var(--sa-gc-max-width, 100%) 100%; mask-size: var(--sa-gc-max-width, 100%) 100%; -webkit-mask-repeat: no-repeat; mask-repeat: no-repeat; -webkit-mask-position: center; mask-position: center;',
					'{{WRAPPER}}.sa-grid-canvas-yes.sa-grid-canvas-yes.sa-gc-horizontal-yes > .sa-gc-layer::after' => 'background-image: linear-gradient(180deg, var(--sa-gc-line-color, rgba(255,255,255,0.12)), var(--sa-gc-line-color-end)); background-size: 100% var(--sa-gc-max-width, 100%); -webkit-mask-image: linear-gradient(to bottom, #000 0, #000 var(--sa-gc-width, 1px), transparent var(--sa-gc-width, 1px)), repeating-linear-gradient(0deg, #000 0, #000 var(--sa-gc-width, 1px), transparent var(--sa-gc-width, 1px), transparent calc(100% / var(--sa-gc-rows, 8))); mask-image: linear-gradient(to bottom, #000 0, #000 var(--sa-gc-width, 1px), transparent var(--sa-gc-width, 1px)), repeating-linear-gradient(0deg, #000 0, #000 var(--sa-gc-width, 1px), transparent var(--sa-gc-width, 1px), transparent calc(100% / var(--sa-gc-rows, 8))); -webkit-mask-size: 100% var(--sa-gc-max-width, 100%); mask-size: 100% var(--sa-gc-max-width, 100%); -webkit-mask-repeat: no-repeat; mask-repeat: no-repeat; -webkit-mask-position: center; mask-position: center;',
					'{{WRAPPER}}.sa-grid-canvas-yes.sa-grid-canvas-yes.sa-gc-mask-yes > .sa-gc-layer::before' => '-webkit-mask-image: linear-gradient(var(--sa-gc-mask-dir, to bottom), rgba(0,0,0,1) var(--sa-gc-mask-start-stop, 30%), rgba(0,0,0,0) var(--sa-gc-mask-end-stop, 100%)), linear-gradient(to left, #000 0, #000 var(--sa-gc-width, 1px), transparent var(--sa-gc-width, 1px)), repeating-linear-gradient(90deg, #000 0, #000 var(--sa-gc-width, 1px), transparent var(--sa-gc-width, 1px), transparent calc(100% / var(--sa-gc-columns, 12))); mask-image: linear-gradient(var(--sa-gc-mask-dir, to bottom), rgba(0,0,0,1) var(--sa-gc-mask-start-stop, 30%), rgba(0,0,0,0) var(--sa-gc-mask-end-stop, 100%)), linear-gradient(to left, #000 0, #000 var(--sa-gc-width, 1px), transparent var(--sa-gc-width, 1px)), repeating-linear-gradient(90deg, #000 0, #000 var(--sa-gc-width, 1px), transparent var(--sa-gc-width, 1px), transparent calc(100% / var(--sa-gc-columns, 12))); -webkit-mask-size: 100% 100%, var(--sa-gc-max-width, 100%) 100%, var(--sa-gc-max-width, 100%) 100%; mask-size: 100% 100%, var(--sa-gc-max-width, 100%) 100%, var(--sa-gc-max-width, 100%) 100%; -webkit-mask-repeat: no-repeat; mask-repeat: no-repeat; -webkit-mask-position: center; mask-position: center; -webkit-mask-composite: source-in, source-over, source-over; mask-composite: intersect, add, add;',
					'{{WRAPPER}}.sa-grid-canvas-yes.sa-grid-canvas-yes.sa-gc-mask-yes.sa-gc-horizontal-yes > .sa-gc-layer::after' => '-webkit-mask-image: linear-gradient(var(--sa-gc-mask-dir, to bottom), rgba(0,0,0,1) var(--sa-gc-mask-start-stop, 30%), rgba(0,0,0,0) var(--sa-gc-mask-end-stop, 100%)), linear-gradient(to bottom, #000 0, #000 var(--sa-gc-width, 1px), transparent var(--sa-gc-width, 1px)), repeating-linear-gradient(0deg, #000 0, #000 var(--sa-gc-width, 1px), transparent var(--sa-gc-width, 1px), transparent calc(100% / var(--sa-gc-rows, 8))); mask-image: linear-gradient(var(--sa-gc-mask-dir, to bottom), rgba(0,0,0,1) var(--sa-gc-mask-start-stop, 30%), rgba(0,0,0,0) var(--sa-gc-mask-end-stop, 100%)), linear-gradient(to bottom, #000 0, #000 var(--sa-gc-width, 1px), transparent var(--sa-gc-width, 1px)), repeating-linear-gradient(0deg, #000 0, #000 var(--sa-gc-width, 1px), transparent var(--sa-gc-width, 1px), transparent calc(100% / var(--sa-gc-rows, 8))); -webkit-mask-size: 100% 100%, 100% var(--sa-gc-max-width, 100%), 100% var(--sa-gc-max-width, 100%); mask-size: 100% 100%, 100% var(--sa-gc-max-width, 100%), 100% var(--sa-gc-max-width, 100%); -webkit-mask-repeat: no-repeat; mask-repeat: no-repeat; -webkit-mask-position: center; mask-position: center; -webkit-mask-composite: source-in, source-over, source-over; mask-composite: intersect, add, add;',
				],
			]
		);
	}

	public function enqueue_styles() {
		$css  = '
/* Grid lines live on the pseudo-elements of the injected `.sa-gc-layer` child, never
   on the host own ::before/::after — Elementor container Background Overlay owns
   `.e-con::before` and was being silently overwritten.
   The layer box rule (position/inset/pointer-events/border-radius) is NOT here: it is a
   `style` attribute on the element itself (self::LAYER_STYLE), so the layer is out of
   flow before any stylesheet is parsed and this file stays free of rules that a page
   without a grid would download for nothing. */
@keyframes sa-gc-fade-in {
	from { opacity: 0; }
	to   { opacity: var(--sa-gc-opacity, 1); }
}
@keyframes sa-gc-pulse {
	0%, 100% { opacity: var(--sa-gc-opacity, 1); }
	50%       { opacity: calc(var(--sa-gc-opacity, 1) * 0.15); }
}
.sa-grid-canvas-yes.sa-gc-anim-fade-in > .sa-gc-layer::before {
	animation: sa-gc-fade-in var(--sa-gc-anim-duration, 1s) var(--sa-gc-anim-delay, 0s) forwards ease-out;
}
.sa-grid-canvas-yes.sa-gc-anim-fade-in.sa-gc-horizontal-yes > .sa-gc-layer::after {
	animation: sa-gc-fade-in var(--sa-gc-anim-duration, 1s) var(--sa-gc-anim-delay, 0s) forwards ease-out;
}
.sa-grid-canvas-yes.sa-gc-anim-pulse > .sa-gc-layer::before,
.sa-grid-canvas-yes.sa-gc-anim-pulse.sa-gc-horizontal-yes > .sa-gc-layer::after {
	animation: sa-gc-pulse var(--sa-gc-anim-duration, 3s) ease-in-out infinite;
	animation-delay: var(--sa-gc-anim-delay, 0s);
}
/* Draw In — entrance: lines grow into place with a soft fade, expo-style ease. */
@keyframes sa-gc-draw-in-v {
	from { transform: scaleY(0); opacity: 0; }
	to   { transform: scaleY(1); opacity: var(--sa-gc-opacity, 1); }
}
@keyframes sa-gc-draw-in-h {
	from { transform: scaleX(0); opacity: 0; }
	to   { transform: scaleX(1); opacity: var(--sa-gc-opacity, 1); }
}
.sa-grid-canvas-yes.sa-gc-anim-draw-in > .sa-gc-layer::before,
.sa-grid-canvas-yes.sa-gc-anim-draw-down > .sa-gc-layer::before {
	transform-origin: top center;
	animation: sa-gc-draw-in-v var(--sa-gc-anim-duration, 1.5s) var(--sa-gc-anim-delay, 0s) both cubic-bezier(0.16, 1, 0.3, 1);
}
.sa-grid-canvas-yes.sa-gc-anim-draw-in.sa-gc-horizontal-yes > .sa-gc-layer::after,
.sa-grid-canvas-yes.sa-gc-anim-draw-down.sa-gc-horizontal-yes > .sa-gc-layer::after {
	transform-origin: left center;
	animation: sa-gc-draw-in-h var(--sa-gc-anim-duration, 1.5s) var(--sa-gc-anim-delay, 0s) both cubic-bezier(0.16, 1, 0.3, 1);
}
/* ── Viewport-triggered modes (no JS) ───────────────────────────────────────
   Fade In and Draw In are re-bound to a view() timeline here so they play as the
   section scrolls INTO view. Without this they run on the document timeline at
   page load, so any section below the fold has finished animating before the
   visitor ever reaches it — the effect is invisible in practice.
   Range `entry 0% entry 100%` covers both cases: for a short section it completes
   when the section is fully on screen; for a section taller than the viewport it
   completes when the top edge of the section reaches the top of the screen.
   Draw Down is kept as an alias of Draw In for pages saved before 4.5.0.
   Browsers without animation-timeline (Safari < 26, Chrome < 115) keep the
   time-based rules above — Fade In / Draw In still play, just on page load.
   NOTE: animation-timeline MUST come after the animation shorthand — the
   shorthand resets it to auto. */
@supports (animation-timeline: view()) {
	.sa-grid-canvas-yes.sa-gc-anim-fade-in > .sa-gc-layer::before,
	.sa-grid-canvas-yes.sa-gc-anim-fade-in.sa-gc-horizontal-yes > .sa-gc-layer::after {
		animation: sa-gc-fade-in 1s linear both;
		animation-timeline: view();
		animation-range: entry 0% entry 100%;
	}
	.sa-grid-canvas-yes.sa-gc-anim-draw-in > .sa-gc-layer::before,
	.sa-grid-canvas-yes.sa-gc-anim-draw-down > .sa-gc-layer::before {
		transform-origin: top center;
		animation: sa-gc-draw-in-v 1s linear both;
		animation-timeline: view();
		animation-range: entry 0% entry 100%;
	}
	.sa-grid-canvas-yes.sa-gc-anim-draw-in.sa-gc-horizontal-yes > .sa-gc-layer::after,
	.sa-grid-canvas-yes.sa-gc-anim-draw-down.sa-gc-horizontal-yes > .sa-gc-layer::after {
		transform-origin: left center;
		animation: sa-gc-draw-in-h 1s linear both;
		animation-timeline: view();
		animation-range: entry 0% entry 100%;
	}
}
';
		$css .= $this->get_escape_css();

		// Inline CSS handle — no file (see .ai/ASSETS-LOAD.md "Inline CSS Handles").
		// Registered always, enqueued only when this request paints a grid:
		//  - editor/preview: elements re-render client-side, so there is no reliable
		//    server-side signal — always load;
		//  - the queried document uses the grid (Page Settings, or the `sa_gc_enable`
		//    key present anywhere in its saved element data) — enqueued here, so it
		//    lands in <head>;
		//  - anything else (theme-builder parts, popups, archive loops) — enqueued
		//    lazily by open_layer_buffer() the first time a grid-enabled element renders.
		//    Only keyframes and animation rules live in this stylesheet, so arriving
		//    with the late styles costs nothing structural.
		wp_register_style( 'sa-grid-canvas', false, [], SKY_ADDONS_VERSION );
		wp_add_inline_style( 'sa-grid-canvas', $css );

		if ( sky_addons_editor_mode() || $this->request_uses_grid() ) {
			wp_enqueue_style( 'sa-grid-canvas' );
		}

		$this->enqueue_editor_layer_script();
	}

	/**
	 * Does the document being viewed use the grid anywhere?
	 *
	 * Cheap head-time probe so the stylesheet can be enqueued in <head> for the normal
	 * case instead of arriving with the late styles (an animated grid would otherwise
	 * paint at rest and then snap into its entrance animation). A miss is not a bug —
	 * open_layer_buffer() enqueues on demand.
	 */
	private function request_uses_grid() {
		$settings = $this->get_page_settings();
		if ( ! empty( $settings['sa_gc_enable'] ) && 'yes' === $settings['sa_gc_enable'] ) {
			return true;
		}

		if ( is_admin() || ! is_singular() ) {
			return false;
		}

		$post_id = get_queried_object_id();
		if ( ! $post_id ) {
			return false;
		}

		// Elementor saves only non-default settings, and the enable switcher defaults
		// to empty — so the key is in `_elementor_data` only if it was switched on at
		// least once. A stale key just loads ~4 KB of inline CSS; a missed one falls
		// through to the lazy path.
		$data = get_post_meta( $post_id, '_elementor_data', true );

		return is_string( $data ) && false !== strpos( $data, 'sa_gc_enable' );
	}

	/**
	 * Settings of the Elementor document being viewed (Page Settings variant), memoized.
	 */
	private function get_page_settings() {
		if ( null !== $this->page_settings ) {
			return $this->page_settings;
		}

		$this->page_settings = [];

		if ( is_admin() || ! is_singular() || ! class_exists( '\Elementor\Plugin' ) ) {
			return $this->page_settings;
		}

		$post_id = get_queried_object_id();
		if ( ! $post_id ) {
			return $this->page_settings;
		}

		$document = \Elementor\Plugin::$instance->documents->get( $post_id );
		if ( ! $document ) {
			return $this->page_settings;
		}

		$this->page_settings = (array) $document->get_settings();

		return $this->page_settings;
	}

	/**
	 * The layer markup. `$extra_style` is appended to the box style attribute.
	 */
	private function get_layer_html( $extra_style = '' ) {
		return '<div class="sa-gc-layer" aria-hidden="true" style="' . esc_attr( self::LAYER_STYLE . $extra_style ) . '"></div>';
	}

	private function is_grid_enabled( $element ) {
		return 'yes' === $element->get_settings( 'sa_gc_enable' );
	}

	/**
	 * Open a buffer around a grid-enabled section/container.
	 *
	 * The grid used to paint into the host element own ::before/::after. Elementor
	 * container background overlay also renders into `.e-con::before`
	 * (elementor/includes/elements/container.php:793, $background_overlay_selector), so a
	 * container carrying both lost its overlay entirely — the grid rule won on
	 * specificity and nothing warned about it. The grid paints into its own child
	 * element instead, leaving ::before free for Elementor.
	 *
	 * That child is emitted server-side. It was briefly injected from JS on
	 * `frontend/element_ready/{section,container}`, but that hook is a poor host for a
	 * decoration that used to be pure CSS: Elementor schedules it in a `setTimeout`
	 * (elementor/assets/js/frontend-modules.js:221) so the grid popped in a macrotask
	 * after first paint; it never fires at all inside a `data-delay-child-handlers`
	 * subtree such as Nested Tabs / Nested Accordion / Mega Menu
	 * (elementor/assets/js/frontend.js:204), so a grid inside a closed tab appeared
	 * only when the tab was opened; and with JS off the grid vanished completely.
	 *
	 * Elementor exposes no hook inside an element opening tag —
	 * Element_Base::print_element() fires `elementor/frontend/{type}/before_render`
	 * BEFORE before_render() prints that tag (elementor/includes/base/element-base.php:513
	 * vs :550) — so the element is buffered between the typed before_render and
	 * after_render actions and the layer spliced in after the opening tag. Both actions
	 * always fire in pairs (they sit outside the `$should_render` branch), and nested
	 * containers nest the buffers LIFO, so the pairing holds.
	 */
	public function open_layer_buffer( $element ) {
		if ( ! $this->is_grid_enabled( $element ) ) {
			return;
		}

		// Theme-builder parts, popups and loop items render after wp_head, so this is
		// the only point at which their CSS need is known. No-op when already enqueued.
		wp_enqueue_style( 'sa-grid-canvas' );

		ob_start();
	}

	/**
	 * Close the buffer and splice the layer in as the host first child.
	 *
	 * First child, not last: appending after the content would put the layer behind a
	 * `:last-child` boundary that Elementor core CSS uses for widget spacing
	 * (`.elementor-widget:not(:last-child)`), and would depend on the host closing tag
	 * still being the final byte of the buffer — which a third-party `after_render`
	 * hook can break. The first `>` is unambiguously the end of the opening tag:
	 * Elementor escapes every attribute value with esc_attr(), which encodes `>`.
	 */
	public function close_layer_buffer( $element ) {
		if ( ! $this->is_grid_enabled( $element ) ) {
			return;
		}

		$html = ob_get_clean();

		if ( ! is_string( $html ) || '' === $html ) {
			return;
		}

		$tag_end = strpos( $html, '>' );

		if ( false !== $tag_end ) {
			$html = substr( $html, 0, $tag_end + 1 ) . $this->get_layer_html() . substr( $html, $tag_end + 1 );
		}

		// PHPCS - Elementor own already-escaped element markup, plus get_layer_html().
		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Print the page-level grid layer (Page Settings variant).
	 *
	 * Hooked on `wp_body_open` and again on `wp_footer`, so a theme that never calls
	 * `wp_body_open` still gets the layer. Position in the DOM is irrelevant: the host
	 * (`body`) carries `isolation: isolate` from the sa_gc_output control selector, and
	 * the layer pseudo-elements default to `z-index: -1`, so the grid paints behind all
	 * page content either way.
	 *
	 * `min-height: 100vh` because <body> can be shorter than the viewport on sparse
	 * pages, which would clip the grid. Scoped to this element only, so
	 * sections/containers are never affected.
	 */
	public function render_page_layer() {
		if ( $this->page_layer_printed ) {
			return;
		}

		$settings = $this->get_page_settings();
		if ( empty( $settings['sa_gc_enable'] ) || 'yes' !== $settings['sa_gc_enable'] ) {
			return;
		}

		$this->page_layer_printed = true;

		// PHPCS - get_layer_html() escapes its only dynamic part.
		echo $this->get_layer_html( ';min-height:100vh' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Editor-preview-only layer script.
	 *
	 * Elementor re-renders an edited section/container from its JS template, wiping the
	 * server-side layer, so the editor still needs a JS path. It is registered only in
	 * the editor/preview — the frontend ships zero JavaScript for this module, which is
	 * also why nothing here forces jQuery onto public pages (`elementor-frontend`
	 * already depends on it, and it is always present in the preview iframe).
	 *
	 * `sa_gc_enable` is `render_type: 'template'`, and Elementor treats any
	 * template-typed control as a content change (elementor/assets/js/editor.js:33082),
	 * so toggling the switcher runs renderHTML() → onRender() → runReadyTrigger(). The
	 * removal branch below is therefore reachable.
	 */
	private function enqueue_editor_layer_script() {
		if ( ! sky_addons_editor_mode() ) {
			return;
		}

		$js = '
( function ( $ ) {
	"use strict";

	var LAYER = "sa-gc-layer",
		STYLE = "' . self::LAYER_STYLE . '";

	function syncLayer( $scope ) {
		var host = $scope && $scope[0],
			layer = null,
			i;

		if ( ! host || ! host.children ) {
			return;
		}

		for ( i = 0; i < host.children.length; i++ ) {
			if ( host.children[ i ].className === LAYER ) {
				layer = host.children[ i ];
				break;
			}
		}

		if ( ! host.classList.contains( "sa-grid-canvas-yes" ) ) {
			if ( layer ) {
				layer.parentNode.removeChild( layer );
			}
			return;
		}

		if ( layer ) {
			return;
		}

		layer = document.createElement( "div" );
		layer.className = LAYER;
		layer.setAttribute( "aria-hidden", "true" );
		layer.setAttribute( "style", STYLE );
		host.insertBefore( layer, host.firstChild );
	}

	$( window ).on( "elementor/frontend/init", function () {
		if ( "undefined" === typeof elementorFrontend || ! elementorFrontend.hooks ) {
			return;
		}

		elementorFrontend.hooks.addAction( "frontend/element_ready/section", syncLayer );
		elementorFrontend.hooks.addAction( "frontend/element_ready/container", syncLayer );
	} );
}( jQuery ) );
';

		// Inline JS handle — no file, mirroring the inline CSS handle above.
		wp_register_script( 'sa-grid-canvas', false, [ 'elementor-frontend' ], SKY_ADDONS_VERSION, true );
		wp_add_inline_script( 'sa-grid-canvas', $js );
		wp_enqueue_script( 'sa-grid-canvas' );
	}

	/**
	 * Per-page "escape" CSS for the Page Settings grid.
	 *
	 * The page grid lives on `body > .sa-gc-layer` behind content, so it shows
	 * through any transparent section. To exclude a section the user lists a CSS
	 * selector; we give that element an opaque background in the Escape Background
	 * colour. The element sits above the body grid, so its background occludes the
	 * grid within its box. Using background-color (not an overlay + position) keeps
	 * sticky/fixed layouts intact and paints behind any existing background-image.
	 * Frontend only — the selectors are dynamic so this can't use control selectors.
	 */
	private function get_escape_css() {
		$settings = $this->get_page_settings();
		if ( empty( $settings['sa_gc_enable'] ) || 'yes' !== $settings['sa_gc_enable'] || empty( $settings['sa_gc_escape'] ) ) {
			return '';
		}

		$selectors = [];
		foreach ( explode( ',', $settings['sa_gc_escape'] ) as $sel ) {
			$sel = trim( $sel );
			// Whitelist plain CSS-selector characters only — blocks any attempt to
			// break out of the rule (no braces, semicolons, angle brackets except >).
			if ( '' !== $sel && preg_match( '/^[a-zA-Z0-9 .#_\-\[\]=:()"\'>+~*]+$/', $sel ) ) {
				$selectors[] = $sel;
			}
		}

		if ( ! $selectors ) {
			return '';
		}

		return "\n" . implode( ',', $selectors )
			. '{background-color:var(--sa-gc-escape-bg,#fff) !important}';
	}

	protected function add_actions() {
		// section
		add_action( 'elementor/element/section/section_background/after_section_end', [ $this, 'register_section' ] );
		add_action( 'elementor/element/section/section_sky_addons_gc_controls/before_section_end', [ $this, 'register_controls' ], 10, 2 );

		// container
		add_action( 'elementor/element/container/section_background/after_section_end', [ $this, 'register_section' ] );
		add_action( 'elementor/element/container/section_sky_addons_gc_controls/before_section_end', [ $this, 'register_controls' ], 10, 2 );

		// page settings (whole-page grid)
		add_action( 'elementor/documents/register_controls', [ $this, 'register_page_controls' ] );
		add_filter( 'body_class', [ $this, 'add_body_classes' ] );
		add_action( 'wp_body_open', [ $this, 'render_page_layer' ] );
		// Fallback for themes that never call wp_body_open — render_page_layer() is
		// guarded so only the first of the two hooks prints anything.
		add_action( 'wp_footer', [ $this, 'render_page_layer' ], 0 );

		// server-side .sa-gc-layer injection (frontend + first editor paint)
		add_action( 'elementor/frontend/section/before_render', [ $this, 'open_layer_buffer' ] );
		add_action( 'elementor/frontend/section/after_render', [ $this, 'close_layer_buffer' ] );
		add_action( 'elementor/frontend/container/before_render', [ $this, 'open_layer_buffer' ] );
		add_action( 'elementor/frontend/container/after_render', [ $this, 'close_layer_buffer' ] );

		// enqueue keyframes + animation classes
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_styles' ] );
	}
}

<?php

namespace Sky_Addons\Modules\BackdropFilter;

use Elementor\Controls_Manager;
use Sky_Addons\Base\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Module extends Module_Base {

	public function __construct() {
		parent::__construct();
		$this->add_actions();
	}

	public function get_name() {
		return 'sky-backdrop-filter';
	}

	public function register_section( $element ) {

		$tab = Controls_Manager::TAB_ADVANCED;

		if ( 'section' === $element->get_name() || 'column' === $element->get_name() || 'container' === $element->get_name() ) {
			$tab = Controls_Manager::TAB_STYLE;
		}

		$element->start_controls_section(
			'section_sky_addons_bf_controls',
			[
				'tab'   => $tab,
				'label' => esc_html__( 'Backdrop Filter', 'sky-elementor-addons' ) . sky_addons_get_icon(),
			]
		);

		$element->end_controls_section();
	}

	public function register_controls( $element, $args ) {

		$element_name = $element->get_name();

		switch ( $element_name ) {
			case 'section':
				$element_selector = '.sa-backdrop-filter-yes > .elementor-container';
				break;
			case 'column':
				$element_selector = '.sa-backdrop-filter-yes > .elementor-element-populated';
				break;
			case 'container':
				$element_selector = '.sa-backdrop-filter-yes > .e-con-inner, .sa-backdrop-filter-yes > .elementor-element';
				break;
			default:
				// Widget: uses ::after overlay (see sa_bf_output below).
				// $element_selector kept empty; only used by the transition control.
				$element_selector = '';
				break;
		}

		// Full filter chain shared by every output control.
		// drop-shadow defaults to transparent/zero, so it is a no-op when unset.
		$bf_value = 'blur(var(--sky-bf-blur, 0)) brightness(var(--sky-bf-brightness, 100%)) contrast(var(--sky-bf-contrast, 1)) grayscale(var(--sky-bf-grayscale, 0)) invert(var(--sky-bf-invert, 0)) opacity(var(--sky-bf-opacity, 1)) sepia(var(--sky-bf-sepia, 0)) saturate(var(--sky-bf-saturate, 1)) hue-rotate(var(--sky-bf-hue-rotate, 0)) drop-shadow(var(--sky-bf-ds-h, 0px) var(--sky-bf-ds-v, 0px) var(--sky-bf-ds-blur, 0px) var(--sky-bf-ds-color, transparent))';

		$element->add_control(
			'sa_bf_enable',
			[
				'label'        => esc_html__( 'Enable', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'render_type'  => 'template',
				'prefix_class' => 'sa-backdrop-filter-',
				'description'  => esc_html__( 'Backdrop Filter requires a transparent or semi-transparent background on the element sitting above the content you want to blur.', 'sky-elementor-addons' ),
			]
		);

		// ── Normal / Hover tabs ───────────────────────────────────────────────────
		$element->start_controls_tabs(
			'sa_bf_tabs',
			[ 'condition' => [ 'sa_bf_enable' => 'yes' ] ]
		);

		// Normal tab
		$element->start_controls_tab(
			'sa_bf_tab_normal',
			[ 'label' => esc_html__( 'Normal', 'sky-elementor-addons' ) ]
		);

		$element->add_control(
			'sa_bf_blur',
			[
				'label'     => esc_html__( 'Blur', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					// 'px' key kept as-is for backward compatibility — Elementor stores the unit alongside the value
					'px' => [
						'max'  => 200,
						'min'  => 0,
						'step' => .5,
					],
				],
				'condition' => [ 'sa_bf_enable' => 'yes' ],
				'selectors' => [ '{{WRAPPER}}' => '--sky-bf-blur: {{SIZE}}px;' ],
			]
		);

		$element->add_control(
			'sa_bf_brightness',
			[
				'label'     => esc_html__( 'Brightness', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					// 'px' key kept as-is for backward compatibility
					'px' => [
						'max' => 200,
						'min' => 0,
					],
				],
				'condition' => [ 'sa_bf_enable' => 'yes' ],
				'selectors' => [ '{{WRAPPER}}' => '--sky-bf-brightness: {{SIZE}}%;' ],
			]
		);

		$element->add_control(
			'sa_bf_contrast',
			[
				'label'     => esc_html__( 'Contrast', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					// 'px' key kept as-is for backward compatibility
					'px' => [
						'max'  => 2,
						'min'  => 0,
						'step' => 0.1,
					],
				],
				'condition' => [ 'sa_bf_enable' => 'yes' ],
				'selectors' => [ '{{WRAPPER}}' => '--sky-bf-contrast: {{SIZE}};' ],
			]
		);

		$element->add_control(
			'sa_bf_grayscale',
			[
				'label'     => esc_html__( 'Grayscale', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					// 'px' key kept as-is for backward compatibility
					'px' => [
						'max'  => 1,
						'min'  => 0,
						'step' => 0.1,
					],
				],
				'condition' => [ 'sa_bf_enable' => 'yes' ],
				'selectors' => [ '{{WRAPPER}}' => '--sky-bf-grayscale: {{SIZE}};' ],
			]
		);

		$element->add_control(
			'sa_bf_hue_rotate',
			[
				'label'     => esc_html__( 'Hue Rotate', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					// 'px' key kept as-is for backward compatibility
					'px' => [
						'max' => 360,
						'min' => 0,
					],
				],
				'condition' => [ 'sa_bf_enable' => 'yes' ],
				'selectors' => [ '{{WRAPPER}}' => '--sky-bf-hue-rotate: {{SIZE}}deg;' ],
			]
		);

		$element->add_control(
			'sa_bf_invert',
			[
				'label'     => esc_html__( 'Invert', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					// 'px' key kept as-is for backward compatibility
					'px' => [
						'max'  => 1,
						'min'  => 0,
						'step' => 0.1,
					],
				],
				'condition' => [ 'sa_bf_enable' => 'yes' ],
				'selectors' => [ '{{WRAPPER}}' => '--sky-bf-invert: {{SIZE}};' ],
			]
		);

		$element->add_control(
			'sa_bf_opacity',
			[
				'label'     => esc_html__( 'Opacity', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					// 'px' key kept as-is for backward compatibility
					'px' => [
						'max'  => 1,
						'min'  => 0,
						'step' => 0.1,
					],
				],
				'condition' => [ 'sa_bf_enable' => 'yes' ],
				'selectors' => [ '{{WRAPPER}}' => '--sky-bf-opacity: {{SIZE}};' ],
			]
		);

		$element->add_control(
			'sa_bf_sepia',
			[
				'label'     => esc_html__( 'Sepia', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					// 'px' key kept as-is for backward compatibility
					'px' => [
						'max'  => 1,
						'min'  => 0,
						'step' => 0.1,
					],
				],
				'condition' => [ 'sa_bf_enable' => 'yes' ],
				'selectors' => [ '{{WRAPPER}}' => '--sky-bf-sepia: {{SIZE}};' ],
			]
		);

		$element->add_control(
			'sa_bf_saturate',
			[
				'label'     => esc_html__( 'Saturate', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					// 'px' key kept as-is for backward compatibility
					'px' => [
						'max'  => 2,
						'min'  => 0,
						'step' => 0.1,
					],
				],
				'condition' => [ 'sa_bf_enable' => 'yes' ],
				'selectors' => [ '{{WRAPPER}}' => '--sky-bf-saturate: {{SIZE}};' ],
			]
		);

		// Drop Shadow sub-group
		$element->add_control(
			'sa_bf_ds_heading',
			[
				'label'     => esc_html__( 'Drop Shadow', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [ 'sa_bf_enable' => 'yes' ],
			]
		);

		$element->add_control(
			'sa_bf_dropshadow_h',
			[
				'label'     => esc_html__( 'Horizontal', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min'  => -100,
						'max'  => 100,
						'step' => 1,
					],
				],
				'condition' => [ 'sa_bf_enable' => 'yes' ],
				'selectors' => [ '{{WRAPPER}}' => '--sky-bf-ds-h: {{SIZE}}px;' ],
			]
		);

		$element->add_control(
			'sa_bf_dropshadow_v',
			[
				'label'     => esc_html__( 'Vertical', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min'  => -100,
						'max'  => 100,
						'step' => 1,
					],
				],
				'condition' => [ 'sa_bf_enable' => 'yes' ],
				'selectors' => [ '{{WRAPPER}}' => '--sky-bf-ds-v: {{SIZE}}px;' ],
			]
		);

		$element->add_control(
			'sa_bf_dropshadow_blur',
			[
				'label'     => esc_html__( 'Blur', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					],
				],
				'condition' => [ 'sa_bf_enable' => 'yes' ],
				'selectors' => [ '{{WRAPPER}}' => '--sky-bf-ds-blur: {{SIZE}}px;' ],
			]
		);

		$element->add_control(
			'sa_bf_dropshadow_color',
			[
				'label'     => esc_html__( 'Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'condition' => [ 'sa_bf_enable' => 'yes' ],
				'selectors' => [ '{{WRAPPER}}' => '--sky-bf-ds-color: {{VALUE}};' ],
			]
		);

		$element->end_controls_tab();

		// Hover tab — sliders override the same CSS variables on {{WRAPPER}}:hover,
		// so ::after and inner-target selectors pick up the change automatically.
		$element->start_controls_tab(
			'sa_bf_tab_hover',
			[ 'label' => esc_html__( 'Hover', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ) ]
		);

		$element->add_control(
			'sa_bf_blur_h',
			[
				'label'     => esc_html__( 'Blur', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'max'  => 200,
						'min'  => 0,
						'step' => .5,
					],
				],
				'condition' => [ 'sa_bf_enable' => 'yes' ],
				'selectors' => [ '{{WRAPPER}}:hover' => '--sky-bf-blur: {{SIZE}}px;' ],
			]
		);

		$element->add_control(
			'sa_bf_brightness_h',
			[
				'label'     => esc_html__( 'Brightness', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'max' => 200,
						'min' => 0,
					],
				],
				'condition' => [ 'sa_bf_enable' => 'yes' ],
				'selectors' => [ '{{WRAPPER}}:hover' => '--sky-bf-brightness: {{SIZE}}%;' ],
			]
		);

		$element->add_control(
			'sa_bf_contrast_h',
			[
				'label'     => esc_html__( 'Contrast', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'max'  => 2,
						'min'  => 0,
						'step' => 0.1,
					],
				],
				'condition' => [ 'sa_bf_enable' => 'yes' ],
				'selectors' => [ '{{WRAPPER}}:hover' => '--sky-bf-contrast: {{SIZE}};' ],
			]
		);

		$element->add_control(
			'sa_bf_grayscale_h',
			[
				'label'     => esc_html__( 'Grayscale', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'max'  => 1,
						'min'  => 0,
						'step' => 0.1,
					],
				],
				'condition' => [ 'sa_bf_enable' => 'yes' ],
				'selectors' => [ '{{WRAPPER}}:hover' => '--sky-bf-grayscale: {{SIZE}};' ],
			]
		);

		$element->add_control(
			'sa_bf_hue_rotate_h',
			[
				'label'     => esc_html__( 'Hue Rotate', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'max' => 360,
						'min' => 0,
					],
				],
				'condition' => [ 'sa_bf_enable' => 'yes' ],
				'selectors' => [ '{{WRAPPER}}:hover' => '--sky-bf-hue-rotate: {{SIZE}}deg;' ],
			]
		);

		$element->add_control(
			'sa_bf_invert_h',
			[
				'label'     => esc_html__( 'Invert', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'max'  => 1,
						'min'  => 0,
						'step' => 0.1,
					],
				],
				'condition' => [ 'sa_bf_enable' => 'yes' ],
				'selectors' => [ '{{WRAPPER}}:hover' => '--sky-bf-invert: {{SIZE}};' ],
			]
		);

		$element->add_control(
			'sa_bf_opacity_h',
			[
				'label'     => esc_html__( 'Opacity', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'max'  => 1,
						'min'  => 0,
						'step' => 0.1,
					],
				],
				'condition' => [ 'sa_bf_enable' => 'yes' ],
				'selectors' => [ '{{WRAPPER}}:hover' => '--sky-bf-opacity: {{SIZE}};' ],
			]
		);

		$element->add_control(
			'sa_bf_sepia_h',
			[
				'label'     => esc_html__( 'Sepia', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'max'  => 1,
						'min'  => 0,
						'step' => 0.1,
					],
				],
				'condition' => [ 'sa_bf_enable' => 'yes' ],
				'selectors' => [ '{{WRAPPER}}:hover' => '--sky-bf-sepia: {{SIZE}};' ],
			]
		);

		$element->add_control(
			'sa_bf_saturate_h',
			[
				'label'     => esc_html__( 'Saturate', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'max'  => 2,
						'min'  => 0,
						'step' => 0.1,
					],
				],
				'condition' => [ 'sa_bf_enable' => 'yes' ],
				'selectors' => [ '{{WRAPPER}}:hover' => '--sky-bf-saturate: {{SIZE}};' ],
			]
		);

		$element->end_controls_tab();
		$element->end_controls_tabs();

		// ── Transition ────────────────────────────────────────────────────────────
		// Targets both the inner element (default mode) and ::after (bg_overlay mode).
		$element->add_control(
			'sa_bf_transition',
			[
				'label'     => esc_html__( 'Transition Duration (s)', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					// 'px' key kept as-is for backward compatibility
					'px' => [
						'max'  => 3,
						'min'  => 0,
						'step' => 0.1,
					],
				],
				'separator' => 'before',
				'condition' => [ 'sa_bf_enable' => 'yes' ],
				'selectors' => [
					'{{WRAPPER}}' . $element_selector => 'transition: backdrop-filter {{SIZE}}s ease, -webkit-backdrop-filter {{SIZE}}s ease;',
					'{{WRAPPER}}.sa-backdrop-filter-yes::after' => 'transition: backdrop-filter {{SIZE}}s ease, -webkit-backdrop-filter {{SIZE}}s ease;',
				],
			]
		);

		// ── Output: widget + column default mode ──────────────────────────────────
		if ( 'section' !== $element_name && 'container' !== $element_name ) {

			if ( 'column' === $element_name ) {
				// Column default mode — apply filter to the inner populated wrapper.
				// 'sa_bf_selector!' prevents this from firing when bg_overlay is active.
				$element->add_control(
					'sa_bf_output',
					[
						'type'        => Controls_Manager::HIDDEN,
						'default'     => '1',
						'selectors'   => [
							'{{WRAPPER}}' . $element_selector => '-webkit-backdrop-filter: ' . $bf_value . ';
      backdrop-filter: ' . $bf_value . ';',
						],
						'render_type' => 'template',
						'condition'   => [
							'sa_bf_enable'    => 'yes',
							'sa_bf_selector!' => 'bg_overlay',
						],
					]
				);
			} else {
				// Widget: use ::after overlay so backdrop-filter works even when the widget
				// has its own background image. The ::after sits above the background,
				// so backdrop-filter composites through it. > * lifts real content above
				// the overlay — no dependency on .elementor-widget-container.
				$element->add_control(
					'sa_bf_output',
					[
						'type'        => Controls_Manager::HIDDEN,
						'default'     => '1',
						'selectors'   => [
							'{{WRAPPER}}.sa-backdrop-filter-yes'        => 'position: relative;',
							'{{WRAPPER}}.sa-backdrop-filter-yes::after' => 'content: ""; position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 0; pointer-events: none; border-radius: inherit; -webkit-backdrop-filter: ' . $bf_value . '; backdrop-filter: ' . $bf_value . ';',
							'{{WRAPPER}}.sa-backdrop-filter-yes > *'    => 'position: relative; z-index: 1;',
						],
						'render_type' => 'template',
						'condition'   => [ 'sa_bf_enable' => 'yes' ],
					]
				);

				// The ::after overlay can only inherit the WRAPPER's border-radius, which is
				// usually 0 — a widget's visible radius typically lives on an inner element
				// (e.g. the Audio Player card). This lets the user round the overlay to match.
				// Registered after sa_bf_output so its identical-specificity rule wins.
				$element->add_responsive_control(
					'sa_bf_border_radius',
					[
						'label'       => esc_html__( 'Border Radius', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.0.0' ),
						'type'        => Controls_Manager::DIMENSIONS,
						'size_units'  => [ 'px', '%', 'em' ],
						'description' => esc_html__( 'Match the visible radius of the widget so the filter overlay follows its corners.', 'sky-elementor-addons' ),
						'condition'   => [ 'sa_bf_enable' => 'yes' ],
						'selectors'   => [
							'{{WRAPPER}}.sa-backdrop-filter-yes::after' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);
			}
		}

		// ── Creative Selector block: section + column + container ─────────────────
		if ( in_array( $element_name, [ 'section', 'column', 'container' ], true ) ) {

			$element->add_control(
				'sa_bf_selector',
				[
					'label'       => esc_html__( 'Creative Selector', 'sky-elementor-addons' ),
					'type'        => Controls_Manager::SELECT,
					'default'     => 'default',
					'options'     => [
						'default'    => esc_html__( 'Default', 'sky-elementor-addons' ),
						'bg_overlay' => esc_html__( 'Background Overlay', 'sky-elementor-addons' ),
					],
					'separator'   => 'before',
					'render_type' => 'template',
					'condition'   => [ 'sa_bf_enable' => 'yes' ],
				]
			);

			$element->add_control(
				'sa_bf_selector_note',
				[
					'type'            => Controls_Manager::RAW_HTML,
					'raw'             => esc_html__( 'The filter is applied automatically via an overlay layer. Make sure the element has a background image, gradient, or colour set for the effect to be visible.', 'sky-elementor-addons' ),
					'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
					'condition'       => [
						'sa_bf_enable'   => 'yes',
						'sa_bf_selector' => 'bg_overlay',
					],
				]
			);

			// Default mode output — section and container only.
			// Column in default mode is handled by sa_bf_output above to preserve backward compat.
			// border-radius: inherit propagates the wrapper's border-radius to the inner target
			// so the backdrop-filter area is clipped to match the element's rounded corners.
			if ( 'column' !== $element_name ) {
				if ( 'section' === $element_name ) {
					$output_1_selectors = [
						'{{WRAPPER}}.sa-backdrop-filter-yes > .elementor-container' => 'border-radius: inherit; -webkit-backdrop-filter: ' . $bf_value . '; backdrop-filter: ' . $bf_value . ';',
					];
				} else {
					// container
					$output_1_selectors = [
						'{{WRAPPER}}.sa-backdrop-filter-yes > .e-con-inner'        => 'border-radius: inherit; -webkit-backdrop-filter: ' . $bf_value . '; backdrop-filter: ' . $bf_value . ';',
						'{{WRAPPER}}.sa-backdrop-filter-yes > .elementor-element'  => 'border-radius: inherit; -webkit-backdrop-filter: ' . $bf_value . '; backdrop-filter: ' . $bf_value . ';',
					];
				}

				$element->add_control(
					'sa_bf_output_1',
					[
						'type'        => Controls_Manager::HIDDEN,
						'default'     => '1',
						'selectors'   => $output_1_selectors,
						'render_type' => 'template',
						'condition'   => [
							'sa_bf_enable'   => 'yes',
							'sa_bf_selector' => 'default',
						],
					]
				);
			}

			// Background Overlay mode: auto ::after for section + column; .elementor-background-overlay for container.
			if ( 'section' === $element_name ) {
				$output_2_selectors = [
					'{{WRAPPER}}.sa-backdrop-filter-yes > .elementor-container' => 'position: relative; z-index: 1;',
					'{{WRAPPER}}.sa-backdrop-filter-yes::after'                  => 'content: ""; position: absolute; top: 0; left: 0; height: 100%; width: 100%; z-index: 0; pointer-events: none; border-radius: inherit; -webkit-backdrop-filter: ' . $bf_value . ' !important;
              backdrop-filter: ' . $bf_value . ' !important;',
				];
			} elseif ( 'column' === $element_name ) {
				$output_2_selectors = [
					'{{WRAPPER}}.sa-backdrop-filter-yes > .elementor-element-populated' => 'position: relative; z-index: 1;',
					'{{WRAPPER}}.sa-backdrop-filter-yes::after'                          => 'content: ""; position: absolute; top: 0; left: 0; height: 100%; width: 100%; z-index: 0; pointer-events: none; border-radius: inherit; -webkit-backdrop-filter: ' . $bf_value . ' !important;
              backdrop-filter: ' . $bf_value . ' !important;',
				];
			} else {
				// container — sa_bf_output_3 handles ::after; this keeps .elementor-background-overlay for legacy use
				$output_2_selectors = [
					'{{WRAPPER}}.sa-backdrop-filter-yes > .elementor-background-overlay' => '-webkit-backdrop-filter: ' . $bf_value . ' !important;
              backdrop-filter: ' . $bf_value . ' !important;',
				];
			}

			$element->add_control(
				'sa_bf_output_2',
				[
					'type'        => Controls_Manager::HIDDEN,
					'default'     => '1',
					'selectors'   => $output_2_selectors,
					'render_type' => 'template',
					'condition'   => [
						'sa_bf_enable'   => 'yes',
						'sa_bf_selector' => 'bg_overlay',
					],
				]
			);

			if ( 'container' === $element_name ) {
				$element->add_control(
					'sa_bf_output_3',
					[
						'type'        => Controls_Manager::HIDDEN,
						'default'     => '1',
						'selectors'   => [
							'{{WRAPPER}}.sa-backdrop-filter-yes > .e-con-inner' => 'z-index: 1;',
							'{{WRAPPER}}.sa-backdrop-filter-yes > .e-con'       => 'z-index: 1;',
							'{{WRAPPER}}.sa-backdrop-filter-yes::after'         => 'content: ""; position: absolute; top: 0; left: 0; height: 100%; width: 100%; z-index: 0; pointer-events: none; border-radius: inherit; -webkit-backdrop-filter: ' . $bf_value . ' !important;
              backdrop-filter: ' . $bf_value . ' !important;',
						],
						'render_type' => 'template',
						'condition'   => [
							'sa_bf_enable'   => 'yes',
							'sa_bf_selector' => 'bg_overlay',
						],
					]
				);
			}
		}
	}

	protected function add_actions() {

		// section
		add_action( 'elementor/element/section/section_background/after_section_end', [ $this, 'register_section' ] );
		add_action( 'elementor/element/section/section_sky_addons_bf_controls/before_section_end', [ $this, 'register_controls' ], 10, 2 );

		// container
		add_action( 'elementor/element/container/section_background/after_section_end', [ $this, 'register_section' ] );
		add_action( 'elementor/element/container/section_sky_addons_bf_controls/before_section_end', [ $this, 'register_controls' ], 10, 2 );

		// column
		add_action( 'elementor/element/column/section_style/after_section_end', [ $this, 'register_section' ] );
		add_action( 'elementor/element/column/section_sky_addons_bf_controls/before_section_end', [ $this, 'register_controls' ], 10, 2 );

		// widget
		add_action( 'elementor/element/common/_section_background/after_section_end', [ $this, 'register_section' ] );
		add_action( 'elementor/element/common/section_sky_addons_bf_controls/before_section_end', [ $this, 'register_controls' ], 10, 2 );
	}
}

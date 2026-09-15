<?php

namespace Sky_Addons\Modules\AnimatedHeading\Widgets;

use Elementor\Utils;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Text_Stroke;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Animated_Heading extends Widget_Base {

	public function get_name() {
		return 'sky-animated-heading';
	}

	public function get_title() {
		return esc_html__( 'Animated Heading', 'sky-elementor-addons' );
	}

	public function get_icon() {
		return 'sky-icon-animated-heading';
	}

	public function get_categories() {
		return [ 'sky-elementor-addons' ];
	}

	public function get_keywords() {
		return [ 'sky', 'animated', 'heading' ];
	}
	public function get_style_depends() {
		if ( sky_addons_editor_mode() ) {
			return [ 'sky-addons-styles' ];
		}

		return [ 'sa-animated-heading' ];
	}


	public function get_script_depends() {
		if ( sky_addons_editor_mode() ) {
			return [ 'typed', 'morphext', 'sky-addons-scripts' ];
		}

		return [ 'typed', 'morphext', 'sa-animated-heading' ];
	}

	public function has_widget_inner_wrapper(): bool {
		return ! \Elementor\Plugin::$instance->experiments->is_feature_active( 'e_optimized_markup' );
	}

	protected function register_controls() {

		$this->start_controls_section(
			'section_title',
			[
				'label' => esc_html__( 'Title', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'animate_style',
			[
				'label'   => esc_html__( 'Select Animate', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'animated',
				'options' => [
					'animated'    => esc_html__( 'Animated', 'sky-elementor-addons' ),
					'typed'       => esc_html__( 'Typed', 'sky-elementor-addons' ),
					'highlight'   => esc_html__( 'Highlight', 'sky-elementor-addons' ),
					'glitch'      => esc_html__( 'Glitch', 'sky-elementor-addons' ),
					'reveal'      => esc_html__( 'Reveal', 'sky-elementor-addons' ),
					'word-rotate' => esc_html__( 'Word Rotate', 'sky-elementor-addons' ),
					'split-chars' => esc_html__( 'Split Chars', 'sky-elementor-addons' ),
					'gravity'     => esc_html__( 'Gravity', 'sky-elementor-addons' ),
					'flip-chars'  => esc_html__( 'Flip Chars', 'sky-elementor-addons' ),
					'vortex'      => esc_html__( 'Vortex', 'sky-elementor-addons' ),
					'wave-in'     => esc_html__( 'Wave In', 'sky-elementor-addons' ),
				],
			]
		);

		$this->add_control(
			'title_prefix',
			[
				'label'   => esc_html__( 'Title Prefix', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::TEXTAREA,
				'dynamic' => [ 'active' => true ],
				'rows'    => 5,
				'default' => 'Hello I am',
			]
		);

		$this->add_control(
			'title',
			[
				'label'       => esc_html__( 'Title', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXTAREA,
				'dynamic'     => [
					'active' => true,
				],
				'placeholder' => esc_html__( 'Enter your title', 'sky-elementor-addons' ),
				'default'     => esc_html__( 'Animated,Morphing,Awesome', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'title_suffix',
			[
				'label'   => esc_html__( 'Title Suffix', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::TEXTAREA,
				'dynamic' => [ 'active' => true ],
				'rows'    => 5,
				'default' => 'Heading',
			]
		);

		$this->add_control(
			'link',
			[
				'label'     => esc_html__( 'Link', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::URL,
				'dynamic'   => [
					'active' => true,
				],
				'default'   => [
					'url' => '',
				],
				'separator' => 'before',
			]
		);

		$this->add_control(
			'header_size',
			[
				'label'   => esc_html__( 'HTML Tag', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'h1'   => 'H1',
					'h2'   => 'H2',
					'h3'   => 'H3',
					'h4'   => 'H4',
					'h5'   => 'H5',
					'h6'   => 'H6',
					'div'  => 'div',
					'span' => 'span',
					'p'    => 'p',
				],
				'default' => 'h2',
			]
		);

		$this->add_responsive_control(
			'align',
			[
				'label'   => esc_html__( 'Alignment', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::CHOOSE,
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
				'default' => '',
				'selectors' => [
					'{{WRAPPER}}' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_typed_settings',
			[
				'label' => esc_html__( 'Animation Settings', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
				'condition' => [
					'animate_style' => 'typed',
				],
			]
		);

		$this->add_control(
			'typed_speed',
			[
				'label' => esc_html__( 'Speed', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::SLIDER,
				'default' => [
					'size' => 60,
				],
				'range' => [
					'px' => [
						'min' => 30,
						'max' => 100,
					],
				],
			]
		);

		$this->add_control(
			'typed_loop',
			[
				'label'        => esc_html__( 'Loop', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'typed_loop_count',
			[
				'label'   => esc_html__( 'Loop Count', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 0,
				'condition' => [
					'typed_loop' => 'yes',
				],
			]
		);

		$this->add_control(
			'typed_start_delay',
			[
				'label' => esc_html__( 'Start Delay', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::SLIDER,
				'default' => [
					'size' => 1000,
				],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 5000,
					],
				],
			]
		);

		$this->add_control(
			'typed_back_speed',
			[
				'label' => esc_html__( 'Back Speed', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::SLIDER,
				'default' => [
					'size' => 30,
				],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 1000,
					],
				],
			]
		);

		$this->add_control(
			'typed_back_delay',
			[
				'label' => esc_html__( 'Back Delay', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::SLIDER,
				'default' => [
					'size' => 500,
				],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 5000,
					],
				],
			]
		);

		$this->add_control(
			'typed_show_cursor',
			[
				'label'        => esc_html__( 'Show Cursor', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
				'separator'    => 'before',
			]
		);

		$this->add_control(
			'typed_cursor_char',
			[
				'label'   => esc_html__( 'Cursor Character', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'    => Controls_Manager::TEXT,
				'default' => '|',
				'condition' => [
					'typed_show_cursor' => 'yes',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_animated_settings',
			[
				'label' => esc_html__( 'Animation Settings', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
				'condition' => [
					'animate_style' => 'animated',
				],
			]
		);

		$this->add_control(
			'heading_animation',
			[
				'label'       => esc_html__( 'Animation', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::ANIMATION,
				'default'     => 'fadeIn',
				'label_block' => true,
				'render_type' => 'template',
			]
		);

		$this->add_control(
			'animated_speed',
			[
				'label' => esc_html__( 'Speed', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::SLIDER,
				'default' => [
					'size' => 2000,
				],
				'range' => [
					'px' => [
						'min' => 500,
						'max' => 5000,
					],
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_custom_settings',
			[
				'label' => esc_html__( 'Animation Settings', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
				'condition' => [
					'animate_style' => [ 'highlight', 'glitch', 'reveal', 'word-rotate', 'split-chars', 'gravity', 'flip-chars', 'vortex', 'wave-in' ],
				],
			]
		);

		$this->add_control(
			'word_interval',
			[
				'label' => esc_html__( 'Word Interval (ms)', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'  => Controls_Manager::SLIDER,
				'default' => [
					'size' => 2000,
				],
				'range' => [
					'px' => [
						'min'  => 500,
						'max'  => 10000,
						'step' => 100,
					],
				],
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

		$this->add_control(
			'title_color',
			[
				'label' => esc_html__( 'Text Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-animated-heading' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'typography',
				'selector' => '{{WRAPPER}} .sa-animated-heading',
			]
		);

		$this->add_group_control(
			Group_Control_Text_Stroke::get_type(),
			[
				'name'     => 'text_stroke',
				'selector' => '{{WRAPPER}} .sa-animated-heading',
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'text_shadow',
				'selector' => '{{WRAPPER}} .sa-animated-heading',
			]
		);

		$this->add_control(
			'blend_mode',
			[
				'label'     => esc_html__( 'Blend Mode', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					''            => esc_html__( 'Normal', 'sky-elementor-addons' ),
					'multiply'    => 'Multiply',
					'screen'      => 'Screen',
					'overlay'     => 'Overlay',
					'darken'      => 'Darken',
					'lighten'     => 'Lighten',
					'color-dodge' => 'Color Dodge',
					'saturation'  => 'Saturation',
					'color'       => 'Color',
					'difference'  => 'Difference',
					'exclusion'   => 'Exclusion',
					'hue'         => 'Hue',
					'luminosity'  => 'Luminosity',
				],
				'selectors' => [
					'{{WRAPPER}} .sa-animated-heading' => 'mix-blend-mode: {{VALUE}}',
				],
				'separator' => 'none',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_highlight_style',
			[
				'label' => esc_html__( 'Highlight', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'tab'   => Controls_Manager::TAB_STYLE,
				'condition' => [
					'animate_style' => 'highlight',
				],
			]
		);

		$this->add_control(
			'highlight_bg_color',
			[
				'label' => esc_html__( 'Background Color', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-highlight-word::before' => 'background: {{VALUE}}; opacity: 1;',
				],
			]
		);

		$this->add_responsive_control(
			'highlight_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-highlight-word::before' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_prefix_title_style',
			[
				'label' => esc_html__( 'Prefix', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
				'condition' => [
					'title_prefix!' => '',
				],
			]
		);

		$this->add_control(
			'prefix_display',
			[
				'label'   => esc_html__( 'Display', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					''             => esc_html__( 'Default', 'sky-elementor-addons' ),
					'inline-block' => esc_html__( 'Inline Block', 'sky-elementor-addons' ),
					'block'        => esc_html__( 'Block', 'sky-elementor-addons' ),
					'inline'       => esc_html__( 'Inline', 'sky-elementor-addons' ),
				],
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .sa-prefix' => 'display: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'prefix_title_color',
			[
				'label' => esc_html__( 'Text Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-prefix' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'prefix_typography',
				'selector' => '{{WRAPPER}} .sa-prefix',
			]
		);

		$this->add_group_control(
			Group_Control_Text_Stroke::get_type(),
			[
				'name'     => 'prefix_text_stroke',
				'selector' => '{{WRAPPER}} .sa-prefix',
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'prefix_text_shadow',
				'selector' => '{{WRAPPER}} .sa-prefix',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_suffix_title_style',
			[
				'label' => esc_html__( 'Suffix', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
				'condition' => [
					'title_suffix!' => '',
				],
			]
		);

		$this->add_control(
			'suffix_display',
			[
				'label'   => esc_html__( 'Display', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					''             => esc_html__( 'Default', 'sky-elementor-addons' ),
					'inline-block' => esc_html__( 'Inline Block', 'sky-elementor-addons' ),
					'block'        => esc_html__( 'Block', 'sky-elementor-addons' ),
					'inline'       => esc_html__( 'Inline', 'sky-elementor-addons' ),
				],
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .sa-suffix' => 'display: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'suffix_title_color',
			[
				'label' => esc_html__( 'Text Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-suffix' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'suffix_typography',
				'selector' => '{{WRAPPER}} .sa-suffix',
			]
		);

		$this->add_group_control(
			Group_Control_Text_Stroke::get_type(),
			[
				'name'     => 'suffix_text_stroke',
				'selector' => '{{WRAPPER}} .sa-suffix',
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'suffix_text_shadow',
				'selector' => '{{WRAPPER}} .sa-suffix',
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$id       = 'sa-animated-heading-' . $this->get_id();

		if ( empty( $settings['title'] ) && empty( $settings['title_prefix'] ) && empty( $settings['title_suffix'] ) ) {
			return;
		}

		$main_title    = explode( ',', esc_html( $settings['title'] ) );
		$custom_styles = [ 'highlight', 'glitch', 'reveal', 'word-rotate', 'split-chars', 'gravity', 'flip-chars', 'vortex', 'wave-in' ];

		$this->add_render_attribute( 'title', [
			'class'      => 'sa-animated-heading sa-mb-0',
			'data-id'    => $id,
			'data-style' => $settings['animate_style'],
		] );

		if ( 'typed' === $settings['animate_style'] ) {
			$show_cursor = ( 'yes' === $settings['typed_show_cursor'] );
			$this->add_render_attribute( 'title', 'data-settings', wp_json_encode( [
				'style'      => $settings['animate_style'],
				'strings'    => $main_title,
				'typeSpeed'  => ! empty( $settings['typed_speed']['size'] ) ? (int) $settings['typed_speed']['size'] : 60,
				'loop'       => ( 'yes' === $settings['typed_loop'] ),
				'loopCount'  => ( 'yes' === $settings['typed_loop'] && isset( $settings['typed_loop_count'] ) ) ? (int) $settings['typed_loop_count'] : 0,
				'startDelay' => ! empty( $settings['typed_start_delay']['size'] ) ? (int) $settings['typed_start_delay']['size'] : 0,
				'backSpeed'  => ! empty( $settings['typed_back_speed']['size'] ) ? (int) $settings['typed_back_speed']['size'] : 60,
				'backDelay'  => ! empty( $settings['typed_back_delay']['size'] ) ? (int) $settings['typed_back_delay']['size'] : 700,
				'showCursor' => $show_cursor,
				'cursorChar' => ( $show_cursor && ! empty( $settings['typed_cursor_char'] ) ) ? esc_html( $settings['typed_cursor_char'] ) : '|',
			], JSON_HEX_TAG | JSON_HEX_AMP ) );
		} elseif ( in_array( $settings['animate_style'], $custom_styles, true ) ) {
			$this->add_render_attribute( 'title', 'data-settings', wp_json_encode( [
				'style'    => $settings['animate_style'],
				'strings'  => $main_title,
				'interval' => ! empty( $settings['word_interval']['size'] ) ? (int) $settings['word_interval']['size'] : 2000,
			], JSON_HEX_TAG | JSON_HEX_AMP ) );
		} else {
			$this->add_render_attribute( 'title', 'data-settings', wp_json_encode( [
				'style'     => $settings['animate_style'],
				'animation' => sanitize_html_class( $settings['heading_animation'] ),
				'separator' => ',',
				'speed'     => ! empty( $settings['animated_speed']['size'] ) ? (int) $settings['animated_speed']['size'] : 2000,
			] ) );
		}

		$title_html_parts = [];

		if ( ! empty( $settings['title_prefix'] ) ) {
			$title_html_parts[] = '<div class="sa-prefix">' . esc_html( $settings['title_prefix'] ) . '</div>';
		}

		if ( 'typed' === $settings['animate_style'] || in_array( $settings['animate_style'], $custom_styles, true ) ) {
			$main_title_html = '<div class="sa-main-heading" id="' . esc_attr( $id ) . '"></div>';
		} elseif ( ! empty( $settings['title'] ) ) {
			$main_title_html = '<div class="sa-main-heading" id="' . esc_attr( $id ) . '">' . esc_html( $settings['title'] ) . '</div>';
		} else {
			$main_title_html = '';
		}

		if ( $main_title_html ) {
			$title_html_parts[] = $main_title_html;
		}

		if ( ! empty( $settings['title_suffix'] ) ) {
			$title_html_parts[] = '<div class="sa-suffix">' . esc_html( $settings['title_suffix'] ) . '</div>';
		}

		$title_html = implode( ' ', $title_html_parts );

		if ( ! empty( $settings['link']['url'] ) ) {
			$this->add_link_attributes( 'url', $settings['link'] );
			$title_html = sprintf( '<a %1$s>%2$s</a>', $this->get_render_attribute_string( 'url' ), $title_html );
		}

		$output = sprintf(
			'<%1$s %2$s>%3$s</%1$s>',
			Utils::validate_html_tag( $settings['header_size'] ),
			$this->get_render_attribute_string( 'title' ),
			$title_html
		);

		echo wp_kses_post( $output );
	}
}

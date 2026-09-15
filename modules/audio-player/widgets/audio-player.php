<?php

namespace Sky_Addons\Modules\AudioPlayer\Widgets;

use Elementor\Utils;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Widget_Base;

use Elementor\Modules\DynamicTags\Module as TagsModule;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Audio_Player extends Widget_Base {

	public function get_name() {
		return 'sky-audio-player';
	}

	public function get_title() {
		return esc_html__( 'Audio Player', 'sky-elementor-addons' );
	}

	public function get_icon() {
		return 'sky-icon-audio-player';
	}

	public function get_categories() {
		return [ 'sky-elementor-addons' ];
	}

	public function get_keywords() {
		return [ 'sky', 'audio', 'player', 'music', 'sound', 'song', 'track', 'podcast' ];
	}

	public function get_script_depends() {
		if ( sky_addons_editor_mode() ) {
			return [ 'plyr', 'sky-addons-scripts' ];
		}

		return [ 'plyr', 'sa-audio-player' ];
	}

	public function get_style_depends() {
		if ( sky_addons_editor_mode() ) {
			return [ 'sky-addons-styles' ];
		}

		return [ 'sa-audio-player' ];
	}

	public function has_widget_inner_wrapper(): bool {
		return ! \Elementor\Plugin::$instance->experiments->is_feature_active( 'e_optimized_markup' );
	}

	protected function register_controls() {

		// ── Layout ──────────────────────────────────────────────────────────────

		$this->start_controls_section(
			'section_layout',
			[
				'label' => esc_html__( 'Layout', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'player_style',
			[
				'label'   => esc_html__( 'Player Style', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'modern',
				'options' => [
					'modern'  => esc_html__( 'Modern', 'sky-elementor-addons' ),
					'nextgen' => esc_html__( 'NextGen', 'sky-elementor-addons' ),
					'card'    => esc_html__( 'Card', 'sky-elementor-addons' ),
					'vinyl'   => esc_html__( 'Vinyl', 'sky-elementor-addons' ),
				],
			]
		);

		$this->add_control(
			'vinyl_image_rotate',
			[
				'label'        => esc_html__( 'Rotate Image on Play', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
				'prefix_class' => 'sa-vinyl-rotate-',
				'condition'    => [ 'player_style' => 'vinyl' ],
			]
		);

		$this->add_control(
			'show_volume',
			[
				'label'        => esc_html__( 'Show Volume', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'hide_volume_mobile',
			[
				'label'        => esc_html__( 'Hide Volume on Mobile', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.0.0' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'none',
				'selectors'    => [
					'(mobile){{WRAPPER}} .sa-audio-volume' => 'display: {{VALUE}};',
				],
				'condition'    => [ 'show_volume' => 'yes' ],
			]
		);

		$this->add_control(
			'show_time',
			[
				'label'        => esc_html__( 'Show Time', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'show_wave',
			[
				'label'        => esc_html__( 'Show Wave', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
			]
		);

		$this->end_controls_section();

		// ── Content: Audio Source ──────────────────────────────────────────────

		$this->start_controls_section(
			'section_audio_source',
			[
				'label' => esc_html__( 'Audio Source', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'source_type',
			[
				'label'   => esc_html__( 'Source', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'hosted_url',
				'options' => [
					'hosted_url' => esc_html__( 'Local Audio', 'sky-elementor-addons' ),
					'remote_url' => esc_html__( 'Remote URL', 'sky-elementor-addons' ),
				],
			]
		);

		$this->add_control(
			'hosted_url',
			[
				'label'      => esc_html__( 'Local Audio', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::MEDIA,
				'dynamic'    => [
					'active' => true,
					'categories' => [
						TagsModule::POST_META_CATEGORY,
						TagsModule::MEDIA_CATEGORY,
					],
				],
				'media_type' => 'audio',
				'default'    => [
					'url' => SKY_ADDONS_ASSETS_URL . 'others/sample-music.mp3',
				],
				'condition'  => [ 'source_type' => 'hosted_url' ],
			]
		);

		$this->add_control(
			'remote_url',
			[
				'label'         => esc_html__( 'Remote URL', 'sky-elementor-addons' ),
				'description'   => __( 'Supports MP3, OGG, WAV, WebM, and M4A.', 'sky-elementor-addons' ),
				'type'          => Controls_Manager::URL,
				'show_external' => false,
				'default'       => [ 'url' => SKY_ADDONS_ASSETS_URL . 'others/sample-music.mp3' ],
				'placeholder'   => 'https://example.com/music.mp3',
				'dynamic'       => [
					'active' => true,
					'categories' => [
						TagsModule::POST_META_CATEGORY,
						TagsModule::URL_CATEGORY,
					],
				],
				'condition'     => [ 'source_type' => 'remote_url' ],
			]
		);

		$this->add_control(
			'autoplay',
			[
				'label'        => esc_html__( 'Autoplay', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
				'separator'    => 'before',
			]
		);

		$this->add_control(
			'loop',
			[
				'label'        => esc_html__( 'Loop', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
			]
		);

		$this->end_controls_section();

		// ── Content: Track Info ────────────────────────────────────────────────

		$this->start_controls_section(
			'section_track_info',
			[
				'label' => esc_html__( 'Track Info', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'show_track_info',
			[
				'label'        => esc_html__( 'Show Track Info', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'track_title',
			[
				'label'       => esc_html__( 'Track Title', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Track Title', 'sky-elementor-addons' ),
				'placeholder' => esc_html__( 'Enter track name', 'sky-elementor-addons' ),
				'dynamic'     => [ 'active' => true ],
				'condition'   => [ 'show_track_info' => 'yes' ],
			]
		);

		$this->add_control(
			'track_artist',
			[
				'label'       => esc_html__( 'Artist', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Artist Name', 'sky-elementor-addons' ),
				'placeholder' => esc_html__( 'Enter artist name', 'sky-elementor-addons' ),
				'dynamic'     => [ 'active' => true ],
				'condition'   => [ 'show_track_info' => 'yes' ],
			]
		);

		$this->add_responsive_control(
			'info_alignment',
			[
				'label'     => esc_html__( 'Alignment', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => esc_html__( 'Left', 'sky-elementor-addons' ),
						'icon'  => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'sky-elementor-addons' ),
						'icon'  => 'eicon-text-align-center',
					],
					'right' => [
						'title' => esc_html__( 'Right', 'sky-elementor-addons' ),
						'icon'  => 'eicon-text-align-right',
					],
				],
				'default'   => 'left',
				'condition' => [ 'show_track_info' => 'yes' ],
				'selectors' => [
					'{{WRAPPER}} .sa-audio-info' => 'text-align: {{VALUE}}',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_cover',
			[
				'label' => esc_html__( 'Cover Image', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'show_cover',
			[
				'label'        => esc_html__( 'Show Cover', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'track_cover',
			[
				'label'     => esc_html__( 'Cover Image', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::MEDIA,
				'dynamic'   => [ 'active' => true ],
				'default'   => [ 'url' => Utils::get_placeholder_image_src() ],
				'condition' => [ 'show_cover' => 'yes' ],
			]
		);

		$this->add_responsive_control(
			'media_position',
			[
				'label'        => esc_html__( 'Media Position', 'sky-elementor-addons' ) . sky_addons_label_badge( 'updated', '4.0.0' ),
				'type'         => Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => esc_html__( 'Left', 'sky-elementor-addons' ),
						'icon'  => 'eicon-h-align-left',
					],
					'top' => [
						'title' => esc_html__( 'Top', 'sky-elementor-addons' ),
						'icon'  => 'eicon-v-align-top',
					],
				],
				'default'      => 'left',
				'prefix_class' => 'sa-media-position-%s',
				'selectors_dictionary' => [
					'left' => '--sa-media-direction: row; --sa-media-left-width: auto; --sa-media-right-width: auto; --sa-cover-height: 88px;',
					'top'  => '--sa-media-direction: column; --sa-media-left-width: 100%; --sa-media-right-width: 100%; --sa-cover-height: auto;',
				],
				'selectors' => [
					'{{WRAPPER}} .sa-audio-player' => '{{VALUE}}',
				],
				'condition'    => [ 'show_cover' => 'yes' ],
			]
		);

		// media aligment
		$this->add_responsive_control(
			'media_alignment',
			[
				'label'     => esc_html__( 'Media Alignment', 'sky-elementor-addons' ) . sky_addons_label_badge( 'updated', '4.0.0' ),
				'type'      => Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => esc_html__( 'Left', 'sky-elementor-addons' ),
						'icon'  => 'eicon-h-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'sky-elementor-addons' ),
						'icon'  => 'eicon-h-align-center',
					],
					'right' => [
						'title' => esc_html__( 'Right', 'sky-elementor-addons' ),
						'icon'  => 'eicon-h-align-right',
					],
				],
				'default'   => 'left',
				'condition' => [ 'show_cover' => 'yes' ],
				'selectors' => [
					'{{WRAPPER}} .sa-audio-left' => 'justify-content: {{VALUE}}',
				],
			]
		);

		$this->end_controls_section();

		// ── Style: Card ────────────────────────────────────────────────────────

		$this->start_controls_section(
			'section_card_style',
			[
				'label' => esc_html__( 'Card', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'spacing_card',
			[
				'label'       => esc_html__( 'Spacing', 'sky-elementor-addons' ),
				'description' => esc_html__( 'Spacing between cover image and right side controls.', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::SLIDER,
				'size_units'  => [ 'px', 'em', '%' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 200,
					],
					'em' => [
						'min' => 0,
						'max' => 20,
					],
					'%'  => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .sa-audio-player' => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'card_background',
				'selector' => '{{WRAPPER}} .sa-audio-player',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'card_border',
				'selector' => '{{WRAPPER}} .sa-audio-player',
			]
		);

		$this->add_responsive_control(
			'card_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-audio-player' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'card_box_shadow',
				'selector' => '{{WRAPPER}} .sa-audio-player',
			]
		);

		$this->add_responsive_control(
			'card_padding',
			[
				'label'      => esc_html__( 'Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-audio-player' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// ── Style: Cover ───────────────────────────────────────────────────────

		$this->start_controls_section(
			'section_cover_style',
			[
				'label'     => esc_html__( 'Cover Image', 'sky-elementor-addons' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [ 'show_cover' => 'yes' ],
			]
		);

		$this->add_responsive_control(
			'cover_width',
			[
				'label'      => esc_html__( 'Width', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', '%' ],
				'range'      => [
					'px' => [
						'min' => 40,
						'max' => 400,
					],
					'em' => [
						'min' => 2,
						'max' => 25,
					],
					'%'  => [
						'min' => 5,
						'max' => 100,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-audio-cover' => 'width: {{SIZE}}{{UNIT}};',
				],
				'condition'  => [ 'player_style!' => 'vinyl' ],
			]
		);

		$this->add_responsive_control(
			'cover_height',
			[
				'label'      => esc_html__( 'Height', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', '%' ],
				'range'      => [
					'px' => [
						'min' => 40,
						'max' => 400,
					],
					'em' => [
						'min' => 2,
						'max' => 25,
					],
					'%'  => [
						'min' => 5,
						'max' => 100,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-audio-cover' => 'height: {{SIZE}}{{UNIT}};',
				],
				'condition'  => [ 'player_style!' => 'vinyl' ],
			]
		);

		$this->add_responsive_control(
			'vinyl_size',
			[
				'label'      => esc_html__( 'Vinyl Size', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min'  => 100,
						'max'  => 500,
						'step' => 1,
					],
				],
				'default'    => [
					'size' => 200,
					'unit' => 'px',
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-vinyl-cover-wrap' => '--sa-vinyl-size: {{SIZE}}{{UNIT}};',
				],
				'condition'  => [ 'player_style' => 'vinyl' ],
			]
		);

		$this->add_responsive_control(
			'vinyl_ring_gap',
			[
				'label'      => esc_html__( 'Ring Gap', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 80,
						'step' => 1,
					],
				],
				'default'    => [
					'size' => 0,
					'unit' => 'px',
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-audio-cover' => 'padding: {{SIZE}}{{UNIT}};',
				],
				'condition'  => [ 'player_style' => 'vinyl' ],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'cover_border',
				'selector' => '{{WRAPPER}} .sa-audio-cover',
			]
		);

		$this->add_responsive_control(
			'cover_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-audio-cover, {{WRAPPER}} .sa-audio-cover img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'cover_box_shadow',
				'selector' => '{{WRAPPER}} .sa-audio-cover',
			]
		);

		$this->end_controls_section();

		// ── Style: Controls Area ─────────────────────────────────────────────────────

		$this->start_controls_section(
			'section_controls_area_style',
			[
				'label' => esc_html__( 'Controls Area', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		// padding

		$this->add_responsive_control(
			'controls_area_padding',
			[
				'label'      => esc_html__( 'Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-audio-right' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// ── Style: Track Info ──────────────────────────────────────────────────

		$this->start_controls_section(
			'section_track_info_style',
			[
				'label'     => esc_html__( 'Track Info', 'sky-elementor-addons' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [ 'show_track_info' => 'yes' ],
			]
		);

		$this->add_control(
			'track_title_color',
			[
				'label'     => esc_html__( 'Title Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .sa-audio-title' => 'color: {{VALUE}};' ],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'track_title_typography',
				'selector' => '{{WRAPPER}} .sa-audio-title',
			]
		);

		$this->add_control(
			'track_artist_color',
			[
				'label'     => esc_html__( 'Artist Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .sa-audio-artist' => 'color: {{VALUE}};' ],
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'track_artist_typography',
				'selector' => '{{WRAPPER}} .sa-audio-artist',
			]
		);

		$this->end_controls_section();

		// ── Style: Play Button ─────────────────────────────────────────────────

		$this->start_controls_section(
			'section_play_btn_style',
			[
				'label' => esc_html__( 'Play Button', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->start_controls_tabs( 'tabs_play_btn' );

		$this->start_controls_tab( 'tab_play_btn_normal', [ 'label' => esc_html__( 'Normal', 'sky-elementor-addons' ) ] );

		$this->add_control(
			'play_btn_color',
			[
				'label'     => esc_html__( 'Icon Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .sa-btn-play-pause' => 'color: {{VALUE}};' ],
			]
		);

		$this->add_control(
			'play_btn_background',
			[
				'label'     => esc_html__( 'Background', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .sa-btn-play-pause' => 'background-color: {{VALUE}};' ],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab( 'tab_play_btn_hover', [ 'label' => esc_html__( 'Hover', 'sky-elementor-addons' ) ] );

		$this->add_control(
			'play_btn_color_hover',
			[
				'label'     => esc_html__( 'Icon Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .sa-btn-play-pause:hover' => 'color: {{VALUE}};' ],
			]
		);

		$this->add_control(
			'play_btn_background_hover',
			[
				'label'     => esc_html__( 'Background', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .sa-btn-play-pause:hover' => 'background-color: {{VALUE}};' ],
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_responsive_control(
			'play_btn_size',
			[
				'label'     => esc_html__( 'Size', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SLIDER,
				'units'     => [ 'px' ],
				'range'     => [
					'px' => [
						'min' => 30,
						'max' => 100,
					],
				],
				'selectors' => [ '{{WRAPPER}} .sa-btn-play-pause' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};' ],
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'play_btn_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [ '{{WRAPPER}} .sa-btn-play-pause' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
			]
		);

		$this->end_controls_section();

		// ── Style: Progress Bar ────────────────────────────────────────────────

		$this->start_controls_section(
			'section_progress_style',
			[
				'label' => esc_html__( 'Progress Bar', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'progress_fill_color',
			[
				'label'     => esc_html__( 'Fill Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .sa-progress-fill' => 'background-color: {{VALUE}};' ],
			]
		);

		$this->add_control(
			'progress_track_color',
			[
				'label'     => esc_html__( 'Track Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .sa-progress-bar' => 'background-color: {{VALUE}};' ],
			]
		);

		$this->add_responsive_control(
			'progress_height',
			[
				'label'     => esc_html__( 'Height', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min' => 2,
						'max' => 20,
					],
				],
				'selectors' => [ '{{WRAPPER}} .sa-progress-bar' => 'height: {{SIZE}}{{UNIT}};' ],
			]
		);

		$this->end_controls_section();

		// ── Style: Time ────────────────────────────────────────────────────────

		$this->start_controls_section(
			'section_time_style',
			[
				'label'     => esc_html__( 'Time Display', 'sky-elementor-addons' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [ 'show_time' => 'yes' ],
			]
		);

		$this->add_control(
			'time_color',
			[
				'label' => esc_html__( 'Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-time-current, {{WRAPPER}} .sa-time-total, {{WRAPPER}} .sa-time-sep' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'time_typography',
				'selector' => '{{WRAPPER}} .sa-time-current, {{WRAPPER}} .sa-time-total',
			]
		);

		$this->end_controls_section();

		// ── Style: Volume ──────────────────────────────────────────────────────

		$this->start_controls_section(
			'section_volume_style',
			[
				'label'     => esc_html__( 'Volume', 'sky-elementor-addons' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [ 'show_volume' => 'yes' ],
			]
		);

		$this->add_control(
			'volume_color',
			[
				'label' => esc_html__( 'Accent Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-volume-icon' => 'color: {{VALUE}};',
					'{{WRAPPER}} .sa-volume-slider' => '--sa-volume-fill: {{VALUE}};',
					'{{WRAPPER}} .sa-volume-slider::-webkit-slider-thumb' => 'background: {{VALUE}};',
					'{{WRAPPER}} .sa-volume-slider::-moz-range-thumb' => 'background: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		// ── Style: Wave Bar ────────────────────────────────────────────────────

		$this->start_controls_section(
			'section_wave_style',
			[
				'label'     => esc_html__( 'Wave Bar', 'sky-elementor-addons' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [ 'show_wave' => 'yes' ],
			]
		);

		$this->add_control(
			'wave_color',
			[
				'label'     => esc_html__( 'Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .sa-wave-bar span' => 'background-color: {{VALUE}};' ],
			]
		);

		$this->add_responsive_control(
			'wave_height',
			[
				'label'      => esc_html__( 'Height', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 12,
						'max' => 80,
					],
				],
				'selectors'  => [ '{{WRAPPER}} .sa-wave-bar' => 'height: {{SIZE}}{{UNIT}};' ],
			]
		);

		$this->add_responsive_control(
			'wave_bar_width',
			[
				'label'      => esc_html__( 'Bar Width', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 1,
						'max' => 20,
					],
				],
				'selectors'  => [ '{{WRAPPER}} .sa-wave-bar span' => 'flex: unset; width: {{SIZE}}{{UNIT}};' ],
			]
		);

		$this->add_responsive_control(
			'wave_gap',
			[
				'label'      => esc_html__( 'Spacing', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 10,
					],
				],
				'selectors'  => [ '{{WRAPPER}} .sa-wave-bar' => 'gap: {{SIZE}}{{UNIT}};' ],
			]
		);

		$this->add_responsive_control(
			'wave_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 10,
					],
				],
				'selectors'  => [ '{{WRAPPER}} .sa-wave-bar span' => 'border-radius: {{SIZE}}{{UNIT}} {{SIZE}}{{UNIT}} 0 0;' ],
			]
		);

		$this->add_responsive_control(
			'wave_margin',
			[
				'label'              => esc_html__( 'Margin', 'sky-elementor-addons' ),
				'type'               => Controls_Manager::DIMENSIONS,
				'size_units'         => [ 'px', 'em', '%' ],
				'allowed_dimensions' => [ 'top', 'bottom' ],
				'selectors'          => [ '{{WRAPPER}} .sa-wave-bar' => 'margin: {{TOP}}{{UNIT}} 0 {{BOTTOM}}{{UNIT}} 0;' ],
			]
		);

		$this->end_controls_section();
	}

	// ── Data Helpers ──────────────────────────────────────────────────────────

	private function get_audio_url( array $settings ): string {
		return 'remote_url' === $settings['source_type']
			? $settings['remote_url']['url']
			: $settings['hosted_url']['url'];
	}

	private function get_mime_type( string $url ): string {
		$ext = strtolower( pathinfo( wp_parse_url( $url, PHP_URL_PATH ), PATHINFO_EXTENSION ) );
		$map = [
			'mp3'  => 'audio/mpeg',
			'ogg'  => 'audio/ogg',
			'wav'  => 'audio/wav',
			'webm' => 'audio/webm',
			'm4a'  => 'audio/mp4',
		];
		return $map[ $ext ] ?? 'audio/mpeg';
	}

	// ── Render Parts ──────────────────────────────────────────────────────────

	private function render_cover_photo( array $settings ): void {
		if ( 'yes' !== ( $settings['show_cover'] ?? 'yes' ) || empty( $settings['track_cover']['url'] ) ) {
			return;
		}
		?>
		<div class="sa-audio-cover">
			<img src="<?php echo esc_url( $settings['track_cover']['url'] ); ?>" alt="<?php echo esc_attr( $settings['track_title'] ); ?>" loading="lazy">
		</div>
		<?php
	}

	private function render_vinyl_cover( array $settings ): void {
		if ( 'yes' !== ( $settings['show_cover'] ?? 'yes' ) || empty( $settings['track_cover']['url'] ) ) {
			return;
		}
		?>
		<div class="sa-vinyl-cover-wrap">
			<svg class="sa-vinyl-svg" viewBox="0 0 200 200" aria-hidden="true">
				<circle class="sa-vinyl-track" cx="100" cy="100" r="90" fill="none" stroke-width="4"/>
				<circle class="sa-vinyl-fill"  cx="100" cy="100" r="90" fill="none" stroke-width="4" stroke-dasharray="565" stroke-dashoffset="565"/>
			</svg>
			<div class="sa-audio-cover">
				<img src="<?php echo esc_url( $settings['track_cover']['url'] ); ?>" alt="<?php echo esc_attr( $settings['track_title'] ); ?>" loading="lazy">
			</div>
		</div>
		<?php
	}

	private function render_track_title( array $settings ): void {
		if ( empty( $settings['track_title'] ) ) {
			return;
		}
		?>
		<div class="sa-audio-title"><?php echo esc_html( $settings['track_title'] ); ?></div>
		<?php
	}

	private function render_artist( array $settings ): void {
		if ( empty( $settings['track_artist'] ) ) {
			return;
		}
		?>
		<div class="sa-audio-artist"><?php echo esc_html( $settings['track_artist'] ); ?></div>
		<?php
	}

	private function render_track_info( array $settings ): void {
		if ( 'yes' !== ( $settings['show_track_info'] ?? 'yes' ) ) {
			return;
		}
		?>
		<div class="sa-audio-info">
			<?php
			$this->render_track_title( $settings );
			$this->render_artist( $settings );
			?>
		</div>
		<?php
	}

	private function render_wave_bar( array $settings ): void {
		if ( 'yes' !== ( $settings['show_wave'] ?? 'yes' ) ) {
			return;
		}
		?>
		<div class="sa-wave-bar" aria-hidden="true">
			<?php
			for ( $i = 0; $i < 30; $i++ ) :
				?>
				<span></span><?php endfor; ?>
		</div>
		<?php
	}

	private function render_play_pause(): void {
		?>
		<button class="sa-btn-play-pause" aria-label="<?php echo esc_attr__( 'Play / Pause', 'sky-elementor-addons' ); ?>">
			<svg class="sa-icon-play" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>
			<svg class="sa-icon-pause" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
		</button>
		<?php
	}

	private function render_time_wrap(): void {
		?>
		<div class="sa-time-wrap">
			<span class="sa-time-current">0:00</span>
			<span class="sa-time-sep" aria-hidden="true">/</span>
			<span class="sa-time-total">0:00</span>
		</div>
		<?php
	}

	private function render_progress( array $settings ): void {
		?>
		<div class="sa-audio-progress">
			<div class="sa-progress-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
				<div class="sa-progress-fill"></div>
			</div>
			<?php if ( 'yes' === $settings['show_time'] ) : ?>
				<?php $this->render_time_wrap(); ?>
			<?php endif; ?>
		</div>
		<?php
	}

	private function render_volume(): void {
		?>
		<div class="sa-audio-volume">
			<svg class="sa-volume-icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M3 9v6h4l5 5V4L7 9H3zm13.5 3c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02zM14 3.23v2.06c2.89.86 5 3.54 5 6.71s-2.11 5.85-5 6.71v2.06c4.01-.91 7-4.49 7-8.77s-2.99-7.86-7-8.77z"/></svg>
			<input type="range" class="sa-volume-slider" min="0" max="1" step="0.05" value="1" aria-label="<?php echo esc_attr__( 'Volume', 'sky-elementor-addons' ); ?>">
		</div>
		<?php
	}

	private function render_controls( array $settings ): void {
		?>
		<div class="sa-audio-controls">
			<?php
			$this->render_play_pause();
			$this->render_progress( $settings );
			if ( 'yes' === ( $settings['show_volume'] ?? 'yes' ) ) :
				$this->render_volume();
			endif;
			?>
		</div>
		<?php
	}

	private function render_audio_source( string $audio_url, string $mime_type, string $id ): void {
		?>
		<audio id="<?php echo esc_attr( $id ); ?>-player" preload="metadata">
			<source src="<?php echo esc_url( $audio_url ); ?>" type="<?php echo esc_attr( $mime_type ); ?>">
		</audio>
		<?php
	}

	// ── Render ────────────────────────────────────────────────────────────────

	protected function render() {
		$settings  = $this->get_settings_for_display();
		$id        = 'sa-audio-' . $this->get_id();
		$audio_url = $this->get_audio_url( $settings );
		$mime_type = $this->get_mime_type( $audio_url );

		$this->add_render_attribute( 'wrapper', [
			'id'    => $id,
			'class' => [ 'sa-audio-player', 'sa-style-' . $settings['player_style'] ],
			'data-settings' => wp_json_encode( [
				'id'       => $id,
				'autoplay' => 'yes' === $settings['autoplay'],
				'loop'     => 'yes' === $settings['loop'],
			] ),
		] );
		?>
		<?php $this->render_audio_source( $audio_url, $mime_type, $id ); ?>

		<div <?php $this->print_render_attribute_string( 'wrapper' ); ?>>
			<div class="sa-audio-left">
				<?php
				if ( 'vinyl' === $settings['player_style'] ) :
					$this->render_vinyl_cover( $settings );
				else :
					$this->render_cover_photo( $settings );
				endif;
				?>
			</div>

			<div class="sa-audio-right">
				<?php
				$this->render_track_info( $settings );
				$this->render_wave_bar( $settings );
				$this->render_controls( $settings );
				?>
			</div>
		</div>
		<?php
	}
}

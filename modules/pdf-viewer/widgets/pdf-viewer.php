<?php

namespace Sky_Addons\Modules\PdfViewer\Widgets;

use Elementor\Utils;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Group_Control_Background;
use Elementor\Icons_Manager;
use Elementor\Widget_Base;
use Elementor\Modules\DynamicTags\Module as TagsModule;


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PDF_Viewer extends Widget_Base {

	public function get_name() {
		return 'sky-pdf-viewer';
	}

	public function get_title() {
		return esc_html__( 'PDF Viewer', 'sky-elementor-addons' );
	}

	public function get_icon() {
		return 'sky-icon-pdf-viewer';
	}

	public function get_categories() {
		return [ 'sky-elementor-addons' ];
	}

	public function get_keywords() {
		return [ 'sky', 'pdf', 'pdf-viewer', 'reader', 'document', 'object' ];
	}

	public function get_style_depends() {
		if ( sky_addons_editor_mode() ) {
			return [ 'sky-addons-styles' ];
		}

		return [ 'sa-pdf-viewer' ];
	}

	public function get_script_depends() {
		if ( sky_addons_editor_mode() ) {
			return [ 'pdfobject', 'sky-addons-scripts' ];
		}

		return [ 'pdfobject', 'sa-pdf-viewer' ];
	}

	public function get_custom_help_url() {
		return 'https://skyaddons.com/docs/sky-addons/widgets/pdf-viewer/';
	}

	public function has_widget_inner_wrapper(): bool {
		return ! \Elementor\Plugin::$instance->experiments->is_feature_active( 'e_optimized_markup' );
	}

	protected function register_controls() {

		$this->start_controls_section(
			'section_pdf_layout',
			[
				'label' => esc_html__( 'PDF Viewer', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'source_type',
			[
				'label'   => esc_html__( 'Select Source', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'hosted_url',
				'options' => [
					'hosted_url' => esc_html__( 'Hosted File', 'sky-elementor-addons' ),
					'remote_url' => esc_html__( 'Remote URL', 'sky-elementor-addons' ),
				],
			]
		);

		$this->add_control(
			'hosted_url',
			[
				'label'      => esc_html__( 'Local File', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::MEDIA,
				'dynamic'    => [
					'active' => true,
				],
				'media_type' => 'application/pdf',
				'default'    => [
					'url' => SKY_ADDONS_ASSETS_URL . 'others/pdf-file-sample.pdf',
				],
				'condition'  => [
					'source_type' => 'hosted_url',
				],
			]
		);

		$this->add_control(
			'remote_url',
			[
				'label'         => esc_html__( 'Remote URL', 'sky-elementor-addons' ),
				'type'          => Controls_Manager::URL,
				'show_external' => false,
				'default'       => [
					'url' => SKY_ADDONS_ASSETS_URL . 'others/pdf-file-sample.pdf',
				],
				'placeholder'   => 'https://file-examples-com.github.io/uploads/2017/10/file-sample_150kB.pdf',
				'dynamic'       => [
					'active' => true,
					'categories' => [
						TagsModule::POST_META_CATEGORY,
						TagsModule::URL_CATEGORY,
					],
				],
				'condition'     => [
					'source_type' => 'remote_url',
				],
			]
		);

		$this->add_control(
			'opened_page',
			[
				'label'       => esc_html__( 'Opened Page', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 1,
				'description' => esc_html__( 'Any number entered here will cause the PDF be opened to the specified page number, if the browser supports it. If left unspecified, the PDF will open on page 1.', 'sky-elementor-addons' ),
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_pdf_additional',
			[
				'label' => esc_html__( 'Additional Settings', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'width',
			[
				'label'      => esc_html__( 'Width', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', '%' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 1900,
					],
					'%'  => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default'    => [
					'unit' => '%',
					'size' => 100,
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-content' => 'width: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .pdfobject'  => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'height',
			[
				'label'      => esc_html__( 'Height', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'vh' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 1200,
					],
					'vh' => [
						'min' => 10,
						'max' => 100,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 600,
				],
				'selectors'  => [
					'{{WRAPPER}} .pdfobject' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'show_title',
			[
				'label'     => esc_html__( 'Show Title', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SWITCHER,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'title',
			[
				'label'       => esc_html__( 'Title', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'default'     => esc_html__( 'Simple PDF File', 'sky-elementor-addons' ),
				'placeholder' => esc_html__( 'Type your title here', 'sky-elementor-addons' ),
				'dynamic'     => [ 'active' => true ],
				'condition'   => [
					'show_title' => 'yes',
				],
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

		$this->add_control(
			'show_pdf_badge',
			[
				'label'     => esc_html__( 'Show PDF Badge', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'separator' => 'before',
				'condition' => [
					'show_title' => 'yes',
				],
			]
		);

		$this->add_control(
			'badge_text',
			[
				'label'   => esc_html__( 'Badge Text', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'PDF', 'sky-elementor-addons' ),
				'dynamic' => [ 'active' => true ],
				'condition' => [
					'show_pdf_badge' => 'yes',
					'show_title'     => 'yes',
				],
			]
		);

		$this->add_control(
			'badge_icon',
			[
				'label' => esc_html__( 'Badge Icon', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'  => Controls_Manager::ICONS,
				'condition' => [
					'show_pdf_badge' => 'yes',
					'show_title'     => 'yes',
				],
			]
		);

		$this->add_control(
			'badge_icon_position',
			[
				'label'          => esc_html__( 'Icon Position', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'           => Controls_Manager::CHOOSE,
				'label_block'    => false,
				'options'        => [
					'before' => [
						'title' => esc_html__( 'Before', 'sky-elementor-addons' ),
						'icon'  => 'eicon-h-align-left',
					],
					'after'  => [
						'title' => esc_html__( 'After', 'sky-elementor-addons' ),
						'icon'  => 'eicon-h-align-right',
					],
				],
				'default'        => 'before',
				'toggle'         => false,
				'condition'      => [
					'show_pdf_badge'     => 'yes',
					'show_title'         => 'yes',
					'badge_icon[value]!' => '',
				],
				'style_transfer' => true,
			]
		);

		$this->add_control(
			'show_button',
			[
				'label'     => esc_html__( 'Show Download Button', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::SWITCHER,
				'separator' => 'before',
			]
		);

		$this->end_controls_section();

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
			'button_text',
			[
				'label'   => esc_html__( 'Download', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Download', 'sky-elementor-addons' ),
				'dynamic' => [ 'active' => true ],
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
						'title' => esc_html__( 'Before', 'sky-elementor-addons' ),
						'icon'  => 'eicon-h-align-left',
					],
					'after'  => [
						'title' => esc_html__( 'After', 'sky-elementor-addons' ),
						'icon'  => 'eicon-h-align-right',
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
					'{{WRAPPER}} .sa-button-icon-before .sa-button-icon' => 'margin-right: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .sa-button-icon-after .sa-button-icon'  => 'margin-left: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_pdf_style',
			[
				'label' => esc_html__( 'PDF Viewer', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'pdf_padding',
			[
				'label'      => esc_html__( 'Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .pdfobject' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'pdf_margin',
			[
				'label'      => esc_html__( 'Margin', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .pdfobject' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'pdf_border',
				'label'    => esc_html__( 'Border', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .pdfobject',
			]
		);

		$this->add_responsive_control(
			'pdf_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .pdfobject' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow: hidden;',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'pdf_box_shadow',
				'label'    => esc_html__( 'Box Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .pdfobject',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_header_style',
			[
				'label' => esc_html__( 'Header Bar', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'tab'   => Controls_Manager::TAB_STYLE,
				'conditions' => [
					'relation' => 'or',
					'terms'    => [
						[
							'name'     => 'show_title',
							'operator' => '==',
							'value'    => 'yes',
						],
						[
							'name'     => 'show_button',
							'operator' => '==',
							'value'    => 'yes',
						],
					],
				],
			]
		);

		$this->add_responsive_control(
			'header_padding',
			[
				'label'      => esc_html__( 'Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'header_background',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .sa-content',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'header_border',
				'label'    => esc_html__( 'Border', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-content',
			]
		);

		$this->add_responsive_control(
			'header_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-content' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'header_box_shadow',
				'label'    => esc_html__( 'Box Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-content',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_title_style',
			[
				'label' => esc_html__( 'Title', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_title' => 'yes',
					'title!'     => '',
				],
			]
		);

		$this->add_control(
			'title_color',
			[
				'label' => esc_html__( 'Text Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-title' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'title_text_shadow',
				'label'    => esc_html__( 'Text Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-title',
			]
		);

		$this->add_responsive_control(
			'title_margin',
			[
				'label'      => esc_html__( 'Margin', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'title_typography',
				'label'    => esc_html__( 'Typography', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-title',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_badge_style',
			[
				'label' => esc_html__( 'PDF Badge', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'tab'   => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_pdf_badge' => 'yes',
					'show_title'     => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'badge_padding',
			[
				'label'      => esc_html__( 'Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-pdf-badge' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'badge_typography',
				'label'    => esc_html__( 'Typography', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-pdf-badge .sa-badge-text',
			]
		);

		$this->add_control(
			'badge_color',
			[
				'label' => esc_html__( 'Text Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-pdf-badge' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'badge_background',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .sa-pdf-badge',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'badge_border',
				'label'    => esc_html__( 'Border', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-pdf-badge',
			]
		);

		$this->add_responsive_control(
			'badge_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-pdf-badge' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'badge_box_shadow',
				'label'    => esc_html__( 'Box Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-pdf-badge',
			]
		);

		$this->add_control(
			'badge_icon_heading',
			[
				'label'     => esc_html__( 'Icon', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					'badge_icon[value]!' => '',
				],
			]
		);

		$this->add_responsive_control(
			'badge_icon_size',
			[
				'label'      => esc_html__( 'Size', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range'      => [
					'px' => [
						'min' => 6,
						'max' => 64,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-pdf-badge .sa-badge-icon'     => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .sa-pdf-badge .sa-badge-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				],
				'condition'  => [
					'badge_icon[value]!' => '',
				],
			]
		);

		$this->add_control(
			'badge_icon_color',
			[
				'label' => esc_html__( 'Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-pdf-badge .sa-badge-icon'     => 'color: {{VALUE}};',
					'{{WRAPPER}} .sa-pdf-badge .sa-badge-icon svg' => 'fill: {{VALUE}};',
				],
				'condition' => [
					'badge_icon[value]!' => '',
				],
			]
		);

		$this->add_responsive_control(
			'badge_icon_spacing',
			[
				'label'      => esc_html__( 'Spacing', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 20,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-badge-icon-before .sa-badge-icon' => 'margin-right: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .sa-badge-icon-after .sa-badge-icon'  => 'margin-left: {{SIZE}}{{UNIT}};',
				],
				'condition'  => [
					'badge_icon[value]!' => '',
				],
			]
		);

		$this->end_controls_section();

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
					'{{WRAPPER}} .sa-button, {{WRAPPER}} .sa-button:focus' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'button_background',
				'label'    => esc_html__( 'Background', 'sky-elementor-addons' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .sa-button, {{WRAPPER}} .sa-button:focus',
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
	}

	private function empty_alert() {
		?>
		<div style="border: 1px solid #F76E11; padding: 15px; text-align:center;">
			<?php echo esc_html__( 'Sorry, PDF File is empty. Please upload or use proper URL of PDF file.', 'sky-elementor-addons' ); ?>
		</div>
		<?php
	}

	private function render_badge( $settings ) {
		if ( 'yes' !== $settings['show_pdf_badge'] ) {
			return;
		}

		$has_icon      = ! empty( $settings['badge_icon']['value'] );
		$icon_position = $has_icon ? $settings['badge_icon_position'] : '';
		$badge_classes = 'sa-pdf-badge' . ( $icon_position ? ' sa-badge-icon-' . $icon_position : '' );

		echo '<span class="' . esc_attr( $badge_classes ) . '">';

		if ( $has_icon && 'before' === $icon_position ) {
			echo '<span class="sa-badge-icon">';
			Icons_Manager::render_icon( $settings['badge_icon'], [ 'aria-hidden' => 'true' ] );
			echo '</span>';
		}

		if ( ! empty( $settings['badge_text'] ) ) {
			echo '<span class="sa-badge-text">' . esc_html( $settings['badge_text'] ) . '</span>';
		}

		if ( $has_icon && 'after' === $icon_position ) {
			echo '<span class="sa-badge-icon">';
			Icons_Manager::render_icon( $settings['badge_icon'], [ 'aria-hidden' => 'true' ] );
			echo '</span>';
		}

		echo '</span>';
	}

	private function render_title( $settings ) {
		if ( 'yes' !== $settings['show_title'] || empty( $settings['title'] ) ) {
			return;
		}

		$this->add_render_attribute( 'title', 'class', 'sa-title sa--title sa--text-title sa-mt-0 sa-mb-1 sa-fs-5' );
		$this->add_inline_editing_attributes( 'title', 'none' );

		$title_tag = Utils::validate_html_tag( $settings['title_tag'] );

		// Badge prints directly — its SVG icon wouldn't survive wp_kses_post().
		printf( '<div><%1$s %2$s>', esc_attr( $title_tag ), wp_kses_post( $this->get_render_attribute_string( 'title' ) ) );
		$this->render_badge( $settings );
		echo wp_kses_post( $settings['title'] );
		printf( '</%1$s></div>', esc_attr( $title_tag ) );
	}

	private function render_download_button( $settings, $pdf_url ) {
		if ( 'yes' !== $settings['show_button'] ) {
			return;
		}

		$this->add_render_attribute( 'link_attr', 'class', 'sa-button sa-button-primary sa-d-inline-flex sa-align-items-center sa-text-decoration-none sa-p-3 sa-rounded' );
		$this->add_render_attribute( 'link_attr', 'href', esc_url( $pdf_url ) );
		$this->add_render_attribute( 'link_attr', 'download' );

		if ( $settings['button_hover_animation'] ) {
			$this->add_render_attribute( 'link_attr', 'class', 'elementor-animation-' . $settings['button_hover_animation'] );
		}

		if ( ! empty( $settings['button_text'] ) ) {
			$this->add_render_attribute( 'link_attr', 'class', 'sa-button-icon-' . $settings['button_icon_position'] );
		}
		?>
		<a <?php $this->print_render_attribute_string( 'link_attr' ); ?>>
			<?php
			if ( ! empty( $settings['button_icon']['value'] ) && 'before' === $settings['button_icon_position'] ) {
				echo '<span class="sa-icon-wrap sa-button-icon">';
				Icons_Manager::render_icon( $settings['button_icon'], [
					'aria-hidden' => 'true',
					'class'       => 'sa-button-icon',
				] );
				echo '</span>';
			}

			if ( ! empty( $settings['button_text'] ) ) {
				$this->add_render_attribute( 'button_text', 'class', 'sa-button-text' );
				$this->add_inline_editing_attributes( 'button_text', 'none' );

				printf(
					'<span %1$s>%2$s</span>',
					wp_kses_post( $this->get_render_attribute_string( 'button_text' ) ),
					esc_html( $settings['button_text'] )
				);
			}

			if ( ! empty( $settings['button_icon']['value'] ) && 'after' === $settings['button_icon_position'] ) {
				echo '<span class="sa-icon-wrap sa-button-icon">';
				Icons_Manager::render_icon( $settings['button_icon'], [
					'aria-hidden' => 'true',
					'class'       => 'sa-button-icon',
				] );
				echo '</span>';
			}
			?>
		</a>
		<?php
	}

	private function render_header( $settings, $pdf_url ) {
		if ( 'yes' !== $settings['show_title'] && 'yes' !== $settings['show_button'] ) {
			return;
		}
		?>
		<div class="sa-content sa-d-flex sa-justify-content-between sa-my-2 sa-align-items-center">
			<?php $this->render_title( $settings ); ?>
			<div>
				<?php $this->render_download_button( $settings, $pdf_url ); ?>
			</div>
		</div>
		<?php
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$id       = 'sa-pdf-viewer' . $this->get_id();
		$pdf_url  = ( 'hosted_url' === $settings['source_type'] && ! empty( $settings['hosted_url']['url'] ) )
			? $settings['hosted_url']['url']
			: ( ( isset( $settings['remote_url'] ) && ! empty( $settings['remote_url']['url'] ) )
				? $settings['remote_url']['url']
				: false );

		if ( false === $pdf_url ) {
			$this->empty_alert();
			return;
		}

		$this->add_render_attribute( 'pdf-viewer', [
			'class' => [ 'sa-pdf-viewer' ],
			'data-settings' => [
				wp_json_encode( array_filter( [
					'id'     => '#' . $id,
					'pdfUrl' => esc_url_raw( $pdf_url ),
				] ) ),
			],
			'data-pdf-settings' => [
				wp_json_encode( [
					'width'            => ! empty( $settings['width']['size'] ) ? $settings['width']['size'] . $settings['width']['unit'] : '100%',
					'height'           => ! empty( $settings['height']['size'] ) ? $settings['height']['size'] . $settings['height']['unit'] : '600px',
					'page'             => ! empty( $settings['opened_page'] ) ? (int) $settings['opened_page'] : 1,
					'omitInlineStyles' => true,
				] ),
			],
		] );
		?>
		<div <?php $this->print_render_attribute_string( 'pdf-viewer' ); ?>>
			<?php $this->render_header( $settings, $pdf_url ); ?>
			<div id="<?php echo esc_attr( $id ); ?>"></div>
		</div>
		<?php
	}
}

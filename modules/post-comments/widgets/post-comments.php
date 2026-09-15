<?php

namespace Sky_Addons\Modules\PostComments\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Post_Comments extends Widget_Base {

	public function get_name() {
		return 'sky-post-comments';
	}

	public function get_title() {
		return esc_html__( 'Post Comments', 'sky-elementor-addons' );
	}

	public function get_icon() {
		return 'sky-icon-post-comments';
	}

	public function get_categories() {
		return [ 'sky-elementor-addons' ];
	}

	public function get_keywords() {
		return [ 'sky', 'post', 'comments', 'themebuilder', 'single' ];
	}

	public function get_style_depends() {
		if ( sky_addons_editor_mode() ) {
			return [ 'sky-addons-styles' ];
		}

		return [ 'sa-post-comments' ];
	}

	public function has_widget_inner_wrapper(): bool {
		return ! \Elementor\Plugin::$instance->experiments->is_feature_active( 'e_optimized_markup' );
	}

	protected function register_controls() {
		$this->register_content_controls();
		$this->register_style_controls();
	}

	protected function register_content_controls() {
		$this->start_controls_section(
			'_section_post_comments',
			[
				'label' => esc_html__( 'Post Comments', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'source_type',
			[
				'label'   => esc_html__( 'Source', 'sky-elementor-addons' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'current_post' => esc_html__( 'Current Post', 'sky-elementor-addons' ),
					'custom'       => esc_html__( 'Custom', 'sky-elementor-addons' ),
				],
				'default' => 'current_post',
			]
		);

		$this->add_control(
			'source_custom',
			[
				'label'       => esc_html__( 'Custom Post', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'Enter Post ID', 'sky-elementor-addons' ),
				'condition'   => [
					'source_type' => 'custom',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_style_controls() {
		$this->register_wrapper_style_controls();
		$this->register_title_style_controls();
		$this->register_comment_item_style_controls();
		$this->register_author_style_controls();
		$this->register_meta_style_controls();
		$this->register_content_style_controls();
		$this->register_reply_link_style_controls();
		$this->register_form_style_controls();
	}

	private function register_wrapper_style_controls() {
		$this->start_controls_section(
			'section_wrapper_style',
			[
				'label' => esc_html__( 'Wrapper', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'wrapper_background',
				'selector' => '{{WRAPPER}} .sa-post-comments:not(#_):not(#_)',
			]
		);

		$this->add_responsive_control(
			'wrapper_padding',
			[
				'label'      => esc_html__( 'Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-post-comments:not(#_):not(#_)' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'wrapper_border',
				'selector' => '{{WRAPPER}} .sa-post-comments:not(#_):not(#_)',
			]
		);

		$this->add_responsive_control(
			'wrapper_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-post-comments:not(#_):not(#_)' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'wrapper_box_shadow',
				'selector' => '{{WRAPPER}} .sa-post-comments:not(#_):not(#_)',
			]
		);

		$this->end_controls_section();
	}

	private function register_title_style_controls() {
		$this->start_controls_section(
			'section_title_style',
			[
				'label' => esc_html__( 'Comments Title', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'title_color',
			[
				'label' => esc_html__( 'Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-post-comments:not(#_):not(#_) .comments-title' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'title_typography',
				'selector' => '{{WRAPPER}} .sa-post-comments:not(#_):not(#_) .comments-title',
			]
		);

		$this->add_responsive_control(
			'title_spacing',
			[
				'label'      => esc_html__( 'Bottom Spacing', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 80,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-post-comments:not(#_):not(#_) .comments-title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'title_divider_color',
			[
				'label' => esc_html__( 'Divider Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-post-comments:not(#_):not(#_) .comments-title' => 'border-bottom-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'title_accent_color',
			[
				'label' => esc_html__( 'Accent Line Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-post-comments:not(#_):not(#_) .comments-title::after' => 'background: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();
	}

	private function register_comment_item_style_controls() {
		$this->start_controls_section(
			'section_comment_item_style',
			[
				'label' => esc_html__( 'Comment Item', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'item_background',
				'selector' => '{{WRAPPER}} .sa-post-comments:not(#_):not(#_) .comment-body',
			]
		);

		$this->add_responsive_control(
			'item_padding',
			[
				'label'      => esc_html__( 'Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-post-comments:not(#_):not(#_) .comment-body' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'item_gap',
			[
				'label'      => esc_html__( 'Gap Between Items', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 60,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-post-comments:not(#_):not(#_) .comment-body' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'item_border',
				'selector' => '{{WRAPPER}} .sa-post-comments:not(#_):not(#_) .comment-body',
			]
		);

		$this->add_responsive_control(
			'item_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-post-comments:not(#_):not(#_) .comment-body' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'item_box_shadow',
				'selector' => '{{WRAPPER}} .sa-post-comments:not(#_):not(#_) .comment-body',
			]
		);

		$this->end_controls_section();
	}

	private function register_author_style_controls() {
		$this->start_controls_section(
			'section_author_style',
			[
				'label' => esc_html__( 'Author', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'avatar_size',
			[
				'label'      => esc_html__( 'Avatar Size', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 20,
						'max' => 120,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-post-comments:not(#_):not(#_) .comment-author .avatar' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'avatar_border_radius',
			[
				'label'      => esc_html__( 'Avatar Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 60,
					],
					'%'  => [
						'min' => 0,
						'max' => 50,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-post-comments:not(#_):not(#_) .comment-author .avatar' => 'border-radius: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'avatar_border_color',
			[
				'label' => esc_html__( 'Avatar Border Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-post-comments:not(#_):not(#_) .comment-author .avatar' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'author_name_typography',
				'label'    => esc_html__( 'Name Typography', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-post-comments:not(#_):not(#_) .comment-author .fn a',
			]
		);

		$this->start_controls_tabs( 'author_name_tabs' );

		$this->start_controls_tab(
			'author_name_tab_normal',
			[ 'label' => esc_html__( 'Normal', 'sky-elementor-addons' ) ]
		);

		$this->add_control(
			'author_name_color',
			[
				'label' => esc_html__( 'Name Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-post-comments:not(#_):not(#_) .comment-author .fn a' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'author_name_tab_hover',
			[ 'label' => esc_html__( 'Hover', 'sky-elementor-addons' ) ]
		);

		$this->add_control(
			'author_name_color_hover',
			[
				'label' => esc_html__( 'Name Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-post-comments:not(#_):not(#_) .comment-author .fn a:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	private function register_meta_style_controls() {
		$this->start_controls_section(
			'section_meta_style',
			[
				'label' => esc_html__( 'Meta', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'meta_typography',
				'selector' => '{{WRAPPER}} .sa-post-comments:not(#_):not(#_) .comment-metadata a',
			]
		);

		$this->start_controls_tabs( 'meta_tabs' );

		$this->start_controls_tab(
			'meta_tab_normal',
			[ 'label' => esc_html__( 'Normal', 'sky-elementor-addons' ) ]
		);

		$this->add_control(
			'meta_color',
			[
				'label' => esc_html__( 'Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-post-comments:not(#_):not(#_) .comment-metadata a' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'meta_tab_hover',
			[ 'label' => esc_html__( 'Hover', 'sky-elementor-addons' ) ]
		);

		$this->add_control(
			'meta_color_hover',
			[
				'label' => esc_html__( 'Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-post-comments:not(#_):not(#_) .comment-metadata a:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	private function register_content_style_controls() {
		$this->start_controls_section(
			'section_content_style',
			[
				'label' => esc_html__( 'Comment Content', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'content_color',
			[
				'label' => esc_html__( 'Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-post-comments:not(#_):not(#_) .comment-content' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'content_typography',
				'selector' => '{{WRAPPER}} .sa-post-comments:not(#_):not(#_) .comment-content',
			]
		);

		$this->end_controls_section();
	}

	private function register_reply_link_style_controls() {
		$this->start_controls_section(
			'section_reply_link_style',
			[
				'label' => esc_html__( 'Reply Link', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'reply_link_typography',
				'selector' => '{{WRAPPER}} .sa-post-comments:not(#_):not(#_) .comment-reply-link',
			]
		);

		$this->add_responsive_control(
			'reply_link_padding',
			[
				'label'      => esc_html__( 'Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-post-comments:not(#_):not(#_) .comment-reply-link' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'reply_link_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-post-comments:not(#_):not(#_) .comment-reply-link' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->start_controls_tabs( 'reply_link_tabs' );

		$this->start_controls_tab(
			'reply_link_tab_normal',
			[ 'label' => esc_html__( 'Normal', 'sky-elementor-addons' ) ]
		);

		$this->add_control(
			'reply_link_color',
			[
				'label' => esc_html__( 'Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-post-comments:not(#_):not(#_) .comment-reply-link' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'reply_link_bg_color',
			[
				'label' => esc_html__( 'Background', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-post-comments:not(#_):not(#_) .comment-reply-link' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'reply_link_tab_hover',
			[ 'label' => esc_html__( 'Hover', 'sky-elementor-addons' ) ]
		);

		$this->add_control(
			'reply_link_color_hover',
			[
				'label' => esc_html__( 'Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-post-comments:not(#_):not(#_) .comment-reply-link:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'reply_link_bg_color_hover',
			[
				'label' => esc_html__( 'Background', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-post-comments:not(#_):not(#_) .comment-reply-link:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	private function register_form_style_controls() {
		$this->start_controls_section(
			'section_form_style',
			[
				'label' => esc_html__( 'Comment Form', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'form_background',
				'label'    => esc_html__( 'Form Background', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-post-comments:not(#_):not(#_) .comment-respond',
			]
		);

		$this->add_responsive_control(
			'form_padding',
			[
				'label'      => esc_html__( 'Form Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-post-comments:not(#_):not(#_) .comment-respond' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'form_border_radius',
			[
				'label'      => esc_html__( 'Form Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-post-comments:not(#_):not(#_) .comment-respond' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'form_title_heading',
			[
				'label'     => esc_html__( 'Form Title', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'form_title_color',
			[
				'label' => esc_html__( 'Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-post-comments:not(#_):not(#_) .comment-reply-title' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'form_title_typography',
				'selector' => '{{WRAPPER}} .sa-post-comments:not(#_):not(#_) .comment-reply-title',
			]
		);

		$this->add_control(
			'form_inputs_heading',
			[
				'label'     => esc_html__( 'Input Fields', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'input_text_color',
			[
				'label' => esc_html__( 'Text Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-post-comments:not(#_):not(#_) .comment-form input[type="text"], {{WRAPPER}} .sa-post-comments:not(#_):not(#_) .comment-form input[type="email"], {{WRAPPER}} .sa-post-comments:not(#_):not(#_) .comment-form input[type="url"], {{WRAPPER}} .sa-post-comments:not(#_):not(#_) .comment-form textarea#comment' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'input_bg_color',
			[
				'label' => esc_html__( 'Background Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-post-comments:not(#_):not(#_) .comment-form input[type="text"], {{WRAPPER}} .sa-post-comments:not(#_):not(#_) .comment-form input[type="email"], {{WRAPPER}} .sa-post-comments:not(#_):not(#_) .comment-form input[type="url"], {{WRAPPER}} .sa-post-comments:not(#_):not(#_) .comment-form textarea#comment' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'input_border_color',
			[
				'label' => esc_html__( 'Border Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-post-comments:not(#_):not(#_) .comment-form input[type="text"], {{WRAPPER}} .sa-post-comments:not(#_):not(#_) .comment-form input[type="email"], {{WRAPPER}} .sa-post-comments:not(#_):not(#_) .comment-form input[type="url"], {{WRAPPER}} .sa-post-comments:not(#_):not(#_) .comment-form textarea#comment' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'input_focus_border_color',
			[
				'label' => esc_html__( 'Focus Border Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-post-comments:not(#_):not(#_) .comment-form input[type="text"]:focus, {{WRAPPER}} .sa-post-comments:not(#_):not(#_) .comment-form input[type="email"]:focus, {{WRAPPER}} .sa-post-comments:not(#_):not(#_) .comment-form input[type="url"]:focus, {{WRAPPER}} .sa-post-comments:not(#_):not(#_) .comment-form textarea#comment:focus' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'input_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-post-comments:not(#_):not(#_) .comment-form input[type="text"], {{WRAPPER}} .sa-post-comments:not(#_):not(#_) .comment-form input[type="email"], {{WRAPPER}} .sa-post-comments:not(#_):not(#_) .comment-form input[type="url"], {{WRAPPER}} .sa-post-comments:not(#_):not(#_) .comment-form textarea#comment' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'submit_btn_heading',
			[
				'label'     => esc_html__( 'Submit Button', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'submit_typography',
				'selector' => '{{WRAPPER}} .sa-post-comments:not(#_):not(#_) .comment-form input[type="submit"]',
			]
		);

		$this->add_responsive_control(
			'submit_padding',
			[
				'label'      => esc_html__( 'Padding', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-post-comments:not(#_):not(#_) .comment-form input[type="submit"]' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'submit_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sa-post-comments:not(#_):not(#_) .comment-form input[type="submit"]' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->start_controls_tabs( 'submit_tabs' );

		$this->start_controls_tab(
			'submit_tab_normal',
			[ 'label' => esc_html__( 'Normal', 'sky-elementor-addons' ) ]
		);

		$this->add_control(
			'submit_color',
			[
				'label' => esc_html__( 'Text Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-post-comments:not(#_):not(#_) .comment-form input[type="submit"]' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'submit_bg_color',
			[
				'label' => esc_html__( 'Background', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-post-comments:not(#_):not(#_) .comment-form input[type="submit"]' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'submit_tab_hover',
			[ 'label' => esc_html__( 'Hover', 'sky-elementor-addons' ) ]
		);

		$this->add_control(
			'submit_color_hover',
			[
				'label' => esc_html__( 'Text Color', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-post-comments:not(#_):not(#_) .comment-form input[type="submit"]:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'submit_bg_color_hover',
			[
				'label' => esc_html__( 'Background', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sa-post-comments:not(#_):not(#_) .comment-form input[type="submit"]:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'submit_box_shadow_hover',
				'selector' => '{{WRAPPER}} .sa-post-comments:not(#_):not(#_) .comment-form input[type="submit"]:hover',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	protected function render() {
		$settings     = $this->get_settings_for_display();
		$source_type  = isset( $settings['source_type'] ) ? $settings['source_type'] : 'current_post';
		$post_changed = false;

		if ( 'custom' === $source_type ) {
			$post_id = isset( $settings['source_custom'] ) ? (int) $settings['source_custom'] : 0;
			if ( $post_id ) {
				global $post;
				$post = get_post( $post_id );
				setup_postdata( $post );
				$post_changed = true;
			}
		}

		echo '<div class="sa-post-comments">';

		if ( comments_open() || get_comments_number() ) {
			comments_template();
		} else {
			echo '<p class="sa-no-comments">' . esc_html__( 'Comments are closed.', 'sky-elementor-addons' ) . '</p>';
		}

		echo '</div>';

		if ( $post_changed ) {
			wp_reset_postdata();
		}
	}

	public function content_template() {}
}

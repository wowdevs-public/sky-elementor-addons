<?php

namespace Sky_Addons\Modules\PostComments\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Widget_Base;
use Elementor\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
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
		return [ 'sky', 'post', 'title', 'themebuilder', 'single' ];
	}

	protected function register_controls() {
		// Add style controls from the parent Elementor Heading widget
		parent::register_controls();

		// push a control of parent start_controls_section
		$this->update_control(
			'section_title',
			[
				'label' => esc_html__( 'Post Comments', 'sky-elementor-addons' ),
			]
		);
		$this->update_control(
			'section_title_style',
			[
				'label' => esc_html__( 'Post Comments', 'sky-elementor-addons' ),
			]
		);
	}

	protected function register_content_controls() {
		$this->start_controls_section(
			'_section_post_comments',
			[
				'label' => __( 'Post Comments', 'sky-elementor-addons' ),
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
		$this->start_controls_section(
			'comments_style',
			[
				'label' => __( 'Comments Style', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'comments_text_color',
			[
				'label'     => esc_html__( 'Text Color', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .comments-area' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'comments_typography',
				'label'    => __( 'Typography', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .comments-area',
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$source_type = isset( $settings['source_type'] ) ? $settings['source_type'] : 'current_post';

		if ( 'custom' === $source_type ) {
			$post_id = isset( $settings['source_custom'] ) ? (int) $settings['source_custom'] : 0;
			if ( $post_id ) {
				global $post;
				$post = get_post( $post_id );
				setup_postdata( $post );
			}
		}

		if ( comments_open() || get_comments_number() ) {
			comments_template();
		} else {
			echo '<p>' . esc_html__( 'Comments are closed.', 'sky-elementor-addons' ) . '</p>';
		}

		if ( 'custom' === $source_type && isset( $post ) ) {
			wp_reset_postdata();
		}
	}

	public function content_template() {}
}

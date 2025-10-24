<?php

namespace Sky_Addons\Modules\PostTitle\Widgets;

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

class Post_Title extends \Elementor\Widget_Heading {

	public function get_name() {
		return 'sky-post-title';
	}

	public function get_title() {
		return esc_html__( 'Post Title', 'sky-elementor-addons' );
	}

	public function get_icon() {
		return 'sky-icon-post-title';
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
				'label' => esc_html__( 'Post Title', 'sky-elementor-addons' ),
			]
		);
		$this->update_control(
			'section_title_style',
			[
				'label' => esc_html__( 'Post Title', 'sky-elementor-addons' ),
			]
		);
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$title = get_the_title();
		$link = get_permalink();

		if ( empty( $title ) ) {
			return;
		}

		if ( sky_addons_editor_mode() ) {
			$title = 'Lorem Ipsum is simply dummy text.';
		}

		$this->add_render_attribute( 'title', 'class', 'elementor-heading-title' );

		if ( ! empty( $settings['size'] ) ) {
			$this->add_render_attribute( 'title', 'class', 'elementor-size-' . $settings['size'] );
		} else {
			$this->add_render_attribute( 'title', 'class', 'elementor-size-default' );
		}

		if ( ! empty( $link ) && 'yes' === $settings['enable_title_link'] ) {
			$title = sprintf( '<a href="%1$s">%2$s</a>', $link, $title );
		}

		$title_html = sprintf( '<%1$s %2$s>%3$s</%1$s>', Utils::validate_html_tag( $settings['header_size'] ), $this->get_render_attribute_string( 'title' ), $title );

		// PHPCS - the variable $title_html holds safe data.
		echo $title_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	public function content_template() {}
}

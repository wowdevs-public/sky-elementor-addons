<?php

namespace Sky_Addons\Modules\PostContent\Widgets;

use Elementor\Widget_Base;
use Elementor\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Post_Content extends \Elementor\Widget_Text_Editor {

	public function get_name() {
		return 'sky-post-content';
	}

	public function get_title() {
		return esc_html__( 'Post Content', 'sky-elementor-addons' );
	}

	public function get_icon() {
		return 'sky-icon-post-content';
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

		$this->update_control(
			'section_editor',
			[
				'label' => esc_html__( 'Post Content', 'sky-elementor-addons' ),
			]
		);
		$this->update_control(
			'section_style',
			[
				'label' => esc_html__( 'Post Content', 'sky-elementor-addons' ),
			]
		);
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$content = get_the_content();

		if ( empty( $content ) ) {
			return;
		}

		if ( sky_addons_editor_mode() ) {
			$content = '<div class="sky-editor-mode">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum. <br/> <br/>There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour, or randomised words which don\'t look even slightly believable. If you are going to use a passage of Lorem Ipsum, you need to be sure there isn\'t anything embarrassing hidden in the middle of text. All the Lorem Ipsum generators on the Internet tend to repeat predefined chunks as necessary, making this the first true generator on the Internet. It uses a dictionary of over 200 Latin words, combined with a handful of model sentence structures, to generate Lorem Ipsum which looks reasonable. The generated Lorem Ipsum is therefore always free from repetition, injected humour, or non-characteristic words etc.</div>';
		}

		$this->add_render_attribute( 'content', 'class', 'elementor-text-editor' );

		// Wrap the content in a paragraph tag
		$content_html = sprintf(
			'<div %1$s>%2$s</div>',
			$this->get_render_attribute_string( 'content' ),
			wp_kses_post( $content ) // Escape output for security
		);

		// Output the content
		echo $content_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}


	public function content_template() {}
}

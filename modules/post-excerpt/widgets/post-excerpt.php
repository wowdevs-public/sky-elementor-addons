<?php

namespace Sky_Addons\Modules\PostExcerpt\Widgets;

use Elementor\Widget_Base;
use Elementor\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Post_Excerpt extends \Elementor\Widget_Text_Editor {

	public function get_name() {
		return 'sky-post-excerpt';
	}

	public function get_title() {
		return esc_html__( 'Post Excerpt', 'sky-elementor-addons' );
	}

	public function get_icon() {
		return 'sky-icon-post-excerpt';
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
				'label' => esc_html__( 'Post Excerpt', 'sky-elementor-addons' ),
			]
		);
		$this->update_control(
			'section_style',
			[
				'label' => esc_html__( 'Post Excerpt', 'sky-elementor-addons' ),
			]
		);
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$content = get_the_excerpt();

		if ( empty( $content ) ) {
			return;
		}

		if ( sky_addons_editor_mode() ) {
			$content = '<div class="sky-editor-mode">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. </div>';
		}

		$this->add_render_attribute( 'excerpt', 'class', 'elementor-text-editor' );

		// Wrap the excerpt in a paragraph tag
		$content_html = sprintf(
			'<div %1$s>%2$s</div>',
			$this->get_render_attribute_string( 'excerpt' ),
			wp_kses_post( $content ) // Escape output for security
		);

		// Output the content
		echo $content_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}


	public function content_template() {}
}

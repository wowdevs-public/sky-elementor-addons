<?php

namespace Sky_Addons\Includes\DynamicTags\Tags\Text;

use Sky_Addons\Includes\Traits\UtilsTrait;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Dynamic_Tag_Post_Featured_Image extends \Elementor\Core\DynamicTags\Tag {

	use UtilsTrait;

	public function get_name(): string {
		return 'sky-addons-post-featured-image';
	}

	public function get_title(): string {
		return esc_html__( 'Post Featured Image', 'sky-elementor-addons' );
	}

	public function get_group(): array {
		return [ 'sky-addons-post' ];
	}

	public function get_categories(): array {
		return [
			\Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
		];
	}

	public function is_settings_required() {
		return true;
	}

	protected function register_controls(): void {
		$this->common_post_controls();
		$this->add_control(
			'sky_featured_image_data_type',
			[
				'label'   => esc_html__( 'Data Type', 'sky-elementor-addons' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'id'          => esc_html__( 'ID', 'sky-elementor-addons' ),
					'alt'         => esc_html__( 'Alt', 'sky-elementor-addons' ),
					'description' => esc_html__( 'Description', 'sky-elementor-addons' ),
					'title'       => esc_html__( 'Title', 'sky-elementor-addons' ),
					'caption'     => esc_html__( 'Caption', 'sky-elementor-addons' ),
				],
				'default' => 'alt',
			]
		);
	}

	protected function register_advanced_section() {
		$this->advanced_controls();
	}

	public function render(): void {
		$settings = $this->get_settings();
		$post_id  = $this->get_post_id();
		if ( ! $post_id ) {
			return;
		}
		$image_id = get_post_thumbnail_id( $post_id );
		if ( ! $image_id ) {
			return;
		}
		$output = '';
		switch ( $settings['sky_featured_image_data_type'] ) {
			case 'id':
				$output = $image_id;
				break;
			case 'alt':
				$output = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
				break;
			case 'description':
				$image_post = get_post( $image_id );
				$output     = $image_post ? $image_post->post_content : '';
				break;
			case 'title':
				$output = get_the_title( $image_id );
				break;
			case 'caption':
				$output = wp_get_attachment_caption( $image_id );
				break;
		}
		echo wp_kses_post( $this->apply_word_limit( $output ) );
	}
}

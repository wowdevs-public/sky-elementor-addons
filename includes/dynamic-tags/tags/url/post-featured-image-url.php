<?php

namespace Sky_Addons\Includes\DynamicTags\Tags\Url;

use Sky_Addons\Includes\Traits\UtilsTrait;
use Elementor\Core\DynamicTags\Data_Tag;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Dynamic_Tag_Post_Featured_Image_URL extends Data_Tag {
	use UtilsTrait;

	public function get_name(): string {
		return 'sky-addons-post-featured-image-url';
	}

	public function get_title(): string {
		return esc_html__( 'Featured Image URL', 'sky-elementor-addons' );
	}

	public function get_group(): array {
		return [ 'sky-addons-post' ];
	}

	public function get_categories(): array {
		return [
			\Elementor\Modules\DynamicTags\Module::URL_CATEGORY,
		];
	}

	public function is_settings_required() {
		return true;
	}

	protected function register_controls(): void {
		$this->common_post_controls();

		$this->add_control(
			'sky_image_size',
			[
				'label'   => esc_html__( 'Image Size', 'sky-elementor-addons' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => $this->get_image_sizes(),
				'default' => 'full',
			]
		);

		$this->fallback_control();
	}

	protected function register_advanced_section(): void {}

	private function get_image_sizes(): array {
		$sizes   = get_intermediate_image_sizes();
		$options = [
			'full' => esc_html__( 'Full', 'sky-elementor-addons' ),
		];

		foreach ( $sizes as $size ) {
			$options[ $size ] = ucfirst( str_replace( '_', ' ', $size ) );
		}

		return $options;
	}

	public function get_value( array $options = [] ) {
		$settings = $this->get_settings();
		$post_id  = $this->get_post_id();

		if ( ! $post_id ) {
			return '';
		}

		$thumbnail_id = get_post_thumbnail_id( $post_id );

		if ( ! $thumbnail_id ) {
			return '';
		}

		$size  = $settings['sky_image_size'] ?? 'full';
		$image = wp_get_attachment_image_src( $thumbnail_id, $size );

		if ( ! $image ) {
			return '';
		}

		return $image[0];
	}
}

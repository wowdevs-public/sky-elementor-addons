<?php

namespace Sky_Addons\Includes\DynamicTags\Tags\Url;

use Sky_Addons\Includes\Traits\UtilsTrait;
use Elementor\Core\DynamicTags\Data_Tag;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Dynamic_Tag_Post_Author_URL extends Data_Tag {
	use UtilsTrait;

	public function get_name(): string {
		return 'sky-addons-post-author-url';
	}

	public function get_title(): string {
		return esc_html__( 'Post Author URL', 'sky-elementor-addons' );
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
			'sky_url_type',
			[
				'label'   => esc_html__( 'URL Type', 'sky-elementor-addons' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'archive' => esc_html__( 'Author Archive', 'sky-elementor-addons' ),
					'website' => esc_html__( 'Author Website', 'sky-elementor-addons' ),
				],
				'default' => 'archive',
			]
		);

		$this->fallback_control();
	}

	protected function register_advanced_section(): void {}

	public function get_value( array $options = [] ) {
		$settings = $this->get_settings();
		$post_id  = $this->get_post_id();

		if ( ! $post_id ) {
			return '';
		}

		$post = get_post( $post_id );

		if ( ! $post ) {
			return '';
		}

		$url = '';

		if ( 'website' === $settings['sky_url_type'] ) {
			$url = get_the_author_meta( 'user_url', $post->post_author );
		} else {
			$url = get_author_posts_url( $post->post_author );
		}

		if ( empty( $url ) ) {
			return '';
		}

		return $url;
	}
}

<?php

namespace Sky_Addons\Includes\DynamicTags\Tags\Url;

use Sky_Addons\Includes\Traits\UtilsTrait;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Dynamic_Tag_Post_Comments_URL extends \Elementor\Core\DynamicTags\Tag {
	use UtilsTrait;

	public function get_name(): string {
		return 'sky-addons-post-comments-url';
	}

	public function get_title(): string {
		return esc_html__( 'Post Comments URL', 'sky-elementor-addons' );
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
					'comments' => esc_html__( 'Comments', 'sky-elementor-addons' ),
					'respond'  => esc_html__( 'Respond', 'sky-elementor-addons' ),
				],
				'default' => 'comments',
			]
		);

		$this->fallback_control();
	}

	protected function register_advanced_section(): void {}

	public function render(): void {
		$settings = $this->get_settings();
		$post_id  = $this->get_post_id();

		if ( ! $post_id ) {
			return;
		}

		$post = get_post( $post_id );

		if ( ! $post ) {
			return;
		}

		$url = '';

		if ( 'respond' === $settings['sky_url_type'] ) {
			$url = get_permalink( $post_id ) . '#respond';
		} else {
			$url = get_comments_link( $post_id );
		}

		if ( empty( $url ) ) {
			return;
		}

		echo esc_url( $url );
	}
}

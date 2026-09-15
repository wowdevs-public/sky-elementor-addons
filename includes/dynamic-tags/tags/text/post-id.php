<?php

namespace Sky_Addons\Includes\DynamicTags\Tags\Text;

use Sky_Addons\Includes\Traits\UtilsTrait;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Dynamic_Tag_Post_ID extends \Elementor\Core\DynamicTags\Tag {
	use UtilsTrait;

	public function get_name(): string {
		return 'sky-addons-post-id';
	}

	public function get_title(): string {
		return esc_html__( 'Post ID', 'sky-elementor-addons' );
	}

	public function get_group(): array {
		return [ 'sky-addons-post' ];
	}

	public function get_categories(): array {
		return [
			\Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
			\Elementor\Modules\DynamicTags\Module::NUMBER_CATEGORY,
		];
	}

	public function is_settings_required() {
		return false;
	}

	protected function register_controls(): void {
		$this->common_post_controls();
	}

	public function render(): void {
		$settings = $this->get_settings();

		$post_id = $this->get_post_id();

		if ( ! $post_id ) {
			return;
		}

		echo wp_kses_post( $post_id );
	}
}

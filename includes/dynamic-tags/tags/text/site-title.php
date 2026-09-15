<?php

namespace Sky_Addons\Includes\DynamicTags\Tags\Text;

use Sky_Addons\Includes\Traits\UtilsTrait;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Dynamic_Tag_Site_Title extends \Elementor\Core\DynamicTags\Tag {

	use UtilsTrait;

	public function get_name(): string {
		return 'sky-addons-site-title';
	}

	public function get_title(): string {
		return esc_html__( 'Site Title', 'sky-elementor-addons' );
	}

	public function get_group(): array {
		return [ 'sky-addons-site' ];
	}

	public function get_categories(): array {
		return [
			\Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
		];
	}

	protected function register_advanced_section() {
		$this->advanced_controls();
	}

	public function render(): void {
		echo wp_kses_post( $this->apply_word_limit( get_bloginfo( 'name' ) ) );
	}
}

<?php

namespace Sky_Addons\Includes\DynamicTags\Tags\Text;

use Sky_Addons\Includes\Traits\UtilsTrait;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Dynamic_Tag_Search_Results_Count extends \Elementor\Core\DynamicTags\Tag {
	use UtilsTrait;

	public function get_name(): string {
		return 'sky-addons-search-results-count';
	}

	public function get_title(): string {
		return esc_html__( 'Search Results Count', 'sky-elementor-addons' );
	}

	public function get_group(): array {
		return [ 'sky-addons-search' ];
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
		if ( ! is_search() ) {
			return;
		}

		global $wp_query;
		echo absint( $wp_query->found_posts );
	}
}

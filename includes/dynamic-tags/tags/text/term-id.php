<?php

namespace Sky_Addons\Includes\DynamicTags\Tags\Text;

use Sky_Addons\Includes\Traits\UtilsTrait;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Dynamic_Tag_Term_ID extends \Elementor\Core\DynamicTags\Tag {
	use UtilsTrait;

	public function get_name(): string {
		return 'sky-addons-term-id';
	}

	public function get_title(): string {
		return esc_html__( 'Term ID', 'sky-elementor-addons' );
	}

	public function get_group(): array {
		return [ 'sky-addons-term' ];
	}

	public function get_categories(): array {
		return [
			\Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
			\Elementor\Modules\DynamicTags\Module::NUMBER_CATEGORY,
		];
	}

	public function is_settings_required() {
		return true;
	}

	protected function register_controls(): void {
		$this->common_term_controls();
	}

	public function render(): void {
		echo esc_html( $this->get_term_id() );
	}
}

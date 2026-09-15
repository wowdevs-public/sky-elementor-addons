<?php

namespace Sky_Addons\Includes\DynamicTags\Tags\Woocommerce\Url;

use Sky_Addons\Includes\Traits\UtilsTrait;
use Elementor\Core\DynamicTags\Data_Tag;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Dynamic_Tag_Product_URL extends Data_Tag {

	use UtilsTrait;

	public function get_name(): string {
		return 'sky-addons-product-url';
	}

	public function get_title(): string {
		return esc_html__( 'Product URL', 'sky-elementor-addons' );
	}

	public function get_group(): array {
		return [ 'sky-addons-woocommerce' ];
	}

	public function get_categories(): array {
		return [
			\Elementor\Modules\DynamicTags\Module::URL_CATEGORY,
		];
	}

	public function is_settings_required(): bool {
		return true;
	}

	protected function register_controls(): void {
		$this->common_product_controls();
		$this->fallback_control();
	}

	protected function register_advanced_section(): void {}

	public function get_value( array $options = [] ) {
		$product_id = $this->get_product_id();

		if ( ! $product_id ) {
			return '';
		}

		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			return '';
		}

		return get_permalink( $product_id );
	}
}

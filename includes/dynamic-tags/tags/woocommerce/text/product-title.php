<?php

namespace Sky_Addons\Includes\DynamicTags\Tags\Woocommerce\Text;

use Sky_Addons\Includes\Traits\UtilsTrait;
use Elementor\Core\DynamicTags\Tag;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Dynamic_Tag_Product_Title extends Tag {
	use UtilsTrait;

	public function get_name(): string {
		return 'sky-addons-product-title';
	}

	public function get_title(): string {
		return esc_html__( 'Product Title', 'sky-elementor-addons' );
	}

	public function get_group(): array {
		return [ 'sky-addons-woocommerce' ];
	}

	public function get_categories(): array {
		return [
			\Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
		];
	}

	protected function register_controls(): void {
		$this->common_product_controls();
	}

	public function render() {
		$product_id = $this->get_product_id();

		if ( ! $product_id ) {
			return;
		}

		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			return;
		}

		echo esc_html( $product->get_title() );
	}
}

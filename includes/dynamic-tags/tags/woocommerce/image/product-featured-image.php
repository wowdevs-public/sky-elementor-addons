<?php

namespace Sky_Addons\Includes\DynamicTags\Tags\Woocommerce\Image;

use Sky_Addons\Includes\Traits\UtilsTrait;
use Elementor\Core\DynamicTags\Data_Tag;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Dynamic_Tag_Product_Featured_Image extends Data_Tag {

	use UtilsTrait;

	public function get_name(): string {
		return 'sky-addons-product-featured-image';
	}

	public function get_title(): string {
		return esc_html__( 'Product Featured Image', 'sky-elementor-addons' );
	}

	public function get_group(): array {
		return [ 'sky-addons-woocommerce' ];
	}

	public function get_categories(): array {
		return [
			\Elementor\Modules\DynamicTags\Module::IMAGE_CATEGORY,
			\Elementor\Modules\DynamicTags\Module::MEDIA_CATEGORY,
		];
	}

	public function is_settings_required(): bool {
		return true;
	}

	protected function register_controls(): void {
		$this->common_product_controls();

		$this->add_control(
			'fallback',
			[
				'label'       => esc_html__( 'Fallback', 'sky-elementor-addons' ),
				'type'        => \Elementor\Controls_Manager::MEDIA,
				'dynamic'     => [ 'active' => true ],
				'description' => esc_html__( 'This image will be used if the product has no featured image', 'sky-elementor-addons' ),
			]
		);
	}

	protected function register_advanced_section(): void {}

	public function get_value( array $options = [] ) {
		$product_id = $this->get_product_id();

		if ( ! $product_id ) {
			return $this->get_settings( 'fallback' );
		}

		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			return $this->get_settings( 'fallback' );
		}

		$thumbnail_id = $product->get_image_id();

		if ( $thumbnail_id ) {
			$image_data = [
				'id'  => $thumbnail_id,
				'url' => wp_get_attachment_image_src( $thumbnail_id, 'full' )[0],
			];
		} else {
			$image_data = $this->get_settings( 'fallback' );
		}

		return $image_data;
	}
}

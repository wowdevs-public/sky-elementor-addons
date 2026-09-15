<?php

namespace Sky_Addons\Includes\DynamicTags\Tags\Woocommerce\Url;

use Sky_Addons\Includes\Traits\UtilsTrait;
use Elementor\Core\DynamicTags\Data_Tag;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Dynamic_Tag_Product_Review_URL extends Data_Tag {

	use UtilsTrait;

	public function get_name(): string {
		return 'sky-addons-product-review-url';
	}

	public function get_title(): string {
		return esc_html__( 'Product Review URL', 'sky-elementor-addons' );
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

		$this->add_control(
			'sky_review_id',
			[
				'label'       => esc_html__( 'Review ID', 'sky-elementor-addons' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => '',
				'ai' => [
					'active' => false,
				],
				'description' => esc_html__( 'Leave empty to link to the review section. Enter a specific review ID to link to that review.', 'sky-elementor-addons' ),
			]
		);

		$this->fallback_control();
	}

	public function get_value( array $options = [] ) {
		$settings   = $this->get_settings();
		$product_id = $this->get_product_id();

		if ( ! $product_id ) {
			return '';
		}

		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			return '';
		}

		// Get the product URL
		$url = get_permalink( $product_id );

		// If a specific review ID is provided, link to that review
		if ( ! empty( $settings['sky_review_id'] ) ) {
			$review_id = absint( $settings['sky_review_id'] );
			if ( $review_id > 0 ) {
				// Verify the review belongs to this product
				$review = get_comment( $review_id );
				if ( $review && $review->comment_post_ID === $product_id ) {
					return $url . '#review-' . $review_id;
				}
			}
		}

		// Otherwise, link to the review section
		return $url . '#reviews';
	}
}

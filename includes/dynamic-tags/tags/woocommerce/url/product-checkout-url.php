<?php

namespace Sky_Addons\Includes\DynamicTags\Tags\Woocommerce\Url;

use Sky_Addons\Includes\Traits\UtilsTrait;
use Elementor\Core\DynamicTags\Data_Tag;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Dynamic_Tag_Product_Checkout_URL extends Data_Tag {

	use UtilsTrait;

	public function get_name(): string {
		return 'sky-addons-product-checkout-url';
	}

	public function get_title(): string {
		return esc_html__( 'Product Checkout URL', 'sky-elementor-addons' );
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
			'sky_checkout_type',
			[
				'label'       => esc_html__( 'Checkout Type', 'sky-elementor-addons' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'default'     => 'direct',
				'options' => [
					'direct'      => esc_html__( 'Direct to Checkout', 'sky-elementor-addons' ),
					'add_to_cart' => esc_html__( 'Add to Cart & Checkout', 'sky-elementor-addons' ),
				],
				'description' => esc_html__( 'Choose whether to go directly to checkout or add product to cart first', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'sky_quantity',
			[
				'label'       => esc_html__( 'Quantity', 'sky-elementor-addons' ),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'default'     => 1,
				'min'         => 1,
				'description' => esc_html__( 'Number of items to add to cart', 'sky-elementor-addons' ),
				'condition' => [
					'sky_checkout_type' => 'add_to_cart',
				],
			]
		);

		$this->fallback_control();
	}

	public function get_value( array $options = [] ) {
		$settings      = $this->get_settings();
		$checkout_type = $settings['sky_checkout_type'] ?? 'direct';

		// If direct checkout, just return checkout URL
		if ( 'direct' === $checkout_type ) {
			return wc_get_checkout_url();
		}

		// For add to cart & checkout, we need a product
		$product_id = $this->get_product_id();
		if ( ! $product_id ) {
			return wc_get_checkout_url();
		}

		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			return wc_get_checkout_url();
		}

		$quantity = (int) ( $settings['sky_quantity'] ?? 1 );

		// For variable or grouped products, redirect to single product page
		if ( $product->is_type( [ 'variable', 'grouped' ] ) ) {
			return get_permalink( $product_id );
		}

		// For simple products, add to cart and redirect to checkout
		return add_query_arg(
			[
				'add-to-cart' => $product_id,
				'quantity'    => $quantity,
				'checkout'    => '1',
			],
			wc_get_cart_url()
		);
	}
}

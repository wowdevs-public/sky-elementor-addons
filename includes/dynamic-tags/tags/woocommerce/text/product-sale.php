<?php

namespace Sky_Addons\Includes\DynamicTags\Tags\Woocommerce\Text;

use Sky_Addons\Includes\Traits\UtilsTrait;
use Elementor\Core\DynamicTags\Tag;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Dynamic_Tag_Product_Sale extends Tag {
	use UtilsTrait;

	public function get_name(): string {
		return 'sky-addons-product-sale';
	}

	public function get_title(): string {
		return esc_html__( 'Product Sale', 'sky-elementor-addons' );
	}

	public function get_group(): array {
		return [ 'sky-addons-woocommerce' ];
	}

	public function get_categories(): array {
		return [
			\Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
		];
	}

	public function is_settings_required(): bool {
		return true;
	}

	protected function register_controls(): void {
		$this->common_product_controls();

		$this->add_control(
			'sky_sale_type',
			[
				'label'   => esc_html__( 'Sale Type', 'sky-elementor-addons' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'discount_percentage',
				'options' => [
					'discount_percentage' => esc_html__( 'Discount Percentage', 'sky-elementor-addons' ),
					'discount_amount'     => esc_html__( 'Discount Amount', 'sky-elementor-addons' ),
					'custom_text'         => esc_html__( 'Custom Text', 'sky-elementor-addons' ),
				],
			]
		);

		$this->add_control(
			'sky_sale_text',
			[
				'label'   => esc_html__( 'Sale Text', 'sky-elementor-addons' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'Sale!', 'sky-elementor-addons' ),
				'condition' => [
					'sky_sale_type' => 'custom_text',
				],
				'ai' => [
					'active' => false,
				],
			]
		);

		$this->add_control(
			'sky_show_discount',
			[
				'label'   => esc_html__( 'Show Discount', 'sky-elementor-addons' ),
				'type'    => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
				'condition' => [
					'sky_sale_type' => 'custom_text',
				],
			]
		);

		$this->add_control(
			'sky_discount_position',
			[
				'label'   => esc_html__( 'Discount Position', 'sky-elementor-addons' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'after',
				'options' => [
					'before' => esc_html__( 'Before Text', 'sky-elementor-addons' ),
					'after'  => esc_html__( 'After Text', 'sky-elementor-addons' ),
				],
				'condition' => [
					'sky_sale_type'     => 'custom_text',
					'sky_show_discount' => 'yes',
				],
			]
		);
	}

	public function render() {
		$settings   = $this->get_settings();
		$product_id = $this->get_product_id();
		$sale_type  = $settings['sky_sale_type'] ?? 'discount_percentage';

		if ( ! $product_id ) {
			return;
		}

		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			return;
		}

		if ( ! $product->is_on_sale() ) {
			return;
		}

		switch ( $sale_type ) {
			case 'discount_percentage':
				if ( $product->is_type( 'variable' ) ) {
					$percentages = [];
					$variations  = $product->get_children();

					foreach ( $variations as $variation_id ) {
						$variation = wc_get_product( $variation_id );
						if ( $variation && $variation->is_on_sale() ) {
							$regular = (float) $variation->get_regular_price();
							$sale    = (float) $variation->get_sale_price();
							if ( $regular > 0 ) {
								$percentages[] = round( 100 - ( $sale / $regular * 100 ) );
							}
						}
					}
					if ( ! empty( $percentages ) ) {
						echo esc_html( min( $percentages ) . '%' );
					}
				} else {
					$regular_price = (float) $product->get_regular_price();
					$sale_price    = (float) $product->get_sale_price();

					if ( $regular_price > 0 ) {
						echo esc_html( round( 100 - ( $sale_price / $regular_price * 100 ) ) . '%' );
					}
				}
				break;

			case 'discount_amount':
				if ( $product->is_type( 'variable' ) ) {
					$discounts  = [];
					$variations = $product->get_children();

					foreach ( $variations as $variation_id ) {
						$variation = wc_get_product( $variation_id );
						if ( $variation && $variation->is_on_sale() ) {
							$regular = (float) $variation->get_regular_price();
							$sale    = (float) $variation->get_sale_price();
							if ( $regular > 0 ) {
								$discounts[] = $regular - $sale;
							}
						}
					}
					if ( ! empty( $discounts ) ) {
						echo wp_kses_post( wc_price( min( $discounts ) ) );
					}
				} else {
					$regular_price = (float) $product->get_regular_price();
					$sale_price    = (float) $product->get_sale_price();

					if ( $regular_price > 0 ) {
						echo wp_kses_post( wc_price( $regular_price - $sale_price ) );
					}
				}
				break;

			case 'custom_text':
				$sale_text = $settings['sky_sale_text'] ?? esc_html__( 'Sale!', 'sky-elementor-addons' );
				echo esc_html( $sale_text );
				break;
		}
	}
}

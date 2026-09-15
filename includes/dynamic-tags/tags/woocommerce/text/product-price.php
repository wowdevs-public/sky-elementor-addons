<?php

namespace Sky_Addons\Includes\DynamicTags\Tags\Woocommerce\Text;

use Sky_Addons\Includes\Traits\UtilsTrait;
use Elementor\Core\DynamicTags\Tag;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Dynamic_Tag_Product_Price extends Tag {
	use UtilsTrait;

	public function get_name(): string {
		return 'sky-addons-product-price';
	}

	public function get_title(): string {
		return esc_html__( 'Product Price', 'sky-elementor-addons' );
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
			'sky_price_type',
			[
				'label'   => esc_html__( 'Price Type', 'sky-elementor-addons' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'regular',
				'options' => [
					'regular'             => esc_html__( 'Regular Price', 'sky-elementor-addons' ),
					'sale'                => esc_html__( 'Sale Price', 'sky-elementor-addons' ),
					'both'                => esc_html__( 'Both Prices', 'sky-elementor-addons' ),
					'discount'            => esc_html__( 'Discount Amount', 'sky-elementor-addons' ),
					'discount_percentage' => esc_html__( 'Discount Percentage', 'sky-elementor-addons' ),
				],
			]
		);

		$this->add_control(
			'sky_regular_price_html_format',
			[
				'label'   => esc_html__( 'Regular Price HTML Format', 'sky-elementor-addons' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'strikethrough',
				'options' => [
					'none'          => esc_html__( 'None', 'sky-elementor-addons' ),
					'strikethrough' => esc_html__( 'Strikethrough', 'sky-elementor-addons' ),
					'underline'     => esc_html__( 'Underline', 'sky-elementor-addons' ),
				],
				'condition' => [
					'sky_price_type' => 'both',
				],
			]
		);

		$this->add_control(
			'sky_sale_price_html_format',
			[
				'label'   => esc_html__( 'Sale Price HTML Format', 'sky-elementor-addons' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'underline',
				'options' => [
					'none'          => esc_html__( 'None', 'sky-elementor-addons' ),
					'strikethrough' => esc_html__( 'Strikethrough', 'sky-elementor-addons' ),
					'underline'     => esc_html__( 'Underline', 'sky-elementor-addons' ),
				],
				'condition' => [
					'sky_price_type' => 'both',
				],
			]
		);

		$this->add_control(
			'sky_regular_price_prefix',
			[
				'label' => esc_html__( 'Regular Price Prefix', 'sky-elementor-addons' ),
				'type'  => \Elementor\Controls_Manager::TEXT,
				'condition' => [
					'sky_price_type' => [ 'regular', 'both' ],
				],
				'ai' => [
					'active' => false,
				],
			]
		);

		$this->add_control(
			'sky_sale_price_prefix',
			[
				'label' => esc_html__( 'Sale Price Prefix', 'sky-elementor-addons' ),
				'type'  => \Elementor\Controls_Manager::TEXT,
				'condition' => [
					'sky_price_type' => [ 'sale', 'both' ],
				],
				'ai' => [
					'active' => false,
				],
			]
		);

		$this->add_control(
			'sky_discount_prefix',
			[
				'label' => esc_html__( 'Discount Prefix', 'sky-elementor-addons' ),
				'type'  => \Elementor\Controls_Manager::TEXT,
				'condition' => [
					'sky_price_type' => [ 'discount', 'discount_percentage' ],
				],
				'ai' => [
					'active' => false,
				],
			]
		);

		$this->add_control(
			'sky_discount_suffix',
			[
				'label' => esc_html__( 'Discount Suffix', 'sky-elementor-addons' ),
				'type'  => \Elementor\Controls_Manager::TEXT,
				'condition' => [
					'sky_price_type' => [ 'discount', 'discount_percentage' ],
				],
				'ai' => [
					'active' => false,
				],
			]
		);

		$this->add_control(
			'sky_separator',
			[
				'label' => esc_html__( 'Separator', 'sky-elementor-addons' ),
				'type'  => \Elementor\Controls_Manager::TEXT,
				'condition' => [
					'sky_price_type' => 'both',
				],
				'ai' => [
					'active' => false,
				],
			]
		);
	}

	public function render() {
		$settings   = $this->get_settings();
		$product_id = $this->get_product_id();
		$price_type = $settings['sky_price_type'] ?? 'regular';

		if ( ! $product_id ) {
			return;
		}

		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			return;
		}

		$regular_price = $product->get_regular_price();
		$sale_price    = $product->get_sale_price();
		$price         = $product->get_price();

		// Return if no price
		if ( empty( $price ) ) {
			return;
		}

		// Format prices
		$regular_price_formatted = wc_price( $regular_price );
		$sale_price_formatted    = wc_price( $sale_price );
		$price_formatted         = wc_price( $price );

		// Calculate discount
		$discount_amount     = 0;
		$discount_percentage = 0;
		if ( $regular_price && $sale_price ) {
			$discount_amount     = $regular_price - $sale_price;
			$discount_percentage = round( ( $discount_amount / $regular_price ) * 100 );
		}

		switch ( $price_type ) {
			case 'regular':
				$regular_prefix = $settings['sky_regular_price_prefix'] ?? '';
				echo wp_kses_post( $regular_prefix . ' ' . $regular_price_formatted );
				break;

			case 'sale':
				if ( $sale_price ) {
					$sale_prefix = $settings['sky_sale_price_prefix'] ?? '';
					echo wp_kses_post( $sale_prefix . ' ' . $sale_price_formatted );
				} else {
					echo wp_kses_post( $price_formatted );
				}
				break;

			case 'both':
				$regular_prefix = $settings['sky_regular_price_prefix'] ?? '';
				$sale_prefix    = $settings['sky_sale_price_prefix'] ?? '';
				$separator      = $settings['sky_separator'] ?? ' - ';

				// Apply HTML formatting only when showing both prices
				$regular_format = $settings['sky_regular_price_html_format'] ?? '';
				$sale_format    = $settings['sky_sale_price_html_format'] ?? '';

				$formatted_regular = $regular_price_formatted;
				$formatted_sale    = $sale_price_formatted;

				// Format regular price
				switch ( $regular_format ) {
					case 'strikethrough':
						$formatted_regular = '<del>' . $regular_price_formatted . '</del>';
						break;
					case 'underline':
						$formatted_regular = '<ins>' . $regular_price_formatted . '</ins>';
						break;
				}

				// Format sale price
				switch ( $sale_format ) {
					case 'strikethrough':
						$formatted_sale = '<del>' . $sale_price_formatted . '</del>';
						break;
					case 'underline':
						$formatted_sale = '<ins>' . $sale_price_formatted . '</ins>';
						break;
				}

				echo wp_kses_post( $regular_prefix . ' ' . $formatted_regular . $separator . $sale_prefix . ' ' . $formatted_sale );
				break;

			case 'discount':
				if ( $discount_amount > 0 ) {
					echo wp_kses_post( wc_price( $discount_amount ) );
				}
				break;

			case 'discount_percentage':
				if ( $discount_percentage > 0 ) {
					echo wp_kses_post( $discount_percentage . '%' );
				}
				break;
		}
	}
}

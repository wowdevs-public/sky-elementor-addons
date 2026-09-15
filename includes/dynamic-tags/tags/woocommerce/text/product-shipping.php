<?php

namespace Sky_Addons\Includes\DynamicTags\Tags\Woocommerce\Text;

use Sky_Addons\Includes\Traits\UtilsTrait;
use Elementor\Core\DynamicTags\Tag;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Dynamic_Tag_Product_Shipping extends Tag {

	use UtilsTrait;

	public function get_name(): string {
		return 'sky-addons-product-shipping';
	}

	public function get_title(): string {
		return esc_html__( 'Product Shipping', 'sky-elementor-addons' );
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
			'sky_shipping_type',
			[
				'label'   => esc_html__( 'Shipping Type', 'sky-elementor-addons' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'weight',
				'options' => [
					'weight'     => esc_html__( 'Weight', 'sky-elementor-addons' ),
					'dimensions' => esc_html__( 'Dimensions', 'sky-elementor-addons' ),
					'class'      => esc_html__( 'Shipping Class', 'sky-elementor-addons' ),
				],
			]
		);

		$this->add_control(
			'sky_dimension_type',
			[
				'label'   => esc_html__( 'Dimension Type', 'sky-elementor-addons' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'all',
				'options' => [
					'all'    => esc_html__( 'All Dimensions', 'sky-elementor-addons' ),
					'length' => esc_html__( 'Length', 'sky-elementor-addons' ),
					'width'  => esc_html__( 'Width', 'sky-elementor-addons' ),
					'height' => esc_html__( 'Height', 'sky-elementor-addons' ),
				],
				'condition' => [
					'sky_shipping_type' => 'dimensions',
				],
			]
		);
	}

	public function render() {
		$product_id     = $this->get_product_id();
		$shipping_type  = $this->get_settings( 'sky_shipping_type' );
		$dimension_type = $this->get_settings( 'sky_dimension_type' );

		if ( ! $product_id ) {
			return;
		}

		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			return;
		}

		$shipping_info = '';

		switch ( $shipping_type ) {
			case 'weight':
				$weight = $product->get_weight();
				if ( ! empty( $weight ) ) {
					$shipping_info = wc_format_weight( $weight );
				}
				break;

			case 'dimensions':
				$length = $product->get_length();
				$width  = $product->get_width();
				$height = $product->get_height();

				switch ( $dimension_type ) {
					case 'length':
						if ( ! empty( $length ) ) {
							$shipping_info = wc_format_dimensions( [ $length ] );
						}
						break;

					case 'width':
						if ( ! empty( $width ) ) {
							$shipping_info = wc_format_dimensions( [ $width ] );
						}
						break;

					case 'height':
						if ( ! empty( $height ) ) {
							$shipping_info = wc_format_dimensions( [ $height ] );
						}
						break;

					case 'all':
						$shipping_info = wc_format_dimensions( [ $length, $width, $height ] );
						break;
				}
				break;

			case 'class':
				$shipping_class = $product->get_shipping_class();
				if ( ! empty( $shipping_class ) ) {
					$term          = get_term_by( 'slug', $shipping_class, 'product_shipping_class' );
					$shipping_info = $term ? $term->name : $shipping_class;
				}
				break;
		}

		echo wp_kses_post( $shipping_info );
	}
}

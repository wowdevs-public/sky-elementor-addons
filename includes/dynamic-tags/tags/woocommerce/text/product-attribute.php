<?php

namespace Sky_Addons\Includes\DynamicTags\Tags\Woocommerce\Text;

use Sky_Addons\Includes\Traits\UtilsTrait;
use Elementor\Core\DynamicTags\Tag;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! function_exists( 'wc_get_attribute_taxonomies' ) ) {
	return; // Exit if WooCommerce is not active
}

class Dynamic_Tag_Product_Attribute extends Tag {

	use UtilsTrait;

	public function get_name(): string {
		return 'sky-addons-product-attribute';
	}

	public function get_title(): string {
		return esc_html__( 'Product Attribute', 'sky-elementor-addons' );
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

		// Get all product attributes
		$attributes        = wc_get_attribute_taxonomies();
		$attribute_options = [];

		foreach ( $attributes as $attribute ) {
			$attribute_options[ $attribute->attribute_name ] = $attribute->attribute_label;
		}

		$this->add_control(
			'sky_attribute_name',
			[
				'label'   => esc_html__( 'Attribute', 'sky-elementor-addons' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => $attribute_options,
				'default' => ! empty( $attribute_options ) ? array_key_first( $attribute_options ) : '',
			]
		);

		$this->add_control(
			'sky_attribute_format',
			[
				'label'   => esc_html__( 'Format', 'sky-elementor-addons' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'value',
				'options' => [
					'name'  => esc_html__( 'Name', 'sky-elementor-addons' ),
					'slug'  => esc_html__( 'Slug', 'sky-elementor-addons' ),
					'value' => esc_html__( 'Value', 'sky-elementor-addons' ),
				],
			]
		);

		$this->add_control(
			'sky_attribute_separator',
			[
				'label'   => esc_html__( 'Separator', 'sky-elementor-addons' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => ', ',
				'condition' => [
					'sky_attribute_format' => 'value',
				],
			]
		);
	}

	public function render() {
		$settings            = $this->get_settings();
		$product_id          = $this->get_product_id();
		$attribute_name      = $settings['sky_attribute_name'] ?? '';
		$attribute_format    = $settings['sky_attribute_format'] ?? 'value';
		$attribute_separator = $settings['sky_attribute_separator'] ?? ', ';

		if ( ! $product_id || ! $attribute_name ) {
			return;
		}

		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			return;
		}

		$attribute_info = '';

		switch ( $attribute_format ) {
			case 'name':
				// Get attribute label from the options array we created earlier
				$attributes = wc_get_attribute_taxonomies();
				foreach ( $attributes as $attribute ) {
					if ( $attribute->attribute_name === $attribute_name ) {
						$attribute_info = $attribute->attribute_label;
						break;
					}
				}
				break;

			case 'slug':
				$attribute_info = $attribute_name;
				break;

			case 'value':
				$values = [];

				// Get attribute taxonomy name
				$taxonomy = wc_attribute_taxonomy_name( $attribute_name );

				// Get terms
				$terms = get_the_terms( $product->get_id(), $taxonomy );

				if ( $terms && ! is_wp_error( $terms ) ) {
					foreach ( $terms as $term ) {
						$values[] = $term->name;
					}
					$attribute_info = implode( $attribute_separator, $values );
				}
				break;
		}

		echo wp_kses_post( $attribute_info );
	}
}

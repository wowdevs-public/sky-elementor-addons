<?php

namespace Sky_Addons\Includes\DynamicTags\Tags\Woocommerce\Text;

use Sky_Addons\Includes\Traits\UtilsTrait;
use Elementor\Core\DynamicTags\Tag;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Dynamic_Tag_Product_Type extends Tag {

	use UtilsTrait;

	public function get_name(): string {
		return 'sky-addons-product-type';
	}

	public function get_title(): string {
		return esc_html__( 'Product Type', 'sky-elementor-addons' );
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
			'sky_type_format',
			[
				'label'   => esc_html__( 'Type Format', 'sky-elementor-addons' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'name',
				'options' => [
					'name'       => esc_html__( 'Name', 'sky-elementor-addons' ),
					'slug'       => esc_html__( 'Slug', 'sky-elementor-addons' ),
					'label'      => esc_html__( 'Label', 'sky-elementor-addons' ),
					'properties' => esc_html__( 'Properties', 'sky-elementor-addons' ),
				],
			]
		);

		$this->add_control(
			'sky_property_separator',
			[
				'label'   => esc_html__( 'Property Separator', 'sky-elementor-addons' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => ', ',
				'condition' => [
					'sky_type_format' => 'properties',
				],
			]
		);
	}

	public function render() {
		$product_id         = $this->get_product_id();
		$type_format        = $this->get_settings( 'sky_type_format' );
		$property_separator = $this->get_settings( 'sky_property_separator' );

		if ( ! $product_id ) {
			return;
		}

		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			return;
		}

		$product_type = $product->get_type();
		$type_info    = '';

		switch ( $type_format ) {
			case 'name':
				$type_info = ucfirst( $product_type );
				break;

			case 'slug':
				$type_info = $product_type;
				break;

			case 'label':
				$type_labels = [
					'simple'   => esc_html__( 'Simple Product', 'sky-elementor-addons' ),
					'grouped'  => esc_html__( 'Grouped Product', 'sky-elementor-addons' ),
					'external' => esc_html__( 'External/Affiliate Product', 'sky-elementor-addons' ),
					'variable' => esc_html__( 'Variable Product', 'sky-elementor-addons' ),
				];
				$type_info   = isset( $type_labels[ $product_type ] ) ? $type_labels[ $product_type ] : ucfirst( $product_type );
				break;

			case 'properties':
				$properties = [];

				// Add base type
				$type_labels  = [
					'simple'   => esc_html__( 'Simple', 'sky-elementor-addons' ),
					'grouped'  => esc_html__( 'Grouped', 'sky-elementor-addons' ),
					'external' => esc_html__( 'External', 'sky-elementor-addons' ),
					'variable' => esc_html__( 'Variable', 'sky-elementor-addons' ),
				];
				$properties[] = isset( $type_labels[ $product_type ] ) ? $type_labels[ $product_type ] : ucfirst( $product_type );

				// Add downloadable property
				if ( $product->is_downloadable() ) {
					$properties[] = esc_html__( 'Downloadable', 'sky-elementor-addons' );
				}

				// Add virtual property
				if ( $product->is_virtual() ) {
					$properties[] = esc_html__( 'Virtual', 'sky-elementor-addons' );
				}

				$type_info = implode( $property_separator, $properties );
				break;
		}

		echo wp_kses_post( $type_info );
	}
}

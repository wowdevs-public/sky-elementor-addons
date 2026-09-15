<?php

namespace Sky_Addons\Includes\DynamicTags\Tags\Woocommerce\Text;

use Sky_Addons\Includes\Traits\UtilsTrait;
use Elementor\Core\DynamicTags\Tag;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Dynamic_Tag_Product_Stock extends Tag {
	use UtilsTrait;

	public function get_name(): string {
		return 'sky-addons-product-stock';
	}

	public function get_title(): string {
		return esc_html__( 'Product Stock', 'sky-elementor-addons' );
	}

	public function get_group(): array {
		return [ 'sky-addons-woocommerce' ];
	}

	public function get_categories(): array {
		return [
			\Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
		];
	}

	public function is_settings_required() {
		return true;
	}

	protected function register_controls(): void {
		$this->common_product_controls();

		$this->add_control(
			'sky_stock_type',
			[
				'label'   => esc_html__( 'Stock Type', 'sky-elementor-addons' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'status',
				'options' => [
					'status'       => esc_html__( 'Status', 'sky-elementor-addons' ),
					'quantity'     => esc_html__( 'Quantity', 'sky-elementor-addons' ),
					'low_stock'    => esc_html__( 'Low Stock', 'sky-elementor-addons' ),
					'stock_status' => esc_html__( 'Stock Status Text', 'sky-elementor-addons' ),
				],
			]
		);

		$this->add_control(
			'sky_stock_text',
			[
				'label'   => esc_html__( 'Stock Text', 'sky-elementor-addons' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'In Stock', 'sky-elementor-addons' ),
				'condition' => [
					'sky_stock_type' => 'stock_status',
				],
				'ai' => [
					'active' => false,
				],
			]
		);

		$this->add_control(
			'sky_out_of_stock_text',
			[
				'label'   => esc_html__( 'Out of Stock Text', 'sky-elementor-addons' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'Out of Stock', 'sky-elementor-addons' ),
				'condition' => [
					'sky_stock_type' => 'stock_status',
				],
				'ai' => [
					'active' => false,
				],
			]
		);

		$this->add_control(
			'sky_backorder_text',
			[
				'label'   => esc_html__( 'Backorder Text', 'sky-elementor-addons' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'Available on Backorder', 'sky-elementor-addons' ),
				'condition' => [
					'sky_stock_type' => 'stock_status',
				],
				'ai' => [
					'active' => false,
				],
			]
		);
	}

	public function render() {
		$product_id = $this->get_product_id();
		$stock_type = $this->get_settings( 'sky_stock_type' );

		if ( ! $product_id ) {
			return;
		}

		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			return;
		}

		switch ( $stock_type ) {
			case 'status':
				echo esc_html( $product->get_stock_status() );
				break;

			case 'quantity':
				if ( $product->managing_stock() ) {
					echo esc_html( $product->get_stock_quantity() );
				}
				break;

			case 'low_stock':
				if ( $product->managing_stock() ) {
					$low_stock_amount = wc_get_low_stock_amount( $product );
					$stock_quantity   = $product->get_stock_quantity();

					if ( $stock_quantity <= $low_stock_amount ) {
						echo esc_html__( 'Yes', 'sky-elementor-addons' );
					} else {
						echo esc_html__( 'No', 'sky-elementor-addons' );
					}
				}
				break;

			case 'stock_status':
				$stock_status = $product->get_stock_status();
				$stock_text   = '';

				switch ( $stock_status ) {
					case 'instock':
						$stock_text = $this->get_settings( 'sky_stock_text' );
						break;
					case 'outofstock':
						$stock_text = $this->get_settings( 'sky_out_of_stock_text' );
						break;
					case 'onbackorder':
						$stock_text = $this->get_settings( 'sky_backorder_text' );
						break;
				}

				echo esc_html( $stock_text );
				break;
		}
	}
}

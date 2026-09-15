<?php

namespace Sky_Addons\Includes\DynamicTags\Tags\Woocommerce\Text;

use Sky_Addons\Includes\Traits\UtilsTrait;
use Elementor\Core\DynamicTags\Tag;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Dynamic_Tag_Product_Terms extends Tag {
	use UtilsTrait;

	public function get_name(): string {
		return 'sky-addons-product-terms';
	}

	public function get_title(): string {
		return esc_html__( 'Product Terms', 'sky-elementor-addons' );
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
			'sky_taxonomy',
			[
				'label'   => esc_html__( 'Taxonomy', 'sky-elementor-addons' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'product_cat',
				'options' => $this->get_taxonomies(),
			]
		);

		$this->add_control(
			'sky_limit',
			[
				'label'       => esc_html__( 'Limit', 'sky-elementor-addons' ),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'default'     => 0,
				'min'         => 0,
				'description' => esc_html__( '0 means no limit', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'sky_offset',
			[
				'label'   => esc_html__( 'Offset', 'sky-elementor-addons' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'default' => 0,
				'min'     => 0,
			]
		);

		$this->add_control(
			'sky_separator',
			[
				'label'   => esc_html__( 'Separator', 'sky-elementor-addons' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => ', ',
				'ai' => [
					'active' => false,
				],
			]
		);
	}

	private function get_taxonomies() {
		$taxonomies = get_object_taxonomies( 'product', 'objects' );
		$options    = [];

		foreach ( $taxonomies as $taxonomy ) {
			// Skip product shipping class
			if ( 'product_shipping_class' === $taxonomy->name ) {
				continue;
			}

			if ( $taxonomy->public ) {
				$options[ $taxonomy->name ] = $taxonomy->label;
			}
		}

		return $options;
	}

	public function render() {
		$settings   = $this->get_settings();
		$product_id = $this->get_product_id();
		$taxonomy   = $settings['sky_taxonomy'] ?? 'product_cat';
		$limit      = (int) ( $settings['sky_limit'] ?? 0 );
		$offset     = (int) ( $settings['sky_offset'] ?? 0 );
		$separator  = $settings['sky_separator'] ?? ', ';

		if ( ! $product_id ) {
			return;
		}

		$terms = get_the_terms( $product_id, $taxonomy );
		if ( ! $terms || is_wp_error( $terms ) ) {
			return;
		}

		// Apply offset and limit
		if ( $offset > 0 ) {
			$terms = array_slice( $terms, $offset );
		}
		if ( $limit > 0 ) {
			$terms = array_slice( $terms, 0, $limit );
		}

		$term_names = array_map(function ( $term ) {
			return $term->name;
		}, $terms);

		echo esc_html( implode( $separator, $term_names ) );
	}
}

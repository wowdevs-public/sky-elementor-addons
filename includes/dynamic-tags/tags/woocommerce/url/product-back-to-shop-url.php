<?php

namespace Sky_Addons\Includes\DynamicTags\Tags\Woocommerce\Url;

use Sky_Addons\Includes\Traits\UtilsTrait;
use Elementor\Core\DynamicTags\Data_Tag;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Dynamic_Tag_Product_Back_To_Shop_URL extends Data_Tag {

	use UtilsTrait;

	public function get_name(): string {
		return 'sky-addons-product-back-to-shop-url';
	}

	public function get_title(): string {
		return esc_html__( 'Back to Shop URL', 'sky-elementor-addons' );
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
		return false;
	}

	protected function register_controls(): void {
		$this->add_control(
			'sky_shop_page',
			[
				'label'       => esc_html__( 'Shop Page', 'sky-elementor-addons' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'default'     => 'default',
				'options' => [
					'default' => esc_html__( 'Default Shop Page', 'sky-elementor-addons' ),
					'custom'  => esc_html__( 'Custom Page', 'sky-elementor-addons' ),
				],
				'description' => esc_html__( 'Choose which page to link to', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'sky_custom_shop_url',
			[
				'label'       => esc_html__( 'Custom Shop URL', 'sky-elementor-addons' ),
				'type'        => \Elementor\Controls_Manager::URL,
				'placeholder' => esc_html__( 'https://your-shop-url.com', 'sky-elementor-addons' ),
				'condition' => [
					'sky_shop_page' => 'custom',
				],
				'description' => esc_html__( 'Enter a custom URL to return to', 'sky-elementor-addons' ),
			]
		);

		$this->fallback_control();
	}

	public function get_value( array $options = [] ) {
		$settings  = $this->get_settings();
		$shop_page = $settings['sky_shop_page'] ?? 'default';

		if ( 'custom' === $shop_page && ! empty( $settings['sky_custom_shop_url']['url'] ) ) {
			return $settings['sky_custom_shop_url']['url'];
		}

		// Get the default shop page URL
		$shop_url = wc_get_page_permalink( 'shop' );

		// If no shop page is set, fallback to home URL
		if ( ! $shop_url ) {
			$shop_url = home_url();
		}

		return $shop_url;
	}
}

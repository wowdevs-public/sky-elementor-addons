<?php

namespace Sky_Addons\Includes\DynamicTags\Tags\Text;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Dynamic_Tag_Shortcode extends \Elementor\Core\DynamicTags\Tag {

	public function get_name(): string {
		return 'sky-addons-shortcode';
	}

	public function get_title(): string {
		return esc_html__( 'Shortcode', 'sky-elementor-addons' );
	}

	public function get_group(): array {
		return [ 'sky-addons-site' ];
	}

	public function get_atomic_group(): string {
		return 'sky-addons-site';
	}

	public function get_categories(): array {
		return [
			\Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
			\Elementor\Modules\DynamicTags\Module::URL_CATEGORY,
			\Elementor\Modules\DynamicTags\Module::POST_META_CATEGORY,
		];
	}

	protected function register_controls(): void {
		$this->add_control(
			'sky_shortcode',
			[
				'label'   => esc_html__( 'Shortcode', 'sky-elementor-addons' ),
				'type'    => \Elementor\Controls_Manager::TEXTAREA,
				'default' => '',
				'ai' => [
					'active' => false,
				],
			]
		);
	}

	public function render(): void {
		$settings = $this->get_settings();

		if ( empty( $settings['sky_shortcode'] ) ) {
			return;
		}

		$shortcode_string = trim( $settings['sky_shortcode'] );
		// Auto-wrap in brackets if not present
		if ( strpos( $shortcode_string, '[' ) !== 0 ) {
			$shortcode_string = '[' . $shortcode_string . ']';
		}
		// Handle escaped quotes
		$shortcode_string = str_replace( '"', '"', $shortcode_string );
		$shortcode_string = str_replace( "'", "'", $shortcode_string );

		$value = do_shortcode( $shortcode_string );

		echo wp_kses_post( $value );
	}
}

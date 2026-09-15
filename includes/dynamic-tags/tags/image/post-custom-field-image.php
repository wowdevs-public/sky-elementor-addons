<?php

namespace Sky_Addons\Includes\DynamicTags\Tags\Image;

use Sky_Addons\Includes\Traits\UtilsTrait;
use Elementor\Core\DynamicTags\Data_Tag;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Dynamic_Tag_Post_Custom_Field_Image extends Data_Tag {
	use UtilsTrait;

	public function get_name(): string {
		return 'sky-addons-post-custom-field-image';
	}

	public function get_title(): string {
		return esc_html__( 'Custom Field', 'sky-elementor-addons' );
	}

	public function get_group(): array {
		return [ 'sky-addons-post' ];
	}

	public function get_categories(): array {
		return [
			\Elementor\Modules\DynamicTags\Module::IMAGE_CATEGORY,
			\Elementor\Modules\DynamicTags\Module::MEDIA_CATEGORY,
		];
	}

	public function is_settings_required() {
		return true;
	}

	protected function register_controls(): void {
		$this->common_post_controls();
		$this->add_control(
			'sky_custom_field_key',
			[
				'label'       => esc_html__( 'Meta Key', 'sky-elementor-addons' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'options'     => $this->get_custom_keys_array(),
				'placeholder' => esc_html__( 'Select a meta key', 'sky-elementor-addons' ),
				'allow_clear' => true,
			]
		);

		$this->add_control(
			'sky_custom_field_custom_key',
			[
				'label'       => esc_html__( 'Custom Key', 'sky-elementor-addons' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'Enter a custom key', 'sky-elementor-addons' ),
				'condition' => [
					'sky_custom_field_key' => '',
				],
				'ai' => [
					'active' => false,
				],
			]
		);

		$this->add_control(
			'fallback',
			[
				'label' => esc_html__( 'Fallback', 'sky-elementor-addons' ),
				'type'  => \Elementor\Controls_Manager::MEDIA,
			]
		);
	}

	protected function register_advanced_section(): void {}

	private function get_custom_keys_array(): array {
		$custom_keys = get_post_custom_keys();
		$options     = [
			'' => esc_html__( 'Select...', 'sky-elementor-addons' ),
		];

		if ( ! empty( $custom_keys ) ) {
			foreach ( $custom_keys as $custom_key ) {
				if ( '_' !== substr( $custom_key, 0, 1 ) ) {
					$options[ $custom_key ] = $custom_key;
				}
			}
		}

		return $options;
	}

	public function get_value( array $options = [] ) {
		$post_id = $this->get_post_id();

		if ( ! $post_id ) {
			return $this->get_settings( 'fallback' );
		}

		$key = $this->get_settings( 'sky_custom_field_key' );

		// If no meta key is selected, use custom key
		if ( empty( $key ) ) {
			$key = $this->get_settings( 'sky_custom_field_custom_key' );
		}

		if ( ! $key ) {
			return $this->get_settings( 'fallback' );
		}

		$value = get_post_meta( $post_id, $key, true );

		if ( ! $value ) {
			return $this->get_settings( 'fallback' );
		}

		// Handle different types of image values
		if ( is_numeric( $value ) ) {
			// If value is an attachment ID
			$image_url = wp_get_attachment_image_url( $value, 'full' );
			if ( $image_url ) {
				return [
					'id'  => $value,
					'url' => $image_url,
				];
			}
		} elseif ( is_string( $value ) ) {
			// If value is a URL
			if ( filter_var( $value, FILTER_VALIDATE_URL ) ) {
				return [
					'id'  => 0,
					'url' => $value,
				];
			}
		}

		return $this->get_settings( 'fallback' );
	}
}

<?php

namespace Sky_Addons\Includes\DynamicTags\Tags\Text;

use Sky_Addons\Includes\Traits\UtilsTrait;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Dynamic_Tag_Post_Custom_Field extends \Elementor\Core\DynamicTags\Tag {
	use UtilsTrait;

	public function get_name(): string {
		return 'sky-addons-post-custom-field';
	}

	public function get_title(): string {
		return esc_html__( 'Custom Field', 'sky-elementor-addons' );
	}

	public function get_group(): array {
		return [ 'sky-addons-post' ];
	}

	public function get_categories(): array {
		return [
			\Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
			\Elementor\Modules\DynamicTags\Module::URL_CATEGORY,
			\Elementor\Modules\DynamicTags\Module::POST_META_CATEGORY,
		];
	}

	public function is_settings_required() {
		return true;
	}

	public function get_editor_config() {
		$config = parent::get_editor_config();

		if ( ! $this->is_atomic_dynamic_tags_context() ) {
			return $config;
		}

		$controls                 = is_array( $config['controls'] ?? null ) ? $config['controls'] : [];
		$allowed_setting_controls = [
			'settings',
			'sky_meta_key',
			'sky_custom_meta_key',
			'advanced',
			'sky_word_limit',
			'before',
			'after',
			'fallback',
		];

		$filtered_controls = [];

		foreach ( $controls as $name => $control ) {
			if ( in_array( $name, $allowed_setting_controls, true ) ) {
				$filtered_controls[ $name ] = $control;
			}
		}

		$config['controls']          = $filtered_controls;
		$config['settings_required'] = ! empty( $filtered_controls );

		return $config;
	}

	protected function register_controls(): void {
		$this->common_post_controls();

		$this->add_control(
			'sky_meta_key',
			[
				'label'   => esc_html__( 'Field Key', 'sky-elementor-addons' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => $this->get_custom_keys_array(),
			]
		);

		$this->add_control(
			'sky_custom_meta_key',
			[
				'label'       => esc_html__( 'Custom Field Key', 'sky-elementor-addons' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'label_block' => true,
				'ai' => [
					'active' => false,
				],
			]
		);
	}

	protected function register_advanced_section() {
		$this->advanced_controls();
	}

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

	public function render(): void {
		$settings = $this->get_settings();
		$value    = '';

		// Get post ID based on settings
		$post_id = $this->get_post_id();

		// Get the meta key
		$meta_key = '';

		if ( ! empty( $settings['sky_meta_key'] ) ) {
			$meta_key = $settings['sky_meta_key'];
		} elseif ( ! empty( $settings['sky_custom_meta_key'] ) ) {
			$meta_key = $settings['sky_custom_meta_key'];
		}

		// If we have both post ID and meta key, get the value
		if ( $post_id && $meta_key ) {
			$value = get_post_meta( $post_id, $meta_key, true );
		}

		echo wp_kses_post( $this->apply_word_limit( $value ) );
	}
}

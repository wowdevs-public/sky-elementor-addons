<?php

namespace Sky_Addons\Includes\DynamicTags\Tags\Url;

use Sky_Addons\Includes\Traits\UtilsTrait;
use Sky_Addons\Includes\Controls\SelectInput\Dynamic_Select;
use Elementor\Core\DynamicTags\Data_Tag;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Dynamic_Tag_Attachment_URL extends Data_Tag {

	use UtilsTrait;

	public function get_name(): string {
		return 'sky-addons-attachment-url';
	}

	public function get_title(): string {
		return esc_html__( 'Attachment URL', 'sky-elementor-addons' );
	}

	public function get_group(): array {
		return [ 'sky-addons-media' ];
	}

	public function get_categories(): array {
		return [
			\Elementor\Modules\DynamicTags\Module::URL_CATEGORY,
		];
	}

	public function is_settings_required() {
		return true;
	}

	protected function register_controls(): void {
		$this->add_control(
			'sky_attachment_id',
			[
				'label'       => esc_html__( 'Attachment', 'sky-elementor-addons' ),
				'type'        => Dynamic_Select::TYPE,
				'label_block' => true,
				'placeholder' => esc_html__( 'Select Attachment', 'sky-elementor-addons' ),
				'query_args' => [
					'query'     => '_related_post_type',
					'post_type' => 'attachment',
				],
			]
		);

		$this->add_control(
			'sky_image_size',
			[
				'label'       => esc_html__( 'Image Size', 'sky-elementor-addons' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'options'     => $this->get_image_sizes(),
				'default'     => 'full',
				'description' => esc_html__( 'This option is only available for images.', 'sky-elementor-addons' ),
				'condition' => [
					'sky_attachment_id!' => '',
				],
			]
		);

		$this->fallback_control();
	}

	protected function register_advanced_section(): void {}

	private function get_image_sizes(): array {
		$sizes   = get_intermediate_image_sizes();
		$options = [
			'full' => esc_html__( 'Full', 'sky-elementor-addons' ),
		];

		foreach ( $sizes as $size ) {
			$options[ $size ] = ucfirst( str_replace( '_', ' ', $size ) );
		}

		return $options;
	}

	public function get_value( array $options = [] ) {
		$settings      = $this->get_settings();
		$attachment_id = $settings['sky_attachment_id'] ?? 0;

		if ( ! $attachment_id ) {
			return '';
		}

		$attachment = get_post( $attachment_id );
		if ( ! $attachment ) {
			return '';
		}

		$mime_type = get_post_mime_type( $attachment );

		// Handle different types of attachments
		if ( strpos( $mime_type, 'image/' ) === 0 ) {
			// For images, use wp_get_attachment_image_src
			$size  = $settings['sky_image_size'] ?? 'full';
			$image = wp_get_attachment_image_src( $attachment_id, $size );
			if ( $image ) {
				return $image[0];
			}
		} else {
			// For other types (audio, video, pdf, etc.), use wp_get_attachment_url
			return wp_get_attachment_url( $attachment_id );
		}

		return '';
	}
}

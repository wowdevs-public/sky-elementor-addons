<?php

namespace Sky_Addons\Includes;

use WPML_Elementor_Module_Without_Items;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPML_Image_Compare
 */
class WPML_Image_Compare extends WPML_Module_Without_Items {

	/**
	 * @return string
	 */
	public function get_name() {
		return 'sky-image-compare';
	}

	/**
	 * @return array
	 */
	public function get_fields() {
		return [
			'before_text' => [
				'title'       => esc_html__( 'Image Compare: Before Text', 'sky-elementor-addons' ),
				'editor_type' => 'LINE',
			],
			'after_text'  => [
				'title'       => esc_html__( 'Image Compare: After Text', 'sky-elementor-addons' ),
				'editor_type' => 'LINE',
			],
			'title'       => [
				'title'       => esc_html__( 'Image Compare: Title', 'sky-elementor-addons' ),
				'editor_type' => 'LINE',
			],
			'content'     => [
				'title'       => esc_html__( 'Image Compare: Content', 'sky-elementor-addons' ),
				'editor_type' => 'AREA',
			],
		];
	}

	/**
	 * @param string $field
	 *
	 * @return string
	 */
	protected function get_title( $field ) {
		switch ( $field ) {
			case 'before_text':
				return esc_html__( 'Image Compare: Before Text', 'sky-elementor-addons' );
			case 'after_text':
				return esc_html__( 'Image Compare: After Text', 'sky-elementor-addons' );
			case 'title':
				return esc_html__( 'Image Compare: Title', 'sky-elementor-addons' );
			case 'content':
				return esc_html__( 'Image Compare: Content', 'sky-elementor-addons' );
			default:
				return '';
		}
	}

	/**
	 * @param string $field
	 *
	 * @return string
	 */
	protected function get_editor_type( $field ) {
		return isset( $this->get_fields()[ $field ]['editor_type'] ) ? $this->get_fields()[ $field ]['editor_type'] : 'LINE';
	}
}

<?php
namespace Sky_Addons\Includes;

/**
 * Class WPML_Logo_Carousel
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPML_Logo_Carousel extends WPML_Module_With_Items {

	/**
	 * @return string
	 */
	public function get_items_field() {
		return 'logo_list';
	}

	/**
	 * @return array
	 */
	public function get_fields() {
		return [
			'link' => [
				'field'       => 'url',
				'type'        => esc_html__( 'Logo Carousel: Link', 'sky-elementor-addons' ),
				'editor_type' => 'LINK',
			],
			'brand_name',
			'brand_text',
		];
	}

	/**
	 * @param string $field
	 * @return string
	 */
	protected function get_title( $field ) {
		switch ( $field ) {
			case 'link':
				return esc_html__( 'Logo Carousel: Link', 'sky-elementor-addons' );
			case 'brand_name':
				return esc_html__( 'Logo Carousel: Brand Name', 'sky-elementor-addons' );
			case 'brand_text':
				return esc_html__( 'Logo Carousel: Brand Text', 'sky-elementor-addons' );
			default:
				return '';
		}
	}

	/**
	 * @param string $field
	 * @return string
	 */
	protected function get_editor_type( $field ) {
		switch ( $field ) {
			case 'brand_name':
				return 'LINE';
			case 'brand_text':
				return 'AREA';
			default:
				return 'LINE';
		}
	}
}

<?php
namespace Sky_Addons\Includes;

/**
 * Class WPML_Content_Switcher
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPML_Content_Switcher extends WPML_Module_With_Items {

	/**
	 * @return string
	 */
	public function get_items_field() {
		return 'switcher_list';
	}

	/**
	 * @return array
	 */
	public function get_fields() {
		return [ 'title', 'custom_text' ];
	}

	/**
	 * @param string $field
	 * @return string
	 */
	protected function get_title( $field ) {
		switch ( $field ) {
			case 'title':
				return esc_html__( 'Content Switcher: Title', 'sky-elementor-addons' );
			case 'custom_text':
				return esc_html__( 'Content Switcher: Content', 'sky-elementor-addons' );
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
			case 'title':
				return 'LINE';
			case 'custom_text':
				return 'VISUAL';
			default:
				return '';
		}
	}
}

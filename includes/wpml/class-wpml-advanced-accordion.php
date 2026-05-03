<?php
namespace Sky_Addons\Includes;

/**
 * Class WPML_Advanced_Accordion
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class WPML_Advanced_Accordion extends WPML_Module_With_Items {

	/**
	 * @return string
	 */
	public function get_items_field() {
		return 'acc_list';
	}

	/**
	 * @return array
	 */
	public function get_fields() {
		return [ 'title', 'custom_content' ];
	}

	/**
	 * @param string $field
	 * @return string
	 */
	protected function get_title( $field ) {
		switch ( $field ) {

			case 'title':
				return esc_html__( 'Advanced Accordion: Title', 'sky-elementor-addons' );

			case 'custom_content':
				return esc_html__( 'Advanced Accordion: Content', 'sky-elementor-addons' );

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

			case 'custom_content':
				return 'VISUAL';

			default:
				return '';
		}
	}
}

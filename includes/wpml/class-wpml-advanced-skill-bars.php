<?php
namespace Sky_Addons\Includes;

/**
 * Class WPML_Advanced_Skill_Bars
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPML_Advanced_Skill_Bars extends WPML_Module_With_Items {

	/**
	 * @return string
	 */
	public function get_items_field() {
		return 'skill_list';
	}

	/**
	 * @return array
	 */
	public function get_fields() {
		return [ 'skill_name' ];
	}

	/**
	 * @param string $field
	 * @return string
	 */
	protected function get_title( $field ) {
		switch ( $field ) {
			case 'skill_name':
				return esc_html__( 'Advanced Skill Bars: Skill Name', 'sky-elementor-addons' );
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
			case 'skill_name':
				return 'LINE';
			default:
				return '';
		}
	}
}

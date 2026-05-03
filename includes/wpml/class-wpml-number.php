<?php
namespace Sky_Addons\Includes;

/**
 * Class WPML_Number
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPML_Number extends WPML_Module_Without_Items {

	/**
	 * @return array
	 */
	public function get_fields() {
		return [
			'text' => [
				'title'       => esc_html__( 'Number: Text', 'sky-elementor-addons' ),
				'editor_type' => 'LINE',
			],
		];
	}

	/**
	 * @param string $field
	 * @return string
	 */
	protected function get_title( $field ) {
		return isset( $this->get_fields()[ $field ]['title'] ) ? $this->get_fields()[ $field ]['title'] : '';
	}
}

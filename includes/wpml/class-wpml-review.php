<?php
namespace Sky_Addons\Includes;

/**
 * Class WPML_Review
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPML_Review extends WPML_Module_Without_Items {

	/**
	 * @return array
	 */
	public function get_fields() {
		return [
			'name'        => [
				'title'       => esc_html__( 'Review: Name', 'sky-elementor-addons' ),
				'editor_type' => 'LINE',
			],
			'designation' => [
				'title'       => esc_html__( 'Review: Designation', 'sky-elementor-addons' ),
				'editor_type' => 'LINE',
			],
			'review'      => [
				'title'       => esc_html__( 'Review: Content', 'sky-elementor-addons' ),
				'editor_type' => 'AREA',
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

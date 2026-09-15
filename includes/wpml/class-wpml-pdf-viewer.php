<?php

namespace Sky_Addons\Includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPML_PDF_Viewer
 */
class WPML_PDF_Viewer extends WPML_Module_Without_Items {

	/**
	 * @return string
	 */
	public function get_name() {
		return 'sky-pdf-viewer';
	}

	/**
	 * @return array
	 */
	public function get_fields() {
		return [
			'title'       => [
				'title'       => esc_html__( 'PDF Viewer: Title', 'sky-elementor-addons' ),
				'editor_type' => 'LINE',
			],
			'badge_text'  => [
				'title'       => esc_html__( 'PDF Viewer: Badge Text', 'sky-elementor-addons' ),
				'editor_type' => 'LINE',
			],
			'button_text' => [
				'title'       => esc_html__( 'PDF Viewer: Button Text', 'sky-elementor-addons' ),
				'editor_type' => 'LINE',
			],
		];
	}

	/**
	 * @param string $field
	 *
	 * @return string
	 */
	protected function get_title( $field ) {
		return isset( $this->get_fields()[ $field ]['title'] ) ? $this->get_fields()[ $field ]['title'] : '';
	}
}

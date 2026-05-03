<?php

namespace Sky_Addons\Includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Only define the class if WPML is available
if ( ! class_exists( 'WPML_Elementor_Module_Without_Items' ) ) {
	return;
}

use WPML_Elementor_Module_Without_Items;

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
			'title',
			'button_text',
		];
	}

	/**
	 * @param string $field
	 *
	 * @return string
	 */
	protected function get_title( $field ) {
		switch ( $field ) {
			case 'title':
				return esc_html__( 'PDF Viewer: Title', 'sky-elementor-addons' );
			case 'button_text':
				return esc_html__( 'PDF Viewer: Button Text', 'sky-elementor-addons' );
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
		switch ( $field ) {
			case 'title':
			case 'button_text':
				return 'LINE';
			default:
				return '';
		}
	}
}

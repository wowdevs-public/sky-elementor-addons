<?php
namespace Sky_Addons\Includes;

/**
 * Class WPML_Info_Box
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPML_Info_Box extends WPML_Module_Without_Items {

	/**
	 * @return array
	 */
	public function get_fields() {
		return [
			'title'       => [
				'title'       => esc_html__( 'Info Box: Title', 'sky-elementor-addons' ),
				'editor_type' => 'AREA',
			],
			'desc'        => [
				'title'       => esc_html__( 'Info Box: Description', 'sky-elementor-addons' ),
				'editor_type' => 'AREA',
			],
			'button_text' => [
				'title'       => esc_html__( 'Info Box: Button Text', 'sky-elementor-addons' ),
				'editor_type' => 'LINE',
			],
			'link'        => [
				'field'       => 'url',
				'title'       => esc_html__( 'Info Box: Link', 'sky-elementor-addons' ),
				'editor_type' => 'LINK',
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

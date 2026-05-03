<?php
namespace Sky_Addons\Includes;

/**
 * Class WPML_Step_Flow
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPML_Step_Flow extends WPML_Module_Without_Items {

	/**
	 * @return array
	 */
	public function get_fields() {
		return [
			'title'      => [
				'title'       => esc_html__( 'Step Flow: Title', 'sky-elementor-addons' ),
				'editor_type' => 'LINE',
			],
			'desc'       => [
				'title'       => esc_html__( 'Step Flow: Description', 'sky-elementor-addons' ),
				'editor_type' => 'AREA',
			],
			'badge_text' => [
				'title'       => esc_html__( 'Step Flow: Badge Text', 'sky-elementor-addons' ),
				'editor_type' => 'LINE',
			],
			'link'       => [
				'title'       => esc_html__( 'Step Flow: Link', 'sky-elementor-addons' ),
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

<?php
namespace Sky_Addons\Includes;

/**
 * Class WPML_Card
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPML_Card extends WPML_Module_Without_Items {

	/**
	 * @return array
	 */
	public function get_fields() {
		return [
			'title'       => [
				'title'       => esc_html__( 'Card: Title', 'sky-elementor-addons' ),
				'editor_type' => 'AREA',
			],
			'sub_title'   => [
				'title'       => esc_html__( 'Card: Sub Title', 'sky-elementor-addons' ),
				'editor_type' => 'LINE',
			],
			'description' => [
				'title'       => esc_html__( 'Card: Description', 'sky-elementor-addons' ),
				'editor_type' => 'AREA',
			],
			'badge_text'  => [
				'title'       => esc_html__( 'Card: Badge Text', 'sky-elementor-addons' ),
				'editor_type' => 'LINE',
			],
			'button_text' => [
				'title'       => esc_html__( 'Card: Button Text', 'sky-elementor-addons' ),
				'editor_type' => 'LINE',
			],
			'link'        => [
				'field'       => 'url',
				'title'       => esc_html__( 'Card: Link', 'sky-elementor-addons' ),
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

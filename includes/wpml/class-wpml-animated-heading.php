<?php
namespace Sky_Addons\Includes;

/**
 * Class WPML_Animated_Heading
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPML_Animated_Heading extends WPML_Module_Without_Items {

	/**
	 * @return array
	 */
	public function get_fields() {
		return [
			'title_prefix' => [
				'title'       => esc_html__( 'Animated Heading: Title Prefix', 'sky-elementor-addons' ),
				'editor_type' => 'AREA',
			],
			'title'        => [
				'title'       => esc_html__( 'Animated Heading: Main Title', 'sky-elementor-addons' ),
				'editor_type' => 'AREA',
			],
			'title_suffix' => [
				'title'       => esc_html__( 'Animated Heading: Title Suffix', 'sky-elementor-addons' ),
				'editor_type' => 'AREA',
			],
			'link'         => [
				'title'       => esc_html__( 'Animated Heading: Link', 'sky-elementor-addons' ),
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

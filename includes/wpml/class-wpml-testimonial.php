<?php
namespace Sky_Addons\Includes;

/**
 * Class WPML_Testimonial
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPML_Testimonial extends WPML_Module_Without_Items {

	/**
	 * @return array
	 */
	public function get_fields() {
		return [
			'testimonial_text' => [
				'title'       => esc_html__( 'Testimonial: Content', 'sky-elementor-addons' ),
				'editor_type' => 'VISUAL',
			],
			'testimonial_name' => [
				'title'       => esc_html__( 'Testimonial: Name', 'sky-elementor-addons' ),
				'editor_type' => 'LINE',
			],
			'testimonial_job'  => [
				'title'       => esc_html__( 'Testimonial: Job Title', 'sky-elementor-addons' ),
				'editor_type' => 'LINE',
			],
			'link'             => [
				'title'       => esc_html__( 'Testimonial: Link', 'sky-elementor-addons' ),
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

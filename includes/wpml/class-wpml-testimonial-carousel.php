<?php

namespace Sky_Addons\Includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles WPML translation integration for the Testimonial Carousel widget in Sky Elementor Addons.
 * Extends WPML_Module_With_Items to support translation of testimonial items and fields.
 */
class WPML_Testimonial_Carousel extends WPML_Module_With_Items {
	/**
	 * Returns the widget name for WPML registration.
	 * Used to identify the widget for translation mapping.
	 *
	 * @return string Widget name for WPML.
	 */
	public function get_name() {
		return 'sky-testimonial-carousel';
	}

	/**
	 * Returns the field name containing the testimonial items.
	 *
	 * @return string The field name for testimonial items.
	 */
	public function get_items_field() {
		return 'testimonial_list';
	}

	/**
	 * Returns the translatable fields for the Testimonial Carousel widget.
	 *
	 * @return array List of translatable fields and their editor types.
	 */
	public function get_fields() {
		return [
			'testimonial_text' => [
				'title'       => esc_html__( 'Testimonial Carousel: Content', 'sky-elementor-addons' ),
				'editor_type' => 'AREA',
			],
			'testimonial_name' => [
				'title'       => esc_html__( 'Testimonial Carousel: Name', 'sky-elementor-addons' ),
				'editor_type' => 'LINE',
			],
			'testimonial_job'  => [
				'title'       => esc_html__( 'Testimonial Carousel: Job Title', 'sky-elementor-addons' ),
				'editor_type' => 'LINE',
			],
			'link'             => [
				'title'       => esc_html__( 'Testimonial Carousel: Link', 'sky-elementor-addons' ),
				'editor_type' => 'LINK',
			],
		];
	}

	protected function get_title( $field ) {
		return isset( $this->get_fields()[ $field ]['title'] ) ? $this->get_fields()[ $field ]['title'] : '';
	}

	protected function get_editor_type( $field ) {
		return isset( $this->get_fields()[ $field ]['editor_type'] ) ? $this->get_fields()[ $field ]['editor_type'] : 'LINE';
	}
}

<?php

namespace Sky_Addons\Includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPML_Fancy_Testimonial
 *
 * Repeater field IDs still use the `review*` vocabulary the widget shipped with — the
 * labels say testimonial, the IDs were kept so saved content survives. Match the IDs,
 * not the labels.
 */
class WPML_Fancy_Testimonial extends WPML_Module_With_Items {

	public function get_name() {
		return 'sky-fancy-testimonial';
	}

	public function get_items_field() {
		return 'reviews_list';
	}

	public function get_fields() {
		return [
			'name'        => [
				'title'       => esc_html__( 'Fancy Testimonial: Name', 'sky-elementor-addons' ),
				'editor_type' => 'LINE',
			],
			'designation' => [
				'title'       => esc_html__( 'Fancy Testimonial: Designation', 'sky-elementor-addons' ),
				'editor_type' => 'LINE',
			],
			'review'      => [
				'title'       => esc_html__( 'Fancy Testimonial: Content', 'sky-elementor-addons' ),
				'editor_type' => 'AREA',
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

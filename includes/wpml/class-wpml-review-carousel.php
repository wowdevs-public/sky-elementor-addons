<?php

namespace Sky_Addons\Includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPML_Review_Carousel
 */
class WPML_Review_Carousel extends WPML_Module_With_Items {

	public function get_name() {
		return 'sky-review-carousel';
	}

	public function get_items_field() {
		return 'review_list';
	}

	public function get_fields() {
		return [
			'name'        => [
				'title'       => esc_html__( 'Review Carousel: Name', 'sky-elementor-addons' ),
				'editor_type' => 'LINE',
			],
			'designation' => [
				'title'       => esc_html__( 'Review Carousel: Designation', 'sky-elementor-addons' ),
				'editor_type' => 'LINE',
			],
			'review'      => [
				'title'       => esc_html__( 'Review Carousel: Content', 'sky-elementor-addons' ),
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

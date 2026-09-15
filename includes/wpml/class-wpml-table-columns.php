<?php

namespace Sky_Addons\Includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPML_Table_Columns
 *
 * Column headings. Registered as a second node for the same widget type, since
 * one integration class can only declare a single items field.
 */
class WPML_Table_Columns extends WPML_Module_With_Items {

	public function get_name() {
		return 'sky-table';
	}

	public function get_items_field() {
		return 'columns_data';
	}

	public function get_fields() {
		return [
			'column_name' => [
				'title'       => esc_html__( 'Table: Column Title', 'sky-elementor-addons' ),
				'editor_type' => 'LINE',
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

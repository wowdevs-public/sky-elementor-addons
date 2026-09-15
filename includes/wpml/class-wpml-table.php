<?php

namespace Sky_Addons\Includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPML_Table
 *
 * Body cells. The head repeater is handled separately by {@see WPML_Table_Columns},
 * because a module integration can only declare one items field.
 */
class WPML_Table extends WPML_Module_With_Items {

	public function get_name() {
		return 'sky-table';
	}

	public function get_items_field() {
		return 'rows_data';
	}

	public function get_fields() {
		return [
			'cell_name' => [
				'title'       => esc_html__( 'Table: Cell Text', 'sky-elementor-addons' ),
				'editor_type' => 'AREA',
			],
			'cell_link' => [
				'field'       => 'url',
				'title'       => esc_html__( 'Table: Cell Link', 'sky-elementor-addons' ),
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

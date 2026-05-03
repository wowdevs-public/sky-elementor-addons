<?php
namespace Sky_Addons\Includes;

/**
 * Handles WPML translation integration for the List Group widget.
 * Extends WPML_Module_With_Items to support translation of list group items and fields.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPML_List_Group extends WPML_Module_With_Items {

	/**
	 * Returns the field name containing the list group items.
	 *
	 * @return string The field name for list group items.
	 */
	public function get_items_field() {
		return 'list';
	}

	/**
	 * Returns the translatable fields for the List Group widget.
	 *
	 * @return array List of translatable fields and their editor types.
	 */
	public function get_fields() {
		return [
			'list_title' => [
				'title'       => esc_html__( 'List Group: Title', 'sky-elementor-addons' ),
				'editor_type' => 'AREA',
			],
			'list_text'  => [
				'title'       => esc_html__( 'List Group: Text', 'sky-elementor-addons' ),
				'editor_type' => 'AREA',
			],
			'list_link'  => [
				'field'       => 'url',
				'title'       => esc_html__( 'List Group: Link', 'sky-elementor-addons' ),
				'editor_type' => 'LINK',
			],
		];
	}

	/**
	 * Gets the title for a given field.
	 *
	 * @param string $field Field name.
	 * @return string Field title, or empty string if not found.
	 */
	protected function get_title( $field ) {
		switch ( $field ) {
			case 'list_title':
				return esc_html__( 'List Group: Title', 'sky-elementor-addons' );
			case 'list_text':
				return esc_html__( 'List Group: Text', 'sky-elementor-addons' );
			case 'list_link':
				return esc_html__( 'List Group: Link', 'sky-elementor-addons' );
			default:
				return '';
		}
	}

	/**
	 * Gets the editor type for a given field.
	 *
	 * @param string $field Field name.
	 * @return string Editor type for the field, defaults to 'LINE'.
	 */
	protected function get_editor_type( $field ) {
		return isset( $this->get_fields()[ $field ]['editor_type'] ) ? $this->get_fields()[ $field ]['editor_type'] : 'LINE';
	}
}

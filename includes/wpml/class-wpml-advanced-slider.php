<?php

namespace Sky_Addons\Includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles WPML translation integration for the Advanced Slider widget.
 * Extends WPML_Module_With_Items to support translation of slider items and fields.
 */
class WPML_Advanced_Slider extends WPML_Module_With_Items {

	/**
	 * Returns the field name containing the slider items.
	 *
	 * @return string The field name for slider items.
	 */
	public function get_items_field() {
		return 'slider_list';
	}

	/**
	 * Returns the translatable fields for the Advanced Slider widget.
	 *
	 * @return array List of translatable fields and their editor types.
	 */
	public function get_fields() {
		return [
			'sub_title'   => [
				'title'       => esc_html__( 'Advanced Slider: Sub Title', 'sky-elementor-addons' ),
				'editor_type' => 'LINE',
			],
			'title'       => [
				'title'       => esc_html__( 'Advanced Slider: Title', 'sky-elementor-addons' ),
				'editor_type' => 'LINE',
			],
			'custom_text' => [
				'title'       => esc_html__( 'Advanced Slider: Text', 'sky-elementor-addons' ),
				'editor_type' => 'VISUAL',
			],
			'link'        => [
				'field'       => 'url',
				'title'       => esc_html__( 'Advanced Slider: Link', 'sky-elementor-addons' ),
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
		return isset( $this->get_fields()[ $field ]['title'] ) ? $this->get_fields()[ $field ]['title'] : '';
	}

	/**
	 * Collects translatable strings from the Advanced Slider widget.
	 * Handles both regular and repeater fields.
	 *
	 * @param string|int        $node_id Unique node identifier.
	 * @param array             $element Widget element data.
	 * @param \WPML_PB_String[] $strings Array of WPML_PB_String objects.
	 * @return \WPML_PB_String[] Updated array of WPML_PB_String objects.
	 */
	public function get( $node_id, $element, $strings ) {
		// Handle regular fields first
		$regular_fields = [
			'button_text' => 'LINE',
		];

		foreach ( $regular_fields as $field => $editor_type ) {
			if ( isset( $element['settings'][ $field ] ) && ! empty( $element['settings'][ $field ] ) ) {
				$strings[] = new \WPML_PB_String(
					$element['settings'][ $field ],
					'sky-advanced-slider-' . $field . '-' . $node_id,
					$this->get_title( $field ),
					$editor_type
				);
			}
		}

		// Then handle repeater items
		return parent::get( $node_id, $element, $strings );
	}

	/**
	 * Updates the widget element with translated string values.
	 * Handles both regular and repeater fields.
	 *
	 * @param string|int      $node_id Unique node identifier.
	 * @param array           $element Widget element data.
	 * @param \WPML_PB_String $string Translated WPML_PB_String object.
	 * @return array Updated widget element data.
	 */
	public function update( $node_id, $element, \WPML_PB_String $string ) {
		// Handle regular fields
		$regular_fields = [
			'button_text' => 'LINE',
		];

		foreach ( $regular_fields as $field => $editor_type ) {
			if ( 'sky-advanced-slider-' . $field . '-' . $node_id === $string->get_name() ) {
				$element['settings'][ $field ] = $string->get_value();
				return $element;
			}
		}

		// Then handle repeater items
		return parent::update( $node_id, $element, $string );
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

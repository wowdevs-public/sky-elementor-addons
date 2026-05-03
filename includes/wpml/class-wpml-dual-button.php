<?php
namespace Sky_Addons\Includes;

/**
 * Handles WPML translation integration for the Dual Button widget.
 * Extends WPML_Module_Without_Items to support translation of button fields.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPML_Dual_Button extends WPML_Module_Without_Items {

	/**
	 * Returns the translatable fields for the Dual Button widget.
	 *
	 * @return array List of translatable fields and their editor types.
	 */
	public function get_fields() {
		return [
			'separator_content_text' => [
				'title'       => esc_html__( 'Dual Button: Separator Text', 'sky-elementor-addons' ),
				'editor_type' => 'LINE',
			],
			'button_a_text'          => [
				'title'       => esc_html__( 'Dual Button: First Button Text', 'sky-elementor-addons' ),
				'editor_type' => 'LINE',
			],
			'button_a_link'          => [
				'title'       => esc_html__( 'Dual Button: First Button Link', 'sky-elementor-addons' ),
				'editor_type' => 'LINK',
			],
			'button_b_text'          => [
				'title'       => esc_html__( 'Dual Button: Second Button Text', 'sky-elementor-addons' ),
				'editor_type' => 'LINE',
			],
			'button_b_link'          => [
				'title'       => esc_html__( 'Dual Button: Second Button Link', 'sky-elementor-addons' ),
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
}

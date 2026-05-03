<?php
namespace Sky_Addons\Includes;

/**
 * Class WPML_Social_Icons
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPML_Social_Icons extends WPML_Module_With_Items {

	/**
	 * @return string
	 */
	public function get_items_field() {
		return 'social_icon_list';
	}

	/**
	 * @return array
	 */
	public function get_fields() {
		return [
			'social_label' => [
				'title'       => esc_html__( 'Social Icons: Social Name', 'sky-elementor-addons' ),
				'editor_type' => 'LINE',
			],
			'link'         => [
				'field'       => 'url',
				'title'       => esc_html__( 'Social Icons: Link', 'sky-elementor-addons' ),
				'editor_type' => 'LINK',
			],
		];
	}

	/**
	 * @param string $field
	 * @return string
	 */
	protected function get_title( $field ) {
		switch ( $field ) {
			case 'social_label':
				return esc_html__( 'Social Icons: Social Name', 'sky-elementor-addons' );
			case 'link':
				return esc_html__( 'Social Icons: Link', 'sky-elementor-addons' );
			case 'separator_text':
				return esc_html__( 'Social Icons: Custom Separator', 'sky-elementor-addons' );
			default:
				return '';
		}
	}

	/**
	 * @param string $field
	 * @return string
	 */
	protected function get_editor_type( $field ) {
		return isset( $this->get_fields()[ $field ]['editor_type'] ) ? $this->get_fields()[ $field ]['editor_type'] : 'LINE';
	}

	/**
	 * @param string|int        $node_id
	 * @param array             $element
	 * @param \WPML_PB_String[] $strings
	 *
	 * @return \WPML_PB_String[]
	 */
	public function get( $node_id, $element, $strings ) {
		// Handle regular fields
		if ( isset( $element['settings']['separator_text'] ) ) {
			$strings[] = new \WPML_PB_String(
				$element['settings']['separator_text'],
				$this->get_string_name_regular( $node_id, 'separator_text', $element['widgetType'] ),
				$this->get_title( 'separator_text' ),
				'LINE'
			);
		}

		// Handle repeater items
		return parent::get( $node_id, $element, $strings );
	}

	/**
	 * @param int|string      $node_id
	 * @param mixed           $element
	 * @param \WPML_PB_String $string
	 *
	 * @return mixed
	 */
	public function update( $node_id, $element, \WPML_PB_String $string ) {
		// Handle regular fields
		if ( $this->get_string_name_regular( $node_id, 'separator_text', $element['widgetType'] ) === $string->get_name() ) {
			$element['settings']['separator_text'] = $string->get_value();
			return $element;
		}

		// Handle repeater items
		return parent::update( $node_id, $element, $string );
	}

	/**
	 * @param string $node_id
	 * @param string $field
	 * @param string $widget_type
	 * @return string
	 */
	private function get_string_name_regular( $node_id, $field, $widget_type ) {
		return $widget_type . '-' . $field . '-' . $node_id;
	}
}

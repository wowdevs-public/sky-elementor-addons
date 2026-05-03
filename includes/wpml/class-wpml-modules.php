<?php
namespace Sky_Addons\Includes;

defined( 'ABSPATH' ) || die();

use WPML_PB_String;
use IWPML_Page_Builders_Module;
use WPML_Elementor_Translatable_Nodes;

/**
 * Abstract base class for WPML translation integration in Sky Elementor Addons widgets.
 * Implements IWPML_Page_Builders_Module and provides core logic for handling translatable fields and items.
 */
abstract class WPML_Modules implements IWPML_Page_Builders_Module {

	/**
	 * Gets the title for a given field.
	 *
	 * @param string $field Field name.
	 * @return string Field title.
	 */
	abstract protected function get_title( $field );

	/**
	 * Returns the translatable fields for the widget.
	 *
	 * @return array List of translatable fields and their editor types.
	 */
	abstract protected function get_fields();

	/**
	 * Gets the editor type for a given field.
	 *
	 * @param string $field Field name.
	 * @return string Editor type for the field.
	 */
	abstract protected function get_editor_type( $field );

	/**
	 * Returns the field name containing the repeater items, or an array of field names.
	 *
	 * @return string|array Field name(s) for repeater items.
	 */
	abstract public function get_items_field();

	/**
	 * Collects translatable strings from widget items and fields.
	 * Handles both simple and complex field configurations.
	 *
	 * @param string|int       $node_id Unique node identifier.
	 * @param array            $element Widget element data.
	 * @param WPML_PB_String[] $strings Array of WPML_PB_String objects.
	 * @return WPML_PB_String[] Updated array of WPML_PB_String objects.
	 */
	public function get( $node_id, $element, $strings ) {
		foreach ( $this->get_items( $element ) as $item ) {
			foreach ( $this->get_fields() as $key => $field ) {
				if ( ! is_array( $field ) ) {
					// Simple field (string)
					if ( ! isset( $item[ $field ] ) ) {
						continue;
					}

					$strings[] = new WPML_PB_String(
						$item[ $field ],
						$this->get_string_name( $node_id, $item[ $field ], $field, $element['widgetType'], $item['_id'] ),
						$this->get_title( $field ),
						$this->get_editor_type( $field )
					);
				} else {
					// Complex field (array)
					if ( isset( $field['field'] ) && isset( $field['editor_type'] ) ) {
						// New field configuration format
						if ( $field['editor_type'] === 'LINK' ) {
							// Handle URL/Link field
							if ( isset( $item[ $key ][ $field['field'] ] ) ) {
								$strings[] = new WPML_PB_String(
									$item[ $key ][ $field['field'] ],
									$this->get_string_name( $node_id, $item[ $key ][ $field['field'] ], $key . '_' . $field['field'], $element['widgetType'], $item['_id'] ),
									$field['type'],
									'LINK'
								);
							}
						} else {
							// Handle other configured fields
							if ( isset( $item[ $key ] ) ) {
								$strings[] = new WPML_PB_String(
									$item[ $key ],
									$this->get_string_name( $node_id, $item[ $key ], $key, $element['widgetType'], $item['_id'] ),
									$field['type'],
									$field['editor_type']
								);
							}
						}
					} else {
						// Legacy format - array of sub-fields
						foreach ( $field as $inner_field ) {
							if ( ! isset( $item[ $key ][ $inner_field ] ) ) {
								continue;
							}

							$strings[] = new WPML_PB_String(
								$item[ $key ][ $inner_field ],
								$this->get_string_name( $node_id, $item[ $key ][ $inner_field ], $key . '_' . $inner_field, $element['widgetType'], $item['_id'] ),
								$this->get_title( $key ),
								$this->get_editor_type( $key )
							);
						}
					}
				}
			}
		}
		return $strings;
	}

	/**
	 * Updates widget items with translated string values.
	 * Handles both simple and complex field configurations.
	 *
	 * @param int|string     $node_id Unique node identifier.
	 * @param mixed          $element Widget element data.
	 * @param WPML_PB_String $string Translated WPML_PB_String object.
	 * @return mixed Updated widget item or element data.
	 */
	public function update( $node_id, $element, WPML_PB_String $string ) {
		foreach ( $this->get_items( $element ) as $key => $item ) {
			foreach ( $this->get_fields() as $field_key => $field ) {
				if ( ! is_array( $field ) ) {
					// Simple field (string)
					if ( ! isset( $item[ $field ] ) ) {
						continue;
					}

					if ( $this->get_string_name( $node_id, $item[ $field ], $field, $element['widgetType'], $item['_id'] ) === $string->get_name() ) {
						$item[ $field ] = $string->get_value();
						$item['index']  = $key;
						return $item;
					}
				} else {
					// Complex field (array)
					if ( isset( $field['field'] ) && isset( $field['editor_type'] ) ) {
						// New field configuration format
						if ( $field['editor_type'] === 'LINK' ) {
							// Handle URL/Link field
							if ( isset( $item[ $field_key ][ $field['field'] ] ) ) {
								if ( $this->get_string_name( $node_id, $item[ $field_key ][ $field['field'] ], $field_key . '_' . $field['field'], $element['widgetType'], $item['_id'] ) === $string->get_name() ) {
									$item[ $field_key ][ $field['field'] ] = $string->get_value();
									$item['index']                         = $key;
									return $item;
								}
							}
						} else {
							// Handle other configured fields
							if ( isset( $item[ $field_key ] ) ) {
								if ( $this->get_string_name( $node_id, $item[ $field_key ], $field_key, $element['widgetType'], $item['_id'] ) === $string->get_name() ) {
									$item[ $field_key ] = $string->get_value();
									$item['index']      = $key;
									return $item;
								}
							}
						}
					} else {
						// Legacy format - array of sub-fields
						foreach ( $field as $inner_field ) {
							if ( ! isset( $item[ $field_key ][ $inner_field ] ) ) {
								continue;
							}

							if ( $this->get_string_name( $node_id, $item[ $field_key ][ $inner_field ], $field_key . '_' . $inner_field, $element['widgetType'], $item['_id'] ) === $string->get_name() ) {
								$item[ $field_key ][ $inner_field ] = $string->get_value();
								$item['index']                      = $key;
								return $item;
							}
						}
					}
				}
			}
		}
		return $element;
	}

	/**
	 * Generates a unique string name for WPML translation mapping.
	 *
	 * @param string $node_id Node identifier.
	 * @param string $value Field value.
	 * @param string $type Field type.
	 * @param string $key Widget type or key.
	 * @param string $item_id Item identifier.
	 * @return string Unique string name for WPML.
	 */
	private function get_string_name( $node_id, $value, $type, $key = '', $item_id = '' ) {
		return $key . '-' . $type . '-' . $node_id . '-' . $item_id;
	}

	/**
	 * @param $element
	 *
	 * @return mixed
	 */
	public function get_items( $element ) {

		$items_field = $this->get_items_field();

		if ( is_array( $items_field ) ) {
			$items = [];

			foreach ( $items_field as $field ) {
				if ( isset( $element[ WPML_Elementor_Translatable_Nodes::SETTINGS_FIELD ][ $field ] ) ) {
					$items = array_merge(
						$items,
						$element[ WPML_Elementor_Translatable_Nodes::SETTINGS_FIELD ][ $field ]
					);
				}
			}

			return $items;
		}

		return $element[ WPML_Elementor_Translatable_Nodes::SETTINGS_FIELD ][ $items_field ];
	}
}

/**
 * Base class for modules that have repeater items (e.g., sliders, lists).
 * Extends WPML_Modules and provides default editor type logic.
 */
abstract class WPML_Module_With_Items extends WPML_Modules {

	/**
	 * Gets the editor type for a given field. Default is 'LINE'.
	 *
	 * @param string $field Field name.
	 * @return string Editor type for the field.
	 */
	protected function get_editor_type( $field ) {
		return 'LINE';
	}
}

/**
 * Base class for modules that don't have repeater items (e.g., single field widgets).
 * Implements IWPML_Page_Builders_Module and provides default logic for non-repeater widgets.
 */
abstract class WPML_Module_Without_Items implements IWPML_Page_Builders_Module {

	/**
	 * @param string $field
	 * @return string
	 */
	abstract protected function get_title( $field );

	/**
	 * @return array
	 */
	abstract protected function get_fields();

	/**
	 * Gets the editor type for a given field. Default is 'LINE'.
	 *
	 * @param string $field Field name.
	 * @return string Editor type for the field.
	 */
	protected function get_editor_type( $field ) {
		return 'LINE';
	}

	/**
	 * Collects translatable strings from widget fields.
	 * Handles both simple and link field types.
	 *
	 * @param string|int       $node_id Unique node identifier.
	 * @param array            $element Widget element data.
	 * @param WPML_PB_String[] $strings Array of WPML_PB_String objects.
	 * @return WPML_PB_String[] Updated array of WPML_PB_String objects.
	 */
	public function get( $node_id, $element, $strings ) {
		foreach ( $this->get_fields() as $field => $field_config ) {
			if ( ! isset( $element['settings'][ $field ] ) ) {
				continue;
			}

			$editor_type = is_array( $field_config ) && isset( $field_config['editor_type'] ) ? $field_config['editor_type'] : $this->get_editor_type( $field );
			$title       = is_array( $field_config ) && isset( $field_config['title'] ) ? $field_config['title'] : $this->get_title( $field );

			if ( $editor_type === 'LINK' ) {
				$value = $element['settings'][ $field ];
				if ( is_array( $value ) && isset( $value['url'] ) ) {
					$strings[] = new WPML_PB_String(
						$value['url'],
						$this->get_string_name( $node_id, $field, $element['widgetType'] ),
						$title,
						'LINK'
					);
				}
			} else {
				$strings[] = new WPML_PB_String(
					$element['settings'][ $field ],
					$this->get_string_name( $node_id, $field, $element['widgetType'] ),
					$title,
					$editor_type
				);
			}
		}
		return $strings;
	}

	/**
	 * Updates widget fields with translated string values.
	 * Handles both simple and link field types.
	 *
	 * @param int|string     $node_id Unique node identifier.
	 * @param mixed          $element Widget element data.
	 * @param WPML_PB_String $string Translated WPML_PB_String object.
	 * @return mixed Updated widget element data.
	 */
	public function update( $node_id, $element, WPML_PB_String $string ) {
		foreach ( $this->get_fields() as $field => $field_config ) {
			if ( $this->get_string_name( $node_id, $field, $element['widgetType'] ) === $string->get_name() ) {
				$editor_type = is_array( $field_config ) && isset( $field_config['editor_type'] ) ? $field_config['editor_type'] : $this->get_editor_type( $field );

				if ( $editor_type === 'LINK' ) {
					$value = $element['settings'][ $field ];
					if ( is_array( $value ) ) {
						$value['url']                  = $string->get_value();
						$element['settings'][ $field ] = $value;
					}
				} else {
					$element['settings'][ $field ] = $string->get_value();
				}
				return $element;
			}
		}
		return $element;
	}

	/**
	 * Generates a unique string name for WPML translation mapping for non-repeater widgets.
	 *
	 * @param string $node_id Node identifier.
	 * @param string $field Field name.
	 * @param string $widget_type Widget type.
	 * @return string Unique string name for WPML.
	 */
	private function get_string_name( $node_id, $field, $widget_type ) {
		return $widget_type . '-' . $field . '-' . $node_id;
	}
}

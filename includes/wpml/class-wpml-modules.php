<?php
namespace Sky_Addons\Includes;

defined( 'ABSPATH' ) || die();

use WPML_PB_String;
use IWPML_Page_Builders_Module;
use WPML_Elementor_Translatable_Nodes;

/**
 * Abstract base class for WPML translation integration in Sky Elementor Addons widgets.
 * Implements IWPML_Page_Builders_Module and provides core logic for handling translatable fields and items.
 *
 * Supported get_fields() formats (any mix is allowed):
 *   1. Simple field          : 'field_name'
 *   2. Configured field      : 'field_name' => [ 'title' => '', 'editor_type' => 'LINE' ]
 *   3. Configured link field : 'field_name' => [ 'title' => '', 'editor_type' => 'LINK' ]
 *
 * For format 1 the title/editor type come from get_title()/get_editor_type().
 * Legacy keys 'type' (instead of 'title') are still accepted for back-compat.
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
	 * Normalizes get_fields() into a uniform map: field_name => [ 'title', 'editor_type' ].
	 *
	 * @return array
	 */
	protected function normalize_fields() {
		$normalized = [];

		foreach ( $this->get_fields() as $key => $field ) {
			if ( is_array( $field ) ) {
				// Configured field. The array key is the field name; fall back to
				// the legacy 'field' sub-key when the entry uses a numeric key.
				$field_name = is_string( $key ) ? $key : ( isset( $field['field'] ) ? $field['field'] : '' );

				if ( '' === $field_name ) {
					continue;
				}

				$title = '';
				if ( isset( $field['title'] ) ) {
					$title = $field['title'];
				} elseif ( isset( $field['type'] ) ) {
					$title = $field['type'];
				} else {
					$title = $this->get_title( $field_name );
				}

				$normalized[ $field_name ] = [
					'title'       => $title,
					'editor_type' => isset( $field['editor_type'] ) ? $field['editor_type'] : $this->get_editor_type( $field_name ),
				];
			} else {
				// Simple field.
				$normalized[ $field ] = [
					'title'       => $this->get_title( $field ),
					'editor_type' => $this->get_editor_type( $field ),
				];
			}
		}

		return $normalized;
	}

	/**
	 * Collects translatable strings from widget repeater items.
	 *
	 * @param string|int       $node_id Unique node identifier.
	 * @param array            $element Widget element data.
	 * @param WPML_PB_String[] $strings Array of WPML_PB_String objects.
	 * @return WPML_PB_String[] Updated array of WPML_PB_String objects.
	 */
	public function get( $node_id, $element, $strings ) {
		$widget_type = isset( $element['widgetType'] ) ? $element['widgetType'] : '';
		$fields      = $this->normalize_fields();

		foreach ( $this->get_items( $element ) as $item ) {
			if ( ! is_array( $item ) || ! isset( $item['_id'] ) ) {
				continue;
			}

			foreach ( $fields as $field_name => $config ) {
				if ( 'LINK' === $config['editor_type'] ) {
					if ( isset( $item[ $field_name ]['url'] ) && '' !== $item[ $field_name ]['url'] ) {
						$strings[] = new WPML_PB_String(
							$item[ $field_name ]['url'],
							$this->get_string_name( $node_id, $field_name, $widget_type, $item['_id'] ),
							$config['title'],
							'LINK'
						);
					}
				} elseif ( isset( $item[ $field_name ] ) && is_string( $item[ $field_name ] ) && '' !== $item[ $field_name ] ) {
						$strings[] = new WPML_PB_String(
							$item[ $field_name ],
							$this->get_string_name( $node_id, $field_name, $widget_type, $item['_id'] ),
							$config['title'],
							$config['editor_type']
						);
				}
			}
		}

		return $strings;
	}

	/**
	 * Updates widget repeater items with translated string values.
	 *
	 * @param int|string     $node_id Unique node identifier.
	 * @param mixed          $element Widget element data.
	 * @param WPML_PB_String $string  Translated WPML_PB_String object.
	 * @return mixed Updated repeater item (with 'index') or unchanged element data.
	 */
	public function update( $node_id, $element, WPML_PB_String $string ) {
		$widget_type = isset( $element['widgetType'] ) ? $element['widgetType'] : '';
		$fields      = $this->normalize_fields();

		foreach ( $this->get_items( $element ) as $index => $item ) {
			if ( ! is_array( $item ) || ! isset( $item['_id'] ) ) {
				continue;
			}

			foreach ( $fields as $field_name => $config ) {
				if ( $this->get_string_name( $node_id, $field_name, $widget_type, $item['_id'] ) !== $string->get_name() ) {
					continue;
				}

				if ( 'LINK' === $config['editor_type'] ) {
					if ( isset( $item[ $field_name ] ) && is_array( $item[ $field_name ] ) ) {
						$item[ $field_name ]['url'] = $string->get_value();
					}
				} else {
					$item[ $field_name ] = $string->get_value();
				}

				$item['index'] = $index;
				return $item;
			}
		}

		return $element;
	}

	/**
	 * Generates a unique string name for WPML translation mapping of a repeater field.
	 *
	 * @param string $node_id     Node identifier.
	 * @param string $field       Field name.
	 * @param string $widget_type Widget type.
	 * @param string $item_id     Repeater item identifier.
	 * @return string Unique string name for WPML.
	 */
	private function get_string_name( $node_id, $field, $widget_type, $item_id ) {
		return $widget_type . '-' . $field . '-' . $node_id . '-' . $item_id;
	}

	/**
	 * Returns the repeater items for the given element.
	 *
	 * @param array $element Widget element data.
	 * @return array Repeater items, or an empty array when none exist.
	 */
	public function get_items( $element ) {
		$settings_key = WPML_Elementor_Translatable_Nodes::SETTINGS_FIELD;
		$settings     = isset( $element[ $settings_key ] ) && is_array( $element[ $settings_key ] ) ? $element[ $settings_key ] : [];
		$items_field  = $this->get_items_field();

		if ( is_array( $items_field ) ) {
			$items = [];

			foreach ( $items_field as $field ) {
				if ( isset( $settings[ $field ] ) && is_array( $settings[ $field ] ) ) {
					$items = array_merge( $items, $settings[ $field ] );
				}
			}

			return $items;
		}

		return ( isset( $settings[ $items_field ] ) && is_array( $settings[ $items_field ] ) ) ? $settings[ $items_field ] : [];
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
 *
 * Supported get_fields() formats match WPML_Modules (simple, configured, configured link).
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
	 * Normalizes get_fields() into a uniform map: field_name => [ 'title', 'editor_type' ].
	 *
	 * @return array
	 */
	protected function normalize_fields() {
		$normalized = [];

		foreach ( $this->get_fields() as $key => $field ) {
			if ( is_array( $field ) ) {
				$field_name = is_string( $key ) ? $key : ( isset( $field['field'] ) ? $field['field'] : '' );

				if ( '' === $field_name ) {
					continue;
				}

				$title = '';
				if ( isset( $field['title'] ) ) {
					$title = $field['title'];
				} elseif ( isset( $field['type'] ) ) {
					$title = $field['type'];
				} else {
					$title = $this->get_title( $field_name );
				}

				$normalized[ $field_name ] = [
					'title'       => $title,
					'editor_type' => isset( $field['editor_type'] ) ? $field['editor_type'] : $this->get_editor_type( $field_name ),
				];
			} else {
				$normalized[ $field ] = [
					'title'       => $this->get_title( $field ),
					'editor_type' => $this->get_editor_type( $field ),
				];
			}
		}

		return $normalized;
	}

	/**
	 * Collects translatable strings from widget fields.
	 *
	 * @param string|int       $node_id Unique node identifier.
	 * @param array            $element Widget element data.
	 * @param WPML_PB_String[] $strings Array of WPML_PB_String objects.
	 * @return WPML_PB_String[] Updated array of WPML_PB_String objects.
	 */
	public function get( $node_id, $element, $strings ) {
		$widget_type = isset( $element['widgetType'] ) ? $element['widgetType'] : '';
		$settings    = isset( $element['settings'] ) && is_array( $element['settings'] ) ? $element['settings'] : [];

		foreach ( $this->normalize_fields() as $field => $config ) {
			if ( ! isset( $settings[ $field ] ) ) {
				continue;
			}

			if ( 'LINK' === $config['editor_type'] ) {
				$value = $settings[ $field ];
				if ( is_array( $value ) && isset( $value['url'] ) && '' !== $value['url'] ) {
					$strings[] = new WPML_PB_String(
						$value['url'],
						$this->get_string_name( $node_id, $field, $widget_type ),
						$config['title'],
						'LINK'
					);
				}
			} else {
				if ( ! is_string( $settings[ $field ] ) || '' === $settings[ $field ] ) {
					continue;
				}

				$strings[] = new WPML_PB_String(
					$settings[ $field ],
					$this->get_string_name( $node_id, $field, $widget_type ),
					$config['title'],
					$config['editor_type']
				);
			}
		}

		return $strings;
	}

	/**
	 * Updates widget fields with translated string values.
	 *
	 * @param int|string     $node_id Unique node identifier.
	 * @param mixed          $element Widget element data.
	 * @param WPML_PB_String $string  Translated WPML_PB_String object.
	 * @return mixed Updated widget element data.
	 */
	public function update( $node_id, $element, WPML_PB_String $string ) {
		$widget_type = isset( $element['widgetType'] ) ? $element['widgetType'] : '';

		foreach ( $this->normalize_fields() as $field => $config ) {
			if ( $this->get_string_name( $node_id, $field, $widget_type ) !== $string->get_name() ) {
				continue;
			}

			if ( 'LINK' === $config['editor_type'] ) {
				if ( isset( $element['settings'][ $field ] ) && is_array( $element['settings'][ $field ] ) ) {
					$element['settings'][ $field ]['url'] = $string->get_value();
				}
			} else {
				$element['settings'][ $field ] = $string->get_value();
			}

			return $element;
		}

		return $element;
	}

	/**
	 * Generates a unique string name for WPML translation mapping for non-repeater widgets.
	 *
	 * @param string $node_id     Node identifier.
	 * @param string $field       Field name.
	 * @param string $widget_type Widget type.
	 * @return string Unique string name for WPML.
	 */
	private function get_string_name( $node_id, $field, $widget_type ) {
		return $widget_type . '-' . $field . '-' . $node_id;
	}
}

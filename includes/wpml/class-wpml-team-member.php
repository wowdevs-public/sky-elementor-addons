<?php
namespace Sky_Addons\Includes;

/**
 * Class WPML_Team_Member
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPML_Team_Member extends WPML_Module_With_Items {

	/**
	 * @return string
	 */
	public function get_items_field() {
		return 'social_list';
	}

	/**
	 * @return array
	 */
	public function get_fields() {
		return [
			'social_name',
			'social_link' => [
				'field'       => 'url',
				'type'        => esc_html__( 'Team Member: Social Link', 'sky-elementor-addons' ),
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
			case 'name':
				return esc_html__( 'Team Member: Name', 'sky-elementor-addons' );
			case 'job_title':
				return esc_html__( 'Team Member: Job Title', 'sky-elementor-addons' );
			case 'text':
				return esc_html__( 'Team Member: Short Text', 'sky-elementor-addons' );
			case 'button_text':
				return esc_html__( 'Team Member: Button Text', 'sky-elementor-addons' );
			case 'link':
				return esc_html__( 'Team Member: Button Link', 'sky-elementor-addons' );
			case 'social_name':
				return esc_html__( 'Team Member: Social Name', 'sky-elementor-addons' );
			case 'social_link':
				return esc_html__( 'Team Member: Social Link', 'sky-elementor-addons' );
			default:
				return '';
		}
	}

	/**
	 * @param string $field
	 * @return string
	 */
	protected function get_editor_type( $field ) {
		switch ( $field ) {
			case 'name':
			case 'text':
				return 'AREA';
			case 'job_title':
			case 'button_text':
			case 'social_name':
				return 'LINE';
			default:
				return 'LINE';
		}
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
		$regular_fields = [
			'name'        => 'AREA',
			'job_title'   => 'LINE',
			'text'        => 'AREA',
			'button_text' => 'LINE',
			'link'        => 'LINK',
		];

		foreach ( $regular_fields as $field => $editor_type ) {
			if ( ! isset( $element['settings'][ $field ] ) ) {
				continue;
			}

			if ( $editor_type === 'LINK' ) {
				$value = $element['settings'][ $field ];
				if ( is_array( $value ) && isset( $value['url'] ) ) {
					$strings[] = new \WPML_PB_String(
						$value['url'],
						$this->get_string_name_regular( $node_id, $field, $element['widgetType'] ),
						$this->get_title( $field ),
						'LINK'
					);
				}
			} else {
				$strings[] = new \WPML_PB_String(
					$element['settings'][ $field ],
					$this->get_string_name_regular( $node_id, $field, $element['widgetType'] ),
					$this->get_title( $field ),
					$editor_type
				);
			}
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
		$regular_fields = [ 'name', 'job_title', 'text', 'button_text', 'link' ];

		foreach ( $regular_fields as $field ) {
			if ( $this->get_string_name_regular( $node_id, $field, $element['widgetType'] ) === $string->get_name() ) {
				if ( $field === 'link' ) {
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

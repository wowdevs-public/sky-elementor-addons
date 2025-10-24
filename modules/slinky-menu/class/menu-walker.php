<?php

namespace Sky_Addons\Modules\SlinkyMenu;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Custom Menu Walker Class
 */
class Menu_Walker extends \Walker_Nav_Menu {

	/**
	 * Indicates if the current menu item has children.
	 *
	 * @var bool
	 */
	public $has_child = false;

	/**
	 * Starts the list before the child elements are added.
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int    $depth  Depth of menu item. Used for padding.
	 * @param array  $args   An array of arguments.
	 */
	public function start_lvl( &$output, $depth = 0, $args = [] ) {
		$output .= '<ul>';
	}

	/**
	 * Ends the list of after the child elements are added.
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int    $depth  Depth of menu item. Used for padding.
	 * @param array  $args   An array of arguments.
	 */
	public function end_lvl( &$output, $depth = 0, $args = [] ) {
		$output .= '</ul>';
	}

	/**
	 * Starts the element output.
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $item   Menu item data object.
	 * @param int    $depth  Depth of menu item. Used for padding.
	 * @param array  $args   An array of arguments.
	 * @param int    $id     Current item ID.
	 */
	public function start_el( &$output, $item, $depth = 0, $args = [], $id = 0 ) {
		$data    = [];
		$class   = '';
		$classes = empty( $item->classes ) ? [] : (array) $item->classes;

		// Filter and sanitize classes
		$classes = array_map( 'sanitize_html_class', $classes );

		if ( $args->walker->has_children ) {
			$classes[] = 'has-arrow';
		}

		if ( $item->current || $item->current_item_parent || $item->current_item_ancestor ) {
			$classes[] = 'current-menu-item';
		}

		if ( $item->dropdown_child && $depth > 0 ) {
			$classes[] = 'sub-dropdown';
		}

		$class = implode( ' ', $classes );

		// Add data attributes
		if ( in_array( 'current-menu-item', $classes ) || in_array( 'current_page_item', $classes ) ) {
			$data['data-menu-active'] = 2;
		} elseif ( preg_replace( '/#(.+)$/', '', $item->url ) === 'index.php' && ( is_home() || is_front_page() ) ) {
			$data['data-menu-active'] = 2;
		}

		$attributes = '';
		foreach ( $data as $name => $value ) {
			$attributes .= sprintf( ' %s="%s"', esc_attr( $name ), esc_attr( $value ) );
		}

		$id_attr = $id ? sprintf( ' id="%s"', esc_attr( $id ) ) : '';
		$class_attr = $class ? sprintf( ' class="%s"', esc_attr( $class ) ) : '';

		$output .= sprintf( '<li%s%s%s>', $id_attr, $attributes, $class_attr );

		$link_attributes = '';
		foreach ( [
			'attr_title' => 'title',
			'target'     => 'target',
			'xfn'        => 'rel',
			'url'        => 'href',
		] as $var => $attr ) {
			if ( ! empty( $item->$var ) ) {
				$link_attributes .= sprintf( ' %s="%s"', esc_attr( $attr ), esc_url( $item->$var ) );
			}
		}

		$icon = isset( $item->icon ) ? sprintf( '<span class="sky-margin-small-right" sky-icon="icon: %s"></span>', esc_attr( $item->icon ) ) : '';

		$item_output = sprintf(
			'%s<a%s>%s%s%s</a>%s',
			$args->before,
			$link_attributes,
			$icon,
			$args->link_before,
			apply_filters( 'the_title', $item->title, $item->ID ),
			$args->link_after,
			$args->after
		);

		$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
	}

	/**
	 * Ends the element output, if needed.
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $item   Page data object. Not used.
	 * @param int    $depth  Depth of page. Not Used.
	 * @param array  $args   An array of arguments.
	 */
	public function end_el( &$output, $item, $depth = 0, $args = [] ) {
		$output .= '</li>';
	}

	/**
	 * Displays an element and its children.
	 *
	 * @param object $element           Data object.
	 * @param array  &$children_elements List of elements to continue traversing.
	 * @param int    $max_depth         Max depth to traverse.
	 * @param int    $depth             Depth of current element.
	 * @param array  $args              Arguments.
	 * @param string &$output           Passed by reference. Used to append additional content.
	 */
	public function display_element( $element, &$children_elements, $max_depth, $depth, $args, &$output ) {
		$element->hasChildren = isset( $children_elements[ $element->ID ] ) && ! empty( $children_elements[ $element->ID ] );
		parent::display_element( $element, $children_elements, $max_depth, $depth, $args, $output );
	}
}

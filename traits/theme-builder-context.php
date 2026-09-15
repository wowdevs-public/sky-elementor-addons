<?php

namespace Sky_Addons\Traits;

use Sky_Addons\ThemeBuilder\Builder_Context;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shared context helpers for Theme Builder widgets.
 *
 * Any widget that renders data about "the current post" inside a Theme Builder
 * template must resolve the post through this trait. Reading the global $post
 * or calling get_the_ID() directly returns the *template* document, because
 * Elementor swaps the global while rendering builder content.
 *
 * Every method is prefixed `tb_` / `get_tb_` — a widget method that reuses a
 * Controls_Stack or Widget_Base name fatals the whole site.
 *
 * @since 4.5.0
 */
trait Theme_Builder_Context {

	/**
	 * ID of the post this widget is rendering for.
	 *
	 * @return int
	 */
	protected function get_tb_post_id() {
		return Builder_Context::instance()->get_queried_post_id();
	}

	/**
	 * @return \WP_Post|null
	 */
	protected function get_tb_post() {
		return Builder_Context::instance()->get_queried_post();
	}

	/**
	 * Post type of the resolved post — useful for taxonomy and meta lookups.
	 *
	 * @return string
	 */
	protected function get_tb_post_type() {
		$post_id = $this->get_tb_post_id();

		return $post_id ? (string) get_post_type( $post_id ) : '';
	}

	/**
	 * True inside the Elementor editor, the preview iframe, or an editor AJAX
	 * render. Use it to show placeholder content instead of an empty widget.
	 *
	 * @return bool
	 */
	protected function is_tb_editor() {
		return Builder_Context::instance()->is_editor();
	}

	/**
	 * Panel gate for Theme Builder widgets.
	 *
	 * Widgets call this from show_in_panel() so they only appear while editing a
	 * theme builder template or an Elementor library template — they render
	 * nothing meaningful on an ordinary page.
	 *
	 * @return bool
	 */
	protected function tb_show_in_panel() {
		return Builder_Context::is_builder_document();
	}
}

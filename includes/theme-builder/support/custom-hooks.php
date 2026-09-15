<?php

namespace Sky_Addons\ThemeBuilder;

defined( 'ABSPATH' ) || exit;

/**
 * Custom Hooks
 *
 * Renders an Elementor template at any WordPress action hook.
 */
class Custom_Hooks {

	private $hook_name;
	private $hook_priority;
	private $template_id;

	public function __construct( $hook_name, $hook_priority, $template_id ) {
		$this->hook_name     = $hook_name;
		$this->hook_priority = ! empty( $hook_priority ) ? (int) $hook_priority : 10;
		$this->template_id   = $template_id;

		if ( ! empty( $this->hook_name ) && ! empty( $this->template_id ) ) {
			add_action( $this->hook_name, [ $this, 'add_content_to_hook' ], $this->hook_priority );
		}
	}

	public function add_content_to_hook() {
		if ( empty( $this->template_id ) ) {
			return;
		}
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo wowdevs_render_elementor_content( $this->template_id );
	}
}

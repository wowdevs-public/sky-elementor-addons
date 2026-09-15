<?php

namespace Sky_Addons\ThemeBuilder\Themes_Hooks;

defined( 'ABSPATH' ) || exit;

/**
 * Storefront theme compatibility.
 */
class Storefront {

	private $elementor;

	private $header;
	private $footer;

	public function __construct( $template_ids ) {

		$this->header = $template_ids['header'];
		$this->footer = $template_ids['footer'];

		if ( defined( 'ELEMENTOR_VERSION' ) && is_callable( 'Elementor\Plugin::instance' ) ) {
			$this->elementor = \Elementor\Plugin::instance();
		}

		if ( null !== $this->header ) {
			add_action( 'template_redirect', [ $this, 'remove_theme_header_markup' ], 10 );
		}

		if ( null !== $this->footer ) {
			add_action( 'template_redirect', [ $this, 'remove_theme_footer_markup' ], 10 );
		}
	}

	/**
	 * Clear the theme's own header callbacks, then attach ours to the same hook.
	 */
	public function remove_theme_header_markup() {
		remove_all_actions( 'storefront_header' );
		add_action( 'storefront_header', [ $this, 'add_plugin_header_markup' ] );
	}

	public function add_plugin_header_markup() {
		do_action( 'wowdevs_themes_builder_template_before_header' );
		echo '<div class="wowdevs-template-content-markup wowdevs-template-content-header">';
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo wowdevs_render_elementor_content( $this->header );
		echo '</div>';
		do_action( 'wowdevs_themes_builder_template_after_header' );
	}

	/**
	 * Clear the theme's own footer callbacks, then attach ours to the same hook.
	 */
	public function remove_theme_footer_markup() {
		remove_all_actions( 'storefront_footer' );
		add_action( 'storefront_footer', [ $this, 'add_plugin_footer_markup' ] );
	}

	public function add_plugin_footer_markup() {
		do_action( 'wowdevs_themes_builder_template_before_footer' );
		echo '<div class="wowdevs-template-content-markup wowdevs-template-content-footer">';
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo wowdevs_render_elementor_content( $this->footer );
		echo '</div>';
		do_action( 'wowdevs_themes_builder_template_after_footer' );
	}
}

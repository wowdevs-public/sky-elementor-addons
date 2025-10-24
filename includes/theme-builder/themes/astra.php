<?php

namespace Sky_Addons\ThemeBuilder\Themes_Hooks;

defined( 'ABSPATH' ) || exit;

/**
 * Astra theme compatibility.
 */
class Astra {

	/**
	 * Instance of Elementor Frontend class.
	 *
	 * @var \Elementor\Frontend()
	 */
	private $elementor;

	private $header;
	private $footer;

	/**
	 * Run all the Actions / Filters.
	 */
	public function __construct( $template_ids ) {

		$this->header = $template_ids['header'];
		$this->footer = $template_ids['footer'];

		if ( defined( 'ELEMENTOR_VERSION' ) && is_callable( 'Elementor\Plugin::instance' ) ) {
			$this->elementor = \Elementor\Plugin::instance();
		}

		if ( null !== $this->header ) {
			add_action( 'template_redirect', [ $this, 'remove_theme_header_markup' ], 10 );
			add_action( 'astra_header', [ $this, 'add_plugin_header_markup' ] );
		}

		if ( null !== $this->footer ) {
			add_action( 'template_redirect', [ $this, 'remove_theme_footer_markup' ], 10 );
			add_action( 'astra_footer', [ $this, 'add_plugin_footer_markup' ] );
		}
	}

	public function remove_theme_header_markup() {
		remove_action( 'astra_header', 'astra_header_markup' );
	}

	public function add_plugin_header_markup() {
		do_action( 'wowdevs_themes_builder_template_before_header' );
		echo '<div class="wowdevs-template-content-markup wowdevs-template-content-header">';
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo wowdevs_render_elementor_content( $this->header );
		echo '</div>';
		do_action( 'wowdevs_themes_builder_template_after_header' );
	}

	public function remove_theme_footer_markup() {
		remove_action( 'astra_footer', 'astra_footer_markup' );
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

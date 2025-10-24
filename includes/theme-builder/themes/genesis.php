<?php

namespace Sky_Addons\ThemeBuilder\Themes_Hooks;

defined( 'ABSPATH' ) || exit;

/**
 * Genesis Themes
 */
class Genesis {

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
			add_action( 'ocean_header', [ $this, 'add_plugin_header_markup' ] );
			add_action( 'genesis_header', [ $this, 'genesis_header_markup_open' ], 16 );
			add_action( 'genesis_header', [ $this, 'genesis_header_markup_close' ], 25 );
		}

		if ( null !== $this->footer ) {
			add_action( 'template_redirect', [ $this, 'remove_theme_footer_markup' ], 10 );
			add_action( 'genesis_footer', [ $this, 'genesis_footer_markup_open' ], 16 );
			add_action( 'genesis_footer', [ $this, 'genesis_footer_markup_close' ], 25 );
			add_action( 'ocean_footer', [ $this, 'add_plugin_footer_markup' ] );
		}
	}

	public function remove_theme_header_markup() {
		for ( $priority = 0; $priority < 16; $priority++ ) {
			remove_all_actions( 'genesis_header', $priority );
		}
	}

	/**
	 * Open markup for header.
	 */
	public function genesis_header_markup_open() {

		genesis_markup(
			[
				'html5'   => '<header %s>',
				'xhtml'   => '<div id="header">',
				'context' => 'site-header',
			]
		);

		genesis_structural_wrap( 'header' );
	}

	/**
	 * Close MArkup for header.
	 */
	public function genesis_header_markup_close() {

		genesis_structural_wrap( 'header', 'close' );
		genesis_markup(
			[
				'html5' => '</header>',
				'xhtml' => '</div>',
			]
		);
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
		for ( $priority = 0; $priority < 16; $priority++ ) {
			remove_all_actions( 'genesis_footer', $priority );
		}
	}
	/**
	 * Open markup for footer.
	 */
	public function genesis_footer_markup_open() {

		genesis_markup(
			[
				'html5'   => '<footer %s>',
				'xhtml'   => '<div id="footer" class="footer">',
				'context' => 'site-footer',
			]
		);
		genesis_structural_wrap( 'footer', 'open' );
	}

	/**
	 * Close markup for footer.
	 */
	public function genesis_footer_markup_close() {

		genesis_structural_wrap( 'footer', 'close' );
		genesis_markup(
			[
				'html5' => '</footer>',
				'xhtml' => '</div>',
			]
		);
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

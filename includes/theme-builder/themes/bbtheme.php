<?php

namespace Sky_Addons\ThemeBuilder\Themes_Hooks;

defined( 'ABSPATH' ) || exit;

/**
 * BB Themes
 */
class Bbtheme {

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
			add_filter( 'fl_header_enabled', '__return_false' );
			add_action( 'fl_before_header', [ $this, 'add_plugin_header_markup' ] );
		}

		if ( null !== $this->footer ) {
			add_filter( 'fl_footer_enabled', '__return_false' );
			add_action( 'fl_after_content', [ $this, 'add_plugin_footer_markup' ] );
		}
	}

	public function add_plugin_header_markup() {

		if ( class_exists( '\FLTheme' ) ) {
			$header_layout = \FLTheme::get_setting( 'fl-header-layout' );

			if ( 'none' === $header_layout || is_page_template( 'tpl-no-header-footer.php' ) ) {
				return;
			}
		}

		do_action( 'wowdevs_themes_builder_template_before_header' );
		?>
		<header id="masthead" itemscope="itemscope" itemtype="https://schema.org/WPHeader">
			<div class="wowdevs-template-content-markup wowdevs-template-content-header">
				<?php
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo wowdevs_render_elementor_content( $this->header );
				?>
			</div>
		</header>
		<style>
			[data-type="header"] {
				display: none !important;
			}
		</style>
		<?php
		do_action( 'wowdevs_themes_builder_template_after_header' );
	}

	public function add_plugin_footer_markup() {
		if ( is_page_template( 'tpl-no-header-footer.php' ) ) {
			return;
		}

		do_action( 'wowdevs_themes_builder_template_before_footer' );
		?>

		<footer itemscope="itemscope" itemtype="https://schema.org/WPFooter">
			<?php
      // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo wowdevs_render_elementor_content( $this->footer );
			?>
		</footer>

		<?php
		do_action( 'wowdevs_themes_builder_template_after_footer' );
	}
}

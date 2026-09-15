<?php

namespace Sky_Addons\ThemeBuilder\Themes_Hooks;

defined( 'ABSPATH' ) || exit;

class Default_Support {

	private $header;
	private $footer;

	public function __construct( $template_ids ) {
		$this->header = $template_ids['header'];
		$this->footer = $template_ids['footer'];

		if ( null !== $this->header ) {
			add_action( 'get_header', [ $this, 'get_header' ] );
		}

		if ( null !== $this->footer ) {
			add_action( 'get_footer', [ $this, 'get_footer' ] );
		}
	}

	public function get_header( $name = null ) {
		require_once SKY_ADDONS_INC_PATH . 'theme-builder/support/default-header.php';

		$templates = [];
		$name      = (string) $name;
		if ( '' !== $name ) {
			$templates[] = "header-{$name}.php";
		}

		$templates[] = 'header.php';

		// Avoid running wp_head hooks again
		remove_all_actions( 'wp_head' );
		ob_start();
		// It cause a `require_once` so, in the get_header it self it will not be required again.
		locate_template( $templates, true );
		ob_get_clean();
	}

	public function get_footer( $name = null ) {
		require SKY_ADDONS_INC_PATH . 'theme-builder/support/default-footer.php';
		$templates = [];
		$name      = (string) $name;
		if ( '' !== $name ) {
			$templates[] = "footer-{$name}.php";
		}

		$templates[] = 'footer.php';

		// Avoid running wp_footer hooks again — default-footer.php has already
		// fired them, and the theme's own footer.php calls wp_footer() a second
		// time inside the buffer below. Scripts are deduped by WP_Scripts, but
		// arbitrary callbacks (analytics init, cart fragments) would run twice.
		// Mirrors the remove_all_actions( 'wp_head' ) guard in get_header().
		remove_all_actions( 'wp_footer' );
		ob_start();
		// It cause a `require_once` so, in the get_header it self it will not be required again.
		locate_template( $templates, true );
		ob_get_clean();
	}
}

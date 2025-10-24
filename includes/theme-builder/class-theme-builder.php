<?php

namespace Sky_Addons\ThemeBuilder;

defined( 'ABSPATH' ) || exit;

class Theme_Builder {
	public static $instance = null;

	protected $templates;
	public $header_template;
	public $footer_template;

	public $single_template;

	protected $current_theme;
	protected $current_template;

	protected $archive_template;

	protected $not_found_template;

	protected $custom_hooks = [];

	protected $post_type = 'wowdevs-hooks';


	public function __construct() {
		$this->includes();
		add_action( 'wp', [ $this, 'apply_conditions' ] );
		add_action( 'wp', [ $this, 'hooks' ] );
		add_filter( 'template_include', [ $this, 'set_builder_template' ], 9999 );
	}

	public function includes() {
		require_once SKY_ADDONS_INC_PATH . 'theme-builder/class-builder-data.php';

		require_once SKY_ADDONS_INC_PATH . 'theme-builder/themes/astra.php';
		require_once SKY_ADDONS_INC_PATH . 'theme-builder/themes/bbtheme.php';
		require_once SKY_ADDONS_INC_PATH . 'theme-builder/themes/default-support.php';
		require_once SKY_ADDONS_INC_PATH . 'theme-builder/themes/generatepress.php';
		require_once SKY_ADDONS_INC_PATH . 'theme-builder/themes/genesis.php';
		require_once SKY_ADDONS_INC_PATH . 'theme-builder/themes/neve.php';
		require_once SKY_ADDONS_INC_PATH . 'theme-builder/themes/oceanwp.php';

		if ( apply_filters( 'sky_addons_pro_init', false ) && defined( 'SKY_ADDONS_PRO_INC_PATH' ) ) {
			if ( file_exists( SKY_ADDONS_PRO_INC_PATH . 'theme-builder/support/custom-hooks.php' ) ) {
				require_once SKY_ADDONS_PRO_INC_PATH . 'theme-builder/support/custom-hooks.php';
				$this->get_custom_hooks();
			}
		}
	}

	public function hooks() {

		$this->current_template = basename( get_page_template_slug() );

		if ( 'elementor_canvas' === $this->current_template ) {
			return;
		}

		$this->current_theme = get_template();
		$template_ids = [
			'header'  => $this->header_template,
			'footer'  => $this->footer_template,
			'single'  => $this->single_template,
			'archive' => $this->archive_template,
			'404'     => $this->not_found_template,
		];

		switch ( $this->current_theme ) {
			case 'astra':
				new Themes_Hooks\Astra( $template_ids );
				break;

			case 'neve':
				new Themes_Hooks\Neve( $template_ids );
				break;

			case 'generatepress':
			case 'generatepress-child':
				new Themes_Hooks\Generatepress( $template_ids );
				break;

			case 'oceanwp':
			case 'oceanwp-child':
				new Themes_Hooks\Oceanwp( $template_ids );
				break;

			case 'bb-theme':
			case 'bb-theme-child':
				new Themes_Hooks\Bbtheme( $template_ids );
				break;

			case 'genesis':
			case 'genesis-child':
				new Themes_Hooks\Genesis( $template_ids );
				break;

			default:
				new Themes_Hooks\Default_Support( $template_ids );
				break;
		}
	}

	/**
	 * Apply Conditions for Header, Footer, Single, Archive, 404
	 */
	public function apply_conditions() {
		$this->templates = $this->get_theme_templates();

		// if ( ! is_admin() ) {
		// }
		$this->match_conditions();
	}

	/**
	 * Fetch all Theme Builder Templates
	 */
	private function get_theme_templates() {
		$args = [
			'post_type'      => $this->post_type,
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'meta_query'     => [
				'relation' => 'AND',
				[
					'key'     => 'wowdevs_theme_builder_type',
					'value'   => [ 'header', 'footer', 'single', 'archive', '404', 'custom_hooks' ],
					'compare' => 'IN',
				],
				[
					'key'     => 'wowdevs_theme_builder_status',
					'value'   => 'enabled',
					'compare' => '=',
				],
			],
		];

		return get_posts( $args );
	}

	/**
	 * Match Conditions and Assign Templates
	 */
	private function match_conditions() {
		if ( ! $this->templates ) {
			return;
		}

		foreach ( $this->templates as $template ) {
			$meta = get_post_meta( $template->ID );

			$display_on       = maybe_unserialize( $meta['wowdevs_theme_builder_display_on'][0] ?? [] );
			$not_display_on   = maybe_unserialize( $meta['wowdevs_theme_builder_not_display_on'][0] ?? [] );
			$display_special  = maybe_unserialize( $meta['wowdevs_theme_builder_display_special_pages'][0] ?? [] );
			$not_display_special = maybe_unserialize( $meta['wowdevs_theme_builder_not_display_special_pages'][0] ?? [] );
			$display_custom   = maybe_unserialize( $meta['wowdevs_theme_builder_display_custom_pages'][0] ?? [] );
			$not_display_custom = maybe_unserialize( $meta['wowdevs_theme_builder_not_display_custom_pages'][0] ?? [] );
			$display_roles    = maybe_unserialize( $meta['wowdevs_theme_builder_display_roles'][0] ?? [] );

			$should_display = $this->should_display_template( $display_on, $not_display_on, $display_special, $not_display_special, $display_custom, $not_display_custom, $display_roles );

			if ( $should_display ) {
				$this->assign_template( $template );
			}
		}
	}

	/**
	 * Determine if a template should be displayed
	 */
	private function should_display_template( $display_on, $not_display_on, $display_special, $not_display_special, $display_custom, $not_display_custom, $display_roles ) {
		global $post;

		$should_display = false;

		// ✅ Check Display Conditions
		if ( in_array( 'entire_site', array_column( $display_on, 'value' ) ) ) {
			$should_display = true;
		}

		if ( is_page() && in_array( 'all_pages', array_column( $display_on, 'value' ) ) ) {
			$should_display = true;
		}

		if ( is_single() && in_array( 'all_posts', array_column( $display_on, 'value' ) ) ) {
			$should_display = true;
		}

		// Special Pages
		if ( is_front_page() && in_array( 'front_page', array_column( $display_special, 'value' ) ) ) {
			$should_display = true;
		}

		if ( is_home() && in_array( 'blog_page', array_column( $display_special, 'value' ) ) ) {
			$should_display = true;
		}

		if ( is_archive() && in_array( 'archive_page', array_column( $display_special, 'value' ) ) ) {
			$should_display = true;
		}

		if ( is_404() && in_array( '404_page', array_column( $display_special, 'value' ) ) ) {
			$should_display = true;
		}

		// Custom Selected Pages
		if ( $post && in_array( $post->ID, array_column( $display_custom, 'value' ) ) ) {
			$should_display = true;
		}

		// ✅ User Role Checks (Properly Structured)
		if ( is_user_logged_in() ) {
			$user = wp_get_current_user();
			$user_roles = array_column( $display_roles, 'value' );

			// Allow if user is in "logged_in" list or their role matches allowed roles
			if ( in_array( 'logged_in', $user_roles ) || ! empty( array_intersect( $user->roles, $user_roles ) ) ) {
				$should_display = true;
			}

			// 🚀 Fix: If "logged_out" is set, prevent display for logged-in users (including admins)
			if ( in_array( 'logged_out', $user_roles ) ) {
				$should_display = false;
			}
		}

		// ✅ If user is logged out and "logged_out" is set, allow display
		if ( ! is_user_logged_in() && in_array( 'logged_out', array_column( $display_roles, 'value' ) ) ) {
			$should_display = true;
		}

		// ❌ Check Not Display Conditions (Overrides Above)
		if ( in_array( 'entire_site', array_column( $not_display_on, 'value' ) ) ) {
			return false;
		}

		if ( is_page() && in_array( 'all_pages', array_column( $not_display_on, 'value' ) ) ) {
			return false;
		}

		if ( is_single() && in_array( 'all_posts', array_column( $not_display_on, 'value' ) ) ) {
			return false;
		}

		if ( is_front_page() && in_array( 'front_page', array_column( $not_display_special, 'value' ) ) ) {
			return false;
		}

		if ( is_home() && in_array( 'blog_page', array_column( $not_display_special, 'value' ) ) ) {
			return false;
		}

		if ( is_archive() && in_array( 'archive_page', array_column( $not_display_special, 'value' ) ) ) {
			return false;
		}

		if ( is_404() && in_array( '404_page', array_column( $not_display_special, 'value' ) ) ) {
			return false;
		}

		if ( $post && in_array( $post->ID, array_column( $not_display_custom, 'value' ) ) ) {
			return false;
		}

		return $should_display;
	}

	/**
	 * Assign the correct template (Header, Footer, Single, etc.)
	 */
	private function assign_template( $template ) {
		$type = get_post_meta( $template->ID, 'wowdevs_theme_builder_type', true );

		switch ( $type ) {
			case 'header':
				$this->header_template = $template->ID;
				break;

			case 'footer':
				$this->footer_template = $template->ID;
				break;

			case 'single':
				if ( apply_filters( 'sky_addons_pro_init', false ) ) {
					$this->single_template = $template->ID;
				}
				break;

			case 'archive':
				$this->archive_template = $template->ID;
				break;

			case '404':
				$this->not_found_template = $template->ID;
				break;

			case 'custom_hooks':
				$this->custom_hooks = $template->ID;
				break;
		}
	}

	/**
	 * Get All Templates IDs
	 */
	public static function template_ids() {
		$instance = self::instance();
		return [
			'header'  => $instance->header_template,
			'footer'  => $instance->footer_template,
			'single'  => $instance->single_template,
			'archive' => $instance->archive_template,
			'404'     => $instance->not_found_template,
		];
	}

	/**
	 * Rewrite default template
	 */
	public function set_builder_template( $template ) {
		if ( $this->is_edit_mode() ) {
			return $this->set_edit_template( $template );
		} else {
			return $this->set_preview_template( $template );
		}
	}

	public function is_edit_mode() {

		if ( 'wowdevs-hooks' === get_post_type() ) {
			return true;
		}

    // phpcs:ignore
		if ( isset( $_REQUEST['wowdevs-hooks'] ) ) {
			return true;
		}
	}

	/**
	 * Get Template Path
	 *
	 * @param $slug
	 * @param $default_path
	 *
	 * @return mixed|string|void
	 */
	protected function get_template_path( $slug, $default_path = '' ) {
		$phpSlug = "{$slug}.php";

		$template = $this->get_plugin_template_path( $phpSlug );
		if ( $template ) {
			return $template;
		}

		return $default_path;
	}

	protected function set_edit_template( $template ) {
		return $template;
	}

	protected function set_preview_template( $template ) {

		if ( defined( 'ELEMENTOR_PATH' ) ) {
			$elementorTem = ELEMENTOR_PATH . 'modules/page-templates/templates/';
			$elementorTem = explode( $elementorTem, $template );
			if ( 2 === count( $elementorTem ) ) {
				return $template;
			}
		}

		// single posts
		if ( is_single() && 'post' === get_post_type() ) {
			$custom_template = $this->get_template_id( 'single', 'post' );
			if ( $custom_template ) {
				$this->current_template = $custom_template;
				return $this->get_template_path( 'posts/single', $template );
			}
		}

		// archive page
		if ( ( is_archive() || is_home() ) && get_post_type( get_the_ID() ) === 'post' ) {
			if ( is_category() ) {
				$custom_template = $this->get_template_id( 'category', 'post' );
				if ( $custom_template ) {
					$this->current_template = $custom_template;
					return $this->get_template_path( 'posts/category', $template );
				}
			} elseif ( is_tag() ) {
				$custom_template = $this->get_template_id( 'tag', 'post' );
				if ( $custom_template ) {
					$this->current_template = $custom_template;
					return $this->get_template_path( 'posts/tag', $template );
				}
			} elseif ( is_author() ) {
				$custom_template = $this->get_template_id( 'author', 'post' );
				if ( $custom_template ) {
					$this->current_template = $custom_template;
					return $this->get_template_path( 'posts/author', $template );
				}
			} elseif ( is_date() ) {
				$custom_template = $this->get_template_id( 'date', 'post' );
				if ( $custom_template ) {
					$this->current_template = $custom_template;
					return $this->get_template_path( 'posts/date', $template );
				}
			} else {
				$custom_template = $this->get_template_id( 'archive', 'post' );
				if ( $custom_template ) {
					$this->current_template = $custom_template;
					return $this->get_template_path( 'posts/archive', $template );
				}
			}
		}

		// Pages
		if ( is_page() && is_page_template() && 'page' === get_post_type() ) {
			$custom_template = $this->get_template_id( 'single', 'page' );
			if ( $custom_template ) {
				$this->current_template = $custom_template;
				return $this->get_template_path( 'pages/single', $template );
			}
		}

		// 404 page
		if ( is_404() ) {
			$custom_template = $this->get_template_id( '404', 'page' );
			if ( $custom_template ) {
				$this->current_template = $custom_template;
				return $this->get_template_path( 'pages/404', $template );
			}
		}

		// search page
		if ( is_search() ) {
			$custom_template = $this->get_template_id( 'search', 'page' );
			if ( $custom_template ) {
				$this->current_template = $custom_template;
				return $this->get_template_path( 'pages/search', $template );
			}
		}

		return $template;
	}

	protected function get_template_id( $type, $post_type ) {
		$template_ids = self::template_ids();
		if ( isset( $template_ids[ $type ] ) ) {
			return $template_ids[ $type ];
		}
		return false;
	}

	public function get_plugin_template_path( $slug ) {

		$fullPath = SKY_ADDONS_INC_PATH . "theme-builder/templates/$slug";
		if ( file_exists( $fullPath ) ) {
			return $fullPath;
		}
	}

	public function get_custom_hooks() {
		if ( apply_filters( 'sky_addons_pro_init', false ) ) {
			$templates = $this->get_theme_templates();
			$hooks = [];
			foreach ( $templates as $template ) {
				$meta = get_post_meta( $template->ID );
				$type = $meta['wowdevs_theme_builder_type'][0];
				$template_id = $template->ID;
				if ( 'custom_hooks' === $type ) {
					$hook_name = $meta['wowdevs_theme_builder_hook'][0];
					$hook_priority = $meta['wowdevs_theme_builder_hook_priority'][0];
					new \Sky_Addons\ThemeBuilder\Custom_Hooks( $hook_name, $hook_priority, $template_id );
				}
			}
		}
	}

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}

Theme_Builder::instance();

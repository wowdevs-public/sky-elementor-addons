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
		require_once SKY_ADDONS_INC_PATH . 'theme-builder/class-builder-context.php';
		require_once SKY_ADDONS_INC_PATH . 'theme-builder/class-template-router.php';
		require_once SKY_ADDONS_INC_PATH . 'theme-builder/class-preview-manager.php';
		require_once SKY_ADDONS_INC_PATH . 'theme-builder/class-live-preview.php';

		Builder_Context::instance();
		Preview_Manager::instance();
		Live_Preview::instance();

		require_once SKY_ADDONS_INC_PATH . 'theme-builder/themes/astra.php';
		require_once SKY_ADDONS_INC_PATH . 'theme-builder/themes/bbtheme.php';
		require_once SKY_ADDONS_INC_PATH . 'theme-builder/themes/default-support.php';
		require_once SKY_ADDONS_INC_PATH . 'theme-builder/themes/generatepress.php';
		require_once SKY_ADDONS_INC_PATH . 'theme-builder/themes/genesis.php';
		require_once SKY_ADDONS_INC_PATH . 'theme-builder/themes/kadence.php';
		require_once SKY_ADDONS_INC_PATH . 'theme-builder/themes/neve.php';
		require_once SKY_ADDONS_INC_PATH . 'theme-builder/themes/oceanwp.php';
		require_once SKY_ADDONS_INC_PATH . 'theme-builder/themes/storefront.php';

		require_once SKY_ADDONS_INC_PATH . 'theme-builder/support/custom-hooks.php';
	}

	/**
	 * Requests that can never render a Theme Builder template.
	 *
	 * Feeds, robots.txt, sitemaps, favicon, trackbacks and oEmbed responses all
	 * run past the `wp` action and then exit at `template_redirect`, before
	 * `template_include` is ever reached — so every template query, meta prime
	 * and theme-support class built for them is pure waste. On a crawled site
	 * these are a meaningful share of all requests.
	 *
	 * REST, admin-ajax and cron never reach `wp` at all, so they need no guard.
	 * 404 deliberately is NOT listed — a 404 template is a supported type.
	 *
	 * @return bool
	 */
	private function is_ignorable_request() {
		return is_feed() || is_robots() || is_favicon() || is_trackback() || is_embed();
	}

	public function hooks() {
		if ( $this->is_ignorable_request() ) {
			return;
		}

		// A live preview renders the template alone on Elementor's Canvas. Injecting
		// the site's assigned header, footer or Custom Hooks around it would bury the
		// design being previewed inside unrelated chrome.
		if ( Live_Preview::instance()->is_preview_request() ) {
			return;
		}

		$this->current_template = basename( get_page_template_slug() );

		if ( 'elementor_canvas' === $this->current_template ) {
			return;
		}

		$this->current_theme = get_template();
		$template_ids        = [
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

			case 'kadence':
				new Themes_Hooks\Kadence( $template_ids );
				break;

			case 'storefront':
				new Themes_Hooks\Storefront( $template_ids );
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
		if ( $this->is_ignorable_request() ) {
			return;
		}

		// A live preview renders the template alone on Elementor's Canvas. Injecting
		// the site's assigned header, footer or Custom Hooks around it would bury the
		// design being previewed inside unrelated chrome.
		if ( Live_Preview::instance()->is_preview_request() ) {
			return;
		}

		$this->templates = $this->get_theme_templates();
		$this->match_conditions();
		$this->get_custom_hooks();
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

			$display_on          = maybe_unserialize( $meta['wowdevs_theme_builder_display_on'][0] ?? [] );
			$not_display_on      = maybe_unserialize( $meta['wowdevs_theme_builder_not_display_on'][0] ?? [] );
			$display_special     = maybe_unserialize( $meta['wowdevs_theme_builder_display_special_pages'][0] ?? [] );
			$not_display_special = maybe_unserialize( $meta['wowdevs_theme_builder_not_display_special_pages'][0] ?? [] );
			$display_custom      = maybe_unserialize( $meta['wowdevs_theme_builder_display_custom_pages'][0] ?? [] );
			$not_display_custom  = maybe_unserialize( $meta['wowdevs_theme_builder_not_display_custom_pages'][0] ?? [] );
			$display_roles       = maybe_unserialize( $meta['wowdevs_theme_builder_display_roles'][0] ?? [] );

			$should_display = $this->should_display_template( $display_on, $not_display_on, $display_special, $not_display_special, $display_custom, $not_display_custom, $display_roles );

			if ( $should_display ) {
				$this->assign_template( $template );
			}
		}
	}

	/**
	 * Determine if a template should be displayed.
	 *
	 * Three-step AND model:
	 *   Step 1 — WHERE  : page/location must match at least one Display On condition.
	 *   Step 2 — WHO    : if roles are set, current user must match (empty roles = no restriction).
	 *   Step 3 — EXCEPT : Not Display On overrides everything — always wins.
	 *
	 * Meta values are stored as [{value:"..."}] objects; array_column(...,'value') extracts them.
	 * Custom page IDs stored as strings from JS, so cast $post->ID to string for comparison.
	 * Custom page checks guarded by is_singular() to prevent false matches on archive pages.
	 */
	private function should_display_template( $display_on, $not_display_on, $display_special, $not_display_special, $display_custom, $not_display_custom, $display_roles ) {
		global $post;

		// ── Step 1: WHERE ────────────────────────────────────────────────────────
		// Must match at least one location — no match means skip this template entirely.
		$display_on_values      = array_column( $display_on, 'value' );
		$display_special_values = array_column( $display_special, 'value' );
		$display_custom_values  = array_column( $display_custom, 'value' );

		$location_match = false;

		if ( in_array( 'entire_site', $display_on_values ) ) {
			$location_match = true;
		} elseif ( is_page() && in_array( 'all_pages', $display_on_values ) ) {
			$location_match = true;
		} elseif ( is_single() && in_array( 'all_posts', $display_on_values ) ) {
			// is_single() covers posts + all custom post type singles
			$location_match = true;
		} elseif ( is_front_page() && in_array( 'front_page', $display_special_values ) ) {
			$location_match = true;
		} elseif ( is_home() && in_array( 'blog_page', $display_special_values ) ) {
			// is_home() = blog posts index; is_archive() does NOT include it
			$location_match = true;
		} elseif ( is_archive() && in_array( 'archive_page', $display_special_values ) ) {
			// is_archive() covers category, tag, author, date, CPT archives
			$location_match = true;
		} elseif ( is_search() && in_array( 'search_page', $display_special_values ) ) {
			// is_search() is NOT covered by is_archive() — search needs its own option
			$location_match = true;
		} elseif ( is_404() && in_array( '404_page', $display_special_values ) ) {
			$location_match = true;
		} elseif ( is_singular() && $post && in_array( (string) $post->ID, $display_custom_values ) ) {
			// is_singular() guard prevents $post false-positives on archive pages
			$location_match = true;
		}

		if ( ! $location_match ) {
			return false;
		}

		// ── Step 2: WHO ──────────────────────────────────────────────────────────
		// If roles configured and not all_users, current user must match.
		// Empty roles or all_users = no restriction, skip this check entirely.
		$user_role_values = array_column( $display_roles, 'value' );

		if ( ! empty( $user_role_values ) && ! in_array( 'all_users', $user_role_values ) ) {
			$role_match = false;

			if ( is_user_logged_in() ) {
				$user = wp_get_current_user();
				// 'logged_in' matches any authenticated user; specific roles matched via intersect
				if ( in_array( 'logged_in', $user_role_values ) || ! empty( array_intersect( $user->roles, $user_role_values ) ) ) {
					$role_match = true;
				}
			} elseif ( in_array( 'logged_out', $user_role_values ) ) {
				// logged_out only matches unauthenticated visitors
				$role_match = true;
			}

			if ( ! $role_match ) {
				return false;
			}
		}

		// ── Step 3: EXCEPTIONS ───────────────────────────────────────────────────
		// Not Display On overrides Steps 1 & 2 entirely — return false on any match.
		$not_display_on_values      = array_column( $not_display_on, 'value' );
		$not_display_special_values = array_column( $not_display_special, 'value' );
		$not_display_custom_values  = array_column( $not_display_custom, 'value' );

		if ( in_array( 'entire_site', $not_display_on_values ) ) {
			return false;
		}

		if ( is_page() && in_array( 'all_pages', $not_display_on_values ) ) {
			return false;
		}

		if ( is_single() && in_array( 'all_posts', $not_display_on_values ) ) {
			return false;
		}

		if ( is_front_page() && in_array( 'front_page', $not_display_special_values ) ) {
			return false;
		}

		if ( is_home() && in_array( 'blog_page', $not_display_special_values ) ) {
			return false;
		}

		if ( is_archive() && in_array( 'archive_page', $not_display_special_values ) ) {
			return false;
		}

		if ( is_search() && in_array( 'search_page', $not_display_special_values ) ) {
			return false;
		}

		if ( is_404() && in_array( '404_page', $not_display_special_values ) ) {
			return false;
		}

		if ( is_singular() && $post && in_array( (string) $post->ID, $not_display_custom_values ) ) {
			return false;
		}

		return true;
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
				$this->single_template = $template->ID;
				break;

			case 'archive':
				$this->archive_template = $template->ID;
				break;

			case '404':
				$this->not_found_template = $template->ID;
				break;

			case 'custom_hooks':
				$this->custom_hooks[] = $template->ID;
				break;
		}
	}

	/**
	 * Get All Templates IDs
	 *
	 * The archive-family keys (category / tag / author / date / home / search)
	 * are aliases of the resolved `archive` template. There is deliberately no
	 * separate template type for each: one Archive template covers every
	 * archive screen, and narrowing is done with display conditions. The alias
	 * keys exist so the per-screen template files stay meaningful override
	 * points for theme authors.
	 */
	public static function template_ids() {
		$instance = self::instance();
		$archive  = $instance->archive_template;

		return [
			'header'   => $instance->header_template,
			'footer'   => $instance->footer_template,
			'single'   => $instance->single_template,
			'archive'  => $archive,
			'404'      => $instance->not_found_template,
			'category' => $archive,
			'tag'      => $archive,
			'author'   => $archive,
			'date'     => $archive,
			'home'     => $archive,
			'search'   => $archive,
		];
	}

	/**
	 * Render a resolved Theme Builder template.
	 *
	 * Every template file in templates/ calls this, so the Elementor guard, the
	 * before/after hooks and the postdata reset live in exactly one place.
	 *
	 * The Elementor content is printed *outside* the loop on purpose: widgets
	 * resolve their post through Builder_Context (queried object), and archive
	 * widgets read the untouched main query. Opening the loop here would leave
	 * the global pointer on the last post of the archive.
	 *
	 * @param string $type Template type key from template_ids().
	 */
	public static function render_template( $type ) {
		$templates = self::template_ids();

		/**
		 * Fires inside a Theme Builder template, before the Elementor content.
		 *
		 * @param string $type Template type being rendered.
		 */
		do_action( 'wowdevs_themes_builder_template_before_main_content', $type );

		if ( ! empty( $templates[ $type ] ) && class_exists( '\Elementor\Plugin' ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Elementor-rendered builder content.
			echo wowdevs_render_elementor_content( $templates[ $type ] );
			wp_reset_postdata();
		}

		/**
		 * Fires inside a Theme Builder template, after the Elementor content.
		 *
		 * @param string $type Template type being rendered.
		 */
		do_action( 'wowdevs_themes_builder_template_after_main_content', $type );
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
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_REQUEST['wowdevs-hooks'] ) ) {
			return true;
		}
		return false;
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

		// $this->current_template is deliberately NOT written here. On the `wp`
		// action hooks() stores a page-template *slug* in it and compares that
		// slug to 'elementor_canvas'. Writing a template post ID into the same
		// property would leave it holding two unrelated types, and nothing reads
		// it after this point. Template_Router::get_matched_template_id() is the
		// accessor if the resolved ID is ever needed.
		return Template_Router::instance()->resolve( $template );
	}

	public function get_plugin_template_path( $slug ) {

		$fullPath = SKY_ADDONS_INC_PATH . "theme-builder/templates/$slug";
		if ( file_exists( $fullPath ) ) {
			return $fullPath;
		}
	}

	/**
	 * Register the Custom Hooks templates that matched the current page.
	 *
	 * Reads $this->custom_hooks — the list assign_template() builds from
	 * condition-matched templates only.
	 *
	 * Before 4.5.0 this method re-walked $this->templates (every *enabled*
	 * template, matched or not), so a Custom Hook restricted to, say, the front
	 * page still rendered its full Elementor document on every page of the site:
	 * the Display On / Exclude From settings were collected correctly and then
	 * ignored. That also made unmatched hooks the single largest per-request
	 * cost in the theme builder.
	 */
	public function get_custom_hooks() {
		if ( empty( $this->custom_hooks ) ) {
			return;
		}

		foreach ( $this->custom_hooks as $template_id ) {
			$hook_name = get_post_meta( $template_id, 'wowdevs_theme_builder_hook', true );

			if ( empty( $hook_name ) ) {
				continue;
			}

			$hook_priority = get_post_meta( $template_id, 'wowdevs_theme_builder_hook_priority', true );
			$hook_priority = ( '' === $hook_priority || null === $hook_priority ) ? 10 : (int) $hook_priority;

			new \Sky_Addons\ThemeBuilder\Custom_Hooks( $hook_name, $hook_priority, $template_id );
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

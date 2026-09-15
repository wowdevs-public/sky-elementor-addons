<?php

namespace Sky_Addons\ThemeBuilder;

defined( 'ABSPATH' ) || exit;

/**
 * Maps the current screen to a Theme Builder template file.
 *
 * Split out of Theme_Builder so the condition engine (which decides *whether* a
 * template applies) stays untouched and this class only decides *which file*
 * renders it.
 *
 * ── Scope: three states, opt-in widening ─────────────────────────────────────
 * The `wowdevs_theme_builder_post_type` meta decides how far a template reaches.
 *
 *   absent            LEGACY. Every template saved before this meta existed is
 *                     in this state, and it behaves exactly as it did:
 *                       single  → posts, plus pages using a custom page template
 *                       archive → the `post` post type only
 *                     An existing site therefore cannot suddenly render its blog
 *                     Single template on WooCommerce products, and its Archive
 *                     template cannot take over the shop or a portfolio archive.
 *
 *   a post type slug  Exactly that post type — `product` singles, the `product`
 *                     archive and product taxonomies, and nothing else.
 *
 *   `any`             Every singular screen (pages with no page template, the
 *                     static front page, attachments, any CPT) and every archive
 *                     screen (custom taxonomies, CPT archives, search results).
 *
 * Widening is always the user's explicit choice — nothing widens on upgrade.
 *
 * The one deliberate behaviour change: category, tag, author and date archives
 * now receive the Archive template. They never did before — the old lookup asked
 * for `category` / `tag` / `author` / `date` template IDs that were never
 * registered, so it always returned false and those screens silently fell back
 * to the theme. Users who ticked "Archive Page" always intended this.
 *
 * @since 4.5.0
 */
class Template_Router {

	/**
	 * Post-type target meaning "every post type / every archive screen".
	 */
	const SCOPE_ANY = 'any';

	/**
	 * @var self|null
	 */
	private static $instance = null;

	/**
	 * Template ID resolved for the current screen, for Theme_Builder to record.
	 *
	 * @var int|false
	 */
	private $matched_template_id = false;

	/**
	 * @return self
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {}

	/**
	 * @return int|false Template matched by the last resolve() call.
	 */
	public function get_matched_template_id() {
		return $this->matched_template_id;
	}

	/**
	 * Resolve the current screen to a plugin template file.
	 *
	 * @param string $fallback Template WordPress already resolved.
	 * @return string
	 */
	public function resolve( $fallback ) {
		$this->matched_template_id = false;

		if ( is_404() ) {
			$path = $this->route( '404', [ 'pages/404' ], $fallback );
		} elseif ( is_search() ) {
			$path = $this->route_search( $fallback );
		} elseif ( is_singular() ) {
			$path = $this->route_singular( $fallback );
		} elseif ( is_archive() || is_home() ) {
			$path = $this->route_archive( $fallback );
		} else {
			$path = $fallback;
		}

		/**
		 * Filters the template file a Theme Builder screen resolves to.
		 *
		 * Return $fallback to hand the screen back to the theme — the escape
		 * hatch for a site that wants a specific screen left alone.
		 *
		 * @param string   $path        Resolved template file, or $fallback.
		 * @param string   $fallback     Template WordPress resolved.
		 * @param int|false $template_id Matched template post ID.
		 */
		$path = apply_filters( 'sky_addons/theme-builder/template_path', $path, $fallback, $this->matched_template_id );

		if ( $path === $fallback ) {
			$this->matched_template_id = false;
		}

		return $path;
	}

	/**
	 * Single posts, pages and custom post type singles.
	 *
	 * @param string $fallback
	 * @return string
	 */
	private function route_singular( $fallback ) {
		$template_id = $this->get_template_id( 'single' );

		if ( ! $template_id ) {
			return $fallback;
		}

		$post_type = (string) get_post_type( get_queried_object_id() );
		$target    = Builder_Context::instance()->get_template_post_type( $template_id );

		if ( self::SCOPE_ANY === $target ) {
			// Every singular screen: pages with no page template, the static
			// front page, attachments, products, any custom post type.
			$matched = true;
		} elseif ( $target ) {
			$matched = ( $post_type === $target );
		} else {
			$matched = $this->matches_legacy_singular_scope( $post_type );
		}

		if ( ! $matched ) {
			return $fallback;
		}

		$slugs = is_page() ? [ 'pages/single', 'posts/single' ] : [ 'posts/single' ];

		return $this->route( 'single', $slugs, $fallback );
	}

	/**
	 * Search results.
	 *
	 * Opt-in only, by either route: the author ticked Display On → Search Page,
	 * or the template is scoped to "any". Search spans every post type, so a
	 * legacy or single-post-type template that never asked for search keeps
	 * leaving it to the theme, exactly as before.
	 *
	 * @param string $fallback
	 * @return string
	 */
	private function route_search( $fallback ) {
		$template_id = $this->get_template_id( 'archive' );

		if ( ! $template_id ) {
			return $fallback;
		}

		$context = Builder_Context::instance();

		if (
			self::SCOPE_ANY !== $context->get_template_post_type( $template_id )
			&& ! $context->template_targets_search( $template_id )
		) {
			return $fallback;
		}

		return $this->route( 'search', [ 'pages/search', 'posts/archive' ], $fallback );
	}

	/**
	 * Legacy scope for templates with no post-type target.
	 *
	 * Reproduces the two pre-4.5.0 branches verbatim — including the
	 * `is_single()` guard, which is what kept pages and attachments out of the
	 * post branch — so an existing site cannot shift by a single screen:
	 *
	 *   is_single() && 'post' === get_post_type()          → posts/single
	 *   is_page() && is_page_template() && 'page' === ...   → pages/single
	 *
	 * @param string $post_type
	 * @return bool
	 */
	private function matches_legacy_singular_scope( $post_type ) {
		if ( is_single() && 'post' === $post_type ) {
			return true;
		}

		return is_page() && is_page_template() && 'page' === $post_type;
	}

	/**
	 * Category, tag, author, date, CPT archives and the blog index.
	 *
	 * @param string $fallback
	 * @return string
	 */
	private function route_archive( $fallback ) {
		$template_id = $this->get_template_id( 'archive' );

		if ( ! $template_id ) {
			return $fallback;
		}

		$target = Builder_Context::instance()->get_template_post_type( $template_id );

		if ( self::SCOPE_ANY !== $target ) {
			// No target = legacy scope: the `post` post type only.
			$post_type = $this->get_archive_post_type();

			if ( ( $target ? $target : 'post' ) !== $post_type ) {
				return $fallback;
			}
		}

		if ( is_category() ) {
			$slugs = [ 'posts/category', 'posts/archive' ];
		} elseif ( is_tag() ) {
			$slugs = [ 'posts/tag', 'posts/archive' ];
		} elseif ( is_author() ) {
			$slugs = [ 'posts/author', 'posts/archive' ];
		} elseif ( is_date() ) {
			$slugs = [ 'posts/date', 'posts/archive' ];
		} else {
			$slugs = [ 'posts/archive' ];
		}

		return $this->route( 'archive', $slugs, $fallback );
	}

	/**
	 * Post type the current archive lists.
	 *
	 * Resolved from the queried object rather than the first post in the loop,
	 * so an empty archive still resolves to the right post type.
	 *
	 * @return string
	 */
	private function get_archive_post_type() {
		if ( is_post_type_archive() ) {
			$queried = get_queried_object();

			return isset( $queried->name ) ? (string) $queried->name : '';
		}

		if ( is_tax() ) {
			$queried = get_queried_object();

			if ( isset( $queried->taxonomy ) ) {
				$taxonomy = get_taxonomy( $queried->taxonomy );

				if ( $taxonomy && ! empty( $taxonomy->object_type ) ) {
					return (string) reset( $taxonomy->object_type );
				}
			}

			return '';
		}

		// Category, tag, author, date and the blog index all list posts.
		return 'post';
	}

	/**
	 * Pick the first template file that exists.
	 *
	 * $slugs is a preference list, most specific first, so a missing per-screen
	 * file falls back to the generic one instead of dropping the template.
	 *
	 * @param string   $type
	 * @param string[] $slugs
	 * @param string   $fallback
	 * @return string
	 */
	private function route( $type, array $slugs, $fallback ) {
		$template_id = $this->get_template_id( $type );

		if ( ! $template_id ) {
			return $fallback;
		}

		foreach ( $slugs as $slug ) {
			// One source of truth for the templates directory — Theme_Builder
			// already owns it and returns null when the file is missing.
			$path = Theme_Builder::instance()->get_plugin_template_path( "{$slug}.php" );

			if ( $path ) {
				$this->matched_template_id = $template_id;

				return $path;
			}
		}

		return $fallback;
	}

	/**
	 * @param string $type Template type key from Theme_Builder::template_ids().
	 * @return int|false
	 */
	private function get_template_id( $type ) {
		$template_ids = Theme_Builder::template_ids();

		return empty( $template_ids[ $type ] ) ? false : $template_ids[ $type ];
	}
}

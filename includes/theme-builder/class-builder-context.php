<?php

namespace Sky_Addons\ThemeBuilder;

defined( 'ABSPATH' ) || exit;

/**
 * Theme Builder rendering context.
 *
 * Answers one question for every theme builder widget: *which* post or query is
 * this template actually about?
 *
 * Elementor swaps the global $post to the template document while rendering a
 * builder document, so a widget that calls get_the_ID() / get_the_title()
 * directly renders the template's own title instead of the visitor's post.
 * Everything theme-builder aware must resolve through this class.
 *
 * @since 4.5.0
 */
class Builder_Context {

	const POST_TYPE = 'wowdevs-hooks';

	/**
	 * Elementor page-setting key holding the sample post used while editing a
	 * `single` template.
	 */
	const SAMPLE_POST_SETTING = 'sky_tb_sample_post_id';

	/**
	 * Template types that render against an archive-style query rather than a
	 * single post.
	 */
	const ARCHIVE_TYPES = [ 'archive', 'search' ];

	/**
	 * @var self|null
	 */
	private static $instance = null;

	/**
	 * Memoised sample post IDs, keyed by template ID.
	 *
	 * @var array<int,int>
	 */
	private $sample_post_ids = [];

	/**
	 * Memoised result of get_editing_template_id(). null until resolved.
	 *
	 * @var int|null
	 */
	private $editing_template_id = null;

	/**
	 * @return self
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {
		add_action( 'elementor/elements/categories_registered', [ $this, 'register_widget_category' ] );
	}

	/**
	 * Register the Theme Builder panel category.
	 *
	 * Registered unconditionally — Elementor hides categories with no visible
	 * widgets, and each widget gates itself through show_in_panel(). Gating the
	 * category itself would orphan the widgets into "General" whenever the
	 * document post type could not be detected.
	 *
	 * @param \Elementor\Elements_Manager $elements_manager
	 */
	public function register_widget_category( $elements_manager ) {
		$elements_manager->add_category(
			'sky-theme-builder',
			[
				'title' => esc_html__( 'Sky Theme Builder', 'sky-elementor-addons' ),
				'icon'  => 'font',
			]
		);
	}

	// ── Document detection ──

	/**
	 * Is the document currently being edited a theme builder template?
	 *
	 * Used by widget show_in_panel(). elementor_library is included so template
	 * kits and Elementor Pro theme templates can reuse the same widgets.
	 *
	 * @return bool
	 */
	public static function is_builder_document() {
		$post_type = get_post_type();

		if ( in_array( $post_type, [ self::POST_TYPE, 'elementor_library' ], true ) ) {
			return true;
		}

		return (bool) self::instance()->get_editing_template_id();
	}

	/**
	 * True wherever a template is being *previewed* rather than served to a
	 * visitor — the Elementor editor, and the dashboard's live preview.
	 *
	 * Both need the same stand-in context: a sample post for `single`, a
	 * condition-derived query for `archive`. Neither is a real visitor request,
	 * so neither has a post or query of its own to render against.
	 *
	 * Kept separate from is_editor() on purpose. Widgets branch on is_editor()
	 * to draw editor-only affordances (placeholders, "select a source" notices);
	 * the live preview is a front-end render and must not get those.
	 *
	 * @return bool
	 */
	public function is_preview_context() {
		return $this->is_editor() || Live_Preview::instance()->is_preview_request();
	}

	/**
	 * True while Elementor renders inside the editor, the preview iframe, or an
	 * editor AJAX round trip.
	 *
	 * @return bool
	 */
	public function is_editor() {
		if ( ! class_exists( '\Elementor\Plugin' ) ) {
			return false;
		}

		$elementor = \Elementor\Plugin::instance();

		if ( $elementor->editor->is_edit_mode() || $elementor->preview->is_preview_mode() ) {
			return true;
		}

		return wp_doing_ajax() && (bool) $this->get_editing_template_id();
	}

	/**
	 * Resolve the theme builder template being edited or previewed.
	 *
	 * Checks the frontend preview flag, the editor request, then the global post.
	 *
	 * A request-supplied ID is only trusted when it really is a theme builder
	 * template AND the current user may edit it. Templates are admin-only, so a
	 * crafted link cannot steer anyone else's preview context. No nonce: this
	 * selects which post to *preview*, it never writes.
	 *
	 * @return int 0 when not editing a template.
	 */
	public function get_editing_template_id() {
		// Memoised: widgets call this once per render through the context trait,
		// and it does three superglobal probes plus get_post_type() lookups.
		// null = not resolved yet, 0 = resolved to "not editing a template".
		if ( null !== $this->editing_template_id ) {
			return $this->editing_template_id;
		}

		$this->editing_template_id = $this->resolve_editing_template_id();

		return $this->editing_template_id;
	}

	/**
	 * @return int
	 */
	private function resolve_editing_template_id() {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		foreach ( [ 'sky_tb_template_id', 'post', 'preview_id' ] as $key ) {
			if ( empty( $_REQUEST[ $key ] ) || ! is_scalar( $_REQUEST[ $key ] ) ) {
				// Reject `?post[]=1` — absint() silently coerces an array to 1.
				continue;
			}

			$candidate = absint( wp_unslash( $_REQUEST[ $key ] ) );

			if ( ! $candidate || self::POST_TYPE !== get_post_type( $candidate ) ) {
				continue;
			}

			if ( current_user_can( 'edit_post', $candidate ) ) {
				return $candidate;
			}
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		// Fallback: the global post is itself a template. Capability-checked for
		// the same reason as the request path above — the CPT is
		// publicly_queryable, so a visitor can land on a template URL directly
		// and this must not hand them an editing context.
		$current = get_the_ID();

		if ( $current && self::POST_TYPE === get_post_type( $current ) && current_user_can( 'edit_post', $current ) ) {
			return (int) $current;
		}

		return 0;
	}

	/**
	 * @param int $template_id
	 * @return string Template type slug, '' when unknown.
	 */
	public function get_template_type( $template_id ) {
		if ( ! $template_id ) {
			return '';
		}

		return (string) get_post_meta( $template_id, 'wowdevs_theme_builder_type', true );
	}

	/**
	 * Post type a template targets.
	 *
	 * Stored in the same [{value:"…"}] shape as every other theme builder array
	 * meta. Absent meta means "no restriction" — that keeps every template saved
	 * before this meta existed working exactly as it did.
	 *
	 * @param int $template_id
	 * @return string '' when unrestricted.
	 */
	public function get_template_post_type( $template_id ) {
		if ( ! $template_id ) {
			return '';
		}

		$stored = get_post_meta( $template_id, 'wowdevs_theme_builder_post_type', true );

		if ( empty( $stored ) ) {
			return '';
		}

		if ( is_string( $stored ) ) {
			return $stored;
		}

		$values = array_column( (array) $stored, 'value' );
		$first  = reset( $values );

		return $first ? (string) $first : '';
	}

	/**
	 * @param int $template_id
	 * @return bool
	 */
	/**
	 * Did the author explicitly tick "Search Page" on this template?
	 *
	 * Search is not covered by is_archive(), so it needs its own opt-in. Reading
	 * the condition here keeps the router from having to guess.
	 *
	 * @param int $template_id
	 * @return bool
	 */
	public function template_targets_search( $template_id ) {
		if ( ! $template_id ) {
			return false;
		}

		$special = get_post_meta( $template_id, 'wowdevs_theme_builder_display_special_pages', true );

		return in_array( 'search_page', array_column( (array) $special, 'value' ), true );
	}

	/**
	 * @param int $template_id
	 * @return bool
	 */
	public function is_archive_template( $template_id ) {
		return in_array( $this->get_template_type( $template_id ), self::ARCHIVE_TYPES, true );
	}

	// ── Post resolution ──

	/**
	 * The post a single template is rendering for.
	 *
	 * Frontend  → the queried singular post, never the global $post (Elementor
	 *             has already swapped it to the template document).
	 * Editor    → the template's sample post.
	 * Fallback  → whatever the loop currently holds.
	 *
	 * @return int
	 */
	public function get_queried_post_id() {
		$template_id = $this->get_editing_template_id();

		if ( $template_id ) {
			$sample = $this->get_sample_post_id( $template_id );

			if ( $sample ) {
				return $sample;
			}
		}

		if ( is_singular() ) {
			$queried = (int) get_queried_object_id();

			if ( $queried ) {
				return $queried;
			}
		}

		return (int) get_the_ID();
	}

	/**
	 * @return \WP_Post|null
	 */
	public function get_queried_post() {
		$post_id = $this->get_queried_post_id();

		return $post_id ? get_post( $post_id ) : null;
	}

	/**
	 * Sample post shown while editing a `single` template.
	 *
	 * Resolution order: the author's explicit pick (Elementor page setting) →
	 * the newest published post of the targeted post type. Memoised per request
	 * so repeated widget renders never re-query.
	 *
	 * @param int $template_id
	 * @return int 0 when the template is not a single template.
	 */
	public function get_sample_post_id( $template_id ) {
		$template_id = absint( $template_id );

		if ( ! $template_id ) {
			return 0;
		}

		if ( isset( $this->sample_post_ids[ $template_id ] ) ) {
			return $this->sample_post_ids[ $template_id ];
		}

		$this->sample_post_ids[ $template_id ] = 0;

		if ( 'single' !== $this->get_template_type( $template_id ) ) {
			return 0;
		}

		$selected = $this->get_document_setting( $template_id, self::SAMPLE_POST_SETTING );

		if ( $selected && 'publish' === get_post_status( $selected ) ) {
			$this->sample_post_ids[ $template_id ] = (int) $selected;

			return $this->sample_post_ids[ $template_id ];
		}

		$post_type = $this->get_template_post_type( $template_id );

		$latest = get_posts(
			[
				'post_type'              => $post_type ? $post_type : 'post',
				'post_status'            => 'publish',
				'posts_per_page'         => 1,
				'fields'                 => 'ids',
				'orderby'                => 'date',
				'order'                  => 'DESC',
				'suppress_filters'       => false,
				'ignore_sticky_posts'    => true,
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			]
		);

		if ( ! empty( $latest ) ) {
			$this->sample_post_ids[ $template_id ] = (int) $latest[0];
		}

		return $this->sample_post_ids[ $template_id ];
	}

	/**
	 * Read one Elementor page setting off a template document.
	 *
	 * @param int    $template_id
	 * @param string $key
	 * @return mixed null when Elementor or the setting is unavailable.
	 */
	private function get_document_setting( $template_id, $key ) {
		if ( ! class_exists( '\Elementor\Core\Settings\Manager' ) ) {
			return null;
		}

		$manager = \Elementor\Core\Settings\Manager::get_settings_managers( 'page' );

		if ( ! $manager ) {
			return null;
		}

		$model = $manager->get_model( $template_id );

		return $model ? $model->get_settings( $key ) : null;
	}

	// ── Archive preview ──

	/**
	 * Query args that stand in for the real archive query while an archive
	 * template is being edited.
	 *
	 * Derived from the template's own display conditions so the preview matches
	 * where the template will actually appear.
	 *
	 * @param int $template_id
	 * @return array<string,mixed>
	 */
	public function get_preview_query_args( $template_id ) {
		$template_id = absint( $template_id );
		$post_type   = $this->get_template_post_type( $template_id );

		$args = [
			'post_type'   => $post_type ? $post_type : 'post',
			'post_status' => 'publish',
		];

		if ( 'search' === $this->get_template_type( $template_id ) ) {
			return [ 's' => '' ] + $args;
		}

		// Give the preview a real term context where we can. Archive-context
		// dynamic tags (Archive Title, Archive Description) read the queried
		// object via get_the_archive_title(); a bare post_type query is not
		// is_category(), so those tags would preview blank. Seeding a real
		// category makes the preview query behave like an actual archive.
		if ( ! $post_type || 'post' === $post_type ) {
			$terms = get_terms(
				[
					'taxonomy'   => 'category',
					'number'     => 1,
					'orderby'    => 'count',
					'order'      => 'DESC',
					'hide_empty' => true,
					'fields'     => 'ids',
				]
			);

			if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
				$args['cat'] = (int) $terms[0];
			}
		}

		$special = get_post_meta( $template_id, 'wowdevs_theme_builder_display_special_pages', true );
		// No maybe_unserialize() here: get_post_meta() with a key and single=true
		// has already unserialized. Running it again would re-unserialize a value
		// that is still a serialized string, which is a needless object-injection
		// surface. (match_conditions() does need it — it fetches ALL meta at once,
		// which returns raw values.)
		$special = array_column( (array) $special, 'value' );

		/**
		 * Filters the query used to preview an archive template in the editor.
		 *
		 * @param array $args        WP_Query args.
		 * @param int   $template_id Template post ID.
		 * @param array $special     Special-page conditions on the template.
		 */
		return apply_filters( 'sky_addons/theme-builder/preview_query_args', $args, $template_id, $special );
	}
}

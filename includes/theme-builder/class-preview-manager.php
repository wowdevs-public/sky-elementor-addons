<?php

namespace Sky_Addons\ThemeBuilder;

use Elementor\Controls_Manager;
use Sky_Addons\Includes\Controls\SelectInput\Dynamic_Select;

defined( 'ABSPATH' ) || exit;

/**
 * Stand-in render context for Theme Builder templates.
 *
 * A template document has no visitor context of its own: editing an `archive`
 * template there is no archive query, and editing a `single` template the only
 * post in scope is the template itself. Without this class every dynamic widget
 * renders empty in the editor and the feature reads as broken.
 *
 * Applies to both places a template is *previewed* rather than served — the
 * Elementor editor and the dashboard's live preview ({@see Live_Preview}). The
 * gate is Builder_Context::is_preview_context().
 *
 * Single templates  → a sample post (author-picked, else newest published) is
 *                     installed as the global post, so Sky's dynamic tags —
 *                     Post Title, Featured Image, Excerpt, Terms — preview the
 *                     sample instead of the template's own title. Those tags
 *                     resolve through get_the_ID(), and while editing, the
 *                     global post *is* the template.
 * Archive templates → a stand-in query derived from the template's own
 *                     display conditions, injected into "Current Query".
 *
 * A real visitor request is never touched: the frontend always uses the real
 * query. Elementor never swaps the global post while rendering builder content
 * (get_builder_content_for_display() leaves $GLOBALS['post'] alone; only
 * db.php's switch_to_post/switch_to_query touch it), so on the front end the
 * global post is already the visitor's post and the tags are correct as-is.
 *
 * @since 4.5.0
 */
class Preview_Manager {

	/**
	 * @var self|null
	 */
	private static $instance = null;

	/**
	 * Depth of switch_to_preview_query() calls. A counter rather than a flag so
	 * nested dynamic-tag renders cannot restore the query while an outer render
	 * is still using it.
	 *
	 * @var int
	 */
	private $switch_depth = 0;

	/**
	 * Set once the sample post has been installed, so the three hooks that call
	 * setup_sample_post() do the work only once per request.
	 *
	 * @var bool
	 */
	private $sample_post_ready = false;

	/**
	 * Post displaced by the tag-scoped swap, restored afterwards.
	 *
	 * @var \WP_Post|null
	 */
	private $tag_swap_original = null;

	/**
	 * @var bool
	 */
	private $tag_swap_active = false;

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
		// Sample post picker on single templates.
		add_action( 'elementor/documents/register_controls', [ $this, 'register_document_controls' ] );

		// "Current Query" preview for archive templates — Sky widgets and any
		// Elementor / third-party widget using the same convention.
		add_filter( 'sky_addons/query/get_query_args/current_query', [ $this, 'filter_current_query' ], 15 );
		add_filter( 'elementor/query/get_query_args/current_query', [ $this, 'filter_current_query' ], 15 );
		add_filter( 'elementor_pro/query_control/get_query_args/current_query', [ $this, 'filter_current_query' ], 15 );

		// Single templates: install the sample post as the global post.
		//
		// Two different lifetimes on purpose. In the editor and the preview
		// iframe the swap is meant to last the whole render — that request
		// exists only to draw the template. The tag-render AJAX request is
		// shared with other work, so there the swap is scoped and restored.
		add_action( 'elementor/preview/init', [ $this, 'setup_sample_post' ] );
		add_action( 'elementor/editor/init', [ $this, 'setup_sample_post' ] );
		add_action( 'elementor/dynamic_tags/before_render', [ $this, 'swap_sample_post_for_tags' ] );
		add_action( 'elementor/dynamic_tags/after_render', [ $this, 'restore_post_after_tags' ] );

		// Dynamic tags resolve against the previewed query too.
		//
		// Scope note: these two actions fire only in the editor's render-tags
		// AJAX request, once per batch, wrapping the whole tag loop — not in the
		// preview iframe and not on the frontend. Widget queries in the preview
		// are covered by the current_query filters above instead. Elementor Pro
		// hooks the same pair for the same reason.
		add_action( 'elementor/dynamic_tags/before_render', [ $this, 'switch_to_preview_query' ] );
		add_action( 'elementor/dynamic_tags/after_render', [ $this, 'restore_current_query' ] );
	}

	/**
	 * Add the sample-post picker to `single` template documents.
	 *
	 * @param \Elementor\Core\Base\Document $document
	 */
	public function register_document_controls( $document ) {
		if ( ! $document || ! method_exists( $document, 'get_main_id' ) ) {
			return;
		}

		$template_id = (int) $document->get_main_id();

		if ( Builder_Context::POST_TYPE !== get_post_type( $template_id ) ) {
			return;
		}

		if ( 'single' !== Builder_Context::instance()->get_template_type( $template_id ) ) {
			return;
		}

		$document->start_controls_section(
			'sky_tb_preview_section',
			[
				'label' => esc_html__( 'Sky Preview Settings', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_SETTINGS,
			]
		);

		$document->add_control(
			'sky_tb_preview_notice',
			[
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => esc_html__( 'Choose which post this template previews while you design it. Visitors always see their own post.', 'sky-elementor-addons' ),
				'content_classes' => 'elementor-descriptor',
			]
		);

		$document->add_control(
			Builder_Context::SAMPLE_POST_SETTING,
			[
				'label'       => esc_html__( 'Preview Post', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'        => class_exists( Dynamic_Select::class ) ? Dynamic_Select::TYPE : Controls_Manager::TEXT,
				'multiple'    => false,
				'label_block' => true,
				'query_args'  => [
					'query' => 'posts',
				],
				'description' => esc_html__( 'Leave empty to preview the most recent published post.', 'sky-elementor-addons' ),
			]
		);

		$document->end_controls_section();
	}

	// ── Single template preview post ──

	/**
	 * Install the sample post as the global post while a `single` template is
	 * being edited or previewed.
	 *
	 * Sky's dynamic tags read `get_the_ID()`, which inside the editor is the
	 * template document. Without this, every Post Title / Featured Image /
	 * Excerpt tag previews the template's own title and the design is
	 * impossible to lay out.
	 *
	 * Deliberately does NOT run on a real visitor request: there the global post
	 * is already the visitor's post. The live preview is the one front-end render
	 * that does call this — Live_Preview::render_template() invokes it directly,
	 * since neither `elementor/preview/init` nor `elementor/editor/init` fires
	 * there.
	 */
	public function setup_sample_post() {
		if ( $this->sample_post_ready ) {
			return;
		}

		$this->sample_post_ready = $this->swap_global_post_to_sample();
	}

	/**
	 * Scoped swap for the editor's tag-render AJAX request.
	 *
	 * Remembers the post that was in place so restore_post_after_tags() can put
	 * it back — that request also serves other code, and leaving the sample post
	 * installed would leak into it.
	 */
	public function swap_sample_post_for_tags() {
		if ( $this->sample_post_ready || $this->tag_swap_active ) {
			return;
		}

		$original = isset( $GLOBALS['post'] ) ? $GLOBALS['post'] : null;

		if ( ! $this->swap_global_post_to_sample() ) {
			return;
		}

		$this->tag_swap_original = $original;
		$this->tag_swap_active   = true;
	}

	/**
	 * Undo swap_sample_post_for_tags().
	 */
	public function restore_post_after_tags() {
		if ( ! $this->tag_swap_active ) {
			return;
		}

		if ( $this->tag_swap_original ) {
			// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- restoring what we replaced.
			$GLOBALS['post'] = $this->tag_swap_original;
			setup_postdata( $this->tag_swap_original );
		} else {
			unset( $GLOBALS['post'] );
			wp_reset_postdata();
		}

		$this->tag_swap_original = null;
		$this->tag_swap_active   = false;
	}

	/**
	 * Install the template's sample post as the global post.
	 *
	 * @return bool True when the swap happened.
	 */
	private function swap_global_post_to_sample() {
		$context = Builder_Context::instance();

		if ( ! $context->is_preview_context() ) {
			return false;
		}

		$template_id = $context->get_editing_template_id();

		if ( ! $template_id || 'single' !== $context->get_template_type( $template_id ) ) {
			return false;
		}

		$sample_id = $context->get_sample_post_id( $template_id );
		$sample    = $sample_id ? get_post( $sample_id ) : null;

		if ( ! $sample ) {
			return false;
		}

		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- editor preview only.
		$GLOBALS['post'] = $sample;
		setup_postdata( $sample );

		return true;
	}

	// ── Archive preview query ──

	/**
	 * Swap "Current Query" for the preview query while editing an archive
	 * template. Returns $query_vars untouched everywhere else, so the frontend
	 * is never affected.
	 *
	 * @param array<string,mixed> $query_vars
	 * @return array<string,mixed>
	 */
	public function filter_current_query( $query_vars ) {
		$template_id = $this->get_previewable_template_id();

		if ( ! $template_id ) {
			return $query_vars;
		}

		$preview_args = Builder_Context::instance()->get_preview_query_args( $template_id );

		if ( empty( $preview_args ) ) {
			return $query_vars;
		}

		// The preview only decides *what* is listed. Paging and per-page stay
		// with the widget, so a preview arg can never silently override them.
		unset( $preview_args['paged'], $preview_args['posts_per_page'], $preview_args['offset'] );

		$query_vars = (array) $query_vars;

		// In the editor the main query is the template's OWN singular query, so
		// $query_vars carries `name`/`p` pointing at the wowdevs-hooks post.
		// Merging the preview args over the top would leave those in place and
		// produce `WHERE post_name = '<template-slug>' AND post_type = 'post'` —
		// zero rows, and the archive loop renders blank in the editor. Drop every
		// singular identifier before merging.
		foreach ( [ 'p', 'name', 'page_id', 'pagename', 'attachment', 'attachment_id', 'subpost', 'subpost_id', 'static', 'preview' ] as $singular_var ) {
			unset( $query_vars[ $singular_var ] );
		}

		return array_merge( $query_vars, $preview_args );
	}

	/**
	 * Point Elementor's query stack at the preview query before dynamic tags
	 * render.
	 */
	public function switch_to_preview_query() {
		if ( ! class_exists( '\Elementor\Plugin' ) ) {
			return;
		}

		$template_id = $this->get_previewable_template_id();

		if ( ! $template_id ) {
			return;
		}

		$preview_args = Builder_Context::instance()->get_preview_query_args( $template_id );

		if ( empty( $preview_args ) ) {
			return;
		}

		\Elementor\Plugin::instance()->db->switch_to_query( $preview_args, true );
		++$this->switch_depth;
	}

	/**
	 * Restore the query switched by switch_to_preview_query().
	 *
	 * Pairs one-for-one with the switch, so an unmatched after_render (a tag
	 * that rendered before this class was active) cannot pop a query it never
	 * pushed.
	 */
	public function restore_current_query() {
		if ( $this->switch_depth < 1 || ! class_exists( '\Elementor\Plugin' ) ) {
			return;
		}

		\Elementor\Plugin::instance()->db->restore_current_query();
		--$this->switch_depth;
	}

	/**
	 * Template ID when — and only when — an archive-style template is being
	 * previewed in the editor.
	 *
	 * @return int 0 on the frontend or for non-archive templates.
	 */
	private function get_previewable_template_id() {
		$context = Builder_Context::instance();

		if ( ! $context->is_preview_context() ) {
			return 0;
		}

		$template_id = $context->get_editing_template_id();

		if ( ! $template_id || ! $context->is_archive_template( $template_id ) ) {
			return 0;
		}

		return $template_id;
	}
}

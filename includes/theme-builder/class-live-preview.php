<?php

namespace Sky_Addons\ThemeBuilder;

defined( 'ABSPATH' ) || exit;

/**
 * Live preview of a Theme Builder template, rendered in an iframe inside the
 * dashboard.
 *
 * The `wowdevs-hooks` CPT is already publicly queryable, so every template has
 * a working frontend URL. Loading it raw is useless as a preview though: the
 * request is a normal singular view, so the active theme wraps it in its own
 * header and footer *and* Theme_Builder injects the site's assigned header,
 * footer and Custom Hooks around it. The template being previewed ends up
 * buried inside a full page of unrelated chrome, sometimes twice.
 *
 * Adding `?sky_tb_preview=1` to that URL does four things:
 *
 *   1. swaps in `templates/preview.php`, a bare shell that prints the template's
 *      own markup and nothing else;
 *   2. tells Theme_Builder to stand down for the request (both `apply_conditions`
 *      and `hooks` bail), so no assigned header/footer/Custom Hook is injected
 *      and the template-matching queries are skipped entirely;
 *   3. installs the same stand-in context the Elementor editor gets — a sample
 *      post for `single`, the condition-derived query for `archive` — via
 *      Builder_Context::is_preview_context(). Without it those two types
 *      preview as empty skeletons: their dynamic widgets have no post and no
 *      query to read;
 *   4. hides the admin bar, which would otherwise offset the design by 32px.
 *
 * Nothing is cached or generated. The iframe shows the template as it is right
 * now, so a preview can never go stale the way a stored screenshot does.
 *
 * Access: the flag only swaps the *template* — it grants no visibility a
 * visitor did not already have, since WordPress still runs its own status and
 * capability checks on the query. `sky_preview_url` is nevertheless returned
 * empty to anyone without `edit_post` on the template, so the dashboard never
 * offers a preview the user has no business opening.
 *
 * @since 4.0.0
 */
class Live_Preview {

	/**
	 * Query flag that turns a normal template view into a preview render.
	 */
	const QUERY_VAR = 'sky_tb_preview';

	/**
	 * @var self|null
	 */
	private static $instance = null;

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
		add_action( 'rest_api_init', [ $this, 'register_rest_fields' ] );

		if ( ! $this->has_preview_flag() ) {
			return;
		}

		// Later than Theme_Builder's own 9999 and than Elementor's page-template
		// filter, so the shell is the last word on this request.
		add_filter( 'template_include', [ $this, 'force_preview_template' ], 99999 );

		// The bar renders 32px of chrome above the design and shifts every
		// sticky/absolute offset in the preview. Keyed off the raw flag rather
		// than is_preview_request(), because `show_admin_bar` is applied on
		// `init` — before the main query exists, so there is no queried object
		// to check yet.
		add_filter( 'show_admin_bar', '__return_false', 99999 );

		add_action( 'send_headers', [ $this, 'send_frame_header' ] );
	}

	/**
	 * Is the preview flag present on this request?
	 *
	 * Says nothing about *what* is being requested — see is_preview_request().
	 *
	 * @return bool
	 */
	private function has_preview_flag() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only render flag; it changes no state and exposes nothing the query would not already serve.
		return isset( $_GET[ self::QUERY_VAR ] );
	}

	/**
	 * Is this request a live preview of a Theme Builder template?
	 *
	 * Deliberately not memoized: the answer depends on the main query, and a
	 * cached "false" from an early caller would silently disable the preview
	 * for every later one.
	 *
	 * @return bool
	 */
	public function is_preview_request() {
		if ( ! $this->has_preview_flag() ) {
			return false;
		}

		$post_id = get_queried_object_id();

		return $post_id && Builder_Context::POST_TYPE === get_post_type( $post_id );
	}

	/**
	 * Render the template through the preview shell — no theme header, no theme
	 * footer, no sidebar.
	 *
	 * @param string $template
	 * @return string
	 */
	public function force_preview_template( $template ) {
		if ( ! $this->is_preview_request() ) {
			return $template;
		}

		$shell = SKY_ADDONS_INC_PATH . 'theme-builder/templates/preview.php';

		return file_exists( $shell ) ? $shell : $template;
	}

	/**
	 * Print one template's Elementor content, with the stand-in context a
	 * preview needs installed around it.
	 *
	 * `single` templates render against a sample post — without it every Post
	 * Title, Featured Image and Excerpt reads the *template's* own post and the
	 * preview shows an empty skeleton. Archive templates need nothing here:
	 * Preview_Manager's `current_query` filters already hand widgets the
	 * condition-derived query, and they are live for this request because
	 * Builder_Context::is_preview_context() covers it.
	 *
	 * The swap is not restored — this request exists only to draw the template,
	 * and nothing runs after the shell.
	 *
	 * @param int $template_id
	 */
	public function render_template( $template_id ) {
		$template_id = absint( $template_id );

		if ( ! $template_id || Builder_Context::POST_TYPE !== get_post_type( $template_id ) ) {
			return;
		}

		Preview_Manager::instance()->setup_sample_post();

		if ( ! class_exists( '\\Elementor\\Plugin' ) ) {
			return;
		}

		// Mirrors wowdevs_render_elementor_content(): print the document's CSS
		// inline when the site is set to internal CSS, otherwise rely on its
		// external file.
		$with_css = 'internal' === get_option( 'elementor_css_print_method' );

		// Deliberately NOT wowdevs_render_elementor_content(). That wraps
		// Frontend::get_builder_content_for_display(), which refuses to render a
		// document into itself — `get_the_ID() === $post_id` returns an empty
		// string (elementor/includes/frontend.php, "Avoid recursion").
		//
		// Everywhere else in the plugin the global post is the visitor's page, so
		// that guard never fires. Here the queried post *is* the template, so it
		// fired on every type without a sample-post swap — header, footer, 404,
		// custom hooks — and blanked the whole preview.
		//
		// get_builder_content() is the same render one level down, minus the
		// guard. The only thing the wrapper adds is an edit-mode toggle, and edit
		// mode is already false on a front-end request.
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Elementor builder output, escaped by the widgets that produced it.
		echo \Elementor\Plugin::instance()->frontend->get_builder_content( $template_id, $with_css );
	}

	/**
	 * Only our own dashboard may frame the preview.
	 *
	 * Sent unconditionally on flagged requests: `send_headers` fires before the
	 * posts query, so there is nothing to inspect yet, and a SAMEORIGIN header
	 * on a page nobody else needs to embed costs nothing.
	 */
	public function send_frame_header() {
		if ( headers_sent() ) {
			return;
		}

		header( 'X-Frame-Options: SAMEORIGIN' );
	}

	/**
	 * Expose the preview URL on the REST item, so the dashboard never has to
	 * rebuild permalink structure in JavaScript.
	 */
	public function register_rest_fields() {
		register_rest_field(
			Builder_Context::POST_TYPE,
			'sky_preview_url',
			[
				'get_callback' => [ $this, 'get_preview_url' ],
				'schema'       => [
					'description' => __( 'Front-end URL that renders this template on its own for preview.', 'sky-elementor-addons' ),
					'type'        => 'string',
					'format'      => 'uri',
					'context'     => [ 'view', 'edit' ],
					'readonly'    => true,
				],
			]
		);
	}

	/**
	 * @param array<string,mixed>|\WP_Post $post
	 * @return string Empty when the current user may not edit the template.
	 */
	public function get_preview_url( $post ) {
		$post_id = is_array( $post ) ? (int) $post['id'] : (int) $post->ID;

		if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) ) {
			return '';
		}

		return add_query_arg( self::QUERY_VAR, '1', get_permalink( $post_id ) );
	}
}

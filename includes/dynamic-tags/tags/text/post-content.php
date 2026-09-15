<?php

namespace Sky_Addons\Includes\DynamicTags\Tags\Text;

use Sky_Addons\Includes\Traits\UtilsTrait;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Sky Addons Dynamic Tag - Post Content
 *
 * Outputs the post body, filtered through `the_content` so blocks, shortcodes,
 * embeds and Elementor-built posts all render exactly as they do on a normal
 * single template.
 *
 * Placement: any control that accepts a TEXT-category dynamic tag. For the full
 * article use Elementor's HTML widget — its CODE control declares
 * `dynamic => categories => [ TEXT_CATEGORY ]` and prints unescaped, so the
 * markup survives. A Heading or Icon Box description will also accept the tag,
 * but wraps the output in its own element.
 */
class Dynamic_Tag_Post_Content extends \Elementor\Core\DynamicTags\Tag {
	use UtilsTrait;

	/**
	 * Post IDs currently mid-render, keyed by ID.
	 *
	 * Guards the one way this tag can hang a request: a post whose content
	 * renders a template that contains this tag again. Static, so it holds
	 * across every instance of the tag on the page.
	 *
	 * @var array<int,bool>
	 */
	private static $rendering = [];

	public function get_name(): string {
		return 'sky-addons-post-content';
	}

	public function get_title(): string {
		return esc_html__( 'Post Content', 'sky-elementor-addons' );
	}

	public function get_group(): array {
		return [ 'sky-addons-post' ];
	}

	/**
	 * Atomic editor expects a single group key string.
	 */
	public function get_atomic_group(): string {
		return 'sky-addons-post';
	}

	public function get_categories(): array {
		return [
			\Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
		];
	}

	public function is_settings_required() {
		return true;
	}

	protected function register_controls(): void {
		$this->common_post_controls();

		$this->add_control(
			'sky_apply_content_filters',
			[
				'label'       => esc_html__( 'Apply Content Filters', 'sky-elementor-addons' ),
				'type'        => \Elementor\Controls_Manager::SWITCHER,
				'default'     => 'yes',
				'label_on'    => esc_html__( 'Yes', 'sky-elementor-addons' ),
				'label_off'   => esc_html__( 'No', 'sky-elementor-addons' ),
				'description' => esc_html__( 'Runs the content through the_content — required for blocks, shortcodes, embeds and Elementor-built posts. Turn off only to output the raw stored content.', 'sky-elementor-addons' ),
			]
		);
	}

	public function render(): void {
		$post_id = (int) $this->get_post_id();

		if ( ! $post_id || isset( self::$rendering[ $post_id ] ) ) {
			return;
		}

		$post = get_post( $post_id );

		if ( ! $post ) {
			return;
		}

		// Password-protected posts must show the form, never the body — this tag
		// prints outside the loop, so nothing else enforces it.
		if ( post_password_required( $post ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core-generated form markup.
			echo get_the_password_form( $post );

			return;
		}

		$settings      = $this->get_settings();
		$apply_filters = ! isset( $settings['sky_apply_content_filters'] ) || 'yes' === $settings['sky_apply_content_filters'];
		$content       = $post->post_content;

		self::$rendering[ $post_id ] = true;

		// the_content filters resolve against the global post — Elementor's own
		// builder rendering included — so point it at the target post while the
		// filters run, then put back exactly what was there.
		$original   = isset( $GLOBALS['post'] ) ? $GLOBALS['post'] : null;
		$needs_swap = ! $original || (int) $original->ID !== $post_id;

		if ( $needs_swap ) {
			// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- restored below.
			$GLOBALS['post'] = $post;
			setup_postdata( $post );
		}

		if ( $apply_filters ) {
			/** This filter is documented in wp-includes/post-template.php */
			$content = apply_filters( 'the_content', $content );
			$content = str_replace( ']]>', ']]&gt;', $content );
		}

		if ( $needs_swap ) {
			if ( $original ) {
				// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- restoring what we replaced.
				$GLOBALS['post'] = $original;
				setup_postdata( $original );
			} else {
				unset( $GLOBALS['post'] );
				wp_reset_postdata();
			}
		}

		unset( self::$rendering[ $post_id ] );

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- post content already run through the_content; escaping would destroy the markup.
		echo $apply_filters ? $content : wp_kses_post( $content );
	}
}

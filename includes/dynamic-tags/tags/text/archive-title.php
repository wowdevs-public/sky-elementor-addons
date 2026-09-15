<?php

namespace Sky_Addons\Includes\DynamicTags\Tags\Text;

use Sky_Addons\Includes\Traits\UtilsTrait;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Dynamic_Tag_Archive_Title extends \Elementor\Core\DynamicTags\Tag {

	use UtilsTrait;

	public function get_name(): string {
		return 'sky-addons-archive-title';
	}

	public function get_title(): string {
		return esc_html__( 'Archive Title', 'sky-elementor-addons' );
	}

	public function get_group(): array {
		return [ 'sky-addons-archive' ];
	}

	public function get_categories(): array {
		return [
			\Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
		];
	}

	protected function register_controls(): void {
		$this->add_control(
			'sky_include_context',
			[
				'label'       => esc_html__( 'Include Context', 'sky-elementor-addons' ),
				'type'        => \Elementor\Controls_Manager::SWITCHER,
				'default'     => 'yes',
				'description' => esc_html__( 'Include archive context (Category:, Tag:, Author:, etc.)', 'sky-elementor-addons' ),
			]
		);
	}

	protected function register_advanced_section() {
		$this->advanced_controls();
	}

	public function render(): void {
		$include_context = 'yes' === $this->get_settings( 'sky_include_context' );

		// Search and the blog index are handled before core, because
		// get_the_archive_title() has no branch for either and falls through to
		// the literal string "Archives" — which then reads as the H1 on both.
		if ( is_search() ) {
			$title = $include_context
				/* translators: %s: search query. */
				? sprintf( esc_html__( 'Search Results for: %s', 'sky-elementor-addons' ), get_search_query() )
				: get_search_query();

			echo wp_kses_post( $this->apply_word_limit( $title ) );

			return;
		}

		if ( is_home() && ! is_front_page() ) {
			$blog_page_id = (int) get_option( 'page_for_posts' );
			$title        = $blog_page_id ? get_the_title( $blog_page_id ) : esc_html__( 'Blog', 'sky-elementor-addons' );

			echo wp_kses_post( $this->apply_word_limit( $title ) );

			return;
		}

		if ( $include_context ) {
			$title = get_the_archive_title();
		} else {
			$title = post_type_archive_title( '', false );

			if ( empty( $title ) ) {
				if ( is_category() || is_tag() || is_tax() ) {
					$title = single_term_title( '', false );
				} elseif ( is_author() ) {
					$title = get_the_author();
				} elseif ( is_date() ) {
					if ( is_year() ) {
						$title = get_the_date( 'Y' );
					} elseif ( is_month() ) {
						$title = get_the_date( 'F Y' );
					} else {
						$title = get_the_date();
					}
				} elseif ( is_post_type_archive() ) {
					$title = post_type_archive_title( '', false );
				}
			}
		}

		echo wp_kses_post( $this->apply_word_limit( $title ) );
	}
}

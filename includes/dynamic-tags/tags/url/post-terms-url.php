<?php

namespace Sky_Addons\Includes\DynamicTags\Tags\Url;

use Sky_Addons\Includes\Traits\UtilsTrait;
use Elementor\Core\DynamicTags\Data_Tag;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Dynamic_Tag_Post_Terms_URL extends Data_Tag {
	use UtilsTrait;

	public function get_name(): string {
		return 'sky-addons-post-terms-url';
	}

	public function get_title(): string {
		return esc_html__( 'Post Terms URL', 'sky-elementor-addons' );
	}

	public function get_group(): array {
		return [ 'sky-addons-post' ];
	}

	public function get_categories(): array {
		return [
			\Elementor\Modules\DynamicTags\Module::URL_CATEGORY,
		];
	}

	public function is_settings_required() {
		return true;
	}

	protected function register_controls(): void {
		$this->common_post_controls();

		$this->add_control(
			'sky_taxonomy',
			[
				'label'   => esc_html__( 'Taxonomy', 'sky-elementor-addons' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => $this->get_taxonomies_options(),
				'default' => 'category',
			]
		);

		$this->add_control(
			'sky_term_index',
			[
				'label'       => esc_html__( 'Term Index', 'sky-elementor-addons' ),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'default'     => 0,
				'min'         => 0,
				'description' => esc_html__( '0 for first term, 1 for second term, etc.', 'sky-elementor-addons' ),
			]
		);

		$this->fallback_control();
	}

	protected function register_advanced_section(): void {}

	private function get_taxonomies_options(): array {
		$taxonomies = get_taxonomies( [ 'public' => true ], 'objects' );
		$options    = [];

		foreach ( $taxonomies as $taxonomy ) {
			$options[ $taxonomy->name ] = $taxonomy->label;
		}

		return $options;
	}

	public function get_value( array $options = [] ) {
		$settings = $this->get_settings();
		$post_id  = $this->get_post_id();

		if ( ! $post_id ) {
			return '';
		}

		$taxonomy   = $settings['sky_taxonomy'] ?? 'category';
		$term_index = (int) ( $settings['sky_term_index'] ?? 0 );

		$terms = get_the_terms( $post_id, $taxonomy );

		if ( ! $terms || is_wp_error( $terms ) ) {
			return '';
		}

		// Get the term at the specified index
		$term = isset( $terms[ $term_index ] ) ? $terms[ $term_index ] : $terms[0];

		if ( ! $term ) {
			return '';
		}

		$url = get_term_link( $term );

		if ( is_wp_error( $url ) ) {
			return '';
		}

		return $url;
	}
}

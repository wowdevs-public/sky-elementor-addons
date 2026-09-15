<?php

namespace Sky_Addons\Includes\DynamicTags\Tags\Url;

use Sky_Addons\Includes\Traits\UtilsTrait;
use Elementor\Core\DynamicTags\Data_Tag;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Dynamic_Tag_Post_Navigation_URL extends Data_Tag {
	use UtilsTrait;

	public function get_name(): string {
		return 'sky-addons-post-navigation-url';
	}

	public function get_title(): string {
		return esc_html__( 'Post Navigation URL', 'sky-elementor-addons' );
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
			'sky_navigation_type',
			[
				'label'   => esc_html__( 'Navigation Type', 'sky-elementor-addons' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'previous' => esc_html__( 'Previous Post', 'sky-elementor-addons' ),
					'next'     => esc_html__( 'Next Post', 'sky-elementor-addons' ),
					'parent'   => esc_html__( 'Parent Post', 'sky-elementor-addons' ),
					'child'    => esc_html__( 'Child Post', 'sky-elementor-addons' ),
				],
				'default' => 'next',
			]
		);

		$this->add_control(
			'sky_same_term',
			[
				'label'       => esc_html__( 'Same Term', 'sky-elementor-addons' ),
				'type'        => \Elementor\Controls_Manager::SWITCHER,
				'label_on'    => esc_html__( 'Yes', 'sky-elementor-addons' ),
				'label_off'   => esc_html__( 'No', 'sky-elementor-addons' ),
				'default'     => 'no',
				'description' => esc_html__( 'Whether to navigate within the same term.', 'sky-elementor-addons' ),
				'condition' => [
					'sky_navigation_type' => [ 'previous', 'next' ],
				],
			]
		);

		$this->add_control(
			'sky_taxonomy',
			[
				'label'   => esc_html__( 'Taxonomy', 'sky-elementor-addons' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => $this->get_taxonomies_options(),
				'default' => 'category',
				'condition' => [
					'sky_same_term'       => 'yes',
					'sky_navigation_type' => [ 'previous', 'next' ],
				],
			]
		);

		$this->add_control(
			'sky_child_order',
			[
				'label'   => esc_html__( 'Child Order', 'sky-elementor-addons' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'menu_order' => esc_html__( 'Menu Order', 'sky-elementor-addons' ),
					'date'       => esc_html__( 'Date', 'sky-elementor-addons' ),
					'title'      => esc_html__( 'Title', 'sky-elementor-addons' ),
				],
				'default' => 'menu_order',
				'condition' => [
					'sky_navigation_type' => 'child',
				],
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

		$navigation_type = $settings['sky_navigation_type'] ?? 'next';
		$post            = get_post( $post_id );

		if ( ! $post ) {
			return '';
		}

		// Handle parent/child navigation
		if ( 'parent' === $navigation_type ) {
			if ( $post->post_parent ) {
				return get_permalink( $post->post_parent );
			}
			return '';
		}

		if ( 'child' === $navigation_type ) {
			$child_order = $settings['sky_child_order'] ?? 'menu_order';
			$args        = [
				'post_type'      => $post->post_type,
				'post_parent'    => $post_id,
				'posts_per_page' => 1,
				'orderby'        => $child_order,
				'order'          => 'ASC',
				'post_status'    => 'publish',
			];

			$query = new \WP_Query( $args );
			if ( ! $query->have_posts() ) {
				return '';
			}

			return get_permalink( $query->posts[0]->ID );
		}

		// Handle previous/next navigation
		$same_term = 'yes' === $settings['sky_same_term'];
		$taxonomy  = $settings['sky_taxonomy'] ?? 'category';

		$args = [
			'post_type'      => $post->post_type,
			'posts_per_page' => 1,
			'order'          => 'next' === $navigation_type ? 'ASC' : 'DESC',
			'orderby'        => 'date',
			'post_status'    => 'publish',
			'post__not_in'   => [ $post_id ],
		];

		if ( $same_term ) {
			$terms = get_the_terms( $post_id, $taxonomy );
			if ( $terms && ! is_wp_error( $terms ) ) {
				$term_ids          = wp_list_pluck( $terms, 'term_id' );
				$args['tax_query'] = [
					[
						'taxonomy' => $taxonomy,
						'field'    => 'term_id',
						'terms'    => $term_ids,
					],
				];
			}
		}

		// Get the post date for comparison
		$post_date = get_post_field( 'post_date', $post_id );
		if ( 'next' === $navigation_type ) {
			$args['date_query'] = [
				[
					'after' => $post_date,
				],
			];
		} else {
			$args['date_query'] = [
				[
					'before' => $post_date,
				],
			];
		}

		$query = new \WP_Query( $args );

		if ( ! $query->have_posts() ) {
			return '';
		}

		return get_permalink( $query->posts[0]->ID );
	}
}

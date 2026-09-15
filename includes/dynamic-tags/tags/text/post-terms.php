<?php

namespace Sky_Addons\Includes\DynamicTags\Tags\Text;

use Sky_Addons\Includes\Controls\SelectInput\Dynamic_Select;
use Sky_Addons\Includes\Traits\UtilsTrait;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Dynamic_Tag_Post_Terms extends \Elementor\Core\DynamicTags\Tag {

	use UtilsTrait;

	public function get_name(): string {
		return 'sky-addons-post-terms';
	}

	public function get_title(): string {
		return esc_html__( 'Post Terms', 'sky-elementor-addons' );
	}

	public function get_group(): array {
		return [ 'sky-addons-post' ];
	}

	public function get_categories(): array {
		return [
			\Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
		];
	}

	public function is_settings_required() {
		return true;
	}

	public function get_all_taxonomies_array( $output = 'names', $operator = 'and' ) {
		$args             = [
			'public'  => true,
			'show_ui' => true,
		];
		$taxonomies       = get_taxonomies( $args, 'objects', $operator );
		$taxonomies_array = [];
		foreach ( $taxonomies as $taxonomy ) {
			$taxonomies_array[ $taxonomy->name ] = $taxonomy->label;
		}
		return $taxonomies_array;
	}

	protected function register_controls(): void {
		$this->common_post_controls();

		$this->add_control(
			'sky_taxonomy',
			[
				'label'       => esc_html__( 'Taxonomy', 'sky-elementor-addons' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'options'     => $this->get_all_taxonomies_array(),
				'default'     => 'category',
				'label_block' => true,
			]
		);

		$this->add_control(
			'sky_separator',
			[
				'label'   => esc_html__( 'Separator', 'sky-elementor-addons' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => ', ',
				'ai' => [
					'active' => false,
				],
			]
		);

		$this->add_control(
			'sky_link',
			[
				'label'   => esc_html__( 'Link', 'sky-elementor-addons' ),
				'type'    => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		// Add limit control
		$this->add_control(
			'sky_terms_limit',
			[
				'label'       => esc_html__( 'Limit', 'sky-elementor-addons' ),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'default'     => 0,
				'min'         => 0,
				'description' => esc_html__( '0 means no limit', 'sky-elementor-addons' ),
			]
		);

		// Add offset control
		$this->add_control(
			'sky_terms_offset',
			[
				'label'   => esc_html__( 'Offset', 'sky-elementor-addons' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'default' => 0,
				'min'     => 0,
			]
		);
	}

	public function render(): void {
		$settings = $this->get_settings();

		$post_id = $this->get_post_id();

		if ( ! $post_id ) {
			return;
		}

		$taxonomy    = $settings['sky_taxonomy'];
		$separator   = $settings['sky_separator'];
		$should_link = 'yes' === $settings['sky_link'];

		$terms = wp_get_post_terms( $post_id, $taxonomy );

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return;
		}

		// Apply offset and limit
		$limit  = isset( $settings['sky_terms_limit'] ) ? intval( $settings['sky_terms_limit'] ) : 0;
		$offset = isset( $settings['sky_terms_offset'] ) ? intval( $settings['sky_terms_offset'] ) : 0;
		if ( $limit > 0 ) {
			$terms = array_slice( $terms, $offset, $limit );
		} else {
			$terms = array_slice( $terms, $offset );
		}

		$terms_list = [];

		foreach ( $terms as $term ) {
			if ( $should_link ) {
				$terms_list[] = '<a href="' . esc_url( get_term_link( $term ) ) . '">' . esc_html( $term->name ) . '</a>';
			} else {
				$terms_list[] = esc_html( $term->name );
			}
		}

		echo wp_kses_post( implode( $separator, $terms_list ) );
	}
}

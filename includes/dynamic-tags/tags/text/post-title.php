<?php

namespace Sky_Addons\Includes\DynamicTags\Tags\Text;

use Sky_Addons\Includes\Controls\SelectInput\Dynamic_Select;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Sky Addons Dynamic Tag - Post Title
 *
 * Elementor dynamic tag that returns post title with advanced options
 */
class Dynamic_Tag_Post_Title extends \Elementor\Core\DynamicTags\Tag {

	use \Sky_Addons\Includes\Traits\UtilsTrait;

	/**
	 * Get dynamic tag name.
	 *
	 * Retrieve the name of the post title tag.
	 *
	 * @access public
	 * @return string Dynamic tag name.
	 */
	public function get_name(): string {
		return 'sky-addons-post-title';
	}

	/**
	 * Get dynamic tag title.
	 *
	 * Returns the title of the post title tag.
	 *
	 * @access public
	 * @return string Dynamic tag title.
	 */
	public function get_title(): string {
		return esc_html__( 'Post Title', 'sky-elementor-addons' );
	}

	/**
	 * Get dynamic tag groups.
	 *
	 * Retrieve the list of groups the post title tag belongs to.
	 *
	 * @access public
	 * @return array Dynamic tag groups.
	 */
	public function get_group(): array {
		return [ 'sky-addons-post' ];
	}

	/**
	 * Atomic editor expects a single group key string.
	 */
	public function get_atomic_group(): string {
		return 'sky-addons-post';
	}

	/**
	 * Get dynamic tag categories.
	 *
	 * Retrieve the list of categories the post title tag belongs to.
	 *
	 * @access public
	 * @return array Dynamic tag categories.
	 */
	public function get_categories(): array {
		return [
			\Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
		];
	}

	public function is_settings_required() {
		return true;
	}

	protected function register_controls(): void {
		$this->add_control(
			'sky_post_type',
			[
				'label'   => esc_html__( 'Post Type', 'sky-elementor-addons' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'current'  => esc_html__( 'Current Post', 'sky-elementor-addons' ),
					'selected' => esc_html__( 'Selected Post', 'sky-elementor-addons' ),
				],
				'default' => 'current',
			]
		);

		$this->add_control(
			'sky_posts_selected_id_legacy',
			[
				'label'       => esc_html__( 'Search & Select Post', 'sky-elementor-addons' ),
				'type'        => Dynamic_Select::TYPE,
				'multiple'    => false,
				'label_block' => true,
				'query_args' => [
					'query' => 'posts',
				],
				'condition' => [
					'sky_post_type' => 'selected',
				],
			]
		);
	}

	protected function register_advanced_section() {
		$this->start_controls_section(
			'advanced',
			[
				'label' => esc_html__( 'Advanced', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'sky_word_limit',
			[
				'label'       => esc_html__( 'Word Limit', 'sky-elementor-addons' ),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'default'     => 0,
				'min'         => 0,
				'description' => esc_html__( '0 means no limit', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'before',
			[
				'label'   => esc_html__( 'Before', 'sky-elementor-addons' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'ai' => [
					'active' => false,
				],
			]
		);

		$this->add_control(
			'after',
			[
				'label'   => esc_html__( 'After', 'sky-elementor-addons' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'ai' => [
					'active' => false,
				],
			]
		);

		$this->add_control(
			'fallback',
			[
				'label'   => esc_html__( 'Fallback', 'sky-elementor-addons' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'ai' => [
					'active' => false,
				],
			]
		);

		$this->end_controls_section();
	}
	/**
	 * Render tag output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @access public
	 * @return void
	 */
	public function render(): void {
		$value    = '';
		$settings = $this->get_settings();
		$post_id  = 0;

		if (
			! empty( $settings['sky_post_type'] ) &&
			'selected' === $settings['sky_post_type']
		) {
			$legacy_id = ! empty( $settings['sky_posts_selected_id_legacy'] ) ? (int) $settings['sky_posts_selected_id_legacy'] : 0;
			$post_id   = $legacy_id;
		} else {
			$post_id = get_the_ID();
		}

		// If we have a valid post ID, get the title; otherwise, fallback to an empty value
		if ( $post_id ) {
			$value = get_the_title( $post_id );
		}

		// Output the sanitized value
		echo wp_kses_post( $this->apply_word_limit( $value ) );
	}
}

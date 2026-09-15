<?php

namespace Sky_Addons\Includes\DynamicTags\Tags\Text;

use Sky_Addons\Includes\Controls\SelectInput\Dynamic_Select;
use Sky_Addons\Includes\Traits\UtilsTrait;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Dynamic_Tag_Post_Comments extends \Elementor\Core\DynamicTags\Tag {

	use UtilsTrait;

	public function get_name(): string {
		return 'sky-addons-post-comments';
	}

	public function get_title(): string {
		return esc_html__( 'Post Comments', 'sky-elementor-addons' );
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

	protected function register_controls(): void {
		$this->common_post_controls();

		$this->add_control(
			'sky_comments_data_type',
			[
				'label'   => esc_html__( 'Data Type', 'sky-elementor-addons' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'count'  => esc_html__( 'Comments Count', 'sky-elementor-addons' ),
					'number' => esc_html__( 'Comments Number', 'sky-elementor-addons' ),
				],
				'default' => 'count',
			]
		);

		$this->add_control(
			'sky_comments_no_text',
			[
				'label'       => esc_html__( 'No Comments Text', 'sky-elementor-addons' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => esc_html__( 'No Comments', 'sky-elementor-addons' ),
				'condition' => [
					'sky_comments_data_type' => 'number',
				],
				'label_block' => true,
				'ai' => [
					'active' => false,
				],
			]
		);

		$this->add_control(
			'sky_comments_single_text',
			[
				'label'       => esc_html__( 'Single Comment Text', 'sky-elementor-addons' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => esc_html__( '1 Comment', 'sky-elementor-addons' ),
				'condition' => [
					'sky_comments_data_type' => 'number',
				],
				'label_block' => true,
				'ai' => [
					'active' => false,
				],
			]
		);

		$this->add_control(
			'sky_comments_multi_text',
			[
				'label'       => esc_html__( 'Multiple Comments Text', 'sky-elementor-addons' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => esc_html__( '{{number}} Comments', 'sky-elementor-addons' ),
				'condition' => [
					'sky_comments_data_type' => 'number',
				],
				'description' => esc_html__( 'Use {{number}} as a placeholder for the comments count.', 'sky-elementor-addons' ),
				'label_block' => true,
				'ai' => [
					'active' => false,
				],
			]
		);
	}

	public function render(): void {
		$settings = $this->get_settings();

		$post_id = $this->get_post_id();

		if ( ! $post_id ) {
			return;
		}

		$comments_count = get_comments_number( $post_id );
		$output         = '';

		if ( 'count' === $settings['sky_comments_data_type'] ) {
			$output = $comments_count;
		} elseif ( 0 === $comments_count ) {
				$output = $settings['sky_comments_no_text'];
		} elseif ( 1 === $comments_count ) {
			$output = $settings['sky_comments_single_text'];
		} else {
			$output = str_replace( '{{number}}', $comments_count, $settings['sky_comments_multi_text'] );
		}

		echo wp_kses_post( $output );
	}
}

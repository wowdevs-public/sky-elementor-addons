<?php

namespace Sky_Addons\Includes\DynamicTags\Tags\Text;

use Sky_Addons\Includes\Traits\UtilsTrait;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Dynamic_Tag_Post_Time extends \Elementor\Core\DynamicTags\Tag {

	use UtilsTrait;

	public function get_name(): string {
		return 'sky-addons-post-time';
	}

	public function get_title(): string {
		return esc_html__( 'Post Time', 'sky-elementor-addons' );
	}

	public function get_group(): array {
		return [ 'sky-addons-post' ];
	}

	public function get_categories(): array {
		return [
			\Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
		];
	}

	protected function register_controls(): void {
		$this->common_post_controls();

		$this->add_control(
			'sky_time_type',
			[
				'label'   => esc_html__( 'Time Type', 'sky-elementor-addons' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'post_time'     => esc_html__( 'Post Time', 'sky-elementor-addons' ),
					'post_modified' => esc_html__( 'Post Modified Time', 'sky-elementor-addons' ),
				],
				'default' => 'post_time',
			]
		);

		$this->add_control(
			'sky_time_format',
			[
				'label'   => esc_html__( 'Time Format', 'sky-elementor-addons' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'default' => esc_html__( 'Default', 'sky-elementor-addons' ),
					'g:i a'   => date_i18n( 'g:i a' ),
					'g:i A'   => date_i18n( 'g:i A' ),
					'H:i'     => date_i18n( 'H:i' ),
					'custom'  => esc_html__( 'Custom', 'sky-elementor-addons' ),
				],
				'default' => 'default',
			]
		);

		$this->add_control(
			'sky_custom_time_format',
			[
				'label'   => esc_html__( 'Custom Format', 'sky-elementor-addons' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => 'g:i a',
				'condition' => [
					'sky_time_format' => 'custom',
				],
				'description' => sprintf(
					'<a href="https://wordpress.org/support/article/formatting-date-and-time/" target="_blank">%s</a>',
					esc_html__( 'Documentation on time formatting', 'sky-elementor-addons' )
				),
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

		$post = get_post( $post_id );

		if ( ! $post ) {
			return;
		}

		$time = 'post_modified' === $settings['sky_time_type'] ? $post->post_modified : $post->post_date;

		if ( 'default' === $settings['sky_time_format'] ) {
			$time_format = get_option( 'time_format' );
		} elseif ( 'custom' === $settings['sky_time_format'] ) {
			$time_format = $settings['sky_custom_time_format'];
		} else {
			$time_format = $settings['sky_time_format'];
		}

		echo wp_kses_post( date_i18n( $time_format, strtotime( $time ) ) );
	}
}

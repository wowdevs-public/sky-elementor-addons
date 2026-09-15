<?php

namespace Sky_Addons\Includes\DynamicTags\Tags\Text;

use Sky_Addons\Includes\Traits\UtilsTrait;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Dynamic_Tag_Post_Date extends \Elementor\Core\DynamicTags\Tag {

	use UtilsTrait;

	public function get_name(): string {
		return 'sky-addons-post-date';
	}

	public function get_title(): string {
		return esc_html__( 'Post Date', 'sky-elementor-addons' );
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
			'sky_date_type',
			[
				'label'   => esc_html__( 'Date Type', 'sky-elementor-addons' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'post_date'     => esc_html__( 'Post Date', 'sky-elementor-addons' ),
					'post_modified' => esc_html__( 'Post Modified Date', 'sky-elementor-addons' ),
				],
				'default' => 'post_date',
			]
		);

		$this->add_control(
			'sky_format_type',
			[
				'label'   => esc_html__( 'Format', 'sky-elementor-addons' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'default' => esc_html__( 'Default', 'sky-elementor-addons' ),
					'F j, Y'  => date_i18n( 'F j, Y' ),   // April 30, 2025
					'Y-m-d'   => date_i18n( 'Y-m-d' ),     // 2025-04-30
					'm/d/Y'   => date_i18n( 'm/d/Y' ),     // 04/30/2025
					'd/m/Y'   => date_i18n( 'd/m/Y' ),     // 30/04/2025
					'human'   => esc_html__( 'Human Readable', 'sky-elementor-addons' ),
					'custom'  => esc_html__( 'Custom', 'sky-elementor-addons' ),
				],
				'default' => 'default',
			]
		);

		$this->add_control(
			'sky_custom_format',
			[
				'label'   => esc_html__( 'Custom Format', 'sky-elementor-addons' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => 'F j, Y',
				'condition' => [
					'sky_format_type' => 'custom',
				],
				'description' => sprintf(
					'<a href="https://wordpress.org/support/article/formatting-date-and-time/" target="_blank">%s</a>',
					esc_html__( 'Documentation on date and time formatting', 'sky-elementor-addons' )
				),
				'ai' => [
					'active' => false,
				],
			]
		);
	}

	protected function register_advanced_section() {
		$this->advanced_controls();
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

		$date      = 'post_modified' === $settings['sky_date_type'] ? $post->post_modified : $post->post_date;
		$timestamp = strtotime( $date );

		if ( 'human' === $settings['sky_format_type'] ) {
			$value = human_time_diff( $timestamp, current_time( 'timestamp' ) );
		} elseif ( 'default' === $settings['sky_format_type'] ) {
			$value = date_i18n( get_option( 'date_format' ), $timestamp );
		} elseif ( 'custom' === $settings['sky_format_type'] ) {
			$format = $settings['sky_custom_format'];
			$value  = date_i18n( $format, $timestamp );
		} else {
			$value = date_i18n( $settings['sky_format_type'], $timestamp );
		}

		echo wp_kses_post( $this->apply_word_limit( $value ) );
	}
}

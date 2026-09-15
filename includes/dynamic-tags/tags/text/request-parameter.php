<?php

namespace Sky_Addons\Includes\DynamicTags\Tags\Text;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Dynamic_Tag_Request_Parameter extends \Elementor\Core\DynamicTags\Tag {

	public function get_name(): string {
		return 'sky-addons-request-parameter';
	}

	public function get_title(): string {
		return esc_html__( 'Request Parameter', 'sky-elementor-addons' );
	}

	public function get_group(): array {
		return [ 'sky-addons-site' ];
	}

	public function get_atomic_group(): string {
		return 'sky-addons-site';
	}

	public function get_categories(): array {
		return [
			\Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
		];
	}

	protected function register_controls(): void {
		$this->add_control(
			'sky_request_type',
			[
				'label'   => esc_html__( 'Request Type', 'sky-elementor-addons' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'get'       => esc_html__( 'GET', 'sky-elementor-addons' ),
					'post'      => esc_html__( 'POST', 'sky-elementor-addons' ),
					'query_var' => esc_html__( 'Query Var', 'sky-elementor-addons' ),
				],
				'default' => 'get',
			]
		);

		$this->add_control(
			'sky_param_name',
			[
				'label'       => esc_html__( 'Parameter Name', 'sky-elementor-addons' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'description' => esc_html__( 'Enter the parameter name to get its value', 'sky-elementor-addons' ),
				'label_block' => true,
				'condition' => [
					'sky_request_type!' => '',
				],
				'ai' => [
					'active' => false,
				],
			]
		);
	}

	public function render(): void {
		$settings   = $this->get_settings();
		$param_name = $settings['sky_param_name'];
		$type       = isset( $settings['sky_request_type'] ) ? $settings['sky_request_type'] : 'get';

		if ( empty( $param_name ) ) {
			return;
		}

		$value = '';
		// phpcs:disable WordPress.Security.NonceVerification -- Displays a public request parameter; nothing is saved or changed.
		if ( 'get' === $type ) {
			if ( isset( $_GET[ $param_name ] ) ) {
				$value = sanitize_text_field( wp_unslash( $_GET[ $param_name ] ) );
			}
		} elseif ( 'post' === $type ) {
			if ( isset( $_POST[ $param_name ] ) ) {
				$value = sanitize_text_field( wp_unslash( $_POST[ $param_name ] ) );
			}
		} elseif ( 'query_var' === $type ) {
			$query_var_value = get_query_var( $param_name, '' );

			if ( ! empty( $query_var_value ) ) {
				$value = sanitize_text_field( $query_var_value );
			}
		}
		// phpcs:enable WordPress.Security.NonceVerification
		echo wp_kses_post( $value );
	}
}

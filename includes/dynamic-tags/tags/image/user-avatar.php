<?php

namespace Sky_Addons\Includes\DynamicTags\Tags\Image;

use Sky_Addons\Includes\Traits\UtilsTrait;
use Elementor\Core\DynamicTags\Data_Tag;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Dynamic_Tag_User_Avatar extends Data_Tag {
	use UtilsTrait;

	public function get_name(): string {
		return 'sky-addons-user-avatar';
	}

	public function get_title(): string {
		return esc_html__( 'User Avatar', 'sky-elementor-addons' );
	}

	public function get_group(): array {
		return [ 'sky-addons-user' ];
	}

	public function get_categories(): array {
		return [
			\Elementor\Modules\DynamicTags\Module::IMAGE_CATEGORY,
			\Elementor\Modules\DynamicTags\Module::MEDIA_CATEGORY,
		];
	}

	public function is_settings_required() {
		return true;
	}

	protected function register_controls(): void {
		$this->add_control(
			'sky_avatar_source',
			[
				'label'   => esc_html__( 'Avatar Source', 'sky-elementor-addons' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'gravatar',
				'options' => [
					'gravatar' => esc_html__( 'Gravatar', 'sky-elementor-addons' ),
					'meta'     => esc_html__( 'User Meta', 'sky-elementor-addons' ),
				],
			]
		);

		$this->add_control(
			'sky_meta_key',
			[
				'label'       => esc_html__( 'Meta Key', 'sky-elementor-addons' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'Enter meta key', 'sky-elementor-addons' ),
				'condition' => [
					'sky_avatar_source' => 'meta',
				],
				'ai' => [
					'active' => false,
				],
			]
		);

		$this->add_control(
			'sky_user_id',
			[
				'label'       => esc_html__( 'User', 'sky-elementor-addons' ),
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'options'     => $this->get_users_list(),
				'description' => esc_html__( 'Leave empty to use current user', 'sky-elementor-addons' ),
			]
		);

		$this->add_control(
			'sky_gravatar_size',
			[
				'label'   => esc_html__( 'Gravatar Size', 'sky-elementor-addons' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'default' => 96,
				'min'     => 1,
				'max'     => 2048,
				'condition' => [
					'sky_avatar_source' => 'gravatar',
				],
			]
		);

		$this->add_control(
			'fallback',
			[
				'label'   => esc_html__( 'Fallback', 'sky-elementor-addons' ),
				'type'    => \Elementor\Controls_Manager::MEDIA,
				'dynamic' => [ 'active' => true ],
			]
		);
	}

	protected function register_advanced_section(): void {}

	private function get_users_list() {
		$users = get_users([
			'orderby' => 'display_name',
			'order'   => 'ASC',
		]);

		$options = [];
		foreach ( $users as $user ) {
			$options[ $user->ID ] = $user->display_name;
		}

		return $options;
	}

	public function get_value( array $options = [] ) {
		$user_id = $this->get_settings( 'sky_user_id' );
		$source  = $this->get_settings( 'sky_avatar_source' );

		// Get user ID
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		if ( ! $user_id ) {
			return $this->get_settings( 'fallback' );
		}

		// Get user email for Gravatar
		$user = get_userdata( $user_id );
		if ( ! $user ) {
			return $this->get_settings( 'fallback' );
		}

		switch ( $source ) {
			case 'gravatar':
				$size         = $this->get_settings( 'sky_gravatar_size' );
				$gravatar_url = get_avatar_url( $user->user_email, [ 'size' => $size ] );

				if ( $gravatar_url ) {
					return [
						'id'  => 0,
						'url' => $gravatar_url,
					];
				}
				break;

			case 'meta':
				$meta_key = $this->get_settings( 'sky_meta_key' );
				if ( ! $meta_key ) {
					return $this->get_settings( 'fallback' );
				}

				$value = get_user_meta( $user_id, $meta_key, true );

				if ( is_numeric( $value ) ) {
					// If value is an attachment ID
					$image_url = wp_get_attachment_image_url( $value, 'full' );
					if ( $image_url ) {
						return [
							'id'  => $value,
							'url' => $image_url,
						];
					}
				} elseif ( is_string( $value ) ) {
					// If value is a URL
					if ( filter_var( $value, FILTER_VALIDATE_URL ) ) {
						return [
							'id'  => 0,
							'url' => $value,
						];
					}
				}
				break;
		}

		return $this->get_settings( 'fallback' );
	}
}

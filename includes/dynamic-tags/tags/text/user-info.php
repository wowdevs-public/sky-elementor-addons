<?php

namespace Sky_Addons\Includes\DynamicTags\Tags\Text;

use Sky_Addons\Includes\Traits\UtilsTrait;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Dynamic_Tag_User_Info extends \Elementor\Core\DynamicTags\Tag {
	use UtilsTrait;

	public function get_name(): string {
		return 'sky-addons-user-info';
	}

	public function get_title(): string {
		return esc_html__( 'User Info', 'sky-elementor-addons' );
	}

	public function get_group(): array {
		return [ 'sky-addons-user' ];
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
		$this->common_user_controls();

		$this->add_control(
			'user_info_type',
			[
				'label'   => esc_html__( 'User Info Type', 'sky-elementor-addons' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'id'           => esc_html__( 'ID', 'sky-elementor-addons' ),
					'name'         => esc_html__( 'Name', 'sky-elementor-addons' ),
					'email'        => esc_html__( 'Email', 'sky-elementor-addons' ),
					'website'      => esc_html__( 'Website', 'sky-elementor-addons' ),
					'display_name' => esc_html__( 'Display Name', 'sky-elementor-addons' ),
					'nickname'     => esc_html__( 'Nickname', 'sky-elementor-addons' ),
					'first_name'   => esc_html__( 'First Name', 'sky-elementor-addons' ),
					'last_name'    => esc_html__( 'Last Name', 'sky-elementor-addons' ),
					'description'  => esc_html__( 'Description', 'sky-elementor-addons' ),
					'role'         => esc_html__( 'Role', 'sky-elementor-addons' ),
				],
				'default' => 'name',
			]
		);
	}

	protected function register_advanced_section() {
		$this->advanced_controls();
	}

	public function render(): void {
		$user_id = $this->get_user_id();
		if ( empty( $user_id ) ) {
			return;
		}

		$user = get_user_by( 'id', $user_id );
		if ( empty( $user ) ) {
			return;
		}

		$user_info_type = $this->get_settings_for_display( 'user_info_type' );
		switch ( $user_info_type ) {
			case 'id':
				echo esc_html( $user_id );
				break;
			case 'name':
				echo esc_html( $user->display_name );
				break;
			case 'email':
				echo esc_html( $user->user_email );
				break;
			case 'website':
				echo esc_html( $user->user_url );
				break;
			case 'display_name':
				echo esc_html( $user->display_name );
				break;
			case 'nickname':
				echo esc_html( $user->nickname );
				break;
			case 'first_name':
				echo esc_html( $user->first_name );
				break;
			case 'last_name':
				echo esc_html( $user->last_name );
				break;
			case 'description':
				echo esc_html( $user->description );
				break;
			case 'role':
				echo esc_html( $user->roles[0] );
				break;
			default:
				echo esc_html( $user->display_name );
				break;
		}
	}
}

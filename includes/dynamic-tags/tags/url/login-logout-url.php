<?php

namespace Sky_Addons\Includes\DynamicTags\Tags\Url;

use Sky_Addons\Includes\Traits\UtilsTrait;
use Elementor\Core\DynamicTags\Data_Tag;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Dynamic_Tag_Login_Logout_URL extends Data_Tag {

	use UtilsTrait;

	public function get_name(): string {
		return 'sky-addons-login-logout-url';
	}

	public function get_title(): string {
		return esc_html__( 'Login/Logout URL', 'sky-elementor-addons' );
	}

	public function get_group(): array {
		return [ 'sky-addons-user' ];
	}

	public function get_categories(): array {
		return [
			\Elementor\Modules\DynamicTags\Module::URL_CATEGORY,
		];
	}

	public function is_settings_required() {
		return false;
	}

	protected function register_controls(): void {
		$this->add_control(
			'sky_url_type',
			[
				'label'   => esc_html__( 'URL Type', 'sky-elementor-addons' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'login'         => esc_html__( 'Login', 'sky-elementor-addons' ),
					'logout'        => esc_html__( 'Logout', 'sky-elementor-addons' ),
					'register'      => esc_html__( 'Register', 'sky-elementor-addons' ),
					'lost_password' => esc_html__( 'Lost Password', 'sky-elementor-addons' ),
				],
				'default' => 'login',
			]
		);

		$this->add_control(
			'sky_redirect_url',
			[
				'label'       => esc_html__( 'Redirect URL', 'sky-elementor-addons' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'Enter redirect URL', 'sky-elementor-addons' ),
				'description' => esc_html__( 'Where to redirect after login/logout. Leave empty for default behavior.', 'sky-elementor-addons' ),

			]
		);

		$this->fallback_control();
	}

	protected function register_advanced_section(): void {}

	public function get_value( array $options = [] ) {
		$url_type     = $this->get_settings( 'sky_url_type' );
		$redirect_url = $this->get_settings( 'sky_redirect_url' );
		$url          = '';

		switch ( $url_type ) {
			case 'login':
				$url = wp_login_url( $redirect_url );
				break;
			case 'logout':
				$url = wp_logout_url( $redirect_url );
				break;
			case 'register':
				$url = wp_registration_url();
				break;
			case 'lost_password':
				$url = wp_lostpassword_url( $redirect_url );
				break;
		}

		return $url;
	}
}

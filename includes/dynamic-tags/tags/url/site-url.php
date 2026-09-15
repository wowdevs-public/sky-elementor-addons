<?php

namespace Sky_Addons\Includes\DynamicTags\Tags\Url;

use Sky_Addons\Includes\Traits\UtilsTrait;
use Elementor\Core\DynamicTags\Data_Tag;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Dynamic_Tag_Site_URL extends Data_Tag {
	use UtilsTrait;

	public function get_name(): string {
		return 'sky-addons-site-url';
	}

	public function get_title(): string {
		return esc_html__( 'Site URL', 'sky-elementor-addons' );
	}

	public function get_group(): array {
		return [ 'sky-addons-site' ];
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
					'home'  => esc_html__( 'Home URL', 'sky-elementor-addons' ),
					'site'  => esc_html__( 'Site URL', 'sky-elementor-addons' ),
					'admin' => esc_html__( 'Admin URL', 'sky-elementor-addons' ),
				],
				'default' => 'home',
			]
		);

		$this->fallback_control();
	}

	protected function register_advanced_section(): void {}

	public function get_value( array $options = [] ) {
		$url_type = $this->get_settings( 'sky_url_type' );
		$url      = '';

		switch ( $url_type ) {
			case 'site':
				$url = get_site_url();
				break;
			case 'admin':
				$url = admin_url();
				break;
			case 'home':
			default:
				$url = home_url();
				break;
		}

		return $url;
	}
}

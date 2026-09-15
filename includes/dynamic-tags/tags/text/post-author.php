<?php

namespace Sky_Addons\Includes\DynamicTags\Tags\Text;

use Sky_Addons\Includes\Traits\UtilsTrait;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Dynamic_Tag_Post_Author extends \Elementor\Core\DynamicTags\Tag {

	use UtilsTrait;

	public function get_name(): string {
		return 'sky-addons-post-author';
	}

	public function get_title(): string {
		return esc_html__( 'Post Author', 'sky-elementor-addons' );
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
			'sky_author_data_type',
			[
				'label'   => esc_html__( 'Data Type', 'sky-elementor-addons' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'ID'           => esc_html__( 'ID', 'sky-elementor-addons' ),
					'display_name' => esc_html__( 'Display Name', 'sky-elementor-addons' ),
					'description'  => esc_html__( 'Bio', 'sky-elementor-addons' ),
					'user_email'   => esc_html__( 'Email', 'sky-elementor-addons' ),
					'first_name'   => esc_html__( 'First Name', 'sky-elementor-addons' ),
					'last_name'    => esc_html__( 'Last Name', 'sky-elementor-addons' ),
					'user_login'   => esc_html__( 'Username', 'sky-elementor-addons' ),
					'user_role'    => esc_html__( 'Role', 'sky-elementor-addons' ),
				],
				'default' => 'display_name',
			]
		);
	}

	protected function register_advanced_section() {
		$this->advanced_controls();
	}

	public function render(): void {
		$settings = $this->get_settings();
		$post_id  = $this->get_post_id();
		if ( ! $post_id ) {
			return;
		}
		$post = get_post( $post_id );
		if ( ! $post ) {
			return;
		}
		$author_id = $post->post_author;
		$data_type = $settings['sky_author_data_type'] ?? 'display_name';
		$output    = '';
		switch ( $data_type ) {
			case 'description':
				$output = get_the_author_meta( 'description', $author_id );
				break;
			case 'user_email':
				$output = get_the_author_meta( 'user_email', $author_id );
				break;
			case 'first_name':
				$output = get_the_author_meta( 'first_name', $author_id );
				break;
			case 'last_name':
				$output = get_the_author_meta( 'last_name', $author_id );
				break;
			case 'user_login':
				$output = get_the_author_meta( 'user_login', $author_id );
				break;
			case 'user_role':
				$user   = get_userdata( $author_id );
				$output = $user ? implode( ', ', $user->roles ) : '';
				break;
			case 'ID':
				$output = $author_id;
				break;
			case 'display_name':
			default:
				$output = get_the_author_meta( 'display_name', $author_id );
				break;
		}
		echo wp_kses_post( $this->apply_word_limit( $output ) );
	}
}

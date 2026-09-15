<?php

namespace Sky_Addons\Includes\DynamicTags\Tags\Image;

use Sky_Addons\Includes\Traits\UtilsTrait;
use Elementor\Core\DynamicTags\Data_Tag;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Dynamic_Tag_Post_Author_Avatar extends Data_Tag {
	use UtilsTrait;

	public function get_name(): string {
		return 'sky-addons-post-author-avatar';
	}

	public function get_title(): string {
		return esc_html__( 'Author Avatar', 'sky-elementor-addons' );
	}

	public function get_group(): array {
		return [ 'sky-addons-post' ];
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
		$this->common_post_controls();

		$this->add_control(
			'fallback',
			[
				'label'       => esc_html__( 'Fallback', 'sky-elementor-addons' ),
				'type'        => \Elementor\Controls_Manager::MEDIA,
				'dynamic'     => [ 'active' => true ],
				// Deliberately specific about when this fires. get_avatar_url()
				// returns a URL whenever avatars are enabled — the mystery-person
				// image included — and never reports that the URL is unreachable,
				// so a Fallback cannot stand in for a blocked or missing Gravatar.
				// Set Discussion → Default Avatar for that case.
				'description' => esc_html__( 'Used when the post has no author, or when avatars are turned off in Settings → Discussion. An author with no Gravatar still gets WordPress\'s default avatar image, not this one.', 'sky-elementor-addons' ),
			]
		);
	}

	protected function register_advanced_section(): void {}

	public function get_value( array $options = [] ) {
		$post_id = $this->get_post_id();

		if ( ! $post_id ) {
			return $this->get_settings( 'fallback' );
		}

		$author_id = get_post_field( 'post_author', $post_id );

		if ( ! $author_id ) {
			return $this->get_settings( 'fallback' );
		}

		$avatar_url = get_avatar_url( $author_id, [ 'size' => 512 ] );

		if ( $avatar_url ) {
			$image_data = [
				'id'  => 0, // Avatar doesn't have an attachment ID
				'url' => $avatar_url,
			];
		} else {
			$image_data = $this->get_settings( 'fallback' );
		}

		return $image_data;
	}
}

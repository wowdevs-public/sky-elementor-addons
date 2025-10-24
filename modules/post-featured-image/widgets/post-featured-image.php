<?php

namespace Sky_Addons\Modules\PostFeaturedImage\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Image_Size;
use Elementor\Widget_Base;
use Elementor\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Post_Featured_Image extends \Elementor\Widget_Image {

	public function get_name() {
		return 'sky-post-featured-image';
	}

	public function get_title() {
		return esc_html__( 'Post Featured Image', 'sky-elementor-addons' );
	}

	public function get_icon() {
		return 'sky-icon-post-featured-image';
	}

	public function get_categories() {
		return [ 'sky-elementor-addons' ];
	}

	public function get_keywords() {
		return [ 'sky', 'post', 'title', 'themebuilder', 'single' ];
	}

	protected function register_controls() {
		parent::register_controls();

		$this->update_control(
			'section_image',
			[
				'label' => esc_html__( 'Post Featured Image', 'sky-elementor-addons' ),
			]
		);
		$this->update_control(
			'section_style_image',
			[
				'label' => esc_html__( 'Post Featured Image', 'sky-elementor-addons' ),
			]
		);
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$post_id = get_the_ID();
		$settings['caption_source'] = 'attachment';

		if ( sky_addons_editor_mode() ) {
			// elementor default placeholder
			$featured_image_url = Utils::get_placeholder_image_src();
			$featured_image_id = '';
		} else {
			$featured_image_id = get_post_thumbnail_id( $post_id );
				$featured_image_url = wp_get_attachment_image_url( $featured_image_id, $settings['image_size'] );
		}

		if ( empty( $featured_image_url ) ) {
			return;
		}

		$link = get_permalink();

		if ( 'yes' === $settings['enable_link'] ) {
			$this->add_render_attribute( 'link', [
				'class' => 'elementor-clickable',
				'href'  => $link,
			] );
		}

		?>
			<?php if ( 'yes' === $settings['has_caption'] ) : ?>
				<figure class="wp-caption">
			<?php endif; ?>
			<?php if ( $link ) : ?>
				<a <?php $this->print_render_attribute_string( 'link' ); ?>>
			<?php endif; ?>
				<img src="<?php echo esc_url( $featured_image_url ); ?>" alt="<?php echo esc_attr( get_post_meta( $featured_image_id, '_wp_attachment_image_alt', true ) ); ?>" />
			<?php if ( $link ) : ?>
				</a>
			<?php endif; ?>
			<?php if ( 'yes' === $settings['has_caption'] ) : ?>
				<figcaption class="widget-image-caption wp-caption-text">
				<?php echo wp_kses_post( $this->get_caption( $settings, $featured_image_id ) ); ?>
		</figcaption>
			<?php endif; ?>
			<?php if ( 'yes' === $settings['has_caption'] ) : ?>
				</figure>
			<?php endif; ?>
		<?php
	}

	/**
	 * Get the caption for current widget.
	 *
	 * @access private
	 * @since 2.3.0
	 * @param $settings
	 *
	 * @return string
	 */
	private function get_caption( $settings, $featured_image_id ) {
		$caption = '';
		if ( ! empty( $settings['caption_source'] ) ) {
			switch ( $settings['caption_source'] ) {
				case 'attachment':
					$caption = wp_get_attachment_caption( $featured_image_id );
					break;
				case 'custom':
					$caption = ! Utils::is_empty( $settings['caption'] ) ? $settings['caption'] : '';
			}
		}
		return $caption;
	}

	public function content_template() {}
}

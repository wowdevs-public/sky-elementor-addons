<?php

namespace Sky_Addons\Traits;

use Elementor\Utils;
use Elementor\Icons_Manager;
use Elementor\Embed;
use Elementor\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

trait Global_Widget_Functions {

	protected function render_post_image( $add = [] ) {
		$image_id       = $add['image_id'];
		$thumbnail_size = $add['thumbnail_size'];

		$wrapper_class = 'sa-post-img';

		$placeholder_image_src = Utils::get_placeholder_image_src();
		$image_src             = wp_get_attachment_image_src( $image_id, $thumbnail_size );

		if ( isset( $add['wrapper_class'] ) ) {
			$wrapper_class .= ' ' . $add['wrapper_class'];
		}

		if ( ! $image_src ) {
			printf( '<img class="%1$s" src="%2$s" alt="%3$s">', esc_attr( $wrapper_class ), esc_url( $placeholder_image_src ), esc_html( get_the_title() ) );
		} else {
			print( wp_get_attachment_image(
				$image_id,
				$thumbnail_size,
				false,
				[
					'class' => $wrapper_class,
					'alt'   => esc_html( get_the_title() ),
				]
			) );
		}
	}

	protected function render_post_title( $add = [] ) {
		$settings      = $this->get_settings_for_display();
		$wrapper_class = 'sa-post-title';

		if ( ! isset( $settings['show_title'] ) || 'yes' !== $settings['show_title'] ) {
			return;
		}

		if ( isset( $add['wrapper_class'] ) ) {
			$wrapper_class .= ' ' . $add['wrapper_class'];
		}

		printf(
			'<%1$s class="%2$s"><a href="%3$s" title="%4$s">%4$s</a></%1$s>',
			esc_attr( Utils::validate_html_tag( $settings['title_tag'] ) ),
			esc_attr( $wrapper_class ),
			esc_url( get_permalink() ),
			esc_html( get_the_title() )
		);
	}

	/**
	 * Feature Version - Alpha
	 *
	 * @since 1.0.11
	 * Used - Mate Slider
	 */
	protected function render_post_title_attr( $id, $add = [] ) {
		$settings = $this->get_settings_for_display();

		if ( ! isset( $settings['show_title'] ) || 'yes' !== $settings['show_title'] ) {
			return;
		}

		$this->add_render_attribute( $id, $add );

		printf(
			'<%1$s ' . wp_kses_post( $this->get_render_attribute_string( $id ) ) . '><a href="%2$s" title="%3$s">%3$s</a></%1$s>',
			esc_attr( Utils::validate_html_tag( $settings['title_tag'] ) ),
			esc_url( get_permalink() ),
			esc_html( get_the_title() )
		);
	}

	protected function render_post_category( $add = [] ) {
		$settings      = $this->get_settings_for_display();
		$wrapper_class = 'sa-post-category';

		if ( ! isset( $settings['show_category'] ) || 'yes' !== $settings['show_category'] ) {
			return;
		}

		if ( ! function_exists( 'sky_addons_get_post_category' ) ) {
			return;
		}

		if ( isset( $add['wrapper_class'] ) ) {
			$wrapper_class .= ' ' . $add['wrapper_class'];
		}

		printf(
			'<div class="%1$s">%2$s</div>',
			esc_attr( $wrapper_class ),
			wp_kses_post( sky_addons_get_post_category( $this->get_settings( 'posts_source' ) ) )
		);
	}

	/**
	 * Feature Version - Alpha
	 *
	 * @since 1.0.11
	 * Used - Mate Slider
	 */
	protected function render_post_category_attr( $id, $add = [] ) {
		$settings = $this->get_settings_for_display();

		if ( ! isset( $settings['show_category'] ) || 'yes' !== $settings['show_category'] ) {
			return;
		}

		if ( ! function_exists( 'sky_addons_get_post_category' ) ) {
			return;
		}

		$this->add_render_attribute( $id, $add );

		printf(
			'<div ' . wp_kses_post( $this->get_render_attribute_string( $id ) ) . '>%1$s</div>',
			wp_kses_post( sky_addons_get_post_category( $this->get_settings( 'posts_source' ) ) )
		);
	}

	protected function render_post_date() {
		$settings = $this->get_settings_for_display();

		if ( ! isset( $settings['show_date'] ) || 'yes' !== $settings['show_date'] ) {
			return;
		}

		$date = get_the_date();

		if ( 'yes' === $settings['show_human_diff_time'] ) {
			$date = sky_addons_post_time_ago( ( 'yes' === $settings['human_diff_time_short'] ) ? 'short' : '' );
		}

		printf(
			'<span class="%1$s">%2$s</span>',
			'sa-post-date',
			wp_kses_post( $date )
		);

		if ( isset( $settings['show_time'] ) && 'yes' === $settings['show_time'] ) {
			// Was `sky-icon-clock`, which is undefined twice over: no such glyph
			// exists in widget-icons.less, and the sky-icon font it belongs to is
			// only ever enqueued in the editor and the admin, never on the
			// frontend. Show Time is a real control (register_post_date_controls),
			// so this printed a permanent blank box beside the time.
			//
			// The .sa-icon-wrap span is load-bearing, not decoration: there is no
			// global size rule for an inline icon, so an unwrapped <svg> falls back
			// to its viewBox and paints at hundreds of pixels. base.less constrains
			// both `i` and `svg` inside .sa-icon-wrap to 1em and fills them with
			// currentColor — the same wrapper every post widget already puts around
			// its author/date icons.
			printf(
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static markup, no user input.
				'<span class="%1$s"><span class="sa-icon-wrap sa-me-1"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000" aria-hidden="true"><path d="M500 917C270 917 83 730 83 500S270 83 500 83 917 270 917 500 730 917 500 917ZM698 607L538 502V310C538 290 521 272 500 272S462 290 462 310V522C462 535 468 547 479 554L656 671C673 682 697 677 708 660 720 642 715 619 698 607Z"></path></svg></span>%2$s</span>',
				'sa-post-time sa-ms-1 sa-d-inline-flex sa-align-items-center',
				wp_kses_post( get_the_time() )
			);
		}
	}

	protected function render_post_excerpt( $length, $trail = '' ) {
		$settings = $this->get_settings_for_display();

		if ( ! isset( $settings['show_excerpt'] ) || 'yes' !== $settings['show_excerpt'] ) {
			return;
		}
		$strip_shortcode = ( $settings['strip_shortcode'] ) ? true : false;
		$excerpt         = '';

		if ( has_excerpt() ) {
			$excerpt = get_the_excerpt();
		} else {
			$excerpt = sky_addons_post_custom_excerpt( $length, $strip_shortcode, $trail );
		}

		printf(
			'<div class="%1$s">%2$s</div>',
			'sa-post-text',
			wp_kses_post( $excerpt )
		);
	}

	protected function render_post_author( $add = [] ) {
		$wrapper_class = 'sa-post-author-wrapper';

		if ( isset( $add['wrapper_class'] ) ) {
			$wrapper_class .= ' ' . $add['wrapper_class'];
		}

		// Was `far fa-user`, swapped to the eicons `person` glyph so the plugin
		// carries no Font Awesome dependency at all. Same silhouette, and it now
		// matches the eicons the sibling widgets use for author and date.
		//
		// The .sa-icon-wrap span is load-bearing, not decoration: there is no
		// global size rule for an inline icon, so an unwrapped <svg> falls back to
		// its viewBox and paints at hundreds of pixels. base.less constrains both
		// `i` and `svg` inside .sa-icon-wrap to 1em and fills them with
		// currentColor — the same wrapper every post widget already puts around
		// its author/date icons.
		printf(
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- the icon is static markup, everything else is escaped.
			'<div class="%1$s"><a href="%2$s"><span class="sa-icon-wrap sa-me-1"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000" aria-hidden="true"><path d="M500 708C397 708 306 625 268 500 234 498 208 454 208 396 208 379 211 363 215 349 210 328 208 309 208 292 208 131 339 0 500 0S792 131 792 292C792 309 790 328 785 349 790 363 792 379 792 396 792 454 766 498 732 500 694 625 603 708 500 708ZM283 455C286 455 288 455 290 456 297 459 302 463 303 470 333 588 412 667 500 667S668 588 697 470C698 463 703 458 709 456 715 453 722 454 728 457 735 459 750 436 750 396 750 382 748 368 744 356 743 353 743 349 744 345 748 325 750 308 750 292 750 154 638 42 500 42S250 154 250 292C250 308 252 325 257 345 257 349 257 353 256 356 252 368 250 382 250 396 250 436 266 458 271 458 274 456 279 455 283 455ZM708 375C700 375 693 370 689 363 689 361 673 326 668 291 581 285 490 243 446 198 442 205 437 211 432 216 398 250 345 250 318 250 312 264 309 309 313 353 313 359 311 365 306 369 301 374 295 376 289 375 288 375 260 370 236 370 225 370 215 361 215 350S225 329 236 329C248 329 260 330 270 330 268 293 270 239 289 219 297 211 307 208 318 208 343 208 382 207 402 187 412 177 417 164 417 146 417 134 426 125 438 125S458 134 458 146C461 167 565 250 688 250 699 250 708 259 708 271 708 291 716 317 722 332L762 329C774 328 784 336 785 348 786 359 777 369 766 370L710 375C710 375 709 375 708 375ZM438 417H396C384 417 375 407 375 396S384 375 396 375H438C449 375 458 384 458 396S449 417 438 417ZM604 417H563C551 417 542 407 542 396S551 375 563 375H604C616 375 625 384 625 396S616 417 604 417ZM979 1000H21C14 1000 8 997 4 992 0 987-1 980 1 974L33 845C40 815 60 790 88 777L303 669C308 666 314 666 320 668 325 670 329 674 332 679L439 930 455 898C441 879 417 842 417 812 417 774 449 750 500 750S583 774 583 812C583 842 559 879 545 898L561 930 668 679C671 674 675 670 680 668 686 666 692 666 697 669L913 777C940 790 960 815 967 845L999 974C1001 980 1000 987 996 992 992 997 986 1000 979 1000ZM594 958H953L927 855C922 837 910 822 894 814L698 716 594 958ZM471 958H529L502 905C499 898 500 889 505 883 520 864 542 829 542 812 542 794 513 792 500 792S458 794 458 812C458 829 481 864 496 883 501 889 502 898 498 905L471 958ZM48 958H406L302 716 106 814C90 822 78 837 74 855L48 958Z"></path></svg></span><span>%3$s</span></a></div>',
			esc_attr( $wrapper_class ),
			esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
			wp_kses_post( get_the_author() )
		);
	}


	/**
	 * Start Video Lightbox in Global Posts
	 */
	public function get_post_lightbox_embed_params() {
		$settings = $this->get_settings_for_display();

		$params             = [];
		$params['autoplay'] = '0';

		if ( 'yes' === $settings['video_autoplay'] ) {
			$params['autoplay'] = '1';
			$params['mute']     = 1;
		}

		if ( 'yes' === $settings['mute'] ) {
			$params['mute'] = 1;
		}

		return $params;
	}

	/**
	 * Attributes for the `<video>` element the lightbox builds for hosted videos.
	 *
	 * The lightbox merges these over its own defaults (`src` + `autoplay`), so only
	 * additions belong here — see `setVideoContent()` in Elementor's lightbox bundle.
	 *
	 * @return array
	 */
	public function get_post_hosted_video_params() {
		$settings = $this->get_settings_for_display();

		$video_params = [
			'controls'    => '',
			'playsinline' => '',
		];

		// Browsers block autoplay with sound, so autoplay implies muted.
		if ( 'yes' === $settings['mute'] || 'yes' === $settings['video_autoplay'] ) {
			$video_params['muted'] = 'muted';
		}

		return $video_params;
	}

	public function get_post_lightbox_embed_options() {
		$settings                   = $this->get_settings_for_display();
		$embed_options              = [];
		$embed_options['lazy_load'] = ! empty( $settings['lazy_load'] );

		return $embed_options;
	}

	/**
	 * Get the video URL of a single post.
	 *
	 * The `video_source` control accepts two kinds of value:
	 *
	 * - A plain custom field key (default `sky_video_link_meta`), read per post.
	 * - A dynamic tag, which already resolves to the URL itself. Tags are resolved once
	 *   per post inside the loop, parsing only this control — parsing the whole widget on
	 *   every iteration would re-evaluate the conditions of every control.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return string Video URL, empty string when the post has none.
	 */
	protected function get_post_video_url( $post_id ) {
		$control  = $this->get_controls( 'video_source' );
		$meta_key = 'sky_video_link_meta';

		if ( ! empty( $control ) ) {
			$settings = $this->get_settings();

			if ( ! empty( $settings['__dynamic__']['video_source'] ) ) {
				$settings  = $this->parse_dynamic_settings( $settings, [ $control ], $settings );
				$video_url = isset( $settings['video_source'] ) ? $settings['video_source'] : '';

				return is_string( $video_url ) ? esc_url_raw( trim( $video_url ) ) : '';
			}

			if ( ! empty( $settings['video_source'] ) && is_string( $settings['video_source'] ) ) {
				$custom_key = trim( $settings['video_source'] );

				if ( '' !== $custom_key ) {
					$meta_key = $custom_key;
				}
			}
		}

		$video_url = get_post_meta( $post_id, $meta_key, true );

		return is_string( $video_url ) ? esc_url_raw( trim( $video_url ) ) : '';
	}

	public function render_post_video_lightbox( $video_url, $id ) {
		$settings = $this->get_settings_for_display();

		if ( empty( $video_url ) ) {
			return;
		}

		/**
		 * Work out the provider.
		 *
		 * `Embed::get_video_properties()` matches YouTube, Vimeo, Dailymotion and
		 * VideoPress. A null result means the URL is a hosted file — an .mp4 on the
		 * server, a CDN link — which must keep its raw URL, because
		 * `Embed::get_embed_url()` returns null for anything it cannot match.
		 *
		 * The provider name doubles as the lightbox `videoType`: it decides whether
		 * the lightbox builds a `<video>` element or an iframe.
		 */
		$video_properties = Embed::get_video_properties( $video_url );
		$video_type       = $video_properties ? $video_properties['provider'] : 'hosted';

		if ( $video_properties ) {
			$embed_params  = $this->get_post_lightbox_embed_params();
			$embed_options = $this->get_post_lightbox_embed_options();
			$lightbox_url  = Embed::get_embed_url( $video_url, $embed_params, $embed_options );
		} else {
			$lightbox_url = $video_url;
		}

		if ( empty( $lightbox_url ) ) {
			return;
		}

		if ( 'file' !== $settings['video_open'] ) {

			$lightbox_options = [
				'type'      => 'video',
				'videoType' => $video_type,
				'url'       => $lightbox_url,
				'autoplay'  => $settings['video_autoplay'],
				'modalOptions' => [
					'id'                       => 'elementor-lightbox-' . $id,
					'entranceAnimation'        => $settings['lightbox_content_animation'],
					'entranceAnimation_tablet' => isset( $settings['lightbox_content_animation_tablet'] ) ? $settings['lightbox_content_animation_tablet'] : '',
					'entranceAnimation_mobile' => isset( $settings['lightbox_content_animation_mobile'] ) ? $settings['lightbox_content_animation_mobile'] : '',
					'videoAspectRatio'         => $settings['aspect_ratio'],
				],
			];

			if ( 'hosted' === $video_type ) {
				$lightbox_options['videoParams'] = $this->get_post_hosted_video_params();
			}

			$this->add_render_attribute( 'lightbox-attr-' . $id, [
				'data-elementor-open-lightbox' => 'yes',
				'data-elementor-lightbox'      => wp_json_encode( $lightbox_options ),
				'data-e-action-hash'           => Plugin::instance()->frontend->create_action_hash( 'lightbox', $lightbox_options ),
			] );
		} else {
			$this->add_render_attribute( 'lightbox-attr-' . $id, [
				'href' => $lightbox_url,
			] );
			if ( 'yes' === $settings['file_new_tab'] ) {
				$this->add_render_attribute( 'lightbox-attr-' . $id, [
					'target' => '_blank',
				] );
			}
		}

		if ( Plugin::$instance->editor->is_edit_mode() ) {
			$this->add_render_attribute( 'lightbox-attr-' . $id, [
				'class' => 'elementor-clickable',
			] );
		}
	}

	protected function render_post_thumb_with_video( $post_id, $image_size = 'full', $add = [] ) {
		$settings      = $this->get_settings_for_display();
		$wrapper_class = 'sa-post-img-wrapper';
		$play_class    = 'sa-post-play-button sa-post-play-button-style-1 sa-icon-wrap sa-link sa-d-flex sa-justify-content-center sa-align-items-center sa-rounded-circle';

		if ( 'yes' !== $settings['show_image'] ) {
			return;
		}

		if ( isset( $add['wrapper_class'] ) ) {
			/**
			 * If you set any class then if required you must set flex also
			 */
			$wrapper_class .= ' ' . $add['wrapper_class'];
		} else {
			$wrapper_class .= ' sa-d-flex';
		}

		if ( isset( $add['play_class'] ) ) {
			$play_class .= ' ' . $add['play_class'];
		} else {
			$play_class .= ' sa-p-4';
		}

		/**
		 * Video Feature enabled
		 */

		$video_url = ( 'yes' === $settings['show_video'] ) ? $this->get_post_video_url( $post_id ) : '';

		if ( 'yes' === $settings['show_video'] ) :
			$tag = 'div';
			$id  = $this->get_id() . '-' . $post_id;

			/**
			 * Lightbox
			 */

			$this->render_post_video_lightbox( $video_url, $id );

			if ( 'file' === $settings['video_open'] ) {
				$tag = 'a';
			}
		endif;
		?>
		<div class="<?php print( esc_attr( $wrapper_class ) ); ?>">
			<?php if ( empty( $video_url ) || 'yes' !== $settings['show_video'] ) : ?>
				<!-- Extra - Link added in Image -->
				<a class="sa-w-100 sa-h-100" href="<?php echo esc_url( get_permalink() ); ?>"
					title="<?php echo esc_html( get_the_title() ); ?>">
					<?php
					// $this->render_post_image(get_post_thumbnail_id($post_id), $image_size);
					$this->render_post_image( [
						'image_id'       => get_post_thumbnail_id( $post_id ),
						'thumbnail_size' => $image_size,
					] );
					?>
				</a>
			<?php else : ?>
				<?php
				// $this->render_post_image(get_post_thumbnail_id($post_id), $image_size);
				$this->render_post_image( [
					'image_id'       => get_post_thumbnail_id( $post_id ),
					'thumbnail_size' => $image_size,
				] );
				?>
			<?php endif; ?>

			<?php
			if ( 'yes' === $settings['show_video'] && ! empty( $video_url ) ) :
				$this->add_render_attribute( 'lightbox-attr-' . $id, [
					'class' => $play_class,
				] );
				?>
				<div class="sa-post-play-button-wrapper sa-abs-transform-middle">
					<<?php echo esc_attr( $tag ); ?>
						<?php $this->print_render_attribute_string( 'lightbox-attr-' . $id ); ?>>
						<!-- <i class="fas fa-play"></i> -->
						<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
							<path
								d="M424.4 214.7L72.4 6.6C43.8-10.3 0 6.1 0 47.9V464c0 37.5 40.7 60.1 72.4 41.3l352-208c31.4-18.5 31.5-64.1 0-82.6z" />
						</svg>
					</<?php echo esc_attr( $tag ); ?>>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * End Video Lightbox in Global Posts
	 */
	protected function render_post_general_button() {
		$settings  = $this->get_settings_for_display();
		$id        = $this->get_id();
		$link_attr = $id . get_the_ID();

		if ( 'yes' !== $settings['show_button'] ) {
			return;
		}

		$this->add_render_attribute( $link_attr, 'href', esc_url( get_permalink() ) );
		$this->add_render_attribute( $link_attr, 'class', 'sa-general-button sa-button sa-d-inline-block sa-text-decoration-none sa-p-2 sa-px-4 sa-rounded' );

		if ( $settings['button_hover_animation'] ) {
			$this->add_render_attribute( $link_attr, 'class', 'elementor-animation-' . $settings['button_hover_animation'] );
		}

		if ( ! empty( $settings['button_text'] ) ) :
			$this->add_render_attribute( $link_attr, 'class', 'sa-button-icon-' . $settings['button_icon_position'] );
		endif;

		?>
		<a <?php $this->print_render_attribute_string( $link_attr ); ?>>
			<?php
			if ( ! empty( $settings['button_icon']['value'] ) && 'before' === $settings['button_icon_position'] ) {
				Icons_Manager::render_icon( $settings['button_icon'], [
					'aria-hidden' => 'true',
					'class'       => 'sa-button-icon',
				] );
			}

			if ( ! empty( $settings['button_text'] ) ) :
				$this->add_render_attribute( 'button_text', 'class', 'sa-button-text' );
				$this->add_inline_editing_attributes( 'button_text', 'none' );

				printf(
					'<span %1$s>%2$s</span>',
					wp_kses_post( $this->get_render_attribute_string( 'button_text' ) ),
					esc_html( $settings['button_text'] )
				);

			endif;
			if ( ! empty( $settings['button_icon']['value'] ) && 'after' === $settings['button_icon_position'] ) {
				Icons_Manager::render_icon( $settings['button_icon'], [
					'aria-hidden' => 'true',
					'class'       => 'sa-button-icon',
				] );
			}
			?>
		</a>
		<?php
	}
}

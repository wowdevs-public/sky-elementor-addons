<?php

namespace Sky_Addons\Features;

defined( 'ABSPATH' ) || exit;

class Svg_Support {
	private static $instance = null;

	private function __construct() {
		$this->init();
	}

	public function init() {
		add_filter( 'upload_mimes', [ $this, 'set_svg_mimes' ] );
		add_filter( 'wp_prepare_attachment_for_js', [ $this, 'prepare_attachment_modal_for_svg' ], 10, 3 );
		add_filter( 'wp_generate_attachment_metadata', [ $this, 'svg_attachment_metadata' ], 10, 3 );
		add_filter( 'wp_get_attachment_metadata', [ $this, 'get_attachment_metadata' ], 10, 2 );
	}

	/**
	 * Add Mime Types
	 */
	public function set_svg_mimes( $mimes = [] ) {
		if ( current_user_can( 'administrator' ) ) {
			// allow SVG file upload
			$mimes['svg']  = 'image/svg+xml';
			$mimes['svgz'] = 'image/svg+xml';
		}
		return $mimes;
	}

	public function prepare_attachment_modal_for_svg( $response, $attachment, $meta ) {
		if ( 'image/svg+xml' === $response['mime'] && empty( $response['sizes'] ) ) {
			$svg_path = get_attached_file( $attachment->ID );
			if ( ! file_exists( $svg_path ) ) {
				// If SVG is external, use the URL instead of the path
				$svg_path = $response['url'];
			}
			$dimensions = $this->get_dimensions( $svg_path );
			$response['sizes'] = [
				'full' => [
					'url'         => $response['url'],
					'width'       => $dimensions->width,
					'height'      => $dimensions->height,
					'orientation' => $dimensions->width > $dimensions->height ? 'landscape' : 'portrait',
				],
			];
		}
		return $response;
	}

	public function svg_attachment_metadata( $metadata, $attachment_id ) {
		$mime = get_post_mime_type( $attachment_id );
		if ( 'image/svg+xml' === $mime ) {
			$svg_path   = get_attached_file( $attachment_id );
			$upload_dir = wp_upload_dir();
			$relative_path = str_replace( $upload_dir['basedir'], '', $svg_path );
			$filename      = basename( $svg_path );
			$dimensions = $this->get_dimensions( $svg_path );
			$metadata = [
				'width'  => intval( $dimensions->width ),
				'height' => intval( $dimensions->height ),
				'file'   => $relative_path,
			];

			$sizes = [];
			global $wp_additional_image_sizes; // Access the global variable
			foreach ( get_intermediate_image_sizes() as $s ) {
				$sizes[ $s ] = [
					'width'  => '',
					'height' => '',
					'crop'   => false,
				];
				if ( isset( $wp_additional_image_sizes[ $s ]['width'] ) ) {
					$sizes[ $s ]['width'] = intval( $wp_additional_image_sizes[ $s ]['width'] );
				} else {
					$sizes[ $s ]['width'] = get_option( "{$s}_size_w" );
				}
				if ( isset( $wp_additional_image_sizes[ $s ]['height'] ) ) {
					$sizes[ $s ]['height'] = intval( $wp_additional_image_sizes[ $s ]['height'] );
				} else {
					$sizes[ $s ]['height'] = get_option( "{$s}_size_h" );
				}
				if ( isset( $wp_additional_image_sizes[ $s ]['crop'] ) ) {
					$sizes[ $s ]['crop'] = intval( $wp_additional_image_sizes[ $s ]['crop'] );
				} else {
					$sizes[ $s ]['crop'] = get_option( "{$s}_crop" );
				}
				$sizes[ $s ]['file']      = $filename;
				$sizes[ $s ]['mime-type'] = 'image/svg+xml';
			}
			$metadata['sizes'] = $sizes;
		}
		return $metadata;
	}

	public function get_attachment_metadata( $data, $attachment_id ) {
		$mime = get_post_mime_type( $attachment_id );
		$type = current( explode( '/', $mime ) );
		if ( 'image' !== $type ) {
			return $data;
		}
		if ( ! isset( $data['width'] ) || ! isset( $data['height'] ) ) {
			return false;
		}
		return $data;
	}

	private function get_dimensions( $svg ) {
		$svg = simplexml_load_file( $svg );
		if ( false === $svg ) {
			$width  = '0';
			$height = '0';
		} else {
			$attributes = $svg->attributes();
			$width      = (string) $attributes->width;
			$height     = (string) $attributes->height;
		}
		return (object) [
			'width'  => $width,
			'height' => $height,
		];
	}

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}

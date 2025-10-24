<?php

namespace Sky_Addons\Modules\PostFeaturedImage;

use Sky_Addons\Base\Module_Base;
use Elementor\Widget_Base;
class Module extends Module_Base {

	public function __construct() {
		parent::__construct();

		add_action(
			'elementor/element/before_section_end',
			function( $section, $section_id, $args ) {
				if ( 'sky-post-featured-image' === $section->get_name() && 'section_image' === $section_id ) {
					$section->remove_control( 'image' );
					$section->remove_control( 'caption_source' );
					$section->remove_control( 'caption' );
					$section->remove_control( 'link_to' );
					$section->remove_control( 'link' );
					$section->remove_control( 'open_lightbox' );

					$section->add_control(
						'enable_link',
						[
							'label'        => esc_html__( 'Enable Link', 'sky-elementor-addons' ),
							'type'         => \Elementor\Controls_Manager::SWITCHER,
							'return_value' => 'yes',
							'default'      => 'yes',
						]
					);
					$section->add_control(
						'has_caption',
						[
							'label'        => esc_html__( 'Enable Caption', 'sky-elementor-addons' ),
							'type'         => \Elementor\Controls_Manager::SWITCHER,
							'return_value' => 'yes',
						]
					);
				}
				if ( 'sky-post-featured-image' === $section->get_name() && 'section_title_style' === $section_id ) {
					$section->add_control(
						'post_title_margin',
						[
							'label'      => esc_html__( 'Margin', 'sky-elementor-addons' ),
							'type'       => \Elementor\Controls_Manager::DIMENSIONS,
							'size_units' => [ 'px', 'em', '%' ],
							'separator'  => 'before',
							'selectors'  => [
								'{{WRAPPER}} .elementor-heading-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
							],
						]
					);
				}
			}, 10, 3
		);
	}

	public function get_name() {
		return 'post-featured-image';
	}

	public function get_widgets() {
		return [
			'Post_Featured_Image',
		];
	}
}

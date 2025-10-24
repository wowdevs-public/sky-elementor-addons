<?php

namespace Sky_Addons\Modules\PostTitle;

use Sky_Addons\Base\Module_Base;
use Elementor\Widget_Base;
class Module extends Module_Base {

	public function __construct() {
		parent::__construct();

		add_action(
			'elementor/element/before_section_end',
			function( $section, $section_id, $args ) {
				if ( 'sky-post-title' === $section->get_name() && 'section_title' === $section_id ) {
					$section->remove_control( 'title' );
					$section->remove_control( 'link' );
					$section->remove_control( 'animated_headline_promotion' );

					$section->add_control(
						'enable_title_link',
						[
							'label'        => esc_html__( 'Enable Title Link', 'sky-elementor-addons' ),
							'type'         => \Elementor\Controls_Manager::SWITCHER,
							'return_value' => 'yes',
							'default'      => 'yes',
						]
					);
				}
				if ( 'sky-post-title' === $section->get_name() && 'section_title_style' === $section_id ) {
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

					$section->add_group_control(
						\Elementor\Group_Control_Text_Stroke::get_type(),
						[
							'name'     => 'post_title_text_stroke',
							'selector' => '{{WRAPPER}} .elementor-heading-title',
						]
					);

				}
			}, 10, 3
		);
	}

	public function get_name() {
		return 'post-title';
	}

	public function get_widgets() {
		return [
			'Post_Title',
		];
	}
}

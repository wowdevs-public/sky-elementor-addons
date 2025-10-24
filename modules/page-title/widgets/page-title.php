<?php

namespace Sky_Addons\Modules\PageTitle\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Widget_Base;
use Elementor\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Page_Title extends \Sky_Addons\Modules\PostTitle\Widgets\Post_Title {

	public function get_name() {
		return 'sky-page-title';
	}

	public function get_title() {
		return esc_html__( 'Page Title', 'sky-elementor-addons' );
	}

	public function get_icon() {
		return 'sky-icon-page-title';
	}

	public function get_categories() {
		return [ 'sky-elementor-addons' ];
	}

	public function get_keywords() {
		return [ 'sky', 'post', 'title', 'themebuilder', 'single' ];
	}

	protected function register_controls() {
		// Add style controls from the parent Elementor Heading widget
		parent::register_controls();

		$this->update_control(
			'section_title',
			[
				'label' => esc_html__( 'Page Title', 'sky-elementor-addons' ),
			]
		);
		$this->update_control(
			'section_title_style',
			[
				'label' => esc_html__( 'Page Title', 'sky-elementor-addons' ),
			]
		);
	}


	public function content_template() {}
}

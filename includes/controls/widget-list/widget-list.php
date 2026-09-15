<?php

namespace Sky_Addons\Includes\Controls\WidgetList;

use Elementor\Control_Select2;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Sa_Widget_List extends Control_Select2 {

	const TYPE = 'sa-widget-list';

	public function get_type() {
		return self::TYPE;
	}
}

add_action( 'elementor/controls/register', function () {
	\Elementor\Plugin::$instance->controls_manager->register( new Sa_Widget_List() );
} );

<?php

namespace Sky_Addons\Modules\TestimonialCarousel;

use Sky_Addons\Base\Module_Base;

class Module extends Module_Base {

	public function __construct() {
		parent::__construct();
	}

	public function get_name() {
		return 'testimonial-carousel';
	}

	public function get_widgets() {
		return [
			'Testimonial_Carousel',
		];
	}
}

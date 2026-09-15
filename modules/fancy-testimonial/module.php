<?php

namespace Sky_Addons\Modules\FancyTestimonial;

use Sky_Addons\Base\Module_Base;

class Module extends Module_Base {

	public function __construct() {
		parent::__construct();
	}

	public function get_name() {
		return 'fancy-testimonial';
	}

	public function get_widgets() {
		return [
			'Fancy_Testimonial',
		];
	}
}

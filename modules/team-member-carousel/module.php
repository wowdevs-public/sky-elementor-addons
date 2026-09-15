<?php

namespace Sky_Addons\Modules\TeamMemberCarousel;

use Sky_Addons\Base\Module_Base;

class Module extends Module_Base {

	public function __construct() {
		parent::__construct();
	}

	public function get_name() {
		return 'team-member-carousel';
	}

	public function get_widgets() {
		return [
			'Team_Member_Carousel',
		];
	}
}

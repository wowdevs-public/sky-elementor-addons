<?php

namespace Sky_Addons\Modules\VideoPlayer;

use Sky_Addons\Base\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Module extends Module_Base {

	public function __construct() {
		parent::__construct();
	}

	public function get_name() {
		return 'video-player';
	}

	public function get_widgets() {
		return [
		    'Video_Player',
		];
	}
}

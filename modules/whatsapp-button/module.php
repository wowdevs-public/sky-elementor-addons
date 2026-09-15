<?php

namespace Sky_Addons\Modules\WhatsappButton;

use Sky_Addons\Base\Module_Base;

class Module extends Module_Base {

	public function __construct() {
		parent::__construct();
	}

	public function get_name() {
		return 'whatsapp-button';
	}

	public function get_widgets() {
		return [
			'Whatsapp_Button',
		];
	}
}

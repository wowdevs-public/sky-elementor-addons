<?php

namespace Sky_Addons\Modules\ReadingProgress\Skins;

use Elementor\Skin_Base as Elementor_Skin_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Skin_With_Cursor extends Elementor_Skin_Base {

	public function get_id() {
		return 'sky-skin-with-cursor';
	}

	public function get_title() {
		return esc_html__( 'With Cursor', 'sky-elementor-addons' );
	}

	protected function _register_controls_actions() {
		parent::_register_controls_actions();
	}

	public function render() {
		?>
		<div class="sa-reading-progress sa-skin-with-cursor"></div>
		<?php
	}
}

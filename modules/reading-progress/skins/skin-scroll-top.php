<?php

namespace Sky_Addons\Modules\ReadingProgress\Skins;

use Elementor\Skin_Base as Elementor_Skin_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Skin_Scroll_Top extends Elementor_Skin_Base {

	public function get_id() {
		return 'sky-skin-scroll-top';
	}

	public function get_title() {
		return esc_html__( 'Scroll Top', 'sky-elementor-addons' );
	}

	protected function _register_controls_actions() {
		parent::_register_controls_actions();
	}

	public function render() {
		$settings  = $this->parent->get_settings_for_display();
		$threshold = ! empty( $settings['scroll_top_threshold']['size'] ) ? (int) $settings['scroll_top_threshold']['size'] : 50;
		$duration  = ! empty( $settings['scroll_top_anim_duration']['size'] ) ? (int) $settings['scroll_top_anim_duration']['size'] : 550;
		?>
		<div class="sa-reading-progress sa-skin-scroll-top"
			data-scroll-threshold="<?php echo esc_attr( $threshold ); ?>"
			data-scroll-duration="<?php echo esc_attr( $duration ); ?>">
			<svg class="progress-circle svg-content" width="100%" height="100%" viewBox="-1 -1 102 102">
				<path d="M50,1 a49,49 0 0,1 0,98 a49,49 0 0,1 0,-98" />
			</svg>
		</div>
		<?php
	}
}

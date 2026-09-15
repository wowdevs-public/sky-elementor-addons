<?php

namespace Sky_Addons\Modules\PortionEffect\Widgets;

use Elementor\Utils;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Repeater;
use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Portion_Effect extends Widget_Base {


	public function get_name() {
		return 'sky-portion-effect';
	}

	public function get_title() {
		return esc_html__( 'Portion Effect', 'sky-elementor-addons' );
	}

	public function get_icon() {
		return 'sky-icon-portion-effect';
	}

	public function get_categories() {
		return [ 'sky-elementor-addons' ];
	}

	public function get_keywords() {
		return [ 'sky', 'portion', 'effect', 'image', 'photo', 'portfolio' ];
	}
	public function get_style_depends() {
		if ( sky_addons_editor_mode() ) {
			return [ 'sky-addons-styles' ];
		}

		return [ 'sa-portion-effect' ];
	}
	public function get_script_depends() {
		if ( sky_addons_editor_mode() ) {
			return [ 'sky-addons-scripts' ];
		}

		return [ 'sa-portion-effect' ];
	}



	public function get_custom_help_url() {
		return 'https://skyaddons.com/docs/sky-addons/widgets/portion-effect/';
	}

	public function has_widget_inner_wrapper(): bool {
		return ! \Elementor\Plugin::$instance->experiments->is_feature_active( 'e_optimized_markup' );
	}

	protected function register_controls() {

		$this->start_controls_section(
			'section_number_layout',
			[
				'label' => esc_html__( 'Layout', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'image',
			[
				'label' => esc_html__( 'Choose Image', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::MEDIA,
				'default' => [
					'url' => Utils::get_placeholder_image_src(),
				],
			]
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'block_preset',
			[
				'label'   => esc_html__( 'Preset Position', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'auto',
				'options' => [
					'auto'      => esc_html__( 'Auto', 'sky-elementor-addons' ),
					'left-sm'   => esc_html__( 'Left · Small', 'sky-elementor-addons' ),
					'center-lg' => esc_html__( 'Center · Large', 'sky-elementor-addons' ),
					'right-sm'  => esc_html__( 'Right · Small', 'sky-elementor-addons' ),
					'far-left'  => esc_html__( 'Far Left · XS', 'sky-elementor-addons' ),
					'far-right' => esc_html__( 'Far Right · XS', 'sky-elementor-addons' ),
				],
			]
		);

		$repeater->add_control(
			'block_preset_note',
			[
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => esc_html__( 'Sliders below override the preset.', 'sky-elementor-addons' ),
				'content_classes' => 'elementor-descriptor',
				'condition'       => [
					'block_preset!' => 'auto',
				],
			]
		);

		$repeater->add_responsive_control(
			'block_height',
			[
				'label' => esc_html__( 'Height', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .sa-block{{CURRENT_ITEM}}' => 'height: {{SIZE}}%;',
				],
			]
		);

		$repeater->add_responsive_control(
			'block_width',
			[
				'label' => esc_html__( 'Width', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .sa-block{{CURRENT_ITEM}}' => 'width: {{SIZE}}%;',
				],
			]
		);

		$repeater->add_responsive_control(
			'block_top',
			[
				'label' => esc_html__( 'Top', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .sa-block{{CURRENT_ITEM}}' => 'top: {{SIZE}}%;',
				],
			]
		);

		$repeater->add_responsive_control(
			'block_left',
			[
				'label' => esc_html__( 'Left', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .sa-block{{CURRENT_ITEM}}' => 'left: {{SIZE}}%;',
				],
			]
		);

		$this->add_control(
			'block_list',
			[
				'label'     => esc_html__( 'Blocks', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::REPEATER,
				'fields'    => $repeater->get_controls(),
				'separator' => 'before',
				'default'   => [
					[],
					[],
					[],
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_animation_settings',
			[
				'label' => esc_html__( 'Animation & Hover', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'entrance_animation',
			[
				'label'        => esc_html__( 'Entrance Animation', 'sky-elementor-addons' ),
				'description'  => esc_html__( 'Panels fold in one by one when the widget scrolls into view.', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'sky-elementor-addons' ),
				'label_off'    => esc_html__( 'No', 'sky-elementor-addons' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'animation_repeat',
			[
				'label'        => esc_html__( 'Repeat on Scroll', 'sky-elementor-addons' ),
				'description'  => esc_html__( 'Re-animate each time the widget re-enters the screen after scrolling away.', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'sky-elementor-addons' ),
				'label_off'    => esc_html__( 'No', 'sky-elementor-addons' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => [
					'entrance_animation' => 'yes',
				],
			]
		);

		$this->add_control(
			'animation_threshold',
			[
				'label'       => esc_html__( 'Trigger Point (%)', 'sky-elementor-addons' ),
				'description' => esc_html__( 'How much of the widget must be visible on screen before the animation starts. 40% is a good default.', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::SLIDER,
				'range'       => [
					'px' => [
						'min'  => 0,
						'max'  => 100,
						'step' => 5,
					],
				],
				'default'     => [
					'unit' => 'px',
					'size' => 40,
				],
				'condition'   => [
					'entrance_animation' => 'yes',
				],
			]
		);

		$this->add_control(
			'animation_duration',
			[
				'label'       => esc_html__( 'Speed (ms)', 'sky-elementor-addons' ),
				'description' => esc_html__( 'How long each panel takes to fold in. Lower = faster.', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::SLIDER,
				'range'       => [
					'px' => [
						'min'  => 100,
						'max'  => 3000,
						'step' => 50,
					],
				],
				'default'     => [
					'unit' => 'px',
					'size' => 600,
				],
				'selectors'   => [
					'{{WRAPPER}}' => '--sky-pe-transition-duration: {{SIZE}}ms;',
				],
				'condition'   => [
					'entrance_animation' => 'yes',
				],
			]
		);

		$this->add_control(
			'animation_stagger',
			[
				'label'       => esc_html__( 'Panel Cascade (ms)', 'sky-elementor-addons' ),
				'description' => esc_html__( 'Wait time between each panel animating — creates the cascading waterfall effect. Set to 0 for all panels at once.', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::SLIDER,
				'range'       => [
					'px' => [
						'min'  => 0,
						'max'  => 500,
						'step' => 25,
					],
				],
				'default'     => [
					'unit' => 'px',
					'size' => 150,
				],
				'condition'   => [
					'entrance_animation' => 'yes',
				],
			]
		);

		$this->add_control(
			'animation_easing',
			[
				'label'       => esc_html__( 'Motion Style', 'sky-elementor-addons' ),
				'description' => esc_html__( 'The motion curve of the animation. Spring feels the most natural for 3D effects.', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'ease',
				'options'     => [
					'ease'                              => esc_html__( 'Ease — smooth start & end', 'sky-elementor-addons' ),
					'ease-in'                           => esc_html__( 'Ease In — slow start', 'sky-elementor-addons' ),
					'ease-out'                          => esc_html__( 'Ease Out — slow end', 'sky-elementor-addons' ),
					'ease-in-out'                       => esc_html__( 'Ease In Out — slow both', 'sky-elementor-addons' ),
					'linear'                            => esc_html__( 'Linear — constant speed', 'sky-elementor-addons' ),
					'cubic-bezier(0.34, 1.56, 0.64, 1)' => esc_html__( 'Spring — bouncy & natural', 'sky-elementor-addons' ),
				],
				'selectors'   => [
					'{{WRAPPER}}' => '--sky-pe-transition-easing: {{VALUE}};',
				],
				'condition'   => [
					'entrance_animation' => 'yes',
				],
			]
		);

		$this->add_control(
			'hover_heading',
			[
				'label'     => esc_html__( 'Hover Effect', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'hover_effect',
			[
				'label'        => esc_html__( 'Enable Hover', 'sky-elementor-addons' ),
				'description'  => esc_html__( 'Panels react when a visitor hovers over them.', 'sky-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'sky-elementor-addons' ),
				'label_off'    => esc_html__( 'No', 'sky-elementor-addons' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'hover_style',
			[
				'label'       => esc_html__( 'Hover Style', 'sky-elementor-addons' ),
				'description' => esc_html__( 'Choose how the panels move when hovered.', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'float',
				'options'     => [
					'float'   => esc_html__( 'Float — rises with a deep shadow', 'sky-elementor-addons' ),
					'unfold'  => esc_html__( 'Unfold — panel opens flat', 'sky-elementor-addons' ),
					'zoom'    => esc_html__( 'Zoom — pops forward & brightens', 'sky-elementor-addons' ),
					'spread'  => esc_html__( 'Spread — panels open wider', 'sky-elementor-addons' ),
					'swing'   => esc_html__( 'Swing — door swings wide open', 'sky-elementor-addons' ),
					'tilt'    => esc_html__( 'Tilt — leans on both 3D axes', 'sky-elementor-addons' ),
					'shutter' => esc_html__( 'Shutter — both panels open flat', 'sky-elementor-addons' ),
					'lean'    => esc_html__( 'Lean — block tilts sideways', 'sky-elementor-addons' ),
				],
				'condition'   => [
					'hover_effect' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'hover_lift',
			[
				'label'       => esc_html__( 'Lift Amount', 'sky-elementor-addons' ),
				'description' => esc_html__( 'How high the block floats up on hover.', 'sky-elementor-addons' ),
				'type'        => Controls_Manager::SLIDER,
				'size_units'  => [ 'px' ],
				'range'       => [
					'px' => [
						'min'  => 0,
						'max'  => 40,
						'step' => 1,
					],
				],
				'default'     => [
					'unit' => 'px',
					'size' => 8,
				],
				'selectors'   => [
					'{{WRAPPER}}' => '--sky-pe-hover-lift: -{{SIZE}}{{UNIT}};',
				],
				'condition'   => [
					'hover_effect' => 'yes',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_portion_style',
			[
				'label' => esc_html__( 'Portion', 'sky-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'portion_height',
			[
				'label'      => esc_html__( 'Height', 'sky-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', '%' ],
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 1000,
						'step' => 5,
					],
					'%'  => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .sa-portion-wrapper' => 'padding-top: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'box_shadow',
				'label'    => esc_html__( 'Box Shadow', 'sky-elementor-addons' ),
				'selector' => '{{WRAPPER}} .sa-side',
			]
		);

		$this->add_responsive_control(
			'portion_angel',
			[
				'label' => esc_html__( 'Angle', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min'  => 30,
						'max'  => 50,
						'step' => 1,
					],
				],
				'selectors' => [
					'{{WRAPPER}} ' => '--sky-pe-main-v-offset: {{SIZE}}deg;',
				],
			]
		);

		$this->add_control(
			'portion_left_heading',
			[
				'label'     => esc_html__( 'Left Side', 'sky-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'left_width',
			[
				'label' => esc_html__( 'Width', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					],
				],
				'selectors' => [
					'{{WRAPPER}} ' => '--sky-pe-left-width: {{SIZE}}%;',
				],
			]
		);

		$this->add_responsive_control(
			'left_rotate_y',
			[
				'label' => esc_html__( 'Rotate Y', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min'  => 50,
						'max'  => 70,
						'step' => 1,
					],
				],
				'selectors' => [
					'{{WRAPPER}} ' => '--sky-pe-media-v-offset: -{{SIZE}}deg;',
				],
			]
		);

		$this->add_responsive_control(
			'left_brightness',
			[
				'label' => esc_html__( 'Brightness', 'sky-elementor-addons' ),
				'type'  => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					],
				],
				'selectors' => [
					'{{WRAPPER}} ' => '--sky-pe-media-filter: {{SIZE}}%;',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$wrapper_classes = [ 'sa-portion-effect' ];
		if ( 'yes' === $settings['hover_effect'] ) {
			$hover_style       = ! empty( $settings['hover_style'] ) ? $settings['hover_style'] : 'unfold';
			$wrapper_classes[] = 'sa-hover-' . $hover_style;
		}

		$this->add_render_attribute(
			[
				'portion' => [
					'class' => $wrapper_classes,
					'data-settings' => [
						wp_json_encode( [
							'image'              => isset( $settings['image']['url'] ) ? esc_url( $settings['image']['url'] ) : '',
							'entrance_animation' => isset( $settings['entrance_animation'] ) ? $settings['entrance_animation'] : '',
							'animation_repeat'   => isset( $settings['animation_repeat'] ) ? $settings['animation_repeat'] : 'yes',
							'threshold'          => isset( $settings['animation_threshold']['size'] ) ? (int) $settings['animation_threshold']['size'] : 40,
							'stagger'            => isset( $settings['animation_stagger']['size'] ) ? (int) $settings['animation_stagger']['size'] : 150,
						] ),
					],
				],
			]
		);
		?>
		<div <?php $this->print_render_attribute_string( 'portion' ); ?>>
			<div class="sa-portion-wrapper">
				<?php
				$allowed_presets = [ 'left-sm', 'center-lg', 'right-sm', 'far-left', 'far-right' ];
				foreach ( $settings['block_list'] as $item ) {
					$preset       = isset( $item['block_preset'] ) ? $item['block_preset'] : 'auto';
					$preset_class = in_array( $preset, $allowed_presets, true ) ? ' sa-preset-' . $preset : '';
					?>
					<div class="sa-block<?php echo esc_attr( $preset_class ); ?> elementor-repeater-item-<?php echo esc_attr( $item['_id'] ); ?>">
						<div class="sa-side -main"></div>
						<div class="sa-side -left"></div>
					</div>
				<?php } ?>
			</div>
		</div>
		<?php
	}
}

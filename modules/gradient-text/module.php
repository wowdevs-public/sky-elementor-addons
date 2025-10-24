<?php

namespace Sky_Addons\Modules\GradientText;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Sky_Addons\Base\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Module extends Module_Base {

	private $element_selector = 'h1,h2,h3,h4,h5,h6,span,p,a,div';

	public function __construct() {
		parent::__construct();
		$this->add_actions();
	}

	public function get_name() {
		return 'sky-gradient-text';
	}

	public function register_section( $element ) {
		$element->start_controls_section(
			'section_sa_gt_controls',
			[
				'tab'   => Controls_Manager::TAB_ADVANCED,
				'label' => esc_html__( 'Gradient Text', 'sky-elementor-addons' ) . sky_addons_get_icon(),
			]
		);
		$element->end_controls_section();
	}

	public function register_controls( $widget, $args ) {
		$widget->add_control(
			'sky_gr_enable',
			[
				'label'              => esc_html__( 'Enable Gradient Text', 'sky-elementor-addons' ),
				'type'               => Controls_Manager::SWITCHER,
				'label_on'           => esc_html__( 'Yes', 'sky-elementor-addons' ),
				'label_off'          => esc_html__( 'No', 'sky-elementor-addons' ),
				'return_value'       => 'yes',
				'frontend_available' => true,
			]
		);

		$widget->add_control(
			'sky_gr_selectors',
			[
				'label'              => esc_html__( 'Custom Selector', 'sky-elementor-addons' ),
				'type'               => Controls_Manager::TEXTAREA,
				'placeholder'        => esc_html__( 'h1,h2,h3,h4,h5,h6,span,p,a,div', 'sky-elementor-addons' ),
				'label_block'        => true,
				'description'        => esc_html__( 'Add custom selector for gradient text. Default: h1,h2,h3,h4,h5,h6,span,p,a,div', 'sky-elementor-addons' ),
				'frontend_available' => true,
				'dynamic'            => [
					'active' => true,
				],
				'condition'          => [
					'sky_gr_enable' => 'yes',
				],
				'rows'               => 5,
			]
		);

		$widget->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'           => 'sky_gr_background',
				'label'          => esc_html__( 'Background', 'sky-elementor-addons' ),
				'fields_options' => [
					'background' => [
						'label'   => esc_html__( 'Text Color', 'sky-elementor-addons' ),
						'default' => 'gradient',
					],
				],
				'render_type'    => 'template',
				'condition'      => [
					'sky_gr_enable' => 'yes',
				],
				'selector'       => $this->get_wrapped_selectors(),
			]
		);

		$widget->add_control(
			'sky_gr_prefix',
			[
				'type'         => Controls_Manager::HIDDEN,
				'default'      => 'yes',
				'render_type'  => 'template',
				'prefix_class' => 'sky-gr-selectors-',
				'condition'    => [
					'sky_gr_enable'     => 'yes',
					'sky_gr_selectors!' => '',
				],
			]
		);
		$widget->add_control(
			'sky_gr_output',
			[
				'type'        => Controls_Manager::HIDDEN,
				'default'     => '1',
				'selectors'   => [
					$this->get_wrapped_selectors() => '-webkit-background-clip: text; -webkit-text-fill-color: transparent; background-color: transparent;',
				],
				'render_type' => 'template',
				'condition'   => [
					'sky_gr_enable' => 'yes',
				],
			]
		);
	}

	private function get_wrapped_selectors() {
		$selectors = [
			'{{WRAPPER}}:not(.sky-gr-selectors-yes) h1',
			'{{WRAPPER}}:not(.sky-gr-selectors-yes) h2',
			'{{WRAPPER}}:not(.sky-gr-selectors-yes) h3',
			'{{WRAPPER}}:not(.sky-gr-selectors-yes) h4',
			'{{WRAPPER}}:not(.sky-gr-selectors-yes) h5',
			'{{WRAPPER}}:not(.sky-gr-selectors-yes) h6',
			'{{WRAPPER}}:not(.sky-gr-selectors-yes) span',
			'{{WRAPPER}}:not(.sky-gr-selectors-yes) p',
			'{{WRAPPER}}:not(.sky-gr-selectors-yes) a',
			'{{WRAPPER}}:not(.sky-gr-selectors-yes) div',
			'{{WRAPPER}}.sky-gr-selectors-yes .sky-gr-text',
		];
		return implode( ', ', array_map( 'trim', $selectors ) );
	}

	protected function add_actions() {
		add_action( 'elementor/element/common/_section_background/after_section_end', [ $this, 'register_section' ] );
		add_action( 'elementor/element/common/section_sa_gt_controls/before_section_end', [ $this, 'register_controls' ], 10, 2 );
	}
}

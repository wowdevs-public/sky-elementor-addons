<?php

namespace Sky_Addons\Includes;

/**
 * WPML_Init class
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class WPML_Init {

	/**
	 * A reference to an instance of this class.
	 *
	 * @since 3.1.0
	 * @var   object
	 */
	private static $instance = null;

	/**
	 * Constructor for the class
	 */
	public function init() {

		// WPML String Translation plugin exist check
		if ( defined( 'WPML_ST_VERSION' ) ) {
			add_filter( 'wpml_elementor_widgets_to_translate', [ $this, 'add_translatable_nodes' ] );
		}
	}

	/**
	 * Load wpml required repeater class files.
	 *
	 * @return void
	 */
	public function load_wpml_modules() {

		include_once SKY_ADDONS_INC_PATH . 'wpml/class-wpml-modules.php';

		// Include all WPML module classes
		require_once SKY_ADDONS_INC_PATH . 'wpml/class-wpml-advanced-accordion.php';
		require_once SKY_ADDONS_INC_PATH . 'wpml/class-wpml-advanced-skill-bars.php';
		require_once SKY_ADDONS_INC_PATH . 'wpml/class-wpml-advanced-slider.php';
		require_once SKY_ADDONS_INC_PATH . 'wpml/class-wpml-animated-heading.php';
		require_once SKY_ADDONS_INC_PATH . 'wpml/class-wpml-card.php';
		require_once SKY_ADDONS_INC_PATH . 'wpml/class-wpml-content-switcher.php';
		require_once SKY_ADDONS_INC_PATH . 'wpml/class-wpml-dual-button.php';
		require_once SKY_ADDONS_INC_PATH . 'wpml/class-wpml-image-compare.php';
		require_once SKY_ADDONS_INC_PATH . 'wpml/class-wpml-info-box.php';
		require_once SKY_ADDONS_INC_PATH . 'wpml/class-wpml-list-group.php';
		require_once SKY_ADDONS_INC_PATH . 'wpml/class-wpml-logo-carousel.php';
		require_once SKY_ADDONS_INC_PATH . 'wpml/class-wpml-number.php';
		require_once SKY_ADDONS_INC_PATH . 'wpml/class-wpml-pdf-viewer.php';
		require_once SKY_ADDONS_INC_PATH . 'wpml/class-wpml-review.php';
		require_once SKY_ADDONS_INC_PATH . 'wpml/class-wpml-social-icons.php';
		require_once SKY_ADDONS_INC_PATH . 'wpml/class-wpml-step-flow.php';
		require_once SKY_ADDONS_INC_PATH . 'wpml/class-wpml-team-member.php';
		require_once SKY_ADDONS_INC_PATH . 'wpml/class-wpml-testimonial.php';
	}

	/**
	 * Add  translation nodes
	 *
	 * @param array $nodes_to_translate
	 * @return array
	 */
	public function add_translatable_nodes( $nodes_to_translate ) {

		$this->load_wpml_modules();

		// Advanced Accordion
		$nodes_to_translate['sky-advanced-accordion'] = [
			'conditions'        => [ 'widgetType' => 'sky-advanced-accordion' ],
			'fields'            => [],
			'integration-class' => __NAMESPACE__ . '\\WPML_Advanced_Accordion',
		];

		// Advanced Skill Bars
		$nodes_to_translate['sky-advanced-skill-bars'] = [
			'conditions'        => [ 'widgetType' => 'sky-advanced-skill-bars' ],
			'fields'            => [],
			'integration-class' => __NAMESPACE__ . '\\WPML_Advanced_Skill_Bars',
		];

		// Advanced Slider
		$nodes_to_translate['sky-advanced-slider'] = [
			'conditions'        => [ 'widgetType' => 'sky-advanced-slider' ],
			'fields'            => [],
			'integration-class' => __NAMESPACE__ . '\\WPML_Advanced_Slider',
		];

		// Animated Heading
		$nodes_to_translate['sky-animated-heading'] = [
			'conditions'        => [ 'widgetType' => 'sky-animated-heading' ],
			'fields'            => [],
			'integration-class' => __NAMESPACE__ . '\\WPML_Animated_Heading',
		];

		// Card
		$nodes_to_translate['sky-card'] = [
			'conditions'        => [ 'widgetType' => 'sky-card' ],
			'fields'            => [],
			'integration-class' => __NAMESPACE__ . '\\WPML_Card',
		];

		// Content Switcher
		$nodes_to_translate['sky-content-switcher'] = [
			'conditions'        => [ 'widgetType' => 'sky-content-switcher' ],
			'fields'            => [],
			'integration-class' => __NAMESPACE__ . '\\WPML_Content_Switcher',
		];

		// Dual Button
		$nodes_to_translate['sky-dual-button'] = [
			'conditions'        => [ 'widgetType' => 'sky-dual-button' ],
			'fields'            => [],
			'integration-class' => __NAMESPACE__ . '\\WPML_Dual_Button',
		];

		// Image Compare
		$nodes_to_translate['sky-image-compare'] = [
			'conditions'        => [ 'widgetType' => 'sky-image-compare' ],
			'fields'            => [],
			'integration-class' => __NAMESPACE__ . '\\WPML_Image_Compare',
		];

		// Info Box
		$nodes_to_translate['sky-info-box'] = [
			'conditions'        => [ 'widgetType' => 'sky-info-box' ],
			'fields'            => [],
			'integration-class' => __NAMESPACE__ . '\\WPML_Info_Box',
		];

		// List Group
		$nodes_to_translate['sky-list-group'] = [
			'conditions'        => [ 'widgetType' => 'sky-list-group' ],
			'fields'            => [],
			'integration-class' => __NAMESPACE__ . '\\WPML_List_Group',
		];

		// Logo Carousel
		$nodes_to_translate['sky-logo-carousel'] = [
			'conditions'        => [ 'widgetType' => 'sky-logo-carousel' ],
			'fields'            => [],
			'integration-class' => __NAMESPACE__ . '\\WPML_Logo_Carousel',
		];

		// Number
		$nodes_to_translate['sky-number'] = [
			'conditions'        => [ 'widgetType' => 'sky-number' ],
			'fields'            => [],
			'integration-class' => __NAMESPACE__ . '\\WPML_Number',
		];

		// PDF Viewer
		$nodes_to_translate['sky-pdf-viewer'] = [
			'conditions'        => [ 'widgetType' => 'sky-pdf-viewer' ],
			'fields'            => [],
			'integration-class' => __NAMESPACE__ . '\\WPML_PDF_Viewer',
		];

		// Review
		$nodes_to_translate['sky-review'] = [
			'conditions'        => [ 'widgetType' => 'sky-review' ],
			'fields'            => [],
			'integration-class' => __NAMESPACE__ . '\\WPML_Review',
		];

		// Social Icons
		$nodes_to_translate['sky-social-icons'] = [
			'conditions'        => [ 'widgetType' => 'sky-social-icons' ],
			'fields'            => [],
			'integration-class' => __NAMESPACE__ . '\\WPML_Social_Icons',
		];

		// Step Flow
		$nodes_to_translate['sky-step-flow'] = [
			'conditions'        => [ 'widgetType' => 'sky-step-flow' ],
			'fields'            => [],
			'integration-class' => __NAMESPACE__ . '\\WPML_Step_Flow',
		];

		// Team Member
		$nodes_to_translate['sky-team-member'] = [
			'conditions'        => [ 'widgetType' => 'sky-team-member' ],
			'fields'            => [],
			'integration-class' => __NAMESPACE__ . '\\WPML_Team_Member',
		];

		// Testimonial
		$nodes_to_translate['sky-testimonial'] = [
			'conditions'        => [ 'widgetType' => 'sky-testimonial' ],
			'fields'            => [],
			'integration-class' => __NAMESPACE__ . '\\WPML_Testimonial',
		];

		// Logo Grid (using logo carousel class)
		$nodes_to_translate['sky-logo-grid'] = [
			'conditions'        => [ 'widgetType' => 'sky-logo-grid' ],
			'fields'            => [],
			'integration-class' => __NAMESPACE__ . '\\WPML_Logo_Carousel',
		];

		// Generic Grid
		$nodes_to_translate['sky-generic-grid'] = [
			'conditions' => [ 'widgetType' => 'sky-generic-grid' ],
			'fields'     => [],
		];

		$nodes_to_translate['sky-post-list'] = [
			'conditions' => [ 'widgetType' => 'sky-post-list' ],
			'fields'     => [],
		];

		// Form-related widgets
		$form_widgets = [
			'sky-cf7',
			'sky-fluent-form',
			'sky-gravity-forms',
			'sky-wp-forms',
			'sky-ninja-forms',
			'sky-we-forms',
		];

		foreach ( $form_widgets as $widget ) {
			$nodes_to_translate[ $widget ] = [
				'conditions' => [ 'widgetType' => $widget ],
				'fields'     => [],
			];
		}

		// Carousel and slider widgets (using base carousel class)
		$carousel_widgets = [
			'sky-ultra-carousel',
			'sky-mate-carousel',
			'sky-sapling-carousel',
			'sky-luster-carousel',
			'sky-naive-carousel',
			'sky-fellow-slider',
			'sky-glory-slider',
			'sky-stellar-slider',
			'sky-panel-slider',
			'sky-momentum-slider',
			'sky-mate-slider',
		];

		foreach ( $carousel_widgets as $widget ) {
			$nodes_to_translate[ $widget ] = [
				'conditions' => [ 'widgetType' => $widget ],
				'fields'     => [],
			];
		}

		// Grid widgets (using base carousel class)
		$grid_widgets = [
			'sky-ultra-grid',
			'sky-sapling-grid',
			'sky-luster-grid',
		];

		foreach ( $grid_widgets as $widget ) {
			$nodes_to_translate[ $widget ] = [
				'conditions' => [ 'widgetType' => $widget ],
				'fields'     => [],
			];
		}

		// List widgets (using tidy list class)
		$list_widgets = [ 'sky-mate-list', 'sky-naive-list' ];

		foreach ( $list_widgets as $widget ) {
			$nodes_to_translate[ $widget ] = [
				'conditions' => [ 'widgetType' => $widget ],
				'fields'     => [],
			];
		}

		// Reading Progress
		$nodes_to_translate['sky-reading-progress'] = [
			'conditions' => [ 'widgetType' => 'sky-reading-progress' ],
			'fields'     => [
				'progress_text' => [
					'field'       => 'progress_text',
					'type'        => esc_html__( 'Reading Progress: Text', 'sky-elementor-addons' ),
					'editor_type' => 'LINE',
				],
			],
		];

		// Audio Player
		$nodes_to_translate['sky-audio-player'] = [
			'conditions' => [ 'widgetType' => 'sky-audio-player' ],
			'fields'     => [
				'audio_title'       => [
					'field'       => 'audio_title',
					'type'        => esc_html__( 'Audio Player: Title', 'sky-elementor-addons' ),
					'editor_type' => 'LINE',
				],
				'audio_description' => [
					'field'       => 'audio_description',
					'type'        => esc_html__( 'Audio Player: Description', 'sky-elementor-addons' ),
					'editor_type' => 'AREA',
				],
			],
		];

		// Add extension fields to all widgets that can have extensions
		foreach ( $nodes_to_translate as $widget_type => &$config ) {
			if ( ! isset( $config['fields'] ) || ! is_array( $config['fields'] ) ) {
				$config['fields'] = [];
			}
			$config['fields']['sky_addons_wrapper_link'] = [
				'field'       => 'url',
				'type'        => esc_html__( 'Wrapper Link', 'sky-elementor-addons' ),
				'editor_type' => 'LINK',
			];
		}
		unset( $config );

		return $nodes_to_translate;
	}

	/**
	 * Returns the instance.
	 *
	 * @since  3.1.0
	 * @return object
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}

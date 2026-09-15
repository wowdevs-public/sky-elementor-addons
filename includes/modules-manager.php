<?php

namespace Sky_Addons;

use Elementor\Element_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Managers {

	private $_modules = null;

	const WIDGETS_DB_KEY    = 'sky_addons_inactive_widgets';
	const EXTENSIONS_DB_KEY = 'sky_addons_inactive_extensions';
	const ADVANCED_DB_KEY   = 'sky_addons_advanced_settings';

	public static function get_inactive_widgets() {
		return get_option( self::WIDGETS_DB_KEY, [] );
	}
	public static function get_inactive_extensions() {
		return get_option( self::EXTENSIONS_DB_KEY, [] );
	}

	/**
	 * Inactive advanced-feature slugs.
	 *
	 * Advanced features live inside the `sky_addons_advanced_settings` option
	 * under the `inactive` sub-key (asset_manager mode is a sibling key in the
	 * same option). Absent = active by default.
	 *
	 * @return string[]
	 */
	public static function get_inactive_advanced_features() {
		$settings = get_option( self::ADVANCED_DB_KEY, [] );
		return (array) ( $settings['inactive'] ?? [] );
	}

	/**
	 * Whether an extension is active.
	 *
	 * Memoized — the inactive list is fetched once per request and reused
	 * for subsequent calls. Default behaviour: when the option is empty
	 * (fresh install, never saved) all extensions are considered ACTIVE.
	 *
	 * @param string $slug Extension slug (e.g. 'equal-height', 'simple-parallax').
	 * @return bool
	 */
	public static function is_extension_active( $slug ) {
		static $inactive = null;
		if ( null === $inactive ) {
			$inactive = (array) self::get_inactive_extensions();
		}
		return ! in_array( $slug, $inactive, true );
	}

	/**
	 * Whether an advanced feature is active.
	 *
	 * Memoized — the inactive list is fetched once per request. Default
	 * behaviour: when no slug is stored, the feature is considered ACTIVE.
	 *
	 * @param string $slug Advanced feature slug (e.g. 'dynamic-tags', 'templates-library').
	 * @return bool
	 */
	public static function is_advanced_feature_active( $slug ) {
		static $inactive = null;
		if ( null === $inactive ) {
			$inactive = (array) self::get_inactive_advanced_features();
		}
		return ! in_array( $slug, $inactive, true );
	}

	/**
	 * Whether a widget is active.
	 *
	 * Memoized — the inactive list is fetched once per request and reused
	 * for subsequent calls. Default behaviour: when the option is empty
	 * (fresh install, never saved) all widgets are considered ACTIVE.
	 *
	 * @param string $slug Widget slug (e.g. 'logo-carousel', 'logo-grid').
	 * @return bool
	 */
	public static function is_widget_active( $slug ) {
		static $inactive = null;
		if ( null === $inactive ) {
			$inactive = (array) self::get_inactive_widgets();
		}
		return ! in_array( $slug, $inactive, true );
	}

	private function is_module_active( $module_id ) {
		$module_data      = $this->get_module_data( $module_id );
		$inactive_widgets = self::get_inactive_widgets();

		if ( ! $inactive_widgets ) {
			return $module_data['default_activation'] ?? true;
		} elseif ( ! in_array( $module_id, $inactive_widgets ) ) {
			return true;
		} else {
			return false;
		}
	}

	private function has_module_style( $module_id ) {
		$module_data = $this->get_module_data( $module_id );
		return isset( $module_data['has_style'] ) ? (bool) $module_data['has_style'] : false;
	}

	private function has_module_script( $module_id ) {
		$module_data = $this->get_module_data( $module_id );
		return isset( $module_data['has_script'] ) ? (bool) $module_data['has_script'] : false;
	}

	private function get_module_data( $module_id ) {
		return isset( $this->_modules[ $module_id ] ) ? $this->_modules[ $module_id ] : false;
	}

	private function is_extension( $module_id ) {
		$extensions = [
			'animated-gradient-bg',
			'backdrop-filter',
			'button-effects',
			'custom-clip-path',
			'equal-height',
			'floating-effects',
			'gradient-text',
			'grid-canvas',
			'reveal-effects',
			'ripples-effect',
			'simple-parallax',
			'sticky',
			'tilt-effect',
			'wrapper-link',
		];
		return in_array( $module_id, $extensions );
	}

	public function __construct() {
		$modules   = [];
		$modules[] = 'advanced-accordion';
		$modules[] = 'advanced-skill-bars';
		$modules[] = 'advanced-slider';
		$modules[] = 'animated-heading';
		$modules[] = 'audio-player';
		$modules[] = 'card';
		$modules[] = 'changelog';
		$modules[] = 'content-switcher';
		$modules[] = 'dual-button';
		$modules[] = 'glory-slider';
		$modules[] = 'info-box';
		$modules[] = 'image-compare';
		$modules[] = 'list-group';
		$modules[] = 'logo-grid';
		$modules[] = 'logo-carousel';
		$modules[] = 'momentum-slider';
		$modules[] = 'panel-slider';
		$modules[] = 'pdf-viewer';
		$modules[] = 'portion-effect';
		$modules[] = 'reading-progress';
		$modules[] = 'review';
		$modules[] = 'review-carousel';
		$modules[] = 'slinky-menu';
		$modules[] = 'social-icons';
		$modules[] = 'stellar-slider';
		$modules[] = 'step-flow';
		$modules[] = 'table';
		$modules[] = 'table-of-contents';
		$modules[] = 'team-member';
		$modules[] = 'team-member-carousel';
		$modules[] = 'testimonial';
		$modules[] = 'testimonial-carousel';
		$modules[] = 'tidy-list';
		$modules[] = 'video-player';
		$modules[] = 'whatsapp-button';

		/**
		 * All Post Widgets
		 */

		$modules[] = 'fancy-testimonial';
		$modules[] = 'fellow-slider';
		$modules[] = 'generic-grid';
		$modules[] = 'generic-carousel';
		$modules[] = 'luster-grid';
		$modules[] = 'luster-carousel';
		$modules[] = 'mate-list';
		$modules[] = 'mate-slider';
		$modules[] = 'mate-carousel';
		$modules[] = 'naive-list';
		$modules[] = 'naive-carousel';
		$modules[] = 'post-list';
		$modules[] = 'sapling-grid';
		$modules[] = 'sapling-carousel';
		$modules[] = 'ultra-grid';
		$modules[] = 'ultra-carousel';

		/**
		 * All Post Modules of Theme Builder
		 */

		$modules[] = 'post-comments';

		/**
		 * 3rd Party Modules
		 */
		$modules[] = 'cf7';
		$modules[] = 'fluent-form';
		$modules[] = 'gravity-forms';
		$modules[] = 'ninja-forms';
		$modules[] = 'we-forms';
		$modules[] = 'wp-forms';

		/**
		 * All Extensions
		 */

		if ( ! in_array( 'animated-gradient-bg', self::get_inactive_extensions() ) ) {
			$modules[] = 'animated-gradient-bg';
		}
		if ( ! in_array( 'backdrop-filter', self::get_inactive_extensions() ) ) {
			$modules[] = 'backdrop-filter';
		}
		if ( ! in_array( 'button-effects', self::get_inactive_extensions() ) ) {
			$modules[] = 'button-effects';
		}
		if ( ! in_array( 'custom-clip-path', self::get_inactive_extensions() ) ) {
			$modules[] = 'custom-clip-path';
		}
		if ( ! in_array( 'equal-height', self::get_inactive_extensions() ) ) {
			$modules[] = 'equal-height';
		}
		if ( ! in_array( 'floating-effects', self::get_inactive_extensions() ) ) {
			$modules[] = 'floating-effects';
		}
		if ( ! in_array( 'gradient-text', self::get_inactive_extensions() ) ) {
			$modules[] = 'gradient-text';
		}
		if ( ! in_array( 'grid-canvas', self::get_inactive_extensions() ) ) {
			$modules[] = 'grid-canvas';
		}
		if ( ! in_array( 'reveal-effects', self::get_inactive_extensions() ) ) {
			$modules[] = 'reveal-effects';
		}
		if ( ! in_array( 'ripples-effect', self::get_inactive_extensions() ) ) {
			$modules[] = 'ripples-effect';
		}
		if ( ! in_array( 'simple-parallax', self::get_inactive_extensions() ) ) {
			$modules[] = 'simple-parallax';
		}
		if ( ! in_array( 'sticky', self::get_inactive_extensions() ) ) {
			$modules[] = 'sticky';
		}
		if ( ! in_array( 'tilt-effect', self::get_inactive_extensions() ) ) {
			$modules[] = 'tilt-effect';
		}
		if ( ! in_array( 'wrapper-link', self::get_inactive_extensions() ) ) {
			$modules[] = 'wrapper-link';
		}

		// Fetch all modules data
		foreach ( $modules as $module ) {
			$module_info = SKY_ADDONS_MODULES_PATH . $module . '/module.info.php';

			if ( ! file_exists( $module_info ) ) {
				continue;
			}

			$this->_modules[ $module ] = require $module_info;
		}

		$direction_suffix = is_rtl() ? '.rtl' : '';

		// Inside the Elementor editor/preview the Optimizer always enqueues the combined
		// bundle (sa-scripts/sa-styles), so per-widget handles must alias to it — otherwise
		// each widget's get_script_depends() pulls its individual sa-{id} file too, loading
		// it twice and throwing init errors. Force virtual handles in that context.
		$in_editor = false;
		if ( class_exists( '\Elementor\Plugin' ) ) {
			$elementor = \Elementor\Plugin::$instance;
			$in_editor = ( isset( $elementor->editor ) && $elementor->editor->is_edit_mode() )
				|| ( isset( $elementor->preview ) && $elementor->preview->is_preview_mode() );
		}

		// Use virtual handles (aliases to sa-styles/sa-scripts) whenever Asset Manager is ON
		// or we are in the editor/preview.
		// Optimizer::enqueue_bundle() decides whether to serve Tier 1 (uploads) or Tier 2 (plugin combined).
		$optimized = $in_editor
			|| ( function_exists( 'sky_addons_is_asset_optimization_enabled' )
				&& sky_addons_is_asset_optimization_enabled() );

		foreach ( $this->_modules as $module_id => $module_data ) {

			if ( ! $this->is_module_active( $module_id ) ) {
				continue;
			}

			$class_name = str_replace( '-', ' ', $module_id );
			$class_name = str_replace( ' ', '', ucwords( $class_name ) );
			$class_name = __NAMESPACE__ . '\\Modules\\' . $class_name . '\Module';

			$sub_dir = $this->is_extension( $module_id ) ? 'extensions/' : 'modules/';

			if ( $this->has_module_style( $module_id ) ) {
				if ( $optimized ) {
					wp_register_style( 'sa-' . $module_id, false, [], SKY_ADDONS_VERSION );
				} else {
					wp_register_style( 'sa-' . $module_id, SKY_ADDONS_ASSETS_URL . 'css/' . $sub_dir . 'sa-' . $module_id . $direction_suffix . '.css', [ 'sky-addons-base' ], SKY_ADDONS_VERSION );
				}
			}

			if ( $this->has_module_script( $module_id ) ) {
				if ( $optimized ) {
					wp_register_script( 'sa-' . $module_id, false, [], SKY_ADDONS_VERSION, true );
				} else {
					$script_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
					wp_register_script( 'sa-' . $module_id, SKY_ADDONS_ASSETS_URL . 'js/' . $sub_dir . 'sa-' . $module_id . $script_suffix . '.js', [ 'jquery', 'elementor-frontend', 'sky-addons-base' ], SKY_ADDONS_VERSION, true );
				}
			}

			$class_name::instance();
		}
	}
}

// Managers::init();

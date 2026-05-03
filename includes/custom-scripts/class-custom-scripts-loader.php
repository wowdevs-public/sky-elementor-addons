<?php
/**
 * Custom Scripts Loader Class
 * Handles conditional loading of custom scripts/styles on the frontend
 *
 * @package Sky_Addons\CustomScripts
 * @since 3.3.0
 */

namespace Sky_Addons\CustomScripts;

defined( 'ABSPATH' ) || exit;

class Custom_Scripts_Loader {
	private static $instance = null;

	private function __construct() {
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_custom_scripts' ], 999 );
	}

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __clone() {}

	public function __wakeup() {}

	/**
	 * Enqueue custom scripts and styles based on conditions
	 */
	public function enqueue_custom_scripts() {
		$scripts = $this->get_enabled_scripts();

		if ( empty( $scripts ) ) {
			return;
		}

		foreach ( $scripts as $script ) {
			if ( ! $this->should_load_script( $script ) ) {
				continue;
			}

			$this->load_script( $script );
		}
	}

	/**
	 * Get all enabled custom scripts
	 *
	 * @return array
	 */
	private function get_enabled_scripts() {
		$args = [
			'post_type'      => 'sky-custom-scripts',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'meta_query'     => [
				[
					'key'     => 'sky_script_status',
					'value'   => 'enabled',
					'compare' => '=',
				],
			],
		];

		$query = new \WP_Query( $args );
		return $query->posts;
	}

	/**
	 * Check if script should be loaded based on conditions
	 *
	 * @param \WP_Post $script
	 * @return bool
	 */
	private function should_load_script( $script ) {
		// Check date range first
		$start_date   = get_post_meta( $script->ID, 'sky_script_start_date', true );
		$end_date     = get_post_meta( $script->ID, 'sky_script_end_date', true );
		$current_time = current_time( 'timestamp' );

		// If start date is set and current time is before start date, don't load
		if ( ! empty( $start_date ) && $current_time < strtotime( $start_date ) ) {
			return false;
		}

		// If end date is set and current time is after end date, don't load
		if ( ! empty( $end_date ) && $current_time > strtotime( $end_date . ' 23:59:59' ) ) {
			return false;
		}

		$display_on                = get_post_meta( $script->ID, 'sky_script_display_on', true ) ?: [];
		$not_display_on            = get_post_meta( $script->ID, 'sky_script_not_display_on', true ) ?: [];
		$display_special_pages     = get_post_meta( $script->ID, 'sky_script_display_special_pages', true ) ?: [];
		$not_display_special_pages = get_post_meta( $script->ID, 'sky_script_not_display_special_pages', true ) ?: [];
		$display_custom_pages      = get_post_meta( $script->ID, 'sky_script_display_custom_pages', true ) ?: [];
		$not_display_custom_pages  = get_post_meta( $script->ID, 'sky_script_not_display_custom_pages', true ) ?: [];
		$display_roles             = get_post_meta( $script->ID, 'sky_script_display_roles', true ) ?: [];

		// Check exclusion rules first
		if ( $this->matches_conditions( $not_display_on, $not_display_special_pages, $not_display_custom_pages ) ) {
			return false;
		}

		// Check inclusion rules
		if ( empty( $display_on ) ) {
			return true; // No conditions set, load everywhere
		}

		if ( ! $this->matches_conditions( $display_on, $display_special_pages, $display_custom_pages ) ) {
			return false;
		}

		// Check user roles
		if ( ! empty( $display_roles ) && ! $this->matches_user_role( $display_roles ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Check if current page matches display conditions
	 *
	 * @param array $display_on
	 * @param array $special_pages
	 * @param array $custom_pages
	 * @return bool
	 */
	private function matches_conditions( $display_on, $special_pages, $custom_pages ) {
		if ( empty( $display_on ) ) {
			return false;
		}

		// Check entire site
		if ( in_array( 'entire_site', $display_on ) ) {
			return true;
		}

		// Check all pages
		if ( in_array( 'all_pages', $display_on ) && is_page() ) {
			return true;
		}

		// Check all posts
		if ( in_array( 'all_posts', $display_on ) && is_single() ) {
			return true;
		}

		// Check special pages
		if ( in_array( 'special_pages', $display_on ) && ! empty( $special_pages ) ) {
			if ( in_array( 'front_page', $special_pages ) && is_front_page() ) {
				return true;
			}
			if ( in_array( 'blog_page', $special_pages ) && is_home() ) {
				return true;
			}
			if ( in_array( 'archive_page', $special_pages ) && is_archive() ) {
				return true;
			}
			if ( in_array( '404_page', $special_pages ) && is_404() ) {
				return true;
			}
		}

		// Check custom pages
		if ( in_array( 'custom_pages', $display_on ) && ! empty( $custom_pages ) ) {
			$current_page_id = get_the_ID();
			if ( in_array( (string) $current_page_id, $custom_pages ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if current user matches role conditions
	 *
	 * @param array $roles
	 * @return bool
	 */
	private function matches_user_role( $roles ) {
		if ( empty( $roles ) ) {
			return true;
		}

		if ( in_array( 'all_users', $roles ) ) {
			return true;
		}

		$is_logged_in = is_user_logged_in();

		if ( in_array( 'logged_in', $roles ) && $is_logged_in ) {
			return true;
		}

		if ( in_array( 'logged_out', $roles ) && ! $is_logged_in ) {
			return true;
		}

		if ( $is_logged_in ) {
			$user = wp_get_current_user();
			foreach ( $roles as $role ) {
				if ( in_array( $role, $user->roles ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Load script or style
	 *
	 * @param \WP_Post $script
	 */
	private function load_script( $script ) {
		$script_type     = get_post_meta( $script->ID, 'sky_script_type', true );
		$script_content  = get_post_meta( $script->ID, 'sky_script_content', true );
		$script_position = get_post_meta( $script->ID, 'sky_script_position', true ) ?: 'footer';

		if ( empty( $script_content ) ) {
			return;
		}

		$handle = 'sky-custom-script-' . $script->ID;

		if ( $script_type === 'css' ) {
			$this->load_css( $handle, $script_content, $script_position );
		} else {
			$this->load_js( $handle, $script_content, $script_position );
		}
	}

	/**
	 * Load CSS
	 *
	 * @param string $handle
	 * @param string $content
	 * @param string $position
	 */
	private function load_css( $handle, $content, $position ) {
		if ( $position === 'header' ) {
			add_action( 'wp_head', function () use ( $content ) {
				echo '<style type="text/css">' . "\n" . $content . "\n" . '</style>' . "\n";
			}, 999 );
		} else {
			add_action( 'wp_footer', function () use ( $content ) {
				echo '<style type="text/css">' . "\n" . $content . "\n" . '</style>' . "\n";
			}, 999 );
		}
	}

	/**
	 * Load JavaScript
	 *
	 * @param string $handle
	 * @param string $content
	 * @param string $position
	 */
	private function load_js( $handle, $content, $position ) {
		if ( $position === 'header' ) {
			add_action( 'wp_head', function () use ( $content ) {
				echo '<script type="text/javascript">' . "\n" . $content . "\n" . '</script>' . "\n";
			}, 999 );
		} else {
			add_action( 'wp_footer', function () use ( $content ) {
				echo '<script type="text/javascript">' . "\n" . $content . "\n" . '</script>' . "\n";
			}, 999 );
		}
	}
}

Custom_Scripts_Loader::instance();

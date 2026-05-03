<?php
/**
 * Custom Scripts Data Class
 * Registers custom post type and meta fields for custom scripts/styles
 *
 * @package Sky_Addons\CustomScripts
 * @since 3.3.0
 */

namespace Sky_Addons\CustomScripts;

defined( 'ABSPATH' ) || exit;

class Custom_Scripts_Data {
	private static $instance = null;

	private function __construct() {
		add_action( 'init', [ $this, 'register_post_type' ] );
		add_filter( 'rest_pre_insert_sky-custom-scripts', [ $this, 'rest_pre_insert' ], 10, 2 );
		add_filter( 'rest_prepare_sky-custom-scripts', [ $this, 'rest_prepare' ], 10, 3 );
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
	 * Filter data before inserting via REST API
	 */
	public function rest_pre_insert( $prepared_post, $request ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new \WP_Error(
				'rest_forbidden',
				__( 'You do not have permission to manage custom scripts.', 'sky-elementor-addons' ),
				[ 'status' => 403 ]
			);
		}

		// Get the meta data from request
		$params = $request->get_params();

		if ( isset( $params['meta'] ) && is_array( $params['meta'] ) ) {
			// If we have a post ID (update), save meta immediately
			if ( isset( $params['id'] ) ) {
				$post_id = $params['id'];
				foreach ( $params['meta'] as $meta_key => $meta_value ) {
					update_post_meta( $post_id, $meta_key, $meta_value );
				}
			} else {
				// For new posts, we'll handle this in rest_insert action
				add_action( 'rest_insert_sky-custom-scripts', function ( $post, $request ) use ( $params ) {
					foreach ( $params['meta'] as $meta_key => $meta_value ) {
						update_post_meta( $post->ID, $meta_key, $meta_value );
					}
				}, 10, 2 );
			}
		}

		return $prepared_post;
	}

	/**
	 * Prepare response to include meta fields
	 */
	public function rest_prepare( $response, $post, $request ) {
		$data = $response->get_data();

		// Add meta fields to response
		$data['meta'] = [
			'sky_script_type'                      => get_post_meta( $post->ID, 'sky_script_type', true ) ?: 'js',
			'sky_script_content'                   => get_post_meta( $post->ID, 'sky_script_content', true ) ?: '',
			'sky_script_position'                  => get_post_meta( $post->ID, 'sky_script_position', true ) ?: 'footer',
			'sky_script_status'                    => get_post_meta( $post->ID, 'sky_script_status', true ) ?: 'enabled',
			'sky_script_start_date'                => get_post_meta( $post->ID, 'sky_script_start_date', true ) ?: '',
			'sky_script_end_date'                  => get_post_meta( $post->ID, 'sky_script_end_date', true ) ?: '',
			'sky_script_display_on'                => get_post_meta( $post->ID, 'sky_script_display_on', true ) ?: [],
			'sky_script_not_display_on'            => get_post_meta( $post->ID, 'sky_script_not_display_on', true ) ?: [],
			'sky_script_display_special_pages'     => get_post_meta( $post->ID, 'sky_script_display_special_pages', true ) ?: [],
			'sky_script_not_display_special_pages' => get_post_meta( $post->ID, 'sky_script_not_display_special_pages', true ) ?: [],
			'sky_script_display_custom_pages'      => get_post_meta( $post->ID, 'sky_script_display_custom_pages', true ) ?: [],
			'sky_script_not_display_custom_pages'  => get_post_meta( $post->ID, 'sky_script_not_display_custom_pages', true ) ?: [],
			'sky_script_display_roles'             => get_post_meta( $post->ID, 'sky_script_display_roles', true ) ?: [ 'all_users' ],
		];

		$response->set_data( $data );
		return $response;
	}

	/**
	 * Register custom post type for scripts/styles
	 */
	public function register_post_type() {
		$labels = [
			'name'               => __( 'Custom Scripts', 'sky-elementor-addons' ),
			'singular_name'      => __( 'Custom Script', 'sky-elementor-addons' ),
			'menu_name'          => __( 'Custom Scripts', 'sky-elementor-addons' ),
			'name_admin_bar'     => __( 'Custom Scripts', 'sky-elementor-addons' ),
			'add_new'            => __( 'Add New', 'sky-elementor-addons' ),
			'add_new_item'       => __( 'Add New Script', 'sky-elementor-addons' ),
			'new_item'           => __( 'New Script', 'sky-elementor-addons' ),
			'edit_item'          => __( 'Edit Script', 'sky-elementor-addons' ),
			'view_item'          => __( 'View Script', 'sky-elementor-addons' ),
			'all_items'          => __( 'All Scripts', 'sky-elementor-addons' ),
			'search_items'       => __( 'Search Scripts', 'sky-elementor-addons' ),
			'parent_item_colon'  => __( 'Parent Script:', 'sky-elementor-addons' ),
			'not_found'          => __( 'No Scripts found.', 'sky-elementor-addons' ),
			'not_found_in_trash' => __( 'No Scripts found in Trash.', 'sky-elementor-addons' ),
		];

		$args = [
			'labels'              => $labels,
			'description'         => __( 'Custom Scripts and Styles Manager', 'sky-elementor-addons' ),
			'taxonomies'          => [],
			'hierarchical'        => false,
			'public'              => false,
			'show_in_menu'        => false,
			'show_ui'             => true,
			'show_in_admin_bar'   => false,
			'menu_position'       => null,
			'menu_icon'           => null,
			'publicly_queryable'  => false,
			'supports'            => [ 'title' ],
			'exclude_from_search' => true,
			'has_archive'         => false,
			'query_var'           => false,
			'can_export'          => true,
			'rewrite'             => false,
			'show_in_nav_menus'   => false,
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
			'capabilities'        => [
				'create_posts'           => 'manage_options',
				'edit_posts'             => 'manage_options',
				'edit_others_posts'      => 'manage_options',
				'publish_posts'          => 'manage_options',
				'read_private_posts'     => 'manage_options',
				'delete_posts'           => 'manage_options',
				'delete_private_posts'   => 'manage_options',
				'delete_published_posts' => 'manage_options',
				'delete_others_posts'    => 'manage_options',
				'edit_private_posts'     => 'manage_options',
				'edit_published_posts'   => 'manage_options',
			],
			'show_in_rest'        => true,
		];

		register_post_type( 'sky-custom-scripts', $args );

		// Register meta fields
		$this->register_meta_fields();
	}

	/**
	 * Register all meta fields for custom scripts
	 */
	private function register_meta_fields() {
		$admin_only = function () {
			return current_user_can( 'manage_options' );
		};

		// Script type (css or js)
		register_post_meta( 'sky-custom-scripts', 'sky_script_type', [
			'show_in_rest'  => true,
			'single'        => true,
			'type'          => 'string',
			'default'       => 'js',
			'auth_callback' => $admin_only,
		]);

		// Script content
		register_post_meta( 'sky-custom-scripts', 'sky_script_content', [
			'show_in_rest'  => true,
			'single'        => true,
			'type'          => 'string',
			'default'       => '',
			'auth_callback' => $admin_only,
		]);

		// Script position (header or footer)
		register_post_meta( 'sky-custom-scripts', 'sky_script_position', [
			'show_in_rest'  => true,
			'single'        => true,
			'type'          => 'string',
			'default'       => 'footer',
			'auth_callback' => $admin_only,
		]);

		// Script status (enabled or disabled)
		register_post_meta( 'sky-custom-scripts', 'sky_script_status', [
			'show_in_rest'  => true,
			'single'        => true,
			'type'          => 'string',
			'default'       => 'enabled',
			'auth_callback' => $admin_only,
		]);

		// Start date for scheduling
		register_post_meta( 'sky-custom-scripts', 'sky_script_start_date', [
			'show_in_rest'  => true,
			'single'        => true,
			'type'          => 'string',
			'default'       => '',
			'auth_callback' => $admin_only,
		]);

		// End date for scheduling
		register_post_meta( 'sky-custom-scripts', 'sky_script_end_date', [
			'show_in_rest'  => true,
			'single'        => true,
			'type'          => 'string',
			'default'       => '',
			'auth_callback' => $admin_only,
		]);

		// Array meta fields for conditional loading
		$array_meta_fields = [
			'sky_script_display_on',
			'sky_script_not_display_on',
			'sky_script_display_special_pages',
			'sky_script_not_display_special_pages',
			'sky_script_display_custom_pages',
			'sky_script_not_display_custom_pages',
			'sky_script_display_roles',
		];

		foreach ( $array_meta_fields as $meta_field ) {
			register_post_meta( 'sky-custom-scripts', $meta_field, [
				'show_in_rest' => [
					'schema' => [
						'type'  => 'array',
						'items' => [
							'type' => 'string',
						],
					],
				],
				'single'        => true,
				'type'          => 'array',
				'default'       => [],
				'auth_callback' => $admin_only,
			]);
		}
	}
}

Custom_Scripts_Data::instance();

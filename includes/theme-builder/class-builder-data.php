<?php

namespace Sky_Addons\ThemeBuilder;

defined( 'ABSPATH' ) || exit;

class Builder_Data {
	private static $instance = null;

	private function __construct() {
		add_action( 'init', [ $this, 'registered_post_type' ] );
	}

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __clone() {}

	public function __wakeup() {}

	public function registered_post_type() {
		$labels = [
			'name'               => __( 'Template Items', 'sky-elementor-addons' ),
			'singular_name'      => __( 'Template Item', 'sky-elementor-addons' ),
			'menu_name'          => __( 'Themes Builder', 'sky-elementor-addons' ),
			'name_admin_bar'     => __( 'Themes Builder', 'sky-elementor-addons' ),
			'add_new'            => __( 'Add New', 'sky-elementor-addons' ),
			'add_new_item'       => __( 'Add New Template', 'sky-elementor-addons' ),
			'new_item'           => __( 'New Template', 'sky-elementor-addons' ),
			'edit_item'          => __( 'Edit Template', 'sky-elementor-addons' ),
			'view_item'          => __( 'View Template', 'sky-elementor-addons' ),
			'all_items'          => __( 'All Templates', 'sky-elementor-addons' ),
			'search_items'       => __( 'Search Templates', 'sky-elementor-addons' ),
			'parent_item_colon'  => __( 'Parent Template:', 'sky-elementor-addons' ),
			'not_found'          => __( 'No Template found.', 'sky-elementor-addons' ),
			'not_found_in_trash' => __( 'No Template found in Trash.', 'sky-elementor-addons' ),
		];

		$args = [
			'labels'              => $labels,
			'description'         => __( 'Description.', 'sky-elementor-addons' ),
			'taxonomies'          => [],
			'hierarchical'        => false,
			'public'              => true,
			'show_in_menu'        => false,
			'show_ui'             => true,
			'show_in_admin_bar'   => true,
			'menu_position'       => null,
			'menu_icon'           => null,
			'publicly_queryable'  => true,
			'supports'            => [ 'title', 'editor', 'elementor', 'custom-fields' ],
			'exclude_from_search' => true,
			'has_archive'         => false,
			'query_var'           => true,
			'can_export'          => true,
			'rewrite'             => false,
			'show_in_nav_menus'   => false,
			'capability_type'     => 'post',
			'show_in_rest'        => true,
		];

		register_post_type( 'wowdevs-hooks', $args );

		register_post_meta('wowdevs-hooks', 'wowdevs_theme_builder_type', [
			'show_in_rest' => true,
			'single'       => true,
			'type'         => 'string',
		]);

		register_post_meta('wowdevs-hooks', 'wowdevs_theme_builder_status', [
			'show_in_rest' => true,
			'single'       => true,
			'type'         => 'string',
		]);

		register_post_meta('wowdevs-hooks', 'wowdevs_theme_builder_hook', [
			'show_in_rest' => true,
			'single'       => true,
			'type'         => 'string',
		]);

		register_post_meta('wowdevs-hooks', 'wowdevs_theme_builder_hook_priority', [
			'show_in_rest' => true,
			'single'       => true,
			'type'         => 'number',
		]);

		$meta_fields = [
			'wowdevs_theme_builder',
			'wowdevs_theme_builder_display_on',
			'wowdevs_theme_builder_not_display_on',
			'wowdevs_theme_builder_display_special_pages',
			'wowdevs_theme_builder_not_display_special_pages',
			'wowdevs_theme_builder_display_custom_pages',
			'wowdevs_theme_builder_not_display_custom_pages',
			'wowdevs_theme_builder_display_roles',
		];

		foreach ( $meta_fields as $meta_field ) {
			register_post_meta('wowdevs-hooks', $meta_field, [
				'show_in_rest' => [
					'schema' => [
						'type'  => 'array',
						'items' => [
							'type'       => 'object',
							'properties' => [
								'value' => [
									'type' => 'string',
								],
							],
						],
					],
				],
				'single'       => true,
				'type'         => 'object',
			]);
		}
	}
}

Builder_Data::instance();

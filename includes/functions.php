<?php

defined( 'ABSPATH' ) || exit;

use Sky_Addons\Sky_Addons_Plugin;

if ( ! function_exists( 'sky_addons_core' ) ) {

	function sky_addons_core() {
		$obj = new \stdClass();
		$obj->templates_dir = Sky_Addons_Plugin::sky_addons_dir() . 'includes/views/';
		$obj->includes_dir = Sky_Addons_Plugin::sky_addons_dir() . 'includes/';
		$obj->controls_dir = Sky_Addons_Plugin::sky_addons_dir() . 'controls/';
		$obj->images = Sky_Addons_Plugin::sky_addons_url() . 'assets/images/';
		$obj->traits_dir = Sky_Addons_Plugin::sky_addons_dir() . 'traits/';
		return $obj;
	}
}

if ( ! function_exists( 'sky_addons_get_icon' ) ) {
	function sky_addons_get_icon() {
		return '<span class="sky-ctrl-section-icon-wrapper"><img src="' . sky_addons_core()->images . 'sky-logo-gradient.png" class="sky-ctrl-section-icon" alt="Sky Addons" title="Sky Addons"></span>';
	}
}

if ( ! function_exists( 'sky_addons_init_pro' ) ) {
	function sky_addons_init_pro() {
		return apply_filters( 'sky_addons_pro_init', false );
	}
}


if ( ! function_exists( 'sky_addons_control_indicator_pro' ) ) {
	function sky_addons_control_indicator_pro() {
		if ( sky_addons_init_pro() !== true ) {
			return '<span class="sa-control-indicator-badge sa-pro-badge">' . esc_html( 'Pro', 'sky-elementor-addons' ) . '<span>';
		}
	}
}

/**
 * @param $suffix
 */
function sky_addons_dashboard_link( $suffix = '' ) {
	return add_query_arg( [ 'page' => 'sky-elementor-addons' . $suffix ], admin_url( 'admin.php' ) );
}

function sky_addons_elementor() {
	return \Elementor\Plugin::instance();
}


if ( ! function_exists( 'sky_addons_title_tags' ) ) {
	function sky_addons_title_tags() {

		$title_tags = [
			'h1'   => 'H1',
			'h2'   => 'H2',
			'h3'   => 'H3',
			'h4'   => 'H4',
			'h5'   => 'H5',
			'h6'   => 'H6',
			'div'  => 'div',
			'span' => 'span',
			'p'    => 'p',
		];

		return $title_tags;
	}
}

/**
 * Check you are in Editor
 */

if ( ! function_exists( 'sky_addons_editor_mode' ) ) {
	function sky_addons_editor_mode() {
		if ( Sky_Addons_Plugin::elementor()->preview->is_preview_mode() || Sky_Addons_Plugin::elementor()->editor->is_edit_mode() ) {
			return true;
		}
		return false;
	}
}

/**
 * Disable unserializing of the class
 *
 * @since 1.0.0
 * @return void
 */
if ( ! function_exists( 'sky_addons_template_modify_link' ) ) {
	function sky_addons_template_modify_link( $template_id ) {
		if ( Sky_Addons_Plugin::elementor()->editor->is_edit_mode() ) {

			$final_url = add_query_arg( [ 'elementor' => '' ], get_permalink( $template_id ) );

			$output = sprintf( '<a class="sa-elementor-template-modify-link" href="%s" title="%s" target="_blank"><i class="eicon-edit"></i></a>', esc_url( $final_url ), esc_html__( 'Edit Template', 'sky-elementor-addons' ) );

			return $output;
		}
	}
}

/**
 * @return array of elementor template
 */
if ( ! function_exists( 'sky_addons_elementor_template_settings' ) ) {
	function sky_addons_elementor_template_settings() {

		$templates = Sky_Addons_Plugin::elementor()->templates_manager->get_source( 'local' )->get_items();
		$types = [];

		if ( empty( $templates ) ) {
			$template_settings = [ '0' => esc_html__( 'Template Not Found!', 'sky-elementor-addons' ) ];
		} else {
			$template_settings = [ '0' => esc_html__( 'Select Template', 'sky-elementor-addons' ) ];

			foreach ( $templates as $template ) {
				$template_settings[ $template['template_id'] ] = $template['title'] . ' (' . $template['type'] . ')';
				$types[ $template['template_id'] ] = $template['type'];
			}
		}

		return $template_settings;
	}
}

/**
 * @return array of anywhere templates
 */
if ( ! function_exists( 'sky_addons_anywhere_template_settings' ) ) {
	function sky_addons_anywhere_template_settings() {

		if ( post_type_exists( 'ae_global_templates' ) ) {
			$anywhere = get_posts( [
				'fields'         => 'ids', // Only get post IDs
				'posts_per_page' => -1,
				'post_type'      => 'ae_global_templates',
			] );

			$anywhere_settings = [ '0' => esc_html__( 'Select Template', 'sky-elementor-addons' ) ];

			foreach ( $anywhere as $key => $value ) {
				$anywhere_settings[ $value ] = get_the_title( $value );
			}
		} else {
			$anywhere_settings = [ '0' => esc_html__( 'AE Plugin Not Installed', 'sky-elementor-addons' ) ];
		}

		return $anywhere_settings;
	}
}
if ( ! function_exists( 'sky_addons_get_post_category' ) ) {
	function sky_addons_get_post_category( $post_type ) {
		switch ( $post_type ) {
			case 'campaign':
				$taxonomy = 'campaign_category';
				break;
			case 'give_forms':
				$taxonomy = 'give_forms_category';
				break;
			case 'lightbox_library':
				$taxonomy = 'ngg_tag';
				break;
			case 'product':
				$taxonomy = 'product_cat';
				break;
			case 'tribe_events':
				$taxonomy = 'tribe_events_cat';
				break;
			case 'knowledge-base':
				$taxonomy = 'knowledge-base-category';
				break;

			default:
				$taxonomy = 'category';
				break;
		}

		$categories = get_the_terms( get_the_ID(), $taxonomy );
		$_categories = [];
		if ( $categories ) {
			foreach ( $categories as $category ) {
				$link = '<a href="' . esc_url( get_category_link( $category->term_id ) ) . '">' . wp_kses_post( $category->name ) . '</a>';
				$_categories[ $category->slug ] = $link;
			}
		}
		return implode( ' ', $_categories );
	}
}

if ( ! function_exists( 'sky_addons_post_time_ago_kit' ) ) {
	function sky_addons_post_time_ago_kit( $from, $to = '' ) {
		$diff = human_time_diff( $from, $to );
		$replace = [
			' hour'    => 'h',
			' hours'   => 'h',
			' day'     => 'd',
			' days'    => 'd',
			' minute'  => 'm',
			' minutes' => 'm',
			' second'  => 's',
			' seconds' => 's',
		];

		return strtr( $diff, $replace );
	}
}

if ( ! function_exists( 'sky_addons_post_time_ago' ) ) {
	function sky_addons_post_time_ago( $format = '' ) {
		$display_ago = esc_html__( 'ago', 'sky-elementor-addons' );

		if ( 'short' === $format ) {
			$output = sky_addons_post_time_ago_kit( strtotime( get_the_date() ), current_time( 'timestamp' ) );
		} else {
			$output = human_time_diff( strtotime( get_the_date() ), current_time( 'timestamp' ) );
		}

		$output = $output . ' ' . $display_ago;

		return $output;
	}
}
if ( ! function_exists( 'sky_addons_post_custom_excerpt' ) ) {
	function sky_addons_post_custom_excerpt( $limit = 25, $strip_shortcode = false, $trail = '' ) {

		$output = get_the_content();

		if ( $limit ) {
			$output = wp_trim_words( $output, $limit, $trail );
		}

		if ( $strip_shortcode ) {
			$output = strip_shortcodes( $output );
		}

		return wpautop( $output );
	}
}

if ( ! function_exists( 'sky_addons_post_user_role' ) ) {
	function sky_addons_post_user_role( $id ) {

		$user = new WP_User( $id );

		return array_shift( $user->roles );
	}
}

if ( ! function_exists( 'sky_addons_post_pagination' ) ) {
	function sky_addons_post_pagination( $wp_query, $widget_id = '' ) {

		/**
		 * Check if Page only 1
		 * Pause the execution
		 */
		if ( $wp_query->max_num_pages <= 1 ) {
			return;
		}

		if ( is_front_page() ) {
			$paged = ( get_query_var( 'page' ) ) ? get_query_var( 'page' ) : 1;
		} else {
			$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
		}
		$max = intval( $wp_query->max_num_pages );

		/**
		 * Inject the Current Page
		 */
		if ( $paged >= 1 ) {
			$links[] = $paged;
		}

		/**
		 * Add Middle Pages
		 */
		if ( $paged >= 3 ) {
			$links[] = $paged - 1;
			$links[] = $paged - 2;
		}

		if ( ( $paged + 2 ) <= $max ) {
			$links[] = $paged + 2;
			$links[] = $paged + 1;
		}

		printf(
			'<ul class="%1$s" data-widget-id="%2$s" role="navigation">' . "\n",
			'sa-post-pagination sa-list-style-none sa-d-flex sa-my-5 sa-mx-0 sa-justify-content-center',
			wp_kses_post( $widget_id )
		);

		/**
		 * Previous Link
		 */
		if ( get_previous_posts_link() ) {
			$prev_arrow = '<i class="sa-post-icon-arrow-left" aria-hidden="true"></i>';
			printf(
				'<li class="sa-post-page-previous">%s</li>' . "\n",
				wp_kses_post( get_previous_posts_link( '<span data-sa-post-page-previous>' . $prev_arrow . '</span>' ) )
			);
		}

		if ( ! in_array( 1, $links ) ) {
			$class = ( 1 === $paged ) ? 'current' : 'sa';

			printf(
				'<li class="%s"><a class="sa-post-page-link sa-d-block" href="%s">%s</a></li>' . "\n",
				esc_attr( $class ),
				esc_url( get_pagenum_link( 1 ) ),
				'1'
			);

			if ( ! in_array( 2, $links ) ) {
				printf( '<li class="sa-post-page-dot-dot"><span>...</span></li>' );
			}
		}

		sort( $links );
		foreach ( (array) $links as $link ) {
			$class = ( $paged === $link ) ? 'sa-post-page-active' : 'sa';
			printf(
				'<li class="%s"><a class="sa-post-page-link sa-d-block" href="%s">%s</a></li>' . "\n",
				esc_attr( $class ),
				esc_url( get_pagenum_link( $link ) ),
				wp_kses_post( $link )
			);
		}

		if ( ! in_array( $max, $links ) ) {
			if ( ! in_array( $max - 1, $links ) ) {
				printf( '<li class="sa-post-page-dot-dot"><span>...</span></li>' . "\n" );
			}

			$class = ( $paged === $max ) ? 'sa-post-page-active' : 'sa';
			printf(
				'<li class="%s"><a class="sa-post-page-link sa-d-block" href="%s">%s</a></li>' . "\n",
				esc_attr( $class ),
				esc_url( get_pagenum_link( $max ) ),
				wp_kses_post( $max )
			);
		}

		/**
		 * Next Link
		 */
		if ( get_next_posts_link( null, $paged ) ) {
			$next_arrow = '<i class="sa-post-icon-arrow-right" aria-hidden="true"></i>';
			printf(
				'<li class="sa-post-page-next">%s</li>' . "\n",
				wp_kses_post( get_next_posts_link( '<span data-sa-post-page-next>' . $next_arrow . '</span>' ) )
			);
		}

		printf( '</ul>' . "\n" );
	}
}


/**
 * @return array
 */
if ( ! function_exists( 'sky_addons_wp_get_menu' ) ) {
	function sky_addons_wp_get_menu() {
		$menus = wp_get_nav_menus();
		$items = [ 0 => esc_html__( 'Select Menu', 'sky-elementor-addons' ) ];
		foreach ( $menus as $menu ) {
			$items[ $menu->slug ] = $menu->name;
		}
		return $items;
	}
}

/**
 * Display an Elementor template by its ID.
 *
 * @param int $template_id The ID of the Elementor template.
 */
if ( ! function_exists( 'sky_addons_display_el_tem_by_id' ) ) {
	function sky_addons_display_el_tem_by_id( int $template_id ) {
		$posts = get_posts( [
			'post_type'   => 'elementor_library',
			'post_status' => 'publish',
			'p'           => $template_id,
		] );

		if ( ! empty( $posts ) && $posts[0]->ID === $template_id ) {
      //phpcs:ignore
			echo Sky_Addons_Plugin::elementor()->frontend->get_builder_content_for_display( $template_id );
		} else {
			echo esc_html__( 'The post is not published or does not exist.', 'sky-elementor-addons' );
		}
	}
}


/**
 * Render Elementor Content
 *
 * @param $content_id
 *
 * Used in Themes Builder
 */
if ( ! function_exists( 'wowdevs_render_elementor_content' ) ) {
	function wowdevs_render_elementor_content( $content_id ) {

		$elementor_instance = \Elementor\Plugin::instance();
		$has_css            = false;

		/**
		 * CSS Print Method Internal and Exteral option support for Header and Footer Builder.
		 */
		if ( ( 'internal' === get_option( 'elementor_css_print_method' ) ) || \Elementor\Plugin::$instance->preview->is_preview_mode() ) {
			$has_css = true;
		}

		return $elementor_instance->frontend->get_builder_content_for_display( $content_id, $has_css );
	}
}


/**
 * Check if contact form 7 is activated
 *
 * @return bool
 */
if ( ! function_exists( 'sky_addons_is_cf7_activated' ) ) {
	function sky_addons_is_cf7_activated() {
		return class_exists( '\WPCF7' );
	}
}

/**
 * Check if Ninja Form is activated
 *
 * @return bool
 */
if ( ! function_exists( 'sky_addons_is_ninjaforms_activated' ) ) {
	function sky_addons_is_ninjaforms_activated() {
		return class_exists( '\Ninja_Forms' );
	}
}

/**
 * Check if We Form is activated
 *
 * @return bool
 */
if ( ! function_exists( 'sky_addons_is_weforms_activated' ) ) {
	function sky_addons_is_weforms_activated() {
		return class_exists( '\WeForms' );
	}
}

/**
 * Check if WPForms is activated
 *
 * @return bool
 */
if ( ! function_exists( 'sky_addons_is_wpforms_activated' ) ) {
	function sky_addons_is_wpforms_activated() {
		return class_exists( '\WPForms\WPForms' );
	}
}

/**
 * Check if Gravity Forms is activated
 *
 * @return bool
 */
if ( ! function_exists( 'sky_addons_is_gravityforms_activated' ) ) {
	function sky_addons_is_gravityforms_activated() {
		return class_exists( '\GFForms' );
	}
}

/*
 * Check if Fluent Form is activated
 *
 * @return bool
 */
if ( ! function_exists( 'sky_addons_is_fluent_form_activated' ) ) {
	function sky_addons_is_fluent_form_activated() {
		return defined( 'FLUENTFORM' );
	}
}


/**
 * Get a list of all CF7 forms
 *
 * @return array
 */
if ( ! function_exists( 'sky_addons_get_cf7_forms' ) ) {
	function sky_addons_get_cf7_forms() {
		$forms = [];

		if ( sky_addons_is_cf7_activated() ) {
			$_forms = get_posts( [
				'post_type'      => 'wpcf7_contact_form',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'ASC',
			] );

			if ( ! empty( $_forms ) ) {
				$forms = wp_list_pluck( $_forms, 'post_title', 'ID' );
			}
		}

		return $forms;
	}
}

/**
 * Get a list of all Ninja Form
 *
 * @return array
 */
if ( ! function_exists( 'sky_addons_get_ninjaform' ) ) {
	function sky_addons_get_ninjaform() {
		$forms = [];

		if ( sky_addons_is_ninjaforms_activated() ) {
			$_forms = \Ninja_Forms()->form()->get_forms();

			if ( ! empty( $_forms ) && ! is_wp_error( $_forms ) ) {
				foreach ( $_forms as $form ) {
					$forms[ $form->get_id() ] = $form->get_setting( 'title' );
				}
			}
		}

		return $forms;
	}
}

/**
 * Get a list of all WeForm
 *
 * @return array
 */
if ( ! function_exists( 'sky_addons_get_we_forms' ) ) {
	function sky_addons_get_we_forms() {
		$forms = [];

		if ( sky_addons_is_weforms_activated() ) {
			$_forms = get_posts( [
				'post_type'      => 'wpuf_contact_form',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'ASC',
			] );

			if ( ! empty( $_forms ) ) {
				$forms = wp_list_pluck( $_forms, 'post_title', 'ID' );
			}
		}

		return $forms;
	}
}

/**
 * Get a list of all WPForms
 *
 * @return array
 */
if ( ! function_exists( 'sky_addons_get_wpforms' ) ) {
	function sky_addons_get_wpforms() {
		$forms = [];

		if ( sky_addons_is_wpforms_activated() ) {
			$_forms = get_posts( [
				'post_type'      => 'wpforms',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'ASC',
			] );

			if ( ! empty( $_forms ) ) {
					$forms = wp_list_pluck( $_forms, 'post_title', 'ID' );
			}
		}

		return $forms;
	}
}


/**
 * Get a list of all GravityForms
 *
 * @return array
 */
if ( ! function_exists( 'sky_addons_get_gravity_forms' ) ) {
	function sky_addons_get_gravity_forms() {
		$forms = [];

		if ( sky_addons_is_gravityforms_activated() ) {
			$gravity_forms = \RGFormsModel::get_forms( null, 'title' );

			if ( ! empty( $gravity_forms ) && ! is_wp_error( $gravity_forms ) ) {
				foreach ( $gravity_forms as $gravity_form ) {
					$forms[ $gravity_form->id ] = $gravity_form->title;
				}
			}
		}

		return $forms;
	}
}

/*
 * Get a list of all Fluent Forms
 *
 * @return array
 */
if ( ! function_exists( 'sky_addons_fluent_forms' ) ) {
	function sky_addons_fluent_forms() {
		$forms = [];

		if ( sky_addons_is_fluent_form_activated() ) {
			global $wpdb;

			$table = $wpdb->prefix . 'fluentform_forms';
			$query = "SELECT * FROM {$table}";
			$fluent_forms = $wpdb->get_results( $query );

			if ( $fluent_forms ) {
				foreach ( $fluent_forms as $form ) {
					$forms[ $form->id ] = $form->title;
				}
			}
		}

		return $forms;
	}
}


/**
 * @return mixed
 */
if ( ! function_exists( 'sky_addons_get_current_user_display_name' ) ) {
	function sky_addons_get_current_user_display_name() {
		$user = wp_get_current_user();
		$name = 'user';
		if ( $user->exists() && $user->display_name ) {
			$name = $user->display_name;
		}
		return $name;
	}
}

/**
 * Call a shortcode function by tag name.
 *
 * @since  1.0.0
 *
 * @param string $tag     The shortcode whose function to call.
 * @param array  $atts    The attributes to pass to the shortcode function. Optional.
 * @param array  $content The shortcode's content. Default is null (none).
 *
 * @return string|bool False on failure, the result of the shortcode on success.
 */

if ( ! function_exists( 'sky_addons_do_shortcode' ) ) {
	function sky_addons_do_shortcode( $tag, array $atts = [], $content = null ) {
		global $shortcode_tags;
		if ( ! isset( $shortcode_tags[ $tag ] ) ) {
			return false;
		}
		return call_user_func( $shortcode_tags[ $tag ], $atts, $content, $tag );
	}
}

/**
 * Get plugin missing notice
 *
 * @param string $plugin
 * @return void
 */
if ( ! function_exists( 'sky_addons_show_plugin_missing_alert' ) ) {
	function sky_addons_show_plugin_missing_alert( $plugin ) {
		if ( current_user_can( 'activate_plugins' ) && $plugin ) {
			printf(
				'<div %s>%s</div>',
				'style="margin: 1rem;padding: 1rem 1.25rem;border-left: 5px solid #f5c848;color: #856404;background-color: #fff3cd;"',
				$plugin . __( ' is missing! Please install and activate ', 'sky-elementor-addons' ) . $plugin . '.'
			);
		}
	}
}

/**
 * Get alert notice
 *
 * @param string $content
 * @return void
 */

if ( ! function_exists( 'sky_addons_alert_notice' ) ) {
	function sky_addons_alert_notice( $content ) {
      // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			printf(
				'<div %s>%s</div>',
				'style="margin: 1rem;padding: 1rem 1.25rem;border-left: 5px solid #f5c848;color: #856404;background-color: #fff3cd;"',
				esc_html( $content )
			);
	}
}

/**
 * Sanitize html class string
 *
 * @param $class
 * @return string
 */
function sky_addons_sanitize_html_class_param( $class ) {
	$classes   = ! empty( $class ) ? explode( ' ', $class ) : [];
	$sanitized = [];
	if ( ! empty( $classes ) ) {
		$sanitized = array_map(function ( $cls ) {
			return sanitize_html_class( $cls );
		}, $classes);
	}
	return implode( ' ', $sanitized );
}

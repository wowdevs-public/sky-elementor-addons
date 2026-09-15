<?php

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'sky_addons_pagination_link_attributes' ) ) {
	/**
	 * Build the attribute string for a prev/next pagination anchor.
	 *
	 * The anchors are assembled by hand (see sky_addons_post_pagination()), so
	 * core never fires its own attribute filters for them. Firing them here
	 * keeps third-party code that hooks `previous_posts_link_attributes` /
	 * `next_posts_link_attributes` working, and is where `.sa-post-page-link`
	 * gets added — every Pagination style control (padding, typography, radius,
	 * border, colour, hover) targets that class and nothing else, so without it
	 * the arrows drift away from the number pills the moment a user touches a
	 * control.
	 *
	 * @param string $filter `previous_posts_link_attributes` | `next_posts_link_attributes`.
	 * @return string Attribute string, ready to interpolate into the `<a>`.
	 */
	function sky_addons_pagination_link_attributes( $filter ) {
		$attributes = apply_filters( $filter, '' );

		// A filter is free to return anything. Anything but a string is not an
		// attribute list; casting an array here would emit a warning and drop the
		// token `Array` into the tag.
		if ( ! is_string( $attributes ) ) {
			$attributes = '';
		}

		/*
		 * Walk the string one attribute at a time and rebuild it, rather than
		 * pattern-matching `class=` in place.
		 *
		 * Two reasons. A quoted value is consumed whole by the pass below, so a
		 * `class=` sitting inside some *other* attribute's value — a title, a
		 * data-, anything — can never be mistaken for the class attribute; a
		 * search-and-replace would write the plugin's classes into that value and
		 * leave the real class untouched, silently costing the arrows every
		 * Pagination style control. And HTML5's unquoted form (`class=foo`) is
		 * matched here too, where a quote-anchored pattern would miss it, append a
		 * second `class` attribute, and lose one of the two to the browser.
		 *
		 * `sa-d-block` is carried for parity with the number pills, which have it
		 * in their markup. Both are inert: `.sa-post-pagination .sa-post-page-link`
		 * out-specifies `body .sa-d-block`, so the pill rule sets the display in
		 * either case.
		 */
		$classes = [ 'sa-post-page-link', 'sa-d-block' ];
		$parsed  = [];

		preg_match_all(
			'/([^\s"\'=<>\/]+)(?:\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s"\'=<>`]*)))?/',
			$attributes,
			$matches,
			PREG_SET_ORDER
		);

		foreach ( $matches as $match ) {
			$name  = strtolower( $match[1] );
			$value = '';

			// Exactly one of the three value groups can be set — double-quoted,
			// single-quoted, unquoted — and trailing groups are absent entirely
			// when the attribute had no value at all.
			for ( $i = 2; $i <= 4; $i++ ) {
				if ( isset( $match[ $i ] ) && '' !== $match[ $i ] ) {
					$value = $match[ $i ];
					break;
				}
			}

			// The link is echoed without wp_kses(), because kses' post allowlist
			// has no `svg`/`path` and would strip the chevron out of it. kses was
			// also the only thing standing between a third-party filter and an
			// event handler on the tag: esc_attr() escapes an attribute's VALUE
			// and has nothing to say about its NAME, so `onclick` would survive
			// it intact. Drop the whole `on*` family here instead — narrower than
			// kses, and applied where the untrusted string actually enters.
			if ( 0 === strpos( $name, 'on' ) ) {
				continue;
			}

			if ( 'class' === $name ) {
				$classes[] = $value;
				continue;
			}

			$parsed[ $name ] = $value;
		}

		$parsed['class'] = trim( implode( ' ', $classes ) );

		$output = '';

		foreach ( $parsed as $name => $value ) {
			$output .= sprintf( ' %s="%s"', esc_attr( $name ), esc_attr( $value ) );
		}

		return trim( $output );
	}
}

if ( ! function_exists( 'sky_addons_core' ) ) {

	function sky_addons_core() {
		$obj                = new \stdClass();
		$obj->templates_dir = \Sky_Addons\Sky_Addons_Plugin::sky_addons_dir() . 'includes/views/';
		$obj->includes_dir  = \Sky_Addons\Sky_Addons_Plugin::sky_addons_dir() . 'includes/';
		$obj->controls_dir  = \Sky_Addons\Sky_Addons_Plugin::sky_addons_dir() . 'controls/';
		$obj->images        = \Sky_Addons\Sky_Addons_Plugin::sky_addons_url() . 'assets/images/';
		$obj->traits_dir    = \Sky_Addons\Sky_Addons_Plugin::sky_addons_dir() . 'traits/';
		return $obj;
	}
}

if ( ! function_exists( 'sky_addons_get_icon' ) ) {
	function sky_addons_get_icon() {
		return '<span class="sky-ctrl-section-icon-wrapper"><img src="' . SKY_ADDONS_ASSETS_URL . 'images/sky-logo-gradient.png" class="sky-ctrl-section-icon" alt="Sky Addons" title="Sky Addons"></span>';
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

if ( ! function_exists( 'sky_addons_label_badge' ) ) {
	/**
	 * Return a coloured badge span for appending to an Elementor control label.
	 * Automatically disappears once the plugin reaches $until_version.
	 *
	 * Usage: 'label' => esc_html__( 'My Control', 'sky-elementor-addons' ) . sky_addons_label_badge( 'new', '4.5.0' )
	 *
	 * @param string      $type          Badge type: 'new' | 'updated' | 'fixed' | 'beta'.
	 * @param string|null $until_version Plugin version at which the badge auto-expires (e.g. '3.5.0').
	 * @return string Badge span HTML, or empty string when expired or type is unknown.
	 */
	function sky_addons_label_badge( $type = 'new', $until_version = null ) {
		if ( $until_version && defined( 'SKY_ADDONS_VERSION' )
			&& version_compare( SKY_ADDONS_VERSION, $until_version, '>=' ) ) {
			return '';
		}

		$types = [
			'new'     => 'New',
			'updated' => 'Updated',
			'fixed'   => 'Fixed',
			'beta'    => 'Beta',
		];

		if ( ! isset( $types[ $type ] ) ) {
			return '';
		}

		return ' <span class="sa-control-indicator-badge sa-badge--' . esc_attr( $type ) . '">' . esc_html( $types[ $type ] ) . '</span>';
	}
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
		if ( \Sky_Addons\Sky_Addons_Plugin::elementor()->preview->is_preview_mode() || \Sky_Addons\Sky_Addons_Plugin::elementor()->editor->is_edit_mode() ) {
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
		if ( \Sky_Addons\Sky_Addons_Plugin::elementor()->editor->is_edit_mode() ) {

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

		$templates = \Sky_Addons\Sky_Addons_Plugin::elementor()->templates_manager->get_source( 'local' )->get_items();
		$types     = [];

		if ( empty( $templates ) ) {
			$template_settings = [ '0' => esc_html__( 'Template Not Found!', 'sky-elementor-addons' ) ];
		} else {
			$template_settings = [ '0' => esc_html__( 'Select Template', 'sky-elementor-addons' ) ];

			foreach ( $templates as $template ) {
				$template_settings[ $template['template_id'] ] = $template['title'] . ' (' . $template['type'] . ')';
				$types[ $template['template_id'] ]             = $template['type'];
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

		$categories  = get_the_terms( get_the_ID(), $taxonomy );
		$_categories = [];
		if ( $categories ) {
			foreach ( $categories as $category ) {
				$link                           = '<a href="' . esc_url( get_category_link( $category->term_id ) ) . '">' . wp_kses_post( $category->name ) . '</a>';
				$_categories[ $category->slug ] = $link;
			}
		}
		return implode( ' ', $_categories );
	}
}

if ( ! function_exists( 'sky_addons_post_time_ago_kit' ) ) {
	function sky_addons_post_time_ago_kit( $from, $to = '' ) {
		$diff    = human_time_diff( $from, $to );
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

		/**
		 * Current page number.
		 *
		 * Read the same way the widget builds its query — max() of both vars —
		 * rather than branching on is_front_page(). WordPress paginates a static
		 * front page with `page` but a "latest posts" front page with `paged`,
		 * so the old branch returned 1 on every page of a blog-index archive and
		 * the active state never moved off page 1.
		 *
		 * Cast to int: these are compared with === below, and a query var
		 * arriving as a string would never match.
		 */
		$paged = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
		$max   = intval( $wp_query->max_num_pages );

		$links = [];

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
		 *
		 * Built here instead of via get_previous_posts_link(). That function and
		 * get_previous_posts_page_link() both read the *global* `$paged`, and
		 * WordPress leaves that at 1 on a static front page because it paginates
		 * those with `page` rather than `paged` — so the back arrow disappeared on
		 * every page of a paginated front page, and the forward arrow linked to
		 * page 2 from page 2. No parameter overrides it; the href is resolved from
		 * the global a second time inside next_posts().
		 *
		 * The number pills below already avoid this by pairing the local `$paged`
		 * with get_pagenum_link(). The arrows now do the same, so the two halves of
		 * the block can no longer disagree about which page this is.
		 *
		 * `! is_single()` is kept from core, and it is not redundant: `$max` is read
		 * off the *widget's* query, so a post grid dropped into a Theme Builder
		 * single template has more than one page while is_single() is true. A page
		 * link there is a dead end — redirect_canonical() strips `/page/N/` from a
		 * single post's URL and only puts it back when ! is_single(), so the arrow
		 * would bounce the reader straight back to page 1.
		 */
		if ( ! is_single() && $paged > 1 ) {
			// `sa-post-icon-arrow-left` was never defined — no glyph, no stylesheet,
			// nothing behind the class — so this link rendered as an empty box.
			//
			// The chevron is picked by reading direction, not by the words
			// previous/next: grunt-rtlcss rewrites CSS properties and cannot flip an
			// SVG path, so a hardcoded left chevron points backwards on an RTL site.
			//
			// Path data is lifted verbatim from Elementor's eicons (GPLv3), which is
			// where the shared `0 0 1000 1000` viewBox comes from too. Do not redraw
			// it by hand — eyeballed geometry reads visibly wrong next to the real
			// eicons Elementor renders elsewhere on the same page.
			$prev_arrow = is_rtl()
				? '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000" aria-hidden="true"><path d="M696 533C708 521 713 504 713 487 713 471 708 454 696 446L400 146C388 133 375 125 354 125 338 125 325 129 313 142 300 154 292 171 292 187 292 204 296 221 308 233L563 492 304 771C292 783 288 800 288 817 288 833 296 850 308 863 321 871 338 875 354 875 371 875 388 867 400 854L696 533Z"></path></svg>'
				: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000" aria-hidden="true"><path d="M646 125C629 125 613 133 604 142L308 442C296 454 292 471 292 487 292 504 296 521 308 533L604 854C617 867 629 875 646 875 663 875 679 871 692 858 704 846 713 829 713 812 713 796 708 779 692 767L438 487 692 225C700 217 708 204 708 187 708 171 704 154 692 142 675 129 663 125 646 125Z"></path></svg>';

			// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- href is esc_url'd, attributes are esc_attr'd in the helper, the arrow is a constant.
			printf(
				'<li class="sa-post-page-previous"><a href="%1$s" %2$s><span class="sa-icon-wrap" data-sa-post-page-previous>%3$s</span></a></li>' . "\n",
				esc_url( get_pagenum_link( $paged - 1 ) ),
				sky_addons_pagination_link_attributes( 'previous_posts_link_attributes' ),
				$prev_arrow
			);
			// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		if ( ! in_array( 1, $links, true ) ) {
			// Same active class as every other number — this branch used to emit
			// 'current', which no stylesheet targets.
			$class = ( 1 === $paged ) ? 'sa-post-page-active' : 'sa';

			printf(
				'<li class="%s"><a class="sa-post-page-link sa-d-block" href="%s">%s</a></li>' . "\n",
				esc_attr( $class ),
				esc_url( get_pagenum_link( 1 ) ),
				'1'
			);

			if ( ! in_array( 2, $links, true ) ) {
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

		if ( ! in_array( $max, $links, true ) ) {
			if ( ! in_array( $max - 1, $links, true ) ) {
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
		// See the previous-link comment — same global-`$paged` problem, and it
		// showed up here as a third symptom on top of the two there: with no page
		// count to compare against, the arrow also rendered on the last page and
		// pointed past the end of the archive. `$max` is the count read off the
		// query above.
		if ( ! is_single() && $paged < $max ) {
			// See the previous-link comment — `sa-post-icon-arrow-right` was equally
			// undefined, and the chevron follows reading direction the same way.
			$next_arrow = is_rtl()
				? '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000" aria-hidden="true"><path d="M646 125C629 125 613 133 604 142L308 442C296 454 292 471 292 487 292 504 296 521 308 533L604 854C617 867 629 875 646 875 663 875 679 871 692 858 704 846 713 829 713 812 713 796 708 779 692 767L438 487 692 225C700 217 708 204 708 187 708 171 704 154 692 142 675 129 663 125 646 125Z"></path></svg>'
				: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000" aria-hidden="true"><path d="M696 533C708 521 713 504 713 487 713 471 708 454 696 446L400 146C388 133 375 125 354 125 338 125 325 129 313 142 300 154 292 171 292 187 292 204 296 221 308 233L563 492 304 771C292 783 288 800 288 817 288 833 296 850 308 863 321 871 338 875 354 875 371 875 388 867 400 854L696 533Z"></path></svg>';

			// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- href is esc_url'd, attributes are esc_attr'd in the helper, the arrow is a constant.
			printf(
				'<li class="sa-post-page-next"><a href="%1$s" %2$s><span class="sa-icon-wrap" data-sa-post-page-next>%3$s</span></a></li>' . "\n",
				esc_url( get_pagenum_link( $paged + 1 ) ),
				sky_addons_pagination_link_attributes( 'next_posts_link_attributes' ),
				$next_arrow
			);
			// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
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
			echo \Sky_Addons\Sky_Addons_Plugin::elementor()->frontend->get_builder_content_for_display( $template_id );
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

			$table        = $wpdb->prefix . 'fluentform_forms';
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Fluent Forms' own table, no user input; table names can't be placeholders before WP 6.2.
			$fluent_forms = $wpdb->get_results( "SELECT id, title FROM {$table}" );

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
				esc_html( $plugin ) . esc_html__( ' is missing! Please install and activate ', 'sky-elementor-addons' ) . esc_html( $plugin ) . '.'
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

/**
 * Swiper breakpoint keys, aligned to Elementor's CSS bands.
 *
 * Swiper `breakpoints` keys are MIN-width (`breakpointsBase` defaults to `window`, resolved
 * via `matchMedia( '(min-width: Npx)' )`). Elementor breakpoint values are MAX-width and are
 * emitted as `@media(max-width: Npx)`, so the two systems have to be translated.
 *
 * The translation is delegated to Elementor's Breakpoints Manager (3.2+), which reads the
 * active kit. Do NOT reimplement it as `mobile + 1` / `tablet + 1` — that hard-codes a
 * three-device world and breaks as soon as an additional breakpoint is enabled:
 *
 * - `get_device_min_breakpoint( 'tablet' )` is the first px of the Tablet band. With Mobile
 *   Landscape (`mobile_extra`, 880) enabled it is 881, not 768.
 * - `get_desktop_min_point()` is the first px of the Desktop band. With Laptop (1366)
 *   enabled it is 1367, not 1025; it also skips Widescreen, which is a min-width band.
 *
 * Under Elementor's default breakpoints (mobile 767, tablet 1024) this returns 768 / 1025 —
 * the same values the old arithmetic produced, so the common case is unchanged.
 *
 * Scope note: these two keys describe a THREE-band Swiper config, because the widgets that
 * consume them only read `columns` / `columns_tablet` / `columns_mobile` — there is no
 * `columns_laptop` control to map an extra device onto. The values above are chosen so the
 * three Swiper bands never contradict the CSS bands; giving every enabled Elementor device
 * its own Swiper band would require new column controls and is a separate change.
 *
 * The legacy `elementor_viewport_md` / `_lg` options are deliberately NOT consulted: they are
 * pre-3.0 leftovers, nothing has written them since Elementor 3.0 (`core/upgrade/upgrades.php`
 * only reads them once, into the kit), so they would silently serve values frozen in 2020.
 *
 * WHAT THIS REPLACED, and the behaviour change it caused
 * ------------------------------------------------------
 * Kept here on purpose: if a slider ever reports "wrong column count at one specific width",
 * this is the history that explains it.
 *
 * Before 4.0.0 every call site inlined the same block:
 *
 *     $elementor_vp_lg = get_option( 'elementor_viewport_lg' );
 *     $elementor_vp_md = get_option( 'elementor_viewport_md' );
 *     $viewport_lg     = ! empty( $elementor_vp_lg ) ? $elementor_vp_lg - 1 : 1023;
 *     $viewport_md     = ! empty( $elementor_vp_md ) ? $elementor_vp_md - 1 : 767;
 *
 * Those two numbers were then used as Swiper `breakpoints` KEYS. That is the bug: the options
 * hold Elementor MAX-widths, `- 1` turns them into the last px of the previous band, but a
 * Swiper key is a MIN-width. So the old keys 767 / 1023 opened each band one to two px early
 * and disagreed with the `@media` rules Elementor had already written for the same page.
 *
 * Only three widths behave differently now (Elementor default kit). Everything else is
 * byte-identical, so a regression report that is NOT at one of these widths is not this change:
 *
 *     767px         was tablet   -> now mobile    (Elementor CSS: mobile)
 *     1023, 1024px  was desktop  -> now tablet    (Elementor CSS: tablet)
 *
 * 1024px is iPad landscape, so this is the one users notice: a slider set to 4 desktop / 2
 * tablet columns now shows 2 there. That is correct — it finally matches the Tablet tab the
 * user filled in — but it is a visible change on sites built before 4.0.0.
 *
 * Two Pro widgets (showcase-flow, showcase-wall) already used 768 / 1025 and did not change.
 *
 * @since 4.0.0
 *
 * @return array {
 *     @type int $md Min-width where the tablet band starts.
 *     @type int $lg Min-width where the desktop band starts.
 * }
 */
if ( ! function_exists( 'sky_addons_get_swiper_breakpoints' ) ) {
	function sky_addons_get_swiper_breakpoints() {
		$md = 0;
		$lg = 0;

		if ( class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->breakpoints ) ) {
			$breakpoints = \Elementor\Plugin::$instance->breakpoints;

			// Both are public API since Elementor 3.2, but stay defensive.
			if ( method_exists( $breakpoints, 'get_device_min_breakpoint' ) ) {
				$md = (int) $breakpoints->get_device_min_breakpoint( 'tablet' );
			}

			if ( method_exists( $breakpoints, 'get_desktop_min_point' ) ) {
				$lg = (int) $breakpoints->get_desktop_min_point();
			}
		}

		if ( $md < 1 ) {
			$md = 768;
		}

		if ( $lg < 1 ) {
			$lg = 1025;
		}

		// A kit imported or edited outside the editor can carry tablet <= mobile, which would
		// collapse both Swiper keys onto the same value and silently drop one band.
		if ( $lg <= $md ) {
			$lg = $md + 1;
		}

		return [
			'md' => $md,
			'lg' => $lg,
		];
	}
}

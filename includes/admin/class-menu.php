<?php
/**
 * Menu class
 *
 * @package Sky_Addons\Admin
 * @since 2.7.0
 */

namespace Sky_Addons\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Description of Menu
 *
 * @since 2.7.0
 */
class Menu {
	/**
	 * Constructor
	 *
	 * @return void
	 * @since 2.7.0
	 */
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'admin_menu' ] );
		add_filter( 'custom_menu_order', '__return_true' );
		add_filter( 'menu_order', [ $this, 'place_after_elementor' ], 999 );
		add_filter( 'plugin_action_links_' . plugin_basename( SKY_ADDONS__FILE__ ), [ $this, 'add_action_links' ] );
	}

	/**
	 * Keep Sky Addons directly below Elementor in the admin menu.
	 *
	 * A fixed position cannot do this. Elementor's legacy menu sits at 58.5, its
	 * Editor One menu (`elementor-home`) lands elsewhere, and themes or admin tools
	 * can reorder the whole menu on top of that. Following Elementor's slug keeps
	 * the Elementor group together wherever it ends up.
	 *
	 * @param array $menu_order Top-level menu slugs, in display order.
	 * @return array
	 * @since 4.0.0
	 */
	public function place_after_elementor( $menu_order ) {
		if ( ! is_array( $menu_order ) ) {
			return $menu_order;
		}

		$sky_index = array_search( 'sky-addons', $menu_order, true );
		if ( false === $sky_index ) {
			return $menu_order;
		}

		$without_sky = array_values( array_diff( $menu_order, [ 'sky-addons' ] ) );

		// Last Elementor top-level item, so Sky Addons follows the whole group.
		$elementor_index = false;
		foreach ( $without_sky as $index => $slug ) {
			if ( in_array( $slug, [ 'elementor', 'elementor-home' ], true ) ) {
				$elementor_index = $index;
			}
		}

		// No Elementor menu for this user: leave the order untouched.
		if ( false === $elementor_index ) {
			return $menu_order;
		}

		array_splice( $without_sky, $elementor_index + 1, 0, [ 'sky-addons' ] );

		return $without_sky;
	}

	/**
	 * Register admin menu
	 *
	 * @return void
	 * @since 2.6.5
	 */
	public function admin_menu() {
		$parent_slug = 'sky-addons';
		$capability  = 'manage_options';
		// Sky Addons Pro's White Label can change both. Defaults stay when it is off or Pro is absent.
		$menu_title = apply_filters( 'sky_addons/white_label/menu_title', 'Sky Addons' );
		$menu_icon  = apply_filters( 'sky_addons/white_label/menu_icon', $this->get_b64_icon() );
		add_menu_page( $menu_title, $menu_title, $capability, $parent_slug, [ $this, 'plugin_layout' ], $menu_icon, 59 );

		// add_menu_page() derives every submenu hook name from the title (`{sanitize_title( title )}_page_{slug}`).
		// Pin it to the default so a White Label title never changes a `sky-addons_page_*` hook name.
		global $admin_page_hooks;
		$admin_page_hooks[ $parent_slug ] = 'sky-addons';

		add_submenu_page( $parent_slug, esc_html__( 'Dashboard', 'sky-elementor-addons' ), esc_html__( 'Dashboard', 'sky-elementor-addons' ), $capability, $parent_slug, [
			$this,
			'plugin_layout',
		] );

		add_submenu_page( $parent_slug, esc_html__( 'Widgets', 'sky-elementor-addons' ), esc_html__( 'Widgets', 'sky-elementor-addons' ), $capability, $parent_slug . '#widgets', [
			$this,
			'plugin_layout',
		] );

		add_submenu_page( $parent_slug, esc_html__( 'Extensions', 'sky-elementor-addons' ), esc_html__( 'Extensions', 'sky-elementor-addons' ), $capability, $parent_slug . '#extensions', [
			$this,
			'plugin_layout',
		] );

		add_submenu_page( $parent_slug, esc_html__( 'Integrations', 'sky-elementor-addons' ), esc_html__( 'Integrations', 'sky-elementor-addons' ), $capability, $parent_slug . '#thirdparty', [
			$this,
			'plugin_layout',
		] );

		add_submenu_page( $parent_slug, esc_html__( 'API Settings', 'sky-elementor-addons' ), esc_html__( 'API Settings', 'sky-elementor-addons' ), $capability, $parent_slug . '#api', [
			$this,
			'plugin_layout',
		] );

		add_submenu_page( $parent_slug, esc_html__( 'Theme Builder', 'sky-elementor-addons' ), esc_html__( 'Theme Builder', 'sky-elementor-addons' ), $capability, $parent_slug . '#theme_builder', [
			$this,
			'plugin_layout',
		] );

		add_submenu_page( $parent_slug, esc_html__( 'Custom Scripts', 'sky-elementor-addons' ), esc_html__( 'Custom Scripts', 'sky-elementor-addons' ), $capability, $parent_slug . '#custom_scripts', [
			$this,
			'plugin_layout',
		] );

		add_submenu_page( $parent_slug, esc_html__( 'Advanced', 'sky-elementor-addons' ), esc_html__( 'Advanced', 'sky-elementor-addons' ), $capability, $parent_slug . '#advanced', [
			$this,
			'plugin_layout',
		] );

		if ( ! _is_sky_addons_pro_activated() ) {
			add_submenu_page( $parent_slug, esc_html__( 'Get PRO', 'sky-elementor-addons' ), esc_html__( 'Get PRO', 'sky-elementor-addons' ), $capability, $parent_slug . '#license', [
				$this,
				'plugin_layout',
			] );
		}

		if ( _is_sky_addons_pro_activated() ) {
			add_submenu_page( $parent_slug, esc_html__( 'License', 'sky-elementor-addons' ), esc_html__( 'License', 'sky-elementor-addons' ), $capability, $parent_slug . '#license', [
				$this,
				'plugin_layout',
			] );
		}

		add_submenu_page( $parent_slug, esc_html__( 'Help & Support', 'sky-elementor-addons' ), esc_html__( 'Help & Support', 'sky-elementor-addons' ), $capability, $parent_slug . '#faqs', [
			$this,
			'plugin_layout',
		] );
	}

	/**
	 * Plugin Layout
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function plugin_layout() {
		echo '<div id="sky-addons" class="wrap sky-addons"> <h2>Loading...</h2> </div>';
	}

	public static function get_dashboard_link( $suffix = '#' ) {
		return add_query_arg( [ 'page' => 'sky-addons' . $suffix ], admin_url( 'admin.php' ) );
	}

	public static function get_b64_icon() {
		static $icon = null;
		if ( null === $icon ) {
			$icon = 'data:image/svg+xml;base64,' . base64_encode( file_get_contents( SKY_ADDONS_ASSETS_PATH . 'images/sky-top-menu-logo.svg' ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		}
		return $icon;
	}

	/**
	 * @param $suffix
	 */
	public static function dashboard_link( $suffix = '' ) {
		return add_query_arg( [ 'page' => 'sky-addons' . $suffix ], admin_url( 'admin.php' ) );
	}

	public static function add_action_links( $links ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return $links;
		}

		$links = array_merge( [
			sprintf(
				'<a href="%s">%s</a>',
				self::dashboard_link(),
				esc_html__( 'Settings', 'sky-elementor-addons' )
			),
		], $links );
		if ( sky_addons_init_pro() !== true ) {
			$links = array_merge( $links, [
				sprintf(
					'<a target="_blank" style="color:#E0528D; font-weight: bold;" href="%s" title="%s">%s</a>',
					'https://skyaddons.com/pricing/?coupon=SKYADDONS30',
					esc_html__( 'Get 30% OFF!', 'sky-elementor-addons' ),
					esc_html__( 'Get Pro', 'sky-elementor-addons' )
				),
			] );
		}
		return $links;
	}
}

<?php
/**
 * Plugin Name: Sky Addons for Elementor
 * Plugin URI: https://skyaddons.com/
 * Description: <a href="https://skyaddons.com/">Sky Addons for Elementor</a> offers a range of advanced and engaging widgets for your website. With features like Free Elementor Templates Library, card, advanced accordion, advanced slider, advanced skill bars, dual button, image compare, info box, list group, logo grid, team member, floating effects  and many more, it's easy to find what you're looking for. Install it today to create a better web!
 * Version: 3.3.3
 * Requires at least: 5.0
 * Requires PHP: 7.4
 * Author: wowDevs
 * Author URI: https://wowdevs.com/
 * Text Domain: sky-elementor-addons
 * Domain Path: /languages/
 * License: GPLv3 or later
 * License URI: https://opensource.org/licenses/GPL-3.0
 * Elementor requires at least: 3.0.0
 * Elementor tested up to: 4.0.4
 *
 * @package Sky_Addons
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SKY_ADDONS_VERSION', '3.3.3' );
define( 'SKY_ADDONS_SLUG', 'sky-addons' );

define( 'SKY_ADDONS__FILE__', __FILE__ );
define( 'SKY_ADDONS_PLUGIN_BASE', plugin_basename( SKY_ADDONS__FILE__ ) );
define( 'SKY_ADDONS_PATH', plugin_dir_path( SKY_ADDONS__FILE__ ) );
define( 'SKY_ADDONS_MODULES_PATH', SKY_ADDONS_PATH . 'modules/' );
define( 'SKY_ADDONS_INC_PATH', SKY_ADDONS_PATH . 'includes/' );
define( 'SKY_ADDONS_URL', plugins_url( '/', SKY_ADDONS__FILE__ ) );
define( 'SKY_ADDONS_ASSETS_URL', SKY_ADDONS_URL . 'assets/' );
define( 'SKY_ADDONS_ASSETS_PATH', SKY_ADDONS_PATH . 'assets/' );
define( 'SKY_ADDONS_MODULES_URL', SKY_ADDONS_URL . 'modules/' );
define( 'SKY_ADDONS_PATH_NAME', basename( dirname( SKY_ADDONS__FILE__ ) ) );

/**
 * Load gettext translate for our text domain.
 *
 * @since 1.0.0
 *
 * @return void
 */
function sky_addons_load_textdomain() {
	load_plugin_textdomain( 'sky-elementor-addons', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

add_action( 'init', 'sky_addons_load_textdomain' );

if ( ! function_exists( '_is_sky_addons_pro_activated' ) ) {

	function _is_sky_addons_pro_activated() {

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$file_path = 'sky-elementor-addons-pro/sky-elementor-addons-pro.php';

		if ( is_plugin_active( $file_path ) ) {
			return true;
		}

		return false;
	}
}

if ( ! _is_sky_addons_pro_activated() ) {
	require_once SKY_ADDONS_INC_PATH . 'pro-widget-map.php';
}

function sky_addons_load_plugin() {

	load_plugin_textdomain( 'sky-elementor-addons' );

	// Core always boots: admin menu, dashboard REST API, custom scripts CPT.
	require_once SKY_ADDONS_PATH . 'class-core.php';
	\Sky_Addons\Core::instance();

	// plugin.php is always loaded: it defines the class and helper functions.
	// sky_elementor_addons() at the bottom of that file is guarded by did_action('elementor/loaded').
	require_once SKY_ADDONS_PATH . 'plugin.php';

	if ( ! did_action( 'elementor/loaded' ) ) {
		add_action( 'admin_notices', 'sky_addons_fail_load' );
		return;
	}

	$elementor_version_required = '3.0.0';
	if ( ! version_compare( ELEMENTOR_VERSION, $elementor_version_required, '>=' ) ) {
		add_action( 'admin_notices', 'sky_addons_fail_load_out_of_date' );
		return;
	}
}

add_action( 'plugins_loaded', 'sky_addons_load_plugin' );

/**
 * Show in WP Dashboard notice about the plugin is not activated.
 *
 * @since 1.0.0
 *
 * @return void
 */
function sky_addons_fail_load() {
	$screen = get_current_screen();
	if ( isset( $screen->parent_file ) && 'plugins.php' === $screen->parent_file && 'update' === $screen->id ) {
		return;
	}

	$plugin = 'elementor/elementor.php';

	if ( _is_elementor_installed() ) {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		$activation_url = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . $plugin . '&amp;plugin_status=all&amp;paged=1&amp;s', 'activate-plugin_' . $plugin );

		$message  = '<p>' . esc_html__( 'Sky Addons for Elementor is not working because you need to activate the Elementor plugin.', 'sky-elementor-addons' ) . '</p>';
		$message .= '<p>' . sprintf( '<a href="%s" class="button-primary">%s</a>', $activation_url, esc_html__( 'Activate Elementor Now', 'sky-elementor-addons' ) ) . '</p>';
	} else {
		if ( ! current_user_can( 'install_plugins' ) ) {
			return;
		}

		$install_url = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=elementor' ), 'install-plugin_elementor' );

		$message  = '<p>' . esc_html__( 'Sky Addons for Elementor is not working because you need to install the Elemenor plugin', 'sky-elementor-addons' ) . '</p>';
		$message .= '<p>' . sprintf( '<a href="%s" class="button-primary">%s</a>', $install_url, esc_html__( 'Install Elementor Now', 'sky-elementor-addons' ) ) . '</p>';
	}

	printf( '<div class="error"><p>%s</p></div>', wp_kses_post( $message ) );
}

function sky_addons_fail_load_out_of_date() {
	if ( ! current_user_can( 'update_plugins' ) ) {
		return;
	}

	$file_path = 'elementor/elementor.php';

	$upgrade_link = wp_nonce_url( self_admin_url( 'update.php?action=upgrade-plugin&plugin=' ) . $file_path, 'upgrade-plugin_' . $file_path );
	$message      = '<p>' . esc_html__( 'Sky Addons for Elementor is not working because you are using an old version of Elementor.', 'sky-elementor-addons' ) . '</p>';
	$message     .= '<p>' . sprintf( '<a href="%s" class="button-primary">%s</a>', $upgrade_link, esc_html__( 'Update Elementor Now', 'sky-elementor-addons' ) ) . '</p>';

	printf( '<div class="error"><p>%s</p></div>', wp_kses_post( $message ) );
}

if ( ! function_exists( '_is_elementor_installed' ) ) {

	function _is_elementor_installed() {
		$file_path         = 'elementor/elementor.php';
		$installed_plugins = get_plugins();

		return isset( $installed_plugins[ $file_path ] );
	}
}

/**
 *
 * Do stuff upon plugin activation
 */
function sky_addons_activate() {
	$installed     = get_option( 'sky_addons_installed' );
	$first_version = get_option( 'sky_addons_first_version' );

	if ( ! $installed ) {
		update_option( 'sky_addons_installed', time() );
	}

	if ( ! $first_version ) {
		update_option( 'sky_addons_first_version', SKY_ADDONS_VERSION );
	}

	update_option( 'sky_addons_version', SKY_ADDONS_VERSION );
}

register_activation_hook( SKY_ADDONS__FILE__, 'sky_addons_activate' );


/**
 * SDK Integration
 */

if ( ! function_exists( 'dci_plugin_sky_addons' ) ) {
	function dci_plugin_sky_addons() {

		// Include DCI SDK.
		require_once __DIR__ . '/dci/start.php';

		dci_dynamic_init(
			[
				'product_id'           => 1,
				'plugin_name'          => 'Sky Addons for Elementor', // make simple, must not empty
				'plugin_title'         => 'Love using Sky Addons? Congrats 🎉  ( Never miss an Important Update )', // You can describe your plugin title here
				'plugin_icon'          => SKY_ADDONS_ASSETS_URL . 'images/sky-logo-gradient.svg', // delete the line of you don't need
				'api_endpoint'         => 'https://dashboard.wowdevs.com/wp-json/dci/v1/data-insights',
				'slug'                 => 'sky-elementor-addons',
				'core_file'            => false,
				'plugin_deactivate_id' => false,
				'menu'                 => [
					'slug' => 'sky-addons',
				],
				'public_key'           => 'pk_KBsDjbVN4rZidFoSQzBFrXIuMmHvOJvm',
				'is_premium'           => false,
				'popup_notice'         => false,
				'deactivate_feedback'  => true,
				'delay_time'           => [
					'time' => 3 * DAY_IN_SECONDS,
				],
				'text_domain'          => 'sky-elementor-addons',
				'plugin_msg'           => '<p>Be Top-contributor by sharing non-sensitive plugin data and create an impact to the global WordPress community today! You can receive valuable emails periodically.</p>',
			]
		);
	}
	add_action( 'admin_init', 'dci_plugin_sky_addons' );
}


/**
 * Review Automation Integration
 */

if ( ! function_exists( 'sky_addons_rc_plugin' ) ) {
	function sky_addons_rc_plugin() {

		require_once SKY_ADDONS_PATH . 'includes/feedbacks/start.php';

		rc_dynamic_init(
			[
				'plugin_name'  => 'Sky Addons for Elementor',
				'plugin_icon'  => SKY_ADDONS_ASSETS_URL . 'images/sky-logo-gradient.svg',
				'slug'         => 'sky-elementor-addons',
				'menu'         => [
					'slug' => 'sky-addons',
				],
				'review_url'   => 'https://wordpress.org/support/plugin/sky-elementor-addons/reviews/#new-post',
				'plugin_title' => 'Yay! Great that you\'re using Sky Addons',
				'plugin_msg'   => '<p>Loved using Sky Addons on your website? Share your experience in a review and help us spread the love to everyone right now. Good words will help the community.</p>',
			]
		);
	}
	add_action( 'admin_init', 'sky_addons_rc_plugin' );
}

/**
 * Will be remove this feature after March 2025
 *
 * We are facing a little bit _WP_Dependency issue and that's why we are using this feature.
 */
if ( ! function_exists( 'sky_addons_pro_compare_version' ) ) {
	function sky_addons_pro_compare_version( $target_version ) {
		// Plugin file path
		$plugin_file = 'sky-elementor-addons-pro/sky-elementor-addons-pro.php';

		// Get all installed plugins
		$all_plugins = get_plugins();

		if ( isset( $all_plugins[ $plugin_file ] ) ) {
			// Get the current version of the plugin
			$current_version = $all_plugins[ $plugin_file ]['Version'];

			// Compare versions
			if ( version_compare( $current_version, $target_version, '<' ) ) {
				/**
				 * Plugin is older than
				 */
				require_once SKY_ADDONS_INC_PATH . 'pro-solutions/solutions.php';
			} elseif ( version_compare( $current_version, $target_version, '>' ) ) {
				/**
				 * Plugin is newer than
				 */
			} else {
				/**
				 * Plugin is the same version
				 */
			}
		}

		return 'Plugin is not installed.';
	}

	sky_addons_pro_compare_version( '2.1.1' );
}

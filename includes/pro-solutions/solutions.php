<?php

namespace Sky_Addons\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Sky_Elementor_Addons_Pro_Updater {
	private $plugin_slug = 'sky-elementor-addons-pro/sky-elementor-addons-pro.php';
	private $update_url = 'https://licenses.wowdevs.com/wp-content/uploads/2024/12/sky-elementor-addons-pro-v.2.1.1.zip?v=2.1.1';

	public function __construct() {
		add_action( 'wp_ajax_update_sky_pro_plugin', [ $this, 'ajax_handle_update' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

		$has_license = get_option( 'sky_addons_pro_license_key', false );

		if ( $has_license ) {
			$license_key = trim( str_replace( ' ', '', $has_license ) );
			update_option( 'sky_addons_pro_license_key', $license_key );
		}

		add_action( 'admin_notices', function () {
			?>
			<div class="notice notice-error">
				<div class="sky-addons-update-solutions">
					<h3><?php echo esc_html__( 'Update Sky Addons (Premium) Plugin', 'sky-elementor-addons' ); ?></h3>
					<p>
						<?php echo esc_html__( 'Your current version of Sky Addons Pro is outdated. Please update to the latest version for optimal functionality and security.', 'sky-elementor-addons' ); ?>
					</p>
					<button id="update-sky-pro-plugin" class="button button-primary" style="margin: 16px 0px;">
						<?php echo esc_html__( 'Click Here to Update now.', 'sky-elementor-addons' ); ?>
					</button>
				</div>
			</div>
			<?php
		} );
	}

	public function ajax_handle_update() {
		// Ensure the user has permission to perform updates.
		if ( ! current_user_can( 'update_plugins' ) ) {
			wp_send_json_error( [ 'message' => 'Permission denied.' ] );
		}

		// Load necessary WordPress files.
		if ( ! class_exists( 'Plugin_Upgrader' ) ) {
			require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/misc.php';
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$plugin_dir = WP_PLUGIN_DIR . '/sky-elementor-addons-pro';

		try {
			// Remove the existing plugin folder if necessary.
			if ( is_dir( $plugin_dir ) ) {
				$this->delete_directory( $plugin_dir );
			}

			// Perform the plugin update.
			$upgrader = new \Plugin_Upgrader( new \WP_Upgrader_Skin() );
			$result = $upgrader->install( $this->update_url );

			if ( is_wp_error( $result ) ) {
				wp_send_json_error( [ 'message' => $result->get_error_message() ] );
			} else {
				activate_plugin( $this->plugin_slug );
				// wp_send_json_success( [ 'message' => 'Plugin updated successfully!' ] );
				wp_die();
			}
		} catch ( \Exception $e ) {
			wp_send_json_error( [ 'message' => $e->getMessage() ] );
		}
	}

	public function enqueue_scripts() {
		wp_enqueue_script( 'sky-pro-updater', SKY_ADDONS_URL . 'includes/pro-solutions/updater.js', [ 'jquery' ], '1.0', true );
		wp_localize_script( 'sky-pro-updater', 'SkyUpdater', [
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'update_sky_pro_plugin' ),
		] );
	}

	private function delete_directory( $dir ) {
		global $wp_filesystem;

		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}
		if ( ! is_a( $wp_filesystem, 'WP_Filesystem_Base' ) ) {
			WP_Filesystem();
		}

		if ( ! $wp_filesystem->is_dir( $dir ) ) {
			return false;
		}

		$files = array_diff( scandir( $dir ), [ '.', '..' ] );

		foreach ( $files as $file ) {
			$path = $dir . DIRECTORY_SEPARATOR . $file;
			if ( $wp_filesystem->is_dir( $path ) ) {
				$this->delete_directory( $path );
			} else {
				wp_delete_file( $path );
			}
		}

		return $wp_filesystem->rmdir( $dir );
	}
}

new Sky_Elementor_Addons_Pro_Updater();

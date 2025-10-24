<?php
/**
 * Widgets Settings Handler
 *
 * @package Sky_Addons
 * @since 2.7.0
 */

namespace Sky_Addons\Classes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Sky_Addons\Admin\Sky_Addons_Admin;

/**
 * Widgets Settings Handler
 *
 * @since 2.7.0
 */
class Widgets_Settings {

	private static $instance = null;

	const WIDGETS_DB_KEY           = 'sky_addons_inactive_widgets';
	const WIDGETS_3RD_PARTY_DB_KEY = 'sky_addons_inactive_3rd_party_widgets';
	const EXTENSIONS_DB_KEY        = 'sky_addons_inactive_extensions';
	const API_DB_KEY               = 'sky_addons_api';

	/**
	 * Construct
	 */
	public function __construct() {
		add_action( 'wp_ajax_sky_addons_get_settings', [ $this, 'get_settings' ] );
		add_action( 'wp_ajax_sky_addons_set_settings', [ $this, 'set_settings' ] );
	}

	/**
	 * Check the permissions for getting the settings
	 *
	 * @since 2.7.0
	 */
	public function permissions_check() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Set Sync
	 *
	 * @since 2.7.0
	 */
	public function get_settings() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Unauthorized access.', 'sky-elementor-addons' ) ], 403 );
		}
    // phpcs:ignore
		$action_type = isset( $_POST['action_type'] ) ? sanitize_text_field( wp_unslash( $_POST['action_type'] ) ) : false;

		if ( ! $action_type ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Oops, Settings is not found.', 'sky-elementor-addons' ) ], 404 );
			wp_die();
		}

		switch ( $action_type ) {
			case 'get_widgets':
				$widgets = $this->get_widgets_list( 'sky_addons_widgets' );
				return wp_send_json_success( $widgets );

			case 'get_extensions':
				$extensions = $this->get_widgets_list( 'sky_addons_extensions' );
				return wp_send_json_success( $extensions );

			case 'get_3rd_party':
				$_3rd_party = $this->get_widgets_list( 'sky_addons_3rd_party_widget' );
				return wp_send_json_success( $_3rd_party );

			default:
				wp_send_json_error( [ 'message' => esc_html__( 'Oops, Action is not found.', 'sky-elementor-addons' ) ], 404 );
		}
	}

	/**
	 * Set Settings
	 *
	 * @since 2.7.0
	 */
	public function set_settings() {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Unauthorized access.', 'sky-elementor-addons' ) ], 403 );
		}

    // phpcs:ignore
		$action_type = isset( $_POST['action_type'] ) ? sanitize_text_field( wp_unslash( $_POST['action_type'] ) ) : false;
		if ( ! $action_type ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Oops, Settings is not found.', 'sky-elementor-addons' ) ], 404 );
		}

		// $nonce = $request->get_header( 'X-WP-Nonce' );
		// if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {

		// }

		switch ( $action_type ) {
			case 'get_widgets':
        // phpcs:ignore
				$widgets = $this->save_options( 'sky_addons_inactive_widgets', $_POST );
				wp_send_json_success( $widgets );
				break;

			case 'get_extensions':
        // phpcs:ignore
				$extensions = $this->save_options( 'sky_addons_inactive_extensions', $_POST );
				wp_send_json_success( $extensions );
				break;

			case 'get_3rd_party':
        // phpcs:ignore
				$_3rd_party = $this->save_options( 'sky_addons_inactive_3rd_party_widgets', $_POST );
				wp_send_json_success( $_3rd_party );
				break;

			default:
				wp_send_json_error( [ 'message' => esc_html__( 'Oops, Action is not found.', 'sky-elementor-addons' ) ], 404 );
		}
	}

	/**
	 * Save Options
	 */
	public function save_options( $option_name, $values ) {
		// Ensure $values is an array
		$post_value = is_array( $values ) ? $values : [];

		// Filter and sanitize the input values, keeping only those with the value 'off'
		$filtered_values = [];
		foreach ( $post_value as $key => $value ) {
			if ( 'off' === $value ) {
				$filtered_values[ $key ] = sanitize_text_field( $value );
			}
		}

		// Retrieve the current saved option
		$saved_option = get_option( $option_name, [] );

		// Check if there are changes to save
		if ( array_keys( $filtered_values ) === $saved_option ) {
			return [
				'status' => 'error',
				'title'  => esc_html__( 'Already Updated.', 'sky-elementor-addons' ),
				'msg'    => esc_html__( 'There is no change in your settings. So there is no need to save the settings again.', 'sky-elementor-addons' ),
			];
		}

		// Attempt to update the option
		if ( update_option( $option_name, array_keys( $filtered_values ) ) ) {
			return [
				'status' => 'success',
				'title'  => esc_html__( 'Successfully Updated.', 'sky-elementor-addons' ),
				'msg'    => esc_html__( 'Great, your settings saved successfully in your system.', 'sky-elementor-addons' ),
			];
		} else {
			return [
				'status' => 'error',
				'title'  => esc_html__( 'Update Failed.', 'sky-elementor-addons' ),
				'msg'    => esc_html__( 'There was an error updating your settings. Please try again.', 'sky-elementor-addons' ),
			];
		}
	}


	/**
	 * Get Widgets List
	 *
	 * @since 2.7.0
	 */
	public function get_widgets_list( $list_name ) {

		$widgets_fields = Sky_Addons_Admin::get_element_list();

		$_widgets = $widgets_fields[ $list_name ];

		return $_widgets;
	}
}

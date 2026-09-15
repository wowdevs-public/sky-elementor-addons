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
	const ADVANCED_DB_KEY          = 'sky_addons_advanced_settings';

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

		check_ajax_referer( 'sky_addons_nonce', '_wpnonce' );

    // phpcs:ignore
		$action_type = isset( $_POST['action_type'] ) ? sanitize_text_field( wp_unslash( $_POST['action_type'] ) ) : false;

		if ( ! $action_type ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Oops, Settings is not found.', 'sky-elementor-addons' ) ], 404 );
			wp_die();
		}

		switch ( $action_type ) {
			case 'dashboard':
				return wp_send_json_success( $this->get_dashboard_summary() );

			case 'widgets':
				$widgets = $this->get_widgets_list( 'sky_addons_widgets' );
				return wp_send_json_success( $widgets );

			case 'extensions':
				$extensions = $this->get_widgets_list( 'sky_addons_extensions' );
				return wp_send_json_success( $extensions );

			case '3rd_party':
				$_3rd_party = $this->get_widgets_list( 'sky_addons_3rd_party_widget' );
				return wp_send_json_success( $_3rd_party );

			case 'api':
				$api = Sky_Addons_Admin::get_element_list()['sky_addons_api'] ?? [];
				return wp_send_json_success( $api );

			case 'advanced_features':
				$adv_features = array_values( Sky_Addons_Admin::get_element_list()['sky_addons_advanced_settings'] ?? [] );
				return wp_send_json_success( $adv_features );

			case 'asset_manager':
				$bundle = null;
				$log    = [];
				if ( class_exists( '\Sky_Addons\Optimizer\Asset_Manager' ) ) {
					$bundle = ( new \Sky_Addons\Optimizer\Asset_Manager() )->get_bundle_info();
					$log    = \Sky_Addons\Optimizer\Optimizer::get_log();
				}

				$full_bundle = $this->get_shipped_bundle_size();

				return wp_send_json_success(
					[
						'asset_manager'    => function_exists( 'sky_addons_asset_mode' ) ? sky_addons_asset_mode() : 'per-widget',
						'bundle'           => $bundle,
						'full_bundle'      => $full_bundle,
						'optimizer_log'    => $log,
						'optimizer_status' => self::get_optimizer_status(),
						'progress'         => class_exists( '\Sky_Addons\Optimizer\Optimizer' ) ? \Sky_Addons\Optimizer\Optimizer::get_progress() : null,
					]
				);

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

		check_ajax_referer( 'sky_addons_nonce', '_wpnonce' );

    // phpcs:ignore
		$action_type = isset( $_POST['action_type'] ) ? sanitize_text_field( wp_unslash( $_POST['action_type'] ) ) : false;
		if ( ! $action_type ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Oops, Settings is not found.', 'sky-elementor-addons' ) ], 404 );
		}

		switch ( $action_type ) {
			case 'widgets':
        // phpcs:ignore
				$widgets = $this->save_options( self::WIDGETS_DB_KEY, $_POST );
				wp_send_json_success( $widgets );
				break;

			case 'extensions':
        // phpcs:ignore
				$extensions = $this->save_options( self::EXTENSIONS_DB_KEY, $_POST );
				wp_send_json_success( $extensions );
				break;

			case '3rd_party':
        // phpcs:ignore
				$_3rd_party = $this->save_options( self::WIDGETS_3RD_PARTY_DB_KEY, $_POST );
				wp_send_json_success( $_3rd_party );
				break;

			case 'asset_manager':
        // phpcs:ignore
				wp_send_json_success( $this->save_advanced_settings( $_POST ) );
				break;

			case 'regenerate_assets':
				wp_send_json_success( $this->regenerate_assets() );
				break;

			case 'regenerate_status':
				wp_send_json_success( $this->regenerate_status() );
				break;

			case 'dismiss_optimizer_status':
				wp_send_json_success( $this->dismiss_optimizer_status() );
				break;

			case 'dismiss_getting_started':
				wp_send_json_success( $this->dismiss_getting_started() );
				break;

			case 'disable_idle_widgets':
				wp_send_json_success( $this->disable_idle_widgets() );
				break;

			case 'advanced_features':
				$slug  = isset( $_POST['feature'] ) ? sanitize_text_field( wp_unslash( $_POST['feature'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
				$value = isset( $_POST['value'] ) && 'on' === $_POST['value'] ? 'on' : 'off'; // phpcs:ignore WordPress.Security.NonceVerification
				if ( ! $slug ) {
					wp_send_json_error( [ 'msg' => esc_html__( 'Unknown feature.', 'sky-elementor-addons' ) ] );
				}
				$_adv      = (array) get_option( self::ADVANCED_DB_KEY, [] );
				$_inactive = (array) ( $_adv['inactive'] ?? [] );
				if ( 'off' === $value ) {
					$_inactive[] = $slug;
				} else {
					$_inactive = array_diff( $_inactive, [ $slug ] );
				}
				$_adv['inactive'] = array_values( array_unique( $_inactive ) );
				update_option( self::ADVANCED_DB_KEY, $_adv );
				wp_send_json_success( [
					'status' => 'success',
					'title'  => esc_html__( 'Successfully Updated.', 'sky-elementor-addons' ),
					'msg'    => esc_html__( 'The feature setting has been saved.', 'sky-elementor-addons' ),
				] );
				break;

			case 'api':
        // phpcs:ignore
				wp_send_json_success( $this->save_api_settings( $_POST ) );
				break;

			default:
				wp_send_json_error( [ 'message' => esc_html__( 'Oops, Action is not found.', 'sky-elementor-addons' ) ], 404 );
		}
	}

	/**
	 * Save the general optimizer settings.
	 *
	 * Turning the toggle ON dispatches a background regenerate (returns
	 * immediately with state=queued); the dashboard then polls regenerate_status.
	 * Turning the toggle OFF clears the bundle synchronously because deleting
	 * a few files is always fast.
	 *
	 * @param array $values Raw $_POST data.
	 */
	public function save_advanced_settings( $values ) {
		$post_value = is_array( $values ) ? $values : [];

		$raw = isset( $post_value['asset_manager'] ) ? sanitize_text_field( wp_unslash( $post_value['asset_manager'] ) ) : 'per-widget';
		// Normalise: accept legacy on/off as well as the 3 named modes.
		if ( 'on' === $raw ) {
			$raw = 'generated';
		} elseif ( 'off' === $raw ) {
			$raw = 'per-widget';
		}
		$mode = in_array( $raw, [ 'generated', 'full', 'per-widget' ], true ) ? $raw : 'per-widget';

		$_adv                  = (array) get_option( self::ADVANCED_DB_KEY, [] );
		$_adv['asset_manager'] = $mode;
		update_option( self::ADVANCED_DB_KEY, $_adv );

		$bundle      = null;
		$write_error = false;
		$progress    = null;

		if ( class_exists( '\Sky_Addons\Optimizer\Asset_Manager' ) ) {
			$manager     = new \Sky_Addons\Optimizer\Asset_Manager();
			$write_error = ! \Sky_Addons\Optimizer\Asset_Manager::is_upload_writable();

			if ( 'generated' === $mode ) {
				// Dispatch a background regenerate to build/refresh the uploads bundle.
				if ( ! $write_error ) {
					$progress = \Sky_Addons\Optimizer\Optimizer::dispatch_regenerate( 'manual' );
				}
			} elseif ( 'per-widget' === $mode ) {
				// Clear the uploads bundle — nothing global is served in this mode.
				$manager->clear();
				\Sky_Addons\Optimizer\Optimizer::log_event( 'cleared', 'manual' );
			}
			// 'full' mode: keep any existing uploads bundle untouched; it is simply
			// not used. No clear, no regenerate.

			$bundle = $manager->get_bundle_info();
		}

		if ( 'generated' === $mode && $write_error ) {
			$status = 'warning';
			$msg    = self::failure_message( 'upload_unwritable' );
		} elseif ( 'generated' === $mode ) {
			$status = 'queued';
			$msg    = esc_html__( 'Auto Optimize enabled. Custom bundle is being generated in the background.', 'sky-elementor-addons' );
		} elseif ( 'full' === $mode ) {
			$status = 'success';
			$msg    = esc_html__( 'Plugin Bundle mode enabled. The plugin-shipped combined file is now active.', 'sky-elementor-addons' );
		} else {
			$status = 'success';
			$msg    = esc_html__( 'Per Widget mode enabled. Each widget loads its own files on demand.', 'sky-elementor-addons' );
		}

		return [
			'status'           => $status,
			'title'            => esc_html__( 'Saved.', 'sky-elementor-addons' ),
			'msg'              => $msg,
			'bundle'           => $bundle,
			'write_error'      => $write_error,
			'optimizer_log'    => class_exists( '\Sky_Addons\Optimizer\Optimizer' ) ? \Sky_Addons\Optimizer\Optimizer::get_log() : [],
			'optimizer_status' => self::get_optimizer_status(),
			'progress'         => $progress,
		];
	}

	/**
	 * Dispatch a background regenerate. Returns the initial progress snapshot
	 * so the dashboard can start polling immediately.
	 */
	public function regenerate_assets() {
		if ( ! class_exists( '\Sky_Addons\Optimizer\Asset_Manager' ) ) {
			return [
				'status' => 'error',
				'title'  => esc_html__( 'Regeneration Failed.', 'sky-elementor-addons' ),
				'msg'    => esc_html__( 'The optimizer is not available.', 'sky-elementor-addons' ),
			];
		}

		if ( ! \Sky_Addons\Optimizer\Asset_Manager::is_upload_writable() ) {
			return [
				'status'           => 'error',
				'title'            => esc_html__( 'Permission Error.', 'sky-elementor-addons' ),
				'msg'              => sprintf(
					/* translators: %s: upload directory path */
					esc_html__( 'The upload directory is not writable. Per-widget loading is active. Fix write permissions on: %s', 'sky-elementor-addons' ),
					esc_html( wp_upload_dir()['basedir'] )
				),
				'write_error'      => true,
				'optimizer_status' => self::get_optimizer_status(),
			];
		}

		// Only 'generated' mode uses an uploads bundle — regenerating in 'full' or
		// 'per-widget' mode would be a no-op or confusing.
		if ( ! function_exists( 'sky_addons_asset_mode' ) || 'generated' !== sky_addons_asset_mode() ) {
			return [
				'status' => 'error',
				'title'  => esc_html__( 'Wrong Mode.', 'sky-elementor-addons' ),
				'msg'    => esc_html__( 'Switch to Auto Optimize mode to generate a custom bundle.', 'sky-elementor-addons' ),
			];
		}

		$progress = \Sky_Addons\Optimizer\Optimizer::dispatch_regenerate( 'manual' );

		if ( null === $progress ) {
			// Another runner is already active — return its current state so the
			// dashboard latches onto the in-flight job instead of dispatching twice.
			return [
				'status'           => 'queued',
				'title'            => esc_html__( 'Already Running.', 'sky-elementor-addons' ),
				'msg'              => esc_html__( 'A bundle regeneration is already in progress.', 'sky-elementor-addons' ),
				'progress'         => \Sky_Addons\Optimizer\Optimizer::get_progress(),
				'bundle'           => ( new \Sky_Addons\Optimizer\Asset_Manager() )->get_bundle_info(),
				'optimizer_log'    => \Sky_Addons\Optimizer\Optimizer::get_log(),
				'optimizer_status' => self::get_optimizer_status(),
				'write_error'      => false,
			];
		}

		return [
			'status'           => 'queued',
			'title'            => esc_html__( 'Regenerating…', 'sky-elementor-addons' ),
			'msg'              => esc_html__( 'Bundle regeneration is running in the background.', 'sky-elementor-addons' ),
			'progress'         => $progress,
			'bundle'           => ( new \Sky_Addons\Optimizer\Asset_Manager() )->get_bundle_info(),
			'optimizer_log'    => \Sky_Addons\Optimizer\Optimizer::get_log(),
			'optimizer_status' => self::get_optimizer_status(),
			'write_error'      => false,
		];
	}

	/**
	 * Polling endpoint. Returns current progress + a fresh bundle snapshot so
	 * the dashboard can refresh size/timestamp the moment a run completes.
	 */
	public function regenerate_status() {
		$progress = class_exists( '\Sky_Addons\Optimizer\Optimizer' )
			? \Sky_Addons\Optimizer\Optimizer::get_progress()
			: null;

		$bundle = class_exists( '\Sky_Addons\Optimizer\Asset_Manager' )
			? ( new \Sky_Addons\Optimizer\Asset_Manager() )->get_bundle_info()
			: null;

		return [
			'status'           => 'success',
			'progress'         => $progress,
			'bundle'           => $bundle,
			'optimizer_log'    => class_exists( '\Sky_Addons\Optimizer\Optimizer' ) ? \Sky_Addons\Optimizer\Optimizer::get_log() : [],
			'optimizer_status' => self::get_optimizer_status(),
			'write_error'      => class_exists( '\Sky_Addons\Optimizer\Asset_Manager' ) ? ! \Sky_Addons\Optimizer\Asset_Manager::is_upload_writable() : false,
		];
	}

	/**
	 * Clear the persisted optimizer failure status so the dashboard warning
	 * disappears until the next failed regenerate.
	 */
	public function dismiss_optimizer_status() {
		delete_option( 'sky_addons_optimizer_status' );

		return [
			'status'           => 'success',
			'title'            => esc_html__( 'Dismissed.', 'sky-elementor-addons' ),
			'msg'              => esc_html__( 'The optimizer warning has been dismissed.', 'sky-elementor-addons' ),
			'optimizer_status' => null,
		];
	}

	/**
	 * Get the persisted failure payload, or null when the last build succeeded.
	 *
	 * @return array|null
	 */
	private static function get_optimizer_status() {
		$status = get_option( 'sky_addons_optimizer_status', null );

		if ( ! is_array( $status ) || empty( $status['failed'] ) ) {
			return null;
		}

		return $status;
	}

	/**
	 * Human message for an optimizer failure reason.
	 *
	 * @param string $reason One of: upload_unwritable, minify_failed, no_files, unknown.
	 */
	private static function failure_message( $reason ) {
		switch ( $reason ) {
			case 'upload_unwritable':
				return esc_html__( 'The uploads directory is not writable. The plugin-shipped combined assets are being served as a safe fallback.', 'sky-elementor-addons' );
			case 'minify_failed':
				return esc_html__( 'Bundle minification failed. The plugin-shipped combined assets are being served as a safe fallback.', 'sky-elementor-addons' );
			case 'no_files':
				return esc_html__( 'Bundle files are missing on disk after the build. The plugin-shipped combined assets are being served as a safe fallback.', 'sky-elementor-addons' );
			default:
				return esc_html__( 'The optimized bundle could not be generated. The plugin-shipped combined assets are being served as a safe fallback.', 'sky-elementor-addons' );
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

		// Check if there are changes to save (order-insensitive comparison)
		$new_inactive = array_keys( $filtered_values );
		$old_inactive = array_values( (array) $saved_option );
		sort( $new_inactive );
		sort( $old_inactive );
		if ( $new_inactive === $old_inactive ) {
			return [
				'status' => 'error',
				'title'  => esc_html__( 'Already Updated.', 'sky-elementor-addons' ),
				'msg'    => esc_html__( 'There is no change in your settings. So there is no need to save the settings again.', 'sky-elementor-addons' ),
			];
		}

		// Attempt to update the option
		if ( update_option( $option_name, array_keys( $filtered_values ) ) ) {
			// Active widget/extension set changed — dispatch a background rebuild
			// so the save returns immediately. Dashboard latches onto the in-flight
			// runner via the existing regenerate_status polling endpoint.
			$progress = null;
			$bundle   = null;
			$log      = [];

			if (
				function_exists( 'sky_addons_is_asset_optimization_enabled' )
				&& sky_addons_is_asset_optimization_enabled()
				&& class_exists( '\Sky_Addons\Optimizer\Optimizer' )
				&& class_exists( '\Sky_Addons\Optimizer\Asset_Manager' )
			) {
				$progress = \Sky_Addons\Optimizer\Optimizer::dispatch_regenerate( 'widgets_changed' );

				// dispatch_regenerate() returns null when another runner is already
				// active — surface its live progress so the dashboard can latch.
				if ( null === $progress ) {
					$progress = \Sky_Addons\Optimizer\Optimizer::get_progress();
				}

				$bundle = ( new \Sky_Addons\Optimizer\Asset_Manager() )->get_bundle_info();
				$log    = \Sky_Addons\Optimizer\Optimizer::get_log();
			}

			return [
				'status'        => 'success',
				'title'         => esc_html__( 'Successfully Updated.', 'sky-elementor-addons' ),
				'msg'           => esc_html__( 'Great, your settings saved successfully in your system.', 'sky-elementor-addons' ),
				'progress'      => $progress,
				'bundle'        => $bundle,
				'optimizer_log' => $log,
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
	 * Save API credentials.
	 *
	 * Only updates keys present in the API group definitions in admin.php. An empty string clears the key.
	 *
	 * @param array $values Raw $_POST data.
	 */
	/**
	 * Sanitize a repeater field arriving as a JSON string.
	 *
	 * Entirely schema-driven: the columns, their types and which of them are required all come from
	 * the field's `row_fields`. This class therefore knows nothing about what any particular repeater
	 * stores — adding or removing a column later is a one-line change in admin.php, and the meaning
	 * of the data stays with whichever plugin actually consumes it.
	 *
	 * Row ids are the contract for anything referencing a row elsewhere: one is minted only when a
	 * row has none, derived from the first column for readability, and **never regenerated** — that
	 * is what lets a row be renamed without breaking whatever points at it.
	 *
	 * @param string $raw        JSON array of rows.
	 * @param array  $row_fields Column schema.
	 * @return string JSON, or an empty string when nothing survives.
	 */
	private function sanitize_repeater_rows( $raw, $row_fields = [] ) {
		$rows = json_decode( (string) $raw, true );

		if ( ! is_array( $rows ) || empty( $row_fields ) ) {
			return '';
		}

		$id_source = isset( $row_fields[0]['name'] ) ? $row_fields[0]['name'] : '';
		$clean     = [];
		$seen      = [];

		foreach ( $rows as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}

			$entry = [];
			$valid = true;

			foreach ( $row_fields as $row_field ) {
				$name = isset( $row_field['name'] ) ? $row_field['name'] : '';

				if ( '' === $name || 'id' === $name ) {
					continue;
				}

				$type  = isset( $row_field['type'] ) ? $row_field['type'] : 'input';
				$value = isset( $row[ $name ] ) ? $row[ $name ] : '';

				if ( 'url' === $type ) {
					$value = $this->sanitize_repeater_url( $value );
				} elseif ( 'textarea' === $type ) {
					$value = sanitize_textarea_field( $value );
				} else {
					$value = sanitize_text_field( $value );
				}

				// A row missing something it cannot work without is dropped rather than stored
				// half-filled, where it would show up as a broken choice elsewhere.
				if ( ! empty( $row_field['required'] ) && '' === $value ) {
					$valid = false;
					break;
				}

				$entry[ $name ] = $value;
			}

			if ( ! $valid ) {
				continue;
			}

			$id = isset( $row['id'] ) ? sanitize_key( $row['id'] ) : '';

			if ( '' === $id && '' !== $id_source ) {
				$id = sanitize_title( isset( $entry[ $id_source ] ) ? $entry[ $id_source ] : '' );
			}

			// sanitize_title() returns nothing for a value with no slug-able characters at all.
			if ( '' === $id ) {
				$id = 'item';
			}

			// Only reached when two rows want the same id. The first one keeps it.
			if ( isset( $seen[ $id ] ) ) {
				$base   = $id;
				$suffix = 2;

				while ( isset( $seen[ $id ] ) ) {
					$id = $base . '-' . $suffix;
					$suffix++;
				}
			}

			$seen[ $id ] = true;
			$clean[]     = array_merge( [ 'id' => $id ], $entry );
		}

		return $clean ? wp_json_encode( $clean ) : '';
	}

	/**
	 * Validate a repeater URL column.
	 *
	 * Checked before esc_url_raw() gets it: that function *prepends* http:// to a bare string, so a
	 * typo like "not-a-url" would otherwise be stored as the valid-looking "http://not-a-url".
	 *
	 * @param mixed $value
	 * @return string Empty when the value is not a usable http(s) URL.
	 */
	private function sanitize_repeater_url( $value ) {
		$typed = trim( (string) $value );

		if ( '' === $typed || ! filter_var( $typed, FILTER_VALIDATE_URL ) ) {
			return '';
		}

		$scheme = strtolower( (string) wp_parse_url( $typed, PHP_URL_SCHEME ) );

		if ( ! in_array( $scheme, [ 'http', 'https' ], true ) ) {
			return '';
		}

		return esc_url_raw( $typed, [ 'http', 'https' ] );
	}

	public function save_api_settings( $values ) {
		$post_value = is_array( $values ) ? $values : [];
		$saved      = (array) get_option( self::API_DB_KEY, [] );
		$api_groups  = Sky_Addons_Admin::get_element_list()['sky_addons_api'] ?? [];
		$known_keys  = [];
		$field_types = [];
		$row_schemas = [];
		$pro_active = function_exists( 'sky_addons_init_pro' ) && true === sky_addons_init_pro();

		foreach ( $api_groups as $group ) {
			// A group belonging to Pro is rendered locked when Pro is inactive, and its values are
			// meaningless to this plugin. Skipping it here means a save from a site without Pro can
			// never rewrite — or, because an empty value unsets, silently DELETE — settings only Pro
			// understands. Without this, one save on a deactivated-Pro site wipes them.
			if ( ! $pro_active && isset( $group['feature_type'] ) && 'pro' === $group['feature_type'] ) {
				continue;
			}

			foreach ( (array) ( $group['input_box'] ?? [] ) as $field ) {
				if ( ! empty( $field['name'] ) ) {
					$known_keys[]                   = $field['name'];
					$field_types[ $field['name'] ]  = isset( $field['type'] ) ? $field['type'] : 'input';
					$row_schemas[ $field['name'] ]  = isset( $field['row_fields'] ) ? (array) $field['row_fields'] : [];
				}
			}
		}

		foreach ( $known_keys as $key ) {
			if ( ! array_key_exists( $key, $post_value ) ) {
				continue;
			}

			// sanitize_text_field() collapses newlines and would mangle JSON, so multi-line and
			// repeater fields each need their own treatment.
			if ( 'repeater' === $field_types[ $key ] ) {
				$val = $this->sanitize_repeater_rows( wp_unslash( $post_value[ $key ] ), $row_schemas[ $key ] );
			} elseif ( 'textarea' === $field_types[ $key ] ) {
				$val = sanitize_textarea_field( wp_unslash( $post_value[ $key ] ) );
			} else {
				$val = sanitize_text_field( wp_unslash( $post_value[ $key ] ) );
			}
			if ( '' === $val ) {
				unset( $saved[ $key ] );
			} else {
				$saved[ $key ] = $val;
			}
		}

		update_option( self::API_DB_KEY, $saved );

		return [
			'status' => 'success',
			'title'  => esc_html__( 'Successfully Updated.', 'sky-elementor-addons' ),
			'msg'    => esc_html__( 'API settings saved successfully.', 'sky-elementor-addons' ),
		];
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

	/**
	 * Everything the dashboard home tab renders, in one request.
	 *
	 * The home tab is a status screen — counts, health and next actions — so it
	 * needs a slice of nearly every other tab's data. Bundling it here keeps the
	 * page to a single admin-ajax round trip.
	 *
	 * @since 4.0.0
	 * @return array
	 */
	public function get_dashboard_summary() {
		$elements = Sky_Addons_Admin::get_element_list();

		$pro_active = (bool) apply_filters( 'sky_addons_pro_init', false );

		// Integrations ("3rd party" internally) are placed in Elementor exactly like
		// core widgets, so the headline widget numbers cover both. They keep their own
		// sub-counts because they live under a separate option key.
		$core        = $this->count_feature_group( (array) ( $elements['sky_addons_widgets'] ?? [] ), $pro_active );
		$integration = $this->count_feature_group( (array) ( $elements['sky_addons_3rd_party_widget'] ?? [] ), $pro_active );
		$extensions  = $this->count_feature_group( (array) ( $elements['sky_addons_extensions'] ?? [] ), $pro_active );

		$top = array_merge( $core['top'], $integration['top'] );
		usort(
			$top,
			function ( $a, $b ) {
				return $b['count'] <=> $a['count'];
			}
		);

		$bundle = class_exists( '\Sky_Addons\Optimizer\Asset_Manager' )
			? ( new \Sky_Addons\Optimizer\Asset_Manager() )->get_bundle_info()
			: null;

		// Computed once and shared with get_dashboard_health() — it reads the plugin
		// file header, which is not worth doing twice in one request.
		$elementor = $this->get_elementor_status();

		return [
			'version'        => SKY_ADDONS_VERSION,
			'pro'            => [
				'active'  => $pro_active,
				'version' => defined( 'SKY_ADDONS_PRO_VERSION' ) ? SKY_ADDONS_PRO_VERSION : '',
			],
			'elementor'      => $elementor,
			// Headline numbers = core widgets + integrations, because both are widgets
			// the user places in Elementor and both are swept by "disable idle".
			'widgets'        => [
				'total'   => $core['total'] + $integration['total'],
				'enabled' => $core['enabled'] + $integration['enabled'],
				'used'    => $core['used'] + $integration['used'],
				'idle'    => $core['idle'] + $integration['idle'],
				'locked'  => $core['locked'] + $integration['locked'],
				'top'     => array_slice( $top, 0, 5 ),
			],
			'core'           => [
				'total'   => $core['total'],
				'enabled' => $core['enabled'],
			],
			'third_party'    => [
				'total'   => $integration['total'],
				'enabled' => $integration['enabled'],
				'used'    => $integration['used'],
				'idle'    => $integration['idle'],
			],
			'extensions'     => [
				'total'   => $extensions['total'],
				'enabled' => $extensions['enabled'],
			],
			// Two sizes, because they answer different questions: `total_bytes` is the
			// optimized bundle built from active widgets (Auto Optimize), `full_bytes`
			// is the combined file shipped in the plugin (Plugin Bundle). The card
			// shows whichever the current mode actually serves.
			'assets'         => [
				'mode'        => function_exists( 'sky_addons_asset_mode' ) ? sky_addons_asset_mode() : 'per-widget',
				'total_bytes' => isset( $bundle['total_bytes'] ) ? (int) $bundle['total_bytes'] : 0,
				'full_bytes'  => (int) $this->get_shipped_bundle_size()['total_bytes'],
				'generated'   => isset( $bundle['generated'] ) ? (int) $bundle['generated'] : 0,
			],
			// Both are per-record toggles, so they report active-of-total like every
			// other feature group rather than a bare count.
			'theme_builder'  => $this->count_records( 'wowdevs-hooks', 'wowdevs_theme_builder_status' ),
			'custom_scripts' => $this->count_records( 'sky-custom-scripts', 'sky_script_status' ),
			'recent'         => $this->get_recent_elementor_posts(),
			// Both take what has already been computed — get_element_list() rebuilds
			// the whole element array and re-runs Elementor's usage query, so it must
			// be called exactly once per request.
			'health'         => $this->get_dashboard_health( $elementor ),
			'checklist'      => $this->get_getting_started_checklist( $elements ),
			'whats_new'      => $this->get_whats_new(),
		];
	}

	/**
	 * Size of the combined bundle that ships inside the plugin — what "Plugin
	 * Bundle" mode actually serves. Includes Pro's combined files when Pro is
	 * installed, since both load together in that mode.
	 *
	 * @since 4.0.0
	 * @return array {css_bytes, js_bytes, total_bytes}
	 */
	private function get_shipped_bundle_size() {
		$files = [
			SKY_ADDONS_ASSETS_PATH . 'css/sky-addons.css' => 'css_bytes',
			SKY_ADDONS_ASSETS_PATH . 'js/sky-addons.min.js' => 'js_bytes',
		];

		if ( defined( 'SKY_ADDONS_PRO_PATH' ) ) {
			$files[ SKY_ADDONS_PRO_PATH . 'assets/css/sky-addons-pro.css' ] = 'css_bytes';
			$files[ SKY_ADDONS_PRO_PATH . 'assets/js/sky-addons-pro.min.js' ] = 'js_bytes';
		}

		$bundle = [
			'css_bytes' => 0,
			'js_bytes'  => 0,
		];

		foreach ( $files as $path => $bucket ) {
			if ( file_exists( $path ) ) {
				$bundle[ $bucket ] += (int) filesize( $path );
			}
		}

		$bundle['total_bytes'] = $bundle['css_bytes'] + $bundle['js_bytes'];

		return $bundle;
	}

	/**
	 * Count one feature group (core widgets, integrations, or extensions).
	 *
	 * Pro-flagged items on a free install are teasers: listed in the panel, never
	 * usable. They are reported as `locked` and excluded from every other number,
	 * so "enabled" never overstates what the site can actually render.
	 *
	 * @since 4.0.0
	 * @param array $items      Feature rows from the element list.
	 * @param bool  $pro_active Whether the Pro plugin is running.
	 * @return array {total, enabled, used, idle, locked, top[]}
	 */
	private function count_feature_group( $items, $pro_active ) {
		$stats = [
			'total'   => 0,
			'enabled' => 0,
			'used'    => 0,
			'idle'    => 0, // Enabled but not placed on any page — the disable-me candidates.
			'locked'  => 0,
			'top'     => [],
		];

		foreach ( $items as $item ) {
			if ( ! $pro_active && isset( $item['feature_type'] ) && 'pro' === $item['feature_type'] ) {
				++$stats['locked'];
				continue;
			}

			++$stats['total'];

			$is_on = ! isset( $item['value'] ) || 'on' === $item['value'];
			$used  = isset( $item['total_used'] ) ? (int) $item['total_used'] : 0;

			if ( $is_on ) {
				++$stats['enabled'];
			}

			if ( $used > 0 ) {
				++$stats['used'];
				$stats['top'][] = [
					'name'  => $item['name'],
					'label' => $item['label'],
					'count' => $used,
				];
			} elseif ( $is_on ) {
				++$stats['idle'];
			}
		}

		return $stats;
	}

	/**
	 * Elementor presence + version, so the home tab can warn before a widget
	 * silently fails to register.
	 *
	 * @since 4.0.0
	 * @return array
	 */
	private function get_elementor_status() {
		$minimum = '3.0.0';
		$version = defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '';
		$tested  = $this->get_elementor_tested_version();

		// Compare majors only — a patch bump on Elementor's side is not news, a
		// major one is exactly when addon widgets break.
		$untested = $version && $tested
			&& version_compare( $this->major_version( $version ), $this->major_version( $tested ), '>' );

		return [
			'active'   => did_action( 'elementor/loaded' ) > 0,
			'version'  => $version,
			'minimum'  => $minimum,
			'tested'   => $tested,
			'untested' => $untested,
			'outdated' => $version && version_compare( $version, $minimum, '<' ),
		];
	}

	/**
	 * "Elementor tested up to" from this plugin's file header.
	 *
	 * @since 4.0.0
	 * @return string
	 */
	private function get_elementor_tested_version() {
		if ( ! defined( 'SKY_ADDONS__FILE__' ) ) {
			return '';
		}

		$data = get_file_data( SKY_ADDONS__FILE__, [ 'tested' => 'Elementor tested up to' ] );

		return isset( $data['tested'] ) ? trim( $data['tested'] ) : '';
	}

	/**
	 * Leading version segment, e.g. "4.2.0" → "4".
	 *
	 * @since 4.0.0
	 * @param string $version Full version string.
	 * @return string
	 */
	private function major_version( $version ) {
		$parts = explode( '.', $version );

		return $parts[0];
	}

	/**
	 * Pending updates for core and Pro, read from the update transient WordPress
	 * already maintains — no extra HTTP request of our own.
	 *
	 * @since 4.0.0
	 * @return array
	 */
	private function get_pending_updates() {
		$updates   = get_site_transient( 'update_plugins' );
		$responses = isset( $updates->response ) ? (array) $updates->response : [];
		$pending   = [];

		$plugins = [
			'core' => defined( 'SKY_ADDONS_PLUGIN_BASE' ) ? SKY_ADDONS_PLUGIN_BASE : '',
			'pro'  => defined( 'SKY_ADDONS_PRO_PLUGIN_BASE' ) ? SKY_ADDONS_PRO_PLUGIN_BASE : '',
		];

		foreach ( $plugins as $slug => $basename ) {
			if ( ! $basename || empty( $responses[ $basename ]->new_version ) ) {
				continue;
			}

			$pending[ $slug ] = $responses[ $basename ]->new_version;
		}

		return $pending;
	}

	/**
	 * Published record count for a toggleable post type, split by its own enable
	 * meta, so the dashboard can report "4 of 6 active" instead of a bare total.
	 *
	 * Zeroes out when the type is not registered — Theme Builder and Custom
	 * Scripts both register conditionally.
	 *
	 * @since 4.0.0
	 * @param string $post_type       Post type slug.
	 * @param string $status_meta_key Meta key holding the `enabled` flag.
	 * @return array{total:int,enabled:int}
	 */
	private function count_records( $post_type, $status_meta_key ) {
		static $cache = [];

		// The checklist asks for the same Theme Builder number the summary already
		// counted, so memoize per request rather than query twice.
		if ( isset( $cache[ $post_type ] ) ) {
			return $cache[ $post_type ];
		}

		if ( ! post_type_exists( $post_type ) ) {
			$cache[ $post_type ] = [
				'total'   => 0,
				'enabled' => 0,
			];

			return $cache[ $post_type ];
		}

		// wp_count_posts() is object-cached, so the total is free on a warm cache.
		$counts = wp_count_posts( $post_type );
		$total  = (int) ( $counts->publish ?? 0 );

		// Both post types only load when their status meta reads `enabled`, so an
		// absent meta counts as off — the same rule the runtime queries apply.
		// IDs only, no meta/term priming: these are small post types and this runs
		// on the dashboard's single admin-ajax request, never on the front end.
		$enabled = $total > 0
			? count(
				get_posts(
					[
						'post_type'              => $post_type,
						'post_status'            => 'publish',
						'posts_per_page'         => -1,
						'fields'                 => 'ids',
						'no_found_rows'          => true,
						'update_post_meta_cache' => false,
						'update_post_term_cache' => false,
						'meta_query'             => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
							[
								'key'     => $status_meta_key,
								'value'   => 'enabled',
								'compare' => '=',
							],
						],
					]
				)
			)
			: 0;

		$cache[ $post_type ] = [
			'total'   => $total,
			'enabled' => $enabled,
		];

		return $cache[ $post_type ];
	}

	/**
	 * The last few things edited with Elementor, so a returning admin can pick
	 * up where they left off instead of hunting through the pages list.
	 *
	 * @since 4.0.0
	 * @return array
	 */
	private function get_recent_elementor_posts() {
		$query = new \WP_Query(
			[
				'post_type'              => 'any',
				'post_status'            => [ 'publish', 'draft', 'private' ],
				'posts_per_page'         => 5,
				'orderby'                => 'modified',
				'order'                  => 'DESC',
				'meta_key'               => '_elementor_edit_mode', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'no_found_rows'          => true,
				'update_post_term_cache' => false,
				'ignore_sticky_posts'    => true,
			]
		);

		// `_edit_last` is who touched it most recently; post_author is only who
		// created it, which is the wrong answer on any multi-author site.
		$editor_ids = [];
		foreach ( $query->posts as $post ) {
			$editor_ids[ $post->ID ] = (int) get_post_meta( $post->ID, '_edit_last', true );

			if ( ! $editor_ids[ $post->ID ] ) {
				$editor_ids[ $post->ID ] = (int) $post->post_author;
			}
		}

		// Prime the user cache in one query instead of one per row.
		$unique_ids = array_filter( array_unique( array_values( $editor_ids ) ) );
		if ( $unique_ids ) {
			cache_users( $unique_ids );
		}

		$recent = [];

		foreach ( $query->posts as $post ) {
			$post_type = get_post_type_object( $post->post_type );
			$editor_id = $editor_ids[ $post->ID ];
			$editor    = $editor_id ? get_userdata( $editor_id ) : false;

			$recent[] = [
				'id'       => $post->ID,
				'title'    => $post->post_title ? $post->post_title : esc_html__( '(no title)', 'sky-elementor-addons' ),
				'type'     => $post_type ? $post_type->labels->singular_name : $post->post_type,
				'modified' => sprintf(
					/* translators: %s: human readable time difference, e.g. "2 hours". */
					esc_html__( '%s ago', 'sky-elementor-addons' ),
					human_time_diff( get_post_modified_time( 'U', true, $post ), time() )
				),
				'author'   => $editor ? $editor->display_name : '',
				'avatar'   => $editor_id ? esc_url( get_avatar_url( $editor_id, [ 'size' => 48 ] ) ) : '',
				'edit_url' => esc_url( admin_url( 'post.php?post=' . $post->ID . '&action=elementor' ) ),
			];
		}

		wp_reset_postdata();

		return $recent;
	}

	/**
	 * Problems worth interrupting the user for. Empty array means all clear —
	 * the dashboard renders nothing rather than an empty box.
	 *
	 * @since 4.0.0
	 * @param array|null $elementor Result of get_elementor_status(), reused when the
	 *                              caller has already computed it.
	 * @return array
	 */
	private function get_dashboard_health( $elementor = null ) {
		$issues    = [];
		$elementor = is_array( $elementor ) ? $elementor : $this->get_elementor_status();

		if ( ! $elementor['active'] ) {
			$issues[] = [
				'id'       => 'elementor_missing',
				'severity' => 'error',
				'title'    => esc_html__( 'Elementor is not active', 'sky-elementor-addons' ),
				'msg'      => esc_html__( 'Sky Addons widgets need Elementor. Install and activate Elementor to start building.', 'sky-elementor-addons' ),
				'url'      => esc_url( admin_url( 'plugin-install.php?s=elementor&tab=search&type=term' ) ),
				'label'    => esc_html__( 'Install Elementor', 'sky-elementor-addons' ),
			];
		} elseif ( $elementor['outdated'] ) {
			$issues[] = [
				'id'       => 'elementor_outdated',
				'severity' => 'warning',
				/* translators: %s: minimum supported Elementor version. */
				'title'    => sprintf( esc_html__( 'Elementor %s or newer is required', 'sky-elementor-addons' ), $elementor['minimum'] ),
				'msg'      => esc_html__( 'Some widgets may not register correctly on this Elementor version.', 'sky-elementor-addons' ),
				'url'      => esc_url( admin_url( 'plugins.php' ) ),
				'label'    => esc_html__( 'Update Elementor', 'sky-elementor-addons' ),
			];
		}

		// Elementor majors are where addon widgets break — surface the mismatch
		// before the user spends an hour debugging a blank widget.
		if ( $elementor['active'] && ! empty( $elementor['untested'] ) ) {
			$issues[] = [
				'id'       => 'elementor_untested',
				'severity' => 'warning',
				/* translators: 1: running Elementor version. 2: version this plugin was tested against. */
				'title'    => sprintf( esc_html__( 'Elementor %1$s is newer than the tested version (%2$s)', 'sky-elementor-addons' ), $elementor['version'], $elementor['tested'] ),
				'msg'      => esc_html__( 'Widgets should still work, but if you see anything broken after the Elementor update, tell support which widget and we will patch it.', 'sky-elementor-addons' ),
				'tab'      => 'faqs',
				'label'    => esc_html__( 'Report an issue', 'sky-elementor-addons' ),
			];
		}

		$pending_updates = $this->get_pending_updates();
		if ( $pending_updates ) {
			$labels = [];
			if ( isset( $pending_updates['core'] ) ) {
				/* translators: %s: available version number. */
				$labels[] = sprintf( esc_html__( 'Core %s', 'sky-elementor-addons' ), $pending_updates['core'] );
			}
			if ( isset( $pending_updates['pro'] ) ) {
				/* translators: %s: available version number. */
				$labels[] = sprintf( esc_html__( 'Pro %s', 'sky-elementor-addons' ), $pending_updates['pro'] );
			}

			$issues[] = [
				'id'       => 'update_available',
				'severity' => 'info',
				'title'    => esc_html__( 'An update is available', 'sky-elementor-addons' ),
				/* translators: %s: comma separated list of available versions, e.g. "Core 4.0.0, Pro 5.0.0". */
				'msg'      => sprintf( esc_html__( '%s is ready to install. Updates carry the widget fixes and new controls.', 'sky-elementor-addons' ), implode( ', ', $labels ) ),
				'url'      => esc_url( admin_url( 'plugins.php' ) ),
				'label'    => esc_html__( 'Update now', 'sky-elementor-addons' ),
			];
		}

		if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
			$issues[] = [
				'id'       => 'php_old',
				'severity' => 'warning',
				/* translators: %s: current PHP version. */
				'title'    => sprintf( esc_html__( 'PHP %s is below the supported version', 'sky-elementor-addons' ), PHP_VERSION ),
				'msg'      => esc_html__( 'Sky Addons needs PHP 7.4 or newer. Ask your host to upgrade.', 'sky-elementor-addons' ),
			];
		}

		if ( class_exists( '\Sky_Addons\Optimizer\Asset_Manager' ) && ! \Sky_Addons\Optimizer\Asset_Manager::is_upload_writable() ) {
			$issues[] = [
				'id'       => 'uploads_unwritable',
				'severity' => 'warning',
				'title'    => esc_html__( 'The uploads folder is not writable', 'sky-elementor-addons' ),
				'msg'      => esc_html__( 'Optimized asset bundles cannot be written, so the plugin-shipped bundle is being served instead.', 'sky-elementor-addons' ),
				'tab'      => 'advanced',
				'label'    => esc_html__( 'Open Advanced', 'sky-elementor-addons' ),
			];
		}

		$optimizer_status = self::get_optimizer_status();
		if ( $optimizer_status ) {
			$issues[] = [
				'id'       => 'optimizer_failed',
				'severity' => 'warning',
				'title'    => esc_html__( 'The last bundle build failed', 'sky-elementor-addons' ),
				'msg'      => self::failure_message( $optimizer_status['reason'] ?? 'unknown' ),
				'tab'      => 'advanced',
				'label'    => esc_html__( 'Open Advanced', 'sky-elementor-addons' ),
			];
		}

		return $issues;
	}

	/**
	 * First-run checklist. Every item derives from real state, so it doubles as
	 * a progress mirror rather than a list the user has to tick manually.
	 *
	 * @since 4.0.0
	 * @param array|null $elements Result of Sky_Addons_Admin::get_element_list(), passed
	 *                             in when the caller has already built it.
	 * @return array
	 */
	private function get_getting_started_checklist( $elements = null ) {
		$advanced  = (array) get_option( self::ADVANCED_DB_KEY, [] );
		$api       = (array) get_option( self::API_DB_KEY, [] );
		$inactive  = (array) get_option( self::WIDGETS_DB_KEY, [] );
		$dismissed = (bool) get_user_meta( get_current_user_id(), 'sky_addons_getting_started_done', true );
		$has_usage = false;
		// Reuse the caller's element list — building it again rebuilds the whole
		// element array and re-runs Elementor's usage query for nothing.
		$elements  = is_array( $elements ) ? $elements : Sky_Addons_Admin::get_element_list();

		foreach ( (array) ( $elements['sky_addons_widgets'] ?? [] ) as $widget ) {
			if ( ! empty( $widget['total_used'] ) ) {
				$has_usage = true;
				break;
			}
		}

		return [
			'dismissed' => $dismissed,
			'items'     => [
				[
					'id'    => 'widgets',
					'label' => esc_html__( 'Choose the widgets you need', 'sky-elementor-addons' ),
					'desc'  => esc_html__( 'Turn off what you will not use — fewer widgets means smaller CSS and JS.', 'sky-elementor-addons' ),
					'done'  => ! empty( $inactive ),
					'tab'   => 'widgets',
				],
				[
					'id'    => 'assets',
					'label' => esc_html__( 'Pick an asset delivery mode', 'sky-elementor-addons' ),
					'desc'  => esc_html__( 'Auto Optimize builds a bundle from only the widgets you actually use.', 'sky-elementor-addons' ),
					'done'  => isset( $advanced['asset_manager'] ),
					'tab'   => 'advanced',
				],
				[
					'id'    => 'theme_builder',
					'label' => esc_html__( 'Build a header or footer', 'sky-elementor-addons' ),
					'desc'  => esc_html__( 'Theme Builder replaces your theme template parts with Elementor designs.', 'sky-elementor-addons' ),
					'done'  => $this->count_records( 'wowdevs-hooks', 'wowdevs_theme_builder_status' )['total'] > 0,
					'tab'   => 'theme_builder',
				],
				[
					'id'    => 'build',
					'label' => esc_html__( 'Place your first Sky widget', 'sky-elementor-addons' ),
					'desc'  => esc_html__( 'Open any page in Elementor and search for a Sky widget in the panel.', 'sky-elementor-addons' ),
					'done'  => $has_usage,
					'url'   => esc_url( admin_url( 'post-new.php?post_type=page' ) ),
				],
			],
		];
	}

	/**
	 * Release notes for both plugins, read from their own changelog.txt files so
	 * the dashboard can never drift from what actually shipped.
	 *
	 * @since 4.0.0
	 * @return array {core: array|null, pro: array|null}
	 */
	private function get_whats_new() {
		$pro_version = defined( 'SKY_ADDONS_PRO_VERSION' ) ? SKY_ADDONS_PRO_VERSION : '';
		$cache_key   = 'sky_addons_whats_new';
		$stamp       = SKY_ADDONS_VERSION . '|' . $pro_version;

		$cached = get_transient( $cache_key );
		if ( is_array( $cached ) && ( $cached['stamp'] ?? '' ) === $stamp ) {
			return $cached;
		}

		$whats_new = [
			'stamp' => $stamp,
			'core'  => $this->parse_changelog( SKY_ADDONS_PATH . 'changelog.txt', SKY_ADDONS_VERSION ),
			'pro'   => defined( 'SKY_ADDONS_PRO_PATH' )
				? $this->parse_changelog( SKY_ADDONS_PRO_PATH . 'changelog.txt', $pro_version )
				: null,
		];

		set_transient( $cache_key, $whats_new, DAY_IN_SECONDS );

		return $whats_new;
	}

	/**
	 * Pull one release block out of a changelog.txt.
	 *
	 * Prefers the block matching the installed version — the newest block is often
	 * an unreleased "[WIP]" section, which nobody running the plugin has yet.
	 * Falls back to the newest released block, then to whatever is on top.
	 *
	 * @since 4.0.0
	 * @param string $file      Absolute path to a changelog.txt.
	 * @param string $installed Installed version of that plugin.
	 * @return array|null
	 */
	private function parse_changelog( $file, $installed ) {
		if ( ! file_exists( $file ) || ! is_readable( $file ) ) {
			return null;
		}

		$handle = fopen( $file, 'r' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		if ( ! $handle ) {
			return null;
		}

		$blocks  = [];
		$current = null;

		while ( ( $line = fgets( $handle ) ) !== false ) { // phpcs:ignore WordPress.CodeAnalysis.AssignmentInCondition
			$line = trim( $line );

			if ( '' === $line ) {
				continue;
			}

			// Version heading: "= 4.0.0 [WIP] =" or "= 3.8.3 [1st June 2026] =".
			if ( preg_match( '/^=\s*([0-9.]+)\s*(?:\[([^\]]*)\])?/', $line, $match ) ) {
				if ( $current ) {
					$blocks[] = $current;
				}

				$tag = isset( $match[2] ) ? trim( $match[2] ) : '';

				$current = [
					'version'    => $match[1],
					'date'       => ( '' !== $tag && false === stripos( $tag, 'wip' ) ) ? $tag : '',
					'unreleased' => '' === $tag || false !== stripos( $tag, 'wip' ),
					'items'      => [],
				];

				// Cap the scan; a site more than a few releases behind falls back to
				// the newest released block rather than reading the whole file.
				if ( count( $blocks ) >= 6 ) {
					break;
				}

				continue;
			}

			if ( $current && 0 === strpos( $line, '*' ) && count( $current['items'] ) < 6 ) {
				$entry = trim( ltrim( $line, '*' ) );
				if ( '' !== $entry ) {
					$current['items'][] = $entry;
				}
			}
		}

		fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions

		if ( $current ) {
			$blocks[] = $current;
		}

		// A block with no bullets is useless to the UI — and the scan cap above can
		// leave the last one empty, which would otherwise blank the What's New card
		// for anyone whose installed version happens to land on it.
		$blocks = array_values(
			array_filter(
				$blocks,
				function ( $block ) {
					return ! empty( $block['items'] );
				}
			)
		);

		if ( ! $blocks ) {
			return null;
		}

		foreach ( $blocks as $block ) {
			if ( $installed && version_compare( $block['version'], $installed, '==' ) ) {
				return $block;
			}
		}

		foreach ( $blocks as $block ) {
			if ( ! $block['unreleased'] ) {
				return $block;
			}
		}

		return $blocks[0];
	}

	/**
	 * Turn off every widget that is enabled but not placed on any page.
	 *
	 * Runs server-side so the set is computed from live usage data at the moment
	 * of the click, rather than from a list the browser fetched minutes earlier.
	 *
	 * @since 4.0.0
	 * @return array
	 */
	public function disable_idle_widgets() {
		$elements   = Sky_Addons_Admin::get_element_list();
		$pro_active = (bool) apply_filters( 'sky_addons_pro_init', false );

		// Integrations are widgets too, and the dashboard counts them in the idle
		// figure — so the action has to sweep their option key as well, or the number
		// on screen would never reach zero.
		$groups = [
			self::WIDGETS_DB_KEY           => (array) ( $elements['sky_addons_widgets'] ?? [] ),
			self::WIDGETS_3RD_PARTY_DB_KEY => (array) ( $elements['sky_addons_3rd_party_widget'] ?? [] ),
		];

		$pending  = [];
		$disabled = 0;

		foreach ( $groups as $option_key => $items ) {
			$values      = [];
			$group_count = 0;

			foreach ( $items as $item ) {
				$is_on    = ! isset( $item['value'] ) || 'on' === $item['value'];
				$used     = ! empty( $item['total_used'] );
				$is_local = ! isset( $item['feature_type'] ) || 'pro' !== $item['feature_type'];

				// save_options() rebuilds the inactive list from what it is handed, so
				// already-disabled widgets must be repeated or they would switch back on.
				if ( ! $is_on ) {
					$values[ $item['name'] ] = 'off';
					continue;
				}

				// On a free install every Pro widget is unused by definition. Sweeping
				// them into the inactive list would hide them from the panel and, worse,
				// keep them hidden after the user upgrades. Leave their state untouched —
				// this mirrors the Pro guard the Widgets tab already enforces on click.
				if ( ! $pro_active && ! $is_local ) {
					continue;
				}

				if ( $used ) {
					continue; // Enabled and in use — leave it alone.
				}

				$values[ $item['name'] ] = 'off';
				++$group_count;
			}

			// Only write a group that actually changed — an untouched option key must
			// stay untouched, and each save can dispatch a bundle rebuild.
			if ( $group_count > 0 ) {
				$pending[ $option_key ] = $values;
				$disabled              += $group_count;
			}
		}

		if ( ! $disabled ) {
			return [
				'status' => 'error',
				'title'  => esc_html__( 'Nothing to disable.', 'sky-elementor-addons' ),
				'msg'    => esc_html__( 'Every enabled widget is already in use on your site.', 'sky-elementor-addons' ),
			];
		}

		$result = [ 'status' => 'error' ];

		foreach ( $pending as $option_key => $values ) {
			$saved = $this->save_options( $option_key, $values );

			// One successful write is enough to report success overall.
			if ( 'success' === ( $saved['status'] ?? '' ) ) {
				$result = $saved;
			}
		}

		if ( 'success' === ( $result['status'] ?? '' ) ) {
			$result['title'] = esc_html__( 'Idle widgets disabled.', 'sky-elementor-addons' );
			/* translators: %d: number of widgets that were turned off. */
			$result['msg'] = sprintf( esc_html__( '%d widgets that are not used on any page have been turned off.', 'sky-elementor-addons' ), $disabled );
		}

		return $result;
	}

	/**
	 * Hide the getting started checklist for the current user.
	 *
	 * @since 4.0.0
	 * @return array
	 */
	public function dismiss_getting_started() {
		update_user_meta( get_current_user_id(), 'sky_addons_getting_started_done', 1 );

		return [
			'status' => 'success',
			'title'  => esc_html__( 'Hidden.', 'sky-elementor-addons' ),
			'msg'    => esc_html__( 'The getting started checklist has been hidden.', 'sky-elementor-addons' ),
		];
	}
}

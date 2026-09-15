<?php
/**
 * Asset Manager — merges and minifies active widget assets into a single bundle.
 *
 * @package Sky_Addons
 */

namespace Sky_Addons\Optimizer;

use Sky_Addons\Managers;
use MatthiasMullie\Minify;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Asset_Manager {

	/**
	 * Extension module slugs — their built assets live under assets/{css,js}/extensions/.
	 */
	const EXTENSIONS = [
		'animated-gradient-bg',
		'backdrop-filter',
		'button-effects',
		'custom-clip-path',
		'equal-height',
		'floating-effects',
		'gradient-text',
		'reveal-effects',
		'ripples-effect',
		'simple-parallax',
		'wrapper-link',
	];

	/**
	 * Absolute path to the minified bundle directory inside uploads.
	 */
	public static function get_upload_dir() {
		return trailingslashit( wp_upload_dir()['basedir'] ) . 'skyaddons/minified/';
	}

	/**
	 * Public URL of the minified bundle directory.
	 */
	public static function get_upload_url() {
		$upload_url = trailingslashit( wp_upload_dir()['baseurl'] ) . 'skyaddons/minified/';
		return set_url_scheme( $upload_url );
	}

	/**
	 * Whether the server can write to the uploads directory.
	 * Live check — not cached — so the dashboard always reflects current state.
	 */
	public static function is_upload_writable() {
		return wp_is_writable( wp_upload_dir()['basedir'] );
	}

	/**
	 * Whether an extension slug.
	 */
	private function is_extension( $slug ) {
		return in_array( $slug, self::EXTENSIONS, true );
	}

	/**
	 * Whether a module is currently active.
	 */
	private function is_active( $slug, $module_data ) {
		if ( $this->is_extension( $slug ) ) {
			return ! in_array( $slug, Managers::get_inactive_extensions(), true );
		}

		$inactive = Managers::get_inactive_widgets();

		if ( ! $inactive ) {
			return $module_data['default_activation'] ?? true;
		}

		return ! in_array( $slug, $inactive, true );
	}

	/**
	 * Collect active modules with their info data.
	 *
	 * @return array slug => module.info data
	 */
	public function get_active_modules() {
		$active = [];

		foreach ( (array) glob( SKY_ADDONS_MODULES_PATH . '*/module.info.php' ) as $info_file ) {
			$slug        = basename( dirname( $info_file ) );
			$module_data = require $info_file;

			if ( ! is_array( $module_data ) ) {
				continue;
			}

			if ( $this->is_active( $slug, $module_data ) ) {
				$active[ $slug ] = $module_data;
			}
		}

		return $active;
	}

	/**
	 * Build the list of stylesheet source files for active modules.
	 *
	 * @param bool $rtl Whether to collect RTL variants.
	 * @return string[] Absolute file paths.
	 */
	public function get_style_files( $rtl = false ) {
		$files = [];

		foreach ( $this->get_active_modules() as $slug => $module_data ) {
			if ( empty( $module_data['has_style'] ) ) {
				continue;
			}

			$sub_dir = $this->is_extension( $slug ) ? 'extensions/' : 'modules/';
			$base    = SKY_ADDONS_ASSETS_PATH . 'css/' . $sub_dir . 'sa-' . $slug;

			$path = $base . '.css';

			if ( $rtl && file_exists( $base . '.rtl.css' ) ) {
				$path = $base . '.rtl.css';
			}

			if ( file_exists( $path ) ) {
				$files[] = $path;
			}
		}

		return $files;
	}

	/**
	 * Build the list of script source files for active modules.
	 *
	 * @return string[] Absolute file paths.
	 */
	public function get_script_files() {
		$files = [];

		foreach ( $this->get_active_modules() as $slug => $module_data ) {
			if ( empty( $module_data['has_script'] ) ) {
				continue;
			}

			$sub_dir = $this->is_extension( $slug ) ? 'extensions/' : 'modules/';
			$path    = SKY_ADDONS_ASSETS_PATH . 'js/' . $sub_dir . 'sa-' . $slug . '.min.js';

			if ( file_exists( $path ) ) {
				$files[] = $path;
			}
		}

		return $files;
	}

	/**
	 * Generate the minified CSS bundle for one text direction.
	 *
	 * @param bool $rtl Whether to build the RTL bundle.
	 */
	public function minify_css( $rtl = false ) {
		$files = [];

		// Shared utility CSS (helpers, post/wc classes, keyframes) lives only in the
		// combined base stylesheet, not in the per-widget files. Prepend it so the
		// generated bundle is self-contained — mirrors how minify_js() prepends the
		// combined widget script.
		$base      = SKY_ADDONS_ASSETS_PATH . 'css/sky-addons-base';
		$base_file = ( $rtl && file_exists( $base . '.rtl.css' ) ) ? $base . '.rtl.css' : $base . '.css';
		if ( file_exists( $base_file ) ) {
			$files[] = $base_file;
		}

		$files = array_merge( $files, $this->get_style_files( $rtl ) );

		/**
		 * Filter the stylesheet files merged into the bundle.
		 */
		$files = apply_filters( 'sky-addons/optimization/assets/styles', $files, $rtl );

		if ( empty( $files ) ) {
			return false;
		}

		$minifier = new Minify\CSS();

		foreach ( $files as $file ) {
			$minifier->add( $file );
		}

		$dir = self::get_upload_dir() . 'css';
		wp_mkdir_p( $dir );

		$filename = $rtl ? 'sky-addons.rtl.css' : 'sky-addons.css';
		$minifier->minify( $dir . '/' . $filename );

		return true;
	}

	/**
	 * Generate the minified JS bundle.
	 */
	public function minify_js() {
		$files = [];

		// Prepend shared JS helpers (observer, carousel) so per-widget files can
		// call them. Use the standalone base handle, NOT sky-addons.min.js (which
		// is the full combined bundle and would double-include every widget's code).
		$base = SKY_ADDONS_ASSETS_PATH . 'js/sky-addons-base.min.js';
		if ( file_exists( $base ) ) {
			$files[] = $base;
		}

		$files = array_merge( $files, $this->get_script_files() );

		/**
		 * Filter the script files merged into the bundle.
		 */
		$files = apply_filters( 'sky-addons/optimization/assets/scripts', $files );

		if ( empty( $files ) ) {
			return false;
		}

		$minifier = new Minify\JS();

		foreach ( $files as $file ) {
			$minifier->add( $file );
		}

		$dir = self::get_upload_dir() . 'js';
		wp_mkdir_p( $dir );

		$minifier->minify( $dir . '/sky-addons.js' );

		return true;
	}

	/**
	 * Build all bundles and stamp a fresh version.
	 *
	 * Returns false (and skips writing) if the upload dir is not writable,
	 * if minification throws, or if the expected output files do not exist
	 * after the build. Each failure path records its reason so the admin
	 * notice can explain what happened.
	 *
	 * The optional $on_progress callable is invoked at every phase boundary
	 * with `( string $phase, int $percent )`. Phases:
	 *   collecting (0) → minify_css (20) → minify_css_rtl (45) →
	 *   minify_js (65) → verifying (90) → done (100).
	 *
	 * @param callable|null $on_progress Progress reporter.
	 * @return bool
	 */
	public function generate( $on_progress = null ) {
		$report = function ( $phase, $percent ) use ( $on_progress ) {
			if ( is_callable( $on_progress ) ) {
				call_user_func( $on_progress, $phase, (int) $percent );
			}
		};

		$report( 'collecting', 0 );

		if ( ! self::is_upload_writable() ) {
			self::record_failure( 'upload_unwritable' );
			return false;
		}

		// Another plugin may already ship matthiasmullie/minify — reuse it to
		// avoid a "Cannot declare class" fatal from a duplicate definition.
		if ( ! class_exists( '\MatthiasMullie\Minify\Minify' ) ) {
			require_once __DIR__ . '/autoload.php';
		}

		// Autoload bails silently when the bundled vendor tree is incomplete;
		// guard against that here so we record a clean failure instead of
		// crashing later inside minify_css().
		if ( ! class_exists( '\MatthiasMullie\Minify\Minify' ) ) {
			$minify_entry = __DIR__ . '/vendor/matthiasmullie/minify/src/Minify.php';
			if ( ! file_exists( $minify_entry ) ) {
				$vendor_msg = 'matthiasmullie/minify vendor files are missing from the server. The plugin may have been installed without them (partial upload or zip that excluded vendor/). Re-upload the full plugin zip.';
			} elseif ( ! is_readable( $minify_entry ) ) {
				$vendor_msg = 'matthiasmullie/minify exists but PHP cannot read it (file permission issue). Check permissions on: includes/optimizer/vendor/';
			} else {
				$vendor_msg = 'matthiasmullie/minify could not be loaded. Reinstall the plugin.';
			}
			self::record_failure( 'vendor_missing', $vendor_msg );
			return false;
		}

		$report( 'collecting', 10 );

		try {
			$report( 'minify_css', 20 );
			$this->minify_css( false );

			$report( 'minify_css_rtl', 45 );
			$this->minify_css( true );

			$report( 'minify_js', 65 );
			$this->minify_js();
		} catch ( \Throwable $e ) {
			self::record_failure( 'minify_failed', $e->getMessage() );
			return false;
		}

		$report( 'verifying', 90 );

		$dir = self::get_upload_dir();
		if ( ! file_exists( $dir . 'css/sky-addons.css' ) || ! file_exists( $dir . 'js/sky-addons.js' ) ) {
			self::record_failure( 'no_files' );
			return false;
		}

		update_option( 'sky_addons_minified_asset_version', (string) time() );
		delete_option( 'sky_addons_optimizer_status' );

		$report( 'done', 100 );

		return true;
	}

	/**
	 * Persist the reason the last generate() call failed so the admin notice
	 * and the dashboard can surface it.
	 *
	 * @param string $reason  One of: upload_unwritable, minify_failed, no_files.
	 * @param string $message Optional exception message for diagnostics.
	 */
	private static function record_failure( $reason, $message = '' ) {
		update_option(
			'sky_addons_optimizer_status',
			[
				'failed'  => true,
				'reason'  => $reason,
				'message' => $message,
				'time'    => time(),
			],
			false
		);
	}

	/**
	 * Inspect the generated bundle — paths, file sizes and build time.
	 *
	 * @return array
	 */
	public function get_bundle_info() {
		$dir     = self::get_upload_dir();
		$url     = self::get_upload_url();
		$version = get_option( 'sky_addons_minified_asset_version', '' );

		$map = [
			'css'     => 'css/sky-addons.css',
			'css_rtl' => 'css/sky-addons.rtl.css',
			'js'      => 'js/sky-addons.js',
		];

		$info = [
			'generated'   => $version ? (int) $version : 0,
			'location'    => str_replace( ABSPATH, '', $dir ),
			'files'       => [],
			'total_bytes' => 0,
			'write_error' => ! self::is_upload_writable(),
		];

		foreach ( $map as $key => $rel ) {
			if ( file_exists( $dir . $rel ) ) {
				$bytes                 = (int) filesize( $dir . $rel );
				$info['files'][ $key ] = [
					'url'   => $url . $rel,
					'bytes' => $bytes,
				];
				// RTL is an alternative to LTR — both never load together, so exclude from total.
				if ( 'css_rtl' !== $key ) {
					$info['total_bytes'] += $bytes;
				}
			}
		}

		return $info;
	}

	/**
	 * Remove generated bundles and clear the version stamp.
	 */
	public function clear() {
		$dir = self::get_upload_dir();

		foreach ( [ 'css/sky-addons.css', 'css/sky-addons.rtl.css', 'js/sky-addons.js' ] as $file ) {
			if ( file_exists( $dir . $file ) ) {
				wp_delete_file( $dir . $file );
			}
		}

		delete_option( 'sky_addons_minified_asset_version' );
		delete_option( 'sky_addons_optimizer_status' );
	}
}

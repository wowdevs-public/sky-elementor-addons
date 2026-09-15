<?php
/**
 * Data sources for the Table widget.
 *
 * One entry point — {@see Data_Source::get()} — turns a settings array into a
 * `[ 'head' => [], 'body' => [] ]` matrix, whatever the source is.
 *
 * Sources: the row repeater, pasted HTML rows, a Media Library CSV, a remote CSV
 * URL, and a public Google Sheet.
 *
 * The last two make outbound HTTP requests — see readme.txt for the external
 * services disclosure that wordpress.org requires. Both go through
 * {@see Data_Source::fetch()}, which is the only place in this plugin that talks
 * to the network.
 *
 * `sky-addons/table/sources`, `sky-addons/table/source-controls` and
 * `sky-addons/table/data` remain as extension points so further sources (ACF,
 * WooCommerce, a WP_Query) can be added without touching the widget.
 *
 * @package Sky_Addons
 */

namespace Sky_Addons\Modules\Table;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Data_Source {

	/**
	 * Transient prefix. The rest of the key hashes the fully built URL, so two
	 * widgets reading different ranges of the same sheet never collide.
	 */
	const CACHE_PREFIX = 'sky_table_';

	/**
	 * Google Sheets "visualization" CSV export. Works on any sheet shared with
	 * "anyone with the link" — no API key, no Cloud console, no quota.
	 */
	const SHEET_ENDPOINT = 'https://docs.google.com/spreadsheets/d/%s/gviz/tq?tqx=out:csv';

	/**
	 * Resolve a table matrix for the given widget settings.
	 *
	 * @param array $settings Widget settings.
	 * @return array{head: array, body: array}
	 */
	public static function get( $settings ) {
		$source = isset( $settings['table_source'] ) ? $settings['table_source'] : 'repeater';

		if ( 'repeater' === $source ) {
			return self::empty_matrix();
		}

		if ( 'csv' === $source ) {
			return self::parse(
				self::read_attachment( $settings ),
				self::delimiter( $settings ),
				self::is_on( $settings, 'csv_has_header' )
			);
		}

		if ( 'html' === $source ) {
			return Html_Source::parse( isset( $settings['html_rows'] ) ? $settings['html_rows'] : '' );
		}

		if ( 'csv_url' === $source || 'google_sheet' === $source ) {
			return self::from_remote( $settings, $source );
		}

		/**
		 * Resolve rows for a source this plugin does not handle itself.
		 *
		 * Return a `[ 'head' => [], 'body' => [] ]` matrix to take over, or null
		 * to leave the table empty.
		 *
		 * @param array|null $matrix   Resolved matrix, or null when unhandled.
		 * @param array      $settings Widget settings.
		 * @param string     $source   The selected source key.
		 */
		$matrix = apply_filters( 'sky-addons/table/data', null, $settings, $source );

		return isset( $matrix['head'], $matrix['body'] ) ? $matrix : self::empty_matrix();
	}

	/**
	 * Source options shown in the widget's Source dropdown.
	 *
	 * @return array<string, string>
	 */
	public static function sources() {
		$sources = [
			'repeater'     => esc_html__( 'Manual (Repeater)', 'sky-elementor-addons' ),
			'html'         => esc_html__( 'HTML Rows', 'sky-elementor-addons' ),
			'csv'          => esc_html__( 'CSV File', 'sky-elementor-addons' ),
			'csv_url'      => esc_html__( 'CSV URL (Remote)', 'sky-elementor-addons' ),
			'google_sheet' => esc_html__( 'Google Sheet', 'sky-elementor-addons' ),
		];

		/**
		 * Filter the Table widget's available data sources.
		 *
		 * Adding a key here is only half the job — also hook
		 * `sky-addons/table/data` to resolve rows for it.
		 *
		 * @param array $sources Source key => label.
		 */
		return apply_filters( 'sky-addons/table/sources', $sources );
	}

	/**
	 * Parse a CSV body into head + body rows.
	 *
	 * Uses a stream + fgetcsv rather than exploding on newlines, so quoted fields
	 * containing line breaks survive intact.
	 *
	 * Public so extensions that fetch CSV from elsewhere can reuse it.
	 *
	 * @param string $raw        CSV body.
	 * @param string $delimiter  Field delimiter.
	 * @param bool   $has_header Treat the first row as column names.
	 * @return array{head: array, body: array}
	 */
	public static function parse( $raw, $delimiter = ',', $has_header = true ) {
		$raw = trim( (string) $raw );

		// Excel and Google Sheets both prepend a UTF-8 BOM, which would otherwise
		// end up glued to the first column heading.
		if ( 0 === strpos( $raw, "\xEF\xBB\xBF" ) ) {
			$raw = substr( $raw, 3 );
		}

		if ( '' === $raw ) {
			return self::empty_matrix();
		}

		// php://temp is an in-memory stream, not a file — WP_Filesystem has no equivalent,
		// and str_getcsv() can't handle line breaks inside quoted cells.
		// phpcs:disable WordPress.WP.AlternativeFunctions.file_system_operations_fopen, WordPress.WP.AlternativeFunctions.file_system_operations_fwrite, WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		$rows   = [];
		$stream = fopen( 'php://temp', 'r+' );

		if ( ! $stream ) {
			return self::empty_matrix();
		}

		fwrite( $stream, $raw );
		rewind( $stream );

		// Enclosure and escape passed explicitly — PHP 8.4 deprecates relying on
		// the default escape character.
		while ( false !== ( $row = fgetcsv( $stream, 0, $delimiter, '"', '\\' ) ) ) {
			// fgetcsv yields [ null ] for a blank line.
			if ( [ null ] === $row ) {
				continue;
			}

			$rows[] = array_map( 'trim', array_map( 'strval', $row ) );
		}

		fclose( $stream );
		// phpcs:enable WordPress.WP.AlternativeFunctions.file_system_operations_fopen, WordPress.WP.AlternativeFunctions.file_system_operations_fwrite, WordPress.WP.AlternativeFunctions.file_system_operations_fclose

		if ( empty( $rows ) ) {
			return self::empty_matrix();
		}

		// Keep interior empty header cells. Dropping them would shorten the head
		// while the body kept its width, shearing every column after the blank one.
		$head = $has_header ? array_map( 'strval', (array) array_shift( $rows ) ) : [];
		$body = self::pad( self::cap( $rows ), max( count( $head ), self::widest( $rows ) ) );

		return self::trim_empty_columns( $head, $body );
	}

	/**
	 * Drop trailing columns that are empty in the header *and* in every row.
	 *
	 * Google Sheets' CSV export returns the full grid width, so a six-column sheet
	 * commonly arrives with a dozen blank columns attached. Head and body are
	 * trimmed together — trimming one alone is what shears the table.
	 *
	 * @param array $head Header cells.
	 * @param array $body Body rows.
	 * @return array{head: array, body: array}
	 */
	private static function trim_empty_columns( $head, $body ) {
		$width = max( count( $head ), self::widest( $body ) );

		for ( $column = $width - 1; $column >= 0; $column-- ) {
			if ( isset( $head[ $column ] ) && '' !== $head[ $column ] ) {
				break;
			}

			$empty = true;

			foreach ( $body as $row ) {
				if ( isset( $row[ $column ] ) && '' !== $row[ $column ] ) {
					$empty = false;
					break;
				}
			}

			if ( ! $empty ) {
				break;
			}

			$width = $column;
		}

		if ( $width < 1 ) {
			return self::empty_matrix();
		}

		$head = array_slice( $head, 0, $width );

		foreach ( $body as $index => $row ) {
			$body[ $index ] = array_slice( $row, 0, $width );
		}

		return [
			'head' => $head,
			'body' => $body,
		];
	}

	/**
	 * Fetch and parse a remote source.
	 *
	 * @param array  $settings Widget settings.
	 * @param string $source   Source key.
	 * @return array{head: array, body: array}
	 */
	private static function from_remote( $settings, $source ) {
		$url = 'google_sheet' === $source
			? self::sheet_url( $settings )
			: self::value( $settings, 'csv_url' );

		if ( '' === $url ) {
			return self::empty_matrix();
		}

		// A Google Sheet always comes back comma-separated; a user's own CSV may
		// not, so only that one honours the delimiter control.
		$delimiter = 'google_sheet' === $source ? ',' : self::delimiter( $settings );

		return self::parse(
			self::fetch( $url, self::ttl( $settings ) ),
			$delimiter,
			self::is_on( $settings, 'csv_has_header' )
		);
	}

	/**
	 * Build the gviz CSV endpoint for the configured sheet.
	 *
	 * @param array $settings Widget settings.
	 * @return string
	 */
	private static function sheet_url( $settings ) {
		$sheet_id = self::sheet_id( self::value( $settings, 'sheet_url' ) );

		if ( '' === $sheet_id ) {
			return '';
		}

		$url   = sprintf( self::SHEET_ENDPOINT, rawurlencode( $sheet_id ) );
		$sheet = self::value( $settings, 'sheet_name' );
		$range = self::value( $settings, 'sheet_range' );

		if ( '' !== $sheet ) {
			$url = add_query_arg( 'sheet', rawurlencode( $sheet ), $url );
		}

		if ( '' !== $range ) {
			$url = add_query_arg( 'range', rawurlencode( $range ), $url );
		}

		return $url;
	}

	/**
	 * Pull a remote body, cached.
	 *
	 * The only outbound request this plugin makes. Never bypasses or deletes the
	 * cache on read — a miss writes, a hit returns, nothing else.
	 *
	 * @param string $url Remote URL.
	 * @param int    $ttl Cache lifetime in seconds. 0 disables caching.
	 * @return string
	 */
	private static function fetch( $url, $ttl ) {
		if ( ! wp_http_validate_url( $url ) ) {
			return '';
		}

		$key    = self::CACHE_PREFIX . md5( $url );
		$cached = $ttl > 0 ? get_transient( $key ) : false;

		if ( is_string( $cached ) ) {
			return $cached;
		}

		// wp_safe_remote_get() — blocks internal hosts, so a URL field can't be
		// turned into a request against the site's own network.
		$response = wp_safe_remote_get(
			$url,
			[
				'timeout'     => 15,
				'redirection' => 3,
			]
		);

		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return '';
		}

		$body = wp_remote_retrieve_body( $response );

		if ( $ttl > 0 ) {
			set_transient( $key, $body, $ttl );
		}

		return $body;
	}

	/**
	 * Extract a spreadsheet ID from a full Google Sheets URL, or accept a bare ID.
	 *
	 * @param string $value Sheet URL or ID.
	 * @return string
	 */
	private static function sheet_id( $value ) {
		if ( preg_match( '#/spreadsheets/d/([a-zA-Z0-9_-]+)#', $value, $matches ) ) {
			return $matches[1];
		}

		return preg_match( '#^[a-zA-Z0-9_-]{20,}$#', $value ) ? $value : '';
	}

	/**
	 * Cache lifetime in seconds.
	 *
	 * @param array $settings Widget settings.
	 * @return int
	 */
	private static function ttl( $settings ) {
		$ttl = isset( $settings['remote_cache_ttl'] ) ? absint( $settings['remote_cache_ttl'] ) : HOUR_IN_SECONDS;

		/**
		 * Filter the Table widget's remote-source cache lifetime.
		 *
		 * @param int   $ttl      Lifetime in seconds. 0 disables caching.
		 * @param array $settings Widget settings.
		 */
		return (int) apply_filters( 'sky-addons/table/cache-ttl', $ttl, $settings );
	}

	/**
	 * Trimmed string setting.
	 *
	 * @param array  $settings Widget settings.
	 * @param string $key      Setting key.
	 * @return string
	 */
	private static function value( $settings, $key ) {
		return isset( $settings[ $key ] ) ? trim( (string) $settings[ $key ] ) : '';
	}

	/**
	 * Read a CSV attachment off disk. Local read — no HTTP round trip.
	 *
	 * @param array $settings Widget settings.
	 * @return string
	 */
	private static function read_attachment( $settings ) {
		$attachment_id = isset( $settings['csv_file']['id'] ) ? absint( $settings['csv_file']['id'] ) : 0;

		// The control's default is the bundled sample, which has a URL but no
		// attachment ID, so fall back to resolving that.
		$path = $attachment_id ? get_attached_file( $attachment_id ) : self::bundled_path( $settings );

		if ( ! $path || ! is_readable( $path ) ) {
			return '';
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		return (string) file_get_contents( $path );
	}

	/**
	 * Map a media URL to a path on disk, but **only** inside this plugin's own
	 * assets directory. Anything else returns empty, so an arbitrary URL in the
	 * control can never be turned into a file-read primitive.
	 *
	 * @param array $settings Widget settings.
	 * @return string
	 */
	private static function bundled_path( $settings ) {
		$url = isset( $settings['csv_file']['url'] ) ? (string) $settings['csv_file']['url'] : '';
		$url = strtok( $url, '?' );

		if ( '' === $url || 0 !== strpos( $url, SKY_ADDONS_ASSETS_URL ) ) {
			return '';
		}

		$relative = substr( $url, strlen( SKY_ADDONS_ASSETS_URL ) );

		if ( false !== strpos( $relative, '..' ) || ! preg_match( '/\.(csv|txt|tsv)$/i', $relative ) ) {
			return '';
		}

		return SKY_ADDONS_PATH . 'assets/' . $relative;
	}

	/**
	 * Every row is rendered into the page, so an accidental 50,000-row file would
	 * ship 50,000 rows of markup. Cap it.
	 *
	 * @param array $rows Body rows.
	 * @return array
	 */
	public static function cap( $rows ) {
		/**
		 * Filter the maximum number of rows a file or remote source may render.
		 *
		 * @param int $max Row ceiling.
		 */
		$max = (int) apply_filters( 'sky-addons/table/max-rows', 2000 );

		return $max > 0 && count( $rows ) > $max ? array_slice( $rows, 0, $max ) : $rows;
	}

	/**
	 * Pad short rows so every row has the same cell count. Spreadsheets omit
	 * trailing empty cells, which would otherwise shear the columns.
	 *
	 * @param array $rows  Body rows.
	 * @param int   $width Target column count.
	 * @return array
	 */
	private static function pad( $rows, $width ) {
		if ( $width < 1 ) {
			return $rows;
		}

		foreach ( $rows as $index => $row ) {
			$rows[ $index ] = array_pad( array_slice( $row, 0, $width ), $width, '' );
		}

		return $rows;
	}

	/**
	 * Widest row in the set.
	 *
	 * @param array $rows Body rows.
	 * @return int
	 */
	private static function widest( $rows ) {
		$width = 0;

		foreach ( $rows as $row ) {
			$width = max( $width, count( $row ) );
		}

		return $width;
	}

	/**
	 * Resolve the field delimiter, translating the tab token.
	 *
	 * @param array $settings Widget settings.
	 * @return string
	 */
	public static function delimiter( $settings ) {
		$delimiter = isset( $settings['csv_delimiter'] ) && '' !== $settings['csv_delimiter']
			? $settings['csv_delimiter']
			: ',';

		return 'tab' === $delimiter ? "\t" : substr( $delimiter, 0, 1 );
	}

	/**
	 * Empty matrix in the shape callers expect.
	 *
	 * @return array{head: array, body: array}
	 */
	public static function empty_matrix() {
		return [
			'head' => [],
			'body' => [],
		];
	}

	/**
	 * Switcher helper.
	 *
	 * @param array  $settings Widget settings.
	 * @param string $key      Setting key.
	 * @return bool
	 */
	public static function is_on( $settings, $key ) {
		return isset( $settings[ $key ] ) && 'yes' === $settings[ $key ];
	}
}

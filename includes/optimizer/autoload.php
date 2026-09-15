<?php
/**
 * Manual autoloader for the bundled matthiasmullie/minify library.
 *
 * @package Sky_Addons
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$sky_addons_minify_path = __DIR__ . '/vendor/matthiasmullie';

// Bail if the bundled vendor tree is incomplete (partial copy / corrupt
// upload). Caller checks class_exists() and records a clean failure.
if ( ! is_readable( $sky_addons_minify_path . '/minify/src/Minify.php' ) ) {
	return;
}

require_once $sky_addons_minify_path . '/path-converter/src/ConverterInterface.php';
require_once $sky_addons_minify_path . '/path-converter/src/Converter.php';
require_once $sky_addons_minify_path . '/path-converter/src/NoConverter.php';

require_once $sky_addons_minify_path . '/minify/src/Exception.php';
require_once $sky_addons_minify_path . '/minify/src/Exceptions/BasicException.php';
require_once $sky_addons_minify_path . '/minify/src/Exceptions/FileImportException.php';
require_once $sky_addons_minify_path . '/minify/src/Exceptions/IOException.php';
require_once $sky_addons_minify_path . '/minify/src/Minify.php';
require_once $sky_addons_minify_path . '/minify/src/CSS.php';
require_once $sky_addons_minify_path . '/minify/src/JS.php';

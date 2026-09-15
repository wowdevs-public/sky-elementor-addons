<?php
/**
 * Templates Library subsystem entry point.
 *
 * Single place that loads the Templates Library classes. Required from
 * plugin.php; the library is only *activated* (instances booted) there when
 * the `templates-library` advanced feature is active.
 *
 * @package Sky_Addons
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/Init_Templates.php';
require_once __DIR__ . '/Import_Template.php';
require_once __DIR__ . '/Library_Api.php';
require_once __DIR__ . '/Load_Template.php';

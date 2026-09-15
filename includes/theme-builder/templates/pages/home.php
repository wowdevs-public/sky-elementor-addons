<?php
/**
 * Sky Addons Theme Builder — Blog index template.
 *
 * Swapped in by Theme_Builder::set_builder_template() when a matching
 * "home" template is enabled. Copy this file into your theme only if you
 * need markup around the builder content; the content itself is rendered by
 * Theme_Builder::render_template().
 *
 * @package Sky_Addons
 */

defined( 'ABSPATH' ) || exit;

get_header();

\Sky_Addons\ThemeBuilder\Theme_Builder::render_template( 'home' );

get_footer();

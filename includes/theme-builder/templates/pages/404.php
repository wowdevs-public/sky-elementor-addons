<?php
defined( 'ABSPATH' ) || exit;

/**
 * The Template for displaying product archives, including the main shop page which is a post type archive
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/archive-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.4.0
 */

get_header();

do_action( 'wowdevs_themes_builder_template_before_main_content' );

if ( class_exists( 'Elementor\Plugin' ) ) {
	$templates = \Sky_Addons\ThemeBuilder\Theme_Builder::template_ids();
	if ( isset( $templates['404'] ) && ! empty( $templates['404'] ) ) {
    //phpcs:ignore
		echo wowdevs_render_elementor_content( $templates['404'] );
	}
}

do_action( 'wowdevs_themes_builder_template_after_main_content' );
get_footer();

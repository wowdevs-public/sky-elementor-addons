<?php

defined( 'ABSPATH' ) || exit;

get_header();

if ( class_exists( 'Elementor\Plugin' ) ) {
	$templates = \Sky_Addons\ThemeBuilder\Theme_Builder::template_ids();
	if ( isset( $templates['author'] ) && ! empty( $templates['author'] ) ) {
    //phpcs:ignore
		echo wowdevs_render_elementor_content( $templates['author'] );
	}
}


get_footer();

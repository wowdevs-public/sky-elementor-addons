<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

return [
	'title'              => esc_html__( 'Post Featured Image', 'sky-elementor-addons' ),
	'required'           => true,
	'default_activation' => true,
];

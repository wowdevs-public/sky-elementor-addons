<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return [
	'title'              => esc_html__( 'Video Player', 'sky-elementor-addons' ),
	'required'           => true,
	'default_activation' => true,
	'has_style'          => true,
	'has_script'         => true,
];

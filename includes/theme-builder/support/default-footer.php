<?php do_action( 'wowdevs_themes_builder_template_before_footer' ); ?>
<div class="wowdevs-template-content-markup wowdevs-template-content-footer wowdevs-template-content-theme-support">
	<?php
	$templates = \Sky_Addons\ThemeBuilder\Theme_Builder::template_ids();
	if ( isset( $templates['footer'] ) && ! empty( $templates['footer'] ) ) {
    //phpcs:ignore
		echo wowdevs_render_elementor_content( $templates['footer'] );
	}
	?>
</div>
<?php do_action( 'wowdevs_themes_builder_template_after_footer' ); ?>
<?php wp_footer(); ?>

</body>

</html>

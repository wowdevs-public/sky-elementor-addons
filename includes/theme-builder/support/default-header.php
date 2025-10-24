<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<?php if ( ! current_theme_supports( 'title-tag' ) ) : ?>
		<title>
			<?php echo esc_html( wp_get_document_title() ); ?>
		</title>
	<?php endif; ?>
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
	<?php wp_body_open(); ?>
	<?php do_action( 'wowdevs_themes_builder_template_before_header' ); ?>
	<div class="wowdevs-template-content-markup wowdevs-template-content-header wowdevs-template-content-theme-support">
		<?php
		$templates = \Sky_Addons\ThemeBuilder\Theme_Builder::template_ids();
		if ( isset( $templates['header'] ) && ! empty( $templates['header'] ) ) {
      //phpcs:ignore
			echo wowdevs_render_elementor_content( $templates['header'] );
		}
		?>
	</div>
	<?php do_action( 'wowdevs_themes_builder_template_after_header' ); ?>

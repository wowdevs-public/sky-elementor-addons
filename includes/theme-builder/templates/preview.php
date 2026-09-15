<?php
/**
 * Live preview shell for a Theme Builder template.
 *
 * Deliberately not Elementor's canvas.php. Canvas renders through the loop and
 * `the_content()`, which resolves the document from the global post — so the
 * global post has to stay the template, and a `single` template can never be
 * shown against a sample post. Here the document is printed by explicit ID
 * instead, which frees the globals to carry the preview context.
 *
 * @package Sky_Addons
 */

defined( 'ABSPATH' ) || exit;

$sky_preview_template_id = (int) get_queried_object_id();

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<?php if ( ! current_theme_supports( 'title-tag' ) ) : ?>
		<title><?php echo esc_html( wp_get_document_title() ); ?></title>
	<?php endif; ?>
	<?php wp_head(); ?>
</head>

<body <?php body_class( 'sky-tb-live-preview' ); ?>>
	<?php wp_body_open(); ?>
	<div class="wowdevs-template-content-markup sky-tb-live-preview__content">
		<?php \Sky_Addons\ThemeBuilder\Live_Preview::instance()->render_template( $sky_preview_template_id ); ?>
	</div>
	<?php wp_footer(); ?>
</body>

</html>

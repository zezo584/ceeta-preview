<?php
/**
 * CEETA Petroleum & Engineering — theme bootstrap.
 *
 * @package ceeta
 */

defined( 'ABSPATH' ) || exit;

define( 'CEETA_VER', '1.0.1' );
define( 'CEETA_DIR', get_template_directory() );
define( 'CEETA_URI', get_template_directory_uri() );

require CEETA_DIR . '/inc/core.php';       // helpers, language routing, assets.
require CEETA_DIR . '/inc/template.php';   // shared template tags.
require CEETA_DIR . '/inc/setup.php';      // page creation on activation.
require CEETA_DIR . '/inc/customizer.php'; // theme options.
require CEETA_DIR . '/inc/seo.php';        // meta, Open Graph, hreflang, JSON-LD.
require CEETA_DIR . '/inc/sitemap.php';    // bilingual XML sitemap + robots.txt.
require CEETA_DIR . '/inc/forms.php';      // inquiry form handler + Inquiries inbox.
require CEETA_DIR . '/inc/gallery.php';    // training gallery meta box.

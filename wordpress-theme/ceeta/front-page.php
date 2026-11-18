<?php
/**
 * Front page (English at /, Arabic at /ar/).
 *
 * @package ceeta
 */

defined( 'ABSPATH' ) || exit;

get_header();
ceeta_render( 'home' );
get_footer();

<?php
/**
 * Bilingual XML sitemap at /ceeta-sitemap.xml (with hreflang) + robots.txt entry.
 *
 * @package ceeta
 */

defined( 'ABSPATH' ) || exit;

/* The theme sitemap covers both languages, so the core one (English only) is switched off. */
add_filter( 'wp_sitemaps_enabled', '__return_false' );

add_action(
	'template_redirect',
	function () {
		if ( ! get_query_var( 'ceeta_sitemap' ) ) {
			return;
		}
		$pages = ceeta_data( 'pages' );
		header( 'Content-Type: application/xml; charset=UTF-8' );
		echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
		echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";
		foreach ( $pages as $p ) {
			$is_svc = 0 === strpos( $p['key'], 'service-' );
			$key    = $is_svc ? 'service' : $p['key'];
			$slug   = $is_svc ? substr( $p['key'], 8 ) : '';
			$en     = ceeta_url( $key, $slug, 'en' );
			$ar     = ceeta_url( $key, $slug, 'ar' );
			$id     = ceeta_page_id( $p['key'] );
			$mod    = $id ? get_post_modified_time( 'c', true, $id ) : '';
			foreach ( array( $en, $ar ) as $loc ) {
				echo '<url><loc>' . esc_url( $loc ) . '</loc>';
				echo '<xhtml:link rel="alternate" hreflang="en" href="' . esc_url( $en ) . '"/>';
				echo '<xhtml:link rel="alternate" hreflang="ar" href="' . esc_url( $ar ) . '"/>';
				echo '<xhtml:link rel="alternate" hreflang="x-default" href="' . esc_url( $en ) . '"/>';
				if ( $mod ) {
					echo '<lastmod>' . esc_html( $mod ) . '</lastmod>';
				}
				echo '<priority>' . ( 'home' === $p['key'] ? '1.0' : '0.8' ) . '</priority></url>' . "\n";
			}
		}
		$posts = get_posts( array( 'numberposts' => 500, 'post_status' => 'publish' ) );
		foreach ( $posts as $post ) {
			echo '<url><loc>' . esc_url( get_permalink( $post ) ) . '</loc><lastmod>' . esc_html( get_post_modified_time( 'c', true, $post ) ) . '</lastmod><priority>0.6</priority></url>' . "\n";
		}
		echo '</urlset>';
		exit;
	}
);

add_filter(
	'robots_txt',
	function ( $output, $public ) {
		if ( $public ) {
			$output .= "\nSitemap: " . esc_url( home_url( '/ceeta-sitemap.xml' ) ) . "\n";
		}
		return $output;
	},
	10,
	2
);

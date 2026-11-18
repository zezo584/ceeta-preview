<?php
/**
 * Built-in SEO: titles, meta descriptions, canonical, hreflang, Open Graph, Twitter cards, JSON-LD.
 *
 * @package ceeta
 */

defined( 'ABSPATH' ) || exit;

/** SEO entry (title, desc, schema) for the current theme page, or null. */
function ceeta_seo_entry() {
	$key = ceeta_current_key();
	if ( ! $key ) {
		return null;
	}
	$data = ceeta_data( 'seo-data' );
	$lang = ceeta_lang();
	if ( ! isset( $data[ $lang ][ $key ] ) ) {
		return null;
	}
	$e = $data[ $lang ][ $key ];
	if ( 'home' === $key ) {
		$t = ceeta_opt( 'seo_title_' . $lang );
		$s = ceeta_opt( 'seo_desc_' . $lang );
		if ( $t ) {
			$e['title'] = $t;
		}
		if ( $s ) {
			$e['desc'] = $s;
		}
	}
	return $e;
}

/** Canonical URL of the current theme page. */
function ceeta_canonical() {
	return ceeta_alt_url( ceeta_lang() );
}

/* Title tag. */
add_filter(
	'pre_get_document_title',
	function ( $title ) {
		$e = ceeta_seo_entry();
		return $e ? $e['title'] : $title;
	},
	99
);

/* Avoid duplicate tags from WordPress core / SEO plugins on theme pages. */
add_action(
	'wp',
	function () {
		if ( ! ceeta_current_key() ) {
			return;
		}
		remove_action( 'wp_head', 'rel_canonical' );
		remove_action( 'wp_head', 'wp_shortlink_wp_head' );
		add_filter( 'wpseo_frontend_presenters', '__return_empty_array' ); // Yoast.
		remove_all_actions( 'rank_math/head' ); // Rank Math.
	}
);

add_action( 'wp_head', 'ceeta_seo_head', 1 );

/** Print meta tags and structured data. */
function ceeta_seo_head() {
	$gsc = ceeta_opt( 'gsc' );
	if ( $gsc ) {
		printf( '<meta name="google-site-verification" content="%s">' . "\n", esc_attr( $gsc ) );
	}

	$e    = ceeta_seo_entry();
	$lang = ceeta_lang();
	$home = untrailingslashit( home_url() );
	$og   = CEETA_URI . '/assets/img/og-image.jpg';

	if ( ! $e ) {
		// Blog posts & other WordPress pages: basic description + Open Graph.
		if ( is_singular() ) {
			$desc = wp_strip_all_tags( get_the_excerpt() );
			if ( $desc ) {
				printf( '<meta name="description" content="%s">' . "\n", esc_attr( wp_trim_words( $desc, 30, '…' ) ) );
			}
			$img = has_post_thumbnail() ? get_the_post_thumbnail_url( null, 'large' ) : $og;
			printf( '<meta property="og:type" content="article"><meta property="og:title" content="%s"><meta property="og:url" content="%s"><meta property="og:image" content="%s">' . "\n", esc_attr( get_the_title() ), esc_url( get_permalink() ), esc_url( $img ) );
		}
		return;
	}

	$url = ceeta_canonical();
	$en  = ceeta_alt_url( 'en' );
	$ar  = ceeta_alt_url( 'ar' );

	printf( '<meta name="description" content="%s">' . "\n", esc_attr( $e['desc'] ) );
	printf( '<link rel="canonical" href="%s">' . "\n", esc_url( $url ) );
	printf( '<link rel="alternate" hreflang="en" href="%s">' . "\n", esc_url( $en ) );
	printf( '<link rel="alternate" hreflang="ar" href="%s">' . "\n", esc_url( $ar ) );
	printf( '<link rel="alternate" hreflang="x-default" href="%s">' . "\n", esc_url( $en ) );
	printf( '<meta property="og:type" content="website">' . "\n" );
	printf( '<meta property="og:site_name" content="CEETA">' . "\n" );
	printf( '<meta property="og:title" content="%s">' . "\n", esc_attr( $e['title'] ) );
	printf( '<meta property="og:description" content="%s">' . "\n", esc_attr( $e['desc'] ) );
	printf( '<meta property="og:url" content="%s">' . "\n", esc_url( $url ) );
	printf( '<meta property="og:image" content="%s"><meta property="og:image:width" content="1200"><meta property="og:image:height" content="630">' . "\n", esc_url( $og ) );
	printf( '<meta property="og:locale" content="%s"><meta property="og:locale:alternate" content="%s">' . "\n", 'ar' === $lang ? 'ar_EG' : 'en_US', 'ar' === $lang ? 'en_US' : 'ar_EG' );
	printf( '<meta name="twitter:card" content="summary_large_image"><meta name="twitter:title" content="%s"><meta name="twitter:description" content="%s"><meta name="twitter:image" content="%s">' . "\n", esc_attr( $e['title'] ), esc_attr( $e['desc'] ), esc_url( $og ) );

	$same = array_values( array_filter( array( ceeta_opt( 'instagram' ), ceeta_opt( 'facebook' ), ceeta_opt( 'linkedin' ) ) ) );
	$org  = array(
		'@type'         => array( 'Organization', 'ProfessionalService' ),
		'@id'           => $home . '/#organization',
		'name'          => 'CEETA for Petroleum and Engineering Services',
		'alternateName' => array( 'CEETA', 'سيتا', 'سيتا CEETA للخدمات البترولية والهندسية' ),
		'url'           => $home . '/',
		'logo'          => CEETA_URI . '/assets/img/ceeta-logo.png',
		'image'         => $og,
		'description'   => $e['desc'],
		'telephone'     => ceeta_opt( 'phone_tel' ),
		'email'         => ceeta_opt( 'email' ),
		'address'       => array(
			'@type'           => 'PostalAddress',
			'addressLocality' => 'Shibin El Kom',
			'addressRegion'   => 'Menoufia',
			'addressCountry'  => 'EG',
		),
		'areaServed'    => array( array( '@type' => 'Country', 'name' => 'Egypt' ), array( '@type' => 'Place', 'name' => 'Middle East' ) ),
		'sameAs'        => $same,
		'knowsAbout'    => array( 'Lifting inspection', 'Non-destructive testing', 'Tubular inspection', 'CAT III and CAT IV rig inspection', 'Rig auditing', 'Lifting planning', 'Dropped objects prevention', 'HSE training' ),
		'contactPoint'  => array(
			array(
				'@type'             => 'ContactPoint',
				'telephone'         => ceeta_opt( 'phone_tel' ),
				'contactType'       => 'customer service',
				'availableLanguage' => array( 'English', 'Arabic' ),
			),
		),
	);
	$graph = array(
		$org,
		array(
			'@type'      => 'WebSite',
			'@id'        => $home . '/#website',
			'url'        => $home . '/',
			'name'       => 'CEETA',
			'inLanguage' => array( 'en', 'ar' ),
			'publisher'  => array( '@id' => $home . '/#organization' ),
		),
		array(
			'@type'       => 'WebPage',
			'@id'         => $url . '#webpage',
			'url'         => $url,
			'name'        => $e['title'],
			'description' => $e['desc'],
			'inLanguage'  => $lang,
			'isPartOf'    => array( '@id' => $home . '/#website' ),
		),
	);
	if ( ! empty( $e['schema'] ) && is_array( $e['schema'] ) ) {
		$extra = wp_json_encode( $e['schema'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
		$extra = json_decode( str_replace( '{{HOME}}', $home, $extra ), true );
		if ( is_array( $extra ) ) {
			$graph = array_merge( $graph, $extra );
		}
	}
	echo '<script type="application/ld+json">' . wp_json_encode( array( '@context' => 'https://schema.org', '@graph' => $graph ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput
}

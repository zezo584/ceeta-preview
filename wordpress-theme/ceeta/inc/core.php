<?php
/**
 * Core helpers, bilingual routing (/ar/...), theme supports and assets.
 *
 * @package ceeta
 */

defined( 'ABSPATH' ) || exit;

/* ---------------------------------------------------------------- data */

/**
 * Load a JSON data file from /inc (seo-data, ui, pages).
 *
 * @param string $name File name without extension.
 * @return array
 */
function ceeta_data( $name ) {
	static $cache = array();
	if ( ! isset( $cache[ $name ] ) ) {
		$file           = CEETA_DIR . '/inc/' . $name . '.json';
		$cache[ $name ] = file_exists( $file ) ? json_decode( file_get_contents( $file ), true ) : array(); // phpcs:ignore WordPress.WP.AlternativeFunctions
		if ( ! is_array( $cache[ $name ] ) ) {
			$cache[ $name ] = array();
		}
	}
	return $cache[ $name ];
}

/** Default option values (editable in Appearance → Customize → CEETA Options). */
function ceeta_defaults() {
	return array(
		'email'         => 'services@ceeta-eps.com',
		'phone_display' => '+20 100 397 1910',
		'phone_tel'     => '+201003971910',
		'whatsapp'      => '201003971910',
		'address_en'    => 'Shibin El Kom, Menoufia, Egypt',
		'address_ar'    => 'شبين الكوم، المنوفية، مصر',
		'maps'          => 'https://www.google.com/maps/search/?api=1&query=Shibin+El+Kom+Menoufia+Egypt',
		'map_embed'     => 'https://www.google.com/maps?q=Shibin+El+Kom,+Menoufia,+Egypt&output=embed',
		'instagram'     => 'https://www.instagram.com/ceeta40/',
		'facebook'      => '',
		'linkedin'      => '',
		'form_email'    => 'services@ceeta-eps.com',
		'preloader'     => '1',
		'gsc'           => '',
		'seo_title_en'  => '',
		'seo_title_ar'  => '',
		'seo_desc_en'   => '',
		'seo_desc_ar'   => '',
	);
}

/**
 * Get a theme option.
 *
 * @param string $key Option key.
 * @return string
 */
function ceeta_opt( $key ) {
	$d = ceeta_defaults();
	return (string) get_theme_mod( 'ceeta_' . $key, isset( $d[ $key ] ) ? $d[ $key ] : '' );
}

/**
 * Echo a theme option, escaped for HTML attributes/text. Empty social links fall back to "#".
 *
 * @param string $key Option key.
 */
function ceeta_e( $key ) {
	$v = ceeta_opt( $key );
	if ( '' === $v && in_array( $key, array( 'facebook', 'linkedin', 'instagram' ), true ) ) {
		$v = '#';
	}
	echo esc_attr( $v );
}

/* ---------------------------------------------------------------- language */

/** Current language: "ar" for /ar/... URLs, otherwise "en". */
function ceeta_lang() {
	return ( 'ar' === get_query_var( 'ceeta_lang' ) ) ? 'ar' : 'en';
}

/** True on Arabic pages. */
function ceeta_is_ar() {
	return 'ar' === ceeta_lang();
}

/**
 * UI string in the current language.
 *
 * @param string $key String key.
 * @return string
 */
function ceeta_t( $key ) {
	$ui   = ceeta_data( 'ui' );
	$lang = ceeta_lang();
	return isset( $ui[ $lang ][ $key ] ) ? $ui[ $lang ][ $key ] : $key;
}

/** Register the ceeta_lang query var. */
add_filter(
	'query_vars',
	function ( $vars ) {
		$vars[] = 'ceeta_lang';
		$vars[] = 'ceeta_sitemap';
		return $vars;
	}
);

/** Rewrite rules: /ar/ → Arabic home, /ar/{page-path}/ → Arabic version of a page. */
function ceeta_register_rewrites() {
	add_rewrite_rule( '^ar/?$', 'index.php?ceeta_lang=ar', 'top' );
	add_rewrite_rule( '^ar/(.+?)/?$', 'index.php?ceeta_lang=ar&pagename=$matches[1]', 'top' );
	add_rewrite_rule( '^ceeta-sitemap\.xml$', 'index.php?ceeta_sitemap=1', 'top' );
}
add_action( 'init', 'ceeta_register_rewrites' );

/** /ar/ alone → show the static front page in Arabic. */
add_filter(
	'request',
	function ( $qv ) {
		if ( isset( $qv['ceeta_lang'] ) && 'ar' === $qv['ceeta_lang'] && 1 === count( $qv ) ) {
			$front = (int) get_option( 'page_on_front' );
			if ( $front ) {
				$qv['page_id'] = $front;
			}
		}
		return $qv;
	}
);

/** Never let WordPress "fix" Arabic URLs back to English ones. */
add_filter(
	'redirect_canonical',
	function ( $redirect ) {
		return ( 'ar' === get_query_var( 'ceeta_lang' ) ) ? false : $redirect;
	}
);

/* ---------------------------------------------------------------- pages & URLs */

/**
 * ID of a theme page (home, about, services, academy, certifications, contact, service-{slug}).
 *
 * @param string $key Page key.
 * @return int
 */
function ceeta_page_id( $key ) {
	$map = get_option( 'ceeta_pages', array() );
	return ( is_array( $map ) && isset( $map[ $key ] ) ) ? (int) $map[ $key ] : 0;
}

/**
 * URL of a theme page in a given language.
 *
 * @param string $key  Page key or "service".
 * @param string $slug Service slug when $key is "service".
 * @param string $lang "en" or "ar".
 * @return string
 */
function ceeta_url( $key, $slug = '', $lang = 'en' ) {
	$k      = ( 'service' === $key ) ? 'service-' . $slug : $key;
	$ar     = ( 'ar' === $lang );
	$pretty = (bool) get_option( 'permalink_structure' );

	if ( 'home' === $k ) {
		if ( ! $ar ) {
			return home_url( '/' );
		}
		return $pretty ? home_url( '/ar/' ) : add_query_arg( 'ceeta_lang', 'ar', home_url( '/' ) );
	}

	$id   = ceeta_page_id( $k );
	$path = $id ? get_page_uri( $id ) : ( 'service' === $key ? 'services/' . $slug : $key );

	if ( ! $ar ) {
		return $id ? get_permalink( $id ) : home_url( '/' . trailingslashit( $path ) );
	}
	if ( ! $pretty ) {
		return add_query_arg( 'ceeta_lang', 'ar', $id ? get_permalink( $id ) : home_url( '/' ) );
	}
	return home_url( '/ar/' . trailingslashit( $path ) );
}

/** Key of the page being viewed ('' when it is not a theme page). */
function ceeta_current_key() {
	if ( is_front_page() && ! is_home() ) {
		return 'home';
	}
	if ( ! is_page() ) {
		return '';
	}
	$id  = get_queried_object_id();
	$map = get_option( 'ceeta_pages', array() );
	if ( ! is_array( $map ) ) {
		return '';
	}
	$key = array_search( (int) $id, array_map( 'intval', $map ), true );
	return $key ? (string) $key : '';
}

/**
 * Echo " is-active" for the current menu item.
 *
 * @param string $key Menu key.
 */
function ceeta_active_class( $key ) {
	$cur   = ceeta_current_key();
	$group = ( 0 === strpos( $cur, 'service-' ) ) ? 'services' : $cur;
	if ( $group === $key ) {
		echo ' is-active';
	}
}

/**
 * Echo aria-current for the current page link.
 *
 * @param string $key Menu key.
 */
function ceeta_aria_current( $key ) {
	if ( ceeta_current_key() === $key ) {
		echo ' aria-current="page"';
	}
}

/**
 * URL of the current page in a given language (defaults to the other language).
 *
 * @param string|null $lang Target language.
 * @return string
 */
function ceeta_alt_url( $lang = null ) {
	$lang = $lang ? $lang : ( ceeta_is_ar() ? 'en' : 'ar' );
	$cur  = ceeta_current_key();
	if ( ! $cur ) {
		return ceeta_url( 'home', '', $lang );
	}
	if ( 0 === strpos( $cur, 'service-' ) ) {
		return ceeta_url( 'service', substr( $cur, 8 ), $lang );
	}
	return ceeta_url( $cur, '', $lang );
}

/**
 * Image URL from a Customizer image setting, with a theme default.
 *
 * @param string $mod Setting suffix (e.g. hero_1).
 * @param string $rel Default path relative to /assets.
 * @return string
 */
function ceeta_img_mod( $mod, $rel ) {
	$url = get_theme_mod( 'ceeta_' . $mod );
	return $url ? $url : CEETA_URI . '/assets/' . $rel;
}

/** Contact page map iframe (Customizer → Map embed URL). */
function ceeta_map_iframe() {
	$src = ceeta_opt( 'map_embed' );
	if ( $src ) {
		printf(
			'<iframe src="%s" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="%s" allowfullscreen></iframe>',
			esc_url( $src ),
			esc_attr( ceeta_t( 'find_us' ) )
		);
	}
}

/**
 * Render a theme page partial for the current language.
 *
 * @param string $key Page key.
 * @return bool True when a partial exists.
 */
function ceeta_render( $key ) {
	$file = CEETA_DIR . '/parts/' . ceeta_lang() . '/' . $key . '.php';
	if ( $key && file_exists( $file ) ) {
		include $file;
		return true;
	}
	return false;
}

/* ---------------------------------------------------------------- theme setup */

add_action(
	'after_setup_theme',
	function () {
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'align-wide' );
		add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
		add_image_size( 'ceeta-card', 900, 600, true );
	}
);

add_filter(
	'body_class',
	function ( $classes ) {
		$classes[] = 'lang-' . ceeta_lang();
		$key       = ceeta_current_key();
		if ( $key ) {
			$classes[] = 'page-' . ( 0 === strpos( $key, 'service-' ) ? 'service' : $key );
		}
		return $classes;
	}
);

/* ---------------------------------------------------------------- assets */

add_action(
	'wp_enqueue_scripts',
	function () {
		$fonts = ceeta_is_ar()
			? 'https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&family=Montserrat:wght@500;600;700;800&display=swap'
			: 'https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap';
		wp_enqueue_style( 'ceeta-fonts', $fonts, array(), null ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
		$css = CEETA_DIR . '/assets/css/style.css';
		wp_enqueue_style( 'ceeta-style', CEETA_URI . '/assets/css/style.css', array(), file_exists( $css ) ? filemtime( $css ) : CEETA_VER );
		$js = CEETA_DIR . '/assets/js/main.js';
		wp_enqueue_script( 'ceeta-main', CEETA_URI . '/assets/js/main.js', array(), file_exists( $js ) ? filemtime( $js ) : CEETA_VER, array( 'in_footer' => true, 'strategy' => 'defer' ) );
	}
);

/** Preconnect to Google Fonts. */
add_filter(
	'wp_resource_hints',
	function ( $urls, $relation ) {
		if ( 'preconnect' === $relation ) {
			$urls[] = 'https://fonts.googleapis.com';
			$urls[] = array( 'href' => 'https://fonts.gstatic.com', 'crossorigin' );
		}
		return $urls;
	},
	10,
	2
);

/** Preload the first hero image on the home page (LCP). */
add_action(
	'wp_head',
	function () {
		if ( 'home' === ceeta_current_key() ) {
			printf( '<link rel="preload" as="image" href="%s" fetchpriority="high">' . "\n", esc_url( ceeta_img_mod( 'hero_1', 'img/photos/rig-drilling-site.webp' ) ) );
		}
	},
	2
);

/** Favicon = CEETA logo mark (unless a Site Icon is set in the Customizer). */
add_action(
	'wp_head',
	function () {
		if ( has_site_icon() ) {
			return;
		}
		$u = esc_url( CEETA_URI . '/assets/img/' );
		echo '<link rel="icon" href="' . $u . 'favicon.ico" sizes="any">' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput
		echo '<link rel="icon" type="image/png" sizes="32x32" href="' . $u . 'favicon-32.png">' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput
		echo '<link rel="apple-touch-icon" href="' . $u . 'apple-touch-icon.png">' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput
		echo '<meta name="theme-color" content="#001F5B">' . "\n";
	},
	3
);

/* Lighter front end: no emoji scripts, no generator tag. */
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'wp_head', 'wp_generator' );

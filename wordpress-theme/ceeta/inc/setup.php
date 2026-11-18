<?php
/**
 * One-click setup: creates all bilingual pages, sets the front page and pretty permalinks.
 *
 * @package ceeta
 */

defined( 'ABSPATH' ) || exit;

/** Create / link the theme pages. Safe to run more than once. */
function ceeta_install() {
	$pages = ceeta_data( 'pages' );
	$map   = get_option( 'ceeta_pages', array() );
	if ( ! is_array( $map ) ) {
		$map = array();
	}

	foreach ( $pages as $p ) {
		$key = $p['key'];
		if ( ! empty( $map[ $key ] ) && get_post( (int) $map[ $key ] ) && 'trash' !== get_post_status( (int) $map[ $key ] ) ) {
			continue;
		}
		$slug      = $p['slug'] ? $p['slug'] : 'home';
		$parent_id = ( $p['parent'] && ! empty( $map[ $p['parent'] ] ) ) ? (int) $map[ $p['parent'] ] : 0;
		$path      = $parent_id ? get_page_uri( $parent_id ) . '/' . $slug : $slug;
		$existing  = get_page_by_path( $path, OBJECT, 'page' );

		if ( $existing ) {
			$id = $existing->ID;
		} else {
			$id = wp_insert_post(
				array(
					'post_type'    => 'page',
					'post_status'  => 'publish',
					'post_title'   => $p['en'],
					'post_name'    => $slug,
					'post_parent'  => $parent_id,
					'post_content' => '',
					'menu_order'   => count( $map ),
				)
			);
		}
		if ( $id && ! is_wp_error( $id ) ) {
			$map[ $key ] = (int) $id;
			update_post_meta( $id, '_ceeta_key', $key );
		}
	}

	update_option( 'ceeta_pages', $map );

	if ( ! empty( $map['home'] ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', (int) $map['home'] );
	}

	if ( ! get_option( 'permalink_structure' ) ) {
		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '/%postname%/' );
	}

	ceeta_register_rewrites();
	flush_rewrite_rules();
	update_option( 'ceeta_installed', CEETA_VER );
}

add_action(
	'after_switch_theme',
	function () {
		ceeta_install();
		set_transient( 'ceeta_welcome', 1, 60 );
	}
);

/* Self-heal: run setup if the page map is missing (e.g. theme files copied manually). */
add_action(
	'admin_init',
	function () {
		if ( current_user_can( 'manage_options' ) && get_option( 'ceeta_installed' ) !== CEETA_VER ) {
			ceeta_install();
		}
	}
);

/* Mark theme pages in the Pages list so editors know where their content lives. */
add_filter(
	'display_post_states',
	function ( $states, $post ) {
		$key = get_post_meta( $post->ID, '_ceeta_key', true );
		if ( $key ) {
			$states['ceeta'] = 'CEETA · EN + AR';
		}
		return $states;
	},
	10,
	2
);

add_action(
	'admin_notices',
	function () {
		if ( ! get_transient( 'ceeta_welcome' ) ) {
			return;
		}
		delete_transient( 'ceeta_welcome' );
		echo '<div class="notice notice-success is-dismissible"><p><strong>CEETA theme is ready.</strong> All English and Arabic pages were created (Arabic lives under <code>/ar/</code>). Edit contact details, social links, hero images and SEO in <a href="' . esc_url( admin_url( 'customize.php?autofocus[panel]=ceeta_panel' ) ) . '">Appearance → Customize → CEETA Options</a>. Add training photos from the <em>CEETA Academy</em> page → <em>Training Gallery</em> box.</p></div>';
	}
);

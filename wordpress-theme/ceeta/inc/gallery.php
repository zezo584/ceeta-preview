<?php
/**
 * Training Gallery: pick photos on the "CEETA Academy" page; they feed the 3D slider on Home + Academy.
 *
 * @package ceeta
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'add_meta_boxes_page',
	function ( $post ) {
		if ( (int) $post->ID !== ceeta_page_id( 'academy' ) ) {
			return;
		}
		add_meta_box( 'ceeta_gallery', 'Training Gallery — 3D slider on Home & Academy', 'ceeta_gallery_box', 'page', 'normal', 'high' );
	}
);

/**
 * Meta box markup.
 *
 * @param WP_Post $post Current page.
 */
function ceeta_gallery_box( $post ) {
	$ids = get_post_meta( $post->ID, '_ceeta_gallery', true );
	wp_nonce_field( 'ceeta_gallery', 'ceeta_gallery_nonce' );
	echo '<p>Select the training photos (landscape, 1100 px wide or more). Image <strong>Alt text</strong> is used as the caption. Leave empty to show the default photos.</p>';
	echo '<input type="hidden" id="ceeta-gallery-ids" name="ceeta_gallery" value="' . esc_attr( $ids ) . '">';
	echo '<ul id="ceeta-gallery-preview" style="display:flex;flex-wrap:wrap;gap:8px;margin:12px 0">';
	foreach ( array_filter( array_map( 'absint', explode( ',', (string) $ids ) ) ) as $id ) {
		$src = wp_get_attachment_image_url( $id, 'thumbnail' );
		if ( $src ) {
			echo '<li><img src="' . esc_url( $src ) . '" width="80" height="80" style="object-fit:cover;border-radius:8px" alt=""></li>';
		}
	}
	echo '</ul><button type="button" class="button button-primary" id="ceeta-gallery-pick">Select / reorder photos</button> <button type="button" class="button" id="ceeta-gallery-clear">Use default photos</button>';
}

add_action(
	'save_post_page',
	function ( $post_id ) {
		if ( ! isset( $_POST['ceeta_gallery_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ceeta_gallery_nonce'] ) ), 'ceeta_gallery' ) ) {
			return;
		}
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_page', $post_id ) ) {
			return;
		}
		$raw = isset( $_POST['ceeta_gallery'] ) ? sanitize_text_field( wp_unslash( $_POST['ceeta_gallery'] ) ) : '';
		$ids = implode( ',', array_filter( array_map( 'absint', explode( ',', $raw ) ) ) );
		update_post_meta( $post_id, '_ceeta_gallery', $ids );
	}
);

add_action(
	'admin_enqueue_scripts',
	function ( $hook ) {
		global $post;
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) || ! $post || (int) $post->ID !== ceeta_page_id( 'academy' ) ) {
			return;
		}
		wp_enqueue_media();
		wp_enqueue_script( 'ceeta-admin-gallery', CEETA_URI . '/assets/js/admin-gallery.js', array( 'jquery' ), CEETA_VER, true );
	}
);

/**
 * Print carousel items from the selected gallery.
 *
 * @param string $lang Language (unused for markup, kept for future captions).
 * @return bool False when no gallery is set (the default photos are printed instead).
 */
function ceeta_gallery_items( $lang = 'en' ) {
	$page = ceeta_page_id( 'academy' );
	$ids  = $page ? array_filter( array_map( 'absint', explode( ',', (string) get_post_meta( $page, '_ceeta_gallery', true ) ) ) ) : array();
	if ( ! $ids ) {
		return false;
	}
	$n    = count( $ids );
	$i    = 0;
	$zoom = '<svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 9V4h5M20 9V4h-5M4 15v5h5M20 15v5h-5"/></svg>';
	foreach ( $ids as $id ) {
		$src = wp_get_attachment_image_src( $id, 'large' );
		if ( ! $src ) {
			continue;
		}
		$full = wp_get_attachment_image_url( $id, 'full' );
		$alt  = get_post_meta( $id, '_wp_attachment_image_alt', true );
		$alt  = $alt ? $alt : get_the_title( $id );
		++$i;
		$attr = ( $i <= 3 || $i >= $n - 1 ) ? 'src' : 'data-src';
		printf(
			'<div class="carousel__item" role="group" aria-roledescription="slide" aria-label="%1$d / %2$d"><img %3$s="%4$s" data-full="%5$s" alt="%6$s" width="%7$d" height="%8$d" loading="lazy" decoding="async"><div class="carousel__cap"><strong>%6$s</strong><span class="zoom">%9$s</span></div></div>',
			(int) $i,
			(int) $n,
			esc_attr( $attr ),
			esc_url( $src[0] ),
			esc_url( $full ),
			esc_attr( $alt ),
			(int) $src[1],
			(int) $src[2],
			$zoom // phpcs:ignore WordPress.Security.EscapeOutput
		);
	}
	return $i > 0;
}

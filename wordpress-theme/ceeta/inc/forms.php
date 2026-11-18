<?php
/**
 * Inquiry form: email notification + saved copy in Dashboard → Inquiries.
 *
 * @package ceeta
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'init',
	function () {
		register_post_type(
			'ceeta_inquiry',
			array(
				'labels'          => array(
					'name'          => 'Inquiries',
					'singular_name' => 'Inquiry',
					'menu_name'     => 'Inquiries',
					'all_items'     => 'All Inquiries',
					'edit_item'     => 'View Inquiry',
				),
				'public'          => false,
				'show_ui'         => true,
				'show_in_menu'    => true,
				'menu_position'   => 25,
				'menu_icon'       => 'dashicons-email-alt',
				'supports'        => array( 'title', 'editor' ),
				'capability_type' => 'post',
				'map_meta_cap'    => true,
				'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
			)
		);
	}
);

add_action( 'admin_post_nopriv_ceeta_inquiry', 'ceeta_handle_inquiry' );
add_action( 'admin_post_ceeta_inquiry', 'ceeta_handle_inquiry' );

/** Handle the inquiry form POST. */
function ceeta_handle_inquiry() {
	$back = wp_get_referer();
	$back = $back ? remove_query_arg( 'ceeta_sent', $back ) : home_url( '/' );
	$fail = add_query_arg( 'ceeta_sent', '0', $back ) . '#ceeta-form';
	$done = add_query_arg( 'ceeta_sent', '1', $back ) . '#ceeta-form';

	$nonce = isset( $_POST['ceeta_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['ceeta_nonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'ceeta_inquiry' ) ) {
		wp_safe_redirect( $fail );
		exit;
	}
	if ( ! empty( $_POST['website'] ) ) { // Honeypot: bots fill hidden fields.
		wp_safe_redirect( $done );
		exit;
	}

	$f = array(
		'name'    => isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '',
		'company' => isset( $_POST['company'] ) ? sanitize_text_field( wp_unslash( $_POST['company'] ) ) : '',
		'email'   => isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '',
		'phone'   => isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '',
		'service' => isset( $_POST['service'] ) ? sanitize_text_field( wp_unslash( $_POST['service'] ) ) : '',
		'message' => isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '',
		'lang'    => ( isset( $_POST['lang'] ) && 'ar' === $_POST['lang'] ) ? 'ar' : 'en',
	);
	if ( '' === $f['name'] || '' === $f['phone'] || '' === $f['message'] ) {
		wp_safe_redirect( $fail );
		exit;
	}

	$body = "New inquiry from the CEETA website\n\n"
		. 'Name: ' . $f['name'] . "\n"
		. 'Company: ' . ( $f['company'] ? $f['company'] : '-' ) . "\n"
		. 'Email: ' . ( $f['email'] ? $f['email'] : '-' ) . "\n"
		. 'Phone: ' . $f['phone'] . "\n"
		. 'Service: ' . ( $f['service'] ? $f['service'] : '-' ) . "\n"
		. 'Language: ' . strtoupper( $f['lang'] ) . "\n"
		. 'Page: ' . $back . "\n\n"
		. "Message:\n" . $f['message'] . "\n";

	wp_insert_post(
		array(
			'post_type'    => 'ceeta_inquiry',
			'post_status'  => 'private',
			'post_title'   => $f['name'] . ( $f['service'] ? ' — ' . $f['service'] : '' ),
			'post_content' => $body,
		)
	);

	$to      = ceeta_opt( 'form_email' );
	$to      = $to ? $to : ( ceeta_opt( 'email' ) ? ceeta_opt( 'email' ) : get_option( 'admin_email' ) );
	$headers = array( 'Content-Type: text/plain; charset=UTF-8' );
	if ( is_email( $f['email'] ) ) {
		$headers[] = 'Reply-To: ' . $f['name'] . ' <' . $f['email'] . '>';
	}
	wp_mail( $to, 'CEETA Website Inquiry — ' . $f['name'], $body, $headers );

	wp_safe_redirect( $done );
	exit;
}

/** Success / error notice above the form after redirect. */
function ceeta_form_notice() {
	echo '<span id="ceeta-form"></span>';
	if ( ! isset( $_GET['ceeta_sent'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		return;
	}
	$ok  = '1' === $_GET['ceeta_sent']; // phpcs:ignore WordPress.Security.NonceVerification
	$ar  = ceeta_is_ar();
	$msg = $ok
		? ( $ar ? 'شكرًا لك! تم استلام استفسارك وسيتواصل معك فريقنا قريبًا.' : 'Thank you! Your inquiry has been received — our team will contact you shortly.' )
		: ( $ar ? 'تعذر إرسال الاستفسار. يرجى إدخال الاسم ورقم الهاتف والرسالة ثم المحاولة مرة أخرى، أو تواصل معنا عبر واتساب.' : 'Your inquiry could not be sent. Please fill in your name, phone and message and try again, or contact us on WhatsApp.' );
	$ic  = $ok ? '<path d="M7.8 12.3l2.8 2.8 5.6-5.8"/><circle cx="12" cy="12" r="9.5"/>' : '<circle cx="12" cy="12" r="9.5"/><path d="M12 7.5v5.5M12 16.5h.01"/>';
	printf(
		'<div class="form-notice form-notice--%1$s" role="status"><svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">%2$s</svg><span>%3$s</span></div>',
		$ok ? 'ok' : 'err',
		$ic, // phpcs:ignore WordPress.Security.EscapeOutput
		esc_html( $msg )
	);
}

<?php
/**
 * Customizer: Appearance → Customize → CEETA Options.
 *
 * @package ceeta
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'customize_register',
	function ( $wp ) {
		$d = ceeta_defaults();

		$wp->add_panel(
			'ceeta_panel',
			array(
				'title'    => 'CEETA Options',
				'priority' => 30,
			)
		);

		$sections = array(
			'ceeta_contact' => 'Contact details (top bar, footer, contact page)',
			'ceeta_social'  => 'Social links',
			'ceeta_hero'    => 'Home hero slider images',
			'ceeta_form'    => 'Inquiry form',
			'ceeta_seo'     => 'SEO',
			'ceeta_misc'    => 'Animation',
		);
		foreach ( $sections as $id => $title ) {
			$wp->add_section( $id, array( 'title' => $title, 'panel' => 'ceeta_panel' ) );
		}

		$text = function ( $key, $label, $section, $type = 'text', $sanitize = 'sanitize_text_field', $desc = '' ) use ( $wp, $d ) {
			$wp->add_setting(
				'ceeta_' . $key,
				array(
					'default'           => isset( $d[ $key ] ) ? $d[ $key ] : '',
					'sanitize_callback' => $sanitize,
				)
			);
			$wp->add_control(
				'ceeta_' . $key,
				array(
					'label'       => $label,
					'section'     => $section,
					'type'        => $type,
					'description' => $desc,
				)
			);
		};

		$text( 'email', 'Email address', 'ceeta_contact', 'email', 'sanitize_email' );
		$text( 'phone_display', 'Phone (as displayed)', 'ceeta_contact', 'text', 'sanitize_text_field', 'e.g. +20 100 397 1910' );
		$text( 'phone_tel', 'Phone (for tel: links)', 'ceeta_contact', 'text', 'sanitize_text_field', 'International format without spaces, e.g. +201003971910' );
		$text( 'whatsapp', 'WhatsApp number', 'ceeta_contact', 'text', 'ceeta_sanitize_digits', 'Digits only with country code, e.g. 201003971910' );
		$text( 'address_en', 'Address (English)', 'ceeta_contact' );
		$text( 'address_ar', 'Address (Arabic)', 'ceeta_contact' );
		$text( 'maps', 'Google Maps link', 'ceeta_contact', 'url', 'esc_url_raw' );
		$text( 'map_embed', 'Google Maps embed URL (contact page)', 'ceeta_contact', 'url', 'esc_url_raw', 'Google Maps → Share → Embed a map → copy the src="…" URL. Leave empty to hide the map.' );

		$text( 'facebook', 'Facebook URL', 'ceeta_social', 'url', 'esc_url_raw' );
		$text( 'linkedin', 'LinkedIn URL', 'ceeta_social', 'url', 'esc_url_raw' );
		$text( 'instagram', 'Instagram URL', 'ceeta_social', 'url', 'esc_url_raw' );

		$hero = array( 'CAT III & CAT IV Rig Inspection', 'Lifting Inspection', 'Tubular Inspection', 'Lifting Planning & Supervision', 'Rig Auditing & Commissioning' );
		foreach ( $hero as $i => $label ) {
			$n = $i + 1;
			$wp->add_setting( 'ceeta_hero_' . $n, array( 'sanitize_callback' => 'esc_url_raw' ) );
			$wp->add_control(
				new WP_Customize_Image_Control(
					$wp,
					'ceeta_hero_' . $n,
					array(
						'label'       => sprintf( 'Slide %d — %s', $n, $label ),
						'section'     => 'ceeta_hero',
						'description' => 1 === $n ? 'Recommended: 1920 × 1080 px, WebP or JPG under 300 KB. Leave empty to keep the default photo.' : '',
					)
				)
			);
		}

		$text( 'form_email', 'Send inquiries to', 'ceeta_form', 'email', 'sanitize_email', 'Defaults to the contact email. Every inquiry is also saved in Dashboard → Inquiries.' );

		$text( 'seo_title_en', 'Home page title (English)', 'ceeta_seo', 'text', 'sanitize_text_field', 'Optional — leave empty to use the built-in SEO title.' );
		$text( 'seo_desc_en', 'Home page meta description (English)', 'ceeta_seo', 'textarea', 'sanitize_textarea_field' );
		$text( 'seo_title_ar', 'Home page title (Arabic)', 'ceeta_seo' );
		$text( 'seo_desc_ar', 'Home page meta description (Arabic)', 'ceeta_seo', 'textarea', 'sanitize_textarea_field' );
		$text( 'gsc', 'Google Search Console verification code', 'ceeta_seo', 'text', 'sanitize_text_field', 'Only the content="…" value of the google-site-verification tag.' );

		$wp->add_setting( 'ceeta_preloader', array( 'default' => '1', 'sanitize_callback' => 'ceeta_sanitize_check' ) );
		$wp->add_control( 'ceeta_preloader', array( 'label' => 'Show logo preloader', 'section' => 'ceeta_misc', 'type' => 'checkbox' ) );
	}
);

/**
 * Keep digits only.
 *
 * @param string $v Value.
 * @return string
 */
function ceeta_sanitize_digits( $v ) {
	return preg_replace( '/\D+/', '', (string) $v );
}

/**
 * Checkbox sanitizer.
 *
 * @param mixed $v Value.
 * @return string
 */
function ceeta_sanitize_check( $v ) {
	return $v ? '1' : '';
}

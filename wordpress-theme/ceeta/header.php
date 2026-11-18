<?php
/**
 * Site header: document head, preloader, top bar (email + phone), main navigation.
 *
 * @package ceeta
 */

defined( 'ABSPATH' ) || exit;
?><!DOCTYPE html>
<html lang="<?php echo esc_attr( ceeta_lang() ); ?>" dir="<?php echo ceeta_is_ar() ? 'rtl' : 'ltr'; ?>" class="no-js">
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="format-detection" content="telephone=no">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<?php if ( ceeta_opt( 'preloader' ) ) : ?>
<div class="preloader" aria-hidden="true"><div class="preloader__inner"><img class="preloader__mark" src="<?php echo esc_url( CEETA_URI . '/assets/img/ceeta-mark.png' ); ?>" alt="" width="84" height="84"><div class="preloader__bar"><span></span></div></div></div>
<?php endif; ?>
<a class="skip-link" href="#main"><?php echo esc_html( ceeta_t( 'skip' ) ); ?></a>
<?php require CEETA_DIR . '/parts/' . ceeta_lang() . '/chrome.php'; ?>
<main id="main">

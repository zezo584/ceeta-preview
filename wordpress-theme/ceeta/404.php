<?php
/**
 * 404 page.
 *
 * @package ceeta
 */

defined( 'ABSPATH' ) || exit;

get_header();
$ceeta_ar = ceeta_is_ar();
ceeta_page_hero( $ceeta_ar ? 'الصفحة غير موجودة' : 'Page not found', $ceeta_ar ? 'ربما تم نقل الصفحة أو حذفها.' : 'The page you are looking for may have been moved or removed.' );
?>
<section class="section"><div class="container not-found">
	<b>404</b>
	<p><?php echo esc_html( $ceeta_ar ? 'جرّب إحدى الصفحات التالية:' : 'Try one of these pages instead:' ); ?></p>
	<p class="hero__cta" style="justify-content:center">
		<a class="btn" href="<?php echo esc_url( ceeta_url( 'home', '', ceeta_lang() ) ); ?>"><?php echo esc_html( ceeta_t( 'home' ) ); ?></a>
		<a class="btn btn--navy" href="<?php echo esc_url( ceeta_url( 'services', '', ceeta_lang() ) ); ?>"><?php echo esc_html( ceeta_t( 'services_short' ) ); ?></a>
		<a class="btn btn--outline" href="<?php echo esc_url( ceeta_url( 'contact', '', ceeta_lang() ) ); ?>"><?php echo esc_html( ceeta_t( 'contact' ) ); ?></a>
	</p>
</div></section>
<?php
get_footer();

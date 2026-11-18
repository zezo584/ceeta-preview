<?php
/**
 * Template helpers shared by blog / generic templates.
 *
 * @package ceeta
 */

defined( 'ABSPATH' ) || exit;

/**
 * Inline SVG icon.
 *
 * @param string $name arrow|chevr.
 * @return string
 */
function ceeta_icon( $name ) {
	$p = array(
		'arrow' => '<path d="M4 12h15"/><path d="M13 6l6 6-6 6"/>',
		'chevr' => '<path d="M9 5l7 7-7 7"/>',
	);
	return '<svg class="ic' . ( 'arrow' === $name ? ' ic-arrow' : '' ) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ( isset( $p[ $name ] ) ? $p[ $name ] : '' ) . '</svg>';
}

/**
 * Page hero for blog posts, archives, 404 and generic pages.
 *
 * @param string $title Heading.
 * @param string $lead  Optional lead text (plain text).
 * @param string $meta  Optional extra HTML under the lead (already escaped).
 */
function ceeta_page_hero( $title, $lead = '', $meta = '' ) {
	$bg = CEETA_URI . '/assets/img/photos/drilling-rig-blue-sky.webp';
	?>
	<section class="page-hero">
		<div class="page-hero__bg" data-parallax="0.12" style="background-image:url('<?php echo esc_url( $bg ); ?>')"></div>
		<img class="hero__mark" src="<?php echo esc_url( CEETA_URI . '/assets/img/ceeta-mark-white.png' ); ?>" alt="" width="512" height="512">
		<div class="container">
			<nav class="crumbs" aria-label="<?php echo esc_attr( ceeta_t( 'breadcrumb' ) ); ?>">
				<a href="<?php echo esc_url( ceeta_url( 'home', '', ceeta_lang() ) ); ?>"><?php echo esc_html( ceeta_t( 'home' ) ); ?></a>
				<?php echo ceeta_icon( 'chevr' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<span aria-current="page"><?php echo esc_html( wp_strip_all_tags( $title ) ); ?></span>
			</nav>
			<h1><?php echo esc_html( wp_strip_all_tags( $title ) ); ?></h1>
			<?php if ( $lead ) : ?>
				<p><?php echo esc_html( $lead ); ?></p>
			<?php endif; ?>
			<?php echo $meta; // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>
		<svg class="flow" viewBox="0 0 1440 150" preserveAspectRatio="none" aria-hidden="true"><path class="f-line" vector-effect="non-scaling-stroke" d="M0 96 C 360 18, 900 22, 1440 54"/><path class="f-o" d="M0 132 C 380 46, 940 44, 1440 78 L1440 150 L0 150 Z"/><path class="f-w" d="M0 146 C 420 76, 980 74, 1440 100 L1440 150 L0 150 Z"/></svg>
	</section>
	<?php
}

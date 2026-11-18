<?php
/**
 * Single blog post.
 *
 * @package ceeta
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$ceeta_meta = '<div class="post-meta"><time datetime="' . esc_attr( get_the_date( 'c' ) ) . '">' . esc_html( get_the_date() ) . '</time><span>' . esc_html( get_the_author() ) . '</span></div>';
	ceeta_page_hero( get_the_title(), '', $ceeta_meta );
	?>
	<section class="section"><div class="container"><article <?php post_class( 'entry' ); ?>>
		<?php if ( has_post_thumbnail() ) : ?>
			<figure class="svc-figure"><?php the_post_thumbnail( 'large' ); ?></figure>
		<?php endif; ?>
		<div class="entry-content"><?php the_content(); ?></div>
		<?php wp_link_pages(); ?>
		<p style="margin-top:40px"><a class="btn btn--navy" href="<?php echo esc_url( ceeta_url( 'contact', '', ceeta_lang() ) ); ?>"><?php echo esc_html( ceeta_t( 'quote' ) ); ?><?php echo ceeta_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a></p>
	</article></div></section>
	<?php
endwhile;

get_footer();

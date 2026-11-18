<?php
/**
 * Pages: CEETA pages render their bilingual layout; any other page shows its editor content.
 *
 * @package ceeta
 */

defined( 'ABSPATH' ) || exit;

get_header();

if ( ! ceeta_render( ceeta_current_key() ) ) :
	while ( have_posts() ) :
		the_post();
		ceeta_page_hero( get_the_title(), has_excerpt() ? get_the_excerpt() : '' );
		?>
		<section class="section"><div class="container"><article <?php post_class( 'entry' ); ?>>
			<div class="entry-content"><?php the_content(); ?></div>
			<?php wp_link_pages(); ?>
		</article></div></section>
		<?php
	endwhile;
endif;

get_footer();

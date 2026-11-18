<?php
/**
 * Blog / news index, archives and search results.
 *
 * @package ceeta
 */

defined( 'ABSPATH' ) || exit;

get_header();

if ( is_search() ) {
	/* translators: %s: search query. */
	$ceeta_title = sprintf( ceeta_is_ar() ? 'نتائج البحث عن: %s' : 'Search results for: %s', get_search_query() );
} elseif ( is_archive() ) {
	$ceeta_title = get_the_archive_title();
} else {
	$ceeta_title = ceeta_is_ar() ? 'الأخبار والمقالات' : 'News & Insights';
}
ceeta_page_hero( $ceeta_title, ceeta_is_ar() ? 'أحدث أخبار CEETA ومقالات الفحص والسلامة.' : 'Latest CEETA news and articles on inspection, testing and safety.' );
?>
<section class="section"><div class="container">
<?php if ( have_posts() ) : ?>
	<div class="post-grid">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<a class="post-card" href="<?php the_permalink(); ?>" data-reveal>
			<div class="post-card__media">
				<?php
				if ( has_post_thumbnail() ) {
					the_post_thumbnail( 'ceeta-card', array( 'loading' => 'lazy' ) );
				} else {
					echo '<img src="' . esc_url( CEETA_URI . '/assets/img/photos/rig-drilling-site.webp' ) . '" alt="" loading="lazy" width="1280" height="720">';
				}
				?>
			</div>
			<div class="post-card__body">
				<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
				<h2><?php the_title(); ?></h2>
				<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22, '…' ) ); ?></p>
				<span class="link-arrow"><?php echo esc_html( ceeta_t( 'learn_more' ) ); ?><?php echo ceeta_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
			</div>
		</a>
	<?php endwhile; ?>
	</div>
	<nav class="pagination"><?php echo paginate_links( array( 'prev_text' => '‹', 'next_text' => '›' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></nav>
<?php else : ?>
	<p class="not-found"><?php echo esc_html( ceeta_is_ar() ? 'لا توجد مقالات بعد.' : 'No posts yet.' ); ?></p>
<?php endif; ?>
</div></section>
<?php
get_footer();

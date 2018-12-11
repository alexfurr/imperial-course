<?php get_header(); ?>
<main id="content">
<?php if ( have_posts() ) : ?>
<header class="header">
<h1 class="entry-title"><?php printf( esc_html__( 'Search Results for: %s', 'generic' ), get_search_query() ); ?></h1>
</header>
<?php while ( have_posts() ) : the_post(); ?>




<?php



$postTypeLookupArray = array(

"session_page" => "Session",
"topic_session" => "Topic",
"session_page" => "Session Page",
"page" => "Page",

);



$ID = get_the_id();
$pageLink = get_the_permalink($ID);
$postType = get_post_type($ID);


echo '<div class="contentBox">';
echo '<div class="contentBoxInner">';
echo '<div class="searchResultPageType">';
if(isset($postTypeLookupArray[$postType] ) )
{
	echo $postTypeLookupArray[$postType];
}
echo '</div>';
echo '<a href="'.$pageLink.'"><h2>'.get_the_title($ID).'</h2></a>';
echo the_excerpt();
echo '<br/><a href="'.$pageLink.'">Read more</a>';
echo '</div>';
echo '</div>';

?>




<?php endwhile; ?>
<?php get_template_part( 'nav', 'below' ); ?>
<?php else : ?>
<article id="post-0" class="post no-results not-found">
<header class="header">
<h1 class="entry-title"><?php esc_html_e( 'Nothing Found', 'generic' ); ?></h1>
</header>
<div class="entry-content">
<p><?php esc_html_e( 'Sorry, nothing matched your search. Please try again.', 'generic' ); ?></p>
<?php get_search_form(); ?>
</div>
</article>
<?php endif; ?>
</main>
<?php get_sidebar(); ?>
<?php get_footer(); ?>
<?php
 get_header(); ?>
<div id="imperial_page_title">
<h1 class="entry-title"><?php the_title(); ?></h1>
</div>
<main id="content">
<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

<?php




/*
echo '<div class="imperial-breadcrumbs">';
$topicsURL = get_site_url().'/topics';
echo '<a href="'. $topicsURL .'">'.$level1Plural.'</a> > '.get_the_title();
echo '</div>';
*/
?>

<div class="entry-content">
<?php the_content(); 


$topicID = get_the_id();


$sessionStr = imperialCourse_draw::drawSessionsInTopic($topicID);

echo $sessionStr;



?>
<div class="entry-links"><?php wp_link_pages(); ?></div>
</div>
</article>
<?php endwhile; endif; ?>
<?php edit_post_link($editPageIcon. ' Edit this page', '<br/><br/>', '',  '', 'editPageButton'); ?>
</main>
<?php get_footer(); ?>
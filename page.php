<?php get_header(); ?>
<div id="imperial_page_title">
<h1 class="entry-title"><?php the_title(); ?></h1>
</div>
<main id="content">
<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
<?php
$currentPageID = get_the_id();
global $post;
$parentID = $post->post_parent;
$parentTemplate = get_page_template_slug( $parentID );
$showSubMenu=false;

?>

<div class="entry-content">
<?php if ( has_post_thumbnail() ) { the_post_thumbnail(); } ?>
<?php
if($showSubMenu==true)
{
	echo '<div class="container">';
	echo '<div class="row">';
	echo '<div class="col-md-8">';
	
}
the_content();
if($showSubMenu==true)
{
	echo '</div>';
	echo '<div class="col-md-4 subpageMenuContainer">';
	
	echo '<div class="sessionSubpageMenu">';
	//echo '<h3>Menu</h3>';
	
	$args = array(
		'sort_order' => 'asc',
		'sort_column' => ',menu_order',
		'parent' => $parentID,
		'post_type' => 'page',
		'post_status' => 'publish'
	); 
	$silblings = get_pages($args); 
	
	
	
	echo '<ul>';
	
	$currentPageNo = 1;
	foreach($silblings as $siblingInfo)
	{
		$pageName = $siblingInfo->post_title;
		$pageID = $siblingInfo->ID;
		
		
		
		$pageURL = get_permalink($pageID);
		
		echo '<li>';
		
		if($currentPageID==$pageID)
		{
			echo '<strong>';
		}
		
		
		
		echo '<a href="'.$pageURL.'">'.$currentPageNo.'. '. $pageName.'</a>';
		if($currentPageID==$pageID)
		{
			echo '</strong>';
		}
		$currentPageNo++;
		
		echo '</li>';
	}
	echo '</ul>';
	
	
	
	echo '</div>'; // End of sessionSubpageMenu
	
	echo '</div>';	// End of subpageMenuContainer
	echo '</div>';
	echo '</div>';
	
}
?>
<div class="entry-links"><?php wp_link_pages(); ?></div>
</div>
</article>
<?php if ( ! post_password_required() ) comments_template( '', true ); ?>
<?php endwhile; endif; ?>
<?php edit_post_link('Edit this page', '<br/><br/>', '',  '', 'editPageButton'); ?>
</main>
<?php get_sidebar(); ?>
<?php get_footer(); ?>
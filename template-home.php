<?php 
/* Template Name: Home page */

get_header(); 
?>
<main id="content">
<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

<header class="header">
<h1 class="entry-title"><?php the_title(); ?></h1> 
</header>
<div class="entry-content">
<div class="row">
<div class="col-md-9">
<?php the_content(); ?>
</div>
<div class="md-3 centreContent">
<?php		$args = array(
			'sort_order' => 'asc',		
			'sort_column' => ',menu_order',	
			'post_type' => 'course_contact',
			'post_status' => 'publish'	
			); 		
$contacts = get_pages($args); 	
$courseLeaderStr = '';		
foreach ($contacts as $contactInfo)		
{			
	
	$contactName = $contactInfo->post_title;
	$postID = $contactInfo->ID;
	$email = get_post_meta($postID, 'email', true);	
	$courseLeader = get_post_meta($postID, 'course_leader', true);	
	if($courseLeader=="on")	
	{								
		if ( has_post_thumbnail( $postID ) )			
		{						
			$courseLeaderStr.= '<div class="courseLeaderAvatar" style="padding-left:10px">'.get_the_post_thumbnail( $postID, array(200, 200) ).'</div>';
		}				
		$courseLeaderStr.='<span class="courseLeaderName">'.$contactName.'</span><br/><a href="mailto'.$email.'">'.$email.'</a><h3>Course Leader</h3>';	

	}																
}
echo $courseLeaderStr;		?>
</div>
</div>
<div class="entry-links">
<?php wp_link_pages(); ?>
</div>
</div>
</article>
<?php endwhile; endif; ?>
<?php edit_post_link('Edit this page', '<br/><br/>', '',  '', 'editPageButton'); ?>
</main>
<?php get_sidebar(); ?>
<?php get_footer(); ?>
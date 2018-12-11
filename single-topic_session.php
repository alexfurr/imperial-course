<?php 
 // Get the Parent ID
$thisSessionID = get_the_ID();
$parentID = wp_get_post_parent_id( $thisSessionID );
$parentName = get_the_title($parentID);
$parentURL = get_the_permalink($parentID);
 
$topicsURL = get_site_url().'/topics';


// Get the Nomenclature
$topicNaming = getTopicNaming();
$level1Name = $topicNaming[0];
$level1Plural = pluralize($level1Name);
$level2Name = $topicNaming[1];
$level2Plural = pluralize($level2Name);


// Get the SubMenu array info
$submenuArray  = imperialCourse_draw::drawSubpagesMenu($thisSessionID, "", $level2Name);
$submenuStr = $submenuArray['menuStr'];
$percentComplete = $submenuArray['percentComplete'];


/* Get the Session Meta */
$sessionDate = get_post_meta($thisSessionID, "sessionDate", true);

 get_header(); ?>
 <div id="imperial_page_title">
<h1 class="entry-title"><?php the_title(); ?></h1>
</div>
<main id="content">
<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

<div class="entry-content">
<?php 
$thisSessionID = get_the_id();

// Generate the subpages menu */
$sessionPages = getSessionPages ( $thisSessionID );
$pageCount = count($sessionPages);

$initialPageURL = '';
if($pageCount>=1)
{
	// Get the first page in this session
	$initialPageID = $sessionPages[0]->ID;
	$initialPageURL = get_permalink($initialPageID);
}


// Spit out the main content



if($sessionDate)
{
	//echo date('l jS \of F Y h:i:s A', $sessionDate);
	echo '<div class="sp_sessionDate">';
	echo date("l jS F, Y", strtotime($sessionDate));
	echo '</div>';
}




the_content(); 



// Spit out the Learning outcomes if they exist
$learningOutcomes = get_post_meta($thisSessionID, 'learning_outcomes', true);

if($learningOutcomes)
{
	echo '<div class="learning-outcomes">';
	echo '<h2>Learning Outcomes</h2>';
	echo $learningOutcomes;
	echo '</div>';
}



// Check the Available From and to Dates
$canViewSessionToday = checkCanViewSessionByDate($thisSessionID, $initialPageURL);

if($canViewSessionToday[0]==false)
{
	
	echo $canViewSessionToday[1];
	
}

	
if($canViewSessionToday[0]==true && $pageCount>=1)
{
	echo '<br/><a href="'.$initialPageURL.'" class="button">Start this '.$level2Name.'</a>';
}

?>

<div class="entry-links"><?php wp_link_pages(); ?></div>
</div>
</article>
<?php endwhile; endif; ?>
<?php edit_post_link($editPageIcon.' Edit this '.$level2Name, '<br/><br/>', '',  '', 'editPageButton'); ?>
</main>
<?php
/*
if($pageCount>=1)
{
	echo '<aside id="sidebar" class="session_submenu contentBox">';
	echo $submenuStr;
	echo '</aside>';
}
*/
?>


<?php get_footer(); ?>
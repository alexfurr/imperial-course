<?php
$thisID = get_the_ID();
$sessionID = wp_get_post_parent_id( $thisID );
$sessionName = get_the_title($sessionID);
$sessionURL = get_the_permalink($sessionID);










/* Also get the Topic Name */
$topicID = wp_get_post_parent_id( $sessionID );
$topicName = get_the_title($topicID);
$topicURL = get_the_permalink($topicID);

// Get the Nomenclature
$topicNaming = getTopicNaming();
$level1Name = $topicNaming[0];
$level1Plural = pluralize($level1Name);
$level2Name = $topicNaming[1];
$level2Plural = pluralize($level2Name);
$level3Name = $topicNaming[2];
$level3Plural = pluralize($level3Name);

// Get the SubMenu array info
$submenuArray  = imperialCourse_draw::drawSubpagesMenu($sessionID, $thisID, $level2Name);
$prevPage = $submenuArray['prevPage'];
$nextPage = $submenuArray['nextPage'];
$lastPageID = $submenuArray['lastPageID'];

$lastSlide = false;
if($lastPageID==$thisID)
{
	$lastSlide=true;
}



// Get ALL the submenu
$mySessions = getTopicSessions ($topicID);

$submenuStr = '<ol>';
$submenuStr.='<li class="topicNameItem"><div class="topicName">'.$topicName.'</div>';


$currentPage = 0;
foreach($mySessions as $sessionInfo)
{
	$tempSessionID = $sessionInfo->ID;
	
	$tempMenuArray = imperialCourse_draw::drawSubpagesMenu($tempSessionID, $thisID, $level2Name);	
	$submenuStr.= $tempMenuArray['menuStr'];
	
	if($lastSlide==true && $tempSessionID==$sessionID)
	{
		if(isset($mySessions[$currentPage+1]) )
		{
			$nextSessionPageID = $mySessions[$currentPage+1]->ID;		
			$tempMenuArray = imperialCourse_draw::drawSubpagesMenu($nextSessionPageID, $thisID, $level2Name);	

			$nextSectionFirstPageID = $tempMenuArray['firstPageID'];
			$nextSectionURL = get_permalink($nextSectionFirstPageID);
			
		}
		else
		{
			$nextSectionURL = '';
		}
	}
	
	$currentPage++;
	
	
}
$submenuStr.='</li></ol>';



//$submenuArray  = imperialCourse_draw::drawSubpagesMenu($sessionID, $thisID, $level2Name);






//$submenuStr.= $submenuArray['menuStr'];





$topicsURL = get_site_url().'/topics';


get_header(); ?> 
<?php
$canViewSessionToday = checkCanViewSessionByDate($sessionID);

if($canViewSessionToday[0]==false)
{
	if(current_user_can( "manage_options" ) )
	{
	}
	else
	{
		echo $canViewSessionToday[1];
		get_footer();
		die();
	}
}

?>

<main  class="contentNoMargin">


    <div class="grid-wrapper">
        
        
        <div class="grid-sidebar">
            
            <div class="nav-toggle-wrap"><div id="nav_toggle_button"><i id="nav_toggle_icon" class="fas fa-ellipsis-v"></i></div></div>
            
            <div class="gridNav">
                <div class="learning-content-menu">				
				<?php
				echo $submenuStr;
				?>
                </div>
            </div>
            
        </div>



        <div class="grid-content" id="content">
            <div class="entry-content">
				<h1 class="entry-title"><?php echo $sessionName;?></h1>
				<h2><?php the_title(); ?></h2> 
				</header>
				<div class="entry-content">
				<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

				<?php
				the_content();
				// Add notes form if the plugin is activated
				if (class_exists('ekNotes'))
				{
					echo do_shortcode('[ek-notes]');
				}



				echo '<div id="session-page-nav">';
				if($prevPage)
				{
					$prevPageURL = get_the_permalink($prevPage);	
					echo '<a href="'.$prevPageURL.'" class="prev-page-button button"><i class="fas fa-chevron-left"></i> Previous '.$level3Name.'</a>';
				}

				if($nextPage)
				{
					$nextPageURL = get_the_permalink($nextPage);	
					echo '<a href="'.$nextPageURL.'" class="next-page-button button">Next '.$level3Name.' <i class="fas fa-chevron-right"></i></a>';
				}
				
				
				if($lastSlide==true)
				{
					if($nextSectionURL)
					{
						echo '<a href="'.$nextSectionURL.'">';
					}
					echo '<div class="endOfSessionDiv"><strong>You have now completed this '.$level2Name.'</strong>';
					if($nextSectionURL)
					{
						echo '<br/>Click here to continue to the next '.$level2Name;
					}
					echo '</div>';
					if($nextSectionURL)
					{

					echo '</a>';
					}
				}
				
				
				echo '</div>';


				?>

				<?php endwhile; endif; ?>
				<?php edit_post_link($editPageIcon. ' Edit this '.$level3Name, '<br/><br/>', '',  '', 'editPageButton'); ?>



				</div>
            </div>
            
        </div>

        
        
        
    </div><!-- End of grid-wrapper -->

</main>


<script>
jQuery( document ).ready( function () {
    jQuery('#nav_toggle_button').on( 'click', function ( e ) {
        jQuery('.grid-sidebar').toggleClass('mobile-mode-wide');
        
        var current_rotation = jQuery('#nav_toggle_icon').attr('data-fa-transform' );
        if ( 'rotate-90' === current_rotation ) {
            jQuery('#nav_toggle_icon').attr('data-fa-transform', 'rotate-0' );
        } else {
            jQuery('#nav_toggle_icon').attr('data-fa-transform', 'rotate-90' );
        }
    });
    jQuery('.learning-content-menu li').on( 'click', function ( e ) {
         jQuery('.grid-sidebar').removeClass('mobile-mode-wide');
         jQuery('#nav_toggle_icon').attr('data-fa-transform', 'rotate-0' );
    });
});
</script>




<?php get_footer(); ?>
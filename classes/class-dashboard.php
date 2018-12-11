<?php
class ek_course_dashboard
{
	
	static function topics_tree()
	{

	
		$args = array(	
		"admin" => true,
		);
		$html = imperialCourse_draw::drawLearningContentTree($args);
		
		echo $html;
		
	}		
	
	static function tips_widget()
	{
		
		
		echo '<div class="tips_widget_wrap">';
		
		// Check for Quiz Tool		
		if (class_exists('ekQuiz'))
		{
			echo '<div>';

			echo '<h2>Quiz Questions</h2>';
			echo '<div>';
			echo '<a href="wp-admin/edit.php?post_type=ek_pot" class="button-secondary">Create a question</a>';
			echo '</div>';
			echo '</div>';
		
		}
		
		echo '<div>';
		echo '<h2>Make an Announcement</h2>';
		echo '<div>';
		echo '<a href="wp-admin/post-new.php" class="button-secondary">Add Announcement</a>';
		echo '</div>';
		echo '</div>';
		
		echo '<div>';
		echo '<h2>Add a Header Image</h2>';
		
		$siteURL = get_site_url();
		$headerURL = $siteURL.'/wp-admin/customize.php?return='.urlencode($siteURL.'/wp-admin').'&autofocus%5Bcontrol%5D=header_image';
		
		echo '<div>';
		echo '<a href="'.$headerURL.'" class="button-secondary">View Header Options</a>';
		echo '</div>';
		echo '</div>';		
		
		echo '<div>';
		echo '<h2>Search the FAQ</h2>';
		echo '<div>';
		echo '<input type="text">';
		echo '<a href="" class="button-secondary">Search</a>';
		echo '</div>';
		echo '</div>';		
		
		
		
	
		
		echo '</div>';
		
		
	}
	
	
	
	
	
	
} //Close class
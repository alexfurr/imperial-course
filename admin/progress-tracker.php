<h1>Progress Tracker</h1>

<?php

//Create a blank array for all page IDs

$masterPageID_array = array();


// Get All Users Activity
$masterActivityLog = ek_user_stats_queries::getActivity();
$masterUserActivityArray = array();

foreach($masterActivityLog as $activityMeta)
{
	$userID = $activityMeta['user_id'];
	$pageID = $activityMeta['page_id'];
	$masterUserActivityArray[$userID][$pageID] = true;

}



// Get all topics and put into array
$topics = getTopics();

foreach($topics as $topicInfo)
{
	
	$topicName = $topicInfo->post_title;
	$topicID = $topicInfo->ID;
	
	// Add thie page to the master array
	$masterPageID_array[] = $topicID;

	echo '<h2>'.$topicName.'</h2>';
	
	// Get the Sessions 
	
	$topicSessions = getTopicSessions($topicID);
	{
		foreach($topicSessions as $sessionInfo)
		{
			
			$sessionName = $sessionInfo->post_title;
			$sessionID = $sessionInfo->ID;	
			
			// Add thie page to the master array
			$masterPageID_array[] = $topicID;
			
			
			
			echo '<h3>'.$sessionName.'</h3>';
			
			
			$sessionPages = getSessionPages($sessionID);
			// get the session pages
			foreach($sessionPages as $pageInfo)
			{
				
				$pageName = $pageInfo->post_title;
				$pageID = $pageInfo->ID;
				
				// Add thie page to the master array
				$masterPageID_array[] = $topicID;
				
				
				echo '- '.$pageName.'<br/>';
			}				
			
			
		}
	}
}


$totalPages = count($masterPageID_array);

echo 'Total Pages = '.$totalPages;



// Finally get all users and get their progress


?>
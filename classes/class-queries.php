<?php
function getTopics()
{
	$args = array(
		'posts_per_page'   => -1,
		'orderby'          => 'menu_order',
		'order'            => 'ASC',		
		'post_type'        => 'imperial_topic',
		'post_status'      => 'publish',
	);
	$posts_array = get_posts( $args );
	return  $posts_array;	
}

function getTopicSessions ($topicID)
{

	$args = array(
		'posts_per_page'   => -1,
		'orderby'          => 'menu_order',
		'order'            => 'ASC',
		'include'          => '',
		'exclude'          => '',
		'post_type'        => 'topic_session',
		'post_parent'      => $topicID,
		'post_status'      => 'publish',
	);
	$posts_array = get_posts( $args );
	return  $posts_array;
}

function getSessionPages ( $sessionID ) 
{

	$args = array(
		'posts_per_page'   => -1,
		'orderby'          => 'menu_order',
		'order'            => 'ASC',
		'include'          => '',
		'exclude'          => '',
		'post_type'        => 'session_page',
		'post_parent'      => $sessionID,
		'post_status'      => 'publish',
	);
	$posts_array = get_posts( $args );
	return  $posts_array;
}




function getSessionNumber($sessionID)
{
	// Find out the session number of this session
	$moduleSessions = getModuleSessions	();
	$tempSessionNumber=1;
	foreach($moduleSessions as $sessionInfo)
	{
		$tempSessionID = $sessionInfo->ID;		
		if($tempSessionID == $sessionID)
		{
			$thisSessionNumber = $tempSessionNumber++;
		}
		
		$tempSessionNumber++;
	}
	
	return $thisSessionNumber;
				
}
function getModuleOverallProgress($userID)
{
	
	$sessions = getModuleSessions ();
	$modulesPagesArray = array();
	$pageCompleteCount=0;
	$totalPages = 0;
	$userPagesCompleteLookup = array();
	foreach($sessions as $sessionInfo)
	{
		// Put ALL pages into an array for lookup
		$thisSessionID = $sessionInfo->ID;		
		$args = array
		(
			"sessionID" => $thisSessionID			
		);
		$mySessionContent = getSessionContent($args);
		
		foreach($mySessionContent as $contentInfo)
		{
			$postID = $contentInfo->ID;
			$modulesPagesArray[] = $postID;
			$totalPages++;
		}
	}
	
	
	global $terms_DB;
	
	// Now get all the pages this person has seen
	$userCompletePages = $terms_DB-> getRows($userID);
	
	foreach($userCompletePages as $thisInfo)
	{
		$completePageID = $thisInfo->page_id;
		$userPagesCompleteLookup[]= $completePageID;
	}
	
	
	foreach($modulesPagesArray as $pageID)
	{
		
		if(in_array($pageID, $userPagesCompleteLookup))
		{
			$pageCompleteCount++;
		}
	}
	
	
	// Total Complete
	$percentComplete = (($pageCompleteCount / $totalPages)*100);
	
	return $percentComplete;
	
	
		
	
}
?>
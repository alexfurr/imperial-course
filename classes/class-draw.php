<?php
class imperialCourse_draw
{
	
	
	public static function drawTopics()
	{	
		$str = '';
		$topicImage = get_stylesheet_directory_uri().'/images/topic-icon.png';		
		
		$args = array(
			'sort_order' => 'asc',
			'sort_column' => ',menu_order',
			'post_type' => 'imperial_topic',
			'post_status' => 'publish'
		); 
		$topics = get_pages($args); 	
		
		$currentTopicNumber=1;
		
		$str.='<div class="container">';
		//$str.='<div class="row">';		
		$str.='<div class="topics_list">';
		
		foreach ($topics as $topicInfo)
		{
			$topicName = $topicInfo->post_title;
			$topicID = $topicInfo->ID;		
			$romanNo = integerToRoman($currentTopicNumber);
			/* Get the sessions in the topic 
			$args = array(
                'sort_order'    => 'asc',
                'sort_column'   => ',menu_order',
                'parent'        => $topicID,
                'post_type'     => 'topic_session',
                'post_status'   => 'publish'
			); 
			*/
            
			//$thumbnail = get_the_post_thumbnail($topicID);
            $topicSessions = getTopicSessions ($topicID);
			$sessionCount = count($topicSessions);
			$topicLink = get_permalink($topicID);
            $imageInfo = wp_get_attachment_image_src( get_post_thumbnail_id( $topicID ), 'full' );
            $image_url = $imageInfo[0] ? esc_attr( $imageInfo[0] ) : get_stylesheet_directory_uri().'/images/topic_placeholder.jpg';

   			// Also get the Percent complete			
			// Get the sessions
			$mySessions = getTopicSessions($topicID);					
			$topicPercentComplete = 0;
			$totalPercent=0;
			foreach ($mySessions as $sessionInfo)
			{
				$sessionID = $sessionInfo->ID;		
				$subpagesArray = imperialCourse_draw::drawSubpagesMenu($sessionID);
				$percentComplete = $subpagesArray['percentComplete'];
				$totalPercent = $totalPercent +$percentComplete;
			}
			if($sessionCount>=1)
			{
				// Topic Percent Complete
				$topicPercentComplete = round(($totalPercent/$sessionCount), 0);	
			}
			
	
            
            $str .= '<div class="topic_tile">';
            $str .=     '<div>';
            $str .=         '<a href="' .$topicLink. '">';
            $str .=             '<div class="image" style="background-image:url(' .$image_url. ');"></div>';
            $str .=             '<div class="overlay">';
            $str .=                 '<div class="title">' .$topicName. '</div>';
            $str .=                 '<div class="progression">' .$topicPercentComplete. '% complete</div>';
            $str .=             '</div>';
            $str .=         '</a>';
            $str .=     '</div>';
            $str .= '</div>';
            

			
			
			$currentTopicNumber++;
		}
		
		$str.='</div></div>';
		
		return $str;
	}	
	
	
	static function drawSessionsShortcode($atts)
	{
		$atts = shortcode_atts( 
			array(
				'id'		=> ''
				), 
			$atts
		);
		
		
		$topicID = (int) $atts['id'];	
		
		$sessionsStr = imperialCourse_draw::drawSessionsInTopic($topicID);
		return $sessionsStr;
	}
	
	
	static function drawSessionsInTopic($topicID)
	{
		
		
		
		$topicNaming = getTopicNaming();
		$level1Name = $topicNaming[0];
		$level1Plural = pluralize($level1Name);
		$level2Name = $topicNaming[1];
		$level2Plural = pluralize($level2Name);
		$level3Name = $topicNaming[2];
		$level3Plural = pluralize($level3Name);


		// Find out if we need to skip to the first page instead of showing lecture overview
		$skipLectureOverviewPage = get_option( 'skipLectureOverviewPage'  );	
		
		
		
		$sessions = getTopicSessions ($topicID);
		$str='';
		$str.='<div class="imperial-grid-container">';
		$currentSesson = 1;

		foreach ($sessions as $sessionInfo)
		{
			$sessionName = $sessionInfo->post_title;
			$sessionID = $sessionInfo->ID;
			$sessionURL = get_permalink($sessionID);
			
			// Get the Percent complete
			
			// Get the SubMenu array info
			$submenuArray  = imperialCourse_draw::drawSubpagesMenu($sessionID);	
			
			if($skipLectureOverviewPage=="on")
			{
				$firstPageID = $submenuArray['firstPageID'];	
				$sessionURL = get_permalink($firstPageID);
			}

			$percentComplete = $submenuArray['percentComplete'];	
			$totalPages = $submenuArray['totalPages'];	
			
			// Get the Session Date
			$sessionDateStr = '';
			$sessionDate = get_post_meta($sessionID, "sessionDate", true);
			if($sessionDate)
			{
				$sessionDateStr =date("l jS F, Y", strtotime($sessionDate));
			}
			

			
			$session_slides_ref = get_post_meta($sessionID, "session_slides_ref", true);
			$sessionDuration = get_post_meta($sessionID, "sessionDuration", true);
			
			$str.='<div class="sessionListDiv imperial-flexbox">';
			$str.='<div class="sessionInfo">';
			$str.='<div class="sessionNumber">'.$level2Name.' '.$currentSesson.'</div>';
			$str.='<div class="sessionTitle">';
			
			// Check the Available From and to Dates
			$canViewSessionToday = checkCanViewSessionByDate($sessionID, $sessionURL);
			if($canViewSessionToday[0] == true || $skipLectureOverviewPage=="")
			{
				$str.='<a href="'.$sessionURL.'">'.$sessionName.'</a>';
			}
			else
			{
				$str.=$sessionName;
			}
			
			$str.='</div>';	
			
			
			
			
			$str.='<div class="sessionDate"><strong>'.$sessionDateStr.'</strong></div>';
			
			$str.='<div class="smallText">'.$totalPages.' '.$level3Name.'(s).</div>';
			
			
			if($canViewSessionToday[0] == false)
			{	
				$str.='<div class="greyText">'.$canViewSessionToday[1].'</div>';
			}
			
			
			$str.='</div>'; // Close the info div
			
			$str.='<div class="sessionMetaContent">';
			
			$radialProgress = imperialCourse_draw::drawRadialProgress($percentComplete);
			
			if($canViewSessionToday[0] == true || $skipLectureOverviewPage=="")
			{
				$str.='<a href="'.$sessionURL.'">'.$radialProgress.'</a>';
			}
			else
			{
				$str.=$radialProgress;
			}
			
			
			$str.='</div>';
			
			
			
			$str.='<div class="sessionMetaContent">';
			
			
			if($session_slides_ref)
			{
				$slidesFolder = getSlidesFolderURL();
				$slidesURL = $slidesFolder.'/'.$session_slides_ref;	

				$fileExt = pathinfo($session_slides_ref, PATHINFO_EXTENSION);
				$fileImg = get_stylesheet_directory_uri().'/images/file-type-icons/'.$fileExt.'-icon.png';
				
				$str.='<a href="'.$slidesURL.'" taget="blank">';
				$str.='<img src="'.$fileImg.'" width="60px">';
				$str.='<br/>Slides</a>';
			}
			
			$str.='</div>'; //  End of slides
			
			$str.='<div class="sessionMetaContent">';
			
			if($sessionDuration)
			{		
				$str.='<i class="far fa-clock fa-5x"></i><br/>';
				$str.=$sessionDuration.' minutes';
			}
			
			
			//$str.='<a href="http://wp.elearningimperial.com/neuroscience-and-mental-health/topics/introduction-to-neuroscience-and-neurology/knowledge-check/"><img src="http://wp.elearningimperial.com/neuroscience-and-mental-health/wp-content/uploads/sites/11/2018/01/Tests-icon1.png" width="60px">';
			//$str.='<br/>Knowledge Check</a>';
			$str.='</div>'; //  End of Knowledge Check
			
			$str.='</div>';
			
			$currentSesson++;
		}
		$str.='</div>';
		
		return $str;
		
	}	
	
	
	
	static function drawContacts()
	{
	
		$str='';
		
		$args = array(
			'sort_order' => 'asc',
			'sort_column' => ',menu_order',			
			'post_type' => 'course_contact',
			'post_status' => 'publish'
		); 
		$contacts = get_pages($args); 
		
		
		$courseLeaderStr = '';
		$contactStr='';
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
						$courseLeaderStr.= '<div class="courseLeaderAvatar">'.get_the_post_thumbnail( $postID, array(200, 200) ).'</div>';
					}
					$courseLeaderStr.='<span class="courseLeaderName">'.$contactName.'</span><br/><a href="mailto'.$email.'">'.$email.'</a>';
			}
				
				
			
			
			else
			{
				$contactStr.= '<tr><td width="20px"><i class="fa fa-user-circle fa-2x" aria-hidden="true"></i></td><td>'.$contactName.'</td><td><a href="mailto:'.$email.'">'.$email.'</a></td></tr>';
			}
		
		}
		
		$contactStr = '<table class="contactsTable">'.$contactStr.'</table>';
		
		$str ='<div class="row">';
		$str.='<div class="col-md-4 centreContent">';
		$str.='<h2>Course Leader</h2>';
		$str.=$courseLeaderStr;
		$str.='</div>';
		$str.='<div class="col-md-8 centreContent"><h2>Teaching Staff</h2>';
		$str.=$contactStr;
		$str.='</div>';
		$str.='</div>';
		return $str;
	
	}
	
	
	public static function drawIbooksList()
	{
	
		
		$str='';
		$courseList = getCourseList();
		
		$tabLinks = '';
		
		$tabContent = '';
			
			
			
		
			
		foreach ($courseList as $year => $yearModules)
		{
		
			$tabLinks.='<li>'.$year.'</li>';
			
			$tabContent.='<div>';
			
			foreach($yearModules as $theme => $moduleArray)
			{
			
				$divID = generateRandomString();
				$tabContent.='<div id="click'.$divID.'" class="iBooksThemeTitle"><a href="javascript:void();">'.$theme.' <i class="fa fa-plus-square-o" aria-hidden="true"></i></a></div>';
				$tabContent.='<div class="container" id="'.$divID.'" style="display:none;">';
				
				$tabContent.='<div class="row">';
				foreach ($moduleArray as $moduleName)
				{
					
					
					$tabContent.='<div class="col-sm-3 iBookDiv">';
					$tabContent.='<a href="http://wp.elearningimperial.com/neuroscience-and-mental-health"><img src="http://wp.elearningimperial.com/ibooks/wp-content/uploads/sites/9/2018/01/iBook_cover.png" width="70px"><br/>';
					$tabContent.=$moduleName.'</a>';
					$tabContent.='</div>';
				}
				$tabContent.='</div>'; // End of Row				
				$tabContent.='</div>'; // End of Container
				
				
				$tabContent.='<script>jQuery( "#click'.$divID.'" ).click(function() {
  jQuery( "#'.$divID.'" ).toggle( "fast", function() {
    // Animation complete.
  });
});
</script>';
			}
			
			
			$tabContent.='</div>';
		}
		
		
		
		
		$str.='<div class="ekTabsWrapper vertTabs">'; // tabs Wrapper
		
		$str.='<div class="ekTabLinks"><ul>';
		$str.=$tabLinks;
		$str.='</ul></div>'; // End of tab links
		
		
		$str.='<div class="ekTabsContent">';
		$str.=$tabContent;
		$str.='</div>';
		
		$str.='</div>'; // End of tab wrapper
		
		
	
	
		//$str.='<img src="http://wp.elearningimperial.com/ibooks/wp-content/uploads/sites/9/2018/01/iBook_cover.png">';
		
		return $str;
	}
	
	
	public static function drawTopicsTree()
	{
		
		
		$str = '';
		
		/*
		$str.='<div class="ekTabsWrapper vertTabs">';		
		$str.='<div class="ekTabLinks">';
		$str.='<ul>';
		$str.='<li>Lectures</li>';
		$str.='<li>Practicals</li>';
		$str.='<li>Tutorials</li>';
		$str.='</ul>';
		$str.='</div>';
		
				
		$str.='<div class="ekTabsContent">';
		$str.='<div>';
		$str.='Content 1';
		$str.='</div>';
		$str.='<div>';
		$str.='Content 2';
		$str.='</div>';
		$str.='<div>';
		$str.='Content 3';
		$str.='</div>';		
		
		$str.='</div></div>';
		
		*/
		
		
		
		
		
		
		
		// Get the Topics
		$myTopics = getTopics();
		
		$topicIDarray = array();
		
		
		
		$str.='<div class="ekTabsWrapper vertTabs">';
		
		
		// Draw the tabs 
		$str.='<div class="ekTabLinks">';
		$str.='<ul>';
		
		foreach ($myTopics as $topicInfo)
		{
			$topicTitle = $topicInfo -> post_title;
			$topicID = $topicInfo -> ID;
			$topicLink = get_the_permalink($topicID);
			$topicIDarray[] = $topicID;
			
			
			$str.='<li>';
			$str.=$topicTitle;
			$str.'</li>';
			
		
		}
		
		$str.='</ul></div>';
		
		
		
		// Draw the tab content
		$str.='<div class="ekTabsContent">';
		foreach ($topicIDarray as $topicID)
		{
			
			
//			$str.='<div>';
			$str.='<div class="topicsTOC">';
			
			// Show the sessions
			$mySessions = getTopicSessions($topicID);			
			
			
			$str.= '<ul>';
			foreach ($mySessions as $sessionInfo)
			{
				$sessionTitle = $sessionInfo -> post_title;
				$sessionID = $sessionInfo -> ID;
				$sessionLink = get_the_permalink($sessionID);				
				$str.= '<li><a href="'.$sessionLink.'">'.$sessionTitle.'</a></li>';
				
				// Show the pages
				$myPages = getSessionPages($sessionID);			
				$str.= '<ul>';
				foreach ($myPages as $pageInfo)
				{
					$pageTitle = $pageInfo -> post_title;
					$pageID = $pageInfo -> ID;
					$pageLink = get_the_permalink($pageID);				
					$str.= '<li><a href="'.$pageLink.'">'.$pageTitle.'</a></li>';
				}
				
				$str.= '</ul>';
				
				
			}
			
			
			$str.= '</ul>';
			
			
		//	$str.=$topicID.'<br/>';
			
			$str.='</div>';
			
			
		}
		
		$str.='</div></div>';
		
		
		
		return $str;
	}
	
	public static function drawLearningContentTree($args=array("admin" => false) )
	{

		$myClass = 'topicsTOC';
		$adminView = false;
		if(is_array($args) )
		{
			$adminView=$args['admin'];
		}
		if($adminView=="true")
		{
			$myClass = "contentTreeMenu";
		}
				
		$str='<div class="'.$myClass.'">';
		
		// Get the Topics
		$myTopics = getTopics();
		$str.='<ul>';
		foreach ($myTopics as $topicInfo)
		{
			$topicTitle = $topicInfo -> post_title;
			$topicID = $topicInfo -> ID;
			$pageLink = get_the_permalink($topicID);
			$adminEditLink = 'post.php?post='.$topicID.'&action=edit';
			if($adminView==true)
			{
				$pageLink = $adminEditLink;
			}
			
			$str.='<li><a href="'.$pageLink.'">'.$topicTitle.'</a></li>';
			
			
			
			// Show the sessions
			$mySessions = getTopicSessions($topicID);			
			$str.= '<ol>';
			foreach ($mySessions as $sessionInfo)
			{
				$sessionTitle = $sessionInfo -> post_title;
				$sessionID = $sessionInfo -> ID;
				$pageLink = get_the_permalink($sessionID);	
				$adminEditLink = 'post.php?post='.$sessionID.'&action=edit';			
		
				if($adminView==true)
				{
					$pageLink = $adminEditLink;
				}

				
				$str.= '<li><a href="'.$pageLink.'">'.$sessionTitle.'</a></li>';

				
				// Show the pages
				$myPages = getSessionPages($sessionID);			
				$str.= '<ol>';
				foreach ($myPages as $pageInfo)
				{
					$pageTitle = $pageInfo -> post_title;
					$pageID = $pageInfo -> ID;
					$pageLink = get_the_permalink($pageID);		

					$adminEditLink = 'post.php?post='.$pageID.'&action=edit';			
			
					if($adminView==true)
					{
						$pageLink = $adminEditLink;
					}


					
					$str.= '<li><a href="'.$pageLink.'">'.$pageTitle.'</a></li>';
				}				
				$str.= '</ol>';
				
				
			}
			$str.= '</ol>';
			
			
		}
		
		$str.='</ul>';
		
		$str.='</div>';
		
		
		
		return $str;
	}	
	
	
	
	// This creates a string of the sub menu and returns an array of the string, algin with prev and next page IDs 
	public static function drawSubpagesMenu($sessionID, $currentPageID="", $level2Name="Session")
	{
		
		// Get the Session Name
		$sessionName = get_the_title($sessionID);
		
		
		$userEntryLookupArray = array();
				
		// Check to see if the user stats plugin is activated. If so get the stat data
		if (class_exists('ek_user_stats'))
		{
			$currentUserID = get_current_user_id();
						
			// Get a lookup array of page IDs this person has viewed
			$ek_user_stats_db= new ek_user_stats_db();
			$userRecords = $ek_user_stats_db->getRecords($currentUserID);
			
			foreach ($userRecords as $entryInfo)
			{
				$pageID = $entryInfo->page_id;
				$userEntryLookupArray[$pageID] = true;
			}
		}
		
		$siblings = getSessionPages ( $sessionID );
		
		$str = '';

		
		
		
        $menuHTML = '';
		//$menuHTML.='<div class="subMenuWrap">';
		//$menuHTML.= '<ol class="sessionSubpageMenu">';
       // $menuHTML.='<div class="sub_menu_wrap">';
		$menuHTML.= '<ol>';
        
        
		$menuIDarray    = array(); // Create an Array for lookup previous / next IDs.		
		$currentPageNo  = 1;
		$pagesComplete  = 0;
		$siblingCount   = count($siblings);
		$menuGroupClass='hidden';
		$firstPageID = '';
		$lastPageID = '';
		
		
		
		
		foreach($siblings as $siblingInfo)
		{
			$pageName           = $siblingInfo->post_title;
			$pageID             = $siblingInfo->ID;
			$pageURL            = get_permalink($pageID);			
			$menuIDarray[]      = $pageID;
			$thisPageComplete   = false; // By default make as not read
			
			// If this pageID is in array it's complete
			if (array_key_exists($pageID, $userEntryLookupArray) || $pageID==$currentPageID) {
				$pagesComplete++;
				$thisPageComplete = true;
			}
            
           // $li_class       = $thisPageComplete ? 'subpage_complete' : '';
       //     $li_class      = ( $currentPageID == $pageID ) ? ' activeTab' : '';
			
			$li_class = '';
			
			
			
			if($currentPageID==$pageID)
			{
				$li_class = 'activeTab';
				$menuGroupClass='';
				
			}
			
            
            $status_colour  = $thisPageComplete ? 'green' : '#e0e0e0';
            $romanNo        = integerToRoman( $currentPageNo );
            
            $menuHTML .= '<li class="' .$li_class. '">';
            $menuHTML .=    '<a href="' .$pageURL. '">';
			$menuHTML .=        '<div class="linkText"><div class="numeral">' .$romanNo. '.</div>';
            $menuHTML .=        '<div class="pagename">' . $pageName . '</div></div>';
			
			
			if($thisPageComplete==true)
			{
				/*
				$menuHTML .=        '<div class="status">';
				$menuHTML .=            '<div class="fa-x"><span class="fa-layers fa-fw">';
				$menuHTML .=                '<i class="fas fa-circle status_bg" style="color:' .$status_colour. ';"></i>';
				$menuHTML .=                '<i class="fa-inverse fas fa-check" data-fa-transform="shrink-6"></i>';
				$menuHTML .=            '</span></div>';
				$menuHTML .=        '</div>';
				*/
				
				$menuHTML.='<div class="status">';
				$menuHTML.='<div class="fa-stack fa-1x" style="margin:10px;">';
				$menuHTML.='<i class="fa fa-circle fa-stack-1x"  style="color:' .$status_colour. ';"></i>';
				$menuHTML.='<i class="fa fa-check fa-stack-1x fa-inverse fa-xs"></i>';
				$menuHTML.='</div></div>';
				
				
			}
            $menuHTML .=        '<br class="clearB">';
            $menuHTML .=    '</a>';
            $menuHTML .= '</li>';
            
            
         
            
			/*
			$menuHTML.= '<li ';
			
			if($thisPageComplete==true)
			{
				$menuHTML.='class="subpageComplete"';
			}			
			$menuHTML.='><div style="float:left">';	
			if($currentPageID==$pageID)
			{
				$menuHTML.= '<strong>';
			}
			$romanNo = integerToRoman($currentPageNo);
			
			$menuHTML.= '<a href="'.$pageURL.'">';
			if ($thisPageComplete==true) {
				//$menuHTML.='<i class="fas fa-check"></i>';
			}			
			//$menuHTML.='<div class="romanNo">'.$romanNo.'.</div>';
			$menuHTML.= '<div class="menuText">'.$romanNo.'. '. $pageName.'</div></a>';
			if($currentPageID==$pageID)
			{
				$menuHTML.= '</strong>';
			}
			
			$menuHTML.='</div>';
			
			if ($thisPageComplete==true) {
				$menuHTML.='<div class="fa-x" style="float:right;">
				<span class="fa-layers fa-fw">
				<i class="fas fa-circle" style="color:green"></i>
				<i class="fa-inverse fas fa-check" data-fa-transform="shrink-6"></i>
				</span>';
			}			
			
			$menuHTML.= '</li>';
			*/
			
			
			if($currentPageNo==1)
			{
				$firstPageID = $pageID;
			}
			
			if($currentPageNo==$siblingCount)
			{
				$lastPageID = $pageID;
			}
			
			
            
            
			$currentPageNo++;		
			
		}
		$menuHTML.= '</ol>';
		//$menuHTML.='</div>';
		
		
		// Get Percent Complete
		$percentComplete = 0;
		if($siblingCount>=1)
		{
			$percentComplete = round(($pagesComplete/$siblingCount)*100, 0);
		}
		
				
		// Now get the next and prev items		
		$nextPage = false; // Default
		$prevPage = false; // Default
		
		if($currentPageID)
		{			
			
			$thisPageKey = array_search ($currentPageID, $menuIDarray);
			
			// If its the FIRST PAGE there is no prev page
			if($thisPageKey==0){
				$prevPage = false;

			}
			else
			{
				$prevPage = $menuIDarray[$thisPageKey-1];
			}
			
			// if its the last page there is no next page
			if(($thisPageKey+1) == $siblingCount)
			{
				$nextPage = false;
			}
			else
			{
				if(isset($menuIDarray[$thisPageKey+1]) )
				{
					$nextPage = $menuIDarray[$thisPageKey+1];
				}
			}
		}
		
		//$str.='<div class="sessionMenuTitle">'.$level2Name.' Content</div>';
		
		
		
		$progressBar = imperialCourse_draw::drawRadialProgress($percentComplete);
		$str.='<ol>';
		$str.='<li>';
		$str.='<div class="sessionNameWrap" id="session_'.$sessionID.'">';
		$str.='<div class="sessionName">'.$sessionName.'</div>';
		$str.='<div class="submenuSessionProgress">'.$progressBar.'</div>';
		$str.='</div>';
		
		$str.='<ol id="session'.$sessionID.'_menuWrap" class="'.$menuGroupClass.'">';
		$str.=$menuHTML;
		$str.='</li></ol>';
		$str.='</ol>';
		
		$subMenuArray = array(
			"menuStr"			=> $str,
			"nextPage"			=> $nextPage,
			"prevPage"			=> $prevPage,
			"pagesComplete"		=> $pagesComplete,
			"percentComplete" 	=> $percentComplete,
			"totalPages"		=> $siblingCount,
			"firstPageID"		=> $firstPageID,
			"lastPageID"		=> $lastPageID,
		);
		
		$canViewSessionToday = checkCanViewSessionByDate($sessionID);
		if(current_user_can("manage_options") )
		{
			
		}
		elseif($canViewSessionToday[0]==false)		
		{			
			
			$subMenuArray["menuStr"] = "";
		}	
		
		return $subMenuArray;		
	}
	
	// This creates a string of the sub menu and returns an array of the string, algin with prev and next page IDs 
	public static function drawSubpagesMenuBCKUP($sessionID, $currentPageID="", $level2Name="Session")
	{
		
		// Get the Session Name
		$sessionName = get_the_title($sessionID);
		
		
		$userEntryLookupArray = array();
				
		// Check to see if the user stats plugin is activated. If so get the stat data
		if (class_exists('ek_user_stats'))
		{
			$currentUserID = get_current_user_id();
						
			// Get a lookup array of page IDs this person has viewed
			$ek_user_stats_db= new ek_user_stats_db();
			$userRecords = $ek_user_stats_db->getRecords($currentUserID);
			
			foreach ($userRecords as $entryInfo)
			{
				$pageID = $entryInfo->page_id;
				$userEntryLookupArray[$pageID] = true;
			}
		}
		
		$siblings = getSessionPages ( $sessionID );
		
		$str = '';

		
		
		
        $menuHTML = '';
		//$menuHTML.='<div class="subMenuWrap">';
		//$menuHTML.= '<ol class="sessionSubpageMenu">';
       // $menuHTML.='<div class="sub_menu_wrap">';
		$menuHTML.= '<ol>';
        
        
		$menuIDarray    = array(); // Create an Array for lookup previous / next IDs.		
		$currentPageNo  = 1;
		$pagesComplete  = 0;
		$siblingCount   = count($siblings);
		$menuGroupClass='hidden';
		$firstPageID = '';
		$lastPageID = '';
		
		
		
		
		foreach($siblings as $siblingInfo)
		{
			$pageName           = $siblingInfo->post_title;
			$pageID             = $siblingInfo->ID;
			$pageURL            = get_permalink($pageID);			
			$menuIDarray[]      = $pageID;
			$thisPageComplete   = false; // By default make as not read
			
			// If this pageID is in array it's complete
			if (array_key_exists($pageID, $userEntryLookupArray) || $pageID==$currentPageID) {
				$pagesComplete++;
				$thisPageComplete = true;
			}
            
           // $li_class       = $thisPageComplete ? 'subpage_complete' : '';
       //     $li_class      = ( $currentPageID == $pageID ) ? ' activeTab' : '';
			
			$li_class = '';
			
			
			
			if($currentPageID==$pageID)
			{
				$li_class = 'activeTab';
				$menuGroupClass='';
				
			}
			
            
            $status_colour  = $thisPageComplete ? 'green' : '#e0e0e0';
            $romanNo        = integerToRoman( $currentPageNo );
            
            $menuHTML .= '<li class="' .$li_class. '">';
            $menuHTML .=    '<a href="' .$pageURL. '">';
			$menuHTML .=        '<div class="linkText"><div class="numeral">' .$romanNo. '.</div>';
            $menuHTML .=        '<div class="pagename">' . $pageName . '</div></div>';
			
			
			if($thisPageComplete==true)
			{
				$menuHTML .=        '<div class="status">';
				$menuHTML .=            '<div class="fa-x"><span class="fa-layers fa-fw">';
				$menuHTML .=                '<i class="fas fa-circle status_bg" style="color:' .$status_colour. ';"></i>';
				$menuHTML .=                '<i class="fa-inverse fas fa-check" data-fa-transform="shrink-6"></i>';
				$menuHTML .=            '</span></div>';
				$menuHTML .=        '</div>';
			}
            $menuHTML .=        '<br class="clearB">';
            $menuHTML .=    '</a>';
            $menuHTML .= '</li>';
            
            
         
            
			/*
			$menuHTML.= '<li ';
			
			if($thisPageComplete==true)
			{
				$menuHTML.='class="subpageComplete"';
			}			
			$menuHTML.='><div style="float:left">';	
			if($currentPageID==$pageID)
			{
				$menuHTML.= '<strong>';
			}
			$romanNo = integerToRoman($currentPageNo);
			
			$menuHTML.= '<a href="'.$pageURL.'">';
			if ($thisPageComplete==true) {
				//$menuHTML.='<i class="fas fa-check"></i>';
			}			
			//$menuHTML.='<div class="romanNo">'.$romanNo.'.</div>';
			$menuHTML.= '<div class="menuText">'.$romanNo.'. '. $pageName.'</div></a>';
			if($currentPageID==$pageID)
			{
				$menuHTML.= '</strong>';
			}
			
			$menuHTML.='</div>';
			
			if ($thisPageComplete==true) {
				$menuHTML.='<div class="fa-x" style="float:right;">
				<span class="fa-layers fa-fw">
				<i class="fas fa-circle" style="color:green"></i>
				<i class="fa-inverse fas fa-check" data-fa-transform="shrink-6"></i>
				</span>';
			}			
			
			$menuHTML.= '</li>';
			*/
			
			
			if($currentPageNo==1)
			{
				$firstPageID = $pageID;
			}
			
			if($currentPageNo==$siblingCount)
			{
				$lastPageID = $pageID;
			}
			
			
            
            
			$currentPageNo++;		
			
		}
		$menuHTML.= '</ol>';
		//$menuHTML.='</div>';
		
		
		// Get Percent Complete
		$percentComplete = 0;
		if($siblingCount>=1)
		{
			$percentComplete = round(($pagesComplete/$siblingCount)*100, 0);
		}
		
				
		// Now get the next and prev items		
		$nextPage = false; // Default
		$prevPage = false; // Default
		
		if($currentPageID)
		{			
			
			$thisPageKey = array_search ($currentPageID, $menuIDarray);
			
			// If its the FIRST PAGE there is no prev page
			if($thisPageKey==0){
				$prevPage = false;

			}
			else
			{
				$prevPage = $menuIDarray[$thisPageKey-1];
			}
			
			// if its the last page there is no next page
			if(($thisPageKey+1) == $siblingCount)
			{
				$nextPage = false;
			}
			else
			{
				if(isset($menuIDarray[$thisPageKey+1]) )
				{
					$nextPage = $menuIDarray[$thisPageKey+1];
				}
			}
		}
		
		//$str.='<div class="sessionMenuTitle">'.$level2Name.' Content</div>';
		
		
		
		$progressBar = imperialCourse_draw::drawRadialProgress($percentComplete);
		$str.='<div class="sessionNameWrap" id="session_'.$sessionID.'">';
		$str.='<div class="sessionName">'.$sessionName.'</div>';
		$str.='<div class="submenuSessionProgress">'.$progressBar.'</div></div>';
		
		$str.='<div id="session'.$sessionID.'_menuWrap" class="'.$menuGroupClass.'">';
		$str.=$menuHTML;
		$str.='</div>';
		
		$subMenuArray = array(
			"menuStr"			=> $str,
			"nextPage"			=> $nextPage,
			"prevPage"			=> $prevPage,
			"pagesComplete"		=> $pagesComplete,
			"percentComplete" 	=> $percentComplete,
			"totalPages"		=> $siblingCount,
			"firstPageID"		=> $firstPageID,
			"lastPageID"		=> $lastPageID,
		);
		
		$canViewSessionToday = checkCanViewSessionByDate($sessionID);
		if(current_user_can("manage_options") )
		{
			
		}
		elseif($canViewSessionToday[0]==false)		
		{			
			
			$subMenuArray["menuStr"] = "";
		}	
		
		return $subMenuArray;		
	}	
	
	
	
	static function drawProgressBar($myProgress)
	{
		
		$html= '<div class="progressWrap">';
		$html.= '<div class="progress">';
		$html.= '<span class="progressText">'.$myProgress.'% complete</span>';	

		if($myProgress>=1)
		{			
			$html.= '<div class="progress-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="width:'.$myProgress.'%"></div>';
		}
		$html.= '</div></div>';		
		return $html;		
		
		
	}
	
	static function drawRadialProgress($percent)
	{
	
	//	$percent = $args['percent'];		
		//$moduleName = $args['moduleName'];
		
		$str='<div class="c100 p'.$percent.'">
                    <span>'.$percent.'%</span>
                    <div class="slice">
                        <div class="bar"></div>
                        <div class="fill"></div>
                    </div>
                </div>';
					
		return $str;
	
	}		

}
	
	?>
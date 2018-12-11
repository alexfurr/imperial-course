<h1>Topic Settings</h1>


<?php




if(isset($_GET['action']))
{
	$action=$_GET['action'];
	
	switch ($action) {
		case "updateSettings":
		
			// Check the nonce before proceeding;	
			$retrieved_nonce="";
			if(isset($_REQUEST['_wpnonce'])){$retrieved_nonce = $_REQUEST['_wpnonce'];}
			if (wp_verify_nonce($retrieved_nonce, 'topicSettingsNonce' ) )
			{
			
				// Get the current set - for defaults etc or if blank
				$siteNomenclature = getTopicNaming();

			
				// Create the array that we will be saving as site option
				$namingArray = array();
				$i=1;
				while($i<=3)
				{
					//$optionName = 'level'.$i.'_naming';
					$optionValue = $_POST['level'.$i.'_name'];

					
					// If its blank use the existing defaults
					if($optionValue== "")
					{
						$optionValue= $siteNomenclature[$i-1];
					}
					
					$namingArray[] = $optionValue;
					$i++;
					
	
				}
				
				// Update the site option
				update_option( 'topicNaming', $namingArray  );


				// Update the skip homepage
				$skipLectureOverviewPage = "";
				if(isset ($_POST['skipLectureOverviewPage'] ))
				{
					$skipLectureOverviewPage = "on";
				}
				update_option( 'skipLectureOverviewPage', $skipLectureOverviewPage  );

				
				

				echo '<div class="notice notice-success"><p>Settings updated</p></div><br/>';

				
			}
			
			
			
		break;

	}	
	
}
$siteNomenclature = getTopicNaming();




// Check the site options




$level1Name = $siteNomenclature[0];
$level2Name = $siteNomenclature[1];
$level3Name = $siteNomenclature[2];

$skipLectureOverviewPage = get_option( 'skipLectureOverviewPage'  );



//$siteNomenclature = getTopicNaming();
echo '<form action="edit.php?post_type=imperial_topic&page=topic-settings&action=updateSettings" method="post">';
echo '<h2>Navigation</h2>';

echo '<label for="skipLectureOverviewPage">';
echo '<input type="checkbox" name="skipLectureOverviewPage" id="skipLectureOverviewPage"';

if($skipLectureOverviewPage=="on")
{
	echo ' checked ';
}
echo ' />Skip the '.$level2Name.' overview page';
echo '</label>';


echo '<h2>Nomenclature</h2>';





$i=1;
while($i<=3)
{
	$thisVar = 'level'.$i.'Name';
	echo '<label for="level'.$i.'_name">Level '.$i.' Name : ';
	echo '<input name="level'.$i.'_name" type="text" value="'.$$thisVar.'">';
	echo '</label><hr/>';
	
	
	
	$i++;
}

// nonce field
wp_nonce_field('topicSettingsNonce');    


echo '<input type="submit" class="button-primary" value="Save Settings"/>';


?>
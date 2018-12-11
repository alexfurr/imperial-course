<?php
function integerToRoman($integer)
{
	// Convert the integer into an integer (just to make sure)
	$integer = intval($integer);
	$result = '';
	// Create a lookup array that contains all of the Roman numerals.
	$lookup = array('M' => 1000,
	'CM' => 900,
	'D' => 500,
	'CD' => 400,
	'C' => 100,
	'XC' => 90,
	'L' => 50,
	'XL' => 40,
	'X' => 10,
	'IX' => 9,
	'V' => 5,
	'IV' => 4,
	'I' => 1);
	foreach($lookup as $roman => $value)
	{
		// Determine the number of matches
		$matches = intval($integer/$value);
		// Add the same number of characters to the string
		$result .= str_repeat($roman,$matches);
		// Set the integer to be the remainder of the integer and the value
		$integer = $integer % $value;
	}
	// The Roman numeral should be built, return it
	return $result;
}
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

// Gets the slides folder URL
function getSlidesFolderDir()
{
	$uploadsDirArray = wp_upload_dir();
	$uploadsDir = $uploadsDirArray['basedir'];
	$slidesFolder = $uploadsDir.'/slides';
	
	return $slidesFolder;
}

function getSlidesFolderURL()
{
	$uploadsDirArray = wp_upload_dir();
	$uploadsDir = $uploadsDirArray['baseurl'];
	$slidesFolder = $uploadsDir.'/slides';
	
	return $slidesFolder;
}

function getTopicNaming()
{
	// These are the defaults for the entire site
	$defaults = array(
	"Topic", "Session", "Page"
	);


	$namingArray = get_option( 'topicNaming', $defaults  );

	
	return $namingArray;
			
						
			
}


function pluralize($singular) {

    $last_letter = strtolower($singular[strlen($singular)-1]);
    switch($last_letter) {
        case 'y':
            return substr($singular,0,-1).'ies';
        case 's':
            return $singular.'es';
        default:
            return $singular.'s';
    }
}


function getCurrentDateTime()
{
	date_default_timezone_set('Europe/London');
    // Then call the date functions
    $now = date('Y-m-d H:i:s');
	return $now;	
}

function checkCanViewSessionByDate($sessionID, $linkTo="")
{
	
	$availableFromDate = get_post_meta($sessionID, "availableFromDate", true);
	$availableToDate = get_post_meta($sessionID, "availableToDate", true);		

	$currentDate = getCurrentDateTime();
	
	$canViewNow = true;
	
	if($availableFromDate)
	{
		if($availableFromDate>$currentDate)
		{			
			$canViewNow = false;
		}
	}
	
	if($availableToDate)
	{
		if($availableToDate<$currentDate)
		{			
			$canViewNow = false;
		}
	}	
	
	
	$dateErrorMsg = '';
	
	if($availableFromDate)
	{		
		$fromDateStr = new DateTime($availableFromDate);
		$fromDateStr = $fromDateStr->format('g:ia \o\n l jS F\, Y');
		// Format the date
		$dateErrorMsg.= 'This content will be available from <strong>'.$fromDateStr.'</strong>';
	}
	
	if($availableToDate)
	{		
		// Format the date
		$toDateStr = new DateTime($availableToDate);
		$toDateStr = $toDateStr->format('g:ia \o\n l jS F\, Y');
		
		
		if($availableFromDate)
		{
			$dateErrorMsg.= ' until <strong>'.$toDateStr.'</strong>';
		}
		else
		{
			$dateErrorMsg.= 'This content is no longer available';
		}
		
	}
	
	// if date error msg is not blank and they are admin apend link anyway
	if($dateErrorMsg<>"")
	{
		if($linkTo)
		{
			$thisURL = $linkTo;
		}
		else
		{
			$thisURL = get_permalink($sessionID);
		}
		$dateErrorMsg.='<br/><a href="'.$thisURL.'" class="smallText">[View this content as admin]</a>';
		
	}
	

	$accessCheck = array(
		$canViewNow,
		$dateErrorMsg,
	);
	
	return $accessCheck;	
}



?>
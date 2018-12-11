<?php
$cpt_sessions = new cpt_sessions();
class cpt_sessions
{
	
	
	
	//~~~~~
	function __construct ()
	{
		$this->addWPActions();
	}	
	
	
/*	---------------------------
	PRIMARY HOOKS INTO WP 
	--------------------------- */	
	function addWPActions ()
	{
		//Admin Menu
		add_action( 'init',  array( $this, 'create_CPTs' ) );		
		
		// Post type metaboxes
		add_action( 'add_meta_boxes_topic_session', array( $this, 'addMetaBoxes' ));
		
		// Save additional project meta for the custom post
		add_action( 'save_post', array($this, 'savePostMeta' ));
		
		// Remove and add columns in the projects table
		add_filter( 'manage_topic_session_posts_columns', array( $this, 'my_custom_post_columns' ), 10, 2 );		
		add_action('manage_topic_session_posts_custom_column', array($this, 'customColumnContent'), 10, 2);
		
		// Modify admin list main query to only show sessions with correct topicID parent	
		add_action( 'pre_get_posts', array($this, 'modify_admin_list_query' ) );
		
		// Adds the Back to Topic links and other minor nav improvements
		add_action( 'all_admin_notices', array($this, 'addBackButton_on_editPage' ) );
		
		
		
		/* If delete a session then delete all sub pages */
		//add_action('wp_trash_topic_session',array ($this, 'deleteSessionSubPages') );
		
		
	}
	
	
/*	---------------------------
	ADMIN-SIDE MENU / SCRIPTS 
	--------------------------- */
	function create_CPTs ()
	{
		
		$naming = getTopicNaming();		
		$singular = $naming[1];
		$plural = pluralize($singular);
	
		//Topics
		$labels = array(
			'name'               =>  $plural,
			'singular_name'      =>  $singular,
			'menu_name'          =>  $plural,
			'name_admin_bar'     =>  $plural,
			'add_new'            =>  'Add New '.$singular,
			'add_new_item'       =>  'Add New '.$singular,
			'new_item'           =>  'New '.$singular,
			'edit_item'          =>  'Edit '.$singular,
			'view_item'          => 'View '.$plural,
			'all_items'          => 'All '.$plural,
			'search_items'       => 'Search '.$plural,
			'parent_item_colon'  => '',
			'not_found'          => 'No '.$plural.' found.',
			'not_found_in_trash' => 'No '.$plural.' found in Trash.'
		);
	
		$args = array(
			'menu_icon' => 'dashicons-portfolio',		
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_nav_menus'	 => false,
			'show_in_menu'       => false,
			'query_var'          => true,
			'rewrite' => array( 'slug' => 'sessions' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'supports'           => array( 'title', 'editor', 'revisions'  )
			
		);
		
		
		
		register_post_type( 'topic_session', $args );
		
	}
	
	
	
	// Register the metaboxes on  CPT
	function  addMetaBoxes()
	{
		

		$naming = getTopicNaming();
		$topicSingular = $naming[0];
		$topicPlural = pluralize($topicSingular);	
		$sessionSingular = $naming[1];		
		$sessionPlural = pluralize($sessionSingular);			
		$pageSingular = $naming[2];
		$pagePlural = pluralize($pageSingular);				
		
			
		//Session Date Metabox
		$id 			= 'topic_session_settings';
		$title 			= $sessionSingular.' Details';
		$drawCallback 	= array( $this, 'drawMetaBox_sessionOptions' );
		$screen 		= 'topic_session';
		$context 		= 'side';
		$priority 		= 'default';
		$callbackArgs 	= array();
		
		add_meta_box( 
			$id, 
			$title, 
			$drawCallback, 
			$screen, 
			$context,
			$priority, 
			$callbackArgs
		);
		
		//Lecture SlidesMetabox
		$id 			= 'topic_session_slides';
		$title 			= 'Slides';
		$drawCallback 	= array( $this, 'drawMetaBox_sessionSlides' );
		$screen 		= 'topic_session';
		$context 		= 'side';
		$priority 		= 'default';
		$callbackArgs 	= array();
		
		add_meta_box( 
			$id, 
			$title, 
			$drawCallback, 
			$screen, 
			$context,
			$priority, 
			$callbackArgs
		);		
		
		//Availability Metabox
		$id 			= 'topic_session_availability';
		$title 			= 'Availability';
		$drawCallback 	= array( $this, 'drawMetaBox_sessionAvailability' );
		$screen 		= 'topic_session';
		$context 		= 'side';
		$priority 		= 'default';
		$callbackArgs 	= array();
		
		add_meta_box( 
			$id, 
			$title, 
			$drawCallback, 
			$screen, 
			$context,
			$priority, 
			$callbackArgs
		);		
		
		//Subpages Metabox
		$id 			= 'topic_session_pages';
		$title 			= $sessionSingular.' '.$pagePlural;
		$drawCallback 	= array( $this, 'drawMetaBox_sessionPages' );
		$screen 		= 'topic_session';
		$context 		= 'side';
		$priority 		= 'default';
		$callbackArgs 	= array();
		
		add_meta_box( 
			$id, 
			$title, 
			$drawCallback, 
			$screen, 
			$context,
			$priority, 
			$callbackArgs
		);			
		
		//Learning outcomes Metabox
		$id 			= 'topic_session_outcomes';
		$title 			= 'Learning Outcomes';
		$drawCallback 	= array( $this, 'drawMetaBox_learningOutcomes' );
		$screen 		= 'topic_session';
		$context 		= 'normal';
		$priority 		= 'high';
		$callbackArgs 	= array();
		
		add_meta_box( 
			$id, 
			$title, 
			$drawCallback, 
			$screen, 
			$context,
			$priority, 
			$callbackArgs
		);		
		
		
	}
	
	function drawMetaBox_sessionOptions($post, $metabox)
	{
		// Add Nonce Field
		wp_nonce_field( 'save_session_metabox_nonce', 'session_metabox_nonce' );
		
		$sessionDate = get_post_meta($post->ID,'sessionDate',true);		
		$sessionDuration = get_post_meta($post->ID,'sessionDuration',true);
		
		
        //echo '<br>get:' . $_GET['topicID'];
        
		/* Spit out the Parent ID */	
		if(isset($_GET['topicID']))
		{
			$parentID = $_GET['topicID'];
			$_SESSION['currentTopicID'] = $parentID; // This is picked up on submit page
		}
		else
		{
			$parentID = wp_get_post_parent_id( $post->ID );
		}	
		
		if(!$parentID){$parentID = $_SESSION['currentTopicID'];}
		
		
		
		echo '<input type="hidden" id="parentID" name="parentID" value = "'.$parentID.'" />';
		
		echo '<label for="sessionDate">Session Date</label><br/>';
		echo '<input type="text" name="sessionDate" id="sessionDate" size="12" value="'.$sessionDate.'"/>';	
		echo '<hr/>';	
		
		
		// Enable Date Picker
		?>
		<script>
					jQuery( document ).ready( function ()
					{
					jQuery('#sessionDate').datepicker({
						dateFormat : 'dd-mm-yy'
					});
						
				});
		</script>
		<?php	

		// Session Duration Input
		echo '<label for ="sessionDuration">';
		echo 'Session Duration (optional)<br/>';
		echo '<input type="text" size="3" name="sessionDuration" id="sessionDuration" value = "'.$sessionDuration.'" /> minutes';
		echo '</label>';
	}
	
	
	function drawMetaBox_sessionSlides($post, $metabox)
	{
		
		$session_slides_ref = get_post_meta($post->ID, "session_slides_ref", true);
		
		$divStyle = 'block'; // By default show the upload
		if($session_slides_ref)
		{
			
			$fileExt = pathinfo($session_slides_ref, PATHINFO_EXTENSION);

			//echo 'File = '.$session_slides_ref;
			
			echo '<div id="adminSlidesLinkDiv">';
			
			$slidesFolder = getSlidesFolderURL();
			$slidesURL = $slidesFolder.'/'.$session_slides_ref;
			echo '<a href="'.$slidesURL.'" target="blank">';
			echo '<img src="'.get_stylesheet_directory_uri().'/images/file-type-icons/'.$fileExt.'-icon.png" width="70px"><br/>';
			echo 'View Slides</a>';			
			echo '<hr/><span id="removeSlidesButton" class="button-secondary">Remove Slides</span>';			
			echo '</div>';
			$divStyle = 'none';			
		}
		
		echo '<div id="adminSlidesRestoreDiv" style="display:none">';
		echo '<span id="restoreSlidesButton" class="button-secondary">Restore slides</span><hr/>';
		echo '</div>';
		
		echo '<div id="adminSlidesUploadDiv" style="display:'.$divStyle.'">';
		echo '<input type="file" id="session_slides_ref" name="session_slides_ref" value="" size="25" />';
		echo '</div>';
		
		// This input is key - set to true to delete slides
		echo '<input id="deleteSlides" name="deleteSlides" value="false" type="hidden">';

	}
	
	
	function drawMetaBox_sessionAvailability($post, $metabox)
	{
		
		$availableFromDate = get_post_meta($post->ID,'availableFromDate',true);
		$availableToDate = get_post_meta($post->ID,'availableToDate',true);
		
		$fromHour = '';
		$fromMin = '';
		$fromAMPM = '';
		$toHour = '';
		$toMin = '';
		$toAMPM = '';
		
		if($availableFromDate)
		{
			$availableFromDateTime = new DateTime($availableFromDate);
			$availableFromDate = $availableFromDateTime->format('d-m-Y');
			$fromHour = $availableFromDateTime->format('g');
			$fromMin = $availableFromDateTime->format('i');
			$fromAMPM = $availableFromDateTime->format('A');
		}
		
		if($availableToDate)
		{
			$availableToDateTime = new DateTime($availableToDate);
			$availableToDate = $availableToDateTime->format('d-m-Y');
			$toHour = $availableToDateTime->format('g');
			$toMin = $availableToDateTime->format('i');
			$toAMPM = $availableToDateTime->format('A');
		}		

			
		echo '<label for="availableFromDate">Available From</label><br/>';
		echo '<input type="text" name="availableFromDate" id="availableFromDate" size="12" value="'.$availableFromDate.'"/>';	
		echo '<br/><select name="fromHour">';
		$i=1;
		while ($i<=12)
		{
			echo '<option value="'.$i.'" ';
			if($fromHour==$i){echo ' selected';}			
			echo '>'.$i.'</option>';
			$i++;
		}
		echo '</select>';
		
		echo '<select name="fromMin">';
		$i=0;
		while ($i<=55)
		{
			$thisMin = $i;			
			if($thisMin==0 || $thisMin==5)
			{
				$thisMin = '0'.$thisMin;				
			}
			echo '<option value="'.$thisMin.'" ';			
			if($fromMin==$i){echo ' selected';}
			echo '>'.$thisMin.'</option>';
			$i = $i+5;
		}
		echo '</select>';

		echo '<select name="fromAMPM">';
		echo '<option value="AM" ';	
		if($fromAMPM=="AM"){echo ' selected';}
		echo '>AM</option>';
		
		echo '<option value="PM" ';	
		if($fromAMPM=="PM"){echo ' selected';}
		echo '>PM</option>';
		
		echo '</select>';
		echo '<hr/>';
		
		echo '<label for="availableToDate">Available To</label><br/>';
		echo '<input type="text" name="availableToDate" id="availableToDate" size="12" value="'.$availableToDate.'"/>';	
		
		echo '<br/><select name="toHour">';
		$i=1;
		while ($i<=12)
		{
			echo '<option value="'.$i.'" ';
			if($toHour==$i){echo ' selected';}			
			echo '>'.$i.'</option>';
			$i++;
		}
		echo '</select>';
		
		echo '<select name="toMin">';
		$i=0;
		while ($i<=55)
		{
			$thisMin = $i;
			echo 'min = '.$thisMin;
			if($thisMin==0 || $thisMin==5)
			{
				$thisMin = '0'.$thisMin;				
			}
			echo '<option value="'.$thisMin.'" ';	
			if($toMin==$i){echo ' selected';}
			echo '>'.$thisMin.'</option>';
			$i = $i+5;
		}
		echo '</select>';

		echo '<select name="toAMPM">';
		echo '<option value="AM" ';		
		if($toAMPM=="AM"){echo ' selected';}
		echo '>AM</option>';
		
		echo '<option value="PM" ';			
		if($toAMPM=="PM"){echo ' selected';}		
		echo '>PM</option>';
		
		echo '</select>';		
		echo '<hr/>';	
		
		
		
		// Enable Date Picker
		?>
		<script>
			jQuery( document ).ready( function ()
			{
				jQuery('#availableFromDate').datepicker({
				dateFormat : 'dd-mm-yy'
				});
				
				jQuery('#availableToDate').datepicker({
				dateFormat : 'dd-mm-yy'
				});				
			});
		</script>	
		<?php
		
		
		
	}
	
	function drawMetaBox_sessionPages($post, $metabox)
	{
		$naming = getTopicNaming();
		$topicSingular = $naming[0];
		
		$sessionSingular = $naming[1];
		$sessionPlural = pluralize($sessionSingular);
		$pageSingular = $naming[2];
		$pagePlural = pluralize($pageSingular);

		
		
		$sessionID = $post->ID;
		$sessionTitle = $post->post_title;
		$parentID = wp_get_post_parent_id( $sessionID );	
		$parentTitle = get_the_title($parentID);
		$parentURL = 'post.php?post='.$parentID.'&action=edit';

		
		echo '<a class="button-secondary" href="post-new.php?post_type=session_page&sessionID='.$sessionID.'">';
		echo 'Add new '.$sessionSingular.' '.$pageSingular;
		echo '</a><hr/>';
		
		
		
		// Get array of children
		$sessionPages = getSessionPages($sessionID);
		
		echo 'Current '.$pagePlural.' in ';
		echo '<b>'.$sessionTitle.'</b>';
		

		echo '<ol>';
		foreach ($sessionPages as $pageInfo)
		{
			$pageTitle =  $pageInfo->post_title;
			$pageID =  $pageInfo->ID;
			echo '<li><a href="post.php?post='.$pageID.'&action=edit">'.$pageTitle.'</a></li>';
			
		}		
		echo '</ol>';
		
		echo '<span class="smallText">'.$topicSingular.' : <a href="'.$parentURL.'">'.$parentTitle.'</a></span><br/>';		
		
	}
	

	
	function drawMetaBox_learningOutcomes($post, $metabox)
	{
        $learning_outcomes = get_post_meta( $post->ID, 'learning_outcomes', true );
        
        $editorID = 'learning_outcomes';
        $editor_args = array(
            'media_buttons' => false,
            'textarea_name' => 'learning_outcomes',
            'editor_height' => 200,
        );
        wp_editor( $learning_outcomes, $editorID, $editor_args );
	}
	
	
	
	
	// Save metabox data on edit slide
	function savePostMeta ( $postID )
	{
	
		// Check if nonce is set.
		if ( ! isset( $_POST['session_metabox_nonce'] ) ) {
			return;
		}
		
		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $_POST['session_metabox_nonce'], 'save_session_metabox_nonce' ) ) {
			return;
		}
		
		// If this is an autosave, our form has not been submitted, so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
	
		// Check the user's permissions.
		if ( ! current_user_can( 'edit_post', $postID ) ) {
			return;
		}		
		
		
		
		// Session Date
		$sessionDate 	= isset( $_POST['sessionDate'] ) ?	$_POST['sessionDate'] : '';
		update_post_meta( $postID, 'sessionDate', $sessionDate );
		
		
		// Session Duration
		$sessionDuration 	= isset( $_POST['sessionDuration'] ) ?	$_POST['sessionDuration'] : '';
		update_post_meta( $postID, 'sessionDuration', $sessionDuration );

		
		/*
		$termUtils = new term_utils();
		
		// Validation
		if(!$termUtils->validateInputDate($sessionDate)){$sessionDate="";} // Validate the date
		*/
		
		// Learning Outcomes        // Key concepts
        $learning_outcomes = isset( $_POST['learning_outcomes'] ) ? $_POST['learning_outcomes'] : '';
        update_post_meta( $postID, 'learning_outcomes', $learning_outcomes );
        

		// Availability Settings
		$availableFromDate = $_POST['availableFromDate'];
		$availableToDate = $_POST['availableToDate'];
		
		
		if($availableFromDate)
		{
			// Get the time as well
			$fromHour = $_POST['fromHour'];
			$fromMin = $_POST['fromMin'];
			$fromAMPM = $_POST['fromAMPM'];	
			$fromTime= $fromHour.':'.$fromMin.' '.$fromAMPM;		
			$myFromDate = $availableFromDate.' '.$fromTime;
			$finalFromDate = DateTime::createFromFormat('d-m-Y h:i A', $myFromDate);
			$finalMysqlDate =  $finalFromDate->format('Y-m-d H:i:s');
			update_post_meta( $postID, 'availableFromDate', $finalMysqlDate );
			
		}
		else{
			update_post_meta( $postID, 'availableFromDate', "" );				
		}
		
		if($availableToDate)
		{

			
			// Get the time as well
			$toHour = $_POST['toHour'];
			$toMin = $_POST['toMin'];
			$toAMPM = $_POST['toAMPM'];	
			$toTime= $toHour.':'.$toMin.' '.$toAMPM;		
			$myToDate = $availableToDate.' '.$toTime;
			$finalToDate = DateTime::createFromFormat('d-m-Y h:i A', $myToDate);			
			$finalMysqlDate =  $finalToDate->format('Y-m-d H:i:s');
			update_post_meta( $postID, 'availableToDate', $finalMysqlDate );	
		
		}	
		else{
			update_post_meta( $postID, 'availableToDate', "" );	
		}	
		
		
		
		
		
		
		// Save the Parent ID 		
		$parentID = wp_get_post_parent_id( $postID );	
		if($parentID=="")
		{
			$parentID = $_POST['parentID'];
            
            remove_action( 'save_post', array($this, 'savePostMeta' ));
            wp_update_post(
				array(
					'ID' => $postID, 
					//'post_parent' => $_SESSION['parentTopicID']
                    'post_parent' => $parentID
				)
			);
            add_action( 'save_post', array($this, 'savePostMeta' ));
		}	
		
		
		/* See if the custom slides folder has been created */		
		/* Get the uploads root folder */
		$slidesFolder = getSlidesFolderDir();
		
		// make the DIR if it does not exist
		if(!file_exists($slidesFolder))
		{			
			mkdir($slidesFolder);			
		}

		
		// Check if delete slides is set to true.
		// Delete if so		
		if($_POST['deleteSlides']=="true")
		{

			// Its empty so check for existing file and delete iterator_apply
			$slidesRef = get_post_meta($postID, "session_slides_ref", true);
			$slidesPath = $slidesFolder.'/'.$slidesRef;

			if($slidesRef)
			{
				wp_delete_file($slidesPath);	
				
			}
			
			// Update the Post Meta to blank
			update_post_meta($postID, 'session_slides_ref', "");     
		}		
		
		
		
		/* Handle the File upload */
		// Make sure the file array isn't empty
		if(!empty($_FILES['session_slides_ref']['name']))
		{	
			// Get the tmep file name
			$tempName = $_FILES['session_slides_ref']['name'];
			
			// Get the post name for the slides filename
			$postName = html_entity_decode(get_the_title($postID));
			$fileName = preg_replace("/[^A-Za-z0-9 ]/", "", $postName);
			$fileName = preg_replace('#[ -]+#', '-', $fileName); // Remove spaces
			$fileName = $fileName.'_'.$postID; // Add Post ID as unique idenitifier in case of ducplicate  session names

			// Get the extension
			$fileExt = pathinfo($tempName, PATHINFO_EXTENSION);
			
			// Create new filename based on session name and extension
			$newFilename =  $fileName.'.'.$fileExt;
	
			// Move the file
			$destination = trailingslashit( $slidesFolder ) . $newFilename;			
			move_uploaded_file( $_FILES['session_slides_ref']['tmp_name'], $destination );
				
			// Update the post meta
			update_post_meta($postID, 'session_slides_ref', $newFilename);
			 
		}
		
		

		
	}	
	
	

	
	/* BACK BUTTONS */
	function addBackButton_on_editPage()
	{
		

		global $post_type, $pagenow, $post;
		
		if($post_type<>"topic_session")
		{
			return;
		}
		
		
		$topicID='';
		if(isset($_GET['topicID']))
		{
			$topicID = $_GET['topicID'];	
			$_SESSION['currentTopicID'] = $topicID;
			
			
		}
		elseif(isset($_SESSION['currentTopicID']))
		{
			$topicID = $_SESSION['currentTopicID'];			
		}
		
		if($topicID=="")
		{
			$postID = $post->ID;
			$topicID = wp_get_post_parent_id( $postID );	
		}
		
		if($topicID)
		{
			$topicName = html_entity_decode(get_the_title($topicID));
		}
		
		$naming = getTopicNaming();
		$topicSingular = $naming[0];
		$topicPlural = pluralize($topicSingular);	
		$sessionSingular = $naming[0];
		$sessionPlural = pluralize($sessionSingular);		
		
		
	
		if(($pagenow == "post.php" || $pagenow=="post-new.php") && $post_type=="topic_session")
		{
			echo '<h1>'.$topicName.'</h1>';
			$href = get_admin_url().'edit.php?post_type=topic_session&topicID='.$topicID;
			echo '<a href="'.$href.'"><i class="fas fa-chevron-circle-left"></i> Back to '.$topicName.'</a>';
			
		}
		elseif($post_type=="topic_session")
		{
			
			?>
			<script>
			jQuery(document).ready(function() {
			jQuery( ".wp-heading-inline" ).text( "<?php echo $topicSingular;?> : <?php echo $topicName; ?> " );
			jQuery( ".wp-header-end" ).after( "<div class='adminBreadcrumb'><a href='edit.php?post_type=imperial_topic'>All <?php echo $topicPlural;?></a> > <?php echo $topicName;?></div>" );

			});
			</script>
			<style>
			.subsubsub .mine, .subsubsub .byorder
			{
				display:none;
			}
			
			</style>
			
			<?php


			return;
		}
		
	}
	
	
	
	
	
	
	
	/* START OF ADMIN LIST MODIFIERS */
	
	
	// Remove Date Columns on projects
	function my_custom_post_columns( $columns )
	{
		$naming = getTopicNaming();		
		$singularTopic = $naming[0];
		$sessionSingular = $naming[1];
		$sessionPlural = pluralize($sessionSingular);	  
		$sessionPagesSingular = $naming[2];
		$sessionPagesPlural = pluralize($sessionPagesSingular);	  
		
		
		

		unset(
			$columns['date']
		);		
		$columns['session_content'] = $sessionPagesPlural;			
		$columns['addPage'] = '';
		$columns['session_date'] = $sessionSingular.' Date';
		$columns['session_topic'] = $singularTopic;
		 
	  return $columns;
	}	
	
	
	// Content of the custom columns for Topics Page
	function customColumnContent($column_name, $post_ID)
	{
		
		$naming = getTopicNaming();		
		$singularPage = $naming[2];
		$pluralPage = pluralize($singularPage);		
		
		switch ($column_name)
		{
			
			
			case "session_date":
				$sessionDate = get_post_meta( $post_ID, 'sessionDate', true );
				
				//$sessionNiceDate = DateTime::createFromFormat('Y-m-d', $sessionDate);
				//$sessionNiceDate =  $sessionNiceDate->format('jS F Y');
				
				echo $sessionDate;
				if($sessionDate=="")
				{
					echo '-';
				}
				
				
				
			break;	
			
			case "addPage":
				
				echo '<a href="edit.php?post_type=session_page&sessionID='.$post_ID.'" class="button-secondary">View '.$pluralPage.'</a> ';
				echo '<a href="post-new.php?post_type=session_page&sessionID='.$post_ID.'" class="button-primary">Add New '.$singularPage.'</a> ';				
				
			break;
			
			break;
			
			
			case "session_content":
				
				$sessionContent = getSessionPages ($post_ID);				
				$pageCount = count($sessionContent);
				
				echo $pageCount.' '.$singularPage.'(s) found<br/>';				
				
				// Convert to Page Options - network  admin only
				if(current_user_can('manage_network') )
				{
					$parentID = wp_get_post_parent_id( $post_ID );
					echo '<a href="?post_type=topic_session&topicID='.$parentID.'&myAction=convertToPage&convertID='.$post_ID.'">Convert to standard page</a>';
				}
	
			break;	
			
			case "session_topic":
				$parentID = wp_get_post_parent_id( $post_ID );
				
				if($parentID==0)
				{
					echo 'None';
				}else{
					
					$topicName = get_the_title($parentID);
					echo '<a href="edit.php?post_type=topic_session&topicID='.$parentID.'">';
					echo $topicName.'</a>';
				}

				
				
				
			break;
		}		
	}		
	
	// Default order by session date
	function term_session_default_order( $query ) {
	  if (is_admin()) {
		  
	
		// Nothing to do:  
		if( ! $query->is_main_query() || 'term_session' != $query->get( 'post_type' )  )
		{
			return;
		}
		
		
		
		//-------------------------------------------  
		// Modify the 'orderby' and 'meta_key' parts
		//-------------------------------------------  
		$orderby = $query->get( 'orderby');     
		$query->set( 'meta_key', 'sessionDate' );  
		$query->set( 'orderby',  'meta_value' );
		
		return $query;
		
	  }
	}
	
	
	// Modify the query for admin list to only show sessions in this topic
	function modify_admin_list_query( $query )
	{
		$filterPosts = true;
		// Check if 'All posts' are selected
		if(isset($_GET['all_posts']) )
		{
			if($_GET['all_posts']==1)
			{
				$filterPosts = false;	
			}
		}
		
		if(isset($_GET['post_status']) )
		{
			if($_GET['post_status']=="trash")
			{
				$filterPosts = false;	
			}
		}		
		
		// Check if on frontend and main query is modified
		if(  $filterPosts==true && is_admin() && $query->is_main_query() && $query->query_vars['post_type'] == 'topic_session' )
		{	
	
			
			// Check for actions
			
			if(isset($_GET['myAction']) )
			{
				
				
				
				if($_GET['myAction']=="convertToPage")
				{
					
				
				
					$parentID = $_GET['convertID'];

					$my_post = array(
					'ID'           => $parentID,
					'post_type'   => 'page',
					'post_parent'	=> 0,
					);

					// Update the post into the database
					wp_update_post( $my_post );		

					// Also update the fact to show the children
					update_post_meta( 
						$parentID, 
						'showPageChildren', 
						"on"
					);

		
					$args = array(
						'post_parent' => $parentID,
						'numberposts' => -1,
						'output'	=> 'ARRAY_A'
					);
					$children = get_children( $args );
					
					foreach ($children as $childID => $childMeta)
					{
						$my_post = array(
						'ID'           => $childID,
						'post_type'   => 'page',
						);

						// Update the post into the database
						wp_update_post( $my_post );
		
						
					}
				}

			}

			if(isset($_GET['topicID']) )
			{
				$_SESSION['currentTopicID']=$_GET['topicID'];
			}	
			
			$topicID = $_SESSION['currentTopicID'];
			$query->set('orderby', "menu_order");
			$query->set('order', 'asc'); // Needs to be set by menu order ASC for draggable ordering to work
			$query->set('post_parent', $topicID);
			return $query;
		}
		
	 
	}		
	

	
	
	
	
} //Close class


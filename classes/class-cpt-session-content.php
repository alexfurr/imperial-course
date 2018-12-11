<?php
$cpt_session_page = new cpt_session_page();
class cpt_session_page
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
		
		// Save additional project meta for the custom post
		add_action( 'save_post', array($this, 'savePostMeta' ));
		
		// Add the Metaboxes
		add_action( 'add_meta_boxes_session_page', array( $this, 'addMetaBoxes_sessionContent' ));		
		
		// modify edit and admin screens with additional info / back buttons
		add_action( 'all_admin_notices', array($this, 'addBackButton_on_editPage' ) );
		
		// Modify admin list main query to only show pages with correct sessionID parent	
		add_action( 'pre_get_posts', array($this, 'modify_admin_list_query' ) );
		
		//
		add_filter( 'manage_session_page_posts_columns', array( $this, 'my_custom_post_columns' ), 10, 2 );		
		//add_action('manage_content_pages_posts_custom_column', array($this, 'customColumnContent'), 10, 2);
	
	}
	
	
/*	---------------------------
	ADMIN-SIDE MENU / SCRIPTS 
	--------------------------- */
	function create_CPTs ()
	{
		
	
		$naming = getTopicNaming();		
		$singular = $naming[2];
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
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_nav_menus'	 => false,
			'show_in_menu'       => false,
			'query_var'          => true,
			'rewrite' => array( 'slug' => 'session-pages' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'show_in_rest'		 => true,		
			
			'supports'           => array( 'title', 'editor', 'revisions' )
			
		);
		
		register_post_type( 'session_page', $args );	
		
	}
	
	
	// Register the metaboxes on  CPT
	function  addMetaBoxes_sessionContent()
	{
			
			
						
		$naming = getTopicNaming();
		$pageSingular = $naming[2];
			
		//Project Settings Metabox
		$id 			= 'session_page_meta';
		$title 			= $pageSingular.' Parent';
		$drawCallback 	= array( $this, 'drawMetaBox_contentMeta' );
		$screen 		= 'session_page';
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
		
		
	}
	
	function drawMetaBox_contentMeta($post, $metabox)
	{
		// Add Nonce Field
		wp_nonce_field( 'save_session_page_metabox_nonce', 'session_page_metabox_nonce' );
	
		
		/* Spit out the Parent ID */
	
		if(isset($_GET['sessionID']))
		{
			$parentID = $_GET['sessionID'];
			$_SESSION['currentSessionID'] = $parentID; // This is picked up on submit page
		}
		else
		{
			$parentID = wp_get_post_parent_id( $post->ID );

		}	
		
		if(!$parentID){$parentID = $_SESSION['currentSessionID'];}	

		//echo 'parent ID = '.$parentID.'<br/>';
		
		$topicID = wp_get_post_parent_id( $parentID );
		
		
		// Get the list of lectures in this tpoic and add to drop down so we cna move them
		$mySessions = getTopicSessions ($topicID);
		
		echo '<select name="parentSessionID">';
		
		foreach ($mySessions as $sessionMeta)
		{
			$thisSessionID = $sessionMeta->ID;
			$thisSessionTitle = $sessionMeta->post_title;
			
			
			echo '<option value="'.$thisSessionID.'"';
			if($thisSessionID==$parentID)
			{
				echo ' selected ';
			}
			
			echo ' />'.$thisSessionTitle.'</option>';
		}
		
		echo '</select>';
		
		
		

	}
		
	
	
	
	// Save metabox data on edit slide
	function savePostMeta ( $postID )
	{
	
		
	
		// Check if nonce is set.
		if ( ! isset( $_POST['session_page_metabox_nonce'] ) ) {
			return;
		}
		
		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $_POST['session_page_metabox_nonce'], 'save_session_page_metabox_nonce' ) ) {
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
		
		
		
		
		$parentID = $_POST['parentSessionID'];
		remove_action( 'save_post', array($this, 'savePostMeta' ));
		wp_update_post(
			array(
				'ID' => $postID, 
				//'post_parent' => $_SESSION['parentSessionID']
				'post_parent' => $parentID
			)
		);
		add_action( 'save_post', array($this, 'savePostMeta' ));		
		
		
		
		/*
		$parentID = wp_get_post_parent_id( $postID );
		
		if($parentID=="")
		{
			//$parent_session_ID = isset( $_SESSION['parentSessionID'] ) ? $_SESSION['parentSessionID'] : 0;
            $parent_session_ID = isset( $_SESSION['currentSessionID'] ) ? $_SESSION['currentSessionID'] : 0;
            
            remove_action( 'save_post', array($this, 'savePostMeta' ));
            wp_update_post(
				array(
					'ID' => $postID, 
					//'post_parent' => $_SESSION['parentSessionID']
                    'post_parent' => $parent_session_ID
				)
			);
            add_action( 'save_post', array($this, 'savePostMeta' ));
		}
		*/
		
		
		
		
		/*
		//$session 	= isset( $_POST['sessionDate'] ) 	?  		$_POST['sessionDate']  		: '';		
		
		
		$termUtils = new term_utils();
		
		// Validation
		if(!$termUtils->validateInputDate($sessionDate)){$sessionDate="";} // Validate the date
		update_post_meta( $postID, 'sessionDate', $sessionDate );
		*/
		
	}	
	
	
	/* START OF ADMIN LIST MODIFIERS */
	
	
	// Remove Date Created Columns for  this post type
	function my_custom_post_columns( $columns )
	{
		unset(
			$columns['date']
		);			
		 
	  return $columns;
	}	
	
	

		
		
		
		
		
		/* BACK BUTTONS */
	function addBackButton_on_editPage()
	{
		global $post_type, $pagenow, $post;
		
		$sessionPagesAdmin = array("post.php", "post-new.php", "edit.php");
	
		if(in_array($pagenow, $sessionPagesAdmin) && $post_type=="session_page")
		{
			
			$naming = getTopicNaming();
			$topicSingular = $naming[0];
			$topicPlural = pluralize($topicSingular);	
			$sessionSingular = $naming[1];
			$sessionPlural = pluralize($sessionSingular);
			
			
			$sessionID = '';
			if(isset($post-> ID ) )
			{
				// Check if there is already a parent ID of this post
				$sessionID = wp_get_post_parent_id( $post ->ID );
			}
			
			if(!$sessionID)
			{

		
				if(isset($_GET['sessionID']))
				{
					$sessionID = $_GET['sessionID'];
					$_SESSION['currentSessionID']=$sessionID;
				}
				elseif(isset($_SESSION['currentSessionID']))
				{
					$sessionID = $_SESSION['currentSessionID'];
				}
				else
				{
					return; // If the session is not set do nothing
				}	
			}			
			
			$sessionName = html_entity_decode(get_the_title($sessionID));
			
			
			if(($pagenow == "post.php" || $pagenow=="post-new.php") && $post_type=="session_page")
			{
				echo '<h1>'.$sessionName.'</h1>';
				$href = get_admin_url().'edit.php?post_type=session_page&sessionID='.$sessionID;
				echo '<a href="'.$href.'"><i class="fas fa-chevron-circle-left"></i> Back to '.$sessionSingular.' pages</a>';
				
			}
			elseif($post_type=="session_page")
			{
				
				$topicID = wp_get_post_parent_id( $sessionID );
				$topicName = get_the_title($topicID);


				$breadcrumbStr = "<div class='adminBreadcrumb'><a href='edit.php?post_type=imperial_topic'>All ".$topicPlural."</a> >";
				$breadcrumbStr.= "<a href='edit.php?post_type=topic_session&topicID=".$topicID."'>".$sessionPlural."</a> >";
				$breadcrumbStr.=$sessionName.'</div>';	
				
				?>
				<script>
				jQuery(document).ready(function() {
				jQuery( ".wp-heading-inline" ).text( "Session : <?php echo $sessionName; ?> " );
				
				jQuery( ".wp-header-end" ).after( "<?php echo $breadcrumbStr; ?>" );

				});
				</script>

				
				<?php
				return;
			}
			return;
		}
		
	}	
	
	// Modify the query for admin list to only show pages in this session
	function modify_admin_list_query( $query )
	{
		// Check if on frontend and main query is modified
		if(  is_admin() && $query->is_main_query() && $query->query_vars['post_type'] == 'session_page' )
		{
			



			if(isset($_GET['sessionID']) )
			{
				$_SESSION['currentSessionID']=$_GET['sessionID'];
			}	

			$sessionID = $_SESSION['currentSessionID'];
			$query->set('post_parent', $sessionID);
			$query->set('orderby', "menu_order");
			$query->set('order', 'asc'); // Needs to be set by menu order ASC for draggable ordering to work

			return $query;
		}
	 
	}	
	
	
	
}
?>
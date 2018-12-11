<?php
$cpt_topics = new cpt_topics();
class cpt_topics
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
		add_action( 'add_meta_boxes_imperial_topic', array( $this, 'addMetaBoxes' ));
		
		// Save additional project meta for the custom post
		//add_action( 'save_post', array($this, 'savePostMeta' ));
		
		// Add Default order of DATE to the project list edit table
		//add_filter('pre_get_posts', array($this, 'term_session_default_order'), 9);		
		// Remove and add columns in the projects table
		add_filter( 'manage_imperial_topic_posts_columns', array( $this, 'my_custom_post_columns' ), 10, 2 );		
		add_action('manage_imperial_topic_posts_custom_column', array($this, 'customColumnContent'), 10, 2);


		// Add Theme Settings for Topics
		//add_action( 'admin_menu', array( $this, 'create_AdminPages' ));


	}
		
	
	/*	---------------------------
	ADMIN-SIDE MENU / SCRIPTS 
	--------------------------- */
	function create_CPTs ()
	{
		
		
		$naming = getTopicNaming();		
		$singular = $naming[0];
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
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite' => array( 'slug' => 'topics' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => true,
			'menu_position'      => 21,
			'supports'           => array( 'title', 'editor', 'revisions', 'thumbnail' )
			
		);
		
		
		
		register_post_type( 'imperial_topic', $args );
		//remove_post_type_support('term-session', 'editor');	
		
	}
	
	
	function addMetaBoxes()
	{
		
		global $post;
		$postID = $post->ID;
		$postStatus = get_post_status( $postID );

		if($postStatus=="publish")
		{		
			
			
			$naming = getTopicNaming();
			$topicSingular = $naming[0];
			$topicPlural = pluralize($topicSingular);
			$sessionSingular = $naming[1];
			$sessionPlural = pluralize($sessionSingular);			
			
			//Add new Session Metabox
			$id 			= 'topic_add_session_options';
			$title 			= $topicSingular.' '.$sessionPlural;
			$drawCallback 	= array( $this, 'drawMetaBox_topicSessions' );
			$screen 		= 'imperial_topic';
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
	}
	
	function drawMetaBox_topicSessions($post, $metabox)
	{
		$naming = getTopicNaming();
		$topicSingular = $naming[0];
		$topicPlural = pluralize($topicSingular);
		$sessionSingular = $naming[1];
		$sessionPlural = pluralize($sessionSingular);
		$sessionPagesSingular = $naming[2];
		$sessionPagesPlural = pluralize($sessionPagesSingular);		
		
		
		
		$topicID = $post->ID;
		

		
		
		echo '<a class="button-secondary" href="post-new.php?post_type=topic_session&topicID='.$topicID.'">';
		echo 'Add new '.$topicSingular.' '.$sessionSingular;
		echo '</a><hr/>';
		
		
		
		// Get array of children
		$topicSessions = getTopicSessions($topicID);
		
		echo '<b>Current '.$sessionPlural.'</b><br/>';

		echo '<ol>';
		foreach ($topicSessions as $topicInfo)
		{
			$sessionTitle =  $topicInfo->post_title;
			$sessionID =  $topicInfo->ID;
			
			// Get the subpages so we can count them
			$sessionPages = getSessionPages($sessionID);
			$pageCount = count($sessionPages);
			
			
			echo '<li><a href="post.php?post='.$sessionID.'&action=edit">'.$sessionTitle.'</a> ('.$pageCount.' '.$sessionPagesPlural.')</li>';
			
		}		
		echo '</ol>';
		
	}
	

	
	// Remove Date Columns on projects
	function my_custom_post_columns( $columns )
	{
	  
		$naming = getTopicNaming();		
		$sessionSingular = $naming[1];
		$sessionPlural = pluralize($sessionSingular);	  
	  

		unset(
			$columns['date']
		);	

		$columns['sessions'] = $sessionPlural;
		$columns['addSession'] = '';	

		
			
		 
	  return $columns;
	}	
	
	
	// Content of the custom columns for Topics Page
	function customColumnContent($column_name, $post_ID)
	{
		
		$naming = getTopicNaming();
		$sessionSingular = $naming[1];
		$sessionPlural = pluralize($sessionSingular);

		
		switch ($column_name)
		{
			
			case "sessions":
				/* Get the sessions in this topic */
				$topicSessions = getTopicSessions ($post_ID);				
				$sessionCount = count($topicSessions);			
				echo '<b>'.$sessionCount.'</b> '.$sessionSingular.'(s) found.<br/>';			
			break;
			
			case "addSession":
				// Get the CPT sessions that has this topic ID as it's parent
				
				echo '<a href="edit.php?post_type=topic_session&topicID='.$post_ID.'" class="button-secondary">View '.$sessionPlural.'</a> ';			
				echo '<a href="post-new.php?post_type=topic_session&topicID='.$post_ID.'" class="button-primary">Add '.$sessionSingular.'</a>';

				


				
			break;	
		}		
	}


	function create_AdminPages()
	{
		
		/* Create Settings Pages */		
		$parentSlug = "edit.php?post_type=imperial_topic";
		$page_title="Settings";
		$menu_title="Settings";
		$menu_slug="topic-settings";
		$function=  array( $this, 'drawTopicSettings' );
		$myCapability = "manage_options";
		add_submenu_page($parentSlug, $page_title, $menu_title, $myCapability, $menu_slug, $function);

		
		
	}
	
	function drawTopicSettings()
	{
		include_once get_stylesheet_directory() . '/admin/topic-settings.php';
	}
	

} //Close class
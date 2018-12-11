<?php
$cpt_course_contacts = new cpt_course_contacts();
class cpt_course_contacts
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
		add_action( 'add_meta_boxes_course_contact', array( $this, 'addMetaBoxes' ));
		
		// Save additional project meta for the custom post
		add_action( 'save_post', array($this, 'savePostMeta' ));
		
		// Add Default order of DATE to the project list edit table
		//add_filter('pre_get_posts', array($this, 'term_session_default_order'), 9);		
		// Remove and add columns in the projects table
		add_filter( 'manage_posts_columns', array( $this, 'my_custom_post_columns' ), 10, 2 );		
		add_action('manage_pages_custom_column', array($this, 'customColumnContent'), 10, 2);
		
	}
	
	
/*	---------------------------
	ADMIN-SIDE MENU / SCRIPTS 
	--------------------------- */
	function create_CPTs ()
	{
		
	
		$singularName = 'Academic Staff';
		$pluralName = 'Academic Staff';
	
		//Sessions
		$labels = array(
			'name'               =>  $pluralName,
			'singular_name'      =>  $singularName,
			'menu_name'          =>  $pluralName,
			'name_admin_bar'     =>  $pluralName,
			'add_new'            =>  'Add New '.$singularName,
			'add_new_item'       =>  'Add New '.$singularName,
			'new_item'           =>  'New '.$singularName,
			'edit_item'          =>  'Edit '.$singularName,
			'view_item'          => 'View '.$pluralName,
			'all_items'          => 'All '.$pluralName,
			'search_items'       => 'Search '.$pluralName,
			'parent_item_colon'  => '',
			'not_found'          => 'No '.$pluralName.' found.',
			'not_found_in_trash' => 'No '.$pluralName.' found in Trash.'
		);
	
		$args = array(
			'menu_icon' => 'dashicons-businessman',		
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_nav_menus'	 => false,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite' => array( 'slug' => 'contacts' ),
			'capability_type'    => 'page',
			'has_archive'        => true,
			'hierarchical'       => true,
			'menu_position'      => 22,
			'supports'           => array( 'title', 'thumbnail',   )
			
		);
		
		register_post_type( 'course_contact', $args );
		//remove_post_type_support('term-session', 'editor');
	}
	
	
	
	// Register the metaboxes on  CPT
	function  addMetaBoxes()
	{
			
		//Project Settings Metabox
		$id 			= 'course_contacts_meta';
		$title 			= 'Person Information';
		$drawCallback 	= array( $this, 'drawMetaBox_options' );
		$screen 		= 'course_contact';
		$context 		= 'normal';
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
	
	function drawMetaBox_options($post, $metabox)
	{
		// Add Nonce Field
		wp_nonce_field( 'save_metabox_nonce', 'metabox_nonce' );
		
		$course_leader = get_post_meta( $post->ID, 'course_leader', true );
		$email = get_post_meta( $post->ID, 'email', true );
		
		echo '<label for="course_leader"><input type="checkbox" name="course_leader" id="course_leader"';
		
		if($course_leader=="on"){echo ' checked ';}
		echo '>This person is a course leader</label><hr/>';
		
		echo '<label for="email">Email Address<br/><input type="text" name="email" id="email" value="'.$email.'">';
		
	
	}
	
	
	// Save metabox data on edit slide
	function savePostMeta ( $postID )
	{
	
		// Check if nonce is set.
		if ( ! isset( $_POST['metabox_nonce'] ) ) {
			return;
		}
		
		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $_POST['metabox_nonce'], 'save_metabox_nonce' ) ) {
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
		
		$course_leader = '';
		if(isset($_POST['course_leader']))
		{
			$course_leader = $_POST['course_leader'];
		}
		
		update_post_meta( $postID, 'course_leader', $course_leader );
		
		update_post_meta( $postID, 'email', $_POST['email'] );
		
	}	
	
	
	// Remove Date Columns on projects
	function my_custom_post_columns( $columns, $post_type )
	{
	  
	  switch ( $post_type )
	  {    
		
			case 'course_contact':
			
			unset(
				$columns['date']
			);			
			
			$columns['course-leader'] = 'Course Leader';
			$columns['email'] = 'Email';
			break;
		}
		 
	  return $columns;
	}	
	
	
	// Content of the custom columns for Topics Page
	function customColumnContent($column_name, $post_ID)
	{
		
		switch ($column_name)
		{
			
			case "course-leader":
				
				$course_leader = get_post_meta( $post_ID, 'course_leader', true );
				
				if($course_leader=="on")
				{
					echo '<b>Course leader</b>';
				}
				
			
			break;		
			case "email":
				
				$email = get_post_meta( $post_ID, 'email', true );
				
				if($email)
				{
					echo '<a href="mailto:'.$email.'">'.$email.'</a>';
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
} //Close class
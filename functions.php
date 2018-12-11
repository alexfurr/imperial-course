<?php
// Create the table for placement allocations
include_once get_stylesheet_directory() . '/classes/class-draw.php';
include_once get_stylesheet_directory() . '/classes/class-utils.php';
include_once get_stylesheet_directory() . '/classes/class-cpt-contacts.php';
include_once get_stylesheet_directory() . '/classes/class-cpt-topics.php';
include_once get_stylesheet_directory() . '/classes/class-cpt-sessions.php';
include_once get_stylesheet_directory() . '/classes/class-cpt-session-content.php';
include_once get_stylesheet_directory() . '/classes/class-queries.php';
include_once get_stylesheet_directory() . '/classes/class-dashboard.php';





$imperial_iBooks = new imperial_iBooks();
class imperial_iBooks
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
			


		
		//Frontend
		add_action( 'wp_enqueue_scripts', array( $this, 'frontendEnqueues' ), 1 );
		
		add_action( 'admin_enqueue_scripts', array( $this, 'adminSettingsEnqueues' ) );
		
		
		
		// Add shortcode to display placements
		add_shortcode( 'show-topics', array( 'imperialCourse_draw', 'drawTopics' ) );
		add_shortcode( 'show-sessions', array( 'imperialCourse_draw', 'drawSessionsShortcode' ) );


		add_shortcode( 'imperial-topics', array( 'imperialCourse_draw', 'drawTopics' ) ); //Same as above. Retire 'Show-topics'
		
		add_shortcode( 'course-contacts', array( 'imperialCourse_draw', 'drawContacts' ) );
		
		add_shortcode( 'ibooks-list', array( 'imperialCourse_draw', 'drawIbooksList' ) );
		
		add_shortcode( 'topics-tree', array( 'imperialCourse_draw', 'drawTopicsTree' ) );
		
		add_shortcode( 'site-map', array( 'imperialCourse_draw', 'drawLearningContentTree' ) );
		
		
		//add_shortcode( 'my-placements', array( 'imperialPlacementsDraw', 'drawMyPlacements' ) );
		
		// Setup Dashboard Widgets
		add_action('wp_dashboard_setup', array($this, 'my_custom_dashboard_widgets') );		

		
		
		
		
		// Enable Session Storage
		add_action('init', array($this, 'myStartSession'), 1);
		add_action('wp_logout', array( $this, 'myEndSession') );
		add_action('wp_login', array($this, 'myEndSession') ) ;
		
		//Admin Menu
		//add_action( 'init',  array( $this, 'create_CPTs' ) );		
		//add_action( 'admin_menu', array( $this, 'create_AdminPages' ));
		
		
		//add_action( 'add_meta_boxes_imperial_placement', array( $this, 'addPlacementMetaBox' ));
		
		
		// Chave POSTS to ANNOUCEMENTS
		add_action( 'admin_menu', array($this, 'change_posts_to_news' ) );
		add_action( 'init', array($this, 'change_posts_object' ) );
		
		
		
		
		
		
		
	      
		// Let Sessions and session content be ordered by simple page ordering plugin
		add_filter( 
            'simple_page_ordering_is_sortable', 
            function( $sortable, $post_type ) {
                if ( 'session_page' == $post_type || 'topic_session' == $post_type ) {
	
                    $sortable = true;
			
                }
                return $sortable;
            }, 
            10, 
            2 
        );	
		 

		
		
		
		add_action('post_edit_form_tag', array($this, 'update_edit_form') );


		
		
		
	
	
	}
	
	function adminSettingsEnqueues ()
	{
		//WP includes
		
		global $wp_scripts;	
		$parent_style = 'imperial-theme'; 
	
		
		// get the jquery ui object
		$queryui = $wp_scripts->query('jquery-ui-core');
		// load the jquery ui theme
		$url = "https://ajax.googleapis.com/ajax/libs/jqueryui/".$queryui->ver."/themes/smoothness/jquery-ui.css";	
		wp_enqueue_style('jquery-ui-smoothness', $url, false, null);	


		// Add the admin css		
		wp_enqueue_style( 'admin-child-styles', get_stylesheet_directory_uri() . '/css/admin.css');
		
		// Custom Admin JS
		wp_enqueue_script('imperial_course_admin_js', get_stylesheet_directory_uri().'/js/admin.js', array( 'jquery' ) );
		
		wp_enqueue_style( 'progress-bars', get_stylesheet_directory_uri() . '/css/progress_bar.css' );
		wp_enqueue_style( 'radial-progress', get_stylesheet_directory_uri() . '/css/radial_progress.css' );
		
		
	}	
	
	
	
	
	function frontendEnqueues ()
	{
		//Scripts
		wp_enqueue_script('jquery');
		
		
		$parent_style = 'imperial-theme'; 
		
		wp_enqueue_style( $parent_style, get_template_directory_uri() . '/style.css'  );
		wp_enqueue_style( 'child-style',
			get_stylesheet_directory_uri() . '/style.css',
			array( $parent_style ),
			wp_get_theme()->get('Version')
		);
		
		
		wp_enqueue_style( 'progress-bars', get_stylesheet_directory_uri() . '/css/progress_bar.css' );
		wp_enqueue_style( 'radial-progress', get_stylesheet_directory_uri() . '/css/radial_progress.css' );
		wp_enqueue_style( 'content-side-nav', get_stylesheet_directory_uri() . '/css/content-side-nav.css' );		
		
		
		

		
	}	
	
	
	function drawProgressTracker()
	{

		include_once get_stylesheet_directory() . '/admin/progress-tracker.php';
	}	
	
	
	function myStartSession() {
		if(!session_id()) {
			session_start();
		}
	}
	function myEndSession() {
		session_start();

		session_destroy ();
	}		
	
	/* Update the Post Form so it accepts file uploads */			
	function update_edit_form() {
		echo ' enctype="multipart/form-data"';
		
	} // end update_edit_form
	
	
	
	
	function my_custom_dashboard_widgets() {
		global $wp_meta_boxes;

		// Learning Content Overview
		wp_add_dashboard_widget('learning_content_dash', 'Learning Content', array('ek_course_dashboard', 'topics_tree') );

		// Force this wdiget to display on the right hand side
		$my_widget = $wp_meta_boxes['dashboard']['normal']['core']['learning_content_dash'];
		unset($wp_meta_boxes['dashboard']['normal']['core']['learning_content_dash']);
		$wp_meta_boxes['dashboard']['side']['core']['learning_content_dash'] = $my_widget;		
	
	
		// Helpful tools
		wp_add_dashboard_widget('helpful_tips', 'What do you want to do?', array('ek_course_dashboard', 'tips_widget') );

	
	}



	
	// These two functions change all POSTS references to NEWS
	static function change_posts_to_news() {
		global $menu;
		global $submenu;
		$menu[5][0] = 'Announcements';
		$submenu['edit.php'][5][0] = 'Announcements';
		$submenu['edit.php'][10][0] = 'Add Announcement';
	}
	
	static function change_posts_object() {
		global $wp_post_types;
		$labels = &$wp_post_types['post']->labels;
		$labels->name = 'Announcements';
		$labels->singular_name = 'Announcement';
		$labels->add_new = 'Add Announcement';
		$labels->add_new_item = 'Add Announcement';
		$labels->edit_item = 'Edit Announcements';
		$labels->new_item = 'Announcements';
		$labels->view_item = 'View Announcements';
		$labels->search_items = 'Search Announcements';
		$labels->not_found = 'No Announcements found';
		$labels->not_found_in_trash = 'No Announcements found in Trash';
		$labels->all_items = 'All Announcements';
		$labels->menu_name = 'Announcements';
		$labels->name_admin_bar = 'Announcements';
	}	
	
	
	
			
	
	
}







?>
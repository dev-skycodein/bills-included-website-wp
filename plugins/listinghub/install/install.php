<?php $blog_title = get_bloginfo(); 
	global $wpdb;
	// Create Basic Role
	global $wp_roles;												
	$role_name_new= 'basic';
	$wp_roles->remove_role( $role_name_new );						 
	$role_display_name = 'Basic';						
	$wp_roles->add_role($role_name_new, $role_display_name, array(
    'read' => true, // True allows that capability, False specifically removes it.
    'upload_files' => true //last in array needs no comma!
	));
	require_once ('install-signup-email.php');
	require_once ('install-order-email.php');
	require_once ('install-reminder-email.php'); 
	update_option('listinghub_payment_gateway', 'paypal-express' ); 
	update_option('listinghub_payment_terms', 'yes' ); 
	update_option('listinghub_price-table', 'style-1' ); 
	update_option('listinghub_api_currency', 'USD' );
	update_option('listinghub_payment_terms_text', ' I have read & accept the  Terms & Conditions' ); 
	update_option('epjblistinghub_hide_admin_bar', 'yes' ); 
	/// **** Create Page for Pricing Table******
	$page_title='Pricing Table';
	$page_name='price-table';
	$page_content='[listinghub_price_table]';
	$my_post_form = array(		
	'post_title'    => wp_strip_all_tags( $page_title),
	'post_name'    => wp_strip_all_tags( $page_name),
	'post_content'  => $page_content,
	'post_status'   => 'publish',
	'post_author'   =>  get_current_user_id(),	
	'post_type'			=> 'page',
	);
	$newpost_id= wp_insert_post( $my_post_form,'true' );	
	update_option('epjblistinghub_price_table', $newpost_id); 		
	// **** Create Account Form For Registration Page******
	$page_title='Registration';
	$page_name='registration';
	$page_content='[listinghub_form_wizard]';
	$post_iv = array(
	'post_title'    => wp_strip_all_tags( $page_title),
	'post_name'    => wp_strip_all_tags( $page_name),
	'post_content'  => $page_content,
	'post_status'   => 'publish',
	'post_author'   =>  get_current_user_id(),	
	'post_type'		=> 'page',
	);
	$newpost_id= wp_insert_post( $post_iv );
	update_option('epjblistinghub_registration', $newpost_id); 	
	/// **** Create Page for User Profile******
	$page_title='My Account';
	$page_name='my-account';
	$page_content='[listinghub_profile_template]';
	$my_post_form = array(
	'post_title'    => wp_strip_all_tags( $page_title),
	'post_name'    => wp_strip_all_tags( $page_name),
	'post_content'  => $page_content,
	'post_status'   => 'publish',
	'post_author'   =>  get_current_user_id(),	
	'post_type'		=> 'page',
	);
	$newpost_id= wp_insert_post( $my_post_form );	
	update_option('epjblistinghub_profile_page', $newpost_id); 	
	/// **** Create Page for User public Profile****** 
	$page_title='Author Profile';
	$page_name='author-public';
	$page_content='[listinghub_profile_public]';
	$my_post_form = array(
	'post_title'    => wp_strip_all_tags( $page_title),
	'post_name'    => wp_strip_all_tags( $page_name),
	'post_content'  => $page_content,
	'post_status'   => 'publish',
	'post_author'   =>  get_current_user_id(),	
	'post_type'		=> 'page',
	);
	$newpost_id= wp_insert_post( $my_post_form );	
	update_option('epjblistinghubr_public_profile_page', $newpost_id);
	
	// Login Page *******************
	$page_title='Login';
	$page_name='login';
	$page_content='[listinghub_login]';
	$my_post_form = array(
	'post_title'    => wp_strip_all_tags( $page_title),
	'post_name'    => wp_strip_all_tags( $page_name),
	'post_content'  => $page_content,
	'post_status'   => 'publish',
	'post_author'   =>  get_current_user_id(),	
	'post_type'		=> 'page',
	);
	$newpost_id= wp_insert_post( $my_post_form );	
	$reg_login_page= get_permalink( $newpost_id);
	update_option('epjblistinghub_login_page', $newpost_id);

	/// **** Create Page for  Employer Directory ******	
	$page_title='Author Directory';
	$page_name='author-directory';
	$page_content='[listinghub_author_directory]';
	$my_post_form = array(
	'post_title'    => wp_strip_all_tags( $page_title),
	'post_name'    => wp_strip_all_tags( $page_name),
	'post_content'  => $page_content,
	'post_status'   => 'publish',
	'post_author'   =>  get_current_user_id(),	
	'post_type'		=> 'page',
	);
	$newpost_id= wp_insert_post( $my_post_form );	
	update_option('epjblistinghub_author_dir_page', $newpost_id);
	
	
	/// **** Create Page for All Listing with map ******	
	$page_title='All Listings';
	$page_name='all-listings';
	$page_content='[listinghub_archive_grid]';
	$my_post_form = array(
	'post_title'    => wp_strip_all_tags( $page_title),
	'post_name'    => wp_strip_all_tags( $page_name),
	'post_content'  => $page_content,
	'post_status'   => 'publish',
	'post_author'   =>  get_current_user_id(),	
	'post_type'		=> 'page',
	);
	$newpost_id= wp_insert_post( $my_post_form );	
	/// **** Create Page for All Listing No map ******	
	$page_title='All Listings Without Map';
	$page_name='all-listings-no-map';
	$page_content='[listinghub_archive_grid_no_map]';
	$my_post_form = array(
	'post_title'    => wp_strip_all_tags( $page_title),
	'post_name'    => wp_strip_all_tags( $page_name),
	'post_content'  => $page_content,
	'post_status'   => 'publish',
	'post_author'   =>  get_current_user_id(),	
	'post_type'		=> 'page',
	);
	$newpost_id= wp_insert_post( $my_post_form );	
	
	/// **** Create Page for All Locations ******	
	$page_title='All Locations';
	$page_name='all-locations';
	$page_content='[listinghub_locations]';
	$my_post_form = array(
	'post_title'    => wp_strip_all_tags( $page_title),
	'post_name'    => wp_strip_all_tags( $page_name),
	'post_content'  => $page_content,
	'post_status'   => 'publish',
	'post_author'   =>  get_current_user_id(),	
	'post_type'		=> 'page',
	);
	$newpost_id= wp_insert_post( $my_post_form );	
	/// **** Create Page for All Categories ******	
	$page_title='All Categories';
	$page_name='all-categories';
	$page_content='[listinghub_categories]';
	$my_post_form = array(
	'post_title'    => wp_strip_all_tags( $page_title),
	'post_name'    => wp_strip_all_tags( $page_name),
	'post_content'  => $page_content,
	'post_status'   => 'publish',
	'post_author'   =>  get_current_user_id(),	
	'post_type'		=> 'page',
	);
	$newpost_id= wp_insert_post( $my_post_form );	
	
	/// **** Create Page for Search Form ******	
	$page_title='Search Form';
	$page_name='search-form';
	$page_content='[listinghub_search]';
	$my_post_form = array(
	'post_title'    => wp_strip_all_tags( $page_title),
	'post_name'    => wp_strip_all_tags( $page_name),
	'post_content'  => $page_content,
	'post_status'   => 'publish',
	'post_author'   =>  get_current_user_id(),	
	'post_type'		=> 'page',
	);
	$newpost_id= wp_insert_post( $my_post_form );	
	/// **** Create Page for Add Listing ******	
	$page_title='Add Listing';
	$page_name='add-listing';
	$page_content='[listinghub_add_listing]';
	$my_post_form = array(
	'post_title'    => wp_strip_all_tags( $page_title),
	'post_name'    => wp_strip_all_tags( $page_name),
	'post_content'  => $page_content,
	'post_status'   => 'publish',
	'post_author'   =>  get_current_user_id(),	
	'post_type'		=> 'page',
	);
	$newpost_id= wp_insert_post( $my_post_form );	
	
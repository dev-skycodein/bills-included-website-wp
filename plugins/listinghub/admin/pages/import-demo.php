<?php
if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( 'Are you cheating:user Permission?' );
}
global $current_user; global $wpdb;	
$listinghub_directory_url=get_option('ep_listinghub_url');					
if($listinghub_directory_url==""){$listinghub_directory_url='listing';}
	$post_names = array('Therapy','Foxglove','Lake Merritt Dental','Plant Technician', 'Grey Mat Technical Services','Tadu Ethiopian Kitchen');
	$post_cat = array('Hotels','Commercial','Restaurant','Support Service','Real Estate');	
	$post_tag = array('Free WiFi','Indoor Pool','Laundry','Private Garden','Swing Pool','Weddings','Good for Groups','Has TV','Parking','SPA','Takes Reservations','Waiter Service','Wheelchair Accessible');
	$post_city = array('New York ','Dubai','Bretagne','New South Wales','London','Paris','Berlin');	
	$post_aear = array('Central Brooklyn','Chelsea','Midtown','Shoreditch' , 'Upper Manhattan','Berlin');
	$post_location = array('New York','London','Tokyo','Los Angeles' , 'Houston','Berlin');
	
	 $storeSchedule = [
        'Mon' => ['08:00 AM' => '05:00 PM'],
        'Tue' => ['08:00 AM' => '05:00 PM'],
        'Wed' => ['08:00 AM' => '05:00 PM'],
        'Thu' => ['08:00 AM' => '05:00 PM'],
        'Fri' => ['08:00 AM' => '05:00 PM']
    ];
	
$i=0;	
	foreach($post_names as $one_post){ 
	$my_post = array();
	$my_post['post_title'] = $one_post;
	$my_post['post_content'] = 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.
	
	Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.
	
	';	
	$my_post['post_status'] = 'publish';	
	$my_post['post_type'] = $listinghub_directory_url;	
	$newpost_id= wp_insert_post( $my_post );		
	
	$rand_keys = array_rand($post_cat, 2);	
	$new_post_arr=array();
	$new_post_arr[]=$post_cat[$rand_keys[0]];
	$new_post_arr[]=$post_cat[$rand_keys[1]];
	wp_set_object_terms( $newpost_id, $new_post_arr, $listinghub_directory_url.'-category');	
	
	// For Tag Save tag_arr	
	$rand_keys = array_rand($post_tag, 6);	
	$new_post_arr=array();
	$new_post_arr[]=$post_tag[$rand_keys[0]];
	$new_post_arr[]=$post_tag[$rand_keys[1]];
	$new_post_arr[]=$post_tag[$rand_keys[2]];
	$new_post_arr[]=$post_tag[$rand_keys[3]];
	$new_post_arr[]=$post_tag[$rand_keys[4]];
	$new_post_arr[]=$post_tag[$rand_keys[5]];
	
	wp_set_object_terms( $newpost_id, $new_post_arr, $listinghub_directory_url.'-tag');
	
	
	wp_set_object_terms( $newpost_id, $post_location[$i], $listinghub_directory_url.'-locations');
	
	update_post_meta($newpost_id, 'address', '129-133 West 22nd Street'); 
	$rand_keys = array_rand($post_aear, 1);	
	update_post_meta($newpost_id, 'local-area', $post_aear[$rand_keys]); 
	update_post_meta($newpost_id, 'latitude', '40.7427704'); 
	update_post_meta($newpost_id, 'longitude','-73.99455039999998');
	$rand_keys = array_rand($post_city, 1);		
	update_post_meta($newpost_id, 'city', $post_city[$rand_keys]); 
	update_post_meta($newpost_id, 'postcode', '10011'); 
	update_post_meta($newpost_id, 'country', 'USA'); 
	update_post_meta($newpost_id, 'phone', '212245-4606'); 
	update_post_meta($newpost_id, 'fax', '212245-4606'); 
		
	update_post_meta($newpost_id, 'company_name', 'Apple Inc'); 
	update_post_meta($newpost_id, 'contact-email', 'test@test.com'); 
	update_post_meta($newpost_id, 'contact_web', 'www.e-plugins.com'); 
	update_post_meta($newpost_id, 'listing_contact_source', 'new_value'); 	
	update_post_meta($newpost_id, 'youtube', 'FzcfZyEhOoI');  
		
	$date = date('Y-m-d', strtotime('+'.$i.' days'));
	
	
	// FAQ;
	update_post_meta($newpost_id, 'faq_title0', 'How often does availability change?');
	update_post_meta($newpost_id, 'faq_description0', 'Rates and availability changes minute-by-minute. We recommend that you reserve now for immediate confirmation. In most cases, you will be able to change or cancel your reservation without penalty, though some rates do require a deposit. Be sure to read the Rate Terms carefully.'); 
	
	update_post_meta($newpost_id, 'faq_title1', 'Are reservations confirmed immediately?');
	update_post_meta($newpost_id, 'faq_description1', 'Yes. We only offer rates that are confirmed as available. Reservations are confirmed immediately with the hotel.'); 
	
	update_post_meta($newpost_id, 'faq_title2', 'Will I receive a confirmation email?');
	update_post_meta($newpost_id, 'faq_description2', 'Yes. Immediately after making your reservation, you will be sent an email which will include your hotel confirmation number. If you do not receive your confirmation email, please check your Bulk/Spam folder.'); 
	
	update_post_meta($newpost_id, 'faq_title3', 'What is the hotel child policy?');
	update_post_meta($newpost_id, 'faq_description3', 'Child policies vary by hotel. Many hotels are able to provide a rollaway bed or crib, though there may be an extra fee. Such fees will not be included in the quoted rate. Our advice is to first make your reservation. Our agents will then be able to more effectively assist you with details regarding your children.'); 
	
	update_post_meta($newpost_id, 'faq_title4', 'How many people may I have in my room?');
	update_post_meta($newpost_id, 'faq_description4', 'Reservations made on our site are for the industry-standard double occupancy. Additional room occupants may require an addition charge, depending on the hotel.'); 
	
	update_post_meta($newpost_id, '_opening_time', $storeSchedule);
	
	

 $i++; 
}

// /// **** Create Home Page ******	
	$page_title='Home';
	$page_name='home';
	$page_content='[depicter id="9"]';
	$my_post_form = array(
	'post_title'    => wp_strip_all_tags( $page_title),
	'post_name'    => wp_strip_all_tags( $page_name),
	'post_content'  => $page_content,
	'post_status'   => 'publish',
	'post_author'   =>  get_current_user_id(),	
	'post_type'		=> 'page',
	);
	$newpost_id= wp_insert_post( $my_post_form );	

?>
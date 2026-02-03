<?php
	$listinghub_directory_url=get_option('ep_listinghub_url');
	if($listinghub_directory_url==""){$listinghub_directory_url='listing';}
	$args = array();
	$args['number']='999999999';
	$args['orderby']='display_name';
	$args['order']='ASC'; 
	
	$email_body_main = get_option( 'listinghub_notification_email');
	$contact_email_subject =  get_option( 'listinghub_notification_email_subject');
	$admin_mail = get_option('admin_email');
	$wp_title = get_bloginfo();
	$dir_id=$newpost_id;
	$dir_detail= get_post($dir_id); 
	$listing_name= $dir_detail->post_title; 
	$currentCategory=wp_get_object_terms( $dir_id, $listinghub_directory_url.'-category');
	$deadline='';
	if(get_post_meta($dir_id,'deadline', true)!=''){
		$deadline =date('M d, Y', strtotime(get_post_meta($dir_id,'deadline', true)));
	}
	$listing_link= '<a href="'.get_the_permalink($dir_id).'">'.$dir_detail->post_title.'</a>';	
	$user_query = new WP_User_Query( $args );
	// User Loop
	if ( ! empty( $user_query->results ) ) {
		foreach ( $user_query->results as $user ) {
			$listing_notifications_all='';
			$listing_notifications_all= get_user_meta($user->ID ,'listing_notifications',true);
			$will_send_email='no';		
			if(!empty($listing_notifications_all)){
				foreach($currentCategory as $c){			
					if(is_array($listing_notifications_all)){
						if(in_array($c->slug,$listing_notifications_all)){
							$will_send_email='yes';
						}
					}				
				}
			}
			if($will_send_email=='yes'){  
				$email_body	=$email_body_main;		
				$full_name =get_user_meta($user->ID,'full_name',true);
				$cilent_email_address =$user->user_email;
				$email_body = str_replace("[user_name]", $full_name, $email_body);
				$email_body = str_replace("[iv_member_listing_name]",$listing_name, $email_body);
				$email_body = str_replace("[iv_member_listing_deadline]", $deadline, $email_body);
				$email_body = str_replace("[iv_member_listing_url]",$listing_link, $email_body); 			
				$headers = array("From: " . $wp_title . " <" . $admin_mail . ">", "Reply-To: ".$admin_mail  ,"Content-Type: text/html");
				$h = implode("\r\n", $headers) . "\r\n";
				wp_mail($cilent_email_address, $contact_email_subject, $email_body, $h);
			}
		}
	}		
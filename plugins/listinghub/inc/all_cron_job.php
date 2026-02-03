<?php
	$listinghub_directory_url=get_option('ep_listinghub_url');					
	if($listinghub_directory_url==""){$listinghub_directory_url='listing';}
	global $wpdb, $post;
	$main_class = new eplugins_listinghub;
	//Strat  Subscription remainder email ********************************
	$sql="SELECT * FROM $wpdb->users ";
	$membership_users = $wpdb->get_results($sql);
	$total_package=count($membership_users);
	if(sizeof($membership_users)>0){
		$i=0;
		foreach ( $membership_users as $row )
		{	
			$user_id= $row->ID;
			$reminder_day = get_option( 'listinghub_reminder_day');
			$exp_date= get_user_meta($user_id, 'listinghub_exprie_date', true);
			$date2 = date("Y-m-d");
			$date1 = $exp_date;
			$diff = abs(strtotime($date2) - strtotime($date1));
			$days = floor($diff / (60*60*24));
			if( $reminder_day >= $days ){
				$exprie_send_email_date= get_user_meta($user_id, 'exprie_send_email_date', true);
				if(strtotime($exprie_send_email_date) != strtotime($exp_date) || $exprie_send_email_date=='' ){
					// Start Email Action
					$email_body = get_option( 'listinghub_reminder_email');
					$signup_email_subject = get_option( 'listinghub_reminder_email_subject');			
					$admin_mail = get_option('admin_email');	
					if( get_option( 'listinghub_admin_email' )==FALSE ) {
						$admin_mail = get_option('admin_email');						 
						}else{
						$admin_mail = get_option('listinghub_admin_email');								
					}						
					$wp_title = get_bloginfo();
					$user_info = get_userdata( $user_id);											
					$email_body = str_replace("[expire_date]", $exp_date, $email_body);	
					$cilent_email_address =$user_info->user_email;			
					$auto_subject=  $signup_email_subject; 
					$headers = array("From: " . $wp_title . " <" . $admin_mail . ">", "Content-Type: text/html");
					$h = implode("\r\n", $headers) . "\r\n";
					wp_mail($cilent_email_address, $auto_subject, $email_body, $h);
					// End Email Action
					update_user_meta($user_id, 'exprie_send_email_date', $exp_date);
				}	
			}	
		}
	}	
	//End Subscription remainder email *************************
	// Start Hide Directory******************
	$sql=$wpdb->prepare("SELECT * FROM $wpdb->posts WHERE post_type ='%s'  and post_status='publish' ", $listinghub_directory_url);
	$all_post = $wpdb->get_results($sql);
	$total_post=count($all_post);									
	if($total_post>0){
		$i=0;
		foreach ( $all_post as $row )								
		{			
			$dir_id=$row->ID;
			$post_author_id=$row->post_author;	
			$main_class->check_listing_expire_date($dir_id, $post_author_id,$listinghub_directory_url);					
		}
	}										
// End  Hide Directory******************
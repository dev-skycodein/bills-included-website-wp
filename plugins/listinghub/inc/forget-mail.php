<?php
	global $wpdb;			
	$email_body = get_option( 'listinghub_forget_email');
	$forget_email_subject = get_option( 'listinghub_forget_email_subject');			
	$admin_mail = get_option('admin_email');	
	if( get_option( 'listinghub_admin_email' )==FALSE ) {
		$admin_mail = get_option('admin_email');						 
		}else{
		$admin_mail = get_option('listinghub_admin_email');								
	}						
	$wp_title = get_bloginfo();
	
	
	

	
	
	parse_str($_POST['form_data'], $data_a);
	$user_info = get_user_by( 'email',$data_a['forget_email'] );
	if(isset($user_info->ID) ){
		$url = home_url();
		$user = new WP_User( (int) $user_info->ID );

		$adt_rp_key = get_password_reset_key( $user );
		$user_login = $user->user_login;
		$rp_link = '<a href="' . network_site_url("wp-login.php?action=rp&key=$adt_rp_key&login=" . rawurlencode($user_login), 'login') . '">' . network_site_url("wp-login.php?action=rp&key=$adt_rp_key&login=" . rawurlencode($user_login), 'login') . '</a>';
	
	       
		$email_body = str_replace("[user_name]", $user_info->display_name, $email_body);
		$email_body = str_replace("[iv_member_user_name]", $user_info->user_login, $email_body);	
		$email_body = str_replace("[iv_member_password]", $rp_link, $email_body); 
		$cilent_email_address =$user_info->user_email; 
		$auto_subject=  $forget_email_subject; 
		$headers = array("From: " . $wp_title . " <" . $admin_mail . ">", "Content-Type: text/html");
		$h = implode("\r\n", $headers) . "\r\n";
		wp_mail($cilent_email_address, $auto_subject, $email_body, $h);
		
		
	}	
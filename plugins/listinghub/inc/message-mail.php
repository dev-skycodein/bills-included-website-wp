<?php
	global $wpdb;	
	$email_body = get_option( 'listinghub_contact_email');
	$contact_email_subject = get_option( 'listinghub_contact_email_subject');			
	$admin_mail = get_option('admin_email');	
	if( get_option( 'listinghub_admin_email' )==FALSE ) {
		$admin_mail = get_option('admin_email');						 
		}else{
		$admin_mail = get_option('listinghub_admin_email');								
	}						
	$bcc_message='';
	if( get_option('epjblistinghub_bcc_message' ) ) {
		$bcc_message= get_option('epjblistinghub_bcc_message'); 
	}	
	$wp_title = get_bloginfo();
	parse_str($_POST['form_data'], $form_data);
	$dir_id = isset( $form_data['dir_id'] ) ? $form_data['dir_id'] : 0;
	$client_email_address = '';
	$dir_title = '';
	
	if ( isset( $form_data['dir_id'] ) ) {
		if ( $form_data['dir_id'] > 0 ) {
			$dir_detail = get_post( $dir_id );
			$dir_title  = '<a href="' . get_permalink( $dir_id ) . '">' . $dir_detail->post_title . '</a>';

			// Prefer agency owner email when listing belongs to an agency.
			$user_id        = (int) $dir_detail->post_author;
			$agency_post_id = (int) get_post_meta( $dir_id, 'agency_post_id', true );
			if ( $agency_post_id > 0 ) {
				$agency_owner = (int) get_post_meta( $agency_post_id, 'agency_owner', true );
				if ( $agency_owner > 0 ) {
					$user_id = $agency_owner;
				}
			}

			// If listing uses "New Contact Info", send to listing contact email.
			$listing_contact_source = (string) get_post_meta( $dir_id, 'listing_contact_source', true );
			if ( $listing_contact_source === 'new_value' ) {
				$contact_email = sanitize_email( (string) get_post_meta( $dir_id, 'contact-email', true ) );
				if ( $contact_email !== '' && is_email( $contact_email ) ) {
					$client_email_address = $contact_email;
				}
			}

			// Default to resolved user email if still empty.
			if ( $client_email_address === '' ) {
				$user_info = get_userdata( $user_id );
				if ( $user_info && ! empty( $user_info->user_email ) ) {
					$client_email_address = (string) $user_info->user_email;
				}
			}
		}
	}
	if(isset($form_data['user_id'])){
		if($form_data['user_id']>0){
		$dir_title= '<a href="'.site_url().'">'.get_bloginfo().'</a>';
		$user_info = get_userdata( $form_data['user_id']);
		$client_email_address =$user_info->user_email;
	}
	}
	// Email for Client	
			
	$name=(isset($form_data['name'])?$form_data['name']:'');
	$raw_sender_email = isset( $form_data['email_address'] ) ? $form_data['email_address'] : '';
	$visitor_email_address=$name.' | '.$raw_sender_email;
	$sender_phone='';
	if(isset($form_data['visitorphone'])){
		$sender_phone=$form_data['visitorphone'];
	}
	$move_when = isset( $form_data['enquiry_move_when'] ) ? (string) $form_data['enquiry_move_when'] : '';
	$budget    = isset( $form_data['enquiry_budget'] ) ? (string) $form_data['enquiry_budget'] : '';
	$bedrooms  = isset( $form_data['enquiry_bedrooms'] ) ? (string) $form_data['enquiry_bedrooms'] : '';
	$message_for_email = '';
	if ( ! empty( $form_data['message-content'] ) ) {
		$message_for_email = $form_data['message-content'];
	} elseif ( function_exists( 'listinghub_format_contact_enquiry_message' ) ) {
		$message_for_email = listinghub_format_contact_enquiry_message( $form_data );
	}
	// Preserve newlines in message for HTML templates.
	$message_for_email_html = nl2br( esc_html( (string) $message_for_email ) );

	$email_body = str_replace("[iv_member_sender_email]", $visitor_email_address, $email_body);
	$email_body = str_replace("[iv_member_sender_phone]", $sender_phone, $email_body);
	$email_body = str_replace("[iv_member_directory]", $dir_title, $email_body);
	$email_body = str_replace("[iv_member_message]", $message_for_email_html, $email_body);	
	// Additional placeholders for per-row email templates.
	$email_body = str_replace("[iv_member_name]", esc_html( (string) $name ), $email_body);
	$email_body = str_replace("[iv_member_email]", esc_html( (string) $raw_sender_email ), $email_body);
	$email_body = str_replace("[iv_member_phone]", esc_html( (string) $sender_phone ), $email_body);
	$email_body = str_replace("[iv_member_move_when]", esc_html( $move_when ), $email_body);
	$email_body = str_replace("[iv_member_budget]", esc_html( $budget ), $email_body);
	$email_body = str_replace("[iv_member_bedrooms]", esc_html( $bedrooms ), $email_body);
	
	

	$auto_subject = $contact_email_subject !== '' ? $contact_email_subject : sprintf( __( 'New message from %s', 'listinghub' ), $wp_title );
	$reply_email  = isset( $form_data['email_address'] ) ? sanitize_email( $form_data['email_address'] ) : '';

	// Fallback: if recipient is missing/invalid, send to admin to avoid silent drops.
	$client_email_address = sanitize_email( $client_email_address );
	if ( $client_email_address === '' || ! is_email( $client_email_address ) ) {
		$client_email_address = sanitize_email( $admin_mail );
	}

	$headers = array(
		"From: " . $wp_title . " <" . sanitize_email( $admin_mail ) . ">",
		"Content-Type: text/html; charset=UTF-8",
	);
	if ( $reply_email !== '' && is_email( $reply_email ) ) {
		$headers[] = "Reply-To: " . $reply_email;
	}

	$sent_to_recipient = wp_mail( $client_email_address, $auto_subject, $email_body, $headers );
	if($bcc_message=='yes'){
		$sent_to_admin = wp_mail( sanitize_email( $admin_mail ), $auto_subject, $email_body, $headers );
	}		

	// Expose send status to the AJAX handler.
	$GLOBALS['listinghub_last_mail_sent'] = array(
		'to_recipient' => (bool) $sent_to_recipient,
		'to_admin'     => isset( $sent_to_admin ) ? (bool) $sent_to_admin : null,
		'recipient'    => $client_email_address,
	);
<?php
	$listinghub_directory_url=get_option('ep_listinghub_url');					
	if($listinghub_directory_url==""){$listinghub_directory_url='listing';}
	global $post;

	
	add_action( 'manage_listinghub_message_posts_custom_column' , 'listinghub_custom_listinghub_message_column' );
	add_filter( 'manage_edit-listinghub_message_columns',  'listinghub_set_custom_edit_listinghub_message_columns'  );
	function listinghub_set_custom_edit_listinghub_message_columns($columns) {				
		$columns['Message'] = esc_html__('Message','listinghub');
		$columns['email'] = esc_html__('Email','listinghub');
		$columns['phone'] = esc_html__('Phone','listinghub');		
		return $columns;
	}
	function listinghub_custom_listinghub_message_column( $column ) {
		global $post;
		switch ( $column ) {
			case 'Message' :		
				echo esc_html($post->post_content);
			break; 
			case 'phone' :			
				echo get_post_meta($post->ID,'from_phone',true);  
			break;
			case 'email' :
				echo get_post_meta($post->ID,'from_email',true);  
			break;
			
			
		}
	}	
	
?>
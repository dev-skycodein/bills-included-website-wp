<?php
	global $current_user;
	$listinghub_directory_url=get_option('ep_listinghub_url');
	if($listinghub_directory_url==""){$listinghub_directory_url='listing';}
	$main_class = new eplugins_listinghub;
	$csv =  get_attached_file($csv_file_id) ;
	$eppro_total_row = floatval( get_option( 'eppro_total_row' ));	
	$default_fields = array();
	$field_set=get_option('listinghub_li_fields' );
	if($field_set!=""){ 
		$default_fields=get_option('listinghub_li_fields' );
		}else{															
		$default_fields['other_link']=esc_html__('Other Link','listinghub');
	}
	$done_status='not-done';
	$row=$row_start;
	$row_max=$row+2;
	$iii=1;
	if (($handle = fopen($csv, 'r' )) !== FALSE) {					
		$top_header = fgetcsv($handle,1000, ",");
		while (($data = fgetcsv($handle)) !== FALSE) {
			if($iii>=$row  AND $row<$row_max){	
				$i=0;
				$post_id=0; $post_data=array();
				foreach($data as $one_col){
					if(in_array("ID", $top_header) OR in_array("Id", $top_header) OR in_array("id", $top_header)){
						// Check ID 
						if(strtolower($top_header[$i])=='id'){		
							if(trim($one_col)!=''){
								$post_check=get_post($one_col);													
								if ( isset($post_check->post_type) and $post_check->post_type==$listinghub_directory_url ) {								
									$post_id=$one_col;	
									}else{
									$post_id=0;								
								}							
							}
							}else{						
							$top_header_i=str_replace (' ','-', $top_header[$i]);						
							$post_data[$form_data[$top_header_i]]=$one_col;
						}					
						}else{					
						$top_header_i=str_replace (' ','-', $top_header[$i]);					
						$post_data[$form_data[$top_header_i]]=$one_col;					
						$post_id=0;
					}
					$i++;
				}
				if($post_id==0){
					// Insert Post
					$my_post=array();
					$my_post['post_title'] = sanitize_text_field($post_data['post_title']);
					$my_post['post_content'] =sanitize_text_field($post_data['post_content']);
					$my_post['post_author'] =$current_user->ID;
					$my_post['post_date'] =date("Y-m-d H:i:s");
					$my_post['post_status'] = 'publish';	
					$my_post['post_type'] = $listinghub_directory_url;		
					$post_id= wp_insert_post( $my_post );				
					}else{
					$my_post=array();				
					$my_post['ID'] = $post_id;
					$my_post['post_title'] = sanitize_text_field($post_data['post_title']);
					$my_post['post_content'] =sanitize_text_field($post_data['post_content']);
					$my_post['post_status'] = 'publish';	
					$my_post['post_type'] = $listinghub_directory_url;		
					wp_update_post($my_post);
				}
				if(isset($post_data['category'])) {  
					$post_cat_arr =explode(",",$post_data['category']) ;
					wp_set_object_terms( $post_id, $post_cat_arr, $listinghub_directory_url.'-category');
				}				
				if(isset($post_data['tag'])) { 
					$post_tag_arr =explode(",",$post_data['tag']) ;
					wp_set_object_terms( $post_id, $post_tag_arr, $listinghub_directory_url.'-tag');
				}
				if(isset($post_data['locations'])) {  
					$post_location_arr =explode(",",$post_data['locations']) ;
					wp_set_object_terms( $post_id, $post_location_arr, $listinghub_directory_url.'-locations');
				}
				if(isset($post_data['featured-image'])){
					if(strlen(trim($post_data['featured-image']))>3){
						$main_class->eppro_upload_featured_image($post_data['featured-image'] ,$post_id);
					}	
				}			
				if(isset($post_data['image_gallery_urls'])) {				
					update_post_meta($post_id, 'image_gallery_urls', $post_data['image_gallery_urls']); 	
				}
				
				if(isset($post_data['business_hours'])){
					$hours_arr=array();
					$opening_day=array();
					$hours_arr =explode('|', $post_data['business_hours']);
					if(is_array($hours_arr)){
						foreach($hours_arr as $one_day_full_row){
							if(!empty( $one_day_full_row)){
							$day_arr =array();
							$day_arr =explode(',', $one_day_full_row);							
							$opening_day[sanitize_text_field($day_arr[0])]= array(sanitize_text_field($day_arr[1])=>sanitize_text_field($day_arr[2])) ;
							}
						}
					}				
					
					update_post_meta($post_id, '_opening_time', $opening_day); 	
				}
				
				if(isset($post_data['listing_contact_source'])){
					update_post_meta($post_id, 'listing_contact_source', sanitize_text_field($post_data['listing_contact_source'])); 				
				}
				if(isset($post_data['company_name'])){
					update_post_meta($post_id, 'company_name', sanitize_text_field($post_data['company_name'])); 				
				}
				if(isset($post_data['address'])){
					update_post_meta($post_id, 'address', sanitize_text_field($post_data['address'])); 				
				}				
				if(isset($post_data['local-area'])){
					update_post_meta($post_id, 'local-area', sanitize_text_field($post_data['local-area'])); 				
				}
				if(isset($post_data['latitude'])){
					update_post_meta($post_id, 'latitude', sanitize_text_field($post_data['latitude'])); 				
				}
				if(isset($post_data['longitude'])){
					update_post_meta($post_id, 'longitude', sanitize_text_field($post_data['longitude'])); 				
				}
				if(isset($post_data['city'])){
					update_post_meta($post_id, 'city', sanitize_text_field($post_data['city'])); 				
				}
				if(isset($post_data['postcode'])){
					update_post_meta($post_id, 'postcode', sanitize_text_field($post_data['postcode'])); 				
				}
				if(isset($post_data['state'])){
					update_post_meta($post_id, 'state', sanitize_text_field($post_data['state'])); 				
				}
				if(isset($post_data['country'])){
					update_post_meta($post_id, 'country', sanitize_text_field($post_data['country'])); 				
				}
				if(isset($post_data['phone'])){
					update_post_meta($post_id, 'phone', sanitize_text_field($post_data['phone'])); 				
				}
				if(isset($post_data['contact-email'])){
					update_post_meta($post_id, 'contact-email', sanitize_text_field($post_data['contact-email'])); 				
				}
				if(isset($post_data['contact_web'])){
					update_post_meta($post_id, 'contact_web', sanitize_text_field($post_data['contact_web'])); 				
				}
				if(isset($post_data['youtube'])){
					update_post_meta($post_id, 'youtube', sanitize_text_field($post_data['youtube'])); 				
				}
				if(sizeof($default_fields )){			
					foreach( $default_fields as $field_key => $field_value ) { 
						update_post_meta($post_id, $field_key, $post_data[$field_key] );							
					}					
				}
				$row++;
				update_option( 'eppro_current_row',$row);	
			}		
			$iii++;	
		}
	}
	$row_done=$row_max;
	if($row_max >=$eppro_total_row){$done_status='done';}
	fclose($handle);
?>
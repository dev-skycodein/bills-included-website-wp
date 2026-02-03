




<?php
	
if (!function_exists('listinghub_get_icon')) {	
	function listinghub_get_icon($active_single_icon_saved, $field_key, $onpage ){
		$saved_icon=''; 		
		if(isset($active_single_icon_saved[$field_key]) and $active_single_icon_saved[$field_key]!=""){ 			
			if($field_key!='category'){				
				if(trim($active_single_icon_saved [$field_key])!=''){ 						
					$saved_icon=$active_single_icon_saved [$field_key].' mr-2 icon-color';
				}else{
					
				}
			}	
		}else{
			if($onpage=='single'){ 
				$archive_page_icon_saved=get_option('listinghub_archive_icon_saved');
				if(isset($archive_page_icon_saved[$field_key]) and $archive_page_icon_saved[$field_key]!=""){ 
					$saved_icon=$archive_page_icon_saved [$field_key].' mr-2 icon-color';
				}				
			}
			if($onpage=='archive'){ 
				$archive_page_icon_saved=get_option('listinghub_single_icon_saved');
				if(isset($archive_page_icon_saved[$field_key]) and $archive_page_icon_saved[$field_key]!=""){ 
						$saved_icon=$archive_page_icon_saved [$field_key].' mr-2 icon-color';
				}
			}
		}	
		return $saved_icon;	
	}
}	
if (!function_exists('listinghub_get_cat_icon')) {		
	function listinghub_get_cat_icon($term_id){
		$saved_icon='';
		$caticon= get_term_meta($term_id, 'listinghub_term_icon', true);
		$saved_icon=$caticon.' mr-1 icon-color';
		return $saved_icon;	
	}
}	
if (!function_exists('listinghub_check_field_display_access')) {	
	function listinghub_check_field_display_access($saved_fields_arr, $field_key){
		var_dump($saved_fields_arr);
		return '';
	}
}	
if (!function_exists('listinghub_get_archive_field')) {	
	function listinghub_get_archive_field($active_fields_arr, $field_icon_saved){
		var_dump($active_fields_arr);
		return '';
	}

}
if (!function_exists('listinghub_get_listing_fields_all_single')) {		
	function listinghub_get_listing_fields_all_single(){
				$available_fields_main=array();	
				
				$available_fields_main['top-image']='Top Baner Image';	
				$available_fields_main['image-gallery']='Image Gallery';
				$available_fields_main['open_status']='Business Hours';
				$available_fields_main['open_status_table']='Business Hours Table';
				$available_fields_main['company-logo']='Company Logo';	
				$available_fields_main['title']='Title';
				$available_fields_main['description']='Description';	
				$available_fields_main['category']='Category';
				$available_fields_main['tag']='Tag';
				$available_fields_main['review']='Review';
				$available_fields_main['location']='Location';	
				$available_fields_main['post_date']='Post Date';	
				$available_fields_main['contact_button']='Contact Button';
				$available_fields_main['claim_button']='Claim Button';
				$available_fields_main['pdf_button']='PDF Button';
				$available_fields_main['favorite']='Favorite Button';
				$available_fields_main['video']='Video';
				$available_fields_main['faq']='FAQ';
				$available_fields_main['simillar_listing']='Simillar listings';
				$available_fields_main['map']='Map';
				$available_fields_main['address']='Address';
				$available_fields_main['author_info']='Author Info';
				$available_fields_main['social-share']='Social Share';
				
				
			$new_field_set=	get_option('listinghub_li_fields' );
			if(empty($new_field_set)){				
				$new_field_set['business_type']='Business Type';
				$new_field_set['main_products']='Main Products';
				$new_field_set['number_of_employees']='Number Of Employees';
				$new_field_set['main_markets']='Main Markets';
				$new_field_set['total_annual_sales_volume']='Total Annual Sales Volume';	
			}
			
			if(is_array($new_field_set)){
				foreach($new_field_set  as $field_key => $field_value){
					$available_fields_main[$field_key]=$field_value;
				}
			}
		return $available_fields_main;
	}
}
if (!function_exists('listinghub_get_listing_fields_all')) {	
	function listinghub_get_listing_fields_all(){
	
				$available_fields_main=array();
				$available_fields_main['top_search_form']='Top Filter';
				$available_fields_main['sort_option']='Sort By';
				$available_fields_main['image']='Image';		
				$available_fields_main['title']='Title';
				$available_fields_main['category']='Category';
				$available_fields_main['tag']='Tag';
				$available_fields_main['review']='Review';
				$available_fields_main['open_status']='Business Hours';
				$available_fields_main['location']='Location';	
				$available_fields_main['post_date']='Post Date';	
				$available_fields_main['contact_email']='Contact Email';	
				$available_fields_main['phone']='Phone';
				$available_fields_main['address']='Address';	
				$available_fields_main['post_date']='Post Date';	
				$available_fields_main['web_link']='Web Link Button';	
				$available_fields_main['contact_button']='Contact Button';				
				$available_fields_main['favorite']='Favorite Button';
	
			$new_field_set=	get_option('listinghub_li_fields' );
			if(empty($new_field_set)){				
				$new_field_set['business_type']='Business Type';
				$new_field_set['main_products']='Main Products';
				$new_field_set['number_of_employees']='Number Of Employees';
				$new_field_set['main_markets']='Main Markets';
				$new_field_set['total_annual_sales_volume']='Total Annual Sales Volume';	
			}
								
			if(is_array($new_field_set)){
				foreach($new_field_set  as $field_key => $field_value){
					$available_fields_main[$field_key]=$field_value;
				}
			}
			
		
		return $available_fields_main;
	}
}
if (!function_exists('listinghub_get_archive_fields_all')) {
	function listinghub_get_archive_fields_all(){
		$active_archive_fields_saved=get_option('listinghub_archive_fields_saved' );	
		if($active_archive_fields_saved==''){
			$active_archive_fields=array();	
			$active_archive_fields['top_search_form']='Top Filter';
			$active_archive_fields['sort_option']='Sort By';
			$active_archive_fields['image']='Image';			
			$active_archive_fields['title']='Title';
			$active_archive_fields['category']='Category';
			$available_fields_main['open_status']='Business Hours';
			$active_archive_fields['location']='Location';						
			$active_archive_fields['review']='Review';
			$active_archive_fields['web_link']='Web link Button';	
			$active_archive_fields['favorite']='Favorite Button';
			$active_archive_fields['contact_button']='Contact Button';
			}else{
			$active_archive_fields=array();
			$active_archive_fields=$active_archive_fields_saved;
		}
		return $active_archive_fields;
	}
}
if (!function_exists('listinghub_text_translate_array_all')) {
	function listinghub_text_translate_array_all(){
		$listinghub_directory_url=get_option('ep_listinghub_url');
		if($listinghub_directory_url==""){$listinghub_directory_url='listing';}
		
		$data_for_translate=array();	
		$data_for_translate['category']=esc_html__( 'Category', 'listinghub' );				
		$data_for_translate['locations']=esc_html__( 'Location', 'listinghub' );	
		$data_for_translate['social-share']=esc_html__( 'Social Share', 'listinghub' );		
		$data_for_translate[$listinghub_directory_url.'-category']=esc_html__( 'Categories', 'listinghub' );
		$data_for_translate[$listinghub_directory_url.'-tag']=esc_html__( 'Tags', 'listinghub' );
		$data_for_translate[$listinghub_directory_url.'-locations']=esc_html__( 'Locations', 'listinghub' );
		$data_for_translate['title']=esc_html__( 'Title', 'listinghub' );				
		$data_for_translate['city']=esc_html__( 'City', 'listinghub' );	
		$data_for_translate['postcode']=esc_html__( 'Post code', 'listinghub' );	
		$data_for_translate['state']=esc_html__( 'State', 'listinghub' );	
		$data_for_translate['country']=esc_html__( 'Country', 'listinghub' );	
		$data_for_translate['review']=esc_html__( 'Review', 'listinghub' );	
		$data_for_translate['post_date']=esc_html__( 'Post Date', 'listinghub' );
		
		$new_field_set=	get_option('listinghub_li_fields' );	
		if(is_array($new_field_set)){
			foreach($new_field_set  as $field_key_custom => $field_value_custom){
				$data_for_translate[$field_key_custom]=$field_value_custom;				
				
			}
		}	
		
		return $data_for_translate;
	}
}
if (!function_exists('listinghub_text_translate')) {
	function listinghub_text_translate($key_text){		
		$data_for_translate=listinghub_text_translate_array_all();		
		$display_title= (isset($data_for_translate[$key_text])? $data_for_translate[$key_text]:$key_text);	
		
		return $display_title;
	}
}
if (!function_exists('listinghub_get_search_fields_default')) {
	function listinghub_get_search_fields_default(){
		$listinghub_directory_url=get_option('ep_listinghub_url');
		if($listinghub_directory_url==""){$listinghub_directory_url='listing';}
		$active_search_fields=array();
		$active_search_fields[$listinghub_directory_url.'-category']='multi-checkbox';		
		$active_search_fields[$listinghub_directory_url.'-locations']='multi-checkbox';		
		$active_search_fields['review']='multi-checkbox';
		
		return $active_search_fields;
	}
}	
if (!function_exists('listinghub_get_color_changer_js')) {
	function listinghub_get_color_changer_js(){
		$big_button_color=get_option('epjbdir_big_button_color');	
		if($big_button_color==""){$big_button_color='#2e7ff5';}	
		$small_button_color=get_option('epjbdir_small_button_color');	
		if($small_button_color==""){$small_button_color='#5f9df7';}
		$icon_color=get_option('epjbdir_icon_color');	
		if($icon_color==""){$icon_color='#5b5b5b';}	
		$title_color=get_option('epjbdir_title_color');	
		if($title_color==""){$title_color='#5b5b5b';}
		$button_font_color=get_option('epjbdir_button_font_color');	
		if($button_font_color==""){$button_font_color='#fffff';}
		$button_small_font_color=get_option('epjbdir_button_small_font_color');	
		if($button_small_font_color==""){$button_small_font_color='#fffff';}	
		$content_font_color=get_option('epjbdir_content_font_color');	
		if($content_font_color==""){$content_font_color='#66789C';}	
		$border_color=get_option('epjbdir_border_color');	
		if($border_color==""){$border_color='#E0E6F7';}	
		wp_enqueue_script('listinghub-dynamic-color', ep_listinghub_URLPATH . 'admin/files/js/dynamic-color.js');
		wp_localize_script('listinghub-dynamic-color', 'listinghub_color', array(
		'ajaxurl' 			=> admin_url( 'admin-ajax.php' ),
		'big_button'=>$big_button_color,
		'small_button'=>$small_button_color,
		'button_font'=>$button_font_color,
		'button_small_font'=>$button_small_font_color,
		'title_color'=>$title_color,
		'content_font_color'=>$content_font_color,
		'icon_color'=>$icon_color,
		'max_border_color'=>$border_color,	
		) );	
	}
}
if (!function_exists('listinghub_get_search_args')) {
	function listinghub_get_search_args($listinghub_directory_url){
		$search_arg= array();
		global $listinghub_filter_badge;
		$listinghub_filter_badge=0;
		$other_field_mq= array();
		
		$field_prefix='sf';
		$active_archive_icon_saved=get_option('listinghub_archive_icon_saved' );
		
		if(!isset($active_archive_icon_saved['sort_option'])){
			$dir_listing_sort='date-desc';
		}else{
			$dir_listing_sort=$active_archive_icon_saved['sort_option'];
		}
		if($_REQUEST[$field_prefix.'sort_listing']){
		    $dir_listing_sort = $_REQUEST[$field_prefix.'sort_listing'];
		}
					
		if($dir_listing_sort=='asc'){
			$search_arg['orderby']='title';
			$search_arg['order']='ASC';
			
		}
		if($dir_listing_sort=='desc'){ 
			$search_arg['orderby']='title';
			$search_arg['order']='DESC';
			
		}
		// Date
		if($dir_listing_sort=='date-desc'){
			$search_arg['orderby']='date';
			$search_arg['order']='DESC';
			
		}
		if($dir_listing_sort=='date-asc'){
			$search_arg['orderby']='date';
			$search_arg['order']='ASC';
			
		}
		if($dir_listing_sort=='rand'){
			$search_arg['orderby']='rand';
			$search_arg['order']='ASC';
			
		}
		if ($dir_listing_sort == 'high-to-low') {
            $search_arg['meta_key'] = 'search_price'; // Define the meta field
            $search_arg['orderby']  = 'meta_value_num'; // Sort numerically
            $search_arg['order']    = 'DESC'; // High to Low
        }
        
        if ($dir_listing_sort == 'low-to-high') {
            $search_arg['meta_key'] = 'search_price'; // Define the meta field
            $search_arg['orderby']  = 'meta_value_num'; // Sort numerically
            $search_arg['order']    = 'ASC'; // Low to High
        }


		
		// Search Fields****************
		$active_search_fields_saved=get_option('listinghub_search_fields_saved' );	
		if($active_search_fields_saved==''){		
			$active_search_fields =listinghub_get_search_fields_default();				
			}else{
			$active_search_fields=array();
			$active_search_fields=$active_search_fields_saved;
		}	
		//atts atts
		if(isset($atts['field-name']) ){	
			$field_name= $atts['field-name'];
			$field_type= $atts['field-type'];
			$field_name_arr= explode(",",$field_name);
			$field_type_arr= explode(",",$field_type);
			$i=0;
			$active_search_fields=array();
			foreach($field_name_arr as $one_field){		
				if(isset($field_type_arr[$i])){
					$active_search_fields[$one_field]=$field_type_arr[$i];
				}
				$i++;
			}		
		}
		$category_query=''; $tag_query=''; $location_query='';
		if(is_array($active_search_fields)){
			foreach($active_search_fields  as $field_key => $field_value){	 				
				if(isset($_REQUEST[$field_prefix.$field_key]) AND $_REQUEST[$field_prefix.$field_key]!='' AND $field_key!='sort_listing'){
					$listinghub_filter_badge=$listinghub_filter_badge+1;
					if($field_key=='title'){
						$search_title= $_REQUEST[$field_prefix.$field_key];
						if(is_array($search_title)){
							$title_arr=array();
							foreach($search_title as $one_title){
								$title_arr[]= sanitize_text_field($one_title);
							}	
							$search_arg['post__in']= $title_arr;
							
							}else{
							$search_arg['s']=   sanitize_text_field($_REQUEST[$field_prefix.$field_key]);
							
						}
						}elseif($field_key==$listinghub_directory_url.'-category'){	
							if(isset($_REQUEST[$field_prefix.$listinghub_directory_url.'-category']) AND $_REQUEST[$field_prefix.$listinghub_directory_url.'-category']!=''){
							$categories= $_REQUEST[$field_prefix.$listinghub_directory_url.'-category'];
							$categories_arr=array();							
							if(is_array($categories)){
								foreach($categories as $one_category){
									$categories_arr[]= sanitize_text_field($one_category);
								}
							}else{
									$categories_arr[]= sanitize_text_field($categories);
							}	
							$category_query = 
							array(
							'taxonomy'  => $listinghub_directory_url.'-category',
							'field'		=> 	'slug',
							'terms'   	=> $categories_arr,
							'compare' 	=> 'IN'
							);
							
						}	
						}elseif($field_key==$listinghub_directory_url.'-tag'){		
						if(isset($_REQUEST[$field_prefix.$listinghub_directory_url.'-tag'])  AND $_REQUEST[$field_prefix.$listinghub_directory_url.'-tag']!=''){
							$tags= $_REQUEST[$field_prefix.$listinghub_directory_url.'-tag'];
							$tags_arr=array();							
							if(is_array($tags)){
								foreach($tags as $one_tag){
								$tags_arr[]= sanitize_text_field($one_tag);
								}
							}else{
									$tags_arr[]= sanitize_text_field($tags);
							}	
							
							
							
							$tag_query = 
							array(
							'taxonomy'  => $listinghub_directory_url.'-tag',
							'field'		=> 	'slug',
							'terms'   	=> $tags_arr,
							'compare' 	=> 'IN'
							);
							
						}	
						}elseif(trim($field_key)==$listinghub_directory_url.'-locations'){
						if(isset($_REQUEST[$field_prefix.$listinghub_directory_url.'-locations'])  AND $_REQUEST[$field_prefix.$listinghub_directory_url.'-locations']!=''){
							$locations= $_REQUEST[$field_prefix.$listinghub_directory_url.'-locations'];
							$locations_arr=array();
							if(is_array($locations)){
								foreach($locations as $one_location){
									$locations_arr[]= sanitize_text_field($one_location);
								}
							}else{
									$locations_arr[]= sanitize_text_field($locations);
							}	
							
							
							$location_query = 
							array(
							'taxonomy'  => $listinghub_directory_url.'-locations',
							'field'		=> 	'slug',
							'terms'   	=> $locations_arr,
							'compare' 	=> 'IN'
							);
							
						}		
						}else{
						if(isset($_REQUEST[$field_prefix.$field_key])  AND $_REQUEST[$field_prefix.$field_key]!=''){ 
							
							$other_field= $_REQUEST[$field_prefix.$field_key];
							$other_field_arr=array();
							if(is_array($other_field)){								
									foreach($other_field as $one_field){
										$other_field_arr[]= sanitize_text_field($one_field);
									}
							}else{
								$other_field_arr[]= $_REQUEST[$field_prefix.$field_key];
							}	
							
							$field_mq = 
								array(
								'key'     => $field_key,
								'value'   => $other_field_arr,
								'compare' => 'IN'							
								);
							array_push( $other_field_mq, $field_mq );
							
						}	
					}					
				}
			}
		}	
		$search_arg['tax_query'] = array(
		'relation' => 'AND',
			$category_query, $tag_query, $location_query,
			
		);
		if (!empty($_GET['input-search'])) {
			$keyword = sanitize_text_field($_GET['input-search']);
		
			$meta_query = [
				'relation' => 'OR',
				[
					'key'     => 'postcode',
					'value'   => $keyword,
					'compare' => 'LIKE',
				],
				[
					'key'     => 'city',
					'value'   => $keyword,
					'compare' => 'LIKE',
				],
				[
					'key'     => 'address',
					'value'   => $keyword,
					'compare' => 'LIKE',
				],
			];
		
			$search_arg['meta_query'] = $meta_query;
		}
		else{
			$search_arg['meta_query'] = array(
				'relation' => 'AND',
				$other_field_mq,
			);
		}
		if(isset($_REQUEST['latitude']) AND $_REQUEST['latitude']!=''){
			$search_arg['lat']=$_REQUEST['latitude'];
			$listinghub_filter_badge=$listinghub_filter_badge+1;
			
		}
		if(isset($_REQUEST['longitude']) AND $_REQUEST['longitude']!=''){
			$search_arg['lng']=$_REQUEST['longitude'];
		}
		if(isset($_REQUEST['near_km']) AND $_REQUEST['near_km']!=''){
			$search_arg['distance']=$_REQUEST['near_km'];
			
		}
		
		return $search_arg;
	}
}
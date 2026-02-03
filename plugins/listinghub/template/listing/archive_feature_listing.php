<?php
if ( $listinghub_query->have_posts() ) :
	while ( $listinghub_query->have_posts() ) : $listinghub_query->the_post();
	$id = get_the_ID();
	if(get_post_meta($id, 'listinghub_featured', true)=='featured'){
		$feature_img='';
		if(has_post_thumbnail()){ 
			$feature_image = wp_get_attachment_image_src( get_post_thumbnail_id( $id ), 'large' );
			if($feature_image[0]!=""){
				$feature_img =$feature_image[0];
			}
			}else{ 
			$feature_img= $defaul_feature_img;
		}
		$dir_data['title']=esc_html($post->post_title);
		$dir_data['dlink']=get_permalink($id);
		$dir_data['address']= get_post_meta($id,'address',true);										
		$dir_data['image']=  $feature_img;	
		$dir_data['locations']= '';								
		$dir_data['lat']=(get_post_meta($id,'latitude',true)!=''? get_post_meta($id,'latitude',true):0);
		$dir_data['lng']=(get_post_meta($id,'longitude',true)!=''? get_post_meta($id,'longitude',true):0);
		$dir_data['marker_icon']= $main_class->listinghub_get_categories_mapmarker($id,$listinghub_directory_url);
		$ins_lat=get_post_meta($id,'latitude',true);
		$ins_lng=get_post_meta($id,'longitude',true);
		$cat_link='';$cat_name='';$cat_slug='';
		// VIP
		$post_author_id= $listinghub_query->post->post_author;	
		$current_date=time();
	?>						
	
		<?php
				include( ep_listinghub_template. 'listing/single-template/archive-grid-block.php');
			?>	
		<?php
		array_push( $dirs_data, $dir_data );
		$i++;
	}
	endwhile;
						
 endif;

wp_reset_query();

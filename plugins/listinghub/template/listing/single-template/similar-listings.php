<?php
	if(!isset($listingid)){$listingid=0;}
	if(!isset($single_page_icon_saved)){$single_page_icon_saved=get_option('listinghub_single_icon_saved' );}
	
	$listinghub_similar = get_posts(array(
	'numberposts'	=> '5',
	'post_type'		=> $listinghub_directory_url,
	'post__not_in' => array(esc_html($listingid)),
	'post_status'	=> 'publish',
	'orderby'		=> 'rand',
	));
?>
<div class="sidebar-border">
	<div class="toptitle mb-3"><?php esc_html_e('Recent listing', 'listinghub'); ?></div>
	<?php
		if ( ! empty( $listinghub_similar ) ) {	
			$i=0;$company_logo_sim='';
			foreach( $listinghub_similar as $listing_sm ){
				$company_logo_sim='';
				$listing_contact_source_sim=get_post_meta($listing_sm->ID,'listing_contact_source',true);
				if($listing_contact_source_sim==''){$listing_contact_source_sim='user_info';}
				$dir_detail_sim= get_post($listing_sm->ID); 
				
				$author_id_sim=$dir_detail_sim->post_author;			
				if($listing_contact_source_sim=='new_value'){
						$feature_image_sim = wp_get_attachment_image_src( get_post_thumbnail_id( $listing_sm->ID ), 'large' );
						if(isset($feature_image_sim[0])){
							$company_logo_sim =$feature_image_sim[0];
						}
					
					}else{
					$company_logo_sim=get_user_meta($author_id_sim, 'listinghub_profile_pic_thum',true);
				}
			?>	<div class="sidebar-list-listing col-md-12"></div>
			<div class="row  card-list-4  " >
				<div class="col-md-3 col-3 mt-3 ">
					<div class="image "><a href="<?php echo  get_the_permalink($listing_sm->ID );?>">
						<?php
							if(trim($company_logo_sim)!=''){
							?>
								<img class="rounded-image img-responsive " src="<?php echo esc_url($company_logo_sim); ?>" alt="img">
							<?php
							}else{?>
								<div class="blank-rounded-image--"></div>
							<?php
							}
						?>
					</a>
					</div>
				</div>
				<div class="col-md-9 col-9 mt-3">
					<div class="ml-2"><a href="<?php echo  get_the_permalink($listing_sm->ID );?>" class="font-md "><?php echo get_the_title($listing_sm->ID); ?> </a></div>
					<div class="mt-0 "><span class="card-time"><span> 
							<?php
							$currentCategory_sl = $main_class->listinghub_get_categories_caching($listing_sm->ID,$listinghub_directory_url);
							$saved_icon_sl='';
							$cat_name2_sl='';
							$iii=0;
							if(isset($currentCategory_sl[0]->slug)){										
								foreach($currentCategory_sl as $c){							
									if(trim($saved_icon_sl)==''){
										$saved_icon_sl= listinghub_get_cat_icon($c->term_id);
									}
									if($iii==0){
										$cat_name2_sl = $c->name;
										}else{
										$cat_name2_sl = $cat_name2_sl .' / '.$c->name;
									}
									$iii++;
								}
							}
							
							
						?><i class="ml-1 <?php echo esc_html($saved_icon_sl); ?>"></i><?php echo esc_html($cat_name2_sl); ?></span></span></div>		  
						<div class="row mt-2 ">							
							<?php
								$location_array= wp_get_object_terms( $listing_sm->ID,  $listinghub_directory_url.'-locations');
								$i=0;$company_locations='';
								foreach($location_array as $one_tag){	
									$company_locations= $company_locations.' '.esc_html($one_tag->name); 						
								}	
								if(trim($company_locations)){
								?>
								<div class="col-12 "><span class="ml-2 card-briefcase"> <i class="fa-solid fa-location-dot mr-1"></i><?php echo esc_html($company_locations); ?></span></div>
								<?php
								}
							?>
						</div>
				</div>
			</div>
			<?php
			}
		}
	?>
</div>
<?php
	wp_reset_query();
?>
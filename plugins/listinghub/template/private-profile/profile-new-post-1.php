<?php
	$dir_map_api=get_option('epjbdir_map_api');	
	if($dir_map_api==""){$dir_map_api='';}	
	$listinghub_directory_url=get_option('ep_listinghub_url');					
	if($listinghub_directory_url==""){$listinghub_directory_url='listing';}
	$map_api_have='no';
				
	global $wpdb;
	// Check Max\
	$max=999999;									 
	 
	$listinghub_pack='listinghub_pack';
	$sql=$wpdb->prepare("SELECT * FROM $wpdb->posts WHERE post_type = '%s'  and post_status='draft' ",$listinghub_pack );
	$membership_pack = $wpdb->get_results($sql);
	$total_package = count($membership_pack);
	$max=999999;
	$package_id=get_user_meta($current_user->ID,'listinghub_package_id',true);							
					
	if($package_id!=""){  					
		$max=get_post_meta($package_id, 'listinghub_package_max_post_no', true);
	}											
	if($package_id=="" OR $package_id=="0"){  						
		global $wpdb;
		$sql=$wpdb->prepare("SELECT * FROM $wpdb->posts WHERE post_type = '%s' and post_status='draft'", $listinghub_pack);
		$membership_pack = $wpdb->get_results($sql);
		$total_package=count($membership_pack);								
		if($total_package>0){		  						
			$max=get_post_meta($package_id, 'listinghub_package_max_post_no', true);								
		}else{ 
			 $max=999999;
		}	
	}		
	
	if(isset($current_user->roles[0]) and $current_user->roles[0]=='administrator'){
		$max=999999;
	}		
						 
	
	$sql=$wpdb->prepare("SELECT count(*) as total FROM $wpdb->posts WHERE post_type ='%s' and post_status IN ('publish','pending','draft') and post_author='%d'",$listinghub_directory_url, $current_user->ID);									
	$all_post = $wpdb->get_row($sql);
	$my_post_count=$all_post->total;
	if ( $my_post_count>=$max or !current_user_can('edit_posts') )  { 
		$iv_redirect = get_option('epjblistinghub_profile_page');							
		$reg_page= get_permalink( $iv_redirect); 							
	?>
	<?php  esc_html_e('Please Upgrade Your Account','listinghub'); ?>
	<a href="<?php echo esc_url($reg_page).'?&profile=level'; ?>" title="Upgarde"><b><?php  esc_html_e('Here','listinghub'); ?> </b></a> 
	<?php  esc_html_e('To Add More Post.','listinghub'); ?>	
	<?php
		}else{	
	?>					
	<div class="row">
		<div class="col-md-12">	 
			<form action="" id="new_post" name="new_post"  method="POST" role="form">
				<div class=" form-group">
					<label for="text" class=" control-label"><?php  esc_html_e('Title','listinghub'); ?></label>
					<div class="  "> 
						<input type="text" class="form-control" name="title" id="title" value="" placeholder="<?php  esc_html_e('Enter Title Here','listinghub'); ?>">
					</div>																		
				</div>
				<br><br>	
				<input type="hidden" name="feature_image_id" id="feature_image_id" value="">
				
				<div class=" form-group row">	
						<div class="col-md-6" id="post_image_div">				
						</div> 
						
						<div class="col-md-6" id="post_image_edit">	
							<button type="button" onclick="listinghub_edit_post_image('post_image_div');"  class="btn btn-small-ar"><?php  esc_html_e('Company Logo[best fit 450X350]','listinghub'); ?> </button>
						</div>									
				</div>
				<br>
				<div class=" form-group row">																
					<div class=" col-md-6" id="post_image_topbaner">
					</div> 
					<div class=" form-group col-md-6">											
							<div class="" id="topbanner_image_edit">
							
								<button type="button" onclick="listinghub_topbanner_image('post_image_topbaner');"  class="btn btn-small-ar"><?php  esc_html_e('Top Banner[best fit 1200X400]','listinghub'); ?> </button>
							</div>									
					</div>								
				</div>	
			
			
			<input type="hidden" name="topbanner_image_id" id="topbanner_image_id" value="">	
				<div class="form-group">
					<label for="text" class="control-label"><?php  esc_html_e('Listing Description','listinghub'); ?>  </label>
					<?php
						$settings_a = array(															
						'textarea_rows' =>8,
						'editor_class' => 'form-control'															 
						);
						$editor_id = 'new_post_content';
						wp_editor( '', $editor_id,$settings_a );										
					?>
				</div>
				
									
				
				
				<div class="  form-group ">
					<label for="text" class="  control-label"><?php  esc_html_e('Status','listinghub'); ?>  </label>
					<select name="post_status" id="post_status"  class="form-control">
						<?php
								$listinghub_user_can_publish=get_option('listinghub_user_can_publish');	
								if($listinghub_user_can_publish==""){$listinghub_user_can_publish='yes';}	
								if(isset($current_user->roles[0]) and $current_user->roles[0]=='administrator'){?>
								<option value="publish"><?php esc_html_e('Publish','listinghub'); ?></option>
								<?php
								}else{
								
									if($listinghub_user_can_publish=="yes"){
									?>
									<option value="publish"><?php esc_html_e('Publish','listinghub'); ?></option>
									<?php
									}
								}
								
							?>											
						<option value="pending"><?php esc_html_e('Pending Review','listinghub'); ?></option>
						<option value="draft" ><?php esc_html_e('Draft','listinghub'); ?></option>	
					</select>	
				</div>										
				
				
				
				<span class="caption-subject">														
					<?php  esc_html_e('Contact Info','listinghub'); ?>
				</span>
				<hr/>
				<?php
				
					$listing_contact_source='';
					if($listing_contact_source==''){$listing_contact_source='user_info';}
				?>
				<div class=" form-group">	
					<div class="radio">											
						<label><input type="radio" name="contact_source" value="user_info"  class="mr-1" <?php echo ($listing_contact_source=='user_info'?'checked':''); ?> > <?php  esc_html_e(' Use The company Info ->','listinghub'); ?> <?php echo ucfirst($current_user->display_name); ?><?php  esc_html_e(' : Logo, Email, Phone, Website','listinghub'); ?> <a href="<?php echo get_permalink().'?profile=setting';?>" target="_blank"> <?php  esc_html_e('Edit','listinghub'); ?> </a></label>
					</div>
					<div class="radio">
						<label><input type="radio" name="contact_source" value="new_value" class="mr-1" <?php echo ($listing_contact_source=='new_value'?'checked':''); ?>><?php  esc_html_e(' New Contact Info','listinghub'); ?>  </label>
					</div>
				</div>
				<div  class="row" id="new_contact_div" <?php echo ($listing_contact_source=='user_info'?'style="display:none"':''); ?> >
					
					<div class=" form-group col-md-6">
						<label for="text" class=" control-label"><?php  esc_html_e('Company Name','listinghub'); ?></label>						
						<input type="text" class="form-control" name="company_name" id="company_name" value="" placeholder="<?php  esc_attr_e('Company name','listinghub'); ?>">
					</div>
					<div class=" form-group col-md-6">
						<label for="text" class=" control-label"><?php  esc_html_e('Phone','listinghub'); ?></label>						
						<input type="text" class="form-control" name="phone" id="phone" value="" placeholder="<?php  esc_attr_e('Enter Phone Number','listinghub'); ?>">
					</div>
					
					
					
					<div class=" form-group col-md-12">
						<label for="text" class=" control-label"><?php  esc_html_e('Address (Save in the listing field)','listinghub'); ?></label>
						<input type="text" class="form-control" name="address" id="address" value=""  placeholder="<?php  esc_html_e('Enter Address','listinghub'); ?>">
						<div id="autocomplete-results"></div>
					</div>
				
					<div class=" form-group col-md-6">
						<label for="text" class=" control-label"><?php  esc_html_e('City','listinghub'); ?></label>
						<input type="text" class="form-control" name="city" id="city" value="" placeholder="<?php  esc_attr_e('Enter city','listinghub'); ?>">
					</div>	
					<div class=" form-group col-md-6">
						<label for="text" class=" control-label"><?php  esc_html_e('State','listinghub'); ?></label>
						<input type="text" class="form-control" name="state" id="state" value="" placeholder="<?php  esc_attr_e('Enter State','listinghub'); ?>">
					</div>	
					<div class=" form-group col-md-6">
						<label for="text" class=" control-label"><?php  esc_html_e('Zipcode/Postcode','listinghub'); ?></label>
						<input type="text" class="form-control" name="postcode" id="postcode" value="" placeholder="<?php  esc_attr_e('Enter Zipcode/Postcode','listinghub'); ?>">
					</div>	
					<div class=" form-group col-md-6">
						<label for="text" class=" control-label"><?php  esc_html_e('Country','listinghub'); ?></label>
						<input type="text" class="form-control" name="country" id="country" value="" placeholder="<?php  esc_attr_e('Enter Country','listinghub'); ?>">
					</div>	
					<div class=" form-group col-md-6">
					<label for="text" class=" control-label"><?php  esc_html_e('Latitude ','listinghub'); ?></label>
					<input type="text" class="form-control" name="latitude" id="latitude" value="" placeholder="<?php  esc_attr_e('Enter Latitude','listinghub'); ?>">
				</div>	
					<div class=" form-group col-md-6">
					<label for="text" class=" control-label"><?php  esc_html_e('Longitude','listinghub'); ?></label>
					<input type="text" class="form-control" name="longitude" id="longitude" value="" placeholder="<?php  esc_attr_e('Enter Longitude','listinghub'); ?>">
				</div>	
					
					<div class=" form-group col-md-6">
						<label for="text" class=" control-label"><?php  esc_html_e('Email Address','listinghub'); ?></label>
						<input type="text" class="form-control" name="contact-email" id="contact-email" value="" placeholder="<?php  esc_attr_e('Enter Email Address','listinghub'); ?>">
					</div>
					<div class=" form-group col-md-6">
						<label for="text" class=" control-label"><?php  esc_html_e('Web Site','listinghub'); ?></label>
						<input type="text" class="form-control" name="contact_web" id="contact_web" value="" placeholder="<?php  esc_attr_e('Enter Web Site','listinghub'); ?>">
					</div>
				</div>	
				
				
				<hr/>
				<div class="clearfix"></div>
				<span class="caption-subject">												
					<?php  esc_html_e('Categories','listinghub'); ?>
				</span>
				<hr/>
				
					<div class=" form-group row"  id="listinghubcats-container">																	
						<?php $selected='';
						
							
							//listing
							$taxonomy = $listinghub_directory_url.'-category';
							$args = array(
							'orderby'           => 'name', 
							'order'             => 'ASC',
							'hide_empty'        => false, 
							'exclude'           => array(), 
							'exclude_tree'      => array(), 
							'include'           => array(),
							'number'            => '', 
							'fields'            => 'all', 
							'slug'              => '',
						
							'hierarchical'      => true, 
							'child_of'          => 0,
							'childless'         => false,
							'get'               => '', 
							);
							$terms = get_terms($taxonomy,$args); // Get all terms of a taxonomy
							if ( $terms && !is_wp_error( $terms ) ) :
							$i=0;
							foreach ( $terms as $term_parent ) {  ?>												
							<?php  
							if($term_parent->name!=''){	
							?>	
								<div class="col-md-6">
									<label class="form-group "> <input type="checkbox" name="postcats[]" id="postcats"  value="<?php echo esc_attr($term_parent->slug); ?>" class="listinghubcats-fields" > <?php echo esc_html($term_parent->name); ?> </label>
								</div>
							<?php
							}
								$i++;
							} 								
							endif;	
							
						?>	
							
						<div class="col-md-12">
							<input type="text" class="form-control" name="new_category" id="new_category" value="" placeholder="<?php  esc_html_e('Enter New Categories: Separate with commas','listinghub'); ?>">
						</div>		
						
					</div>
					
				<div class="clearfix"></div>
				<span class="caption-subject">												
					<?php  esc_html_e('Tags','listinghub'); ?>
				</span>
				<hr/>
				
				<div class=" row">		
				<?php
					$args2 = array(
					'type'                     => $listinghub_directory_url,
					'orderby'                  => 'name',
					'order'                    => 'ASC',
					'hide_empty'               => 0,
					'hierarchical'             => 1,
					'exclude'                  => '',
					'include'                  => '',
					'number'                   => '',
					'taxonomy'                 => $listinghub_directory_url.'-tag',
					'pad_counts'               => false
					);
					$main_tag = get_categories( $args2 );	
					$tags_all= '';													
					if ( $main_tag && !is_wp_error( $main_tag ) ) :
					foreach ( $main_tag as $term_m ) {
					?>
					<div class="col-md-6">
						<label class="form-group"> 
							<input type="checkbox" name="tag_arr[]" id="tag_arr[]"  value="<?php echo esc_attr($term_m->slug); ?>"> <?php echo esc_html($term_m->name); ?> </label>  
					</div>
					<?php	
					}
					endif;	
				?>
				</div>
				<div class=" form-group">	
						<input type="text" class="form-control" name="new_tag" id="new_tag" value="" placeholder="<?php  esc_html_e('Enter New Tags: Separate with commas','listinghub'); ?>">
				</div>															
				<div class="clearfix"></div>
				<span class="caption-subject">												
					<?php  esc_html_e('Locations','listinghub'); ?>
				</span>
				<hr/>
				
				<div class=" row mb-3">		
				<?php
					$args2 = array(
					'type'                     => $listinghub_directory_url,
					'orderby'                  => 'name',
					'order'                    => 'ASC',
					'hide_empty'               => 0,
					'hierarchical'             => 1,
					'exclude'                  => '',
					'include'                  => '',
					'number'                   => '',
					'taxonomy'                 => $listinghub_directory_url.'-locations',
					'pad_counts'               => false
					);
					$main_tag = get_categories( $args2 );	
					$tags_all= '';													
					if ( $main_tag && !is_wp_error( $main_tag ) ) :
					foreach ( $main_tag as $term_m ) {
					?>
					<div class="col-md-6">
						<label class="form-group"> 
							<input type="checkbox" name="location_arr[]" id="location_arr"  value="<?php echo esc_attr($term_m->slug); ?>"> <?php echo esc_html($term_m->name); ?> </label>  
					</div>
					<?php	
					}
					endif;	
				?>
						<div class="col-md-12">
							<input type="text" class="form-control" name="new_location" id="new_location" value="" placeholder="<?php  esc_html_e('Enter New Locations: Separate with commas','listinghub'); ?>">
						</div>															
						
				</div>
				<div class="clearfix"></div>	
				
			
				<span class="caption-subject">	
					<?php  esc_html_e('Videos ','listinghub'); ?>
				</span>
				
				<hr/>
			
					<div class="row">
						<div class=" col-md-6 form-group">
							<label for="text" class=" control-label"><?php  esc_html_e('Youtube','listinghub'); ?></label>
							<input type="text" class="form-control" name="youtube" id="youtube" value="" placeholder="<?php  esc_html_e('Enter Youtube video ID, e.g : bU1QPtOZQZU ','listinghub'); ?>">
						</div>
						<div class="col-md-6  form-group">
							<label for="text" class=" control-label"><?php  esc_html_e('vimeo','listinghub'); ?></label>
							<input type="text" class="form-control" name="vimeo" id="vimeo" value="" placeholder="<?php  esc_html_e('Enter vimeo ID, e.g : 134173961','listinghub'); ?>">								
						</div>
					</div>	
					
				<span class="caption-subject">											
					<?php  esc_html_e('Image Gallery','listinghub'); ?>
				</span>
				<hr/>
			
					<input type="hidden" name="gallery_image_ids" id="gallery_image_ids" value="">
					<div class="row" id="gallery_image_div">
					
					</div>									
				
				<div class="row">										
					<div class="  form-group col-md-12">									
						<button type="button" onclick="listinghub_edit_gallery_image('gallery_image_div');"  class="btn btn-small-ar mt-2"><?php  esc_html_e('Add Images','listinghub'); ?></button>
					</label>						
					</div>
				</div>
				
				<hr/>
				<span class="caption-subject">	
					<?php  esc_html_e('More Details ','listinghub'); ?>
				</span>								
				<hr/>
				<div class="row" id="listinghub_fields">
					<?php							
							$post_cats=array();			
							echo ''.$main_class->listinghub_listing_fields(0, $post_cats );
						?>	
				</div>
				<span class="caption-subject">	
					<?php  esc_html_e('Business Hours ','listinghub'); ?>
				</span>									
				<hr/>
				<div class="">
					<?php							
						include( ep_listinghub_template. 'private-profile/listing-open-close-time.php');						
						?>		
				</div>
				<hr/>
			
				<span class="caption-subject">	
					<?php  esc_html_e('FAQs ','listinghub'); ?>
				</span>								
				<hr/>
				<div class="row">
					<?php							
						include( ep_listinghub_template. 'private-profile/profile-add-edit-faq.php');						
						?>		
				</div>
				<span class="caption-subject">												
					<?php  esc_html_e('Button Setting','listinghub'); ?>
				</span>
				<hr/>
				<?php											
					
					$dir_style5_email=get_option('dir_style5_email');	
					if($dir_style5_email==""){$dir_style5_email='yes';}
					if($dir_style5_email=="yes"){
						$dirpro_email_button='';
						if($dirpro_email_button==""){$dirpro_email_button='yes';}
					?>	
					<div class="form-group row ">
						<label  class="col-md-12 button-c control-label"> <?php  esc_html_e('Contact Button','listinghub');  ?></label>
						<div class="col-md-3">
							<label>												
								<input type="radio" name="dirpro_email_button" id="dirpro_email_button" value='yes' class=" mr-1" <?php echo ($dirpro_email_button=='yes' ? 'checked':'' ); ?> ><?php  esc_html_e('Show','listinghub');  ?>
							</label>	
						</div>
						<div class="col-md-5">	
							<label>											
								<input type="radio"  name="dirpro_email_button" id="dirpro_email_button" class=" mr-1" value='no' <?php echo ($dirpro_email_button=='no' ? 'checked':'' );  ?> > <?php  esc_html_e('Hide','listinghub');  ?>
							</label>
						</div>	
					</div>		
					<?php
					}	
					?>
					
				
				
				<div class="clearfix"></div>	
				<div class="row">
					<div class="col-md-12  "> <hr/>
						<div class="" id="update_message"></div>
						<input type="hidden" name="user_post_id" id="user_post_id" value="0">
						<button type="button" onclick="listinghub_save_post();"  class="btn green-haze"><?php  esc_html_e('Save Post',	'listinghub'); ?></button>
						
					</div>	
					
				</div>	
			</form>
		</div>
	
	<?php
	} // for Role
?>
				
		
<!-- END PROFILE CONTENT -->
<?php
	$save_address='';
	$my_theme = wp_get_theme();
	$theme_name= strtolower($my_theme->get( 'Name' ));
	wp_enqueue_script('listinghub_add-edit-listing', ep_listinghub_URLPATH . 'admin/files/js/add-edit-listing.js');
	wp_localize_script('listinghub_add-edit-listing', 'realpro_data', array(
	'ajaxurl' 					=> admin_url( 'admin-ajax.php' ),
	'loading_image'			=> '<img src="'.ep_listinghub_URLPATH.'admin/files/images/loader.gif">',
	'current_user_id'		=>get_current_user_id(),
	'Set_Feature_Image'	=> esc_html__('Set Feature Image','listinghub'),
	'Set_plan_Image'		=> esc_html__('Set plan Image','listinghub'),
	'Set_Event_Image'		=> esc_html__('Set Event Image','listinghub'),
	'Gallery Images'		=> esc_html__('Gallery Images','listinghub'),
	'permalink'				=> get_permalink(),
	'save_address'			=> $save_address,
	'dirwpnonce'			=> wp_create_nonce("addlisting"),
	'theme_name'			=> $theme_name,
	) );
?> 
<?php
	$dir_map_api=get_option('epjbdir_map_api');	
	if($dir_map_api==""){$dir_map_api='';}	
	$listinghub_directory_url=get_option('ep_listinghub_url');					
	if($listinghub_directory_url==""){$listinghub_directory_url='listing';}
	$map_api_have='no';
?>
<div class="border-bottom pb-15 mb-3 toptitle-sub"><?php esc_html_e('Edit listing', 'listinghub'); ?>
</div>	

	
			<div class="tab-content">
				<?php					
					// Check Max\
					$package_id=get_user_meta($current_user->ID,'listinghub_package_id',true);						
					$max=get_post_meta($package_id, 'listinghub_package_max_post_no', true);
					$curr_post_id=$_REQUEST['post-id'];
					$current_post = $curr_post_id;
					$post_edit = get_post($curr_post_id); 
					$have_edit_access='yes';
					$exp_date= get_user_meta($current_user->ID, 'listinghub_exprie_date', true);
					if($exp_date!=''){
						$package_id=get_user_meta($current_user->ID,'listinghub_package_id',true);
						$dir_hide= get_post_meta($package_id, 'listinghub_package_hide_exp', true);
						if($dir_hide=='yes'){								
							if(strtotime($exp_date) < time()){	
								$have_edit_access='no';		
							}
						}
					}
					if($post_edit->post_author != $current_user->ID ){
						$have_edit_access='no';	
					}
					if(isset($current_user->roles[0]) and $current_user->roles[0]=='administrator'){
						$have_edit_access='yes';					
					}	
					if ( $have_edit_access=='no') { 
						$iv_redirect = get_option('epjblistinghub_login_page');
						$reg_page= get_permalink( $iv_redirect); 
					?>
					<?php  esc_html_e('Please ','listinghub'); ?>
					<a href="<?php echo esc_url($reg_page).'?&profile=level'; ?>" title="Upgarde"><b><?php  esc_html_e('Login or upgrade ','listinghub'); ?> </b></a> 
					<?php  esc_html_e('To Edit The Post.','listinghub'); ?>	
					<?php
						}else{
						$title = esc_html($post_edit->post_title);
						$content = $post_edit->post_content;
					?>					
					<div class="row">
						<div class="col-md-12">	 
							<form action="" id="new_post" name="new_post"  method="POST" role="form">
								<div class=" form-group">
									<label for="text" class=" control-label"><?php  esc_html_e('Title','listinghub'); ?></label>
									<div class="  "> 
										<input type="text" class="form-control" name="title" id="title" value="<?php echo esc_attr($title);?>" placeholder="<?php  esc_html_e('Enter Title Here','listinghub'); ?>">
									</div>																		
								</div>	
								<?php
									$listinghub_active_chatGPT=get_option('listinghub_active_chatGPT');		
									if($listinghub_active_chatGPT==""){$listinghub_active_chatGPT='yes';}	
									if($listinghub_active_chatGPT=="yes"){
									?>
									<div class="row">
										<div class="col-md-12 "> <hr/>											
											<button type="button" onclick="listinghub_chatgtp_settings_popup();"  class="btn green-haze mt-2 mb-2"><?php  esc_html_e('Create Post Using ChatGPT ',	'listinghub'); ?></button>	
											<div id="chatgpt-message"></div>
										</div>						
									</div>	
									<?php
									}
									?>
								<div class=" form-group row">																
										<div class=" col-md-6" id="post_image_div">	
											<?php $feature_image = wp_get_attachment_image_src( get_post_thumbnail_id( $curr_post_id ), 'thumbnail' );
												if(isset($feature_image[0])){ ?>
												<img title="profile image" class=" img-responsive rounded "  src="<?php  echo esc_url($feature_image[0]); ?>">
												<?php
												}
												$feature_image_id=get_post_thumbnail_id( $curr_post_id );
											?>
										</div> 
									<div class=" form-group col-md-6">											
											<div class="" id="post_image_edit">	
												<button type="button" onclick="listinghub_edit_post_image('post_image_div');"  class="btn btn-small-ar"><?php  esc_html_e('Feature Image / Company Logo','listinghub'); ?> </button>
											</div>									
									</div>								
								</div>	
								<input type="hidden" name="feature_image_id" id="feature_image_id" value="<?php echo esc_attr($feature_image_id); ?>">	
								
								<div class=" form-group row">																
										<div class=" col-md-6" id="post_image_topbaner">	
											<?php 
												$topbanner=get_post_meta($post_edit->ID,'topbanner', true);
												if(trim($topbanner)!=''){ 
													$listinghub_topbanner_image = wp_get_attachment_url($topbanner );
													?>
													<img title="image" class=" img-responsive rounded " src="<?php  echo esc_url($listinghub_topbanner_image); ?>">
												<?php
												}
												
											?>
										</div> 
									<div class=" form-group col-md-6">											
											<div class="" id="topbanner_image_edit">
											
												<button type="button" onclick="listinghub_topbanner_image('post_image_topbaner');"  class="btn btn-small-ar"><?php  esc_html_e('Top Banner[best fit 1200X400]','listinghub'); ?> </button>
											</div>									
									</div>								
								</div>	
								<input type="hidden" name="topbanner_image_id" id="topbanner_image_id" value="<?php echo esc_attr($topbanner); ?>">	
								<div class="form-group">
									<label for="text" class="control-label"><?php  esc_html_e('Listing Description','listinghub'); ?>  </label>
									<?php
										$settings_a = array(															
										'textarea_rows' =>8,
										'editor_class' => 'form-control'															 
										);
										$editor_id = 'new_post_content';
										wp_editor( $content, $editor_id,$settings_a );										
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
										<option value="pending" <?php echo (get_post_status( $post_edit->ID )=='pending'?'selected="selected"':'' ) ; ?>><?php esc_html_e('Pending Review','listinghub'); ?></option>
										<option value="draft" <?php echo (get_post_status( $post_edit->ID )=='draft'?'selected="selected"':'' ) ; ?> ><?php esc_html_e('Draft','listinghub'); ?></option>	
									</select>	
								</div>
								
								
								
								<span class="caption-subject">														
									<?php  esc_html_e('Contact Info','listinghub'); ?>
								</span>
								<hr/>
								<?php
									$listing_contact_source=get_post_meta($post_edit->ID,'listing_contact_source',true);
									if($listing_contact_source==''){$listing_contact_source='user_info';}
								?>
								<div class=" form-group">	
									<div class="radio">											
										<label><input type="radio" name="contact_source" value="user_info" class="mr-1" <?php echo ($listing_contact_source=='user_info'?'checked':''); ?> > <?php  esc_html_e(' Use The company Info ->','listinghub'); ?> <?php echo ucfirst($current_user->display_name); ?><?php  esc_html_e(' : Logo, Email, Phone, Website','listinghub'); ?> <a href="<?php echo get_permalink().'?profile=setting';?>" target="_blank"> <?php  esc_html_e('Edit','listinghub'); ?> </a></label>
									</div>
									<div class="radio">
										<label><input type="radio" name="contact_source" value="new_value" class="mr-1" <?php echo ($listing_contact_source=='new_value'?'checked':''); ?>><?php  esc_html_e(' New Contact Info','listinghub'); ?>  </label>
									</div>
								</div>
								
								
								<div  class="row" id="new_contact_div" <?php echo ($listing_contact_source=='user_info'?'style="display:none"':''); ?> >
																		
									<div class=" form-group col-md-6">																
										<div class=" col-md-6" id="post_image_div">	
											<?php $feature_image = wp_get_attachment_image_src( get_post_thumbnail_id( $curr_post_id ), 'thumbnail' );
												if(isset($feature_image[0])){ ?>
												<img title="profile image" class=" img-responsive" src="<?php  echo esc_url($feature_image[0]); ?>">
												<?php
												}
												$feature_image_id=get_post_thumbnail_id( $curr_post_id );
											?>
										</div> 
																	
									</div>	
									
									<div class="col-md-6" id="post_image_edit">	
																			
											<button type="button" onclick="listinghub_edit_post_image('post_image_div');"  class="btn btn-small-ar"><?php  esc_html_e('Logo Upload','listinghub'); ?> </button>
											<?php
												if(isset($feature_image[0])){ ?>											
											<button type="button" onclick="listinghub_remove_post_image('post_image_div');" class="btn btn-small-ar"> <i class="far fa-trash-alt"></i> </button>
											
											<?php
												}
											?>
									</div>	
									<div class=" form-group col-md-6">
										<label for="text" class=" control-label"><?php  esc_html_e('Company Name','listinghub'); ?></label>						
										<input type="text" class="form-control" name="company_name" id="company_name" value="<?php echo esc_attr(get_post_meta($post_edit->ID,'company_name',true)); ?>" placeholder="<?php  esc_html_e('Company name','listinghub'); ?>">
									</div>
									<div class=" form-group col-md-6">
										<label for="text" class=" control-label"><?php  esc_html_e('Phone','listinghub'); ?></label>						
										<input type="text" class="form-control" name="phone" id="phone" value="<?php echo esc_attr(get_post_meta($post_edit->ID,'phone',true)); ?>" placeholder="<?php  esc_html_e('Enter Phone Number','listinghub'); ?>">
									</div>
									
										
										
									<div class=" form-group col-md-12">
										<label for="text" class=" control-label"><?php  esc_html_e('Address (Save in the listing field)','listinghub'); ?></label>
										<input type="text" class="form-control" name="address" id="address" value="<?php echo esc_attr( get_post_meta( $post_edit->ID, 'address', true ) ); ?>"  placeholder="<?php  esc_html_e('Enter Address','listinghub'); ?>">
										<div id="autocomplete-results"></div>
									</div>
									
									<div class=" form-group col-md-6">
									<label for="text" class=" control-label"><?php  esc_html_e('City','listinghub'); ?></label>
										<input type="text" class="form-control" name="city" id="city" value="<?php echo esc_attr(get_post_meta($post_edit->ID,'city',true)); ?>" placeholder="<?php  esc_attr_e('Enter city','listinghub'); ?>">
									</div>	
									<div class=" form-group col-md-6">
										<label for="text" class=" control-label"><?php  esc_html_e('State','listinghub'); ?></label>
										<input type="text" class="form-control" name="state" id="state" value="<?php echo esc_attr(get_post_meta($post_edit->ID,'state',true)); ?>" placeholder="<?php  esc_attr_e('Enter State','listinghub'); ?>">
									</div>	
									<div class=" form-group col-md-6">
										<label for="text" class=" control-label"><?php  esc_html_e('Zipcode/Postcode','listinghub'); ?></label>
										<input type="text" class="form-control" name="postcode" id="postcode" value="<?php echo esc_attr(get_post_meta($post_edit->ID,'postcode',true)); ?>" placeholder="<?php  esc_attr_e('Enter Zipcode/Postcode','listinghub'); ?>">
									</div>	
																		
									<div class=" form-group col-md-6">
										<label for="text" class=" control-label"><?php  esc_html_e('Country','listinghub'); ?></label>
										<input type="text" class="form-control" name="country" id="country" value="<?php echo esc_attr(get_post_meta($post_edit->ID,'country',true)); ?>" placeholder="<?php  esc_attr_e('Enter Country','listinghub'); ?>">
									</div>
									<div class=" form-group col-md-6">
										<label for="text" class=" control-label"><?php  esc_html_e('Latitude ','listinghub'); ?></label>
										<input type="text" class="form-control" name="latitude" id="latitude" value="<?php echo esc_attr(get_post_meta($post_edit->ID,'latitude',true)); ?>" placeholder="<?php  esc_attr_e('Enter Latitude','listinghub'); ?>">
									</div>	
										<div class=" form-group col-md-6">
										<label for="text" class=" control-label"><?php  esc_html_e('Longitude','listinghub'); ?></label>
										<input type="text" class="form-control" name="longitude" id="longitude" value="<?php echo esc_attr(get_post_meta($post_edit->ID,'longitude',true)); ?>" placeholder="<?php  esc_attr_e('Enter Longitude','listinghub'); ?>">
									</div>	
									
									<div class=" form-group col-md-6">
										<label for="text" class=" control-label"><?php  esc_html_e('Email Address','listinghub'); ?></label>
										<input type="text" class="form-control" name="contact-email" id="contact-email" value="<?php echo esc_attr(get_post_meta($post_edit->ID,'contact-email',true)); ?>" placeholder="<?php  esc_html_e('Enter Email Address','listinghub'); ?>">
									</div>
									<div class=" form-group col-md-6">
										<label for="text" class=" control-label"><?php  esc_html_e('Web Site','listinghub'); ?></label>
										<input type="text" class="form-control" name="contact_web" id="contact_web" value="<?php echo esc_attr(get_post_meta($post_edit->ID,'contact_web',true)); ?>"  placeholder="<?php  esc_html_e('Enter Web Site','listinghub'); ?>">
									</div>
								</div>	
								<hr/>
								<div class="clearfix"></div>
								<span class="caption-subject">												
									<?php  esc_html_e('Categories','listinghub'); ?>
								</span>
								<hr/>
								<div class=" form-group row " id="listinghubcats-container">																	
									<?php
										$currentCategory=wp_get_object_terms( $post_edit->ID, $listinghub_directory_url.'-category');
										$post_cats=array();
										foreach($currentCategory as $c)
										{
											array_push($post_cats,$c->slug);
										}
										$selected='';									
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
										foreach ( $terms as $term_parent ) {
											if(in_array($term_parent->slug,$post_cats)){
												$selected=$term_parent->slug;
											}
											if($term_parent->name!=''){
										?>
										<div class="col-md-6">
											<label class="form-group"> <input type="checkbox" name="postcats[]" id="postcats" <?php echo ($selected==$term_parent->slug?'checked':'' );?> value="<?php echo esc_attr($term_parent->slug); ?>" class="listinghubcats-fields" > <?php echo esc_html($term_parent->name); ?> </label>
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
										$args =array();								
										
										
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
										$tags_all= wp_get_object_terms( $post_edit->ID,  $listinghub_directory_url.'-tag');
										if ( $main_tag && !is_wp_error( $main_tag ) ) :
										foreach ( $main_tag as $term_m ) {
											$checked='';
											foreach ( $tags_all as $term ) {
												if( $term->term_id==$term_m->term_id){
													$checked=' checked';
												}
											}
										?>
										<div class="col-md-6">
											<label class="form-group"> <input type="checkbox" name="tag_arr[]" id="tag_arr[]" <?php echo esc_attr($checked);?> value="<?php echo esc_attr($term_m->slug); ?>"> <?php echo esc_html($term_m->name); ?> </label>
										</div>
										<?php
										}
										endif;
										
									?>
								</div>
								<div class=" form-group">	
									<input type="text" class="form-control" name="new_tag" id="new_tag" value="" placeholder="<?php  esc_attr_e('Enter New Tags: Separate with commas','listinghub'); ?>">
								</div>			
								<div class="clearfix"></div>
								<span class="caption-subject">												
									<?php  esc_html_e('Locations','listinghub'); ?>
								</span>
								<hr/>
								<div class=" row">		
									<?php
										$args =array();								
										
										
										$args3 = array(
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
										$main_tag = get_categories( $args3 );
										$tags_all= wp_get_object_terms( $post_edit->ID,  $listinghub_directory_url.'-locations');
										if ( $main_tag && !is_wp_error( $main_tag ) ) :
										foreach ( $main_tag as $term_m ) {
											$checked='';
											foreach ( $tags_all as $term ) {
												if( $term->term_id==$term_m->term_id){
													$checked=' checked';
												}
											}
										?>
										<div class="col-md-6">
											<label class="form-group"> <input type="checkbox" name="location_arr[]" id="location_arr" <?php echo esc_attr($checked);?> value="<?php echo esc_attr($term_m->slug); ?>"> <?php echo esc_html($term_m->name); ?> </label>
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
									<?php  esc_html_e('Videos','listinghub'); ?>
								</span>
								<hr/>
								
									<div class="row">
										<div class=" col-md-6 form-group">
											<label for="text" class=" control-label"><?php  esc_html_e('Youtube','listinghub'); ?></label>
											<input type="text" class="form-control" name="youtube" id="youtube" value="<?php echo esc_attr(get_post_meta($post_edit->ID,'youtube',true));?>" placeholder="<?php  esc_attr_e('Enter Youtube video ID, e.g : bU1QPtOZQZU ','listinghub'); ?>">
										</div>
										<div class="col-md-6  form-group">
											<label for="text" class=" control-label"><?php  esc_html_e('vimeo','listinghub'); ?></label>
											<input type="text" class="form-control" name="vimeo" id="vimeo" value="<?php echo esc_attr(get_post_meta($post_edit->ID,'vimeo',true));?>" placeholder="<?php  esc_html_e('Enter vimeo ID, e.g : 134173961','listinghub'); ?>">								
										</div>
									</div>	
									
								<span class="caption-subject">											
									<?php  esc_html_e('Image Gallery','listinghub'); ?>
								</span>
								<hr/>
								
									
									<div class="row" id="gallery_image_div">
									</div>									
								
								<div class="row">										
									<div class="  form-group col-md-12">	
										<?php
											$gallery_ids=get_post_meta($curr_post_id ,'image_gallery_ids',true);
											$gallery_ids_array = array_filter(explode(",", $gallery_ids));
										?>
										<input type="hidden" name="gallery_image_ids" id="gallery_image_ids" value="<?php echo esc_attr($gallery_ids); ?>">
										<div class="row" id="gallery_image_div">
											<?php
												if(sizeof($gallery_ids_array)>0){
													foreach($gallery_ids_array as $slide){
													?>
													<div id="gallery_image_div<?php echo esc_attr($slide);?>" class="col-md-3"><img  class="img-responsive"  src="<?php echo wp_get_attachment_url( $slide ); ?>"><button type="button" onclick="listinghub_remove_gallery_image('gallery_image_div<?php echo esc_attr($slide);?>', <?php echo esc_attr($slide);?>);"  class="btn btn-small-ar btn-danger"><?php esc_html_e('X','listinghub'); ?> </button> </div>
													<?php
													}
												}
											?>
										</div>
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
															
									echo  ''.$main_class->listinghub_listing_fields($post_edit->ID, $post_cats );
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
									$dirpro_email_button=esc_attr(get_post_meta($post_edit->ID,'dirpro_email_button',true));
									if($dirpro_email_button==""){$dirpro_email_button='yes';}
								?>	
								<div class="form-group row ">
									<label  class="col-md-12 button-c control-label"> <?php  esc_html_e('Contact Button','listinghub');  ?></label>
									<div class="col-md-3">
										<label>												
											<input type="radio" name="dirpro_email_button" id="dirpro_email_button" value='yes' class="mr-1" <?php echo ($dirpro_email_button=='yes' ? 'checked':'' ); ?> > <?php  esc_html_e('Show','listinghub');  ?>
										</label>	
									</div>
									<div class="col-md-5">	
										<label>											
											<input type="radio"  name="dirpro_email_button" id="dirpro_email_button" value='no' class="mr-1" <?php echo ($dirpro_email_button=='no' ? 'checked':'' );  ?> > <?php  esc_html_e('Hide','listinghub');  ?>
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
									
									<input type="hidden" name="user_post_id" id="user_post_id" value="<?php echo esc_attr($curr_post_id); ?>">
									<button type="button" onclick="listinghub_update_post();"  class="btn green-haze"><?php  esc_html_e('Update Post',	'listinghub'); ?></button>
								</div>	
							</div>	
						</form>
					</div>
				</div>
				<?php
				} // for Role
			?>
		

<!-- END PROFILE CONTENT -->
<?php
	$save_address=get_post_meta($curr_post_id ,'address',true);
	
	$my_theme = wp_get_theme();
	$theme_name= strtolower($my_theme->get( 'Name' ));
	wp_enqueue_script('listinghub_add-edit-listing', ep_listinghub_URLPATH . 'admin/files/js/add-edit-listing.js');
	wp_localize_script('listinghub_add-edit-listing', 'realpro_data', array(
	'ajaxurl' 					=> admin_url( 'admin-ajax.php' ),
	'loading_image'			=> '<img src="'.ep_listinghub_URLPATH.'admin/files/images/loader.gif">',
	'current_user_id'		=>get_current_user_id(),
	'Set_Feature_Image'	=> esc_html__('Set Feature Image','listinghub'),
	'Set_plan_Image'		=> esc_html__('Set Image ','listinghub'),
	'Set_Event_Image'		=> esc_html__(' Set Image ','listinghub'),
	'Gallery Images'		=> esc_html__('Gallery Images','listinghub'),
	'permalink'				=> get_permalink(),
	'save_address'			=>$save_address,
	'dirwpnonce'			=> wp_create_nonce("addlisting"),
	'theme_name'			=> $theme_name,
	) );
	?> 		
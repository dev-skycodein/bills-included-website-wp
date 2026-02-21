<?php
	
	wp_enqueue_script("jquery");
	wp_enqueue_script('jquery-ui-core');
	wp_enqueue_script('jquery-ui-accordion');
	 
	wp_enqueue_style('bootstrap', 	ep_listinghub_URLPATH . 'admin/files/css/iv-bootstrap.css');
	wp_enqueue_style('fontawesome', ep_listinghub_URLPATH . 'admin/files/css/fontawesome.css');
	wp_enqueue_style('jquery.fancybox', ep_listinghub_URLPATH . 'admin/files/css/jquery.fancybox.css');
	wp_enqueue_style('colorbox', ep_listinghub_URLPATH . 'admin/files/css/colorbox.css');
	wp_enqueue_style('jquery-ui', ep_listinghub_URLPATH . 'admin/files/css/jquery-ui.css');
	wp_enqueue_script('colorbox', ep_listinghub_URLPATH . 'admin/files/js/jquery.colorbox-min.js');	
	wp_enqueue_script('jquery.fancybox',ep_listinghub_URLPATH . 'admin/files/js/jquery.fancybox.js');	
	wp_enqueue_style('listinghub_single-listing', ep_listinghub_URLPATH . 'admin/files/css/single-listing.css');	
	
	$main_class = new eplugins_listinghub;
	$listinghub_directory_url=get_option('ep_listinghub_url');
	if($listinghub_directory_url==""){$listinghub_directory_url='listing';}
	global $post,$wpdb, $current_user;
	$favorite_icon='';$listingid ='0';
	if(isset($_GET['detail'])){ 			
	  $post = get_page_by_path($_GET['detail'], OBJECT, $listinghub_directory_url);
		if ($post) {
			$listingid =$post->ID;
		}	
	
	
	$id=$listingid;
	$post_id_1 = get_post($listingid);
	$post_id_1->post_title;
	$active_single_fields_saved=get_option('listinghub_single_fields_saved' );	
	if(empty($active_single_fields_saved)){$active_single_fields_saved=listinghub_get_listing_fields_all_single();}	
	$single_page_icon_saved=get_option('listinghub_single_icon_saved' );		
	$wp_directory= new eplugins_listinghub();
	
	$currentCategory = $main_class->listinghub_get_categories_caching($listingid,$listinghub_directory_url);
	$cat_name2='';
	if(isset($currentCategory[0]->slug)){										
		foreach($currentCategory as $c){						
			$cat_name2 = $cat_name2. $c->name.' / ';
		}
	}
	$listing_contact_source=get_post_meta($listingid,'listing_contact_source',true);
	if($listing_contact_source==''){$listing_contact_source='user_info';}
	if($listing_contact_source=='new_value'){
		$company_logo='';
		}else{
		$company_logo='';
	}
	// View count (total + session) is tracked in plugin via template_redirect.
	$data_for_top=array();	
	$data_for_top['category']='category';	
	$data_for_top['post_date']='post_date';	
	$data_not_for_all_section=array();	
	$data_not_for_all_section['title']='title';
	$data_not_for_all_section['address']='address';
	$data_not_for_all_section['top-image']='top-image';
	$data_not_for_all_section['category']='category';	
	$data_not_for_all_section['contact_button']='contact_button';
	$data_not_for_all_section['pdf_button']='pdf_button';
	$data_not_for_all_section['favorite']='favorite';
	$data_not_for_all_section['simillar_listing']='simillar_listing';
	$data_not_for_all_section['review']='review';
	$dir_detail= get_post($listingid); 	
	$author_id=$dir_detail->post_author;
	$user_info = get_userdata( $author_id);
	$company_email =$user_info->user_email;	
	if($listing_contact_source=='new_value'){
		$company_name= get_post_meta($listingid, 'company_name',true);
		$company_address= get_post_meta($listingid, 'address',true);
		$company_web=get_post_meta($listingid, 'contact_web',true);
		$company_phone=get_post_meta($listingid, 'phone',true);
		$company_email= get_post_meta($listingid, 'contact-email',true);
		$company_logo = get_post_meta($listingid, 'company_logo', true);
		if (trim((string) $company_logo) === '' && has_post_thumbnail()){
			$feature_image = wp_get_attachment_image_src( get_post_thumbnail_id( $listingid ), 'large' );
			if(isset($feature_image[0])){
				$company_logo =$feature_image[0];
			}
		}
		}else{ 
			$company_name= get_user_meta($author_id,'full_name', true);
			$company_address= get_user_meta($author_id,'address', true);
			$company_web=get_user_meta($author_id,'website', true);
			$company_phone=get_user_meta($author_id,'phone', true);
			$company_logo=get_user_meta($author_id, 'listinghub_profile_pic_thum',true);
	}

	// When listing is tied to an agency, logo comes from single source: agency_logo (agency post meta).
	$agency_post_id_for_logo = (int) get_post_meta( $listingid, 'agency_post_id', true );
	if ( $agency_post_id_for_logo ) {
		$agency_logo_url = get_post_meta( $agency_post_id_for_logo, 'agency_logo', true );
		if ( is_string( $agency_logo_url ) && trim( $agency_logo_url ) !== '' ) {
			$company_logo = trim( $agency_logo_url );
		}
	}
		
?>
<!-- SLIDER SECTION -->

<div class="bootstrap-wrapper ">	
	<div class="container">
		<section class="section ">       
			<?php
				$topbanner=get_post_meta($listingid,'topbanner', true);
				if(trim($topbanner)!=''){					
					$default_image_banner = wp_get_attachment_url($topbanner );
				}else{
					if(get_option('listinghub_banner_defaultimage')!=''){
						$default_image_banner= wp_get_attachment_image_src(get_option('listinghub_banner_defaultimage'),'large');
						if(isset($default_image_banner[0])){									
							$default_image_banner=$default_image_banner[0] ;			
						}
						}else{
						$default_image_banner=ep_listinghub_URLPATH."/assets/images/banner.png";
					}
				}
				
			?>
			<?php			
			if(array_key_exists('top-image',$active_single_fields_saved)){ 				
			?>			
			<div class=" banner-hero banner-image-single mt-1" style="background:url(<?php echo esc_url($default_image_banner); ?>) no-repeat; background-size:cover;"></div>
			<?php
			}
			?>
			<div class="row mt-2">
				<div class="col-lg-7 col-md-12 mb-2">					
					<?php			
					if(array_key_exists('title',$active_single_fields_saved)){ 	
						$saved_icon= listinghub_get_icon($single_page_icon_saved, 'title' ,'single');
					?>
					<h2 class="title-detail "><i class="<?php echo esc_html($saved_icon); ?> "></i> <?php echo get_the_title($listingid); ?></h2>
					<?php
					}
					?>
					<div class="mt-0 mb-15">
					<?php			
					if(array_key_exists('post_date',$active_single_fields_saved)){ 				
					?>	
					<span class="card-time"><?php
						$saved_icon= listinghub_get_icon($single_page_icon_saved,'post_date', 'single');
						?><i class=" <?php echo esc_html($saved_icon); ?>"></i><?php echo get_the_date( 'd F - Y g:i a ', $listingid ); ?>
					</span>		
					<?php
						}
					?>
					<?php			
					if(array_key_exists('category',$active_single_fields_saved)){ 				
					?>	
					<span class="card-time"><?php					
							$currentCategory = $main_class->listinghub_get_categories_caching($listingid,$listinghub_directory_url);
							$saved_icon='';
							$cat_name2='';
							$i=0;
							if(isset($currentCategory[0]->slug)){										
								foreach($currentCategory as $c){							
									if(trim($saved_icon)==''){
										$saved_icon= listinghub_get_cat_icon($c->term_id);
									}
									if($i==0){
										$cat_name2 = $c->name;
										}else{
										$cat_name2 = $cat_name2 .' / '.$c->name;
									}
									$i++;
								}
							}
							
						?><i class=" ml-2 <?php echo esc_html($saved_icon); ?>"></i><strong class="small-heading"><?php echo esc_html($cat_name2); ?></strong>
					</span>		
					<?php
						}
					?>
					<?php
					// Open/close status badge removed per client request.
					?>
					
					
					
					</div>
					
				</div>
				<div class="col-lg-5 col-md-12 text-lg-end ">
					<div class="btn-feature text-right">
							<?php
							$user_ID = get_current_user_id();
							$favourites='no';
							if($user_ID>0){
								$my_favorite = get_post_meta($listingid,'_favorites',true);
								$all_users = explode(",", $my_favorite);
								if (in_array($user_ID, $all_users)) {
									$favourites='yes';
								}
							}
							?>
							<?php
							$listing_apply='no';
							$user_ID = get_current_user_id();
							$listing_apply_all = get_user_meta($user_ID,'listing_apply_all',true);
							$listing_apply_all = explode(",", $listing_apply_all);
							if (in_array($listingid, $listing_apply_all)) {
								$listing_apply='yes';
							}										
							?>
							<?php			
								if(array_key_exists('contact_button',$active_single_fields_saved)){ 				
							?>
								<button type="button" class="btn btn-big mr-2 mb-2 " onclick="listinghub_call_popup('<?php echo esc_html($listingid);?>')"><?php esc_html_e( 'Contact', 'listinghub' ); ?></button>
							<?php
							}
							?>
							
							<?php			
							if(array_key_exists('pdf_button',$active_single_fields_saved)){ 				
							?>							
							<a class=" btn btn-border  mr-2 mb-2" href="<?php echo get_permalink();?>?&listinghubpdfpost=<?php echo esc_attr($listingid);?>" target="_blank"><i class="fas fa-download"></i> <?php esc_html_e('PDF', 'listinghub'); ?></a>
							<?php
							}
							?>
							<?php			
							if(array_key_exists('favorite',$active_single_fields_saved)){ 				
									
								$favorite_icon= listinghub_get_icon($single_page_icon_saved, 'favorite', 'single');
								if($favorite_icon==''){
									$favorite_icon='far fa-heart';
								}else{
									$favorite_icon =str_replace('mr-2','',$favorite_icon );
								}
							?>
							<span id="fav_dir<?php echo esc_html($listingid); ?>">
								<?php
									if($favourites=='yes'){ ?>
									<button class="btn btn-big mb-2" data-placement="left" data-toggle="tooltip" title="<?php esc_html_e('Saved','listinghub'); ?>" href="javascript:;" onclick="listinghub_save_unfavorite('<?php echo esc_attr($listingid); ?>')" >
										<i class="<?php echo esc_html($favorite_icon); ?>" ></i>
									</button>
									<?php
										}else{
									?>
									<button class="btn btn-border mb-2" data-placement="left" data-toggle="tooltip" title="<?php esc_html_e('Save','listinghub'); ?>" href="javascript:;" onclick="listinghub_save_favorite('<?php echo esc_attr($listingid); ?>')" >
										<i class="<?php echo esc_html($favorite_icon); ?>" ></i>
									</button>
									<?php
									}
								?>
							</span>
							<?php
							}
						?>
					</div>		
				</div>
			</div>
			<div class="border-bottom pt-10 pb-10"></div>       
		</section>
		<div class="row mt-5">
			<div class="col-lg-8 col-md-12 col-sm-12 col-12">				
			
				<div class="listing-overview">
					<?php
						$saved_icon_cat='';
						if(is_array($active_single_fields_saved)){
							foreach($active_single_fields_saved  as $field_key => $field_value){
								$saved_icon= listinghub_get_icon($single_page_icon_saved, $field_key, 'single');
								if( !in_array($field_key,$data_for_top ) AND !in_array($field_key,$data_not_for_all_section) ){	 					
									switch ($field_key) {																
										case "description": 
									?>									
									<div class="border-bottom pb-15 mb-3 toptitle"><i class="<?php echo esc_html($saved_icon); ?>"></i> <?php esc_html_e('Description','listinghub'); ?></div>
									<div class=" none  mb-4">
										<?php
										$is_scraped_listing = (int) get_post_meta( $listingid, 'agency_post_id', true ) > 0;
										$content_post       = get_post( $listingid );
										$raw_content        = $content_post ? $content_post->post_content : '';

										if ( $is_scraped_listing && trim( (string) $raw_content ) !== '' ) {
											$text   = wp_strip_all_tags( $raw_content );
											$length = function_exists( 'mb_strlen' ) ? mb_strlen( $text ) : strlen( $text );
											$half   = (int) floor( $length / 2 );
											$was_truncated = false;

											if ( $half > 0 && $half < $length ) {
												if ( function_exists( 'mb_substr' ) && function_exists( 'mb_strrpos' ) ) {
													$before_half = mb_substr( $text, 0, $half );
													$cut_pos     = mb_strrpos( $before_half, ' ' );
													$summary     = $cut_pos !== false ? mb_substr( $text, 0, $cut_pos ) : mb_substr( $text, 0, $half );
												} else {
													$before_half = substr( $text, 0, $half );
													$cut_pos     = strrpos( $before_half, ' ' );
													$summary     = $cut_pos !== false ? substr( $text, 0, $cut_pos ) : substr( $text, 0, $half );
												}
												$was_truncated = true;
											} else {
												$summary = $text;
											}

											$summary    = trim( $summary );
											$source_url = get_post_meta( $listingid, 'source_listing_url', true );

											$display = $summary;
											if ( $was_truncated ) {
												$display .= '...';
											}

											echo '<p>' . esc_html( $display ) . ' ';
											if ( ! empty( $source_url ) ) {
												echo '<a href="' . esc_url( $source_url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Click here to see full description on original listing.', 'listinghub' ) . '</a>';
											} else {
												echo esc_html__( 'Click here to see full description on original listing.', 'listinghub' );
											}
											echo '</p>';
										} else {
											$content = apply_filters( 'the_content', $raw_content );
											$content = str_replace( ']]>', ']]&gt;', $content );
											echo wpautop( $content );
										}
										?>												
									</div>
									<?php											
										break;
										case "video": 
										include(ep_listinghub_template . '/listing/single-template/video.php');
										break;
										case "tag": 
										include(ep_listinghub_template . '/listing/single-template/tags.php');
										break;
										case "location": 
										include(ep_listinghub_template . '/listing/single-template/locations.php');
										break;
										case "image-gallery": 
										include(ep_listinghub_template . '/listing/single-template/image-gallery.php');
										break;
										case "faq": 
										include(ep_listinghub_template . '/listing/single-template/faqs.php');
										break;
										default:
										if(get_post_meta($listingid,$field_key,true)!=''){
											$custom_meta_data=get_post_meta($listingid,$field_key,true);
											if(is_array($custom_meta_data)){
												$full_data='';
												foreach( $custom_meta_data as $one_data){
													$full_data=$full_data.'<span class="btn btn-small background-urgent btn-pink mr-1">'.$one_data.'</span>';
												}
												$custom_meta_data=$full_data;
											}
										?>
										<div class="border-bottom pb-15 mb-3 toptitle"><i class="<?php echo esc_html($saved_icon); ?>"></i>  <?php echo esc_html(ucwords(str_replace('_',' ',$field_key)));  ?></div>
										<div class="row col-md-12 mb-4"> <?php echo wp_kses($custom_meta_data,'post'); ?></div>											
										<?php	
										}
										break;
									}										
								}									
							}
						}								
					?>	
					<?php
					if(array_key_exists('review',$active_single_fields_saved)){ 
						include(ep_listinghub_template.'/listing/single-template/reviews.php');			
					}
					?>
				
				</div>
				
				 <?php
					include(ep_listinghub_template . '/listing/single-template/footer_share.php');  
				 ?>
			</div>
			<div class="col-lg-4 col-md-12 col-sm-12 col-12 pl-40 pl-lg-15 mt-lg-30">
				
				<div class="sidebar-border">
					<?php
					if(array_key_exists('author_info',$active_single_fields_saved)){ 
					?>
						<div class="row mb-4">					
							<div class="col-4">
								<?php			
								if(array_key_exists('company-logo',$active_single_fields_saved)){ 
									if(trim($company_logo)!=''){
								?>
									<img alt="image" class="rounded-logo img-fluid" src="<?php echo esc_url($company_logo); ?>">
								<?php
									}else{?>
										<div class="blank-rounded-logo-"></div>
									<?php
									}
								}
								?>
							</div>
								
							<div class="col-8">
								  <?php
									$location_array= wp_get_object_terms( $listingid,  $listinghub_directory_url.'-locations');
									$i=0;$company_locations='';
									foreach($location_array as $one_tag){	
										$company_locations= $company_locations.' '.esc_html($one_tag->name); 						
									}	
									?>
									<div class="">
										
										<div class="col-12">	
											<span class="toptitle"><?php echo esc_html($company_name); ?></span>
										</div>
										
										<div class="col-12">	
												<?php
												if(!empty($company_locations)){
												?>
												<span class="card-location mt-2"><i class="fa-solid fa-location-dot mr-2"></i><?php echo esc_html($company_locations); ?>
												</span>
												<?php
												}
												$agency_post_id = (int) get_post_meta( $listingid, 'agency_post_id', true );
												$agency_owner   = $agency_post_id ? (int) get_post_meta( $agency_post_id, 'agency_owner', true ) : 0;

												// Count listings differently when this listing is tied to an unclaimed agency.
												if ( $agency_post_id ) {
													$listinghub_directory_url = get_option( 'ep_listinghub_url' );
													if ( $listinghub_directory_url === '' ) {
														$listinghub_directory_url = 'listing';
													}

													$agency_listings_query = new WP_Query(
														array(
															'post_type'      => $listinghub_directory_url,
															'post_status'    => 'publish',
															'posts_per_page' => 99999,
															'meta_query'     => array(
																array(
																	'key'     => 'agency_post_id',
																	'value'   => $agency_post_id,
																	'compare' => '=',
																),
															),
														)
													);

													$total_listings = (int) $agency_listings_query->found_posts;
												} else {
													// Fallback: total listings authored by this user (existing behaviour).
													$total_listings = $main_class->listinghub_total_listing_count( $user_info->ID, $allusers = 'no' );
												}
												?>
										</div>
										
										<div class="col-12">	
											<?php if ( $agency_post_id && $agency_owner === 0 ) : ?>
												<a class="link-underline mt-1 " href="<?php echo esc_url( get_permalink( $agency_post_id ) ); ?>">
													<?php echo esc_html( $total_listings ); ?> <?php esc_html_e( 'listing', 'listinghub' ); ?>
												</a>
											<?php else : ?>
												<a class="link-underline mt-1 " href="<?php echo esc_url( get_post_type_archive_link( $listinghub_directory_url ) . '?&listing-author=' . esc_attr( $user_info->ID ) ); ?>">
													<?php echo esc_html( $total_listings ); ?> <?php esc_html_e( 'listing', 'listinghub' ); ?>
												</a>
											<?php endif; ?>
										</div>
										<?php if ( $agency_post_id && $agency_owner === 0 ) : ?>
										<style>
											.cya-claim-agency-button { font-size: 15px; }
										</style>
										<div class="col-12 mt-2">
											<button type="button" class="btn btn-border btn-sm mt-1 cya-claim-agency-button" onclick="bia_open_claim_agency_popup(this,'<?php echo esc_attr( (string) $agency_post_id ); ?>','<?php echo esc_attr( $listingid ); ?>')">
												<?php esc_html_e( 'Claim Your Agency', 'listinghub' ); ?>
											</button>
										</div>
										<?php elseif ( $agency_post_id && $agency_owner !== 0 ) : ?>
										<style>.cya-profile-claimed-badge{display:inline-block;padding:5px 10px;font-size:12px;font-weight:600;color:#065f46;background:#d1fae5;border:1px solid #a7f3d0;border-radius:6px;}</style>
										<div class="col-12 mt-2">
											<span class="cya-profile-claimed-badge"><?php esc_html_e( 'Agency Claimed', 'listinghub' ); ?></span>
										</div>
										<?php endif; ?>
									</div>	
									
							</div>
							
						</div>
						<?php
						}
						?>
			
					
				
						<div class="sidebar-list-listing">
						<?php
							if(array_key_exists('map',$active_single_fields_saved)){ 
							?>
							<div class="box-map mt-4">
								<?php
								$latitude=get_post_meta($listingid,'latitude',true);
								$longitude=get_post_meta($listingid,'longitude',true);
								if($latitude!='' AND $longitude!='' ){?>
									<iframe width="100%" height="325" frameborder="0" scrolling="no" marginheight="0" marginwidth="0"src = "https://maps.google.com/maps?q=<?php echo esc_html($latitude); ?>,<?php echo esc_html($longitude); ?>&amp;ie=UTF8&amp;&amp;output=embed"></iframe>
								<?php
								}else{?>								
									<iframe width="100%" height="325" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.com/maps?q=<?php echo esc_attr($company_address); ?>&amp;ie=UTF8&amp;&amp;output=embed"></iframe>
								<?php
								}
								?>
								
								
								
								
							</div>
							<?php
								}
							?>
						<?php
							if(array_key_exists('address',$active_single_fields_saved)){ 
							?>
						<ul class="ul-disc">
							<?php if($company_address!=''){  ?>
							<li><?php echo esc_html($company_address); ?></li>
							<?php
							}
							?>
							<?php if($company_phone!=''){  ?>
							<li><?php esc_html_e('Phone','listinghub'); ?> : <?php echo esc_html($company_phone); ?></li>
							<?php
							}
							?>
							<?php if($company_email!=''){  ?>
							<li><?php esc_html_e('Email','listinghub'); ?> : <?php echo esc_html($company_email); ?></li>
							<?php
							}
							?>
							
						</ul>
						<?php
							}
						?>
					</div>
					
				</div>
				<?php			
					if(array_key_exists('open_status_table',$active_single_fields_saved)){ 
						include(ep_listinghub_template.'/listing/single-template/business_hours.php');		
					}
					?>
				<?php
				if(array_key_exists('simillar_listing',$active_single_fields_saved)){
					include(ep_listinghub_template.'/listing/single-template/similar-listings.php');			
				}
				?>
			</div>
		</div>
		
	
	</div>
</div>
<?php

	wp_enqueue_script('popper', ep_listinghub_URLPATH . 'admin/files/js/popper.min.js');
	wp_enqueue_script('bootstrap', ep_listinghub_URLPATH . 'admin/files/js/bootstrap.min-4.js');
	
	wp_enqueue_script('listinghub_single-listing', ep_listinghub_URLPATH . 'admin/files/js/single-listing.js');
	wp_localize_script('listinghub_single-listing', 'listinghub_data', array(
	'ajaxurl' 			=> admin_url( 'admin-ajax.php' ),
	'loading_image'		=> '<img src="'.ep_listinghub_URLPATH.'admin/files/images/loader.gif">',
	'current_user_id'	=>get_current_user_id(),
	'Please_login'=>esc_html__('Please login', 'listinghub' ),
	'Add_to_Favorites'=>esc_html__('Save', 'listinghub' ),
	'Added_to_Favorites'=>esc_html__('Saved', 'listinghub' ),
	'success'=>esc_html__('Message Sent', 'listinghub' ),
	'Please_put_your_message'=>esc_html__('Please put your name,email Cover letter & attached file', 'listinghub' ),
	'contact'=> wp_create_nonce("contact"),
	'listing'=> wp_create_nonce("listing"),
	'cv'=> wp_create_nonce("Doc/CV/PDF"),
	'ep_listinghub_URLPATH'=>ep_listinghub_URLPATH,
	'favorite_icon'=>$favorite_icon,
	) );
	
	
?>
<?php
	wp_reset_query();
}
?>

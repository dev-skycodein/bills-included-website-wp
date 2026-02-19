<?php
	get_header();
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
	wp_enqueue_style('listinghub_single-listing-custom', ep_listinghub_URLPATH . 'admin/files/css/single-listing-custom.css', array('listinghub_single-listing'));
	
	$main_class = new eplugins_listinghub;
	$listinghub_directory_url=get_option('ep_listinghub_url');
	if($listinghub_directory_url==""){$listinghub_directory_url='listing';}
	global $post,$wpdb, $current_user;
	$favorite_icon='';
	$listingid = get_the_ID();
	$post_id_1 = get_post($listingid);
	$post_id_1->post_title;
	$active_single_fields_saved=get_option('listinghub_single_fields_saved' );	
	if(empty($active_single_fields_saved)){$active_single_fields_saved=listinghub_get_listing_fields_all_single();}	
	$single_page_icon_saved=get_option('listinghub_single_icon_saved' );		
	$wp_directory= new eplugins_listinghub();
	$listinghub_section_has_data = function( $field_key ) use ( $listingid, $listinghub_directory_url ) {
		switch ( $field_key ) {
			case 'description':
				$p = get_post( $listingid );
				return $p && trim( (string) $p->post_content ) !== '';
			case 'video':
				return trim( (string) get_post_meta( $listingid, 'vimeo', true ) ) !== '' || trim( (string) get_post_meta( $listingid, 'youtube', true ) ) !== '';
			case 'tag':
				$terms = wp_get_object_terms( $listingid, $listinghub_directory_url . '-tag' );
				return ! is_wp_error( $terms ) && ! empty( $terms );
			case 'location':
				$terms = wp_get_object_terms( $listingid, $listinghub_directory_url . '-locations' );
				return ! is_wp_error( $terms ) && ! empty( $terms );
			case 'image-gallery':
				$gallery_ids = get_post_meta( $listingid, 'image_gallery_ids', true );
				$gallery_arr = array_filter( array_map( 'trim', explode( ',', (string) $gallery_ids ) ) );
				return ! empty( $gallery_arr ) || has_post_thumbnail( $listingid );
			case 'faq':
				return trim( (string) get_post_meta( $listingid, 'faq_title0', true ) ) !== '';
			case 'review':
				return true;
			default:
				$val = get_post_meta( $listingid, $field_key, true );
				return $val !== '' && $val !== array();
		}
	};
	if ( array_key_exists( 'simillar_listing', $active_single_fields_saved ) ) {
		wp_enqueue_style( 'listinghub_archive-listing', ep_listinghub_URLPATH . 'admin/files/css/archive-listing.css' );
		wp_enqueue_style( 'font-awesome', ep_listinghub_URLPATH . 'admin/files/css/all.min.css' );
		wp_enqueue_style( 'slick', ep_listinghub_URLPATH . 'admin/files/css/slick/slick.css' );
		wp_enqueue_style( 'slick-theme', ep_listinghub_URLPATH . 'admin/files/css/slick/slick-theme.css' );
		wp_enqueue_script( 'slick', ep_listinghub_URLPATH . 'admin/files/css/slick/slick.min.js', array( 'jquery' ), null, true );
	}
	while ( have_posts() ) : the_post();
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
	// View Count***
	$current_count=get_post_meta($listingid,'listing_views_count',true);
	$current_count=(int)$current_count+1;
	update_post_meta($listingid,'listing_views_count',$current_count);
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
					if (array_key_exists('title', $active_single_fields_saved)) {
						$saved_icon = listinghub_get_icon($single_page_icon_saved, 'title', 'single');
					?>
					<h2 class="title-detail "><i class="<?php echo esc_html($saved_icon); ?> "></i> <?php echo get_the_title($listingid); ?>
					<?php
					// Open/close status badge removed per client request.
					?>
					</h2>
					<?php
					}
					?>
					
					
					
				</div>
				<div class="col-lg-5 col-md-12 text-lg-end ">
					<div class="btn-feature text-right">
							<?php
							$user_ID = get_current_user_id();
							$favourites='no';
							if($user_ID>0){
								$my_favorite = get_post_meta($id,'_favorites',true);
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
							<!-- <a class=" btn btn-border  mr-2 mb-2" href="<?php echo get_permalink();?>?&listinghubpdfpost=<?php// echo esc_attr($listingid);?>" target="_blank"><i class="fas fa-download"></i> <?php// esc_html_e('PDF', 'listinghub'); ?></a> -->
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
			<div class="border-bottom mt-0 mb-0"></div>       
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
									if ( ! $listinghub_section_has_data( $field_key ) ) {
										continue;
									}
									switch ($field_key) {																
										case "description": 
									?>									
									<div id="section-description" class="border-bottom pb-15 mb-3 mt-3 toptitle"><i class="<?php echo esc_html($saved_icon); ?>"></i> <?php esc_html_e('Description','listinghub'); ?></div>
									<div class=" none  mb-4">
										<?php
										
											$content_post = get_post($listingid);
											$content = $content_post->post_content;
											$content = apply_filters('the_content', $content);
											$content = str_replace(']]>', ']]&gt;', $content);
											echo wpautop($content);
										?>												
									</div>
									<?php											
										break;
										case "video": 
										echo '<div id="section-video" class="mt-5">';
										include(ep_listinghub_template . '/listing/single-template/video.php');
										echo '</div>';
										break;
										case "tag": 
										echo '<div id="section-tag" class="mt-5">';
										include(ep_listinghub_template . '/listing/single-template/tags.php');
										echo '</div>';
										break;
										case "location": 
										echo '<div id="section-location" class="mt-5">';
										include(ep_listinghub_template . '/listing/single-template/locations.php');
										echo '</div>';
										break;
										case "image-gallery": 
										echo '<div id="section-image-gallery" class="mt-5">';
										include(ep_listinghub_template . '/listing/single-template/image-gallery.php');
										echo '</div>';
										break;
										case "faq": 
										echo '<div id="section-faq" class="mt-5">';
										include(ep_listinghub_template . '/listing/single-template/faqs.php');
										echo '</div>';
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
										<div id="section-<?php echo esc_attr($field_key); ?>" class="border-bottom pb-15 mt-5 toptitle">
											 <?php echo esc_html(ucwords(str_replace('_',' ',$field_key)));  ?>
										</div>
										<div><i class="<?php echo esc_html($saved_icon); ?>"></i>  <?php echo wp_kses($custom_meta_data,'post'); ?></div>											
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
						echo '<div id="section-review">';
						include(ep_listinghub_template.'/listing/single-template/reviews.php');
						echo '</div>';
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
									<img alt="image" class="rounded-logo" src="<?php echo esc_url($company_logo); ?>">
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
									<div class="row">
										
										<div class="col-12">	
											<span class="toptitle"><?php echo esc_html($company_name); ?></span>
										</div>
										
										<div class="col-12">	
											<?php
												if(!empty($company_locations)){
												?>
												<span class="card-location mt-2"><i class="fa-solid fa-location-dot mr-2"></i><?php echo esc_html($company_locations); ?></span>
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
													<?php echo esc_html( $total_listings ); ?> <?php esc_html_e( 'listings', 'listinghub' ); ?>
												</a>
											<?php else : ?>
												<a class="link-underline mt-1 " href="<?php echo esc_url( get_post_type_archive_link( $listinghub_directory_url ) . '?&listing-author=' . esc_attr( $user_info->ID ) ); ?>">
													<?php echo esc_html( $total_listings ); ?> <?php esc_html_e( 'listings', 'listinghub' ); ?>
												</a>
											<?php endif; ?>
										</div>
									</div>	
									
							</div>
							<?php
										if ( $agency_post_id && $agency_owner === 0 ) :
										?>
										<div class="col-12 mt-2">
											<button
												type="button"
												class="btn btn-border btn-big mt-1 cya-claim-agency-button"
												onclick="bia_open_claim_agency_popup(this,'<?php echo esc_attr( (string) $agency_post_id ); ?>','<?php echo esc_attr( $listingid ); ?>')"
											>
												<?php esc_html_e( 'Claim this agency', 'listinghub' ); ?>
											</button>
										</div>
										<?php endif; ?>
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
						<!-- <ul class="ul-disc">
							<?php //if($company_address!=''){  ?>
							<li><?php //echo esc_html($company_address); ?></li>
							<?php
							//}
							?>
							<?php //if($company_phone!=''){  ?>
							<li><?php //esc_html_e('Phone','listinghub'); ?> : <?php //echo esc_html($company_phone); ?></li>
							<?php
							//}
							?>
							<?php //if($company_email!=''){  ?>
							<li><?php //esc_html_e('Email','listinghub'); ?> : <?php //echo esc_html($company_email); ?></li>
							<?php
							//}
							?>
							
						</ul> -->
						<?php
							}
						?>
					</div>
					
				</div>
				<?php
					// Dynamic section navigation: only show heading if section has data
					$section_nav_labels = array(
						'description'   => esc_html__('Description', 'listinghub'),
						'video'         => esc_html__('Video', 'listinghub'),
						'tag'           => esc_html__('What’s Included', 'listinghub'),
						'location'      => esc_html__('Locations', 'listinghub'),
						'image-gallery' => esc_html__('Gallery', 'listinghub'),
						'faq'           => esc_html__('FAQs', 'listinghub'),
					);
					$section_nav_items = array();
					if ( is_array( $active_single_fields_saved ) ) {
						foreach ( $active_single_fields_saved as $field_key => $field_value ) {
							if ( in_array( $field_key, $data_for_top ) || in_array( $field_key, $data_not_for_all_section ) ) {
								continue;
							}
							if ( ! $listinghub_section_has_data( $field_key ) ) {
								continue;
							}
							$section_id = 'section-' . $field_key;
							if ( isset( $section_nav_labels[ $field_key ] ) ) {
								$section_nav_items[ $section_id ] = $section_nav_labels[ $field_key ];
							} else {
								$section_nav_items[ $section_id ] = ucwords( str_replace( '_', ' ', $field_key ) );
							}
						}
						if ( array_key_exists( 'review', $active_single_fields_saved ) && $listinghub_section_has_data( 'review' ) ) {
							$section_nav_items['section-review'] = esc_html__( 'Reviews', 'listinghub' );
						}
					}
					if ( ! empty( $section_nav_items ) ) :
				?>
					<div class="sidebar-border sidebar-jump">
						<div class="toptitle mb-3"><?php esc_html_e('Jump to section', 'listinghub'); ?></div>
						<ul class="list-unstyled">
							<?php foreach ( $section_nav_items as $section_id => $label ) : ?>
								<li class="mb-2"><a href="#<?php echo esc_attr( $section_id ); ?>" class="listing-section-link"><?php echo esc_html( $label ); ?></a></li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php
					endif;
				?>
				<?php
				// if(array_key_exists('simillar_listing',$active_single_fields_saved)){
				// 	include(ep_listinghub_template.'/listing/single-template/similar-listings.php');			
				// }
				?>
			</div>
		</div>
		<?php
		// Similar listings at end of page – archive style (image slider + box)
		if ( array_key_exists( 'simillar_listing', $active_single_fields_saved ) ) {
			$listinghub_similar_bottom = get_posts( array(
				'numberposts' => 4,
				'post_type'   => $listinghub_directory_url,
				'post__not_in' => array( (int) $listingid ),
				'post_status'  => 'publish',
				'orderby'      => 'rand',
			) );
			$defaul_feature_img = $main_class->listinghub_listing_default_image();
			$active_archive_fields = listinghub_get_archive_fields_all();
			$active_archive_icon_saved = get_option( 'listinghub_archive_icon_saved' );
			if ( ! empty( $listinghub_similar_bottom ) ) :
		?>
		<div class="row mt-5 mb-5 pt-4 border-top listing-similar-bottom">
			<div class="col-12">
				<div class="toptitle mb-4"><?php esc_html_e( 'Similar listings', 'listinghub' ); ?></div>
				<div class="row justify-content-center">
					<?php
					$i = 0;
					foreach ( $listinghub_similar_bottom as $similar_post ) {
						$id = $similar_post->ID;
						$post = $similar_post;
						setup_postdata( $post );
						$post_author_id = (int) $similar_post->post_author;
						$feature_img = '';
						if ( has_post_thumbnail( $id ) ) {
							$feature_image = wp_get_attachment_image_src( get_post_thumbnail_id( $id ), 'large' );
							if ( ! empty( $feature_image[0] ) ) {
								$feature_img = $feature_image[0];
							}
						}
						if ( empty( $feature_img ) ) {
							$feature_img = $defaul_feature_img;
						}
						$dir_data = array(
							'title' => get_the_title( $id ),
							'dlink' => get_permalink( $id ),
							'address' => get_post_meta( $id, 'address', true ),
							'image' => $feature_img,
							'locations' => '',
							'lat' => get_post_meta( $id, 'latitude', true ) ?: 0,
							'lng' => get_post_meta( $id, 'longitude', true ) ?: 0,
							'marker_icon' => '',
						);
						include( ep_listinghub_template . 'listing/single-template/archive-grid-block.php' );
						$i++;
					}
					wp_reset_postdata();
					?>
				</div>
			</div>
		</div>
		<?php
			endif;
		}
		?>
		
	
	</div>
</div>
<?php
	endwhile;
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
?>
<?php
	get_footer();
?>
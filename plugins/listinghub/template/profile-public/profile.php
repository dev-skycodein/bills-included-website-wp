<?php
	wp_enqueue_script("jquery");	
	wp_enqueue_style('bootstrap', ep_listinghub_URLPATH .'admin/files/css/iv-bootstrap.css');
	wp_enqueue_style('listinghub-profile-public', ep_listinghub_URLPATH . 'admin/files/css/profile-public.css');
	wp_enqueue_style('all-awesome', ep_listinghub_URLPATH . 'admin/files/css/all.min.css');
	wp_enqueue_style('colorbox', ep_listinghub_URLPATH . 'admin/files/css/colorbox.css');
	wp_enqueue_script('colorbox', ep_listinghub_URLPATH . 'admin/files/js/jquery.colorbox-min.js');
	$listinghub_directory_url=get_option('ep_listinghub_url');
	if($listinghub_directory_url==""){$listinghub_directory_url='listing';}
	$display_name='';
	$email='';
	$current_page_permalink='';
	$user_id=1;
	$main_class = new eplugins_listinghub;
	if(isset($_REQUEST['id'])){
		$author_name= sanitize_text_field($_REQUEST['id']);
		$user = get_user_by( 'ID', $author_name );
		if(isset($user->ID)){
			$user_id=$user->ID;
			$display_name=$user->display_name;
			$email=$user->user_email;
		}
	}else{
		global $current_user;
		$user_id=$current_user->ID;
		$display_name=$current_user->display_name;
		$email=$current_user->user_email;
		$author_name= $current_user->ID;
		if($user_id==0){
			$user_id=1;
		}
		$user = get_user_by( 'ID', $user_id );
	}
	$iv_profile_pic_url=get_user_meta($user_id, 'listinghub_profile_pic_thum',true);
	$topbanner=get_user_meta($user_id,'topbanner', true);
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
	$active_single_fields_saved=get_option('listinghub_single_fields_saved' );	
	if(empty($active_single_fields_saved)){$active_single_fields_saved=listinghub_get_listing_fields_all_single();}	
?>
<div class="bootstrap-wrapper " id="">
	<input type="hidden" id="profileID" value="<?php echo esc_attr($user_id); ?>">
	<div class="container" id="iv_prifile_div">
		<section class="section ">   		
			<div class=" banner-hero banner-image-single mt-1" style="background:url(<?php echo esc_url($default_image_banner); ?>) no-repeat; background-size:cover;">
			</div>
			<div class="row mt-2">
				<div class="col-lg-7 col-md-12 ">					
					<h2 class="title-detail mr-2"><?php echo (get_user_meta($user_id,'full_name',true)!=''? get_user_meta($user_id,'full_name',true) : $user->display_name ); ?>
						<?php					
							
							$all_locations= get_user_meta($user_id, 'all_locations', true);
							if($all_locations!=''){
							
							?>
							<span class="card-location ">
								<i class="fa-solid fa-location-dot mr-1"></i><?php echo esc_html($all_locations); ?>
							</span>
							<?php
							}
						?>
					</h2>
					<div class="mt-0  font-tag_line "><?php						
					?><?php echo get_user_meta($user_id,'tagline',true); ?></span> 				
				</div>
			</div>
			<div class="col-lg-5 col-md-12 text-lg-end ">
				<div class="btn-feature text-right">						
					<button class="btn btn-big  mb-2" onclick="listinghub_author_email_popup('<?php echo esc_attr($user_id);?>')">
					<?php esc_html_e('Contact Us', 'listinghub'); ?></button>
					<?php
						$current_ID = get_current_user_id();
						$favourites='no';
						if($current_ID>0){
							$my_favorite = get_post_meta($user_id,'listinghub_authorbookmark',true);											
							$all_users = explode(",", $my_favorite);
							if (in_array($current_ID, $all_users)) {
								$favourites='yes';
							}
						}
						$added_to_Boobmark=esc_html__('Saved', 'listinghub');
						$add_to_Boobmark=esc_html__('Save', 'listinghub');
					?>
					<button id="authorbookmark" class="btn <?php echo ($favourites=='yes'?'btn btn-big ':'btn btn-border' ); ?> ml-1  mb-2"  title="<?php echo ($favourites=='yes'? $added_to_Boobmark: $add_to_Boobmark ); ?>" ><i class="far fa-heart"></i></button>
				</div>		
			</div>
		</div>
		<div class="border-bottom pt-10 pb-10"></div>  
	</section>
	<div class="row mt-5">
		<div class="col-lg-8 col-md-12 col-sm-12 col-12 profile-col1">
			<div class="listing-overview">					
				<div class="border-bottom pb-15 mb-3 toptitle"><?php esc_html_e('About', 'listinghub'); ?>
				</div>
				<div class=" row col-md-12 col-12 text-description  mb-4">
					<?php
						$content= get_user_meta($user_id,'description',true);								
						$content = apply_filters('the_content', $content);
						$content = str_replace(']]>', ']]&gt;', $content);
						echo wpautop($content);
					?>												
				</div>										
				<div class="border-bottom pb-15 mb-3 toptitle"><?php esc_html_e('Industry', 'listinghub'); ?>
				</div>			
				<div class=" row col-md-12 text-description  mb-4">							
					<?php echo get_user_meta($user_id,'company_type',true);?>										
				</div>
				<?php
					$default_fields = array();
					$field_set=get_option('listinghub_profile_fields' );
					$all_empty='no';
					if($field_set!=""){
						$default_fields=get_option('listinghub_profile_fields' );
						}else{
						$default_fields['company_since']='Estd Since';
						$default_fields['team_size']='Team Size';	
						$all_empty='yes';
					}
					$field_type_roles=  	get_option( 'listinghub_field_type_roles' );	
					
					$myaccount_fields_array=  get_option( 'listinghub_myaccount_fields' );
					$not_show= array('description','country','state','zipcode','city','address','full_name','tagline');
					$user = new WP_User( $user_id);
					$i=1;
					foreach ( $default_fields as $field_key => $field_value ) { 
						$role_access='no';
						
						if(isset($myaccount_fields_array[$field_key])){ 
							if($myaccount_fields_array[$field_key]=='yes'){
								
								if(in_array('all',$field_type_roles[$field_key] )){ 
									$role_access='yes';
								}
								if(in_array('administrator',$field_type_roles[$field_key]  )){
									$role_access='yes'; 
								}
								
								if(in_array('employer',$field_type_roles[$field_key] )){
									$role_access='yes'; 
								}
								if ( !empty( $user->roles ) && is_array( $user->roles ) ) {
									foreach ( $user->roles as $role ){
										if(in_array($role,$field_type_roles )){
											$role_access='yes'; 
										}
										
									}
								}	
							}
						}
						
						if($role_access=='yes' OR $all_empty=='yes' ){
							if(!in_array($field_key,$not_show)){
								if(get_user_meta($user_id,$field_key,true)!=''){?> 
								<div class="border-bottom pb-15 mb-3 toptitle"><?php echo esc_html($field_value); ?>
								</div>			
								<div class=" row col-md-12 text-description  mb-4">							
									<?php echo esc_html( get_user_meta($user_id,$field_key,true)); ?>										
								</div>	
								<?php													
								}										
							}
						}
					}	
				?>
			</div>			
			<?php
				include(ep_listinghub_template . '/profile-public/footer_share.php');  
			?>
		</div>
		<div class="col-lg-4 col-md-12 col-sm-12 col-12 pl-40 pl-lg-15 mt-lg-30 profile-col2">
			<div class="sidebar-border">
				<div class="sidebar-heading pb-15 ">
					<div class="avatar-sidebar">
						<?php	
							$company_name= get_user_meta($user_id,'full_name', true);
							$company_address= get_user_meta($user_id,'address', true);
							$company_web=get_user_meta($user_id,'website', true);
							$company_phone=get_user_meta($user_id,'phone', true);
							$company_logo=get_user_meta($user_id, 'listinghub_profile_pic_thum',true);
							if(array_key_exists('company-logo',$active_single_fields_saved)){ 
								if(trim($company_logo)!=''){
								?>
								<figure><img alt="image" src="<?php echo esc_url($company_logo); ?>"></figure>
								<?php
								}else{?>
								<figure class="blank-rounded-logo"></figure>
								<?php
								}
							}
						?>
						<div class="sidebar-info"><span class="toptitle"><?php echo (get_user_meta($user_id,'full_name',true)!=''? get_user_meta($user->ID,'full_name',true) : $user->display_name ); ?></span>
							<?php
							 $all_locations= str_replace(',',' ',get_user_meta($user_id, 'all_locations', true));
								if(!empty( $all_locations)){
								?>
								<span class="card-location mt-2"><i class="fa-solid fa-location-dot mr-2"></i><?php echo esc_html($all_locations); ?>
								</span>
								<?php
								}
								$total_listings= $main_class->listinghub_total_listing_count($user_id, $allusers='no' );
							?>
							<a class="link-underline mt-1 " href="<?php echo get_post_type_archive_link( $listinghub_directory_url ).'?employer='.esc_attr($user_id); ?>">
								<?php echo esc_html($total_listings);?> <?php esc_html_e('Open listings', 'listinghub'); ?>
							</a></div>
					</div>
				</div>
				<div class="sidebar-list-listing">
					<div class="box-map mt-4">				  
						<iframe width="100%" height="325" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.com/maps?q=<?php echo esc_attr($company_address); ?>&amp;ie=UTF8&amp;&amp;output=embed"></iframe>
					</div>
					<ul class="ul-disc ml-3">
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
						<?php if($email!=''){  ?>
							<li><?php esc_html_e('Email','listinghub'); ?> : <?php echo esc_html($email); ?></li>
							<?php
							}
						?>
						<?php if($company_web!=''){  ?>
							<li><a href="<?php echo esc_url($company_web); ?>"  target="_blank" ><?php echo esc_url($company_web); ?></a></li>
							<?php
							}
						?>
					</ul>
				</div>
			</div>
			<?php
				include(ep_listinghub_template.'/profile-public/author-listings.php');			
			?>
		</div>
	</div>		
</div>
</div>
<?php
	wp_enqueue_script('popper', ep_listinghub_URLPATH . 'admin/files/js/popper.min.js');
	wp_enqueue_script('bootstrap', ep_listinghub_URLPATH . 'admin/files/js/bootstrap.min-4.js');
	
	$currencyCode = get_option('listinghub_api_currency');
	wp_enqueue_script('epmyaccount-script-27', ep_listinghub_URLPATH . 'admin/files/js/public-profile.js');
	wp_localize_script('epmyaccount-script-27', 'listinghub1', array(
	'ajaxurl' 			=> admin_url( 'admin-ajax.php' ),
	'loading_image'		=> '<img src="'.ep_listinghub_URLPATH.'admin/files/images/loader.gif">',
	'wp_iv_directories_URLPATH'		=> ep_listinghub_URLPATH,
	'current_user_id'	=>get_current_user_id(),
	'dirwpnonce'=> wp_create_nonce("myaccount"),
	"Please_login"=>  esc_html__('Please Login','listinghub'), 
	'Add_to_Boobmark'=>esc_html__('Add to Boobmark', 'listinghub' ),
	'Added_to_Boobmark'=>esc_html__('Added to Boobmark', 'listinghub' ),	
	) );
	wp_enqueue_script('listinghub_message', ep_listinghub_URLPATH . 'admin/files/js/user-message.js');
	wp_localize_script('listinghub_message', 'listinghub_data_message', array(
	'ajaxurl' 			=> admin_url( 'admin-ajax.php' ),
	'loading_image'		=> '<img src="'.ep_listinghub_URLPATH.'admin/files/images/loader.gif">',		
	'Please_put_your_message'=>esc_html__('Please put your name,email & message', 'listinghub' ),
	'contact'=> wp_create_nonce("contact"),
	'listing'=> wp_create_nonce("listing"),
	) );
	wp_enqueue_script('listinghub_single-listing', ep_listinghub_URLPATH . 'admin/files/js/single-listing.js');
	wp_localize_script('listinghub_single-listing', 'listinghub_data', array(
	'ajaxurl' 			=> admin_url( 'admin-ajax.php' ),
	'loading_image'		=> '<img src="'.ep_listinghub_URLPATH.'admin/files/images/loader.gif">',
	'current_user_id'	=>get_current_user_id(),
	'Please_login'=>esc_html__('Please login', 'listinghub' ),
	'Add_to_Favorites'=>esc_html__('Add to Favorites', 'listinghub' ),
	'Added_to_Favorites'=>esc_html__('Added to Favorites', 'listinghub' ),
	'Please_put_your_message'=>esc_html__('Please put your name,email & Cover letter', 'listinghub' ),
	'contact'=> wp_create_nonce("contact"),
	'listing'=> wp_create_nonce("listing"),
	'cv'=> wp_create_nonce("Doc/CV/PDF"),
	'ep_listinghub_URLPATH'=>ep_listinghub_URLPATH,
	) );	
	wp_reset_query();
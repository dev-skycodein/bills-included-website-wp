






 
    
    <?php
	wp_enqueue_script("jquery");
	wp_enqueue_style('jquery-ui', ep_listinghub_URLPATH . 'admin/files/css/jquery-ui.css');
	wp_enqueue_script('jquery-ui-core');
	wp_enqueue_script('jquery-ui-datepicker');
	wp_enqueue_style('bootstrap', ep_listinghub_URLPATH . 'admin/files/css/iv-bootstrap.css');
	wp_enqueue_script('bootstrap.min', ep_listinghub_URLPATH . 'admin/files/js/bootstrap.min-4.js');
	wp_enqueue_script('popper', 		ep_listinghub_URLPATH . 'admin/files/js/popper.min.js');
	wp_enqueue_style('colorbox', ep_listinghub_URLPATH . 'admin/files/css/colorbox.css');
	wp_enqueue_script('colorbox', ep_listinghub_URLPATH . 'admin/files/js/jquery.colorbox-min.js');
	wp_enqueue_style('fontawesome', ep_listinghub_URLPATH . 'admin/files/css/all.min.css');
	wp_enqueue_style('listinghub_my-account', ep_listinghub_URLPATH . 'admin/files/css/my-account.css');
	wp_enqueue_style('listinghub_my-account-2', ep_listinghub_URLPATH . 'admin/files/css/my-account-new.css');
	wp_enqueue_style('listinghub_my-menu', ep_listinghub_URLPATH . 'admin/files/css/cssmenu.css');
	wp_enqueue_script('listinghub_script-user-directory', ep_listinghub_URLPATH . 'admin/files/js/user-directory.js');
	wp_enqueue_style('jquery.dataTables', ep_listinghub_URLPATH . 'admin/files/css/jquery.dataTables.css');
	wp_enqueue_script('jquery.dataTables', ep_listinghub_URLPATH . 'admin/files/js/jquery.dataTables.js');		
	wp_enqueue_script('datetimepicker', ep_listinghub_URLPATH . 'admin/files/js/jquery.datetimepicker.full.js');
	wp_enqueue_style('datetimepicker', ep_listinghub_URLPATH . 'admin/files/css/jquery.datetimepicker.css');
	// Map openstreet
	wp_enqueue_script('leaflet', ep_listinghub_URLPATH . 'admin/files/js/leaflet.js');
	wp_enqueue_style('leaflet', ep_listinghub_URLPATH . 'admin/files/css/leaflet.css');
	wp_enqueue_script('leaflet-geocoder-locationiq', ep_listinghub_URLPATH . 'admin/files/js/leaflet-geocoder-locationiq.min.js');		
	wp_enqueue_style('leaflet-geocoder-locationiq', ep_listinghub_URLPATH . 'admin/files/css/leaflet-geocoder-locationiq.min.css');
	wp_enqueue_media();
	$main_class = new eplugins_listinghub;
	$listinghub_directory_url=get_option('ep_listinghub_url');
	if($listinghub_directory_url==""){$listinghub_directory_url='listing';}
	global $current_user;
	global $wpdb;
	$user = new WP_User( $current_user->ID );
	if ( !empty( $user->roles ) && is_array( $user->roles ) ) {
		foreach ( $user->roles as $role ){
			$crole= ucfirst($role);
			break;
		}
	}
	if(strtoupper($crole)!=strtoupper('administrator')){
		include(ep_listinghub_template.'/private-profile/check_status.php');
	}
	$currencies = array();
	$currencies['AUD'] ='$';$currencies['CAD'] ='$';
	$currencies['EUR'] ='€';$currencies['GBP'] ='£';
	$currencies['JPY'] ='¥';$currencies['USD'] ='$';
	$currencies['NZD'] ='$';$currencies['CHF'] ='Fr';
	$currencies['HKD'] ='$';$currencies['SGD'] ='$';
	$currencies['SEK'] ='kr';$currencies['DKK'] ='kr';
	$currencies['PLN'] ='zł';$currencies['NOK'] ='kr';
	$currencies['HUF'] ='Ft';$currencies['CZK'] ='Kč';
	$currencies['ILS'] ='₪';$currencies['MXN'] ='$';
	$currencies['BRL'] ='R$';$currencies['PHP'] ='₱';
	$currencies['MYR'] ='RM';$currencies['AUD'] ='$';
	$currencies['TWD'] ='NT$';$currencies['THB'] ='฿';
	$currencies['TRY'] ='TRY';	$currencies['CNY'] ='¥';
	$currency= get_option('listinghub_api_currency');
	$currency_symbol=(isset($currencies[$currency]) ? $currencies[$currency] :$currency );
	$user_id= $current_user->ID;
?>
<?php
	
	$user_id=$current_user->ID;
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
<div class="bootstrap-wrapper " id="profile-account2">
	<input type="hidden" id="profileID" value="<?php echo esc_attr($user_id); ?>">
	<div class="container">
		<section class="section ">   		
			<div class=" banner-hero banner-image-single mt-1" id="topbanner_heroimg" style="background:url(<?php echo esc_url($default_image_banner); ?>) no-repeat; background-size:cover;">
			</div>	
			<div class="row mt-2 my-hide">
				<div class="col-lg-7 col-md-12 ">					
					<h2 class="title-detail "><?php echo get_user_meta($user_id,'full_name',true); ?>
						<?php					
							
							$all_locations= get_user_meta($user_id, 'all_locations', true);
							if($all_locations!=''){							
							?>
							<span class="card-location ">
								<i class="fa-solid fa-location-dot mr-1 ml-2"></i><?php echo esc_html($all_locations); ?>
							</span>
							<?php
							}
						?>
					</h2>
					<?php if(get_user_meta($user_id,'tagline',true)!=''){ ?>
					<div class="mt-1 mb-1  font-tag_line "><?php						
					?><?php echo get_user_meta($user_id,'tagline',true); ?></span> 				
					</div>
					<?php
						}
					?>
					
			</div>
			<div class="col-lg-5 col-md-12 text-lg-end  col-12">
				<div class=" text-right">	
					<?php 					
						
						$profile_page=get_option('epjblistinghubr_public_profile_page');
						
						$page_link= get_permalink( $profile_page).'?&id='.$current_user->ID; 
					?>
					<a class="btn btn-big  mb-2" href="<?php echo esc_url($page_link); ?>" target="_blank"><?php  esc_html_e('View Profile','listinghub');?> </a>	
					
					
					<button class="btn btn-border ml-2 mb-2" id="compose_adminmenu" ><i class="fa-solid fa-bars"></i></button>
					
				</div>		
			</div>
			
			
		</div>
		
		
	</section>
	
	<div class="row mt-4" >
		<div class="col-lg-3 col-md-12 col-sm-12 col-12 pl-40 pl-lg-15 mt-lg-30">
			<div class="sidebar-myaccount" id="listinghub-left-menu">
				<div class="sidebar-heading">
					<div class="avatar-sidebar mt-3">
				 		<?php	
				// 			$company_name= get_user_meta($user_id,'full_name', true);
				// 			$company_address= get_user_meta($user_id,'address', true);
				// 			$company_web=get_user_meta($user_id,'website', true);
				// 			$company_phone=get_user_meta($user_id,'phone', true);
				// 			$company_logo=get_user_meta($user_id, 'listinghub_profile_pic_thum',true);
							//if(array_key_exists('company-logo',$active_single_fields_saved)){ 
								//if(trim($company_logo)!=''){
								?>
								<!--<figure><img alt="image" class="ml-2 height100p"  src="<?php echo esc_url($company_logo); ?>"></figure>-->
								<?php
								//}else{
								?>
								<!--<figure class="blank-rounded-logo ml-2"></figure>-->
								<?php
				// 				}
				// 			}
						?>
						<h2 class="my-account-welcome">Your Account 🏠</h2>
						<div class="sidebar-info"><span class="toptitle-sub"><?php echo esc_html($company_name); ?></span>
							<?php
								$all_locations= str_replace(',',' ',get_user_meta($user_id, 'all_locations', true));
								if(!empty( $all_locations)){
								?>
								<span class="card-location mt-2"><i class="fa-solid fa-location-dot mr-2"></i><?php echo esc_html($all_locations); ?>
								</span>
								<?php
								}
								$total_listings= $main_class->listinghub_total_listing_count($user_id, $allusers='no' );
								if($total_listings>0){
								?>
								<a class="link-underline mt-1 " href="<?php echo get_post_type_archive_link( $listinghub_directory_url ).'?employer='.esc_attr($user_id); ?>">
									<?php echo esc_html($total_listings);?> <?php esc_html_e('Open listings', 'listinghub'); ?>
								</a>
								<?php
								}
							?>
						</div>
					</div>
				</div>
				
					
				<div class="sidebar-list-listing"  id="listinghub-left-menu">
					
					<!-- SIDEBAR MENU -->
					<div class="profile-usermenu" >
						<?php
							$active='setting';
							if(isset($_GET['profile']) AND $_GET['profile']=='setting' ){
								$active='setting';
							}
							if(isset($_GET['profile']) AND $_GET['profile']=='level' ){
								$active='level';
							}
							if(isset($_GET['profile']) AND $_GET['profile']=='all-post' ){
								$active='all-post';
							}
							if(isset($_GET['profile']) AND $_GET['profile']=='new-post' ){
								$active='new-post';
							}							
							if(isset($_GET['profile']) AND $_GET['profile']=='dashboard' ){
								$active='dashboard';
							}							
							
							if(isset($_GET['profile']) AND $_GET['profile']=='notification' ){
								$active='notification';
							}
							if(isset($_GET['profile']) AND $_GET['profile']=='post-edit' ){
								$active='all-post';
							}							
							
							if(isset($_GET['profile']) AND $_GET['profile']=='author_bookmarks' ){
								$active='author_bookmarks';
							}
							if(isset($_GET['profile']) AND $_GET['profile']=='messageboard' ){
								$active='messageboard';
							}							
							if(isset($_GET['profile']) AND $_GET['profile']=='listing_bookmark' ){
								$active='listing_bookmark';
							}
							$post_type=  'listing';
						?>
						<div id='cssmenu'>
							<?php
								
								include(  ep_listinghub_template. 'private-profile/all-menu.php');
									
							?>
						</div>
					</div>
					<!-- END MENU -->
				</div>
			</div>
		</div>
		<div class="col-lg-9 col-md-12 col-sm-12 col-12">
			<div class="listing-overview">	
				<?php
					if(isset($_GET['profile']) AND $_GET['profile']=='all-post' ){						
						if( get_option('epjblistinghub_menuallpost' ) ) {
							$account_menu_check= get_option('epjblistinghub_menuallpost');
						}
						if($account_menu_check!='yes'){
							include(  ep_listinghub_template. 'private-profile/profile-all-post-1.php');
						}else{
							include(  ep_listinghub_template. 'private-profile/profile-setting-1.php');
						}
						
					}elseif(isset($_GET['profile']) AND $_GET['profile']=='level' ){						
						if( get_option('epjblistinghub_mylevel' ) ) {
							$account_menu_check= get_option('epjblistinghub_mylevel');
						}
						if($account_menu_check!='yes'){
							include(  ep_listinghub_template. 'private-profile/profile-level-1.php');
						}else{
							include(  ep_listinghub_template. 'private-profile/profile-setting-1.php');
						}
					}elseif(isset($_GET['profile']) AND $_GET['profile']=='post-edit' ){						
						if( get_option('epjblistinghub_menuallpost' ) ) {
							$account_menu_check= get_option('epjblistinghub_menuallpost');
						}
						if($account_menu_check!='yes'){
							include(  ep_listinghub_template. 'private-profile/profile-edit-post-1.php');
						}else{
							include(  ep_listinghub_template. 'private-profile/profile-setting-1.php');
						}
					}
					
					elseif(isset($_GET['profile']) AND $_GET['profile']=='setting' ){
						include(  ep_listinghub_template. 'private-profile/profile-setting-1.php');
					}	
					elseif(isset($_GET['profile']) AND $_GET['profile']=='author_bookmarks' ){
						if( get_option('epjblistinghub_author_bookmarks' ) ) {
							$account_menu_check= get_option('epjblistinghub_author_bookmarks');
						}
						if($account_menu_check!='yes'){
							include(  ep_listinghub_template. 'private-profile/author-bookmarks.php');
						}else{
							include(  ep_listinghub_template. 'private-profile/profile-setting-1.php');
						}
					}										
					elseif(isset($_GET['profile']) AND $_GET['profile']=='listing_bookmark' ){						
						if( get_option('epjblistinghub_listing_bookmarks' ) ) {
							$account_menu_check= get_option('epjblistinghub_listing_bookmarks');
						}
						if($account_menu_check!='yes'){
							include(  ep_listinghub_template. 'private-profile/listing_bookmark-file.php');
						}else{
							include(  ep_listinghub_template. 'private-profile/profile-setting-1.php');
						}
						
					}elseif(isset($_GET['profile']) AND $_GET['profile']=='messageboard' ){						
						if( get_option('epjblistinghub_messageboard' ) ) {
							$account_menu_check= get_option('epjblistinghub_messageboard');
						}
						if($account_menu_check!='yes'){
							include(  ep_listinghub_template. 'private-profile/messageboard.php');
						}else{
							include(  ep_listinghub_template. 'private-profile/profile-setting-1.php');
						}
						
					}elseif(isset($_GET['profile']) AND $_GET['profile']=='notification' ){						
						if( get_option('epjblistinghub_notification' ) ) {
							$account_menu_check= get_option('epjblistinghub_notification');
						}
						if($account_menu_check!='yes'){
							include(  ep_listinghub_template. 'private-profile/listing-notifications.php');
						}else{
							include(  ep_listinghub_template. 'private-profile/profile-setting-1.php');
						}					
					}
					elseif(isset($_GET['profile']) AND $_GET['profile']=='dashboard' ){					 
						include(  ep_listinghub_template. 'private-profile/dashboard.php');
						
					}
					else{					 
						include(  ep_listinghub_template. 'private-profile/profile-setting-1.php');
						
					}
				?>
			</div>			
		</div>
	</div>		
</div>
</div>

<?php
	$currencyCode = get_option('listinghub_api_currency');
	wp_enqueue_script('listinghub_myaccount', ep_listinghub_URLPATH . 'admin/files/js/my-account.js');
	wp_localize_script('listinghub_myaccount', 'listinghub1', array(
	'ajaxurl' 			=> admin_url( 'admin-ajax.php' ),
	'loading_image'		=> '<img src="'.ep_listinghub_URLPATH.'admin/files/images/loader.gif">',
	'wp_iv_directories_URLPATH'		=> ep_listinghub_URLPATH,
	'current_user_id'	=>get_current_user_id(),
	'SetImage'		=>esc_html__('Set Image','listinghub'),
	'GalleryImages'=>esc_html__('Gallery Images','listinghub'),
	'cancel-message' => esc_html__('Are you sure to cancel this Membership','listinghub'),
	'currencyCode'=>  $currencyCode,
	'dirwpnonce'=> wp_create_nonce("myaccount"),
	'dirwpnonce2'=> wp_create_nonce("signup2"),
	'signup'=> wp_create_nonce("signup"),
	'contact'=> wp_create_nonce("contact"),
	'permalink'=> get_permalink(),
	"sProcessing"=>  esc_html__('Processing','listinghub'),
	"sSearch"=>   esc_html__('Search','listinghub'),
	"lengthMenu"=>   esc_html__('Display _MENU_ ','listinghub'),
	"zeroRecords"=>  esc_html__('Nothing found - sorry','listinghub'),
	"info"=>  esc_html__('Showing page _PAGE_ of _PAGES_','listinghub'),
	"infoEmpty"=>   esc_html__('No records available','listinghub'),
	"infoFiltered"=>  esc_html__('(filtered from _MAX_ total records)','listinghub'),
	"sFirst"=> esc_html__('First','listinghub'),
	"sLast"=>  esc_html__('Last','listinghub'),
	"sNext"=>     esc_html__('Next','listinghub'),
	"sPrevious"=>  esc_html__('Previous','listinghub'),
	"makeShortListed"=>  esc_html__('Make Shortlisted','listinghub'), 
	"ShortListed"=>  esc_html__('Undo Shortlisted','listinghub'), 
	"Rejected"=>  esc_html__('Rejected','listinghub'), 
	"MakeReject"=>  esc_html__('Make Reject','listinghub'), 		
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
	wp_enqueue_script('listinghub_message', ep_listinghub_URLPATH . 'admin/files/js/user-message.js');
	wp_localize_script('listinghub_message', 'listinghub_data_message', array(
	'ajaxurl' 			=> admin_url( 'admin-ajax.php' ),
	'loading_image'		=> '<img src="'.ep_listinghub_URLPATH.'admin/files/images/loader.gif">',		
	'Please_put_your_message'=>esc_html__('Please put your name,email & message', 'listinghub' ),
	'contact'=> wp_create_nonce("contact"),
	'listing'=> wp_create_nonce("listing"),
	) );
	wp_reset_query();
	?>	
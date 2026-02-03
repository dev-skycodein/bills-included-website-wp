

<?php
	wp_enqueue_script("jquery");	
	wp_enqueue_script('jquery-ui-core');
	wp_enqueue_script('jquery-ui-datepicker');
	wp_enqueue_script('jquery-ui-autocomplete');
	wp_enqueue_script('popper', ep_listinghub_URLPATH . 'admin/files/js/popper.min.js');
	wp_enqueue_script('bootstrap', ep_listinghub_URLPATH . 'admin/files/js/bootstrap.min-4.js'); 
	wp_enqueue_style('bootstrap', ep_listinghub_URLPATH . 'admin/files/css/iv-bootstrap.css');
	wp_enqueue_style('listinghub_listing_style_alphabet_sort', ep_listinghub_URLPATH . 'admin/files/css/archive-listing.css');	
	wp_enqueue_style('colorbox', ep_listinghub_URLPATH . 'admin/files/css/colorbox.css');
	wp_enqueue_script('colorbox', ep_listinghub_URLPATH . 'admin/files/js/jquery.colorbox-min.js');
	wp_enqueue_style('jquery-ui', ep_listinghub_URLPATH . 'admin/files/css/jquery-ui.css');
	wp_enqueue_style('font-awesome', ep_listinghub_URLPATH . 'admin/files/css/all.min.css');	
	wp_enqueue_style('flaticon', ep_listinghub_URLPATH . 'admin/files/fonts/flaticon/flaticon.css');	 
	wp_enqueue_style('listinghub_post-paging', ep_listinghub_URLPATH . 'admin/files/css/post-paging.css');
	$main_class = new eplugins_listinghub;
	global $post,$wpdb,$tag,$listinghub_query,$listinghub_filter_badge;
	$defaul_feature_img= $this->listinghub_listing_default_image();
	$listinghub_directory_url=get_option('ep_listinghub_url');
	if($listinghub_directory_url==""){$listinghub_directory_url='listing';}
	$current_post_type=$listinghub_directory_url;
	$dir_style5_perpage=get_option('listinghub_dir_perpage');
	if($dir_style5_perpage==""){$dir_style5_perpage=20;}	
	$dirs_data =array();
	$tag_arr= array();
	$search_arg= array();
	$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
	$args = array(
	'post_type' => $listinghub_directory_url, // enter your custom post type
	'paged' => $paged,
	'post_status' => 'publish',
	'posts_per_page'=> $dir_style5_perpage,  // overrides posts per page in theme settings
	);
	$search_arg= listinghub_get_search_args($listinghub_directory_url);
	$args= array_merge( $args, $search_arg );
	$lat='';$long='';$keyword_post='';$address='';$postcats ='';$selected='';
	// Add new shortcode only category
	if(isset($atts['category']) and $atts['category']!="" ){
		$postcats = $atts['category'];
		$args[$listinghub_directory_url.'-category']=$postcats;		
	}
	if(isset($atts['locations']) and $atts['locations']!="" ){
		$postcats = $atts['locations'];
		$args[$listinghub_directory_url.'-locations']=$postcats;		
	}
	if(isset($atts['tag']) and $atts['tag']!="" ){
		$postcats = $atts['tag'];
		$args[$listinghub_directory_url.'-tag']=$postcats;
	}
	if(get_query_var($listinghub_directory_url.'-category')!=''){
		$postcats = get_query_var($listinghub_directory_url.'-category');
		$args[$listinghub_directory_url.'-category']=$postcats;
		$selected=$postcats;
		$search_show=1;
	}
	if(get_query_var($listinghub_directory_url.'-tag')!=''){
		$postcats = get_query_var($listinghub_directory_url.'-tag');
		$args[$listinghub_directory_url.'-tag']=$postcats;
		$search_show=1;
	}
	if(get_query_var($listinghub_directory_url.'-locations')!=''){
		$postcats = get_query_var($listinghub_directory_url.'-locations');
		$args[$listinghub_directory_url.'-locations']=$postcats;
		$search_show=1;
	}
	if(get_query_var('listing-author')!=''){
		$author = get_query_var('listing-author');
		$args['author']=(int) sanitize_text_field($author);		
	}
	if( isset($_REQUEST['listing-author'])){ 
		$author = $_REQUEST['listing-author'];
		$args['author']= (int)sanitize_text_field($author);		
	}
	// For featrue listing***********
	$feature_listing_all =array();
	$feature_listing_all =$args;
	if(isset($search_arg['lng']) and $search_arg['lng']!=''){ 
		$listinghub_query = new WP_GeoQuery( $args );
		}else{
		$listinghub_query = new WP_Query( $args );
	}
	$active_archive_fields=listinghub_get_archive_fields_all();	
	$active_archive_icon_saved=get_option('listinghub_archive_icon_saved' );
	
	$search_form_setting='popup';
	if(isset($active_archive_icon_saved['top_search_form'])){	
		$search_form_setting=$active_archive_icon_saved['top_search_form'];
	}
	if(isset($atts['search-form']) and $atts['search-form']!="" ){
		$search_form_setting=$atts['search-form'];
	}
?>
<!-- wrap everything for our isolated bootstrap -->
<div class="bootstrap-wrapper">
	<!-- archieve page own design font and others -->
	<section class=" py-5">
		<div class="container-fluid "  >
			<!-- Search Form -->
			<div class="display-none"  tabindex="-1" >	.
				<div class="" id="listingfilter">
					<?php
						if(isset($active_archive_fields['top_search_form'])){
							if($search_form_setting=='popup'){
							echo do_shortcode('[listinghub_search_popup action="same_page"]');
							}
						}
					?>
				</div>
			</div>
			<!-- end of search form -->
			<div class="row" id="full_grid"> 
				<img class="col-md-12" src="<?php echo ep_listinghub_URLPATH."assets/images/astra.png"; ?>">
					<div class="col-md-12 col-lg-12 col-xl-12 col-sm-12 " >	
						<?php
						if(isset($active_archive_fields['top_search_form'])){										
							if($search_form_setting=='on-page'){
							 echo do_shortcode('[listinghub_search action="same_page"]');					
							}
						}	
						?>
					</div>	
				<div class="col-md-12 col-lg-12 col-xl-12 col-sm-12  " id="dirpro_directories" >	
					<div class="row">	
						<div class="col-xl-3 col-lg-3 col-md-3  col-sm-6 col-6 ">
							<div class="pull-left clearfix   text-small ">
								<?php echo esc_html($listinghub_query->found_posts);?><?php esc_html_e(' Results','listinghub');?>
							</div>
						</div>
						<div class="col-xl-9 col-lg-9 col-md-9  col-sm-6 col-6 ">
							<div class="text-right clearfix   ">
								
								<div class="listing-top-layout">
									<?php
										if(isset($active_archive_fields['top_search_form'])){
											if($search_form_setting=='popup'){
										?>
										<span>							
											<button type="button" class="btn btn-big "  onclick="listinghub_call_filter()">
												<i class="fa-solid fa-magnifying-glass mr-1"></i><?php esc_html_e('Filters', 'listinghub' ); ?>	
												<?php
													if( $listinghub_filter_badge>0 ){
													?>
													<span class="badge badge-pill badge-secondary"><?php echo esc_html($listinghub_filter_badge); ?></span>
													<?php
													}
												?>
											</button>	
										</span>
										<?php
											}
										}
									?>
									<ul class="mr-3">										
										<?php
											if( $listinghub_filter_badge>0 ){
											?>	
											<li class="topicon-border">									
												<a id="resetmainpage"  href="#" data-placement="top" data-toggle="tooltip"  title="<?php  esc_html_e('Reset Search','listinghub'); ?>"><i class="fa fa-refresh" aria-hidden="true"></i>
												</a>
											</li>
											<?php
											}
										?>
									</ul>
								</div>
							</div>
						</div>
					</div>	
					<div class="clearfix"></div>
					<div class="row justify-content-center" >
						<?php
							$i=0;
							include( ep_listinghub_template. 'listing/archive_feature_listing.php');
							if ( $listinghub_query->have_posts() ) :
							while ( $listinghub_query->have_posts() ) : $listinghub_query->the_post();
							$id = get_the_ID();
							
							$post_author_id= get_post_field( 'post_author', $id );
							$main_class->check_listing_expire_date($id, $post_author_id, $listinghub_directory_url);
							if(get_post_meta($id, 'listinghub_featured', true)!='featured'){
							    
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
								$author_package_id=get_user_meta($post_author_id, 'iv_directories_package_id', true);
								$have_vip_badge= get_post_meta($author_package_id,'iv_directories_package_vip_badge',true);
								$exprie_date= strtotime (get_user_meta($post_author_id, 'iv_directories_exprie_date', true));
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
							$dirs_json_map = json_encode($dirs_data);
						?>
						<?php else :
						$dirs_json=''; ?>
						<?php esc_html_e( 'Sorry, no posts matched your criteria.','listinghub' ); ?>
						<?php endif; ?>
					</div>	
					<div class="row mt-1 post-pagination">
						<div class="col-lg-12 text-center ep-list-style">
							<?php 						
								$GLOBALS['wp_query']->max_num_pages = $listinghub_query->max_num_pages;
								the_posts_pagination(array(
								'next_text' => '<i class="fas fa-angle-double-right"></i>',
								'prev_text' => '<i class="fas fa-angle-double-left"></i>',
								'screen_reader_text' => ' ',
								'type'                => 'list'
								));
							?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
	<!-- end of arhiece page -->
</div>
<!-- end of bootstrap wrapper -->
<?php
	$dir_addedit_contactustitle=get_option('dir_addedit_contactustitle');
	if($dir_addedit_contactustitle==""){$dir_addedit_contactustitle='Contact US';}
?>
<?php
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
	'Add_to_Favorites'=>esc_html__('Save', 'listinghub' ),
	'Added_to_Favorites'=>esc_html__('Saved', 'listinghub' ),		
	'Please_put_your_message'=>esc_html__('Please put your name,email Cover letter & attached file', 'listinghub' ),
	'contact'=> wp_create_nonce("contact"),
	'dirwpnonce'=> wp_create_nonce("myaccount"),
	'listing'=> wp_create_nonce("listing"),
	'cv'=> wp_create_nonce("Doc/CV/PDF"),
	'ep_listinghub_URLPATH'=>ep_listinghub_URLPATH,
	) );
	
	
?>
<?php
	wp_reset_query();
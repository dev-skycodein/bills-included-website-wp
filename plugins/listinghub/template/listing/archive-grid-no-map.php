

<?php
	// Version assets by file mtime so cache updates when files change (avoids stale CSS/JS).
	$listinghub_archive_css = defined( 'ep_listinghub_ABSPATH' ) ? ep_listinghub_ABSPATH . 'admin/files/css/archive-listing.css' : '';
	$listinghub_archive_ver = ( $listinghub_archive_css !== '' && file_exists( $listinghub_archive_css ) ) ? (string) filemtime( $listinghub_archive_css ) : '';

	wp_enqueue_script("jquery");	
	wp_enqueue_script('jquery-ui-core');
	wp_enqueue_script('jquery-ui-datepicker');
	wp_enqueue_script('jquery-ui-autocomplete');
	wp_enqueue_script('popper', ep_listinghub_URLPATH . 'admin/files/js/popper.min.js');
	wp_enqueue_script('bootstrap', ep_listinghub_URLPATH . 'admin/files/js/bootstrap.min-4.js'); 
	wp_enqueue_style('bootstrap', ep_listinghub_URLPATH . 'admin/files/css/iv-bootstrap.css');
	wp_enqueue_style('listinghub_listing_style_alphabet_sort', ep_listinghub_URLPATH . 'admin/files/css/archive-listing.css', array(), $listinghub_archive_ver );	
	listinghub_enqueue_colorbox();
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
	// Filter by agency (post meta agency_post_id)
	$agency_id = 0;
	if ( is_array( $atts ) && ! empty( $atts['agency_post_id'] ) ) {
		$agency_id = (int) $atts['agency_post_id'];
	}
	if ( ! $agency_id && get_query_var( 'listing-agency' ) !== '' ) {
		$agency_id = (int) get_query_var( 'listing-agency' );
	}
	if ( ! $agency_id && ! empty( $_REQUEST['listing-agency'] ) ) {
		$agency_id = (int) $_REQUEST['listing-agency'];
	}
	if ( $agency_id ) {
		if ( ! isset( $args['meta_query'] ) ) {
			$args['meta_query'] = array();
		}
		$args['meta_query'][] = array(
			'key'   => 'agency_post_id',
			'value' => $agency_id,
			'compare' => '=',
		);
	}
	// For featrue listing***********
	$feature_listing_all =array();
	$feature_listing_all =$args;
	if(isset($search_arg['lng']) and $search_arg['lng']!=''){ 
		$listinghub_query = new WP_GeoQuery( $args );
		}else{
		$listinghub_query = new WP_Query( $args );
	}
	if ( function_exists( 'listinghub_log_search' ) ) {
		listinghub_log_search( $listinghub_directory_url, $listinghub_query->found_posts );
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
	$wrapper_class = 'container-fluid';
	if ( is_array( $atts ) && ! empty( $atts['wrapper_class'] ) ) {
		$wrapper_class = sanitize_text_field( $atts['wrapper_class'] );
	}
?>
<!-- wrap everything for our isolated bootstrap -->
<div class="bootstrap-wrapper">
	<!-- archieve page own design font and others -->
	<section class=" py-3">
		<div class="<?php echo esc_attr( $wrapper_class ); ?> "  >
			<div class="row" id="full_grid"> 
					<div class="col-md-12 col-lg-12 col-xl-12 col-sm-12 " id="listinghub_search_bar" >	
						<?php echo do_shortcode( '[listinghub_search_bar]' ); ?>
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
								<?php
								$listinghub_sort_options = array(
									'high-to-low' => __( 'Highest price', 'listinghub' ),
									'low-to-high' => __( 'Lowest price', 'listinghub' ),
									'date-desc'   => __( 'Recent', 'listinghub' ),
									'date-asc'    => __( 'Oldest', 'listinghub' ),
								);
								$listinghub_current_sort = isset( $_REQUEST['sfsort_listing'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['sfsort_listing'] ) ) : 'date-desc';
								if ( ! array_key_exists( $listinghub_current_sort, $listinghub_sort_options ) ) {
									$listinghub_current_sort = 'date-desc';
								}
								$listinghub_archive_link = get_post_type_archive_link( $listinghub_directory_url );
								?>
								<div class="listinghub-archive-sort" id="listinghub_archive_sort">
									<button type="button" class="listinghub-archive-sort-trigger" id="listinghub_archive_sort_trigger" aria-expanded="false" aria-controls="listinghub_archive_sort_dropdown" aria-haspopup="listbox">
										<span class="listinghub-archive-sort-label"><?php esc_html_e( 'Sort:', 'listinghub' ); ?></span>
										<span class="listinghub-archive-sort-value listinghub-archive-sort-value-underline"><?php echo esc_html( $listinghub_sort_options[ $listinghub_current_sort ] ); ?></span>
										<span class="listinghub-archive-sort-arrow" aria-hidden="true"></span>
									</button>
									<ul class="listinghub-archive-sort-dropdown" id="listinghub_archive_sort_dropdown" role="listbox" hidden>
										<?php foreach ( $listinghub_sort_options as $value => $label ) : ?>
											<?php
											$sort_url = add_query_arg( array_merge( $_GET, array( 'sfsort_listing' => $value ) ), $listinghub_archive_link );
											$is_selected = ( $value === $listinghub_current_sort );
											?>
											<li role="option" <?php echo $is_selected ? ' aria-selected="true"' : ''; ?>>
												<a href="<?php echo esc_url( $sort_url ); ?>" class="listinghub-archive-sort-option <?php echo $is_selected ? ' is-selected' : ''; ?>" data-value="<?php echo esc_attr( $value ); ?>" data-label="<?php echo esc_attr( $label ); ?>">
													<?php if ( $is_selected ) : ?><span class="listinghub-archive-sort-check" aria-hidden="true">✓</span><?php endif; ?>
													<?php echo esc_html( $label ); ?>
												</a>
											</li>
										<?php endforeach; ?>
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
	$listinghub_sl_js = defined( 'ep_listinghub_ABSPATH' ) ? ep_listinghub_ABSPATH . 'admin/files/js/single-listing.js' : '';
	$listinghub_sl_ver = ( $listinghub_sl_js !== '' && file_exists( $listinghub_sl_js ) ) ? (string) filemtime( $listinghub_sl_js ) : null;
	wp_enqueue_script( 'listinghub_single-listing', ep_listinghub_URLPATH . 'admin/files/js/single-listing.js', array( 'jquery' ), $listinghub_sl_ver, true );
	wp_localize_script('listinghub_single-listing', 'listinghub_data', array(
	'ajaxurl' 			=> admin_url( 'admin-ajax.php' ),
	'loading_image'		=> '<img src="'.ep_listinghub_URLPATH.'admin/files/images/loader.gif">',
	'current_user_id'	=>get_current_user_id(),
	'Please_login'=>esc_html__('Please login', 'listinghub' ),
	'Add_to_Favorites'=>esc_html__('Save', 'listinghub' ),
	'Added_to_Favorites'=>esc_html__('Saved', 'listinghub' ),		
	'Please_put_your_message'=>esc_html__('Please complete Name, Email, and all required fields (move date, budget, bedrooms).', 'listinghub' ),
	'contact'=> wp_create_nonce("contact"),
	'dirwpnonce'=> wp_create_nonce("myaccount"),
	'listing'=> wp_create_nonce("listing"),
	'cv'=> wp_create_nonce("Doc/CV/PDF"),
	'ep_listinghub_URLPATH'=>ep_listinghub_URLPATH,
	) );
	
	
?>
<script>
(function() {
	var wrap = document.getElementById('listinghub_archive_sort');
	if (!wrap) return;
	var trigger = document.getElementById('listinghub_archive_sort_trigger');
	var dropdown = document.getElementById('listinghub_archive_sort_dropdown');
	if (!trigger || !dropdown) return;
	trigger.addEventListener('click', function(e) {
		e.preventDefault();
		e.stopPropagation();
		// When Sort is opened, hide any open search/filter dropdowns in the search bar (but not the Filters panel itself).
		try {
			// Hide Beds panel
			var bedsPanel = document.getElementById('listinghub_sb_beds_panel');
			var bedsWrap = document.querySelector('.listinghub-sb-beds-wrap');
			if (bedsPanel) bedsPanel.setAttribute('hidden', '');
			if (bedsWrap) bedsWrap.classList.remove('is-open');
			// Hide Renter type panel
			var renterPanel = document.getElementById('listinghub_sb_renter_panel');
			var renterWrap = document.querySelector('.listinghub-sb-renter-wrap');
			if (renterPanel) renterPanel.setAttribute('hidden', '');
			if (renterWrap) renterWrap.classList.remove('is-open');
			// Hide simple panels (radius, min/max price)
			document.querySelectorAll('.listinghub-sb-simple-panel').forEach(function(p){ p.setAttribute('hidden',''); });
			document.querySelectorAll('.listinghub-sb-simple-wrap').forEach(function(w){ w.classList.remove('is-open'); });
			// Hide Property type popup
			var propPopup = document.getElementById('listinghub_sb_property_popup');
			var propWrap = document.querySelector('.listinghub-sb-property-section');
			if (propPopup) propPopup.setAttribute('hidden','');
			if (propWrap) propWrap.classList.remove('is-open');
			// Hide Bathrooms panel
			var bathsPanel = document.getElementById('listinghub_sb_baths_panel');
			var bathsWrap = document.querySelector('.listinghub-sb-baths-wrap');
			if (bathsPanel) bathsPanel.setAttribute('hidden','');
			if (bathsWrap) bathsWrap.classList.remove('is-open');
			// Hide Locations popup
			var locPopup = document.getElementById('listinghub_sb_locations_popup');
			var locWrap = document.querySelector('.listinghub-sb-locations-section');
			if (locPopup) locPopup.setAttribute('hidden','');
			if (locWrap) locWrap.classList.remove('is-open');
		} catch (err) {
			// Fail silently – sort still works even if any element is missing.
		}
		var open = dropdown.getAttribute('hidden') === null;
		if (open) {
			dropdown.setAttribute('hidden', '');
		} else {
			dropdown.removeAttribute('hidden');
		}
		trigger.setAttribute('aria-expanded', !open);
		wrap.classList.toggle('is-open', !open);
	});
	document.addEventListener('click', function() {
		dropdown.setAttribute('hidden', '');
		trigger.setAttribute('aria-expanded', 'false');
		wrap.classList.remove('is-open');
	});
	wrap.addEventListener('click', function(e) { e.stopPropagation(); });
})();
</script>
<?php
	wp_reset_query();
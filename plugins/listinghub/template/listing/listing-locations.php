<?php
	wp_enqueue_style('bootstrap', ep_listinghub_URLPATH . 'admin/files/css/iv-bootstrap.css');
	wp_enqueue_style('listinghub_locations', ep_listinghub_URLPATH . 'admin/files/css/locations.css');	
	wp_enqueue_script('masonry', ep_listinghub_URLPATH . 'admin/files/js/masonry.pkgd.min.js');
	wp_enqueue_script('listinghub_locations', ep_listinghub_URLPATH . 'admin/files/js/locations.js');
	global $post,$wpdb,$tag;
	$listinghub_directory_url=get_option('ep_listinghub_url');
	if($listinghub_directory_url==""){$listinghub_directory_url='listing';}
	$post_limit='9999';
	if(isset($atts['post_limit']) and $atts['post_limit']!="" ){
		$post_limit=$atts['post_limit'];
	}
	$locations_arr=array();
	if(isset($atts['locations'])){
		$locations = $atts['locations'];
		$locations_arr=explode(',',$locations);
	}
?>
<div class="bootstrap-wrapper">	
	<div class="container">	
		<div class="row locations">	
			<?php
				$taxonomy = $listinghub_directory_url.'-locations';
				$args = array(
				'orderby'           => 'name',
				'order'             => 'ASC',
				'hide_empty'        => true,
				'exclude'           => array(),
				'exclude_tree'      => array(),
				'include'           => array(),
				'number'            => $post_limit,
				'fields'            => 'all',
				'slug'              => $locations_arr,	
				'parent'            => '0',
				'hierarchical'      => true,					
				'get'               => '',
				);
				$terms = get_terms($taxonomy,$args); // Get all terms of a taxonomy
				if ( $terms && !is_wp_error( $terms ) ) :
				$i=0;
				foreach ( $terms as $term_parent ) {
					if($term_parent->count>0){
						$cate_main_image = get_term_meta($term_parent->term_id, 'listinghub_term_image', true); 
						if($cate_main_image!=''){
							$feature_img=$cate_main_image;
							}else{									
							if(get_option('listinghub_location_defaultimage')!=''){
								$feature_img= wp_get_attachment_image_src(get_option('listinghub_location_defaultimage'));
								if(isset($feature_img[0])){									
									$feature_img=$feature_img[0] ;
								}
								}else{
								$feature_img=ep_listinghub_URLPATH."/assets/images/location.jpg";
							}
						}
						$cat_link= get_term_link($term_parent , $listinghub_directory_url.'-locations');
					?>
					
					<div class="col-xl-3 col-lg-3 col-md-6  col-sm-12 col-12  mt-2 mb-2  ">
						<a href="<?php echo esc_url($cat_link);?>">
							<div class=" location-card-border-round mb-2 " >	
								<div class="location-card-img-container-location"  style="background:url(<?php echo esc_url($feature_img); ?>) no-repeat; background-size:cover;">									
									<div class="location-card-img_overlay rounded mr-5"></div>
									<h6 class="location-card-cat_title text-center text-white"><i class="fa-solid fa-location-dot mr-1"></i> <?php echo esc_html($term_parent->name);?></h6>
								</div>
							</div>
						</a>	
					</div>
					<?php
					}
				}
				endif;
			?>
		</div>
	</div>
	</div>
	<?php
		wp_reset_query();
	?>	
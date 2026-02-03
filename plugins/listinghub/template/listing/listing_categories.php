<?php
	wp_enqueue_style('bootstrap', ep_listinghub_URLPATH . 'admin/files/css/iv-bootstrap.css');
	wp_enqueue_style('listinghub_categories', ep_listinghub_URLPATH . 'admin/files/css/categories.css');
	global $post,$wpdb,$tag;
	$listinghub_directory_url=get_option('ep_listinghub_url');
	if($listinghub_directory_url==""){$listinghub_directory_url='listing';}
	$post_limit='9999';
	if(isset($atts['post_limit']) and $atts['post_limit']!="" ){
		$post_limit=$atts['post_limit'];
	}
	$postcats_arr=array();
	if(isset($atts['slugs'])){
		$postcats = $atts['slugs'];
		$postcats_arr=explode(',',$postcats);
	}
	
?>
<section class="bootstrap-wrapper background-transparent">
	<div class="container ">
		<div class="row justify-content-center">
			<?php
				$taxonomy = $listinghub_directory_url.'-category';
				$args = array(					
				  'exclude'           => array(),
				'exclude_tree'      => array(),
				'include'           => array(),
				'number'            => $post_limit,
				'fields'            => 'all',
				'slug'              => $postcats_arr,	
				'parent'            => '0',
				'hierarchical'      => true,
				'get'               => '',
				'hide_empty'        => false, 
				);
				$terms = get_terms($taxonomy,$args); // Get all terms of a taxonomy
				
				if ( $terms && !is_wp_error( $terms ) ) :
				$i=0;
				
				$terms = get_terms($taxonomy, $args); // Get all terms of a taxonomy
			if ($terms && !is_wp_error($terms)) {
				// Reorder terms based on $postcats_arr order
				$ordered_terms = array();
				$i=0;
				foreach ($postcats_arr as $slug) {
					foreach ($terms as $term) {
						if ($term->slug === $slug) {
							$ordered_terms[$i] = $term;
							$i++;
						}
					}
				}
				if(empty($ordered_terms)){
					$ordered_terms= $terms;
				}
					
				// Now process the ordered terms
				foreach ($ordered_terms as $term_parent) { 
					if ($term_parent->count > 0) {
						$cate_main_image = get_term_meta($term_parent->term_id, 'listinghub_term_image', true); 
						if ($cate_main_image != '') {
							$feature_img = $cate_main_image;
						} else {									
							if (get_option('listinghub_category_defaultimage') != '') {
								$feature_img = wp_get_attachment_image_src(get_option('listinghub_category_defaultimage'));
								if (isset($feature_img[0])) {									
									$feature_img = $feature_img[0];
								}
							} else {
								$feature_img = ep_listinghub_URLPATH . "/assets/images/category.png";
							}
						}
						$cat_link = get_term_link($term_parent, $listinghub_directory_url . '-category');
						?>
								
						<div class="col-xl-4 col-lg-4 col-md-6  col-sm-6 col-12  mt-4 mb-4" id="<?php echo esc_html($i); ?>" >
							<div class=" card-border-round mb-2 " >										
								
									<div class="card-img-container">
										<a href="<?php echo esc_url($cat_link);?>"><img src="<?php echo esc_html($feature_img);?>" class="card-img-top-listing">					
											</a>
									</div>	
									
								<div class="card-body  ">
									<h4 class="cat_title"><?php echo esc_html($term_parent->name);?></h4>
								</div>
										
							</div>
						</div>
					<?php	
					}
				}
			}
				
		endif;
			?>
		</div>
	</div>
</section>
<?php	
	wp_reset_query();
?>
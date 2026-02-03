<?php
	wp_enqueue_style('bootstrap', ep_listinghub_URLPATH . 'admin/files/css/iv-bootstrap.css');
	wp_enqueue_script('slick', ep_listinghub_URLPATH.'admin/files/css/slick/slick.js', array('jquery'), $ver = true, true );
	wp_enqueue_style('slick', ep_listinghub_URLPATH . 'admin/files/css/slick/slick.css');
	wp_enqueue_style('slick-theme', ep_listinghub_URLPATH . 'admin/files/css/slick/slick-theme.css'); 
	wp_enqueue_style('fontawesome', ep_listinghub_URLPATH . 'admin/files/css/fontawesome.css'); 
	global $post,$wpdb;
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
<div class="bootstrap-wrapper " >
	<div class=" container  caretories-slick">
		<?php
			$taxonomy = $listinghub_directory_url.'-category';
			$args = array(
			'orderby'           => 'name',
			'order'             => 'ASC',
			'hide_empty'        => true,
			'exclude'           => array(),
			'exclude_tree'      => array(),
			'include'           => array(),
			'number'            => $post_limit,
			'fields'            => 'all',
			'slug'              => $postcats_arr,	
			'parent'            => '0',
			'hierarchical'      => true,					
			'get'               => '',
			);
			$terms = get_terms($taxonomy,$args); // Get all terms of a taxonomy
			if ( $terms && !is_wp_error( $terms ) ) :
			$i=0;
			foreach ( $terms as $term_parent ) {
				if($term_parent->count>0){							
					$caticon= get_term_meta($term_parent->term_id, 'listinghub_term_icon', true);
					if($caticon==''){							
						$caticon="far fa-check-circle";
					}							
					$cat_link= get_term_link($term_parent , $listinghub_directory_url.'-category');
				?>
				<div class="p-3 text-center item ">
					<a href="<?php echo esc_url($cat_link); ?>" class="">
						<i class="<?php echo esc_html($caticon);?>"></i>	
						<div class=" item-title">
							<?php echo esc_html($term_parent->name); ?>
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

<?php
	wp_enqueue_script('listinghub_carousel_slick', ep_listinghub_URLPATH.'admin/files/js/carousel-slick.js', array('jquery'), $ver = true, true );
	wp_reset_query();
?>
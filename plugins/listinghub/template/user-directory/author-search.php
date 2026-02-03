<form method="GET" action="#">
	<div class="sidebar-shadow none-shadow mb-30">
		<div class="sidebar-filters">
			<div class="filter-block mb-30">			
				<div class="form-group">
					<input type="text" name="user_name_search" value="<?php echo (isset($_REQUEST['user_name_search'])? esc_attr($_REQUEST['user_name_search']):''); ?>" class="form-control " placeholder="<?php esc_html_e('Search','listinghub'); ?>">				
				</div>
			</div>
			<div class="filter-block mb-30">
				<h5 class="medium-heading mb-3"><?php esc_html_e('Category','listinghub'); ?></h5>
				<div class="form-group no-gutters">
					<?php
						$argscat = array(
						'type'                     => $listinghub_directory_url,
						'orderby'                  => 'name',
						'order'                    => 'ASC',
						'hide_empty'               => true,
						'hierarchical'             => 1,
						'exclude'                  => '',
						'include'                  => '',
						'number'                   => '',
						'taxonomy'                 => $listinghub_directory_url.'-category',
						'pad_counts'               => false
						);
						$categories = get_categories( $argscat );
						$category_input_array= array();
						if(isset($_REQUEST['category_input'])){												
							$category_input_array1 = array_map( 'sanitize_text_field', $_REQUEST['category_input'] );
							foreach($category_input_array1 as $cat_one){
								$category_input_array[]=esc_html($cat_one);
							}
						}						
						if ( $categories && !is_wp_error( $categories ) ) :
						foreach ( $categories as $term ) {
							if(trim($term->name)!=''){	
								$selected='';
								if(in_array($term->name, $category_input_array)){
									$selected='checked="checked"';
								}
							?>		
							<div class="col-12 mt-2">
								<label class="customcheck"><span class="text-small "><?php echo esc_html(ucfirst($term->name)); ?></span>
									<input type="checkbox" name="category_input[]" value="<?php echo esc_attr($term->name);?>" id="<?php echo esc_attr($term->name);?>" <?php echo esc_attr($selected); ?> >
									<span class="checkmark"></span>
								</label>
							</div>	
							<?php
							}
						}
						endif;
					?>
				</div>
			</div>
			<div class="filter-block mb-30">
				<h5 class="medium-heading mb-10"><?php esc_html_e('Location','listinghub'); ?></h5>
				<div class="form-group no-gutters">
					<?php
						$argscat = array(
						'type'                     => $listinghub_directory_url,
						'orderby'                  => 'name',
						'order'                    => 'ASC',
						'hide_empty'               => true,
						'hierarchical'             => 1,
						'exclude'                  => '',
						'include'                  => '',
						'number'                   => '',
						'taxonomy'                 => $listinghub_directory_url.'-locations',
						'pad_counts'               => false
						);
						$categories = get_categories( $argscat );
						$category_input_array= array();
						if(isset($_REQUEST['location_input'])){												
							$category_input_array1 = array_map( 'sanitize_text_field', $_REQUEST['location_input'] );
							foreach($category_input_array1 as $cat_one){
								$category_input_array[]=esc_html($cat_one);
							}
						}						
						if ( $categories && !is_wp_error( $categories ) ) :
						foreach ( $categories as $term ) {
							if(trim($term->name)!=''){	
								$selected='';
								if(in_array($term->name, $category_input_array)){
									$selected='checked="checked"';
								}
							?>
							<div class="col-12 mt-2">
								<label class="customcheck"><span class="text-small "><?php echo esc_html(ucfirst($term->name)); ?></span>
									<input type="checkbox" name="location_input[]"  value="<?php echo esc_attr($term->name);?>" <?php echo esc_attr($selected); ?> >
									<span class="checkmark"></span>
								</label>
							</div>							
							<?php
								
							}
						}
						endif;								
					?>
				</div>
			</div>
			<div class="buttons-filter">			
				<a  href="#" class="btn btn-border mr-2  mt-2" id="resetmainpage"><i class="fa fa-refresh" aria-hidden="true"></i></a>
				<button type="submit"  class="btn btn-big mt-2"><?php esc_html_e('Apply filter','listinghub'); ?></button>
			</div>
		</div>
	</div>
</form>
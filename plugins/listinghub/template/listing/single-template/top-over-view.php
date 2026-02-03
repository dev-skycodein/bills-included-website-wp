<div class="listing-overview">
	<h5 class="border-bottom pb-15 mb-30 toptitle"><?php esc_html_e( 'Top Information', 'listinghub' ); ?></h5>
	<div class="row">
		<?php
			$saved_icon_cat='';
			if(is_array($active_single_fields_saved)){
				foreach($active_single_fields_saved  as $field_key => $field_value){
					$saved_icon= listinghub_get_icon($single_page_icon_saved, $field_key, 'single');
					if( in_array($field_key,$data_for_top ) ){						
						switch ($field_key) {
							case "category":
							$currentCategory = $main_class->listinghub_get_categories_caching($id,$listinghub_directory_url);
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
							if($cat_name2 !=''){
							?>
							<div class="col-md-6 ">
								<div class="row mt-3"> 
									<div class="col-md-6  col-6">
										<i class="<?php echo esc_html($saved_icon); ?>"></i><span class="text-description"><?php esc_html_e( 'Industry', 'listinghub' ); ?></span>
									</div>
									<div class=" col-md-6  col-6">
											<strong class="small-heading"><?php echo esc_html($cat_name2); ?></strong>
									</div>
								</div>							
							</div>
							<?php
							}
							break;								
							case "post_date": 
						?>
						<div class="col-md-6 ">
							<div class="row mt-3"> 
								<div class="col-md-6  col-6">
									<i class="<?php echo esc_html($saved_icon); ?>"></i><span class="text-description"><?php esc_html_e( 'Updated', 'listinghub' ); ?></span>
								</div>
								<div class=" col-md-6  col-6">
										<strong class="small-heading"><?php echo get_the_date( 'd M-Y ', $id ); ?></strong>
								</div>
							</div>							
						</div>							
						<?php																
							break;
							case "deadline": 											
						?>
						<div class="col-md-6 ">
							<div class="row mt-3"> 
								<div class="col-md-6  col-6">
									<i class="<?php echo esc_html($saved_icon); ?>"></i><span class="text-description"><?php echo ucfirst($field_key); ?></span>
								</div>
								<div class=" col-md-6  col-6">
									<?php if(get_post_meta($id,$field_key,true)!=''){?>
										<strong class="small-heading"><?php echo date( 'd M-Y ', strtotime (get_post_meta($id,$field_key,true)) ); ?></strong>
										<?php
										}
										?>
								</div>
							</div>							
						</div>						
						
						<?php																
							break;
							default:
							if(get_post_meta($id,$field_key,true)!=''){
								$custom_meta_data=get_post_meta($id,$field_key,true);
								if(is_array($custom_meta_data)){
									$full_data='';
									foreach( $custom_meta_data as $one_data){
										$full_data=$full_data.' '.$one_data;
									}
									$custom_meta_data=$full_data;
								}
								$display_title= array_search($field_key,$data_for_top);
							?>
							<div class="col-md-6 ">
								<div class="row mt-3"> 
									<div class="col-md-6  col-6">
										<i class="<?php echo esc_html($saved_icon); ?>"></i><span class="text-description"><?php echo ucfirst($display_title); ?></span>
									</div>
									<div class=" col-md-6  col-6">
											<strong class="small-heading"><?php echo esc_html($custom_meta_data); ?></strong>
									</div>
								</div>							
							</div>
							<?php	
							}
							break;
						}										
					}									
				}
			}								
		?>			 
	</div>
</div>
			
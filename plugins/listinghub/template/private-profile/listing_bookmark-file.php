<?php
	$listinghub_directory_url=get_option('ep_listinghub_url');
	if($listinghub_directory_url==""){$listinghub_directory_url='listing';}
?>
<div class="border-bottom pb-15 mb-3 toptitle-sub"><?php esc_html_e('Saved listings', 'listinghub'); ?>	
</div>	
<section class="content-main-right list-listings mb-30">
	<div class="list">
		<?php
			$favorites=get_user_meta(get_current_user_id(),'_dir_favorites', true);	
			$favorites_a = array();
			$favorites_a = explode(",", $favorites);	
			$profile_page=get_option('epjbfavorites');				
			$ids = array_filter($favorites_a);		
			if(sizeof($favorites_a)>0){
			?>
			<table id="all-bookmark" class="table tbl-epmplyer-bookmark" >
				<thead>
					<tr class="">
						<th><?php  esc_html_e('Title','listinghub');?></th>
					</tr>
				</thead>
				<?php
					$i=0;
					foreach ($ids as $user_id){	 
						if((int)$user_id>0){						
							$page_link= get_permalink( $profile_page).'?&id='.$user_id; 
							$user_data = get_user_by( 'ID', $user_id );
							$user_id=trim($user_id);
							if ( get_post_status ( $user_id ) ) {
							?>
							<tr id="listingbookmark_<?php echo esc_html(trim($user_id));?>" >
								<td class="d-md-table-cell">
									<div class="listing-item bookmark">
										<div class="row align-items-center">
											<div class="col-md-2">
												<div class="img-listing text-center circle">												
													<a href="<?php  echo get_permalink($user_id); ?>">
														<?php
															$feature_image = wp_get_attachment_image_src( get_post_thumbnail_id( $user_id ), 'large' );
															if(isset($feature_image[0])){?>														
															<img  class="rounded-profileimg" src="<?php echo esc_url($feature_image[0]); ?>">
															<?php															
																}else{	
																?>
																<div class="blank-rounded-image--"></div>
																<?php
																
															}													
														?>
													</a>
												</div>
											</div>
											<div class="col-md-10 listing-info px-0">
												<div class="text px-0 text-left">
													<span class="toptitle-sub"><a href="<?php  echo get_permalink($user_id); ?>">
														<?php echo get_the_title($user_id); ?>
													</a></span>
													<?php

														if(get_post_meta($user_id,'address',true)!=''){
														?>
														<div class="date-listing"><span class="location"><i class="far fa-map"></i><span class="p-2"><?php echo esc_html(get_post_meta($user_id,'address',true)); ?> <?php echo esc_html(get_post_meta($user_id,'city',true)); ?>, <?php echo esc_attr(get_post_meta($user_id,'zipcode',true)); ?>,<?php echo esc_attr(get_post_meta($user_id,'country',true)); ?></span></span>
														</div>
														<?php
														}
													?>
													
														<?php
														$currentCategory = $main_class->listinghub_get_categories_caching($user_id,$listinghub_directory_url);
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
														?>
													<span> <?php echo esc_html($cat_name2); ?> </span>
													<div class="group-button mt-2">	
														<?php
															$dirpro_email_button=get_post_meta($user_id,'dirpro_email_button',true);
															if($dirpro_email_button==""){$dirpro_email_button='yes';}
															if($dirpro_email_button=='yes'){
															?>
															<button class="btn btn-small-ar" onclick="listinghub_listing_email_popup('<?php echo esc_html(trim($user_id));?>')" ><i class="far fa-envelope"></i></button>
															<?php
															}
														?>
														<button class="btn btn-small-ar" onclick="listinghub_listing_bookmark_delete_myaccount('<?php echo esc_html($user_id);?>','listingbookmark')"><i class="far fa-trash-alt"></i></button>
													</div>
												</div>
											</div>
										</div>
									</div>
								</td>
							</tr>
							<?php
							}
						}
					}
				?>
			</table>
			<?php
			}
		?>
	</div>
</section>
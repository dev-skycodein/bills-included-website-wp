<div class="border-bottom pb-15 mb-3 toptitle-sub"><?php esc_html_e('Saved Author', 'listinghub'); ?>	
</div>	


	<section class="content-main-right list-listings mb-30">
		<div class="list">
			<?php
				$favorites=get_user_meta(get_current_user_id(),'listinghub_authorbookmark', true);	
				$favorites_a = array();
				$main_class = new eplugins_listinghub;
				$favorites_a = explode(",", $favorites);	
				$profile_page=get_option('epjblistinghubr_public_profile_page');				
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
							
						?>
						<tr id="companybookmark_<?php echo esc_html(trim($user_id));?>" >
							<td class="d-md-table-cell">
								<div class="listing-item bookmark">
									<div class="row align-items-center">
										<div class="col-md-2">
											<div class="img-listing text-center circle">												
												<a href="<?php  echo esc_url($page_link); ?>">
													<?php
													$iv_profile_pic_url=get_user_meta($user_id, 'listinghub_profile_pic_thum',true);
													if($iv_profile_pic_url!=''){ ?>
													<img  class="rounded-profileimg img-fluid" src="<?php echo esc_url($iv_profile_pic_url); ?>">
													<?php
														}else{
														echo'<img src="'. ep_listinghub_URLPATH.'assets/images/Blank-Profile.jpg" class="rounded-logo img-fluid" >';
													}
												?>
												</a>
											</div>
										</div>
										<div class="col-md-10 listing-info px-0">
											<div class="text px-0 text-left">
												<span class="toptitle-sub"><a href="<?php  echo esc_url($page_link); ?>">
												<?php echo (get_user_meta($user_id,'full_name',true)!=''? get_user_meta($user_id,'full_name',true) : $user_data->display_name ); ?>
												</a></span>
																	
												<div class="location"><span> <?php esc_html_e('Open listings', 'listinghub'); ?></span>:<span class="p-2"> <?php echo esc_html($main_class->listinghub_total_listing_count($user_id, $allusers='no' )); ?></span>
												</div>
												
												<?php									
												if(get_user_meta($user_id,'address',true)!=''){
												?>
												<div class="date-listing"><span class="location"><i class="far fa-map"></i><span class="p-2"><?php echo get_user_meta($user_id,'address',true); ?> <?php echo get_user_meta($user_id,'city',true); ?>, <?php echo get_user_meta($user_id,'zipcode',true); ?>,<?php echo get_user_meta($user_id,'country',true); ?></span></span>
												
												</div>
												<?php
												}
												?>
												
												
												<div class="group-button mt-2">	
													<button class="btn btn-small-ar" onclick="listinghub_author_email_popup('<?php echo esc_html(trim($user_id));?>')" ><i class="far fa-envelope"></i></button>
													<button class="btn btn-small-ar" onclick="listinghub_company_bookmark_delete_myaccount('<?php echo esc_html($user_id);?>','companybookmark')"><i class="far fa-trash-alt"></i></button>
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
					?>
				</table>
				<?php
					}
				?>
				
				
		</div>
	</section>

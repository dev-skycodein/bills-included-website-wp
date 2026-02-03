<div class=" col-xl-3 col-lg-3  col-md-6  col-sm-6 col-12 listingdata-col">
	<div class="card-grid-2 card-employers " >		
		<div class="text-center card-grid-2-image-rd  d-flex justify-content-around">
			<a href="<?php echo esc_url($page_link); ?>">
				
					<?php
						$iv_profile_pic_url=get_user_meta($user->ID, 'listinghub_profile_pic_thum',true);
						if($iv_profile_pic_url!=''){ ?>
							<figure class="">	<img  class="rounded-circle logo-max-h"  src="<?php echo esc_url($iv_profile_pic_url); ?>"></figure>
						<?php
							}else{
							echo'<figure class="blank-rounded-logo center "></figure>';
							
						}
					?>
					
				
			</a>
		</div>
		<div class="card-block-info">
			<div class="card-profile">
				<h5><a href="<?php echo esc_url($page_link); ?>" class="toptitle "><?php echo (get_user_meta($user->ID,'full_name',true)!=''? get_user_meta($user->ID,'full_name',true) : $user->display_name ); ?></a></h5>
				
			</div>			
			<div class="row mt-2">
				<div class="col-sm-12 text-center text-sm">
					<?php
					  $all_locations= str_replace(',',' ',get_user_meta($user->ID, 'all_locations', true));
					 
						if($all_locations!=''){
						?>
						<span class="card-location ">
							<i class="fa-solid fa-location-dot mr-1"></i><?php echo esc_html($all_locations); ?>
						</span>
						<?php
						}
					?>					
				</div>					
			</div>
			
			<div class="card-2-bottom card-2-bottom-candidate mt-3">
				<div class="text-center"><?php				
					$total_listings= $main_class->listinghub_total_listing_count($user->ID, $allusers='no' );
					?>
						<a class="btn btn-border" href="<?php echo get_post_type_archive_link( $listinghub_directory_url ).'?&listing-author='.esc_attr($user->ID); ?>"><?php echo esc_html($total_listings);?> <?php esc_html_e('Open listings', 'listinghub'); ?></a>
					<div class="text-center">
					<a class="btn btn-border mt-2" href="<?php echo esc_url($page_link); ?>"><?php esc_html_e('View Profile', 'listinghub'); ?></a>
					</div>
				
				</div>
			</div>
		</div>
	</div>
</div>
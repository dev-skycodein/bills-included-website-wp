<div class="single-apply-listings mb-4">
	<div class="row align-items-center">
		<div class="col-md-5">
			<?php			
				if(array_key_exists('apply_button',$active_single_fields_saved)){ 				
				?>
				<button onclick="listinghub_apply_popup('<?php echo esc_attr($listingid);?>')" class="btn btn-big mr-2 mb-2 ">
					<?php 
						if($listing_apply=='yes'){?>
						<i class="fa fa-check-circle"></i>
						<?php
						}
					?>
				<?php esc_html_e('Apply Now','listinghub'); ?></button>	
				<?php
				}
				
				$dir_style5_email='yes';
				$dirpro_email_button=get_post_meta($listingid,'dirpro_email_button',true);
				if($dirpro_email_button==""){$dirpro_email_button='yes';}
				if($dir_style5_email=="yes" AND $dirpro_email_button=='yes'){
					$email_button='yes';
					}else{
					$email_button='no';
				}
				
				if(array_key_exists('contact_button',$active_single_fields_saved)){ 
					if($email_button=='yes'){ ?>
						<button type="button" class="btn btn-big mr-2 mb-2 " onclick="listinghub_call_popup('<?php echo esc_html($listingid);?>')"><?php esc_html_e( 'Contact', 'listinghub' ); ?></button>
					<?php
					}
				}
				if(array_key_exists('claim_button',$active_single_fields_saved)){ 
					 ?>
						<button type="button" class="btn btn-border mr-2 mt-2 mb-2 " onclick="listinghub_claim_popup('<?php echo esc_html($listingid);?>')"><?php esc_html_e( 'Report', 'listinghub' ); ?></button>
					<?php
					
				}
			?>
			
		</div>
		<?php
			if(array_key_exists('social-share',$active_single_fields_saved)){  
			?>
			<div class="col-md-7 text-lg-end social-share">
				<h6 class="color-text-paragraph-2 d-inline-block d-baseline mr-10"><?php esc_html_e('Share this', 'listinghub'); ?> </h6>
				<a class=" d-inline-block d-middle" href="<?php echo esc_url('https://www.facebook.com/sharer/sharer.php?u='.get_the_permalink($listingid ));?>">
				<img alt="" src="<?php echo ep_listinghub_URLPATH.'/assets/images/'; ?>share-fb.svg"></a>
				<a class=" d-inline-block d-middle" href="<?php echo esc_url('https://twitter.com/home?status='.get_the_permalink($listingid ));?>">
				<img alt="" src="<?php echo ep_listinghub_URLPATH.'/assets/images/'; ?>share-tw.svg"></a>
				<a class=" d-inline-block d-middle" href="<?php echo esc_url('http://www.reddit.com/submit?url='. get_the_permalink($listingid ));?>">
				<img alt="" src="<?php echo ep_listinghub_URLPATH.'/assets/images/'; ?>share-red.svg"></a>
				<a class="d-inline-block d-middle" href="<?php echo esc_url('https://api.whatsapp.com/send?text='.get_the_permalink($listingid ));?>">
				<img alt="" src="<?php echo ep_listinghub_URLPATH.'/assets/images/'; ?>share-whatsapp.svg"></a>
			</div>
			<?php
			}
		?>
	</div>
</div>
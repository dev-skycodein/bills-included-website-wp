<div class="single-apply-listings mb-4">
	<div class="row align-items-center">
		<div class="col-md-5">	
			<button type="button" class="btn btn-big mt-1 mb-3" onclick="listinghub_author_email_popup('<?php echo esc_attr($user_id);?>')">
									<?php esc_html_e('Send Message', 'listinghub'); ?></button>
		</div>		
			<div class="col-md-7 text-lg-end social-share">
				<h6 class="color-text-paragraph-2 d-inline-block d-baseline mr-10"><?php esc_html_e('Share this', 'listinghub'); ?> </h6>
				<a class=" d-inline-block d-middle" href="<?php echo esc_url('https://www.facebook.com/sharer/sharer.php?u='. get_the_permalink().'?id='.$user_id);?>">
				<img alt="" src="<?php echo ep_listinghub_URLPATH.'/assets/images/'; ?>share-fb.svg"></a>
				<a class=" d-inline-block d-middle" href="<?php echo esc_url('https://twitter.com/home?status='.get_the_permalink().'?id='.$user_id);?>">
				<img alt="" src="<?php echo ep_listinghub_URLPATH.'/assets/images/'; ?>share-tw.svg"></a>
				<a class=" d-inline-block d-middle" href="<?php echo esc_url('http://www.reddit.com/submit?url='. get_the_permalink().'?id='.$user_id);?>">
				<img alt="" src="<?php echo ep_listinghub_URLPATH.'/assets/images/'; ?>share-red.svg"></a>
				<a class="d-inline-block d-middle" href="<?php echo esc_url('https://api.whatsapp.com/send?text='.get_the_permalink().'?id='.$user_id);?>">
				<img alt="" src="<?php echo ep_listinghub_URLPATH.'/assets/images/'; ?>share-whatsapp.svg"></a>
			</div>			
	</div>
</div>
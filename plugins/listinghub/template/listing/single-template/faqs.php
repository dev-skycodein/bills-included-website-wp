<?php
wp_enqueue_style('listinghub_faqs', ep_listinghub_URLPATH . 'admin/files/css/faqs.css');

$listingid = (isset($listingid)?$listingid: '0' );

if(get_post_meta($listingid,'faq_title0',true)!="" ){
?>
<div class=" border-bottom pb-15 mb-3 toptitle"><i class="<?php echo esc_html($saved_icon); ?>"></i> <?php esc_html_e('FAQs', 'listinghub'); ?></div>

<div class=" row    mb-4 "> 
 <ul class="accordionFAQ faqul col-md-12 ">	
	<?php
		
		for($i=0;$i<20;$i++){
			if(get_post_meta($listingid,'faq_title'.$i,true)!='' || get_post_meta($listingid,'faq_description'.$i,true) ){?>
				  <li class="item">
					<h2 class="accordionTitle"><?php echo esc_attr(get_post_meta($listingid,'faq_title'.$i,true)); ?><span class="accIcon"></span></h2>
					 <div class="text"><?php echo esc_attr(get_post_meta($listingid,'faq_description'.$i,true)); ?> </div>
				  </li>								
			<?php							
			}
		}				
	?>
	</ul>
</div>
<?php
}
?>


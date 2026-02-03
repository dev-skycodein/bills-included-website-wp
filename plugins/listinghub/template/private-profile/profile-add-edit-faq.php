<div class="col-md-12"  id="faqsall">
		
	<?php	
		$faq_i=0;
		$post_edit_ID = (isset($post_edit->ID)?$post_edit->ID: '0' );
		for($i=0;$i<20;$i++){
			if(get_post_meta($post_edit_ID,'faq_title'.$i,true)!='' || get_post_meta($post_edit_ID,'faq_description'.$i,true) ){?>
			
				<div class="row border-bottom mb-4" id="faq_delete_<?php echo esc_html($i); ?>">
					<div class="col-md-5   form-group">
						
							<input type="text" class="form-control" name="faq_title[]" id="faq_title[]" value="<?php echo esc_attr(get_post_meta($post_edit_ID,'faq_title'.$i,true)); ?>" placeholder="<?php esc_html_e('FAQ','listinghub'); ?>">
					</div>
					<div class="col-md-6 form-group">	
						<textarea rows="2"  name="faq_description[]" id="faq_description[]" placeholder="<?php esc_html_e('Answer','listinghub'); ?>"><?php echo esc_attr(get_post_meta($post_edit_ID,'faq_description'.$i,true)); ?></textarea>
						
					</div>
						<div class="col-md-1 form-group pull-right">											
							<button type="button" onclick="listinghub_faq_delete(<?php echo esc_html($i); ?>);"  class="btn btn-small-ar"><span class="dashicons dashicons-trash"></span></button>						
					</div>
					<div class="row"><hr></div>
			</div>
			<div class="clearfix"></div>
			
			<?php
				$faq_i++;
			}
		}
	?>
		
	<div class="row" id="faqmain">
		<div class="col-md-5 form-group">
			<input type="text" class="form-control" name="faq_title[]" id="faq_title[]" value="" placeholder="<?php esc_html_e('FAQ','listinghub'); ?>">
		</div>
		<div class="col-md-6 form-group">		
			<textarea  class="form-control" rows="2" name="faq_description[]" id="faq_description[]" placeholder="<?php esc_html_e('Answer','listinghub'); ?>"></textarea>
		</div>
		<div class="col-md-1 form-group pull-right"></div>
	</div>
</div>
<div class="col-md-12 form-group ">	
	<button type="button" onclick="listinghub_add_faq_field();"  class="btn btn-small-ar"><?php esc_html_e('Add More','listinghub'); ?></button>
</div>


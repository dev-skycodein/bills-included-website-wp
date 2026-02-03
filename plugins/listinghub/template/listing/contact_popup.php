<?php
	//require_once( explode( "wp-content" , __FILE__ )[0] . "wp-load.php" );	
	wp_enqueue_style('bootstrap', ep_listinghub_URLPATH . 'admin/files/css/iv-bootstrap.css');
	$dir_id=0; if(isset($_REQUEST['dir_id'])){$dir_id=sanitize_text_field($_REQUEST['dir_id']);}
	$id=$dir_id;
	$dir_addedit_contactustitle='';
	if($dir_addedit_contactustitle==""){$dir_addedit_contactustitle= esc_html__( 'Contact Us', 'listinghub' );}	
?>
<div class="bootstrap-wrapper  "id="popup-contact" >		
	<div class="container" >
		<div class="row" >
			<div class="col-md-12">
				<div class="modal-header">
					<h5 class="modal-title"><?php echo esc_html($dir_addedit_contactustitle);?></h5>	
					<div class="ml-2" id="update_message_popup"></div> 
					<button type="button" onclick="listinghub_contact_close();" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<?php
						include( ep_listinghub_template. 'listing/contact-form.php');						
					?>
				</div>
				<div class="modal-footer">					
					<button type="button" class="btn btn-small-ar col-md-6 " onclick="listinghub_contact_close();" ><?php  esc_html_e( 'Close', 'listinghub' ); ?></button>				
					<button type="button" class="btn btn-small-ar col-md-6 ml-2"  onclick="listinghub_contact_send_message_iv();" ><?php  esc_html_e( 'Send', 'listinghub' ); ?></button>							
				</div>					
			</div>				
		</div>	
	</div>	
</div>		
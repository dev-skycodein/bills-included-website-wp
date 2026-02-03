<?php
	include( explode( "wp-content" , __FILE__ )[0] . "wp-load.php" );	
	
	$dir_id=0; if(isset($_REQUEST['dir_id'])){$dir_id=sanitize_text_field($_REQUEST['dir_id']);}
	$id=$dir_id;	
	$dir_addedit_claimtitle=get_option('dir_addedit_claimtitle');
	if($dir_addedit_claimtitle==""){$dir_addedit_claimtitle=esc_html__('Report','listinghub');}
	?>
<div class="bootstrap-wrapper">
	<div class="container">	
		<div class="row" >
			<div class="col-md-12">		
		
						<div class="modal-header">
							<h5 class="modal-title" id="exampleModalLabel"><?php echo esc_html($dir_addedit_claimtitle); ?></h5>
							<div  class="ml-2" id="update_message_claim"></div>
							<button onclick="listinghub_contact_close();" type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
						<div class="modal-body">
							<form action="#" id="message-claim" name="message-claim"    method="POST" >
								<div class="form-group row">
									<label class="col-md-4"  for="Name"><?php esc_html_e( 'Name', 'listinghub' ); ?></label>
									<input class="col-md-7 form-control"  id="subject" name ="subject" type="text">
								</div>
								<div class="form-group row">
									<label class="col-md-4"  for="eamil"><?php esc_html_e( 'Email', 'listinghub' ); ?></label>
									<input class="col-md-7 form-control"  name="email_address" id="email_address" type="email">
								</div>
								<div class="form-group row">
									<label class="col-md-4"  for="message"><?php esc_html_e( 'Message', 'listinghub' ); ?></label>
									<input type="hidden" name="dir_id" id="dir_id" value="<?php echo esc_html($id); ?>">
									<textarea class="col-md-7 form-control"  name="message-content" id="message-content"  cols="20" rows="5"></textarea>
								</div>
							</form>
							
						</div>
						<div class="modal-footer">
						
							<button onclick="listinghub_contact_close();" type="button" class="btn btn-small-ar" data-dismiss="modal"><?php esc_html_e( 'Close', 'listinghub' ); ?></button>
							<button type="button" onclick="send_message_claim();" class="btn btn-small-ar col-md-6 ml-2"><?php esc_html_e( 'Send Message', 'listinghub' ); ?></button>
						</div>
					</div>
				
		</div>
	</div>
</div>
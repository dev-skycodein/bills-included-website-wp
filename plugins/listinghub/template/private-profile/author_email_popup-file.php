<?php
	require_once( explode( "wp-content" , __FILE__ )[0] . "wp-load.php" );	
	wp_enqueue_style('bootstrap', ep_listinghub_URLPATH . 'admin/files/css/iv-bootstrap.css');
	$user_id=0; if(isset($_REQUEST['user_id'])){$user_id=sanitize_text_field($_REQUEST['user_id']);}	
	$dir_id=0; if(isset($_REQUEST['dir_id'])){$dir_id=sanitize_text_field($_REQUEST['dir_id']);}
?>
<div class="bootstrap-wrapper  "id="popup-contact" >		
	<div class="container" >
		<div class="row" >
			<div class="col-md-12">
				<div class="modal-header">
					<h4 class="modal-title"><?php esc_html_e('Message','listinghub'); ?></h4>	
					
					<button type="button" onclick="listinghub_contact_close();" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="col-md-12 col-sm-12">
							<form action="#" id="message-pop" name="candidate-message"   method="POST" >
								<div class="form-group  ">
									<label  for="Name"><?php  esc_html_e( 'Subject', 'listinghub' ); ?></label>
									<input  class=" form-control" id="subject" name ="subject" type="text">
								</div>
								<div class="form-group  ">
									<label  for="Name"><?php  esc_html_e( 'Email', 'listinghub' ); ?></label>
									<input  class=" form-control" id="email_address" name ="email_address" type="text">
								</div>
								<div class="form-group  ">
									<label  for="Name"><?php  esc_html_e( 'Phone#', 'listinghub' ); ?></label>
									<input  class=" form-control" id="visitorphone" name ="visitorphone" type="text">
								</div>
								<div class="form-group ">
									<label for="message" ><?php  esc_html_e( 'Message', 'listinghub' ); ?></label>
									<input type="hidden" name="user_id" id="user_id" value="<?php echo esc_attr($user_id);?>">	
									<input type="hidden" name="dir_id" id="dir_id" value="<?php echo esc_attr($dir_id);?>">									
									<textarea    name="message-content" id="message-content"  cols="20" rows="3"></textarea>
								</div>									
								
							</form>
						</div>
					</div>		
						<div class="row">
							<div id="update_message_popup" class="col-md-12"></div>
					   </div>
				</div>	
					<div class="modal-footer">
						
				
						<button type="button" class="btn btn-small-ar col-md-6 " onclick="listinghub_contact_close();" ><?php  esc_html_e( 'Close', 'listinghub' ); ?></button>				
						<button type="button" class="btn btn-small-ar col-md-6 ml-2"  onclick="listinghub_user_message();" ><?php  esc_html_e( 'Send', 'listinghub' ); ?></button>							
					</div>
			</div>				
		</div>	
	</div>	
</div>	
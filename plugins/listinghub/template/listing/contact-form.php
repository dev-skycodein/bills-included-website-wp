<form action="#" id="message-pop" name="message-pop"   method="POST" >
	<div class="form-group  ">
		<label   for="Name"><?php  esc_html_e( 'Name', 'listinghub' ); ?></label>
		<input  class="form-control" id="name" name ="name" type="text">
	</div>
	<div class="form-group  ">
		<label  for="Name"><?php  esc_html_e( 'Phone#', 'listinghub' ); ?></label>
		<input  class="form-control" id="visitorphone" name ="visitorphone" type="text">
	</div>
	<div class="form-group ">
		<label for="eamil" ><?php  esc_html_e( 'Email', 'listinghub' ); ?></label>
		<input class="form-control"  name="email_address" id="email_address" type="text">
	</div>
	<div class="form-group ">
		<label for="message"><?php  esc_html_e( 'Message', 'listinghub' ); ?></label>
		<input type="hidden" name="dir_id" id="dir_id" value="<?php echo esc_attr($id);?>">
		<textarea  class="col-md-12 form-control-textarea" name="message-content" id="message-content"  cols="20" rows="3"></textarea>
	</div>
</form>
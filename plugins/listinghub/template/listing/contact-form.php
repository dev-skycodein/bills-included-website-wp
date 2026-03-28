<form action="#" id="listinghub-contact-form" name="listinghub-contact-form" method="POST" class="listinghub-contact-form">
	<input type="hidden" name="dir_id" id="dir_id" value="<?php echo esc_attr( $id ); ?>">

	<div class="form-group">
		<label for="name"><?php esc_html_e( 'Name', 'listinghub' ); ?> <span class="text-danger">*</span></label>
		<input class="form-control" id="name" name="name" type="text" required autocomplete="name">
	</div>

	<div class="form-group">
		<label for="visitorphone"><?php esc_html_e( 'Phone', 'listinghub' ); ?></label>
		<input class="form-control" id="visitorphone" name="visitorphone" type="text" autocomplete="tel">
	</div>

	<div class="form-group">
		<label for="email_address"><?php esc_html_e( 'Email', 'listinghub' ); ?> <span class="text-danger">*</span></label>
		<input class="form-control" name="email_address" id="email_address" type="email" required autocomplete="email">
	</div>

	<div class="form-group">
		<label for="enquiry_move_when"><?php esc_html_e( 'When are you looking to move?', 'listinghub' ); ?> <span class="text-danger">*</span></label>
		<select class="form-control" name="enquiry_move_when" id="enquiry_move_when" required>
			<option value=""><?php esc_html_e( 'Select…', 'listinghub' ); ?></option>
			<option value="<?php echo esc_attr( __( 'Within 2 weeks', 'listinghub' ) ); ?>"><?php esc_html_e( 'Within 2 weeks', 'listinghub' ); ?></option>
			<option value="<?php echo esc_attr( __( 'Within 1 month', 'listinghub' ) ); ?>"><?php esc_html_e( 'Within 1 month', 'listinghub' ); ?></option>
			<option value="<?php echo esc_attr( __( '1-3 months', 'listinghub' ) ); ?>"><?php esc_html_e( '1-3 months', 'listinghub' ); ?></option>
			<option value="<?php echo esc_attr( __( '3+ months', 'listinghub' ) ); ?>"><?php esc_html_e( '3+ months', 'listinghub' ); ?></option>
			<option value="<?php echo esc_attr( __( 'Just browsing', 'listinghub' ) ); ?>"><?php esc_html_e( 'Just browsing', 'listinghub' ); ?></option>
		</select>
	</div>

	<div class="form-group">
		<label for="enquiry_budget"><?php esc_html_e( 'What is your monthly budget?', 'listinghub' ); ?> <span class="text-danger">*</span></label>
		<select class="form-control" name="enquiry_budget" id="enquiry_budget" required>
			<option value=""><?php esc_html_e( 'Select…', 'listinghub' ); ?></option>
			<option value="<?php echo esc_attr( __( 'Under £1,000', 'listinghub' ) ); ?>"><?php esc_html_e( 'Under £1,000', 'listinghub' ); ?></option>
			<option value="<?php echo esc_attr( __( '£1,000 - £1,500', 'listinghub' ) ); ?>"><?php esc_html_e( '£1,000 - £1,500', 'listinghub' ); ?></option>
			<option value="<?php echo esc_attr( __( '£1,500 - £2,000', 'listinghub' ) ); ?>"><?php esc_html_e( '£1,500 - £2,000', 'listinghub' ); ?></option>
			<option value="<?php echo esc_attr( __( '£2,000+', 'listinghub' ) ); ?>"><?php esc_html_e( '£2,000+', 'listinghub' ); ?></option>
		</select>
	</div>

	<div class="form-group">
		<label for="enquiry_bedrooms"><?php esc_html_e( 'How many bedrooms do you need?', 'listinghub' ); ?> <span class="text-danger">*</span></label>
		<select class="form-control" name="enquiry_bedrooms" id="enquiry_bedrooms" required>
			<option value=""><?php esc_html_e( 'Select…', 'listinghub' ); ?></option>
			<option value="<?php echo esc_attr( __( 'Studio', 'listinghub' ) ); ?>"><?php esc_html_e( 'Studio', 'listinghub' ); ?></option>
			<option value="<?php echo esc_attr( __( '1 bed', 'listinghub' ) ); ?>"><?php esc_html_e( '1 bed', 'listinghub' ); ?></option>
			<option value="<?php echo esc_attr( __( '2 bed', 'listinghub' ) ); ?>"><?php esc_html_e( '2 bed', 'listinghub' ); ?></option>
			<option value="<?php echo esc_attr( __( '3+ bed', 'listinghub' ) ); ?>"><?php esc_html_e( '3+ bed', 'listinghub' ); ?></option>
		</select>
	</div>
</form>

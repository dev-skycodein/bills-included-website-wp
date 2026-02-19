	<style>
	#listinghub_registration .content-real{
		max-width: 100% !important;
		background: transparent !important;
	}
	#listinghub_registration h3{
		text-align: left !important;
		margin: 40px 0 20px !important;
	}
	.login-row {
		display: flex;
		justify-content: space-between;
		gap: 10%;
		align-items: center;
		}
		.login-colmun{
		width: 45%;
		}
		img.login-logo {
			max-width: 60px;
		}
		#listinghub_registration button.uppercase{
			width: 160px;
			padding: 12px !important;
		}
		#listinghub_registration a#register-btn{
		padding: 14px !important;
		}
		#listinghub_registration .btn-custom{
		font-family: 'Poppins';
		font-weight: 300 !important;
		}
		#listinghub_registration .content-real{
		padding: 30px 20px 50px !important;
		}
		#listinghub_registration .form-control-solid {
			background: transparent !important;
			padding: 0;
		}
		#listinghub_registration .form-control-solid:focus {
			border: unset !important;
			border-bottom: 1px solid #808080 !important;
		}
		.login-image{
		border-radius: 10px !important;
		}
		.form-title{
			font-family: 'Font Website';
			margin: 20px 0 20px !important;
			font-size: 28px !important;
			color: #6c7a89 !important;
			line-height: 35px !important;
		}
		.payment_info{
			padding: 0 0 40px !important;
		}
		.package-switch {
            display: flex;
            gap: 40px;
            margin-top: 40px;
        }
        .package-switch a{
            color: #996afd;
        }
        .package-switch a:before{
            content: "";
            height: 14px;
            width: 14px;
            background: #ffffff;
            display: inline-block;
            border-radius: 50%;
            margin-right: 5px;
            border: .2px solid #996afd;
            margin-bottom: -1px;
        }
        .package-switch a.package-switch-item-active:before{
            background: #996afd;
            border: 3px solid #e8feff;
        }
		@media(max-width: 767px){
		.login-row{
			flex-direction: column;
		}
		.login-row .login-colmun{
			width: 100%;
		}
		.hide-mobile{
			display: none !important;
		}
		}
		@media(max-width: 500px){
		.form-actions.login-row{
			flex-direction: column;
		}
		.form-actions.login-row .login-colmun{
			justify-content: left !important;
		}
		}
	</style>

		<?php
		$package_id=get_user_meta(get_current_user_id(),'listinghub_package_id',true);
		if($package_id!=""){
			$post_p = get_post($package_id);
		}
		$renter = false;
		$landlord = false;
		if($post_p->post_title == "Renter"){
			$renter = true;
		}
		else if($post_p->post_title == "Lanlords" || $post_p->post_title == "Essential" || $post_p->post_title == "Enterprise"){
			$landlord = true;
		}

		global $wpdb;
		wp_enqueue_script("jquery");
		wp_enqueue_script('jquery-ui-core');
		wp_enqueue_script('jquery-ui-datepicker'); 
		wp_enqueue_style('bootstrap', ep_listinghub_URLPATH . 'admin/files/css/iv-bootstrap.css');
		wp_enqueue_style('listinghub_signup', ep_listinghub_URLPATH . 'admin/files/css/signup.css');
		wp_enqueue_script('bootstrap.min', ep_listinghub_URLPATH . 'admin/files/js/bootstrap.min-4.js');
		wp_enqueue_style('jquery-ui', ep_listinghub_URLPATH . 'admin/files/css/jquery-ui.css');
		wp_enqueue_style('datetimepicker', ep_listinghub_URLPATH . 'admin/files/css/jquery.datetimepicker.css');
		
		$api_currency= 'USD';
		if( get_option('listinghub_api_currency' )!=FALSE ) {
			$api_currency= get_option('listinghub_api_currency' );
		}
		if(isset($_REQUEST['payment_gateway'])){
			$payment_gateway=$_REQUEST['payment_gateway'];
		}
		$eprecaptcha_api=get_option( 'eprecaptcha_api');
		
		$iv_directories_pack='listinghub_pack';
		$sql=$wpdb->prepare("SELECT * FROM $wpdb->posts WHERE post_type =%s  and post_status='draft' ", $iv_directories_pack);
		$membership_pack = $wpdb->get_results($sql);
		$total_package=count($membership_pack);
		$package_id= 0;
		$main_class = new eplugins_listinghub;
		$iv_gateway='paypal-express';
		if( get_option( 'listinghub_payment_gateway' )!=FALSE ) {
			$iv_gateway = get_option('listinghub_payment_gateway');
			if($iv_gateway=='paypal-express'){
				$post_name='listinghub_paypal_setting';
				$row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->posts WHERE post_name = '%s' ", $post_name));
				$paypal_id='0';
				if(isset($row->ID )){
					$paypal_id= $row->ID;
				}
				$api_currency=get_post_meta($paypal_id, 'listinghub_paypal_api_currency', true);
			}
		}
		$package_id='';
		if(isset($_REQUEST['package_id'])){
			$package_id=$_REQUEST['package_id'];
			$recurring= get_post_meta($package_id, 'listinghub_package_recurring', true);
			if($recurring == 'on'){
				$package_amount=get_post_meta($package_id, 'listinghub_package_recurring_cost_initial', true);
				}else{
				$package_amount=get_post_meta($package_id, 'listinghub_package_cost',true);
			}
			if($package_amount=='' || $package_amount=='0' ){$iv_gateway='paypal-express';}
		}
		$form_meta_data= get_post_meta( $package_id,'listinghub_content',true);
		$row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->posts WHERE id = '%s' ",$package_id ));
		$package_name='';
		$package_amount='';
		if(isset($row->post_title)){
			$package_name=$row->post_title;
			$count =get_post_meta($package_id, 'listinghub_package_recurring_cycle_count', true);
			$package_name=$package_name;
			$package_amount=get_post_meta($package_id, 'listinghub_package_cost',true);
		}
		$renter_hide = '';
		$landord_hide = '';
		if($package_name == "Renter"){
			$renter_hide = 'display: none;';
		}
		if($package_name == "Lanlords" || $package_name == "Essential" || $package_name == "Enterprise​"){
			$landord_hide = 'display: none;';
		}
		$newpost_id='';
		$post_name='listinghub_stripe_setting';
		$row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->posts WHERE post_name = '%s' " ,$post_name));
		if(isset($row->ID )){
			$newpost_id= $row->ID;
		}
		$stripe_mode=get_post_meta( $newpost_id,'listinghub_stripe_mode',true);
		if($stripe_mode=='test'){
			$stripe_publishable =get_post_meta($newpost_id, 'listinghub_stripe_publishable_test',true);
			}else{
			$stripe_publishable =get_post_meta($newpost_id, 'listinghub_stripe_live_publishable_key',true);
		}
		
		if($total_package<1){$iv_gateway='paypal-express';}
	?>
	<div class="bootstrap-wrapper  mb-3">
		<div class="container mt-5 mb-5">
			<div class="login-row">
				<div class="login-colmun">
					<img class="login-logo" src="https://thebillsincluded.com/wp-content/uploads/2025/06/logo.png">
					<?php
					$package_get = $_GET['package_id'];
					if($package_get == '3076'){
						$package_acive1 = 'package-switch-item-active';
					}
					else {
						$package_acive1 = '';
					}
					if($package_get == '3309'){
						$package_acive2 = 'package-switch-item-active';
					}
					else {
						$package_acive2 = '';
					}
					if($package_get == ''){
						$d_none = 'display: none;';
					}
					?>
					<div class="package-switch" style="<?=$d_none?>">
						<div class="package-switch-item">
							<a class="<?=$package_acive1?>" href="<?php echo home_url(); ?>/registration/?package_id=3076">
								I'm a renter
							</a>
						</div>
						<div class="package-switch-item">
							<a class="<?=$package_acive2?>" href="<?php echo home_url(); ?>/registration/?package_id=3309">
								I'm a agency
							</a>
						</div>
					</div>
					<?php
					if($iv_gateway=='paypal-express'){
					?>
					<form id="listinghub_registration" name="listinghub_registration" class="form-horizontal" action="<?php  the_permalink() ?>?package_id=<?php echo esc_attr($package_id); ?>&payment_gateway=paypal&iv-submit-listing=register" method="post" role="form"  enctype="multipart/form-data">
					<?php
					}
					if($iv_gateway=='woocommerce'){
					?>
					<form id="listinghub_registration" name="listinghub_registration" class="form-horizontal" action="<?php  the_permalink() ?>?package_id=<?php echo esc_attr($package_id); ?>&payment_gateway=woocommerce&iv-submit-listing=register" method="post" role="form"  enctype="multipart/form-data">
					<?php
						}
					if($iv_gateway=='stripe'){?>
					<form id="listinghub_registration" name="listinghub_registration" class="form-horizontal" action="<?php  the_permalink() ?>?&package_id=<?php echo esc_attr($package_id); ?>&payment_gateway=stripe&iv-submit-stripe=register" method="post" role="form"  enctype="multipart/form-data">
						<input type="hidden" name="payment_gateway" id="payment_gateway" value="stripe">
						<input type="hidden" name="iv-submit-stripe" id="iv-submit-stripe" value="register">
						<?php
						}
					?>				
					<div class="border-bottom form-title toptitle ">Please enter your details <br>to create an account</div>
					
					<div class="row user_info">
					
						<div class="col-md-12  ">
							<?php
								if(isset($_REQUEST['message-error'])){?>
							<div class="row alert alert-info alert-dismissable" id='loading-2'><a class="panel-close close" data-dismiss="alert">x</a> <?php  echo $_GET['message-error'] ?></div>
							<?php
								}
							?>
							
							<!--
								For Form Validation we used plugins https://formvalidation.io/
								This is in line validation so you can add fields easily.
							-->
												
							
							<div class="text-center" id="loading"> </div>
							<div class="form-group row"  >
								<label for="text" class="col-md-12 control-label" style="display: none;"><?php   esc_html_e('User Name','listinghub');?><span class="chili"></span></label>
								<div class="col-md-12 mb-4">
									<input type="text"  name="iv_member_user_name" id="iv_member_user_name"  data-validation="length alphanumeric"
									data-validation-length="4-12" data-validation-error-msg="<?php   esc_html_e(' The user name has to be an alphanumeric value between 4-12 characters','listinghub');?>" class="form-control form-control-solid placeholder-no-fix" placeholder="<?php  esc_html_e('Enter User Name*','listinghub');?>"  alt="required">
								</div>
							</div>
							
							<div class="form-group row">
								<label for="email" class="col-md-12 control-label" style="display: none;"><?php   esc_html_e('Email Address','listinghub');?><span class="chili"></span></label>
								<div class="col-md-12 mb-4">
									<input type="email" name="iv_member_email" id="iv_member_email" data-validation="email"  class="form-control form-control-solid placeholder-no-fix" placeholder="<?php   esc_html_e('Enter email address*','listinghub');?>" data-validation-error-msg="<?php   esc_html_e('Please enter a valid email address','listinghub');?> " >
								</div>
							</div>
							
							<?php wp_nonce_field( 'signup1' ); ?>
							<div class="form-group row ">
								<label for="text" class="col-md-12 control-label" style="display: none;"><?php   esc_html_e('Password','listinghub');?><span class="chili"></span></label>
								<div class="col-md-12 mb-4">
									<div class="password-field">
										<input type="password" name="iv_member_password"  id="iv_member_password" class="form-control form-control-solid placeholder-no-fix"  placeholder="<?php   esc_html_e('Enter password*','listinghub');?>" data-validation="strength"
										data-validation-strength="2" data-validation-error-msg="<?php   esc_html_e('The password is not strong enough','listinghub');?>">
										<div class="eye-icons">
											<i class="fa fa-eye" aria-hidden="true" style="display: none;"></i>
											<i class="fa fa-eye-slash" aria-hidden="true" style=""></i>
										</div>
									</div>
								</div>
							</div>
							
								
								<?php
								$iv_membership_signup_profile_pic=get_option('listinghub_signup_profile_pic');
								if($iv_membership_signup_profile_pic=='' ){ $iv_membership_signup_profile_pic='yes';}	
								if($iv_membership_signup_profile_pic=='yes' ){
								?>
								<div class="form-group row " style="<?=$renter_hide?><?=$landord_hide?>">
									<label for="text" class="col-md-12 control-label" style="display: none;"><?php  esc_html_e('Profile Image','listinghub');?></label>
									<div class="col-md-12 mb-4">
										<input type="file" name="profilepicture"  id="profilepicture" size="25" class="form-input " />
									</div>
								</div>
								<?php
								}
							?>
							
					
							<?php
							$i=1;
							$default_fields = array();
							$default_fields=get_option('listinghub_profile_fields');
							$sign_up_array=get_option( 'listinghub_signup_fields');
							$require_array=get_option( 'listinghub_signup_require');
							if(is_array($default_fields)){
								foreach ( $default_fields as $field_key => $field_value ) {
									$sign_up='no';
									if(isset($sign_up_array[$field_key]) && $sign_up_array[$field_key] == 'yes') {
										$sign_up='yes';
									}
									$require='no';
									if(isset($require_array[$field_key]) && $require_array[$field_key] == 'yes') {
										$require='yes';
									}
									if($sign_up=='yes--'){
									?>
									<div class="form-group row">
										<label  class="col-md-4 control-label" ><?php echo esc_html($field_value); ?><span class="<?php echo($require=='yes'?'chili':''); ?>"></span></label>
										<div class="col-md-8">
											<input type="text"  name="<?php echo esc_html($field_key);?>" <?php echo($require=='yes'?'data-validation="length" data-validation-length="2-100"':''); ?>
											class="form-control form-control-solid placeholder-no-fix" placeholder="<?php esc_html_e('Enter', 'listinghub');?><?php echo esc_html($field_value);?>" >
										</div>
									</div>
									<?php
									}
									if($renter_hide != ""){
										if($field_key != "listing_title"){
											echo  $main_class->listinghub_check_field_input_access_signup($field_key, $field_value);
										}
									}
									else{
										echo  $main_class->listinghub_check_field_input_access_signup($field_key, $field_value);
									}
								}
							}
						?>
							
							<?php							
							$total_package = count($membership_pack);
							if($total_package<1){		
							?>							
								<div class="row form-group" id="nopaymentform">
									<input type="hidden" name="reg_error" id="reg_error" value="yes">
									<input type="hidden" name="package_id" id="package_id" value="0">
									<input type="hidden" name="return_page" id="return_page" value="<?php  the_permalink() ?>">
										<div class="col-md-4"> </div>
										<div class="col-md-8">
										<div id="errormessage" class="alert alert-danger mt-2 displaynone" role="alert"></div>
											<div id="paypal-button">
												<div id="loading-3" class="displaynone"  ><img src='<?php echo ep_listinghub_URLPATH. 'admin/files/images/loader.gif'; ?>' /></div>
												<?php
												if($eprecaptcha_api==''){
												?>
													<button  id="submit_listinghub_payment" name="submit_listinghub_payment"  type="submit" class="btn btn-secondary"  >												
														<?php  esc_html_e('Submit','listinghub');?>
													</button>
												<?php
												}else{
												?>
													<button  id="submit_listinghub_payment" name="submit_listinghub_payment"  class="btn btn-secondary g-recaptcha" data-sitekey="<?php echo esc_html($eprecaptcha_api); ?>"  data-callback='listinghub_epluginrecaptchaSubmit' data-action='submit' >
														<?php  esc_html_e('Submit','listinghub');?>
													</button>
												<?php
												}
												?>
												
											</div>
										</div>
									</div>
							<?php
							}
							?>
							<input type="hidden" name="hidden_form_name" id="hidden_form_name" value="listinghub_registration">
						</div>
					
					</div>
					<?php
					
					if($total_package>0){
					?>
					<div id="employer-div">			
						<div class="border-bottom pb-4 mb-3 toptitle" style="<?=$renter_hide?><?=$landord_hide?>"><?php esc_html_e('Payment Info','listinghub');?></div>
						<div class="row payment_info">
							<div class="col-md-12 ">
								<?php
									if($iv_gateway=='paypal-express'){
										include(ep_listinghub_template.'signup/paypal_form_2.php');
									}
									if($iv_gateway=='stripe'){
										include(ep_listinghub_template.'signup/iv_stripe_form_2.php');
									}
									if($iv_gateway=='woocommerce'){
										include(ep_listinghub_template.'signup/woocommerce.php');
									}
								?>
							</div>
						</div>
					</div>
					<?php
					}
					?>
				</form>
			</div>
			<div class="login-colmun hide-mobile" style="display: flex;justify-content: center;">
		<img class="login-image" src="https://thebillsincluded.com/wp-content/uploads/2025/06/my-account.jpg">
		</div>
			</div>
			</div>
		</div>
		<?php
			
			wp_enqueue_script('jquery.form-validator', ep_listinghub_URLPATH . 'admin/files/js/jquery.form-validator.js');
			wp_enqueue_script('listinghub_signup', ep_listinghub_URLPATH . 'admin/files/js/signup.js');
			wp_localize_script('listinghub_signup', 'dirpro_data', array(
			'ajaxurl' 			=> admin_url( 'admin-ajax.php' ),
			'loader_image'=>'<img src="'.ep_listinghub_URLPATH. 'admin/files/images/loader.gif" />',
			'loader_image2'=>'<img src="'.ep_listinghub_URLPATH. 'admin/files/images/old-loader.gif" />',
			'right_icon'=>'<img src="'.ep_listinghub_URLPATH. 'admin/files/images/right_icon.png" />',
			'wrong_16x16'=>'<img src="'.ep_listinghub_URLPATH. 'admin/files/images/wrong_16x16.png" />',
			'stripe_publishable'=>$stripe_publishable,
			'package_amount'=>$package_amount,
			'api_currency'=>$api_currency,
			'iv_gateway'=>$iv_gateway,
			'total_package'=> $total_package,
			'errormessage'=>esc_html__("Please complete the form",'listinghub'),
			'HideCoupon'=>esc_html__("Hide Coupon",'listinghub'),
			'Havecoupon'=> esc_html__("Have Coupon",'listinghub'),
			'dirwpnonce'=> wp_create_nonce("signup"),
			'signup'=> wp_create_nonce("signup"),
			) );


		if($eprecaptcha_api!=''){	
			wp_register_script( 'rechaptcha', 'https://www.google.com/recaptcha/api.js?render='.$eprecaptcha_api, null, null, true );
			wp_enqueue_script('rechaptcha');
		}
		

	?>	

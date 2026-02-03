<div class="edit-profile-tab">
    <div class="mt-3 row ">	
	<div class="col-md-6">
		<span class="toptitle-sub"><?php esc_html_e('Profile', 'listinghub'); ?></span>
	</div>
	<div class="col-md-6">
		<ul class="nav nav-pills  float-right" id="pills-tab" role="tablist">
			<li class="nav-item">
				 <a class="nav-link active" id="pills-all-tab" data-toggle="pill" href="#per_info" role="tab" aria-controls="pills-home" aria-selected="true"><?php   esc_html_e('Personal Info','listinghub');?> </a>
			</li>
			<li class="nav-item">
				 <a class="nav-link " id="pills-add-tab" data-toggle="pill" href="#password_tab" role="tab" aria-controls="pills-home" ><?php   esc_html_e('Change Password','listinghub');?></a>
				 
				
			</li>
			
		</ul>
	</div>
	<div class="col-md-12"> <p class="border-bottom"> </p></div>
</div>		



<div class="tab-content">
	<div class="tab-pane active" id="per_info">
		<form role="form" name="profile_setting_form" id="profile_setting_form" action="#">
			<?php
			include('author-edit-profile.php');				
				
			?>
		</form>
	</div>
	<div class="tab-pane" id="password_tab">
		<form action="" name="pass_word" id="pass_word">
			<div class="form-group">
				<label class="control-label"><?php   esc_html_e('Current Password','listinghub');?> </label>
				<div class="password-field">
					<input type="password" id="c_pass" name="c_pass" class="form-control"/>
					<div class="eye-icons">
						<i class="fa fa-eye" aria-hidden="true"></i>
						<i class="fa fa-eye-slash" aria-hidden="true"></i>
					</div>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label"><?php   esc_html_e('New Password','listinghub');?> </label>
				<div class="password-field">
					<input type="password" id="n_pass" name="n_pass" class="form-control"/>
					<div class="eye-icons">
						<i class="fa fa-eye" aria-hidden="true"></i>
						<i class="fa fa-eye-slash" aria-hidden="true"></i>
					</div>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label"><?php   esc_html_e('Re-type New Password','listinghub');?> </label>
				<div class="password-field">
					<input type="password" id="r_pass" name="r_pass" class="form-control"/>
					<div class="eye-icons">
						<i class="fa fa-eye" aria-hidden="true"></i>
						<i class="fa fa-eye-slash" aria-hidden="true"></i>
					</div>
				</div>
			</div>
			<div class="margin-top-10">
				<div class="" id="update_message_pass"></div>
				<button type="button" onclick="listinghub_update_password();"  class="btn green-haze"><?php   esc_html_e('Change Password','listinghub');?> </button>
			</div>
		</form>
	</div>
</div>
</div>
<!-- END PROFILE CONTENT -->
<?php
	$save_address=get_user_meta($current_user->ID ,'address',true);
	$my_theme = wp_get_theme();
	$theme_name= strtolower($my_theme->get( 'Name' ));
	wp_enqueue_script('listinghub_add-edit-listing', ep_listinghub_URLPATH . 'admin/files/js/add-edit-listing.js');
	wp_localize_script('listinghub_add-edit-listing', 'realpro_data', array(
	'ajaxurl' 					=> admin_url( 'admin-ajax.php' ),
	'loading_image'			=> '<img src="'.ep_listinghub_URLPATH.'admin/files/images/loader.gif">',
	'current_user_id'		=>get_current_user_id(),
	'Set_Feature_Image'	=> esc_html__('Set Feature Image','listinghub'),
	'Set_plan_Image'		=> esc_html__('Set Image ','listinghub'),
	'Set_Event_Image'		=> esc_html__(' Set Image ','listinghub'),
	'Gallery Images'		=> esc_html__('Gallery Images','listinghub'),
	'permalink'				=> get_permalink(),
	'save_address'			=>$save_address,
	'dirwpnonce'			=> wp_create_nonce("addlisting"),
	'theme_name'			=> $theme_name,
	) );
?>		  
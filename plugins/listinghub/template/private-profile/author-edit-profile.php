<div class="row upload-avatar-row"> 
	<div class="col-md-2">			
		<div class="upload-avatar">
			<span class="avatar" id="profile_image_main">
				<?php
					$iv_profile_pic_url=get_user_meta($current_user->ID, 'listinghub_profile_pic_thum',true);
					if($iv_profile_pic_url!=''){ ?>
					<img src="<?php echo esc_url($iv_profile_pic_url); ?>">
					<?php
						}else{
						echo'	 <img src="'. ep_listinghub_URLPATH.'assets/images/company-enterprise.png">';
					}
				?>
			</span>
		</div>
	</div>		
	<div class="col-md-10">
		<button type="button" onclick="listinghub_edit_profile_image('profile_image_main');"  class="btn btn-small-ar">
		<?php esc_html_e('Change Logo','listinghub'); ?> </button>
	</div>
	</div>
	<div class="row">
	<div class="col-md-12 mt-3">	
		<?php
			$topbanner=get_user_meta($current_user->ID,'topbanner', true);
			if(trim($topbanner)!=''){					
				$default_image_banner = wp_get_attachment_url($topbanner );
				}else{
				if(get_option('listinghub_banner_defaultimage')!=''){
					$default_image_banner= wp_get_attachment_image_src(get_option('listinghub_banner_defaultimage'),'large');
					if(isset($default_image_banner[0])){									
						$default_image_banner=$default_image_banner[0] ;			
					}
					}else{
					$default_image_banner=ep_listinghub_URLPATH."/assets/images/banner.png";
				}
			}
		?>
		<span class="avatar" id="banner_image_main">
			<?php					
				echo'<img class="col-md-12 img-responsive rounded" src="'. esc_url($default_image_banner).'">';
			?>
		</span>
	</div>		
	<div class="col-md-8 mt-3">	
		<button type="button" onclick="listinghub_edit_banner_image('banner_image_main');"  class="btn btn-small-ar">
		<?php esc_html_e('Change Banner [best fit: 1200 X 400]','listinghub'); ?> </button>
	</div>
	<input type="hidden" name="topbanner_url" id="topbanner_url" value="<?php echo esc_url($default_image_banner); ?>">	
	<input type="hidden" name="topbanner" id="topbanner_id" value="<?php echo esc_attr($topbanner); ?>">
	
	<div class="form-group">
		<label for="first_name"><?php esc_html_e('First Name', 'listinghub'); ?></label>
		<input type="text" class="form-control" name="first_name" id="first_name" value="<?php echo esc_attr($current_user->user_firstname); ?>">
	</div>

	<div class="form-group">
		<label for="last_name"><?php esc_html_e('Last Name', 'listinghub'); ?></label>
		<input type="text" class="form-control" name="last_name" id="last_name" value="<?php echo esc_attr($current_user->user_lastname); ?>">
	</div>

	<div class="form-group">
		<label for="user_email"><?php esc_html_e('Email Address', 'listinghub'); ?></label>
		<input type="email" class="form-control" name="user_email" id="user_email" value="<?php echo esc_attr($current_user->user_email); ?>">
		<?php
		$pending_email = get_user_meta($current_user->ID, '_pending_new_email', true);

		if ( ! empty($pending_email) ) {
			// Display the pending email notice with a cancel option
			echo '<div class="pending-email-notice" style="padding:10px; background:#fff3cd; color:#856404; border:1px solid #ffeeba; border-radius:4px; margin-bottom:15px;">';
			echo sprintf(
				esc_html__('A verification email has already been sent to %s. Please check your inbox to confirm it.', 'listinghub'),
				esc_html($pending_email)
			);
		
			// Add a cancel link/button
			echo ' <a href="#" id="cancel-email-verification" style="color: #007bff; text-decoration: none;">' . esc_html__('Cancel the email verification', 'listinghub') . '</a>';
			echo '</div>';
		}
		?>
		
		<script type="text/javascript">
			document.getElementById('cancel-email-verification').addEventListener('click', function(e) {
				e.preventDefault();
		
				// Confirm the cancel action
				if (confirm('<?php esc_html_e( "Are you sure you want to cancel the email verification?", "listinghub" ); ?>')) {
					// Send a request to cancel the pending email verification
					var xhr = new XMLHttpRequest();
					xhr.open('POST', '<?php echo admin_url('admin-ajax.php'); ?>', true);
					xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
					xhr.onreadystatechange = function () {
						if (xhr.readyState == 4 && xhr.status == 200) {
							// Remove the pending email notice
							location.reload(); // Reload to reflect changes
						}
					};
					xhr.send('action=cancel_email_verification');
				}
			});
		</script>
		
	</div>

		
		
	<?php
		$default_fields = array();
		$field_set=get_option('listinghub_profile_fields' );
		if($field_set!=""){
			$default_fields=get_option('listinghub_profile_fields' );
			}else{
			$default_fields['full_name']='Full Name';	
			$default_fields['tagline']='Tag line';
			$default_fields['company_since']='Estd Since';
			$default_fields['team_size']='Team Size';									
			$default_fields['phone']='Phone Number';			
			$default_fields['address']='Address';
			$default_fields['city']='City';
			$default_fields['postcode']='Postcode';
			$default_fields['state']='State';
			$default_fields['country']='Country';	
			$default_fields['website']='Website Url';
			$default_fields['description']='About';
		}
		
		$field_type_opt=  get_option( 'listinghub_field_type' );
		$field_type_roles=  	get_option( 'listinghub_field_type_roles' );			
		$myaccount_fields_array=  get_option( 'listinghub_myaccount_fields' );							
		$user = new WP_User( $current_user->ID );
		$i=1;
		
		foreach ( $default_fields as $field_key => $field_value ) { 		
			if(isset($myaccount_fields_array[$field_key])){  				
				
				if($myaccount_fields_array[$field_key]=='yes'){ 
					$role_access='no';
					if(in_array('all',$field_type_roles[$field_key] )){
						$role_access='yes';
					}
					if(in_array('administrator',$field_type_roles[$field_key] )){
						$role_access='yes';
					}
					if(in_array('employer',$field_type_roles[$field_key] )){
						$role_access='yes';
					}
					if ( !empty( $user->roles ) && is_array( $user->roles ) ) {
						foreach ( $user->roles as $role ){
							if(in_array($role,$field_type_roles[$field_key] )){
								$role_access='yes';
							}
							
						}
					}	
					if($role_access=='yes'){
						echo  $main_class->listinghub_check_field_input_access($field_key, $field_value, 'myaccount', $current_user->ID );
					}
				}
				}else{ 
				echo  $main_class->listinghub_check_field_input_access($field_key, $field_value, 'myaccount', $current_user->ID );
			}
		}
	?>
</div>
<div class="margin-top-10">
	<div class="" id="update_message"></div>
	<input type="hidden" name="latitude" id="latitude" value="<?php echo esc_attr(get_user_meta($current_user->ID,'latitude ',true)); ?>">
	<input type="hidden" name="longitude" id="longitude" value="<?php echo esc_attr(get_user_meta($current_user->ID,'longitude',true)); ?>">
	<button type="button" onclick="listinghub_update_profile_setting();"  class="btn green-haze"><?php   esc_html_e('Save Changes','listinghub');
<?php
	$ii=1;
	global $wp_roles;
	
	
?> 
<div class="row">
			<div class="col-md-12 table-responsive mb-4">

		<h4><?php esc_html_e('Registration / User Profile Fields','listinghub');?></h4>
		<form id="profile_fields_signup" name="profile_fields_signup" class="form-horizontal" role="form" onsubmit="return false;">
			<table id="all_fieldsdatatable" name="all_fieldsdatatable"  class="display table" width="100%">					
				<thead>
					<tr>
						<th > <?php  esc_html_e('Input Detail','listinghub')	;?> </th>	
						<th > <?php  esc_html_e('User Role & Section ','listinghub');?></th>	
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>
							<?php  esc_html_e('User Profile Pic Uploader','listinghub');
								$listinghub_signup_profile_pic=get_option('listinghub_signup_profile_pic');
								if($listinghub_signup_profile_pic=='' ){ $listinghub_signup_profile_pic='yes';	}		
							?>
						</td>						
						<td> <label>
							<input type="checkbox" name="signup_profile_pic" id="signup_profile_pic" value="yes" <?php echo($listinghub_signup_profile_pic=='yes'? 'checked':'' );?> >
							 
							 <?php  esc_html_e('Registration','listinghub');?>
						</label></td>
						
										
					</tr>	
					<tr>
						<td>
							<?php  esc_html_e('Terms CheckBox','listinghub')	;
								$listinghub_payment_terms=get_option('listinghub_payment_terms');
								if($listinghub_payment_terms=='' ){ $listinghub_payment_terms='yes';	}
							?>
						</td>
						
						<td> <label>
							<input type="checkbox" name="listinghub_payment_terms" id="listinghub_payment_terms" value="yes" <?php echo($listinghub_payment_terms=='yes'? 'checked':'' );?> >
							<?php  esc_html_e('Registration','listinghub');?>
						</label>						
						</td>
										
					</tr>	
					<tr>
						<td>
							<?php  esc_html_e('Coupon Buton','listinghub')	;
								$listinghub_payment_coupon=get_option('_listinghub_payment_coupon');
								if($listinghub_payment_coupon=='' ){ $listinghub_payment_coupon='yes';	}
							?>
						</td>	
						<td> <label>
							<input type="checkbox" name="listinghub_payment_coupon" id="listinghub_payment_coupon" value="yes" <?php echo($listinghub_payment_coupon=='yes'? 'checked':'' );?> >
							<?php  esc_html_e('Registration','listinghub');?>
						</label></td>
						
						
					</tr>	
					<?php
						
						$default_fields = array();
						$field_set=get_option('listinghub_profile_fields' );
						if($field_set!=""){
							$default_fields=$field_set;
							}else{									
							$default_fields['full_name']='Full Name';	
							$default_fields['tagline']='Tag line';
							$default_fields['company_since']='Estd Since';
							$default_fields['team_size']='Team Size';									
							$default_fields['phone']='Phone Number';
							$default_fields['mobile']='Mobile Number';
							$default_fields['address']='Address';
							$default_fields['city']='City';
							$default_fields['postcode']='Postcode';
							$default_fields['state']='State';
							$default_fields['country']='Country';										
							$default_fields['listing_title']='listing title';
							$default_fields['website']='Website Url';
							$default_fields['description']='About';
						}
						$i=0;								
						$field_type_opt=  get_option( 'listinghub_field_type' );
						if($field_type_opt!=''){
							$field_type=$field_type_opt;
							}else{	
							$field_type= array();
							$field_type['full_name']='text';								
							$field_type['company_since']='datepicker';
							$field_type['team_size']='text';									
							$field_type['phone']='text';
							$field_type['mobile']='text';
							$field_type['address']='text';
							$field_type['city']='text';
							$field_type['postcode']='text';
							$field_type['state']='text';
							$field_type['country']='text';										
							$field_type['listing_title']='text';	
							$field_type['website']='url';
							$field_type['description']='textarea';									
						}
						$field_type_value= get_option( 'listinghub_field_type_value' );
						if($field_type_value==''){
							$field_type_value=array();
							$field_type_value['gender']=esc_html__('Female,Male,Other', 'listinghub');	
						}
						$field_type_roles=  	get_option( 'listinghub_field_type_roles' );
						$sign_up_array=  get_option( 'listinghub_signup_fields' );
						$myaccount_fields_array=  get_option( 'listinghub_myaccount_fields' );
						$require_array=  get_option( 'listinghub_signup_require' );								
						foreach ( $default_fields as $field_key => $field_value ) {
							$sign_up='';									
							if(isset($sign_up_array[$field_key]) && $sign_up_array[$field_key] == 'yes') {
								$sign_up=$sign_up_array[$field_key] ;
							}
							$require='';
							if(isset($require_array[$field_key]) && $require_array[$field_key] == 'yes') {
								$require=$require_array[$field_key];
							}
							$myaccount_one='';									
							if(isset($myaccount_fields_array[$field_key]) && $myaccount_fields_array[$field_key] == 'yes') {
								$myaccount_one=$myaccount_fields_array[$field_key];
							}
						?>
						<tr  id="wpdatatablefield_<?php echo esc_attr($i);?>">
							<td >
								<div class="row mt-2">
									<label class="col-md-6 col-6"><?php  esc_html_e('Input Name','listinghub');?></label>
									<input type="text" class="form-control col-md-6 col-6 " name="meta_name[]" id="meta_name[]" value="<?php echo esc_attr($field_key); ?>"> 
								</div>
								<div class="row mt-2">
										<label class="col-md-6 col-6"><?php  esc_html_e('Label','listinghub')	;?></label>
										<input type="text" class="form-control col-md-6 col-6 " name="meta_label[]" id="meta_label[]" value="<?php echo esc_attr($field_value);?>" >
								</div>
								<div class="row mt-2" id="inputtypell_<?php echo esc_attr($i);?>">
									<label class="col-md-6 col-6"><?php  esc_html_e('Type','listinghub');?></label>
									<?php $field_type_saved= (isset($field_type[$field_key])?$field_type[$field_key]:'' );?>
									<select class="form-control col-md-6 col-6 " name="field_type[]" id="field_type[]">
										<option value="text" <?php echo ($field_type_saved=='text'? "selected":'');?> ><?php esc_html_e('Text','listinghub');?></option>
										<option value="textarea" <?php echo ($field_type_saved=='textarea'? "selected":'');?> ><?php esc_html_e('Text Area','listinghub');?></option>
										<option value="dropdown" <?php echo ($field_type_saved=='dropdown'? "selected":'');?> ><?php esc_html_e('Dropdown','listinghub');?></option>
										<option value="radio" <?php echo ($field_type_saved=='radio'? "selected":'');?> ><?php esc_html_e('Radio button','listinghub');?></option>
										<option value="datepicker" <?php echo ($field_type_saved=='datepicker'? "selected":'');?> ><?php esc_html_e('Date Picker','listinghub');?></option>
										<option value="checkbox" <?php echo ($field_type_saved=='checkbox'? "selected":'');?> ><?php esc_html_e('Checkbox','listinghub');?></option>
										<option value="url" <?php echo ($field_type_saved=='url'? "selected":'');?> ><?php esc_html_e('URL','listinghub');?></option>
									</select>
								</div>
								<div class="row mt-2">
									<label class="col-md-12 col-12"><?php  esc_html_e('Value[Dropdown,checkbox,Radio]','listinghub')	;?> </label>
									<textarea class="form-control ml-2 mr-2 " rows="3" name="field_type_value[]" id="field_type_value[]" placeholder="<?php  esc_html_e('Separated by comma','listinghub');?> "><?php echo esc_attr((isset($field_type_value[$field_key])?$field_type_value[$field_key]:''));?></textarea>
								</div>
								
								<?php
									if($i>=1){
									?>
									<div class="row mt-2">
										<button class="btn btn-danger btn-sm ml-2" onclick="return listinghub_remove_field('<?php echo esc_attr($i); ?>');"><span class="dashicons dashicons-trash ml-1"></span></button>
									</div>
									<?php
									}
								?>
							</td>							
							
							<td >	
								<div class="row">
									<div class="col-12 col-md-12 col-lg-6 mb-2">
										<div id="roleall_<?php echo esc_attr($i);?>">
										<?php $field_user_role_saved= (isset($field_type_roles[$field_key])?$field_type_roles[$field_key]:'' );
											if($field_user_role_saved==''){$field_user_role_saved=array('all');}
										?>									
											<select name="field_user_role<?php echo esc_attr($i);?>[]" multiple="multiple" class="form-control col-md-12 col-12 " size="7">
												<option value="all" <?php echo (in_array('all',$field_user_role_saved)? "selected":'');?>> 
												<?php esc_html_e('All Users','listinghub');?> </option>
												
												<?php										
													foreach ( $wp_roles->roles as $key_role=>$value_role ){?>
													<option value="<?php echo esc_attr($key_role); ?>" <?php echo (in_array($key_role,$field_user_role_saved)? "selected":'');?>> <?php echo esc_html($key_role);?> </option>
													<?php												
													}
												?>
											</select>
										</div>	
									</div>
									<div class="col-12 col-md-12 col-lg-6 ">
										<p>
											<label>
												<input type="checkbox" name="signup<?php echo esc_attr($i); ?>" id="signup<?php echo esc_attr($i); ?>" value="yes" <?php echo($sign_up=='yes'? 'checked':'' );?> >
												
												<?php  esc_html_e('Registration','listinghub')	;?> 
											</label>
											</p>
											<p>
											<label>
												<input type="checkbox" name="myaccountprofile<?php echo esc_attr($i); ?>" id="myaccountprofile<?php echo esc_attr($i); ?>" value="yes" <?php echo ($myaccount_one=='yes'? 'checked':'' );?>  class="text-center">
												
												<?php  esc_html_e('My Account/Profile','listinghub')	;?> 
											</label>
											</p>
											<p>
											<label>
												<input type="checkbox" name="srequire<?php echo esc_attr($i); ?>" id="srequire<?php echo esc_attr($i); ?>" value="yes" <?php echo ($require=='yes'? 'checked':'' );?>  class="text-center">
												<?php  esc_html_e('Require','listinghub')	;?> 
											</label>
											</p>
									</div>
								</div>								
							</td>	
						</tr>	
						<?php
							$i++;
						}
						$listinghub_signup_fields_serial=$i;
					?>
				</tbody>			
			</table>
			<div id="custom_field_div">
			</div>
			<div class="col-xs-12">
				<div id="success_message_profile"></div>
				<button class="button button-primary" onclick="return listinghub_update_profile_signup_fields();"><?php esc_html_e('Update Fields','listinghub');?> </button>
				<button class="btn btn-warning " onclick="return listinghub_add_profile_field();"><?php esc_html_e('Add More Field','listinghub');?></button>
			</div>
		</form>
	</div>
</div>
<?php
	wp_enqueue_script('eplugins_listinghub-dashboard5', ep_listinghub_URLPATH.'admin/files/js/profile-fields.js', array('jquery'), $ver = true, true );
	wp_localize_script('eplugins_listinghub-dashboard5', 'profile_data', array( 			'ajaxurl' 			=> admin_url( 'admin-ajax.php' ),
	'loading_image'		=> '<img src="'.ep_listinghub_URLPATH.'admin/files/images/loader.gif">',
	'redirecturl'	=>  ep_listinghub_ADMINPATH.'admin.php?&page=listinghub-profile-fields',
	'adminnonce'=> wp_create_nonce("admin"),
	'pii'	=>$ii,
	'pi'	=> $i,
	'signup_field_serial'	=> $listinghub_signup_fields_serial, 
	"sProcessing"=>  esc_html__('Processing','listinghub'),
	"sSearch"=>   esc_html__('Search','listinghub'),
	"lengthMenu"=>   esc_html__('Display _MENU_ records per page','listinghub'),
	"zeroRecords"=>  esc_html__('Nothing found - sorry','listinghub'),
	"info"=>  esc_html__('Showing page _PAGE_ of _PAGES_','listinghub'),
	"infoEmpty"=>   esc_html__('No records available','listinghub'),
	"infoFiltered"=>  esc_html__('(filtered from _MAX_ total records)','listinghub'),
	"sFirst"=> esc_html__('First','listinghub'),
	"sLast"=>  esc_html__('Last','listinghub'),
	"sNext"=>     esc_html__('Next','listinghub'),
	"sPrevious"=>  esc_html__('Previous','listinghub'),
	) );
?>	
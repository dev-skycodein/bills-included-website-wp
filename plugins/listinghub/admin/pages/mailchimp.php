
						<div class="form-group  row">
							<label  class="col-md-3  control-label"> </label>
							<div class="col-md-4">
								<button type="button" onclick="return  iv_update_mailchamp_settings();" class="button button-primary"><?php esc_html_e( 'Update MailChimp Setting', 'listinghub' );?></button>					
							</div>							
						</div>						
						<form class="form-horizontal" role="form"  name='mailchimp_settings' id='mailchimp_settings'>
							<div class="form-group row">
								<label  class="col-md-3 col-xs-6 col-sm-6 control-label"> <?php esc_html_e( 'MailChimp API Key :', 'listinghub' );?> </label>
								<div class="col-md-4 col-xs-6 col-sm-6">
									<?php
										$iv_mailchimp_api_key='';
										if( get_option( 'listinghub_mailchimp_api_key' )==FALSE ) {
											$iv_mailchimp_api_key = get_option('listinghub_mailchimp_api_key');						 
											}else{
											$iv_mailchimp_api_key = get_option('listinghub_mailchimp_api_key');								
										}
									?>
									<input type="text" class="form-control" id="listinghub_mailchimp_api_key" name="listinghub_mailchimp_api_key" value="<?php echo esc_html($iv_mailchimp_api_key); ?>" placeholder="">
									<a href="<?php echo esc_url('www.admin.mailchimp.com/account/api'); ?>" target="_blank"><?php  esc_html_e('Get your API key here','listinghub');?>.</a>
								</div>
							</div>
							<div class="form-group row">
								<label  class="col-md-3 col-xs-6 col-sm-6  control-label"> </label>
								<div class="col-md-4 col-xs-6 col-sm-6 ">														
								</div>
							</div>
							<div class="form-group row">	
								<label  class="col-md-3 col-xs-6 col-sm-6 control-label"><?php esc_html_e( 'Mailchimp List ', 'listinghub' );?>: </label>
								<div class="col-md-4 col-xs-6 col-sm-6">
									<?php 
										if( ! class_exists('MailChimp' ) ) {
											require_once(ep_listinghub_DIR . '/inc/MailChimp.php');
										}
										$iv_mailchimp_api_key='';
										if( get_option( 'listinghub_mailchimp_api_key' )==FALSE ) {
											$iv_mailchimp_api_key = get_option('listinghub_mailchimp_api_key');						 
											}else{
											$iv_mailchimp_api_key = get_option('listinghub_mailchimp_api_key');								
										}	
										echo '<select class="form-control" id="listinghub_mailchimp_list" name="listinghub_mailchimp_list">';
										if($iv_mailchimp_api_key!=''){
											$iv_form_membership_mailchimp_list=get_option( 'listinghub_mailchimp_list'); 
											$api = new MailChimp($iv_mailchimp_api_key);
											$list_data = $api->get('lists');
											if($list_data){
												foreach($list_data['lists'] as $key => $list):
												$lists[$list['id']] = $list['name'];
												echo '<option value="'.$list['id'].'" '.($iv_form_membership_mailchimp_list==$list['id']? 'selected': '').'>'.$list['name'].' </option>';
												endforeach;
												}else{
												echo '<option value="" > Not Available</option>';
											}													
										}													
										echo'</select>';
									?>
								</div>
							</div>
							<div class=" col-md-7  bs-callout bs-callout-info">		
								<?php esc_html_e( 'Signup user email address will go to the mailchimp list.', 'listinghub' );?>
							</div>
							<div class="clearfix"></div>
						</form>
						<div class="form-group  row">
							<label  class="col-md-3  control-label"> </label>
							<div class="col-md-4">
								<button type="button" onclick="return  iv_update_mailchamp_settings();" class="button button-primary"><?php esc_html_e( 'Update MailChimp Setting', 'listinghub' );?></button>					
							</div>							
						</div>	
					
				
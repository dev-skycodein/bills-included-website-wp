<?php
	global $wpdb;
	global $current_user;
	$currencyCode= get_option('listinghub_api_currency');
	$iv_gateway = get_option('listinghub_payment_gateway');
	if($iv_gateway=='woocommerce'){
		if ( class_exists( 'WooCommerce' ) ) {
			$api_currency= get_option( 'woocommerce_currency' );
			$api_currency= get_woocommerce_currency_symbol( $api_currency );
			$currencyCode=$api_currency;
		}
	}
?>
<div class="mt-3 row ">	
	<div class="col-md-4">
		<span class="toptitle-sub"><?php esc_html_e('Membership', 'listinghub'); ?></span>
	</div>
	<div class="col-md-8">
		<ul class="nav nav-pills  float-right" id="pills-tab" role="tablist">
			<li class="nav-item">
				<a class="nav-link active" id="pills-all-tab" data-toggle="pill" href="#tab_current" role="tab" aria-controls="pills-home" aria-selected="true"><?php    esc_html_e('Current','listinghub');?></a>
			</li>
			<li class="nav-item">
				<a class="nav-link " id="pills-add-tab" data-toggle="pill" href="#tab_upgrade" role="tab" aria-controls="pills-home" ><?php    esc_html_e('Upgrade','listinghub');?></a>
			</li>
			<li class="nav-item">
				<a class="nav-link " id="pills-add-tab" data-toggle="pill" href="#tab_cancel" role="tab" aria-controls="pills-home" ><?php    esc_html_e('Cancel','listinghub');?></a>
			</li>
			
		</ul>
	</div>
	<div class="col-md-12"> <p class="border-bottom"> </p></div>
</div>		
<div class="tab-content">
	<div class="tab-pane active" id="tab_current">
		<?php
			global $wpdb, $post;
			$iv_gateway = get_option('listinghub_payment_gateway');
			$sql="SELECT * FROM $wpdb->posts WHERE post_type = 'listinghub_pack'  and post_status='draft' ";
			$membership_pack = $wpdb->get_results($sql);
			$total_package=count($membership_pack);
			$package_id=get_user_meta($current_user->ID,'listinghub_package_id',true);
			$iv_pac=$package_id;
		?>
		<table class="table">
			<tr>
				<td  width="60%">
					<?php    esc_html_e('Current Package','listinghub')	;?>
				</td>
				<td width="40%">
					<?php
						if($package_id!=""){
							$post_p = get_post($package_id);
							if(!empty($post_p)){
								$recurring_text='  ';
								if(get_post_meta($package_id, 'listinghub_package_cost', true)=='0' or get_post_meta($package_id, 'listinghub_package_cost', true)==""){
									$amount= esc_html__('Free','listinghub' );
									}else{
									$amount= $api_currency.' '. get_post_meta($package_id, 'listinghub_package_cost', true);
									$amount_only= get_post_meta($package_id, 'listinghub_package_cost', true);
								}
								$recurring_text=$amount;						
								$recurring= get_post_meta($package_id, 'listinghub_package_recurring', true);
								if($recurring == 'on'){
									$amount= $api_currency.' '. get_post_meta($package_id, 'listinghub_package_recurring_cost_initial', true);
									$amount_only= get_post_meta($package_id, 'listinghub_package_recurring_cost_initial', true);
									$count_arb=get_post_meta($package_id, 'listinghub_package_recurring_cycle_count', true);
									if($count_arb=="" or $count_arb=="1"){
										$recurring_text=$recurring_text.' per '.$count_arb.' '.get_post_meta($package_id, 'listinghub_package_recurring_cycle_type', true).esc_html__(', Auto recurring ','listinghub') ;
										$recurring_text=$recurring_text;
										}else{
										$recurring_text=$recurring_text.' per '.$count_arb.' '.get_post_meta($package_id, 'listinghub_package_recurring_cycle_type', true).'s'.esc_html__(', Auto recurring ','listinghub') ;
										$recurring_text=$recurring_text;
									}
									}else{ 
									$recurring_text=$recurring_text.', '.esc_html__('Package Expire After ','listinghub' ).get_post_meta($package_id, 'listinghub_package_initial_expire_interval', true).' '.get_post_meta($package_id, 'listinghub_package_initial_expire_type', true);
								}
								echo esc_html($post_p->post_title).' | '.esc_html($recurring_text);
								}else{
								echo  esc_html__('None','listinghub' );
							}
							}else{
							echo esc_html__('None','listinghub' );
						}
					?>
				</td>
			</tr>
			<tr>
				<td width="60%" >
					<?php    esc_html_e('Package Amount','listinghub')	;?>
				</td>
				<td width="40%" >
					<?php
						$recurring_text='  '; $amount= '';
						if(get_post_meta($package_id, 'listinghub_package_cost', true)=='0' or get_post_meta($package_id, 'listinghub_package_cost', true)==""){
							$amount= 'Free';
							}else{
							$amount= $currencyCode.' '. get_post_meta($package_id, 'listinghub_package_cost', true);
						}
						$recurring= get_post_meta($package_id, 'listinghub_package_recurring', true);
						if($recurring == 'on'){
							$amount= $currencyCode.' '. get_post_meta($package_id, 'listinghub_package_recurring_cost_initial', true);
							$count_arb=get_post_meta($package_id, 'listinghub_package_recurring_cycle_count', true);
							if($count_arb=="" or $count_arb=="1"){
								$recurring_text=" per ".' '.get_post_meta($package_id, 'listinghub_package_recurring_cycle_type', true);
								}else{
								$recurring_text=' per '.$count_arb.' '.get_post_meta($package_id, 'listinghub_package_recurring_cycle_type', true).'s';
							}
							}else{
							$recurring_text=' &nbsp; ';
						}
						echo esc_html($amount);
					?>
				</td>
			</tr>
			<tr>
				<td width="60%" >
					<?php    esc_html_e('Package Type','listinghub')	;?>
				</td>
				<td width="40%" >
					<?php
						echo esc_html($amount).' '.esc_html($recurring_text);
					?>
				</td>
			</tr>
			<tr>
				<td width="60%" >
					<?php    esc_html_e('Payment Status','listinghub')	;?>
				</td>
				<td width="40%" >
					<?php
						echo get_user_meta($current_user->ID, 'listinghub_payment_status', true);
					?>
				</td>
			</tr>
			<tr>
				<td width="60%" >
					<?php    esc_html_e('User Role','listinghub')	;?>
				</td>
				<td width="40%">
					<?php
						$user = new WP_User( $current_user->ID );
						if ( !empty( $user->roles ) && is_array( $user->roles ) ) {
							foreach ( $user->roles as $role )
							echo ucfirst($role);
						}
					?>
				</td>
			</tr>
			<?php
				if(get_user_meta($current_user->ID, 'listinghub_payment_status', true)=='cancel'){
				?>
				<tr>
					<td width="60%" >
						<?php    esc_html_e('Exprie Date','listinghub');?>
					</td>
					<td width="40%" >
						<?php
							if($recurring == 'on'){
								$exp_date= get_user_meta($current_user->ID, 'listinghub_exprie_date', true);
								echo date('d-M-Y',strtotime($exp_date));
								}else{
								$exp_date= get_user_meta($current_user->ID, 'listinghub_exprie_date', true);
								echo date('d-M-Y',strtotime($exp_date));
							}
						?>
					</td>
				</tr>
				<?php
					}else{
				?>
				<tr>
					<td width="60%" >
						<?php    esc_html_e('Next Payment Date','listinghub')	;?>
					</td>
					<td width="40%" >
						<?php
							if($recurring == 'on'){
								$exp_date= get_user_meta($current_user->ID, 'listinghub_exprie_date', true);
								echo ($exp_date!=""? date('d-M-Y',strtotime($exp_date)):'');
								}else{
								$exp_date= get_user_meta($current_user->ID, 'listinghub_exprie_date', true);
								echo ($exp_date!=""? date('d-M-Y',strtotime($exp_date)):'');
							}
						?>
					</td>
				</tr>
				<?php
				}
			?>
		</table>
	</div>
	<div class="tab-pane" id="tab_upgrade">
		<?php
			if($iv_gateway=='woocommerce'){
			?>
			<form class="form-group"  name="profile_upgrade_form" id="profile_upgrade_form" action="<?php  the_permalink() ?>?&payment_gateway=woocommerce&iv-submit-upgrade=upgrade" method="post">
				<div class=" row form-group">
					<label for="text" class="col-md-4 col-xs-4 col-sm-4 control-label"><?php  esc_html_e('Package Name','listinghub')	;?></label>
					<div class="col-md-8 col-xs-8 col-sm-8 ">
						<?php
							$listinghub_pack='listinghub_pack';
							$sql=$wpdb->prepare("SELECT * FROM $wpdb->posts WHERE post_type = '%s'  and post_status='draft'",$listinghub_pack);
							$membership_pack = $wpdb->get_results($sql);
							$total_package=count($membership_pack);
							if(sizeof($membership_pack)>0){
								$i=0; $current_package_id=get_user_meta($current_user->ID,'listinghub_package_id',true);
								echo'<select name="package_sel" id ="package_sel" class=" form-control">';
								foreach ( $membership_pack as $row )
								{
									// Recurring text
									$recurring_text='  '; $amount_text= '';
									if(get_post_meta($row->ID, 'listinghub_package_cost', true)=='0' or get_post_meta($package_id, 'listinghub_package_cost', true)==""){
										$amount_text= 'Free';
										}else{
										$amount_text= $currencyCode.''. get_post_meta($row->ID, 'listinghub_package_cost', true);
									}
									$recurring= get_post_meta($row->ID, 'listinghub_package_recurring', true);
									if($recurring == 'on'){
										$amount_text = $currencyCode.' '. get_post_meta($row->ID, 'listinghub_package_recurring_cost_initial', true);
										$count_arb=get_post_meta($row->ID, 'listinghub_package_recurring_cycle_count', true);
										if($count_arb=="" or $count_arb=="1"){
											$recurring_text=$amount_text."/".' '.get_post_meta($row->ID, 'listinghub_package_recurring_cycle_type', true);
											}else{
											$recurring_text=$amount_text.'/'.$count_arb.' '.get_post_meta($row->ID, 'listinghub_package_recurring_cycle_type', true).'s';
										}
										}else{
										$recurring_text='&nbsp;';
									}
									// Recuuring package text end
									if($current_package_id==$row->ID){
										echo '<option value="'. esc_attr($row->ID).'" >'. esc_html($row->post_title).' '.$recurring_text.esc_html__(' [Your Current Package]','listinghub').' </option>';
										}else{
										echo '<option value="'. esc_attr($row->ID).'" >'. esc_html($row->post_title).' '.$recurring_text.'</option>';
									}
									if($i==0){
										$package_id=$row->ID;
										if(get_post_meta($row->ID, 'listinghub_package_recurring',true)=='on'){
											$amount=get_post_meta($row->ID, 'listinghub_package_recurring_cost_initial', true);
											}else{
											$amount=get_post_meta($row->ID, 'listinghub_package_cost',true);
										}
									}
									$i++;
								}
								echo '</select>';
							}
						?>
					</div>
				</div>
				<div class="row form-group">
					<label for="text" class="col-md-4 col-xs-4 col-sm-4 control-label"><?php  esc_html_e('Amount','listinghub')	;?></label>
					<div class="col-md-8 col-xs-8 col-sm-8 " id="p_amount">
						<?php
						echo esc_html($currencyCode).' '.esc_html($amount); ?>
					</div>
				</div>
				<div class="row form-group">
					<label for="text" class="col-md-4 col-xs-4 col-sm-4 control-label"></label>
					<div class="col-md-8 col-xs-8 col-sm-8 " > 	<div id="loading"> </div>
						<input type="hidden" name="package_id" id="package_id" value="<?php echo esc_html($package_id); ?>">
						<input type="hidden" name="coupon_code" id="coupon_code" value="">
						<button class="btn green-haze" type="submit"> <?php  esc_html_e('Upgrade','listinghub')	;?></button>
						<input type="hidden" name="return_page" id="return_page" value="<?php  the_permalink() ?>">
					</div>
				</div>
			</form>
			<?php
			}
			if($iv_gateway=='paypal-express'){
			?>
			<form class="form-group"  name="profile_upgrade_form" id="profile_upgrade_form" action="<?php  the_permalink() ?>?&payment_gateway=paypal&iv-submit-upgrade=upgrade" method="post">
				<div class=" row form-group">
					<label for="text" class="col-md-4 col-xs-4 col-sm-4 control-label"><?php    esc_html_e('Package Name','listinghub')	;?></label>
					<div class="col-md-8 col-xs-8 col-sm-8 ">
						<?php
							$package_amount='';
							$sql="SELECT * FROM $wpdb->posts WHERE post_type = 'listinghub_pack'  and post_status='draft'";
							$membership_pack = $wpdb->get_results($sql);
							$total_package=count($membership_pack);
							if(sizeof($membership_pack)>0){
								$i=0; $current_package_id=get_user_meta($current_user->ID,'listinghub_package_id',true);
								echo'<select name="package_sel" id ="package_sel" class=" form-control">';
								foreach ( $membership_pack as $row )
								{
									// Recurring text
									$recurring_text='  '; $amount_text= '';
									if(get_post_meta($row->ID, 'listinghub_package_cost', true)=='0' or get_post_meta($package_id, 'listinghub_package_cost', true)==""){
										$amount_text= 'Free';
										}else{
										$amount_text= $currencyCode.''. get_post_meta($row->ID, 'listinghub_package_cost', true);
									}
									$recurring= get_post_meta($row->ID, 'listinghub_package_recurring', true);
									if($recurring == 'on'){
										$amount= $currencyCode.' '. get_post_meta($row->ID, 'listinghub_package_recurring_cost_initial', true);
										$count_arb=get_post_meta($row->ID, 'listinghub_package_recurring_cycle_count', true);
										if($count_arb=="" or $count_arb=="1"){
											$recurring_text=$amount_text."/".' '.get_post_meta($row->ID, 'listinghub_package_recurring_cycle_type', true);
											}else{
											$recurring_text=$amount_text.'/'.$count_arb.' '.get_post_meta($row->ID, 'listinghub_package_recurring_cycle_type', true).'s';
										}
										}else{
										$recurring_text='&nbsp;';
									}
									// Recuuring package text end
									if($current_package_id==$row->ID){
										echo '<option value="'. $row->ID.'" >'. $row->post_title.' '.$recurring_text.' [Your Current Package]</option>';
										}else{
										echo '<option value="'. $row->ID.'" >'. $row->post_title.' '.$recurring_text.'</option>';
									}
									if($i==0){
										$package_id=$row->ID;
										if(get_post_meta($row->ID, 'listinghub_package_recurring',true)=='on'){
											$package_amount=get_post_meta($row->ID, 'listinghub_package_recurring_cost_initial', true);
											}else{
											$package_amount=get_post_meta($row->ID, 'listinghub_package_cost',true);
										}
									}
									$i++;
								}
								echo '</select>';
							}
						?>
					</div>
				</div>
				<div class="row form-group">
					<label for="text" class="col-md-4 col-xs-4 col-sm-4 control-label"><?php    esc_html_e('Amount','listinghub')	;?></label>
					<div class="col-md-8 col-xs-8 col-sm-8 " id="p_amount">
						<?php  echo esc_html($package_amount).' '.esc_html($currencyCode); ?>
					</div>
				</div>
				<div class="row form-group">
					<label for="text" class="col-md-4 col-xs-4 col-sm-4 control-label"></label>
					<div class="col-md-8 col-xs-8 col-sm-8 " > 	<div id="loading"> </div>
						<input type="hidden" name="package_id" id="package_id" value="<?php echo esc_html($package_id); ?>">
						<input type="hidden" name="coupon_code" id="coupon_code" value="">
						<button class="btn green-haze" type="submit"> <?php    esc_html_e('Upgrade','listinghub')	;?></button>
						<input type="hidden" name="return_page" id="return_page" value="<?php  the_permalink() ?>">
					</div>
				</div>
			</form>
			<?php
			}
			if($iv_gateway=='stripe'){ ?>
			<form class="form"  name="profile_upgrade_form" id="profile_upgrade_form" action="" method="post">
				<?php
					require_once(ep_listinghub_template.'private-profile/iv_stripe_form_upgrade.php');
					$arb_status =	esc_attr(get_user_meta($current_user->ID, 'listinghub_payment_status', true));
					$cust_id = get_user_meta($current_user->ID,'listinghub_stripe_cust_id',true);
					$sub_id = get_user_meta($current_user->ID,'listinghub_stripe_subscrip_id',true);
				?>
				<input type="hidden" name="cust_id" value="<?php echo esc_attr($cust_id); ?>">
				<input type="hidden" name="sub_id" value="<?php echo esc_attr($sub_id); ?>">
			</form>
			<?php
			}
		?>
		<div class=" row bs-callout bs-callout-info">
			<?php    esc_html_e('Note: Your Successful Upgrade or Downgrade will affect on your user role immediately.','listinghub')	;?>
		</div>
	</div>
	<div class="tab-pane mb-2" id="tab_cancel">
		<?php
			$payment_gateway=get_user_meta($current_user->ID, 'listinghub_payment_gateway', true);
			if($payment_gateway=='paypal-express'){
				$arb_status =	esc_attr(get_user_meta($current_user->ID, 'listinghub_payment_status', true));
				$profile_id = get_user_meta($current_user->ID,'iv_paypal_recurring_profile_id',true);
				if($arb_status!='cancel'  && $profile_id!='' ){											?>
				<div class="" id="update_message_paypal"></div>
				<div id="paypal_cancel_div" name="paypal_cancel_div">
					<form class="form" role="form"  name="paypal_cancel_form" id="paypal_cancel_form" action="" method="post">
						<input type="hidden" name="profile_id" value="<?php echo esc_attr($profile_id); ?>">
						<div class="form-group">
							<label class="control-label"><?php    esc_html_e('Cancel Reason','listinghub')	;?></label>
							<textarea class="form-control" name="cancel_text" id="cancel_text" rows="3" placeholder="<?php    esc_html_e('Canceling Reason','listinghub')	;?>"  ></textarea>
						</div>
						<div class="margiv-top-10">
							<div class="" id="update_message"></div>
							<button type="button"   class="btn green-haze" onclick="return listinghub_cancel_membership_paypal();"><?php    esc_html_e('Cancel Membership','listinghub')	;?></button>
						</div>
					</form>
				</div>
				<?php
				}else{ ?>
				<div class="form-group">
					<label class="control-label"><?php    esc_html_e('Nothing to Cancel','listinghub')	;?></label>
				</div>
				<?php
				}
			}
			if($payment_gateway=='stripe'){
				$arb_status =	get_user_meta($current_user->ID, 'listinghub_payment_status', true);
				$cust_id = get_user_meta($current_user->ID,'listinghub_stripe_cust_id',true);
				$sub_id = get_user_meta($current_user->ID,'listinghub_stripe_subscrip_id',true);
				
				// Only show cancel button if status is not canceled, subscription ID exists, and customer ID exists
				if($arb_status!='cancel' && !empty($sub_id) && !empty($cust_id)){										?>
				<div class="" id="update_message_stripe"></div>
				<div id="stripe_cancel_div" name="stripe_cancel_div">
					<form class="form" role="form"  name="profile_cancel_form" id="profile_cancel_form" action="<?php  the_permalink() ?>" method="post">
						<input type="hidden" name="cust_id" value="<?php echo esc_attr($cust_id); ?>">
						<input type="hidden" name="sub_id" value="<?php echo esc_attr($sub_id); ?>">
						<div class="form-group">
							<label class="control-label"><?php    esc_html_e('Cancel Reason','listinghub')	;?></label>
							<textarea class="form-control" name="cancel_text" id="cancel_text" rows="3" placeholder="<?php    esc_html_e('Canceling Reason','listinghub')	;?>"  ></textarea>
						</div>
						<div class="margiv-top-10">
							<button type="button"   class="btn green-haze" onclick="return listinghub_cancel_membership_stripe();"><?php    esc_html_e('Cancel Membership','listinghub')	;?></button>
						</div>
					</form>
				</div>
				<?php
				}else{ ?>
				<div class="form-group">
					<label class="control-label"><?php    esc_html_e('Nothing to Cancel','listinghub')	;?></label>
				</div>
				<?php
				}					
			}
		?>
		<div class="form-group">
			<p class="mt-2 mb-3"> &nbsp;</p>
		</div>
	</div>
</div>
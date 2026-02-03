<?php
	$userdata = array();
	$package_id=$form_data['package_id'];
	$message='';
	$response=''; // Initialize response variable
	$userId=$current_user->ID;
	$user_id=$userId;
	$userdata['user_email']=$current_user->user_email;
	$userdata['user_login']=$current_user->display_name;
	$stripe_currency =get_post_meta($newpost_id, 'listinghub_stripe_api_currency',true);	
	$row2 = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->posts WHERE id = '%s' ", $package_id));
	$package_name='';
	if($row2==''){$row2=array();}
	if(isset($row2->post_title )){
		$package_name= $row2->post_title;
		$package_post_name= $row2->post_name;
	}
	if(trim(get_post_meta( $package_id,'listinghub_stripe_plan',true))!=''){
		$package_post_name= trim(get_post_meta( $package_id,'listinghub_stripe_plan',true));
	}
	$listinghub_package_cost= get_post_meta( $package_id,'listinghub_package_cost',true);
	$listinghub_package_recurring= get_post_meta( $package_id,'listinghub_package_recurring',true);
	$package_cost=$listinghub_package_cost;
	$recurringDescriptionFull= $package_cost.' '.$stripe_currency.' for '.$package_name .' at '. get_bloginfo();			
	$trial_enable= get_post_meta( $package_id,'listinghub_package_enable_trial_period',true);
	if($listinghub_package_recurring=='on'){
		$package_cost=get_post_meta($package_id, 'listinghub_package_recurring_cost_initial', true);			
	}
	// For Expire date
	$expire_interval = get_post_meta($package_id, 'listinghub_package_initial_expire_interval', true);						
	$initial_expire_type = get_post_meta($package_id, 'listinghub_package_initial_expire_type', true);
	if($expire_interval!='' AND $initial_expire_type!=''){
		$exp_periodNum = (60 * 60 * 24 * 90);
		switch ($initial_expire_type) {
			case 'year':
			$exp_periodNum = (60 * 60 * 24 * 365) * $expire_interval;
			break;
			case 'month':
			$exp_periodNum = (60 * 60 * 24 * 30) * $expire_interval;
			break;
			case 'week':
			$exp_periodNum = (60 * 60 * 24 * 7) * $expire_interval;
			break;
			case 'day':
			$exp_periodNum = (60 * 60 * 24) * $expire_interval;
			break;
		}
		$exp_time = time() + $exp_periodNum;
		$exp_d = date('Y-m-d',$exp_time).'T'.'00:00:00Z';
		//update_user_meta($user_id, 'listinghub_exprie_date',$exp_d); 
		//update_user_meta($user_id, 'listinghub_package_id',$package_id);
	}										
	// Expire date
	$dis_amount=0;	
	if($package_cost >0){					
		$currencyCode = (isset($stripe_currency)) ? $stripe_currency : 'USD';					
		$page_name_registration=get_option('epjblistinghub_registration' ); 
		\Stripe\Stripe::setApiKey($stripe_api);
		if($listinghub_package_recurring=='on'){
			$period= get_post_meta( $package_id,'listinghub_package_recurring_cycle_type',true);
			$trial_enable= get_post_meta( $package_id,'listinghub_package_enable_trial_period',true); 
			if( $trial_enable=='yes' ){
				$pay_text= $package_cost.' '.$stripe_currency.' for '.$package_name .' at '. get_bloginfo();
				$trial_amount=get_post_meta($package_id, 'listinghub_package_trial_amount', true);									
				if($trial_amount>0){
					$pay_text= $trial_amount.' '.$stripe_currency.' for '.$package_name .'  Trial at '. get_bloginfo();
					$stripe_return =  \Stripe\Charge::create(array("amount" => $trial_amount * 100,
					"currency" => $stripe_currency,
					"card" => $form_data['stripeToken'],
					"description" => $pay_text));
				}								
				}else{
				$package_cost_int=get_post_meta($package_id, 'listinghub_package_recurring_cost_initial', true);								
				if($package_cost_int>0){
					$pay_text= $package_cost_int.' '.$stripe_currency.' for '.$package_name .' at '. get_bloginfo();
				}
			}
			try {
				$customer = \Stripe\Customer::create(array(
				'card' 			=> $form_data['stripeToken'],
				'plan' 			=> $package_post_name,
				'email' 		=> $userdata['user_email'],
				'description' 	=> $pay_text
				)
				);
				} catch (Exception $e) {
				$response='Your card was declined(Recurring) | '.$e;
				return;
			} catch (\Exception $e) {
				$response='Your card was declined(Recurring) | '.$e;
				return;
			}
			
			// Check if customer was created successfully
			if(!isset($customer) || !is_object($customer)){
				$response='Error creating customer';
				return;
			}
			
			update_user_meta($userId, 'listinghub_payment_gateway','stripe'); 								
			if(isset($customer->id)){
				update_user_meta($userId, 'listinghub_stripe_cust_id', $customer->id);
			}
			
			// Get subscription ID - check if available in customer object first
			$subscription_id = '';
			if(isset($customer->subscriptions) && isset($customer->subscriptions->data) && is_array($customer->subscriptions->data) && count($customer->subscriptions->data) > 0){
				if(isset($customer->subscriptions->data[0]->id)){
					$subscription_id = $customer->subscriptions->data[0]->id;
				}
			}
			
			// If subscription ID not in customer object, retrieve it separately (only if customer ID exists)
			if(empty($subscription_id) && isset($customer->id) && !empty($customer->id)){
				try {
					if(class_exists('\Stripe\Subscription')){
						$subscriptions = \Stripe\Subscription::all(array('customer' => $customer->id, 'limit' => 1));
						if(isset($subscriptions) && isset($subscriptions->data) && is_array($subscriptions->data) && count($subscriptions->data) > 0){
							if(isset($subscriptions->data[0]->id)){
								$subscription_id = $subscriptions->data[0]->id;
							}
						}
					}
				} catch (Exception $e) {
					// Error retrieving subscriptions, continue without subscription ID
				} catch (\Exception $e) {
					// Error retrieving subscriptions, continue without subscription ID
				}
			}
			
			// Save subscription ID if found
			if(!empty($subscription_id)){
				update_user_meta($userId, 'listinghub_stripe_subscrip_id', $subscription_id);
			}
			$cycle_count=get_post_meta( $package_id,'listinghub_package_recurring_cycle_count',true);
			$period = get_post_meta( $package_id,'listinghub_package_recurring_cycle_type',true);							
			$recurring_cycle_count=	get_post_meta($package_id,'listinghub_package_recurring_cycle_count',true);
			if($recurring_cycle_count=="" || $recurring_cycle_count=="0"){
				$recurring_cycle_count='1';
			}								
			$trial_enable= get_post_meta( $package_id,'listinghub_package_enable_trial_period',true);
			if( $trial_enable=='yes'  ){										
				$period = get_post_meta( $package_id,'listinghub_package_recurring_trial_type',true);
				$recurring_cycle_count=	get_post_meta( $package_id,'listinghub_package_trial_period_interval',true);	
			}
			update_user_meta($userId, 'iv_paypal_recurring_profile_amt', get_post_meta($package_id, 'listinghub_package_recurring_cost_initial', true));
			update_user_meta($userId, 'iv_paypal_recurring_profile_period',$period);	
			update_user_meta($userId, 'iv_paypal_recurring_cycle_count',$cycle_count);	
			update_user_meta($userId, 'listinghub_package_id',$package_id);									
			$payment_status='';
			if(isset($customer->subscriptions->data[0]->status)){
				$payment_status=$customer->subscriptions->data[0]->status;
			} else {
				// If status not in customer object, try to get subscription status separately
				if(isset($subscription_id) && !empty($subscription_id)){
					try {
						$subscription = \Stripe\Subscription::retrieve($subscription_id);
						if(isset($subscription->status)){
							$payment_status = $subscription->status;
						}
					} catch (Exception $e) {
					} catch (\Exception $e) {
					}
				}
			}
			if($payment_status=='active' or $payment_status=='trialing' ){
				$role_package= get_post_meta( $package_id,'listinghub_package_user_role',true); 	
				$user = new WP_User( $userId );
				if(strtolower(trim($role_package))!='administrator'){
					$user->set_role($role_package);
				}	
				$periodNum = (60 * 60 * 24 * 365);
				switch ($period) {
					case 'year':
					$periodNum = (60 * 60 * 24 * 365) * $recurring_cycle_count;
					break;
					case 'month':
					$periodNum = (60 * 60 * 24 * 30) * $recurring_cycle_count;
					break;
					case 'week':
					$periodNum = (60 * 60 * 24 * 7) * $recurring_cycle_count;
					break;
					case 'day':
					$periodNum = (60 * 60 * 24) * $recurring_cycle_count;
					break;
				}
				$timeToBegin = time() + $periodNum;
				$exprie = date('Y-m-d',$timeToBegin).'T'.'00:00:00Z';
				update_user_meta($userId, 'listinghub_exprie_date',$exprie); 
				update_user_meta($userId, 'listinghub_payment_status', 'success');
				$response='Upgrade Successfully';
				}else{							 							
				update_user_meta($userId, 'listinghub_payment_status', 'pending');
				$response='Payment is pending. Please wait for confirmation.';
				error_log('[Stripe Payment Failure] Subscription status is not active or trialing.');
    			error_log('[Stripe Customer Object] ' . print_r($customer, true));
			}	 								  
			require_once( ep_listinghub_ABSPATH. 'inc/order-mail.php');			  
			}else{ // Not recurring Start******************
			$response='';	
			try {
				$stripe_cost= $package_cost - $dis_amount;
				$stripe_return =  \Stripe\Charge::create(array("amount" => $stripe_cost * 100,
				"currency" => $stripe_currency,
				"card" => $form_data['stripeToken'],
				"description" => $recurringDescriptionFull));
				$response='success';																	
			}
			catch (Exception $e) {
				$response='Your card was declined (one time) | '.$e;
				return;
			}
			if($response == 'success') {
				$role_package= get_post_meta( $package_id,'listinghub_package_user_role',true); 	
				$user = new WP_User( $userId );
				if(strtolower(trim($role_package))!='administrator'){
					$user->set_role($role_package);
				}	
				update_user_meta($userId, 'listinghub_payment_status', 'success');									
				update_user_meta( $userId, 'iv_stripe_transaction_id', $stripe_return->id);
				update_user_meta($userId, 'listinghub_payment_gateway','stripe'); 
				update_user_meta($userId, 'listinghub_package_id',$package_id);
				update_user_meta($user_id, 'listinghub_exprie_date',$exp_d); 
				$response='Upgrade Successfully';
				require_once( ep_listinghub_ABSPATH. 'inc/order-mail.php');
			} else {
				// If response is still empty or 'success', set a default message
				if(empty($response) || $response=='success'){
					$response='Upgrade completed.';
				}
			}	  
		} // Not recurring  End
		
		// Ensure response is always set
		if(empty($response)){
			$response='Upgrade completed.';
		}
	} else {
		// Handle Free Package - no payment required
		$role_package= get_post_meta( $package_id,'listinghub_package_user_role',true); 
		if(trim($role_package)==""){
			$role_package='basic';
		}
		if($package_id==0){
			$role_package='basic';
		}
		$user = new WP_User( $userId );
		if(strtolower(trim($role_package))!='administrator'){
			$user->set_role($role_package);
		}
		
		// Calculate expire date for free package
		$expire_interval = get_post_meta($package_id, 'listinghub_package_initial_expire_interval', true);
		$initial_expire_type = get_post_meta($package_id, 'listinghub_package_initial_expire_type', true);
		if($expire_interval!='' AND $initial_expire_type!=''){
			$exp_periodNum = (60 * 60 * 24 * 90);
			switch ($initial_expire_type) {
				case 'year':
				$exp_periodNum = (60 * 60 * 24 * 365) * $expire_interval;
				break;
				case 'month':
				$exp_periodNum = (60 * 60 * 24 * 30) * $expire_interval;
				break;
				case 'week':
				$exp_periodNum = (60 * 60 * 24 * 7) * $expire_interval;
				break;
				case 'day':
				$exp_periodNum = (60 * 60 * 24) * $expire_interval;
				break;
			}
			$exp_time = time() + $exp_periodNum;
			$exp_d = date('Y-m-d',$exp_time).'T'.'00:00:00Z';
		} else {
			$exp_d = date('Y-m-d', strtotime('+10 year'));
		}
		
		update_user_meta($userId, 'listinghub_package_id',$package_id);
		update_user_meta($userId, 'listinghub_payment_status', 'success');
		update_user_meta($userId, 'listinghub_payment_gateway','stripe');
		update_user_meta($userId, 'listinghub_exprie_date',$exp_d);
		$response='Upgrade Successfully';
	}	
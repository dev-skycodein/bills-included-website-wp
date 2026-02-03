<?php
	$userId=$current_user->ID;
	$user_id=$current_user->ID;
	$payment_status='';
	$user = new WP_User( $userId );									
	if ( !empty( $user->roles ) && is_array( $user->roles ) ) {
		foreach ( $user->roles as $role ){
			$crole= ucfirst($role); 
			break;
		}	
	}
	if ( class_exists( 'WC_Subscriptions' ) ) { 
		$subscriptions = wcs_get_users_subscriptions();
		$user_id       = get_current_user_id();																					
		foreach ( $subscriptions as $subscription_id => $subscription ) {
			$payment_status=  esc_attr( wcs_get_subscription_status_name( $subscription->get_status() ) );
			$status_result=1;
		}											
	}
	if($status_result==0){ 
		$order_statuses = array('wc-on-hold', 'wc-processing', 'wc-completed');
		$args = array(
			'customer_id' => $user_id,
			'status'      => $order_statuses,
			'limit'       => -1, // Retrieve all orders
		);
		$customer_orders = wc_get_orders($args);
		$got_status='';
		$package_product_id= get_post_meta( $package_id,'listinghub_package_woocommerce_product',true);
		foreach($customer_orders as $customer_order){													
			$order = wc_get_order($customer_order->ID);
			$order_stat = new WC_Order( $customer_order->ID );																			
			foreach($order->get_items() as $item_id => $item_values){												
				$product_id = $item_values['product_id'];
				if($product_id==$package_product_id){ 
					if($order_stat->has_status( 'completed' )){
						$payment_status= 'completed';  
					}
					else{
						$payment_status='Processing';  
					}
					$got_status='1';														
					break;
				}												
			}
			if($got_status=='1'){   
				break;
			}
		}
	}
	//  Active [STATUS] => Active
	$payment_status=strtolower($payment_status);
	if($payment_status=='completed' OR $payment_status=='active'){
		$package_id=get_user_meta($userId, 'listinghub_package_id', true);
		$role_package= get_post_meta( $package_id,'listinghub_package_user_role',true);
		update_user_meta($userId, 'listinghub_payment_status', 'success');
		update_user_meta($userId, 'listinghub_payment_woo', 'success');
		$user = new WP_User( $userId );
		if(strtoupper($crole)!=strtoupper('administrator')){
			$user->set_role($role_package);
		}
		// Change exprie_date
		$listinghub_exprie_date_old =get_user_meta($userId,'listinghub_exprie_date',true);
		$listinghub_package_recurring= get_post_meta( $package_id,'listinghub_package_recurring',true);			
		if($listinghub_package_recurring=='on'){
			$recurring_cycle_count= get_post_meta($package_id,'listinghub_package_recurring_cycle_count',true);
			if($recurring_cycle_count=="" or $recurring_cycle_count==0){$recurring_cycle_count=1;}
			$recurring_cycle_type= get_post_meta($package_id,'listinghub_package_recurring_cycle_type',true);
			}else{
			$recurring_cycle_count = get_post_meta($package_id, 'listinghub_package_initial_expire_interval', true);						
			$recurring_cycle_type = get_post_meta($package_id, 'listinghub_package_initial_expire_type', true);
		}
		$periodNum='';
		if(trim($recurring_cycle_count)!='' AND trim($recurring_cycle_type)!=''){
			switch ($recurring_cycle_type) {
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
			$date_n = date('Y-m-d',$timeToBegin).'T'.'00:00:00Z';
			$new_exp_date=  date("Y-m-d", strtotime($date_n));
			}else{
			$new_exp_date=date('Y-m-d', strtotime('+10 year'));									
		}
		update_user_meta($userId, 'listinghub_exprie_date', $new_exp_date);	
		}else{
		update_user_meta($userId, 'listinghub_payment_status', 'pending');
		$user = new WP_User( $userId );									
		if(strtoupper($crole)!=strtoupper('administrator')){
			$user->set_role('basic');
		}
	}		
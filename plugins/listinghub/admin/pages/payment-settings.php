<?php
	global $wpdb;
	$newpost_id='';
	$post_name='listinghub_paypal_setting';			
	$row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->posts WHERE post_name = '%s' ",$post_name ));
	if(isset($row->ID )){
		$newpost_id= $row->ID;
	}	
	$paypal_mode=get_post_meta( $newpost_id,'listinghub_paypal_mode',true);
	$newpost_id='';
	$post_name='listinghub_stripe_setting';
	$row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->posts WHERE post_name = '%s' ",$post_name ));
	if(isset($row->ID )){
		$newpost_id= $row->ID;
	}	
	$stripe_mode=get_post_meta( $newpost_id,'listinghub_stripe_mode',true);				
?>

<div class="bootstrap-wrapper">
	<div class="dashboard-eplugin container-fluid">
		
		<div class="row">
		
				<div id="update_message"> </div>
				<table class="table ">
					<thead>
						<tr>
						  <th>#</th>
						  <th><?php esc_html_e( 'Gateways Name', 'listinghub' );?></th>
						  <th><?php esc_html_e( 'Mode', 'listinghub' );?></th>
						  <th><?php esc_html_e( 'Status', 'listinghub' );?></th>
						  <th><?php esc_html_e( 'Action', 'listinghub' );?></th>
						</tr>
					</thead>
					<tbody>
						<tr>
						  <td><?php  esc_html_e('1','listinghub'); ?></td>
						  <td> <label>
								<input name='payment_gateway' id='payment_gateway'  type="radio" <?php echo (get_option('listinghub_payment_gateway')=='paypal-express')? 'checked':'' ?> value="paypal-express">
								<?php esc_html_e( 'Paypal', 'listinghub' );?>
							</label>
						  </td>
						  <td><?php echo strtoupper($paypal_mode); ?></td>
						  <td><?php echo (get_option('listinghub_payment_gateway')=='paypal-express')? 'Active':'Inactive' ?> </td>
							<td><a class="btn btn-primary btn-xs" href="?page=listinghub-payment-paypal"> <?php esc_html_e( 'Edit', 'listinghub' );?></a></td>
						</tr>
						<tr>
						  <td><?php  esc_html_e('2','listinghub'); ?></td>
						  <td>
								<label>
									<input name='payment_gateway' id='payment_gateway'  type="radio" <?php echo (get_option('listinghub_payment_gateway')=='stripe')? 'checked':'' ?>  value="stripe">
									<?php  esc_html_e('Stripe','listinghub'); ?>
								</label> </td>
								<td><?php echo strtoupper($stripe_mode); ?></td>
								<td><?php echo (get_option('listinghub_payment_gateway')=='stripe')? 'Active':'Inactive' ?></td>
								<td> <a class="btn btn-primary btn-xs" href="?page=listinghub-payment-stripe"> <?php esc_html_e( 'Edit', 'listinghub' );?></a> </td>
						</tr>
						<?php
						if ( class_exists( 'WooCommerce' ) ) {
						?>
						<tr>
						  <td>3</td>
						  <td><input name='payment_gateway' id='payment_gateway'  type="radio" <?php echo (get_option('listinghub_payment_gateway')=='woocommerce')? 'checked':'' ?>  value="woocommerce">
								<?php esc_html_e( 'WooCommerce[You need to select the payment gateway from woocommerce settings]', 'listinghub' );?>	
						  </label> </td>
						  <td></td>
						  <td><?php echo (get_option('listinghub_payment_gateway')=='woocommerce')? 'Active':'Inactive' ?></td>
						  <td>  </td>
						</tr>
						<?php
							}
						?>
					</tbody>
				</table>
		
			
			<div class="col-md-12">						
				<button class="button button-primary " onclick="return listinghub_update_payment_gateways_settings();"><?php esc_html_e( 'Save Payment Gateway', 'listinghub' );?></button>	
				<p>&nbsp;</p>	
			</div>
		</div>		
	</div>
</div>
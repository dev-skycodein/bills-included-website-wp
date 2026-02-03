<div id="coupon-div" class="none">
	<div class="row form-group">
		<label for="text" class="col-md-4 col-xs-4 col-sm-4 control-label"><?php   esc_html_e('Discount Coupon','listinghub');?></label>
		<div class="col-md-6 col-xs-6 col-sm-6 ">  <input type="text" class="form-control" name="coupon_name" id="coupon_name" value="" placeholder="<?php esc_attr_e( 'Enter Coupon Cod', 'listinghub' );?>e">
		</div>
		<div class="col-md-1 col-xs-1 col-sm-1 pull-left" id="coupon-result"> 
		</div>										
	</div>
	<div class="row">
		<hr>
	</div>	
	<div class="row">
		<label for="text" class="col-md-4 col-xs-4 col-sm-4 control-label"><?php   esc_html_e('(-) Discount','listinghub');?></label>
		<div class="col-md-8 col-xs-8 col-sm-8 " id="discount">  
		</div>										
	</div>
	<div class="row">
		<hr>
	</div>
	<div class="row">
		<label for="text" class="col-md-4 col-xs-4 col-sm-4 control-label"><?php   esc_html_e('Total','listinghub');?></label>												
		<div class="col-md-8 col-xs-8 col-sm-8" id="total"><label class="control-label">  <?php echo esc_html($package_amount).''.esc_html($api_currency); ?></label>
		</div>										
	</div>
	<div class="row">
		<hr>
	</div>
</div>		
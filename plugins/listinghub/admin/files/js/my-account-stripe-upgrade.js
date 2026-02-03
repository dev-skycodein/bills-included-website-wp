"use strict";
(function($) {
			var select = jQuery(".card-expiry-year"),
			year = new Date().getFullYear();
			for (var i = 0; i < 12; i++) {
				select.append(jQuery("<option value='"+(i + year)+"' "+(i === 0 ? "selected" : "")+">"+(i + year)+"</option>"))
			}
})(jQuery);
	
(function($) {
	var active_payment_gateway=realstripe.iv_gateway; 
	jQuery(document).ready(function($) {
						jQuery.validate({
							form : '#profile_upgrade_form',
							modules : 'security',		
							onSuccess : function() {							  
								jQuery("#loading").html(loader_image);
								if(active_payment_gateway=='stripe'){
									  var chargeAmount = 3000;
									 Stripe.createToken({
										number: jQuery('#card_number').val(),
										cvc: jQuery('#card_cvc').val(),
										exp_month: jQuery('#card_month').val(),
										exp_year: jQuery('#card_year').val(),
									}, listinghub_stripeResponseHandler);
									return false;
								}else{ // Else for paypal
									return true; // false Will stop the submission of the form
								}
							},
					  })
	 })
		Stripe.setPublishableKey( realstripe.stripe_publishable );
		function listinghub_stripeResponseHandler(status, response) {
			if (response.error) {				
				jQuery("#payment-errors").html('<div class="alert alert-info alert-dismissable"><a class="panel-close close" data-dismiss="alert">x</a>'+response.error.message +'.</div> ');
			} else {
				var form$ = jQuery("#profile_upgrade_form");
				// token contains id, last4, and card type
				var token = response['id'];
				// insert the token into the form so it gets submitted to the server
				form$.append("<input type='hidden' name='stripeToken' value='" + token + "' />");
				// and submit
				 	var ajaxurl = realstripe.ajaxurl;			
					var search_params={
						"action"  : 	"listinghub_profile_stripe_upgrade",	
						"form_data":	jQuery("#profile_upgrade_form").serialize(), 
						"_wpnonce": 	realstripe.myaccount,
					};
					jQuery.ajax({					
						url : ajaxurl,					 
						dataType : "json",
						type : "post",
						data : search_params,
						success : function(response){
							if(response.msg){
								jQuery('#payment-errors').html('<div class="alert alert-info alert-dismissable"><a class="panel-close close" data-dismiss="alert">x</a>'+response.msg +'.</div>');
							}
							jQuery("#stripe_form").hide();
							// Reload page after 2 seconds
							setTimeout(function(){
								location.reload();
							}, 2000);
						}
					});
			}
		}
})(jQuery);	
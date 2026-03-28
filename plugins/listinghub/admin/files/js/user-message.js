"use strict";
var ajaxurl = listinghub_data_message.ajaxurl;
var loader_image =listinghub_data_message.loading_image;
function listinghub_user_message(){
	"use strict";
	var formc = jQuery("#listinghub-author-email-form");
	if (!formc.length) {
		return;
	}

		if (jQuery.trim(jQuery("#email_address",formc).val()) == "" || jQuery.trim(jQuery("#message-content",formc).val()) == "") {
				alert(listinghub_data_message.Please_put_your_message);
		} else {
		var ajaxurl = listinghub_data_message.ajaxurl;
		var loader_image =listinghub_data_message.loading_image;
		jQuery('#update_message_popup').html(loader_image);
		var search_params={
			"action"  : 	"listinghub_message_send",
			"form_data":	formc.serialize(),
			"_wpnonce":  	listinghub_data_message.contact,
		};
		jQuery.ajax({
			url : ajaxurl,
			dataType : "json",
			type : "post",
			data : search_params,
			success : function(response){
				jQuery('#update_message_popup').html(response.msg );
				formc.trigger('reset');
			}
		});
	}
}
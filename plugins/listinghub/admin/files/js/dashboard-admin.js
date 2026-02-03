"use strict";
var ajaxurl = admindata.ajaxurl;
var loader_image = admindata.loading_image;
function listinghub_tabopen(evt, tabname) { 
  // Declare all variables
  var i, tabcontent, tablinks;
  // Get all elements with class="tabcontent" and hide them
  tabcontent = document.getElementsByClassName("tabcontent");
  for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = "none";
  }
  // Get all elements with class="tablinks" and remove the class "active"
  tablinks = document.getElementsByClassName("tablinks");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" nav-tab-active", "");
  }
  // Show the current tab, and add an "active" class to the button that opened the tab
  document.getElementById(tabname).style.display = "block";
  evt.currentTarget.className += " nav-tab-active";
  
}
jQuery( function() {	
	
	jQuery("#compose_adminmenu").on("click", function(){
		jQuery('#listinghub-left-menu').toggle();
	});
} );

jQuery( function() {		
	setTimeout(function(){
			jQuery( "#listing_publish" ).show();
			jQuery('#defaultOpen').css("display", "block");
			jQuery("#defaultOpen").addClass(" nav-tab-active");	
		},100); 
} );
	
jQuery( function() {
	jQuery( "#searchfieldsActive, #searchfieldsAvailable" ).sortable({
		connectWith: ".connectedSortable"
	}).disableSelection();
} );

function listinghub_update_openai_settings(){
	"use strict";
	var loader_image = admindata.loading_image;
	jQuery("#update_openai-message").html(loader_image);
	var search_params={
		"action"  : 	"listinghub_update_openai_settings",	
		"form_data":	jQuery("#openai_settings").serialize(), 
		"_wpnonce": 	admindata.settings,
	};
	jQuery.ajax({					
		url : ajaxurl,					 
		dataType : "json",
		type : "post",
		data : search_params,
		success : function(response){
			jQuery('#update_openai-message').html('<div class="alert alert-info alert-dismissable"><a class="panel-close close" data-dismiss="alert">x</a>'+response.msg +'. '+response.api_key_status+'</div>');
			jQuery('#api_key_status').html(response.api_key_status);
			
		}
	});
}
function listinghub_update_myaccount_menu(){
	"use strict";
	var loader_image = admindata.loading_image;
	jQuery("#update_myaccount_menu-message").html(loader_image);
	var search_params={
		"action"  : 	"listinghub_update_myaccount_menu",	
		"form_data":	jQuery("#my_account_menu").serialize(), 
		"_wpnonce": 	admindata.settings,
	};
	jQuery.ajax({					
		url : ajaxurl,					 
		dataType : "json",
		type : "post",
		data : search_params,
		success : function(response){
			jQuery('#update_myaccount_menu-message').html('<div class="alert alert-info alert-dismissable"><a class="panel-close close" data-dismiss="alert">x</a>'+response.msg +'.</div>');
			
		}
	});
}

function listinghub_update_single_fields(){
	"use strict";
	var loader_image = admindata.loading_image;
	jQuery("#success_message-single-fields").html(loader_image);
	var search_params={
		"action"  : 	"listinghub_update_single_fields",	
		"form_data":	jQuery("#active_single_template_fields").serialize(), 
		"_wpnonce": 	admindata.settings,
	};
	jQuery.ajax({					
		url : ajaxurl,					 
		dataType : "json",
		type : "post",
		data : search_params,
		success : function(response){
			jQuery('#success_message-single-fields').html('<div class="alert alert-info alert-dismissable"><a class="panel-close close" data-dismiss="alert">x</a>'+response.msg +'.</div>');
			
		}
	});
}

function listinghub_edit_banner_image(profile_image_id){
	"use strict";
	var image_gallery_frame;
	image_gallery_frame = wp.media.frames.downloadable_file = wp.media({
		// Set the title of the modal.
		title: admindata.SetImage	,
		button: {
			text: admindata.SetImage,
		},
		multiple: false,
		displayUserSettings: true,
	});
	image_gallery_frame.on( 'select', function() {
		var selection = image_gallery_frame.state().get('selection');
		selection.map( function( attachment ) {
			attachment = attachment.toJSON();
			if ( attachment.id ) {			
				
				jQuery('#'+profile_image_id).html('<img  class="rounded-profileimg img-responsive"  src="'+attachment.url+'">');
				jQuery('#topbanner_url').val(attachment.url );
				jQuery('#topbanner_id').val(attachment.id );			
				
			}
		});
	});
	image_gallery_frame.open();
}
function listinghub_edit_profile_image(profile_image_id){
	"use strict";
	var image_gallery_frame;
	image_gallery_frame = wp.media.frames.downloadable_file = wp.media({
		// Set the title of the modal.
		title: admindata.SetImage	,
		button: {
			text: admindata.SetImage,
		},
		multiple: false,
		displayUserSettings: true,
	});
	image_gallery_frame.on( 'select', function() {
		var selection = image_gallery_frame.state().get('selection');
		selection.map( function( attachment ) {
			attachment = attachment.toJSON();
			if ( attachment.id ) {
				jQuery('#'+profile_image_id).html('<img  class="rounded-profileimg img-responsive"  src="'+attachment.url+'">');
				jQuery('#profile_image_url').val(attachment.url );
				
				
			}
		});
	});
	image_gallery_frame.open();
}

function listinghub_update_archive_fields(){
	"use strict";
	var loader_image = admindata.loading_image;
	jQuery("#success_message-archive-fields").html(loader_image);
	var search_params={
		"action"  : 	"listinghub_update_archive_fields",	
		"form_data":	jQuery("#search_active_archive_fields").serialize(), 
		"_wpnonce": 	admindata.settings,
	};
	jQuery.ajax({					
		url : ajaxurl,					 
		dataType : "json",
		type : "post",
		data : search_params,
		success : function(response){
			jQuery('#success_message-archive-fields').html('<div class="alert alert-info alert-dismissable"><a class="panel-close close" data-dismiss="alert">x</a>'+response.msg +'.</div>');
			
		}
	});
}
function listinghub_update_search_fields(){
	"use strict";
	var loader_image = admindata.loading_image;
	jQuery("#success_message-search-fields").html(loader_image);
	var search_params={
		"action"  : 	"listinghub_update_search_fields",	
		"form_data":	jQuery("#search_active_fields").serialize(), 
		"_wpnonce": 	admindata.settings,
	};
	jQuery.ajax({					
		url : ajaxurl,					 
		dataType : "json",
		type : "post",
		data : search_params,
		success : function(response){
			jQuery('#success_message-search-fields').html('<div class="alert alert-info alert-dismissable"><a class="panel-close close" data-dismiss="alert">x</a>'+response.msg +'.</div>');
			
		}
	});
}
function listinghub_remove_saved_shortcode(deletid){	
	"use strict";
	
	jQuery('#searchshortcode'+deletid).remove();
		setTimeout(function(){			
			var search_params={
					"action"  	: 	"listinghub_search_shortcodes_saved_delete",	
					"form_data"	:	jQuery("#savedsearch_shortcodes").serialize(), 		
					"_wpnonce"	: 	admindata.settings,
				};
				jQuery.ajax({					
					url : ajaxurl,					 
					dataType : "json",
					type : "post",
					data : search_params,
					success : function(response){	
						if(response.code=='success'){	
								
						}
					}
				});
			
		},1000); 
	
	
	
}
function listinghub_create_search_shortcode(){
	"use strict";
	var loader_image = admindata.loading_image;
	jQuery("#create_search_shortcode_update_message").html(loader_image);
	var search_params={
		"action"  : 	"listinghub_create_search_shortcode",	
		"form_data":	jQuery("#search_active_fields").serialize(), 
		"_wpnonce": 	admindata.settings,
	};
	jQuery.ajax({					
		url : ajaxurl,					 
		dataType : "json",
		type : "post",
		data : search_params,
		success : function(response){	
			jQuery("#create_search_shortcode_update_message").html('');
			jQuery('#create_search_shortcode').append('<div class="row deleteshorcoderow" ><div class="col-md-11">'+response.msg+'</div><div class="col-md-1"></div></div><hr/>');
		}
	});
}
jQuery(window).on('load',function(){
	if (jQuery(".epinputdate")[0]){	
		jQuery( ".epinputdate" ).datepicker( );
	}
});

jQuery(document).ready(function() {	
	if (jQuery("#user-data")[0]){	
		var oTable = jQuery('#user-data').DataTable( {	 
			rowReorder: {
				selector: 'td:nth-child(2)'
			},
			responsive: true
			
		} );
	}
	
});
jQuery(window).on('load',function(){
	
	if (jQuery("#user_payment_history")[0]){	
		jQuery('#user_payment_history').show();
		var oTable = jQuery('#user_payment_history').dataTable({
			responsive: true
		
		});
		oTable.fnSort( [ [1,'DESC'] ] );
	}
	
});
function iv_update_mailchamp_settings(){
	"use strict";
	var search_params={
		"action"  : 	"listinghub_update_mailchamp_setting",	
		"form_data":	jQuery("#mailchimp_settings").serialize(), 
		"_wpnonce": 	admindata.settings,
	};
	jQuery.ajax({					
		url : ajaxurl,					 
		dataType : "json",
		type : "post",
		data : search_params,
		success : function(response){
			jQuery('#update_message').html('<div class="alert alert-info alert-dismissable"><a class="panel-close close" data-dismiss="alert">x</a>'+response.msg +'.</div>');
			location.reload();
		}
	});
}

function iv_create_home_page(){
	"use strict";
	var search_params={
		"action"  : 	"listinghub_add_home_page",			
		"_wpnonce": 	admindata.settings,
	};
	jQuery.ajax({					
		url : ajaxurl,					 
		dataType : "json",
		type : "post",
		data : search_params,
		success : function(response){
			jQuery('#addhome-succcess').html(': '+response.msg );			
		}
	});
}
function listinghub_delete_feature_field(id_delete){
	"use strict";
	jQuery('#feature_'+id_delete).remove();
}
function listinghub_add_feature_field(){
	"use strict";
	var main_feature_div =jQuery('#pac_feature').html();
	jQuery('#pac_feature_all').append(''+main_feature_div+'<div class="clearfix mt-1 mb-1"><hr/></div>');
}
var current_progress = 0;	
function listinghub_import_demo(){
	"use strict";
			 var interval = setInterval(function() {
					current_progress += 10;
					jQuery("#dynamic")
					.css("width", current_progress + "%")
					.attr("aria-valuenow", current_progress)
					.text(current_progress + "% Complete");
					if (current_progress >= 90)
						clearInterval(interval);
				}, 1000);
	var search_params={
			"action"  : "listinghub_import_data",
			"_wpnonce": 	admindata.settings,
		};
		jQuery.ajax({					
			url : ajaxurl,					 
			dataType : "json",
			type : "post",
			data : search_params,
			success : function(response){
					current_progress = 90;
					jQuery("#dynamic")
					.css("width", current_progress + "%")
					.attr("aria-valuenow", current_progress)
					.text(current_progress + "% Complete");
					jQuery('#cptlink12').show(1000);
					jQuery('#importbutton').hide(500); 
			}
		})
	}

function listinghub_update_user_setting() {
	"use strict";
				// New Block For Ajax*****
			var search_params={
				"action"  : 	"listinghub_update_user_settings",	
				"form_data":	jQuery("#user_form_iv").serialize(), 
				"_wpnonce": 	admindata.settings,
			};
			jQuery.ajax({					
				url : ajaxurl,					 
				dataType : "json",
				type : "post",
				data : search_params,
				success : function(response){					    						
					jQuery('#usersupdatemessage').html('<div class="alert alert-info alert-dismissable"><a class="panel-close close" data-dismiss="alert">x</a>'+response.msg +'.</div>');
					}
			});
}
function listinghub_update_color_settings(){
	"use strict";
		var loader_image = admindata.loading_image;
		jQuery("#success_message_color_setting").html(loader_image);
		var search_params={
				"action"  : 	"listinghub_update_color_settings",	
				"form_data":	jQuery("#color_settings").serialize(), 
				"_wpnonce": 	admindata.settings,
			};
			jQuery.ajax({					
				url : ajaxurl,					 
				dataType : "json",
				type : "post",
				data : search_params,
				success : function(response){
					jQuery('#success_message_color_setting').html('<div class="alert alert-info alert-dismissable"><a class="panel-close close" data-dismiss="alert">x</a>'+response.msg +'.</div>');
				}
			});
}

function listinghub_update_map_settings(){
		"use strict";
		var loader_image = admindata.loading_image;
		jQuery("#success_message_map_setting").html(loader_image);
		var search_params={
				"action"  : 	"listinghub_update_map_settings",	
				"form_data":	jQuery("#map_settings").serialize(), 
				"_wpnonce": 	admindata.settings,
			};
			jQuery.ajax({					
				url : ajaxurl,					 
				dataType : "json",
				type : "post",
				data : search_params,
				success : function(response){
					jQuery('#success_message_map_setting').html('<div class="alert alert-info alert-dismissable"><a class="panel-close close" data-dismiss="alert">x</a>'+response.msg +'.</div>');
				}
			});
}
jQuery(function() {		
    jQuery('#package_sel').on("change", function () {
        this.form.on("submit");
    });
		jQuery(function() {
			jQuery( "#exp_date" ).datepicker({ dateFormat: 'dd-mm-yy' });
		});		
});
function update_stripe_setting() {
				"use strict";
				// New Block For Ajax*****
				var ajaxurl = admindata.ajaxurl;
				var search_params={
					"action"  : 	"listinghub_update_stripe_settings",	
					"form_data":	jQuery("#stripe_form_iv").serialize(), 
					"_wpnonce": 	admindata.paymentgateway,
				};
				jQuery.ajax({					
					url : ajaxurl,					 
					dataType : "json",
					type : "post",
					data : search_params,
					success : function(response){
							jQuery('#iv-loading').html('<div class="col-md-12 alert alert-success">Update Successfully.</div>');
						
					}
				});
				
	}
function listinghub_update_payment_settings_terms() {
				"use strict";
				// New Block For Ajax*****
				var search_params={
					"action"  : 	"listinghub_update_payment_setting",	
					"form_data":	jQuery("#payment_settings").serialize(), 
					"_wpnonce": 	admindata.settings,	
				};
				jQuery.ajax({					
					url : ajaxurl,					 
					dataType : "json",
					type : "post",
					data : search_params,
					success : function(response){
						jQuery('#update_message').html('<div class="alert alert-info alert-dismissable"><a class="panel-close close" data-dismiss="alert">x</a>'+response.msg +'.</div>');
					}
				});
	}
function listinghub_update_page_settings(){
				"use strict";
				jQuery("#page_all_setting_save").html(loader_image);
				var search_params={
					"action"  : 	"listinghub_update_page_setting",	
					"form_data":	jQuery("#listinghub_page_settings").serialize(), 
					"_wpnonce": 	admindata.settings,	
				};
				jQuery.ajax({					
					url : ajaxurl,					 
					dataType : "json",
					type : "post",
					data : search_params,
					success : function(response){
					jQuery('#page_all_setting_save').html('<div class="alert alert-info alert-dismissable"><a class="panel-close close" data-dismiss="alert">x</a>'+response.msg +'.</div>');
					}
				});
}
function listinghub_update_email_settings(){
				"use strict";
				var search_params={
					"action"  : 	"listinghub_update_email_setting",	
					"form_data":	jQuery("#email_settings").serialize(), 
					"_wpnonce": 	admindata.settings,	
				};
				jQuery.ajax({					
					url : ajaxurl,					 
					dataType : "json",
					type : "post",
					data : search_params,
					success : function(response){
							jQuery('#update_message').html('<div class="alert alert-info alert-dismissable"><a class="panel-close close" data-dismiss="alert">x</a>'+response.msg +'.</div>');
							jQuery('#email-success').html('<div class="alert alert-info alert-dismissable"><a class="panel-close close" data-dismiss="alert">x</a>'+response.msg +'.</div>');
					}
				});
}			
function listinghub_update_account_settings(){
				"use strict";
				var search_params={
					"action"  : 	"listinghub_update_account_setting",	
					"form_data":	jQuery("#account_settings").serialize(),
					"_wpnonce": 	admindata.settings,						
				};
				jQuery.ajax({					
					url : ajaxurl,					 
					dataType : "json",
					type : "post",
					data : search_params,
					success : function(response){
						jQuery('#update_message').html('<div class="alert alert-info alert-dismissable"><a class="panel-close close" data-dismiss="alert">x</a>'+response.msg +'.</div>');
					}
				});
}
function listinghub_update_protected_settings(){
		"use strict";
		var search_params={
		"action"  : 	"listinghub_update_protected_setting",	
		"form_data":	jQuery("#protected_settings").serialize(), 
		"_wpnonce": 	admindata.settings,	
	};
	jQuery.ajax({					
		url : ajaxurl,					 
		dataType : "json",
		type : "post",
		data : search_params,
		success : function(response){
			jQuery('#update_message').html('<div class="alert alert-info alert-dismissable"><a class="panel-close close" data-dismiss="alert">x</a>'+response.msg +'.</div>');
		}
	})
}
function listinghub_protect_select_all(sel_name) {
		"use strict";
	   if(jQuery("#"+sel_name+"_all").prop("checked") == true){			
			jQuery("."+sel_name).prop("checked",jQuery("#"+sel_name+"_all").prop("checked"));
		}else{
			jQuery("."+sel_name).prop("checked", false);
		}
}

function listinghub_update_profile_fields(){
		"use strict";
		var ajaxurl = admindata.ajaxurl;
		var search_params = {
			"action": 		"listinghub_update_profile_fields",
			"form_data":	jQuery("#profile_fields").serialize(),
			"_wpnonce": 	admindata.mymenu,	
		};
		jQuery.ajax({
			url: ajaxurl,
			dataType: "json",
			type: "post",
			data: search_params,
			success: function(response) {  
				jQuery('#messageprofile').html('<div class="alert alert-info alert-dismissable"><a class="panel-close close" data-dismiss="alert">x</a>'+response.code +'.</div>');
			}
		});
	}
function listinghub_update_paypal_setting() {
	"use strict";
				var ajaxurl = admindata.ajaxurl;
				// New Block For Ajax*****
				var search_params={
					"action"  : 	"listinghub_update_paypal_settings",	
					"form_data":	jQuery("#paypal_form_iv").serialize(), 
					"_wpnonce": admindata.paymentgateway,
				};
				jQuery.ajax({					
					url : ajaxurl,					 
					dataType : "json",
					type : "post",
					data : search_params,
					success : function(response){
						jQuery('#iv-loading').html('<div class="col-md-5 alert alert-success">Update Successfully.</div>');
						
					}
				});
				
	}
function  listinghub_update_payment_gateways_settings (){
	"use strict";
		var ajaxurl = admindata.ajaxurl;
		
		var search_params = {
			"action": "listinghub_gateway_settings_update",
			"payment_gateway": jQuery("input[name=payment_gateway]:checked").val(),	
			"_wpnonce": admindata.paymentgateway,
			
		};
		jQuery.ajax({
			url: ajaxurl,
			dataType: "json",
			type: "post",
			data: search_params,
			success: function(response) { 
				jQuery('#update_message').html('<div class="alert alert-info alert-dismissable"><a class="panel-close close" data-dismiss="alert">x</a>'+response.msg +'.</div>');					             		
			
			}
		});
}
function listinghub_update_the_package() {
	"use strict";
		var ajaxurl = admindata.ajaxurl;
		var loader_image = admindata.loading_image;
	jQuery("#loading").html(loader_image);
			// New Block For Ajax*****
			
			var search_params={
				"action"  : 	"listinghub_update_package",	
				"form_data":	jQuery("#package_form_iv").serialize(),
				"_wpnonce": admindata.packagenonce,			
				
			};
			jQuery.ajax({					
				url : ajaxurl,					 
				dataType : "json",
				type : "post",
				data : search_params,
				success : function(response){						
					var url = admindata.ep_listinghub_ADMINPATH+"admin.php?page=listinghub-settings&packages";    						
					jQuery(location).attr('href',url);
				}
			});
			
}
function listinghub_save_the_package() {
	"use strict";
		var ajaxurl = admindata.ajaxurl;
		var loader_image = admindata.loading_image;
		jQuery("#loading").html(loader_image);
	
				// New Block For Ajax*****
				var search_params={
					"action"  : 	"listinghub_save_package",	
					"form_data":	jQuery("#package_form_iv").serialize(),
					"_wpnonce": admindata.packagenonce,	
				};
				jQuery.ajax({					
					url : ajaxurl,					 
					dataType : "json",
					type : "post",
					data : search_params,
					success : function(response){
						var url = admindata.ep_listinghub_ADMINPATH+"admin.php?page=listinghub-settings&packages";    						
						jQuery(location).attr('href',url);	
					}
				});
				
	}	

jQuery(document).ready(function(){		

		jQuery('#package_recurring').on("click", function(){
			if(this.checked){				
				jQuery('#recurring_block').show();
			}else{				
				jQuery('#recurring_block').hide();
			}
		});
	
		jQuery('#package_enable_trial_period').on("click", function(){
			if(this.checked){				
				jQuery('#trial_block').show();
			}else{				
				jQuery('#trial_block').hide();
			}
		});
});		

function listinghub_package_status_change(status_id,curr_status){
	"use strict";
	status_id =status_id.trim();
	curr_status=curr_status.trim();
	var ajaxurl = admindata.ajaxurl;
	var search_params = {
		"action": 	"listinghub_update_package_status",
		"status_id": status_id,	
		"status_current":curr_status,
		"_wpnonce": admindata.packagenonce,
	};
	jQuery.ajax({
		url: ajaxurl,
		dataType: "json",
		type: "post",
		data: search_params,
		success: function(response) {   
			if(response.code=='success'){					
				jQuery("#status_"+status_id).html('<button class="btn btn-info btn-xs" onclick="return listinghub_package_status_change(\' '+status_id+' \' ,\' '+response.current_st+' \');">'+response.msg+'</button>');
			}
		}
	});
}	
function listinghub_change_marker_image(cat_image_id){	
	"use strict";
	var image_gallery_frame;
	image_gallery_frame = wp.media.frames.downloadable_file = wp.media({
		// Set the title of the modal.
		title: 'Marker Image',
		button: {
			text: 'Marker Image',
		},
		multiple: false,
		displayUserSettings: true,
	});                
	image_gallery_frame.on( 'select', function() {
		var selection = image_gallery_frame.state().get('selection');
		selection.map( function( attachment ) {
			attachment = attachment.toJSON();
			if ( attachment.id ) {							
				var ajaxurl = admindata.ajaxurl;
				var search_params = {
					"action": 	"listinghub_update_map_marker",
					"attachment_id": attachment.id,
					"category_id": cat_image_id,
					"_wpnonce": admindata.catimage,
				};
				jQuery.ajax({
					url: ajaxurl,
					dataType: "json",
					type: "post",
					data: search_params,
					success: function(response) {   
						if(response=='success'){					
							jQuery('#marker_'+cat_image_id).html('<img width="20px" src="'+attachment.url+'">');                              
						}
					}
				});									
			}
		});
	});               
	image_gallery_frame.open(); 
}
function listinghub_change_cate_image(cat_image_id){	
	"use strict";
	var image_gallery_frame;
	image_gallery_frame = wp.media.frames.downloadable_file = wp.media({
		// Set the title of the modal.
		title: 'Category Image',
		button: {
			text: 'Category Image',
		},
		multiple: false,
		displayUserSettings: true,
	});                
	image_gallery_frame.on( 'select', function() {
		var selection = image_gallery_frame.state().get('selection');
		selection.map( function( attachment ) {
			attachment = attachment.toJSON();
			if ( attachment.id ) {							
				var ajaxurl =admindata.ajaxurl;
				var search_params = {
					"action": 	"listinghub_update_cate_image",
					"attachment_id": attachment.id,
					"category_id": cat_image_id,
					"_wpnonce": admindata.catimage,
				};
				jQuery.ajax({
					url: ajaxurl,
					dataType: "json",
					type: "post",
					data: search_params,
					success: function(response) {   
						if(response=='success'){					
							jQuery('#cate_'+cat_image_id).html('<img width="100px" src="'+attachment.url+'">');
						}
					}
				});									
			}
		});
	});               
	image_gallery_frame.open(); 
}
function listinghub_listing_defaultimage_fun(){	 
	"use strict";
	var image_gallery_frame;
	image_gallery_frame = wp.media.frames.downloadable_file = wp.media({
		// Set the title of the modal.
		title: 'Set Image',
		button: {
			text: 'Set Image',
		},
		multiple: false,
		displayUserSettings: true,
	});                
	image_gallery_frame.on( 'select', function() {
		var selection = image_gallery_frame.state().get('selection');
		selection.map( function( attachment ) {
			attachment = attachment.toJSON();
			if ( attachment.id ) {
				jQuery('#listing_defaultimage').html('<img width="80px" src="'+attachment.url+'">'); 
				jQuery('#listinghub_listing_defaultimage').val(attachment.id);
			}
		});
	});
	image_gallery_frame.open(); 
}
function listinghub_location_defaultimage_fun(){	 
	"use strict";
	var image_gallery_frame;
	image_gallery_frame = wp.media.frames.downloadable_file = wp.media({
		// Set the title of the modal.
		title: 'Set Image',
		button: {
			text: 'Set Image',
		},
		multiple: false,
		displayUserSettings: true,
	});                
	image_gallery_frame.on( 'select', function() {
		var selection = image_gallery_frame.state().get('selection');
		selection.map( function( attachment ) {
			attachment = attachment.toJSON();
			if ( attachment.id ) {
				jQuery('#location_defaultimage').html('<img width="80px" src="'+attachment.url+'">'); 
				jQuery('#listinghub_location_defaultimage').val(attachment.id);
			}
		});
	});
	image_gallery_frame.open(); 
}
function listinghub_category_defaultimage_fun(){
	"use strict";
	var image_gallery_frame;
	image_gallery_frame = wp.media.frames.downloadable_file = wp.media({
		// Set the title of the modal.
		title: 'Set Image',
		button: {
			text: 'Set Image',
		},
		multiple: false,
		displayUserSettings: true,
	});                
	image_gallery_frame.on( 'select', function() {
		var selection = image_gallery_frame.state().get('selection');
		selection.map( function( attachment ) {
			attachment = attachment.toJSON();
			if ( attachment.id ) {
				jQuery('#category_defaultimage').html('<img width="80px" src="'+attachment.url+'">'); 
				jQuery('#listinghub_category_defaultimage').val(attachment.id);
			}
		});
	});
	image_gallery_frame.open(); 
}
function listinghub_banner_defaultimage_fun(){	 
	"use strict";
	var image_gallery_frame;
	image_gallery_frame = wp.media.frames.downloadable_file = wp.media({
		// Set the title of the modal.
		title: 'Set Image',
		button: {
			text: 'Set Image',
		},
		multiple: false,
		displayUserSettings: true,
	});                
	image_gallery_frame.on( 'select', function() {
		var selection = image_gallery_frame.state().get('selection');
		selection.map( function( attachment ) {
			attachment = attachment.toJSON();
			if ( attachment.id ) {
				jQuery('#banner_defaultimage').html('<img width="80px" src="'+attachment.url+'">'); 
				jQuery('#listinghub_banner_defaultimage').val(attachment.id);
			}
		});
	});
	image_gallery_frame.open(); 
}



function listinghub_update_dir_setting(){
	"use strict";
	var search_params={
		"action"  : 	"listinghub_update_dir_setting",	
		"form_data":	jQuery("#directory_settings").serialize(), 
		"_wpnonce": admindata.dirsetting,		
	};
	jQuery.ajax({					
		url : ajaxurl,					 
		dataType : "json",
		type : "post",
		data : search_params,
		success : function(response){
			jQuery('#update_message').html('<div class="alert alert-info alert-dismissable"><a class="panel-close close" data-dismiss="alert">x</a>'+response.msg +'.</div>');
			jQuery('#update_message49').html('<div class="alert alert-info alert-dismissable"><a class="panel-close close" data-dismiss="alert">x</a>'+response.msg +'.</div>');
		}
	})
}
function listinghub_update_dir_fields(){
	"use strict";
	var loader_image = admindata.loading_image;
	var search_params = {
		"action": 		"listinghub_update_dir_fields",
		"form_data":	jQuery("#dir_fields_max").serialize(), 
		"_wpnonce": admindata.fields,	
	};
	jQuery('#success_message-fields').html(loader_image);
	
	jQuery.ajax({
		url: ajaxurl,
		dataType: "json",
		type: "post",
		data: search_params,
		success: function(response) {              		
			jQuery('#success_message-fields').html('<div class="alert alert-info alert-dismissable"><a class="panel-close close" data-dismiss="alert">x</a>'+response.code +'.</div>');
		}
	});
}
function listinghub_update_coupon() {
	"use strict";
	// New Block For Ajax*****
	var search_params={
		"action"  : 	"listinghub_update_coupon",	
		"form_data":	jQuery("#coupon_form_iv").serialize(), 
		"form_pac_ids": jQuery("#package_id").val(),
		"_wpnonce": admindata.coupon,
	};
	jQuery.ajax({					
		url : ajaxurl,					 
		dataType : "json",
		type : "post",
		data : search_params,
		success : function(response){
			var url = admindata.ep_listinghub_ADMINPATH+"admin.php?page=listinghub-settings&coupon";
			jQuery(location).attr('href',url);
		}
	});
}
jQuery(function() {	
	if (jQuery("#start_date")[0]){
			jQuery( "#start_date" ).datepicker({ dateFormat: 'dd-mm-yy' });
	}	
	if (jQuery("#end_date")[0]){
		jQuery( "#end_date" ).datepicker({ dateFormat: 'dd-mm-yy' });
	}
	if (jQuery("#deadline")[0]){
		jQuery( "#deadline" ).datepicker({ dateFormat: 'dd-mm-yy' });
	}
	
});



function listinghub_create_coupon() {	
	"use strict";
	// New Block For Ajax*****
	var search_params={
		"action"  : 	"listinghub_create_coupon",	
		"form_data":	jQuery("#coupon_form_iv").serialize(), 
		"form_pac_ids": jQuery("#package_id").val(),
		"_wpnonce": admindata.coupon,
	};
	jQuery.ajax({					
		url : ajaxurl,					 
		dataType : "json",
		type : "post",
		data : search_params,
		success : function(response){
			var url = admindata.ep_listinghub_ADMINPATH+"admin.php?page=listinghub-settings&coupon";    						
			jQuery(location).attr('href',url);
		}
	});
}
function listinghub_change_city_image(city_image_id){	
	"use strict";
	var image_gallery_frame;
	image_gallery_frame = wp.media.frames.downloadable_file = wp.media({
		// Set the title of the modal.
		title: admindata.SetImage,
		button: {
			text: admindata.SetImage,
		},
		multiple: false,
		displayUserSettings: true,
	});                
	image_gallery_frame.on( 'select', function() {
		var selection = image_gallery_frame.state().get('selection');
		selection.map( function( attachment ) {
			attachment = attachment.toJSON();
			if ( attachment.id ) {							
				var ajaxurl = admindata.ajaxurl;
				var search_params = {
					"action": 	"listinghub_update_city_image",
					"attachment_id": attachment.id,
					"city_id": city_image_id,
					"_wpnonce": admindata.cityimage,
				};
				jQuery.ajax({
					url: ajaxurl,
					dataType: "json",
					type: "post",
					data: search_params,
					success: function(response) {   
						if(response=='success'){					
							jQuery('#city_'+city_image_id).html('<img width="100px" src="'+attachment.url+'">');                              
						}
					}
				});									
			}
		});
	});               
	image_gallery_frame.open(); 
}


// New code for shortcode button
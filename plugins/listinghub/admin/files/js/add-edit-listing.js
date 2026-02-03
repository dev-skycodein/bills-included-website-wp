"use strict";
var ajaxurl = realpro_data.ajaxurl;

jQuery( document ).ready(function() { 
	if (jQuery(".epinputdate")[0]){	
		jQuery( ".epinputdate" ).datepicker( );
	}
});
jQuery(function() {		
	if (jQuery("#deadline")[0]){ 
		jQuery( "#deadline" ).datepicker({ dateFormat: 'dd-mm-yy' });
	}
	
});
function listinghub_chatgtp_settings_popup(){
	"use strict";	
	
	var form_listing_title =jQuery('#title').val();	
	if(form_listing_title!=''){
		let originalText = form_listing_title;
		let newText = originalText.replace(new RegExp(' ', "g"), '+');
		var contactform =realpro_data.ajaxurl+'?action=listinghub_chatgtp_settings_popup&form_listing_title='+newText;		
		jQuery.colorbox({ href:contactform, width:"75%", height: "75%", maxWidth: '750px',maxHeight: '880px', });
	}else{
		alert('Please Add Title');
	}
	
}	
function listinghub_chatgpt_post_creator(){
	"use strict";
	tinyMCE.triggerSave();	
	var ajaxurl = realpro_data.ajaxurl;
	var loader_image = realpro_data.loading_image;
		jQuery('#update_message-gpt').html(loader_image);
		jQuery('#chatgpt_post_creator').hide();
		
		var search_params={
			"action"  : 	"listinghub_chatgpt_post_creator",	
			"form_data":	jQuery("#chatgpt_pop").serialize(), 
			"_wpnonce":  	realpro_data.dirwpnonce,
		};
		jQuery.ajax({					
			url : ajaxurl,					 
			dataType : "json",
			type : "post",
			data : search_params,
			success : function(response){
				if(response.code=='success'){
						jQuery('#update_message-gpt').html('');
						jQuery('#chatgpt_post_creator').hide();
						jQuery('#insert_data_inform').show();
						var editor = tinyMCE.get('new_post_content');						
						// main content
						editor.setContent(response.content);	
						// FAQ						
						jQuery('#faqsall').append(response.faqs);
						// images 	
						if(response.feature_image_url=='off'){
							jQuery.colorbox.close();
						}else{							
							const arr = response.feature_image_url.split("|");
							jQuery('#feature_image_urls').append('<div class="form-group col-md-12 col-12 flex-column d-flex"><label>Select Image</label></div>');
							arr.forEach((item) => {
								jQuery('#feature_image_urls').append('<div class="form-group col-sm-3 col-12 flex-column d-flex"><label> <input type="radio" name="gpt_image" value="'+item+'"><img src="'+item+'"></label></div>');
							});	
						}
				}
			}
		});
}
function listinghub_insert_gpt_image_inform(){
	"use strict";
	var ajaxurl = realpro_data.ajaxurl;
	var loader_image = realpro_data.loading_image;
		jQuery('#update_message-gpt').html(loader_image);
		var search_params={
			"action"  : 	"listinghub_chatgpt_upload_image",	
			"form_data":	jQuery("#chatgpt_pop").serialize(), 
			"_wpnonce":  	realpro_data.dirwpnonce,
		};
		jQuery.ajax({					
			url : ajaxurl,					 
			dataType : "json",
			type : "post",
			data : search_params,
			success : function(response){
				if(response.code=='success'){
					jQuery('#post_image_div').html('<img  class="img-responsive rounded"  src="'+response.image_url+'">');
					jQuery('#feature_image_id').val(response.attachment_id ); 
					jQuery('#post_image_edit').html('<button type="button" onclick="listinghub_remove_post_image(\'post_image_div\');"  class="btn btn-small-ar">X</button>');  
					jQuery.colorbox.close();
											
				}
			}
		});
}	
function add_day_field(){
	"use strict";	
	var main_opening_div =jQuery('#day-row1').html(); 
	jQuery('#day_field_div').append('<div class="clearfix"></div><div class=" row form-group" >'+main_opening_div+'</div>');

}
function remove_old_day(div_id){
	"use strict";	
	jQuery('#old_days'+div_id).remove();
}
jQuery(document).ready(function(){
    jQuery("#toggle-btn").on("click", function(){
      jQuery("#toggle-example").collapse('toggle'); // toggle collapse
    });
});

jQuery( document ).ready(function() { 		
	setTimeout(function(){			
			jQuery(".leaflet-locationiq-input").attr("placeholder", realpro_data.save_address);
			
		},500); 
});

jQuery(document).ready(function () {
		document.getElementById('address').addEventListener('input', event => {
			const query = event.target.value;
			if (query.length >= 3) {
				searchAddress(query);
			} else {
				document.getElementById('autocomplete-results').innerHTML = '';
			}
		});

});
function searchAddress(query) {
	fetch(`https://nominatim.openstreetmap.org/search?q=${query}&format=json`)
	.then(response => response.json())
		.then(data => {
			displayResults(data);
		})
		.catch(error => {
			console.error('Error fetching data:', error);
		});
}
function displayResults(results) {
	   const autocompleteResults = document.getElementById('autocomplete-results');
		autocompleteResults.innerHTML = '';

		results.forEach(result => {
			const address = result.display_name;
			const item = document.createElement('div');
			item.textContent = address;
			item.classList.add('autocomplete-item');
			item.addEventListener('click', () => {
				const lat = result.lat;
				const lon = result.lon;
				let city = '';
				let country = '';

				if (result.address) {
					city = result.address.city || result.address.town || result.address.village || result.address.hamlet || result.address.suburb || result.address.locality || result.address.municipality;
					country = result.address.country;
				}

				console.log('Latitude:', lat);
				console.log('Longitude:', lon);
				console.log('City:', city);
				console.log('Country:', country);
				reverseGeocode(lat, lon);
				document.getElementById('address').value = address;
				autocompleteResults.innerHTML = '';
			});
			autocompleteResults.appendChild(item);
		});
}

function reverseGeocode(latitude, longitude) {
	fetch(`https://nominatim.openstreetmap.org/reverse?lat=${latitude}&lon=${longitude}&format=json`)
        .then(response => response.json())
        .then(data => {
            const city = data.address.city || data.address.town || data.address.village || data.address.hamlet || data.address.suburb || data.address.locality || data.address.municipality || '';
            const state = data.address.state || data.address.region || '';
            const postcode = data.address.postcode || '';
            const country = data.address.country || '';
           
			jQuery('#country').val(country);
			jQuery('#postcode').val(postcode);
			jQuery('#state').val(state);
			jQuery('#city').val(city);
			jQuery('#longitude').val(longitude);
			jQuery('#latitude').val(latitude);
        })
        .catch(error => {
            console.error('Error fetching data:', error);
        });
}
/*
jQuery( document ).ready(function() {
				
	// Initialize an empty map without layers (invisible map)
	var map = L.map('map', {
		center: [40.7259, -73.9805], // Map loads with this location as center
		zoom: 12,
		scrollWheelZoom: true,
		zoomControl: false,
		attributionControl: false,
		
	});
   
	//Geocoder options
	var geocoderControlOptions = {
		bounds: false,          //To not send viewbox
		markers: false,         //To not add markers when we geocoder
		attribution: null,      //No need of attribution since we are not using maps
		expanded: true,         //The geocoder search box will be initialized in expanded mode
		panToPoint: false,       //Since no maps, no need to pan the map to the geocoded-selected location
		params: {               //Set dedupe parameter to remove duplicate results from Autocomplete
				dedupe: 1,
			}
	}

	//Initialize the geocoder
	var geocoderControl = new L.control.geocoder('pk.87f2d9fcb4fdd8da1d647b46a997c727', geocoderControlOptions).addTo(map).on('select', function (e) {
		console.log(e);
		
		jQuery('#address').val(e.feature.feature.display_name);
		jQuery('#country').val(e.feature.feature.address.country);
		jQuery('#postcode').val(e.feature.feature.address.postcode);
		jQuery('#state').val(e.feature.feature.address.state);
		jQuery('#city').val(e.feature.feature.address.city);
		jQuery('#longitude').val(e.latlng.lng);
		jQuery('#latitude').val(e.latlng.lat);
					
		
	});

	//Get the "search-box" div
	var searchBoxControl = document.getElementById("search-box");
	//Get the geocoder container from the leaflet map
	var geocoderContainer = geocoderControl.getContainer();
	//Append the geocoder container to the "search-box" div
	searchBoxControl.appendChild(geocoderContainer);        
	

});
*/
jQuery( document ).ready(function() { 
	jQuery(document).on('click', '.listinghubcats-fields', function(){
			var listID = jQuery('#user_post_id').val();		
			var searchIDs = jQuery("#listinghubcats-container input:checkbox:checked").map(function(){
			  return jQuery(this).val();
			}).get(); 
		
			
			if (searchIDs != undefined && searchIDs != '') {
				console.log(searchIDs);
				var loader_image = realpro_data.loading_image;
				jQuery('#listinghub_fields').html(loader_image);
				var search_params={
					"action"  : "listinghub_load_categories_fields_wpadmin",	
					'term_id': searchIDs,
					'post_id': listID,
					'datatype': 'slug',
					"_wpnonce":  	realpro_data.dirwpnonce,
				};
				jQuery.ajax({					
					url : ajaxurl,					 
					dataType : "json",
					type : "post",
					data : search_params,
					success : function(response){
						if(response.msg=='success'){
								jQuery('#listinghub_fields').html(response.field_data);								
								if (jQuery(".epinputdate")[0]){	
									jQuery( ".epinputdate" ).datepicker( );
								}

						}
					
						
					}
				});
		}
		
	});	
});	

// For dashboard add listing
jQuery( document ).ready(function() { 
	jQuery(document).on('click', '.editor-post-taxonomies__hierarchical-terms-list[aria-label="Categories"] input', function(){ 
			
	   var termID = [];   
       var termIDs='';
	   var listID = jQuery('#post_ID').val();
		jQuery('.editor-post-taxonomies__hierarchical-terms-list[aria-label="Categories"] input:checked').each(function( index ) {
		   termIDs = jQuery(this).parent().next('label').text();
			termID.push(termIDs);
		});
		
		if (termID != undefined && termID != '') {
				console.log(termID);
				var loader_image = realpro_data.loading_image;
				
				jQuery('#listinghub_fields').html(loader_image);
				var search_params={
					"action"  : "listinghub_load_categories_fields_wpadmin",	
					'term_id': termID,
					'post_id': listID,
					'datatype': 'text',
					"_wpnonce":  	realpro_data.dirwpnonce,
				};
				jQuery.ajax({					
					url : ajaxurl,					 
					dataType : "json",
					type : "post",
					data : search_params,
					success : function(response){
						if(response.msg=='success'){
								jQuery('#listinghub_fields').html(response.field_data);								
								if (jQuery(".epinputdate")[0]){	
									jQuery( ".epinputdate" ).datepicker( );
								}

						}
					
						
					}
				});
		}
		
	});	
});	
	
		
function listinghub_update_post(){
	"use strict";
	tinyMCE.triggerSave();	
	var ajaxurl = realpro_data.ajaxurl;
	var loader_image = realpro_data.loading_image;
				jQuery('#update_message').html(loader_image);
				var search_params={
					"action"  : 	"listinghub_update_wp_post",	
					"form_data":	jQuery("#new_post").serialize(), 
					"_wpnonce":  	realpro_data.dirwpnonce,
				};
				jQuery.ajax({					
					url : ajaxurl,					 
					dataType : "json",
					type : "post",
					data : search_params,
					success : function(response){
						if(response.code=='success'){
								var url = realpro_data.permalink+"?&profile=all-post"; 						
								jQuery(location).attr('href',url);	
						}
					
						
					}
				});
	
	}

function listinghub_new_post_without_user(){
	"use strict";
	tinyMCE.triggerSave();	
	var ajaxurl = realpro_data.ajaxurl;
	var has_access=0;
	if(realpro_data.current_user_id=='0'){
		if(jQuery('#n_user_email').val().length === 0 || jQuery('#n_password').val().length === 0){ 			
				jQuery('#update_message').html('<div class="alert alert-info alert-dismissable"><a class="panel-close close" data-dismiss="alert">x</a>'+realpro_data.useremail_message +'.</div>');					
                jQuery('#update_message2').html('<div class="alert alert-info alert-dismissable"><a class="panel-close close" data-dismiss="alert">x</a>'+realpro_data.useremail_message +'.</div>');			
		}else{
			if (IsEmail(jQuery('#n_user_email').val()) == false) { 
			
				jQuery('#update_message').html('<div class="alert alert-info alert-dismissable"><a class="panel-close close" data-dismiss="alert">x</a>'+realpro_data.useremail_message +'.</div>');					
                jQuery('#update_message2').html('<div class="alert alert-info alert-dismissable"><a class="panel-close close" data-dismiss="alert">x</a>'+realpro_data.useremail_message +'.</div>');
                    //return false;
			}else{
				has_access=1;
			
			}
		
		}
	}else{
	has_access=1;
	}
	
	if(has_access==1){
		var loader_image = realpro_data.loading_image;
		jQuery('#update_message').html(loader_image);
		jQuery('#update_message2').html(loader_image);
		var search_params={
			"action"  : 	"listinghub_save_post_without_user",	
			"form_data":	jQuery("#new_post").serialize(), 
			"_wpnonce":  	realpro_data.dirwpnonce,
		};
		jQuery.ajax({					
			url : ajaxurl,					 
			dataType : "json",
			type : "post",
			data : search_params,
			success : function(response){ 
				if(response.code=='success'){					  						
					jQuery('#full-form-add-new').html('<div class="alert alert-info alert-dismissable"><a class="panel-close close" data-dismiss="alert">x</a>'+realpro_data.success_message  +' <a class="btn btn-sm" href="'+realpro_data.my_account_link+'" >'+realpro_data.my_account_link+'</a></div>');	
						
				}
				if(response.code=='error'){
					 jQuery('#update_message').html('<div class="alert alert-info alert-dismissable"><a class="panel-close close" data-dismiss="alert">x</a>'+response.msg +'.</div>');
					 jQuery('#update_message2').html('<div class="alert alert-info alert-dismissable"><a class="panel-close close" data-dismiss="alert">x</a>'+response.msg +'.</div>');
					
				}
				
			}
		});
	}	
}
 function IsEmail(email) {
	"use strict";
	var regex =/^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
	if (!regex.test(email)) {
		return false;
	}
	else {
		return true;
	}
}
function listinghub_save_post (){
	"use strict";
	tinyMCE.triggerSave();	
	var ajaxurl = realpro_data.ajaxurl;
	var loader_image = realpro_data.loading_image;
				jQuery('#update_message').html(loader_image);
				var search_params={
					"action"  : 	"listinghub_save_wp_post",	
					"form_data":	jQuery("#new_post").serialize(), 
					"_wpnonce":  	realpro_data.dirwpnonce,
				};
				jQuery.ajax({					
					url : ajaxurl,					 
					dataType : "json",
					type : "post",
					data : search_params,
					success : function(response){
						if(response.code=='success'){
								var url = realpro_data.permalink+"?&profile=all-post";    						
								jQuery(location).attr('href',url);	
						}
					
						
					}
				});
	
	}
function listinghub_add_faq_field(){
	"use strict";
	var main_faq_div =jQuery('#faqmain').html(); 
	jQuery('#faqsall').append('<div class="clearfix"></div><hr><div class="row">'+main_faq_div+'</div>');
}
function listinghub_faq_delete(id_delete){	
	"use strict";
	jQuery('#faq_delete_'+id_delete).remove();
}



function  listinghub_remove_post_image	(profile_image_id){
	"use strict";
	jQuery('#'+profile_image_id).html('');
	jQuery('#feature_image_id').val(''); 
	jQuery('#post_image_edit').html('<button type="button" onclick="listinghub_edit_post_image(\'post_image_div\');"  class="btn btn-small-ar">Add</button>');  

}	
 function listinghub_edit_post_image(profile_image_id){	
			"use strict";
				var image_gallery_frame;

             
                image_gallery_frame = wp.media.frames.downloadable_file = wp.media({
                    // Set the title of the modal.
                    title: realpro_data.Set_Feature_Image,
                    button: {
                        text: realpro_data.Set_Feature_Image, 
                    },
                    multiple: false,
                    displayUserSettings: true,
                });                
                image_gallery_frame.on( 'select', function() {
                    var selection = image_gallery_frame.state().get('selection');
                    selection.map( function( attachment ) {
                        attachment = attachment.toJSON();
                        if ( attachment.id ) {
							jQuery('#'+profile_image_id).html('<img  class="img-responsive rounded"  src="'+attachment.url+'">');
							jQuery('#feature_image_id').val(attachment.id ); 
							jQuery('#post_image_edit').html('<button type="button" onclick="listinghub_remove_post_image(\'post_image_div\');"  class="btn btn-small-ar">X</button>');  
						   
						}
					});
                   
                });               
				image_gallery_frame.open(); 
				
	}
// Banner 
function  listinghub_remove_topbanner_image	(profile_image_id){
	"use strict";
	jQuery('#'+profile_image_id).html('');
	jQuery('#topbanner_image_id').val(''); 
	jQuery('#post_image_topbaner').html('<button type="button" onclick="listinghub_topbanner_image(\'post_image_topbaner\');"  class="btn btn-small-ar">Add</button>');  

}	
 function listinghub_topbanner_image(profile_image_id){	
		"use strict";
		var image_gallery_frame;             
		image_gallery_frame = wp.media.frames.downloadable_file = wp.media({
			// Set the title of the modal.
			title: realpro_data.Set_Feature_Image,
			button: {
				text: realpro_data.Set_Feature_Image, 
			},
			multiple: false,
			displayUserSettings: true,
		});                
		image_gallery_frame.on( 'select', function() {
			var selection = image_gallery_frame.state().get('selection');
			selection.map( function( attachment ) {
				attachment = attachment.toJSON();
				if ( attachment.id ) {
					jQuery('#'+profile_image_id).html('<img  class="img-responsive rounded img-fluid"  src="'+attachment.url+'">');
					jQuery('#topbanner_image_id').val(attachment.id ); 
					jQuery('#post_image_topbaner').append('<button type="button" onclick="listinghub_remove_topbanner_image(\'post_image_topbaner\');"  class="btn btn-small-ar">X</button>');  
				   
				}
			});
		   
		});               
		image_gallery_frame.open(); 
				
}
		
 function listinghub_edit_gallery_image(profile_image_id){
				"use strict";
				var image_gallery_frame;
				var hidden_field_image_ids = jQuery('#gallery_image_ids').val();
              
                image_gallery_frame = wp.media.frames.downloadable_file = wp.media({
                    // Set the title of the modal.
                    title: realpro_data.Gallery_Images,
                    button: {
                        text: realpro_data.Gallery_Images,
                    },
                    multiple: true,
                    displayUserSettings: true,
                });                
                image_gallery_frame.on( 'select', function() {
                    var selection = image_gallery_frame.state().get('selection');
                    selection.map( function( attachment ) {
                        attachment = attachment.toJSON();
                        console.log(attachment);
                        if ( attachment.id ) {
							jQuery('#'+profile_image_id).append('<div id="gallery_image_div'+attachment.id+'" class="col-md-3"><img  class="img-responsive img-fluid"  src="'+attachment.url+'"><button type="button" onclick="listinghub_remove_gallery_image(\'gallery_image_div'+attachment.id+'\', '+attachment.id+');"  class="btn btn-small-ar">X</button> </div>');
							
							hidden_field_image_ids=hidden_field_image_ids+','+attachment.id ;
							jQuery('#gallery_image_ids').val(hidden_field_image_ids); 
							
							
						   
						}
					});
                   
                });               
				image_gallery_frame.open(); 

 }			

function  listinghub_remove_gallery_image(img_remove_div,rid){	
	"use strict";
	var hidden_field_image_ids = jQuery('#gallery_image_ids').val();	
	hidden_field_image_ids =hidden_field_image_ids.replace(rid, '');	
	jQuery('#'+img_remove_div).remove();
	jQuery('#gallery_image_ids').val(hidden_field_image_ids); 
	

}	

jQuery(document).ready(function() {
	jQuery("input[name$='contact_source']").on("click", function (){
		var rvalue = jQuery(this).val();
		
		if(rvalue=='new_value'){jQuery("#new_contact_div" ).show();}
		if(rvalue=='user_info'){jQuery("#new_contact_div" ).hide();}
		
		
	});
});	

		